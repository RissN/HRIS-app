<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  attendances: [Object, Array],
  departments: Array,
  filters: Object,
});

const attendanceList = computed(() => {
  return Array.isArray(props.attendances) ? props.attendances : (props.attendances?.data || []);
});

const filterDate = ref(props.filters.date || '');
const filterDept = ref(props.filters.department || 'all');
const search = ref(props.filters.search || '');

const applyFilter = () => {
  router.get(route('admin.attendance.index'), {
    date: filterDate.value,
    department: filterDept.value,
    search: search.value,
  }, { preserveState: true });
};

// Edit attendance modal
const showEditModal = ref(false);
const editingAttendance = ref(null);

const editForm = useForm({
  check_in_at: '',
  check_out_at: '',
  status: 'present',
  note: '',
});

const statusOptions = [
  { value: 'present', label: 'Hadir', icon: 'bi-check-circle-fill', color: 'text-emerald-700 bg-emerald-50 border-emerald-300 ring-emerald-500/20' },
  { value: 'late', label: 'Terlambat', icon: 'bi-clock-history', color: 'text-amber-700 bg-amber-50 border-amber-300 ring-amber-500/20' },
  { value: 'wfh', label: 'WFH', icon: 'bi-laptop', color: 'text-blue-700 bg-blue-50 border-blue-300 ring-blue-500/20' },
  { value: 'permission', label: 'Izin', icon: 'bi-file-text', color: 'text-sky-700 bg-sky-50 border-sky-300 ring-sky-500/20' },
  { value: 'sick', label: 'Sakit', icon: 'bi-bandaid', color: 'text-purple-700 bg-purple-50 border-purple-300 ring-purple-500/20' },
  { value: 'absent', label: 'Alpa / Tidak Hadir', icon: 'bi-x-circle', color: 'text-rose-700 bg-rose-50 border-rose-300 ring-rose-500/20' },
];

const openEdit = (att) => {
  editingAttendance.value = att;
  editForm.check_in_at = att.check_in_at ? att.check_in_at.substring(0, 16) : '';
  editForm.check_out_at = att.check_out_at ? att.check_out_at.substring(0, 16) : '';
  editForm.status = att.status;
  editForm.note = att.note || '';
  showEditModal.value = true;
};

const submitEdit = () => {
  if (!editingAttendance.value) return;

  editForm.clearErrors();
  let hasError = false;

  if (!editForm.status) {
    editForm.setError('status', 'Pilih status kehadiran baru');
    hasError = true;
  }
  if (!editForm.note || !editForm.note.trim()) {
    editForm.setError('note', 'Keterangan atau alasan koreksi presensi wajib diisi');
    hasError = true;
  }

  if (hasError) return;

  editForm.put(route('admin.attendance.update', editingAttendance.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false;
    },
  });
};

