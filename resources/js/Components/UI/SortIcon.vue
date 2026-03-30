<script setup>
defineProps({
    activeIcon: {
        type: [String, Boolean],
        default: false // Puede ser 'asc' o 'desc' o falso
    }
});
</script>

<template>
    <svg v-if="activeIcon === 'asc'" class="w-3 h-3 text-indigo-500 inline ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
    </svg>
    <svg v-else-if="activeIcon === 'desc'" class="w-3 h-3 text-indigo-500 inline ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
    <svg v-else class="w-3 h-3 text-gray-300 dark:text-gray-600 inline ml-1 opacity-50 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
    </svg>
</template>
