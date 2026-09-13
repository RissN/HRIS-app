<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';

const props = defineProps({
  leaveBalance: Object,
});

const form = useForm({
  type: 'annual_leave',
  start_date: '',
  end_date: '',
  reason: '',
  attachment: null,
});

const calculatedDays = ref(0);

const isExceedingQuota = computed(() => {
  if (form.type !== 'annual_leave' || !props.leaveBalance) return false;
  return calculatedDays.value > (props.leaveBalance.available ?? 0);
});

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
  if (isExceedingQuota.value) {
    form.setError('type', `Sisa kuota cuti tahunan Anda tidak mencukupi (${props.leaveBalance?.available ?? 0} hari tersisa). Pengajuan membutuhkan ${calculatedDays.value} hari kerja.`);
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

        <!-- Leave Balance Status Card -->
        <div v-if="leaveBalance" class="mb-5 p-4 rounded-2xl border transition-all" :class="form.type === 'annual_leave' ? 'bg-blue-50/40 border-blue-200' : 'bg-slate-50 border-slate-200/70'">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full" :class="leaveBalance.available > 0 ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-rose-500 ring-4 ring-rose-100'"></span>
              <span class="text-xs font-bold text-slate-800">Saldo Cuti Tahunan (Tahun {{ leaveBalance.year }})</span>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full" :class="leaveBalance.available > 0 ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800'">
              {{ leaveBalance.available }} Hari Tersedia
            </span>
          </div>

          <div class="grid grid-cols-3 gap-2 text-center text-xs">
            <div class="p-2.5 bg-white rounded-xl border border-slate-100 shadow-2xs">
              <div class="text-[10px] text-slate-400 font-semibold uppercase">Hak Kuota</div>
              <div class="text-sm font-bold text-slate-800 font-mono mt-0.5">{{ leaveBalance.quota }} Hari</div>
            </div>
            <div class="p-2.5 bg-white rounded-xl border border-slate-100 shadow-2xs">
              <div class="text-[10px] text-slate-400 font-semibold uppercase">Terpakai</div>
              <div class="text-sm font-bold text-amber-600 font-mono mt-0.5">{{ leaveBalance.used }} Hari</div>
            </div>
            <div class="p-2.5 bg-white rounded-xl border border-slate-100 shadow-2xs">
              <div class="text-[10px] text-slate-400 font-semibold uppercase">Menunggu Review</div>
              <div class="text-sm font-bold text-purple-600 font-mono mt-0.5">{{ leaveBalance.pending }} Hari</div>
            </div>
          </div>

          <!-- Alert if quota is exceeded -->
          <div v-if="isExceedingQuota" class="mt-3 p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-2.5 text-xs text-rose-700 font-medium">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base shrink-0"></i>
            <div>
              <strong>Kuota Cuti Tidak Mencukupi!</strong> Pengajuan ini membutuhkan {{ calculatedDays }} hari kerja, sedangkan sisa kuota yang tersedia hanya {{ leaveBalance.available }} hari.
            </div>
          </div>
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
            class="w-full h-14 rounded-2xl font-bold text-sm sm:text-base flex items-center justify-center gap-2 transition-all disabled:opacity-50 cursor-pointer pt-1"
            :class="isExceedingQuota ? 'bg-slate-300 text-slate-500 cursor-not-allowed shadow-none' : 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white shadow-lg shadow-blue-600/25'"
            :disabled="form.processing || isExceedingQuota"
          >
            <span v-if="form.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span>{{ isExceedingQuota ? 'Sisa Kuota Cuti Tidak Cukup' : 'Kirim Permohonan Cuti' }}</span>
            <i v-if="!isExceedingQuota" class="bi bi-send-fill text-sm"></i>
            <i v-else class="bi bi-slash-circle text-sm"></i>
          </button>
        </form>
      </div>
    </div>
  </EmployeeLayout>
</template>
