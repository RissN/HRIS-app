<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  settings: Object,
  flash: Object,
});

const form = useForm({
  office_name: props.settings.office_name || 'Kantor Pusat Jakarta',
  office_latitude: props.settings.office_latitude || '-6.2088000',
  office_longitude: props.settings.office_longitude || '106.8456000',
  office_radius: props.settings.office_radius || '150',
  rate_daily_allowance: props.settings.rate_daily_allowance || '50000',
  rate_late_deduction: props.settings.rate_late_deduction || '25000',
  rate_absent_deduction: props.settings.rate_absent_deduction || '100000',
});

const isLocating = ref(false);
const locationError = ref('');
const mapContainer = ref(null);
let map = null;
let marker = null;
let circle = null;

const initMap = () => {
  if (typeof window === 'undefined' || !window.L || !mapContainer.value) return;

  const lat = parseFloat(form.office_latitude) || -6.2088;
  const lng = parseFloat(form.office_longitude) || 106.8456;
  const radius = parseFloat(form.office_radius) || 150;

  if (!map) {
    map = window.L.map(mapContainer.value).setView([lat, lng], 16);

    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap contributors',
    }).addTo(map);

    marker = window.L.marker([lat, lng], { draggable: true }).addTo(map);
    circle = window.L.circle([lat, lng], {
      radius: radius,
      color: '#2563eb',
      fillColor: '#3b82f6',
      fillOpacity: 0.2,
      weight: 2,
    }).addTo(map);

    // Click on map to set position
    map.on('click', (e) => {
      updateCoordinates(e.latlng.lat, e.latlng.lng);
    });

    // Drag marker to set position
    marker.on('dragend', (e) => {
      const position = e.target.getLatLng();
      updateCoordinates(position.lat, position.lng);
    });
  } else {
    map.setView([lat, lng], 16);
    marker.setLatLng([lat, lng]);
    circle.setLatLng([lat, lng]);
    circle.setRadius(radius);
  }
};

const updateCoordinates = (lat, lng) => {
  form.office_latitude = lat.toFixed(7);
  form.office_longitude = lng.toFixed(7);
  if (marker) marker.setLatLng([lat, lng]);
  if (circle) circle.setLatLng([lat, lng]);
};

// Update circle radius when form changes
watch(() => form.office_radius, (newRadius) => {
  if (circle) {
    circle.setRadius(parseFloat(newRadius) || 150);
  }
});

watch([() => form.office_latitude, () => form.office_longitude], ([newLat, newLng]) => {
  const lat = parseFloat(newLat);
  const lng = parseFloat(newLng);
  if (!isNaN(lat) && !isNaN(lng) && map && marker && circle) {
    marker.setLatLng([lat, lng]);
    circle.setLatLng([lat, lng]);
  }
});

onMounted(() => {
  // Dynamically load Leaflet if not present
  if (!window.L) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => {
      setTimeout(initMap, 200);
    };
    document.body.appendChild(script);
  } else {
    initMap();
  }
});

const getCurrentLocation = () => {
  if (!navigator.geolocation) {
    locationError.value = 'Perangkat tidak mendukung geolokasi GPS.';
    return;
  }

  isLocating.value = true;
  locationError.value = '';

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      isLocating.value = false;
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      updateCoordinates(lat, lng);
      if (map) map.setView([lat, lng], 17);
    },
    (err) => {
      isLocating.value = false;
      locationError.value = 'Gagal mengambil lokasi GPS: ' + err.message;
    },
    { enableHighAccuracy: true, timeout: 10000 }
  );
};

