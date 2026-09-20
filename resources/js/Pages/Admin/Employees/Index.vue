<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import EmployeeDetailModal from '@/Components/EmployeeDetailModal.vue';

const props = defineProps({
  employees: Object, // Paginated { data: [], links: [], ... }
  departments: Array,
  regions: Object,
  employmentStatuses: Object,
  positions: Object,
  filters: Object,
  totalStats: Object,
});

const search = ref(props.filters.search || '');
const department = ref(props.filters.department || 'all');
const region = ref(props.filters.region || 'all');
const employmentStatus = ref(props.filters.employment_status || 'all');
const position = ref(props.filters.position || 'all');

const applyFilter = () => {
  router.get(route('admin.employees.index'), {
    search: search.value,
    department: department.value,
    region: region.value,
    employment_status: employmentStatus.value,
    position: position.value,
  }, { preserveState: true, replace: true });
};

const resetFilter = () => {
  search.value = '';
  department.value = 'all';
  region.value = 'all';
  employmentStatus.value = 'all';
  position.value = 'all';
  applyFilter();
};

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

const showDetailModal = ref(false);
const selectedEmployeeId = ref(null);

const openDetail = (empId) => {
  selectedEmployeeId.value = empId;
  showDetailModal.value = true;
};

const onAvatarError = (e, name) => {
  const initial = (name || 'U').charAt(0).toUpperCase();
  e.target.src = `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"><rect width="40" height="40" fill="%232563eb" rx="20"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="16" font-weight="bold" font-family="sans-serif">${initial}</text></svg>`;
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'tetap':
      return 'bg-blue-50 text-blue-700 border-blue-200';
    case 'vendor':
      return 'bg-purple-50 text-purple-700 border-purple-200';
    case 'magang':
      return 'bg-amber-50 text-amber-700 border-amber-200';
    default:
      return 'bg-slate-50 text-slate-700 border-slate-200';
  }
};

const getRegionBadgeClass = (reg) => {
  switch (reg) {
    case 'jakarta_timur':
      return 'bg-sky-50 text-sky-700 border-sky-200';
    case 'jakarta_barat':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    case 'jakarta_pusat':
      return 'bg-blue-50 text-blue-700 border-blue-200';
    case 'jakarta_utara':
      return 'bg-purple-50 text-purple-700 border-purple-200';
    case 'jakarta_selatan':
      return 'bg-amber-50 text-amber-700 border-amber-200';
    default:
      return 'bg-slate-50 text-slate-700 border-slate-200';
  }
};
</script>

