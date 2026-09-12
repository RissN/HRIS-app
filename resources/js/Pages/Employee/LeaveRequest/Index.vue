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

    <div class="row justify-content-center">
      <div class="col-12 col-xl-10">
        <!-- Header -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4 mb-4">
          <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
            <div>
              <h5 class="fw-bold text-white mb-1">Pengajuan Cuti & Izin</h5>
              <p class="text-secondary small mb-0">Ajukan permohonan cuti tahunan, izin darurat, atau surat keterangan sakit.</p>
            </div>
            <Link :href="route('employee.leave-requests.create')" class="btn btn-primary btn-sm px-3 py-2 d-inline-flex align-items-center gap-2 shadow">
              <i class="bi bi-plus-circle"></i>
              <span>Buat Pengajuan Baru</span>
            </Link>
          </div>
        </div>

        <!-- Requests List -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4">
          <div v-if="leaveRequests && leaveRequests.length > 0">
            <!-- Mobile List Cards -->
            <div class="d-md-none">
              <div 
                v-for="item in leaveRequests" 
                :key="item.id" 
                class="card bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded-3 mb-3 p-3"
              >
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <StatusBadge :status="item.type" />
                  <StatusBadge :status="item.status" />
                </div>
                <div class="text-white small fw-bold mb-1">
                  {{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}
                  <span class="text-primary fw-normal">({{ item.total_days }} Hari Kerja)</span>
                </div>
                <div class="text-secondary small text-truncate mb-2">
                  {{ item.reason }}
                </div>
                <div class="d-flex justify-content-end gap-2 pt-2 border-top border-secondary border-opacity-25">
                  <button type="button" class="btn btn-sm btn-outline-info py-1 px-2" style="font-size: 0.75rem;" @click="openDetail(item)">
                    <i class="bi bi-eye me-1"></i> Detail
                  </button>
                  <button 
                    v-if="item.status === 'pending'" 
                    type="button" 
                    class="btn btn-sm btn-outline-danger py-1 px-2" 
                    style="font-size: 0.75rem;"
                    @click="cancelRequest(item)"
                  >
                    <i class="bi bi-x-circle me-1"></i> Batalkan
                  </button>
                </div>
              </div>
            </div>

            <!-- Desktop Table -->
            <div class="d-none d-md-block table-responsive">
              <table class="table table-hover align-middle small mb-0">
                <thead>
                  <tr class="text-secondary border-bottom border-secondary border-opacity-25">
                    <th>Jenis</th>
                    <th>Periode Tanggal</th>
                    <th>Total Hari</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in leaveRequests" :key="item.id" class="border-bottom border-secondary border-opacity-10">
                    <td><StatusBadge :status="item.type" /></td>
                    <td class="text-white">
                      {{ formatDate(item.start_date) }} s/d {{ formatDate(item.end_date) }}
                    </td>
                    <td class="text-primary fw-bold">{{ item.total_days }} hari</td>
                    <td class="text-secondary text-truncate" style="max-width: 220px;">
                      {{ item.reason }}
                    </td>
                    <td><StatusBadge :status="item.status" /></td>
                    <td class="text-end">
                      <div class="d-inline-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info py-1 px-2" style="font-size: 0.75rem;" @click="openDetail(item)">
                          <i class="bi bi-eye"></i> Detail
                        </button>
                        <button 
                          v-if="item.status === 'pending'" 
                          type="button" 
                          class="btn btn-sm btn-outline-danger py-1 px-2" 
                          style="font-size: 0.75rem;"
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

          <div v-else class="text-center py-5 text-secondary">
            <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-secondary"></i>
            Belum ada pengajuan cuti atau izin.
          </div>
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
