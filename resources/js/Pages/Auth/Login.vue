<script setup>
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.clearErrors();
  let hasError = false;

  if (!form.email || !form.email.trim()) {
    form.setError('email', 'Alamat email wajib diisi.');
    hasError = true;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) {
    form.setError('email', 'Format alamat email tidak valid (contoh: nama@perusahaan.com).');
    hasError = true;
  }

  if (!form.password) {
    form.setError('password', 'Kata sandi wajib diisi.');
    hasError = true;
  }

  if (hasError) {
    return;
  }

  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};

const fillDemo = (role) => {
  form.clearErrors();
  if (role === 'admin') {
    form.email = 'admin@absensi.com';
    form.password = 'password';
  } else if (role === 'pegawai') {
    form.email = 'budi@absensi.com';
    form.password = 'password';
  }
};
</script>

<template>
  <Head title="Masuk ke HRIS" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50/50 via-slate-50 to-slate-100 flex items-center justify-center p-4 font-sans">
    <div class="w-full max-w-md">
      <!-- Logo & Header -->
      <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center mb-3">
          <img 
            src="/favicon.png" 
            alt="Logo Transjakarta" 
            class="h-20 sm:h-24 w-20 sm:w-24 object-contain drop-shadow-sm" 
          />
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">HRIS</h1>
        <p class="text-xs text-slate-500 font-medium mt-0.5">Sistem Informasi Manajemen SDM Transjakarta</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 p-6 sm:p-8">
        <div v-if="status" class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
          {{ status }}
        </div>

        <form novalidate @submit.prevent="submit" class="space-y-4">
          <!-- Email Input -->
          <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Alamat Email
            </label>
            <div class="relative">
              <span 
                class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none transition-colors"
                :class="form.errors.email ? 'text-rose-500' : 'text-slate-400'"
              >
                <i class="bi bi-envelope text-sm"></i>
              </span>
              <input 
                id="email" 
                v-model="form.email" 
                type="email" 
                class="w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border placeholder-slate-400 focus:outline-none transition-all" 
                :class="form.errors.email ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 bg-white text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                placeholder="nama@perusahaan.com" 
                autofocus 
                autocomplete="username"
                @input="form.clearErrors('email')"
              />
            </div>
            <div v-if="form.errors.email" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
              <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
              <span>{{ form.errors.email }}</span>
            </div>
          </div>

          <!-- Password Input -->
          <div>
            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Kata Sandi
            </label>
            <div class="relative">
              <span 
                class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none transition-colors"
                :class="form.errors.password ? 'text-rose-500' : 'text-slate-400'"
              >
                <i class="bi bi-lock text-sm"></i>
              </span>
              <input 
                id="password" 
                v-model="form.password" 
                type="password" 
                class="w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border placeholder-slate-400 focus:outline-none transition-all" 
                :class="form.errors.password ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 bg-white text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                placeholder="••••••••" 
                autocomplete="current-password"
                @input="form.clearErrors('password')"
              />
            </div>
            <div v-if="form.errors.password" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
              <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
              <span>{{ form.errors.password }}</span>
            </div>
          </div>

          <!-- Remember Me -->
          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer">
              <input 
                id="remember" 
                v-model="form.remember" 
                type="checkbox" 
                class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300"
              />
              <span class="text-xs text-slate-600 select-none">Ingat saya di perangkat ini</span>
            </label>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm shadow-md shadow-blue-500/25 transition-all duration-150 flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer"
            :disabled="form.processing"
          >
            <span v-if="form.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span>Masuk Sekarang</span>
            <i class="bi bi-arrow-right text-base"></i>
          </button>
        </form>

        <!-- Demo Quick Login Helper -->
        <div class="mt-6 pt-5 border-t border-slate-100">
          <div class="text-center text-slate-400 text-[11px] font-bold uppercase tracking-wider mb-2.5">
            Akses Cepat Demo:
          </div>
          <div class="grid grid-cols-2 gap-2">
            <button 
              type="button" 
              class="py-2 px-3 rounded-xl border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-blue-700 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
              @click="fillDemo('admin')"
            >
              <i class="bi bi-shield-lock text-sm text-blue-600"></i>
              <span>Admin HR</span>
            </button>
            <button 
              type="button" 
              class="py-2 px-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
              @click="fillDemo('pegawai')"
            >
              <i class="bi bi-person text-sm text-slate-600"></i>
              <span>Pegawai</span>
            </button>
          </div>
        </div>
      </div>

      <div class="text-center mt-4 text-xs text-slate-400">
        &copy; {{ new Date().getFullYear() }} HRIS &mdash; Human Resource Information System
      </div>
    </div>
  </div>
</template>
