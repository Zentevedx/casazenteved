<script setup>
import { ref } from 'vue';
import LoanItem from './LoanItem.vue';

const props = defineProps({
    mes: Object, // The month object from the report
    isInitiallyExpanded: Boolean
});

const isExpanded = ref(props.isInitiallyExpanded || false);
const prestamosDesplegados = ref({});

const toggleMes = () => {
    isExpanded.value = !isExpanded.value;
};

const togglePrestamo = (id) => {
    prestamosDesplegados.value[id] = !prestamosDesplegados.value[id];
};

const formatCurrency = (value) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(value);
</script>

<template>
    <div class="bg-white dark:bg-dark-surface rounded-[28px] overflow-hidden shadow-sm transition-all duration-300 border border-gray-100 dark:border-gray-800">
        
        <!-- Header del Mes -->
        <div @click="toggleMes" 
                class="cursor-pointer px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between hover:bg-gray-50 dark:hover:bg-white/5 transition-colors border-b border-gray-100 dark:border-gray-800/50 gap-4">
            
            <div class="flex items-center gap-4">
                <div class="bg-gray-100 dark:bg-[#333] p-2 rounded-xl flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500 dark:text-gray-400 transition-transform duration-300" 
                            :class="{ 'rotate-180': isExpanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <h2 class="text-xl text-gray-900 dark:text-white font-medium capitalize">{{ mes.nombre_mes }}</h2>
                
                <!-- STATUS COUNTERS -->
                <div class="flex items-center gap-1.5 hidden sm:flex">
                     <span class="text-xs font-bold bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-gray-400 px-2 py-0.5 rounded-md border border-gray-200 dark:border-white/5">
                        {{ mes.contadores.total }}
                    </span>
                    <!-- Green -->
                    <div v-if="mes.contadores.verde > 0" class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-500/20">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400">{{ mes.contadores.verde }}</span>
                    </div>
                    <!-- Yellow -->
                    <div v-if="mes.contadores.amarillo > 0" class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-500/20">
                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div>
                        <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400">{{ mes.contadores.amarillo }}</span>
                    </div>
                    <!-- Red -->
                    <div v-if="mes.contadores.rojo > 0" class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-500/20">
                        <div class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></div>
                        <span class="text-[10px] font-bold text-red-700 dark:text-red-400">{{ mes.contadores.rojo }}</span>
                    </div>
                     <!-- Pagado -->
                    <div v-if="mes.contadores.pagado > 0" class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                        <div class="w-1.5 h-1.5 rounded-full bg-gray-400"></div>
                        <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">{{ mes.contadores.pagado }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto sm:justify-end pl-14 sm:pl-0">
                <div class="flex flex-col items-end px-3 py-1 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <span class="text-[10px] text-gray-500 uppercase tracking-wider">Prestado</span>
                    <span class="font-mono font-bold text-gray-700 dark:text-gray-300 text-xs sm:text-sm">{{ formatCurrency(mes.resumen.monto_total) }}</span>
                </div>

                <div class="flex flex-col items-end px-3 py-1 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-500/20">
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400/70 uppercase tracking-wider">Recuperado</span>
                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs sm:text-sm">{{ formatCurrency(mes.resumen.capital_recuperado) }}</span>
                </div>

                <div class="flex flex-col items-end px-3 py-1 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-100 dark:border-amber-500/20">
                    <span class="text-[10px] text-amber-600 dark:text-amber-400/70 uppercase tracking-wider">Ganancia</span>
                    <span class="font-mono font-bold text-amber-600 dark:text-amber-400 text-xs sm:text-sm">{{ formatCurrency(mes.resumen.intereses_generados) }}</span>
                </div>
            </div>
        </div>

        <!-- Contenido del Mes -->
        <div v-show="isExpanded" class="bg-gray-50 dark:bg-black/20 border-t border-gray-100 dark:border-gray-800">
            <div v-for="semana in mes.semanas" :key="semana.semana" class="p-2 sm:p-4">
                
                <div class="flex items-center gap-4 mb-4 ml-2">
                    <div class="h-[1px] bg-gray-200 dark:bg-gray-800 flex-grow"></div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest bg-gray-50 dark:bg-transparent px-2">
                        Semana {{ semana.semana }} ({{ semana.rango_fechas }})
                    </span>
                    <div class="h-[1px] bg-gray-200 dark:bg-gray-800 flex-grow"></div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-2 items-start grid-flow-dense">
                    <LoanItem 
                        v-for="prestamo in semana.prestamos" 
                        :key="prestamo.id" 
                        :prestamo="prestamo"
                        :is-expanded="!!prestamosDesplegados[prestamo.id]"
                        @toggle="togglePrestamo(prestamo.id)"
                        class="transition-all duration-500 ease-spring"
                        :class="[
                            prestamosDesplegados[prestamo.id] 
                                ? 'col-span-2 md:col-span-3 row-span-2 ring-2 ring-indigo-500/50 shadow-2xl scale-[1.01] z-10' 
                                : 'col-span-1 hover:scale-[1.02] active:scale-95'
                        ]"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
