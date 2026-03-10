<script setup>
/**
 * ClienteCard.vue — Tarjeta compacta de préstamo: solo código + monto.
 * Click navega al detalle del cliente.
 */
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    prestamo: { type: Object, required: true }
})

const agingColors = {
    verde:    { bg: 'bg-emerald-200 dark:bg-emerald-500/30', text: 'text-emerald-800 dark:text-emerald-200', border: 'border-emerald-400 dark:border-emerald-500/50' },
    amarillo: { bg: 'bg-yellow-200 dark:bg-yellow-500/30',   text: 'text-yellow-800 dark:text-yellow-200',   border: 'border-yellow-400 dark:border-yellow-500/50' },
    rojo:     { bg: 'bg-red-200 dark:bg-red-500/30',         text: 'text-red-800 dark:text-red-200',         border: 'border-red-400 dark:border-red-500/50' },
    azul:     { bg: 'bg-blue-200 dark:bg-blue-500/30',       text: 'text-blue-800 dark:text-blue-200',       border: 'border-blue-400 dark:border-blue-500/50' },
    remate:   { bg: 'bg-gray-300 dark:bg-gray-600',          text: 'text-gray-900 dark:text-gray-100',       border: 'border-gray-500 dark:border-gray-500' },
}

const colors = computed(() => agingColors[props.prestamo.estado_envejecimiento] ?? agingColors.verde)

const formatMonto = (val) => {
    if (val >= 1000) return (val / 1000).toFixed(1) + 'k'
    return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(val)
}

const irADetalle = () => {
    if (props.prestamo.cliente_id) {
        router.visit(`/clientes/${props.prestamo.cliente_id}/detalle`)
    }
}
</script>

<template>
    <button
        @click="irADetalle"
        :class="[
            'w-full px-2 py-1.5 rounded-md border-2 text-left transition-all duration-150 flex items-center justify-between gap-1 shadow-sm',
            'hover:scale-[1.03] hover:shadow-md cursor-pointer hover:brightness-105',
            colors.bg, colors.border
        ]"
        :title="`${prestamo.cliente_nombre} — Bs ${prestamo.saldo_pendiente} — ${prestamo.dias_envejecimiento}d`"
    >
        <p :class="['text-xs font-extrabold truncate', colors.text]">
            {{ prestamo.codigo }}
        </p>
        <div class="flex items-center gap-1.5 shrink-0">
            <span 
                v-if="prestamo.dias_envejecimiento > 0" 
                :class="['text-[10px] font-black px-1.5 py-0.5 rounded-sm', colors.bg, colors.text]"
                style="filter: brightness(0.9);"
            >
                {{ prestamo.dias_envejecimiento }}d
            </span>
            <p class="text-xs font-black text-gray-900 dark:text-white">
                Bs{{ formatMonto(prestamo.saldo_pendiente) }}
            </p>
        </div>
    </button>
</template>
