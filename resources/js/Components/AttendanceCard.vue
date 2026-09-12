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
  <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-xs hover:shadow-md transition-all duration-200 mb-3">
    <div class="flex items-center justify-between mb-3">
      <div class="font-semibold text-slate-900 text-sm flex items-center gap-2">
        <div class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center">
          <i class="bi bi-calendar-event text-xs"></i>
        </div>
        {{ formatDate(attendance.date) }}
      </div>
      <StatusBadge :status="attendance.status" />
    </div>

    <!-- Time Grid -->
    <div class="grid grid-cols-2 gap-2 bg-slate-50/80 rounded-xl p-3 border border-slate-100 my-2 text-center">
      <div class="border-r border-slate-200/80 pr-2">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Masuk</div>
        <div class="font-bold text-emerald-600 text-base mt-0.5">{{ formatTime(attendance.check_in_at) }}</div>
      </div>
      <div class="pl-2">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Keluar</div>
        <div class="font-bold text-blue-600 text-base mt-0.5">{{ formatTime(attendance.check_out_at) }}</div>
      </div>
    </div>

    <div v-if="attendance.note" class="text-xs text-slate-500 mt-2 flex items-center gap-1.5 truncate px-1">
      <i class="bi bi-info-circle text-slate-400"></i>
      <span class="truncate">{{ attendance.note }}</span>
    </div>

    <div v-if="showComplaintBtn" class="mt-3 pt-2.5 border-t border-slate-100 flex justify-end">
      <Link 
        :href="route('employee.complaints.create', { attendance_id: attendance.id })" 
        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 rounded-lg transition-colors"
      >
        <i class="bi bi-exclamation-triangle text-amber-600"></i>
        <span>Ajukan Komplain</span>
      </Link>
    </div>
  </div>
</template>
