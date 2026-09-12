<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import LeaveRequestModal from '@/Components/LeaveRequestModal.vue';

const props = defineProps({
  leaveRequests: Array,
  departments: Array,
  filters: Object,
});

const status = ref(props.filters.status || 'all');
const type = ref(props.filters.type || 'all');
const department = ref(props.filters.department || 'all');

const applyFilter = () => {
  router.get(route('admin.leave-requests.index'), {
    status: status.value,
    type: type.value,
    department: department.value,
  }, { preserveState: true });
};

// Detail modal
const selectedRequest = ref(null);
const showDetailModal = ref(false);

const openDetail = (item) => {
  selectedRequest.value = item;
  showDetailModal.value = true;
};

// Approve
const approveForm = useForm({});
const approveRequest = (item) => {
  if (confirm(`Apakah Anda yakin ingin menyetujui pengajuan cuti dari ${item.employee?.user?.name}? Data absensi akan otomatis disinkronkan.`)) {
    approveForm.post(route('admin.leave-requests.approve', item.id), {
      preserveScroll: true,
    });
  }
};

// Reject modal
const showRejectModal = ref(false);
const rejectingItem = ref(null);
const rejectForm = useForm({
  reject_reason: '',
});

const openReject = (item) => {
  rejectingItem.value = item;
  rejectForm.reject_reason = '';
  showRejectModal.value = true;
};

