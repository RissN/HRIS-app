<script setup>
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  show: Boolean,
  preselectedEmployee: Object,
  employeeOptions: {
    type: Array,
    default: () => [],
  },
  sources: {
    type: Object,
    default: () => ({
      sosmed: 'Media Sosial Viral (TikTok/X/IG)',
      customer: 'Pujian Penumpang / Halte',
      service: 'Pelayanan Prima & Keramahan',
      extra_mile: 'Inisiatif & Disiplin Ekstra',
    }),
  },
});

const emit = defineEmits(['close']);

const searchQuery = ref('');

const form = useForm({
  employee_id: '',
  source: 'sosmed',
  title: '',
  description: '',
  evidence_url: '',
  points: 100,
  date: new Date().toISOString().substring(0, 10),
});

const presetPoints = [50, 100, 150, 200, 300];

// Filter employee options by search
const filteredEmployees = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.employeeOptions.slice(0, 50);
  }
  const q = searchQuery.value.toLowerCase();
  return props.employeeOptions.filter((e) => 
    (e.name && e.name.toLowerCase().includes(q)) ||
    (e.employee_code && e.employee_code.toLowerCase().includes(q)) ||
    (e.position && e.position.toLowerCase().includes(q))
  ).slice(0, 50);
});

const selectedEmployeeObj = computed(() => {
  if (props.preselectedEmployee) return props.preselectedEmployee;
  return props.employeeOptions.find((e) => e.id === form.employee_id);
});

watch(() => props.show, (newVal) => {
  if (newVal) {
    if (props.preselectedEmployee) {
      form.employee_id = props.preselectedEmployee.id;
    } else {
      form.employee_id = '';
      searchQuery.value = '';
    }
    form.points = 100;
    form.source = 'sosmed';
    form.title = '';
    form.description = '';
    form.evidence_url = '';
    form.date = new Date().toISOString().substring(0, 10);
    form.clearErrors();
  }
});

watch(() => props.preselectedEmployee, (newVal) => {
  if (newVal) {
    form.employee_id = newVal.id;
  }
});

const selectEmployee = (emp) => {
  form.employee_id = emp.id;
};

