<script setup>
import { ref, computed } from 'vue'
import { PrinterIcon, ChevronUpIcon, ChevronDownIcon, ReceiptPercentIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'
import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
dayjs.extend(utc)

const props = defineProps({
    items: Array,
    filters: Object,
    type: { type: String, default: 'intereses' }
})

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val)

// --- Sorting ---
const sortKey   = ref('monto_pagado')
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
        if (sortKey.value === 'monto_pagado')  { vA = a.monto_pagado;  vB = b.monto_pagado }
        else if (sortKey.value === 'fecha')    { vA = new Date(a.fecha_pago); vB = new Date(b.fecha_pago) }
        else if (sortKey.value === 'cliente')  { vA = a.prestamo?.cliente?.nombre?.toLowerCase(); vB = b.prestamo?.cliente?.nombre?.toLowerCase() }
        else                                   { vA = a[sortKey.value]; vB = b[sortKey.value] }
        if (vA < vB) return sortOrder.value === 'asc' ? -1 : 1
        if (vA > vB) return sortOrder.value === 'asc' ? 1 : -1
        return 0
    })
})

// --- Totales ---
const montoField = 'monto_pagado'
const total    = computed(() => props.items?.reduce((s, i) => s + Number(i[montoField]), 0) ?? 0)
const mayor    = computed(() => props.items?.length ? Math.max(...props.items.map(i => i[montoField])) : 0)
const promedio = computed(() => props.items?.length ? total.value / props.items.length : 0)

const title    = computed(() => props.type === 'intereses' ? 'Detalle de Intereses Cobrados' : 'Detalle de Capital Recuperado')
const subtitle = computed(() => props.type === 'intereses' ? 'Ingresos por concepto de intereses del período' : 'Ingresos por devolución de capital prestado')
const icon     = computed(() => props.type === 'intereses' ? ReceiptPercentIcon : ArrowPathIcon)
const iconColor= computed(() => props.type === 'intereses' ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400')

const downloadPdf = () => {
    window.open(route('reportes.financiero.pdf', { ...props.filters, tipo: props.type }), '_blank')
}

const SortIcon = (key) => sortKey.value === key
    ? (sortOrder.value === 'asc' ? ChevronUpIcon : ChevronDownIcon)
    : null
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">

        <!-- Header -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-3">
                <div :class="['w-9 h-9 rounded-xl flex items-center justify-center', iconColor]">
                    <component :is="icon" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">{{ title }}</h3>
                    <p class="text-xs text-gray-400">{{ items?.length ?? 0 }} registros · <span class="font-bold text-indigo-500">{{ sortKey }} {{ sortOrder === 'desc' ? '↓' : '↑' }}</span></p>
                </div>
            </div>
            <button @click="downloadPdf" class="inline-flex items-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm font-bold hover:bg-indigo-100 transition-colors">
                <PrinterIcon class="w-4 h-4 mr-2" /> Imprimir Lista
            </button>
        </div>

        <!-- Resumen rápido -->
        <div class="grid grid-cols-3 gap-0 border-b border-gray-100 dark:border-gray-800 text-center">
            <div class="py-3 px-4 border-r border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-400 uppercase font-bold">Total cobrado</p>
                <p class="text-base font-black text-gray-900 dark:text-white mt-0.5">{{ formatCurrency(total) }}</p>
            </div>
            <div class="py-3 px-4 border-r border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-400 uppercase font-bold">Mayor pago</p>
                <p class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ formatCurrency(mayor) }}</p>
            </div>
            <div class="py-3 px-4">
                <p class="text-xs text-gray-400 uppercase font-bold">Pago promedio</p>
                <p class="text-base font-black text-indigo-600 dark:text-indigo-400 mt-0.5">{{ formatCurrency(promedio) }}</p>
            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-bold">
                    <tr>
                        <th class="px-5 py-3">#</th>
                        <th class="px-5 py-3 cursor-pointer hover:text-indigo-600 select-none" @click="toggleSort('fecha')">
                            <span class="flex items-center gap-1">Fecha <component :is="SortIcon('fecha')" v-if="SortIcon('fecha')" class="w-3 h-3" /></span>
                        </th>
                        <th class="px-5 py-3">Código</th>
                        <th class="px-5 py-3 cursor-pointer hover:text-indigo-600 select-none" @click="toggleSort('cliente')">
                            <span class="flex items-center gap-1">Cliente <component :is="SortIcon('cliente')" v-if="SortIcon('cliente')" class="w-3 h-3" /></span>
                        </th>
                        <th class="px-5 py-3 cursor-pointer hover:text-indigo-600 select-none text-right" @click="toggleSort('monto_pagado')">
                            <span class="flex items-center justify-end gap-1">Monto <component :is="SortIcon('monto_pagado')" v-if="SortIcon('monto_pagado')" class="w-3 h-3" /></span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="(item, index) in sortedItems" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors">
                        <td class="px-5 py-3.5 text-xs text-gray-400 font-mono">{{ index + 1 }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ dayjs(item.fecha_pago).utc().format('DD/MM/YYYY') }}</td>
                        <td class="px-5 py-3.5 font-bold text-indigo-600 dark:text-indigo-400 font-mono text-sm">{{ item.prestamo?.codigo || 'N/A' }}</td>
                        <td class="px-5 py-3.5 text-gray-800 dark:text-gray-200 font-medium">{{ item.prestamo?.cliente?.nombre || 'Desconocido' }}</td>
                        <td class="px-5 py-3.5 text-right font-black text-gray-900 dark:text-white">{{ formatCurrency(item.monto_pagado) }}</td>
                    </tr>
                    <tr v-if="!items?.length">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">No hay registros para este periodo.</td>
                    </tr>
                </tbody>
                <tfoot v-if="items?.length" class="bg-gray-50 dark:bg-gray-900/50 font-bold text-sm border-t-2 border-gray-200 dark:border-gray-700">
                    <tr>
                        <td colspan="4" class="px-5 py-3 text-gray-600 dark:text-gray-300 uppercase text-xs tracking-wider">TOTAL ({{ items.length }} pagos)</td>
                        <td class="px-5 py-3 text-right text-gray-900 dark:text-white text-base">{{ formatCurrency(total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>
