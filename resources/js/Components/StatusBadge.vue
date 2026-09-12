<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
});

const statusMap = {
  // Attendance statuses
  present: { label: 'Hadir', class: 'bg-emerald-50 text-emerald-700 border-emerald-200/80', dot: 'bg-emerald-500' },
  late: { label: 'Terlambat', class: 'bg-amber-50 text-amber-700 border-amber-200/80', dot: 'bg-amber-500' },
  absent: { label: 'Absen', class: 'bg-rose-50 text-rose-700 border-rose-200/80', dot: 'bg-rose-500' },
  sick: { label: 'Sakit', class: 'bg-sky-50 text-sky-700 border-sky-200/80', dot: 'bg-sky-500' },
  permission: { label: 'Izin', class: 'bg-purple-50 text-purple-700 border-purple-200/80', dot: 'bg-purple-500' },
  wfh: { label: 'WFH', class: 'bg-blue-50 text-blue-700 border-blue-200/80', dot: 'bg-blue-500' },

  // Leave & complaint statuses
  pending: { label: 'Menunggu', class: 'bg-amber-50 text-amber-700 border-amber-200/80', dot: 'bg-amber-500' },
  approved: { label: 'Disetujui', class: 'bg-emerald-50 text-emerald-700 border-emerald-200/80', dot: 'bg-emerald-500' },
  rejected: { label: 'Ditolak', class: 'bg-rose-50 text-rose-700 border-rose-200/80', dot: 'bg-rose-500' },
  in_review: { label: 'Ditinjau', class: 'bg-blue-50 text-blue-700 border-blue-200/80', dot: 'bg-blue-500' },
  resolved: { label: 'Selesai', class: 'bg-emerald-50 text-emerald-700 border-emerald-200/80', dot: 'bg-emerald-500' },

  // Leave types
  annual_leave: { label: 'Cuti Tahunan', class: 'bg-blue-50 text-blue-700 border-blue-200/80', dot: 'bg-blue-500' },
  emergency_leave: { label: 'Cuti Darurat', class: 'bg-rose-50 text-rose-700 border-rose-200/80', dot: 'bg-rose-500' },

  // Complaint types
  wrong_time: { label: 'Waktu Tidak Sesuai', class: 'bg-slate-100 text-slate-700 border-slate-200', dot: 'bg-slate-500' },
  location_error: { label: 'Gagal Lokasi GPS', class: 'bg-slate-100 text-slate-700 border-slate-200', dot: 'bg-slate-500' },
  forgot_checkout: { label: 'Lupa Check-out', class: 'bg-slate-100 text-slate-700 border-slate-200', dot: 'bg-slate-500' },
  system_error: { label: 'Error Sistem', class: 'bg-slate-100 text-slate-700 border-slate-200', dot: 'bg-slate-500' },
  other: { label: 'Lainnya', class: 'bg-slate-100 text-slate-700 border-slate-200', dot: 'bg-slate-500' },
};

const badgeInfo = computed(() => {
  return statusMap[props.status] || { label: props.status, class: 'bg-slate-100 text-slate-700 border-slate-200', dot: 'bg-slate-400' };
});
</script>

<template>
  <span
    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border transition-colors"
    :class="badgeInfo.class"
  >
    <span class="w-1.5 h-1.5 rounded-full" :class="badgeInfo.dot"></span>
    {{ badgeInfo.label }}
  </span>
</template>
