<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  distribution: {
    type: Object,
    required: true,
  },
});

// View mode: 'reference' (Peta Stylized Vektor Jakarta) or 'gis' (Peta Satelit/Jalanan Leaflet)
const mapMode = ref('reference');

// Layout mode: false = split side-by-side (8 cols / 4 cols), true = full-width map (12 cols)
const isExpandedMap = ref(false);

// Active filters
const activeStatusFilter = ref('all'); // 'all', 'tetap', 'vendor', 'magang'
const activePositionFilter = ref('all'); // 'all', 'Pramudi', 'Pramusapa', 'Pramujaga', 'Karyawan Kantor'

// Selected region for deep breakdown (default to jakarta_timur)
const selectedRegionKey = ref('jakarta_timur');

// GIS Tile Style: 'voyager' (CartoDB), 'osm' (OpenStreetMap), 'satellite' (Esri)
const activeTileStyle = ref('voyager');

// 5 Regions Meta matching the stylized Transjakarta Vector Map
const regionsMeta = {
  jakarta_utara: {
    name: 'Jakarta Utara',
    englishName: 'NORTH',
    color: '#027ea5',
    fillColor: '#027ea5',
    activeFill: '#0891b2',
    strokeColor: '#0e7490',
    accentBg: 'bg-cyan-50 text-cyan-800 border-cyan-300',
    dotBg: 'bg-cyan-600',
    center: [-6.1384, 106.8640],
    depots: ['Pool Pegangsaan Dua', 'Pool Tanjung Priok', 'Halte Sentral Pluit'],
    description: 'Pusat operasional rute pesisir utara (Koridor 12 Pluit–Tanjung Priok) serta pangkalan armada bus listrik ramah lingkungan.',
  },
  jakarta_barat: {
    name: 'Jakarta Barat',
    englishName: 'WEST',
    color: '#cca06e',
    fillColor: '#cca06e',
    activeFill: '#dfb584',
    strokeColor: '#b48452',
    accentBg: 'bg-amber-50 text-amber-800 border-amber-300',
    dotBg: 'bg-amber-600',
    center: [-6.1683, 106.7589],
    depots: ['Pool Rawa Buaya', 'Pool Pesing', 'Terminal Kalideres'],
    description: 'Pangkalan bus koridor barat utama (Koridor 3 & Koridor 8) serta perawatan armada bus tempel gandeng dan maxi.',
  },
  jakarta_pusat: {
    name: 'Jakarta Pusat',
    englishName: 'CENTRAL',
    color: '#be5162',
    fillColor: '#be5162',
    activeFill: '#d66879',
    strokeColor: '#9f3c4c',
    accentBg: 'bg-rose-50 text-rose-800 border-rose-300',
    dotBg: 'bg-rose-600',
    center: [-6.1805, 106.8284],
    depots: ['Kantor Pusat Cawang / Koridor 1', 'Halte Sentral Harmoni', 'Depo Monas'],
    description: 'Pusat operasional utama, koridor inti Bus Rapid Transit (BRT) Koridor 1 & 2, serta gedung manajemen korporat.',
  },
  jakarta_selatan: {
    name: 'Jakarta Selatan',
    englishName: 'SOUTH',
    color: '#b78210',
    fillColor: '#b78210',
    activeFill: '#d49b1a',
    strokeColor: '#946607',
    accentBg: 'bg-yellow-50 text-yellow-800 border-yellow-300',
    dotBg: 'bg-yellow-600',
    center: [-6.2615, 106.8106],
    depots: ['Terminal Blok M', 'Pool Lebak Bulus', 'Halte CSW / ASEAN', 'Pool Cipedak'],
    description: 'Hub transit integrasi utama Koridor 1, 6, dan 13 (jalur layang khusus busway Ciledug–Tendean) serta integrasi MRT Jakarta.',
  },
  jakarta_timur: {
    name: 'Jakarta Timur',
    englishName: 'EAST',
    color: '#459885',
    fillColor: '#459885',
    activeFill: '#57b09c',
    strokeColor: '#337a6b',
    accentBg: 'bg-emerald-50 text-emerald-800 border-emerald-300',
    dotBg: 'bg-emerald-600',
    center: [-6.2250, 106.9004],
    depots: ['Pool Cawang', 'Pool Pinang Ranti', 'Pool Klender', 'Terminal Kp. Rambutan'],
    description: 'Wilayah pangkalan dan depo armada terbesar Transjakarta yang melayani Koridor 7, 9, 10, dan rute penghubung timur.',
  },
};

// Transjakarta Depot Locations with GPS coordinates
const transjakartaDepots = [
  // Jakarta Timur
  { name: 'Pool Cawang', region: 'jakarta_timur', lat: -6.2428, lng: 106.8715, desc: 'Pusat operasional armada Koridor 7 & 9' },
  { name: 'Pool Pinang Ranti', region: 'jakarta_timur', lat: -6.2915, lng: 106.8856, desc: 'Depo armada utama Koridor 9 (Pinang Ranti - Pluit)' },
  { name: 'Terminal Kp. Rambutan', region: 'jakarta_timur', lat: -6.3090, lng: 106.8785, desc: 'Pangkalan bus terintegrasi Koridor 7' },
  { name: 'Pool Klender', region: 'jakarta_timur', lat: -6.2135, lng: 106.9012, desc: 'Depo feeder dan rute penghubung timur' },

  // Jakarta Barat
  { name: 'Pool Rawa Buaya', region: 'jakarta_barat', lat: -6.1552, lng: 106.7289, desc: 'Pangkalan armada gandeng & maxi Koridor 3' },
  { name: 'Pool Pesing', region: 'jakarta_barat', lat: -6.1602, lng: 106.7695, desc: 'Depo perawatan dan perbaikan bus barat' },
  { name: 'Terminal Kalideres', region: 'jakarta_barat', lat: -6.1535, lng: 106.7025, desc: 'Pangkalan terminus barat Koridor 3' },

  // Jakarta Pusat
  { name: 'Kantor Pusat Cawang / Koridor 1', region: 'jakarta_pusat', lat: -6.2465, lng: 106.8670, desc: 'Gedung Manajemen & Operation Command Center (OCC)' },
  { name: 'Halte Sentral Harmoni', region: 'jakarta_pusat', lat: -6.1664, lng: 106.8202, desc: 'Hub transit sentral rute utama Transjakarta' },
  { name: 'Depo Monas', region: 'jakarta_pusat', lat: -6.1754, lng: 106.8272, desc: 'Pangkalan armada khusus wisata & shuttle pusat kota' },

  // Jakarta Utara
  { name: 'Pool Pegangsaan Dua', region: 'jakarta_utara', lat: -6.1570, lng: 106.9180, desc: 'Depo modern bus listrik ramah lingkungan' },
  { name: 'Pool Tanjung Priok', region: 'jakarta_utara', lat: -6.1150, lng: 106.8820, desc: 'Pangkalan terminus utara Koridor 10 & 12' },
  { name: 'Halte Sentral Pluit', region: 'jakarta_utara', lat: -6.1265, lng: 106.7915, desc: 'Hub transit pesisir barat-utara Koridor 9 & 12' },

  // Jakarta Selatan
  { name: 'Terminal Blok M', region: 'jakarta_selatan', lat: -6.2440, lng: 106.7980, desc: 'Pusat terminus selatan Koridor 1' },
  { name: 'Pool Lebak Bulus', region: 'jakarta_selatan', lat: -6.2890, lng: 106.7745, desc: 'Pangkalan terminus barat-selatan Koridor 8' },
  { name: 'Halte CSW / ASEAN', region: 'jakarta_selatan', lat: -6.2400, lng: 106.7990, desc: 'Hub transit layang megah Koridor 1 & 13' },
  { name: 'Pool Cipedak / Ragunan', region: 'jakarta_selatan', lat: -6.3120, lng: 106.8210, desc: 'Pangkalan armada koridor 6 (Ragunan–Dukuh Atas)' },
];

