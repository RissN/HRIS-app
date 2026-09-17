<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';

const props = defineProps({
  employee: Object,
  announcements: Array,
  upcomingHolidays: Array,
  todayAttendance: Object,
});

// Active filter tab: 'all', 'info', 'primary', 'warning_danger', 'holidays'
const activeTab = ref('all');
const searchQuery = ref('');

// Selected item for reader modal
const selectedItem = ref(null);
const showModal = ref(false);

const openDetail = (item, isHoliday = false) => {
  selectedItem.value = {
    ...item,
    isHoliday,
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedItem.value = null;
};

// Filtered announcements
const filteredAnnouncements = computed(() => {
  let list = props.announcements || [];

  if (activeTab.value === 'info') {
    list = list.filter((a) => a.type === 'info');
  } else if (activeTab.value === 'primary') {
    list = list.filter((a) => a.type === 'primary');
  } else if (activeTab.value === 'warning_danger') {
    list = list.filter((a) => a.type === 'warning' || a.type === 'danger');
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter((a) => 
      (a.title && a.title.toLowerCase().includes(q)) || 
      (a.content && a.content.toLowerCase().includes(q))
    );
  }

  return list;
});

// Upcoming holidays list
const holidaysList = computed(() => {
  let list = props.upcomingHolidays || [];
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter((h) => 
      (h.name && h.name.toLowerCase().includes(q)) || 
      (h.description && h.description.toLowerCase().includes(q))
    );
  }
  return list;
});

