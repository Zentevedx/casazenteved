<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { PrinterIcon, ExclamationTriangleIcon, ChevronUpIcon, ChevronDownIcon, TableCellsIcon } from '@heroicons/vue/24/outline'
import { useFormatters } from '@/Composables/useFormatters'
import EmptyState from '@/Components/UI/EmptyState.vue'

const { formatCurrency, formatTimeLabel: timeLabel, urgencyColor, dayjs } = useFormatters()

const props = defineProps({
    items: { type: Array, default: () => [] },
    filters: Object,
})

const emit = defineEmits(['download-pdf'])

// Using formatCurrency from useFormatters

// --- Sorting ---
const sortKey   = ref('dias_sin_pago')   // default: más tiempo sin pago primero
const sortOrder = ref('desc')

const toggleSort = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value   = key
        sortOrder.value = 'desc'
    }
}

const sortedItems = computed(() => {
    if (!props.items?.length) return []
    return [...props.items].sort((a, b) => {
        let vA, vB
        if (sortKey.value === 'dias_sin_pago')       { vA = a.dias_sin_pago;     vB = b.dias_sin_pago }
        else if (sortKey.value === 'saldo_a_fecha')  { vA = a.saldo_a_fecha;     vB = b.saldo_a_fecha }
        else if (sortKey.value === 'monto')          { vA = a.monto;             vB = b.monto }
        else if (sortKey.value === 'fecha_prestamo') { vA = new Date(a.fecha_prestamo); vB = new Date(b.fecha_prestamo) }
        else if (sortKey.value === 'cliente')        { vA = a.cliente?.nombre?.toLowerCase(); vB = b.cliente?.nombre?.toLowerCase() }
        else                                         { vA = a[sortKey.value];    vB = b[sortKey.value] }
        if (vA < vB) return sortOrder.value === 'asc' ? -1 : 1
        if (vA > vB) return sortOrder.value === 'asc' ? 1 : -1
        return 0
    })
})

// --- Totales ---
const totalCapital = computed(() => props.items.reduce((s, i) => s + Number(i.saldo_a_fecha ?? i.monto), 0))
const mayorMonto   = computed(() => props.items.length ? Math.max(...props.items.map(i => i.saldo_a_fecha ?? i.monto)) : 0)
const maxDias      = computed(() => props.items.length ? Math.max(...props.items.map(i => i.dias_sin_pago ?? 0)) : 0)

// Using urgencyColor and timeLabel from useFormatters

const SortIcon = (key) => sortKey.value === key
    ? (sortOrder.value === 'asc' ? ChevronUpIcon : ChevronDownIcon)
    : null

const downloadPdf = () => {
    window.open(route('reportes.financiero.pdf', { ...props.filters, tipo: 'remate' }), '_blank')
}

