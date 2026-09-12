<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ComplaintOffcanvas from '@/Components/ComplaintOffcanvas.vue';

const props = defineProps({
  complaints: Array,
  departments: Array,
  filters: Object,
});

const status = ref(props.filters.status || 'all');
const type = ref(props.filters.type || 'all');
const department = ref(props.filters.department || 'all');
const date = ref(props.filters.date || '');

const applyFilter = () => {
  router.get(route('admin.complaints.index'), {
    status: status.value,
    type: type.value,
    department: department.value,
    date: date.value,
  }, { preserveState: true });
};

// Offcanvas drawer
const selectedComplaint = ref(null);
const showOffcanvas = ref(false);

const openOffcanvas = (c) => {
  selectedComplaint.value = c;
  showOffcanvas.value = true;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'short', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Komplain Absensi" />

    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4 mb-4">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
          <h4 class="fw-bold text-white mb-1">Manajemen Komplain Presensi</h4>
          <p class="text-secondary small mb-0">Tinjau keluhan staf, verifikasi kendala absensi, dan koreksi data kehadiran langsung.</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="row g-2 pt-3 border-top border-secondary border-opacity-25">
        <div class="col-6 col-md-3">
          <select v-model="status" class="form-select form-select-sm" @change="applyFilter">
            <option value="all">Semua Status</option>
            <option value="pending">Menunggu (Pending)</option>
            <option value="in_review">Sedang Ditinjau</option>
            <option value="resolved">Selesai (Resolved)</option>
            <option value="rejected">Ditolak</option>
          </select>
        </div>

        <div class="col-6 col-md-3">
          <select v-model="type" class="form-select form-select-sm" @change="applyFilter">
            <option value="all">Semua Jenis Masalah</option>
            <option value="wrong_time">Waktu Tidak Sesuai</option>
            <option value="location_error">Gagal Lokasi GPS</option>
            <option value="forgot_checkout">Lupa Check-out</option>
            <option value="system_error">Error Sistem</option>
            <option value="other">Lainnya</option>
          </select>
        </div>

        <div class="col-6 col-md-3">
          <select v-model="department" class="form-select form-select-sm" @change="applyFilter">
            <option value="all">Semua Departemen</option>
            <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
          </select>
        </div>

        <div class="col-6 col-md-3 d-flex gap-2">
          <input 
            v-model="date" 
            type="date" 
            class="form-control form-control-sm flex-fill" 
            @change="applyFilter"
          />
          <button type="button" class="btn btn-sm btn-outline-secondary" @click="date = ''; applyFilter()">
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Complaints Table -->
    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4">
      <div v-if="complaints && complaints.length > 0" class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
          <thead>
            <tr class="text-secondary border-bottom border-secondary border-opacity-25">
              <th>Pegawai</th>
              <th>Tgl Kejadian</th>
              <th>Kategori Masalah</th>
              <th>Deskripsi Komplain</th>
              <th>Bukti</th>
              <th>Status</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in complaints" :key="c.id" class="border-bottom border-secondary border-opacity-10">
              <td>
                <div class="fw-bold text-white">{{ c.employee?.user?.name }}</div>
                <small class="text-secondary">{{ c.employee?.department }}</small>
              </td>
              <td class="text-white fw-medium">{{ formatDate(c.date) }}</td>
              <td><StatusBadge :status="c.type" /></td>
              <td class="text-secondary text-truncate" style="max-width: 220px;">
                {{ c.description }}
              </td>
              <td>
                <a v-if="c.attachment" :href="c.attachment" target="_blank" class="text-info" title="Lihat Bukti">
                  <i class="bi bi-paperclip fs-6"></i>
                </a>
                <span v-else class="text-secondary">-</span>
              </td>
              <td><StatusBadge :status="c.status" /></td>
              <td class="text-end">
                <button 
                  type="button" 
                  class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1"
                  style="font-size: 0.75rem;"
                  @click="openOffcanvas(c)"
                >
                  <i class="bi bi-layout-sidebar-reverse"></i>
                  <span>Tinjau & Tindak</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="text-center py-5 text-secondary">
        Tidak ada data komplain yang ditemukan.
      </div>
    </div>

    <!-- Complaint Offcanvas Drawer -->
    <ComplaintOffcanvas 
      :show="showOffcanvas" 
      :complaint="selectedComplaint" 
      @close="showOffcanvas = false" 
    />
  </AdminLayout>
</template>
