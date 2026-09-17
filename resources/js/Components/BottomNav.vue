<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const pendingCounts = computed(() => page.props.pendingCounts || { leaveRequests: 0, complaints: 0 });

const currentUrl = computed(() => page.url);

const isActive = (path) => {
  return currentUrl.value.startsWith(path);
};
</script>

<template>
  <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] md:hidden px-1 pt-1 pb-safe flex items-center justify-around select-none">
    <!-- 1. Beranda / Dashboard -->
    <Link 
      :href="route('employee.dashboard')" 
      class="relative flex flex-col items-center justify-center py-1 px-1.5 rounded-xl transition-all duration-150 active:scale-95 flex-1 max-w-[64px]"
      :class="isActive('/employee/dashboard') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800'"
    >
      <i class="bi bi-grid-1x2-fill text-lg mb-0.5 transition-transform" :class="isActive('/employee/dashboard') ? 'scale-110' : ''"></i>
      <span class="text-[10px] tracking-tight truncate leading-none">Beranda</span>
    </Link>

    <!-- 2. Absen -->
    <Link 
      :href="route('employee.attendance')" 
      class="relative flex flex-col items-center justify-center py-1 px-1.5 rounded-xl transition-all duration-150 active:scale-95 flex-1 max-w-[64px]"
      :class="currentUrl === '/employee/attendance' ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800'"
    >
      <i class="bi bi-geo-alt-fill text-lg mb-0.5 transition-transform" :class="currentUrl === '/employee/attendance' ? 'scale-110' : ''"></i>
      <span class="text-[10px] tracking-tight truncate leading-none">Absen</span>
    </Link>

    <!-- 3. Riwayat -->
    <Link 
      :href="route('employee.history')" 
      class="relative flex flex-col items-center justify-center py-1 px-1.5 rounded-xl transition-all duration-150 active:scale-95 flex-1 max-w-[64px]"
      :class="isActive('/employee/history') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800'"
    >
      <i class="bi bi-calendar2-check-fill text-lg mb-0.5 transition-transform" :class="isActive('/employee/history') ? 'scale-110' : ''"></i>
      <span class="text-[10px] tracking-tight truncate leading-none">Riwayat</span>
    </Link>

    <!-- 4. Pengajuan Cuti/Izin -->
    <Link 
      :href="route('employee.leave-requests.index')" 
      class="relative flex flex-col items-center justify-center py-1 px-1.5 rounded-xl transition-all duration-150 active:scale-95 flex-1 max-w-[64px]"
      :class="isActive('/employee/leave-requests') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800'"
    >
      <div class="relative">
        <i class="bi bi-file-earmark-medical-fill text-lg mb-0.5 block transition-transform" :class="isActive('/employee/leave-requests') ? 'scale-110' : ''"></i>
        <span v-if="pendingCounts.leaveRequests > 0" class="absolute -top-0.5 -right-1 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
      </div>
      <span class="text-[10px] tracking-tight truncate leading-none">Izin/Cuti</span>
    </Link>

    <!-- 5. Komplain -->
    <Link 
      :href="route('employee.complaints.index')" 
      class="relative flex flex-col items-center justify-center py-1 px-1.5 rounded-xl transition-all duration-150 active:scale-95 flex-1 max-w-[64px]"
      :class="isActive('/employee/complaints') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800'"
    >
      <div class="relative">
        <i class="bi bi-chat-dots-fill text-lg mb-0.5 block transition-transform" :class="isActive('/employee/complaints') ? 'scale-110' : ''"></i>
        <span v-if="pendingCounts.complaints > 0" class="absolute -top-0.5 -right-1 w-2 h-2 rounded-full bg-amber-500 ring-2 ring-white"></span>
      </div>
      <span class="text-[10px] tracking-tight truncate leading-none">Komplain</span>
    </Link>

    <!-- 6. Profil -->
    <Link 
      :href="route('employee.profile')" 
      class="relative flex flex-col items-center justify-center py-1 px-1.5 rounded-xl transition-all duration-150 active:scale-95 flex-1 max-w-[64px]"
      :class="isActive('/employee/profile') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800'"
    >
      <i class="bi bi-person-circle text-lg mb-0.5 transition-transform" :class="isActive('/employee/profile') ? 'scale-110' : ''"></i>
      <span class="text-[10px] tracking-tight truncate leading-none">Profil</span>
    </Link>
  </nav>
</template>
