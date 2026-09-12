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
  <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 shadow-[0_-4px_20px_rgba(0,0,0,0.04)] md:hidden px-2 py-1.5 flex items-center justify-around">
    <!-- 1. Beranda / Absensi -->
    <Link 
      :href="route('employee.attendance')" 
      class="relative flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200"
      :class="currentUrl === '/employee/attendance' ? 'text-blue-600 font-semibold scale-105' : 'text-slate-500 hover:text-slate-800'"
    >
      <i class="bi bi-geo-alt-fill text-xl mb-0.5"></i>
      <span class="text-[11px] tracking-tight">Absen</span>
    </Link>

    <!-- 2. Riwayat -->
    <Link 
      :href="route('employee.history')" 
      class="relative flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200"
      :class="isActive('/employee/history') ? 'text-blue-600 font-semibold scale-105' : 'text-slate-500 hover:text-slate-800'"
    >
      <i class="bi bi-calendar2-check-fill text-xl mb-0.5"></i>
      <span class="text-[11px] tracking-tight">Riwayat</span>
    </Link>

    <!-- 3. Pengajuan Cuti/Izin -->
    <Link 
      :href="route('employee.leave-requests.index')" 
      class="relative flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200"
      :class="isActive('/employee/leave-requests') ? 'text-blue-600 font-semibold scale-105' : 'text-slate-500 hover:text-slate-800'"
    >
      <i class="bi bi-file-earmark-medical-fill text-xl mb-0.5"></i>
      <span class="text-[11px] tracking-tight">Pengajuan</span>
      <span v-if="pendingCounts.leaveRequests > 0" class="absolute top-1 right-2.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
    </Link>

    <!-- 4. Komplain -->
    <Link 
      :href="route('employee.complaints.index')" 
      class="relative flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200"
      :class="isActive('/employee/complaints') ? 'text-blue-600 font-semibold scale-105' : 'text-slate-500 hover:text-slate-800'"
    >
      <i class="bi bi-chat-dots-fill text-xl mb-0.5"></i>
      <span class="text-[11px] tracking-tight">Komplain</span>
      <span v-if="pendingCounts.complaints > 0" class="absolute top-1 right-2.5 w-2 h-2 rounded-full bg-amber-500 ring-2 ring-white"></span>
    </Link>

    <!-- 5. Profil -->
    <Link 
      :href="route('employee.profile')" 
      class="relative flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200"
      :class="isActive('/employee/profile') ? 'text-blue-600 font-semibold scale-105' : 'text-slate-500 hover:text-slate-800'"
    >
      <i class="bi bi-person-circle text-xl mb-0.5"></i>
      <span class="text-[11px] tracking-tight">Profil</span>
    </Link>
  </nav>
</template>
