<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import LeaveRequestModal from '@/Components/LeaveRequestModal.vue';
import ComplaintOffcanvas from '@/Components/ComplaintOffcanvas.vue';

const props = defineProps({
  stats: Object,
  filters: Object,
  departments: Array,
  attendances: Array,
  pendingLeaveRequests: Array,
  pendingComplaints: Array,
});

const filterDate = ref(props.filters.date);
const filterDept = ref(props.filters.department);

const applyFilter = () => {
  router.get(route('admin.dashboard'), {
    date: filterDate.value,
    department: filterDept.value,
  }, { preserveState: true });
};

// Modal and Drawer state
const selectedLeave = ref(null);
const showLeaveModal = ref(false);
const selectedComplaint = ref(null);
const showComplaintDrawer = ref(false);

const openLeaveDetail = (item) => {
  selectedLeave.value = item;
  showLeaveModal.value = true;
};

const openComplaintDetail = (item) => {
  selectedComplaint.value = item;
  showComplaintDrawer.value = true;
};

const formatTime = (timeStr) => {
  if (!timeStr) return '--:--';
  const date = new Date(timeStr);
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'short', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <AdminLayout>
    <Head title="Dashboard HR & Admin" />

    <!-- Page Title & Quick Filters -->
    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4 mb-4">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h4 class="fw-bold text-white mb-1">Dashboard Monitoring Presensi</h4>
          <p class="text-secondary small mb-0">Ringkasan aktivitas kehadiran harian seluruh pegawai dan tindakan peninjauan.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
          <input 
            v-model="filterDate" 
            type="date" 
            class="form-control form-control-sm" 
            style="width: 160px;" 
            @change="applyFilter"
          />
          <select 
            v-model="filterDept" 
            class="form-select form-select-sm" 
            style="width: 170px;" 
            @change="applyFilter"
          >
            <option value="all">Semua Departemen</option>
            <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-xl-3">
        <div class="card border border-secondary border-opacity-25 p-3 rounded-4 bg-success bg-opacity-10 h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-secondary small fw-semibold">HADIR TEPAT WAKTU</span>
            <span class="badge bg-success rounded-pill px-2">Hadir</span>
          </div>
          <div class="display-6 fw-bold text-success">{{ stats.present }}</div>
          <small class="text-secondary" style="font-size: 0.72rem;">Dari {{ stats.totalEmployees }} total pegawai</small>
        </div>
      </div>

      <div class="col-6 col-xl-3">
        <div class="card border border-secondary border-opacity-25 p-3 rounded-4 bg-warning bg-opacity-10 h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-secondary small fw-semibold">TERLAMBAT</span>
            <span class="badge bg-warning text-dark rounded-pill px-2">Telat</span>
          </div>
          <div class="display-6 fw-bold text-warning">{{ stats.late }}</div>
          <small class="text-secondary" style="font-size: 0.72rem;">Di atas batas toleransi shift</small>
        </div>
      </div>

      <div class="col-6 col-xl-3">
        <div class="card border border-secondary border-opacity-25 p-3 rounded-4 bg-info bg-opacity-10 h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-secondary small fw-semibold">WFH / IZIN / SAKIT</span>
            <span class="badge bg-info rounded-pill px-2">Dispensasi</span>
          </div>
          <div class="display-6 fw-bold text-info">{{ stats.wfh + stats.leave }}</div>
          <small class="text-secondary" style="font-size: 0.72rem;">WFH: {{ stats.wfh }} | Cuti: {{ stats.leave }}</small>
        </div>
      </div>

      <div class="col-6 col-xl-3">
        <div class="card border border-secondary border-opacity-25 p-3 rounded-4 bg-danger bg-opacity-10 h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-secondary small fw-semibold">BELUM CHECK-IN</span>
            <span class="badge bg-danger rounded-pill px-2">Absen</span>
          </div>
          <div class="display-6 fw-bold text-danger">{{ stats.notCheckedIn }}</div>
          <small class="text-secondary" style="font-size: 0.72rem;">Belum ada catatan hari ini</small>
        </div>
      </div>
    </div>

    <!-- Review Tasks Section (2 Columns for pending items) -->
    <div class="row g-3 mb-4">
      <!-- 1. Pending Leave Requests -->
      <div class="col-12 col-lg-6">
        <div class="card border border-secondary border-opacity-25 shadow-sm p-3 rounded-4 h-100">
          <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-file-earmark-medical-fill text-warning fs-5"></i>
              <h6 class="fw-bold text-white mb-0">Pengajuan Cuti Menunggu</h6>
            </div>
            <Link :href="route('admin.leave-requests.index')" class="small text-primary text-decoration-none">
              Lihat Semua &rarr;
            </Link>
          </div>

          <div v-if="pendingLeaveRequests && pendingLeaveRequests.length > 0">
            <div 
              v-for="lr in pendingLeaveRequests" 
              :key="lr.id" 
              class="p-2 rounded-3 bg-secondary bg-opacity-10 mb-2 d-flex align-items-center justify-content-between"
            >
              <div>
                <div class="fw-bold text-white small">{{ lr.employee?.user?.name }}</div>
                <small class="text-secondary">{{ lr.total_days }} hari ({{ formatDate(lr.start_date) }}) — <StatusBadge :status="lr.type" /></small>
              </div>
              <button type="button" class="btn btn-sm btn-outline-warning py-1 px-2" style="font-size: 0.75rem;" @click="openLeaveDetail(lr)">
                Tinjau
              </button>
            </div>
          </div>
          <div v-else class="text-center py-4 text-secondary small">
            <i class="bi bi-check2-all fs-3 d-block mb-1 text-success"></i>
            Tidak ada pengajuan cuti yang perlu ditinjau.
          </div>
        </div>
      </div>

      <!-- 2. Pending Complaints -->
      <div class="col-12 col-lg-6">
        <div class="card border border-secondary border-opacity-25 shadow-sm p-3 rounded-4 h-100">
          <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-chat-left-dots-fill text-danger fs-5"></i>
              <h6 class="fw-bold text-white mb-0">Komplain Perlu Respons</h6>
            </div>
            <Link :href="route('admin.complaints.index')" class="small text-primary text-decoration-none">
              Lihat Semua &rarr;
            </Link>
          </div>

          <div v-if="pendingComplaints && pendingComplaints.length > 0">
            <div 
              v-for="c in pendingComplaints" 
              :key="c.id" 
              class="p-2 rounded-3 bg-secondary bg-opacity-10 mb-2 d-flex align-items-center justify-content-between"
            >
              <div class="overflow-hidden me-2">
                <div class="fw-bold text-white small text-truncate">{{ c.employee?.user?.name }}</div>
                <small class="text-secondary text-truncate d-block">{{ c.description }}</small>
              </div>
              <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 text-nowrap" style="font-size: 0.75rem;" @click="openComplaintDetail(c)">
                Respons
              </button>
            </div>
          </div>
          <div v-else class="text-center py-4 text-secondary small">
            <i class="bi bi-check2-all fs-3 d-block mb-1 text-success"></i>
            Tidak ada komplain aktif yang perlu ditindaklanjuti.
          </div>
        </div>
      </div>
    </div>

    <!-- Today's Attendance Table -->
    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4 mb-4">
      <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-25">
        <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-calendar-check text-primary"></i>
          Daftar Presensi Hari Ini ({{ formatDate(filters.date) }})
        </h5>
        <span class="badge bg-secondary">{{ attendances?.length || 0 }} Pegawai Tercatat</span>
      </div>

      <div v-if="attendances && attendances.length > 0" class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
          <thead>
            <tr class="text-secondary border-bottom border-secondary border-opacity-25">
              <th>Pegawai</th>
              <th>Jabatan & Dept</th>
              <th>Jam Masuk</th>
              <th>Jam Keluar</th>
              <th>Status</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="att in attendances" :key="att.id" class="border-bottom border-secondary border-opacity-10">
              <td>
                <div class="fw-bold text-white">{{ att.employee?.user?.name }}</div>
                <small class="text-secondary">{{ att.employee?.user?.email }}</small>
              </td>
              <td>
                <div class="text-white">{{ att.employee?.position }}</div>
                <small class="text-secondary">{{ att.employee?.department }}</small>
              </td>
              <td class="text-success fw-bold">{{ formatTime(att.check_in_at) }}</td>
              <td class="text-info fw-bold">{{ formatTime(att.check_out_at) }}</td>
              <td><StatusBadge :status="att.status" /></td>
              <td class="text-secondary text-truncate" style="max-width: 220px;">{{ att.note || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="text-center py-5 text-secondary">
        Belum ada aktivitas presensi untuk tanggal ini.
      </div>
    </div>

    <!-- Reusable Leave Detail Modal -->
    <LeaveRequestModal 
      :show="showLeaveModal" 
      :request="selectedLeave" 
      @close="showLeaveModal = false" 
    />

    <!-- Reusable Complaint Drawer -->
    <ComplaintOffcanvas 
      :show="showComplaintDrawer" 
      :complaint="selectedComplaint" 
      @close="showComplaintDrawer = false" 
    />
  </AdminLayout>
</template>
