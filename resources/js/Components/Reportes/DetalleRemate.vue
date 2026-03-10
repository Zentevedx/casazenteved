<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { PrinterIcon, ExclamationTriangleIcon, ChevronUpIcon, ChevronDownIcon, TableCellsIcon } from '@heroicons/vue/24/outline'
import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
dayjs.extend(utc)

const props = defineProps({
    items: { type: Array, default: () => [] },
    filters: Object,
})

const emit = defineEmits(['download-pdf'])

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val ?? 0)

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

// Color de urgencia según días sin pago
const urgencyColor = (dias) => {
    if (dias >= 180) return 'bg-red-900/30 text-red-300 border border-red-800'   // > 6 meses
    if (dias >= 90)  return 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400' // 3-6 meses
    return 'bg-orange-100 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400'
}

const timeLabel = (dias, meses) => {
    if (!dias && dias !== 0) return '—'
    if (meses >= 12) {
        const anios = Math.floor(meses / 12)
        const mesesR = meses % 12
        return mesesR > 0 ? `${anios}a ${mesesR}m` : `${anios} año(s)`
    }
    if (meses > 0) return `${meses} mes(es) y ${dias - meses * 30} día(s)`
    return `${dias} días`
}

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
                <p class="text-lg font-black text-red-600 dark:text-red-400 mt-0.5">{{ formatCurrency(totalCapital) }}</p>
            </div>
            <div class="py-3 px-4 border-r border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-400 uppercase font-bold">Mayor monto</p>
                <p class="text-lg font-black text-gray-900 dark:text-white mt-0.5">{{ formatCurrency(mayorMonto) }}</p>
            </div>
            <div class="py-3 px-4">
                <p class="text-xs text-gray-400 uppercase font-bold">Más tiempo inactivo</p>
                <p class="text-lg font-black text-orange-600 dark:text-orange-400 mt-0.5">{{ maxDias }} días</p>
            </div>
        </div>

        <!-- Estado vacío -->
        <div v-if="items.length === 0" class="py-16 text-center">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 font-medium">¡Excelente! No hay préstamos en situación de remate.</p>
            <p class="text-xs text-gray-400 mt-1">Todos los clientes están al día.</p>
        </div>

        <!-- Tabla -->
        <div v-else class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-bold">
                    <tr>
                        <th class="px-5 py-3">#</th>
                        <th class="px-5 py-3">Código</th>
                        <th class="px-5 py-3 cursor-pointer hover:text-indigo-500 select-none" @click="toggleSort('cliente')">
                            <span class="flex items-center gap-1">Cliente <component :is="SortIcon('cliente')" v-if="SortIcon('cliente')" class="w-3 h-3" /></span>
                        </th>
                        <th class="px-5 py-3">Artículo(s)</th>
                        <th class="px-5 py-3 cursor-pointer hover:text-indigo-500 select-none" @click="toggleSort('fecha_prestamo')">
                            <span class="flex items-center gap-1">Fecha Inicio <component :is="SortIcon('fecha_prestamo')" v-if="SortIcon('fecha_prestamo')" class="w-3 h-3" /></span>
                        </th>
                        <th class="px-5 py-3">Últ. Pago</th>
                        <th class="px-5 py-3 cursor-pointer hover:text-indigo-500 select-none" @click="toggleSort('dias_sin_pago')">
                            <span class="flex items-center gap-1">Tiempo inactivo <component :is="SortIcon('dias_sin_pago')" v-if="SortIcon('dias_sin_pago')" class="w-3 h-3" /></span>
                        </th>
                        <th class="px-5 py-3 cursor-pointer hover:text-indigo-500 select-none text-right" @click="toggleSort('saldo_a_fecha')">
                            <span class="flex items-center justify-end gap-1">Capital <component :is="SortIcon('saldo_a_fecha')" v-if="SortIcon('saldo_a_fecha')" class="w-3 h-3" /></span>
                        </th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr
                        v-for="(p, index) in sortedItems"
                        :key="p.id"
                        class="hover:bg-red-50/30 dark:hover:bg-red-900/10 transition-colors border-l-4 border-l-transparent hover:border-l-red-400"
                    >
                        <td class="px-5 py-4 text-xs text-gray-400 font-mono">{{ index + 1 }}</td>
                        <td class="px-5 py-4 font-bold text-indigo-600 dark:text-indigo-400 font-mono text-sm">{{ p.codigo }}</td>
                        <td class="px-5 py-4 text-gray-800 dark:text-gray-200 font-medium">{{ p.cliente?.nombre }}</td>
                        <td class="px-5 py-4 text-gray-500 dark:text-gray-400 text-sm max-w-[180px] truncate">
                            {{ p.articulos?.map(a => a.nombre).join(', ') || 'Sin artículo' }}
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ dayjs(p.fecha_prestamo).utc().format('DD/MM/YYYY') }}
                        </td>
                        <td class="px-5 py-4 text-sm whitespace-nowrap">
                            <span v-if="p.fecha_ultimo_pago" class="text-gray-500 dark:text-gray-400">
                                {{ dayjs(p.fecha_ultimo_pago).utc().format('DD/MM/YYYY') }}
                            </span>
                            <span v-else class="text-red-500 font-bold text-xs">NINGUNO</span>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['px-2.5 py-1 rounded-full text-xs font-black whitespace-nowrap', urgencyColor(p.dias_sin_pago ?? 0)]">
                                {{ timeLabel(p.dias_sin_pago, p.meses_sin_pago) }}
                            </span>
                            <p class="text-xs text-gray-400 mt-0.5 pl-1">{{ p.dias_sin_pago ?? '—' }} días exactos</p>
                        </td>
                        <td class="px-5 py-4 text-right font-black text-red-600 dark:text-red-400 whitespace-nowrap">
                            {{ formatCurrency(p.saldo_a_fecha ?? p.monto) }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <Link
                                v-if="p.cliente_id_ref"
                                :href="route('clientes.detalle', p.cliente_id_ref)"
                                class="text-indigo-600 dark:text-indigo-400 text-sm font-bold hover:underline whitespace-nowrap"
                            >
                                Ver →
                            </Link>
                        </td>
                    </tr>
                </tbody>
                <!-- Footer con total -->
                <tfoot class="bg-red-50 dark:bg-red-900/20 font-bold text-sm border-t-2 border-red-200 dark:border-red-800">
                    <tr>
                        <td colspan="7" class="px-5 py-3 text-red-700 dark:text-red-300 uppercase text-xs tracking-wider">
                            TOTAL EN RIESGO ({{ items.length }} préstamos)
                        </td>
                        <td class="px-5 py-3 text-right text-red-700 dark:text-red-300 text-base font-black">
                            {{ formatCurrency(totalCapital) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>
