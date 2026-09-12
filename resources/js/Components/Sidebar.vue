<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');
const pendingCounts = computed(() => page.props.pendingCounts || { leaveRequests: 0, complaints: 0 });

const currentRoute = computed(() => page.url);

const isUrlActive = (path) => {
  return currentRoute.value.startsWith(path);
};
</script>

<template>
  <aside class="sidebar-wrapper d-none d-md-flex flex-column">
    <!-- Brand -->
    <div class="sidebar-brand">
      <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
        <i class="bi bi-clock-history fs-5"></i>
      </div>
      <div>
        <div class="fw-bold fs-6 text-white lh-1">Absensi Pro</div>
        <small class="text-secondary" style="font-size: 0.72rem;">Sistem Presensi Kerja</small>
      </div>
    </div>

    <!-- User Mini Profile -->
    <div class="px-3 py-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center gap-3">
      <div class="position-relative">
        <img 
          :src="user?.employee?.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user?.name || 'User') + '&background=6366f1&color=fff'" 
          alt="Avatar" 
          class="rounded-circle object-fit-cover border border-secondary"
          style="width: 44px; height: 44px;"
        />
        <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-dark rounded-circle"></span>
      </div>
      <div class="overflow-hidden">
        <div class="fw-semibold text-truncate text-white small">{{ user?.name }}</div>
        <div class="text-secondary small text-truncate" style="font-size: 0.75rem;">
          {{ user?.employee?.position || (isAdmin ? 'Administrator' : 'Pegawai') }}
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-nav flex-grow-1 overflow-y-auto">
      <div class="text-uppercase text-secondary fw-bold px-3 pt-3 pb-1" style="font-size: 0.68rem; letter-spacing: 0.06em;">
        Menu Utama
      </div>

      <!-- Admin Menu -->
      <template v-if="isAdmin">
        <Link 
          :href="route('admin.dashboard')" 
          class="nav-link" 
          :class="{ active: isUrlActive('/admin/dashboard') }"
        >
          <i class="bi bi-grid-1x2-fill"></i>
          <span>Dashboard</span>
        </Link>

        <Link 
          :href="route('admin.employees.index')" 
          class="nav-link" 
          :class="{ active: isUrlActive('/admin/employees') }"
        >
          <i class="bi bi-people-fill"></i>
          <span>Data Pegawai</span>
        </Link>

        <Link 
          :href="route('admin.schedules.index')" 
          class="nav-link" 
          :class="{ active: isUrlActive('/admin/schedules') }"
        >
          <i class="bi bi-calendar-range-fill"></i>
          <span>Jadwal Kerja</span>
        </Link>

        <Link 
          :href="route('admin.attendance.index')" 
          class="nav-link" 
          :class="{ active: isUrlActive('/admin/attendance') }"
        >
          <i class="bi bi-calendar-check-fill"></i>
          <span>Monitoring Absensi</span>
        </Link>

        <div class="text-uppercase text-secondary fw-bold px-3 pt-3 pb-1" style="font-size: 0.68rem; letter-spacing: 0.06em;">
          Verifikasi & Komplain
        </div>

        <Link 
          :href="route('admin.leave-requests.index')" 
          class="nav-link justify-content-between" 
          :class="{ active: isUrlActive('/admin/leave-requests') }"
        >
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-text-fill"></i>
            <span>Pengajuan Cuti</span>
          </div>
          <span v-if="pendingCounts.leaveRequests > 0" class="badge bg-warning text-dark rounded-pill px-2">
            {{ pendingCounts.leaveRequests }}
          </span>
        </Link>

        <Link 
          :href="route('admin.complaints.index')" 
          class="nav-link justify-content-between" 
          :class="{ active: isUrlActive('/admin/complaints') }"
        >
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-chat-left-dots-fill"></i>
            <span>Komplain Absensi</span>
          </div>
          <span v-if="pendingCounts.complaints > 0" class="badge bg-danger rounded-pill px-2">
            {{ pendingCounts.complaints }}
          </span>
        </Link>
      </template>

      <!-- Employee Menu -->
      <template v-else>
        <Link 
          :href="route('employee.attendance')" 
          class="nav-link" 
          :class="{ active: currentRoute === '/employee/attendance' }"
        >
          <i class="bi bi-geo-alt-fill"></i>
          <span>Absensi Hari Ini</span>
        </Link>

        <Link 
          :href="route('employee.history')" 
          class="nav-link" 
          :class="{ active: isUrlActive('/employee/history') }"
        >
          <i class="bi bi-calendar-week-fill"></i>
          <span>Riwayat Absensi</span>
        </Link>

        <Link 
          :href="route('employee.leave-requests.index')" 
          class="nav-link justify-content-between" 
          :class="{ active: isUrlActive('/employee/leave-requests') }"
        >
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-medical-fill"></i>
            <span>Pengajuan Izin/Cuti</span>
          </div>
          <span v-if="pendingCounts.leaveRequests > 0" class="badge bg-warning text-dark rounded-pill px-2">
            {{ pendingCounts.leaveRequests }}
          </span>
        </Link>

        <Link 
          :href="route('employee.complaints.index')" 
          class="nav-link justify-content-between" 
          :class="{ active: isUrlActive('/employee/complaints') }"
        >
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-chat-dots-fill"></i>
            <span>Komplain Absensi</span>
          </div>
          <span v-if="pendingCounts.complaints > 0" class="badge bg-danger rounded-pill px-2">
            {{ pendingCounts.complaints }}
          </span>
        </Link>

        <Link 
          :href="route('employee.profile')" 
          class="nav-link" 
          :class="{ active: isUrlActive('/employee/profile') }"
        >
          <i class="bi bi-person-fill"></i>
          <span>Profil Saya</span>
        </Link>
      </template>
    </div>

    <!-- Bottom Logout -->
    <div class="p-3 border-top border-secondary border-opacity-25">
      <Link 
        :href="route('logout')" 
        method="post" 
        as="button" 
        class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2 py-2 small"
      >
        <i class="bi bi-box-arrow-right"></i>
        <span>Keluar Sistem</span>
      </Link>
    </div>
  </aside>
</template>
