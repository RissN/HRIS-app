<script setup>
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
  attendances: Object, // Paginated
  stats: Object,
  departments: Array,
  filters: Object,
});

const filterMonth = ref(props.filters.month || new Date().toISOString().slice(0, 7));
const filterDept = ref(props.filters.department || 'all');
const filterStatus = ref(props.filters.status || 'all');
const search = ref(props.filters.search || '');

const applyFilter = () => {
  router.get(route('admin.reports.index'), {
    month: filterMonth.value,
    department: filterDept.value,
    status: filterStatus.value,
    search: search.value,
  }, { preserveState: true });
};

const resetFilter = () => {
  const currentMonth = new Date().toISOString().slice(0, 7);
  filterMonth.value = currentMonth;
  filterDept.value = 'all';
  filterStatus.value = 'all';
  search.value = '';
  router.get(route('admin.reports.index'), {
    month: currentMonth,
  }, { preserveState: true });
};

const exportCsv = () => {
  const params = new URLSearchParams({
    month: filterMonth.value,
    department: filterDept.value,
    status: filterStatus.value,
    search: search.value,
  });
  window.location.href = `${route('admin.reports.export')}?${params.toString()}`;
};

const printReport = () => {
  window.print();
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

const formatMonthName = (monthStr) => {
  if (!monthStr) return '';
  const [year, month] = monthStr.split('-');
  const date = new Date(year, month - 1);
  return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};
</script>

<template>
  <AdminLayout>
    <Head title="Rekap & Laporan Presensi - HRIS" />

    <div class="space-y-6">
      <!-- Printable Header Only visible in print -->
      <div class="hidden print:block mb-8 border-b-2 border-slate-900 pb-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">HRIS &mdash; REKAPITULASI PRESENSI PEGAWAI</h1>
            <p class="text-sm text-slate-600">Periode Laporan: {{ formatMonthName(filterMonth) }}</p>
          </div>
          <div class="text-right text-xs text-slate-500">
            <p>Dicetak pada: {{ new Date().toLocaleString('id-ID') }}</p>
            <p>Sistem Informasi Manajemen SDM</p>
          </div>
        </div>
      </div>

      <!-- Page Header & Action Buttons -->
      <div class="print:hidden bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1">
              <i class="bi bi-file-earmark-bar-graph"></i>
              <span>Laporan & Analitik</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Rekapitulasi Presensi</h1>
            <p class="text-sm text-slate-500 mt-0.5">
              Laporan data kehadiran bulanan, total jam kerja, keterlambatan, dan ekspor dokumen.
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-wrap items-center gap-2.5">
            <button
              type="button"
              @click="printReport"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all shadow-xs active:scale-95"
            >
              <i class="bi bi-printer text-slate-500"></i>
              <span>Cetak / PDF</span>
            </button>

            <button
              type="button"
              @click="exportCsv"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-xs shadow-blue-600/20 transition-all active:scale-95"
            >
              <i class="bi bi-file-earmark-excel"></i>
              <span>Unduh CSV / Excel</span>
            </button>
          </div>
        </div>

        <!-- Filter Controls -->
        <div class="mt-6 pt-5 border-t border-slate-100">
          <form @submit.prevent="applyFilter" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1.5">Bulan & Tahun</label>
              <input
                type="month"
                v-model="filterMonth"
                class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1.5">Departemen</label>
              <select
                v-model="filterDept"
                class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
              >
                <option value="all">Semua Departemen</option>
                <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status Kehadiran</label>
              <select
                v-model="filterStatus"
                class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
              >
                <option value="all">Semua Status</option>
                <option value="present">Hadir Tepat Waktu</option>
                <option value="late">Terlambat</option>
                <option value="wfh">WFH</option>
                <option value="sick">Sakit</option>
                <option value="permission">Izin</option>
                <option value="absent">Alpa / Tanpa Keterangan</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1.5">Cari Pegawai / NIK</label>
              <div class="relative">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input
                  type="text"
                  v-model="search"
                  placeholder="Nama atau NIK..."
                  class="w-full pl-9 bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
                />
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button
                type="submit"
                class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold transition active:scale-95 shadow-xs"
              >
                <i class="bi bi-filter"></i>
                <span>Filter</span>
              </button>
              <button
                type="button"
                @click="resetFilter"
                class="p-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition"
                title="Reset Filter"
              >
                <i class="bi bi-arrow-counterclockwise"></i>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Quick KPI Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-500">Total Log</span>
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
              <i class="bi bi-calendar-check"></i>
            </div>
          </div>
          <div class="text-2xl font-bold text-slate-900">{{ stats.total }}</div>
          <span class="text-[11px] text-slate-400">Periode {{ filterMonth }}</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-emerald-600">Tepat Waktu</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
              <i class="bi bi-check2-circle"></i>
            </div>
          </div>
          <div class="text-2xl font-bold text-emerald-600">{{ stats.present }}</div>
          <span class="text-[11px] text-slate-400">Kehadiran standar</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-amber-600">Terlambat</span>
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
              <i class="bi bi-clock-history"></i>
            </div>
          </div>
          <div class="text-2xl font-bold text-amber-600">{{ stats.late }}</div>
          <span class="text-[11px] text-amber-600 font-medium">Total {{ stats.total_late_minutes }} mnt</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-blue-600">WFH / Remote</span>
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
              <i class="bi bi-laptop"></i>
            </div>
          </div>
          <div class="text-2xl font-bold text-blue-600">{{ stats.wfh }}</div>
          <span class="text-[11px] text-slate-400">Kerja dari rumah</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-sky-600">Izin / Sakit</span>
            <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm">
              <i class="bi bi-bandaid"></i>
            </div>
          </div>
          <div class="text-2xl font-bold text-sky-700">{{ stats.permission + stats.sick }}</div>
          <span class="text-[11px] text-slate-400">{{ stats.sick }} sakit, {{ stats.permission }} izin</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-rose-600">Alpa / Bolos</span>
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm">
              <i class="bi bi-x-circle"></i>
            </div>
          </div>
          <div class="text-2xl font-bold text-rose-600">{{ stats.absent }}</div>
          <span class="text-[11px] text-rose-500 font-medium">Kena potongan</span>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-xs">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h2 class="font-bold text-slate-800 text-base">Rincian Kehadiran Pegawai</h2>
            <p class="text-xs text-slate-500 mt-0.5">Menampilkan {{ attendances.data.length }} dari {{ attendances.total }} data</p>
          </div>
          <span class="text-xs bg-slate-100 text-slate-600 font-medium px-3 py-1 rounded-full">
            Periode: {{ formatMonthName(filterMonth) }}
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50/75 text-xs uppercase font-semibold text-slate-500 border-b border-slate-100 tracking-wider">
              <tr>
                <th class="py-3.5 px-4 font-semibold">Tanggal</th>
                <th class="py-3.5 px-4 font-semibold">Pegawai</th>
                <th class="py-3.5 px-4 font-semibold">Departemen</th>
                <th class="py-3.5 px-4 font-semibold">Masuk</th>
                <th class="py-3.5 px-4 font-semibold">Pulang</th>
                <th class="py-3.5 px-4 font-semibold">Status</th>
                <th class="py-3.5 px-4 font-semibold text-right">Keterlambatan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="attendances.data.length === 0">
                <td colspan="7" class="py-12 text-center text-slate-400">
                  <i class="bi bi-calendar-x text-3xl mb-2 block text-slate-300"></i>
                  Tidak ada rekaman presensi yang cocok dengan filter yang dipilih.
                </td>
              </tr>
              <tr
                v-for="att in attendances.data"
                :key="att.id"
                class="hover:bg-slate-50/60 transition-colors"
              >
                <td class="py-3.5 px-4 font-medium text-slate-800 whitespace-nowrap">
                  {{ formatDate(att.date) }}
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-700 font-bold flex items-center justify-center text-xs">
                      {{ att.employee?.user?.name ? att.employee.user.name.charAt(0) : '?' }}
                    </div>
                    <div>
                      <div class="font-semibold text-slate-900">{{ att.employee?.user?.name || '-' }}</div>
                      <div class="text-xs text-slate-400 font-mono">{{ att.employee?.employee_code || att.employee?.nik || '-' }}</div>
                    </div>
                  </div>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                    {{ att.employee?.department || '-' }}
                  </span>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap font-mono text-xs">
                  <span v-if="att.check_in_at" class="text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded">
                    {{ formatTime(att.check_in_at) }}
                  </span>
                  <span v-else class="text-slate-400">--:--</span>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap font-mono text-xs">
                  <span v-if="att.check_out_at" class="text-blue-700 font-semibold bg-blue-50 px-2 py-0.5 rounded">
                    {{ formatTime(att.check_out_at) }}
                  </span>
                  <span v-else class="text-slate-400">--:--</span>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <StatusBadge :status="att.status" />
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap text-right">
                  <span
                    v-if="att.late_minutes > 0"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full"
                  >
                    <i class="bi bi-clock"></i>
                    {{ att.late_minutes }} mnt
                  </span>
                  <span v-else class="text-xs text-slate-400">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="attendances.links && attendances.links.length > 3" class="print:hidden p-4 border-t border-slate-100 flex items-center justify-between">
          <p class="text-xs text-slate-500">
            Halaman {{ attendances.current_page }} dari {{ attendances.last_page }}
          </p>
          <div class="flex items-center gap-1">
            <Component
              :is="link.url ? Link : 'span'"
              v-for="(link, i) in attendances.links"
              :key="i"
              :href="link.url"
              class="px-3 py-1.5 text-xs font-semibold rounded-lg transition"
              :class="[
                link.active
                  ? 'bg-blue-600 text-white shadow-xs'
                  : link.url
                    ? 'text-slate-600 hover:bg-slate-100'
                    : 'text-slate-300 cursor-not-allowed'
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
