<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  schedules: Array,
});

const showModal = ref(false);
const editingSchedule = ref(null);

const dayOptions = [
  { value: 'monday', label: 'Senin' },
  { value: 'tuesday', label: 'Selasa' },
  { value: 'wednesday', label: 'Rabu' },
  { value: 'thursday', label: 'Kamis' },
  { value: 'friday', label: 'Jumat' },
  { value: 'saturday', label: 'Sabtu' },
  { value: 'sunday', label: 'Minggu' },
];

const form = useForm({
  name: '',
  start_time: '08:00',
  end_time: '17:00',
  days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
  tolerance_minutes: 15,
});

const openCreateModal = () => {
  editingSchedule.value = null;
  form.reset();
  form.name = '';
  form.start_time = '08:00';
  form.end_time = '17:00';
  form.days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
  form.tolerance_minutes = 15;
  showModal.value = true;
};

const openEditModal = (schedule) => {
  editingSchedule.value = schedule;
  form.name = schedule.name;
  form.start_time = schedule.start_time.substring(0, 5);
  form.end_time = schedule.end_time.substring(0, 5);
  form.days = Array.isArray(schedule.days) ? [...schedule.days] : [];
  form.tolerance_minutes = schedule.tolerance_minutes;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingSchedule.value = null;
  form.reset();
};

