<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import BottomNav from '@/Components/BottomNav.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash || {});
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans">
    <!-- Desktop Sidebar -->
    <Sidebar />

    <!-- Mobile Top Header Bar -->
    <header class="md:hidden sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 py-3 flex items-center justify-between shadow-xs">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center shadow-xs">
          <i class="bi bi-clock-history text-base"></i>
        </div>
        <div>
          <div class="font-bold text-slate-900 text-sm leading-tight">HRIS</div>
          <div class="text-[11px] text-slate-500 font-medium leading-none">{{ user?.employee?.position || 'Pegawai' }}</div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <NotificationDropdown />
        <Link :href="route('employee.profile')" class="flex items-center" title="Profil Saya">
          <img 
            :src="user?.employee?.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user?.name || 'User') + '&background=2563eb&color=fff'" 
            class="w-8 h-8 rounded-full object-cover ring-2 ring-blue-600/20" 
            alt="Avatar"
          />
        </Link>
      </div>
    </header>

    <!-- Main Content Area -->
    <div class="md:pl-64 flex-1 flex flex-col pb-24 md:pb-6 min-h-screen">
      <!-- Desktop Top Header Bar -->
      <header class="hidden md:flex sticky top-0 z-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-6 py-3.5 items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
          <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-200/60">
            Portal Pegawai
          </span>
          <span class="text-xs text-slate-500 font-medium">
            Human Resource Information System
          </span>
        </div>

        <div class="flex items-center gap-3">
          <NotificationDropdown />
          <Link :href="route('employee.payroll.index')" class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5 shadow-xs">
            <i class="bi bi-wallet2 text-blue-600"></i>
            <span>Slip Gaji</span>
          </Link>
        </div>
      </header>
      <!-- Flash Notifications -->
      <div v-if="flash.success || flash.error" class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-4">
        <div 
          v-if="flash.success" 
          class="flex items-center gap-3 p-3.5 text-sm rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 shadow-xs"
        >
          <i class="bi bi-check-circle-fill text-emerald-600 text-base shrink-0"></i>
          <span class="flex-1 font-medium">{{ flash.success }}</span>
        </div>

        <div 
          v-if="flash.error" 
          class="flex items-center gap-3 p-3.5 text-sm rounded-xl bg-rose-50 border border-rose-200/80 text-rose-800 shadow-xs"
        >
          <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base shrink-0"></i>
          <span class="flex-1 font-medium">{{ flash.error }}</span>
        </div>
      </div>

      <!-- Main Slot Content -->
      <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <slot />
      </main>

      <!-- Desktop Footer -->
      <footer class="hidden md:block text-center py-4 text-xs text-slate-400 border-t border-slate-200/60 mt-auto">
        &copy; {{ new Date().getFullYear() }} Absensi Pro &mdash; Sistem Presensi Kerja Mobile-Friendly
      </footer>
    </div>

    <!-- Mobile Bottom Navigation Bar -->
    <BottomNav />
  </div>
</template>
