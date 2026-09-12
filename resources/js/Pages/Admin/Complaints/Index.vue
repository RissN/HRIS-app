<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ComplaintOffcanvas from '@/Components/ComplaintOffcanvas.vue';

const props = defineProps({
  complaints: Array,
  departments: Array,
  filters: Object,
});

const status = ref(props.filters.status || 'all');
const type = ref(props.filters.type || 'all');
const department = ref(props.filters.department || 'all');
const date = ref(props.filters.date || '');

const applyFilter = () => {
  router.get(route('admin.complaints.index'), {
    status: status.value,
    type: type.value,
    department: department.value,
    date: date.value,
  }, { preserveState: true });
};

// Offcanvas drawer
const selectedComplaint = ref(null);
const showOffcanvas = ref(false);

const openOffcanvas = (c) => {
  selectedComplaint.value = c;
  showOffcanvas.value = true;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'short', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Komplain Presensi" />

    <div class="space-y-6">
      <!-- Header & Filters Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="pb-4 mb-4 border-b border-slate-100">
          <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Manajemen Komplain Presensi</h1>
          <p class="text-xs text-slate-500 mt-0.5">Tinjau keluhan staf, verifikasi kendala absensi, dan koreksi data kehadiran langsung.</p>
        </div>

        <!-- Filters Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</label>
            <select 
              v-model="status" 
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors" 
              @change="applyFilter"
            >
              <option value="all">Semua Status</option>
              <option value="pending">Menunggu (Pending)</option>
              <option value="in_review">Sedang Ditinjau</option>
              <option value="resolved">Selesai (Resolved)</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Jenis Masalah</label>
            <select 
              v-model="type" 
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors" 
              @change="applyFilter"
            >
              <option value="all">Semua Masalah</option>
              <option value="wrong_time">Waktu Tidak Sesuai</option>
              <option value="location_error">Gagal Lokasi GPS</option>
              <option value="forgot_checkout">Lupa Check-out</option>
              <option value="system_error">Error Sistem</option>
              <option value="other">Lainnya</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Departemen</label>
            <select 
              v-model="department" 
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors" 
              @change="applyFilter"
            >
              <option value="all">Semua Departemen</option>
              <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal Kejadian</label>
            <div class="flex items-center gap-2">
              <input 
                v-model="date" 
                type="date" 
                class="flex-1 px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors" 
                @change="applyFilter"
              />
              <button 
                type="button" 
                class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors" 
                @click="date = ''; applyFilter()"
              >
                Reset
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Complaints List / Table -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
        <!-- Desktop Table -->
        <div v-if="complaints && complaints.length > 0" class="hidden md:block overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="bg-slate-50/60 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4">Pegawai</th>
                <th class="py-3 px-4">Tgl Kejadian</th>
                <th class="py-3 px-4">Kategori Masalah</th>
                <th class="py-3 px-4">Deskripsi</th>
                <th class="py-3 px-4 text-center">Bukti</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="c in complaints" :key="c.id" class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-900">{{ c.employee?.user?.name }}</div>
                  <div class="text-[11px] text-slate-500">{{ c.employee?.department || '-' }}</div>
                </td>
                <td class="py-3 px-4 font-medium text-slate-700 whitespace-nowrap">
                  {{ formatDate(c.date) }}
                </td>
                <td class="py-3 px-4">
                  <StatusBadge :status="c.type" />
                </td>
                <td class="py-3 px-4 text-slate-600 max-w-[220px] truncate" :title="c.description">
                  {{ c.description }}
                </td>
                <td class="py-3 px-4 text-center">
                  <a 
                    v-if="c.attachment" 
                    :href="c.attachment" 
                    target="_blank" 
                    class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"
                    title="Lihat Bukti Lampiran"
                  >
                    <i class="bi bi-paperclip text-sm"></i>
                  </a>
                  <span v-else class="text-slate-300">-</span>
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                  <StatusBadge :status="c.status" />
                </td>
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <button 
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors"
                    @click="openOffcanvas(c)"
                  >
                    <i class="bi bi-layout-sidebar-reverse"></i>
                    <span>Tinjau & Tindak</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Card List -->
        <div v-if="complaints && complaints.length > 0" class="md:hidden divide-y divide-slate-100">
          <div v-for="c in complaints" :key="c.id" class="p-4 space-y-3">
            <div class="flex items-start justify-between gap-2">
              <div>
                <div class="font-bold text-slate-900 text-sm">{{ c.employee?.user?.name }}</div>
                <div class="text-xs text-slate-500">{{ c.employee?.department }} &bull; {{ formatDate(c.date) }}</div>
              </div>
              <StatusBadge :status="c.status" />
            </div>

            <div class="flex items-center gap-2">
              <span class="text-[11px] text-slate-400 font-medium">Kategori:</span>
              <StatusBadge :status="c.type" />
            </div>

            <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
              {{ c.description }}
            </p>

            <div class="flex items-center justify-between pt-1">
              <div>
                <a 
                  v-if="c.attachment" 
                  :href="c.attachment" 
                  target="_blank" 
                  class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium"
                >
                  <i class="bi bi-paperclip"></i>
                  <span>Lihat Lampiran</span>
                </a>
                <span v-else class="text-[11px] text-slate-400">Tanpa lampiran</span>
              </div>
              <button 
                type="button" 
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors"
                @click="openOffcanvas(c)"
              >
                <i class="bi bi-layout-sidebar-reverse"></i>
                <span>Tinjau</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!complaints || complaints.length === 0" class="text-center py-12 px-4">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <i class="bi bi-chat-left-dots text-xl"></i>
          </div>
          <p class="text-xs font-semibold text-slate-600">Tidak ada data komplain yang ditemukan</p>
          <p class="text-[11px] text-slate-400 mt-0.5">Coba ubah filter atau tanggal pencarian di atas</p>
        </div>
      </div>
    </div>

    <!-- Complaint Offcanvas Drawer -->
    <ComplaintOffcanvas 
      :show="showOffcanvas" 
      :complaint="selectedComplaint" 
      @close="showOffcanvas = false" 
    />
  </AdminLayout>
</template>
