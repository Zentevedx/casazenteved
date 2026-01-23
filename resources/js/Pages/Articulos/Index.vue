<script setup>
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import debounce from 'lodash/debounce'
import { 
    MagnifyingGlassIcon, 
    FunnelIcon, 
    ExclamationTriangleIcon,
    ClockIcon,
    CurrencyDollarIcon,
    UserIcon,
    TagIcon,
    ArchiveBoxIcon,

    DocumentTextIcon,
    ArrowsUpDownIcon,
    PhotoIcon
} from '@heroicons/vue/24/outline'
import SkeletonArticuloCard from '@/Components/SkeletonArticuloCard.vue'

const props = defineProps({ 
    articulos: Object,
    filters: Object,
    kpis: Object,
    counters: Object
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

const kpiTitle = computed(() => {
    if(activeTab.value === 'mora_critica') return 'Capital en Riesgo Crítico';
    if(activeTab.value === 'aldia') return 'Capital Activo (Al Día)';
    if(activeTab.value === 'vencido') return 'Capital Vencido';
    return 'Valor Total Inventario';
})
</script>

<template>
  <Layout title="Inventario Valuado">
    <div class="min-h-screen bg-gray-50 dark:bg-[#0f0f0f] text-gray-800 dark:text-gray-300 font-sans pb-20 selection:bg-indigo-500 selection:text-white transition-colors duration-300">
      
      <!-- STICKY HEADER -->
      <div class="bg-white/90 dark:bg-[#0f0f0f]/90 backdrop-blur-xl border-b border-gray-200 dark:border-gray-800 sticky top-0 z-30 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Top Bar -->
            <div class="py-5 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-lg shadow-indigo-500/20">
                        <TagIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Inventario & Garantías</h1>
                        <p class="text-xs text-gray-500 font-medium">Gestión de prendas</p>
                    </div>
                </div>

                <!-- Search -->
                <div class="relative w-full md:w-72 group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <MagnifyingGlassIcon class="h-4 w-4 text-gray-400 group-focus-within:text-indigo-500 transition-colors" />
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
                        <ArrowsUpDownIcon class="h-4 w-4 text-gray-400" />
                    </div>
                    <select 
                        v-model="sortOrder"
                        @change="applySort($event.target.value)"
                        class="pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 block w-full text-gray-900 dark:text-gray-200 cursor-pointer appearance-none transition-all shadow-inner font-medium"
                    >
                        <option value="mas_recientes">Más Recientes</option>
                        <option value="mas_antiguos">Más Antiguos</option>
                        <option value="mayor_precio">Mayor Valor</option>
                        <option value="menor_precio">Menor Valor</option>
                        <option value="criticos">Críticos Primero</option>
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
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">{{ kpiTitle }}</p>
                        <div class="flex items-center gap-2">
                            <CurrencyDollarIcon class="w-5 h-5 text-indigo-500 dark:text-indigo-400" />
                            <p class="text-xl font-black text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-200 transition-colors">{{ formatMoney(kpis.capital_visible) }}</p>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-gray-800 p-4 min-w-[140px] shadow-sm dark:shadow-lg flex flex-col justify-between group hover:border-indigo-500/30 transition-colors">
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Total Items</p>
                        <div class="flex items-center gap-2">
                            <TagIcon class="w-5 h-5 text-purple-500 dark:text-purple-400" />
                            <p class="text-xl font-black text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-200 transition-colors">{{ kpis.items_visibles }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex gap-1 bg-gray-50 dark:bg-[#1a1a1a] p-1 rounded-xl border border-gray-200 dark:border-gray-800 overflow-x-auto max-w-full">
                    <button @click="applyFilter('todos')" 
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2"
                        :class="activeTab === 'todos' ? 'bg-white dark:bg-indigo-600 text-indigo-600 dark:text-white shadow-sm dark:shadow-lg dark:shadow-indigo-500/25 border border-gray-200 dark:border-transparent' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5'">
                        Todos <span class="bg-gray-200 dark:bg-black/30 px-1.5 py-0.5 rounded text-[10px]">{{ counters.todos }}</span>
                    </button>
                    
                    <button @click="applyFilter('aldia')"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-all whitespace-nowrap"
                        :class="activeTab === 'aldia' ? 'bg-white dark:bg-emerald-600 text-emerald-600 dark:text-white shadow-sm dark:shadow-lg dark:shadow-emerald-500/25 border border-gray-200 dark:border-transparent' : 'text-gray-500 dark:text-gray-400 hover:text-emerald-500 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10'">
                        Al Día
                    </button>
                    
                    <button @click="applyFilter('vencido')"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-all whitespace-nowrap"
                        :class="activeTab === 'vencido' ? 'bg-white dark:bg-amber-600 text-amber-600 dark:text-white shadow-sm dark:shadow-lg dark:shadow-amber-500/25 border border-gray-200 dark:border-transparent' : 'text-gray-500 dark:text-gray-400 hover:text-amber-500 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10'">
                        Vencidos
                    </button>
                    
                    <button @click="applyFilter('mora_critica')"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2"
                        :class="activeTab === 'mora_critica' ? 'bg-white dark:bg-red-600 text-red-600 dark:text-white shadow-sm dark:shadow-lg dark:shadow-red-500/25 border border-gray-200 dark:border-transparent' : 'text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10'">
                        <ExclamationTriangleIcon v-if="activeTab !== 'mora_critica'" class="w-3 h-3" />
                        Críticos
                        <span v-if="counters.criticos > 0" class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 dark:bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500 dark:bg-white"></span>
                        </span>
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
        <div v-if="isLoading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
            <SkeletonArticuloCard v-for="n in 12" :key="n" />
        </div>

        <div v-else-if="articulos.data.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
            
            <div v-for="articulo in articulos.data" :key="articulo.id" @click="openModal(articulo)"
                class="group bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl overflow-hidden cursor-pointer hover:-translate-y-1 hover:border-indigo-500/50 hover:shadow-indigo-500/10 transition-all duration-300 relative">
                
                <!-- Badge Estado -->
                <div class="absolute top-2 right-2 z-10 flex flex-col gap-1 items-end">
                    <span v-if="articulo.es_critico" class="bg-red-500/90 backdrop-blur text-white text-[10px] font-black px-2 py-0.5 rounded-lg shadow-sm">
                        CRÍTICO
                    </span>
                    <span v-else-if="articulo.dias_mora > 30" class="bg-amber-500/90 backdrop-blur text-gray-900 text-[10px] font-black px-2 py-0.5 rounded-lg shadow-sm">
                        {{ articulo.dias_mora }} DÍAS
                    </span>
                </div>

                <!-- Image Area -->
                <div class="aspect-square bg-gray-100 dark:bg-[#141414] relative overflow-hidden">
                    <img v-if="articulo.foto_url" :src="`/storage/${articulo.foto_url}`" class="w-full h-full object-cover grayscale-[20%] group-hover:grayscale-0 group-hover:scale-110 transition-all duration-500">
                    <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400 dark:text-gray-700 gap-2 group-hover:text-gray-600 dark:group-hover:text-gray-500 transition-colors">
                        <TagIcon class="w-8 h-8 opacity-20" />
                        <span class="text-[10px] font-bold uppercase tracking-widest opacity-40">Sin Foto</span>
                    </div>
                    
                    <!-- Gradient Overlay on Hover -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 dark:from-black/80 via-transparent to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
                    
                    <!-- Price Tag -->
                    <div class="absolute bottom-2 left-2 right-2 flex justify-center">
                        <div class="bg-white/90 dark:bg-black/60 backdrop-blur-md px-3 py-1 rounded-full border border-gray-200 dark:border-white/10 shadow-lg">
                            <p class="text-gray-900 dark:text-white text-xs font-bold tracking-wide">{{ formatMoney(articulo.valor_proporcional) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Info Area -->
                <div class="p-3">
                    <h3 class="text-xs font-bold text-gray-900 dark:text-white mb-1 truncate">{{ articulo.nombre }}</h3>
                    <div class="flex items-center gap-1.5 mb-2">
                        <UserIcon class="w-3 h-3 text-gray-500" />
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ articulo.cliente.nombre }}</p>
                    </div>
                    
                    <!-- Time Badge -->
                    <div class="bg-gray-50 dark:bg-[#141414] rounded-lg px-2 py-1.5 border border-gray-100 dark:border-gray-800 flex items-center justify-between">
                         <div class="flex items-center gap-1.5">
                             <ClockIcon class="w-3 h-3" :class="articulo.es_critico ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-gray-500'" />
                             <span class="text-[10px] font-bold" :class="articulo.es_critico ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400'">
                                {{ articulo.tiempo_texto }}
                             </span>
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-20 text-gray-500 bg-white dark:bg-[#1a1a1a] rounded-3xl border border-gray-200 dark:border-gray-800 border-dashed">
            <ArchiveBoxIcon class="w-16 h-16 text-gray-300 dark:text-gray-700 mb-4" />
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
      
      <!-- MODERN MODAL -->
      <Modal :show="isModalOpen" @close="closeModal">
          <div v-if="selectedArticulo" class="bg-white dark:bg-[#1a1a1a] text-gray-900 dark:text-gray-200 rounded-2xl overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-700">
              
              <!-- Modal Header Image -->
              <div class="relative bg-gray-100 dark:bg-black flex items-center justify-center transition-all duration-300"
                   :class="isZoomed ? 'fixed inset-0 z-50 h-screen bg-black/95' : 'h-72'">
                   
                  <img v-if="selectedArticulo.foto_url" 
                       :src="`/storage/${selectedArticulo.foto_url}`" 
                       class="object-contain transition-all duration-500"
                       :class="isZoomed ? 'h-[90vh] w-auto cursor-zoom-out' : 'h-full w-full cursor-zoom-in'"
                       @click.stop="isZoomed = !isZoomed">
                       
                  <div v-else class="flex flex-col items-center justify-center gap-3">
                      <PhotoIcon class="w-16 h-16 text-gray-300 dark:text-gray-800" />
                      <span class="text-gray-400 dark:text-gray-600 font-bold uppercase tracking-widest text-sm">Sin Imagen</span>
                  </div>
                  
                  <button v-if="!isZoomed" @click="closeModal" class="absolute top-4 right-4 bg-white/50 dark:bg-black/50 hover:bg-white/80 dark:hover:bg-black/80 backdrop-blur text-gray-900 dark:text-white rounded-full p-2 transition-colors border border-black/5 dark:border-white/10 shadow-sm z-20">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                  </button>

                  <div v-if="!isZoomed" class="absolute bottom-4 left-4 bg-white/80 dark:bg-black/60 backdrop-blur px-3 py-1 rounded-lg border border-black/5 dark:border-white/10 shadow-sm">
                      <p class="text-xs font-mono text-indigo-600 dark:text-indigo-300">ID: #{{ selectedArticulo.id }}</p>
                  </div>
                  
                  <div v-if="isZoomed" class="absolute top-4 right-4 z-50">
                        <button @click.stop="isZoomed = false" class="text-white hover:text-gray-300">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                   </div>
              </div>

              <!-- Modal Content -->
              <div class="p-8">
                  <div class="flex justify-between items-start mb-6">
                      <div>
                          <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ selectedArticulo.nombre }}</h2>
                           <p class="text-sm text-gray-500">{{ selectedArticulo.descripcion }}</p>
                      </div>
                      <div class="text-right">
                          <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Valor Asignado</p>
                          <p class="text-2xl font-black text-gray-900 dark:text-white">{{ formatMoney(selectedArticulo.valor_proporcional) }}</p>
                      </div>
                  </div>

                  <!-- Grid Info -->
                  <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-[#141414] p-5 rounded-2xl border border-gray-100 dark:border-gray-800 mb-8">
                      <div class="space-y-1">
                          <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Estado Temporal</span>
                          <div class="flex items-center gap-2">
                              <span class="text-lg font-bold" :class="selectedArticulo.es_critico ? 'text-red-500' : 'text-emerald-500'">
                                  {{ selectedArticulo.tiempo_texto }}
                              </span>
                              <ExclamationTriangleIcon v-if="selectedArticulo.es_critico" class="w-5 h-5 text-red-500 animate-pulse" />
                          </div>
                      </div>
                      
                      <div class="space-y-1">
                          <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Desde</span>
                          <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ selectedArticulo.origen_calculo }}</span>
                      </div>

                      <div class="space-y-1 border-t border-gray-200 dark:border-gray-800 pt-3 mt-1">
                          <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Préstamo Total</span>
                          <Link :href="route('clientes.detalle', selectedArticulo.cliente.id)" class="block text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 underline underline-offset-2">
                              Código: {{ selectedArticulo.prestamo.codigo }}
                          </Link>
                      </div>

                      <div class="space-y-1 border-t border-gray-200 dark:border-gray-800 pt-3 mt-1">
                           <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Propietario</span>
                          <span class="block text-sm font-bold text-gray-700 dark:text-gray-300">{{ selectedArticulo.cliente.nombre }}</span>
                      </div>
                  </div>

                  <!-- Actions -->
                  <div class="flex gap-3">
                      <button @click="closeModal" class="flex-1 py-3.5 border border-gray-300 dark:border-gray-700 bg-transparent text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm">
                          Cerrar Vista
                      </button>
                      <Link :href="route('clientes.detalle', selectedArticulo.cliente.id)" class="flex-[2] py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-center hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-500/25 flex items-center justify-center gap-2 transition-all transform hover:scale-[1.02] text-sm">
                          <DocumentTextIcon class="w-5 h-5" />
                          <span>Ver Contrato y Gestionar</span>
                      </Link>
                  </div>
              </div>
          </div>
      </Modal>

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