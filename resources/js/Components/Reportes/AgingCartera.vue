<script setup>
const props = defineProps({
    aging: {
        type: Object,
        required: true
    },
    carteraTotal: {
        type: Number,
        default: 0
    }
})

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val ?? 0)

const pct = (monto) => props.carteraTotal > 0 ? ((monto / props.carteraTotal) * 100).toFixed(1) : 0

const niveles = [
    {
        key: 'al_dia',
        label: 'Al Día',
        desc: 'Último movimiento ≤ 30 días',
        color: 'emerald',
        emoji: '🟢',
        barColor: 'bg-emerald-500',
        textColor: 'text-emerald-600 dark:text-emerald-400',
        bgColor: 'bg-emerald-50 dark:bg-emerald-500/10',
        borderColor: 'border-l-emerald-500',
    },
    {
        key: 'riesgo_leve',
        label: 'Riesgo Leve',
        desc: '31 – 60 días sin movimiento',
        color: 'yellow',
        emoji: '🟡',
        barColor: 'bg-yellow-400',
        textColor: 'text-yellow-600 dark:text-yellow-400',
        bgColor: 'bg-yellow-50 dark:bg-yellow-500/10',
        borderColor: 'border-l-yellow-400',
    },
    {
        key: 'riesgo_alto',
        label: 'Riesgo Alto',
        desc: '61 – 89 días sin movimiento',
        color: 'orange',
        emoji: '🟠',
        barColor: 'bg-orange-500',
        textColor: 'text-orange-600 dark:text-orange-400',
        bgColor: 'bg-orange-50 dark:bg-orange-500/10',
        borderColor: 'border-l-orange-500',
    },
    {
        key: 'remate',
        label: 'En Remate',
        desc: '≥ 90 días sin movimiento',
        color: 'red',
        emoji: '🔴',
        barColor: 'bg-red-500',
        textColor: 'text-red-600 dark:text-red-400',
        bgColor: 'bg-red-50 dark:bg-red-500/10',
        borderColor: 'border-l-red-500',
    },
]
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-800 dark:text-white">Aging de Cartera</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Segmentación por antigüedad de inactividad</p>
            </div>
        </div>

        <!-- Niveles -->
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            <div
                v-for="nivel in niveles"
                :key="nivel.key"
                :class="['p-5 border-l-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-900/30', nivel.borderColor]"
            >
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ nivel.emoji }}</span>
                        <div>
                            <p class="text-sm font-bold text-gray-800 dark:text-white">{{ nivel.label }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ nivel.desc }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p :class="['text-lg font-black', nivel.textColor]">{{ formatCurrency(aging[nivel.key]?.monto) }}</p>
                        <p class="text-xs text-gray-400">{{ aging[nivel.key]?.count ?? 0 }} préstamo(s)</p>
                    </div>
                </div>
                <!-- Barra de progreso -->
                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5">
                    <div
                        :class="['h-1.5 rounded-full transition-all duration-700', nivel.barColor]"
                        :style="`width: ${pct(aging[nivel.key]?.monto)}%`"
                    ></div>
                </div>
                <p class="text-right text-xs text-gray-400 mt-1">{{ pct(aging[nivel.key]?.monto) }}% del total</p>
            </div>
        </div>
    </div>
</template>
