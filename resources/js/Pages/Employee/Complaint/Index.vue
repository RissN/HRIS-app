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
    <Head title="Komplain Presensi" />

    <div class="max-w-4xl mx-auto space-y-5">
      <!-- Header Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Komplain Presensi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Ajukan koreksi absensi atau laporkan kendala sistem/lokasi.</p>
          </div>
          <Link 
            :href="route('employee.complaints.create')" 
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-600/20 transition-all cursor-pointer"
          >
            <i class="bi bi-plus-circle text-sm"></i>
            <span>Ajukan Komplain Baru</span>
          </Link>
        </div>
      </div>

      <!-- Complaint List Card -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div v-if="complaints && complaints.length > 0">
          <!-- Mobile View: Cards -->
          <div class="md:hidden space-y-3">
            <div 
              v-for="item in complaints" 
              :key="item.id" 
              class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2.5"
            >
              <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                  <i class="bi bi-calendar-event text-blue-600"></i>
                  <span>{{ formatDate(item.date) }}</span>
                </div>
                <StatusBadge :status="item.status" />
              </div>

              <div>
                <StatusBadge :status="item.type" />
              </div>

              <div class="text-xs text-slate-600 line-clamp-2">
                {{ item.description }}
              </div>

              <!-- Admin note preview if available -->
              <div v-if="item.admin_note" class="p-3 rounded-xl bg-blue-50/70 border border-blue-100 text-xs">
                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block mb-0.5">Respons Tim HR:</span>
                <span class="text-blue-950">{{ item.admin_note }}</span>
              </div>

              <div class="flex justify-end pt-2 border-t border-slate-200/60">
                <button 
                  type="button" 
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors cursor-pointer"
                  @click="selectedComplaint = item"
                >
                  <i class="bi bi-eye mr-1"></i> Detail Lengkap
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop View: Table -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold">
                  <th class="pb-3 px-2">Tanggal Kejadian</th>
                  <th class="pb-3 px-2">Kategori Kendala</th>
                  <th class="pb-3 px-2">Deskripsi Keluhan</th>
                  <th class="pb-3 px-2">Status</th>
                  <th class="pb-3 px-2">Respons HR</th>
                  <th class="pb-3 px-2 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="item in complaints" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-2 font-semibold text-slate-900">{{ formatDate(item.date) }}</td>
                  <td class="py-3 px-2"><StatusBadge :status="item.type" /></td>
                  <td class="py-3 px-2 text-slate-600 max-w-xs truncate">{{ item.description }}</td>
                  <td class="py-3 px-2"><StatusBadge :status="item.status" /></td>
                  <td class="py-3 px-2 text-slate-500 max-w-xs truncate">{{ item.admin_note || '-' }}</td>
                  <td class="py-3 px-2 text-right">
                    <button 
                      type="button" 
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors cursor-pointer"
                      @click="selectedComplaint = item"
                    >
                      <i class="bi bi-eye"></i> Detail
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else class="text-center py-12 text-slate-400">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
            <i class="bi bi-chat-left-check text-2xl"></i>
          </div>
          <p class="text-xs font-medium">Belum ada komplain presensi yang diajukan.</p>
        </div>
      </div>
    </div>

    <!-- Detail Dialog Modal -->
    <div 
      v-if="selectedComplaint" 
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
      @click.self="selectedComplaint = null"
    >
      <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-xl w-full my-auto overflow-hidden animate-in fade-in zoom-in-95 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
              <i class="bi bi-chat-left-dots text-base"></i>
            </div>
            <h5 class="text-base font-bold text-slate-900">Detail Komplain Presensi</h5>
          </div>
          <button 
            type="button" 
            class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors"
            @click="selectedComplaint = null"
          >
            <i class="bi bi-x-lg text-sm"></i>
          </button>
        </div>

        <div class="p-6 space-y-4 text-xs text-slate-700">
          <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Komplain</span>
            <StatusBadge :status="selectedComplaint.status" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100">
              <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tanggal Kejadian</label>
              <div class="font-semibold text-slate-900">{{ formatDate(selectedComplaint.date) }}</div>
            </div>
            <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100">
              <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kategori</label>
              <StatusBadge :status="selectedComplaint.type" />
            </div>
          </div>

          <div>
            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Deskripsi Keluhan</label>
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-slate-800 leading-relaxed whitespace-pre-wrap">
              {{ selectedComplaint.description }}
            </div>
          </div>

          <div v-if="selectedComplaint.attachment">
            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Bukti Lampiran</label>
            <a 
              :href="selectedComplaint.attachment" 
              target="_blank" 
              class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200/80 transition-colors"
            >
              <i class="bi bi-paperclip text-sm"></i>
              <span>Buka File Bukti / Screenshot</span>
            </a>
          </div>

          <div v-if="selectedComplaint.admin_note" class="p-3.5 bg-blue-50/80 border border-blue-100 rounded-xl">
            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider block mb-1">Catatan dari Tim HR</label>
            <div class="text-xs text-blue-900 leading-relaxed">{{ selectedComplaint.admin_note }}</div>
          </div>
        </div>

        <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end">
          <button 
            type="button" 
            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-200 text-xs font-semibold transition-colors cursor-pointer" 
            @click="selectedComplaint = null"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>
