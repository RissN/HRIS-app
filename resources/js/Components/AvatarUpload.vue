<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  currentAvatar: {
    type: String,
    default: null,
  },
  userName: {
    type: String,
    default: 'User',
  },
});

const previewUrl = ref(null);
const fileInput = ref(null);

const form = useForm({
  avatar: null,
});

const triggerUpload = () => {
  fileInput.value.click();
};

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.avatar = file;
    previewUrl.value = URL.createObjectURL(file);
    form.post(route('employee.profile.avatar'), {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <div class="flex items-center gap-4">
    <div class="relative shrink-0">
      <img 
        :src="previewUrl || currentAvatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(userName) + '&background=2563eb&color=fff'" 
        alt="Profile Avatar" 
        class="w-20 h-20 rounded-full object-cover ring-4 ring-blue-50 shadow-sm"
      />
      <button 
        type="button" 
        class="w-7 h-7 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center absolute bottom-0 right-0 shadow-md ring-2 ring-white transition-colors cursor-pointer"
        title="Ganti Foto"
        @click="triggerUpload"
      >
        <i class="bi bi-camera-fill text-xs"></i>
      </button>
      <input 
        ref="fileInput" 
        type="file" 
        accept="image/png, image/jpeg, image/jpg" 
        class="hidden" 
        @change="handleFileChange"
      />
    </div>

    <div>
      <h6 class="font-bold text-slate-900 text-base leading-tight mb-1">{{ userName }}</h6>
      <button 
        type="button" 
        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-colors cursor-pointer"
        :disabled="form.processing"
        @click="triggerUpload"
      >
        <span v-if="form.processing" class="w-3 h-3 border-2 border-slate-400 border-t-transparent rounded-full animate-spin"></span>
        <i v-else class="bi bi-upload text-xs"></i>
        <span>Unggah Foto Profil</span>
      </button>
      <div v-if="form.errors.avatar" class="text-rose-600 text-xs mt-1 font-medium">
        {{ form.errors.avatar }}
      </div>
    </div>
  </div>
</template>
