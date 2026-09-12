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
      onSuccess: () => {
        // Success
      },
    });
  }
};
</script>

<template>
  <div class="d-flex align-items-center gap-3">
    <div class="position-relative">
      <img 
        :src="previewUrl || currentAvatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(userName) + '&background=6366f1&color=fff'" 
        alt="Profile Avatar" 
        class="rounded-circle object-fit-cover border border-2 border-primary shadow"
        style="width: 80px; height: 80px;"
      />
      <button 
        type="button" 
        class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-1 shadow"
        style="width: 28px; height: 28px;"
        title="Ganti Foto"
        @click="triggerUpload"
      >
        <i class="bi bi-camera-fill" style="font-size: 0.8rem;"></i>
      </button>
      <input 
        ref="fileInput" 
        type="file" 
        accept="image/png, image/jpeg, image/jpg" 
        class="d-none" 
        @change="handleFileChange"
      />
    </div>

    <div>
      <h6 class="mb-1 text-white fw-bold">{{ userName }}</h6>
      <button 
        type="button" 
        class="btn btn-outline-secondary btn-sm"
        style="font-size: 0.78rem;"
        :disabled="form.processing"
        @click="triggerUpload"
      >
        <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
        <i v-else class="bi bi-upload me-1"></i>
        Unggah Foto Profil
      </button>
      <div v-if="form.errors.avatar" class="text-danger small mt-1">
        {{ form.errors.avatar }}
      </div>
    </div>
  </div>
</template>
