<script setup>
import { Link } from '@inertiajs/vue3';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  attendance: {
    type: Object,
    required: true,
  },
  showComplaintBtn: {
    type: Boolean,
    default: true,
  },
});

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const formatTime = (timeStr) => {
  if (!timeStr) return '--:--';
  const date = new Date(timeStr);
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
};
</script>

<template>
  <div class="card bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded-3 mb-3 p-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="fw-semibold text-white small">
        <i class="bi bi-calendar-event me-1 text-primary"></i>
        {{ formatDate(attendance.date) }}
      </div>
      <StatusBadge :status="attendance.status" />
    </div>

    <div class="row g-2 my-1 text-center py-2 bg-dark bg-opacity-50 rounded-2 border border-secondary border-opacity-10">
      <div class="col-6 border-end border-secondary border-opacity-25">
        <div class="text-secondary" style="font-size: 0.7rem;">MASUK</div>
        <div class="fw-bold text-success fs-6">{{ formatTime(attendance.check_in_at) }}</div>
      </div>
      <div class="col-6">
        <div class="text-secondary" style="font-size: 0.7rem;">KELUAR</div>
        <div class="fw-bold text-info fs-6">{{ formatTime(attendance.check_out_at) }}</div>
      </div>
    </div>

    <div v-if="attendance.note" class="small text-secondary mt-2 px-1 text-truncate">
      <i class="bi bi-info-circle me-1"></i> {{ attendance.note }}
    </div>

    <div v-if="showComplaintBtn" class="mt-2 pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-end">
      <Link 
        :href="route('employee.complaints.create', { attendance_id: attendance.id })" 
        class="btn btn-sm btn-outline-warning py-1 px-2"
        style="font-size: 0.75rem;"
      >
        <i class="bi bi-exclamation-triangle me-1"></i> Ajukan Komplain
      </Link>
    </div>
  </div>
</template>
