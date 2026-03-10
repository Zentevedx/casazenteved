<script setup>
import { ref, watch, computed } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DetallePrestamos from '@/Components/Reportes/DetallePrestamos.vue';
import DetallePagos from '@/Components/Reportes/DetallePagos.vue';
import DetalleGastos from '@/Components/Reportes/DetalleGastos.vue';
import DetalleRemate from '@/Components/Reportes/DetalleRemate.vue';
import AgingCartera from '@/Components/Reportes/AgingCartera.vue';
import EficienciaCobranza from '@/Components/Reportes/EficienciaCobranza.vue';
import AlertasTemprana from '@/Components/Reportes/AlertasTemprana.vue';
import dayjs from 'dayjs'
import 'dayjs/locale/es'
import utc from 'dayjs/plugin/utc'
import { 
    BanknotesIcon, 
    ArrowPathIcon, 
    ReceiptPercentIcon, 
    ShoppingBagIcon,
    ArrowDownTrayIcon,
    ExclamationTriangleIcon,
    CalendarDaysIcon,
    GlobeAltIcon,
    ClockIcon,
    ChartPieIcon,
    ListBulletIcon,
    PrinterIcon,
    TableCellsIcon,
    HeartIcon
} from '@heroicons/vue/24/outline';

dayjs.extend(utc)
dayjs.locale('es')

const props = defineProps({
    stats: Object,
    listas: Object,
    prestamosRemate: Array,
    filters: Object,
    salud: Object,
})

const selectedModo = ref(props.filters.modo)
const selectedYear = ref(props.filters.year)
const selectedMonth = ref(props.filters.month)
const selectedWeek = ref(props.filters.week)
const activeTab = ref('resumen') // resumen, prestamos, intereses, gastos, capital, remate

const updateFilters = () => {
    router.get(route('reportes.financiero'), {
        modo: selectedModo.value,
        year: selectedYear.value,
        month: selectedMonth.value,
        week: selectedWeek.value
    }, { preserveScroll: true, preserveState: true })
}

watch([selectedModo, selectedYear, selectedMonth, selectedWeek], () => {
    updateFilters()
})

const years = computed(() => {
    const currentYear = dayjs().year()
    const yearsArr = []
    for (let i = currentYear; i >= currentYear - 5; i--) {
        yearsArr.push(i)
    }
    return yearsArr
})

const months = [
    { value: 1, label: 'Enero' },
    { value: 2, label: 'Febrero' },
    { value: 3, label: 'Marzo' },
    { value: 4, label: 'Abril' },
    { value: 5, label: 'Mayo' },
    { value: 6, label: 'Junio' },
    { value: 7, label: 'Julio' },
    { value: 8, label: 'Agosto' },
    { value: 9, label: 'Septiembre' },
    { value: 10, label: 'Octubre' },
    { value: 11, label: 'Noviembre' },
    { value: 12, label: 'Diciembre' },
]

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val)

const downloadPdf = () => {
    const url = route('reportes.financiero.pdf', {
        modo: selectedModo.value,
        year: selectedYear.value,
        month: selectedMonth.value,
        week: selectedWeek.value,
        tipo: 'resumen'
    });
    window.open(url, '_blank');
}

const downloadRematePdf = () => {
    const url = route('reportes.financiero.pdf', {
        modo: selectedModo.value,
        year: selectedYear.value,
        month: selectedMonth.value,
        week: selectedWeek.value,
        tipo: 'remate'
    });
    window.open(url, '_blank');
}

const downloadExcel = () => {
    const url = route('reportes.financiero.excel', {
        modo: selectedModo.value,
        year: selectedYear.value,
        month: selectedMonth.value,
        week: selectedWeek.value,
        tipo: activeTab.value // Exporta según el tab activo
    });
    window.open(url, '_blank');
}

const modos = [
    { value: 'mensual', label: 'Mensual', icon: CalendarDaysIcon },
    { value: 'semanal', label: 'Semanal', icon: ClockIcon },
    { value: 'global', label: 'Global', icon: GlobeAltIcon },
]

const tabs = [
    { id: 'resumen', label: 'Resumen General', icon: ChartPieIcon },
    { id: 'prestamos', label: 'Préstamos', icon: BanknotesIcon },
    { id: 'intereses', label: 'Intereses', icon: ReceiptPercentIcon },
    { id: 'gastos', label: 'Gastos', icon: ShoppingBagIcon },
    { id: 'capital', label: 'Capital Recup.', icon: ArrowPathIcon },
    { id: 'remate', label: 'En Remate', icon: ExclamationTriangleIcon },
    { id: 'salud', label: 'Salud del Negocio', icon: HeartIcon },
]
</script>

