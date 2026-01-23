<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import dayjs from 'dayjs'
import 'dayjs/locale/es'
import Chart from 'chart.js/auto'

// Componentes
import KpiCard from '@/Components/Estadisticas/KpiCard.vue'
import RankingList from '@/Components/Estadisticas/RankingList.vue'
import ModernFilter from '@/Components/Estadisticas/ModernFilter.vue'
import TablaCaja from '@/Components/Estadisticas/TablaCaja.vue'
import { TrophyIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/solid';

dayjs.locale('es')

const props = defineProps({
  kpis: Object,
  ranking: Object,
  graficos: Object,
  reporteCaja: Object,
  filtros: Object
})

const selectedModo = ref(props.filtros.modo)
const selectedFecha = ref(props.filtros.fecha)

watch([selectedModo, selectedFecha], () => {
  router.get(route('estadisticas'), {
    modo: selectedModo.value,
    fecha: selectedFecha.value
  }, { preserveScroll: true, preserveState: true, replace: true })
})

const opcionesFechas = computed(() => {
  const opciones = []
  const hoy = dayjs()
  if (selectedModo.value === 'dia') {
    for (let i = 0; i < 30; i++) opciones.push({ value: hoy.subtract(i, 'day').format('YYYY-MM-DD'), label: hoy.subtract(i, 'day').format('dddd D [de] MMMM') })
  } else if (selectedModo.value === 'semana') {
    for (let i = 0; i < 12; i++) {
      const b = hoy.subtract(i, 'week').startOf('week'); const e = b.endOf('week');
      opciones.push({ value: b.format('YYYY-MM-DD'), label: `Semana ${b.format('W')} (${b.format('D MMM')} - ${e.format('D MMM')})` })
    }
  } else {
    for (let i = 0; i < 12; i++) opciones.push({ value: hoy.subtract(i, 'month').startOf('month').format('YYYY-MM-DD'), label: hoy.subtract(i, 'month').format('MMMM YYYY') })
  }
  return opciones
})

// --- Lógica de Gráficos (Chart.js) ---
let charts = {}

const destroyCharts = () => {
    Object.values(charts).forEach(c => c && c.destroy())
}

const initCharts = () => {
    destroyCharts();

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    };

    // 1. Flujo Neto (Bar Chart)
    const ctxFlujo = document.getElementById('chartFlujo');
    if(ctxFlujo) {
        charts.flujo = new Chart(ctxFlujo, {
            type: 'bar',
            data: {
                labels: props.graficos.fechas,
                datasets: [{
                    label: 'Flujo Neto',
                    data: props.graficos.flujo_neto,
                    backgroundColor: props.graficos.flujo_neto.map(v => v >= 0 ? '#10b981' : '#ef4444'),
                    borderRadius: 4,
                    barThickness: 8
                }]
            },
            options: { ...commonOptions, scales: { x: { grid: { display: false }, ticks: { color: '#6b7280', font: {size: 10} } }, y: { display: false } } }
        });
    }

    // 2. Cartera (Doughnut)
    const ctxCartera = document.getElementById('chartCartera');
    if(ctxCartera) {
        const labelsC = props.graficos.distribucion_cartera.map(d => d.estado);
        charts.cartera = new Chart(ctxCartera, {
            type: 'doughnut',
            data: {
                labels: labelsC,
                datasets: [{
                    data: props.graficos.distribucion_cartera.map(d => d.count),
                    backgroundColor: labelsC.map(l => l==='Activo'?'#6366f1':(l==='Vencido'?'#ef4444':'#10b981')),
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: { cutout: '85%', plugins: { legend: { display: false } } }
        });
    }

    // 3. Inventario (Doughnut)
    const ctxInventario = document.getElementById('chartInventario');
    if(ctxInventario) {
        const labelsI = props.graficos.estado_inventario.map(d => d.estado);
        charts.inventario = new Chart(ctxInventario, {
            type: 'doughnut',
            data: {
                labels: labelsI,
                datasets: [{
                    data: props.graficos.estado_inventario.map(d => d.count),
                    backgroundColor: labelsI.map(l => l==='Prendado'?'#f59e0b':(l==='Vencido'?'#ef4444':'#3b82f6')),
                    borderWidth: 0,
                     hoverOffset: 10
                }]
            },
            options: { cutout: '85%', plugins: { legend: { display: false } } }
        });
    }
}

onMounted(initCharts)
watch(() => props.graficos, initCharts, { deep: true })

// Transform Data for Ranking Components
const topClientsItems = computed(() => props.ranking.vip.map(c => ({
    nombre: c.nombre,
    secondary_text: `${c.num_prestamos} préstamos históricos`,
    value: c.total_aportado,
    value_label: 'Interés Generado'
})));

const riskClientsItems = computed(() => props.ranking.riesgo.map(c => ({
    nombre: c.nombre,
    secondary_text: `${c.cantidad} préstamos activos`,
    value: c.deuda,
    value_label: 'Deuda Total'
})));

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);

// Cálculos simples para leyendas visuales
const totalCartera = computed(() => props.graficos.distribucion_cartera.reduce((acc, curr) => acc + curr.count, 0));
const totalInventario = computed(() => props.graficos.estado_inventario.reduce((acc, curr) => acc + curr.count, 0));
</script>

<template>
    <Head title="Business Intelligence" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#0f0f0f] text-gray-300 pb-20 font-sans selection:bg-indigo-500 selection:text-white">
            
            <!-- Sticky Modern Header -->
            <div class="sticky top-0 z-40 bg-[#0f0f0f]/95 backdrop-blur-xl border-b border-gray-800 transition-all duration-300 shadow-2xl">
                <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex flex-col xl:flex-row justify-between items-center gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-white tracking-tight">Business Intelligence</h1>
                                <p class="text-xs text-gray-500 font-medium">Panel de Control Estratégico</p>
                            </div>
                        </div>

                        <div class="w-full xl:w-auto">
                            <ModernFilter 
                                :selectedModo="selectedModo"
                                :selectedFecha="selectedFecha"
                                :opcionesFechas="opcionesFechas"
                                :rangoTexto="filtros.rango_texto"
                                @update:modo="selectedModo = $event"
                                @update:fecha="selectedFecha = $event"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
                
                <!-- 1. KPI Grid (Top Row) - Expanded to 5 cols for better density -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                    <KpiCard title="Capital Colocado" :value="formatCurrency(kpis.colocado.valor)" :trend="kpis.colocado.variacion" colorTheme="indigo" />
                    <KpiCard title="Ganancia Neta" :value="formatCurrency(kpis.ingresos.valor)" :trend="kpis.ingresos.variacion" colorTheme="emerald" />
                    <KpiCard title="Valor en Custodia" :value="formatCurrency(kpis.inventario.valor_total)" :subValue="`${kpis.inventario.items} artículos`" colorTheme="amber" />
                    <KpiCard title="Ticket Promedio" :value="formatCurrency(kpis.ticket_promedio)" subValue="Por operación" colorTheme="purple" />
                    
                    <!-- Widget de Eficiencia (Nuevo) -->
                    <div class="bg-[#1a1a1a] p-5 rounded-2xl border border-gray-800 shadow-xl flex flex-col justify-between group hover:border-blue-500/30 transition-colors">
                        <div>
                             <p class="text-[10px] uppercase font-bold tracking-widest mb-1 text-blue-400">Eficiencia Operativa</p>
                             <h3 class="text-3xl font-bold text-white tracking-tight">{{ kpis.operaciones }}</h3>
                             <p class="text-xs text-gray-500 mt-1">Operaciones totales</p>
                        </div>
                        <div class="w-full bg-gray-800 h-1.5 rounded-full mt-4 overflow-hidden">
                             <div class="bg-blue-500 h-full rounded-full" style="width: 75%"></div> <!-- Dummy percentage for visual -->
                        </div>
                    </div>
                </div>

                <!-- 2. Mid Section: Trend Charts & Distributions -->
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                    
                    <!-- Left: Cash Flow Trend (Big) - Takes 8 cols -->
                    <div class="xl:col-span-8 bg-[#1a1a1a] rounded-2xl border border-gray-800 p-6 shadow-lg min-h-[400px] flex flex-col">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-sm font-bold text-gray-200 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 7.707 7.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l5-5z" clip-rule="evenodd" /></svg>
                                Flujo de Caja Neto
                            </h3>
                            <span class="text-xs text-gray-600 bg-gray-900 px-3 py-1 rounded-full border border-gray-800">Diario</span>
                        </div>
                        <div class="flex-grow relative w-full">
                            <canvas id="chartFlujo"></canvas>
                        </div>
                    </div>

                    <!-- Right: Distributions (Small) - Takes 4 cols -->
                    <div class="xl:col-span-4 flex flex-col gap-6">
                        
                        <!-- Cartera Doughnut -->
                        <div class="bg-[#1a1a1a] rounded-2xl border border-gray-800 p-6 shadow-lg flex-1 flex flex-col relative overflow-hidden">
                            <h3 class="text-sm font-bold text-gray-200 mb-2 z-10">Salud de Cartera ({{ totalCartera }} préstamos)</h3>
                             <!-- Custom Legend Overlay -->
                            <div class="flex gap-4 mb-4 text-[10px] font-bold z-10">
                                <span class="flex items-center gap-1.5 text-indigo-300"><span class="w-2 h-2 rounded-full bg-indigo-500"></span>Activo</span>
                                <span class="flex items-center gap-1.5 text-red-300"><span class="w-2 h-2 rounded-full bg-red-500"></span>Vencido</span>
                                <span class="flex items-center gap-1.5 text-emerald-300"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Pagado</span>
                            </div>
                            <div class="flex-grow relative flex items-center justify-center">
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-white">{{ kpis.colocado.valor >= 1000000 ? (kpis.colocado.valor/1000000).toFixed(1)+'M' : (kpis.colocado.valor/1000).toFixed(0)+'k' }}</p>
                                        <p class="text-[10px] text-gray-500 uppercase tracking-widest">Global</p>
                                    </div>
                                </div>
                                <canvas id="chartCartera" class="max-h-[160px]"></canvas>
                            </div>
                        </div>

                         <!-- Inventario Doughnut -->
                        <div class="bg-[#1a1a1a] rounded-2xl border border-gray-800 p-6 shadow-lg flex-1 flex flex-col relative overflow-hidden">
                            <h3 class="text-sm font-bold text-gray-200 mb-2 z-10">Inventario ({{ totalInventario }} artículos)</h3>
                             <!-- Custom Legend Overlay -->
                            <div class="flex gap-4 mb-4 text-[10px] font-bold z-10">
                                <span class="flex items-center gap-1.5 text-amber-300"><span class="w-2 h-2 rounded-full bg-amber-500"></span>Prendado</span>
                                <span class="flex items-center gap-1.5 text-red-300"><span class="w-2 h-2 rounded-full bg-red-500"></span>Vencido</span>
                                <span class="flex items-center gap-1.5 text-blue-300"><span class="w-2 h-2 rounded-full bg-blue-500"></span>En Venta</span>
                            </div>
                             <div class="flex-grow relative flex items-center justify-center">
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-white">{{ parseInt((kpis.inventario.valor_total / kpis.colocado.valor * 100) || 0) }}%</p>
                                        <p class="text-[10px] text-gray-500 uppercase tracking-widest">Cobertura</p>
                                    </div>
                                </div>
                                <canvas id="chartInventario" class="max-h-[160px]"></canvas>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 3. Rankings & Reports Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    
                    <!-- Top Clients -->
                    <RankingList 
                        title="Clientes VIP (Mejores Pagadores)" 
                        :items="topClientsItems" 
                        :icon="TrophyIcon"
                        colorTheme="emerald" 
                    />

                    <!-- Risk Clients -->
                    <RankingList 
                        title="Alerta de Riesgo (Mayor Deuda)" 
                        :items="riskClientsItems" 
                        :icon="ExclamationTriangleIcon"
                        colorTheme="red" 
                    />

                    <!-- Detailed Table Helper (Takes full width on tablet / 1 col on desktop) -->
                    <div class="lg:col-span-2 xl:col-span-1 bg-[#1a1a1a] rounded-2xl border border-gray-800 shadow-lg overflow-hidden flex flex-col h-full">
                        <div class="p-4 border-b border-gray-800 bg-gray-900/50">
                            <h3 class="text-sm font-bold text-gray-300">Últimos Movimientos de Caja</h3>
                        </div>
                        <div class="flex-grow overflow-hidden">
                             <TablaCaja :reporteCaja="props.reporteCaja" :compact="true" />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>