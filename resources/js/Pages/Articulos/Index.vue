<script setup>
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'

const props = defineProps({ 
    articulos: Object,
    filters: Object,
    kpis: Object,
    conteos: Object
})

const search = ref(props.filters.search || '')
const activeTab = ref(props.filters.estado || 'todos')
const sortOrder = ref(props.filters.sort || 'mas_recientes')
const isLoading = ref(false)
const isModalOpen = ref(false)
const selectedArticulo = ref(null)
const isZoomed = ref(false)

const openModal = (articulo) => {
    selectedArticulo.value = articulo
    isModalOpen.value = true
    isZoomed.value = false
}

const closeModal = () => {
    isModalOpen.value = false
    setTimeout(() => selectedArticulo.value = null, 300)
}

const formatMoney = (value) => {
    return new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB', minimumFractionDigits: 0 }).format(value);
}

const applyFilter = (estado) => {
    activeTab.value = estado
    isLoading.value = true 
    router.get(route('articulos.index'), { 
        estado: estado, 
        search: search.value,
        sort: sortOrder.value 
    }, { 
        preserveState: true, 
        preserveScroll: true,
        onFinish: () => isLoading.value = false
    })
}

const applySort = (sort) => {
    sortOrder.value = sort
    isLoading.value = true
    router.get(route('articulos.index'), {
        estado: activeTab.value,
        search: search.value,
        sort: sort
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => isLoading.value = false
    })
}

watch(search, debounce((value) => {
    isLoading.value = true;
    router.get(route('articulos.index'), { 
        estado: activeTab.value, 
        search: value,
        sort: sortOrder.value 
    }, { 
        preserveState: true, 
        preserveScroll: true,
        onFinish: () => isLoading.value = false
    })
}, 500))

const tabs = [
    { key: 'todos',    label: 'TODOS',      emoji: '📦', color: 'indigo' },
    { key: 'activos',  label: 'ACTIVOS',     emoji: '🟢', color: 'emerald', subtitle: '0-30 días' },
    { key: 'retraso',  label: 'RETRASO',     emoji: '🟡', color: 'yellow',  subtitle: '31-60 días' },
    { key: 'riesgo',   label: 'RIESGO',      emoji: '🟠', color: 'orange',  subtitle: '61-90 días' },
    { key: 'vencidos',  label: 'VENCIDOS',   emoji: '🔴', color: 'red',     subtitle: '+90 días' },
]

const getCategoriaBadge = (cat) => {
    const colors = {
        'activos':  'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/30',
        'retraso':  'bg-yellow-100 dark:bg-yellow-500/15 text-yellow-700 dark:text-yellow-300 border-yellow-200 dark:border-yellow-500/30',
        'riesgo':   'bg-orange-100 dark:bg-orange-500/15 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-500/30',
        'vencidos':  'bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-300 border-red-200 dark:border-red-500/30',
    }
    return colors[cat] || 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300'
}

const getDiasBadge = (dias) => {
    if (dias <= 30) return 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
    if (dias <= 60) return 'bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400'
    if (dias <= 90) return 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400'
    return 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400'
}

const getBorderColor = (cat) => {
    const borders = {
        'activos':  'hover:border-emerald-400 dark:hover:border-emerald-600',
        'retraso':  'hover:border-yellow-400 dark:hover:border-yellow-600',
        'riesgo':   'hover:border-orange-400 dark:hover:border-orange-600',
        'vencidos':  'hover:border-red-400 dark:hover:border-red-600',
    }
    return borders[cat] || 'hover:border-indigo-400'
}
</script>

