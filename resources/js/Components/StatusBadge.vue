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
  present: { label: 'Hadir', class: 'badge-present' },
  late: { label: 'Terlambat', class: 'badge-late' },
  absent: { label: 'Absen', class: 'badge-absent' },
  sick: { label: 'Sakit', class: 'badge-sick' },
  permission: { label: 'Izin', class: 'badge-permission' },
  wfh: { label: 'WFH', class: 'badge-wfh' },

  // Leave & complaint statuses
  pending: { label: 'Menunggu', class: 'badge-pending' },
  approved: { label: 'Disetujui', class: 'badge-approved' },
  rejected: { label: 'Ditolak', class: 'badge-rejected' },
  in_review: { label: 'Sedang Ditinjau', class: 'badge-in_review' },
  resolved: { label: 'Selesai', class: 'badge-resolved' },

  // Leave types
  annual_leave: { label: 'Cuti Tahunan', class: 'bg-primary' },
  emergency_leave: { label: 'Cuti Darurat', class: 'bg-danger' },

  // Complaint types
  wrong_time: { label: 'Waktu Tidak Sesuai', class: 'bg-secondary' },
  location_error: { label: 'Gagal Lokasi', class: 'bg-secondary' },
  forgot_checkout: { label: 'Lupa Check-out', class: 'bg-secondary' },
  system_error: { label: 'Error Sistem', class: 'bg-secondary' },
  other: { label: 'Lainnya', class: 'bg-secondary' },
};

const badgeInfo = computed(() => {
  return statusMap[props.status] || { label: props.status, class: 'bg-secondary' };
});
</script>

<template>
  <span class="badge" :class="badgeInfo.class">
    {{ badgeInfo.label }}
  </span>
</template>
