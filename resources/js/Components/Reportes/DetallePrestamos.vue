<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { PrinterIcon, ChevronUpIcon, ChevronDownIcon, BanknotesIcon } from '@heroicons/vue/24/outline'
import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
dayjs.extend(utc)

const props = defineProps({
    items: Array,
    filters: Object
})

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val)

// --- Sorting ---
const sortKey   = ref('monto')      // default: mayor a menor por monto
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
        if (sortKey.value === 'monto')          { vA = a.monto;           vB = b.monto }
        else if (sortKey.value === 'fecha')      { vA = new Date(a.fecha_prestamo); vB = new Date(b.fecha_prestamo) }
        else if (sortKey.value === 'cliente')    { vA = a.cliente?.nombre?.toLowerCase(); vB = b.cliente?.nombre?.toLowerCase() }
        else if (sortKey.value === 'estado')     { vA = a.estado;          vB = b.estado }
        else                                    { vA = a[sortKey.value];  vB = b[sortKey.value] }
        if (vA < vB) return sortOrder.value === 'asc' ? -1 : 1
        if (vA > vB) return sortOrder.value === 'asc' ? 1 : -1
        return 0
    })
})

// --- Totales ---
const total  = computed(() => props.items?.reduce((s, i) => s + Number(i.monto), 0) ?? 0)
const mayor  = computed(() => props.items?.length ? Math.max(...props.items.map(i => i.monto)) : 0)
const menor  = computed(() => props.items?.length ? Math.min(...props.items.map(i => i.monto)) : 0)
const promedio = computed(() => props.items?.length ? total.value / props.items.length : 0)

const downloadPdf = () => {
    window.open(route('reportes.financiero.pdf', { ...props.filters, tipo: 'prestamos' }), '_blank')
}

// Helper icono de orden
const SortIcon = (key) => sortKey.value === key
    ? (sortOrder.value === 'asc' ? ChevronUpIcon : ChevronDownIcon)
    : null
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">

        <!-- Header -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center">
                    <BanknotesIcon class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">Detalle de Préstamos</h3>
                    <p class="text-xs text-gray-400">{{ items?.length ?? 0 }} operaciones · ordenado por <span class="font-bold text-indigo-500">{{ sortKey }} {{ sortOrder === 'desc' ? '↓' : '↑' }}</span></p>
                </div>
            </div>
            <button @click="downloadPdf" class="inline-flex items-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm font-bold hover:bg-indigo-100 transition-colors">
                <PrinterIcon class="w-4 h-4 mr-2" /> Imprimir Lista
            </button>
        </div>

        <!-- Resumen rápido -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-0 border-b border-gray-100 dark:border-gray-800 text-center">
            <div class="py-3 px-4 border-r border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-400 uppercase font-bold">Total</p>
                <p class="text-base font-black text-gray-900 dark:text-white mt-0.5">{{ formatCurrency(total) }}</p>
            </div>
            <div class="py-3 px-4 border-r border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-400 uppercase font-bold">Mayor</p>
                <p class="text-base font-black text-indigo-600 dark:text-indigo-400 mt-0.5">{{ formatCurrency(mayor) }}</p>
            </div>
            <div class="py-3 px-4 border-r border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-400 uppercase font-bold">Menor</p>
                <p class="text-base font-black text-gray-600 dark:text-gray-300 mt-0.5">{{ formatCurrency(menor) }}</p>
            </div>
            <div class="py-3 px-4">
                <p class="text-xs text-gray-400 uppercase font-bold">Promedio</p>
                <p class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ formatCurrency(promedio) }}</p>
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
                        <th class="px-5 py-3 cursor-pointer hover:text-indigo-600 select-none text-right" @click="toggleSort('monto')">
                            <span class="flex items-center justify-end gap-1">Monto <component :is="SortIcon('monto')" v-if="SortIcon('monto')" class="w-3 h-3" /></span>
                        </th>
                        <th class="px-5 py-3 cursor-pointer hover:text-indigo-600 select-none" @click="toggleSort('estado')">
                            <span class="flex items-center gap-1">Estado <component :is="SortIcon('estado')" v-if="SortIcon('estado')" class="w-3 h-3" /></span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="(item, index) in sortedItems" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors">
                        <td class="px-5 py-3.5 text-xs text-gray-400 font-mono">{{ index + 1 }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ dayjs(item.fecha_prestamo).utc().format('DD/MM/YYYY') }}</td>
                        <td class="px-5 py-3.5 font-bold text-indigo-600 dark:text-indigo-400 font-mono text-sm">{{ item.codigo }}</td>
                        <td class="px-5 py-3.5 text-gray-800 dark:text-gray-200 font-medium">{{ item.cliente?.nombre }}</td>
                        <td class="px-5 py-3.5 text-right font-black text-gray-900 dark:text-white">{{ formatCurrency(item.monto) }}</td>
                        <td class="px-5 py-3.5">
                            <span :class="{
                                'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400': item.estado === 'Pagado',
                                'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400': item.estado === 'Activo',
                                'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400': item.estado === 'Vencido'
                            }" class="px-2.5 py-1 rounded-full text-xs font-bold uppercase">
                                {{ item.estado }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="!items?.length">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">No hay préstamos registrados en este periodo.</td>
                    </tr>
                </tbody>
                <!-- Footer con totales -->
                <tfoot v-if="items?.length" class="bg-gray-50 dark:bg-gray-900/50 font-bold text-sm border-t-2 border-gray-200 dark:border-gray-700">
                    <tr>
                        <td colspan="4" class="px-5 py-3 text-gray-600 dark:text-gray-300 uppercase text-xs tracking-wider">TOTAL ({{ items.length }} operaciones)</td>
                        <td class="px-5 py-3 text-right text-gray-900 dark:text-white text-base">{{ formatCurrency(total) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>
