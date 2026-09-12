<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
  complaints: Array,
});

const selectedComplaint = ref(null);

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'short', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <EmployeeLayout>
    <Head title="Komplain Absensi" />

    <div class="row justify-content-center">
      <div class="col-12 col-xl-10">
        <!-- Header -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4 mb-4">
          <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
            <div>
              <h5 class="fw-bold text-white mb-1">Komplain Presensi</h5>
              <p class="text-secondary small mb-0">Ajukan koreksi presensi atau laporkan kendala sistem/lokasi pada saat check-in.</p>
            </div>
            <Link :href="route('employee.complaints.create')" class="btn btn-primary btn-sm px-3 py-2 d-inline-flex align-items-center gap-2 shadow">
              <i class="bi bi-plus-circle"></i>
              <span>Ajukan Komplain Baru</span>
            </Link>
          </div>
        </div>

        <!-- Complaint List -->
        <div class="card border border-secondary border-opacity-25 shadow-sm p-3 p-md-4 rounded-4">
          <div v-if="complaints && complaints.length > 0">
            <!-- Mobile Cards -->
            <div class="d-md-none">
              <div 
                v-for="item in complaints" 
                :key="item.id" 
                class="card bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded-3 mb-3 p-3"
              >
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="fw-bold text-white small">
                    <i class="bi bi-calendar-event me-1 text-primary"></i>
                    {{ formatDate(item.date) }}
                  </div>
                  <StatusBadge :status="item.status" />
                </div>

                <div class="mb-2">
                  <StatusBadge :status="item.type" />
                </div>

                <div class="text-secondary small mb-2 text-truncate">
                  {{ item.description }}
                </div>

                <!-- Admin note if any -->
                <div v-if="item.admin_note" class="p-2 rounded-2 bg-dark bg-opacity-50 small mb-2 border border-secondary border-opacity-25">
                  <span class="text-primary fw-semibold d-block" style="font-size: 0.72rem;">RESPONS ADMIN:</span>
                  <div class="text-white">{{ item.admin_note }}</div>
                </div>

                <div class="d-flex justify-content-end pt-2 border-top border-secondary border-opacity-25">
                  <button type="button" class="btn btn-sm btn-outline-info py-1 px-2" style="font-size: 0.75rem;" @click="selectedComplaint = item">
                    <i class="bi bi-eye me-1"></i> Detail Lengkap
                  </button>
                </div>
              </div>
            </div>

            <!-- Desktop Table -->
            <div class="d-none d-md-block table-responsive">
              <table class="table table-hover align-middle small mb-0">
                <thead>
                  <tr class="text-secondary border-bottom border-secondary border-opacity-25">
                    <th>Tanggal Kejadian</th>
                    <th>Jenis Masalah</th>
                    <th>Keluhan</th>
                    <th>Status</th>
                    <th>Tanggapan Admin</th>
                    <th class="text-end">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in complaints" :key="item.id" class="border-bottom border-secondary border-opacity-10">
                    <td class="text-white fw-medium">{{ formatDate(item.date) }}</td>
                    <td><StatusBadge :status="item.type" /></td>
                    <td class="text-secondary text-truncate" style="max-width: 200px;">
                      {{ item.description }}
                    </td>
                    <td><StatusBadge :status="item.status" /></td>
                    <td class="text-secondary text-truncate" style="max-width: 180px;">
                      {{ item.admin_note || '-' }}
                    </td>
                    <td class="text-end">
                      <button type="button" class="btn btn-sm btn-outline-info py-1 px-2" style="font-size: 0.75rem;" @click="selectedComplaint = item">
                        <i class="bi bi-eye"></i> Detail
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-else class="text-center py-5 text-secondary">
            <i class="bi bi-chat-left-check fs-1 d-block mb-2 text-secondary"></i>
            Tidak ada komplain absensi yang diajukan.
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="selectedComplaint" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65);" @click.self="selectedComplaint = null">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border border-secondary border-opacity-25 shadow-lg">
          <div class="modal-header border-bottom border-secondary border-opacity-25">
            <h5 class="modal-title fs-6 fw-bold text-white d-flex align-items-center gap-2">
              <i class="bi bi-chat-left-dots text-primary"></i> Detail Komplain Absensi
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="selectedComplaint = null"></button>
          </div>
          <div class="modal-body">
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-dark bg-opacity-50 rounded-2">
              <span class="text-secondary small">Status</span>
              <StatusBadge :status="selectedComplaint.status" />
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="text-secondary small d-block">Tanggal Kejadian</label>
                <div class="text-white fw-medium">{{ formatDate(selectedComplaint.date) }}</div>
              </div>
              <div class="col-6">
                <label class="text-secondary small d-block">Kategori</label>
                <StatusBadge :status="selectedComplaint.type" />
              </div>
            </div>

            <div class="mb-3">
              <label class="text-secondary small d-block">Deskripsi Keluhan</label>
              <div class="p-2 bg-dark bg-opacity-50 rounded-2 text-white small" style="white-space: pre-wrap;">
                {{ selectedComplaint.description }}
              </div>
            </div>

            <div v-if="selectedComplaint.attachment" class="mb-3">
              <label class="text-secondary small d-block mb-1">Bukti Lampiran</label>
              <a :href="selectedComplaint.attachment" target="_blank" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-2">
                <i class="bi bi-paperclip"></i> Lihat File Bukti
              </a>
            </div>

            <div v-if="selectedComplaint.admin_note" class="mt-3 pt-3 border-top border-secondary border-opacity-25">
              <label class="text-secondary small d-block mb-1">Catatan dari Tim HR</label>
              <div class="p-2 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-2 text-white small">
                {{ selectedComplaint.admin_note }}
              </div>
            </div>
          </div>
          <div class="modal-footer border-top border-secondary border-opacity-25 py-2">
            <button type="button" class="btn btn-secondary btn-sm" @click="selectedComplaint = null">Tutup</button>
          </div>
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>