const submit = () => {
  form.clearErrors();
  let hasError = false;

  if (!form.office_name.trim()) {
    form.setError('office_name', 'Nama kantor wajib diisi');
    hasError = true;
  }
  if (!form.office_latitude || isNaN(form.office_latitude)) {
    form.setError('office_latitude', 'Latitude kantor tidak valid');
    hasError = true;
  }
  if (!form.office_longitude || isNaN(form.office_longitude)) {
    form.setError('office_longitude', 'Longitude kantor tidak valid');
    hasError = true;
  }
  if (!form.office_radius || form.office_radius < 10) {
    form.setError('office_radius', 'Radius minimal adalah 10 meter');
    hasError = true;
  }

  if (hasError) return;

  form.post(route('admin.settings.update'), {
    preserveScroll: true,
  });
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
  <AdminLayout>
    <Head title="Pengaturan Kantor & Geofencing - HRIS" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1">
              <i class="bi bi-gear-fill"></i>
              <span>Konfigurasi HRIS</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Kantor & Radius Presensi</h1>
            <p class="text-sm text-slate-500 mt-0.5">
              Atur titik koordinat kantor, batasan geofencing GPS presensi, dan tarif tunjangan serta potongan.
            </p>
          </div>

          <!-- Save Button -->
          <button
            type="button"
            @click="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-xs shadow-blue-600/20 transition-all active:scale-95 disabled:opacity-50"
          >
            <i class="bi bi-floppy-fill"></i>
            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
          </button>
        </div>
      </div>

      <!-- Flash Notification -->
      <div
        v-if="flash?.success"
        class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm"
      >
        <i class="bi bi-check-circle-fill text-emerald-600 text-lg"></i>
        <span>{{ flash.success }}</span>
      </div>

      <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Geofencing & Office Location (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
          <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
              <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                  <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div>
                  <h2 class="font-bold text-slate-900 text-base">Lokasi Kantor & Geofencing GPS</h2>
                  <p class="text-xs text-slate-500">Klik peta atau geser pin untuk menentukan titik pusat kantor</p>
                </div>
              </div>

              <!-- Button Locate GPS -->
              <button
                type="button"
                @click="getCurrentLocation"
                :disabled="isLocating"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition active:scale-95 disabled:opacity-50"
              >
                <i class="bi" :class="isLocating ? 'bi-arrow-repeat animate-spin text-blue-600' : 'bi-crosshair text-blue-600'"></i>
                <span>{{ isLocating ? 'Mencari...' : 'Gunakan GPS Saya' }}</span>
              </button>
            </div>

            <!-- Error message if GPS fail -->
            <div v-if="locationError" class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <span>{{ locationError }}</span>
            </div>

            <!-- Interactive Map Container -->
            <div class="rounded-2xl border border-slate-200 overflow-hidden mb-5 relative">
              <div ref="mapContainer" class="h-80 w-full z-0 bg-slate-100"></div>
              <div class="absolute bottom-3 left-3 bg-white/95 backdrop-blur-xs border border-slate-200 px-3 py-1.5 rounded-xl shadow-xs text-xs text-slate-700 flex items-center gap-2 z-[1000]">
                <span class="w-3 h-3 rounded-full bg-blue-500/30 border border-blue-600 inline-block"></span>
                <span>Radius Kantor: <strong>{{ form.office_radius }} meter</strong></span>
              </div>
            </div>

            <!-- Form Fields for Coordinates -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Kantor / Gedung</label>
                <input
                  type="text"
                  v-model="form.office_name"
                  placeholder="Contoh: Kantor Pusat Jakarta"
                  class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
                  :class="{ 'border-rose-400 bg-rose-50/50': form.errors.office_name }"
                />
                <p v-if="form.errors.office_name" class="mt-1 text-xs text-rose-600">{{ form.errors.office_name }}</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Latitude</label>
                <input
                  type="text"
                  v-model="form.office_latitude"
                  class="w-full font-mono bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
                  :class="{ 'border-rose-400 bg-rose-50/50': form.errors.office_latitude }"
                />
                <p v-if="form.errors.office_latitude" class="mt-1 text-xs text-rose-600">{{ form.errors.office_latitude }}</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Longitude</label>
                <input
                  type="text"
                  v-model="form.office_longitude"
                  class="w-full font-mono bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
                  :class="{ 'border-rose-400 bg-rose-50/50': form.errors.office_longitude }"
                />
                <p v-if="form.errors.office_longitude" class="mt-1 text-xs text-rose-600">{{ form.errors.office_longitude }}</p>
              </div>

              <div>
                <div class="flex items-center justify-between mb-1.5">
                  <label class="block text-xs font-semibold text-slate-700">Radius Toleransi (Meter)</label>
                  <span class="text-xs font-bold text-blue-600 font-mono">{{ form.office_radius }} m</span>
                </div>
                <input
                  type="number"
                  min="10"
                  max="5000"
                  step="10"
                  v-model="form.office_radius"
                  class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition font-mono"
                  :class="{ 'border-rose-400 bg-rose-50/50': form.errors.office_radius }"
                />
                <p v-if="form.errors.office_radius" class="mt-1 text-xs text-rose-600">{{ form.errors.office_radius }}</p>
              </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center gap-2 text-xs text-slate-500">
              <i class="bi bi-info-circle-fill text-blue-500"></i>
              <span>Pegawai yang melakukan check-in di luar radius ini akan ditandai atau diberikan peringatan jarak.</span>
            </div>
          </div>
        </div>

        <!-- Payroll Rates & Rules (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
          <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
            <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100">
              <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="bi bi-cash-stack"></i>
              </div>
              <div>
                <h2 class="font-bold text-slate-900 text-base">Tarif Payroll & Potongan</h2>
                <p class="text-xs text-slate-500">Aturan perhitungan gaji otomatis</p>
              </div>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                  Uang Makan / Tunjangan Harian
                </label>
                <div class="relative">
                  <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                  <input
                    type="number"
                    step="1000"
                    v-model="form.rate_daily_allowance"
                    class="w-full pl-10 bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition font-mono"
                  />
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Diberikan per kehadiran tepat waktu/WFH</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                  Potongan Keterlambatan
                </label>
                <div class="relative">
                  <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                  <input
                    type="number"
                    step="1000"
                    v-model="form.rate_late_deduction"
                    class="w-full pl-10 bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition font-mono"
                  />
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Dipotong per hari saat terlambat absen</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                  Potongan Alpa / Mangkir
                </label>
                <div class="relative">
                  <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                  <input
                    type="number"
                    step="1000"
                    v-model="form.rate_absent_deduction"
                    class="w-full pl-10 bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition font-mono"
                  />
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Dipotong per hari tidak hadir tanpa izin resmi</p>
              </div>
            </div>

            <div class="mt-6 pt-5 border-t border-slate-100">
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Ringkasan Nilai Aktif</h3>
              <div class="space-y-1.5 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-50">
                  <span class="text-slate-600">Tunjangan Harian:</span>
                  <span class="font-bold text-emerald-600 font-mono">{{ formatRupiah(form.rate_daily_allowance) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                  <span class="text-slate-600">Denda Terlambat:</span>
                  <span class="font-bold text-amber-600 font-mono">{{ formatRupiah(form.rate_late_deduction) }}</span>
                </div>
                <div class="flex justify-between py-1">
                  <span class="text-slate-600">Denda Alpa:</span>
                  <span class="font-bold text-rose-600 font-mono">{{ formatRupiah(form.rate_absent_deduction) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
