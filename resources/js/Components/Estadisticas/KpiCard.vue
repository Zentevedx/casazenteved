<script setup>
defineProps({
    title: String,
    value: [String, Number],
    subValue: String, // e.g. "vs previous period"
    trend: Number, // percentage change, e.g. 5.2 or -1.4
    colorTheme: {
        type: String,
        default: 'indigo',
        validator: (v) => ['indigo', 'emerald', 'amber', 'red', 'purple', 'blue', 'pink'].includes(v)
    }
});

const themes = {
    indigo: { bg: 'bg-indigo-500/10', text: 'text-indigo-400', border: 'group-hover:border-indigo-500/50' },
    emerald: { bg: 'bg-emerald-500/10', text: 'text-emerald-400', border: 'group-hover:border-emerald-500/50' },
    amber: { bg: 'bg-amber-500/10', text: 'text-amber-400', border: 'group-hover:border-amber-500/50' },
    red: { bg: 'bg-red-500/10', text: 'text-red-400', border: 'group-hover:border-red-500/50' },
    purple: { bg: 'bg-purple-500/10', text: 'text-purple-400', border: 'group-hover:border-purple-500/50' },
    blue: { bg: 'bg-blue-500/10', text: 'text-blue-400', border: 'group-hover:border-blue-500/50' },
    pink: { bg: 'bg-pink-500/10', text: 'text-pink-400', border: 'group-hover:border-pink-500/50' },
};
</script>

<template>
    <div class="bg-white dark:bg-dark-surface p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-md dark:shadow-xl relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:hover:shadow-2xl">
        <!-- Glow Effect -->
        <div :class="['absolute -right-6 -top-6 w-24 h-24 rounded-full transition-colors duration-500 blur-2xl opacity-20 dark:opacity-40', themes[colorTheme].bg]"></div>
        
        <div class="relative z-10">
            <p :class="['text-[10px] uppercase font-bold tracking-widest mb-1', themes[colorTheme].text]">
                {{ title }}
            </p>
            
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">{{ value }}</h3>
            
            <div v-if="trend !== undefined" class="mt-3 flex items-center gap-2">
                <span :class="['text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1', trend >= 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-red-500/10 text-red-600 dark:text-red-400']">
                    <svg v-if="trend >= 0" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                    <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    {{ Math.abs(trend).toFixed(1) }}%
                </span>
                <span class="text-[10px] text-gray-400 dark:text-gray-500">vs periodo anterior</span>
            </div>
            
            <div v-else-if="subValue" class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                {{ subValue }}
            </div>
        </div>
    </div>
</template>
