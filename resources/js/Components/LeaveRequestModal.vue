<script setup>
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  request: {
    type: Object,
    default: null,
  },
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { weekday: 'short', day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const formatDateTime = (dtStr) => {
  if (!dtStr) return '-';
  const options = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dtStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <div 
    v-if="show && request" 
    class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
    @click.self="emit('close')"
  >
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-2xl w-full my-auto overflow-hidden animate-in fade-in zoom-in-95 flex flex-col max-h-[90vh]">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-xs">
            <i class="bi bi-file-earmark-medical text-lg"></i>
          </div>
          <div>
            <h4 class="text-base font-bold text-slate-900 leading-tight">Detail Permohonan Cuti & Izin</h4>
            <p class="text-xs text-slate-400 mt-0.5">
              Pengajuan #{{ request.id }} &bull; {{ request.total_days }} Hari Kerja
            </p>
          </div>
        </div>
        <button 
          type="button" 
          class="w-9 h-9 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer"
          @click="emit('close')"
        >
          <i class="bi bi-x-lg text-sm"></i>
        </button>
      </div>

      <!-- Body -->
      <div class="flex-1 overflow-y-auto p-5 sm:p-6 text-slate-700 space-y-4">
        <!-- Top Row: Status Banner & Employee Card -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- Employee Card -->
          <div v-if="request.employee?.user" class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-3">
            <img 
              :src="request.employee.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(request.employee.user.name) + '&background=2563eb&color=fff'" 
              class="w-11 h-11 rounded-xl object-cover ring-2 ring-white shadow-xs" 
              alt="Avatar" 
            />
            <div class="overflow-hidden">
              <div class="font-bold text-slate-900 text-sm truncate">{{ request.employee.user.name }}</div>
              <div class="text-xs text-slate-500 truncate mt-0.5">{{ request.employee.position }} &bull; {{ request.employee.department }}</div>
              <div v-if="request.employee.phone" class="text-[11px] text-slate-400 mt-0.5">
                <i class="bi bi-telephone text-[10px] mr-1"></i>{{ request.employee.phone }}
              </div>
            </div>
          </div>

          <!-- Status & Type Card -->
          <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Permohonan</span>
              <StatusBadge :status="request.status" />
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 mt-2">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kategori</span>
              <StatusBadge :status="request.type" />
            </div>
          </div>
        </div>

        <!-- Dates & Duration Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tanggal Mulai</span>
            <div class="font-semibold text-slate-800 text-xs flex items-center gap-1.5">
              <i class="bi bi-calendar-event text-blue-600"></i>
              <span>{{ formatDate(request.start_date) }}</span>
            </div>
          </div>

          <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tanggal Selesai</span>
            <div class="font-semibold text-slate-800 text-xs flex items-center gap-1.5">
              <i class="bi bi-calendar-check text-blue-600"></i>
              <span>{{ formatDate(request.end_date) }}</span>
            </div>
          </div>

          <div class="p-3 bg-blue-50/60 rounded-2xl border border-blue-100 flex flex-col justify-center">
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block mb-0.5">Durasi Cuti</span>
            <div class="font-extrabold text-blue-700 text-base">
              {{ request.total_days }} Hari Kerja
            </div>
          </div>
        </div>

        <!-- Reason -->
        <div>
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">
            Alasan / Keterangan Pengajuan
          </label>
          <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-slate-800 text-xs leading-relaxed whitespace-pre-wrap">
            {{ request.reason }}
          </div>
        </div>

        <!-- Attachment -->
        <div v-if="request.attachment">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">
            Dokumen Lampiran
          </label>
          <a 
            :href="request.attachment" 
            target="_blank" 
            class="flex items-center justify-between p-3 rounded-2xl bg-blue-50/70 hover:bg-blue-100/70 border border-blue-200/80 text-blue-700 text-xs font-semibold transition-colors"
          >
            <div class="flex items-center gap-2.5 truncate">
              <i class="bi bi-paperclip text-base"></i>
              <span class="truncate">Surat Dokter / Bukti Pendukung</span>
            </div>
            <i class="bi bi-box-arrow-up-right text-xs shrink-0"></i>
          </a>
        </div>

        <!-- Review Info (if reviewed) -->
        <div v-if="request.reviewed_at" class="p-3.5 rounded-2xl border text-xs" :class="request.status === 'approved' ? 'bg-emerald-50/60 border-emerald-100 text-emerald-900' : 'bg-rose-50/60 border-rose-100 text-rose-900'">
          <div class="font-bold flex items-center gap-1.5 mb-1">
            <i :class="request.status === 'approved' ? 'bi bi-check-circle-fill text-emerald-600' : 'bi bi-x-circle-fill text-rose-600'"></i>
            <span>{{ request.status === 'approved' ? 'Telah Disetujui' : 'Permohonan Ditolak' }}</span>
          </div>
          <div class="text-[11px] opacity-80">
            Oleh: <strong>{{ request.reviewer?.name || 'HR Manager' }}</strong> pada {{ formatDateTime(request.reviewed_at) }}
          </div>
          <div v-if="request.status === 'rejected' && request.reject_reason" class="mt-2 p-2.5 bg-white/80 rounded-xl border border-rose-200 text-rose-800">
            <strong>Alasan Penolakan:</strong> {{ request.reject_reason }}
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex justify-end shrink-0">
        <button 
          type="button" 
          class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-200 text-xs font-semibold transition-colors cursor-pointer" 
          @click="emit('close')"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>
