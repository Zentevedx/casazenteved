<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import KpiCard from '@/Components/Estadisticas/KpiCard.vue';
import MonthGroup from '@/Components/Dashboard/MonthGroup.vue';
import ChartComponent from '@/Components/Dashboard/ChartComponent.vue';
import { FunnelIcon, PlusIcon, UserPlusIcon, ExclamationTriangleIcon, ClockIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    reporteAgrupado: Array,
    indicadores: Object,
    estadoFiltro: String,
    alertas: Object,
    topDeudores: Array
});

const estadoSeleccionado = ref(props.estadoFiltro);

const filtros = [
    { id: 'Todos', label: 'Global' },
    { id: 'Activo', label: 'Activos' },
    { id: 'Vencido', label: 'Mora' },
    { id: 'Pagado', label: 'Pagados' },
];

const aplicarFiltro = (nuevoEstado) => {
    estadoSeleccionado.value = nuevoEstado;
    router.get(route('dashboard'), { estado: nuevoEstado }, { 
        preserveState: true, 
        preserveScroll: true 
    });
};

const formatCurrency = (value) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(value);

const chartData = computed(() => {
    const reversedReport = [...props.reporteAgrupado].reverse();
    return {
        labels: reversedReport.map(m => m.nombre_mes),
        datasets: [
            {
                label: 'Préstamos',
                data: reversedReport.map(m => m.resumen.monto_total),
                borderColor: '#818cf8', // indigo-400
                backgroundColor: 'rgba(129, 140, 248, 0.4)',
                tension: 0.4
            },
            {
                label: 'Recuperado',
                data: reversedReport.map(m => m.resumen.capital_recuperado + m.resumen.intereses_generados),
                borderColor: '#34d399', // emerald-400
                backgroundColor: 'rgba(52, 211, 153, 0.4)',
                tension: 0.4
            }
        ]
    };
});
</script>

