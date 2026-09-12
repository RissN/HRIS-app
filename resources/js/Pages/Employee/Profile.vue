<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import AvatarUpload from '@/Components/AvatarUpload.vue';
import LogoutModal from '@/Components/LogoutModal.vue';

const props = defineProps({
  user: Object,
  employee: Object,
});

const showLogoutModal = ref(false);

// Section A: Personal Form
const personalForm = useForm({
  name: props.user?.name || '',
  phone: props.employee?.phone || '',
});

const submitPersonal = () => {
  personalForm.clearErrors();
  let hasError = false;

  if (!personalForm.name || !personalForm.name.trim()) {
    personalForm.setError('name', 'Nama lengkap wajib diisi');
    hasError = true;
  }

  if (hasError) return;

  personalForm.post(route('employee.profile.personal'), {
    preserveScroll: true,
  });
};

// Section B: Bank Form
const bankForm = useForm({
  bank_name: props.employee?.bank_name || '',
  account_number: props.employee?.account_number || '',
});

const submitBank = () => {
  bankForm.clearErrors();
  let hasError = false;

  if (!bankForm.bank_name || !bankForm.bank_name.trim()) {
    bankForm.setError('bank_name', 'Nama bank wajib diisi');
    hasError = true;
  }

  if (!bankForm.account_number || !bankForm.account_number.trim()) {
    bankForm.setError('account_number', 'Nomor rekening wajib diisi');
    hasError = true;
  }

  if (hasError) return;

  bankForm.post(route('employee.profile.bank'), {
    preserveScroll: true,
  });
};

// Section C: Password Form
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const submitPassword = () => {
  passwordForm.clearErrors();
  let hasError = false;

  if (!passwordForm.current_password) {
    passwordForm.setError('current_password', 'Kata sandi saat ini wajib diisi');
    hasError = true;
  }

  if (!passwordForm.password) {
    passwordForm.setError('password', 'Kata sandi baru wajib diisi');
    hasError = true;
  } else if (passwordForm.password.length < 8) {
    passwordForm.setError('password', 'Kata sandi baru minimal 8 karakter');
    hasError = true;
  }

  if (!passwordForm.password_confirmation) {
    passwordForm.setError('password_confirmation', 'Konfirmasi kata sandi wajib diisi');
    hasError = true;
  } else if (passwordForm.password && passwordForm.password !== passwordForm.password_confirmation) {
    passwordForm.setError('password_confirmation', 'Konfirmasi kata sandi tidak cocok');
    hasError = true;
  }

  if (hasError) return;

  passwordForm.post(route('employee.profile.password'), {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset();
    },
  });
};
</script>

