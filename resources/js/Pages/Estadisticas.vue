<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import dayjs from 'dayjs'
import 'dayjs/locale/es'
import Chart from 'chart.js/auto'
import TablaCaja from '@/Components/Estadisticas/TablaCaja.vue'

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

// Generador de fechas (Mismo de antes)
const opcionesFechas = computed(() => {
  const opciones = []
  const hoy = dayjs()
  if (selectedModo.value === 'dia') {
    for (let i = 0; i < 15; i++) opciones.push({ value: hoy.subtract(i, 'day').format('YYYY-MM-DD'), label: hoy.subtract(i, 'day').format('dddd D MMM') })
  } else if (selectedModo.value === 'semana') {
    for (let i = 0; i < 12; i++) {
      const b = hoy.subtract(i, 'week').startOf('week'); const e = b.endOf('week');
      opciones.push({ value: b.format('YYYY-MM-DD'), label: `Sem ${b.format('W')} (${b.format('D MMM')} - ${e.format('D MMM')})` })
    }
  } else {
    for (let i = 0; i < 12; i++) opciones.push({ value: hoy.subtract(i, 'month').startOf('month').format('YYYY-MM-DD'), label: hoy.subtract(i, 'month').format('MMMM YYYY') })
  }
  return opciones
})

// Gráfico de Flujo
let chartFlujo = null;
const renderGraficoFlujo = () => {
    const ctx = document.getElementById('chartFlujo');
    if(!ctx) return;
    if(chartFlujo) chartFlujo.destroy();
    
    chartFlujo = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: props.graficos.fechas,
            datasets: [{
                label: 'Flujo Neto (Bs)',
                data: props.graficos.flujo_neto,
                backgroundColor: props.graficos.flujo_neto.map(v => v >= 0 ? '#10b981' : '#ef4444'),
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#374151' }, ticks: { color: '#9ca3af' } },
                x: { grid: { display: false }, ticks: { color: '#9ca3af' } }
            }
        }
    });
}

onMounted(() => { renderGraficoFlujo() })
watch(() => props.graficos, () => { renderGraficoFlujo() }, { deep: true })

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);
</script>

