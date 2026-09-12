<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import LeaveRequestModal from '@/Components/LeaveRequestModal.vue';

const props = defineProps({
  leaveRequests: Array,
  departments: Array,
  filters: Object,
});

const status = ref(props.filters.status || 'all');
const type = ref(props.filters.type || 'all');
const department = ref(props.filters.department || 'all');

const applyFilter = () => {
  router.get(route('admin.leave-requests.index'), {
    status: status.value,
    type: type.value,
    department: department.value,
  }, { preserveState: true });
};

// Detail modal
const selectedRequest = ref(null);
const showDetailModal = ref(false);

const openDetail = (item) => {
  selectedRequest.value = item;
  showDetailModal.value = true;
};

// Approve
const approveForm = useForm({});
const approveRequest = (item) => {
  if (confirm(`Apakah Anda yakin ingin menyetujui pengajuan dari ${item.employee?.user?.name}? Data absensi otomatis disinkronkan.`)) {
    approveForm.post(route('admin.leave-requests.approve', item.id), {
      preserveScroll: true,
    });
  }
};

// Reject modal
const showRejectModal = ref(false);
const rejectingItem = ref(null);
const rejectForm = useForm({
  reject_reason: '',
});

const openReject = (item) => {
  rejectingItem.value = item;
  rejectForm.reject_reason = '';
  showRejectModal.value = true;
};

const submitReject = () => {
  if (!rejectingItem.value) return;
  rejectForm.post(route('admin.leave-requests.reject', rejectingItem.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showRejectModal.value = false;
      rejectingItem.value = null;
    },
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'short', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Pengajuan Cuti & Izin" />

    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4 mb-4">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
          <h4 class="fw-bold text-white mb-1">Manajemen Pengajuan Cuti / Izin / Sakit</h4>
          <p class="text-secondary small mb-0">Tinjau, setujui, atau tolak permohonan dispensasi kehadiran staf secara terpusat.</p>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="row g-2 pt-3 border-top border-secondary border-opacity-25">
        <div class="col-12 col-sm-4 col-md-3">
          <select v-model="status" class="form-select form-select-sm" @change="applyFilter">
            <option value="all">Semua Status</option>
            <option value="pending">Menunggu (Pending)</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
          </select>
        </div>

        <div class="col-12 col-sm-4 col-md-3">
          <select v-model="type" class="form-select form-select-sm" @change="applyFilter">
            <option value="all">Semua Jenis</option>
            <option value="annual_leave">Cuti Tahunan</option>
            <option value="sick">Sakit</option>
            <option value="permission">Izin Pribadi</option>
            <option value="emergency_leave">Cuti Darurat</option>
          </select>
        </div>

        <div class="col-12 col-sm-4 col-md-3">
          <select v-model="department" class="form-select form-select-sm" @change="applyFilter">
            <option value="all">Semua Departemen</option>
            <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Requests Table -->
    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4">
      <div v-if="leaveRequests && leaveRequests.length > 0" class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
          <thead>
            <tr class="text-secondary border-bottom border-secondary border-opacity-25">
              <th>Pegawai</th>
              <th>Jenis</th>
              <th>Periode</th>
              <th>Total Hari</th>
              <th>Alasan</th>
              <th>Status</th>
              <th class="text-end">Aksi Tindakan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in leaveRequests" :key="item.id" class="border-bottom border-secondary border-opacity-10">
              <td>
                <div class="fw-bold text-white">{{ item.employee?.user?.name }}</div>
                <small class="text-secondary">{{ item.employee?.department }}</small>
              </td>
              <td><StatusBadge :status="item.type" /></td>
              <td class="text-white">
                {{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}
              </td>
              <td class="text-primary fw-bold">{{ item.total_days }} Hari</td>
              <td class="text-secondary text-truncate" style="max-width: 200px;">
                {{ item.reason }}
              </td>
              <td><StatusBadge :status="item.status" /></td>
              <td class="text-end">
                <div class="d-inline-flex gap-2">
                  <button type="button" class="btn btn-sm btn-outline-info py-1 px-2" style="font-size: 0.75rem;" @click="openDetail(item)">
                    <i class="bi bi-eye"></i> Detail
                  </button>

                  <template v-if="item.status === 'pending'">
                    <button type="button" class="btn btn-sm btn-success py-1 px-2" style="font-size: 0.75rem;" @click="approveRequest(item)">
                      <i class="bi bi-check2"></i> Setujui
                    </button>
                    <button type="button" class="btn btn-sm btn-danger py-1 px-2" style="font-size: 0.75rem;" @click="openReject(item)">
                      <i class="bi bi-x"></i> Tolak
                    </button>
                  </template>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="text-center py-5 text-secondary">
        Tidak ada data pengajuan cuti atau izin yang sesuai.
      </div>
    </div>

    <!-- Reject Reason Modal -->
    <div v-if="showRejectModal && rejectingItem" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65);" @click.self="showRejectModal = false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border border-secondary border-opacity-25 shadow-lg">
          <div class="modal-header border-bottom border-secondary border-opacity-25">
            <h5 class="modal-title fs-6 fw-bold text-white d-flex align-items-center gap-2">
              <i class="bi bi-x-circle text-danger"></i>
              Tolak Pengajuan Cuti / Izin
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="showRejectModal = false"></button>
          </div>

          <form @submit.prevent="submitReject">
            <div class="modal-body">
              <div class="p-2 bg-dark rounded-2 mb-3 small text-white">
                Pegawai: <strong>{{ rejectingItem.employee?.user?.name }}</strong> ({{ rejectingItem.total_days }} hari)
              </div>

              <div class="mb-3">
                <label class="form-label small text-secondary fw-semibold">Alasan Penolakan (Wajib Diisi) *</label>
                <textarea 
                  v-model="rejectForm.reject_reason" 
                  class="form-control form-control-sm" 
                  rows="3" 
                  placeholder="Jelaskan alasan mengapa pengajuan ini tidak disetujui..." 
                  required
                ></textarea>
                <div v-if="rejectForm.errors.reject_reason" class="text-danger small mt-1">{{ rejectForm.errors.reject_reason }}</div>
              </div>
            </div>

            <div class="modal-footer border-top border-secondary border-opacity-25 py-2">
              <button type="button" class="btn btn-secondary btn-sm" @click="showRejectModal = false">Batal</button>
              <button type="submit" class="btn btn-danger btn-sm px-3" :disabled="rejectForm.processing">
                Konfirmasi Tolak
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <LeaveRequestModal 
      :show="showDetailModal" 
      :request="selectedRequest" 
      @close="showDetailModal = false" 
    />
  </AdminLayout>
</template>