<template>
  <EmployeeLayout>
    <Head title="Profil Saya" />

    <div class="max-w-2xl mx-auto space-y-5">
      <!-- Profile Header with Avatar -->
      <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-xs">
        <AvatarUpload 
          :current-avatar="employee?.avatar" 
          :user-name="user?.name" 
        />
      </div>

      <!-- Quick Payslip Access Card -->
      <Link
        :href="route('employee.payroll.index')"
        class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-5 text-white flex items-center justify-between shadow-md shadow-blue-500/20 hover:from-blue-700 hover:to-indigo-700 transition active:scale-[0.99] group"
      >
        <div class="flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-xl font-bold">
            <i class="bi bi-wallet2"></i>
          </div>
          <div>
            <h3 class="font-bold text-sm leading-tight">Slip Gaji & Kompensasi</h3>
            <p class="text-xs text-blue-100/90 mt-0.5">Lihat rincian gaji bulanan, tunjangan kehadiran, dan potongan</p>
          </div>
        </div>
        <div class="w-8 h-8 rounded-full bg-white/15 flex items-center justify-center group-hover:translate-x-1 transition-transform">
          <i class="bi bi-chevron-right text-sm"></i>
        </div>
      </Link>

      <!-- Section A: Personal Information -->
      <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-xs">
        <div class="flex items-center gap-2.5 pb-4 mb-4 border-b border-slate-100">
          <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
            <i class="bi bi-person-lines-fill text-base"></i>
          </div>
          <div>
            <h2 class="text-sm font-bold text-slate-900 leading-tight">Informasi Pribadi</h2>
            <p class="text-[11px] text-slate-400">Kelola identitas dan nomor kontak aktif</p>
          </div>
        </div>

        <form novalidate @submit.prevent="submitPersonal" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Nama Lengkap *
              </label>
              <input 
                v-model="personalForm.name" 
                @input="personalForm.clearErrors('name')"
                type="text" 
                :class="personalForm.errors.name 
                  ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                  : 'border-slate-200 bg-white text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
              />
              <div v-if="personalForm.errors.name" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ personalForm.errors.name }}</span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Nomor WhatsApp / HP
              </label>
              <input 
                v-model="personalForm.phone" 
                @input="personalForm.clearErrors('phone')"
                type="text" 
                :class="personalForm.errors.phone 
                  ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                  : 'border-slate-200 bg-white text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                placeholder="0812xxxxxxxx" 
              />
              <div v-if="personalForm.errors.phone" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ personalForm.errors.phone }}</span>
              </div>
            </div>
          </div>

          <!-- Read-only Company Info -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                Email Kantor (Tetap)
              </label>
              <input 
                :value="user?.email" 
                type="email" 
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed" 
                readonly 
                disabled 
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                Jabatan
              </label>
              <input 
                :value="employee?.position || '-'" 
                type="text" 
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed" 
                readonly 
                disabled 
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                Departemen / Divisi
              </label>
              <input 
                :value="employee?.department || '-'" 
                type="text" 
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed" 
                readonly 
                disabled 
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                Tanggal Bergabung
              </label>
              <input 
                :value="employee?.joined_date ? employee.joined_date.substring(0, 10) : '-'" 
                type="text" 
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed" 
                readonly 
                disabled 
              />
            </div>
          </div>

          <div class="flex justify-end pt-2">
            <button 
              type="submit" 
              class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer"
              :disabled="personalForm.processing"
            >
              <span v-if="personalForm.processing">Menyimpan...</span>
              <span v-else>Simpan Informasi Pribadi</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Section B: Bank Account Information -->
      <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-xs">
        <div class="flex items-center gap-2.5 pb-4 mb-4 border-b border-slate-100">
          <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
            <i class="bi bi-credit-card-2-front text-base"></i>
          </div>
          <div>
            <h2 class="text-sm font-bold text-slate-900 leading-tight">Rekening Bank Payroll</h2>
            <p class="text-[11px] text-slate-400">Data rekening diamankan dengan enkripsi standar industri</p>
          </div>
        </div>

        <form novalidate @submit.prevent="submitBank" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Nama Bank *
              </label>
              <input 
                v-model="bankForm.bank_name" 
                @input="bankForm.clearErrors('bank_name')"
                type="text" 
                :class="bankForm.errors.bank_name 
                  ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                  : 'border-slate-200 bg-white text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                placeholder="Contoh: BCA, Mandiri, BRI" 
              />
              <div v-if="bankForm.errors.bank_name" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ bankForm.errors.bank_name }}</span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Nomor Rekening *
              </label>
              <input 
                v-model="bankForm.account_number" 
                @input="bankForm.clearErrors('account_number')"
                type="text" 
                :class="bankForm.errors.account_number 
                  ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                  : 'border-slate-200 bg-white text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                placeholder="1234567890" 
              />
              <div v-if="bankForm.errors.account_number" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ bankForm.errors.account_number }}</span>
              </div>
            </div>
          </div>

          <div class="flex justify-end pt-2">
            <button 
              type="submit" 
              class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer"
              :disabled="bankForm.processing"
            >
              <span v-if="bankForm.processing">Menyimpan...</span>
              <span v-else>Simpan Informasi Rekening</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Section C: Security & Password -->
      <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-xs">
        <div class="flex items-center gap-2.5 pb-4 mb-4 border-b border-slate-100">
          <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
            <i class="bi bi-shield-lock text-base"></i>
          </div>
          <div>
            <h2 class="text-sm font-bold text-slate-900 leading-tight">Keamanan & Ubah Sandi</h2>
            <p class="text-[11px] text-slate-400">Pastikan menggunakan kombinasi sandi yang kuat</p>
          </div>
        </div>

        <form novalidate @submit.prevent="submitPassword" class="space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Kata Sandi Saat Ini *
            </label>
            <input 
              v-model="passwordForm.current_password" 
              @input="passwordForm.clearErrors('current_password')"
              type="password" 
              :class="passwordForm.errors.current_password 
                ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                : 'border-slate-200 bg-white text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
              placeholder="••••••••" 
              autocomplete="current-password" 
            />
            <div v-if="passwordForm.errors.current_password" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
              <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
              <span>{{ passwordForm.errors.current_password }}</span>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Kata Sandi Baru *
              </label>
              <input 
                v-model="passwordForm.password" 
                @input="passwordForm.clearErrors('password')"
                type="password" 
                :class="passwordForm.errors.password 
                  ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                  : 'border-slate-200 bg-white text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                placeholder="Minimal 8 karakter" 
                autocomplete="new-password" 
              />
              <div v-if="passwordForm.errors.password" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ passwordForm.errors.password }}</span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Konfirmasi Kata Sandi Baru *
              </label>
              <input 
                v-model="passwordForm.password_confirmation" 
                @input="passwordForm.clearErrors('password_confirmation')"
                type="password" 
                :class="passwordForm.errors.password_confirmation 
                  ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                  : 'border-slate-200 bg-white text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all" 
                placeholder="Ulangi kata sandi baru" 
                autocomplete="new-password" 
              />
              <div v-if="passwordForm.errors.password_confirmation" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ passwordForm.errors.password_confirmation }}</span>
              </div>
            </div>
          </div>

          <div class="flex justify-end pt-2">
            <button 
              type="submit" 
              class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer"
              :disabled="passwordForm.processing"
            >
              <span v-if="passwordForm.processing">Memperbarui...</span>
              <span v-else>Perbarui Kata Sandi</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Logout Card (Mobile & Desktop) -->
      <div class="bg-white rounded-3xl border border-rose-100 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
          <h4 class="text-sm font-bold text-slate-900">Keluar dari Akun</h4>
          <p class="text-xs text-slate-400 mt-0.5">Akhiri sesi aktif Anda di perangkat ini secara aman.</p>
        </div>
        <button 
          type="button" 
          @click="showLogoutModal = true"
          class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer"
        >
          <i class="bi bi-box-arrow-right"></i>
          <span>Keluar Sistem</span>
        </button>
      </div>
    </div>

    <!-- Logout Confirmation Dialog -->
    <LogoutModal :show="showLogoutModal" @close="showLogoutModal = false" />
  </EmployeeLayout>
</template>
