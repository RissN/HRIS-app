<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AttendanceCard from '@/Components/AttendanceCard.vue';

const props = defineProps({
  attendances: Array,
  filters: Object,
  summary: Object,
});

const month = ref(props.filters.month);
const year = ref(props.filters.year);

const months = [
  { value: 1, label: 'Januari' },
  { value: 2, label: 'Februari' },
  { value: 3, label: 'Maret' },
  { value: 4, label: 'April' },
  { value: 5, label: 'Mei' },
  { value: 6, label: 'Juni' },
  { value: 7, label: 'Juli' },
  { value: 8, label: 'Agustus' },
  { value: 9, label: 'September' },
  { value: 10, label: 'Oktober' },
  { value: 11, label: 'November' },
  { value: 12, label: 'Desember' },
];

const applyFilter = () => {
  router.get(route('employee.history'), {
    month: month.value,
    year: year.value,
  }, { preserveState: true });
};

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
    <Head title="Riwayat Presensi" />

    <div class="max-w-4xl mx-auto space-y-5">
      <!-- Header & Filters Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Riwayat Presensi Saya</h1>
            <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi kehadiran bulanan serta opsi pelaporan komplain.</p>
          </div>

          <!-- Month & Year Filters -->
          <div class="flex items-center gap-2">
            <select 
              v-model="month" 
              class="px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
              @change="applyFilter"
            >
              <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>

            <select 
              v-model="year" 
              class="px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
              @change="applyFilter"
            >
              <option :value="2024">2024</option>
              <option :value="2025">2025</option>
              <option :value="2026">2026</option>
              <option :value="2027">2027</option>
            </select>
          </div>
        </div>
      </div>

      <!-- 4 Summary Metric Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <!-- Hadir -->
        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100 text-center">
          <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Total Hadir</div>
          <div class="text-2xl sm:text-3xl font-black text-emerald-700 mt-1">{{ summary.present }}</div>
        </div>

        <!-- Terlambat -->
        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-100 text-center">
          <div class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Terlambat</div>
          <div class="text-2xl sm:text-3xl font-black text-amber-700 mt-1">{{ summary.late }}</div>
        </div>

        <!-- Izin/Sakit/WFH -->
        <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 text-center">
          <div class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Izin / Sakit / WFH</div>
          <div class="text-2xl sm:text-3xl font-black text-blue-700 mt-1">{{ summary.permission + summary.wfh }}</div>
        </div>

        <!-- Absen/Alpa -->
        <div class="p-4 rounded-2xl bg-rose-50/70 border border-rose-100 text-center">
          <div class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Absen / Alpa</div>
          <div class="text-2xl sm:text-3xl font-black text-rose-700 mt-1">{{ summary.absent }}</div>
        </div>
      </div>

      <!-- Attendance List Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div v-if="attendances && attendances.length > 0">
          <!-- Mobile View (Card List) -->
          <div class="md:hidden space-y-2">
            <AttendanceCard 
              v-for="att in attendances" 
              :key="att.id" 
              :attendance="att" 
              :show-complaint-btn="true"
            />
          </div>

          <!-- Desktop View (Table) -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold">
                  <th class="pb-3 px-2">Tanggal</th>
                  <th class="pb-3 px-2">Jam Masuk</th>
                  <th class="pb-3 px-2">Jam Keluar</th>
                  <th class="pb-3 px-2">Status</th>
                  <th class="pb-3 px-2">Keterangan</th>
                  <th class="pb-3 px-2 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="att in attendances" :key="att.id" class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-2 font-semibold text-slate-900">{{ formatDate(att.date) }}</td>
                  <td class="py-3 px-2 font-bold text-emerald-600">{{ formatTime(att.check_in_at) }}</td>
                  <td class="py-3 px-2 font-bold text-blue-600">{{ formatTime(att.check_out_at) }}</td>
                  <td class="py-3 px-2"><StatusBadge :status="att.status" /></td>
                  <td class="py-3 px-2 text-slate-500 max-w-xs truncate">{{ att.note || '-' }}</td>
                  <td class="py-3 px-2 text-right">
                    <Link 
                      :href="route('employee.complaints.create', { attendance_id: att.id })" 
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 rounded-lg transition-colors"
                    >
                      <i class="bi bi-exclamation-triangle"></i>
                      <span>Komplain</span>
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else class="text-center py-12 text-slate-400">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
            <i class="bi bi-calendar-x text-2xl"></i>
          </div>
          <p class="text-xs font-medium">Tidak ada rekaman presensi pada bulan yang dipilih.</p>
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>