<template>
    <Head title="Artículos" />
  <Layout>
    <div class="min-h-screen bg-gray-50 dark:bg-[#0f0f0f] text-gray-800 dark:text-gray-300 font-sans pb-20 selection:bg-indigo-500 selection:text-white transition-colors duration-300">
      
      <!-- STICKY HEADER -->
      <div class="bg-white/90 dark:bg-[#0f0f0f]/90 backdrop-blur-xl border-b border-gray-200 dark:border-gray-800 sticky top-0 z-30 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Top Bar -->
            <div class="py-5 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-lg shadow-indigo-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Inventario & Garantías</h1>
                        <p class="text-xs text-gray-500 font-medium">Gestión de prendas</p>
                    </div>
                </div>

                <!-- Search -->
                <div class="relative w-full md:w-72 group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Buscar artículo, cliente..." 
                        class="pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 block w-full text-gray-900 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 transition-all shadow-inner"
                    >
                </div>

                <!-- Sorting Dropdown -->
                <div class="relative min-w-[160px] md:w-48">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h6M3 17h6m-6-5h12M15 7l4-4m0 0l4 4m-4-4v12" /></svg>
                    </div>
                    <select 
                        v-model="sortOrder"
                        @change="applySort($event.target.value)"
                        class="pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 block w-full text-gray-900 dark:text-gray-200 cursor-pointer appearance-none transition-all shadow-inner font-medium"
                    >
                        <option value="mas_recientes">Más recientes</option>
                        <option value="mas_antiguos">Más antiguos</option>
                        <option value="mayor_valor">Mayor valor</option>
                        <option value="menor_valor">Menor valor</option>
                        <option value="criticos">Críticos primero</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- KPIs & Filters Row -->
            <div class="flex flex-col md:flex-row justify-between items-end gap-6 md:gap-4 pb-0">
                
                <!-- KPI Mini Cards -->
                <div class="flex gap-4 w-full md:w-auto overflow-x-auto pb-4 md:pb-0 hide-scrollbar">
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-gray-800 p-4 min-w-[160px] shadow-sm dark:shadow-lg flex flex-col justify-between group hover:border-indigo-500/30 transition-colors">
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Capital en Garantía</p>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <p class="text-lg font-black text-gray-900 dark:text-white">{{ formatMoney(kpis.capital_visible) }}</p>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-gray-800 p-4 min-w-[140px] shadow-sm dark:shadow-lg flex flex-col justify-between group hover:border-indigo-500/30 transition-colors">
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Items Visibles</p>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                            <p class="text-lg font-black text-gray-900 dark:text-white">{{ kpis.items_visibles }}</p>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-gray-800 p-4 min-w-[140px] shadow-sm dark:shadow-lg flex flex-col justify-between group hover:border-red-500/30 transition-colors">
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Críticos</p>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                            <p class="text-lg font-black text-red-600 dark:text-red-400">{{ kpis.items_criticos }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex gap-1 bg-gray-50 dark:bg-[#1a1a1a] p-1 rounded-xl border border-gray-200 dark:border-gray-800 overflow-x-auto max-w-full">
                    <button v-for="tab in tabs" :key="tab.key"
                        @click="applyFilter(tab.key)"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2"
                        :class="activeTab === tab.key 
                            ? 'bg-white dark:bg-'+tab.color+'-600 text-'+tab.color+'-600 dark:text-white shadow-sm dark:shadow-lg border border-gray-200 dark:border-transparent' 
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5'">
                        <span>{{ tab.emoji }}</span>
                        {{ tab.label }}
                        <span v-if="tab.subtitle" class="text-[9px] opacity-60 hidden sm:inline">{{ tab.subtitle }}</span>
                        <span class="bg-gray-200 dark:bg-black/30 px-1.5 py-0.5 rounded text-[10px]">{{ conteos[tab.key] }}</span>
                    </button>
                </div>
            </div>
            
             <!-- Spacer line -->
             <div class="h-px w-full bg-gradient-to-r from-transparent via-gray-200 dark:via-gray-800 to-transparent mt-4 transition-colors duration-300"></div>
        </div>
      </div>

      <!-- GRID CONTENT -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        
        <!-- Loading State -->
        <div v-if="isLoading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div v-for="n in 12" :key="n" class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden animate-pulse">
                <div class="aspect-square bg-gray-200 dark:bg-gray-800"></div>
                <div class="p-3 space-y-2">
                    <div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-3/4"></div>
                    <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded w-1/2"></div>
                    <div class="h-6 bg-gray-100 dark:bg-gray-800 rounded w-full"></div>
                </div>
            </div>
        </div>

        <div v-else-if="articulos.data?.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            
            <div v-for="articulo in articulos.data" :key="articulo.id" 
                @click="openModal(articulo)"
                :class="['group bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl overflow-hidden cursor-pointer hover:-translate-y-0.5 transition-all duration-300 relative', getBorderColor(articulo.categoria_articulo)]">
                
                <!-- Categoria Badge -->
                <div class="absolute top-2 left-2 z-10">
                    <span :class="['text-[9px] font-black px-2 py-0.5 rounded-full border shadow-sm', getCategoriaBadge(articulo.categoria_articulo)]">
                        {{ articulo.categoria_emoji }} {{ articulo.categoria_label }}
                    </span>
                </div>

                <!-- Dias Badge -->
                <div class="absolute top-2 right-2 z-10">
                    <span v-if="articulo.dias_retraso > 30" :class="['text-[9px] font-black px-2 py-0.5 rounded-full', getDiasBadge(articulo.dias_retraso)]">
                        {{ articulo.dias_retraso }}d
                    </span>
                    <span v-else class="text-[9px] font-black px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        {{ articulo.fecha_vencimiento }}
                    </span>
                </div>

                <!-- Image -->
                <div class="aspect-square bg-gray-100 dark:bg-[#141414] relative overflow-hidden">
                    <img v-if="articulo.foto_url" :src="`/storage/${articulo.foto_url}`" 
                        class="w-full h-full object-cover group-hover:scale-110 transition-all duration-500"
                        loading="lazy">
                    <div v-else class="w-full h-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <!-- Precio flotante -->
                    <div class="absolute bottom-2 left-2 right-2">
                        <div class="bg-white/90 dark:bg-black/60 backdrop-blur-sm px-2.5 py-1.5 rounded-lg border border-white/20 dark:border-white/10 shadow text-center">
                            <p class="text-xs font-black text-gray-900 dark:text-white">Bs {{ (articulo.valor_proporcional || 0).toLocaleString('es-BO', {minimumFractionDigits: 0}) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="p-3 space-y-1.5">
                    <h3 class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ articulo.nombre }}</h3>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ articulo.cliente?.nombre || '—' }}
                    </p>
                    <p class="text-[9px] font-mono text-indigo-500 dark:text-indigo-400 truncate">
                        #{{ articulo.prestamo?.codigo || '—' }}
                    </p>
                    <!-- Barra de estado visual -->
                    <div class="h-1 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden mt-1">
                        <div :class="[
                            'h-full rounded-full transition-all duration-500',
                            articulo.categoria_articulo === 'activos' ? 'bg-emerald-500 w-1/4' :
                            articulo.categoria_articulo === 'retraso' ? 'bg-yellow-500 w-1/2' :
                            articulo.categoria_articulo === 'riesgo' ? 'bg-orange-500 w-3/4' :
                            'bg-red-500 w-full'
                        ]"></div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-20 text-gray-500 bg-white dark:bg-[#1a1a1a] rounded-3xl border border-gray-200 dark:border-gray-800 border-dashed">
            <svg class="w-16 h-16 text-gray-300 dark:text-gray-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
            <p class="text-lg font-medium text-gray-600 dark:text-gray-400">No se encontraron artículos</p>
            <p class="text-sm text-gray-400 dark:text-gray-600">Intenta cambiar los filtros o buscar por nombre</p>
        </div>

        <!-- Pagination -->
        <div v-if="articulos.links && articulos.data.length > 0" class="mt-8 flex justify-center pb-12">
             <div class="flex gap-2 p-1 bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <Link v-for="(link, k) in articulos.links" :key="k" :href="link.url || '#'" v-html="link.label"
                    class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all" 
                    :class="{ 
                        'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25': link.active, 
                        'text-gray-500 dark:text-gray-500 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800': !link.active && link.url,
                        'text-gray-300 dark:text-gray-700 cursor-not-allowed': !link.url
                    }" />
             </div>
        </div>
      </div>
      
      <!-- MODAL -->
      <Teleport to="body">
          <Transition name="modal">
              <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4" @click.self="closeModal">
                  <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
                  
                  <div v-if="selectedArticulo" class="relative w-full max-w-2xl bg-white dark:bg-[#1a1a1a] rounded-3xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 max-h-[90vh] overflow-y-auto">
                      
                      <!-- Image header -->
                      <div class="relative bg-gray-100 dark:bg-black h-64 flex items-center justify-center cursor-pointer" @click="isZoomed = !isZoomed">
                          <img v-if="selectedArticulo.foto_url" :src="`/storage/${selectedArticulo.foto_url}`" 
                              :class="['object-contain transition-all duration-500', isZoomed ? 'h-[80vh] w-auto fixed inset-0 z-50 m-auto bg-black/95 p-4' : 'h-full w-full']">
                          <div v-else class="flex flex-col items-center gap-2 text-gray-400">
                              <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                              </svg>
                              <span class="text-xs font-bold">Sin imagen</span>
                          </div>
                          <button @click.stop="closeModal" class="absolute top-3 right-3 bg-black/30 hover:bg-black/50 backdrop-blur text-white rounded-full p-1.5 transition-colors z-20">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                              </svg>
                          </button>
                          <!-- Badge en imagen -->
                          <div class="absolute top-3 left-3 z-10">
                              <span :class="['text-[10px] font-black px-2.5 py-1 rounded-full border shadow', getCategoriaBadge(selectedArticulo.categoria_articulo)]">
                                  {{ selectedArticulo.categoria_emoji }} {{ selectedArticulo.categoria_label }}
                              </span>
                          </div>
                      </div>

                      <!-- Content -->
                      <div class="p-6 space-y-5">
                          <div class="flex justify-between items-start">
                              <div>
                                  <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ selectedArticulo.nombre }}</h2>
                                  <p class="text-xs text-gray-500 mt-0.5">{{ selectedArticulo.descripcion || 'Sin descripción' }}</p>
                              </div>
                              <div class="text-right">
                                  <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Valor</p>
                                  <p class="text-xl font-black text-gray-900 dark:text-white">{{ formatMoney(selectedArticulo.valor_proporcional) }}</p>
                              </div>
                          </div>

                          <!-- Info grid -->
                          <div class="grid grid-cols-2 gap-3 bg-gray-50 dark:bg-[#141414] p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                              <div>
                                  <p class="text-[9px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Días de retraso</p>
                                  <span :class="['text-sm font-bold', getDiasBadge(selectedArticulo.dias_retraso)]">
                                      {{ selectedArticulo.dias_retraso > 0 ? `${selectedArticulo.dias_retraso} días` : 'Al día' }}
                                  </span>
                              </div>
                              <div>
                                  <p class="text-[9px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Vence</p>
                                  <p class="text-sm font-bold text-gray-900 dark:text-white">{{ selectedArticulo.fecha_vencimiento }}</p>
                              </div>
                              <div>
                                  <p class="text-[9px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Saldo pendiente</p>
                                  <p class="text-sm font-bold text-red-600 dark:text-red-400">{{ formatMoney(selectedArticulo.saldo_pendiente) }}</p>
                              </div>
                              <div>
                                  <p class="text-[9px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Estado préstamo</p>
                                  <p class="text-sm font-bold text-gray-900 dark:text-white">{{ selectedArticulo.estado_prestamo }}</p>
                              </div>
                          </div>

                          <!-- Cliente y prestamo -->
                          <div class="grid grid-cols-2 gap-3">
                              <div class="bg-gray-50 dark:bg-[#141414] rounded-2xl p-4 border border-gray-100 dark:border-gray-800">
                                  <p class="text-[9px] font-bold text-gray-500 uppercase tracking-wider mb-1">Cliente</p>
                                  <p class="text-sm font-bold text-gray-900 dark:text-white">{{ selectedArticulo.cliente?.nombre }}</p>
                              </div>
                              <div class="bg-gray-50 dark:bg-[#141414] rounded-2xl p-4 border border-gray-100 dark:border-gray-800">
                                  <p class="text-[9px] font-bold text-gray-500 uppercase tracking-wider mb-1">Préstamo</p>
                                  <p class="font-mono text-sm font-bold text-indigo-600 dark:text-indigo-400">#{{ selectedArticulo.prestamo?.codigo }}</p>
                              </div>
                          </div>

                          <!-- Actions -->
                          <div class="flex gap-3 pt-2">
                              <button @click="closeModal" 
                                  class="px-5 py-3 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-bold text-xs hover:bg-gray-50 dark:hover:bg-gray-800 transition-all flex-1">
                                  Cerrar
                              </button>
                              <Link :href="route('clientes.detalle', selectedArticulo.cliente?.id)" 
                                  class="px-5 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-xs text-center hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-500/25 transition-all flex-1 flex items-center justify-center gap-2">
                                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                  </svg>
                                  Ver detalle completo
                              </Link>
                          </div>
                      </div>
                  </div>
              </div>
          </Transition>
      </Teleport>

    </div>
  </Layout>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>