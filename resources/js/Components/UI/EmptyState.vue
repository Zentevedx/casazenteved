<script setup>
defineProps({
    title: {
        type: String,
        default: 'No hay registros'
    },
    description: {
        type: String,
        default: 'Actualmente no hay datos disponibles para mostrar en esta sección.'
    },
    iconType: {
        type: String,
        default: 'success' // success, warning, info
    }
});
</script>

<template>
    <div class="py-16 text-center">
        <div 
            :class="[
                'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4',
                iconType === 'success' ? 'bg-green-100 dark:bg-green-500/20' : 
                iconType === 'warning' ? 'bg-amber-100 dark:bg-amber-500/20' : 
                'bg-blue-100 dark:bg-blue-500/20'
            ]"
        >
            <slot name="icon">
                <svg v-if="iconType === 'success'" class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg v-else-if="iconType === 'warning'" class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else class="w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </slot>
        </div>
        <p class="text-gray-500 dark:text-gray-400 font-medium">{{ title }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ description }}</p>
        <slot name="actions"></slot>
    </div>
</template>
