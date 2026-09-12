<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import LeaveRequestModal from '@/Components/LeaveRequestModal.vue';

const props = defineProps({
  leaveRequests: Array,
});

const selectedRequest = ref(null);
const showModal = ref(false);

const openDetail = (item) => {
  selectedRequest.value = item;
  showModal.value = true;
};

const cancelForm = useForm({});

const cancelRequest = (item) => {
  if (confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')) {
    cancelForm.delete(route('employee.leave-requests.cancel', item.id), {
      preserveScroll: true,
    });
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'short', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <EmployeeLayout>
    <Head title="Pengajuan Cuti / Izin" />

    <div class="max-w-4xl mx-auto space-y-5">
      <!-- Header Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Pengajuan Cuti & Izin</h1>
            <p class="text-xs text-slate-500 mt-0.5">Ajukan cuti tahunan, izin sakit, atau urusan darurat.</p>
          </div>
          <Link 
            :href="route('employee.leave-requests.create')" 
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-600/20 transition-all cursor-pointer"
          >
            <i class="bi bi-plus-circle text-sm"></i>
            <span>Buat Pengajuan Baru</span>
          </Link>
        </div>
      </div>

      <!-- Requests List Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div v-if="leaveRequests && leaveRequests.length > 0">
          <!-- Mobile View: Cards -->
          <div class="md:hidden space-y-3">
            <div 
              v-for="item in leaveRequests" 
              :key="item.id" 
              class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2.5"
            >
              <div class="flex items-center justify-between">
                <StatusBadge :status="item.type" />
                <StatusBadge :status="item.status" />
              </div>
              <div class="text-xs font-bold text-slate-800">
                {{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}
                <span class="text-blue-600 font-medium">({{ item.total_days }} Hari Kerja)</span>
              </div>
              <div class="text-xs text-slate-500 line-clamp-2">
                {{ item.reason }}
              </div>
              <div class="flex justify-end gap-2 pt-2 border-t border-slate-200/60">
                <button 
                  type="button" 
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors"
                  @click="openDetail(item)"
                >
                  <i class="bi bi-eye mr-1"></i> Detail
                </button>
                <button 
                  v-if="item.status === 'pending'" 
                  type="button" 
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors"
                  @click="cancelRequest(item)"
                >
                  <i class="bi bi-x-circle mr-1"></i> Batalkan
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop View: Table -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold">
                  <th class="pb-3 px-2">Jenis</th>
                  <th class="pb-3 px-2">Periode Tanggal</th>
                  <th class="pb-3 px-2">Durasi</th>
                  <th class="pb-3 px-2">Alasan</th>
                  <th class="pb-3 px-2">Status</th>
                  <th class="pb-3 px-2 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="item in leaveRequests" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-2"><StatusBadge :status="item.type" /></td>
                  <td class="py-3 px-2 font-semibold text-slate-900">
                    {{ formatDate(item.start_date) }} &ndash; {{ formatDate(item.end_date) }}
                  </td>
                  <td class="py-3 px-2 font-bold text-blue-600">{{ item.total_days }} Hari</td>
                  <td class="py-3 px-2 text-slate-500 max-w-xs truncate">{{ item.reason }}</td>
                  <td class="py-3 px-2"><StatusBadge :status="item.status" /></td>
                  <td class="py-3 px-2 text-right">
                    <div class="inline-flex gap-1.5">
                      <button 
                        type="button" 
                        class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors cursor-pointer"
                        @click="openDetail(item)"
                      >
                        <i class="bi bi-eye"></i> Detail
                      </button>
                      <button 
                        v-if="item.status === 'pending'" 
                        type="button" 
                        class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors cursor-pointer"
                        @click="cancelRequest(item)"
                      >
                        <i class="bi bi-x-circle"></i> Batal
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else class="text-center py-12 text-slate-400">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
            <i class="bi bi-file-earmark-x text-2xl"></i>
          </div>
          <p class="text-xs font-medium">Belum ada permohonan cuti atau izin.</p>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <LeaveRequestModal 
      :show="showModal" 
      :request="selectedRequest" 
      @close="showModal = false" 
    />
  </EmployeeLayout>
</template>
