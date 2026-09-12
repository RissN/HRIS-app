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
  <nav class="bottom-nav d-md-none">
    <!-- 1. Beranda / Absensi -->
    <Link 
      :href="route('employee.attendance')" 
      class="bottom-nav-item" 
      :class="{ active: currentUrl === '/employee/attendance' }"
    >
      <i class="bi bi-geo-alt-fill"></i>
      <span>Absen</span>
    </Link>

    <!-- 2. Riwayat -->
    <Link 
      :href="route('employee.history')" 
      class="bottom-nav-item" 
      :class="{ active: isActive('/employee/history') }"
    >
      <i class="bi bi-calendar2-check-fill"></i>
      <span>Riwayat</span>
    </Link>

    <!-- 3. Pengajuan Cuti/Izin -->
    <Link 
      :href="route('employee.leave-requests.index')" 
      class="bottom-nav-item" 
      :class="{ active: isActive('/employee/leave-requests') }"
    >
      <i class="bi bi-file-earmark-medical-fill"></i>
      <span>Pengajuan</span>
      <span v-if="pendingCounts.leaveRequests > 0" class="badge-dot"></span>
    </Link>

    <!-- 4. Komplain -->
    <Link 
      :href="route('employee.complaints.index')" 
      class="bottom-nav-item" 
      :class="{ active: isActive('/employee/complaints') }"
    >
      <i class="bi bi-chat-dots-fill"></i>
      <span>Komplain</span>
      <span v-if="pendingCounts.complaints > 0" class="badge-dot"></span>
    </Link>

    <!-- 5. Profil -->
    <Link 
      :href="route('employee.profile')" 
      class="bottom-nav-item" 
      :class="{ active: isActive('/employee/profile') }"
    >
      <i class="bi bi-person-circle"></i>
      <span>Profil</span>
    </Link>
  </nav>
</template>