<template>
  <AdminLayout>
    <Head title="Data Pegawai Transjakarta" />

    <div class="space-y-6">
      <!-- Header & Quick KPI Metric Cards -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Manajemen Staf & Pegawai Transjakarta</h1>
              <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-md bg-blue-600 text-white tracking-wide uppercase">
                Transjakarta
              </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Kelola penempatan wilayah kerja, status kepegawaian (Tetap, Vendor, Magang), dan shift operasional.</p>
          </div>
          <Link 
            :href="route('admin.employees.create')" 
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-600/20 transition-all cursor-pointer shrink-0"
          >
            <i class="bi bi-person-plus-fill text-sm"></i>
            <span>Tambah Pegawai Baru</span>
          </Link>
        </div>

        <!-- 4 Quick KPI Summary Pills -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 pt-5">
          <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Karyawan</div>
            <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">
              {{ totalStats?.total?.toLocaleString('id-ID') || 3520 }}
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">Seluruh 4 Wilayah DKI</div>
          </div>

          <div class="p-3.5 rounded-2xl bg-blue-50/50 border border-blue-100/70">
            <div class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Karyawan Tetap (PKWTT)</div>
            <div class="text-xl sm:text-2xl font-black text-blue-900 mt-0.5">
              {{ totalStats?.tetap?.toLocaleString('id-ID') || 1300 }}
            </div>
            <div class="text-[11px] text-blue-700 mt-0.5">Pegawai Inti Transjakarta</div>
          </div>

          <div class="p-3.5 rounded-2xl bg-purple-50/50 border border-purple-100/70">
            <div class="text-[10px] font-bold text-purple-600 uppercase tracking-wider">Vendor / Mitra (PKWT)</div>
            <div class="text-xl sm:text-2xl font-black text-purple-900 mt-0.5">
              {{ totalStats?.vendor?.toLocaleString('id-ID') || 2100 }}
            </div>
            <div class="text-[11px] text-purple-700 mt-0.5">Mitra Operator & Alih Daya</div>
          </div>

          <div class="p-3.5 rounded-2xl bg-amber-50/50 border border-amber-100/70">
            <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Karyawan Magang</div>
            <div class="text-xl sm:text-2xl font-black text-amber-900 mt-0.5">
              {{ totalStats?.magang?.toLocaleString('id-ID') || 120 }}
            </div>
            <div class="text-[11px] text-amber-700 mt-0.5">Program Internship Operasional</div>
          </div>
        </div>
      </div>

      <!-- Search and Filter Bar Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2.5 text-xs">
          <!-- Keyword Search -->
          <div class="lg:col-span-2 relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
              <i class="bi bi-search text-xs"></i>
            </span>
            <input 
              v-model="search" 
              type="text" 
              class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
              placeholder="Cari nama, email, phone, pool..." 
              @keyup.enter="applyFilter"
            />
          </div>

          <!-- Wilayah Filter -->
          <div>
            <select 
              v-model="region" 
              class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
              @change="applyFilter"
            >
              <option value="all">Semua Wilayah</option>
              <option v-for="(name, key) in regions" :key="key" :value="key">{{ name }}</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <select 
              v-model="employmentStatus" 
              class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
              @change="applyFilter"
            >
              <option value="all">Semua Status Kerja</option>
              <option v-for="(name, key) in employmentStatuses" :key="key" :value="key">{{ name }}</option>
            </select>
          </div>

          <!-- Profesi Filter -->
          <div>
            <select 
              v-model="position" 
              class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
              @change="applyFilter"
            >
              <option value="all">Semua Profesi</option>
              <option v-for="(label, key) in positions" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2">
            <button 
              type="button" 
              class="flex-1 px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition-colors cursor-pointer shadow-xs text-center"
              @click="applyFilter"
            >
              Filter
            </button>
            <button 
              type="button" 
              class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold transition-colors cursor-pointer text-center"
              title="Reset Filter"
              @click="resetFilter"
            >
              <i class="bi bi-arrow-counterclockwise"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Employees List Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div v-if="employees?.data && employees.data.length > 0">
          <!-- Mobile View: Card List -->
          <div class="md:hidden space-y-3">
            <div 
              v-for="emp in employees.data" 
              :key="emp.id" 
              class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-3"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <img 
                    :src="emp.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(emp.name) + '&background=2563eb&color=fff'" 
                    class="w-10 h-10 rounded-full object-cover ring-2 ring-white" 
                    alt="Avatar"
                    @error="onAvatarError($event, emp.name)"
                  />
                  <div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span class="font-bold text-slate-900 text-xs">{{ emp.name }}</span>
                      <span v-if="emp.employee_code" class="px-1.5 py-0.2 text-[10px] font-mono font-bold bg-slate-100 text-slate-700 rounded border border-slate-200">
                        {{ emp.employee_code }}
                      </span>
                    </div>
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

              <!-- Wilayah & Status Badges -->
              <div class="flex items-center gap-1.5 flex-wrap text-[11px]">
                <span class="px-2 py-0.5 font-bold rounded-lg border" :class="getRegionBadgeClass(emp.region)">
                  <i class="bi bi-geo-alt-fill mr-0.5"></i>
                  {{ emp.region_label }}
                </span>
                <span class="px-2 py-0.5 font-bold rounded-lg border" :class="getStatusBadgeClass(emp.employment_status)">
                  {{ emp.employment_status_label }}
                </span>
                <span v-if="emp.pool_depot" class="px-2 py-0.5 font-medium rounded-lg bg-slate-200 text-slate-700">
                  {{ emp.pool_depot }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
                <div>
                  <span class="text-slate-400 block">Profesi:</span>
                  <span class="font-bold text-slate-800">{{ emp.position }}</span>
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
                <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold text-[11px]">
                  <th class="pb-3 px-3">Pegawai</th>
                  <th class="pb-3 px-3">Wilayah & Pool Depo</th>
                  <th class="pb-3 px-3">Status & Profesi</th>
                  <th class="pb-3 px-3">Departemen</th>
                  <th class="pb-3 px-3">Shift Aktif</th>
                  <th class="pb-3 px-3">Status Akun</th>
                  <th class="pb-3 px-3 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="emp in employees.data" :key="emp.id" class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3.5 px-3">
                    <div class="flex items-center gap-2.5">
                      <img 
                        :src="emp.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(emp.name) + '&background=2563eb&color=fff'" 
                        class="w-9 h-9 rounded-full object-cover ring-1 ring-slate-200 shrink-0" 
                        alt="Avatar"
                        @error="onAvatarError($event, emp.name)"
                      />
                      <div class="cursor-pointer group" @click="openDetail(emp.id)">
                        <div class="flex items-center gap-1.5 flex-wrap">
                          <span class="font-bold text-slate-900 text-xs group-hover:text-blue-600 transition-colors">{{ emp.name }}</span>
                          <span v-if="emp.employee_code" class="px-1.5 py-0.5 text-[10px] font-mono font-bold bg-slate-100 text-slate-700 rounded border border-slate-200">
                            {{ emp.employee_code }}
                          </span>
                        </div>
                        <div class="text-[11px] text-slate-400">{{ emp.email }} &bull; {{ emp.phone || '-' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="py-3.5 px-3">
                    <div class="space-y-1">
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 font-bold rounded-lg text-[10px] border" :class="getRegionBadgeClass(emp.region)">
                        <i class="bi bi-geo-alt-fill text-[9px]"></i>
                        {{ emp.region_label }}
                      </span>
                      <div class="text-[11px] text-slate-600 font-medium">
                        {{ emp.pool_depot || '-' }}
                      </div>
                    </div>
                  </td>
                  <td class="py-3.5 px-3">
                    <div class="space-y-1">
                      <span class="inline-block px-2.5 py-0.5 font-bold rounded-lg text-[10px] border" :class="getStatusBadgeClass(emp.employment_status)">
                        {{ emp.employment_status_label }}
                      </span>
                      <div class="font-bold text-slate-900 text-xs">
                        {{ emp.position }}
                      </div>
                    </div>
                  </td>
                  <td class="py-3.5 px-3 text-slate-600 font-medium">{{ emp.department }}</td>
                  <td class="py-3.5 px-3">
                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium text-[11px]">
                      {{ emp.current_schedule }}
                    </span>
                  </td>
                  <td class="py-3.5 px-3">
                    <span 
                      class="px-2.5 py-0.5 text-[11px] font-semibold rounded-full border"
                      :class="emp.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                    >
                      {{ emp.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="py-3.5 px-3 text-right">
                    <div class="inline-flex items-center gap-1.5">
                      <button 
                        type="button" 
                        @click="openDetail(emp.id)" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors cursor-pointer"
                        title="Lihat Rekapan Profil & Rapor Karyawan"
                      >
                        <i class="bi bi-window-sidebar"></i>
                        <span>Rapor</span>
                      </button>
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
                        <span>{{ emp.is_active ? 'Nonaktif' : 'Aktif' }}</span>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Bottom Pagination Controls -->
          <Pagination 
            :links="employees.links" 
            :from="employees.from" 
            :to="employees.to" 
            :total="employees.total" 
          />
        </div>
        <div v-else class="text-center py-14 text-slate-400 text-xs">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <i class="bi bi-people text-xl"></i>
          </div>
          <p class="font-bold text-slate-700">Tidak ada pegawai yang ditemukan</p>
          <p class="text-[11px] text-slate-400 mt-0.5">Silakan sesuaikan filter pencarian atau wilayah kerja yang dipilih.</p>
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

    <!-- Employee 360 Recap Detail Modal -->
    <EmployeeDetailModal
      :show="showDetailModal"
      :employee-id="selectedEmployeeId"
      @close="showDetailModal = false"
    />
  </AdminLayout>
</template>
