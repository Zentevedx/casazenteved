<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    reporteAgrupado: Array,
    indicadores: Object,
    estadoFiltro: String,
});

const estadoSeleccionado = ref(props.estadoFiltro);
// Control de meses desplegados
const mesesDesplegados = ref({}); 
// Control de préstamos desplegados individualmente
const prestamosDesplegados = ref({});

const toggleMes = (mesAnio) => {
    mesesDesplegados.value[mesAnio] = !mesesDesplegados.value[mesAnio];
};

const togglePrestamo = (id) => {
    prestamosDesplegados.value[id] = !prestamosDesplegados.value[id];
};

const aplicarFiltro = () => {
    router.get(route('dashboard'), { estado: estadoSeleccionado.value }, { 
        preserveState: true, 
        preserveScroll: true 
    });
};

const formatCurrency = (value) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(value);

const getAvatar = (name) => name ? name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() : '??';

// Generador de color consistente basado en el nombre (Material Colors)
const getAvatarColor = (name) => {
    if (!name) return 'bg-gray-700 text-gray-300';
    const colors = [
        'bg-red-200 text-red-900', 'bg-pink-200 text-pink-900', 'bg-purple-200 text-purple-900',
        'bg-indigo-200 text-indigo-900', 'bg-blue-200 text-blue-900', 'bg-cyan-200 text-cyan-900',
        'bg-teal-200 text-teal-900', 'bg-emerald-200 text-emerald-900', 'bg-lime-200 text-lime-900',
        'bg-orange-200 text-orange-900', 'bg-amber-200 text-amber-900'
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return colors[Math.abs(hash) % colors.length];
};

const getStatusBadge = (estado, enMora) => {
    if (estado === 'Pagado') return { class: 'bg-emerald-100 text-emerald-800 border-emerald-200', label: 'PAGADO' };
    if (estado === 'Vencido' || enMora) return { class: 'bg-red-100 text-red-800 border-red-200', label: 'VENCIDO' };
    return { class: 'bg-indigo-100 text-indigo-800 border-indigo-200', label: 'ACTIVO' }; 
};
</script>

<template>
    <Head title="Panel Financiero MD3" />

    <AuthenticatedLayout>
        <div class="bg-[#121212] text-[#E0E0E0] min-h-screen p-4 md:p-8 space-y-8 font-sans">
            
            <div class="flex flex-col md:flex-row justify-between items-end gap-4 pb-4">
                <div>
                    <h1 class="text-4xl font-normal text-white tracking-tight">Dashboard</h1>
                    <p class="text-gray-400 mt-1 text-sm">Resumen financiero y control de cartera</p>
                </div>
                
                <div class="relative bg-[#2C2C2C] rounded-t-lg rounded-b-none border-b border-gray-500 hover:bg-[#363636] transition-colors min-w-[200px]">
                    <label class="absolute top-2 left-3 text-[10px] text-gray-400 uppercase tracking-wider">Estado</label>
                    <select v-model="estadoSeleccionado" @change="aplicarFiltro" 
                        class="w-full bg-transparent border-none text-white pt-6 pb-2 px-3 focus:ring-0 cursor-pointer text-sm font-medium">
                        <option value="Activo">Solo Activos</option>
                        <option value="Vencido">En Mora / Vencidos</option>
                        <option value="Pagado">Histórico Pagados</option>
                        <option value="Todos">Vista Global</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-[#1E1E1E] p-6 rounded-[24px] shadow-lg hover:shadow-xl transition-shadow border border-gray-800 relative overflow-hidden group">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <p class="text-gray-400 text-sm font-medium">Total Colocado</p>
                    <p class="text-3xl font-normal text-white mt-2">{{ formatCurrency(indicadores.total_prestado) }}</p>
                </div>

                <div class="bg-[#1E1E1E] p-6 rounded-[24px] shadow-lg border border-gray-800 relative overflow-hidden">
                    <div class="absolute right-0 top-0 p-4 opacity-10"><div class="w-16 h-16 bg-emerald-500 rounded-full blur-2xl"></div></div>
                    <p class="text-emerald-200 text-sm font-medium">Capital Recuperado</p>
                    <p class="text-3xl font-normal text-emerald-400 mt-2">{{ formatCurrency(indicadores.total_capital_recuperado) }}</p>
                </div>

                <div class="bg-[#1E1E1E] p-6 rounded-[24px] shadow-lg border border-gray-800 relative overflow-hidden">
                     <div class="absolute right-0 top-0 p-4 opacity-10"><div class="w-16 h-16 bg-amber-500 rounded-full blur-2xl"></div></div>
                    <p class="text-amber-200 text-sm font-medium">Ganancia (Interés)</p>
                    <p class="text-3xl font-normal text-amber-400 mt-2">{{ formatCurrency(indicadores.total_intereses_generados) }}</p>
                </div>

                <div class="bg-[#2B1818] p-6 rounded-[24px] shadow-lg border border-red-900/30">
                    <p class="text-red-200 text-sm font-medium">Riesgo / Mora</p>
                    <div class="flex items-baseline gap-2 mt-2">
                        <p class="text-3xl font-normal text-red-400">{{ indicadores.total_prestamos_en_mora }}</p>
                        <span class="text-xs text-red-300/60">préstamos</span>
                    </div>
                </div>
            </div>

            <div v-if="reporteAgrupado.length === 0" class="flex flex-col items-center justify-center py-20 bg-[#1E1E1E] rounded-[32px] border border-dashed border-gray-700">
                <p class="text-gray-500 text-lg">No se encontraron registros para este filtro.</p>
            </div>

            <div v-else class="space-y-6">
                <div v-for="mes in reporteAgrupado" :key="mes.mes_anio" class="bg-[#1E1E1E] rounded-[28px] overflow-hidden shadow-sm transition-all duration-300">
                    
                    <div @click="toggleMes(mes.mes_anio)" 
                         class="cursor-pointer px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between hover:bg-white/5 transition-colors border-b border-gray-800/50 gap-4">
                        
                        <div class="flex items-center gap-4">
                            <div class="bg-[#333] p-2 rounded-xl flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 transition-transform duration-300" 
                                     :class="{ 'rotate-180': mesesDesplegados[mes.mes_anio] }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <h2 class="text-xl text-white font-medium capitalize">{{ mes.nombre_mes }}</h2>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto sm:justify-end pl-14 sm:pl-0">
                            <div class="flex flex-col items-end px-3 py-1 bg-gray-800 rounded-xl border border-gray-700">
                                <span class="text-[10px] text-gray-500 uppercase tracking-wider">Prestado</span>
                                <span class="font-mono font-bold text-gray-300 text-xs sm:text-sm">{{ formatCurrency(mes.resumen.monto_total) }}</span>
                            </div>

                            <div class="flex flex-col items-end px-3 py-1 bg-emerald-900/20 rounded-xl border border-emerald-500/20">
                                <span class="text-[10px] text-emerald-400/70 uppercase tracking-wider">Recuperado</span>
                                <span class="font-mono font-bold text-emerald-400 text-xs sm:text-sm">{{ formatCurrency(mes.resumen.capital_recuperado) }}</span>
                            </div>

                            <div class="flex flex-col items-end px-3 py-1 bg-amber-900/20 rounded-xl border border-amber-500/20">
                                <span class="text-[10px] text-amber-400/70 uppercase tracking-wider">Ganancia</span>
                                <span class="font-mono font-bold text-amber-400 text-xs sm:text-sm">{{ formatCurrency(mes.resumen.intereses_generados) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-show="mesesDesplegados[mes.mes_anio]" class="bg-[#181818] border-t border-gray-800">
                        <div v-for="semana in mes.semanas" :key="semana.semana" class="p-2 sm:p-4">
                            
                            <div class="flex items-center gap-4 mb-4 ml-2">
                                <div class="h-[1px] bg-gray-800 flex-grow"></div>
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest bg-[#181818] px-2">
                                    Semana {{ semana.semana }} ({{ semana.rango_fechas }})
                                </span>
                                <div class="h-[1px] bg-gray-800 flex-grow"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div v-for="prestamo in semana.prestamos" :key="prestamo.id" 
                                     class="bg-[#252525] rounded-[20px] overflow-hidden border border-gray-800 hover:border-gray-600 transition-colors">
                                    
                                    <div class="p-4 cursor-pointer" @click="togglePrestamo(prestamo.id)">
                                        <div class="flex justify-between items-start gap-3">
                                            <Link 
                                                v-if="prestamo.cliente_id" 
                                                :href="route('clientes.detalle', prestamo.cliente_id)" 
                                                class="flex-shrink-0 relative group"
                                                @click.stop> 
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-transform group-hover:scale-110"
                                                     :class="getAvatarColor(prestamo.cliente_nombre)">
                                                    {{ getAvatar(prestamo.cliente_nombre) }}
                                                </div>
                                                <div class="absolute inset-0 rounded-full border border-white/10 group-hover:border-white/30"></div>
                                            </Link>
                                            
                                            <div class="flex-grow min-w-0">
                                                <div class="flex justify-between items-center">
                                                    <h3 class="text-base font-bold text-white truncate">{{ prestamo.cliente_nombre }}</h3>
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border"
                                                          :class="getStatusBadge(prestamo.estado, prestamo.esta_en_mora).class">
                                                        {{ getStatusBadge(prestamo.estado, prestamo.esta_en_mora).label }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center mt-1">
                                                    <p class="text-lg text-gray-400 font-mono tracking-wide">{{ prestamo.codigo }}</p>
                                                    <p class="text-sm font-bold text-gray-200">{{ formatCurrency(prestamo.monto) }}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="text-gray-500 mt-1">
                                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200"
                                                      :class="{ 'rotate-180': prestamosDesplegados[prestamo.id] }" viewBox="0 0 20 20" fill="currentColor">
                                                     <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                 </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-show="prestamosDesplegados[prestamo.id]" class="px-4 pb-4 pt-0 space-y-4 bg-[#222]">
                                        <hr class="border-gray-700/50 mb-3" />
                                        
                                        <div>
                                            <p class="text-[10px] text-gray-500 uppercase tracking-widest mb-2 font-bold flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                                Prenda
                                            </p>
                                            <ul class="text-xs space-y-1 text-gray-300 ml-1 border-l-2 border-gray-700 pl-2">
                                                <li v-for="(art, idx) in prestamo.articulos" :key="idx">
                                                    <span class="text-indigo-300 font-medium">{{ art.nombre }}</span> 
                                                    <span class="opacity-70"> - {{ art.detalle }}</span>
                                                </li>
                                                <li v-if="prestamo.articulos.length === 0" class="italic text-gray-500">Sin detalles</li>
                                            </ul>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 text-center text-xs">
                                            <div class="bg-emerald-900/20 p-2 rounded-lg border border-emerald-500/10">
                                                <span class="block text-emerald-400/70 text-[10px]">Capital Pagado</span>
                                                <span class="font-bold text-emerald-300">{{ formatCurrency(prestamo.capital_recuperado) }}</span>
                                            </div>
                                            <div class="bg-amber-900/20 p-2 rounded-lg border border-amber-500/10">
                                                <span class="block text-amber-400/70 text-[10px]">Interés Generado</span>
                                                <span class="font-bold text-amber-300">{{ formatCurrency(prestamo.intereses_generados) }}</span>
                                            </div>
                                        </div>

                                        <div>
                                             <p class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Últimos Pagos (Interés)</p>
                                             <div class="max-h-24 overflow-y-auto pr-1 custom-scrollbar">
                                                <div v-for="pago in prestamo.historial_intereses" :key="pago.id" class="flex justify-between text-[11px] py-1 border-b border-gray-700/50 last:border-0">
                                                    <span class="text-gray-400">{{ pago.fecha }}</span>
                                                    <span class="text-amber-200 font-mono">{{ formatCurrency(pago.monto) }}</span>
                                                </div>
                                                <div v-if="prestamo.historial_intereses.length === 0" class="text-gray-600 text-[10px] italic py-1">Sin historial</div>
                                             </div>
                                        </div>

                                        <div class="bg-[#2A2A2A] rounded-lg p-2 text-center mt-2">
                                            <p class="text-[10px] text-gray-500 uppercase">Próximo Vencimiento</p>
                                            <p class="text-xs font-bold text-white mt-0.5">{{ prestamo.fecha_proximo_pago }}</p>
                                        </div>
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

<style>
/* Personalización de scrollbar para las listas internas */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>