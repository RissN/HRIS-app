<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';

const page = usePage();
const isOpen = ref(false);
const dropdownRef = ref(null);

const notificationsData = computed(() => page.props.notifications || { unread_count: 0, list: [] });
const unreadCount = computed(() => notificationsData.value.unread_count || 0);
const notifications = computed(() => notificationsData.value.list || []);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

const markAsRead = (notif) => {
  if (!notif.is_read) {
    router.post(route('notifications.read', notif.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        if (notif.link) {
          router.visit(notif.link);
        }
      }
    });
  } else if (notif.link) {
    router.visit(notif.link);
  }
  isOpen.value = false;
};

const markAllAsRead = () => {
  router.post(route('notifications.read-all'), {}, {
    preserveScroll: true,
  });
};

const getIconConfig = (type) => {
  switch (type) {
    case 'leave':
      return { icon: 'bi-calendar-check-fill', bg: 'bg-emerald-50 text-emerald-600 border-emerald-200' };
    case 'complaint':
      return { icon: 'bi-chat-left-dots-fill', bg: 'bg-amber-50 text-amber-600 border-amber-200' };
    case 'payroll':
      return { icon: 'bi-cash-coin', bg: 'bg-purple-50 text-purple-600 border-purple-200' };
    default:
      return { icon: 'bi-bell-fill', bg: 'bg-blue-50 text-blue-600 border-blue-200' };
  }
};

const formatTimeAgo = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const now = new Date();
  const diffSecs = Math.floor((now - date) / 1000);

  if (diffSecs < 60) return 'Baru saja';
  const diffMins = Math.floor(diffSecs / 60);
  if (diffMins < 60) return `${diffMins} mnt lalu`;
  const diffHours = Math.floor(diffMins / 60);
  if (diffHours < 24) return `${diffHours} jam lalu`;
  const diffDays = Math.floor(diffHours / 24);
  return `${diffDays} hari lalu`;
};
</script>

<template>
  <div class="relative" ref="dropdownRef">
    <!-- Bell Button -->
    <button
      type="button"
      @click="toggleDropdown"
      class="relative p-2.5 rounded-2xl text-slate-500 hover:text-blue-600 hover:bg-slate-100/80 transition active:scale-95 cursor-pointer"
      title="Notifikasi"
      aria-label="Buka Notifikasi"
    >
      <i class="bi bi-bell text-xl"></i>
      <!-- Unread Badge Counter -->
      <span
        v-if="unreadCount > 0"
        class="absolute top-1.5 right-1.5 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-xs animate-pulse"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown Menu -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-3xl border border-slate-100 shadow-xl z-50 overflow-hidden animate-fade-in"
    >
      <!-- Dropdown Header -->
      <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div class="flex items-center gap-2">
          <h3 class="font-bold text-slate-800 text-sm">Notifikasi</h3>
          <span
            v-if="unreadCount > 0"
            class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[11px] font-semibold"
          >
            {{ unreadCount }} baru
          </span>
        </div>

        <button
          v-if="unreadCount > 0"
          type="button"
          @click="markAllAsRead"
          class="text-xs text-blue-600 hover:text-blue-700 font-semibold transition hover:underline cursor-pointer"
        >
          Tandai semua dibaca
        </button>
      </div>

      <!-- Notifications List -->
      <div class="max-h-80 overflow-y-auto divide-y divide-slate-50">
        <div v-if="notifications.length === 0" class="py-10 text-center text-slate-400">
          <i class="bi bi-bell-slash text-3xl mb-2 block text-slate-300"></i>
          <p class="text-xs">Tidak ada notifikasi baru</p>
        </div>

        <div
          v-for="notif in notifications"
          :key="notif.id"
          @click="markAsRead(notif)"
          class="p-4 flex items-start gap-3 hover:bg-slate-50 transition cursor-pointer relative"
          :class="{ 'bg-blue-50/30': !notif.is_read }"
        >
          <!-- Unread Dot Indicator -->
          <div
            v-if="!notif.is_read"
            class="w-2 h-2 rounded-full bg-blue-600 absolute left-2 top-5"
          ></div>

          <!-- Icon Badge -->
          <div
            class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border"
            :class="getIconConfig(notif.type).bg"
          >
            <i :class="getIconConfig(notif.type).icon"></i>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-1 mb-0.5">
              <h4 class="font-bold text-slate-900 text-xs truncate" :class="{ 'text-blue-700': !notif.is_read }">
                {{ notif.title }}
              </h4>
              <span class="text-[10px] text-slate-400 whitespace-nowrap shrink-0">
                {{ formatTimeAgo(notif.created_at) }}
              </span>
            </div>
            <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
              {{ notif.message }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