const downloadExcel = () => {
    window.open(route('reportes.financiero.excel', { ...props.filters, tipo: 'remate' }), '_blank')
}
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">

        <!-- Header -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-500/20 flex items-center justify-center shadow-md">
                    <ExclamationTriangleIcon class="w-5 h-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">Préstamos en Remate</h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ items.length }} préstamo(s) · sin movimiento ≥ 3 meses ·
                        <span class="font-bold text-indigo-500">orden: {{ sortKey }} {{ sortOrder === 'desc' ? '↓' : '↑' }}</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button @click="downloadExcel" class="inline-flex items-center px-4 py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm font-bold hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors">
                    <TableCellsIcon class="w-4 h-4 mr-2" /> Exportar Excel
                </button>
                <button @click="downloadPdf" class="inline-flex items-center px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-xl text-sm font-bold hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                    <PrinterIcon class="w-4 h-4 mr-2" /> Imprimir PDF
                </button>
            </div>
        </div>

        <!-- Resumen rápido -->
        <div class="grid grid-cols-3 gap-0 border-b border-gray-100 dark:border-gray-800 text-center">
            <div class="py-3 px-4 border-r border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-400 uppercase font-bold">Capital en riesgo</p>
                <p class="font-ufc text-lg font-black text-red-600 dark:text-red-400 mt-0.5">{{ formatCurrency(totalCapital) }}</p>
            </div>
            <div class="py-3 px-4 border-r border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-400 uppercase font-bold">Mayor monto</p>
                <p class="font-ufc text-lg font-black text-gray-900 dark:text-white mt-0.5">{{ formatCurrency(mayorMonto) }}</p>
            </div>
            <div class="py-3 px-4">
                <p class="text-xs text-gray-400 uppercase font-bold">Más tiempo inactivo</p>
                <p class="font-ufc text-lg font-black text-orange-600 dark:text-orange-400 mt-0.5">{{ maxDias }} días</p>
            </div>
        </div>

        <!-- Estado vacío -->
        <EmptyState 
            v-if="items.length === 0" 
            title="¡Excelente! No hay préstamos en situación de remate." 
            description="Todos los clientes están al día." 
            iconType="success" 
        />

        <!-- Layout en Tarjetas (Columnas) -->
        <div v-else class="p-4 bg-gray-50/50 dark:bg-[#141414] border-t border-gray-100 dark:border-gray-800">
            <!-- Barra de Ordenación Móvil/Desktop alternativa -->
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="text-xs text-gray-500 font-bold uppercase mr-2">Ordenar por:</span>
                <button @click="toggleSort('cliente')" :class="['px-3 py-1.5 rounded-lg text-xs font-bold border transition', sortKey === 'cliente' ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-400' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 dark:bg-[#1a1a1a] dark:border-gray-800 dark:text-gray-400']">
                    Cliente <component :is="SortIcon('cliente')" v-if="SortIcon('cliente')" class="w-3 h-3 inline" />
                </button>
                <button @click="toggleSort('dias_sin_pago')" :class="['px-3 py-1.5 rounded-lg text-xs font-bold border transition', sortKey === 'dias_sin_pago' ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-400' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 dark:bg-[#1a1a1a] dark:border-gray-800 dark:text-gray-400']">
                    Tiempo <component :is="SortIcon('dias_sin_pago')" v-if="SortIcon('dias_sin_pago')" class="w-3 h-3 inline" />
                </button>
                <button @click="toggleSort('saldo_a_fecha')" :class="['px-3 py-1.5 rounded-lg text-xs font-bold border transition', sortKey === 'saldo_a_fecha' ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-400' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 dark:bg-[#1a1a1a] dark:border-gray-800 dark:text-gray-400']">
                    Capital <component :is="SortIcon('saldo_a_fecha')" v-if="SortIcon('saldo_a_fecha')" class="w-3 h-3 inline" />
                </button>
            </div>

            <!-- Grilla 1 (Movil) -> 2 (Tablet) -> 3 (Desktop) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="(p, index) in sortedItems"
                    :key="p.id"
                    class="bg-white dark:bg-[#1c1c1c] rounded-2xl p-5 border border-red-100 dark:border-red-900/30 shadow-sm hover:shadow-lg transition-all flex flex-col relative overflow-hidden group"
                >
                    <!-- Red Warning Banner at Top -->
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-600"></div>

                    <!-- Header Tarjeta -->
                    <div class="flex justify-between items-start mb-3 mt-1">
                        <div>
                            <span class="font-ufc text-xs text-indigo-600 dark:text-indigo-400 tracking-wider block mb-1">
                                #{{ index + 1 }} · {{ p.codigo }}
                            </span>
                            <h4 class="font-bold text-gray-900 dark:text-white leading-tight">
                                {{ p.cliente?.nombre }}
                            </h4>
                        </div>
                        <div class="text-right shrink-0">
                            <span :class="['inline-block px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider', urgencyColor(p.dias_sin_pago ?? 0)]">
                                {{ timeLabel(p.dias_sin_pago, p.meses_sin_pago) }}
                            </span>
                        </div>
                    </div>

                    <!-- Articulo -->
                    <div class="bg-gray-50 dark:bg-[#141414] rounded-xl p-3 mb-4 flex-1 border border-gray-100 dark:border-gray-800">
                        <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">En Garantía</p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ p.articulos?.map(a => a.nombre_articulo + (a.descripcion ? ` (${a.descripcion})` : '')).join(' • ') || 'Sin artículo' }}
                        </p>
                    </div>

                    <!-- Fechas y Saldos -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold">F. Inicio</p>
                            <p class="font-ufc text-xs text-gray-600 dark:text-gray-400">{{ dayjs(p.fecha_prestamo).utc().format('DD/MM/YYYY') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Último Pago</p>
                            <p v-if="p.fecha_ultimo_pago" class="font-ufc text-xs text-gray-600 dark:text-gray-400">{{ dayjs(p.fecha_ultimo_pago).utc().format('DD/MM/YYYY') }}</p>
                            <p v-else class="text-xs font-bold text-red-500">NINGUNO</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Capital Adeudado</p>
                            <p class="font-ufc text-lg font-black text-red-600 dark:text-red-400 block -mt-1">
                                {{ formatCurrency(p.saldo_a_fecha ?? p.monto) }}
                            </p>
                        </div>
                        <Link
                            v-if="p.cliente_id_ref"
                            :href="route('clientes.detalle', p.cliente_id_ref)"
                            class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 transition-colors flex items-center justify-center shrink-0"
                            title="Ver Perfil"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Total Footer en Grilla -->
            <div class="mt-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 flex justify-between items-center flex-wrap gap-4">
                <span class="text-red-700 dark:text-red-300 uppercase text-xs font-black tracking-wider">
                    TOTAL EN RIESGO ({{ items.length }} préstamos)
                </span>
                <span class="font-ufc text-red-700 dark:text-red-300 text-xl font-black">
                    {{ formatCurrency(totalCapital) }}
                </span>
            </div>
        </div>
    </div>
</template>
