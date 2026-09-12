<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const confirmLogout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <div 
    v-if="show" 
    class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto"
    @click.self="emit('close')"
  >
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-sm w-full p-6 text-center animate-in fade-in zoom-in-95 my-auto">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4 text-2xl shadow-xs ring-8 ring-rose-50/60">
        <i class="bi bi-box-arrow-right"></i>
      </div>

      <h3 class="text-base font-bold text-slate-900 leading-tight mb-1.5">
        Konfirmasi Keluar Sistem
      </h3>
      <p class="text-xs text-slate-500 leading-relaxed mb-6">
        Apakah Anda yakin ingin keluar dari akun? Anda harus masuk kembali untuk mengakses data aplikasi.
      </p>

      <div class="flex items-center gap-3">
        <button 
          type="button" 
          @click="emit('close')"
          class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-semibold transition-colors cursor-pointer"
        >
          Batal
        </button>
        <button 
          type="button" 
          @click="confirmLogout"
          class="flex-1 py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5"
        >
          <i class="bi bi-check-lg text-sm"></i>
          <span>Ya, Keluar</span>
        </button>
      </div>
    </div>
  </div>
</template>
