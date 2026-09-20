<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  employee: Object,
  schedules: Array,
  regions: Object,
  employmentStatuses: Object,
  positions: Object,
  pools: Object,
});

const form = useForm({
  name: props.employee.name,
  email: props.employee.email,
  password: '',
  phone: props.employee.phone || '',
  employee_code: props.employee.employee_code || '',
  position: props.employee.position,
  department: props.employee.department,
  region: props.employee.region || 'jakarta_pusat',
  employment_status: props.employee.employment_status || 'tetap',
  pool_depot: props.employee.pool_depot || '',
  bank_name: props.employee.bank_name || '',
  account_number: props.employee.account_number || '',
  annual_leave_quota: props.employee.annual_leave_quota ?? 12,
  joined_date: props.employee.joined_date,
  schedule_id: props.employee.schedule_id || (props.schedules?.[0]?.id || ''),
  is_active: props.employee.is_active,
});

// Available pools based on selected region
const availablePools = computed(() => {
  if (props.pools && props.pools[form.region]) {
    return props.pools[form.region];
  }
  return [];
});

const onRegionChange = () => {
  if (availablePools.value && availablePools.value.length > 0) {
    form.pool_depot = availablePools.value[0];
  }
};

const selectPresetPosition = (posKey) => {
  form.position = posKey;
  if (posKey === 'Pramudi') {
    form.department = form.employment_status === 'vendor' ? 'Mitra Operator Bus' : 'Operasional Bus';
  } else if (posKey === 'Pramusapa') {
    form.department = form.employment_status === 'vendor' ? 'Layanan Bus Vendor' : 'Layanan Pelanggan';
  } else if (posKey === 'Pramujaga') {
    form.department = form.employment_status === 'vendor' ? 'Pengamanan Vendor' : 'Pengamanan & Jalur';
  } else if (posKey === 'Karyawan Kantor') {
    form.department = form.employment_status === 'magang' ? 'Operasional & Layanan Magang' : 'Manajemen & Pool';
  }
};

const submit = () => {
  form.clearErrors();
  let hasError = false;

  if (!form.name || !form.name.trim()) {
    form.setError('name', 'Nama lengkap pegawai wajib diisi');
    hasError = true;
  }
  if (!form.email || !form.email.trim()) {
    form.setError('email', 'Alamat email wajib diisi');
    hasError = true;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    form.setError('email', 'Format email tidak valid');
    hasError = true;
  }
  if (form.password && form.password.length < 8) {
    form.setError('password', 'Kata sandi minimal 8 karakter');
    hasError = true;
  }
  if (!form.region) {
    form.setError('region', 'Wilayah penempatan wajib dipilih');
    hasError = true;
  }
  if (!form.employment_status) {
    form.setError('employment_status', 'Status kepegawaian wajib dipilih');
    hasError = true;
  }
  if (!form.position || !form.position.trim()) {
    form.setError('position', 'Jabatan pegawai wajib diisi');
    hasError = true;
  }
  if (!form.department || !form.department.trim()) {
    form.setError('department', 'Divisi / departemen wajib diisi');
    hasError = true;
  }
  if (!form.schedule_id) {
    form.setError('schedule_id', 'Shift jadwal kerja wajib dipilih');
    hasError = true;
  }
  if (!form.joined_date) {
    form.setError('joined_date', 'Tanggal bergabung wajib diisi');
    hasError = true;
  }

  if (hasError) return;

  form.put(route('admin.employees.update', props.employee.id));
};
</script>

