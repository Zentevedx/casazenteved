<script setup>
import { computed } from 'vue';

import { ChevronDownIcon, CalendarDaysIcon, ClockIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    selectedModo: String,
    selectedFecha: String,
    opcionesFechas: Array,
    rangoTexto: String
});

const emit = defineEmits(['update:modo', 'update:fecha']);

const modos = [
    { id: 'dia', label: 'Diario', icon: ClockIcon },
    { id: 'semana', label: 'Semanal', icon: CalendarDaysIcon },
    { id: 'mes', label: 'Mensual', icon: CalendarDaysIcon },
];

const currentModoLabel = computed(() => modos.find(m => m.id === props.selectedModo)?.label);

// Manejo manual de dropdowns para no depender de HeadlessUI si no está instalado
// Usaremos elementos nativos estilizados por ahora para garantizar compatibilidad, 
// o un diseño custom simple.
</script>

<template>
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-[#1a1a1a] p-2 rounded-2xl border border-gray-800 shadow-lg">
        
        <!-- Modo Selector (Tabs Style) -->
        <div class="flex p-1 bg-black/40 rounded-xl">
            <button 
                v-for="modo in modos" 
                :key="modo.id"
                @click="$emit('update:modo', modo.id)"
                :class="['px-4 py-2 rounded-lg text-xs font-bold transition-all duration-300 flex items-center gap-2', 
                    selectedModo === modo.id 
                    ? 'bg-indigo-600 text-white shadow-md' 
                    : 'text-gray-500 hover:text-gray-300 hover:bg-white/5']"
            >
                <!-- <component :is="modo.icon" class="w-3 h-3" /> -->
                {{ modo.label }}
            </button>
        </div>

        <div class="h-6 w-[1px] bg-gray-800 hidden sm:block"></div>

        <!-- Date Selector (Custom styled select) -->
        <div class="relative group w-full sm:w-auto">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <CalendarDaysIcon class="h-4 w-4 text-gray-500 group-hover:text-indigo-400 transition-colors"/>
            </div>
            <select 
                :value="selectedFecha"
                @input="$emit('update:fecha', $event.target.value)"
                class="block w-full sm:w-64 pl-10 pr-10 py-2.5 bg-black/20 border border-transparent text-gray-300 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent cursor-pointer hover:bg-white/5 transition-colors appearance-none font-medium"
            >
                <option v-for="op in opcionesFechas" :key="op.value" :value="op.value" class="bg-gray-900 text-gray-300">
                    {{ op.label }}
                </option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <ChevronDownIcon class="h-4 w-4 text-gray-600" aria-hidden="true" />
            </div>
        </div>

        <div class="hidden md:block ml-auto px-4 text-right">
             <p class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">Rango Visualizado</p>
             <p class="text-xs text-indigo-400 font-mono font-medium">{{ rangoTexto }}</p>
        </div>

    </div>
</template>
