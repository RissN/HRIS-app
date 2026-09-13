<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  holidays: Array,
  flash: Object,
});

const showCreateModal = ref(false);

const form = useForm({
  name: '',
  date: '',
  description: '',
});

const openCreate = () => {
  form.reset();
  form.clearErrors();
  showCreateModal.value = true;
};

const submit = () => {
  form.clearErrors();
  let hasError = false;

  if (!form.name.trim()) {
    form.setError('name', 'Nama hari libur wajib diisi');
    hasError = true;
  }
  if (!form.date) {
    form.setError('date', 'Tanggal libur wajib dipilih');
    hasError = true;
  }

  if (hasError) return;

  form.post(route('admin.holidays.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false;
      form.reset();
    },
  });
};

import ConfirmModal from '@/Components/ConfirmModal.vue';

const showDeleteModal = ref(false);
const holidayToDelete = ref(null);
const isDeleting = ref(false);

const openDelete = (holiday) => {
  holidayToDelete.value = holiday;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (!holidayToDelete.value) return;
  isDeleting.value = true;
  router.delete(route('admin.holidays.destroy', holidayToDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      holidayToDelete.value = null;
    },
    onFinish: () => {
      isDeleting.value = false;
    },
  });
};

const isUpcoming = (dateStr) => {
  const holidayDate = new Date(dateStr);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return holidayDate >= today;
};

const formatDateFull = (dateStr) => {
  if (!dateStr) return '-';
  const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const getDayNumber = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).getDate();
};

const getMonthShort = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('id-ID', { month: 'short' });
};
</script>

<template>
  <AdminLayout>
    <Head title="Kalender Hari Libur - HRIS" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1">
              <i class="bi bi-calendar-heart-fill"></i>
              <span>Kalender Kerja</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Hari Libur Nasional & Cuti Bersama</h1>
            <p class="text-sm text-slate-500 mt-0.5">
              Daftar hari libur resmi untuk mencegah sistem mencatat alpa palsu pada tanggal operasional libur.
            </p>
          </div>

          <button
            type="button"
            @click="openCreate"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-xs shadow-blue-600/20 transition-all active:scale-95 cursor-pointer"
          >
            <i class="bi bi-calendar-plus"></i>
            <span>Tambah Hari Libur</span>
          </button>
        </div>
      </div>

      <!-- Flash Notification -->
      <div
        v-if="flash?.success"
        class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm"
      >
        <i class="bi bi-check-circle-fill text-emerald-600 text-lg"></i>
        <span>{{ flash.success }}</span>
      </div>

      <!-- Holidays List / Cards -->
      <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-xs">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <h2 class="font-bold text-slate-900 text-base">Jadwal Hari Libur Terdaftar</h2>
          <span class="text-xs bg-slate-100 text-slate-600 font-medium px-3 py-1 rounded-full">
            Total {{ holidays.length }} Hari Libur
          </span>
        </div>

        <div v-if="holidays.length === 0" class="p-12 text-center text-slate-400">
          <i class="bi bi-calendar2-x text-4xl mb-3 block text-slate-300"></i>
          <p class="text-base font-semibold text-slate-600">Belum Ada Hari Libur Ditambahkan</p>
          <p class="text-xs text-slate-400 mt-1">Tambahkan hari libur nasional atau cuti bersama tahun ini.</p>
        </div>

        <div v-else class="divide-y divide-slate-100">
          <div
            v-for="holiday in holidays"
            :key="holiday.id"
            class="p-4 sm:p-5 flex items-center justify-between gap-4 hover:bg-slate-50/70 transition-colors"
          >
            <div class="flex items-center gap-4">
              <!-- Date Badge Box -->
              <div
                class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center border font-bold shrink-0 text-center"
                :class="isUpcoming(holiday.date) ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-slate-100 border-slate-200 text-slate-500'"
              >
                <span class="text-lg leading-none">{{ getDayNumber(holiday.date) }}</span>
                <span class="text-[10px] uppercase tracking-wider font-semibold opacity-80">{{ getMonthShort(holiday.date) }}</span>
              </div>

              <div>
                <div class="flex items-center gap-2 mb-0.5">
                  <h3 class="font-bold text-slate-900 text-sm sm:text-base">{{ holiday.name }}</h3>
                  <span
                    v-if="isUpcoming(holiday.date)"
                    class="text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md"
                  >
                    Akan Datang
                  </span>
                  <span
                    v-else
                    class="text-[10px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md"
                  >
                    Sudah Lewat
                  </span>
                </div>
                <p class="text-xs text-slate-500">{{ formatDateFull(holiday.date) }}</p>
                <p v-if="holiday.description" class="text-xs text-slate-400 mt-1 italic">
                  {{ holiday.description }}
                </p>
              </div>
            </div>

            <!-- Delete Action -->
            <button
              type="button"
              @click="openDelete(holiday)"
              class="w-9 h-9 rounded-xl border border-slate-200 text-rose-500 hover:text-white hover:bg-rose-600 hover:border-rose-600 flex items-center justify-center transition shadow-xs cursor-pointer"
              title="Hapus Hari Libur"
            >
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Holiday Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
              <i class="bi bi-calendar-plus-fill"></i>
            </div>
            <h2 class="font-bold text-slate-900 text-base">Tambah Hari Libur</h2>
          </div>
          <button
            type="button"
            @click="showCreateModal = false"
            class="w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Hari Libur</label>
            <input
              type="text"
              v-model="form.name"
              placeholder="Contoh: Maulid Nabi Muhammad SAW"
              class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
              :class="{ 'border-rose-400 bg-rose-50/50': form.errors.name }"
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Libur</label>
            <input
              type="date"
              v-model="form.date"
              class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
              :class="{ 'border-rose-400 bg-rose-50/50': form.errors.date }"
            />
            <p v-if="form.errors.date" class="mt-1 text-xs text-rose-600">{{ form.errors.date }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Keterangan (Opsional)</label>
            <input
              type="text"
              v-model="form.description"
              placeholder="Contoh: Hari Libur Nasional Keagamaan"
              class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
            />
          </div>

          <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <button
              type="button"
              @click="showCreateModal = false"
              class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs shadow-blue-600/20 transition active:scale-95 disabled:opacity-50"
            >
              {{ form.processing ? 'Menyimpan...' : 'Simpan Hari Libur' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal 
      :show="showDeleteModal"
      title="Hapus Hari Libur?"
      :message="holidayToDelete ? `Hapus hari libur '${holidayToDelete.name}' (${holidayToDelete.date}) dari kalender perusahaan?` : ''"
      confirm-text="Ya, Hapus Libur"
      cancel-text="Batal"
      type="danger"
      :loading="isDeleting"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
    />
  </AdminLayout>
</template>