<template>
    <Head title="Panel Financiero" />

    <AuthenticatedLayout>
        <div class="min-h-screen pb-20 font-sans selection:bg-indigo-500 selection:text-white">
            
            <!-- Sticky Modern Header -->
            <div class="sticky top-0 z-40 bg-white/80 dark:bg-dark-bg/95 backdrop-blur-xl border-b border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl transition-all duration-300">
                <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        
                        <!-- Title Zone -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Dashboard General</h1>
                                <p class="text-xs text-gray-500 font-medium">Control Operativo</p>
                            </div>
                        </div>

                        <!-- Modern Tabs Filter -->
                        <div class="flex bg-gray-100 dark:bg-black/40 p-1 rounded-xl overflow-x-auto max-w-full">
                            <button 
                                v-for="filtro in filtros" 
                                :key="filtro.id"
                                @click="aplicarFiltro(filtro.id)"
                                :class="['px-5 py-2 rounded-lg text-xs font-bold transition-all duration-300 flex-shrink-0', 
                                    estadoSeleccionado === filtro.id 
                                    ? 'bg-white dark:bg-indigo-600 text-indigo-600 dark:text-white shadow-sm dark:shadow-md' 
                                    : 'text-gray-500 hover:text-gray-900 dark:hover:text-gray-300 hover:bg-white/50 dark:hover:bg-white/5']"
                            >
                                {{ filtro.label }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">



                 <!-- KPI Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <KpiCard title="Total Colocado" :value="formatCurrency(indicadores.total_prestado)" colorTheme="indigo" />
                    <KpiCard title="Recuperado" :value="formatCurrency(indicadores.total_capital_recuperado)" colorTheme="emerald" />
                    <KpiCard title="Ganancia Interés" :value="formatCurrency(indicadores.total_intereses_generados)" colorTheme="amber" />
                    <KpiCard title="Riesgo / Mora" :value="indicadores.total_prestamos_en_mora" subValue="préstamos retrasados" colorTheme="red" />
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    
                    <!-- LEFT COLUMN: LOAN LIST (Takes 2/3 width) -->
                    <div class="xl:col-span-2 space-y-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                Cronograma de Actividad
                                <span class="bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[10px] px-2 py-0.5 rounded-full border border-gray-300 dark:border-gray-700">{{ reporteAgrupado.length }} periodos</span>
                            </h3>
                        </div>
                        
                        <div v-if="reporteAgrupado.length === 0" class="flex flex-col items-center justify-center py-24 bg-white dark:bg-[#1a1a1a] rounded-[24px] border border-dashed border-gray-300 dark:border-gray-800 group">
                            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800/50 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <FunnelIcon class="w-8 h-8 text-gray-400 dark:text-gray-600" />
                            </div>
                            <p class="text-gray-500 font-medium">No se encontraron registros</p>
                            <p class="text-xs text-gray-400 dark:text-gray-600 mt-1">Prueba cambiando el filtro de estado</p>
                        </div>

                        <div v-else class="space-y-6">
                            <MonthGroup 
                                v-for="(mes, index) in reporteAgrupado" 
                                :key="mes.mes_anio" 
                                :mes="mes"
                                :is-initially-expanded="index === 0" 
                            />
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: ANALYTICS & ACTIONS -->
                    <div class="space-y-6">
                        
                        <!-- Quick Actions -->
                        <div class="grid grid-cols-2 gap-4">
                            <Link :href="route('prestamos.create')" class="group relative overflow-hidden bg-indigo-600 hover:bg-indigo-700 p-4 rounded-2xl transition-all shadow-lg hover:shadow-indigo-500/25 flex flex-col items-center justify-center gap-2 text-center h-28 border border-white/10">
                                <PlusIcon class="w-8 h-8 text-white/90 group-hover:scale-110 transition-transform" />
                                <span class="text-sm font-bold text-white">Nuevo Préstamo</span>
                            </Link>
                             <Link :href="route('clientes.create')" class="group relative overflow-hidden bg-white dark:bg-[#1a1a1a] hover:bg-gray-50 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 p-4 rounded-2xl transition-all flex flex-col items-center justify-center gap-2 text-center h-28">
                                <UserPlusIcon class="w-8 h-8 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-white group-hover:scale-110 transition-transform" />
                                <span class="text-sm font-bold text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-white">Nuevo Cliente</span>
                            </Link>
                        </div>

                      <!-- Chart Widget -->
                        <div class="bg-white dark:bg-[#1a1a1a] p-6 rounded-[24px] shadow-sm dark:shadow-lg border border-gray-200 dark:border-gray-800 min-h-[350px] flex flex-col">
                            <h4 class="text-xs font-bold text-gray-500 mb-6 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                Flujo de Caja (6 meses)
                            </h4>
                            <div class="flex-grow">
                                <ChartComponent :data="chartData" type="line" />
                            </div>
                        </div>

                        <!-- Top Debtors (NEW) -->
                        <div v-if="topDeudores.length > 0" class="bg-white dark:bg-[#1a1a1a] p-6 rounded-[24px] shadow-sm dark:shadow-lg border border-gray-200 dark:border-gray-800">
                             <h4 class="text-xs font-bold text-gray-500 mb-4 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                Mayores Deudores
                            </h4>
                            <div class="space-y-4">
                                <div v-for="cliente in topDeudores" :key="cliente.id" class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 last:border-0 last:pb-0">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                             <img v-if="cliente.foto_url" :src="cliente.foto_url" class="w-full h-full object-cover">
                                             <div v-else class="w-full h-full flex items-center justify-center text-gray-400 font-bold text-xs">
                                                 {{ cliente.nombre.charAt(0) }}
                                             </div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ cliente.nombre }}</p>
                                            <p class="text-[10px] text-gray-500">{{ cliente.cantidad }} préstamos activos</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ formatCurrency(cliente.total_deuda) }}</p>
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