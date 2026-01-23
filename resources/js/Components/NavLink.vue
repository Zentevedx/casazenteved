<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'flex items-center px-4 py-3 bg-indigo-600/10 text-indigo-400 rounded-xl font-bold transition-all border border-indigo-600/20 shadow-inner'
        : 'flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl font-medium transition-all hover:pl-5'
);
</script>

<template>
    <Link :href="href" :class="classes" class="gap-3">
        <slot />
    </Link>
</template>
