<script setup>
import { computed } from 'vue'
import { PrinterIcon } from '@heroicons/vue/24/outline';
import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
dayjs.extend(utc)

const props = defineProps({
    items: Array,
    filters: Object,
    type: {
        type: String, // 'intereses' or 'capital'
        default: 'intereses'
    }
})

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val)

const downloadPdf = () => {
    const url = route('reportes.financiero.pdf', {
        ...props.filters,
        tipo: props.type
    });
    window.open(url, '_blank');
}

const title = computed(() => props.type === 'intereses' ? 'Detalle de Intereses Cobrados' : 'Detalle de Capital Recuperado')
const subtitle = computed(() => props.type === 'intereses' ? 'Ingresos por concepto de intereses' : 'Ingresos por devolución de capital')
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ title }}</h3>
                <p class="text-xs text-gray-500">{{ subtitle }}</p>
            </div>
            <button 
                @click="downloadPdf"
                class="inline-flex items-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
            >
                <PrinterIcon class="w-4 h-4 mr-2" />
                Imprimir Lista
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-bold">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">Fecha Pago</th>
                        <th class="px-6 py-4">Código Préstamo</th>
                        <th class="px-6 py-4">Cliente</th>
                        <th class="px-6 py-4 text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="(item, index) in items" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ index + 1 }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ dayjs(item.fecha_pago).utc().format('DD/MM/YYYY HH:mm') }}</td>
                        <td class="px-6 py-4 font-bold text-indigo-600 dark:text-indigo-400">
                            {{ item.prestamo?.codigo || 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-gray-800 dark:text-gray-200">
                            {{ item.prestamo?.cliente?.nombre || 'Cliente Desconocido' }}
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">{{ formatCurrency(item.monto_pagado) }}</td>
                    </tr>
                    <tr v-if="items.length === 0">
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">No hay registros para este periodo.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
