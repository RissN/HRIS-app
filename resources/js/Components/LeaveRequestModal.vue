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
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const formatDateTime = (dtStr) => {
  if (!dtStr) return '-';
  const options = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dtStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <div v-if="show && request" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65);" @click.self="emit('close')">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border border-secondary border-opacity-25 shadow-lg">
        <div class="modal-header border-bottom border-secondary border-opacity-25">
          <h5 class="modal-title fs-6 fw-bold text-white d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-medical text-primary"></i>
            Detail Pengajuan Cuti / Izin
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="emit('close')"></button>
        </div>

        <div class="modal-body">
          <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-dark bg-opacity-50 rounded-2">
            <span class="text-secondary small">Status Pengajuan</span>
            <StatusBadge :status="request.status" />
          </div>

          <div v-if="request.employee?.user" class="mb-3">
            <label class="text-secondary small d-block">Nama Pegawai</label>
            <div class="fw-semibold text-white">{{ request.employee.user.name }} ({{ request.employee.position }})</div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="text-secondary small d-block">Jenis Pengajuan</label>
              <div class="fw-semibold text-white">
                <StatusBadge :status="request.type" />
              </div>
            </div>
            <div class="col-6">
              <label class="text-secondary small d-block">Total Hari Kerja</label>
              <div class="fw-semibold text-primary fs-6">{{ request.total_days }} Hari</div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="text-secondary small d-block">Tanggal Mulai</label>
              <div class="text-white">{{ formatDate(request.start_date) }}</div>
            </div>
            <div class="col-6">
              <label class="text-secondary small d-block">Tanggal Selesai</label>
              <div class="text-white">{{ formatDate(request.end_date) }}</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="text-secondary small d-block">Alasan Pengajuan</label>
            <div class="p-2 bg-dark bg-opacity-50 rounded-2 text-white small" style="white-space: pre-wrap;">
              {{ request.reason }}
            </div>
          </div>

          <div v-if="request.attachment" class="mb-3">
            <label class="text-secondary small d-block mb-1">Lampiran Dokumen</label>
            <a 
              :href="request.attachment" 
              target="_blank" 
              class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-2"
            >
              <i class="bi bi-paperclip"></i>
              Buka File Lampiran
            </a>
          </div>

          <!-- Review Details -->
          <div v-if="request.reviewed_at" class="mt-3 pt-3 border-top border-secondary border-opacity-25">
            <div class="small text-secondary mb-1">
              Ditinjau oleh: <span class="text-white">{{ request.reviewer?.name || 'HR Admin' }}</span> 
              pada {{ formatDateTime(request.reviewed_at) }}
            </div>
            <div v-if="request.status === 'rejected' && request.reject_reason" class="alert alert-danger py-2 px-3 small mt-2">
              <strong>Alasan Penolakan:</strong> {{ request.reject_reason }}
            </div>
          </div>
        </div>

        <div class="modal-footer border-top border-secondary border-opacity-25 py-2">
          <button type="button" class="btn btn-secondary btn-sm" @click="emit('close')">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</template>