// Realistic boundary polygons for Leaflet GIS
const regionPolygonsCoordinates = {
  jakarta_utara: [
    [-6.102, 106.700], [-6.108, 106.745], [-6.115, 106.785], [-6.120, 106.835],
    [-6.102, 106.885], [-6.096, 106.925], [-6.092, 106.968], [-6.155, 106.960],
    [-6.168, 106.918], [-6.150, 106.872], [-6.140, 106.838], [-6.136, 106.795],
    [-6.125, 106.740], [-6.102, 106.700],
  ],
  jakarta_barat: [
    [-6.115, 106.700], [-6.138, 106.685], [-6.158, 106.695], [-6.180, 106.720],
    [-6.195, 106.735], [-6.205, 106.765], [-6.210, 106.795], [-6.178, 106.795],
    [-6.155, 106.805], [-6.136, 106.795], [-6.125, 106.740], [-6.115, 106.700],
  ],
  jakarta_pusat: [
    [-6.140, 106.838], [-6.160, 106.870], [-6.180, 106.872], [-6.195, 106.855],
    [-6.205, 106.840], [-6.212, 106.820], [-6.210, 106.795], [-6.178, 106.795],
    [-6.158, 106.815], [-6.145, 106.825], [-6.140, 106.838],
  ],
  jakarta_timur: [
    [-6.168, 106.918], [-6.155, 106.960], [-6.175, 106.975], [-6.210, 106.955],
    [-6.245, 106.940], [-6.280, 106.925], [-6.335, 106.915], [-6.365, 106.885],
    [-6.345, 106.860], [-6.310, 106.865], [-6.265, 106.860], [-6.240, 106.865],
    [-6.215, 106.860], [-6.195, 106.855], [-6.180, 106.872], [-6.175, 106.895],
    [-6.168, 106.918],
  ],
  jakarta_selatan: [
    [-6.210, 106.795], [-6.212, 106.820], [-6.205, 106.840], [-6.240, 106.865],
    [-6.265, 106.860], [-6.310, 106.865], [-6.345, 106.860], [-6.360, 106.820],
    [-6.345, 106.785], [-6.280, 106.760], [-6.240, 106.780], [-6.210, 106.795],
  ],
};

// Compute regional counts based on active filters
const filteredRegionStats = computed(() => {
  const result = {};
  const data = props.distribution?.regions || {};

  for (const key of ['jakarta_timur', 'jakarta_barat', 'jakarta_pusat', 'jakarta_utara', 'jakarta_selatan']) {
    const reg = data[key] || {
      total: 0,
      tetap: 0,
      vendor: 0,
      magang: 0,
      positions: { Pramudi: 0, Pramusapa: 0, Pramujaga: 0, 'Karyawan Kantor': 0 },
      attendance: { present: 0, late: 0, sick: 0, permission: 0, not_checked_in: 0 },
    };

    let count = reg.total;

    if (activeStatusFilter.value !== 'all') {
      count = reg[activeStatusFilter.value] || 0;
    } else if (activePositionFilter.value !== 'all') {
      count = reg.positions?.[activePositionFilter.value] || 0;
    }

    result[key] = {
      ...reg,
      displayCount: count,
      meta: regionsMeta[key],
    };
  }

  return result;
});

// Total count based on active filter
const totalFilteredCount = computed(() => {
  let sum = 0;
  for (const key in filteredRegionStats.value) {
    sum += filteredRegionStats.value[key].displayCount || 0;
  }
  return sum;
});

// Active selected region data
const activeRegionData = computed(() => {
  return filteredRegionStats.value[selectedRegionKey.value] || filteredRegionStats.value['jakarta_timur'];
});

const selectRegion = (key) => {
  selectedRegionKey.value = key;
  updateLeafletHighlights();
  if (leafletMap && regionsMeta[key]) {
    leafletMap.panTo(regionsMeta[key].center, { animate: true, duration: 0.8 });
  }
};

// Percentage helper
const pct = (val, total) => {
  if (!total || total <= 0) return 0;
  return Math.round((val / total) * 100);
};

// --- LEAFLET GIS ENGINE ---
const leafletContainer = ref(null);
const isLeafletReady = ref(false);
let leafletMap = null;
let tileLayerInstance = null;
const polygonLayers = {};
let depotMarkerGroup = null;

const tileLayerUrls = {
  voyager: {
    url: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
    options: {
      attribution: '&copy; CARTO &copy; OpenStreetMap',
      maxZoom: 19,
      subdomains: 'abcd',
    },
  },
  osm: {
    url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    options: {
      attribution: '&copy; OpenStreetMap',
      maxZoom: 19,
    },
  },
  satellite: {
    url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    options: {
      attribution: '&copy; Esri World Imagery',
      maxZoom: 18,
    },
  },
};

const switchTileLayer = (styleKey) => {
  activeTileStyle.value = styleKey;
  if (!leafletMap || !window.L) return;

  if (tileLayerInstance) {
    leafletMap.removeLayer(tileLayerInstance);
  }

  const config = tileLayerUrls[styleKey];
  tileLayerInstance = window.L.tileLayer(config.url, config.options).addTo(leafletMap);
};

const resetMapView = () => {
  if (leafletMap) {
    leafletMap.setView([-6.215, 106.845], 11, { animate: true });
  }
};

const updateLeafletHighlights = () => {
  if (!leafletMap || !window.L) return;

  for (const [key, layer] of Object.entries(polygonLayers)) {
    const isSelected = selectedRegionKey.value === key;
    const meta = regionsMeta[key];
    const stats = filteredRegionStats.value[key];
    const count = stats?.displayCount || 0;

    layer.setStyle({
      fillColor: isSelected ? meta.activeFill : meta.fillColor,
      fillOpacity: isSelected ? 0.9 : 0.7,
      color: isSelected ? '#ffffff' : meta.strokeColor,
      weight: isSelected ? 4 : 2,
    });

    if (layer.getTooltip()) {
      layer.setTooltipContent(`
        <div class="p-1 font-sans text-xs">
          <div class="font-bold text-slate-900">${meta.name} (${meta.englishName})</div>
          <div class="font-semibold text-blue-600 mt-0.5">${count.toLocaleString('id-ID')} Pegawai</div>
          <div class="text-[10px] text-slate-400">Klik untuk melihat detail rute & staf</div>
        </div>
      `);
    }
  }
};

