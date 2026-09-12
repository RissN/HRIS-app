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
});

const page = usePage();
const office = computed(() => page.props.office || { latitude: -6.2088, longitude: 106.8456, radius: 150, name: 'Kantor Pusat' });

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
      // Fallback for local development demo if user denies permission or browser blocks
      geoError.value = 'Izin lokasi tidak aktif atau diblokir. Menggunakan koordinat kantor untuk demo.';
      userLat.value = office.value.latitude;
      userLng.value = office.value.longitude;
      distanceToOffice.value = 0;
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
    alert('Tidak dapat mengakses kamera: ' + err.message);
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

// 4. Check-in and Check-out Forms
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
  if (checkInForm.status === 'present' && !isWithinRadius.value) {
    alert(`Lokasi Anda (${distanceToOffice.value}m) di luar radius maksimal ${office.value.radius}m dari kantor.`);
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
</script>

<template>
  <EmployeeLayout>
    <Head title="Presensi Hari Ini" />

    <div class="row justify-content-center">
      <div class="col-12 col-lg-8 col-xl-7">
        <!-- Live Clock & Schedule Card -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-4 rounded-4 mb-4 text-center" style="background: linear-gradient(180deg, #1e1b4b 0%, #161825 100%);">
          <div class="text-secondary small text-uppercase fw-semibold mb-1" style="letter-spacing: 0.08em;">
            {{ currentDate }}
          </div>
          <div class="display-4 fw-bold text-white mb-2 tracking-tight">
            {{ currentTime }}
          </div>
          <div class="d-inline-flex align-items-center justify-content-center gap-2 bg-dark bg-opacity-60 px-3 py-1 rounded-pill mx-auto small border border-secondary border-opacity-25">
            <i class="bi bi-briefcase text-primary"></i>
            <span class="text-white">{{ schedule?.name || 'Shift Reguler' }}</span>
            <span class="text-secondary">({{ schedule?.start_time ? schedule.start_time.substring(0,5) : '08:00' }} - {{ schedule?.end_time ? schedule.end_time.substring(0,5) : '17:00' }})</span>
          </div>
        </div>

        <!-- Location Radar Card -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-3 rounded-4 mb-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
              <div>
                <div class="fw-bold text-white small">Lokasi Anda & Kantor</div>
                <div class="text-secondary" style="font-size: 0.72rem;">{{ office.name }}</div>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;" @click="getLocation">
              <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
          </div>

          <!-- Radius info -->
          <div v-if="geoLoading" class="text-center py-2 text-secondary small">
            <span class="spinner-border spinner-border-sm me-2"></span> Mendeteksi koordinat GPS...
          </div>
          <div v-else class="p-2 rounded-3" :class="isWithinRadius ? 'bg-success bg-opacity-10 border border-success border-opacity-25' : 'bg-danger bg-opacity-10 border border-danger border-opacity-25'">
            <div class="d-flex align-items-center justify-content-between">
              <div class="small">
                <span :class="isWithinRadius ? 'text-success fw-bold' : 'text-danger fw-bold'">
                  <i :class="isWithinRadius ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
                  {{ isWithinRadius ? 'Dalam Radius Kantor' : 'Di Luar Radius Kantor' }}
                </span>
                <div class="text-secondary" style="font-size: 0.72rem;">
                  Jarak: <strong>{{ distanceToOffice }} meter</strong> (Maks. {{ office.radius }}m)
                </div>
              </div>
              <span class="badge" :class="isWithinRadius ? 'bg-success' : 'bg-danger'">
                {{ isWithinRadius ? 'Bisa Hadir' : 'Luar Radius' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Attendance Action Form / State -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-4 rounded-4 mb-4">
          <h6 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-fingerprint text-primary"></i>
            Aktivitas Presensi Hari Ini
          </h6>

          <!-- State 1: Already Completed for Today -->
          <div v-if="isDoneToday" class="text-center py-3">
            <div class="d-inline-flex bg-success bg-opacity-10 text-success p-3 rounded-circle mb-2">
              <i class="bi bi-check-circle-fill fs-2"></i>
            </div>
            <h5 class="text-white fw-bold">Absensi Hari Ini Selesai!</h5>
            <p class="text-secondary small mb-3">Terima kasih atas kerja keras Anda hari ini.</p>
            <div class="row g-2 justify-content-center">
              <div class="col-5 p-2 bg-dark rounded-2 border border-secondary border-opacity-25">
                <small class="text-secondary d-block">Masuk</small>
                <span class="fw-bold text-success">{{ formatTime(todayAttendance.check_in_at) }}</span>
              </div>
              <div class="col-5 p-2 bg-dark rounded-2 border border-secondary border-opacity-25">
                <small class="text-secondary d-block">Keluar</small>
                <span class="fw-bold text-info">{{ formatTime(todayAttendance.check_out_at) }}</span>
              </div>
            </div>
          </div>

          <!-- State 2: Ready to Check-In -->
          <form v-else-if="canCheckIn" @submit.prevent="submitCheckIn">
            <!-- Status Selection -->
            <div class="mb-3">
              <label class="form-label small text-secondary fw-semibold">Status Kehadiran *</label>
              <div class="row g-2">
                <div class="col-6 col-sm-3">
                  <input type="radio" class="btn-check" id="st-present" value="present" v-model="checkInForm.status">
                  <label class="btn btn-outline-success w-100 py-2 small" for="st-present">
                    <i class="bi bi-building d-block mb-1 fs-5"></i> Hadir
                  </label>
                </div>
                <div class="col-6 col-sm-3">
                  <input type="radio" class="btn-check" id="st-wfh" value="wfh" v-model="checkInForm.status">
                  <label class="btn btn-outline-info w-100 py-2 small" for="st-wfh">
                    <i class="bi bi-laptop d-block mb-1 fs-5"></i> WFH
                  </label>
                </div>
                <div class="col-6 col-sm-3">
                  <input type="radio" class="btn-check" id="st-permission" value="permission" v-model="checkInForm.status">
                  <label class="btn btn-outline-purple w-100 py-2 small" for="st-permission">
                    <i class="bi bi-card-checklist d-block mb-1 fs-5"></i> Izin
                  </label>
                </div>
                <div class="col-6 col-sm-3">
                  <input type="radio" class="btn-check" id="st-sick" value="sick" v-model="checkInForm.status">
                  <label class="btn btn-outline-primary w-100 py-2 small" for="st-sick">
                    <i class="bi bi-heart-pulse d-block mb-1 fs-5"></i> Sakit
                  </label>
                </div>
              </div>
            </div>

            <!-- Notes Field -->
            <div class="mb-3">
              <label class="form-label small text-secondary fw-semibold">Catatan Keterangan (Opsional)</label>
              <input 
                v-model="checkInForm.note" 
                type="text" 
                class="form-control form-control-sm" 
                placeholder="Contoh: Bertugas di klien, agak flu, dsb."
              />
            </div>

            <!-- Selfie Capture Section -->
            <div class="mb-4">
              <label class="form-label small text-secondary fw-semibold d-block">Foto Selfie (Opsional)</label>
              
              <!-- Video Preview when camera active -->
              <div v-if="isCameraOpen" class="text-center mb-2">
                <video ref="videoRef" autoplay playsinline class="rounded-3 border border-secondary w-100" style="max-height: 240px; object-fit: cover;"></video>
                <div class="d-flex justify-content-center gap-2 mt-2">
                  <button type="button" class="btn btn-success btn-sm" @click="capturePhoto">
                    <i class="bi bi-camera me-1"></i> Ambil Foto
                  </button>
                  <button type="button" class="btn btn-secondary btn-sm" @click="closeCamera">
                    Batal
                  </button>
                </div>
              </div>

              <!-- Photo preview when taken -->
              <div v-else-if="photoDataUrl" class="d-flex align-items-center gap-3">
                <img :src="photoDataUrl" class="rounded-3 border border-secondary" style="width: 80px; height: 80px; object-fit: cover;" />
                <div>
                  <div class="text-success small fw-semibold"><i class="bi bi-check2"></i> Foto selfie terlampir</div>
                  <button type="button" class="btn btn-outline-danger btn-sm py-0 mt-1" style="font-size: 0.72rem;" @click="photoDataUrl = null">
                    Hapus
                  </button>
                </div>
              </div>

              <!-- Button to open camera -->
              <div v-else>
                <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2" @click="openCamera">
                  <i class="bi bi-camera-fill text-primary"></i> Ambil Selfie Kamera
                </button>
              </div>
            </div>

            <!-- Check-in Big Mobile Button -->
            <button 
              type="submit" 
              class="btn btn-primary btn-mobile-lg w-100 shadow"
              :disabled="checkInForm.processing || (checkInForm.status === 'present' && !isWithinRadius)"
            >
              <span v-if="checkInForm.processing" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-box-arrow-in-right fs-5"></i>
              <span>Check-in Sekarang</span>
            </button>
            <div v-if="checkInForm.status === 'present' && !isWithinRadius" class="text-danger text-center small mt-2">
              <i class="bi bi-exclamation-triangle"></i> Anda harus berada dalam radius kantor untuk Check-in Hadir.
            </div>
          </form>

          <!-- State 3: Ready to Check-Out -->
          <form v-else-if="canCheckOut" @submit.prevent="submitCheckOut">
            <div class="alert alert-info py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-info-circle fs-5"></i>
              <div>
                Anda telah check-in pada pukul <strong>{{ formatTime(todayAttendance.check_in_at) }}</strong> (Status: <StatusBadge :status="todayAttendance.status" />).
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small text-secondary fw-semibold">Catatan Kepulangan (Opsional)</label>
              <input 
                v-model="checkOutForm.note" 
                type="text" 
                class="form-control form-control-sm" 
                placeholder="Pekerjaan hari ini selesai, dsb."
              />
            </div>

            <!-- Check-out Big Mobile Button -->
            <button 
              type="submit" 
              class="btn btn-danger btn-mobile-lg w-100 shadow"
              :disabled="checkOutForm.processing"
            >
              <span v-if="checkOutForm.processing" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-box-arrow-left fs-5"></i>
              <span>Check-out Pulang</span>
            </button>
          </form>
        </div>

        <!-- 7 Days History Section -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-4 rounded-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-clock-history text-primary"></i>
              Riwayat 7 Hari Terakhir
            </h6>
            <Link :href="route('employee.history')" class="small text-primary text-decoration-none fw-medium">
              Lihat Semua &rarr;
            </Link>
          </div>

          <div v-if="recentAttendances && recentAttendances.length > 0">
            <!-- Mobile Card View -->
            <div class="d-md-none">
              <AttendanceCard 
                v-for="item in recentAttendances" 
                :key="item.id" 
                :attendance="item" 
              />
            </div>

            <!-- Desktop Table View -->
            <div class="d-none d-md-block table-responsive">
              <table class="table table-hover align-middle small mb-0">
                <thead>
                  <tr class="text-secondary">
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in recentAttendances" :key="item.id">
                    <td class="text-white fw-medium">{{ item.date }}</td>
                    <td class="text-success fw-bold">{{ formatTime(item.check_in_at) }}</td>
                    <td class="text-info fw-bold">{{ formatTime(item.check_out_at) }}</td>
                    <td><StatusBadge :status="item.status" /></td>
                    <td class="text-secondary text-truncate" style="max-width: 180px;">{{ item.note || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div v-else class="text-center py-4 text-secondary small">
            Belum ada data riwayat presensi.
          </div>
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>
