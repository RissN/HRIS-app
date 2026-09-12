<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AttendanceCard from '@/Components/AttendanceCard.vue';

const props = defineProps({
  attendances: Array,
  filters: Object,
  summary: Object,
});

const month = ref(props.filters.month);
const year = ref(props.filters.year);

const months = [
  { value: 1, label: 'Januari' },
  { value: 2, label: 'Februari' },
  { value: 3, label: 'Maret' },
  { value: 4, label: 'April' },
  { value: 5, label: 'Mei' },
  { value: 6, label: 'Juni' },
  { value: 7, label: 'Juli' },
  { value: 8, label: 'Agustus' },
  { value: 9, label: 'September' },
  { value: 10, label: 'Oktober' },
  { value: 11, label: 'November' },
  { value: 12, label: 'Desember' },
];

const applyFilter = () => {
  router.get(route('employee.history'), {
    month: month.value,
    year: year.value,
  }, { preserveState: true });
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
  <EmployeeLayout>
    <Head title="Riwayat Absensi" />

    <div class="row justify-content-center">
      <div class="col-12 col-xl-10">
        <!-- Header & Filters -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4 mb-4">
          <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
              <h5 class="fw-bold text-white mb-1">Riwayat Presensi Saya</h5>
              <p class="text-secondary small mb-0">Pantau rekapitulasi kehadiran dan ajukan komplain jika ada ketidaksesuaian.</p>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="d-flex align-items-center gap-2">
              <select v-model="month" class="form-select form-select-sm" @change="applyFilter">
                <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
              </select>

              <select v-model="year" class="form-select form-select-sm" @change="applyFilter">
                <option :value="2024">2024</option>
                <option :value="2025">2025</option>
                <option :value="2026">2026</option>
                <option :value="2027">2027</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-2 g-md-3 mb-4">
          <div class="col-6 col-md-3">
            <div class="card border border-secondary border-opacity-25 p-3 rounded-4 bg-success bg-opacity-10 text-center">
              <div class="text-secondary small" style="font-size: 0.72rem;">TOTAL HADIR</div>
              <div class="display-6 fw-bold text-success">{{ summary.present }}</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card border border-secondary border-opacity-25 p-3 rounded-4 bg-warning bg-opacity-10 text-center">
              <div class="text-secondary small" style="font-size: 0.72rem;">TERLAMBAT</div>
              <div class="display-6 fw-bold text-warning">{{ summary.late }}</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card border border-secondary border-opacity-25 p-3 rounded-4 bg-purple bg-opacity-10 text-center" style="background-color: rgba(168, 85, 247, 0.1);">
              <div class="text-secondary small" style="font-size: 0.72rem;">IZIN / SAKIT / WFH</div>
              <div class="display-6 fw-bold text-info">{{ summary.permission + summary.wfh }}</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card border border-secondary border-opacity-25 p-3 rounded-4 bg-danger bg-opacity-10 text-center">
              <div class="text-secondary small" style="font-size: 0.72rem;">ABSEN / ALPA</div>
              <div class="display-6 fw-bold text-danger">{{ summary.absent }}</div>
            </div>
          </div>
        </div>

        <!-- Attendance List / Table -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4">
          <div v-if="attendances && attendances.length > 0">
            <!-- Mobile Card View -->
            <div class="d-md-none">
              <AttendanceCard 
                v-for="att in attendances" 
                :key="att.id" 
                :attendance="att" 
                :show-complaint-btn="true"
              />
            </div>

            <!-- Desktop Table View -->
            <div class="d-none d-md-block table-responsive">
              <table class="table table-hover align-middle small mb-0">
                <thead>
                  <tr class="text-secondary border-bottom border-secondary border-opacity-25">
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                    <th class="text-end">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="att in attendances" :key="att.id" class="border-bottom border-secondary border-opacity-10">
                    <td class="text-white fw-medium">{{ formatDate(att.date) }}</td>
                    <td class="text-success fw-bold">{{ formatTime(att.check_in_at) }}</td>
                    <td class="text-info fw-bold">{{ formatTime(att.check_out_at) }}</td>
                    <td><StatusBadge :status="att.status" /></td>
                    <td class="text-secondary text-truncate" style="max-width: 200px;">
                      {{ att.note || '-' }}
                    </td>
                    <td class="text-end">
                      <Link 
                        :href="route('employee.complaints.create', { attendance_id: att.id })" 
                        class="btn btn-sm btn-outline-warning py-1 px-2"
                        style="font-size: 0.75rem;"
                      >
                        <i class="bi bi-exclamation-triangle me-1"></i> Komplain
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div v-else class="text-center py-5 text-secondary">
            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
            Tidak ada riwayat presensi pada bulan ini.
          </div>
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>