const initLeaflet = () => {
  if (!leafletContainer.value || !window.L || leafletMap) return;

  const L = window.L;

  leafletMap = L.map(leafletContainer.value, {
    center: [-6.215, 106.845],
    zoom: 11,
    zoomControl: false,
    attributionControl: true,
  });

  L.control.zoom({ position: 'bottomright' }).addTo(leafletMap);
  switchTileLayer(activeTileStyle.value);

  for (const [regionKey, coords] of Object.entries(regionPolygonsCoordinates)) {
    const meta = regionsMeta[regionKey];
    const stats = filteredRegionStats.value[regionKey];
    const isSelected = selectedRegionKey.value === regionKey;

    const polygon = L.polygon(coords, {
      fillColor: isSelected ? meta.activeFill : meta.fillColor,
      fillOpacity: isSelected ? 0.9 : 0.7,
      color: isSelected ? '#ffffff' : meta.strokeColor,
      weight: isSelected ? 4 : 2,
    }).addTo(leafletMap);

    polygon.bindTooltip(`
      <div class="p-1 font-sans text-xs">
        <div class="font-bold text-slate-900">${meta.name} (${meta.englishName})</div>
        <div class="font-semibold text-blue-600 mt-0.5">${(stats?.displayCount || 0).toLocaleString('id-ID')} Pegawai</div>
      </div>
    `, { sticky: true, opacity: 0.95 });

    polygon.on('click', () => {
      selectRegion(regionKey);
    });

    polygonLayers[regionKey] = polygon;
  }

  depotMarkerGroup = L.layerGroup().addTo(leafletMap);

  transjakartaDepots.forEach((depot) => {
    const meta = regionsMeta[depot.region];
    const depotIcon = L.divIcon({
      className: 'tj-depot-marker',
      iconSize: [28, 28],
      iconAnchor: [14, 14],
      html: `
        <div style="position:relative; width:28px; height:28px; display:flex; align-items:center; justify-content:center; cursor:pointer;">
          <span style="position:absolute; width:100%; height:100%; border-radius:9999px; background-color:${meta.strokeColor}; opacity:0.4; animation:ping 1.6s cubic-bezier(0,0,0.2,1) infinite;"></span>
          <div style="width:22px; height:22px; border-radius:9999px; background-color:${meta.strokeColor}; color:#ffffff; display:flex; align-items:center; justify-content:center; border:2px solid #ffffff; box-shadow:0 3px 6px rgba(0,0,0,0.25);">
            <svg style="width:11px; height:11px;" fill="currentColor" viewBox="0 0 16 16">
              <path d="M2.5 1A1.5 1.5 0 0 0 1 2.5v11A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-11A1.5 1.5 0 0 0 13.5 1h-11zm1 1h9a.5.5 0 0 1 .5.5V5H3V2.5a.5.5 0 0 1 .5-.5zm9 4v4H3V6h9.5zm-9.5 5h9.5v2.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V11z"/>
            </svg>
          </div>
        </div>
      `,
    });

    const marker = L.marker([depot.lat, depot.lng], { icon: depotIcon }).addTo(depotMarkerGroup);

    marker.bindPopup(`
      <div class="p-2 font-sans">
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full" style="background-color: ${meta.strokeColor}"></span>
          <strong class="text-xs text-slate-900">${depot.name}</strong>
        </div>
        <div class="text-[11px] text-slate-600 mt-1">${depot.desc}</div>
        <div class="text-[10px] text-blue-600 font-bold mt-1.5">${meta.name}</div>
      </div>
    `);
  });

  isLeafletReady.value = true;
};

// Load Leaflet dynamically when GIS mode is picked
const loadLeafletAssets = () => {
  if (window.L) {
    nextTick(initLeaflet);
    return;
  }

  if (!document.getElementById('leaflet-css')) {
    const link = document.createElement('link');
    link.id = 'leaflet-css';
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);
  }

  if (!document.getElementById('leaflet-js')) {
    const script = document.createElement('script');
    script.id = 'leaflet-js';
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => {
      nextTick(initLeaflet);
    };
    document.head.appendChild(script);
  }
};

watch(mapMode, (newMode) => {
  if (newMode === 'gis') {
    nextTick(() => {
      loadLeafletAssets();
      if (leafletMap) {
        setTimeout(() => leafletMap.invalidateSize(), 200);
      }
    });
  }
});

watch([activeStatusFilter, activePositionFilter], () => {
  nextTick(() => {
    updateLeafletHighlights();
  });
});

onMounted(() => {
  // default starts in reference mode
});

onUnmounted(() => {
  if (leafletMap) {
    leafletMap.remove();
    leafletMap = null;
  }
});
</script>

