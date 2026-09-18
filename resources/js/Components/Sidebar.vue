<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import LogoutModal from '@/Components/LogoutModal.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');
const pendingCounts = computed(() => page.props.pendingCounts || { leaveRequests: 0, complaints: 0 });

const showLogoutModal = ref(false);

const currentRoute = computed(() => page.url);

const isUrlActive = (path) => {
  return currentRoute.value.startsWith(path);
};
</script>

<template>
  <aside class="fixed top-0 bottom-0 left-0 w-64 bg-white border-r border-slate-200/80 hidden md:flex flex-col z-30">
    <!-- Brand -->
    <div class="h-16 px-5 border-b border-slate-100 flex items-center gap-3">
      <div class="w-9 h-9 bg-blue-600 text-white rounded-xl shadow-md shadow-blue-500/20 flex items-center justify-center">
        <i class="bi bi-building text-lg"></i>
      </div>
      <div>
        <div class="font-bold text-slate-900 leading-none">HRIS</div>
        <div class="text-[11px] text-slate-400 font-medium mt-0.5">Sistem Manajemen SDM</div>
      </div>
    </div>

    <!-- User Mini Profile -->
    <div class="p-3 border-b border-slate-100">
      <div class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-100/80 flex items-center gap-3">
        <div class="relative shrink-0">
          <img 
            :src="user?.employee?.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user?.name || 'User') + '&background=2563eb&color=fff'" 
            alt="Avatar" 
            class="w-10 h-10 rounded-full object-cover ring-2 ring-white"
          />
          <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 ring-2 ring-white rounded-full"></span>
        </div>
        <div class="overflow-hidden">
          <div class="font-semibold text-slate-800 text-sm truncate leading-tight">{{ user?.name }}</div>
          <div class="text-xs text-slate-500 truncate mt-0.5">
            {{ user?.employee?.position || (isAdmin ? 'Administrator' : 'Pegawai') }}
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <div class="flex-1 overflow-y-auto px-3 py-3 space-y-1">
      <div class="px-3 pt-2 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">
        Menu Utama
      </div>

      <!-- Admin Menu -->
      <template v-if="isAdmin">
        <Link 
          :href="route('admin.dashboard')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-grid-1x2-fill text-base"></i>
          <span>Dashboard</span>
        </Link>

        <Link 
          :href="route('admin.employees.index')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/employees') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-people-fill text-base"></i>
          <span>Data Pegawai</span>
        </Link>

        <Link 
          :href="route('admin.schedules.index')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/schedules') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-calendar-range-fill text-base"></i>
          <span>Jadwal Kerja</span>
        </Link>

        <Link 
          :href="route('admin.attendance.index')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/attendance') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-calendar-check-fill text-base"></i>
          <span>Monitoring Absensi</span>
        </Link>

        <Link 
          :href="route('admin.reports.index')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/reports') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-file-earmark-bar-graph-fill text-base"></i>
          <span>Laporan & Rekap</span>
        </Link>

        <Link 
          :href="route('admin.performance.index')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/performance') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-trophy-fill text-base text-amber-500"></i>
          <span class="flex items-center justify-between flex-1">
            <span>Kinerja & EotM</span>
            <span class="px-1.5 py-0.2 text-[9px] font-black uppercase rounded-md bg-amber-100 text-amber-800 tracking-wider">Top</span>
          </span>
        </Link>

        <Link 
          :href="route('admin.payroll.index')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/payroll') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-wallet2 text-base"></i>
          <span>Payroll & Gaji</span>
        </Link>

        <div class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">
          Verifikasi & Komplain
        </div>

        <Link 
          :href="route('admin.leave-requests.index')" 
          class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/leave-requests') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <div class="flex items-center gap-3">
            <i class="bi bi-file-earmark-text-fill text-base"></i>
            <span>Pengajuan Cuti</span>
          </div>
          <span v-if="pendingCounts.leaveRequests > 0" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
            {{ pendingCounts.leaveRequests }}
          </span>
        </Link>

        <Link 
          :href="route('admin.complaints.index')" 
          class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/complaints') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <div class="flex items-center gap-3">
            <i class="bi bi-chat-left-dots-fill text-base"></i>
            <span>Komplain Absensi</span>
          </div>
          <span v-if="pendingCounts.complaints > 0" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-rose-100 text-rose-800">
            {{ pendingCounts.complaints }}
          </span>
        </Link>

        <div class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">
          Perusahaan & Sistem
        </div>

        <Link 
          :href="route('admin.announcements.index')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/announcements') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-megaphone-fill text-base"></i>
          <span>Pengumuman</span>
        </Link>

        <Link 
          :href="route('admin.holidays.index')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/holidays') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-calendar-heart-fill text-base"></i>
          <span>Hari Libur</span>
        </Link>

        <Link 
          :href="route('admin.settings.index')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/admin/settings') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-gear-fill text-base"></i>
          <span>Pengaturan Kantor</span>
        </Link>
      </template>

      <!-- Employee Menu -->
      <template v-else>
        <Link 
          :href="route('employee.dashboard')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/employee/dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-grid-1x2-fill text-base"></i>
          <span>Dashboard</span>
        </Link>

        <Link 
          :href="route('employee.attendance')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="currentRoute === '/employee/attendance' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-geo-alt-fill text-base"></i>
          <span>Absensi Hari Ini</span>
        </Link>

        <Link 
          :href="route('employee.history')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/employee/history') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-calendar-week-fill text-base"></i>
          <span>Riwayat Absensi</span>
        </Link>

        <Link 
          :href="route('employee.leave-requests.index')" 
          class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/employee/leave-requests') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <div class="flex items-center gap-3">
            <i class="bi bi-file-earmark-medical-fill text-base"></i>
            <span>Pengajuan Izin/Cuti</span>
          </div>
          <span v-if="pendingCounts.leaveRequests > 0" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
            {{ pendingCounts.leaveRequests }}
          </span>
        </Link>

        <Link 
          :href="route('employee.complaints.index')" 
          class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/employee/complaints') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <div class="flex items-center gap-3">
            <i class="bi bi-chat-dots-fill text-base"></i>
            <span>Komplain Absensi</span>
          </div>
          <span v-if="pendingCounts.complaints > 0" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-rose-100 text-rose-800">
            {{ pendingCounts.complaints }}
          </span>
        </Link>

        <Link 
          :href="route('employee.payroll.index')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/employee/payroll') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-wallet2 text-base"></i>
          <span>Slip Gaji</span>
        </Link>

        <Link 
          :href="route('employee.profile')" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isUrlActive('/employee/profile') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
        >
          <i class="bi bi-person-fill text-base"></i>
          <span>Profil Saya</span>
        </Link>
      </template>
    </div>

    <!-- Bottom Logout -->
    <div class="p-3 border-t border-slate-100">
      <button 
        type="button" 
        @click="showLogoutModal = true"
        class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-sm font-medium transition-colors cursor-pointer"
      >
        <i class="bi bi-box-arrow-right text-base"></i>
        <span>Keluar Sistem</span>
      </button>
    </div>
  </aside>

  <!-- Logout Confirmation Dialog -->
  <LogoutModal :show="showLogoutModal" @close="showLogoutModal = false" />
</template>
