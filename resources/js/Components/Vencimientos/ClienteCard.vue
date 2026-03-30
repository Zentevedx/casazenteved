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
            'w-full p-2.5 rounded-lg border-2 text-left transition-all duration-150 flex flex-col gap-1.5 shadow-sm',
            'hover:scale-[1.02] hover:shadow-md cursor-pointer hover:brightness-105',
            colors.bg, colors.border
        ]"
        :title="`${prestamo.cliente_nombre} — Bs ${prestamo.saldo_pendiente} — ${prestamo.dias_envejecimiento}d`"
    >
        <div class="flex items-center justify-between w-full">
            <p :class="['font-ufc text-sm font-extrabold truncate uppercase tracking-widest', colors.text]">
                {{ prestamo.codigo }}
            </p>
            <span 
                v-if="prestamo.dias_envejecimiento > 0" 
                :class="['font-ufc text-[9px] font-black px-1.5 py-0.5 rounded flex-shrink-0', colors.bg, colors.text]"
                style="filter: brightness(0.9);"
            >
                {{ prestamo.dias_envejecimiento }}d
            </span>
        </div>
        
        <p class="text-xs text-gray-900 dark:text-gray-100 font-bold truncate w-full" style="line-height: 1.2;">
            {{ prestamo.cliente_nombre }}
        </p>
        
        <div class="bg-black/5 dark:bg-white/5 rounded px-1.5 py-1 w-full border border-black/5 dark:border-white/5">
           <p class="text-[9px] font-medium opacity-80 truncate line-clamp-1 w-full" style="line-height: 1.1;">
                {{ prestamo.articulos?.length ? prestamo.articulos.map(a => a.nombre + (a.detalle ? ' - '+a.detalle : '')).join(', ') : 'Sin artículo' }}
           </p>
        </div>
        
        <div class="mt-1 flex justify-end w-full">
            <p class="font-ufc text-base font-black text-gray-900 dark:text-white" style="line-height: 1;">
                Bs. {{ formatMonto(prestamo.saldo_pendiente) }}
            </p>
        </div>
    </button>
</template>
