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
  form.clearErrors();
  let hasError = false;

  if (!form.type) {
    form.setError('type', 'Silakan tentukan jenis pengajuan cuti/izin.');
    hasError = true;
  }
  if (!form.start_date) {
    form.setError('start_date', 'Tanggal mulai permohonan wajib dipilih.');
    hasError = true;
  }
  if (!form.end_date) {
    form.setError('end_date', 'Tanggal selesai permohonan wajib dipilih.');
    hasError = true;
  }
  if (!form.reason || !form.reason.trim()) {
    form.setError('reason', 'Alasan pengajuan wajib diisi secara rinci.');
    hasError = true;
  }

  if (hasError) {
    return;
  }

  form.post(route('employee.leave-requests.store'));
};
</script>

<template>
  <EmployeeLayout>
    <Head title="Buat Pengajuan Cuti / Izin" />

    <div class="max-w-2xl mx-auto">
      <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-8 shadow-xs">
        <!-- Card Header -->
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <i class="bi bi-file-earmark-plus text-lg"></i>
            </div>
            <div>
              <h1 class="text-base font-bold text-slate-900 leading-tight">Formulir Pengajuan</h1>
              <p class="text-xs text-slate-400">Isi data permohonan cuti atau izin Anda</p>
            </div>
          </div>
          <Link 
            :href="route('employee.leave-requests.index')" 
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-xl transition-colors cursor-pointer"
          >
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
          </Link>
        </div>

        <form novalidate @submit.prevent="submit" class="space-y-4">
          <!-- Leave Type -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Jenis Pengajuan *
            </label>
            <select 
              v-model="form.type" 
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border transition-all outline-none"
              :class="form.errors.type ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
              @change="form.clearErrors('type')"
            >
              <option value="annual_leave">Cuti Tahunan</option>
              <option value="sick">Sakit (Keterangan Dokter)</option>
              <option value="permission">Izin Keperluan Pribadi</option>
              <option value="emergency_leave">Cuti Darurat / Duka</option>
            </select>
            <div v-if="form.errors.type" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
              <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
              <span>{{ form.errors.type }}</span>
            </div>
          </div>

          <!-- Date Range -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Tanggal Mulai *
              </label>
              <input 
                v-model="form.start_date" 
                type="date" 
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border transition-all outline-none font-medium" 
                :class="form.errors.start_date ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                @change="form.clearErrors('start_date')"
              />
              <div v-if="form.errors.start_date" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ form.errors.start_date }}</span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Tanggal Selesai *
              </label>
              <input 
                v-model="form.end_date" 
                type="date" 
                :min="form.start_date" 
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border transition-all outline-none font-medium" 
                :class="form.errors.end_date ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                @change="form.clearErrors('end_date')"
              />
              <div v-if="form.errors.end_date" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ form.errors.end_date }}</span>
              </div>
            </div>
          </div>

          <!-- Auto Calculated Working Days Indicator -->
          <div class="p-4 bg-blue-50/70 rounded-2xl border border-blue-200/80 flex items-center justify-between">
            <div>
              <span class="text-xs font-bold text-blue-900 block">Estimasi Durasi Hari Kerja</span>
              <span class="text-[11px] text-blue-600/80 mt-0.5 block">* Akhir pekan (Sabtu & Minggu) otomatis dikecualikan</span>
            </div>
            <span class="px-3.5 py-1.5 rounded-xl bg-blue-600 text-white font-black text-sm shadow-xs">
              {{ calculatedDays }} Hari
            </span>
          </div>

          <!-- Reason Field -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Alasan Lengkap Pengajuan *
            </label>
            <textarea 
              v-model="form.reason" 
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border placeholder-slate-400 transition-all leading-relaxed outline-none" 
              :class="form.errors.reason ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
              rows="4" 
              placeholder="Tuliskan keterangan dan alasan pengajuan secara rinci..." 
              @input="form.clearErrors('reason')"
            ></textarea>
            <div v-if="form.errors.reason" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
              <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
              <span>{{ form.errors.reason }}</span>
            </div>
          </div>

          <!-- Attachment Upload -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Lampiran Bukti (Opsional)
            </label>
            <input 
              type="file" 
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-700 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
              accept="application/pdf, image/jpeg, image/png, image/jpg"
              @change="handleFileChange" 
            />
            <span class="text-[11px] text-slate-400 block mt-1">
              Format: PDF, JPG, PNG (Maksimal 5MB). Wajib untuk surat sakit dari klinik/RS.
            </span>
            <div v-if="form.errors.attachment" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium">
              <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
              <span>{{ form.errors.attachment }}</span>
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            class="w-full h-14 rounded-2xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-sm sm:text-base shadow-lg shadow-blue-600/25 flex items-center justify-center gap-2 transition-all disabled:opacity-50 cursor-pointer pt-1"
            :disabled="form.processing"
          >
            <span v-if="form.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span>Kirim Permohonan Cuti</span>
            <i class="bi bi-send-fill text-sm"></i>
          </button>
        </form>
      </div>
    </div>
  </EmployeeLayout>
</template>
