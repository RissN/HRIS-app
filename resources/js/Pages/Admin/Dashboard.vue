<script setup>
import { ref, computed } from 'vue';
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

// Modal states
const selectedLeave = ref(null);
const showLeaveModal = ref(false);
const selectedComplaintId = ref(null);
const showComplaintModal = ref(false);

const selectedComplaint = computed(() => {
  if (!selectedComplaintId.value) return null;
  return props.pendingComplaints?.find(c => c.id === selectedComplaintId.value) || null;
});

const openLeaveDetail = (item) => {
  selectedLeave.value = item;
  showLeaveModal.value = true;
};

const openComplaintDetail = (item) => {
  selectedComplaintId.value = item.id;
  showComplaintModal.value = true;
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
    <Head title="Dashboard HR & Admin" />

    <div class="space-y-6">
      <!-- Page Title & Quick Filters Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Dashboard Monitoring Presensi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Ringkasan aktivitas kehadiran harian seluruh pegawai dan tindakan peninjauan.</p>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <input 
              v-model="filterDate" 
              type="date" 
              class="px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors" 
              @change="applyFilter"
            />
            <select 
              v-model="filterDept" 
              class="px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors" 
              @change="applyFilter"
            >
              <option value="all">Semua Departemen</option>
              <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- 4 Stats Cards Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Hadir -->
        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Hadir Tepat Waktu</span>
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
          </div>
          <div class="text-3xl font-black text-slate-900">{{ stats.present }}</div>
          <div class="text-[11px] text-slate-400 mt-2">Dari {{ stats.totalEmployees }} total pegawai</div>
        </div>

        <!-- Terlambat -->
        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Terlambat</span>
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-xs"></span>
          </div>
          <div class="text-3xl font-black text-slate-900">{{ stats.late }}</div>
          <div class="text-[11px] text-slate-400 mt-2">Melebihi batas toleransi shift</div>
        </div>

        <!-- WFH / Izin / Sakit -->
        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">WFH / Izin / Sakit</span>
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-xs"></span>
          </div>
          <div class="text-3xl font-black text-slate-900">{{ stats.wfh + stats.leave }}</div>
          <div class="text-[11px] text-slate-400 mt-2">WFH: {{ stats.wfh }} &bull; Cuti: {{ stats.leave }}</div>
        </div>

        <!-- Belum Absen -->
        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Belum Check-In</span>
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-xs"></span>
          </div>
          <div class="text-3xl font-black text-slate-900">{{ stats.notCheckedIn }}</div>
          <div class="text-[11px] text-slate-400 mt-2">Belum ada catatan presensi hari ini</div>
        </div>
      </div>

      <!-- 2 Columns Pending Action Tasks -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Pending Leave Requests -->
        <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs flex flex-col">
          <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i class="bi bi-file-earmark-medical text-base"></i>
              </div>
              <div>
                <h2 class="text-sm font-bold text-slate-900">Pengajuan Cuti Menunggu</h2>
                <span class="text-[11px] text-slate-400">{{ pendingLeaveRequests?.length || 0 }} permohonan aktif</span>
              </div>
            </div>
            <Link :href="route('admin.leave-requests.index')" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
              Lihat Semua &rarr;
            </Link>
          </div>

          <div v-if="pendingLeaveRequests && pendingLeaveRequests.length > 0" class="space-y-2.5 flex-1">
            <div 
              v-for="lr in pendingLeaveRequests" 
              :key="lr.id" 
              class="p-3 rounded-2xl bg-slate-50 hover:bg-slate-100/70 border border-slate-100 flex items-center justify-between gap-2 transition-colors"
            >
              <div class="overflow-hidden">
                <div class="font-bold text-slate-900 text-xs">{{ lr.employee?.user?.name }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                  <span class="font-semibold text-blue-600">{{ lr.total_days }} Hari</span>
                  <span>&bull; {{ formatDate(lr.start_date) }}</span>
                  <StatusBadge :status="lr.type" />
                </div>
              </div>
              <button 
                type="button" 
                class="px-3 py-1.5 rounded-xl bg-white hover:bg-amber-50 text-amber-700 text-xs font-semibold border border-amber-200/80 transition-colors shrink-0 shadow-xs cursor-pointer"
                @click="openLeaveDetail(lr)"
              >
                Tinjau
              </button>
            </div>
          </div>
          <div v-else class="text-center py-10 text-slate-400 text-xs flex-1 flex flex-col items-center justify-center">
            <i class="bi bi-check2-circle text-2xl text-emerald-500 mb-1"></i>
            <span>Tidak ada pengajuan cuti yang perlu ditinjau.</span>
          </div>
        </div>

        <!-- Pending Complaints -->
        <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs flex flex-col">
          <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i class="bi bi-chat-left-dots text-base"></i>
              </div>
              <div>
                <h2 class="text-sm font-bold text-slate-900">Komplain Perlu Respons</h2>
                <span class="text-[11px] text-slate-400">{{ pendingComplaints?.length || 0 }} keluhan baru</span>
              </div>
            </div>
            <Link :href="route('admin.complaints.index')" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
              Lihat Semua &rarr;
            </Link>
          </div>

          <div v-if="pendingComplaints && pendingComplaints.length > 0" class="space-y-2.5 flex-1">
            <div 
              v-for="c in pendingComplaints" 
              :key="c.id" 
              class="p-3 rounded-2xl bg-slate-50 hover:bg-slate-100/70 border border-slate-100 flex items-center justify-between gap-2 transition-colors"
            >
              <div class="overflow-hidden pr-2">
                <div class="font-bold text-slate-900 text-xs truncate">{{ c.employee?.user?.name }}</div>
                <div class="text-[11px] text-slate-500 truncate mt-0.5">{{ c.description }}</div>
              </div>
              <button 
                type="button" 
                class="px-3 py-1.5 rounded-xl bg-white hover:bg-rose-50 text-rose-700 text-xs font-semibold border border-rose-200/80 transition-colors shrink-0 shadow-xs cursor-pointer"
                @click="openComplaintDetail(c)"
              >
                Respons
              </button>
            </div>
          </div>
          <div v-else class="text-center py-10 text-slate-400 text-xs flex-1 flex flex-col items-center justify-center">
            <i class="bi bi-check2-circle text-2xl text-emerald-500 mb-1"></i>
            <span>Tidak ada komplain aktif yang perlu ditindaklanjuti.</span>
          </div>
        </div>
      </div>

      <!-- Today's Attendance Table Card -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <i class="bi bi-calendar-check text-base"></i>
            </div>
            <div>
              <h2 class="text-sm font-bold text-slate-900">
                Daftar Presensi Hari Ini ({{ formatDate(filters.date) }})
              </h2>
              <p class="text-xs text-slate-400">Log kehadiran pegawai yang tercatat hari ini</p>
            </div>
          </div>
          <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">
            {{ attendances?.length || 0 }} Pegawai
          </span>
        </div>

        <div v-if="attendances && attendances.length > 0" class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="bg-slate-50/60 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4">Pegawai</th>
                <th class="py-3 px-4">Jabatan & Dept</th>
                <th class="py-3 px-4">Jam Masuk</th>
                <th class="py-3 px-4">Jam Keluar</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Keterangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="att in attendances" :key="att.id" class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-900 text-xs">{{ att.employee?.user?.name }}</div>
                  <div class="text-[11px] text-slate-400">{{ att.employee?.user?.email }}</div>
                </td>
                <td class="py-3 px-4">
                  <div class="font-semibold text-slate-800">{{ att.employee?.position }}</div>
                  <div class="text-[11px] text-slate-400">{{ att.employee?.department }}</div>
                </td>
                <td class="py-3 px-4 font-bold text-emerald-600 whitespace-nowrap">{{ formatTime(att.check_in_at) }}</td>
                <td class="py-3 px-4 font-bold text-blue-600 whitespace-nowrap">{{ formatTime(att.check_out_at) }}</td>
                <td class="py-3 px-4 whitespace-nowrap"><StatusBadge :status="att.status" /></td>
                <td class="py-3 px-4 text-slate-600 max-w-xs truncate text-[11px]">{{ att.note || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-14 text-slate-400 text-xs">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <i class="bi bi-calendar-x text-xl"></i>
          </div>
          <p class="font-semibold text-slate-600">Belum ada catatan presensi hari ini</p>
          <p class="text-[11px] text-slate-400 mt-0.5">Pegawai belum melakukan check-in pada tanggal yang dipilih</p>
        </div>
      </div>
    </div>

    <!-- Centered Modals -->
    <LeaveRequestModal 
      :show="showLeaveModal" 
      :request="selectedLeave" 
      @close="showLeaveModal = false" 
    />

    <ComplaintOffcanvas 
      :show="showComplaintModal" 
      :complaint="selectedComplaint" 
      @close="showComplaintModal = false" 
    />
  </AdminLayout>
</template>