const formatTime = (timeStr) => {
  if (!timeStr) return '--:--';
  const date = new Date(timeStr);
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <AdminLayout>
    <Head title="Monitoring Presensi Pegawai" />

    <div class="space-y-6">
      <!-- Header & Filters Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="pb-4 mb-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Monitoring & Koreksi Presensi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pantau data kehadiran real-time seluruh pegawai dan lakukan koreksi jika diperlukan.</p>
          </div>
          <div class="text-xs text-slate-400 font-medium">
            Total Record: <strong class="text-slate-800">{{ (attendances?.total ?? attendances?.length ?? 0).toLocaleString('id-ID') }}</strong>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
          <div class="sm:col-span-5">
            <input 
              v-model="search" 
              type="text" 
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
              placeholder="Cari nama atau ID pegawai (contoh: Budi, TJT1001)..." 
              @keyup.enter="applyFilter"
            />
          </div>

          <div class="sm:col-span-3">
            <select 
              v-model="filterDept" 
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors" 
              @change="applyFilter"
            >
              <option value="all">Semua Departemen</option>
              <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
            </select>
          </div>

          <div class="sm:col-span-4 flex items-center gap-2">
            <input 
              v-model="filterDate" 
              type="date" 
              class="flex-1 px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors" 
              @change="applyFilter"
            />
            <button 
              type="button" 
              class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer" 
              @click="applyFilter"
            >
              Filter
            </button>
            <button 
              type="button" 
              class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-colors cursor-pointer" 
              @click="search = ''; filterDept = 'all'; filterDate = ''; applyFilter()"
              title="Reset Filter"
            >
              <i class="bi bi-arrow-counterclockwise"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Attendance Table Card -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
        <div v-if="attendanceList && attendanceList.length > 0">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="bg-slate-50/60 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-4">Pegawai</th>
                  <th class="py-3 px-4">Tanggal</th>
                  <th class="py-3 px-4">Jam Masuk</th>
                  <th class="py-3 px-4">Jam Keluar</th>
                  <th class="py-3 px-4">Status</th>
                  <th class="py-3 px-4">Keterangan / Bukti</th>
                  <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="att in attendanceList" :key="att.id" class="hover:bg-slate-50/70 transition-colors">
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-slate-900 text-xs">{{ att.employee?.user?.name || '-' }}</span>
                      <span v-if="att.employee?.employee_code" class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-700">
                        {{ att.employee.employee_code }}
                      </span>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5">
                      {{ att.employee?.position }} &bull; {{ att.employee?.department }}
                      <span v-if="att.employee?.region_label" class="text-slate-400">&bull; {{ att.employee.region_label }}</span>
                    </div>
                  </td>
                  <td class="py-3 px-4 font-medium text-slate-700 whitespace-nowrap">{{ formatDate(att.date) }}</td>
                  <td class="py-3 px-4 font-bold text-emerald-600 whitespace-nowrap">{{ formatTime(att.check_in_at) }}</td>
                  <td class="py-3 px-4 font-bold text-blue-600 whitespace-nowrap">{{ formatTime(att.check_out_at) }}</td>
                  <td class="py-3 px-4 whitespace-nowrap"><StatusBadge :status="att.status" /></td>
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-2">
                      <a 
                        v-if="att.photo_path" 
                        :href="att.photo_path" 
                        target="_blank" 
                        title="Lihat Foto Selfie" 
                        class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors"
                      >
                        <i class="bi bi-camera text-xs"></i>
                      </a>
                      <span class="text-slate-600 max-w-xs truncate text-[11px]">{{ att.note || '-' }}</span>
                    </div>
                  </td>
                  <td class="py-3 px-4 text-right whitespace-nowrap">
                    <button 
                      type="button" 
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200/80 transition-colors cursor-pointer" 
                      @click="openEdit(att)"
                    >
                      <i class="bi bi-pencil-square"></i>
                      <span>Koreksi</span>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Controls -->
          <div class="p-4 border-t border-slate-100">
            <Pagination 
              v-if="attendances?.links" 
              :links="attendances.links" 
              :from="attendances.from" 
              :to="attendances.to" 
              :total="attendances.total" 
            />
          </div>
        </div>
        <div v-else class="text-center py-14 text-slate-400 text-xs">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <i class="bi bi-calendar-x text-xl"></i>
          </div>
          <p class="font-semibold text-slate-600">Tidak ditemukan data absensi</p>
          <p class="text-[11px] text-slate-400 mt-0.5">Silakan sesuaikan filter tanggal atau departemen di atas</p>
        </div>
      </div>
    </div>

    <!-- Centered Modal Dialog for Attendance Correction -->
    <div 
      v-if="showEditModal && editingAttendance" 
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
      @click.self="showEditModal = false"
    >
      <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-xl w-full my-auto overflow-hidden animate-in fade-in zoom-in-95 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-xs">
              <i class="bi bi-pencil-square text-lg"></i>
            </div>
            <div>
              <h4 class="text-base font-bold text-slate-900 leading-tight">
                Koreksi Presensi Pegawai
              </h4>
              <p class="text-xs text-slate-400 mt-0.5">
                {{ editingAttendance.employee?.user?.name }} &bull; {{ formatDate(editingAttendance.date) }}
              </p>
            </div>
          </div>
          <button 
            type="button" 
            class="w-9 h-9 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer"
            @click="showEditModal = false"
          >
            <i class="bi bi-x-lg text-sm"></i>
          </button>
        </div>

        <form novalidate @submit.prevent="submitEdit" class="flex flex-col flex-1 overflow-hidden">
          <div class="p-6 space-y-4 text-xs text-slate-700 overflow-y-auto flex-1">
            <!-- Employee Quick Card -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
              <div>
                <div class="font-bold text-slate-900 text-sm">{{ editingAttendance.employee?.user?.name }}</div>
                <div class="text-[11px] text-slate-500">{{ editingAttendance.employee?.position }} &bull; {{ editingAttendance.employee?.department }}</div>
              </div>
              <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Status Saat Ini</span>
                <StatusBadge :status="editingAttendance.status" />
              </div>
            </div>

            <!-- Status Selector Pills -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Status Kehadiran Baru *</label>
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                <button
                  v-for="opt in statusOptions"
                  :key="opt.value"
                  type="button"
                  class="flex items-center gap-2 p-2.5 rounded-xl border text-xs font-semibold text-left transition-all cursor-pointer"
                  :class="editForm.status === opt.value ? opt.color + ' ring-2' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
                  @click="editForm.status = opt.value; editForm.clearErrors('status')"
                >
                  <i :class="['bi', opt.icon, 'text-sm']"></i>
                  <span>{{ opt.label }}</span>
                </button>
              </div>
              <div v-if="editForm.errors.status" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ editForm.errors.status }}</span>
              </div>
            </div>

            <!-- Times Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                  Waktu Check-In (Masuk)
                </label>
                <input 
                  v-model="editForm.check_in_at" 
                  type="datetime-local" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all font-medium" 
                />
              </div>
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                  Waktu Check-Out (Pulang)
                </label>
                <input 
                  v-model="editForm.check_out_at" 
                  type="datetime-local" 
                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all font-medium" 
                />
              </div>
            </div>

            <!-- Note Textarea -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Keterangan / Alasan Koreksi Presensi *
              </label>
              <textarea 
                v-model="editForm.note" 
                @input="editForm.clearErrors('note')"
                :class="editForm.errors.note 
                  ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                  : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20'"
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border placeholder-slate-400 outline-none transition-all leading-relaxed" 
                rows="3" 
                placeholder="Contoh: Koreksi absensi berdasarkan surat dinas luar atau perbaikan kendala teknis..."
              ></textarea>
              <div v-if="editForm.errors.note" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ editForm.errors.note }}</span>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2 shrink-0">
            <button 
              type="button" 
              class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-200 text-xs font-semibold transition-colors cursor-pointer" 
              @click="showEditModal = false"
            >
              Batal
            </button>
            <button 
              type="submit" 
              class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
              :disabled="editForm.processing"
            >
              <i class="bi bi-check2"></i>
              <span>Simpan Koreksi Presensi</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
