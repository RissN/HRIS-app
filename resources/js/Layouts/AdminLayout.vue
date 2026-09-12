<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import LogoutModal from '@/Components/LogoutModal.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash || {});
const pendingCounts = computed(() => page.props.pendingCounts || { leaveRequests: 0, complaints: 0 });

const mobileMenuOpen = ref(false);
const showLogoutModal = ref(false);
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans">
    <!-- Desktop Sidebar -->
    <Sidebar />

    <!-- Main Content Area -->
    <div class="md:pl-64 flex-1 flex flex-col min-h-screen">
      <!-- Top Header Navbar -->
      <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-3">
          <!-- Mobile Menu Button (md:hidden) -->
          <button 
            type="button" 
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden p-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
            title="Menu Navigasi"
          >
            <i :class="mobileMenuOpen ? 'bi bi-x-lg' : 'bi bi-list'" class="text-base"></i>
          </button>

          <div class="md:hidden flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center shadow-xs">
              <i class="bi bi-building text-base"></i>
            </div>
            <span class="font-bold text-slate-900 text-sm">HRIS</span>
          </div>

          <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-200/60">
              Admin HR
            </span>
            <span class="text-xs text-slate-500 font-medium hidden sm:inline">
              Panel Manajemen Presensi
            </span>
          </div>
        </div>

        <!-- Top Right Actions -->
        <div class="flex items-center gap-2">
          <!-- Interactive Notification Bell Dropdown -->
          <NotificationDropdown />

          <!-- Pending Leave Requests Quick Badge -->
          <Link 
            :href="route('admin.leave-requests.index')" 
            class="relative p-2 rounded-xl text-slate-500 hover:text-amber-600 hover:bg-amber-50/50 border border-slate-200/80 transition-colors"
            title="Pengajuan Cuti Pending"
          >
            <i class="bi bi-file-earmark-medical text-base text-amber-500"></i>
            <span 
              v-if="pendingCounts.leaveRequests > 0" 
              class="absolute -top-1 -right-1 px-1.5 py-0.2 text-[10px] font-bold rounded-full bg-amber-500 text-white shadow-xs"
            >
              {{ pendingCounts.leaveRequests }}
            </span>
          </Link>

          <!-- Pending Complaints Quick Badge -->
          <Link 
            :href="route('admin.complaints.index')" 
            class="relative p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50/50 border border-slate-200/80 transition-colors"
            title="Komplain Pending"
          >
            <i class="bi bi-chat-dots text-base text-rose-500"></i>
            <span 
              v-if="pendingCounts.complaints > 0" 
              class="absolute -top-1 -right-1 px-1.5 py-0.2 text-[10px] font-bold rounded-full bg-rose-500 text-white shadow-xs"
            >
              {{ pendingCounts.complaints }}
            </span>
          </Link>
        </div>
      </header>

      <!-- Mobile Navigation Drawer / Dropdown (Only for < md) -->
      <div 
        v-if="mobileMenuOpen" 
        class="md:hidden bg-white border-b border-slate-200 px-4 py-3 space-y-1 animate-in fade-in slide-in-from-top-2 shadow-sm"
      >
        <div class="px-3 py-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">
          Menu Navigasi
        </div>
        <Link 
          :href="route('admin.dashboard')" 
          @click="mobileMenuOpen = false"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors"
        >
          <i class="bi bi-grid-1x2-fill text-slate-400"></i>
          <span>Dashboard</span>
        </Link>
        <Link 
          :href="route('admin.employees.index')" 
          @click="mobileMenuOpen = false"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors"
        >
          <i class="bi bi-people-fill text-slate-400"></i>
          <span>Data Pegawai</span>
        </Link>
        <Link 
          :href="route('admin.schedules.index')" 
          @click="mobileMenuOpen = false"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors"
        >
          <i class="bi bi-calendar-range-fill text-slate-400"></i>
          <span>Jadwal Kerja</span>
        </Link>
        <Link 
          :href="route('admin.attendance.index')" 
          @click="mobileMenuOpen = false"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors"
        >
          <i class="bi bi-calendar-check-fill text-slate-400"></i>
          <span>Monitoring Presensi</span>
        </Link>
        <Link 
          :href="route('admin.reports.index')" 
          @click="mobileMenuOpen = false"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors"
        >
          <i class="bi bi-file-earmark-bar-graph-fill text-slate-400"></i>
          <span>Laporan & Rekap</span>
        </Link>
        <Link 
          :href="route('admin.payroll.index')" 
          @click="mobileMenuOpen = false"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors"
        >
          <i class="bi bi-wallet2 text-slate-400"></i>
          <span>Payroll & Penggajian</span>
        </Link>
        <Link 
          :href="route('admin.leave-requests.index')" 
          @click="mobileMenuOpen = false"
          class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors"
        >
          <div class="flex items-center gap-3">
            <i class="bi bi-file-earmark-text-fill text-slate-400"></i>
            <span>Pengajuan Cuti</span>
          </div>
          <span v-if="pendingCounts.leaveRequests > 0" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">
            {{ pendingCounts.leaveRequests }}
          </span>
        </Link>
        <Link 
          :href="route('admin.complaints.index')" 
          @click="mobileMenuOpen = false"
          class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors"
        >
          <div class="flex items-center gap-3">
            <i class="bi bi-chat-left-dots-fill text-slate-400"></i>
            <span>Komplain Presensi</span>
          </div>
          <span v-if="pendingCounts.complaints > 0" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-800">
            {{ pendingCounts.complaints }}
          </span>
        </Link>
        <Link 
          :href="route('admin.announcements.index')" 
          @click="mobileMenuOpen = false"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors"
        >
          <i class="bi bi-megaphone-fill text-slate-400"></i>
          <span>Papan Pengumuman</span>
        </Link>
        <Link 
          :href="route('admin.holidays.index')" 
          @click="mobileMenuOpen = false"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors"
        >
          <i class="bi bi-calendar-heart-fill text-slate-400"></i>
          <span>Hari Libur</span>
        </Link>
        <Link 
          :href="route('admin.settings.index')" 
          @click="mobileMenuOpen = false"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors"
        >
          <i class="bi bi-gear-fill text-slate-400"></i>
          <span>Pengaturan Kantor</span>
        </Link>

        <!-- Mobile Drawer Logout -->
        <div class="pt-2 mt-1 border-t border-slate-100">
          <button 
            type="button" 
            @click="mobileMenuOpen = false; showLogoutModal = true"
            class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
          >
            <i class="bi bi-box-arrow-right text-rose-500"></i>
            <span>Keluar Sistem</span>
          </button>
        </div>
      </div>

      <!-- Flash Notifications -->
      <div v-if="flash.success || flash.error" class="w-full px-4 sm:px-6 lg:px-8 pt-4">
        <div 
          v-if="flash.success" 
          class="flex items-center gap-3 p-3.5 text-sm rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 shadow-xs"
        >
          <i class="bi bi-check-circle-fill text-emerald-600 text-base shrink-0"></i>
          <span class="flex-1 font-medium">{{ flash.success }}</span>
        </div>

        <div 
          v-if="flash.error" 
          class="flex items-center gap-3 p-3.5 text-sm rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 shadow-xs"
        >
          <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base shrink-0"></i>
          <span class="flex-1 font-medium">{{ flash.error }}</span>
        </div>
      </div>

      <!-- Main Slot -->
      <main class="flex-1 w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-6">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="text-center py-4 text-xs text-slate-400 border-t border-slate-200/60 mt-auto">
        &copy; {{ new Date().getFullYear() }} HRIS &mdash; Human Resource Information System
      </footer>
    </div>

    <!-- Logout Confirmation Modal -->
    <LogoutModal :show="showLogoutModal" @close="showLogoutModal = false" />
  </div>
</template>
