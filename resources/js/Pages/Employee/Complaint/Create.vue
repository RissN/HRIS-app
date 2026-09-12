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
  form.clearErrors();
  let hasError = false;

  if (!form.date) {
    form.setError('date', 'Tanggal kejadian kendala presensi wajib dipilih.');
    hasError = true;
  }

  if (!form.type) {
    form.setError('type', 'Silakan pilih jenis kendala yang dialami.');
    hasError = true;
  }

  if (!form.description || !form.description.trim()) {
    form.setError('description', 'Deskripsi masalah wajib diisi secara jelas.');
    hasError = true;
  }

  if (hasError) {
    return;
  }

  form.post(route('employee.complaints.store'));
};
</script>

<template>
  <EmployeeLayout>
    <Head title="Ajukan Komplain Presensi" />

    <div class="max-w-2xl mx-auto">
      <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-8 shadow-xs">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <i class="bi bi-chat-left-dots text-lg"></i>
            </div>
            <div>
              <h1 class="text-base font-bold text-slate-900 leading-tight">Formulir Komplain Presensi</h1>
              <p class="text-xs text-slate-400">Laporkan kendala waktu atau gagal lokasi</p>
            </div>
          </div>
          <Link 
            :href="route('employee.complaints.index')" 
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-xl transition-colors cursor-pointer"
          >
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
          </Link>
        </div>

        <form novalidate @submit.prevent="submit" class="space-y-4">
          <!-- Date of Incident -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Tanggal Kejadian *
            </label>
            <input 
              v-model="form.date" 
              type="date" 
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border transition-all outline-none font-medium" 
              :class="form.errors.date ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
              @change="form.clearErrors('date')"
            />
            <div v-if="form.errors.date" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
              <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
              <span>{{ form.errors.date }}</span>
            </div>
          </div>

          <!-- Complaint Type -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Jenis Kendala / Masalah *
            </label>
            <select 
              v-model="form.type" 
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border transition-all outline-none" 
              :class="form.errors.type ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
              @change="form.clearErrors('type')"
            >
              <option value="wrong_time">Waktu Absen Tidak Sesuai</option>
              <option value="location_error">Gagal Absen Karena Lokasi GPS</option>
              <option value="forgot_checkout">Lupa Check-out Pulang</option>
              <option value="system_error">Error Sistem / Aplikasi</option>
              <option value="other">Kendala Lainnya</option>
            </select>
            <div v-if="form.errors.type" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
              <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
              <span>{{ form.errors.type }}</span>
            </div>
          </div>

          <!-- Attendance Linked (Optional) -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Pilih Data Absensi Terkait (Opsional)
            </label>
            <select 
              v-model="form.attendance_id" 
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
            >
              <option value="">-- Tidak terkait langsung dengan data tertentu --</option>
              <option v-for="att in recentAttendances" :key="att.id" :value="att.id">
                {{ att.date }} &mdash; Status: {{ att.status }} ({{ att.check_in_at ? att.check_in_at.substring(11,16) : '--:--' }})
              </option>
            </select>
            <div v-if="form.errors.attendance_id" class="text-rose-600 text-xs mt-1 font-medium">{{ form.errors.attendance_id }}</div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Deskripsi Masalah *
            </label>
            <textarea 
              v-model="form.description" 
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border placeholder-slate-400 transition-all leading-relaxed outline-none" 
              :class="form.errors.description ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
              rows="4" 
              placeholder="Jelaskan secara rinci kendala yang Anda alami saat presensi..." 
              @input="form.clearErrors('description')"
            ></textarea>
            <div v-if="form.errors.description" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
              <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
              <span>{{ form.errors.description }}</span>
            </div>
          </div>

          <!-- Screenshot upload -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Unggah Bukti / Screenshot (Opsional)
            </label>
            <input 
              type="file" 
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-700 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
              accept="image/jpeg, image/png, image/jpg, application/pdf"
              @change="handleFileChange" 
            />
            <span class="text-[11px] text-slate-400 block mt-1">
              Tangkapan layar kendala GPS, foto lokasi, atau bukti pendukung lainnya (Maks. 5MB).
            </span>
            <div v-if="form.errors.attachment" class="text-rose-600 text-xs mt-1 font-medium">{{ form.errors.attachment }}</div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            class="w-full h-14 rounded-2xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-sm sm:text-base shadow-lg shadow-blue-600/25 flex items-center justify-center gap-2 transition-all disabled:opacity-50 cursor-pointer pt-1"
            :disabled="form.processing"
          >
            <span v-if="form.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span>Kirim Laporan Komplain</span>
            <i class="bi bi-send-fill text-sm"></i>
          </button>
        </form>
      </div>
    </div>
  </EmployeeLayout>
</template>
