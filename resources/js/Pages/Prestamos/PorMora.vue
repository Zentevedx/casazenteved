<script setup>
import { ref, computed } from 'vue'
import { router, Link, Head } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import Avatar from '@/Components/Avatar.vue'
import { 
    ExclamationTriangleIcon, ClockIcon, FunnelIcon, 
    ArrowTrendingUpIcon, BanknotesIcon, DocumentTextIcon,
    PhoneIcon, ChevronRightIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    prestamos: Array,
    conteos: Array,
    rangoActual: String,
    kpis: Object,
})

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val ?? 0)

const rangos = computed(() => [
    { key: 'todas', label: 'Todas las moras', emoji: '🔍' },
    { key: '0-30',  label: '0–30 días', emoji: '🟢', color: 'emerald' },
    { key: '31-60', label: '31–60 días', emoji: '🟡', color: 'yellow' },
    { key: '61-90', label: '61–90 días', emoji: '🟠', color: 'orange' },
    { key: '90+',   label: '90+ días (Remate)', emoji: '🔴', color: 'red' },
])

const rangoInfo = computed(() => {
    return rangos.value.find(r => r.key === props.rangoActual) || rangos.value[0]
})

const cambiarRango = (rango) => {
    router.get(route('prestamos.porMora.rango', rango), {}, { preserveScroll: true, replace: true })
}

const getDiasColor = (dias) => {
    if (dias <= 0) return 'text-emerald-600 dark:text-emerald-400'
    if (dias <= 30) return 'text-emerald-600 dark:text-emerald-400'
    if (dias <= 60) return 'text-yellow-600 dark:text-yellow-400'
    if (dias <= 90) return 'text-orange-600 dark:text-orange-400'
    return 'text-red-600 dark:text-red-400'
}

