<script setup>
import { computed } from 'vue'

const props = defineProps({
    proyectado: { type: Number, default: 0 },
    cobrado:    { type: Number, default: 0 },
    eficiencia: { type: Number, default: 0 },
})

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val ?? 0)

const colorConfig = computed(() => {
    if (props.eficiencia >= 80) return {
        bar: 'bg-emerald-500',
        text: 'text-emerald-600 dark:text-emerald-400',
        badge: 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300',
        label: '✅ Excelente',
        ring: 'ring-emerald-500/30',
    }
    if (props.eficiencia >= 50) return {
        bar: 'bg-yellow-400',
        text: 'text-yellow-600 dark:text-yellow-400',
        badge: 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300',
        label: '⚠️ Regular',
        ring: 'ring-yellow-500/30',
    }
    return {
        bar: 'bg-red-500',
        text: 'text-red-600 dark:text-red-400',
        badge: 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300',
        label: '🚨 Crítico',
        ring: 'ring-red-500/30',
    }
})

const diferencia = computed(() => props.cobrado - props.proyectado)
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">Eficiencia de Cobranza</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Interés cobrado vs interés esperado (10% mensual)</p>
                </div>
            </div>
            <span :class="['px-3 py-1 rounded-full text-xs font-bold', colorConfig.badge]">
                {{ colorConfig.label }}
            </span>
        </div>

        <div class="p-6 space-y-6">
            <!-- Cifras principales -->
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Proyectado (10%)</p>
                    <p class="text-2xl font-black text-gray-700 dark:text-gray-200">{{ formatCurrency(proyectado) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Debería cobrar este mes</p>
                </div>
                <div class="p-4 rounded-xl" :class="colorConfig.ring + ' ring-2 bg-gray-50 dark:bg-gray-900/50'">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Cobrado Real</p>
                    <p :class="['text-2xl font-black', colorConfig.text]">{{ formatCurrency(cobrado) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Ingresó en el período</p>
                </div>
            </div>

            <!-- Barra de progreso -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300">Nivel de Cobranza</p>
                    <p :class="['text-2xl font-black', colorConfig.text]">{{ eficiencia }}%</p>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-3 overflow-hidden">
                    <div
                        :class="['h-3 rounded-full transition-all duration-1000', colorConfig.bar]"
                        :style="`width: ${Math.min(eficiencia, 100)}%`"
                    ></div>
                </div>
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>0%</span>
                    <span class="text-yellow-500 font-bold">50% mínimo</span>
                    <span class="text-emerald-500 font-bold">80% óptimo</span>
                </div>
            </div>

            <!-- Diferencia -->
            <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Diferencia</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ diferencia >= 0 ? 'Ingreso extra por remates/multas' : 'Dinero no cobrado este período' }}</p>
                </div>
                <p :class="['text-xl font-black', diferencia >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400']">
                    {{ diferencia >= 0 ? '+' : '' }}{{ formatCurrency(diferencia) }}
                </p>
            </div>
        </div>
    </div>
</template>
