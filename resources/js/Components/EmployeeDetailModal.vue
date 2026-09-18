<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  employeeId: [Number, String],
  month: {
    type: [Number, String],
    default: () => new Date().getMonth() + 1,
  },
  year: {
    type: [Number, String],
    default: () => new Date().getFullYear(),
  },
});

const emit = defineEmits(['close', 'give-appreciation']);

const loading = ref(false);
const summary = ref(null);
const activeTab = ref('rapor'); // 'rapor', 'appreciations', 'attendances'

const fetchSummary = async () => {
  if (!props.employeeId) return;
  loading.value = true;
  try {
    const res = await axios.get(route('admin.employees.summary', props.employeeId), {
      params: {
        month: props.month,
        year: props.year,
      },
    });
    summary.value = res.data;
  } catch (err) {
    console.error('Failed to load employee summary:', err);
  } finally {
    loading.value = false;
  }
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    fetchSummary();
  } else {
    summary.value = null;
    activeTab.value = 'rapor';
  }
});

watch([() => props.month, () => props.year], () => {
  if (props.show) {
    fetchSummary();
  }
});

const close = () => {
  emit('close');
};

const openGiveAppreciation = () => {
  if (summary.value?.employee) {
    emit('give-appreciation', summary.value.employee);
  }
};