const getTypeBadge = (type) => {
  switch (type) {
    case 'primary':
      return { label: 'Agenda & Event', bg: 'bg-indigo-50', text: 'text-indigo-700', border: 'border-indigo-200', icon: 'bi-calendar-event-fill' };
    case 'warning':
      return { label: 'Aturan / Peringatan', bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-200', icon: 'bi-exclamation-triangle-fill' };
    case 'danger':
      return { label: 'Penting / Mendesak', bg: 'bg-rose-50', text: 'text-rose-700', border: 'border-rose-200', icon: 'bi-shield-exclamation' };
    default:
      return { label: 'Pengumuman Umum', bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200', icon: 'bi-megaphone-fill' };
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const formatShortDate = (dateStr) => {
  if (!dateStr) return { day: '01', month: 'JAN' };
  const d = new Date(dateStr);
  const day = d.getDate().toString().padStart(2, '0');
  const month = d.toLocaleDateString('id-ID', { month: 'short' }).toUpperCase();
  return { day, month };
};

const getRelativeTime = (dateStr) => {
  if (!dateStr) return '';
  const now = new Date();
  const created = new Date(dateStr);
  const diffHours = Math.floor((now - created) / (1000 * 60 * 60));
  if (diffHours < 1) return 'Baru saja';
  if (diffHours < 24) return `${diffHours} jam yang lalu`;
  const diffDays = Math.floor(diffHours / 24);
  if (diffDays === 1) return 'Kemarin';
  if (diffDays < 7) return `${diffDays} hari yang lalu`;
  return formatDate(dateStr);
};

const getDaysUntil = (dateStr) => {
  if (!dateStr) return '';
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const target = new Date(dateStr);
  target.setHours(0, 0, 0, 0);
  const diffDays = Math.round((target - today) / (1000 * 60 * 60 * 24));
  if (diffDays === 0) return 'Hari ini';
  if (diffDays === 1) return 'Besok';
  if (diffDays > 1) return `${diffDays} hari lagi`;
  return 'Sudah lewat';
};
</script>

<template>
  <EmployeeLayout>
    <Head title="Dashboard Pegawai - Papan Pengumuman & Agenda" />

    <div class="space-y-4 sm:space-y-6">
      
      <!-- 1. Hero Identity Banner (Mobile Responsive) -->
      <div class="rounded-2xl sm:rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 text-white p-4 sm:p-7 shadow-sm relative overflow-hidden">
        <!-- Background subtle decorative pattern -->
        <div class="absolute -right-10 -bottom-10 w-48 sm:w-64 h-48 sm:h-64 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
        <div class="absolute right-12 top-0 w-24 sm:w-32 h-24 sm:h-32 rounded-full bg-white/10 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-6">
          <div class="flex items-start gap-3 sm:gap-4">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white shrink-0 shadow-inner">
              <i class="bi bi-person-badge-fill text-2xl sm:text-3xl"></i>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-1">
                <span class="px-2 py-0.5 rounded-full bg-blue-500/50 border border-white/25 text-[10px] sm:text-[11px] font-bold tracking-wide uppercase">
                  {{ employee?.employment_status_label || 'Karyawan PT Transjakarta' }}
                </span>
                <span v-if="employee?.employee_code" class="px-2 py-0.5 rounded-md bg-white/20 font-mono font-black text-[11px] sm:text-xs text-blue-100">
                  {{ employee.employee_code }}
                </span>
              </div>
              <h1 class="text-lg sm:text-2xl font-black tracking-tight leading-tight truncate">
                Selamat Datang, {{ employee?.user?.name || $page.props.auth.user?.name }}
              </h1>
              <p class="text-xs sm:text-sm text-blue-100/90 mt-0.5 flex flex-wrap items-center gap-1.5">
                <span class="font-semibold">{{ employee?.position || 'Staf Operasional' }}</span>
                <span>&bull;</span>
                <span>{{ employee?.department || 'Transjakarta' }}</span>
                <span v-if="employee?.region_label">&bull; {{ employee.region_label }}</span>
                <span v-if="employee?.pool_depot">({{ employee.pool_depot }})</span>
              </p>
            </div>
          </div>

          <!-- Today's Attendance Quick Widget -->
          <div class="bg-white/10 backdrop-blur-md rounded-xl sm:rounded-2xl p-3.5 sm:p-4 border border-white/15 w-full md:w-auto md:min-w-[260px] flex flex-col justify-between">
            <div class="text-[11px] font-semibold text-blue-100 flex items-center justify-between mb-1.5">
              <span>Status Presensi Hari Ini</span>
              <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full" :class="todayAttendance ? 'bg-emerald-400 animate-pulse' : 'bg-amber-300'"></span>
                <span class="text-[10px] font-bold text-white uppercase">{{ todayAttendance ? 'Tercatat' : 'Belum' }}</span>
              </span>
            </div>

            <div class="my-1.5">
              <div v-if="todayAttendance" class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-400 text-slate-950 font-black text-xs shrink-0">
                  <i class="bi bi-check-lg"></i>
                </span>
                <div class="min-w-0">
                  <div class="font-bold text-xs sm:text-sm text-white capitalize">Sudah Presensi Masuk</div>
                  <div class="text-[11px] text-blue-200">Waktu: {{ todayAttendance.check_in_at ? todayAttendance.check_in_at.substring(11, 16) + ' WIB' : '-' }}</div>
                </div>
              </div>
              <div v-else class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-amber-400 text-slate-950 font-black text-xs shrink-0">
                  <i class="bi bi-exclamation"></i>
                </span>
                <div class="min-w-0">
                  <div class="font-bold text-xs sm:text-sm text-white">Belum Presensi</div>
                  <div class="text-[11px] text-amber-200">Silakan lakukan absensi kerja hari ini</div>
                </div>
              </div>
            </div>

            <Link
              :href="route('employee.attendance')"
              class="w-full mt-2 py-2 px-3 rounded-xl bg-white text-blue-700 hover:bg-blue-50 active:scale-98 font-bold text-xs text-center transition-all shadow-xs block"
            >
              {{ todayAttendance ? 'Buka Riwayat / Absen Pulang →' : 'Buka Lembar Absen Sekarang →' }}
            </Link>
          </div>
        </div>
      </div>

      <!-- 2. Controls & Filter Bar (Mobile-Friendly) -->
      <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-3 sm:p-4 shadow-xs space-y-3 sm:space-y-0 sm:flex sm:items-center sm:justify-between sm:gap-4">
        <!-- Search box (Full width on mobile, prominent and quick) -->
        <div class="relative w-full sm:w-72 md:w-80 order-1 sm:order-2">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
            <i class="bi bi-search text-xs"></i>
          </span>
          <input
            v-model="searchQuery"
            type="text"
            class="w-full pl-8 pr-8 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 bg-slate-50 text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
            placeholder="Cari pengumuman atau agenda..."
          />
          <button
            v-if="searchQuery"
            type="button"
            @click="searchQuery = ''"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 cursor-pointer"
          >
            <i class="bi bi-x-circle-fill text-xs"></i>
          </button>
        </div>

        <!-- Filter Tabs (Horizontal touch swipe on mobile, wrap on desktop) -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 -mx-3 px-3 sm:mx-0 sm:px-0 no-scrollbar sm:flex-wrap text-xs order-2 sm:order-1 select-none">
          <button
            type="button"
            @click="activeTab = 'all'"
            class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap shrink-0 active:scale-95"
            :class="activeTab === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
          >
            Semua ({{ (announcements?.length || 0) + (upcomingHolidays?.length || 0) }})
          </button>
          <button
            type="button"
            @click="activeTab = 'primary'"
            class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap shrink-0 flex items-center gap-1.5 active:scale-95"
            :class="activeTab === 'primary' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
          >
            <i class="bi bi-calendar-star-fill text-[11px]"></i>
            <span>Agenda & Event</span>
          </button>
          <button
            type="button"
            @click="activeTab = 'info'"
            class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap shrink-0 flex items-center gap-1.5 active:scale-95"
            :class="activeTab === 'info' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
          >
            <i class="bi bi-megaphone-fill text-[11px]"></i>
            <span>Pengumuman Umum</span>
          </button>
          <button
            type="button"
            @click="activeTab = 'warning_danger'"
            class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap shrink-0 flex items-center gap-1.5 active:scale-95"
            :class="activeTab === 'warning_danger' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
          >
            <i class="bi bi-exclamation-triangle-fill text-[11px]"></i>
            <span>Penting & Aturan</span>
          </button>
          <button
            type="button"
            @click="activeTab = 'holidays'"
            class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap shrink-0 flex items-center gap-1.5 active:scale-95"
            :class="activeTab === 'holidays' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
          >
            <i class="bi bi-calendar-heart-fill text-[11px]"></i>
            <span>Hari Libur ({{ upcomingHolidays?.length || 0 }})</span>
          </button>
        </div>
      </div>

      <!-- 3. Main Dashboard Layout (Feed & Calendar) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
        
        <!-- Left: Announcements & Events Feed (8 Cols) -->
        <div class="lg:col-span-8 space-y-3.5 sm:space-y-4">
          <div class="flex items-center justify-between px-1">
            <h2 class="text-sm sm:text-base font-black text-slate-900 tracking-tight flex items-center gap-2">
              <i class="bi bi-newspaper text-blue-600"></i>
              <span>Papan Berita & Surat Edaran Resmi</span>
            </h2>
            <span class="text-xs text-slate-400 font-medium">
              {{ activeTab === 'holidays' ? holidaysList.length : filteredAnnouncements.length }} informasi
            </span>
          </div>

          <!-- If Viewing Holidays tab -->
          <div v-if="activeTab === 'holidays'" class="space-y-3">
            <div 
              v-for="holiday in holidaysList" 
              :key="holiday.id"
              class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-white border border-slate-100 shadow-xs hover:shadow-md active:scale-[0.99] transition-all flex items-start gap-3 sm:gap-4 cursor-pointer"
              @click="openDetail(holiday, true)"
            >
              <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex flex-col items-center justify-center shrink-0">
                <span class="text-[10px] sm:text-xs font-black uppercase tracking-wider">{{ formatShortDate(holiday.date).month }}</span>
                <span class="text-base sm:text-lg font-black leading-none mt-0.5">{{ formatShortDate(holiday.date).day }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    Hari Libur Nasional
                  </span>
                  <span class="text-[11px] sm:text-xs font-bold text-slate-500">{{ getDaysUntil(holiday.date) }}</span>
                </div>
                <h3 class="font-bold text-slate-900 text-sm sm:text-base mt-1 truncate">{{ holiday.name }}</h3>
                <p class="text-xs text-slate-600 mt-0.5 line-clamp-2 leading-relaxed">{{ holiday.description || 'Hari libur operasional resmi.' }}</p>
              </div>
            </div>
          </div>

          <!-- Standard Announcements List -->
          <div v-else-if="filteredAnnouncements.length > 0" class="space-y-3 sm:space-y-4">
            <div
              v-for="ann in filteredAnnouncements"
              :key="ann.id"
              class="p-4 sm:p-6 rounded-2xl sm:rounded-3xl bg-white border border-slate-100 shadow-xs hover:shadow-md active:scale-[0.99] transition-all duration-150 flex flex-col justify-between group cursor-pointer"
              @click="openDetail(ann, false)"
            >
              <div>
                <!-- Top metadata: Type badge, relative time, author -->
                <div class="flex items-center justify-between gap-2 mb-2 sm:mb-3">
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] sm:text-xs font-bold border"
                    :class="[getTypeBadge(ann.type).bg, getTypeBadge(ann.type).text, getTypeBadge(ann.type).border]"
                  >
                    <i :class="getTypeBadge(ann.type).icon"></i>
                    <span>{{ getTypeBadge(ann.type).label }}</span>
                  </span>

                  <div class="flex items-center gap-1.5 text-[11px] sm:text-xs text-slate-400 font-medium">
                    <i class="bi bi-clock"></i>
                    <span>{{ getRelativeTime(ann.created_at) }}</span>
                  </div>
                </div>

                <!-- Title -->
                <h3 class="text-base sm:text-lg font-black text-slate-900 leading-snug group-hover:text-blue-600 transition-colors">
                  {{ ann.title }}
                </h3>

                <!-- Content snippet -->
                <p class="text-xs sm:text-sm text-slate-600 mt-1.5 sm:mt-2 leading-relaxed line-clamp-3 whitespace-pre-line">
                  {{ ann.content }}
                </p>
              </div>

              <!-- Footer with author & read more -->
              <div class="mt-3.5 pt-3 sm:mt-4 sm:pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5 sm:gap-2 text-slate-500 truncate mr-2">
                  <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[9px] sm:text-[10px] shrink-0">
                    <i class="bi bi-building"></i>
                  </div>
                  <span class="truncate text-[11px] sm:text-xs">Oleh: <strong class="text-slate-700">{{ ann.author?.name || 'Manajemen Transjakarta' }}</strong></span>
                </div>

                <span class="font-bold text-blue-600 group-hover:underline flex items-center gap-1 shrink-0 text-xs">
                  <span>Detail</span>
                  <i class="bi bi-arrow-right"></i>
                </span>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="p-8 sm:p-12 text-center bg-white rounded-2xl sm:rounded-3xl border border-slate-100 text-slate-400">
            <i class="bi bi-inbox text-3xl sm:text-4xl text-slate-300 mb-2 block"></i>
            <h4 class="font-bold text-slate-700 text-sm">Tidak ada informasi yang sesuai</h4>
            <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau tab kategori di atas.</p>
          </div>
        </div>

        <!-- Right: Upcoming Events & Quick Links (4 Cols) -->
        <div class="lg:col-span-4 space-y-4 sm:space-y-5">
          
          <!-- Upcoming Events Card -->
          <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-white border border-slate-100 shadow-xs space-y-3.5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
              <h3 class="font-black text-slate-900 text-xs sm:text-sm flex items-center gap-2">
                <i class="bi bi-calendar-event-fill text-indigo-600"></i>
                <span>Kalender Libur & Agenda</span>
              </h3>
              <span class="text-[11px] font-bold text-slate-400">{{ upcomingHolidays?.length || 0 }} Agenda</span>
            </div>

            <div v-if="upcomingHolidays && upcomingHolidays.length > 0" class="space-y-2.5">
              <div
                v-for="holiday in upcomingHolidays"
                :key="holiday.id"
                class="p-2.5 sm:p-3 rounded-xl sm:rounded-2xl bg-slate-50 hover:bg-blue-50/50 active:scale-[0.99] border border-slate-100 transition-all flex items-center gap-3 cursor-pointer"
                @click="openDetail(holiday, true)"
              >
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-white border border-slate-200/80 flex flex-col items-center justify-center shrink-0 shadow-2xs">
                  <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase leading-none">{{ formatShortDate(holiday.date).month }}</span>
                  <span class="text-sm sm:text-base font-black text-slate-800 leading-none mt-0.5">{{ formatShortDate(holiday.date).day }}</span>
                </div>
                <div class="overflow-hidden flex-1 min-w-0">
                  <div class="font-bold text-xs text-slate-900 truncate leading-snug">{{ holiday.name }}</div>
                  <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5 truncate">
                    <span class="font-medium text-emerald-600 shrink-0">{{ getDaysUntil(holiday.date) }}</span>
                    <span>&bull;</span>
                    <span class="truncate">{{ holiday.description || 'Libur Resmi' }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-xs text-slate-400 py-3 text-center">
              Belum ada agenda libur mendatang.
            </div>
          </div>

          <!-- Transjakarta Quick Service Hotline -->
          <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-slate-900 text-white shadow-xs space-y-3 relative overflow-hidden">
            <div class="w-32 h-32 rounded-full bg-blue-600/20 absolute -right-10 -bottom-10 pointer-events-none blur-xl"></div>
            
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center text-white text-sm font-bold shadow-xs shrink-0">
                <i class="bi bi-headset"></i>
              </div>
              <div class="min-w-0">
                <h4 class="font-bold text-xs truncate">Pusat Bantuan Staf Transjakarta</h4>
                <div class="text-[11px] text-slate-400 truncate">Operation Command Center</div>
              </div>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
              Mengalami kendala jam presensi koridor, bus mogok, atau surat dokter?
            </p>

            <div class="pt-1 flex flex-col gap-2">
              <Link
                :href="route('employee.complaints.create')"
                class="w-full py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-bold text-xs text-center transition-all shadow-xs"
              >
                Ajukan Komplain Presensi →
              </Link>
              <Link
                :href="route('employee.leave-requests.create')"
                class="w-full py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-98 text-slate-200 font-bold text-xs text-center transition-all border border-slate-700"
              >
                Pengajuan Cuti / Izin Sakit
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Responsive Modal / Bottom Sheet for Full Announcement Reader -->
    <div
      v-if="showModal && selectedItem"
      class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-end sm:items-center justify-center p-0 sm:p-6 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-100 w-full sm:max-w-2xl max-h-[88vh] sm:max-h-[90vh] overflow-hidden animate-in fade-in slide-in-from-bottom-6 sm:slide-in-from-bottom-0 sm:zoom-in-95 flex flex-col">
        <!-- Mobile drag pill indicator -->
        <div class="w-full pt-3 pb-1 flex justify-center sm:hidden bg-slate-50/50">
          <div class="w-12 h-1.5 bg-slate-300 rounded-full"></div>
        </div>

        <!-- Header -->
        <div class="p-4 sm:p-6 border-b border-slate-100 flex items-start justify-between gap-3 bg-slate-50/50">
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 mb-2 flex-wrap">
              <span
                v-if="selectedItem.isHoliday"
                class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"
              >
                Hari Libur Nasional
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border"
                :class="[getTypeBadge(selectedItem.type).bg, getTypeBadge(selectedItem.type).text, getTypeBadge(selectedItem.type).border]"
              >
                <i :class="getTypeBadge(selectedItem.type).icon"></i>
                <span>{{ getTypeBadge(selectedItem.type).label }}</span>
              </span>
              <span class="text-xs text-slate-400">
                {{ selectedItem.isHoliday ? formatDate(selectedItem.date) : formatDate(selectedItem.created_at) }}
              </span>
            </div>
            <h3 class="text-base sm:text-xl font-black text-slate-900 leading-snug">
              {{ selectedItem.name || selectedItem.title }}
            </h3>
          </div>

          <button
            type="button"
            @click="closeModal"
            class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-500 flex items-center justify-center transition-all cursor-pointer shrink-0"
          >
            <i class="bi bi-x-lg text-sm"></i>
          </button>
        </div>

        <!-- Body Content with smooth scrolling -->
        <div class="p-5 sm:p-6 overflow-y-auto space-y-4 text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line overscroll-contain">
          {{ selectedItem.content || selectedItem.description || 'Tidak ada rincian tambahan untuk agenda ini.' }}
        </div>

        <!-- Footer -->
        <div class="p-3.5 sm:p-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs pb-safe">
          <span class="text-slate-500 truncate mr-2">
            Target: <strong class="text-slate-800">Seluruh Staf Transjakarta</strong>
          </span>

          <button
            type="button"
            @click="closeModal"
            class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold hover:bg-slate-800 active:scale-95 transition-all cursor-pointer shadow-xs shrink-0"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>