const getStatusBadge = (estado) => {
    const styles = {
        'Activo': 'bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20',
        'Vencido': 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
        'Pagado': 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
    }
    return styles[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-white'
}

const getDiasBadge = (dias) => {
    if (dias <= 30) return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
    if (dias <= 60) return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400'
    if (dias <= 90) return 'bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400'
    return 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400'
}
</script>

<template>
    <Head title="Préstamos por Mora" />

    <Layout>
        <div class="min-h-screen bg-gray-50 dark:bg-[#0f0f0f] pb-20">
            
            <!-- Header -->
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center shadow-lg shadow-red-500/20">
                            <ExclamationTriangleIcon class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Control de Mora</h1>
                            <p class="text-sm text-gray-500 font-medium">Préstamos activos con retraso — {{ props.prestamos.length }} préstamos</p>
                        </div>
                    </div>
                    <Link :href="route('prestamos.index')" 
                        class="text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        ← Volver a Préstamos
                    </Link>
                </div>

                <!-- KPIs -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4 mt-6">
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl p-4 border border-gray-200 dark:border-gray-800">
                        <p class="text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1">Cartera Total</p>
                        <p class="text-xl font-black text-gray-900 dark:text-white">{{ formatCurrency(kpis.cartera_total) }}</p>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl p-4 border border-gray-200 dark:border-gray-800">
                        <p class="text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1">Saldo Pendiente</p>
                        <p class="text-xl font-black text-red-600 dark:text-red-400">{{ formatCurrency(kpis.total_pendiente) }}</p>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl p-4 border border-gray-200 dark:border-gray-800">
                        <p class="text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1">En Mora (>30d)</p>
                        <p class="text-xl font-black text-orange-600 dark:text-orange-400">{{ kpis.prestamos_en_mora }}</p>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl p-4 border border-gray-200 dark:border-gray-800">
                        <p class="text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1">Promedio Retraso</p>
                        <p class="text-xl font-black text-yellow-600 dark:text-yellow-400">{{ kpis.promedio_retraso }} días</p>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl p-4 border border-gray-200 dark:border-gray-800">
                        <p class="text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1">Interés Generado</p>
                        <p class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ formatCurrency(kpis.total_intereses) }}</p>
                    </div>
                </div>

                <!-- Filtros por rango -->
                <div class="flex flex-wrap gap-2 mt-6">
                    <button 
                        v-for="rango in rangos" 
                        :key="rango.key"
                        @click="cambiarRango(rango.key)"
                        :class="[
                            'px-4 py-2 rounded-xl text-sm font-bold transition-all border',
                            rangoActual === rango.key 
                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-500/20' 
                                : 'bg-white dark:bg-[#1a1a1a] text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-800 hover:border-indigo-300 dark:hover:border-indigo-700'
                        ]"
                    >
                        {{ rango.emoji }} {{ rango.label }} 
                        <span :class="[
                            'ml-1.5 px-1.5 py-0.5 rounded text-[10px]', 
                            rangoActual === rango.key ? 'bg-white/20' : 'bg-gray-100 dark:bg-gray-800'
                        ]">
                            {{ conteos.find(c => c.rango === rango.key)?.count || 0 }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Tabla de préstamos -->
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-[#1a1a1a] rounded-[24px] border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl overflow-hidden">
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/30 text-xs uppercase tracking-wider text-gray-500 font-bold">
                                    <th class="px-5 py-4">Cliente</th>
                                    <th class="px-5 py-4">Código</th>
                                    <th class="px-5 py-4">Capital</th>
                                    <th class="px-5 py-4">Saldo Pendiente</th>
                                    <th class="px-5 py-4">Interés Generado</th>
                                    <th class="px-5 py-4">Vencimiento</th>
                                    <th class="px-5 py-4 text-center">Días Retraso</th>
                                    <th class="px-5 py-4">Artículos</th>
                                    <th class="px-5 py-4 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                                <tr v-for="prestamo in prestamos" :key="prestamo.id" 
                                    class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <Avatar 
                                                :name="prestamo.cliente?.nombre || '?'"
                                                :identifier="prestamo.cliente?.id || 0"
                                                :foto-url="prestamo.cliente?.foto_url || null"
                                                size-class="w-9 h-9 text-xs"
                                                rounded-class="rounded-full"
                                            />
                                            <div>
                                                <p class="font-bold text-gray-900 dark:text-white text-sm">{{ prestamo.cliente?.nombre || 'Desconocido' }}</p>
                                                <p class="text-[11px] text-gray-500">{{ prestamo.cliente?.telefono || '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="font-mono text-xs text-indigo-600 dark:text-indigo-400 bg-gray-100 dark:bg-gray-900/50 px-2 py-1 rounded border border-gray-200 dark:border-gray-800">
                                            {{ prestamo.codigo }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 font-bold text-gray-900 dark:text-white">
                                        {{ formatCurrency(prestamo.monto) }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="font-bold text-red-600 dark:text-red-400">{{ formatCurrency(prestamo.aging?.saldo_pendiente || prestamo.monto) }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-emerald-600 dark:text-emerald-400 font-semibold">
                                        {{ formatCurrency(prestamo.pagos?.filter(p => p.tipo_pago === 'Interes').reduce((a,b) => a + parseFloat(b.monto_pagado), 0) || 0) }}
                                    </td>
                                    <td class="px-5 py-4 text-gray-500 text-xs">
                                        {{ prestamo.aging?.fecha_vencimiento || '—' }}
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span :class="['px-3 py-1 rounded-full text-xs font-bold inline-block', getDiasBadge(prestamo.aging?.dias_retraso || 0)]">
                                            {{ prestamo.aging?.dias_retraso || 0 }} días
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="(art, i) in (prestamo.articulos || [])" :key="i"
                                                class="text-[10px] bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded">
                                                {{ art.nombre_articulo || '—' }}
                                            </span>
                                            <span v-if="!prestamo.articulos?.length" class="text-[10px] text-gray-400 italic">Sin artículos</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <Link :href="route('clientes.detalle', prestamo.cliente_id)"
                                            class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 text-sm font-medium hover:underline">
                                            Ver <ChevronRightIcon class="w-4 h-4" />
                                        </Link>
                                    </td>
                                </tr>
                                
                                <!-- Empty state -->
                                <tr v-if="prestamos.length === 0">
                                    <td colspan="9" class="px-5 py-16 text-center text-gray-500">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 bg-green-100 dark:bg-green-500/20 rounded-full flex items-center justify-center">
                                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <p class="text-lg font-bold text-gray-400">Sin resultados</p>
                                            <p class="text-sm">No hay préstamos en este rango de mora.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
