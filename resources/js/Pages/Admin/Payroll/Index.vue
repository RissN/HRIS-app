<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  payrolls: Array,
  stats: Object,
  month: String,
  availableMonths: Array,
  flash: Object,
});

const selectedMonth = ref(props.month || new Date().toISOString().slice(0, 7));
const isGenerating = ref(false);
const activePayslip = ref(null);

const changeMonth = () => {
  router.get(route('admin.payroll.index'), {
    month: selectedMonth.value,
  }, { preserveState: true });
};

const generatePayroll = () => {
  if (confirm(`Hitung kalkulasi payroll otomatis untuk semua pegawai aktif periode ${selectedMonth.value}? Data draft yang belum dibayar akan diperbarui berdasarkan presensi terkini.`)) {
    isGenerating.value = true;
    router.post(route('admin.payroll.generate'), {
      month: selectedMonth.value,
    }, {
      preserveScroll: true,
      onFinish: () => {
        isGenerating.value = false;
      },
    });
  }
};

const markAsPaid = (payroll) => {
  if (confirm(`Tandai pembayaran gaji ${payroll.employee?.user?.name} periode ${payroll.month} sebagai LUNAS? Notifikasi akan otomatis dikirimkan ke pegawai.`)) {
    router.post(route('admin.payroll.paid', payroll.id), {}, {
      preserveScroll: true,
    });
  }
};

const viewPayslip = (payroll) => {
  activePayslip.value = payroll;
};

