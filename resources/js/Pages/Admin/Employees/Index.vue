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

import ConfirmModal from '@/Components/ConfirmModal.vue';

const toggleForm = useForm({});
const showToggleModal = ref(false);
const employeeToToggle = ref(null);

const openToggle = (emp) => {
  employeeToToggle.value = emp;
  showToggleModal.value = true;
};

const confirmToggle = () => {
  if (!employeeToToggle.value) return;
  toggleForm.post(route('admin.employees.toggle-status', employeeToToggle.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showToggleModal.value = false;
      employeeToToggle.value = null;
    },
  });
};
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Pegawai" />

    <div class="space-y-6">
      <!-- Header & Actions Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
          <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Manajemen Data Pegawai</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola staf, penugasan shift kerja, dan status akses akun.</p>
          </div>
          <Link 
            :href="route('admin.employees.create')" 
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-600/20 transition-all cursor-pointer"
          >
            <i class="bi bi-person-plus-fill text-sm"></i>
            <span>Tambah Pegawai Baru</span>
          </Link>
        </div>

        <!-- Search and Filter Bar -->
        <div class="flex flex-col sm:flex-row gap-3 pt-4">
          <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
              <i class="bi bi-search text-xs"></i>
            </span>
            <input 
              v-model="search" 
              type="text" 
              class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
              placeholder="Cari nama, email, atau jabatan..." 
              @keyup.enter="applyFilter"
            />
          </div>

          <div class="flex items-center gap-2">
            <select 
              v-model="department" 
              class="px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
              @change="applyFilter"
            >
              <option value="all">Semua Departemen</option>
              <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
            </select>

            <button 
              type="button" 
              class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors cursor-pointer"
              @click="applyFilter"
            >
              Filter
            </button>
          </div>
        </div>
      </div>

      <!-- Employees List Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div v-if="employees && employees.length > 0">
          <!-- Mobile View: Card List -->
          <div class="md:hidden space-y-3">
            <div 
              v-for="emp in employees" 
              :key="emp.id" 
              class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-3"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <img 
                    :src="emp.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(emp.name) + '&background=2563eb&color=fff'" 
                    class="w-10 h-10 rounded-full object-cover ring-2 ring-white" 
                    alt="Avatar"
                  />
                  <div>
                    <div class="font-bold text-slate-900 text-xs">{{ emp.name }}</div>
                    <div class="text-[11px] text-slate-400">{{ emp.email }}</div>
                  </div>
                </div>

                <span 
                  class="px-2 py-0.5 text-[11px] font-semibold rounded-full border"
                  :class="emp.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                >
                  {{ emp.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
                <div>
                  <span class="text-slate-400 block">Jabatan:</span>
                  <span class="font-semibold text-slate-700">{{ emp.position }}</span>
                </div>
                <div>
                  <span class="text-slate-400 block">Departemen:</span>
                  <span class="font-semibold text-slate-700">{{ emp.department }}</span>
                </div>
                <div>
                  <span class="text-slate-400 block">Shift:</span>
                  <span class="font-semibold text-slate-700">{{ emp.current_schedule }}</span>
                </div>
                <div>
                  <span class="text-slate-400 block">Bergabung:</span>
                  <span class="font-semibold text-slate-700">{{ emp.joined_date || '-' }}</span>
                </div>
                <div>
                  <span class="text-slate-400 block">Hak Cuti:</span>
                  <span class="font-semibold text-slate-700 font-mono">{{ emp.annual_leave_quota }} Hari/Thn</span>
                </div>
              </div>

              <div class="flex justify-end gap-2 pt-2.5 border-t border-slate-200/60">
                <Link 
                  :href="route('admin.employees.edit', emp.id)" 
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors"
                >
                  <i class="bi bi-pencil mr-1"></i> Edit
                </Link>
                <button 
                  type="button" 
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors cursor-pointer"
                  :class="emp.is_active ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                  @click="openToggle(emp)"
                >
                  <i :class="emp.is_active ? 'bi bi-person-x' : 'bi bi-person-check'" class="mr-1"></i>
                  {{ emp.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop View: Table -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold">
                  <th class="pb-3 px-2">Pegawai</th>
                  <th class="pb-3 px-2">Jabatan</th>
                  <th class="pb-3 px-2">Departemen</th>
                  <th class="pb-3 px-2">Shift Aktif</th>
                  <th class="pb-3 px-2">Bergabung</th>
                  <th class="pb-3 px-2">Hak Cuti</th>
                  <th class="pb-3 px-2">Status</th>
                  <th class="pb-3 px-2 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="emp in employees" :key="emp.id" class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-2">
                    <div class="flex items-center gap-2.5">
                      <img 
                        :src="emp.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(emp.name) + '&background=2563eb&color=fff'" 
                        class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-200" 
                        alt="Avatar"
                      />
                      <div>
                        <div class="font-bold text-slate-900">{{ emp.name }}</div>
                        <div class="text-[11px] text-slate-400">{{ emp.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="py-3 px-2 font-semibold text-slate-800">{{ emp.position }}</td>
                  <td class="py-3 px-2 text-slate-600">{{ emp.department }}</td>
                  <td class="py-3 px-2">
                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-medium">
                      {{ emp.current_schedule }}
                    </span>
                  </td>
                  <td class="py-3 px-2 text-slate-500">{{ emp.joined_date || '-' }}</td>
                  <td class="py-3 px-2 font-mono font-semibold text-slate-700">{{ emp.annual_leave_quota }} Hari</td>
                  <td class="py-3 px-2">
                    <span 
                      class="px-2.5 py-0.5 text-xs font-semibold rounded-full border"
                      :class="emp.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                    >
                      {{ emp.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="py-3 px-2 text-right">
                    <div class="inline-flex gap-1.5">
                      <Link 
                        :href="route('admin.employees.edit', emp.id)" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors"
                      >
                        <i class="bi bi-pencil"></i>
                        <span>Edit</span>
                      </Link>
                      <button 
                        type="button" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors cursor-pointer"
                        :class="emp.is_active ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                        @click="openToggle(emp)"
                      >
                        <i :class="emp.is_active ? 'bi bi-person-x' : 'bi bi-person-check'"></i>
                        <span>{{ emp.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else class="text-center py-12 text-slate-400 text-xs">
          Tidak ditemukan data pegawai yang sesuai dengan kriteria pencarian.
        </div>
      </div>
    </div>

    <!-- Toggle Status Confirmation Modal -->
    <ConfirmModal 
      :show="showToggleModal"
      :title="employeeToToggle?.is_active ? 'Nonaktifkan Akun Pegawai?' : 'Aktifkan Akun Pegawai?'"
      :message="employeeToToggle ? (employeeToToggle.is_active ? `Apakah Anda yakin ingin menonaktifkan akun '${employeeToToggle.name}'? Pegawai tidak akan dapat masuk ke aplikasi hingga diaktifkan kembali.` : `Aktifkan kembali akses akun '${employeeToToggle.name}'? Pegawai akan dapat login dan melakukan presensi seperti biasa.`) : ''"
      :confirm-text="employeeToToggle?.is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"
      cancel-text="Batal"
      :type="employeeToToggle?.is_active ? 'danger' : 'success'"
      :loading="toggleForm.processing"
      @close="showToggleModal = false"
      @confirm="confirmToggle"
    />
  </AdminLayout>
</template>
