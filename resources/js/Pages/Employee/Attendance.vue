<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AttendanceCard from '@/Components/AttendanceCard.vue';

const props = defineProps({
  employee: Object,
  schedule: Object,
  todayAttendance: Object,
  recentAttendances: Array,
  serverTime: String,
  announcements: Array,
  upcomingHolidays: Array,
  officeLocation: Object,
});

const page = usePage();
const office = computed(() => props.officeLocation || page.props.office || { latitude: -6.2088, longitude: 106.8456, radius: 150, name: 'Kantor Pusat Jakarta' });

// 1. Realtime Digital Clock
const currentTime = ref('');
const currentDate = ref('');
let timerInterval = null;

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
  currentDate.value = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

// 2. Geolocation & Haversine Distance
const userLat = ref(null);
const userLng = ref(null);
const distanceToOffice = ref(null);
const geoLoading = ref(true);
const geoError = ref(null);
const cameraError = ref(null);
const checkInError = ref(null);

function getDistanceMeters(lat1, lng1, lat2, lng2) {
  const R = 6371000;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLng = (lng2 - lng1) * Math.PI / 180;
  const a = Math.sin(dLat/2) ** 2 +
            Math.cos(lat1 * Math.PI/180) *
            Math.cos(lat2 * Math.PI/180) *
            Math.sin(dLng/2) ** 2;
  return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
}

const getLocation = () => {
  geoLoading.value = true;
  geoError.value = null;

  if (!navigator.geolocation) {
    geoError.value = 'Perangkat Anda tidak mendukung geolokasi GPS.';
    geoLoading.value = false;
    return;
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      userLat.value = position.coords.latitude;
      userLng.value = position.coords.longitude;
      distanceToOffice.value = getDistanceMeters(
        userLat.value,
        userLng.value,
        office.value.latitude,
        office.value.longitude
      );
      geoLoading.value = false;
    },
    (err) => {
      console.warn('Geolocation error:', err);
      geoError.value = 'Izin lokasi GPS tidak aktif atau diblokir. Aktifkan GPS untuk check-in status Hadir.';
      userLat.value = null;
      userLng.value = null;
      distanceToOffice.value = null;
      geoLoading.value = false;
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
};

// 3. Camera Selfie Feature
const isCameraOpen = ref(false);
const videoRef = ref(null);
const photoDataUrl = ref(null);
let mediaStream = null;

const openCamera = async () => {
  try {
    isCameraOpen.value = true;
    mediaStream = await navigator.mediaDevices.getUserMedia({ 
      video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }, 
      audio: false 
    });
    if (videoRef.value) {
      videoRef.value.srcObject = mediaStream;
    }
  } catch (err) {
    cameraError.value = 'Tidak dapat mengakses kamera: ' + err.message;
    isCameraOpen.value = false;
  }
};

const capturePhoto = () => {
  if (!videoRef.value) return;
  const canvas = document.createElement('canvas');
  canvas.width = videoRef.value.videoWidth || 640;
  canvas.height = videoRef.value.videoHeight || 480;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(videoRef.value, 0, 0, canvas.width, canvas.height);
  photoDataUrl.value = canvas.toDataURL('image/jpeg', 0.8);
  closeCamera();
};

const closeCamera = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
    mediaStream = null;
  }
  isCameraOpen.value = false;
};

// 4. Forms
const checkInForm = useForm({
  status: 'present',
  latitude: null,
  longitude: null,
  note: '',
  photo: null,
});

const checkOutForm = useForm({
  latitude: null,
  longitude: null,
  note: '',
});

const hasGpsLocation = computed(() => userLat.value !== null && userLng.value !== null);

const isWithinRadius = computed(() => {
  if (distanceToOffice.value === null) return false;
  return distanceToOffice.value <= office.value.radius;
});

const canCheckIn = computed(() => {
  return !props.todayAttendance || !props.todayAttendance.check_in_at;
});

const canCheckOut = computed(() => {
  return props.todayAttendance && props.todayAttendance.check_in_at && !props.todayAttendance.check_out_at;
});

const isDoneToday = computed(() => {
  return props.todayAttendance && props.todayAttendance.check_in_at && props.todayAttendance.check_out_at;
});