<template>
  <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden transition-all duration-300">
    <!-- Main Header & Filter Control Bar -->
    <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-slate-50/50">
      <div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-blue-600 text-white font-bold text-xs shadow-xs">
            <i class="bi bi-geo-alt-fill"></i>
          </span>
          <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
            Peta Distribusi Personel & Armada PT Transportasi Jakarta
          </h2>
        </div>
        <p class="text-xs text-slate-500 mt-1">
          Persebaran resmi <strong>3.520 Staf Transjakarta</strong> di 5 Wilayah Operasional (Utara, Barat, Pusat, Selatan, Timur).
        </p>
      </div>

      <!-- Quick Status Filters & Actions -->
      <div class="flex flex-wrap items-center gap-2">
        <!-- Status Filter Buttons -->
        <div class="inline-flex rounded-xl bg-slate-200/80 p-0.5 text-xs font-semibold">
          <button
            type="button"
            @click="activeStatusFilter = 'all'; activePositionFilter = 'all'"
            class="px-2.5 py-1 rounded-lg transition-all cursor-pointer"
            :class="activeStatusFilter === 'all' && activePositionFilter === 'all' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            Semua (3.520)
          </button>
          <button
            type="button"
            @click="activeStatusFilter = 'tetap'; activePositionFilter = 'all'"
            class="px-2.5 py-1 rounded-lg transition-all cursor-pointer"
            :class="activeStatusFilter === 'tetap' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            Tetap (1.300)
          </button>
          <button
            type="button"
            @click="activeStatusFilter = 'vendor'; activePositionFilter = 'all'"
            class="px-2.5 py-1 rounded-lg transition-all cursor-pointer"
            :class="activeStatusFilter === 'vendor' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            Vendor (2.100)
          </button>
          <button
            type="button"
            @click="activeStatusFilter = 'magang'; activePositionFilter = 'all'"
            class="px-2.5 py-1 rounded-lg transition-all cursor-pointer"
            :class="activeStatusFilter === 'magang' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            Magang (120)
          </button>
        </div>

        <div class="h-4 w-px bg-slate-300 hidden sm:block"></div>

        <!-- Position Quick Buttons -->
        <div class="flex items-center gap-1">
          <button
            type="button"
            @click="activePositionFilter = 'Pramudi'; activeStatusFilter = 'all'"
            class="px-2 py-1 rounded-lg text-[11px] font-medium transition-all cursor-pointer"
            :class="activePositionFilter === 'Pramudi' 
              ? 'bg-emerald-600 text-white font-bold shadow-xs' 
              : 'bg-white text-slate-600 hover:bg-slate-200/70 border border-slate-200/80'"
          >
            Pramudi
          </button>
          <button
            type="button"
            @click="activePositionFilter = 'Pramusapa'; activeStatusFilter = 'all'"
            class="px-2 py-1 rounded-lg text-[11px] font-medium transition-all cursor-pointer"
            :class="activePositionFilter === 'Pramusapa' 
              ? 'bg-sky-600 text-white font-bold shadow-xs' 
              : 'bg-white text-slate-600 hover:bg-slate-200/70 border border-slate-200/80'"
          >
            Pramusapa
          </button>
          <button
            type="button"
            @click="activePositionFilter = 'Pramujaga'; activeStatusFilter = 'all'"
            class="px-2 py-1 rounded-lg text-[11px] font-medium transition-all cursor-pointer"
            :class="activePositionFilter === 'Pramujaga' 
              ? 'bg-rose-600 text-white font-bold shadow-xs' 
              : 'bg-white text-slate-600 hover:bg-slate-200/70 border border-slate-200/80'"
          >
            Pramujaga
          </button>
          <button
            type="button"
            @click="activePositionFilter = 'Karyawan Kantor'; activeStatusFilter = 'all'"
            class="px-2 py-1 rounded-lg text-[11px] font-medium transition-all cursor-pointer"
            :class="activePositionFilter === 'Karyawan Kantor' 
              ? 'bg-slate-700 text-white font-bold shadow-xs' 
              : 'bg-white text-slate-600 hover:bg-slate-200/70 border border-slate-200/80'"
          >
            Kantor
          </button>
        </div>

        <!-- Mode & Expand Controls -->
        <div class="flex items-center gap-1.5 ml-auto">
          <div class="inline-flex rounded-xl bg-slate-200/80 p-0.5 text-xs font-semibold">
            <button
              type="button"
              @click="mapMode = 'reference'"
              class="px-2.5 py-1 rounded-lg transition-all cursor-pointer flex items-center gap-1.5"
              :class="mapMode === 'reference' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
            >
              <i class="bi bi-map-fill"></i>
              <span>Peta Vektor</span>
            </button>
            <button
              type="button"
              @click="mapMode = 'gis'"
              class="px-2.5 py-1 rounded-lg transition-all cursor-pointer flex items-center gap-1.5"
              :class="mapMode === 'gis' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
            >
              <i class="bi bi-globe-americas"></i>
              <span>GIS Satelit</span>
            </button>
          </div>

          <!-- Expand / Full Width Toggle -->
          <button
            type="button"
            @click="isExpandedMap = !isExpandedMap"
            class="px-2.5 py-1.5 rounded-xl border border-slate-200/90 text-slate-700 hover:bg-slate-100 text-xs font-bold transition-all flex items-center gap-1 cursor-pointer bg-white shadow-2xs"
            :title="isExpandedMap ? 'Tampilan Standar' : 'Perbesar Peta (Layar Penuh)'"
          >
            <i :class="isExpandedMap ? 'bi bi-fullscreen-exit text-blue-600' : 'bi bi-arrows-fullscreen text-slate-600'"></i>
            <span class="hidden sm:inline">{{ isExpandedMap ? 'Standar' : 'Perbesar' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Map & Detail Panel Grid -->
    <div 
      class="grid gap-0 divide-y lg:divide-y-0 lg:divide-x divide-slate-100 transition-all duration-300"
      :class="isExpandedMap ? 'grid-cols-1' : 'grid-cols-1 lg:grid-cols-12'"
    >
      <!-- Map Canvas (8 cols on desktop when normal, 12 cols when expanded) -->
      <div 
        class="p-3 sm:p-5 bg-slate-50/40 relative flex flex-col justify-between"
        :class="isExpandedMap ? 'col-span-1' : 'lg:col-span-8'"
      >
        <!-- Subheader Controls bar -->
        <div class="flex items-center justify-between gap-2 mb-2">
          <div class="text-xs text-slate-500 flex items-center gap-1.5">
            <i class="bi bi-hand-index-thumb-fill text-blue-600"></i>
            <span>Klik wilayah kota pada peta untuk melihat persebaran staf & depot</span>
          </div>

          <!-- GIS Layer toolbar if GIS mode -->
          <div v-if="mapMode === 'gis'" class="flex items-center gap-1.5">
            <div class="flex items-center rounded-lg bg-white border border-slate-200/80 p-0.5 text-[11px] shadow-2xs">
              <button
                type="button"
                @click="switchTileLayer('voyager')"
                class="px-2 py-0.5 rounded-md font-medium"
                :class="activeTileStyle === 'voyager' ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600'"
              >
                Modern
              </button>
              <button
                type="button"
                @click="switchTileLayer('satellite')"
                class="px-2 py-0.5 rounded-md font-medium"
                :class="activeTileStyle === 'satellite' ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600'"
              >
                Satelit
              </button>
            </div>

            <button
              type="button"
              @click="resetMapView"
              class="px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200/80 text-slate-700 rounded-lg text-xs font-semibold cursor-pointer"
            >
              <i class="bi bi-arrows-fullscreen text-[10px]"></i>
            </button>
          </div>
        </div>

        <!-- 1. MODE VEKTOR RESMI: PETA STYLIZED JAKARTA (SESUAI GAMBAR USER) -->
        <div 
          v-show="mapMode === 'reference'" 
          class="relative w-full rounded-2xl border border-slate-200/90 shadow-inner p-2 sm:p-4 flex items-center justify-center overflow-hidden select-none bg-gradient-to-b from-slate-50 to-white transition-all duration-300"
          :class="isExpandedMap ? 'h-[720px] sm:h-[800px] lg:h-[860px]' : 'h-[620px] sm:h-[700px] lg:h-[760px]'"
        >
          <svg
            viewBox="90 20 520 620"
            class="w-full h-full max-h-[820px] drop-shadow-md transition-all duration-300"
            xmlns="http://www.w3.org/2000/svg"
          >
            <defs>
              <filter id="soft-shadow" x="-5%" y="-5%" width="115%" height="115%">
                <feDropShadow dx="3" dy="5" stdDeviation="4" flood-color="#64748b" flood-opacity="0.15" />
              </filter>
              <filter id="badge-shadow" x="-10%" y="-10%" width="120%" height="120%">
                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#000000" flood-opacity="0.25" />
              </filter>
            </defs>

            <!-- Title at Top: JAKARTA (Matching reference image typography) -->
            <g class="pointer-events-none select-none">
              <!-- Shadow layer -->
              <text 
                x="352" 
                y="69" 
                text-anchor="middle" 
                font-family="'Outfit', 'Inter', 'Segoe UI', serif" 
                font-size="44" 
                font-weight="900" 
                fill="#cbd5e1" 
                letter-spacing="6"
              >
                JAKARTA
              </text>
              <!-- Main bold slab serif blue title -->
              <text 
                x="350" 
                y="67" 
                text-anchor="middle" 
                font-family="'Outfit', 'Inter', 'Segoe UI', serif" 
                font-size="44" 
                font-weight="900" 
                fill="#1b689a" 
                letter-spacing="6"
              >
                JAKARTA
              </text>
            </g>

            <!-- Background Paper-Cut Silhouette (Offset Shadow Effect from image) -->
            <g transform="translate(12, 14)" opacity="0.45" class="pointer-events-none">
              <!-- Outer silhouette backer -->
              <path
                d="M 145 145 
                   L 180 140 L 250 198 L 270 155 L 290 155 L 295 195 L 340 175 L 395 185 L 405 150 L 580 145 
                   L 585 230 L 580 280 L 585 310 L 550 330 L 555 370 L 515 375 L 520 435 L 540 480 L 530 530 
                   L 490 570 L 470 590 L 450 570 L 435 530 L 395 520 L 395 580 L 330 580 L 330 540 L 350 510 
                   L 310 510 L 265 430 L 270 400 L 250 395 L 260 375 L 190 365 L 195 310 L 145 280 L 145 190 
                   L 160 175 Z"
                fill="#94a3b8"
              />
            </g>

            <!-- Northwest Ocean Water Channel (Signature diagonal blue bay strip from image) -->
            <polygon 
              points="145,145 180,140 250,200 245,225 180,185" 
              fill="#027ea5" 
              stroke="#ffffff" 
              stroke-width="5"
              class="pointer-events-none"
            />

            <!-- ================= 5 PUZZLE-PIECE REGIONS OF JAKARTA ================= -->

            <!-- 1. NORTH (Jakarta Utara - Deep Cyan/Ocean Blue: #027ea5) -->
            <path
              id="poly-north"
              d="M 180 140 
                 L 250 200 
                 L 270 155 
                 L 290 155 
                 L 295 195 
                 L 340 175 
                 L 395 185 
                 L 405 150 
                 L 580 145 
                 L 585 230 
                 L 540 235 
                 L 540 255 
                 L 490 255 
                 L 490 295 
                 L 465 305 
                 L 410 250 
                 L 375 210 
                 L 365 230 
                 L 335 215 
                 L 310 215 
                 L 285 195 
                 L 250 200 
                 Z"
              :fill="selectedRegionKey === 'jakarta_utara' ? regionsMeta.jakarta_utara.activeFill : regionsMeta.jakarta_utara.fillColor"
              stroke="#ffffff"
              :stroke-width="selectedRegionKey === 'jakarta_utara' ? '6' : '9'"
              stroke-linejoin="round"
              stroke-linecap="round"
              class="cursor-pointer transition-all duration-200 hover:brightness-110"
              filter="url(#soft-shadow)"
              @click="selectRegion('jakarta_utara')"
            />

            <!-- 2. WEST (Jakarta Barat - Tan / Warm Camel: #cca06e) -->
            <path
              id="poly-west"
              d="M 145 145 
                 L 180 185 
                 L 250 225 
                 L 285 215 
                 L 305 215 
                 L 285 250 
                 L 305 250 
                 L 285 330 
                 L 265 340 
                 L 245 335 
                 L 240 365 
                 L 190 365 
                 L 195 310 
                 L 145 280 
                 L 145 190 
                 L 160 175 
                 Z"
              :fill="selectedRegionKey === 'jakarta_barat' ? regionsMeta.jakarta_barat.activeFill : regionsMeta.jakarta_barat.fillColor"
              stroke="#ffffff"
              :stroke-width="selectedRegionKey === 'jakarta_barat' ? '6' : '9'"
              stroke-linejoin="round"
              stroke-linecap="round"
              class="cursor-pointer transition-all duration-200 hover:brightness-110"
              filter="url(#soft-shadow)"
              @click="selectRegion('jakarta_barat')"
            />

            <!-- 3. CENTRAL (Jakarta Pusat - Berry Rose / Crimson: #be5162) -->
            <path
              id="poly-central"
              d="M 335 215 
                 L 365 230 
                 L 375 210 
                 L 410 250 
                 L 445 310 
                 L 405 330 
                 L 345 320 
                 L 305 380 
                 L 285 360 
                 L 305 315 
                 L 305 250 
                 L 285 250 
                 L 305 215 
                 Z"
              :fill="selectedRegionKey === 'jakarta_pusat' ? regionsMeta.jakarta_pusat.activeFill : regionsMeta.jakarta_pusat.fillColor"
              stroke="#ffffff"
              :stroke-width="selectedRegionKey === 'jakarta_pusat' ? '6' : '9'"
              stroke-linejoin="round"
              stroke-linecap="round"
              class="cursor-pointer transition-all duration-200 hover:brightness-110"
              filter="url(#soft-shadow)"
              @click="selectRegion('jakarta_pusat')"
            />

            <!-- 4. SOUTH (Jakarta Selatan - Mustard Gold: #b78210) -->
            <path
              id="poly-south"
              d="M 190 365 
                 L 240 365 
                 L 245 335 
                 L 265 340 
                 L 285 360 
                 L 305 380 
                 L 345 320 
                 L 405 330 
                 L 440 380 
                 L 420 440 
                 L 435 500 
                 L 395 520 
                 L 395 580 
                 L 330 580 
                 L 330 540 
                 L 350 510 
                 L 310 510 
                 L 265 430 
                 L 270 400 
                 L 250 395 
                 L 260 375 
                 Z"
              :fill="selectedRegionKey === 'jakarta_selatan' ? regionsMeta.jakarta_selatan.activeFill : regionsMeta.jakarta_selatan.fillColor"
              stroke="#ffffff"
              :stroke-width="selectedRegionKey === 'jakarta_selatan' ? '6' : '9'"
              stroke-linejoin="round"
              stroke-linecap="round"
              class="cursor-pointer transition-all duration-200 hover:brightness-110"
              filter="url(#soft-shadow)"
              @click="selectRegion('jakarta_selatan')"
            />

            <!-- 5. EAST (Jakarta Timur - Sea Green: #459885) -->
            <path
              id="poly-east"
              d="M 465 305 
                 L 490 295 
                 L 490 255 
                 L 540 255 
                 L 540 235 
                 L 585 230 
                 L 580 280 
                 L 585 310 
                 L 550 330 
                 L 555 370 
                 L 515 375 
                 L 520 435 
                 L 540 480 
                 L 530 530 
                 L 490 570 
                 L 470 590 
                 L 450 570 
                 L 435 530 
                 L 395 520 
                 L 435 500 
                 L 420 440 
                 L 440 380 
                 L 405 330 
                 L 445 310 
                 L 410 250 
                 Z"
              :fill="selectedRegionKey === 'jakarta_timur' ? regionsMeta.jakarta_timur.activeFill : regionsMeta.jakarta_timur.fillColor"
              stroke="#ffffff"
              :stroke-width="selectedRegionKey === 'jakarta_timur' ? '6' : '9'"
              stroke-linejoin="round"
              stroke-linecap="round"
              class="cursor-pointer transition-all duration-200 hover:brightness-110"
              filter="url(#soft-shadow)"
              @click="selectRegion('jakarta_timur')"
            />

            <!-- ================= TYPOGRAPHY LABELS ON SHAPES ================= -->

            <!-- NORTH LABELS & COUNTER CHIP -->
            <g transform="translate(485, 195)" class="cursor-pointer" @click="selectRegion('jakarta_utara')">
              <text x="0" y="0" text-anchor="middle" font-size="16" font-weight="900" fill="#ffffff" letter-spacing="2">NORTH</text>
              <rect x="-65" y="10" width="130" height="26" rx="13" fill="#ffffff" fill-opacity="0.95" filter="url(#badge-shadow)" />
              <text x="-48" y="27" font-size="10" font-weight="800" fill="#027ea5">Jakarta Utara</text>
              <text x="45" y="27" text-anchor="end" font-size="11" font-weight="900" fill="#027ea5">{{ filteredRegionStats.jakarta_utara?.displayCount }}</text>
            </g>

            <!-- WEST LABELS & COUNTER CHIP -->
            <g transform="translate(205, 255)" class="cursor-pointer" @click="selectRegion('jakarta_barat')">
              <text x="0" y="0" text-anchor="middle" font-size="16" font-weight="900" fill="#ffffff" letter-spacing="2">WEST</text>
              <rect x="-65" y="10" width="130" height="26" rx="13" fill="#ffffff" fill-opacity="0.95" filter="url(#badge-shadow)" />
              <text x="-48" y="27" font-size="10" font-weight="800" fill="#b48452">Jakarta Barat</text>
              <text x="45" y="27" text-anchor="end" font-size="11" font-weight="900" fill="#b48452">{{ filteredRegionStats.jakarta_barat?.displayCount }}</text>
            </g>

            <!-- CENTRAL LABELS & COUNTER CHIP -->
            <g transform="translate(355, 275)" class="cursor-pointer" @click="selectRegion('jakarta_pusat')">
              <text x="0" y="0" text-anchor="middle" font-size="14" font-weight="900" fill="#ffffff" letter-spacing="2">CENTRAL</text>
              <rect x="-62" y="8" width="124" height="24" rx="12" fill="#ffffff" fill-opacity="0.95" filter="url(#badge-shadow)" />
              <text x="-46" y="24" font-size="10" font-weight="800" fill="#be5162">Jakarta Pusat</text>
              <text x="44" y="24" text-anchor="end" font-size="11" font-weight="900" fill="#be5162">{{ filteredRegionStats.jakarta_pusat?.displayCount }}</text>
            </g>

            <!-- SOUTH LABELS & COUNTER CHIP -->
            <g transform="translate(325, 435)" class="cursor-pointer" @click="selectRegion('jakarta_selatan')">
              <text x="0" y="0" text-anchor="middle" font-size="16" font-weight="900" fill="#ffffff" letter-spacing="2">SOUTH</text>
              <rect x="-68" y="10" width="136" height="26" rx="13" fill="#ffffff" fill-opacity="0.95" filter="url(#badge-shadow)" />
              <text x="-50" y="27" font-size="10" font-weight="800" fill="#b78210">Jakarta Selatan</text>
              <text x="48" y="27" text-anchor="end" font-size="11" font-weight="900" fill="#b78210">{{ filteredRegionStats.jakarta_selatan?.displayCount }}</text>
            </g>

            <!-- EAST LABELS & COUNTER CHIP -->
            <g transform="translate(475, 365)" class="cursor-pointer" @click="selectRegion('jakarta_timur')">
              <text x="0" y="0" text-anchor="middle" font-size="16" font-weight="900" fill="#ffffff" letter-spacing="2">EAST</text>
              <rect x="-65" y="10" width="130" height="26" rx="13" fill="#ffffff" fill-opacity="0.95" filter="url(#badge-shadow)" />
              <text x="-48" y="27" font-size="10" font-weight="800" fill="#337a6b">Jakarta Timur</text>
              <text x="45" y="27" text-anchor="end" font-size="11" font-weight="900" fill="#337a6b">{{ filteredRegionStats.jakarta_timur?.displayCount }}</text>
            </g>

            <!-- ================= TRANSJAKARTA DEPOT BEACONS ================= -->

            <!-- North Depot: Pool Pegangsaan 2 -->
            <g transform="translate(545, 185)" class="pointer-events-none">
              <circle r="14" fill="#027ea5" opacity="0.4" class="animate-ping" />
              <circle r="6.5" fill="#0e7490" stroke="#ffffff" stroke-width="2" />
            </g>

            <!-- West Depot: Pool Rawa Buaya -->
            <g transform="translate(180, 290)" class="pointer-events-none">
              <circle r="14" fill="#cca06e" opacity="0.45" class="animate-ping" />
              <circle r="6.5" fill="#b48452" stroke="#ffffff" stroke-width="2" />
            </g>

            <!-- Central Depot: Kantor Pusat TJ -->
            <g transform="translate(345, 305)" class="pointer-events-none">
              <circle r="14" fill="#be5162" opacity="0.45" class="animate-ping" />
              <circle r="6.5" fill="#9f3c4c" stroke="#ffffff" stroke-width="2" />
            </g>

            <!-- South Depot: Terminal Blok M -->
            <g transform="translate(315, 385)" class="pointer-events-none">
              <circle r="14" fill="#b78210" opacity="0.45" class="animate-ping" />
              <circle r="6.5" fill="#946607" stroke="#ffffff" stroke-width="2" />
            </g>

            <!-- East Depot: Pool Cawang -->
            <g transform="translate(465, 410)" class="pointer-events-none">
              <circle r="14" fill="#459885" opacity="0.45" class="animate-ping" />
              <circle r="6.5" fill="#337a6b" stroke="#ffffff" stroke-width="2" />
            </g>
          </svg>

          <!-- Floating 5-Region Color Chips Legend (Bottom Left) -->
          <div class="absolute bottom-3 left-3 z-20 flex flex-wrap items-center gap-2 bg-white/95 backdrop-blur-md px-3 py-2 rounded-2xl border border-slate-200/90 shadow-md text-xs">
            <div class="flex items-center gap-1.5 cursor-pointer" @click="selectRegion('jakarta_utara')">
              <span class="w-3 h-3 rounded-full bg-[#027ea5] border border-white shadow-2xs"></span>
              <span class="font-bold text-slate-700">Utara (North)</span>
            </div>
            <div class="flex items-center gap-1.5 cursor-pointer" @click="selectRegion('jakarta_barat')">
              <span class="w-3 h-3 rounded-full bg-[#cca06e] border border-white shadow-2xs"></span>
              <span class="font-bold text-slate-700">Barat (West)</span>
            </div>
            <div class="flex items-center gap-1.5 cursor-pointer" @click="selectRegion('jakarta_pusat')">
              <span class="w-3 h-3 rounded-full bg-[#be5162] border border-white shadow-2xs"></span>
              <span class="font-bold text-slate-700">Pusat (Central)</span>
            </div>
            <div class="flex items-center gap-1.5 cursor-pointer" @click="selectRegion('jakarta_selatan')">
              <span class="w-3 h-3 rounded-full bg-[#b78210] border border-white shadow-2xs"></span>
              <span class="font-bold text-slate-700">Selatan (South)</span>
            </div>
            <div class="flex items-center gap-1.5 cursor-pointer" @click="selectRegion('jakarta_timur')">
              <span class="w-3 h-3 rounded-full bg-[#459885] border border-white shadow-2xs"></span>
              <span class="font-bold text-slate-700">Timur (East)</span>
            </div>
          </div>

          <!-- Compass Badge (Top Left) -->
          <div class="absolute top-3.5 left-3.5 z-20 bg-white/90 backdrop-blur-md px-2.5 py-1.5 rounded-xl border border-slate-200 shadow-xs flex items-center gap-2 text-[11px] font-bold text-slate-700">
            <div class="w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[9px] font-black">U</div>
            <span>Utara (North)</span>
          </div>
        </div>

        <!-- 2. MODE GIS (Leaflet Map of DKI Jakarta) -->
        <div 
          v-show="mapMode === 'gis'" 
          class="relative w-full rounded-2xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100 transition-all duration-300"
          :class="isExpandedMap ? 'h-[720px] sm:h-[800px] lg:h-[860px]' : 'h-[620px] sm:h-[700px] lg:h-[760px]'"
        >
          <div ref="leafletContainer" class="w-full h-full z-10"></div>
          
          <!-- Floating Region Buttons Over GIS Map -->
          <div class="absolute top-3 left-3 z-[400] flex flex-col gap-1.5 pointer-events-auto">
            <button
              v-for="(meta, regKey) in regionsMeta"
              :key="regKey"
              type="button"
              @click="selectRegion(regKey)"
              class="px-2.5 py-1 rounded-xl text-left transition-all border shadow-xs cursor-pointer flex items-center justify-between gap-2"
              :class="selectedRegionKey === regKey 
                ? 'bg-slate-900 text-white border-slate-900 shadow-md ring-2 ring-blue-500/40' 
                : 'bg-white/95 backdrop-blur-xs text-slate-700 border-slate-200/90 hover:bg-slate-50'"
            >
              <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: meta.strokeColor }"></span>
                <span class="text-xs font-bold">{{ meta.name }} ({{ meta.englishName }})</span>
              </div>
              <span 
                class="text-[11px] font-extrabold px-1.5 py-0.2 rounded-md"
                :class="selectedRegionKey === regKey ? 'bg-slate-800 text-blue-400' : 'bg-slate-100 text-slate-700'"
              >
                {{ filteredRegionStats[regKey]?.displayCount?.toLocaleString('id-ID') }}
              </span>
            </button>
          </div>
        </div>

        <!-- Footer quick tip -->
        <div class="mt-2 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px] text-slate-500">
          <span class="flex items-center gap-1">
            <i class="bi bi-geo-alt"></i>
            <span>Mode Aktif: <strong>{{ mapMode === 'reference' ? 'Peta Vektor Transjakarta (5 Wilayah Operasional)' : 'Peta Satelit / Jalanan (GIS Leaflet)' }}</strong></span>
          </span>
          <span class="text-slate-400">Total Terpilih: <strong class="text-slate-700">{{ totalFilteredCount.toLocaleString('id-ID') }}</strong> Orang</span>
        </div>
      </div>

      <!-- Regional Drill-Down Detail Drawer (4 cols on desktop when split, or bottom grid when expanded) -->
      <div 
        class="p-5 sm:p-6 flex flex-col justify-between space-y-5 bg-white"
        :class="isExpandedMap ? 'col-span-1 border-t border-slate-100' : 'lg:col-span-4'"
      >
        <!-- Header Info -->
        <div>
          <div class="flex items-start justify-between gap-2 mb-2">
            <div>
              <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wide border" :class="activeRegionData.meta?.accentBg">
                  Wilayah Operasional
                </span>
                <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-md bg-slate-900 text-white uppercase tracking-wider">
                  {{ activeRegionData.meta?.englishName }}
                </span>
              </div>
              <h3 class="text-lg sm:text-xl font-black text-slate-900 mt-1">
                {{ activeRegionData.meta?.name }}
              </h3>
            </div>

            <div class="text-right">
              <div class="text-2xl font-black text-slate-900 leading-none">
                {{ activeRegionData.displayCount?.toLocaleString('id-ID') }}
              </div>
              <div class="text-[11px] text-slate-400 mt-0.5">
                {{ pct(activeRegionData.displayCount, totalFilteredCount) }}% dari total terpilih
              </div>
            </div>
          </div>

          <p class="text-xs text-slate-500 leading-relaxed">
            {{ activeRegionData.meta?.description }}
          </p>
        </div>

        <!-- 5 Region Switcher Quick Tabs -->
        <div class="grid grid-cols-5 gap-1 pt-1 pb-1">
          <button
            v-for="(meta, rKey) in regionsMeta"
            :key="rKey"
            type="button"
            @click="selectRegion(rKey)"
            class="px-1.5 py-1.5 rounded-xl text-center transition-all cursor-pointer border text-xs flex flex-col items-center justify-center gap-0.5"
            :class="selectedRegionKey === rKey 
              ? 'bg-slate-900 text-white border-slate-900 shadow-xs' 
              : 'bg-slate-50 text-slate-700 border-slate-200/80 hover:bg-slate-100'"
          >
            <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: meta.color }"></span>
            <span class="text-[10px] font-bold truncate max-w-full">{{ meta.englishName }}</span>
            <span class="text-[9px] font-extrabold opacity-80">{{ filteredRegionStats[rKey]?.displayCount }}</span>
          </button>
        </div>

        <!-- Status Kepegawaian Breakdown Progress -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
          <div class="text-xs font-bold text-slate-800 flex items-center justify-between">
            <span>Komposisi Status Kerja</span>
            <span class="text-[11px] text-slate-400 font-normal">Total: {{ activeRegionData.total }}</span>
          </div>

          <!-- Multi-colored progress bar -->
          <div class="h-2.5 w-full bg-slate-200/80 rounded-full overflow-hidden flex">
            <div 
              class="h-full bg-blue-600 transition-all duration-300" 
              :style="{ width: pct(activeRegionData.tetap, activeRegionData.total) + '%' }"
              :title="'Tetap: ' + activeRegionData.tetap"
            ></div>
            <div 
              class="h-full bg-purple-500 transition-all duration-300" 
              :style="{ width: pct(activeRegionData.vendor, activeRegionData.total) + '%' }"
              :title="'Vendor: ' + activeRegionData.vendor"
            ></div>
            <div 
              class="h-full bg-amber-500 transition-all duration-300" 
              :style="{ width: pct(activeRegionData.magang, activeRegionData.total) + '%' }"
              :title="'Magang: ' + activeRegionData.magang"
            ></div>
          </div>

          <!-- Status Numbers List -->
          <div class="grid grid-cols-3 gap-2 text-center text-xs pt-1">
            <div class="p-2 rounded-xl bg-white border border-slate-100 shadow-2xs">
              <div class="text-[10px] uppercase font-bold text-blue-600">Tetap</div>
              <div class="font-extrabold text-slate-900 mt-0.5">{{ activeRegionData.tetap }}</div>
              <div class="text-[10px] text-slate-400">{{ pct(activeRegionData.tetap, activeRegionData.total) }}%</div>
            </div>
            <div class="p-2 rounded-xl bg-white border border-slate-100 shadow-2xs">
              <div class="text-[10px] uppercase font-bold text-purple-600">Vendor</div>
              <div class="font-extrabold text-slate-900 mt-0.5">{{ activeRegionData.vendor }}</div>
              <div class="text-[10px] text-slate-400">{{ pct(activeRegionData.vendor, activeRegionData.total) }}%</div>
            </div>
            <div class="p-2 rounded-xl bg-white border border-slate-100 shadow-2xs">
              <div class="text-[10px] uppercase font-bold text-amber-600">Magang</div>
              <div class="font-extrabold text-slate-900 mt-0.5">{{ activeRegionData.magang }}</div>
              <div class="text-[10px] text-slate-400">{{ pct(activeRegionData.magang, activeRegionData.total) }}%</div>
            </div>
          </div>
        </div>

        <!-- Transjakarta Professions Breakdown -->
        <div class="space-y-2">
          <div class="text-xs font-bold text-slate-800 flex items-center justify-between">
            <span>Struktur Profesi Transjakarta</span>
            <span class="text-[11px] text-slate-400 font-medium">Distribusi Lapangan</span>
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs">
            <!-- Pramudi -->
            <div class="p-3 rounded-2xl bg-slate-50/90 border border-slate-100 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                  <i class="bi bi-bus-front"></i>
                </div>
                <div>
                  <div class="font-bold text-slate-900 text-xs">Pramudi</div>
                  <div class="text-[10px] text-slate-400">Driver Armada</div>
                </div>
              </div>
              <span class="text-sm font-extrabold text-slate-900">
                {{ activeRegionData.positions?.Pramudi || 0 }}
              </span>
            </div>

            <!-- Pramusapa -->
            <div class="p-3 rounded-2xl bg-slate-50/90 border border-slate-100 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xs">
                  <i class="bi bi-person-badge"></i>
                </div>
                <div>
                  <div class="font-bold text-slate-900 text-xs">Pramusapa</div>
                  <div class="text-[10px] text-slate-400">Layanan Halte/Bus</div>
                </div>
              </div>
              <span class="text-sm font-extrabold text-slate-900">
                {{ activeRegionData.positions?.Pramusapa || 0 }}
              </span>
            </div>

            <!-- Pramujaga -->
            <div class="p-3 rounded-2xl bg-slate-50/90 border border-slate-100 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-xs">
                  <i class="bi bi-shield-check"></i>
                </div>
                <div>
                  <div class="font-bold text-slate-900 text-xs">Pramujaga</div>
                  <div class="text-[10px] text-slate-400">Keamanan Jalur</div>
                </div>
              </div>
              <span class="text-sm font-extrabold text-slate-900">
                {{ activeRegionData.positions?.Pramujaga || 0 }}
              </span>
            </div>

            <!-- Staf Kantor / Pool -->
            <div class="p-3 rounded-2xl bg-slate-50/90 border border-slate-100 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs">
                  <i class="bi bi-building"></i>
                </div>
                <div>
                  <div class="font-bold text-slate-900 text-xs">Staf Kantor</div>
                  <div class="text-[10px] text-slate-400">OCC & Manajemen</div>
                </div>
              </div>
              <span class="text-sm font-extrabold text-slate-900">
                {{ activeRegionData.positions?.['Karyawan Kantor'] || 0 }}
              </span>
            </div>
          </div>
        </div>

        <!-- Depots & Terminals in this Region -->
        <div>
          <div class="text-xs font-bold text-slate-800 mb-2 flex items-center gap-1.5">
            <i class="bi bi-pin-map-fill text-blue-600"></i>
            <span>Daftar Pool & Terminal di {{ activeRegionData.meta?.name }}:</span>
          </div>
          <div class="flex flex-wrap gap-1.5">
            <span
              v-for="pool in activeRegionData.meta?.depots"
              :key="pool"
              class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-slate-100 text-slate-700 border border-slate-200/60"
            >
              {{ pool }}
            </span>
          </div>
        </div>

        <!-- Attendance Stats Today for this Region -->
        <div class="p-3.5 rounded-2xl bg-blue-50/40 border border-blue-100 flex items-center justify-between gap-3 text-xs">
          <div>
            <div class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Presensi Hari Ini di Wilayah Ini</div>
            <div class="text-xs text-slate-600 mt-0.5 flex items-center gap-2">
              <span class="font-semibold text-emerald-600">Hadir: {{ activeRegionData.attendance?.present || 0 }}</span>
              <span>&bull;</span>
              <span class="font-semibold text-amber-600">Terlambat: {{ activeRegionData.attendance?.late || 0 }}</span>
              <span>&bull;</span>
              <span class="font-semibold text-rose-600">Belum: {{ activeRegionData.attendance?.not_checked_in || 0 }}</span>
            </div>
          </div>
          <Link
            :href="route('admin.employees.index', { region: selectedRegionKey })"
            class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition-colors shrink-0 shadow-xs shadow-blue-500/20"
          >
            Lihat Staf &rarr;
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
/* Leaflet popup overrides */
.leaflet-popup-content-wrapper {
  border-radius: 1rem !important;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
  border: 1px solid #f1f5f9 !important;
  padding: 4px !important;
}
.leaflet-popup-tip {
  background: white !important;
}
.leaflet-tooltip {
  border-radius: 0.75rem !important;
  border: 1px solid #e2e8f0 !important;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
  font-family: inherit !important;
}
</style>