<template>
    <Head title="Estadísticas Pro" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-950 text-white pb-20">
            
            <div class="sticky top-0 z-30 bg-gray-900/90 backdrop-blur border-b border-gray-800 py-4 px-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Reporte Gerencial</h1>
                    <p class="text-xs text-indigo-400 font-medium">{{ filtros.rango_texto }}</p>
                </div>
                <div class="flex gap-2">
                    <select v-model="selectedModo" class="bg-gray-800 border-gray-700 text-sm rounded-lg text-white focus:ring-indigo-500">
                        <option value="dia">Diario</option>
                        <option value="semana">Semanal</option>
                        <option value="mes">Mensual</option>
                    </select>
                    <select v-model="selectedFecha" class="bg-gray-800 border-gray-700 text-sm rounded-lg text-white w-48 focus:ring-indigo-500">
                        <option v-for="op in opcionesFechas" :key="op.value" :value="op.value">{{ op.label }}</option>
                    </select>
                </div>
            </div>

            <div class="p-6 max-w-7xl mx-auto space-y-8">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-gray-900 p-5 rounded-xl border border-gray-800 shadow-lg relative overflow-hidden group">
                        <div class="absolute -right-6 -top-6 bg-indigo-500/10 w-24 h-24 rounded-full group-hover:bg-indigo-500/20 transition-colors"></div>
                        <p class="text-gray-400 text-[10px] uppercase font-bold tracking-wider">Capital Colocado</p>
                        <h3 class="text-2xl font-bold text-white mt-1">{{ formatCurrency(kpis.colocado.valor) }}</h3>
                        <div class="mt-2 text-xs font-medium" :class="kpis.colocado.variacion >= 0 ? 'text-emerald-400' : 'text-red-400'">
                            {{ kpis.colocado.variacion > 0 ? '+' : '' }}{{ kpis.colocado.variacion.toFixed(1) }}% <span class="text-gray-500 font-normal">vs periodo anterior</span>
                        </div>
                    </div>

                    <div class="bg-gray-900 p-5 rounded-xl border border-gray-800 shadow-lg relative overflow-hidden group">
                        <div class="absolute -right-6 -top-6 bg-emerald-500/10 w-24 h-24 rounded-full group-hover:bg-emerald-500/20 transition-colors"></div>
                        <p class="text-gray-400 text-[10px] uppercase font-bold tracking-wider">Ganancia (Intereses)</p>
                        <h3 class="text-2xl font-bold text-white mt-1">{{ formatCurrency(kpis.ingresos.valor) }}</h3>
                        <div class="mt-2 text-xs font-medium" :class="kpis.ingresos.variacion >= 0 ? 'text-emerald-400' : 'text-red-400'">
                            {{ kpis.ingresos.variacion > 0 ? '+' : '' }}{{ kpis.ingresos.variacion.toFixed(1) }}% <span class="text-gray-500 font-normal">vs periodo anterior</span>
                        </div>
                    </div>

                    <div class="bg-gray-900 p-5 rounded-xl border border-gray-800 shadow-lg relative overflow-hidden group">
                        <div class="absolute -right-6 -top-6 bg-amber-500/10 w-24 h-24 rounded-full group-hover:bg-amber-500/20 transition-colors"></div>
                        <p class="text-gray-400 text-[10px] uppercase font-bold tracking-wider">Valor en Prendas</p>
                        <h3 class="text-2xl font-bold text-white mt-1">{{ formatCurrency(kpis.inventario.valor_total) }}</h3>
                        <div class="mt-2 text-xs text-amber-400 font-medium flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" /><path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>
                            {{ kpis.inventario.items }} Artículos en Custodia
                        </div>
                    </div>

                    <div class="bg-gray-900 p-5 rounded-xl border border-gray-800 shadow-lg relative overflow-hidden group">
                         <div class="absolute -right-6 -top-6 bg-blue-500/10 w-24 h-24 rounded-full group-hover:bg-blue-500/20 transition-colors"></div>
                        <p class="text-gray-400 text-[10px] uppercase font-bold tracking-wider">Nuevos Préstamos</p>
                        <h3 class="text-2xl font-bold text-white mt-1">{{ kpis.operaciones }}</h3>
                        <p class="text-xs text-gray-500 mt-2">Tickets generados este periodo</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5 shadow-lg">
                        <h3 class="text-sm font-bold text-emerald-400 flex items-center gap-2 mb-4">
                            🏆 Top Clientes (Mejores Pagadores)
                        </h3>
                        <div class="space-y-3">
                            <div v-for="(cliente, idx) in ranking.vip" :key="idx" class="flex items-center justify-between p-3 bg-gray-800/50 rounded hover:bg-gray-800 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-900/50 text-emerald-400 flex items-center justify-center text-xs font-bold border border-emerald-500/30">
                                        {{ idx + 1 }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-200">{{ cliente.nombre }}</p>
                                        <p class="text-[10px] text-gray-500">{{ cliente.num_prestamos }} préstamos hist.</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-mono font-bold text-emerald-400">+{{ formatCurrency(cliente.total_aportado) }}</p>
                                    <p class="text-[10px] text-gray-500">Intereses gen.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5 shadow-lg">
                        <h3 class="text-sm font-bold text-red-400 flex items-center gap-2 mb-4">
                            🚨 Top Riesgo (Mayor Deuda Activa)
                        </h3>
                        <div class="space-y-3">
                            <div v-for="(cliente, idx) in ranking.riesgo" :key="idx" class="flex items-center justify-between p-3 bg-gray-800/50 rounded hover:bg-gray-800 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-red-900/50 text-red-400 flex items-center justify-center text-xs font-bold border border-red-500/30">
                                        {{ idx + 1 }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-200">{{ cliente.nombre }}</p>
                                        <p class="text-[10px] text-gray-500">{{ cliente.cantidad }} préstamos activos</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-mono font-bold text-red-400">{{ formatCurrency(cliente.deuda) }}</p>
                                    <p class="text-[10px] text-gray-500">Deuda total</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-3 bg-gray-900 rounded-xl border border-gray-800 p-5 shadow-lg">
                        <h3 class="text-sm font-bold text-white mb-4">Tendencia de Flujo de Caja (Diario)</h3>
                        <div class="h-64 w-full">
                            <canvas id="chartFlujo"></canvas>
                        </div>
                    </div>

                    <div class="xl:col-span-3 bg-gray-900 rounded-xl border border-gray-800 shadow-lg overflow-hidden">
                         <TablaCaja :reporteCaja="props.reporteCaja" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>