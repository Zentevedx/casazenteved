<script setup>
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({ 
    articulos: Object,
    filters: Object,
    kpis: Object,
    counters: Object
})

const search = ref(props.filters.search || '')
const activeTab = ref(props.filters.estado || 'todos')
const isModalOpen = ref(false)
const selectedArticulo = ref(null)

const openModal = (articulo) => {
    selectedArticulo.value = articulo
    isModalOpen.value = true
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
    router.get(route('articulos.index'), { estado: estado, search: search.value }, { preserveState: true, preserveScroll: true })
}

watch(search, debounce((value) => {
    router.get(route('articulos.index'), { estado: activeTab.value, search: value }, { preserveState: true, preserveScroll: true })
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
    <div class="min-h-screen bg-gray-50 pb-12">
      
      <div class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-4 flex flex-col md:flex-row justify-between gap-4">
                <h1 class="text-xl font-bold text-gray-900">Inventario y Garantías</h1>
                <input v-model="search" type="text" placeholder="Buscar..." class="text-sm border-gray-300 rounded-md focus:border-orange-500 focus:ring-orange-500 w-full md:w-64">
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4 border-t border-gray-100 pt-4">
                <div class="border-l-4 pl-3" :class="activeTab === 'mora_critica' ? 'border-red-600' : 'border-gray-800'">
                    <p class="text-xs text-gray-500 uppercase">{{ kpiTitle }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ formatMoney(kpis.capital_visible) }}</p>
                </div>
                <div class="border-l-4 border-gray-300 pl-3">
                    <p class="text-xs text-gray-500 uppercase">Items Listados</p>
                    <p class="text-2xl font-bold text-gray-900">{{ kpis.items_visibles }}</p>
                </div>
            </div>

            <div class="flex space-x-6 overflow-x-auto">
                <button @click="applyFilter('todos')" class="pb-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap" :class="activeTab === 'todos' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500'">
                    Todos <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full text-gray-600">{{ counters.todos }}</span>
                </button>
                <button @click="applyFilter('aldia')" class="pb-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap" :class="activeTab === 'aldia' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500'">
                    Al Día
                </button>
                <button @click="applyFilter('vencido')" class="pb-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap" :class="activeTab === 'vencido' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500'">
                    Vencidos
                </button>
                <button @click="applyFilter('mora_critica')" class="pb-3 text-sm font-bold border-b-2 transition-colors whitespace-nowrap flex items-center" :class="activeTab === 'mora_critica' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500'">
                    Críticos (>3 Meses)
                    <span v-if="counters.criticos > 0" class="ml-2 flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                </button>
            </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div v-if="articulos.data.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            
            <div v-for="articulo in articulos.data" :key="articulo.id" @click="openModal(articulo)"
                class="bg-white rounded border border-gray-200 shadow-sm hover:shadow-lg hover:border-orange-300 cursor-pointer transition-all overflow-hidden relative group">
                
                <div v-if="articulo.es_critico" class="absolute top-0 right-0 z-10">
                     <span class="bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-bl">CRÍTICO</span>
                </div>

                <div class="h-28 bg-gray-100 overflow-hidden relative">
                    <img v-if="articulo.foto_url" :src="articulo.foto_url" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-black/60 px-2 py-0.5">
                        <p class="text-white text-xs font-bold text-center">{{ formatMoney(articulo.valor_proporcional) }}</p>
                    </div>
                </div>

                <div class="p-2">
                    <h3 class="text-xs font-bold text-gray-800 truncate">{{ articulo.nombre }}</h3>
                    <p class="text-[10px] text-gray-500 mt-1 truncate">{{ articulo.cliente.nombre }}</p>
                    
                    <div class="mt-1 flex justify-between items-center">
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium truncate max-w-full"
                            :class="articulo.es_critico ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-600'">
                            {{ articulo.tiempo_texto }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-12 text-gray-500">No hay artículos aquí.</div>

        <div v-if="articulos.links && articulos.data.length > 0" class="mt-6 flex justify-center pb-8">
             <div class="flex gap-1">
                <Link v-for="(link, k) in articulos.links" :key="k" :href="link.url || '#'" v-html="link.label"
                    class="px-3 py-1 text-xs border rounded" :class="{ 'bg-orange-500 text-white border-orange-500': link.active, 'bg-white text-gray-600': !link.active && link.url }" />
             </div>
        </div>
      </div>
      
      <Modal :show="isModalOpen" @close="closeModal">
          <div v-if="selectedArticulo" class="bg-white rounded-lg overflow-hidden">
              <div class="relative h-64 bg-black flex items-center justify-center">
                  <img v-if="selectedArticulo.foto_url" :src="selectedArticulo.foto_url" class="h-full object-contain">
                  <span v-else class="text-gray-500">Sin Foto</span>
                  <button @click="closeModal" class="absolute top-2 right-2 bg-black/50 text-white rounded-full p-1 hover:bg-black/80">✕</button>
              </div>

              <div class="p-6">
                  <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ selectedArticulo.nombre }}</h2>
                  <p class="text-gray-600 mb-6">{{ selectedArticulo.descripcion }}</p>

                  <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100 mb-6">
                      <div>
                          <span class="block text-xs text-gray-500 uppercase">Valor Pieza</span>
                          <span class="block text-lg font-bold text-gray-900">{{ formatMoney(selectedArticulo.valor_proporcional) }}</span>
                      </div>
                      <div>
                          <span class="block text-xs text-gray-500 uppercase">Tiempo sin pagar</span>
                          <span class="block text-lg font-bold" :class="selectedArticulo.es_critico ? 'text-red-600' : 'text-gray-900'">
                              {{ selectedArticulo.tiempo_texto }}
                          </span>
                          <span class="text-[10px] text-gray-400">Desde: {{ selectedArticulo.origen_calculo }}</span>
                      </div>
                      <div>
                          <span class="block text-xs text-gray-500 uppercase">Préstamo Total</span>
                          <span class="block text-sm font-medium text-gray-800">{{ formatMoney(selectedArticulo.prestamo.monto_total) }}</span>
                      </div>
                      <div>
                          <span class="block text-xs text-gray-500 uppercase">Cliente</span>
                          <span class="block text-sm font-medium text-gray-800">{{ selectedArticulo.cliente.nombre }}</span>
                      </div>
                  </div>

                  <div class="flex gap-3">
                      <button @click="closeModal" class="w-1/3 py-3 border border-gray-300 rounded text-gray-700 font-semibold hover:bg-gray-50">Cerrar</button>
                      <Link :href="route('clientes.detalle', selectedArticulo.cliente.id)" class="w-2/3 py-3 bg-orange-600 text-white rounded font-bold text-center hover:bg-orange-700 shadow-lg flex items-center justify-center gap-2">
                          <span>Gestionar / Rematar</span>
                      </Link>
                  </div>
              </div>
          </div>
      </Modal>

    </div>
  </Layout>
</template>