const submitCheckIn = () => {
  checkInError.value = null;

  if (checkInForm.status === 'present' && !hasGpsLocation.value) {
    checkInError.value = 'Lokasi GPS tidak tersedia. Aktifkan GPS untuk check-in status Hadir.';
    return;
  }

  if (checkInForm.status === 'present' && !isWithinRadius.value) {
    checkInError.value = `Lokasi Anda (${distanceToOffice.value}m) berada di luar batas radius maksimal ${office.value.radius}m dari kantor.`;
    return;
  }

  checkInForm.latitude = userLat.value;
  checkInForm.longitude = userLng.value;
  checkInForm.photo = photoDataUrl.value;

  checkInForm.post(route('employee.attendance.check-in'), {
    preserveScroll: true,
    onSuccess: () => {
      photoDataUrl.value = null;
    },
  });
};

const submitCheckOut = () => {
  checkOutForm.latitude = userLat.value;
  checkOutForm.longitude = userLng.value;

  checkOutForm.post(route('employee.attendance.check-out'), {
    preserveScroll: true,
  });
};

onMounted(() => {
  updateClock();
  timerInterval = setInterval(updateClock, 1000);
  getLocation();
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
  closeCamera();
});

const formatTime = (timeStr) => {
  if (!timeStr) return '--:--';
  const date = new Date(timeStr);
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <EmployeeLayout>
    <Head title="Presensi Hari Ini" />

    <div class="max-w-2xl mx-auto space-y-5">

      <!-- Upcoming Holiday Notice -->
      <div
        v-if="upcomingHolidays && upcomingHolidays.length > 0"
        class="bg-indigo-50/80 border border-indigo-100/90 rounded-2xl px-4 py-3 flex items-center justify-between gap-3 text-xs text-indigo-900 shadow-xs"
      >
        <div class="flex items-center gap-2.5">
          <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-bold">
            <i class="bi bi-calendar-event-fill"></i>
          </div>
          <div>
            <span class="text-slate-500 font-medium">Libur Nasional Terdekat: </span>
            <strong class="font-bold text-indigo-950">{{ upcomingHolidays[0].name }}</strong>
            <span class="text-indigo-600 ml-1">({{ formatDate(upcomingHolidays[0].date) }})</span>
          </div>
        </div>
        <span class="text-[10px] font-bold uppercase tracking-wider bg-indigo-200/60 text-indigo-800 px-2 py-0.5 rounded-md shrink-0">Libur</span>
      </div>

      <!-- 1. Live Clock & Shift Card (Gradient Blue Minimalist) -->
      <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-700 text-white p-6 sm:p-8 shadow-xl shadow-blue-600/20 text-center">
        <!-- Background decorative circle -->
        <div class="absolute -top-12 -right-12 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-40 h-40 bg-blue-400/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10">
          <div class="text-blue-100 text-xs font-semibold uppercase tracking-widest mb-1.5">
            {{ currentDate }}
          </div>
          <div class="text-4xl sm:text-5xl font-black tracking-tight my-2">
            {{ currentTime }}
          </div>
          <div class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-md px-4 py-1.5 rounded-full text-xs font-medium text-white/95 border border-white/20 mt-1">
            <i class="bi bi-briefcase text-blue-200"></i>
            <span>{{ schedule?.name || 'Shift Reguler' }}</span>
            <span class="text-blue-200 font-semibold">
              ({{ schedule?.start_time ? schedule.start_time.substring(0,5) : '08:00' }} - {{ schedule?.end_time ? schedule.end_time.substring(0,5) : '17:00' }})
            </span>
          </div>
        </div>
      </div>

      <!-- 2. Location & Radar Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex items-center justify-between mb-3.5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
              <i class="bi bi-geo-alt-fill text-lg"></i>
            </div>
            <div>
              <div class="font-bold text-slate-900 text-sm">Lokasi Presensi GPS</div>
              <div class="text-xs text-slate-400 font-medium">{{ office.name }}</div>
            </div>
          </div>

          <button 
            type="button" 
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-blue-600 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-xl transition-colors cursor-pointer"
            @click="getLocation"
          >
            <i class="bi bi-arrow-clockwise text-xs"></i>
            <span>Refresh GPS</span>
          </button>
        </div>

        <!-- Radar Status -->
        <div v-if="geoLoading" class="py-3 text-center text-xs text-slate-400 flex items-center justify-center gap-2 bg-slate-50 rounded-2xl">
          <span class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span>
          <span>Mendeteksi koordinat GPS perangkat Anda...</span>
        </div>
        <div 
          v-else-if="geoError" 
          class="p-4 rounded-2xl border transition-all bg-amber-50/60 border-amber-200/80"
        >
          <div class="flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-amber-600 text-base shrink-0"></i>
            <div>
              <div class="text-xs font-bold text-amber-800">GPS Tidak Tersedia</div>
              <div class="text-[11px] text-amber-700 mt-0.5">{{ geoError }}</div>
            </div>
          </div>
          <button 
            type="button" 
            class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-100 hover:bg-amber-200 border border-amber-300 rounded-xl transition-colors cursor-pointer"
            @click="getLocation"
          >
            <i class="bi bi-arrow-clockwise text-xs"></i>
            <span>Coba Lagi</span>
          </button>
        </div>
        <div 
          v-else 
          class="p-4 rounded-2xl border transition-all"
          :class="isWithinRadius ? 'bg-emerald-50/60 border-emerald-200/80' : 'bg-rose-50/60 border-rose-200/80'"
        >
          <div class="flex items-center justify-between">
            <div>
              <div class="text-xs font-bold flex items-center gap-1.5" :class="isWithinRadius ? 'text-emerald-700' : 'text-rose-700'">
                <i :class="isWithinRadius ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
                <span>{{ isWithinRadius ? 'Berada Dalam Radius Kantor' : 'Di Luar Batas Radius Kantor' }}</span>
              </div>
              <div class="text-[11px] text-slate-500 mt-1">
                Jarak Anda: <strong class="text-slate-800">{{ distanceToOffice }} meter</strong> &bull; Batas maksimal: {{ office.radius }}m
              </div>
            </div>

            <span 
              class="px-2.5 py-1 text-xs font-semibold rounded-full border"
              :class="isWithinRadius ? 'bg-emerald-100/70 text-emerald-800 border-emerald-300' : 'bg-rose-100/70 text-rose-800 border-rose-300'"
            >
              {{ isWithinRadius ? 'Bisa Hadir' : 'Luar Radius' }}
            </span>
          </div>
        </div>
      </div>

      <!-- 3. Attendance Action Section -->
      <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-7 shadow-xs">
        <h6 class="font-bold text-slate-900 text-sm flex items-center gap-2 mb-4">
          <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
            <i class="bi bi-fingerprint text-base"></i>
          </div>
          <span>Aktivitas Presensi Hari Ini</span>
        </h6>

        <!-- State 1: Completed Today -->
        <div v-if="isDoneToday" class="text-center py-4">
          <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-3">
            <i class="bi bi-check-circle-fill text-3xl"></i>
          </div>
          <h5 class="text-lg font-bold text-slate-900">Absensi Hari Ini Lengkap!</h5>
          <p class="text-xs text-slate-500 mt-1 mb-5">Terima kasih, seluruh rekaman jam kerja hari ini telah tersimpan.</p>

          <div class="grid grid-cols-2 gap-3 max-w-sm mx-auto">
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Jam Masuk</span>
              <span class="text-base font-bold text-emerald-600 mt-0.5 block">{{ formatTime(todayAttendance.check_in_at) }}</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Jam Keluar</span>
              <span class="text-base font-bold text-blue-600 mt-0.5 block">{{ formatTime(todayAttendance.check_out_at) }}</span>
            </div>
          </div>
        </div>

        <!-- State 2: Ready to Check-In -->
        <form v-else-if="canCheckIn" @submit.prevent="submitCheckIn" class="space-y-4">
          <!-- Status Grid Selection -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
              Pilih Status Kehadiran *
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
              <!-- Hadir -->
              <label 
                class="flex flex-col items-center justify-center p-3 rounded-2xl border cursor-pointer transition-all text-center"
                :class="checkInForm.status === 'present' ? 'bg-blue-50/80 border-blue-600 text-blue-700 ring-2 ring-blue-600/20 font-semibold' : 'bg-slate-50/70 border-slate-200 text-slate-600 hover:bg-slate-100'"
              >
                <input type="radio" value="present" v-model="checkInForm.status" class="hidden">
                <i class="bi bi-building text-xl mb-1" :class="checkInForm.status === 'present' ? 'text-blue-600' : 'text-slate-400'"></i>
                <span class="text-xs">Hadir Kantor</span>
              </label>

              <!-- WFH -->
              <label 
                class="flex flex-col items-center justify-center p-3 rounded-2xl border cursor-pointer transition-all text-center"
                :class="checkInForm.status === 'wfh' ? 'bg-blue-50/80 border-blue-600 text-blue-700 ring-2 ring-blue-600/20 font-semibold' : 'bg-slate-50/70 border-slate-200 text-slate-600 hover:bg-slate-100'"
              >
                <input type="radio" value="wfh" v-model="checkInForm.status" class="hidden">
                <i class="bi bi-laptop text-xl mb-1" :class="checkInForm.status === 'wfh' ? 'text-blue-600' : 'text-slate-400'"></i>
                <span class="text-xs">WFH</span>
              </label>

              <!-- Izin -->
              <label 
                class="flex flex-col items-center justify-center p-3 rounded-2xl border cursor-pointer transition-all text-center"
                :class="checkInForm.status === 'permission' ? 'bg-blue-50/80 border-blue-600 text-blue-700 ring-2 ring-blue-600/20 font-semibold' : 'bg-slate-50/70 border-slate-200 text-slate-600 hover:bg-slate-100'"
              >
                <input type="radio" value="permission" v-model="checkInForm.status" class="hidden">
                <i class="bi bi-card-checklist text-xl mb-1" :class="checkInForm.status === 'permission' ? 'text-blue-600' : 'text-slate-400'"></i>
                <span class="text-xs">Izin</span>
              </label>

              <!-- Sakit -->
              <label 
                class="flex flex-col items-center justify-center p-3 rounded-2xl border cursor-pointer transition-all text-center"
                :class="checkInForm.status === 'sick' ? 'bg-blue-50/80 border-blue-600 text-blue-700 ring-2 ring-blue-600/20 font-semibold' : 'bg-slate-50/70 border-slate-200 text-slate-600 hover:bg-slate-100'"
              >
                <input type="radio" value="sick" v-model="checkInForm.status" class="hidden">
                <i class="bi bi-heart-pulse text-xl mb-1" :class="checkInForm.status === 'sick' ? 'text-blue-600' : 'text-slate-400'"></i>
                <span class="text-xs">Sakit</span>
              </label>
            </div>
          </div>

          <!-- Note Input -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Catatan Keterangan (Opsional)
            </label>
            <input 
              v-model="checkInForm.note" 
              type="text" 
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
              placeholder="Contoh: Bekerja di klien, agak flu, dll."
            />
          </div>

          <!-- Selfie Camera Section -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Foto Selfie (Opsional)
            </label>

            <!-- Video preview -->
            <div v-if="isCameraOpen" class="space-y-2 text-center">
              <video ref="videoRef" autoplay playsinline class="w-full max-h-56 object-cover rounded-2xl border border-slate-200 bg-black"></video>
              <div class="flex justify-center gap-2">
                <button 
                  type="button" 
                  class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
                  @click="capturePhoto"
                >
                  <i class="bi bi-camera"></i>
                  <span>Ambil Foto</span>
                </button>
                <button 
                  type="button" 
                  class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer"
                  @click="closeCamera"
                >
                  Batal
                </button>
              </div>
            </div>

            <!-- Taken Photo preview -->
            <div v-else-if="photoDataUrl" class="flex items-center gap-3 p-3 bg-emerald-50/60 rounded-2xl border border-emerald-200/80">
              <img :src="photoDataUrl" class="w-16 h-16 rounded-xl object-cover border border-emerald-300" />
              <div>
                <div class="text-emerald-800 text-xs font-bold flex items-center gap-1">
                  <i class="bi bi-check2-circle"></i>
                  <span>Foto selfie tersimpan</span>
                </div>
                <button 
                  type="button" 
                  class="text-[11px] font-semibold text-rose-600 hover:text-rose-700 underline mt-1 cursor-pointer"
                  @click="photoDataUrl = null"
                >
                  Hapus & Ambil Ulang
                </button>
              </div>
            </div>

            <!-- Camera button -->
            <div v-else>
              <button 
                type="button" 
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-colors cursor-pointer"
                @click="openCamera"
              >
                <i class="bi bi-camera-fill text-blue-600"></i>
                <span>Buka Kamera Selfie</span>
              </button>
            </div>
          </div>

          <!-- Camera Error -->
          <div v-if="cameraError" class="flex items-center gap-2 p-3 text-xs rounded-xl bg-rose-50 border border-rose-200/80 text-rose-800">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 shrink-0"></i>
            <span class="flex-1 font-medium">{{ cameraError }}</span>
            <button type="button" class="text-rose-600 hover:text-rose-800 cursor-pointer" @click="cameraError = null">
              <i class="bi bi-x-lg text-xs"></i>
            </button>
          </div>

          <!-- Check-in Error -->
          <div v-if="checkInError" class="flex items-center gap-2 p-3 text-xs rounded-xl bg-rose-50 border border-rose-200/80 text-rose-800">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 shrink-0"></i>
            <span class="flex-1 font-medium">{{ checkInError }}</span>
            <button type="button" class="text-rose-600 hover:text-rose-800 cursor-pointer" @click="checkInError = null">
              <i class="bi bi-x-lg text-xs"></i>
            </button>
          </div>

          <!-- Big Touch-Friendly Button -->
          <button 
            type="submit" 
            class="w-full h-14 rounded-2xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-sm sm:text-base shadow-lg shadow-blue-600/25 flex items-center justify-center gap-2.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            :disabled="checkInForm.processing || (checkInForm.status === 'present' && (!hasGpsLocation || !isWithinRadius))"
          >
            <span v-if="checkInForm.processing" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <i v-else class="bi bi-box-arrow-in-right text-lg"></i>
            <span>Check-in Sekarang</span>
          </button>

          <div v-if="checkInForm.status === 'present' && !hasGpsLocation" class="text-amber-600 text-center text-xs font-medium flex items-center justify-center gap-1">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Aktifkan lokasi GPS untuk check-in sebagai Hadir.</span>
          </div>
          <div v-else-if="checkInForm.status === 'present' && !isWithinRadius" class="text-rose-600 text-center text-xs font-medium flex items-center justify-center gap-1">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Anda harus berada dalam radius kantor untuk status Hadir.</span>
          </div>
        </form>

        <!-- State 3: Ready to Check-Out -->
        <form v-else-if="canCheckOut" @submit.prevent="submitCheckOut" class="space-y-4">
          <div class="p-3.5 bg-blue-50/70 border border-blue-200/70 rounded-2xl text-xs text-blue-800 flex items-center gap-2.5">
            <i class="bi bi-info-circle-fill text-blue-600 text-base shrink-0"></i>
            <div>
              Anda telah check-in pada pukul <strong>{{ formatTime(todayAttendance.check_in_at) }}</strong> 
              (Status: <StatusBadge :status="todayAttendance.status" />).
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Catatan Kepulangan (Opsional)
            </label>
            <input 
              v-model="checkOutForm.note" 
              type="text" 
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
              placeholder="Pekerjaan hari ini selesai, dsb."
            />
          </div>

          <!-- Big Check-out Button -->
          <button 
            type="submit" 
            class="w-full h-14 rounded-2xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-sm sm:text-base shadow-lg shadow-rose-600/25 flex items-center justify-center gap-2.5 transition-all disabled:opacity-50 cursor-pointer"
            :disabled="checkOutForm.processing"
          >
            <span v-if="checkOutForm.processing" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <i v-else class="bi bi-box-arrow-left text-lg"></i>
            <span>Check-out Pulang</span>
          </button>
        </form>
      </div>

      <!-- 4. 7 Days Recent History -->
      <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-7 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <h6 class="font-bold text-slate-900 text-sm flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
              <i class="bi bi-clock-history text-base"></i>
            </div>
            <span>Riwayat 7 Hari Terakhir</span>
          </h6>
          <Link :href="route('employee.history')" class="text-xs text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1">
            <span>Lihat Semua</span>
            <i class="bi bi-arrow-right"></i>
          </Link>
        </div>

        <div v-if="recentAttendances && recentAttendances.length > 0">
          <!-- Mobile View: Cards -->
          <div class="md:hidden space-y-2">
            <AttendanceCard 
              v-for="item in recentAttendances" 
              :key="item.id" 
              :attendance="item" 
            />
          </div>

          <!-- Desktop View: Table -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold">
                  <th class="pb-3 px-2">Tanggal</th>
                  <th class="pb-3 px-2">Jam Masuk</th>
                  <th class="pb-3 px-2">Jam Keluar</th>
                  <th class="pb-3 px-2">Status</th>
                  <th class="pb-3 px-2">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="item in recentAttendances" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-2 font-semibold text-slate-900">{{ item.date }}</td>
                  <td class="py-3 px-2 font-bold text-emerald-600">{{ formatTime(item.check_in_at) }}</td>
                  <td class="py-3 px-2 font-bold text-blue-600">{{ formatTime(item.check_out_at) }}</td>
                  <td class="py-3 px-2"><StatusBadge :status="item.status" /></td>
                  <td class="py-3 px-2 text-slate-500 max-w-xs truncate">{{ item.note || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else class="text-center py-6 text-xs text-slate-400">
          Belum ada riwayat presensi yang tercatat.
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>