<template>
  <AdminLayout>
    <Head title="Edit Data Pegawai Transjakarta" />

    <div class="max-w-4xl mx-auto">
      <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-8 shadow-xs">
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
              <i class="bi bi-pencil-square text-lg"></i>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h1 class="text-base font-bold text-slate-900 leading-tight">Ubah Data Pegawai</h1>
                <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-md bg-blue-100 text-blue-700 uppercase">
                  Transjakarta
                </span>
              </div>
              <p class="text-xs text-slate-400">{{ employee.name }} &bull; {{ employee.email }}</p>
            </div>
          </div>
          <Link 
            :href="route('admin.employees.index')" 
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-xl transition-colors"
          >
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
          </Link>
        </div>

        <form novalidate @submit.prevent="submit" class="space-y-6">
          <!-- Section 1: Akun & Kredensial -->
          <div>
            <h6 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-3">
              1. Akun & Kredensial
            </h6>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nama Lengkap *</label>
                <input 
                  v-model="form.name" 
                  @input="form.clearErrors('name')"
                  type="text" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
                />
                <div v-if="form.errors.name" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.name }}</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Alamat Email *</label>
                <input 
                  v-model="form.email" 
                  @input="form.clearErrors('email')"
                  type="email" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
                />
                <div v-if="form.errors.email" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.email }}</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Ubah Kata Sandi</label>
                <input 
                  v-model="form.password" 
                  @input="form.clearErrors('password')"
                  type="password" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
                  placeholder="Kosongkan jika tidak ingin mengubah"
                />
                <span class="text-[11px] text-slate-400 block mt-1">Kosongkan jika tidak ingin mengubah kata sandi</span>
                <div v-if="form.errors.password" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.password }}</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nomor WhatsApp / HP</label>
                <input 
                  v-model="form.phone" 
                  @input="form.clearErrors('phone')"
                  type="text" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
                  placeholder="0812xxxxxxxx" 
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">ID Karyawan / NIP Transjakarta</label>
                <input 
                  v-model="form.employee_code" 
                  @input="form.clearErrors('employee_code')"
                  type="text" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all uppercase" 
                  placeholder="Contoh: TJT1001 / TJV2001 / TJB1188" 
                />
                <span class="text-[11px] text-slate-400 block mt-1">Format: <strong>TJT</strong> (Tetap), <strong>TJV</strong> (Vendor), <strong>TJB</strong> (Magang)</span>
                <div v-if="form.errors.employee_code" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.employee_code }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 2: Penempatan Wilayah & Status Operasional Transjakarta -->
          <div class="pt-4 border-t border-slate-100">
            <h6 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-3">
              2. Penempatan Wilayah & Status Transjakarta
            </h6>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <!-- Wilayah Penempatan -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Wilayah Penempatan *</label>
                <select 
                  v-model="form.region" 
                  @change="onRegionChange"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all font-semibold"
                >
                  <option v-for="(label, key) in regions" :key="key" :value="key">{{ label }}</option>
                </select>
                <div v-if="form.errors.region" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.region }}</span>
                </div>
              </div>

              <!-- Status Kepegawaian -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status Kepegawaian *</label>
                <select 
                  v-model="form.employment_status" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all font-semibold"
                >
                  <option v-for="(label, key) in employmentStatuses" :key="key" :value="key">{{ label }}</option>
                </select>
                <div v-if="form.errors.employment_status" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.employment_status }}</span>
                </div>
              </div>

              <!-- Pangkalan Pool / Depo -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pangkalan Pool / Depo</label>
                <select 
                  v-if="availablePools && availablePools.length > 0"
                  v-model="form.pool_depot" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all font-medium"
                >
                  <option v-for="pool in availablePools" :key="pool" :value="pool">{{ pool }}</option>
                </select>
                <input 
                  v-else
                  v-model="form.pool_depot"
                  type="text"
                  placeholder="Contoh: Pool Cawang"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                />
              </div>
            </div>

            <!-- Quick Preset Profesi Transjakarta -->
            <div class="mt-4 p-3.5 rounded-2xl bg-blue-50/40 border border-blue-100/70">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-blue-800 mb-2">
                Pilih Cepat Struktur Profesi Transjakarta:
              </label>
              <div class="flex flex-wrap items-center gap-2">
                <button 
                  type="button" 
                  @click="selectPresetPosition('Pramudi')"
                  class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
                  :class="form.position === 'Pramudi' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                >
                  <i class="bi bi-bus-front mr-1"></i> Pramudi (Driver)
                </button>
                <button 
                  type="button" 
                  @click="selectPresetPosition('Pramusapa')"
                  class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
                  :class="form.position === 'Pramusapa' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                >
                  <i class="bi bi-person-badge mr-1"></i> Pramusapa (Layanan)
                </button>
                <button 
                  type="button" 
                  @click="selectPresetPosition('Pramujaga')"
                  class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
                  :class="form.position === 'Pramujaga' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                >
                  <i class="bi bi-shield-check mr-1"></i> Pramujaga (Keamanan)
                </button>
                <button 
                  type="button" 
                  @click="selectPresetPosition('Karyawan Kantor')"
                  class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
                  :class="form.position === 'Karyawan Kantor' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                >
                  <i class="bi bi-building mr-1"></i> Karyawan Kantor / OCC
                </button>
              </div>
            </div>
          </div>

          <!-- Section 3: Detail Posisi & Shift -->
          <div class="pt-4 border-t border-slate-100">
            <h6 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-3">
              3. Detail Jabatan, Departemen & Shift
            </h6>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jabatan / Profesi Pegawai *</label>
                <input 
                  v-model="form.position" 
                  @input="form.clearErrors('position')"
                  type="text" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
                />
                <div v-if="form.errors.position" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.position }}</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Divisi / Departemen *</label>
                <input 
                  v-model="form.department" 
                  @input="form.clearErrors('department')"
                  type="text" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
                />
                <div v-if="form.errors.department" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.department }}</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jadwal Shift Kerja *</label>
                <select 
                  v-model="form.schedule_id" 
                  @change="form.clearErrors('schedule_id')"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
                >
                  <option value="" disabled>Pilih Shift</option>
                  <option v-for="s in schedules" :key="s.id" :value="s.id">
                    {{ s.name }} ({{ s.start_time?.substring(0, 5) }} - {{ s.end_time?.substring(0, 5) }})
                  </option>
                </select>
                <div v-if="form.errors.schedule_id" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.schedule_id }}</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status Akun Login</label>
                <select 
                  v-model="form.is_active" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors font-semibold"
                >
                  <option :value="true">Aktif (Dapat Login)</option>
                  <option :value="false">Nonaktif (Akses Dikunci)</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal Mulai Bergabung *</label>
                <input 
                  v-model="form.joined_date" 
                  @input="form.clearErrors('joined_date')"
                  type="date" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
                />
                <div v-if="form.errors.joined_date" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.joined_date }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
            <Link 
              :href="route('admin.employees.index')" 
              class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl transition-colors"
            >
              Batal
            </Link>
            <button 
              type="submit" 
              :disabled="form.processing"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-600/20 transition-all cursor-pointer"
            >
              <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin text-sm"></i>
              <i v-else class="bi bi-check2 text-sm"></i>
              <span>Simpan Perubahan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
