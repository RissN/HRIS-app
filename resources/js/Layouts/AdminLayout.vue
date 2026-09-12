<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash || {});
const pendingCounts = computed(() => page.props.pendingCounts || { leaveRequests: 0, complaints: 0 });
</script>

<template>
  <div class="min-vh-100 bg-body text-body">
    <!-- Desktop Sidebar -->
    <Sidebar />

    <!-- Main Content Area -->
    <div class="main-content d-flex flex-column min-vh-100">
      <!-- Top Header Navbar -->
      <header class="navbar navbar-expand bg-secondary bg-opacity-10 border-bottom border-secondary border-opacity-25 px-3 px-md-4 py-2 sticky-top">
        <div class="container-fluid p-0">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary px-2 py-1 small">Admin HR</span>
            <span class="text-secondary small d-none d-sm-inline">Panel Manajemen Presensi</span>
          </div>

          <!-- Top Right Actions -->
          <div class="d-flex align-items-center gap-3">
            <!-- Pending Leave Requests Quick Badge -->
            <Link 
              :href="route('admin.leave-requests.index')" 
              class="btn btn-sm btn-dark position-relative border border-secondary border-opacity-25 px-2 py-1"
              title="Pengajuan Cuti Pending"
            >
              <i class="bi bi-file-earmark-medical text-warning"></i>
              <span v-if="pendingCounts.leaveRequests > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                {{ pendingCounts.leaveRequests }}
              </span>
            </Link>

            <!-- Pending Complaints Quick Badge -->
            <Link 
              :href="route('admin.complaints.index')" 
              class="btn btn-sm btn-dark position-relative border border-secondary border-opacity-25 px-2 py-1"
              title="Komplain Pending"
            >
              <i class="bi bi-chat-dots text-danger"></i>
              <span v-if="pendingCounts.complaints > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                {{ pendingCounts.complaints }}
              </span>
            </Link>

            <!-- User Dropdown / Logout -->
            <div class="dropdown">
              <button 
                class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" 
                type="button" 
                data-bs-toggle="dropdown" 
                aria-expanded="false"
              >
                <img 
                  :src="user?.employee?.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user?.name || 'Admin') + '&background=6366f1&color=fff'" 
                  class="rounded-circle" 
                  style="width: 24px; height: 24px;" 
                />
                <span class="small text-white fw-medium">{{ user?.name }}</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow border border-secondary border-opacity-25">
                <li><h6 class="dropdown-header small">{{ user?.email }}</h6></li>
                <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
                <li>
                  <Link :href="route('logout')" method="post" as="button" class="dropdown-item text-danger small">
                    <i class="bi bi-box-arrow-right me-2"></i> Keluar
                  </Link>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </header>

      <!-- Flash Notifications -->
      <div class="container-fluid px-3 px-md-4 mt-3">
        <div v-if="flash.success" class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-sm" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i> {{ flash.success }}
          <button type="button" class="btn-close btn-close-white small py-2" data-bs-dismiss="alert"></button>
        </div>
        <div v-if="flash.error" class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-sm" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ flash.error }}
          <button type="button" class="btn-close btn-close-white small py-2" data-bs-dismiss="alert"></button>
        </div>
      </div>

      <!-- Main Slot -->
      <main class="flex-grow-1 px-3 px-md-4 py-2">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="text-center py-3 text-secondary border-top border-secondary border-opacity-25 mt-auto" style="font-size: 0.75rem;">
        &copy; {{ new Date().getFullYear() }} Absensi Pro — Panel Administrasi & HR
      </footer>
    </div>
  </div>
</template>
