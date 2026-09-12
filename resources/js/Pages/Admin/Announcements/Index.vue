<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  announcements: Array,
  flash: Object,
});

const showCreateModal = ref(false);

const form = useForm({
  title: '',
  content: '',
  type: 'info',
  is_active: true,
});

const openCreate = () => {
  form.reset();
  form.clearErrors();
  showCreateModal.value = true;
};

const submit = () => {
  form.clearErrors();
  let hasError = false;

  if (!form.title.trim()) {
    form.setError('title', 'Judul pengumuman wajib diisi');
    hasError = true;
  }
  if (!form.content.trim()) {
    form.setError('content', 'Isi pengumuman wajib diisi');
    hasError = true;
  }

  if (hasError) return;

  form.post(route('admin.announcements.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false;
      form.reset();
    },
  });
};

const toggleStatus = (announcement) => {
  router.post(route('admin.announcements.toggle-status', announcement.id), {}, {
    preserveScroll: true,
  });
};

const deleteAnnouncement = (announcement) => {
  if (confirm(`Apakah Anda yakin ingin menghapus pengumuman "${announcement.title}"?`)) {
    router.delete(route('admin.announcements.destroy', announcement.id), {
      preserveScroll: true,
    });
  }
};

const getTypeConfig = (type) => {
  switch (type) {
    case 'warning':
      return { label: 'Peringatan / Aturan', bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-200', icon: 'bi-exclamation-triangle-fill' };
    case 'danger':
      return { label: 'Penting / Mendesak', bg: 'bg-rose-50', text: 'text-rose-700', border: 'border-rose-200', icon: 'bi-shield-exclamation' };
    case 'primary':
      return { label: 'Agenda Perusahaan', bg: 'bg-indigo-50', text: 'text-indigo-700', border: 'border-indigo-200', icon: 'bi-calendar-star-fill' };
    default:
      return { label: 'Informasi Umum', bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200', icon: 'bi-info-circle-fill' };
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <AdminLayout>
    <Head title="Papan Pengumuman - HRIS" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1">
              <i class="bi bi-megaphone-fill"></i>
              <span>Komunikasi Internal</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Papan Pengumuman</h1>
            <p class="text-sm text-slate-500 mt-0.5">
              Siarkan berita, kebijakan kerja, pengingat, dan agenda penting ke beranda seluruh pegawai.
            </p>
          </div>

          <button
            type="button"
            @click="openCreate"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-xs shadow-blue-600/20 transition-all active:scale-95 cursor-pointer"
          >
            <i class="bi bi-plus-lg"></i>
            <span>Buat Pengumuman</span>
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

      <!-- Announcements Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-if="announcements.length === 0" class="col-span-full bg-white rounded-3xl border border-slate-100 p-12 text-center text-slate-400">
          <i class="bi bi-chat-square-text text-4xl mb-3 block text-slate-300"></i>
          <p class="text-base font-semibold text-slate-600">Belum Ada Pengumuman</p>
          <p class="text-xs text-slate-400 mt-1">Klik tombol "Buat Pengumuman" untuk menyiarkan informasi ke seluruh staf.</p>
        </div>

        <div
          v-for="ann in announcements"
          :key="ann.id"
          class="bg-white rounded-3xl border border-slate-100 p-5 shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden"
          :class="{ 'opacity-65 bg-slate-50/50': !ann.is_active }"
        >
          <div>
            <!-- Header Card with Badge & Active Switch -->
            <div class="flex items-center justify-between gap-2 mb-3">
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border"
                :class="[getTypeConfig(ann.type).bg, getTypeConfig(ann.type).text, getTypeConfig(ann.type).border]"
              >
                <i :class="getTypeConfig(ann.type).icon"></i>
                <span>{{ getTypeConfig(ann.type).label }}</span>
              </span>

              <button
                type="button"
                @click="toggleStatus(ann)"
                class="text-xs font-semibold px-2.5 py-0.5 rounded-full transition cursor-pointer"
                :class="ann.is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'"
              >
                {{ ann.is_active ? 'Aktif' : 'Diarsipkan' }}
              </button>
            </div>

            <!-- Title & Content -->
            <h3 class="font-bold text-slate-900 text-base mb-2 leading-snug">{{ ann.title }}</h3>
            <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{{ ann.content }}</p>
          </div>

          <!-- Footer with Meta & Delete -->
          <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
            <div class="flex items-center gap-1.5">
              <i class="bi bi-clock"></i>
              <span>{{ formatDate(ann.created_at) }}</span>
            </div>

            <button
              type="button"
              @click="deleteAnnouncement(ann)"
              class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1.5 rounded-lg transition"
              title="Hapus Pengumuman"
            >
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Announcement Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
              <i class="bi bi-megaphone-fill"></i>
            </div>
            <h2 class="font-bold text-slate-900 text-base">Buat Pengumuman Baru</h2>
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
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tipe Pengumuman</label>
            <select
              v-model="form.type"
              class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
            >
              <option value="info">Informasi Umum (Biru)</option>
              <option value="primary">Agenda Perusahaan (Indigo)</option>
              <option value="warning">Peringatan / Aturan (Amber)</option>
              <option value="danger">Penting / Mendesak (Merah)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Judul Pengumuman</label>
            <input
              type="text"
              v-model="form.title"
              placeholder="Contoh: Pembaruan Jadwal Kerja Fleksibel"
              class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
              :class="{ 'border-rose-400 bg-rose-50/50': form.errors.title }"
            />
            <p v-if="form.errors.title" class="mt-1 text-xs text-rose-600">{{ form.errors.title }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Isi Pesan Pengumuman</label>
            <textarea
              rows="4"
              v-model="form.content"
              placeholder="Tuliskan detail pengumuman secara jelas untuk karyawan..."
              class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3.5 py-2 text-sm text-slate-800 transition"
              :class="{ 'border-rose-400 bg-rose-50/50': form.errors.content }"
            ></textarea>
            <p v-if="form.errors.content" class="mt-1 text-xs text-rose-600">{{ form.errors.content }}</p>
          </div>

          <div class="flex items-center gap-2 pt-2">
            <input
              type="checkbox"
              id="is_active"
              v-model="form.is_active"
              class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300"
            />
            <label for="is_active" class="text-xs font-medium text-slate-700 cursor-pointer">
              Langsung tayangkan di beranda presensi pegawai
            </label>
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
              {{ form.processing ? 'Menyimpan...' : 'Terbitkan Pengumuman' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
