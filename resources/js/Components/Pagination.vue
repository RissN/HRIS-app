<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: {
    type: Array,
    required: true,
  },
  from: Number,
  to: Number,
  total: Number,
  label: {
    type: String,
    default: 'pegawai',
  },
});
</script>

<template>
  <div v-if="links && links.length > 3" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-slate-100">
    <div class="text-xs text-slate-500">
      Menampilkan <span class="font-semibold text-slate-800">{{ from || 0 }}</span> - <span class="font-semibold text-slate-800">{{ to || 0 }}</span> dari <span class="font-semibold text-slate-900">{{ total || 0 }}</span> {{ label }}
    </div>

    <div class="flex flex-wrap items-center gap-1">
      <template v-for="(link, key) in links" :key="key">
        <div
          v-if="link.url === null"
          class="px-3 py-1.5 text-xs text-slate-400 bg-slate-50 rounded-xl border border-slate-200/60 cursor-not-allowed select-none"
          v-html="link.label"
        />
        <Link
          v-else
          :href="link.url"
          preserve-scroll
          preserve-state
          class="px-3 py-1.5 text-xs rounded-xl border transition-all duration-150 font-medium"
          :class="link.active 
            ? 'bg-blue-600 border-blue-600 text-white font-bold shadow-xs shadow-blue-500/20' 
            : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-blue-600'"
          v-html="link.label"
        />
      </template>
    </div>
  </div>
</template>
