<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: {
        type: String,
        required: true
    },
    identifier: {
        type: [Number, String],
        required: true
    },
    fotoUrl: {
        type: String,
        default: null
    },
    sizeClass: {
        type: String,
        default: 'w-10 h-10 text-sm'
    },
    roundedClass: {
        type: String,
        default: 'rounded-2xl'
    }
});

const initials = computed(() => {
    if (!props.name) return '???';
    const parts = props.name.trim().split(/\s+/).filter(Boolean);
    return parts.map(n => n[0]).slice(0, 3).join('').toUpperCase();
});

const gradientClass = computed(() => {
    const gradients = [
        'from-blue-500 to-cyan-500', 
        'from-purple-500 to-fuchsia-500', 
        'from-emerald-500 to-teal-500', 
        'from-orange-500 to-amber-500',
        'from-rose-500 to-pink-500'
    ];
    let sum = 0;
    const str = String(props.name || props.identifier);
    for (let i = 0; i < str.length; i++) {
        sum += str.charCodeAt(i);
    }
    return gradients[sum % gradients.length];
});

const isUrl = computed(() => {
    if (!props.fotoUrl) return false;
    return props.fotoUrl.startsWith('http') || props.fotoUrl.startsWith('data:');
});
</script>

<template>
    <div class="relative shrink-0 flex items-center justify-center select-none">
        <img 
            v-if="fotoUrl" 
            :src="isUrl ? fotoUrl : `/storage/${fotoUrl}`" 
            :class="[sizeClass, roundedClass, 'object-cover shadow-[0_4px_12px_rgba(0,0,0,0.15)] pointer-events-none group-hover:scale-105 transition-transform duration-300']"
        />
        <div 
            v-else
            :class="[
                sizeClass, 
                roundedClass, 
                gradientClass, 
                'bg-gradient-to-br flex items-center justify-center text-white font-ufc-condensed shadow-[0_4px_15px_rgba(0,0,0,0.2)] border border-white/20 dark:border-black/20 overflow-hidden'
            ]"
            style="container-type: inline-size;"
        >
            <span :style="`font-size: ${initials.length < 3 ? '65cqi' : '55cqi'}; line-height: 1; padding-top: 1%;`" class="tracking-wide">
                {{ initials }}
            </span>
        </div>
    </div>
</template>

<style scoped>
@font-face {
    font-family: 'UFCSansCondensedMedium';
    src: url('/Fuentes/UFCSans-CondensedMedium.ttf') format('truetype');
}
.font-ufc-condensed {
    font-family: 'UFCSansCondensedMedium', sans-serif;
    font-weight: 500;
}
</style>
