<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Konfirmasi Tindakan',
  },
  message: {
    type: String,
    default: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
  },
  confirmText: {
    type: String,
    default: 'Ya, Lanjutkan',
  },
  cancelText: {
    type: String,
    default: 'Batal',
  },
  type: {
    type: String,
    default: 'danger', // 'danger', 'warning', 'info', 'success', 'primary'
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
  <div 
    v-if="show" 
    class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto"
    @click.self="emit('close')"
  >
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-sm w-full p-6 text-center animate-in fade-in zoom-in-95 my-auto">
      <!-- Icon with Ring -->
      <div 
        class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center mb-4 text-2xl shadow-xs ring-8"
        :class="{
          'bg-rose-50 text-rose-600 ring-rose-50/60': type === 'danger',
          'bg-amber-50 text-amber-600 ring-amber-50/60': type === 'warning',
          'bg-emerald-50 text-emerald-600 ring-emerald-50/60': type === 'success',
          'bg-blue-50 text-blue-600 ring-blue-50/60': type === 'info' || type === 'primary',
        }"
      >
        <i v-if="type === 'danger'" class="bi bi-trash3"></i>
        <i v-else-if="type === 'warning'" class="bi bi-exclamation-triangle"></i>
        <i v-else-if="type === 'success'" class="bi bi-check2-circle"></i>
        <i v-else class="bi bi-info-circle"></i>
      </div>

      <h3 class="text-base font-bold text-slate-900 leading-tight mb-2">
        {{ title }}
      </h3>
      <p class="text-xs text-slate-500 leading-relaxed mb-6 whitespace-pre-line">
        {{ message }}
      </p>

      <div class="flex items-center gap-3">
        <button 
          type="button" 
          :disabled="loading"
          @click="emit('close')"
          class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-semibold transition-colors cursor-pointer disabled:opacity-50"
        >
          {{ cancelText }}
        </button>
        <button 
          type="button" 
          :disabled="loading"
          @click="emit('confirm')"
          class="flex-1 py-2.5 px-4 rounded-xl text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed"
          :class="{
            'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 shadow-rose-600/20': type === 'danger',
            'bg-amber-600 hover:bg-amber-700 active:bg-amber-800 shadow-amber-600/20': type === 'warning',
            'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-emerald-600/20': type === 'success',
            'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-blue-600/20': type === 'info' || type === 'primary',
          }"
        >
          <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          <span v-else>{{ confirmText }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
