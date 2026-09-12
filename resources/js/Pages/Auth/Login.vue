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
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};

const fillDemo = (role) => {
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
  <Head title="Masuk ke Sistem Absensi" />

  <div class="min-vh-100 d-flex align-items-center justify-content-center p-3" style="background: radial-gradient(circle at top, #1e1b4b, #0f1117 70%);">
    <div class="w-100" style="max-width: 420px;">
      <!-- Logo & App Title -->
      <div class="text-center mb-4">
        <div class="d-inline-flex bg-primary text-white rounded-4 p-3 shadow-lg mb-2">
          <i class="bi bi-clock-history fs-2"></i>
        </div>
        <h4 class="fw-bold text-white mb-1">Absensi Pro</h4>
        <p class="text-secondary small">Sistem Presensi Kerja Mobile-Friendly</p>
      </div>

      <!-- Login Card -->
      <div class="card border border-secondary border-opacity-25 shadow-lg p-3 p-md-4 rounded-4">
        <div v-if="status" class="alert alert-success py-2 px-3 small mb-3">
          {{ status }}
        </div>

        <form @submit.prevent="submit">
          <!-- Email Input -->
          <div class="mb-3">
            <label for="email" class="form-label small text-secondary fw-semibold">Alamat Email</label>
            <div class="input-group">
              <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary">
                <i class="bi bi-envelope"></i>
              </span>
              <input 
                id="email" 
                v-model="form.email" 
                type="email" 
                class="form-control" 
                placeholder="nama@perusahaan.com" 
                required 
                autofocus 
                autocomplete="username"
              />
            </div>
            <div v-if="form.errors.email" class="text-danger small mt-1">
              {{ form.errors.email }}
            </div>
          </div>

          <!-- Password Input -->
          <div class="mb-3">
            <label for="password" class="form-label small text-secondary fw-semibold">Kata Sandi</label>
            <div class="input-group">
              <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary">
                <i class="bi bi-lock"></i>
              </span>
              <input 
                id="password" 
                v-model="form.password" 
                type="password" 
                class="form-control" 
                placeholder="••••••••" 
                required 
                autocomplete="current-password"
              />
            </div>
            <div v-if="form.errors.password" class="text-danger small mt-1">
              {{ form.errors.password }}
            </div>
          </div>

          <!-- Remember Me -->
          <div class="form-check mb-4">
            <input 
              id="remember" 
              v-model="form.remember" 
              type="checkbox" 
              class="form-check-input"
            />
            <label class="form-check-label small text-secondary" for="remember">
              Ingat saya di perangkat ini
            </label>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            class="btn btn-primary w-100 py-2 fw-semibold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2"
            :disabled="form.processing"
          >
            <span v-if="form.processing" class="spinner-border spinner-border-sm"></span>
            <span>Masuk Sekarang</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </form>

        <!-- Demo Quick Login Helper -->
        <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
          <div class="text-center text-secondary small mb-2" style="font-size: 0.72rem;">AKSES CEPAT DEMO:</div>
          <div class="d-flex gap-2">
            <button 
              type="button" 
              class="btn btn-sm btn-outline-primary flex-fill py-1"
              style="font-size: 0.78rem;"
              @click="fillDemo('admin')"
            >
              <i class="bi bi-shield-lock me-1"></i> Admin HR
            </button>
            <button 
              type="button" 
              class="btn btn-sm btn-outline-info flex-fill py-1"
              style="font-size: 0.78rem;"
              @click="fillDemo('pegawai')"
            >
              <i class="bi bi-person me-1"></i> Pegawai
            </button>
          </div>
        </div>
      </div>

      <div class="text-center mt-3 text-secondary" style="font-size: 0.75rem;">
        &copy; {{ new Date().getFullYear() }} Absensi Pro. Dilindungi hak cipta.
      </div>
    </div>
  </div>
</template>