<template>
    <Head title="Reporte Financiero" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">
                        Reporte Financiero
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ filters.periodoLabel }}</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Filtros Premium -->
                <div class="bg-white dark:bg-[#1a1a1a] p-6 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800">
                    <div class="flex flex-col md:flex-row gap-6 items-center">
                        <!-- Mode Selector -->
                        <div class="flex flex-wrap gap-2 p-1 bg-gray-100 dark:bg-gray-900 rounded-xl w-fit">
                            <button 
                                v-for="modo in modos" 
                                :key="modo.value"
                                @click="selectedModo = modo.value"
                                :class="[
                                    'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all duration-200',
                                    selectedModo === modo.value 
                                        ? 'bg-white dark:bg-indigo-600 text-indigo-600 dark:text-white shadow-md' 
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white'
                                ]"
                            >
                                <component :is="modo.icon" class="w-4 h-4" />
                                {{ modo.label }}
                            </button>
                        </div>

                        <!-- Date Filters -->
                        <div v-if="selectedModo !== 'global'" class="flex gap-4 w-full md:w-auto">
                            <select v-model="selectedYear" class="bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm py-2">
                                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                            </select>
                            <select v-model="selectedMonth" class="bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm py-2">
                                <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                            </select>
                            <select v-if="selectedModo === 'semanal'" v-model="selectedWeek" class="bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm py-2">
                                <option :value="1">Semana 1</option>
                                <option :value="2">Semana 2</option>
                                <option :value="3">Semana 3</option>
                                <option :value="4">Semana 4</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                        <button 
                            v-for="tab in tabs" 
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                activeTab === tab.id
                                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2'
                            ]"
                        >
                            <component :is="tab.icon" class="w-5 h-5" />
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- Tab Content: RESUMEN -->
                <div v-if="activeTab === 'resumen'" class="space-y-6 fade-in">
                    
                    <!-- Botones de exportación -->
                    <div class="flex justify-end gap-4">
                        <button @click="downloadExcel" class="inline-flex items-center px-4 py-2.5 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl font-bold text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 shadow-sm transition-colors">
                            <TableCellsIcon class="w-5 h-5 mr-2" /> Exportar a Excel
                        </button>
                        <button @click="downloadPdf" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-700 hover:to-purple-700 shadow-lg transition-transform hover:scale-105">
                            <ArrowDownTrayIcon class="w-4 h-4 mr-2" /> Descargar PDF
                        </button>
                    </div>

                    <!-- KPIs Fila 1: Movimiento del período -->
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">📊 Movimiento del Período</p>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-white dark:bg-[#1a1a1a] p-5 rounded-2xl shadow-md border border-gray-100 dark:border-gray-800 border-l-4 border-l-indigo-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Préstamos Otorgados</p>
                                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ formatCurrency(stats.prestamos) }}</h3>
                                <p class="text-xs text-gray-400 mt-1.5">{{ stats.cantidad_prestamos }} operaciones</p>
                                <p class="text-xs text-indigo-500 font-semibold mt-0.5">Ticket prom.: {{ stats.cantidad_prestamos > 0 ? formatCurrency(stats.prestamos / stats.cantidad_prestamos) : 'N/A' }}</p>
                            </div>
                            <div class="bg-white dark:bg-[#1a1a1a] p-5 rounded-2xl shadow-md border border-gray-100 dark:border-gray-800 border-l-4 border-l-emerald-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Intereses Cobrados</p>
                                <h3 class="text-2xl font-black text-emerald-700 dark:text-emerald-400">{{ formatCurrency(stats.intereses) }}</h3>
                                <p class="text-xs text-gray-400 mt-1.5">Ingresos del período</p>
                                <p class="text-xs text-emerald-500 font-semibold mt-0.5">
                                    ROI: {{ salud?.interes_proyectado > 0 ? ((stats.intereses / stats.prestamos) * 100).toFixed(1) + '%' : 'N/A' }}
                                </p>
                            </div>
                            <div class="bg-white dark:bg-[#1a1a1a] p-5 rounded-2xl shadow-md border border-gray-100 dark:border-gray-800 border-l-4 border-l-blue-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Capital Recuperado</p>
                                <h3 class="text-2xl font-black text-blue-700 dark:text-blue-400">{{ formatCurrency(stats.capital_recuperado) }}</h3>
                                <p class="text-xs text-gray-400 mt-1.5">Préstamos cancelados</p>
                            </div>
                            <div class="bg-white dark:bg-[#1a1a1a] p-5 rounded-2xl shadow-md border border-gray-100 dark:border-gray-800 border-l-4 border-l-red-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Gastos Operativos</p>
                                <h3 class="text-2xl font-black text-red-600 dark:text-red-400">{{ formatCurrency(stats.gastos) }}</h3>
                                <p class="text-xs text-gray-400 mt-1.5">Egresos del período</p>
                                <p class="text-xs text-red-500 font-semibold mt-0.5">
                                    {{ stats.intereses > 0 ? ((stats.gastos / stats.intereses) * 100).toFixed(1) + '% de intereses' : '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Resultados + Cartera + Eficiencia (3 columnas en escritorio) -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                        <!-- Panel de Resultados -->
                        <div class="bg-gradient-to-br from-gray-900 to-gray-800 p-6 rounded-2xl shadow-xl text-white">
                            <h3 class="text-sm font-bold mb-5 flex items-center gap-2 text-gray-300 uppercase tracking-widest">
                                <ChartPieIcon class="w-4 h-4 text-indigo-400" /> Resultados del Período
                            </h3>
                            <div class="space-y-4">
                                <div class="pb-4 border-b border-gray-700">
                                    <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Utilidad Bruta</p>
                                    <p class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-500">
                                        {{ formatCurrency(stats.intereses - stats.gastos) }}
                                    </p>
                                    <p class="text-gray-500 text-xs mt-0.5">Intereses − Gastos</p>
                                </div>
                                <div class="pb-4 border-b border-gray-700">
                                    <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Flujo Neto de Caja</p>
                                    <p :class="['text-2xl font-bold', (stats.intereses + stats.capital_recuperado - stats.prestamos - stats.gastos) >= 0 ? 'text-emerald-400' : 'text-red-400']">
                                        {{ formatCurrency(stats.intereses + stats.capital_recuperado - stats.prestamos - stats.gastos) }}
                                    </p>
                                    <p class="text-gray-500 text-xs mt-0.5">Entradas vs Salidas totales</p>
                                </div>
                                <div class="pb-4 border-b border-gray-700">
                                    <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Margen Operativo</p>
                                    <p class="text-2xl font-bold text-yellow-400">
                                        {{ stats.intereses > 0 ? (((stats.intereses - stats.gastos) / stats.intereses) * 100).toFixed(1) + '%' : 'N/A' }}
                                    </p>
                                    <p class="text-gray-500 text-xs mt-0.5">Utilidad / Intereses cobrados</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Eficiencia de Cobranza</p>
                                    <div class="flex items-center gap-2">
                                        <p :class="['text-2xl font-bold', salud?.eficiencia_cobranza >= 80 ? 'text-emerald-400' : salud?.eficiencia_cobranza >= 50 ? 'text-yellow-400' : 'text-red-400']">
                                            {{ salud?.eficiencia_cobranza ?? 0 }}%
                                        </p>
                                        <span :class="['text-xs font-bold px-2 py-0.5 rounded-full', salud?.eficiencia_cobranza >= 80 ? 'bg-emerald-500/20 text-emerald-400' : salud?.eficiencia_cobranza >= 50 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400']">
                                            {{ salud?.eficiencia_cobranza >= 80 ? 'Óptimo' : salud?.eficiencia_cobranza >= 50 ? 'Regular' : 'Crítico' }}
                                        </span>
                                    </div>
                                    <p class="text-gray-500 text-xs mt-0.5">Cobrado vs Proyectado ({{ formatCurrency(salud?.interes_proyectado ?? 0) }})</p>
                                </div>
                            </div>
                        </div>

                        <!-- Estado de Cartera -->
                        <div class="bg-white dark:bg-[#1a1a1a] p-6 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800">
                            <h3 class="text-sm font-bold mb-5 flex items-center gap-2 text-gray-800 dark:text-white uppercase tracking-widest">
                                <GlobeAltIcon class="w-4 h-4 text-blue-500" /> Estado de Cartera
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-end mb-1.5">
                                        <p class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase">Total en Calle</p>
                                        <span class="text-xl font-black text-gray-900 dark:text-white">{{ formatCurrency(stats.cartera_total) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: 100%"></div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Capital activo total</p>
                                </div>
                                <div>
                                    <div class="flex justify-between items-end mb-1.5">
                                        <p class="text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase">Vigente (Recuperable)</p>
                                        <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ formatCurrency(stats.cartera_vigente) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                        <div class="bg-emerald-500 h-2 rounded-full" :style="`width: ${(stats.cartera_vigente / (stats.cartera_total || 1)) * 100}%`"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                                        <span>&lt; 3 meses sin movimiento</span>
                                        <span class="font-bold">{{ ((stats.cartera_vigente / (stats.cartera_total || 1)) * 100).toFixed(1) }}%</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-end mb-1.5">
                                        <p class="text-red-600 dark:text-red-400 text-xs font-bold uppercase">En Riesgo / Remate</p>
                                        <span class="text-lg font-black text-red-600 dark:text-red-400">{{ formatCurrency(stats.cartera_remate) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" :style="`width: ${(stats.cartera_remate / (stats.cartera_total || 1)) * 100}%`"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                                        <span>≥ 3 meses sin movimiento</span>
                                        <span class="font-bold text-red-500">{{ ((stats.cartera_remate / (stats.cartera_total || 1)) * 100).toFixed(1) }}%</span>
                                    </div>
                                </div>
                                <!-- Interés proyectado -->
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                                    <div class="flex justify-between items-center">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase">Interés Esperado (10%)</p>
                                        <span class="text-base font-black text-purple-600 dark:text-purple-400">{{ formatCurrency(salud?.interes_proyectado ?? 0) }}</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5">Si todos pagan este mes</p>
                                </div>
                            </div>
                        </div>

                        <!-- Panel de Alertas rápido -->
                        <div class="bg-white dark:bg-[#1a1a1a] p-6 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800">
                            <h3 class="text-sm font-bold mb-5 flex items-center gap-2 text-gray-800 dark:text-white uppercase tracking-widest">
                                <ExclamationTriangleIcon class="w-4 h-4 text-orange-500" /> Semáforo de Cartera
                            </h3>
                            <div class="space-y-3">
                                <div v-for="item in [
                                    { key: 'al_dia', label: 'Al Día', emoji: '🟢', textColor: 'text-emerald-600 dark:text-emerald-400' },
                                    { key: 'riesgo_leve', label: 'Riesgo Leve (31-60d)', emoji: '🟡', textColor: 'text-yellow-600 dark:text-yellow-400' },
                                    { key: 'riesgo_alto', label: 'Riesgo Alto (61-89d)', emoji: '🟠', textColor: 'text-orange-600 dark:text-orange-400' },
                                    { key: 'remate', label: 'En Remate (≥90d)', emoji: '🔴', textColor: 'text-red-600 dark:text-red-400' },
                                ]" :key="item.key" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">{{ item.emoji }}</span>
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ item.label }}</span>
                                    </div>
                                    <div class="text-right">
                                        <p :class="['font-black text-sm', item.textColor]">{{ formatCurrency(salud?.aging?.[item.key]?.monto ?? 0) }}</p>
                                        <p class="text-xs text-gray-400">{{ salud?.aging?.[item.key]?.count ?? 0 }} préstamo(s)</p>
                                    </div>
                                </div>
                                <!-- Alertas activas -->
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                    <p class="text-xs text-gray-500 font-bold uppercase">Alertas activas</p>
                                    <button @click="activeTab = 'salud'" :class="['px-3 py-1 rounded-full text-xs font-bold transition-colors', (salud?.alertas?.length ?? 0) > 0 ? 'bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300 hover:bg-orange-200' : 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300']">
                                        {{ (salud?.alertas?.length ?? 0) > 0 ? `⚠️ ${salud.alertas.length} alertas → Ver` : '✅ Sin alertas' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: PRESTAMOS -->
                <div v-else-if="activeTab === 'prestamos'" class="fade-in">
                    <DetallePrestamos :items="listas.prestamos" :filters="{ modo: selectedModo, year: selectedYear, month: selectedMonth, week: selectedWeek }" />
                </div>

                <!-- Tab Content: INTERESES -->
                <div v-else-if="activeTab === 'intereses'" class="fade-in">
                    <DetallePagos :items="listas.intereses" type="intereses" :filters="{ modo: selectedModo, year: selectedYear, month: selectedMonth, week: selectedWeek }" />
                </div>

                <!-- Tab Content: GASTOS -->
                <div v-else-if="activeTab === 'gastos'" class="fade-in">
                    <DetalleGastos :items="listas.gastos" :filters="{ modo: selectedModo, year: selectedYear, month: selectedMonth, week: selectedWeek }" />
                </div>

                <!-- Tab Content: CAPITAL -->
                <div v-else-if="activeTab === 'capital'" class="fade-in">
                    <DetallePagos :items="listas.capital" type="capital" :filters="{ modo: selectedModo, year: selectedYear, month: selectedMonth, week: selectedWeek }" />
                </div>

                <!-- Tab Content: SALUD -->
                <div v-else-if="activeTab === 'salud'" class="fade-in space-y-6">
                    <!-- Fila superior: Eficiencia + Aging -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <EficienciaCobranza
                            :proyectado="salud?.interes_proyectado ?? 0"
                            :cobrado="stats?.intereses ?? 0"
                            :eficiencia="salud?.eficiencia_cobranza ?? 0"
                        />
                        <AgingCartera
                            :aging="salud?.aging ?? {}"
                            :carteraTotal="stats?.cartera_total ?? 0"
                        />
                    </div>
                    <!-- Alertas tempranas a ancho completo -->
                    <AlertasTemprana :alertas="salud?.alertas ?? []" />
                </div>

                <!-- Tab Content: REMATE -->
                <div v-else-if="activeTab === 'remate'" class="fade-in">
                    <DetalleRemate
                        :items="prestamosRemate"
                        :filters="{ modo: selectedModo, year: selectedYear, month: selectedMonth, week: selectedWeek }"
                    />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
