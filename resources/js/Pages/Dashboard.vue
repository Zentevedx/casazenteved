<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    reporteAgrupado: Array,
    indicadores: Object,
    estadoFiltro: String,
});

const estadoSeleccionado = ref(props.estadoFiltro);

const aplicarFiltro = () => {
    router.get(route('dashboard'), { estado: estadoSeleccionado.value }, { 
        preserveState: true, 
        preserveScroll: true 
    });
};

const formatCurrency = (value) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(value);

const getAvatar = (name) => name ? name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() : '??';

const getStatusColor = (estado, enMora) => {
    if (estado === 'Pagado') return 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
    if (estado === 'Vencido' || enMora) return 'bg-red-500/20 text-red-300 border-red-500/30';
    return 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30'; 
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="bg-gray-900 text-white min-h-screen p-6 space-y-8">
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 border-b border-gray-800 pb-6">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-white">Panel Financiero</h1>
                    <p class="text-gray-400 text-sm mt-1">Gestión automatizada de préstamos</p>
                </div>
                
                <div class="flex items-center bg-gray-800 p-2 rounded-lg border border-gray-700">
                    <span class="text-gray-400 text-sm mr-3 font-medium">Ver Estado:</span>
                    <select v-model="estadoSeleccionado" @change="aplicarFiltro" class="bg-gray-700 text-white text-sm rounded border-none focus:ring-2 focus:ring-indigo-500 py-1 px-4 cursor-pointer">
                        <option value="Activo">Solo Activos</option>
                        <option value="Vencido">Vencidos (Mora > 3 meses)</option>
                        <option value="Pagado">Finalizados / Pagados</option>
                        <option value="Todos">Todos los Registros</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gray-800 p-5 rounded-xl border border-gray-700 shadow-lg">
                    <p class="text-gray-400 text-xs uppercase tracking-widest">Total Prestado</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ formatCurrency(indicadores.total_prestado) }}</p>
                </div>
                <div class="bg-gray-800 p-5 rounded-xl border border-gray-700 shadow-lg">
                    <p class="text-emerald-400 text-xs uppercase tracking-widest">Capital Recuperado</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ formatCurrency(indicadores.total_capital_recuperado) }}</p>
                </div>
                <div class="bg-gray-800 p-5 rounded-xl border border-gray-700 shadow-lg">
                    <p class="text-amber-400 text-xs uppercase tracking-widest">Intereses Ganados</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ formatCurrency(indicadores.total_intereses_generados) }}</p>
                </div>
                <div class="bg-gray-800 p-5 rounded-xl border border-gray-700 shadow-lg">
                    <p class="text-red-400 text-xs uppercase tracking-widest">Atención / Mora</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ indicadores.total_prestamos_en_mora }}</p>
                </div>
            </div>

            <div v-if="reporteAgrupado.length === 0" class="text-center py-12 bg-gray-800/50 rounded-xl border border-dashed border-gray-700">
                <p class="text-gray-500 text-lg">No hay datos para mostrar en este filtro.</p>
            </div>

            <div v-else class="space-y-8">
                <div v-for="mes in reporteAgrupado" :key="mes.mes_anio">
                    
                    <div class="sticky top-16 z-20 bg-gray-600/95 backdrop-blur border-l-4 border-indigo-500 pl-4 py-3 mb-4 flex flex-wrap justify-between items-center gap-4 shadow-md">
                        <h2 class="text-xl font-bold text-white capitalize">{{ mes.nombre_mes }}</h2>
                        
                        <div class="flex flex-wrap gap-2 sm:gap-4">
                            <div class="flex flex-col items-end px-3 py-1 bg-gray-800 rounded border border-gray-700">
                                <span class="text-[10px] text-gray-500 uppercase tracking-wider">Prestado</span>
                                <span class="font-mono font-bold text-indigo-300 text-sm">{{ formatCurrency(mes.resumen.monto_total) }}</span>
                            </div>
                            
                            <div class="flex flex-col items-end px-3 py-1 bg-gray-800 rounded border border-gray-700">
                                <span class="text-[10px] text-gray-500 uppercase tracking-wider">Recuperado</span>
                                <span class="font-mono font-bold text-emerald-400 text-sm">{{ formatCurrency(mes.resumen.capital_recuperado) }}</span>
                            </div>

                            <div class="flex flex-col items-end px-3 py-1 bg-gray-800 rounded border border-gray-700">
                                <span class="text-[10px] text-gray-500 uppercase tracking-wider">Intereses</span>
                                <span class="font-mono font-bold text-amber-400 text-sm">{{ formatCurrency(mes.resumen.intereses_generados) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-6">
                        <div v-for="semana in mes.semanas" :key="semana.semana" class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
                            <div class="px-4 py-2 bg-gray-750 border-b border-gray-700 flex justify-between items-center bg-gray-700/30">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ semana.rango_fechas }}</span>
                                <span class="text-xs text-gray-500">Semana {{ semana.semana }}</span>
                            </div>

                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
                                <div v-for="prestamo in semana.prestamos" :key="prestamo.id" 
                                     class="relative bg-gray-900 rounded p-5 border transition-colors duration-300 hover:border-gray-500 flex flex-col h-full"
                                     :class="prestamo.esta_en_mora ? 'border-red-500/40 shadow-[0_0_15px_rgba(220,38,38,0.1)]' : 'border-gray-700'">
                                    
                                    <div class="absolute top-3 right-3 text-[10px] font-bold px-2 py-0.5 rounded border z-10"
                                         :class="getStatusColor(prestamo.estado, prestamo.esta_en_mora)">
                                        {{ prestamo.estado === 'Activo' && prestamo.esta_en_mora ? 'MORA' : prestamo.estado.toUpperCase() }}
                                    </div>

                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-sm bg-gray-700 text-gray-300">
                                            {{ getAvatar(prestamo.cliente_nombre) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-white leading-tight truncate">{{ prestamo.cliente_nombre }}</p>
                                            <p class="text-[30px] text-gray-500 font-mono">{{ prestamo.codigo }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 mb-4 text-center">
                                        <div class="bg-gray-800/50 rounded p-2">
                                            <span class="block text-[10px] text-gray-500 uppercase">Préstamo</span>
                                            <span class="text-sm font-bold text-white">{{ formatCurrency(prestamo.monto) }}</span>
                                        </div>
                                        <div class="bg-gray-800/50 rounded p-2">
                                            <span class="block text-[10px] text-gray-500 uppercase">Recuperado</span>
                                            <span class="text-sm font-bold text-emerald-400">{{ formatCurrency(prestamo.capital_recuperado) }}</span>
                                        </div>
                                    </div>

                                    <div class="mb-4 bg-gray-800/30 rounded p-2 text-xs border border-gray-700/50">
                                        <p class="text-gray-500 font-bold mb-1 flex items-center gap-1">
                                            <span>📦</span> Artículos / Prenda
                                        </p>
                                        <ul class="list-disc list-inside text-gray-300 space-y-0.5">
                                            <li v-for="(art, idx) in prestamo.articulos" :key="idx" class="truncate">
                                                <span class="font-medium text-indigo-200">{{ art.nombre }}</span>
                                                <span class="text-gray-500"> - {{ art.detalle }}</span>
                                            </li>
                                        </ul>
                                        <p v-if="prestamo.articulos.length === 0" class="text-gray-600 italic">Sin artículos registrados</p>
                                    </div>

                                    <div class="flex-grow mb-4">
                                        <p class="text-[10px] text-gray-500 uppercase font-bold mb-1 flex items-center justify-between">
                                            <span>Historial Intereses</span>
                                            <span class="text-amber-400">Total: {{ formatCurrency(prestamo.intereses_generados) }}</span>
                                        </p>
                                        <div class="bg-gray-800/50 rounded border border-gray-700/50 max-h-24 overflow-y-auto custom-scrollbar p-1">
                                            <div v-if="prestamo.historial_intereses.length > 0" class="space-y-1">
                                                <div v-for="pago in prestamo.historial_intereses" :key="pago.id" 
                                                     class="flex justify-between text-[11px] px-2 py-1 hover:bg-gray-700 rounded">
                                                    <span class="text-gray-400">{{ pago.fecha }}</span>
                                                    <span class="text-amber-200 font-mono">{{ formatCurrency(pago.monto) }}</span>
                                                </div>
                                            </div>
                                            <div v-else class="text-center py-2 text-gray-600 text-[10px] italic">
                                                No hay pagos de interés registrados
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-auto flex justify-between items-center text-xs pt-3 border-t border-gray-800">
                                        <span class="text-gray-500">Próximo Pago:</span>
                                        <span class="font-medium px-2 py-1 rounded bg-gray-800 text-gray-200">
                                            {{ prestamo.fecha_proximo_pago }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>