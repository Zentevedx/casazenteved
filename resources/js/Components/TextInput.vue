<script setup>
import { onMounted, ref, useAttrs } from 'vue';

const attrs = useAttrs();

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

// Tipos que NO deben forzarse a mayúsculas
const noUppercaseTypes = ['password', 'email', 'url', 'search', 'number', 'tel'];

const isUppercase = !noUppercaseTypes.includes(attrs.type);

function handleInput(e) {
    if (isUppercase) {
        const start = e.target.selectionStart;
        const end   = e.target.selectionEnd;
        model.value  = e.target.value.toUpperCase();
        // Restaurar posición del cursor
        e.target.value = model.value;
        e.target.setSelectionRange(start, end);
    } else {
        model.value = e.target.value;
    }
}

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        :class="[
            'rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-[#111116] text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-indigo-500 dark:focus:ring-indigo-500 placeholder-gray-400 dark:placeholder-gray-600',
            isUppercase ? 'uppercase' : ''
        ]"
        :value="model"
        @input="handleInput"
        ref="input"
    />
</template>
