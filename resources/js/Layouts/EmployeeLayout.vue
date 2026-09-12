<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import BottomNav from '@/Components/BottomNav.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash || {});
</script>

<template>
  <div class="min-vh-100 bg-body text-body">
    <!-- Desktop Sidebar -->
    <Sidebar />

    <!-- Mobile Top Header Bar -->
    <header class="d-md-none bg-secondary bg-opacity-10 border-bottom border-secondary border-opacity-25 px-3 py-2 sticky-top">
      <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <div class="bg-primary text-white rounded-2 p-1 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
            <i class="bi bi-clock-history fs-6"></i>
          </div>
          <div>
            <div class="fw-bold text-white small lh-1">Absensi Pro</div>
            <small class="text-secondary" style="font-size: 0.68rem;">{{ user?.employee?.position || 'Pegawai' }}</small>
          </div>
        </div>

        <Link :href="route('employee.profile')" class="d-flex align-items-center">
          <img 
            :src="user?.employee?.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user?.name || 'User') + '&background=6366f1&color=fff'" 
            class="rounded-circle border border-primary" 
            style="width: 32px; height: 32px; object-fit: cover;" 
          />
        </Link>
      </div>
    </header>

    <!-- Main Content Area -->
    <div class="main-content d-flex flex-column min-vh-100">
      <!-- Flash Notifications -->
      <div class="container-fluid px-3 px-md-4 mt-2">
        <div v-if="flash.success" class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-sm rounded-3" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i> {{ flash.success }}
          <button type="button" class="btn-close btn-close-white small py-2" data-bs-dismiss="alert"></button>
        </div>
        <div v-if="flash.error" class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-sm rounded-3" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ flash.error }}
          <button type="button" class="btn-close btn-close-white small py-2" data-bs-dismiss="alert"></button>
        </div>
      </div>

      <!-- Main Slot Content -->
      <main class="flex-grow-1 px-3 px-md-4 py-2">
        <slot />
      </main>

      <!-- Desktop Footer -->
      <footer class="d-none d-md-block text-center py-3 text-secondary border-top border-secondary border-opacity-25 mt-auto" style="font-size: 0.75rem;">
        &copy; {{ new Date().getFullYear() }} Absensi Pro — Sistem Absensi Kerja
      </footer>
    </div>

    <!-- Mobile Bottom Navigation Bar -->
    <BottomNav />
  </div>
</template>