const submit = () => {
  form.post(route('admin.performance.appreciations.store'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('close');
    },
  });
};
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
    @click.self="$emit('close')"
  >
    <div
      class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-lg w-full my-auto overflow-hidden animate-in fade-in zoom-in-95 flex flex-col max-h-[92vh]"
    >
      <!-- Header -->
      <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200/60 shrink-0">
            <i class="bi bi-award-fill text-base"></i>
          </div>
          <div>
            <h3 class="font-bold text-slate-900 text-base leading-tight">Beri Apresiasi Karyawan</h3>
            <p class="text-[11px] text-slate-500">Pemberian poin apresiasi HR (Viral medsos / Pujian pelanggan)</p>
          </div>
        </div>

        <button
          type="button"
          @click="$emit('close')"
          class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors cursor-pointer shrink-0"
        >
          <i class="bi bi-x-lg text-xs"></i>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="p-5 sm:p-6 overflow-y-auto space-y-4 text-xs">
        
        <!-- Employee Selector -->
        <div>
          <label class="block font-bold text-slate-700 mb-1.5">
            Karyawan Penerima Apresiasi <span class="text-rose-500">*</span>
          </label>

          <!-- Preselected preview -->
          <div v-if="selectedEmployeeObj" class="p-3 rounded-2xl bg-blue-50/70 border border-blue-200/80 flex items-center justify-between">
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="w-8 h-8 rounded-xl bg-blue-600 text-white font-mono font-bold flex items-center justify-center text-xs shrink-0">
                <i class="bi bi-person-check-fill"></i>
              </div>
              <div class="min-w-0">
                <div class="font-bold text-slate-900 truncate">{{ selectedEmployeeObj.name }}</div>
                <div class="text-[10px] text-slate-500">
                  <span class="font-mono font-bold text-blue-700">{{ selectedEmployeeObj.employee_code }}</span>
                  <span> &bull; {{ selectedEmployeeObj.position }}</span>
                  <span v-if="selectedEmployeeObj.pool_depot"> ({{ selectedEmployeeObj.pool_depot }})</span>
                </div>
              </div>
            </div>

            <button
              v-if="!preselectedEmployee"
              type="button"
              @click="form.employee_id = ''"
              class="text-xs text-blue-600 font-bold hover:underline cursor-pointer ml-2 shrink-0"
            >
              Ubah
            </button>
          </div>

          <!-- Search & Select List -->
          <div v-else class="space-y-2">
            <div class="relative">
              <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
              <input
                v-model="searchQuery"
                type="text"
                class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none"
                placeholder="Ketik NIK atau nama karyawan Transjakarta..."
              />
            </div>

            <div class="max-h-36 overflow-y-auto rounded-xl border border-slate-200 divide-y divide-slate-100 bg-white">
              <div
                v-for="emp in filteredEmployees"
                :key="emp.id"
                @click="selectEmployee(emp)"
                class="p-2 hover:bg-blue-50/70 transition cursor-pointer flex items-center justify-between"
              >
                <div class="min-w-0">
                  <div class="font-bold text-slate-800 truncate">{{ emp.name }}</div>
                  <div class="text-[10px] text-slate-400 font-mono">{{ emp.employee_code }} &bull; {{ emp.position }}</div>
                </div>
                <span class="text-[10px] font-bold text-blue-600">Pilih →</span>
              </div>
              <div v-if="filteredEmployees.length === 0" class="p-3 text-center text-slate-400 text-xs">
                Tidak ada karyawan ditemukan.
              </div>
            </div>
          </div>
          <div v-if="form.errors.employee_id" class="text-rose-600 text-[11px] mt-1 font-semibold">
            {{ form.errors.employee_id }}
          </div>
        </div>

        <!-- Sumber Apresiasi -->
        <div>
          <label class="block font-bold text-slate-700 mb-1.5">
            Sumber & Alasan Apresiasi <span class="text-rose-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-2">
            <button
              v-for="(label, key) in sources"
              :key="key"
              type="button"
              @click="form.source = key"
              class="p-2.5 rounded-xl border text-left transition cursor-pointer flex items-start gap-2"
              :class="form.source === key ? 'border-blue-600 bg-blue-50/70 text-blue-900 font-bold ring-1 ring-blue-500/30' : 'border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600'"
            >
              <i
                class="text-sm shrink-0 mt-0.5"
                :class="{
                  'bi-tiktok text-pink-600': key === 'sosmed',
                  'bi-chat-heart-fill text-emerald-600': key === 'customer',
                  'bi-star-fill text-blue-600': key === 'service',
                  'bi-award-fill text-amber-600': key === 'extra_mile',
                }"
              ></i>
              <span class="text-[11px] leading-snug">{{ label }}</span>
            </button>
          </div>
        </div>

        <!-- Judul Apresiasi -->
        <div>
          <label class="block font-bold text-slate-700 mb-1">
            Judul / Penghargaan <span class="text-rose-500">*</span>
          </label>
          <input
            v-model="form.title"
            type="text"
            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none"
            placeholder="Contoh: Viral di TikTok membantu penumpang disabilitas di Halte Bundaran HI"
          />
          <div v-if="form.errors.title" class="text-rose-600 text-[11px] mt-1 font-semibold">
            {{ form.errors.title }}
          </div>
        </div>

        <!-- Poin Tambahan & Tanggal -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">
              Poin Tambahan (Bonus) <span class="text-rose-500">*</span>
            </label>
            <div class="flex items-center gap-1.5 mb-1.5">
              <button
                v-for="p in presetPoints"
                :key="p"
                type="button"
                @click="form.points = p"
                class="px-2.5 py-1 rounded-lg border text-[11px] font-semibold transition cursor-pointer"
                :class="form.points === p ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border-slate-200'"
              >
                +{{ p }}
              </button>
            </div>
            <input
              v-model.number="form.points"
              type="number"
              min="10"
              max="1000"
              class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none font-bold text-blue-700"
            />
            <div v-if="form.errors.points" class="text-rose-600 text-[11px] mt-1 font-semibold">
              {{ form.errors.points }}
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">
              Tanggal Kejadian / Apresiasi <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.date"
              type="date"
              class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none mt-6 sm:mt-8"
            />
            <div v-if="form.errors.date" class="text-rose-600 text-[11px] mt-1 font-semibold">
              {{ form.errors.date }}
            </div>
          </div>
        </div>

        <!-- URL Bukti Sosmed (Opsional) -->
        <div>
          <label class="block font-bold text-slate-700 mb-1 flex items-center justify-between">
            <span>Tautan Bukti / Link Medsos</span>
            <span class="text-[10px] text-slate-400 font-normal">Opsional (TikTok/Instagram/X)</span>
          </label>
          <div class="relative">
            <i class="bi bi-link-45deg absolute left-3 top-2.5 text-slate-400 text-sm"></i>
            <input
              v-model="form.evidence_url"
              type="url"
              class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none"
              placeholder="https://www.tiktok.com/@... atau https://twitter.com/..."
            />
          </div>
          <div v-if="form.errors.evidence_url" class="text-rose-600 text-[11px] mt-1 font-semibold">
            {{ form.errors.evidence_url }}
          </div>
        </div>

        <!-- Keterangan Lengkap / Kronologi -->
        <div>
          <label class="block font-bold text-slate-700 mb-1">
            Catatan Kronologi / Alasan HR
          </label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 outline-none"
            placeholder="Tuliskan catatan kejadian atau detail laporan penumpang..."
          ></textarea>
        </div>

        <!-- Submit Button -->
        <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 font-semibold transition cursor-pointer"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="form.processing || !form.employee_id"
            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-semibold transition shadow-xs shadow-blue-600/20 cursor-pointer disabled:opacity-50 flex items-center gap-1.5"
          >
            <i class="bi bi-check-lg"></i>
            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Apresiasi' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
