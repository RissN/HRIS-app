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

const isResolving = ref(false);
const isRejecting = ref(false);
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
    }
  }
});

const setInReview = () => {
  if (!props.complaint) return;
  const form = useForm({ status: 'in_review' });
  form.post(route('admin.complaints.status', props.complaint.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Updated
    },
  });
};

const handleResolve = () => {
  if (!props.complaint) return;
  resolveForm.correct_attendance = correctAttendance.value;
  resolveForm.post(route('admin.complaints.resolve', props.complaint.id), {
    preserveScroll: true,
    onSuccess: () => {
      isResolving.value = false;
      emit('close');
    },
  });
};

const handleReject = () => {
  if (!props.complaint) return;
  rejectForm.post(route('admin.complaints.reject', props.complaint.id), {
    preserveScroll: true,
    onSuccess: () => {
      isRejecting.value = false;
      emit('close');
    },
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <div v-if="show" class="offcanvas-backdrop fade show" @click="emit('close')"></div>
  <div 
    class="offcanvas offcanvas-end text-bg-dark border-start border-secondary border-opacity-25" 
    :class="{ show: show }" 
    tabindex="-1" 
    style="visibility: visible; width: 420px; max-width: 95vw;"
  >
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
      <h5 class="offcanvas-title fs-6 fw-bold text-white d-flex align-items-center gap-2">
        <i class="bi bi-chat-left-dots text-primary"></i>
        Tinjauan Komplain Absensi
      </h5>
      <button type="button" class="btn-close btn-close-white" @click="emit('close')"></button>
    </div>

    <div v-if="complaint" class="offcanvas-body">
      <!-- Status Bar -->
      <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-secondary bg-opacity-10 rounded-2">
        <span class="text-secondary small">Status Komplain</span>
        <StatusBadge :status="complaint.status" />
      </div>

      <!-- Employee Info -->
      <div class="mb-3">
        <label class="text-secondary small d-block">Pegawai</label>
        <div class="fw-bold text-white">{{ complaint.employee?.user?.name }}</div>
        <small class="text-secondary">{{ complaint.employee?.position }} - {{ complaint.employee?.department }}</small>
      </div>

      <div class="row g-2 mb-3">
        <div class="col-6">
          <label class="text-secondary small d-block">Tanggal Kejadian</label>
          <div class="text-white small fw-medium">{{ formatDate(complaint.date) }}</div>
        </div>
        <div class="col-6">
          <label class="text-secondary small d-block">Kategori Komplain</label>
          <StatusBadge :status="complaint.type" />
        </div>
      </div>

      <!-- Description -->
      <div class="mb-3">
        <label class="text-secondary small d-block">Deskripsi Keluhan</label>
        <div class="p-3 bg-dark bg-opacity-50 rounded-2 text-white small border border-secondary border-opacity-10" style="white-space: pre-wrap;">
          {{ complaint.description }}
        </div>
      </div>

      <!-- Attachment -->
      <div v-if="complaint.attachment" class="mb-3">
        <label class="text-secondary small d-block mb-1">Bukti Lampiran</label>
        <a :href="complaint.attachment" target="_blank" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-2">
          <i class="bi bi-paperclip"></i>
          Buka Bukti / Screenshot
        </a>
      </div>

      <!-- Current Admin Note if any -->
      <div v-if="complaint.admin_note" class="mb-3">
        <label class="text-secondary small d-block">Catatan Respons Admin</label>
        <div class="p-2 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-2 text-white small">
          {{ complaint.admin_note }}
        </div>
        <small v-if="complaint.resolver" class="text-secondary d-block mt-1">
          Oleh: {{ complaint.resolver.name }} ({{ complaint.status }})
        </small>
      </div>

      <!-- Admin Actions Section -->
      <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
        <h6 class="text-white small fw-bold mb-3">Tindakan Admin</h6>

        <!-- Step 1: Set to in_review if still pending -->
        <div v-if="complaint.status === 'pending'" class="mb-3">
          <button type="button" class="btn btn-outline-primary btn-sm w-100" @click="setInReview">
            <i class="bi bi-eye me-1"></i> Tandai Sedang Ditinjau
          </button>
        </div>

        <!-- Resolve or Reject Options -->
        <div v-if="!isResolving && !isRejecting" class="d-flex gap-2">
          <button type="button" class="btn btn-success btn-sm flex-fill" @click="isResolving = true; isRejecting = false">
            <i class="bi bi-check-circle me-1"></i> Selesaikan
          </button>
          <button type="button" class="btn btn-danger btn-sm flex-fill" @click="isRejecting = true; isResolving = false">
            <i class="bi bi-x-circle me-1"></i> Tolak
          </button>
        </div>

        <!-- Resolve Form -->
        <form v-if="isResolving" @submit.prevent="handleResolve" class="p-3 bg-secondary bg-opacity-10 rounded-2 border border-success border-opacity-25">
          <h6 class="text-success small fw-bold mb-2">Penyelesaian Komplain</h6>

          <div class="mb-2">
            <label class="text-secondary small">Catatan Respons Admin *</label>
            <textarea 
              v-model="resolveForm.admin_note" 
              class="form-control form-control-sm" 
              rows="3" 
              placeholder="Jelaskan tindak lanjut komplain..." 
              required
            ></textarea>
            <div v-if="resolveForm.errors.admin_note" class="text-danger small">{{ resolveForm.errors.admin_note }}</div>
          </div>

          <!-- Toggle attendance correction -->
          <div class="form-check form-switch mb-3">
            <input 
              v-model="correctAttendance" 
              class="form-check-input" 
              type="checkbox" 
              id="correctAttendanceSwitch"
            />
            <label class="form-check-label text-white small" for="correctAttendanceSwitch">
              Koreksi Data Absensi Terkait
            </label>
          </div>

          <div v-if="correctAttendance" class="p-2 bg-dark rounded-2 mb-3 border border-secondary border-opacity-25">
            <div class="mb-2">
              <label class="text-secondary small">Jam Masuk (Check-in)</label>
              <input v-model="resolveForm.check_in_at" type="datetime-local" class="form-control form-control-sm" />
            </div>
            <div class="mb-2">
              <label class="text-secondary small">Jam Keluar (Check-out)</label>
              <input v-model="resolveForm.check_out_at" type="datetime-local" class="form-control form-control-sm" />
            </div>
            <div class="mb-2">
              <label class="text-secondary small">Status Absensi Baru</label>
              <select v-model="resolveForm.status" class="form-select form-select-sm">
                <option value="present">Hadir</option>
                <option value="late">Terlambat</option>
                <option value="wfh">WFH</option>
                <option value="permission">Izin</option>
                <option value="sick">Sakit</option>
              </select>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success btn-sm flex-fill" :disabled="resolveForm.processing">
              Simpan & Selesaikan
            </button>
            <button type="button" class="btn btn-secondary btn-sm" @click="isResolving = false">
              Batal
            </button>
          </div>
        </form>

        <!-- Reject Form -->
        <form v-if="isRejecting" @submit.prevent="handleReject" class="p-3 bg-secondary bg-opacity-10 rounded-2 border border-danger border-opacity-25">
          <h6 class="text-danger small fw-bold mb-2">Tolak Komplain</h6>

          <div class="mb-3">
            <label class="text-secondary small">Alasan Penolakan *</label>
            <textarea 
              v-model="rejectForm.admin_note" 
              class="form-control form-control-sm" 
              rows="3" 
              placeholder="Berikan penjelasan penolakan..." 
              required
            ></textarea>
            <div v-if="rejectForm.errors.admin_note" class="text-danger small">{{ rejectForm.errors.admin_note }}</div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-danger btn-sm flex-fill" :disabled="rejectForm.processing">
              Tolak Komplain
            </button>
            <button type="button" class="btn btn-secondary btn-sm" @click="isRejecting = false">
              Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
