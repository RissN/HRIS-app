<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';

const props = defineProps({
  recentAttendances: Array,
  selectedAttendanceId: [String, Number],
});

const form = useForm({
  date: new Date().toISOString().substring(0, 10),
  type: 'wrong_time',
  attendance_id: props.selectedAttendanceId || '',
  description: '',
  attachment: null,
});

const handleFileChange = (e) => {
  form.attachment = e.target.files[0];
};

const submit = () => {
  form.post(route('employee.complaints.store'));
};
</script>

<template>
  <EmployeeLayout>
    <Head title="Ajukan Komplain Presensi" />

    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-7">
        <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4">
          <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom border-secondary border-opacity-25">
            <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-chat-left-dots text-primary"></i>
              Formulir Komplain Presensi
            </h5>
            <Link :href="route('employee.complaints.index')" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Kembali
            </Link>
          </div>

          <form @submit.prevent="submit">
            <!-- Incident Date -->
            <div class="mb-3">
              <label class="form-label small text-secondary fw-semibold">Tanggal Kejadian *</label>
              <input 
                v-model="form.date" 
                type="date" 
                class="form-control" 
                required 
              />
              <div v-if="form.errors.date" class="text-danger small mt-1">{{ form.errors.date }}</div>
            </div>

            <!-- Complaint Type -->
            <div class="mb-3">
              <label class="form-label small text-secondary fw-semibold">Jenis Kendala / Masalah *</label>
              <select v-model="form.type" class="form-select" required>
                <option value="wrong_time">Waktu Absen Tidak Sesuai</option>
                <option value="location_error">Gagal Absen Karena Lokasi GPS</option>
                <option value="forgot_checkout">Lupa Check-out Pulang</option>
                <option value="system_error">Error Sistem / Aplikasi</option>
                <option value="other">Kendala Lainnya</option>
              </select>
              <div v-if="form.errors.type" class="text-danger small mt-1">{{ form.errors.type }}</div>
            </div>

            <!-- Optional Linked Attendance -->
            <div class="mb-3">
              <label class="form-label small text-secondary fw-semibold">Pilih Data Absensi Terkait (Opsional)</label>
              <select v-model="form.attendance_id" class="form-select">
                <option value="">-- Tidak terkait langsung dengan data tertentu --</option>
                <option v-for="att in recentAttendances" :key="att.id" :value="att.id">
                  {{ att.date }} — Status: {{ att.status }} ({{ att.check_in_at ? att.check_in_at.substring(11,16) : '--:--' }})
                </option>
              </select>
              <div v-if="form.errors.attendance_id" class="text-danger small mt-1">{{ form.errors.attendance_id }}</div>
            </div>

            <!-- Description -->
            <div class="mb-3">
              <label class="form-label small text-secondary fw-semibold">Deskripsi Lengkap Masalah *</label>
              <textarea 
                v-model="form.description" 
                class="form-control" 
                rows="4" 
                placeholder="Jelaskan secara rinci kendala yang Anda alami saat presensi..." 
                required
              ></textarea>
              <div v-if="form.errors.description" class="text-danger small mt-1">{{ form.errors.description }}</div>
            </div>

            <!-- Screenshot / Photo Attachment -->
            <div class="mb-4">
              <label class="form-label small text-secondary fw-semibold">Unggah Bukti / Screenshot (Opsional)</label>
              <input 
                type="file" 
                class="form-control" 
                accept="image/jpeg, image/png, image/jpg, application/pdf"
                @change="handleFileChange" 
              />
              <small class="text-secondary d-block mt-1" style="font-size: 0.72rem;">
                Tangkapan layar kendala GPS, foto lokasi, atau bukti pendukung lainnya (Maks. 5MB).
              </small>
              <div v-if="form.errors.attachment" class="text-danger small mt-1">{{ form.errors.attachment }}</div>
            </div>

            <!-- Submit Button -->
            <button 
              type="submit" 
              class="btn btn-primary btn-mobile-lg w-100 shadow"
              :disabled="form.processing"
            >
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-send-fill"></i>
              <span>Kirimkan Komplain ke HR</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>