const getSourceBadge = (source) => {
  switch (source) {
    case 'sosmed':
      return { label: 'Media Sosial Viral', bg: 'bg-pink-50', text: 'text-pink-700', border: 'border-pink-200', icon: 'bi-tiktok' };
    case 'customer':
      return { label: 'Pujian Penumpang', bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200', icon: 'bi-chat-heart-fill' };
    case 'service':
      return { label: 'Pelayanan Prima', bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200', icon: 'bi-star-fill' };
    default:
      return { label: 'Inisiatif Khusus', bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-200', icon: 'bi-award-fill' };
  }
};

const getStatusBadge = (status) => {
  switch (status) {
    case 'present':
      return { label: 'Tepat Waktu', bg: 'bg-emerald-100 text-emerald-800' };
    case 'late':
      return { label: 'Terlambat', bg: 'bg-amber-100 text-amber-800' };
    case 'leave':
      return { label: 'Izin / Cuti', bg: 'bg-blue-100 text-blue-800' };
    default:
      return { label: 'Alpha', bg: 'bg-rose-100 text-rose-800' };
  }
};
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
    @click.self="close"
  >
    <div
      class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-2xl w-full my-auto overflow-hidden animate-in fade-in zoom-in-95 flex flex-col max-h-[90vh]"
    >
      <!-- Loading State -->
      <div v-if="loading" class="p-12 text-center space-y-3">
        <div class="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        <div class="text-xs font-bold text-slate-500">Memuat profil & rapor karyawan...</div>
      </div>

      <!-- Loaded Content -->
      <template v-else-if="summary">
        <!-- 1. Header Profil (Clean White & Slate theme seragam) -->
        <div class="p-5 sm:p-6 border-b border-slate-100 bg-white flex items-start justify-between gap-4">
          <div class="flex items-start gap-3.5 sm:gap-4 min-w-0">
            <img
              :src="summary.employee.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(summary.employee.name) + '&background=2563eb&color=fff'"
              class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl object-cover ring-2 ring-slate-100 shrink-0 shadow-xs"
              alt="Avatar"
            />
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded-md bg-blue-50 font-mono font-bold text-xs text-blue-700 border border-blue-200/60">
                  {{ summary.employee.employee_code }}
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                  {{ summary.employee.employment_status_label }}
                </span>
              </div>
              <h3 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 truncate">
                {{ summary.employee.name }}
              </h3>
              <p class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-1.5">
                <span class="font-semibold text-slate-700">{{ summary.employee.position }}</span>
                <span>&bull;</span>
                <span>{{ summary.employee.region_label }}</span>
                <span v-if="summary.employee.pool_depot">({{ summary.employee.pool_depot }})</span>
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="close"
            class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors cursor-pointer shrink-0"
          >
            <i class="bi bi-x-lg text-xs"></i>
          </button>
        </div>

        <!-- 2. Sub-Tabs Bar -->
        <div class="flex items-center border-b border-slate-100 bg-slate-50/70 px-4 sm:px-6 gap-2 text-xs select-none">
          <button
            type="button"
            @click="activeTab = 'rapor'"
            class="py-3 px-3 font-bold border-b-2 transition-all cursor-pointer flex items-center gap-1.5"
            :class="activeTab === 'rapor' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-900'"
          >
            <i class="bi bi-award-fill"></i>
            <span>Rapor Kinerja ({{ summary.performance.month_name }})</span>
          </button>
          <button
            type="button"
            @click="activeTab = 'appreciations'"
            class="py-3 px-3 font-bold border-b-2 transition-all cursor-pointer flex items-center gap-1.5"
            :class="activeTab === 'appreciations' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-900'"
          >
            <i class="bi bi-chat-heart-fill"></i>
            <span>Apresiasi HR ({{ summary.appreciations.length }})</span>
          </button>
          <button
            type="button"
            @click="activeTab = 'attendances'"
            class="py-3 px-3 font-bold border-b-2 transition-all cursor-pointer flex items-center gap-1.5"
            :class="activeTab === 'attendances' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-900'"
          >
            <i class="bi bi-calendar2-check"></i>
            <span>Log Absensi</span>
          </button>
        </div>

        <!-- 3. Scrollable Body Content -->
        <div class="p-5 sm:p-6 overflow-y-auto flex-1 space-y-4 text-xs sm:text-sm">
          
          <!-- TAB 1: RAPOR KINERJA & SKOR -->
          <div v-if="activeTab === 'rapor'" class="space-y-4">
            <!-- Score Cards Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <div class="p-3.5 rounded-2xl bg-blue-50/80 border border-blue-100">
                <div class="text-[10px] font-bold uppercase text-blue-600">Total Skor</div>
                <div class="text-xl sm:text-2xl font-black text-blue-950 mt-1">{{ summary.performance.final_score }}</div>
                <div class="text-[10px] text-blue-600/80 mt-0.5">Akumulasi Bulan Ini</div>
              </div>

              <div class="p-3.5 rounded-2xl bg-amber-50/80 border border-amber-100">
                <div class="text-[10px] font-bold uppercase text-amber-600">Peringkat Divisi</div>
                <div class="text-xl sm:text-2xl font-black text-amber-950 mt-1">
                  #{{ summary.performance.rank }}
                  <span class="text-xs font-bold text-amber-700">/ {{ summary.performance.total_in_position }}</span>
                </div>
                <div class="text-[10px] text-amber-700/80 mt-0.5">Kategori {{ summary.employee.position }}</div>
              </div>

              <div class="p-3.5 rounded-2xl bg-emerald-50/80 border border-emerald-100">
                <div class="text-[10px] font-bold uppercase text-emerald-600">Skor Presensi</div>
                <div class="text-xl sm:text-2xl font-black text-emerald-950 mt-1">{{ summary.performance.attendance_score }}</div>
                <div class="text-[10px] text-emerald-700/80 mt-0.5">{{ summary.performance.punctuality_rate }}% Disiplin</div>
              </div>

              <div class="p-3.5 rounded-2xl bg-sky-50/80 border border-sky-100">
                <div class="text-[10px] font-bold uppercase text-sky-700">Bonus Apresiasi</div>
                <div class="text-xl sm:text-2xl font-black text-sky-950 mt-1">+{{ summary.performance.appreciation_score }}</div>
                <div class="text-[10px] text-sky-700/80 mt-0.5">Sosmed & Merit HR</div>
              </div>
            </div>

            <!-- Attendance Discipline Breakdown -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2.5">
              <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <i class="bi bi-pie-chart-fill text-blue-600"></i>
                <span>Rincian Kedisiplinan Kerja (Bulan Ini)</span>
              </h4>
              <div class="grid grid-cols-4 gap-2 text-center pt-1">
                <div class="p-2 rounded-xl bg-white border border-slate-200/80">
                  <div class="text-base font-black text-emerald-600">{{ summary.performance.count_present }}</div>
                  <div class="text-[10px] font-bold text-slate-500">Tepat Waktu</div>
                </div>
                <div class="p-2 rounded-xl bg-white border border-slate-200/80">
                  <div class="text-base font-black text-amber-600">{{ summary.performance.count_late }}</div>
                  <div class="text-[10px] font-bold text-slate-500">Terlambat</div>
                </div>
                <div class="p-2 rounded-xl bg-white border border-slate-200/80">
                  <div class="text-base font-black text-blue-600">{{ summary.performance.count_leave }}</div>
                  <div class="text-[10px] font-bold text-slate-500">Izin / Cuti</div>
                </div>
                <div class="p-2 rounded-xl bg-white border border-slate-200/80">
                  <div class="text-base font-black text-rose-600">{{ summary.performance.count_absent }}</div>
                  <div class="text-[10px] font-bold text-slate-500">Alpha</div>
                </div>
              </div>
            </div>

            <!-- Employee Contact & Profile details -->
            <div class="p-4 rounded-2xl bg-white border border-slate-100 space-y-2">
              <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">Informasi Kepegawaian</h4>
              <div class="grid grid-cols-2 gap-y-2 text-xs pt-1">
                <div>
                  <span class="text-slate-400 block text-[11px]">Email Resmi:</span>
                  <span class="font-semibold text-slate-800">{{ summary.employee.email || '-' }}</span>
                </div>
                <div>
                  <span class="text-slate-400 block text-[11px]">Nomor Telepon:</span>
                  <span class="font-semibold text-slate-800">{{ summary.employee.phone || '-' }}</span>
                </div>
                <div>
                  <span class="text-slate-400 block text-[11px]">Tanggal Bergabung:</span>
                  <span class="font-semibold text-slate-800">{{ summary.employee.joined_date }}</span>
                </div>
                <div>
                  <span class="text-slate-400 block text-[11px]">Departemen:</span>
                  <span class="font-semibold text-slate-800">{{ summary.employee.department }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: RIWAYAT APRESIASI HR & SOSMED -->
          <div v-else-if="activeTab === 'appreciations'" class="space-y-3">
            <div class="flex items-center justify-between">
              <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                Daftar Apresiasi & Penghargaan Terverifikasi
              </h4>
              <button
                type="button"
                @click="openGiveAppreciation"
                class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition cursor-pointer flex items-center gap-1"
              >
                <i class="bi bi-plus-lg"></i>
                <span>Beri Apresiasi</span>
              </button>
            </div>

            <div v-if="summary.appreciations && summary.appreciations.length > 0" class="space-y-2.5">
              <div
                v-for="app in summary.appreciations"
                :key="app.id"
                class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2"
              >
                <div class="flex items-start justify-between gap-2">
                  <div class="flex items-center gap-2">
                    <span
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                      :class="[getSourceBadge(app.source).bg, getSourceBadge(app.source).text, getSourceBadge(app.source).border]"
                    >
                      <i :class="getSourceBadge(app.source).icon"></i>
                      <span>{{ getSourceBadge(app.source).label }}</span>
                    </span>
                    <span class="text-[11px] text-slate-400">{{ app.date }}</span>
                  </div>

                  <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-bold text-xs border border-blue-200">
                    +{{ app.points }} Poin
                  </span>
                </div>

                <h5 class="font-bold text-slate-900 text-sm leading-snug">{{ app.title }}</h5>
                <p v-if="app.description" class="text-xs text-slate-600 leading-relaxed">{{ app.description }}</p>

                <div class="flex items-center justify-between pt-1 text-[11px] text-slate-400">
                  <span>Dicatat oleh: <strong class="text-slate-700">{{ app.admin?.name || 'Admin HR' }}</strong></span>
                  <a
                    v-if="app.evidence_url"
                    :href="app.evidence_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-blue-600 hover:underline font-semibold flex items-center gap-1"
                  >
                    <span>Lihat Bukti Sosmed</span>
                    <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                  </a>
                </div>
              </div>
            </div>

            <div v-else class="p-8 text-center text-slate-400 bg-slate-50 rounded-2xl">
              <i class="bi bi-heart text-3xl text-slate-300 block mb-1"></i>
              <div class="font-bold text-xs text-slate-600">Belum ada catatan apresiasi khusus</div>
              <p class="text-[11px] text-slate-400 mt-0.5">Admin HR dapat menambahkan poin apresiasi jika karyawan berprestasi atau viral di media sosial.</p>
              <button
                type="button"
                @click="openGiveAppreciation"
                class="mt-3 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition shadow-xs cursor-pointer inline-flex items-center gap-1.5"
              >
                <i class="bi bi-plus-lg"></i>
                <span>Beri Apresiasi Sekarang</span>
              </button>
            </div>
          </div>

          <!-- TAB 3: LOG 10 PRESENSI TERAKHIR -->
          <div v-else-if="activeTab === 'attendances'" class="space-y-3">
            <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">
              10 Log Presensi Terakhir
            </h4>

            <div v-if="summary.recent_attendances && summary.recent_attendances.length > 0" class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="pb-2">Tanggal</th>
                    <th class="pb-2">Masuk</th>
                    <th class="pb-2">Pulang</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2">Catatan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="att in summary.recent_attendances" :key="att.id" class="hover:bg-slate-50">
                    <td class="py-2.5 font-semibold text-slate-800">{{ att.date }}</td>
                    <td class="py-2.5 text-slate-600">{{ att.check_in }}</td>
                    <td class="py-2.5 text-slate-600">{{ att.check_out }}</td>
                    <td class="py-2.5">
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="getStatusBadge(att.status).bg">
                        {{ getStatusBadge(att.status).label }}
                      </span>
                    </td>
                    <td class="py-2.5 text-slate-400 truncate max-w-[120px]">{{ att.note || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-else class="p-8 text-center text-slate-400 bg-slate-50 rounded-2xl">
              Belum ada riwayat absensi tercatat.
            </div>
          </div>

        </div>

        <!-- 4. Footer Actions -->
        <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs">
          <button
            type="button"
            @click="openGiveAppreciation"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-semibold transition shadow-xs shadow-blue-600/20 cursor-pointer flex items-center gap-1.5"
          >
            <i class="bi bi-star-fill text-amber-300"></i>
            <span>Beri Poin Apresiasi</span>
          </button>

          <button
            type="button"
            @click="close"
            class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 active:scale-95 text-slate-700 font-semibold transition cursor-pointer"
          >
            Tutup Window
          </button>
        </div>
      </template>
    </div>
  </div>
</template>
