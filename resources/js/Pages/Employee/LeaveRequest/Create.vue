<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';

const form = useForm({
  type: 'annual_leave',
  start_date: '',
  end_date: '',
  reason: '',
  attachment: null,
});

const calculatedDays = ref(0);

const calculateWorkingDays = (startStr, endStr) => {
  if (!startStr || !endStr) return 0;
  const start = new Date(startStr);
  const end = new Date(endStr);
  if (start > end) return 0;

  let count = 0;
  let cur = new Date(start);
  while (cur <= end) {
    const dayOfWeek = cur.getDay();
    if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Exclude Sunday (0) and Saturday (6)
      count++;
    }
    cur.setDate(cur.getDate() + 1);
  }
  return Math.max(1, count);
};

watch([() => form.start_date, () => form.end_date], ([newStart, newEnd]) => {
  if (newStart && (!newEnd || newEnd < newStart)) {
    form.end_date = newStart;
  }
  calculatedDays.value = calculateWorkingDays(form.start_date, form.end_date);
});

const handleFileChange = (e) => {
  form.attachment = e.target.files[0];
};

const submit = () => {
  form.post(route('employee.leave-requests.store'));
};
</script>

<template>
  <EmployeeLayout>
    <Head title="Buat Pengajuan Cuti / Izin" />

    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-7">
        <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4">
          <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom border-secondary border-opacity-25">
            <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-file-earmark-plus text-primary"></i>
              Formulir Pengajuan
            </h5>
            <Link :href="route('employee.leave-requests.index')" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Kembali
            </Link>
          </div>

          <form @submit.prevent="submit">
            <!-- Leave Type -->
            <div class="mb-3">
              <label class="form-label small text-secondary fw-semibold">Jenis Pengajuan *</label>
              <select v-model="form.type" class="form-select" required>
                <option value="annual_leave">Cuti Tahunan</option>
                <option value="sick">Sakit (Keterangan Dokter)</option>
                <option value="permission">Izin Keperluan Pribadi</option>
                <option value="emergency_leave">Cuti Darurat / Duka</option>
              </select>
              <div v-if="form.errors.type" class="text-danger small mt-1">{{ form.errors.type }}</div>
            </div>

            <!-- Date Range -->
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label small text-secondary fw-semibold">Tanggal Mulai *</label>
                <input 
                  v-model="form.start_date" 
                  type="date" 
                  class="form-control" 
                  required 
                />
                <div v-if="form.errors.start_date" class="text-danger small mt-1">{{ form.errors.start_date }}</div>
              </div>
              <div class="col-6">
                <label class="form-label small text-secondary fw-semibold">Tanggal Selesai *</label>
                <input 
                  v-model="form.end_date" 
                  type="date" 
                  :min="form.start_date" 
                  class="form-control" 
                  required 
                />
                <div v-if="form.errors.end_date" class="text-danger small mt-1">{{ form.errors.end_date }}</div>
              </div>
            </div>

            <!-- Auto Calculated Days Indicator -->
            <div class="p-3 bg-secondary bg-opacity-10 rounded-3 mb-3 border border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-secondary small d-block">Estimasi Durasi (Hari Kerja):</span>
                <small class="text-muted" style="font-size: 0.72rem;">* Hari Sabtu dan Minggu otomatis tidak dihitung</small>
              </div>
              <span class="badge bg-primary fs-6 px-3 py-2">
                {{ calculatedDays }} Hari
              </span>
            </div>

            <!-- Reason Field -->
            <div class="mb-3">
              <label class="form-label small text-secondary fw-semibold">Alasan Lengkap Pengajuan *</label>
              <textarea 
                v-model="form.reason" 
                class="form-control" 
                rows="4" 
                placeholder="Tuliskan keterangan dan alasan pengajuan cuti/izin Anda..." 
                required
              ></textarea>
              <div v-if="form.errors.reason" class="text-danger small mt-1">{{ form.errors.reason }}</div>
            </div>

            <!-- Attachment Upload -->
            <div class="mb-4">
              <label class="form-label small text-secondary fw-semibold">Lampiran Bukti (Opsional)</label>
              <input 
                type="file" 
                class="form-control" 
                accept="application/pdf, image/jpeg, image/png, image/jpg"
                @change="handleFileChange" 
              />
              <small class="text-secondary d-block mt-1" style="font-size: 0.72rem;">
                Format: PDF, JPG, PNG (Maksimal 5MB). Lampirkan surat dokter jika memilih jenis Sakit.
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
              <span>Kirimkan Pengajuan ke HR</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>
