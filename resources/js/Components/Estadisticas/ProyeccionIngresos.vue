<script setup>
import { computed } from 'vue'

const props = defineProps({
    proyectado: { type: Number, default: 0 },
    cobrado:    { type: Number, default: 0 },
    eficiencia: { type: Number, default: 0 },
})

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val ?? 0)

const diferencia = computed(() => props.cobrado - props.proyectado)

const colorConfig = computed(() => {
    if (props.eficiencia >= 80) return { bar: 'bg-emerald-500', text: 'text-emerald-400', label: 'Óptimo' }
    if (props.eficiencia >= 50) return { bar: 'bg-yellow-400', text: 'text-yellow-400', label: 'Regular' }
    return { bar: 'bg-red-500', text: 'text-red-400', label: 'Crítico' }
})
</script>

<template>
    <div class="bg-[#1a1a1a] p-5 rounded-2xl border border-gray-800 shadow-xl flex flex-col gap-4 hover:border-purple-500/30 transition-colors">
        <div>
            <p class="text-[10px] uppercase font-bold tracking-widest mb-1 text-purple-400">Proyección de Ingresos</p>
            <h3 class="text-2xl font-bold text-white tracking-tight">{{ formatCurrency(proyectado) }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">Interés esperado este mes (10%)</p>
        </div>

        <!-- Barra eficiencia -->
        <div>
            <div class="flex justify-between text-xs mb-1">
                <span class="text-gray-500">Cobrado: <span class="text-white font-bold">{{ formatCurrency(cobrado) }}</span></span>
                <span :class="['font-bold', colorConfig.text]">{{ eficiencia }}% · {{ colorConfig.label }}</span>
            </div>
            <div class="w-full bg-gray-800 h-1.5 rounded-full overflow-hidden">
                <div :class="['h-full rounded-full transition-all duration-700', colorConfig.bar]" :style="`width: ${Math.min(eficiencia, 100)}%`"></div>
            </div>
        </div>

        <!-- Diferencia -->
        <div class="flex items-center justify-between pt-2 border-t border-gray-800">
            <p class="text-xs text-gray-500">Diferencia</p>
            <p :class="['text-sm font-bold', diferencia >= 0 ? 'text-emerald-400' : 'text-red-400']">
                {{ diferencia >= 0 ? '+' : '' }}{{ formatCurrency(diferencia) }}
            </p>
        </div>
    </div>
</template>
