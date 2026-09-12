<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
  attendances: Array,
  departments: Array,
  filters: Object,
});

const filterDate = ref(props.filters.date || '');
const filterDept = ref(props.filters.department || 'all');
const search = ref(props.filters.search || '');

const applyFilter = () => {
  router.get(route('admin.attendance.index'), {
    date: filterDate.value,
    department: filterDept.value,
    search: search.value,
  }, { preserveState: true });
};

// Edit attendance modal
const showEditModal = ref(false);
const editingAttendance = ref(null);

const editForm = useForm({
  check_in_at: '',
  check_out_at: '',
  status: 'present',
  note: '',
});

const openEdit = (att) => {
  editingAttendance.value = att;
  editForm.check_in_at = att.check_in_at ? att.check_in_at.substring(0, 16) : '';
  editForm.check_out_at = att.check_out_at ? att.check_out_at.substring(0, 16) : '';
  editForm.status = att.status;
  editForm.note = att.note || '';
  showEditModal.value = true;
};

const submitEdit = () => {
  if (!editingAttendance.value) return;
  editForm.put(route('admin.attendance.update', editingAttendance.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false;
    },
  });
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
  <AdminLayout>
    <Head title="Monitoring Presensi Pegawai" />

    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4 mb-4">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
          <h4 class="fw-bold text-white mb-1">Monitoring & Koreksi Presensi</h4>
          <p class="text-secondary small mb-0">Pantau data kehadiran real-time seluruh pegawai dan lakukan koreksi jika diperlukan.</p>
        </div>
      </div>

      <!-- Filters Row -->
      <div class="row g-2 pt-3 border-top border-secondary border-opacity-25">
        <div class="col-12 col-md-4">
          <input 
            v-model="search" 
            type="text" 
            class="form-control form-control-sm" 
            placeholder="Cari nama pegawai..." 
            @keyup.enter="applyFilter"
          />
        </div>

        <div class="col-6 col-md-4">
          <select v-model="filterDept" class="form-select form-select-sm" @change="applyFilter">
            <option value="all">Semua Departemen</option>
            <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
          </select>
        </div>

        <div class="col-6 col-md-4 d-flex gap-2">
          <input 
            v-model="filterDate" 
            type="date" 
            class="form-control form-control-sm flex-fill" 
            @change="applyFilter"
          />
          <button type="button" class="btn btn-sm btn-primary px-3" @click="applyFilter">
            Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Attendance Table -->
    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4">
      <div v-if="attendances && attendances.length > 0" class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
          <thead>
            <tr class="text-secondary border-bottom border-secondary border-opacity-25">
              <th>Pegawai</th>
              <th>Tanggal</th>
              <th>Jam Masuk</th>
              <th>Jam Keluar</th>
              <th>Status</th>
              <th>Keterangan / Selfie</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="att in attendances" :key="att.id" class="border-bottom border-secondary border-opacity-10">
              <td>
                <div class="fw-bold text-white">{{ att.employee?.user?.name }}</div>
                <small class="text-secondary">{{ att.employee?.position }} ({{ att.employee?.department }})</small>
              </td>
              <td class="text-white">{{ formatDate(att.date) }}</td>
              <td class="text-success fw-bold">{{ formatTime(att.check_in_at) }}</td>
              <td class="text-info fw-bold">{{ formatTime(att.check_out_at) }}</td>
              <td><StatusBadge :status="att.status" /></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <a v-if="att.photo_path" :href="att.photo_path" target="_blank" title="Buka Selfie" class="text-info">
                    <i class="bi bi-image fs-6"></i>
                  </a>
                  <span class="text-secondary text-truncate" style="max-width: 220px;">
                    {{ att.note || '-' }}
                  </span>
                </div>
              </td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-warning py-1 px-2" style="font-size: 0.75rem;" @click="openEdit(att)">
                  <i class="bi bi-pencil-square"></i> Koreksi
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="text-center py-5 text-secondary">
        Tidak ditemukan data absensi untuk filter yang dipilih.
      </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div v-if="showEditModal && editingAttendance" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65);" @click.self="showEditModal = false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border border-secondary border-opacity-25 shadow-lg">
          <div class="modal-header border-bottom border-secondary border-opacity-25">
            <h5 class="modal-title fs-6 fw-bold text-white d-flex align-items-center gap-2">
              <i class="bi bi-pencil text-warning"></i>
              Koreksi Presensi: {{ editingAttendance.employee?.user?.name }}
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="showEditModal = false"></button>
          </div>

          <form @submit.prevent="submitEdit">
            <div class="modal-body">
              <div class="p-2 bg-dark rounded-2 mb-3 small text-secondary">
                Tanggal: <strong class="text-white">{{ formatDate(editingAttendance.date) }}</strong>
              </div>

              <div class="mb-3">
                <label class="form-label small text-secondary fw-semibold">Status Kehadiran *</label>
                <select v-model="editForm.status" class="form-select form-select-sm" required>
                  <option value="present">Hadir</option>
                  <option value="late">Terlambat</option>
                  <option value="wfh">WFH</option>
                  <option value="permission">Izin</option>
                  <option value="sick">Sakit</option>
                  <option value="absent">Absen / Alpa</option>
                </select>
              </div>

              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label small text-secondary fw-semibold">Waktu Masuk</label>
                  <input v-model="editForm.check_in_at" type="datetime-local" class="form-control form-control-sm" />
                </div>
                <div class="col-6">
                  <label class="form-label small text-secondary fw-semibold">Waktu Keluar</label>
                  <input v-model="editForm.check_out_at" type="datetime-local" class="form-control form-control-sm" />
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label small text-secondary fw-semibold">Keterangan / Catatan Koreksi</label>
                <textarea v-model="editForm.note" class="form-control form-control-sm" rows="3" placeholder="Alasan koreksi data..."></textarea>
              </div>
            </div>

            <div class="modal-footer border-top border-secondary border-opacity-25 py-2">
              <button type="button" class="btn btn-secondary btn-sm" @click="showEditModal = false">Batal</button>
              <button type="submit" class="btn btn-warning btn-sm px-3 text-dark fw-semibold" :disabled="editForm.processing">
                Simpan Koreksi
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
