<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';

const props = defineProps({
  payrolls: Array,
  employee: Object,
});

const activePayslip = ref(props.payrolls.length > 0 ? props.payrolls[0] : null);

const selectPayslip = (p) => {
  activePayslip.value = p;
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

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <EmployeeLayout>
    <Head title="Slip Gaji & Kompensasi - HRIS" />

    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1">
              <i class="bi bi-wallet2"></i>
              <span>Kompensasi Pegawai</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Slip Gaji & Riwayat Penghasilan</h1>
            <p class="text-sm text-slate-500 mt-0.5">
              Rincian gaji bulanan, tunjangan kehadiran, dan potongan presensi resmi.
            </p>
          </div>

          <button
            v-if="activePayslip"
            type="button"
            @click="printPayslip"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition shadow-xs active:scale-95 cursor-pointer"
          >
            <i class="bi bi-printer"></i>
            <span>Cetak Slip Gaji</span>
          </button>
        </div>
      </div>

      <div v-if="payrolls.length === 0" class="bg-white rounded-3xl border border-slate-100 p-12 text-center text-slate-400">
        <i class="bi bi-wallet-fill text-4xl mb-3 block text-slate-300"></i>
        <p class="text-base font-semibold text-slate-600">Belum Ada Slip Gaji</p>
        <p class="text-xs text-slate-400 mt-1">Slip gaji Anda akan ditampilkan di sini setelah diproses oleh bagian HRD.</p>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Payslip Details Card (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
          <div v-if="activePayslip" class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-8 shadow-xs relative">
            <!-- Header -->
            <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6">
              <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-lg font-bold shadow-xs">
                  <i class="bi bi-building"></i>
                </div>
                <div>
                  <h2 class="font-bold text-slate-900 text-base">HRIS &mdash; Slip Gaji</h2>
                  <p class="text-xs text-slate-500">{{ formatMonthName(activePayslip.month) }}</p>
                </div>
              </div>

              <span
                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold"
                :class="activePayslip.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
              >
                <i class="bi" :class="activePayslip.status === 'paid' ? 'bi-check-circle-fill' : 'bi-clock-history'"></i>
                <span>{{ activePayslip.status === 'paid' ? 'LUNAS' : 'ESTIMASI' }}</span>
              </span>
            </div>

            <!-- Net Salary Hero Card -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white mb-6 shadow-md shadow-blue-500/20">
              <span class="text-xs text-blue-100 font-medium">Gaji Bersih Diterima (Take Home Pay)</span>
              <div class="text-3xl font-black tracking-tight my-1 font-mono">
                {{ formatRupiah(activePayslip.net_salary) }}
              </div>
              <div class="text-[11px] text-blue-100/90 flex items-center justify-between pt-2 border-t border-white/15">
                <span>Rekening: {{ employee.bank_name }} &bull; {{ employee.account_number }}</span>
                <span v-if="activePayslip.payment_date">Dibayar: {{ formatDate(activePayslip.payment_date) }}</span>
              </div>
            </div>

            <!-- Detailed Breakdown -->
            <div class="space-y-4 text-xs">
              <!-- Earnings -->
              <div>
                <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-2.5 pb-1 border-b border-slate-100">
                  Rincian Penerimaan
                </h4>
                <div class="space-y-2">
                  <div class="flex justify-between items-center py-1">
                    <span class="text-slate-600">Gaji Pokok Bulanan</span>
                    <span class="font-mono font-semibold text-slate-900">{{ formatRupiah(activePayslip.basic_salary) }}</span>
                  </div>
                  <div class="flex justify-between items-center py-1">
                    <span class="text-slate-600">Tunjangan Kehadiran (Uang Makan & Transport)</span>
                    <span class="font-mono font-semibold text-emerald-600">+ {{ formatRupiah(activePayslip.daily_allowance) }}</span>
                  </div>
                </div>
              </div>

              <!-- Deductions -->
              <div>
                <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-2.5 pb-1 border-b border-slate-100">
                  Rincian Potongan
                </h4>
                <div class="space-y-2">
                  <div class="flex justify-between items-center py-1">
                    <span class="text-slate-600">Denda Keterlambatan Presensi</span>
                    <span class="font-mono font-semibold text-rose-600">- {{ formatRupiah(activePayslip.late_deduction) }}</span>
                  </div>
                  <div class="flex justify-between items-center py-1">
                    <span class="text-slate-600">Potongan Ketidakhadiran (Alpa)</span>
                    <span class="font-mono font-semibold text-rose-600">- {{ formatRupiah(activePayslip.absent_deduction) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="activePayslip.note" class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-400 italic">
              {{ activePayslip.note }}
            </div>
          </div>
        </div>

        <!-- Payroll History List (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
          <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-xs">
            <h3 class="font-bold text-slate-900 text-sm mb-3">Daftar Periode Tersedia</h3>
            <div class="space-y-2.5">
              <div
                v-for="p in payrolls"
                :key="p.id"
                @click="selectPayslip(p)"
                class="p-3.5 rounded-2xl border transition cursor-pointer flex items-center justify-between"
                :class="activePayslip?.id === p.id ? 'border-blue-600 bg-blue-50/50 shadow-xs' : 'border-slate-100 hover:bg-slate-50'"
              >
                <div>
                  <h4 class="font-bold text-slate-900 text-xs">{{ formatMonthName(p.month) }}</h4>
                  <div class="font-mono text-sm font-bold text-blue-600 mt-0.5">
                    {{ formatRupiah(p.net_salary) }}
                  </div>
                </div>

                <div class="text-right">
                  <span
                    class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                    :class="p.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                  >
                    {{ p.status === 'paid' ? 'Lunas' : 'Draft' }}
                  </span>
                  <p v-if="p.payment_date" class="text-[10px] text-slate-400 mt-1">
                    {{ formatDate(p.payment_date) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>
