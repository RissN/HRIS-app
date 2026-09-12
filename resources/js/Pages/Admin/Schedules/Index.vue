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

    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4 mb-4">
      <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
        <div>
          <h4 class="fw-bold text-white mb-1">Pengaturan Shift & Jadwal Kerja</h4>
          <p class="text-secondary small mb-0">Atur jam masuk, jam pulang, toleransi keterlambatan, dan hari operasional.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm px-3 py-2 d-inline-flex align-items-center gap-2 shadow" @click="openCreateModal">
          <i class="bi bi-plus-circle"></i>
          <span>Tambah Shift Baru</span>
        </button>
      </div>
    </div>

    <!-- Schedules Table -->
    <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4">
      <div v-if="schedules && schedules.length > 0" class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
          <thead>
            <tr class="text-secondary border-bottom border-secondary border-opacity-25">
              <th>Nama Shift</th>
              <th>Jam Masuk</th>
              <th>Jam Keluar</th>
              <th>Hari Aktif</th>
              <th>Toleransi Telat</th>
              <th>Jumlah Pegawai</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in schedules" :key="s.id" class="border-bottom border-secondary border-opacity-10">
              <td class="fw-bold text-white">{{ s.name }}</td>
              <td class="text-success fw-bold">{{ s.start_time.substring(0, 5) }}</td>
              <td class="text-info fw-bold">{{ s.end_time.substring(0, 5) }}</td>
              <td class="text-white">{{ formatDays(s.days) }}</td>
              <td class="text-warning fw-medium">{{ s.tolerance_minutes }} Menit</td>
              <td>
                <span class="badge bg-secondary">
                  {{ s.employee_schedules_count }} Pegawai
                </span>
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-2">
                  <button type="button" class="btn btn-sm btn-outline-info py-1 px-2" style="font-size: 0.75rem;" @click="openEditModal(s)">
                    <i class="bi bi-pencil"></i> Edit
                  </button>
                  <button 
                    v-if="s.employee_schedules_count === 0" 
                    type="button" 
                    class="btn btn-sm btn-outline-danger py-1 px-2" 
                    style="font-size: 0.75rem;"
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
      <div v-else class="text-center py-5 text-secondary">
        Belum ada jadwal kerja yang dikonfigurasi.
      </div>
    </div>

    <!-- Modal Form Create / Edit -->
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65);" @click.self="closeModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border border-secondary border-opacity-25 shadow-lg">
          <div class="modal-header border-bottom border-secondary border-opacity-25">
            <h5 class="modal-title fs-6 fw-bold text-white d-flex align-items-center gap-2">
              <i class="bi bi-calendar-range text-primary"></i>
              {{ editingSchedule ? 'Ubah Shift Kerja' : 'Tambah Shift Kerja Baru' }}
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="closeModal"></button>
          </div>

          <form @submit.prevent="submit">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label small text-secondary fw-semibold">Nama Shift *</label>
                <input v-model="form.name" type="text" class="form-control" placeholder="Contoh: Shift Reguler, Shift Pagi, dsb." required />
                <div v-if="form.errors.name" class="text-danger small mt-1">{{ form.errors.name }}</div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-6">
                  <label class="form-label small text-secondary fw-semibold">Jam Masuk (Format 24 Jam) *</label>
                  <input v-model="form.start_time" type="time" class="form-control" required />
                  <div v-if="form.errors.start_time" class="text-danger small mt-1">{{ form.errors.start_time }}</div>
                </div>
                <div class="col-6">
                  <label class="form-label small text-secondary fw-semibold">Jam Pulang (Format 24 Jam) *</label>
                  <input v-model="form.end_time" type="time" class="form-control" required />
                  <div v-if="form.errors.end_time" class="text-danger small mt-1">{{ form.errors.end_time }}</div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small text-secondary fw-semibold">Toleransi Keterlambatan (Menit) *</label>
                <input v-model.number="form.tolerance_minutes" type="number" min="0" max="120" class="form-control" required />
                <small class="text-secondary" style="font-size: 0.72rem;">Check-in setelah batas toleransi akan berstatus "Terlambat".</small>
                <div v-if="form.errors.tolerance_minutes" class="text-danger small mt-1">{{ form.errors.tolerance_minutes }}</div>
              </div>

              <div class="mb-2">
                <label class="form-label small text-secondary fw-semibold d-block">Hari Kerja Aktif *</label>
                <div class="d-flex flex-wrap gap-2">
                  <div v-for="d in dayOptions" :key="d.value" class="form-check form-check-inline m-0">
                    <input 
                      :id="'day-' + d.value" 
                      v-model="form.days" 
                      type="checkbox" 
                      :value="d.value" 
                      class="btn-check" 
                    />
                    <label 
                      :for="'day-' + d.value" 
                      class="btn btn-sm btn-outline-primary py-1 px-2"
                      style="font-size: 0.75rem;"
                    >
                      {{ d.label }}
                    </label>
                  </div>
                </div>
                <div v-if="form.errors.days" class="text-danger small mt-1">{{ form.errors.days }}</div>
              </div>
            </div>

            <div class="modal-footer border-top border-secondary border-opacity-25 py-2">
              <button type="button" class="btn btn-secondary btn-sm" @click="closeModal">Batal</button>
              <button type="submit" class="btn btn-primary btn-sm px-3" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                Simpan Jadwal
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
