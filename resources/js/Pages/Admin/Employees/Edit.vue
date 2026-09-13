<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  employee: Object,
  schedules: Array,
});

const form = useForm({
  name: props.employee.name,
  email: props.employee.email,
  password: '',
  phone: props.employee.phone || '',
  position: props.employee.position,
  department: props.employee.department,
  bank_name: props.employee.bank_name || '',
  account_number: props.employee.account_number || '',
  annual_leave_quota: props.employee.annual_leave_quota ?? 12,
  joined_date: props.employee.joined_date,
  schedule_id: props.employee.schedule_id || (props.schedules?.[0]?.id || ''),
  is_active: props.employee.is_active,
});

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
  if (form.annual_leave_quota === '' || form.annual_leave_quota === null || form.annual_leave_quota < 0 || form.annual_leave_quota > 365) {
    form.setError('annual_leave_quota', 'Hak kuota cuti tahunan minimal 0 dan maksimal 365 hari');
    hasError = true;
  }

  if (hasError) return;

  form.put(route('admin.employees.update', props.employee.id));
};
</script>

<template>
  <AdminLayout>
    <Head title="Edit Data Pegawai" />

    <div class="max-w-4xl mx-auto">
      <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-8 shadow-xs">
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <i class="bi bi-pencil-square text-lg"></i>
            </div>
            <div>
              <h1 class="text-base font-bold text-slate-900 leading-tight">Ubah Data Pegawai</h1>
              <p class="text-xs text-slate-400">{{ employee.name }}</p>
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
          <!-- Section 1 -->
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
                  :class="form.errors.name 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                />
                <div v-if="form.errors.name" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
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
                  :class="form.errors.email 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                />
                <div v-if="form.errors.email" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.email }}</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Ganti Kata Sandi (Opsional)</label>
                <input 
                  v-model="form.password" 
                  @input="form.clearErrors('password')"
                  type="password" 
                  :class="form.errors.password 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                  placeholder="Kosongkan jika tidak diubah" 
                />
                <div v-if="form.errors.password" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
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
                  :class="form.errors.phone 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                />
                <div v-if="form.errors.phone" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.phone }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 2 -->
          <div class="pt-4 border-t border-slate-100">
            <h6 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-3">
              2. Posisi, Jadwal & Status Akun
            </h6>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jabatan Pegawai *</label>
                <input 
                  v-model="form.position" 
                  @input="form.clearErrors('position')"
                  type="text" 
                  :class="form.errors.position 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                />
                <div v-if="form.errors.position" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
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
                  :class="form.errors.department 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                />
                <div v-if="form.errors.department" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.department }}</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Shift Jadwal Kerja *</label>
                <select 
                  v-model="form.schedule_id" 
                  @change="form.clearErrors('schedule_id')"
                  :class="form.errors.schedule_id 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all"
                >
                  <option v-for="s in schedules" :key="s.id" :value="s.id">
                    {{ s.name }} ({{ s.start_time.substring(0,5) }} - {{ s.end_time.substring(0,5) }})
                  </option>
                </select>
                <div v-if="form.errors.schedule_id" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.schedule_id }}</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status Akun *</label>
                <select v-model="form.is_active" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                  <option :value="true">Aktif</option>
                  <option :value="false">Nonaktif (Blokir)</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal Bergabung *</label>
                <input 
                  v-model="form.joined_date" 
                  @input="form.clearErrors('joined_date')"
                  type="date" 
                  :class="form.errors.joined_date 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                />
                <div v-if="form.errors.joined_date" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.joined_date }}</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Hak Cuti Tahunan (Hari/Tahun) *</label>
                <div class="relative">
                  <input 
                    v-model="form.annual_leave_quota" 
                    @input="form.clearErrors('annual_leave_quota')"
                    type="number" 
                    min="0"
                    max="365"
                    :class="form.errors.annual_leave_quota 
                      ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                      : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all font-mono" 
                  />
                  <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-semibold">Hari</span>
                </div>
                <div v-if="form.errors.annual_leave_quota" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.annual_leave_quota }}</span>
                </div>
                <span class="text-[11px] text-slate-400 block mt-1">Kuota cuti per tahun untuk pegawai bersangkutan</span>
              </div>
            </div>
          </div>

          <!-- Section 3 -->
          <div class="pt-4 border-t border-slate-100">
            <h6 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-3">
              3. Data Rekening Payroll
            </h6>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nama Bank</label>
                <input 
                  v-model="form.bank_name" 
                  @input="form.clearErrors('bank_name')"
                  type="text" 
                  :class="form.errors.bank_name 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                  placeholder="Contoh: BCA, Mandiri" 
                />
                <div v-if="form.errors.bank_name" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.bank_name }}</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nomor Rekening</label>
                <input 
                  v-model="form.account_number" 
                  @input="form.clearErrors('account_number')"
                  type="text" 
                  :class="form.errors.account_number 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                  placeholder="1234567890" 
                />
                <div v-if="form.errors.account_number" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.account_number }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <Link :href="route('admin.employees.index')" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-semibold transition-colors">
              Batal
            </Link>
            <button 
              type="submit" 
              class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer" 
              :disabled="form.processing"
            >
              <span v-if="form.processing">Menyimpan...</span>
              <span v-else>Simpan Perubahan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
