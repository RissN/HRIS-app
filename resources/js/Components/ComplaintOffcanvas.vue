<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  complaint: {
    type: Object,
    default: null,
  },
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const activeTab = ref('resolve'); // 'resolve', 'reject', 'review'
const correctAttendance = ref(false);

const resolveForm = useForm({
  admin_note: '',
  correct_attendance: false,
  check_in_at: '',
  check_out_at: '',
  status: 'present',
});

const rejectForm = useForm({
  admin_note: '',
});

watch(() => props.complaint, (val) => {
  if (val) {
    resolveForm.admin_note = val.admin_note || '';
    if (val.attendance) {
      resolveForm.check_in_at = val.attendance.check_in_at ? val.attendance.check_in_at.substring(0, 16) : '';
      resolveForm.check_out_at = val.attendance.check_out_at ? val.attendance.check_out_at.substring(0, 16) : '';
      resolveForm.status = val.attendance.status || 'present';
      correctAttendance.value = true;
    } else {
      correctAttendance.value = false;
    }
    activeTab.value = val.status === 'pending' ? 'review' : 'resolve';
  }
});

const setInReview = () => {
  if (!props.complaint) return;
  const form = useForm({ status: 'in_review' });
  form.post(route('admin.complaints.status', props.complaint.id), {
    preserveScroll: true,
    onSuccess: () => {
      activeTab.value = 'resolve';
    },
  });
};

const handleResolve = () => {
  if (!props.complaint) return;
  resolveForm.clearErrors();

  if (!resolveForm.admin_note || !resolveForm.admin_note.trim()) {
    resolveForm.setError('admin_note', 'Solusi atau catatan penanganan komplain wajib diisi');
    return;
  }

  resolveForm.correct_attendance = correctAttendance.value;
  resolveForm.post(route('admin.complaints.resolve', props.complaint.id), {
    preserveScroll: true,
    onSuccess: () => {
      emit('close');
    },
  });
};

