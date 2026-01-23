<script setup>
defineProps({
    title: String,
    value: [String, Number],
    subValue: String,
    icon: Object, // Can be a component or specific string identifier we handle
    colorTheme: {
        type: String,
        default: 'gray', // gray, emerald, amber, red
        validator: (value) => ['gray', 'emerald', 'amber', 'red', 'blue'].includes(value)
    }
});

const themes = {
    gray: {
        bg: 'bg-[#1E1E1E]',
        border: 'border-gray-800',
        textLabel: 'text-gray-400',
        textValue: 'text-white',
        iconBg: 'opacity-10',
        iconColor: 'text-white'
    },
    emerald: {
        bg: 'bg-[#1E1E1E]',
        border: 'border-gray-800',
        textLabel: 'text-emerald-200',
        textValue: 'text-emerald-400',
        iconBg: 'bg-emerald-500',
        iconColor: 'text-emerald-500' // used for glow
    },
    amber: {
        bg: 'bg-[#1E1E1E]',
        border: 'border-gray-800',
        textLabel: 'text-amber-200',
        textValue: 'text-amber-400',
        iconBg: 'bg-amber-500',
        iconColor: 'text-amber-500'
    },
    red: {
        bg: 'bg-[#2B1818]',
        border: 'border-red-900/30',
        textLabel: 'text-red-200',
        textValue: 'text-red-400',
        iconBg: 'bg-red-500',
        iconColor: 'text-red-500'
    },
    blue: {
        bg: 'bg-[#1E1E1E]',
        border: 'border-blue-900/30',
        textLabel: 'text-blue-200',
        textValue: 'text-blue-400',
        iconBg: 'bg-blue-500',
        iconColor: 'text-blue-500'
    }
};
</script>

<template>
    <div :class="[
        themes[colorTheme].bg, 
        themes[colorTheme].border,
        'p-6 rounded-[24px] shadow-lg hover:shadow-xl transition-shadow border relative overflow-hidden group'
    ]">
        <!-- Glow/Icon visualization -->
        <div v-if="colorTheme !== 'gray' && colorTheme !== 'red'" class="absolute right-0 top-0 p-4 opacity-10">
            <div :class="['w-16 h-16 rounded-full blur-2xl', themes[colorTheme].iconBg]"></div>
        </div>
        <div v-else-if="colorTheme === 'gray'" class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
           <slot name="icon">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
           </slot>
        </div>

        <p :class="[themes[colorTheme].textLabel, 'text-sm font-medium']">{{ title }}</p>
        
        <div class="mt-2">
            <p :class="[themes[colorTheme].textValue, 'text-3xl font-normal']">{{ value }}</p>
            <p v-if="subValue" class="text-xs mt-1 opacity-70" :class="themes[colorTheme].textLabel">{{ subValue }}</p>
        </div>
    </div>
</template>
