<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true
    },
    useDot: {
        type: Boolean,
        default: false
    }
});

const config = computed(() => {
    switch (props.status.toLowerCase()) {
        case 'al_dia':
        case 'estable':
        case 'verde':
            return {
                badge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400',
                dot: 'bg-emerald-500'
            };
        case 'riesgo_leve':
        case 'advertencia':
        case 'amarillo':
            return {
                badge: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                dot: 'bg-amber-500'
            };
        case 'riesgo_alto':
        case 'crítico':
        case 'rojo':
            return {
                badge: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                dot: 'bg-red-500'
            };
        case 'remate':
        case 'azul': 
            return {
                badge: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400',
                dot: 'bg-indigo-500'
            };
        default:
            return {
                badge: 'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-400',
                dot: 'bg-gray-500'
            };
    }
});
</script>

<template>
    <span :class="['inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-black', config.badge]">
        <span v-if="useDot" :class="['w-1.5 h-1.5 rounded-full', config.dot]"></span>
        <slot></slot>
    </span>
</template>
