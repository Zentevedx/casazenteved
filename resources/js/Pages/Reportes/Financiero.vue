<script setup>
import { ref, watch, computed } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DetallePrestamos from '@/Components/Reportes/DetallePrestamos.vue';
import DetallePagos from '@/Components/Reportes/DetallePagos.vue';
import DetalleGastos from '@/Components/Reportes/DetalleGastos.vue';
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
    PrinterIcon
} from '@heroicons/vue/24/outline';

dayjs.extend(utc)
dayjs.locale('es')

const props = defineProps({
    stats: Object,
    listas: Object,
    prestamosRemate: Array,
    filters: Object
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
                <div v-if="activeTab === 'resumen'" class="space-y-8 fade-in">
                    
                    <div class="flex justify-end">
                        <button 
                            @click="downloadPdf"
                            class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-700 hover:to-purple-700 shadow-lg"
                        >
                            <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                            Descargar Resumen PDF
                        </button>
                    </div>

                    <!-- KPIs Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Préstamos -->
                        <div class="group bg-white dark:bg-[#1a1a1a] p-6 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Préstamos Otorgados</p>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ formatCurrency(stats.prestamos) }}</h3>
                            <p class="text-xs text-gray-400 mt-2">{{ stats.cantidad_prestamos }} operaciones</p>
                        </div>
                        <!-- Intereses -->
                        <div class="group bg-white dark:bg-[#1a1a1a] p-6 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Intereses Cobrados</p>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ formatCurrency(stats.intereses) }}</h3>
                            <p class="text-xs text-emerald-500 mt-2 font-semibold">+ Ganancia neta</p>
                        </div>
                        <!-- Capital -->
                        <div class="group bg-white dark:bg-[#1a1a1a] p-6 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Capital Recuperado</p>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ formatCurrency(stats.capital_recuperado) }}</h3>
                        </div>
                        <!-- Gastos -->
                        <div class="group bg-white dark:bg-[#1a1a1a] p-6 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Gastos Operativos</p>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ formatCurrency(stats.gastos) }}</h3>
                            <p class="text-xs text-red-500 mt-2 font-semibold">- Egresos</p>
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Resultados Financieros -->
                        <div class="bg-gradient-to-r from-gray-900 to-gray-800 p-8 rounded-2xl shadow-xl text-white">
                            <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                                <ChartPieIcon class="w-6 h-6 text-indigo-400" />
                                Resultados del Periodo
                            </h3>
                            <div class="space-y-6">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium uppercase tracking-wider mb-2">Utilidad Bruta (Ganancia)</p>
                                    <h3 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-500">
                                        {{ formatCurrency(stats.intereses - stats.gastos) }}
                                    </h3>
                                    <p class="text-gray-500 text-xs mt-1">Intereses - Gastos</p>
                                </div>
                                <div class="border-t border-gray-700 pt-4">
                                    <p class="text-gray-400 text-sm font-medium uppercase tracking-wider mb-2">Flujo Neto</p>
                                    <h3 class="text-3xl font-bold text-white">
                                        {{ formatCurrency(stats.intereses + stats.capital_recuperado - stats.prestamos - stats.gastos) }}
                                    </h3>
                                    <p class="text-gray-500 text-xs mt-1">Entradas vs Salidas</p>
                                </div>
                            </div>
                        </div>

                        <!-- Estado de Cartera -->
                        <div class="bg-white dark:bg-[#1a1a1a] p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800">
                            <h3 class="text-xl font-bold mb-6 flex items-center gap-2 text-gray-800 dark:text-white">
                                <GlobeAltIcon class="w-6 h-6 text-blue-500" />
                                Estado de Cartera (Actual)
                            </h3>
                            <div class="space-y-6">
                                <div>
                                    <div class="flex justify-between items-end mb-1">
                                        <p class="text-gray-500 dark:text-gray-400 text-sm font-bold uppercase">Dinero en Calle (Total)</p>
                                        <span class="text-2xl font-black text-gray-900 dark:text-white">{{ formatCurrency(stats.cartera_total) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between items-end mb-1">
                                        <p class="text-emerald-600 dark:text-emerald-400 text-sm font-bold uppercase">Cartera Vigente (Recuperable)</p>
                                        <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(stats.cartera_vigente) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-emerald-500 h-2 rounded-full" :style="`width: ${(stats.cartera_vigente / (stats.cartera_total || 1)) * 100}%`"></div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Préstamos activos < 3 meses sin mov.</p>
                                </div>

                                <div>
                                    <div class="flex justify-between items-end mb-1">
                                        <p class="text-red-600 dark:text-red-400 text-sm font-bold uppercase">En Riesgo / Remate</p>
                                        <span class="text-xl font-bold text-red-600 dark:text-red-400">{{ formatCurrency(stats.cartera_remate) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" :style="`width: ${(stats.cartera_remate / (stats.cartera_total || 1)) * 100}%`"></div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Préstamos > 3 meses sin mov.</p>
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

                <!-- Tab Content: REMATE -->
                <div v-else-if="activeTab === 'remate'" class="fade-in">
                     <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center">
                                    <ExclamationTriangleIcon class="w-5 h-5 mr-2 text-red-500" />
                                    Préstamos en Remate
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Artículos sin movimiento > 3 meses</p>
                            </div>
                            <button 
                                @click="downloadRematePdf"
                                class="inline-flex items-center px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg text-sm font-bold hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
                            >
                                <PrinterIcon class="w-4 h-4 mr-2" />
                                Imprimir Remates
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-bold">
                                    <tr>
                                        <th class="px-6 py-4">#</th>
                                        <th class="px-6 py-4">Código</th>
                                        <th class="px-6 py-4">Cliente</th>
                                        <th class="px-6 py-4">Artículo</th>
                                        <th class="px-6 py-4">Capital</th>
                                        <th class="px-6 py-4">Fecha Inicio</th>
                                        <th class="px-6 py-4">Estado</th>
                                        <th class="px-6 py-4"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr v-for="(p, index) in prestamosRemate" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ index + 1 }}</td>
                                        <td class="px-6 py-4 font-bold text-indigo-600 dark:text-indigo-400">{{ p.codigo }}</td>
                                        <td class="px-6 py-4 text-gray-800 dark:text-gray-200">{{ p.cliente.nombre }}</td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300 text-sm max-w-xs truncate">
                                            {{ p.articulos?.map(a => a.nombre).join(', ') || 'Sin artículo' }}
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ formatCurrency(p.saldo_a_fecha || p.monto) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ dayjs(p.fecha_prestamo).utc().format('DD/MM/YYYY') }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 bg-red-50 dark:bg-red-500/20 text-red-600 dark:text-red-400 text-xs font-bold uppercase rounded-full">Remate</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <Link :href="route('prestamos.edit', p.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium hover:underline">
                                                Ver →
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="prestamosRemate.length === 0">
                                        <td colspan="8" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="w-16 h-16 bg-green-100 dark:bg-green-500/20 rounded-full flex items-center justify-center mb-4">
                                                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </div>
                                                <p class="text-gray-500 dark:text-gray-400 font-medium">¡Excelente! No hay préstamos en situación de remate.</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Todos los clientes están al día con sus pagos.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