const printPayslip = () => {
  window.print();
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const formatMonthName = (monthStr) => {
  if (!monthStr) return '';
  const [year, month] = monthStr.split('-');
  const date = new Date(year, month - 1);
  return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Penggajian (Payroll) - HRIS" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1">
              <i class="bi bi-wallet2"></i>
              <span>Kompensasi & Tunjangan</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Estimator & Slip Gaji (Payroll)</h1>
            <p class="text-sm text-slate-500 mt-0.5">
              Kalkulasi otomatis gaji bulanan berdasarkan jam kehadiran, denda keterlambatan, dan potongan alpa.
            </p>
          </div>

          <!-- Actions -->
          <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-2">
              <label class="text-xs font-semibold text-slate-500">Periode:</label>
              <input
                type="month"
                v-model="selectedMonth"
                @change="changeMonth"
                class="bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-800 transition"
              />
            </div>

            <button
              type="button"
              @click="generatePayroll"
              :disabled="isGenerating"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs shadow-blue-600/20 transition active:scale-95 disabled:opacity-50 cursor-pointer"
            >
              <i class="bi" :class="isGenerating ? 'bi-arrow-repeat animate-spin' : 'bi-calculator-fill'"></i>
              <span>{{ isGenerating ? 'Mengkalkulasi...' : 'Kalkulasi Otomatis' }}</span>
            </button>
          </div>
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

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-500">Total Pengeluaran Gaji</span>
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <i class="bi bi-cash-stack text-lg"></i>
            </div>
          </div>
          <div class="text-2xl font-bold text-slate-900">{{ formatRupiah(stats.total_expenditure) }}</div>
          <span class="text-xs text-slate-400">Periode {{ formatMonthName(selectedMonth) }}</span>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-500">Total Karyawan</span>
            <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
              <i class="bi bi-people-fill text-lg"></i>
            </div>
          </div>
          <div class="text-2xl font-bold text-slate-900">{{ stats.total_employees }} Orang</div>
          <span class="text-xs text-slate-400">Dalam daftar gaji</span>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-emerald-600">Gaji Lunas / Terbayar</span>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <i class="bi bi-check-circle-fill text-lg"></i>
            </div>
          </div>
          <div class="text-2xl font-bold text-emerald-600">{{ stats.paid_count }} Pegawai</div>
          <span class="text-xs text-slate-400">Slip gaji telah terbit</span>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-xs">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-amber-600">Menunggu Review / Draft</span>
            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <i class="bi bi-hourglass-split text-lg"></i>
            </div>
          </div>
          <div class="text-2xl font-bold text-amber-600">{{ stats.draft_count }} Pegawai</div>
          <span class="text-xs text-slate-400">Belum ditandai bayar</span>
        </div>
      </div>

      <!-- Payroll Table -->
      <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-xs">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h2 class="font-bold text-slate-900 text-base">Daftar Payroll Periode {{ formatMonthName(selectedMonth) }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">Rincian gaji pokok, tunjangan kehadiran, dan potongan</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50/75 text-xs uppercase font-semibold text-slate-500 border-b border-slate-100 tracking-wider">
              <tr>
                <th class="py-3.5 px-4">Pegawai</th>
                <th class="py-3.5 px-4">Gaji Pokok</th>
                <th class="py-3.5 px-4 text-emerald-700">Tunjangan Hadir</th>
                <th class="py-3.5 px-4 text-rose-700">Total Potongan</th>
                <th class="py-3.5 px-4 font-bold text-slate-900">Gaji Bersih (Net)</th>
                <th class="py-3.5 px-4">Status</th>
                <th class="py-3.5 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="payrolls.length === 0">
                <td colspan="7" class="py-12 text-center text-slate-400">
                  <i class="bi bi-wallet2 text-3xl mb-2 block text-slate-300"></i>
                  Belum ada data payroll untuk periode {{ selectedMonth }}.
                  <div class="mt-2">
                    <button
                      type="button"
                      @click="generatePayroll"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 text-xs font-semibold hover:bg-blue-100 transition"
                    >
                      <i class="bi bi-calculator"></i>
                      <span>Hitung Payroll Sekarang</span>
                    </button>
                  </div>
                </td>
              </tr>
              <tr
                v-for="p in payrolls"
                :key="p.id"
                class="hover:bg-slate-50/60 transition-colors"
              >
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-700 font-bold flex items-center justify-center text-xs">
                      {{ p.employee?.user?.name ? p.employee.user.name.charAt(0) : '?' }}
                    </div>
                    <div>
                      <div class="font-semibold text-slate-900">{{ p.employee?.user?.name || '-' }}</div>
                      <div class="text-xs text-slate-400">{{ p.employee?.position || '-' }} &bull; {{ p.employee?.department || '-' }}</div>
                    </div>
                  </div>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap font-mono text-xs text-slate-700">
                  {{ formatRupiah(p.basic_salary) }}
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap font-mono text-xs text-emerald-600 font-semibold">
                  + {{ formatRupiah(p.daily_allowance) }}
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap font-mono text-xs text-rose-600">
                  - {{ formatRupiah(p.late_deduction + p.absent_deduction) }}
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap font-mono text-sm font-bold text-slate-900">
                  {{ formatRupiah(p.net_salary) }}
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="p.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                  >
                    <i class="bi" :class="p.status === 'paid' ? 'bi-check-circle-fill' : 'bi-clock-history'"></i>
                    <span>{{ p.status === 'paid' ? 'LUNAS' : 'DRAFT' }}</span>
                  </span>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      type="button"
                      @click="viewPayslip(p)"
                      class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition shadow-xs cursor-pointer"
                      title="Lihat Slip Gaji"
                    >
                      <i class="bi bi-file-earmark-text"></i>
                      <span class="ml-1">Slip</span>
                    </button>

                    <button
                      v-if="p.status === 'draft'"
                      type="button"
                      @click="markAsPaid(p)"
                      class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs cursor-pointer"
                      title="Tandai Sudah Dibayarkan"
                    >
                      <i class="bi bi-check-lg"></i>
                      <span class="ml-1">Bayar</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Printable Payslip Modal Dialog -->
    <div
      v-if="activePayslip"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative max-h-[90vh] overflow-y-auto">
        <!-- Close Button -->
        <button
          type="button"
          @click="activePayslip = null"
          class="print:hidden absolute top-5 right-5 w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer"
        >
          <i class="bi bi-x-lg"></i>
        </button>

        <!-- Payslip Header -->
        <div class="text-center pb-5 border-b-2 border-dashed border-slate-200 mb-6">
          <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 text-white rounded-2xl mb-2 shadow-xs">
            <i class="bi bi-building text-xl"></i>
          </div>
          <h2 class="text-xl font-black text-slate-900 tracking-tight">HRIS &mdash; SLIP GAJI PEGAWAI</h2>
          <p class="text-xs text-slate-500 font-medium">Periode: {{ formatMonthName(activePayslip.month) }}</p>
        </div>

        <!-- Employee Info -->
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-xs mb-6 space-y-1.5">
          <div class="flex justify-between">
            <span class="text-slate-500">Nama Pegawai:</span>
            <strong class="text-slate-900">{{ activePayslip.employee?.user?.name }}</strong>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">NIK:</span>
            <span class="font-mono text-slate-800">{{ activePayslip.employee?.nik || '-' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Jabatan:</span>
            <span class="text-slate-800">{{ activePayslip.employee?.position }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Departemen:</span>
            <span class="text-slate-800">{{ activePayslip.employee?.department }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Rekening Bank:</span>
            <span class="text-slate-800 font-mono">{{ activePayslip.employee?.bank_name }} - {{ activePayslip.employee?.account_number }}</span>
          </div>
        </div>

        <!-- Earnings & Deductions Breakdown -->
        <div class="space-y-4 text-xs mb-6">
          <!-- Earnings -->
          <div>
            <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-2 pb-1 border-b border-slate-100">
              Penerimaan / Penghasilan
            </h4>
            <div class="space-y-1.5">
              <div class="flex justify-between">
                <span class="text-slate-600">Gaji Pokok:</span>
                <span class="font-mono font-semibold text-slate-800">{{ formatRupiah(activePayslip.basic_salary) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-600">Tunjangan Kehadiran:</span>
                <span class="font-mono font-semibold text-emerald-600">+ {{ formatRupiah(activePayslip.daily_allowance) }}</span>
              </div>
            </div>
          </div>

          <!-- Deductions -->
          <div>
            <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-2 pb-1 border-b border-slate-100">
              Potongan
            </h4>
            <div class="space-y-1.5">
              <div class="flex justify-between">
                <span class="text-slate-600">Denda Keterlambatan:</span>
                <span class="font-mono font-semibold text-rose-600">- {{ formatRupiah(activePayslip.late_deduction) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-600">Potongan Alpa:</span>
                <span class="font-mono font-semibold text-rose-600">- {{ formatRupiah(activePayslip.absent_deduction) }}</span>
              </div>
            </div>
          </div>

          <!-- Total Net -->
          <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200/80 flex items-center justify-between">
            <div>
              <span class="text-xs font-semibold text-blue-900">Total Gaji Diterima (Net):</span>
              <p class="text-[10px] text-blue-700">Ditransfer ke rekening terdaftar</p>
            </div>
            <span class="text-lg font-black text-blue-700 font-mono">
              {{ formatRupiah(activePayslip.net_salary) }}
            </span>
          </div>
        </div>

        <p v-if="activePayslip.note" class="text-[11px] text-slate-400 mb-6 italic text-center">
          Catatan: {{ activePayslip.note }}
        </p>

        <!-- Actions -->
        <div class="print:hidden flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
          <button
            type="button"
            @click="activePayslip = null"
            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition cursor-pointer"
          >
            Tutup
          </button>
          <button
            type="button"
            @click="printPayslip"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs shadow-blue-600/20 transition active:scale-95 cursor-pointer"
          >
            <i class="bi bi-printer"></i>
            <span>Cetak Slip Gaji</span>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
