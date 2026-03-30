<script setup>
/**
 * TableroCobranza.vue — Panel overlay del tablero de cobranza.
 * Muestra grilla horizontal de 4 semanas × 7 días + lista de recuperación.
 */
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import SemanaBlock from './SemanaBlock.vue'
import ListaRecuperacion from './ListaRecuperacion.vue'
import ClienteCard from './ClienteCard.vue'
import { useFormatters } from '@/Composables/useFormatters'

const { formatCurrency, formatMonto } = useFormatters()

const emit = defineEmits(['close'])

const loading = ref(true)
const error = ref(null)

const bloquesSemana = ref([])
const vencidosAntes = ref([])
const listaRecuperacion = ref([])
const kpis = ref({})
const contadores = ref({})
const fechaHoy = ref('')
const mostrarVencidos = ref(true)


const cargarDatos = async () => {
    loading.value = true
    error.value = null
    try {
        const { data } = await axios.get('/api/vencimientos')
        bloquesSemana.value = data.bloquesSemana
        vencidosAntes.value = data.vencidosAntes
        listaRecuperacion.value = data.listaRecuperacion
        kpis.value = data.kpis
        contadores.value = data.contadores
        fechaHoy.value = data.fechaHoy
    } catch (e) {
        error.value = 'Error al cargar datos de cobranza'
        console.error(e)
    } finally {
        loading.value = false
    }
}

onMounted(cargarDatos)

const nivelesConfig = [
    { key: 'verde',    label: 'Estable',     dotColor: 'bg-emerald-500', badgeBg: 'bg-emerald-100 dark:bg-emerald-500/20', badgeText: 'text-emerald-700 dark:text-emerald-300' },
    { key: 'amarillo', label: 'Advertencia', dotColor: 'bg-yellow-400',  badgeBg: 'bg-yellow-100 dark:bg-yellow-500/20',  badgeText: 'text-yellow-700 dark:text-yellow-300' },
    { key: 'rojo',     label: 'Crítico',     dotColor: 'bg-red-500',     badgeBg: 'bg-red-100 dark:bg-red-500/20',        badgeText: 'text-red-700 dark:text-red-300' },
    { key: 'azul',     label: 'Por vender',  dotColor: 'bg-blue-500',    badgeBg: 'bg-blue-100 dark:bg-blue-500/20',      badgeText: 'text-blue-700 dark:text-blue-300' },
    { key: 'remate',   label: 'Remate',      dotColor: 'bg-gray-600',    badgeBg: 'bg-gray-200 dark:bg-gray-700',         badgeText: 'text-gray-700 dark:text-gray-300' },
]
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-[100] flex items-start justify-center">
            <!-- Backdrop -->
            <div 
                class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                @click="emit('close')"
            ></div>

            <!-- Panel -->
            <div class="relative w-full max-w-[1600px] mx-4 mt-4 mb-4 max-h-[calc(100vh-32px)] bg-gray-50 dark:bg-[#0f0f13] rounded-3xl shadow-2xl overflow-hidden flex flex-col animate-slide-up">
                
                <!-- Header -->
                <div class="sticky top-0 z-10 bg-white/95 dark:bg-[#1a1a1a]/95 backdrop-blur-xl border-b border-gray-200 dark:border-gray-800 px-5 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/20 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-base font-black text-gray-900 dark:text-white tracking-tight">
                                        Tablero de Cobranza
                                    </h2>
                                    <!-- Color counter badges al lado del título -->
                                     <div v-if="!loading" class="flex items-center gap-1.5">
                                        <span 
                                            v-for="nivel in nivelesConfig" 
                                            :key="nivel.key"
                                            v-show="(contadores[nivel.key]?.count ?? 0) > 0"
                                            :class="['inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-black', nivel.badgeBg, nivel.badgeText]"
                                            :title="nivel.label"
                                        >
                                            <span :class="['w-1.5 h-1.5 rounded-full', nivel.dotColor]"></span>
                                            <span class="font-ufc">{{ contadores[nivel.key]?.count ?? 0 }}</span>
                                        </span>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                    Hoy + 4 semanas · <span class="font-ufc text-xs">{{ fechaHoy }}</span> · <span class="font-ufc text-xs text-red-500 dark:text-red-400">{{ formatCurrency(kpis.total_por_cobrar) }}</span> por cobrar
                                </p>
                            </div>
                        </div>

                        <button 
                            @click="emit('close')"
                            class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center justify-center transition-colors flex-shrink-0"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Contenido scrollable -->
                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                    
                    <!-- Loading -->
                    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
                        <div class="w-10 h-10 border-4 border-gray-200 dark:border-gray-700 border-t-indigo-500 rounded-full animate-spin mb-3"></div>
                        <p class="text-xs text-gray-500 font-medium">Cargando tablero...</p>
                    </div>

                    <!-- Error -->
                    <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
                        <p class="text-sm text-red-500 font-bold">{{ error }}</p>
                        <button @click="cargarDatos" class="mt-3 text-xs text-indigo-500 hover:underline">Reintentar</button>
                    </div>

                    <!-- Contenido principal -->
                    <template v-else>

                        <!-- Cobros Vencidos (ya pasaron su fecha) -->
                        <div v-if="vencidosAntes.length > 0" class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-amber-200 dark:border-amber-900/40 shadow-sm overflow-hidden">
                            <button 
                                @click="mostrarVencidos = !mostrarVencidos"
                                class="w-full px-4 py-2.5 flex items-center justify-between bg-amber-50/50 dark:bg-amber-500/5 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors"
                            >
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">Cobros Pendientes (ya vencidos)</span>
                                    <span class="font-ufc bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 text-[10px] font-black px-2 py-0.5 rounded-full">
                                        {{ vencidosAntes.length }}
                                    </span>
                                </div>
                                <svg :class="['h-4 w-4 text-gray-400 transition-transform', mostrarVencidos ? 'rotate-180' : '']" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div v-show="mostrarVencidos" class="p-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-1.5">
                                <ClienteCard
                                    v-for="p in vencidosAntes"
                                    :key="p.id"
                                    :prestamo="p"
                                />
                            </div>
                        </div>

                        <!-- 4 Semanas horizontales (stacked) -->
                        <SemanaBlock
                            v-for="bloque in bloquesSemana"
                            :key="bloque.semana"
                            :bloque="bloque"
                        />

                        <!-- Lista de Recuperación -->
                        <ListaRecuperacion :prestamos="listaRecuperacion" />
                    </template>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
@keyframes slide-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up {
    animation: slide-up 0.3s ease-out;
}
</style>
