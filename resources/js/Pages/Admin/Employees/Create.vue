<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  schedules: Array,
});

const form = useForm({
  name: '',
  email: '',
  password: 'password123',
  phone: '',
  position: 'Staff',
  department: 'IT & Technology',
  bank_name: 'BCA',
  account_number: '',
  joined_date: new Date().toISOString().substring(0, 10),
  schedule_id: props.schedules?.[0]?.id || '',
});

const submit = () => {
  form.post(route('admin.employees.store'));
};
</script>

<template>
  <AdminLayout>
    <Head title="Tambah Pegawai Baru" />

    <div class="row justify-content-center">
      <div class="col-12 col-lg-9">
        <div class="card border border-secondary border-opacity-25 shadow-sm p-4 rounded-4">
          <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom border-secondary border-opacity-25">
            <h4 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-person-plus text-primary"></i>
              Formulir Tambah Pegawai
            </h4>
            <Link :href="route('admin.employees.index')" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </Link>
          </div>

          <form @submit.prevent="submit">
            <!-- Section 1: Akun Pengguna -->
            <h6 class="text-primary small fw-bold text-uppercase mb-3" style="letter-spacing: 0.05em;">
              1. Informasi Akun & Kredensial
            </h6>

            <div class="row g-3 mb-3">
              <div class="col-12 col-md-6">
                <label class="form-label small text-secondary fw-semibold">Nama Lengkap *</label>
                <input v-model="form.name" type="text" class="form-control" placeholder="Nama pegawai..." required />
                <div v-if="form.errors.name" class="text-danger small mt-1">{{ form.errors.name }}</div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label small text-secondary fw-semibold">Alamat Email Kantor *</label>
                <input v-model="form.email" type="email" class="form-control" placeholder="pegawai@perusahaan.com" required />
                <div v-if="form.errors.email" class="text-danger small mt-1">{{ form.errors.email }}</div>
              </div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-12 col-md-6">
                <label class="form-label small text-secondary fw-semibold">Kata Sandi Awal *</label>
                <input v-model="form.password" type="text" class="form-control" required />
                <small class="text-secondary" style="font-size: 0.72rem;">Default: password123 (dapat diganti pegawai nanti)</small>
                <div v-if="form.errors.password" class="text-danger small mt-1">{{ form.errors.password }}</div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label small text-secondary fw-semibold">Nomor Handphone (WhatsApp)</label>
                <input v-model="form.phone" type="text" class="form-control" placeholder="0812xxxxxxxx" />
                <div v-if="form.errors.phone" class="text-danger small mt-1">{{ form.errors.phone }}</div>
              </div>
            </div>

            <!-- Section 2: Pekerjaan & Jadwal -->
            <h6 class="text-primary small fw-bold text-uppercase mb-3 pt-3 border-top border-secondary border-opacity-25" style="letter-spacing: 0.05em;">
              2. Posisi, Departemen & Jadwal
            </h6>

            <div class="row g-3 mb-3">
              <div class="col-12 col-md-6">
                <label class="form-label small text-secondary fw-semibold">Jabatan Pegawai *</label>
                <input v-model="form.position" type="text" class="form-control" placeholder="Contoh: Manager, Supervisor, Staff" required />
                <div v-if="form.errors.position" class="text-danger small mt-1">{{ form.errors.position }}</div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label small text-secondary fw-semibold">Divisi / Departemen *</label>
                <input v-model="form.department" type="text" class="form-control" placeholder="Contoh: IT, Marketing, HR, Finance" required />
                <div v-if="form.errors.department" class="text-danger small mt-1">{{ form.errors.department }}</div>
              </div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-12 col-md-6">
                <label class="form-label small text-secondary fw-semibold">Shift Jadwal Kerja *</label>
                <select v-model="form.schedule_id" class="form-select" required>
                  <option v-for="s in schedules" :key="s.id" :value="s.id">
                    {{ s.name }} ({{ s.start_time.substring(0,5) }} - {{ s.end_time.substring(0,5) }})
                  </option>
                </select>
                <div v-if="form.errors.schedule_id" class="text-danger small mt-1">{{ form.errors.schedule_id }}</div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label small text-secondary fw-semibold">Tanggal Mulai Bergabung *</label>
                <input v-model="form.joined_date" type="date" class="form-control" required />
                <div v-if="form.errors.joined_date" class="text-danger small mt-1">{{ form.errors.joined_date }}</div>
              </div>
            </div>

            <!-- Section 3: Payroll / Rekening -->
            <h6 class="text-primary small fw-bold text-uppercase mb-3 pt-3 border-top border-secondary border-opacity-25" style="letter-spacing: 0.05em;">
              3. Data Rekening Payroll
            </h6>

            <div class="row g-3 mb-4">
              <div class="col-12 col-md-6">
                <label class="form-label small text-secondary fw-semibold">Nama Bank</label>
                <input v-model="form.bank_name" type="text" class="form-control" placeholder="Contoh: BCA, Mandiri, BRI" />
                <div v-if="form.errors.bank_name" class="text-danger small mt-1">{{ form.errors.bank_name }}</div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label small text-secondary fw-semibold">Nomor Rekening</label>
                <input v-model="form.account_number" type="text" class="form-control" placeholder="1234567890" />
                <div v-if="form.errors.account_number" class="text-danger small mt-1">{{ form.errors.account_number }}</div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary border-opacity-25">
              <Link :href="route('admin.employees.index')" class="btn btn-secondary btn-sm px-3">Batal</Link>
              <button type="submit" class="btn btn-primary btn-sm px-4 shadow" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                Simpan Data Pegawai
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