const handleReject = () => {
  if (!props.complaint) return;
  rejectForm.clearErrors();

  if (!rejectForm.admin_note || !rejectForm.admin_note.trim()) {
    rejectForm.setError('admin_note', 'Alasan penolakan komplain wajib diisi');
    return;
  }

  rejectForm.post(route('admin.complaints.reject', props.complaint.id), {
    preserveScroll: true,
    onSuccess: () => {
      emit('close');
    },
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const formatDateTime = (dtStr) => {
  if (!dtStr) return '-';
  const options = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dtStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <!-- Centered Modal Backdrop -->
  <div 
    v-if="show && complaint" 
    class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
    @click.self="emit('close')"
  >
    <!-- Centered Modal Card Window -->
    <div 
      class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] my-auto overflow-hidden animate-in fade-in zoom-in-95"
    >
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs">
            <i class="bi bi-chat-left-dots text-lg"></i>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900 leading-tight">Tinjauan Komplain Presensi</h3>
            <p class="text-xs text-slate-400 mt-0.5">
              Keluhan dari <strong class="text-slate-700">{{ complaint.employee?.user?.name }}</strong> &bull; {{ formatDate(complaint.date) }}
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

      <!-- Modal Body (Two-Column Responsive Grid) -->
      <div class="flex-1 overflow-y-auto p-5 sm:p-6 text-slate-700">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
          <!-- Left Column: Complaint & Employee Details (6 cols) -->
          <div class="lg:col-span-6 space-y-4">
            <!-- Status & Category Summary -->
            <div class="grid grid-cols-2 gap-3">
              <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Status Komplain</span>
                <StatusBadge :status="complaint.status" />
              </div>
              <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Jenis Masalah</span>
                <StatusBadge :status="complaint.type" />
              </div>
            </div>

            <!-- Employee Info Card -->
            <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-100 flex items-center gap-3">
              <img 
                :src="complaint.employee?.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(complaint.employee?.user?.name || 'User') + '&background=2563eb&color=fff'" 
                class="w-11 h-11 rounded-xl object-cover ring-2 ring-white shadow-xs" 
                alt="Avatar" 
              />
              <div class="overflow-hidden">
                <div class="font-bold text-slate-900 text-sm truncate">{{ complaint.employee?.user?.name }}</div>
                <div class="text-xs text-slate-500 truncate mt-0.5">
                  {{ complaint.employee?.position }} &bull; {{ complaint.employee?.department }}
                </div>
                <div v-if="complaint.employee?.phone" class="text-[11px] text-slate-400 mt-0.5">
                  <i class="bi bi-telephone text-[10px] mr-1"></i>{{ complaint.employee?.phone }}
                </div>
              </div>
            </div>

            <!-- Complaint Description -->
            <div>
              <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">
                Rincian Keluhan Pegawai
              </label>
              <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-slate-800 text-xs leading-relaxed whitespace-pre-wrap">
                {{ complaint.description }}
              </div>
            </div>

            <!-- Attachment Document -->
            <div v-if="complaint.attachment">
              <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">
                Bukti Pendukung
              </label>
              <a 
                :href="complaint.attachment" 
                target="_blank" 
                class="flex items-center justify-between p-3 rounded-2xl bg-blue-50/70 hover:bg-blue-100/70 border border-blue-200/80 text-blue-700 text-xs font-semibold transition-colors"
              >
                <div class="flex items-center gap-2.5 truncate">
                  <i class="bi bi-paperclip text-base"></i>
                  <span class="truncate">Berkas Lampiran Foto / Screenshot</span>
                </div>
                <i class="bi bi-box-arrow-up-right text-xs shrink-0"></i>
              </a>
            </div>

            <!-- Previous Admin Note (if any) -->
            <div v-if="complaint.admin_note" class="p-3.5 bg-blue-50/60 border border-blue-100 rounded-2xl">
              <label class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block mb-1">
                Catatan Respon HR Sebelumnya
              </label>
              <div class="text-xs text-blue-950 leading-relaxed">{{ complaint.admin_note }}</div>
              <div v-if="complaint.resolver" class="text-[10px] text-blue-600/80 mt-1.5 font-medium">
                Ditinjau oleh: {{ complaint.resolver.name }} ({{ formatDateTime(complaint.resolved_at) }})
              </div>
            </div>
          </div>

          <!-- Right Column: Admin Actions & Attendance Correction (6 cols) -->
          <div class="lg:col-span-6 flex flex-col space-y-4">
            <!-- Action Form Header & Tab Switcher -->
            <div class="p-1 bg-slate-100 rounded-2xl flex text-xs font-semibold">
              <button 
                type="button" 
                class="flex-1 py-2 px-2.5 rounded-xl text-center transition-all cursor-pointer"
                :class="activeTab === 'resolve' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                @click="activeTab = 'resolve'"
              >
                <i class="bi bi-check-circle mr-1"></i> Selesaikan
              </button>
              <button 
                type="button" 
                class="flex-1 py-2 px-2.5 rounded-xl text-center transition-all cursor-pointer"
                :class="activeTab === 'reject' ? 'bg-white text-rose-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                @click="activeTab = 'reject'"
              >
                <i class="bi bi-x-circle mr-1"></i> Tolak
              </button>
              <button 
                v-if="complaint.status === 'pending'"
                type="button" 
                class="flex-1 py-2 px-2.5 rounded-xl text-center transition-all cursor-pointer"
                :class="activeTab === 'review' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                @click="activeTab = 'review'"
              >
                <i class="bi bi-eye mr-1"></i> Tinjau
              </button>
            </div>

            <!-- Tab 1: Resolve Form -->
            <form 
              novalidate
              v-if="activeTab === 'resolve'" 
              @submit.prevent="handleResolve" 
              class="p-4 sm:p-5 bg-emerald-50/40 rounded-3xl border border-emerald-200/80 space-y-3.5 flex-1 flex flex-col justify-between"
            >
              <div class="space-y-3">
                <div class="flex items-center gap-2 text-emerald-800 font-bold text-xs">
                  <i class="bi bi-check2-circle text-base"></i>
                  <span>Selesaikan & Berikan Solusi</span>
                </div>

                <div>
                  <label class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-1.5">
                    Catatan Respons Admin *
                  </label>
                  <textarea 
                    v-model="resolveForm.admin_note" 
                    @input="resolveForm.clearErrors('admin_note')"
                    :class="resolveForm.errors.admin_note 
                      ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                      : 'border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500'"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border leading-relaxed transition-all" 
                    rows="3" 
                    placeholder="Jelaskan penanganan atau solusi untuk komplain ini..." 
                  ></textarea>
                  <div v-if="resolveForm.errors.admin_note" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                    <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                    <span>{{ resolveForm.errors.admin_note }}</span>
                  </div>
                </div>

                <!-- Toggle Correction -->
                <label class="flex items-center gap-2.5 p-3 rounded-2xl bg-white border border-emerald-100 cursor-pointer transition-colors hover:bg-emerald-50/30">
                  <input 
                    v-model="correctAttendance" 
                    type="checkbox" 
                    class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300"
                  />
                  <div class="text-xs font-semibold text-slate-800">
                    Koreksi Data Presensi Otomatis
                  </div>
                </label>

                <!-- Inputs if Correction Checked -->
                <div v-if="correctAttendance" class="p-3.5 bg-white rounded-2xl border border-emerald-100 space-y-3 animate-in fade-in">
                  <div>
                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Status Absensi Baru</label>
                    <select 
                      v-model="resolveForm.status" 
                      class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none font-medium"
                    >
                      <option value="present">Hadir (Tepat Waktu)</option>
                      <option value="late">Terlambat</option>
                      <option value="wfh">WFH</option>
                      <option value="permission">Izin</option>
                      <option value="sick">Sakit</option>
                    </select>
                  </div>

                  <div class="grid grid-cols-2 gap-2.5">
                    <div>
                      <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Check-in</label>
                      <input 
                        v-model="resolveForm.check_in_at" 
                        type="datetime-local" 
                        class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none" 
                      />
                    </div>
                    <div>
                      <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Check-out</label>
                      <input 
                        v-model="resolveForm.check_out_at" 
                        type="datetime-local" 
                        class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none" 
                      />
                    </div>
                  </div>
                </div>
              </div>

              <button 
                type="submit" 
                class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-2 mt-3" 
                :disabled="resolveForm.processing"
              >
                <i class="bi bi-check-lg"></i>
                <span>Simpan & Selesaikan Komplain</span>
              </button>
            </form>

            <!-- Tab 2: Reject Form -->
            <form 
              novalidate
              v-if="activeTab === 'reject'" 
              @submit.prevent="handleReject" 
              class="p-4 sm:p-5 bg-rose-50/40 rounded-3xl border border-rose-200/80 space-y-3.5 flex-1 flex flex-col justify-between"
            >
              <div class="space-y-3">
                <div class="flex items-center gap-2 text-rose-800 font-bold text-xs">
                  <i class="bi bi-x-circle text-base"></i>
                  <span>Tolak Komplain Pegawai</span>
                </div>

                <div>
                  <label class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-1.5">
                    Alasan Penolakan (Wajib Diisi) *
                  </label>
                  <textarea 
                    v-model="rejectForm.admin_note" 
                    @input="rejectForm.clearErrors('admin_note')"
                    :class="rejectForm.errors.admin_note 
                      ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                      : 'border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500'"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border leading-relaxed transition-all" 
                    rows="4" 
                    placeholder="Jelaskan alasan mengapa komplain ini ditolak..." 
                  ></textarea>
                  <div v-if="rejectForm.errors.admin_note" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                    <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                    <span>{{ rejectForm.errors.admin_note }}</span>
                  </div>
                </div>

                <div class="p-3 bg-white rounded-2xl border border-rose-100 text-xs text-rose-700">
                  <i class="bi bi-info-circle mr-1"></i> Data presensi pegawai tidak akan diubah jika komplain ditolak.
                </div>
              </div>

              <button 
                type="submit" 
                class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-2 mt-3" 
                :disabled="rejectForm.processing"
              >
                <i class="bi bi-x-lg"></i>
                <span>Konfirmasi Tolak Komplain</span>
              </button>
            </form>

            <!-- Tab 3: Mark in Review -->
            <div 
              v-if="activeTab === 'review'" 
              class="p-4 sm:p-5 bg-blue-50/40 rounded-3xl border border-blue-200/80 space-y-3 flex-1 flex flex-col justify-between"
            >
              <div class="space-y-3">
                <div class="flex items-center gap-2 text-blue-800 font-bold text-xs">
                  <i class="bi bi-clock-history text-base"></i>
                  <span>Tandai Sedang Ditinjau</span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                  Gunakan status ini jika Anda sedang melakukan verifikasi data (misalnya memeriksa log mesin atau CCTV) sebelum memutuskan penyelesaian komplain.
                </p>
                <div class="p-3 bg-white rounded-2xl border border-blue-100 text-xs text-blue-700">
                  Status komplain akan berubah dari <strong>Menunggu</strong> menjadi <strong>Sedang Ditinjau</strong> pada dashboard staf.
                </div>
              </div>

              <button 
                type="button" 
                class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-2 mt-3"
                @click="setInReview"
              >
                <i class="bi bi-eye"></i>
                <span>Tandai Sedang Ditinjau</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between shrink-0">
        <span class="text-xs text-slate-400">
          Tindakan langsung tersimpan ke sistem presensi
        </span>
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
