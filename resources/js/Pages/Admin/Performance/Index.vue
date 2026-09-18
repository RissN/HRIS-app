<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import EmployeeDetailModal from '@/Components/EmployeeDetailModal.vue';
import GiveAppreciationModal from '@/Components/GiveAppreciationModal.vue';

const props = defineProps({
  month: Number,
  year: Number,
  region: String,
  regions: Object,
  leaderboards: Object,
  recentAppreciations: Array,
  stats: Object,
  employeeOptions: Array,
  appreciationSources: Object,
});

// Selected position tab: 'Pramudi', 'Pramusapa', 'Pramujaga', 'Karyawan Kantor'
const activePosition = ref('Pramudi');

// Filter state
const selectedMonth = ref(props.month);
const selectedYear = ref(props.year);
const selectedRegion = ref(props.region);

// Modals state
const showDetailModal = ref(false);
const selectedEmployeeId = ref(null);

const showAppreciationModal = ref(false);
const preselectedEmployee = ref(null);

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

const currentMonthLabel = computed(() => {
  return months.find((m) => m.value === selectedMonth.value)?.label || 'Bulan';
});

const applyFilters = () => {
  router.get(route('admin.performance.index'), {
    month: selectedMonth.value,
    year: selectedYear.value,
    region: selectedRegion.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const currentBoard = computed(() => {
  return props.leaderboards[activePosition.value] || {
    position: activePosition.value,
    podium: [],
    leaderboard: [],
    total_candidates: 0,
  };
});

const openEmployeeDetail = (employeeId) => {
  selectedEmployeeId.value = employeeId;
  showDetailModal.value = true;
};

const openGiveAppreciation = (employee = null) => {
  preselectedEmployee.value = employee;
  showAppreciationModal.value = true;
};

const deleteAppreciation = (appreciation) => {
  if (confirm(`Hapus apresiasi "${appreciation.title}" untuk ${appreciation.employee?.user?.name}? Poin akan dikurangi kembali.`)) {
    router.delete(route('admin.performance.appreciations.destroy', appreciation.id), {
      preserveScroll: true,
    });
  }
};

const getSourceBadge = (source) => {
  switch (source) {
    case 'sosmed':
      return { label: 'Viral Medsos', bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200', icon: 'bi-phone' };
    case 'customer':
      return { label: 'Pujian Penumpang', bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200', icon: 'bi-chat-heart-fill' };
    case 'service':
      return { label: 'Pelayanan Prima', bg: 'bg-indigo-50', text: 'text-indigo-700', border: 'border-indigo-200', icon: 'bi-star-fill' };
    default:
      return { label: 'Inisiatif Khusus', bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-200', icon: 'bi-award-fill' };
  }
};
</script>

<template>
  <AdminLayout>
    <Head title="Kinerja & Employee of the Month" />

    <div class="space-y-6">
      
      <!-- 1. Header & Quick Metric Cards (Seragam 100% dengan Page Lain) -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
          <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1">
              <i class="bi bi-trophy"></i>
              <span>Manajemen Talenta & Prestasi</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Kinerja & Employee of the Month</h1>
            <p class="text-sm text-slate-500 mt-0.5">
              Akumulasi nilai kedisiplinan presensi harian serta apresiasi prestasi kerja dan pelayanan operasional Transjakarta.
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-2.5">
            <button
              type="button"
              @click="openGiveAppreciation()"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-xs shadow-blue-600/20 transition-all active:scale-95 cursor-pointer shrink-0"
            >
              <i class="bi bi-plus-lg"></i>
              <span>Beri Apresiasi HR</span>
            </button>
          </div>
        </div>

        <!-- 4 Quick Summary KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 pt-5">
          <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Apresiasi Bulan Ini</div>
            <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">
              {{ stats.total_appreciations }} Catatan
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">Periode {{ currentMonthLabel }} {{ selectedYear }}</div>
          </div>

          <div class="p-3.5 rounded-2xl bg-blue-50/50 border border-blue-100/70">
            <div class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Total Poin Bonus HR</div>
            <div class="text-xl sm:text-2xl font-black text-blue-900 mt-0.5">
              +{{ stats.total_points }} Poin
            </div>
            <div class="text-[11px] text-blue-700 mt-0.5">Apresiasi Medsos & Inisiatif</div>
          </div>

          <div class="p-3.5 rounded-2xl bg-emerald-50/50 border border-emerald-100/70">
            <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Rata-rata Skor Disiplin</div>
            <div class="text-xl sm:text-2xl font-black text-emerald-900 mt-0.5">
              {{ stats.average_score }} / 100
            </div>
            <div class="text-[11px] text-emerald-700 mt-0.5">Kedisiplinan Presensi</div>
          </div>

          <div class="p-3.5 rounded-2xl bg-amber-50/50 border border-amber-100/70">
            <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Divisi Jabatan</div>
            <div class="text-xl sm:text-2xl font-black text-amber-900 mt-0.5">
              4 Divisi
            </div>
            <div class="text-[11px] text-amber-700 mt-0.5">Klasemen Terpisah</div>
          </div>
        </div>
      </div>

      <!-- 2. Controls & Filter Bar -->
      <div class="bg-white rounded-3xl border border-slate-100 p-4 sm:p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Position Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 -mx-2 px-2 sm:mx-0 sm:px-0 no-scrollbar sm:flex-wrap text-xs select-none">
          <button
            type="button"
            @click="activePosition = 'Pramudi'"
            class="px-4 py-2.5 rounded-xl font-semibold transition-all cursor-pointer whitespace-nowrap flex items-center gap-2 active:scale-95"
            :class="activePosition === 'Pramudi' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200/60'"
          >
            <i class="bi bi-bus-front text-sm"></i>
            <span>Pramudi Bus ({{ leaderboards['Pramudi']?.total_candidates || 0 }})</span>
          </button>

          <button
            type="button"
            @click="activePosition = 'Pramusapa'"
            class="px-4 py-2.5 rounded-xl font-semibold transition-all cursor-pointer whitespace-nowrap flex items-center gap-2 active:scale-95"
            :class="activePosition === 'Pramusapa' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200/60'"
          >
            <i class="bi bi-person-check text-sm"></i>
            <span>Pramusapa ({{ leaderboards['Pramusapa']?.total_candidates || 0 }})</span>
          </button>

          <button
            type="button"
            @click="activePosition = 'Pramujaga'"
            class="px-4 py-2.5 rounded-xl font-semibold transition-all cursor-pointer whitespace-nowrap flex items-center gap-2 active:scale-95"
            :class="activePosition === 'Pramujaga' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200/60'"
          >
            <i class="bi bi-shield-check text-sm"></i>
            <span>Pramujaga ({{ leaderboards['Pramujaga']?.total_candidates || 0 }})</span>
          </button>

          <button
            type="button"
            @click="activePosition = 'Karyawan Kantor'"
            class="px-4 py-2.5 rounded-xl font-semibold transition-all cursor-pointer whitespace-nowrap flex items-center gap-2 active:scale-95"
            :class="activePosition === 'Karyawan Kantor' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200/60'"
          >
            <i class="bi bi-briefcase text-sm"></i>
            <span>Staf Kantor ({{ leaderboards['Karyawan Kantor']?.total_candidates || 0 }})</span>
          </button>
        </div>

        <!-- Month & Region Selector -->
        <div class="flex items-center gap-2 text-xs shrink-0">
          <select
            v-model="selectedMonth"
            @change="applyFilters"
            class="bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 transition outline-none cursor-pointer"
          >
            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>

          <select
            v-model="selectedYear"
            @change="applyFilters"
            class="bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 transition outline-none cursor-pointer"
          >
            <option :value="2025">2025</option>
            <option :value="2026">2026</option>
            <option :value="2027">2027</option>
          </select>

          <select
            v-model="selectedRegion"
            @change="applyFilters"
            class="bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 transition outline-none cursor-pointer"
          >
            <option value="all">Semua Wilayah</option>
            <option v-for="(label, key) in regions" :key="key" :value="key">{{ label }}</option>
          </select>
        </div>
      </div>

      <!-- 3. Podium Juara 1, 2, 3 (Employee of the Month) -->
      <div v-if="currentBoard.podium && currentBoard.podium.length > 0" class="space-y-3">
        <div class="flex items-center justify-between px-1">
          <h2 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <i class="bi bi-trophy-fill text-amber-500"></i>
            <span>Podium Employee of the Month: {{ activePosition }} ({{ currentMonthLabel }} {{ selectedYear }})</span>
          </h2>
          <span class="text-xs text-slate-400">Peringkat 3 Teratas</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          
          <!-- PODIUM CARD: Loop through podium -->
          <div
            v-for="cand in currentBoard.podium"
            :key="cand.id"
            class="p-5 rounded-3xl bg-white border border-slate-100 shadow-xs transition-all duration-200 relative overflow-hidden flex flex-col justify-between group cursor-pointer hover:border-blue-200 hover:shadow-md active:scale-[0.99]"
            :class="{
              'ring-1 ring-amber-400/30': cand.rank === 1,
            }"
            @click="openEmployeeDetail(cand.id)"
          >
            <!-- Badge Rank Pill -->
            <div class="flex items-start justify-between gap-2 mb-3">
              <span
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                :class="{
                  'bg-amber-100 text-amber-900 border border-amber-200': cand.rank === 1,
                  'bg-slate-100 text-slate-800 border border-slate-200': cand.rank === 2,
                  'bg-orange-50 text-orange-800 border border-orange-200': cand.rank === 3,
                }"
              >
                <i v-if="cand.rank === 1" class="bi bi-trophy-fill text-amber-600 text-xs"></i>
                <i v-else class="bi bi-award-fill text-slate-500 text-xs"></i>
                <span>{{ cand.rank === 1 ? 'Juara 1 - EotM' : (cand.rank === 2 ? 'Juara 2' : 'Juara 3') }}</span>
              </span>

              <span class="text-xs font-mono font-bold text-slate-400">{{ cand.employee_code }}</span>
            </div>

            <!-- Profile Info -->
            <div class="flex items-center gap-3.5 my-2">
              <div class="relative">
                <img
                  :src="cand.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(cand.user?.name || 'User') + '&background=2563eb&color=fff'"
                  class="w-14 h-14 rounded-2xl object-cover ring-2"
                  :class="cand.rank === 1 ? 'ring-amber-400' : 'ring-slate-200'"
                  alt="Avatar"
                />
                <span
                  v-if="cand.rank === 1"
                  class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-amber-400 text-slate-950 flex items-center justify-center text-[10px] shadow-xs"
                  title="Juara 1"
                >
                  <i class="bi bi-star-fill"></i>
                </span>
              </div>

              <div class="min-w-0 flex-1">
                <h3 class="font-bold text-slate-900 text-base leading-snug truncate group-hover:text-blue-600 transition-colors">
                  {{ cand.user?.name }}
                </h3>
                <p class="text-xs text-slate-500 truncate mt-0.5">
                  <span>{{ cand.pool_depot || cand.department }}</span>
                </p>
                <div class="text-[11px] text-slate-400 font-semibold uppercase mt-0.5">
                  {{ cand.region }}
                </div>
              </div>
            </div>

            <!-- Scores Breakdown -->
            <div class="mt-3 pt-3 border-t border-slate-100 grid grid-cols-3 gap-2 text-center text-xs">
              <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Skor Absen</div>
                <div class="font-bold text-slate-800 text-sm mt-0.5">{{ cand.total_attendance_score }}</div>
              </div>

              <div class="p-2 rounded-xl bg-blue-50/60 border border-blue-100/60">
                <div class="text-[10px] text-blue-600 font-bold uppercase">Apresiasi</div>
                <div class="font-bold text-blue-700 text-sm mt-0.5">+{{ cand.total_appreciation_score }}</div>
              </div>

              <div class="p-2 rounded-xl bg-slate-100/80 border border-slate-200">
                <div class="text-[10px] text-slate-600 font-bold uppercase">Total Skor</div>
                <div class="font-black text-slate-900 text-sm mt-0.5">{{ cand.final_total_score }}</div>
              </div>
            </div>

            <!-- Footer Link -->
            <div class="mt-3 pt-2 text-right">
              <span class="text-xs font-bold text-blue-600 group-hover:underline inline-flex items-center gap-1">
                <span>Lihat Rekapan Profil</span>
                <i class="bi bi-arrow-right text-[11px]"></i>
              </span>
            </div>
          </div>

        </div>
      </div>

      <!-- 4. Leaderboard Table (Peringkat Klasemen) -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h3 class="font-bold text-slate-900 text-sm sm:text-base flex items-center gap-2">
              <i class="bi bi-list-ol text-blue-600"></i>
              <span>Klasemen Lengkap Kinerja: {{ activePosition }}</span>
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">
              Menampilkan 20 kandidat dengan skor tertinggi. Klik pada nama pegawai untuk membuka rekapan lengkap.
            </p>
          </div>

          <span class="text-xs font-bold px-3 py-1 rounded-xl bg-slate-100 text-slate-600">
            Total {{ currentBoard.total_candidates }} Pegawai
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50/80 text-slate-400 font-bold uppercase text-[10px] border-b border-slate-100 select-none">
              <tr>
                <th class="py-3 px-4 w-16 text-center">Rank</th>
                <th class="py-3 px-4">Nama Pegawai & NIK</th>
                <th class="py-3 px-4">Wilayah & Depo</th>
                <th class="py-3 px-4 text-center">Disiplin Hadir</th>
                <th class="py-3 px-4 text-right">Poin Presensi</th>
                <th class="py-3 px-4 text-right">Apresiasi HR</th>
                <th class="py-3 px-4 text-right">Total Akumulasi</th>
                <th class="py-3 px-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="cand in currentBoard.leaderboard"
                :key="cand.id"
                class="hover:bg-slate-50/80 transition-colors group cursor-pointer"
                @click="openEmployeeDetail(cand.id)"
              >
                <!-- Rank -->
                <td class="py-3.5 px-4 text-center">
                  <span
                    class="inline-flex items-center justify-center w-7 h-7 rounded-xl font-bold text-xs"
                    :class="{
                      'bg-amber-100 text-amber-900 border border-amber-300': cand.rank === 1,
                      'bg-slate-100 text-slate-800 border border-slate-200': cand.rank === 2,
                      'bg-orange-50 text-orange-900 border border-orange-200': cand.rank === 3,
                      'text-slate-500 font-semibold': cand.rank > 3,
                    }"
                  >
                    {{ cand.rank }}
                  </span>
                </td>

                <!-- Name & NIK -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-2.5">
                    <img
                      :src="cand.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(cand.user?.name || 'User') + '&background=2563eb&color=fff'"
                      class="w-8 h-8 rounded-xl object-cover ring-1 ring-slate-200 shrink-0"
                      alt="Avatar"
                    />
                    <div class="min-w-0">
                      <div class="font-bold text-slate-900 text-xs sm:text-sm group-hover:text-blue-600 transition-colors truncate">
                        {{ cand.user?.name }}
                      </div>
                      <div class="text-[10px] text-slate-400 font-mono">
                        {{ cand.employee_code }} &bull; {{ cand.employment_status }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Region & Depot -->
                <td class="py-3.5 px-4 text-slate-600">
                  <div class="font-medium truncate max-w-[140px]">{{ cand.pool_depot || cand.department }}</div>
                  <div class="text-[10px] text-slate-400 uppercase font-semibold">{{ cand.region }}</div>
                </td>

                <!-- Punctuality Rate -->
                <td class="py-3.5 px-4 text-center">
                  <div class="inline-flex items-center gap-1.5 font-bold text-xs" :class="cand.punctuality_rate >= 90 ? 'text-emerald-600' : 'text-amber-600'">
                    <span>{{ cand.punctuality_rate }}%</span>
                  </div>
                  <div class="text-[10px] text-slate-400">{{ cand.count_present }} tepat / {{ cand.count_late }} telat</div>
                </td>

                <!-- Attendance Score -->
                <td class="py-3.5 px-4 text-right font-semibold text-slate-700">
                  {{ cand.total_attendance_score }}
                </td>

                <!-- Appreciation Score -->
                <td class="py-3.5 px-4 text-right">
                  <span
                    class="font-bold text-xs"
                    :class="cand.total_appreciation_score > 0 ? 'text-blue-600' : 'text-slate-300'"
                  >
                    +{{ cand.total_appreciation_score }}
                  </span>
                </td>

                <!-- Total Score -->
                <td class="py-3.5 px-4 text-right">
                  <span class="px-2.5 py-1 rounded-xl bg-blue-50 text-blue-900 font-black text-xs sm:text-sm border border-blue-100">
                    {{ cand.final_total_score }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="py-3.5 px-4 text-center" @click.stop>
                  <div class="flex items-center justify-center gap-1">
                    <button
                      type="button"
                      @click="openEmployeeDetail(cand.id)"
                      class="p-1.5 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-600 hover:text-blue-600 transition cursor-pointer"
                      title="Lihat Rekapan Karyawan"
                    >
                      <i class="bi bi-window-sidebar text-xs"></i>
                    </button>
                    <button
                      type="button"
                      @click="openGiveAppreciation(cand)"
                      class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition cursor-pointer"
                      title="Beri Apresiasi HR"
                    >
                      <i class="bi bi-plus-lg text-xs"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="currentBoard.leaderboard.length === 0">
                <td colspan="8" class="p-10 text-center text-slate-400">
                  <i class="bi bi-inbox text-3xl text-slate-300 block mb-1"></i>
                  <span>Belum ada data nilai untuk divisi ini pada periode terpilih.</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 5. Seksi Feed Apresiasi Terkini (Sosmed & Pujian HR) -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-bold text-slate-900 text-sm sm:text-base flex items-center gap-2">
              <i class="bi bi-chat-left-heart-fill text-blue-600"></i>
              <span>Riwayat Apresiasi HR & Media Sosial Terkini</span>
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">
              Catatan penghargaan, apresiasi medsos TikTok/X/IG, serta pujian penumpang yang diverifikasi oleh manajemen.
            </p>
          </div>

          <button
            type="button"
            @click="openGiveAppreciation()"
            class="px-3.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition cursor-pointer flex items-center gap-1.5"
          >
            <i class="bi bi-plus-lg"></i>
            <span>Beri Apresiasi</span>
          </button>
        </div>

        <div v-if="recentAppreciations && recentAppreciations.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
          <div
            v-for="app in recentAppreciations"
            :key="app.id"
            class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2.5 flex flex-col justify-between hover:shadow-xs transition-shadow"
          >
            <div>
              <div class="flex items-start justify-between gap-2">
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                  :class="[getSourceBadge(app.source).bg, getSourceBadge(app.source).text, getSourceBadge(app.source).border]"
                >
                  <i :class="getSourceBadge(app.source).icon"></i>
                  <span>{{ getSourceBadge(app.source).label }}</span>
                </span>

                <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold text-xs">
                  +{{ app.points }} Poin
                </span>
              </div>

              <!-- Employee Recipient -->
              <div
                class="mt-2 flex items-center gap-2 cursor-pointer group"
                @click="openEmployeeDetail(app.employee_id)"
              >
                <div class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-[10px] shrink-0">
                  <i class="bi bi-person-fill"></i>
                </div>
                <div class="min-w-0">
                  <div class="font-bold text-slate-800 text-xs group-hover:text-blue-600 truncate">
                    {{ app.employee?.user?.name }}
                  </div>
                  <div class="text-[10px] text-slate-400 font-mono">{{ app.employee?.employee_code }} &bull; {{ app.employee?.position }}</div>
                </div>
              </div>

              <h4 class="font-bold text-slate-900 text-xs leading-snug mt-2">{{ app.title }}</h4>
              <p v-if="app.description" class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                {{ app.description }}
              </p>
            </div>

            <!-- Meta info & Actions -->
            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
              <div class="text-slate-400">
                <span>{{ app.date }}</span>
              </div>

              <div class="flex items-center gap-2">
                <a
                  v-if="app.evidence_url"
                  :href="app.evidence_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-blue-600 hover:underline font-bold flex items-center gap-1"
                  title="Buka tautan media sosial"
                >
                  <span>Bukti</span>
                  <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                </a>

                <button
                  type="button"
                  @click="deleteAppreciation(app)"
                  class="text-slate-400 hover:text-rose-600 transition p-1 cursor-pointer"
                  title="Hapus Apresiasi"
                >
                  <i class="bi bi-trash text-xs"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="p-8 text-center text-slate-400 bg-slate-50 rounded-2xl">
          Belum ada riwayat apresiasi yang diinput oleh HR.
        </div>
      </div>

    </div>

    <!-- 1. Window Modal Rapor Karyawan Lengkap -->
    <EmployeeDetailModal
      :show="showDetailModal"
      :employee-id="selectedEmployeeId"
      :month="selectedMonth"
      :year="selectedYear"
      @close="showDetailModal = false"
      @give-appreciation="(emp) => { showDetailModal = false; openGiveAppreciation(emp); }"
    />

    <!-- 2. Modal Form Input Apresiasi HR -->
    <GiveAppreciationModal
      :show="showAppreciationModal"
      :preselected-employee="preselectedEmployee"
      :employee-options="employeeOptions"
      :sources="appreciationSources"
      @close="showAppreciationModal = false"
    />

  </AdminLayout>
</template>
