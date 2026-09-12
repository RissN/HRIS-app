<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import AvatarUpload from '@/Components/AvatarUpload.vue';

const props = defineProps({
  user: Object,
  employee: Object,
});

// Section A: Personal Form
const personalForm = useForm({
  name: props.user?.name || '',
  phone: props.employee?.phone || '',
});

const submitPersonal = () => {
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

    <div class="row justify-content-center">
      <div class="col-12 col-md-9 col-lg-8">
        <!-- Profile Header with Avatar -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-4 rounded-4 mb-4">
          <AvatarUpload 
            :current-avatar="employee?.avatar" 
            :user-name="user?.name" 
          />
        </div>

        <!-- Section A: Personal Information -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-4 rounded-4 mb-4">
          <h6 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-person-lines-fill text-primary"></i>
            Section A — Informasi Pribadi
          </h6>

          <form @submit.prevent="submitPersonal">
            <div class="row g-3 mb-3">
              <div class="col-12 col-sm-6">
                <label class="form-label small text-secondary fw-semibold">Nama Lengkap *</label>
                <input v-model="personalForm.name" type="text" class="form-control" required />
                <div v-if="personalForm.errors.name" class="text-danger small mt-1">{{ personalForm.errors.name }}</div>
              </div>

              <div class="col-12 col-sm-6">
                <label class="form-label small text-secondary fw-semibold">Nomor Handphone (WhatsApp)</label>
                <input v-model="personalForm.phone" type="text" class="form-control" placeholder="0812xxxxxxxx" />
                <div v-if="personalForm.errors.phone" class="text-danger small mt-1">{{ personalForm.errors.phone }}</div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-12 col-sm-6">
                <label class="form-label small text-secondary fw-semibold">Email Kantor (Read-Only)</label>
                <input :value="user?.email" type="email" class="form-control bg-dark text-secondary" readonly disabled />
              </div>

              <div class="col-12 col-sm-6">
                <label class="form-label small text-secondary fw-semibold">Jabatan (Read-Only)</label>
                <input :value="employee?.position || '-'" type="text" class="form-control bg-dark text-secondary" readonly disabled />
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-12 col-sm-6">
                <label class="form-label small text-secondary fw-semibold">Departemen (Read-Only)</label>
                <input :value="employee?.department || '-'" type="text" class="form-control bg-dark text-secondary" readonly disabled />
              </div>

              <div class="col-12 col-sm-6">
                <label class="form-label small text-secondary fw-semibold">Tanggal Bergabung (Read-Only)</label>
                <input :value="employee?.joined_date ? employee.joined_date.substring(0, 10) : '-'" type="text" class="form-control bg-dark text-secondary" readonly disabled />
              </div>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-primary btn-sm px-4 shadow" :disabled="personalForm.processing">
                <span v-if="personalForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                Simpan Informasi Pribadi
              </button>
            </div>
          </form>
        </div>

        <!-- Section B: Bank Account Information -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-4 rounded-4 mb-4">
          <h6 class="fw-bold text-white mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-credit-card-2-front text-info"></i>
            Section B — Informasi Rekening Payroll
          </h6>
          <p class="text-secondary small mb-3">
            <i class="bi bi-shield-lock-fill text-success me-1"></i>
            Nomor rekening Anda diamankan dengan enkripsi standar industri (Laravel Crypt).
          </p>

          <form @submit.prevent="submitBank">
            <div class="row g-3 mb-3">
              <div class="col-12 col-sm-6">
                <label class="form-label small text-secondary fw-semibold">Nama Bank *</label>
                <input v-model="bankForm.bank_name" type="text" class="form-control" placeholder="Contoh: BCA, Mandiri, BNI" required />
                <div v-if="bankForm.errors.bank_name" class="text-danger small mt-1">{{ bankForm.errors.bank_name }}</div>
              </div>

              <div class="col-12 col-sm-6">
                <label class="form-label small text-secondary fw-semibold">Nomor Rekening *</label>
                <input v-model="bankForm.account_number" type="text" class="form-control" placeholder="1234567890" required />
                <div v-if="bankForm.errors.account_number" class="text-danger small mt-1">{{ bankForm.errors.account_number }}</div>
              </div>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-info btn-sm px-4 shadow text-white" :disabled="bankForm.processing">
                <span v-if="bankForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                Simpan Informasi Rekening
              </button>
            </div>
          </form>
        </div>

        <!-- Section C: Security & Password -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-4 rounded-4 mb-4">
          <h6 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock text-warning"></i>
            Section C — Keamanan & Ganti Kata Sandi
          </h6>

          <form @submit.prevent="submitPassword">
            <div class="mb-3">
              <label class="form-label small text-secondary fw-semibold">Kata Sandi Saat Ini *</label>
              <input v-model="passwordForm.current_password" type="password" class="form-control" placeholder="••••••••" required autocomplete="current-password" />
              <div v-if="passwordForm.errors.current_password" class="text-danger small mt-1">{{ passwordForm.errors.current_password }}</div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-12 col-sm-6">
                <label class="form-label small text-secondary fw-semibold">Kata Sandi Baru (Min. 8 Karakter) *</label>
                <input v-model="passwordForm.password" type="password" class="form-control" placeholder="••••••••" required autocomplete="new-password" />
                <div v-if="passwordForm.errors.password" class="text-danger small mt-1">{{ passwordForm.errors.password }}</div>
              </div>

              <div class="col-12 col-sm-6">
                <label class="form-label small text-secondary fw-semibold">Konfirmasi Kata Sandi Baru *</label>
                <input v-model="passwordForm.password_confirmation" type="password" class="form-control" placeholder="••••••••" required autocomplete="new-password" />
                <div v-if="passwordForm.errors.password_confirmation" class="text-danger small mt-1">{{ passwordForm.errors.password_confirmation }}</div>
              </div>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-warning btn-sm px-4 shadow text-dark fw-semibold" :disabled="passwordForm.processing">
                <span v-if="passwordForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                Perbarui Kata Sandi
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>
