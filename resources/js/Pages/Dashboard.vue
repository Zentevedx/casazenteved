<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    reporte: Array,
    fechaMostrada: Object,
});

const hoveredColumnIndex = ref(null);

const columnasDelDashboard = computed(() => {
    const columnas = [];
    props.reporte.forEach(dia => {
        const prestamos = dia.prestamos;
        const tamanoMaximo = 3;
        for (let i = 0; i < prestamos.length; i += tamanoMaximo) {
            const chunk = prestamos.slice(i, i + tamanoMaximo);
            columnas.push({
                fecha: dia.fecha,
                prestamos: chunk,
                esContinuacion: i > 0,
            });
        }
    });
    return columnas;
});

const getDayInfo = (dateString) => {
    const date = new Date(`${dateString}T12:00:00`);
    return { diaSemana: date.toLocaleDateString('es-ES', { weekday: 'long' }), diaMes: date.getDate() };
};
const formatCurrency = (value) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(value);
const getAvatar = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};
const themes = {
    verde: { borde: 'border-green-400', avatarBg: 'bg-green-500', texto: 'text-green-300', icono: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>` },
    naranja: { borde: 'border-orange-400', avatarBg: 'bg-orange-500', texto: 'text-orange-300', icono: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>` },
    rojo: { borde: 'border-red-400', avatarBg: 'bg-red-500', texto: 'text-red-300', icono: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5.026l.207.208a.833.833 0 01-.588 1.416 12.023 12.023 0 00-1.25 4.908c0 .261.212.473.473.473h.923a.833.833 0 01.821.996l-.286 2.005a.833.833 0 01-1.61.054 12.05 12.05 0 00-1.12 4.902A11.954 11.954 0 0110 18.056c6.264 0 11.37-4.86 11.856-10.966a.833.833 0 111.66.198A13.63 13.63 0 0110 19.722 13.63 13.63 0 01-2.998 7.07a.833.833 0 01.58-1.413l.206-.207A13.63 13.63 0 0110 1.944z" clip-rule="evenodd" /></svg>` }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="bg-gray-900 text-white min-h-screen flex flex-col">
            <header class="p-4 sm:p-6 lg:p-8 sticky top-0 bg-gray-900 bg-opacity-80 backdrop-blur-sm z-10 flex-shrink-0">
                <h1 class="text-3xl font-bold tracking-tight text-white capitalize">
                    {{ fechaMostrada.titulo }}
                    <span class="text-lg font-light text-gray-400">({{ fechaMostrada.subtitulo }})</span>
                </h1>
            </header>

            <main @mouseleave="hoveredColumnIndex = null" class="flex-grow flex space-x-2 overflow-x-auto p-4 sm:p-6 lg:p-8 custom-scrollbar">
                <div v-if="columnasDelDashboard.length === 0" class="flex-1 flex items-center justify-center h-full">
                    <p class="text-gray-500 text-xl">✅ No hay pagos programados.</p>
                </div>
                
                <div v-else v-for="(columna, index) in columnasDelDashboard" 
                     :key="columna.fecha + '-' + index" 
                     @mouseenter="hoveredColumnIndex = index"
                     class="flex-shrink-0 rounded-xl transition-all duration-500 ease-in-out"
                     :class="hoveredColumnIndex === index ? 'w-80' : 'w-24'">
                    
                    <div class="bg-gray-800 rounded-xl p-4 h-full overflow-y-auto custom-scrollbar-vertical">
                        <h2 class="font-bold text-lg capitalize mb-4 text-center transition-opacity duration-300" :class="{'opacity-0': hoveredColumnIndex !== index && hoveredColumnIndex !== null}">
                            {{ getDayInfo(columna.fecha).diaSemana }}
                            <span class="ml-2 font-mono text-xl bg-gray-700 rounded-full w-8 h-8 inline-flex items-center justify-center">{{ getDayInfo(columna.fecha).diaMes }}</span>
                            <span v-if="columna.esContinuacion" class="text-xs text-gray-400 ml-2">(cont.)</span>
                        </h2>
                        
                        <div class="space-y-4">
                            <div v-for="prestamo in columna.prestamos" :key="prestamo.id">
                                <div v-if="hoveredColumnIndex === index"
                                    class="bg-gray-800/50 rounded-lg p-4 border-l-4"
                                    :class="themes[prestamo.color].borde">
                                    <div class="flex items-center space-x-4 mb-3">
                                        <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold text-white" :class="themes[prestamo.color].avatarBg">
                                            {{ getAvatar(prestamo.cliente_nombre) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-white truncate">{{ prestamo.cliente_nombre }}</p>
                                            <p class="text-xs text-gray-400 font-mono">CODE: {{ prestamo.codigo }}</p>
                                        </div>
                                    </div>
                                    <div class="text-center my-4">
                                        <p class="text-4xl font-bold tracking-tighter text-white">{{ formatCurrency(prestamo.monto) }}</p>
                                        <p class="text-xs text-gray-500">Monto del Préstamo</p>
                                    </div>

                                    <div class="space-y-3 text-xs">
                                        <div>
                                            <h4 class="font-bold text-gray-400 mb-1">Artículos Prendados</h4>
                                            <ul v-if="prestamo.articulos.length > 0" class="list-disc list-inside text-gray-300 pl-2">
                                                <li v-for="articulo in prestamo.articulos" :key="articulo.id">{{ articulo.nombre_articulo }}</li>
                                            </ul>
                                            <p v-else class="text-gray-500 italic">No hay artículos registrados.</p>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-400 mb-1">Historial de Pagos</h4>
                                            <div v-if="prestamo.pagos.length > 0" class="space-y-1">
                                                <div v-for="pago in prestamo.pagos" :key="pago.id" class="flex justify-between items-center bg-gray-700/50 p-1 rounded">
                                                    <span class="text-gray-400">{{ pago.fecha_pago }}</span>
                                                    <span class="font-semibold px-2 rounded" :class="pago.tipo_pago === 'Capital' ? 'bg-green-500/20 text-green-300' : 'bg-blue-500/20 text-blue-300'">{{ pago.tipo_pago }}</span>
                                                    <span class="font-mono text-gray-300">{{ formatCurrency(pago.monto_pagado) }}</span>
                                                </div>
                                            </div>
                                            <p v-else class="text-gray-500 italic">No se han realizado pagos.</p>
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center text-xs text-gray-400 border-t border-gray-700 mt-3 pt-2">
                                        <div>
                                            <p><span class="font-semibold">Inicio:</span> {{ prestamo.fecha_prestamo_original }}</p>
                                            <p><span class="font-semibold">Próximo:</span> {{ prestamo.fecha_proximo_pago }}</p>
                                        </div>
                                        <div :class="themes[prestamo.color].texto" v-html="themes[prestamo.color].icono"></div>
                                    </div>
                                </div>
                                
                                <div v-else class="flex flex-col items-center justify-center h-20 space-y-2 text-center">
                                    <div class="font-mono text-xs font-bold text-white px-2 py-1 rounded-full" :class="themes[prestamo.color].avatarBg">
                                        {{ prestamo.codigo }}
                                    </div>
                                    <div class="font-semibold text-sm text-gray-300">
                                        {{ formatCurrency(prestamo.monto) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Estilos de scrollbar genéricos */
.custom-scrollbar::-webkit-scrollbar { height: 8px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #1f2937; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #4b5563; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #6b7280; }
.custom-scrollbar-vertical::-webkit-scrollbar { width: 6px; }
.custom-scrollbar-vertical::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-vertical::-webkit-scrollbar-thumb { background-color: #4b5563; border-radius: 20px; }
</style>