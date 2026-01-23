<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-transparent px-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25"
    >
        <slot />
    </button>
</template>