const submitReject = () => {
  if (!rejectingItem.value) return;

  rejectForm.clearErrors();
  if (!rejectForm.reject_reason || !rejectForm.reject_reason.trim()) {
    rejectForm.setError('reject_reason', 'Alasan penolakan wajib diisi secara jelas');
    return;
  }

  rejectForm.post(route('admin.leave-requests.reject', rejectingItem.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showRejectModal.value = false;
      rejectingItem.value = null;
    },
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'short', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Pengajuan Cuti & Izin" />

    <div class="space-y-6">
      <!-- Header & Filters Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="pb-4 mb-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Manajemen Pengajuan Cuti & Izin</h1>
            <p class="text-xs text-slate-500 mt-0.5">Tinjau, setujui, atau tolak permohonan cuti staf secara terpusat.</p>
          </div>
          <div class="text-xs text-slate-400 font-medium">
            Total Pengajuan: <strong class="text-slate-800">{{ leaveRequests?.length || 0 }}</strong>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</label>
            <select 
              v-model="status" 
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors" 
              @change="applyFilter"
            >
              <option value="all">Semua Status</option>
              <option value="pending">Menunggu (Pending)</option>
              <option value="approved">Disetujui</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Kategori Cuti</label>
            <select 
              v-model="type" 
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors" 
              @change="applyFilter"
            >
              <option value="all">Semua Kategori</option>
              <option value="annual_leave">Cuti Tahunan</option>
              <option value="sick">Sakit</option>
              <option value="permission">Izin Pribadi</option>
              <option value="emergency_leave">Cuti Darurat</option>
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
        </div>
      </div>

      <!-- Requests Table Card -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
        <div v-if="leaveRequests && leaveRequests.length > 0" class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="bg-slate-50/60 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4">Pegawai</th>
                <th class="py-3 px-4">Jenis</th>
                <th class="py-3 px-4">Periode</th>
                <th class="py-3 px-4">Durasi</th>
                <th class="py-3 px-4">Alasan</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="item in leaveRequests" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-900 text-xs">{{ item.employee?.user?.name }}</div>
                  <div class="text-[11px] text-slate-500">{{ item.employee?.department || '-' }}</div>
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                  <StatusBadge :status="item.type" />
                </td>
                <td class="py-3 px-4 font-medium text-slate-700 whitespace-nowrap">
                  {{ formatDate(item.start_date) }} &ndash; {{ formatDate(item.end_date) }}
                </td>
                <td class="py-3 px-4 font-bold text-blue-600 whitespace-nowrap">
                  {{ item.total_days }} Hari
                </td>
                <td class="py-3 px-4 text-slate-600 max-w-xs truncate" :title="item.reason">
                  {{ item.reason }}
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                  <StatusBadge :status="item.status" />
                </td>
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <div class="inline-flex gap-1.5">
                    <button 
                      type="button" 
                      class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors cursor-pointer" 
                      @click="openDetail(item)"
                    >
                      <i class="bi bi-eye"></i>
                      <span>Detail</span>
                    </button>

                    <template v-if="item.status === 'pending'">
                      <button 
                        type="button" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors cursor-pointer shadow-xs" 
                        @click="approveRequest(item)"
                      >
                        <i class="bi bi-check2"></i>
                        <span>Setujui</span>
                      </button>
                      <button 
                        type="button" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl bg-rose-600 hover:bg-rose-700 text-white transition-colors cursor-pointer shadow-xs" 
                        @click="openReject(item)"
                      >
                        <i class="bi bi-x"></i>
                        <span>Tolak</span>
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-14 text-slate-400 text-xs">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <i class="bi bi-file-earmark-x text-xl"></i>
          </div>
          <p class="font-semibold text-slate-600">Tidak ada pengajuan cuti</p>
          <p class="text-[11px] text-slate-400 mt-0.5">Semua permohonan staf telah diproses</p>
        </div>
      </div>
    </div>

    <!-- Centered Reject Reason Modal Window -->
    <div 
      v-if="showRejectModal && rejectingItem" 
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
      @click.self="showRejectModal = false"
    >
      <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-lg w-full my-auto overflow-hidden animate-in fade-in zoom-in-95 flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shadow-xs">
              <i class="bi bi-x-circle text-lg"></i>
            </div>
            <div>
              <h5 class="text-base font-bold text-slate-900">Tolak Permohonan Cuti</h5>
              <p class="text-xs text-slate-400">Pengajuan #{{ rejectingItem.id }}</p>
            </div>
          </div>
          <button 
            type="button" 
            class="w-9 h-9 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer"
            @click="showRejectModal = false"
          >
            <i class="bi bi-x-lg text-sm"></i>
          </button>
        </div>

        <form novalidate @submit.prevent="submitReject">
          <div class="p-6 space-y-4 text-xs text-slate-700">
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
              <div>
                <div class="font-bold text-slate-900">{{ rejectingItem.employee?.user?.name }}</div>
                <div class="text-[11px] text-slate-500">{{ rejectingItem.employee?.position }} &bull; {{ rejectingItem.total_days }} Hari Kerja</div>
              </div>
              <StatusBadge :status="rejectingItem.type" />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Alasan Penolakan (Wajib Diisi) *
              </label>
              <textarea 
                v-model="rejectForm.reject_reason" 
                @input="rejectForm.clearErrors('reject_reason')"
                :class="rejectForm.errors.reject_reason 
                  ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                  : 'border-slate-200 bg-slate-50 text-slate-900 focus:bg-white focus:border-rose-600 focus:ring-2 focus:ring-rose-500/20'"
                class="w-full px-3.5 py-2.5 text-xs rounded-xl border placeholder-slate-400 outline-none transition-all leading-relaxed" 
                rows="4" 
                placeholder="Jelaskan alasan mengapa permohonan ini ditolak secara jelas untuk pegawai..." 
              ></textarea>
              <div v-if="rejectForm.errors.reject_reason" class="flex items-center gap-1.5 text-rose-600 text-xs mt-1.5 font-medium animate-in fade-in slide-in-from-top-1">
                <i class="bi bi-exclamation-circle-fill text-xs shrink-0"></i>
                <span>{{ rejectForm.errors.reject_reason }}</span>
              </div>
            </div>
          </div>

          <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
            <button 
              type="button" 
              class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-200 text-xs font-semibold transition-colors cursor-pointer" 
              @click="showRejectModal = false"
            >
              Batal
            </button>
            <button 
              type="submit" 
              class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer flex items-center gap-1.5" 
              :disabled="rejectForm.processing"
            >
              <i class="bi bi-x-circle"></i>
              <span>Konfirmasi Tolak Permohonan</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Centered Detail Modal Window -->
    <LeaveRequestModal 
      :show="showDetailModal" 
      :request="selectedRequest" 
      @close="showDetailModal = false" 
    />
  </AdminLayout>
</template>
