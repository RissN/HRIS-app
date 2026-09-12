<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  employees: Array,
  departments: Array,
  filters: Object,
});

const search = ref(props.filters.search || '');
const department = ref(props.filters.department || 'all');

const applyFilter = () => {
  router.get(route('admin.employees.index'), {
    search: search.value,
    department: department.value,
  }, { preserveState: true });
};

const toggleForm = useForm({});

const toggleStatus = (emp) => {
  const action = emp.is_active ? 'menonaktifkan' : 'mengaktifkan';
  if (confirm(`Apakah Anda yakin ingin ${action} akun ${emp.name}?`)) {
    toggleForm.post(route('admin.employees.toggle-status', emp.id), {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Pegawai" />

    <!-- Page Header & Actions -->
    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4 mb-4">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h4 class="fw-bold text-white mb-1">Manajemen Data Pegawai</h4>
          <p class="text-secondary small mb-0">Kelola informasi staf, penugasan shift kerja, dan status akses akun.</p>
        </div>
        <Link :href="route('admin.employees.create')" class="btn btn-primary btn-sm px-3 py-2 d-inline-flex align-items-center gap-2 shadow">
          <i class="bi bi-person-plus-fill"></i>
          <span>Tambah Pegawai Baru</span>
        </Link>
      </div>

      <!-- Search and Filter Bar -->
      <div class="row g-2 mt-3 pt-3 border-top border-secondary border-opacity-25">
        <div class="col-12 col-md-6">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary">
              <i class="bi bi-search"></i>
            </span>
            <input 
              v-model="search" 
              type="text" 
              class="form-control" 
              placeholder="Cari nama, email, atau jabatan..." 
              @keyup.enter="applyFilter"
            />
            <button class="btn btn-outline-secondary" type="button" @click="applyFilter">Cari</button>
          </div>
        </div>

        <div class="col-12 col-md-6 d-flex justify-content-md-end">
          <select v-model="department" class="form-select form-select-sm" style="max-width: 220px;" @change="applyFilter">
            <option value="all">Semua Departemen</option>
            <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Employees Table -->
    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4">
      <div v-if="employees && employees.length > 0" class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
          <thead>
            <tr class="text-secondary border-bottom border-secondary border-opacity-25">
              <th>Pegawai</th>
              <th>Jabatan</th>
              <th>Departemen</th>
              <th>Shift Aktif</th>
              <th>Bergabung</th>
              <th>Status</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="emp in employees" :key="emp.id" class="border-bottom border-secondary border-opacity-10">
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img 
                    :src="emp.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(emp.name) + '&background=6366f1&color=fff'" 
                    class="rounded-circle" 
                    style="width: 34px; height: 34px; object-fit: cover;" 
                  />
                  <div>
                    <div class="fw-bold text-white">{{ emp.name }}</div>
                    <small class="text-secondary">{{ emp.email }}</small>
                  </div>
                </div>
              </td>
              <td class="text-white">{{ emp.position }}</td>
              <td class="text-secondary">{{ emp.department }}</td>
              <td>
                <span class="badge bg-secondary bg-opacity-25 text-white">
                  {{ emp.current_schedule }}
                </span>
              </td>
              <td class="text-secondary">{{ emp.joined_date || '-' }}</td>
              <td>
                <span class="badge" :class="emp.is_active ? 'bg-success' : 'bg-danger'">
                  {{ emp.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-2">
                  <Link :href="route('admin.employees.edit', emp.id)" class="btn btn-sm btn-outline-info py-1 px-2" style="font-size: 0.75rem;">
                    <i class="bi bi-pencil"></i> Edit
                  </Link>
                  <button 
                    type="button" 
                    class="btn btn-sm py-1 px-2" 
                    :class="emp.is_active ? 'btn-outline-danger' : 'btn-outline-success'"
                    style="font-size: 0.75rem;"
                    @click="toggleStatus(emp)"
                  >
                    <i :class="emp.is_active ? 'bi bi-person-x' : 'bi bi-person-check'"></i>
                    {{ emp.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="text-center py-5 text-secondary">
        Tidak ditemukan data pegawai yang sesuai dengan pencarian.
      </div>
    </div>
  </AdminLayout>
</template>