const submit = () => {
  form.clearErrors();
  let hasError = false;

  if (!form.name || !form.name.trim()) {
    form.setError('name', 'Nama shift wajib diisi');
    hasError = true;
  }
  if (!form.start_time) {
    form.setError('start_time', 'Jam masuk wajib diisi');
    hasError = true;
  }
  if (!form.end_time) {
    form.setError('end_time', 'Jam pulang wajib diisi');
    hasError = true;
  }
  if (form.tolerance_minutes === '' || form.tolerance_minutes === null || form.tolerance_minutes === undefined || form.tolerance_minutes < 0) {
    form.setError('tolerance_minutes', 'Toleransi keterlambatan wajib diisi (minimal 0 menit)');
    hasError = true;
  }
  if (!form.days || form.days.length === 0) {
    form.setError('days', 'Pilih minimal satu hari kerja operasional');
    hasError = true;
  }

  if (hasError) return;

  if (editingSchedule.value) {
    form.put(route('admin.schedules.update', editingSchedule.value.id), {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('admin.schedules.store'), {
      onSuccess: () => closeModal(),
    });
  }
};

const deleteForm = useForm({});
const deleteSchedule = (schedule) => {
  if (confirm(`Apakah Anda yakin ingin menghapus jadwal "${schedule.name}"?`)) {
    deleteForm.delete(route('admin.schedules.destroy', schedule.id));
  }
};

const formatDays = (daysArray) => {
  if (!Array.isArray(daysArray)) return '-';
  const labelMap = {
    monday: 'Sen', tuesday: 'Sel', wednesday: 'Rab', thursday: 'Kam',
    friday: 'Jum', saturday: 'Sab', sunday: 'Min'
  };
  return daysArray.map(d => labelMap[d] || d).join(', ');
};
</script>

<template>
  <AdminLayout>
    <Head title="Jadwal Kerja (Shift)" />

    <div class="space-y-6">
      <!-- Header Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Pengaturan Shift & Jadwal Kerja</h1>
            <p class="text-xs text-slate-500 mt-0.5">Atur jam masuk, jam pulang, toleransi keterlambatan, dan hari operasional.</p>
          </div>
          <button 
            type="button" 
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-600/20 transition-all cursor-pointer shrink-0"
            @click="openCreateModal"
          >
            <i class="bi bi-plus-circle text-sm"></i>
            <span>Tambah Shift Baru</span>
          </button>
        </div>
      </div>

      <!-- Schedules List Card -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
        <div v-if="schedules && schedules.length > 0">
          <!-- Mobile View: Cards -->
          <div class="md:hidden divide-y divide-slate-100 p-2">
            <div 
              v-for="s in schedules" 
              :key="s.id" 
              class="p-4 rounded-2xl space-y-3"
            >
              <div class="flex items-center justify-between">
                <div class="font-bold text-slate-900 text-sm">{{ s.name }}</div>
                <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full bg-blue-50 text-blue-700">
                  {{ s.employee_schedules_count }} Pegawai
                </span>
              </div>

              <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                  <span class="text-[10px] uppercase font-bold text-slate-400 block">Jam Kerja</span>
                  <span class="font-bold text-slate-800">{{ s.start_time.substring(0, 5) }} &ndash; {{ s.end_time.substring(0, 5) }}</span>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                  <span class="text-[10px] uppercase font-bold text-slate-400 block">Toleransi</span>
                  <span class="font-bold text-amber-600">{{ s.tolerance_minutes }} Menit</span>
                </div>
              </div>

              <div class="text-[11px] text-slate-500">
                <span class="font-semibold text-slate-700">Hari Aktif:</span> {{ formatDays(s.days) }}
              </div>

              <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button 
                  type="button" 
                  class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors cursor-pointer"
                  @click="openEditModal(s)"
                >
                  <i class="bi bi-pencil"></i> Edit
                </button>
                <button 
                  v-if="s.employee_schedules_count === 0" 
                  type="button" 
                  class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors cursor-pointer"
                  @click="deleteSchedule(s)"
                >
                  <i class="bi bi-trash"></i> Hapus
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop View: Table -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="bg-slate-50/60 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-4">Nama Shift</th>
                  <th class="py-3 px-4">Jam Kerja</th>
                  <th class="py-3 px-4">Toleransi Keterlambatan</th>
                  <th class="py-3 px-4">Hari Operasional</th>
                  <th class="py-3 px-4 text-center">Total Pegawai</th>
                  <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="s in schedules" :key="s.id" class="hover:bg-slate-50/70 transition-colors">
                  <td class="py-3.5 px-4 font-bold text-slate-900">{{ s.name }}</td>
                  <td class="py-3.5 px-4 font-bold text-slate-800 whitespace-nowrap">
                    {{ s.start_time.substring(0, 5) }} &ndash; {{ s.end_time.substring(0, 5) }}
                  </td>
                  <td class="py-3.5 px-4 text-amber-600 font-bold whitespace-nowrap">
                    {{ s.tolerance_minutes }} Menit
                  </td>
                  <td class="py-3.5 px-4 text-slate-600">{{ formatDays(s.days) }}</td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full bg-blue-50 text-blue-700">
                      {{ s.employee_schedules_count }} Pegawai
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-right whitespace-nowrap">
                    <div class="inline-flex gap-1.5">
                      <button 
                        type="button" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors cursor-pointer"
                        @click="openEditModal(s)"
                      >
                        <i class="bi bi-pencil"></i> Edit
                      </button>
                      <button 
                        v-if="s.employee_schedules_count === 0" 
                        type="button" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors cursor-pointer"
                        @click="deleteSchedule(s)"
                      >
                        <i class="bi bi-trash"></i> Hapus
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else class="text-center py-14 text-slate-400 text-xs">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <i class="bi bi-calendar-range text-xl"></i>
          </div>
          <p class="font-semibold text-slate-600">Belum ada jadwal kerja yang dikonfigurasi</p>
          <p class="text-[11px] text-slate-400 mt-0.5">Klik tombol "Tambah Shift Baru" di atas</p>
        </div>
      </div>
    </div>

    <!-- Centered Modal Form Create / Edit Shift Window -->
    <div 
      v-if="showModal" 
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-xl w-full my-auto overflow-hidden animate-in fade-in zoom-in-95 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs">
              <i class="bi bi-calendar-range text-lg"></i>
            </div>
            <div>
              <h5 class="text-base font-bold text-slate-900 leading-tight">
                {{ editingSchedule ? 'Ubah Shift Kerja' : 'Tambah Shift Kerja Baru' }}
              </h5>
              <p class="text-xs text-slate-400 mt-0.5">Konfigurasi parameter jam kerja operasional</p>
            </div>
          </div>
          <button 
            type="button" 
            class="w-9 h-9 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer"
            @click="closeModal"
          >
            <i class="bi bi-x-lg text-sm"></i>
          </button>
        </div>

        <form novalidate @submit.prevent="submit" class="flex flex-col flex-1 overflow-hidden">
          <div class="p-6 space-y-4 text-xs text-slate-700 overflow-y-auto flex-1">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nama Shift *</label>
              <input 
                v-model="form.name" 
                @input="form.clearErrors('name')"
                type="text" 
                :class="form.errors.name 
                  ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                  : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all font-medium" 
                placeholder="Contoh: Shift Reguler, Shift Pagi, Shift Malam" 
              />
              <div v-if="form.errors.name" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ form.errors.name }}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jam Masuk *</label>
                <input 
                  v-model="form.start_time" 
                  @input="form.clearErrors('start_time')"
                  type="time" 
                  :class="form.errors.start_time 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all font-semibold" 
                />
                <div v-if="form.errors.start_time" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.start_time }}</span>
                </div>
              </div>
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jam Pulang *</label>
                <input 
                  v-model="form.end_time" 
                  @input="form.clearErrors('end_time')"
                  type="time" 
                  :class="form.errors.end_time 
                    ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all font-semibold" 
                />
                <div v-if="form.errors.end_time" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                  <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                  <span>{{ form.errors.end_time }}</span>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Toleransi Keterlambatan (Menit) *</label>
              <input 
                v-model.number="form.tolerance_minutes" 
                @input="form.clearErrors('tolerance_minutes')"
                type="number" 
                min="0" 
                max="120" 
                :class="form.errors.tolerance_minutes 
                  ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                  : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border outline-none transition-all font-semibold" 
              />
              <span class="text-[11px] text-slate-400 block mt-1">Check-in setelah menit ini akan otomatis ditandai "Terlambat".</span>
              <div v-if="form.errors.tolerance_minutes" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ form.errors.tolerance_minutes }}</span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Hari Kerja Operasional *</label>
              <div class="flex flex-wrap gap-2">
                <label 
                  v-for="d in dayOptions" 
                  :key="d.value" 
                  class="inline-flex items-center px-3 py-1.5 rounded-xl border text-xs font-semibold cursor-pointer transition-all"
                  :class="form.days.includes(d.value) ? 'bg-blue-50 text-blue-700 border-blue-300 ring-1 ring-blue-400/20' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
                >
                  <input type="checkbox" :value="d.value" v-model="form.days" @change="form.clearErrors('days')" class="hidden" />
                  <span>{{ d.label }}</span>
                </label>
              </div>
              <div v-if="form.errors.days" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ form.errors.days }}</span>
              </div>
            </div>
          </div>

          <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 shrink-0">
            <button 
              type="button" 
              class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-200 text-xs font-semibold transition-colors cursor-pointer" 
              @click="closeModal"
            >
              Batal
            </button>
            <button 
              type="submit" 
              class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
              :disabled="form.processing"
            >
              <i class="bi bi-check2"></i>
              <span v-if="form.processing">Menyimpan...</span>
              <span v-else>Simpan Shift Kerja</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
