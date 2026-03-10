<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    alertas: {
        type: Array,
        default: () => []
    }
})

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val ?? 0)

const nivelConfig = {
    leve: {
        label: 'Riesgo Leve',
        badge: 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300',
        row: 'border-l-4 border-l-yellow-400',
        icon: '🟡',
    },
    alto: {
        label: 'Riesgo Alto',
        badge: 'bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300',
        row: 'border-l-4 border-l-orange-500',
        icon: '🟠',
    },
}
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">Alertas Tempranas</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Clientes 31–89 días sin pago — actúa antes del remate</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300 rounded-full text-xs font-bold">
                {{ alertas.length }} alerta(s)
            </span>
        </div>

        <!-- Lista vacía -->
        <div v-if="alertas.length === 0" class="p-12 text-center">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 font-medium">¡Sin alertas! Todos los clientes están al día.</p>
        </div>

        <!-- Tabla de alertas -->
        <div v-else class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-bold">
                    <tr>
                        <th class="px-5 py-3">Nivel</th>
                        <th class="px-5 py-3">Código</th>
                        <th class="px-5 py-3">Cliente</th>
                        <th class="px-5 py-3">Teléfono</th>
                        <th class="px-5 py-3">Días sin pago</th>
                        <th class="px-5 py-3 text-right">Monto</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr
                        v-for="alerta in alertas"
                        :key="alerta.id"
                        :class="['transition-colors hover:bg-gray-50 dark:hover:bg-gray-900/30', nivelConfig[alerta.nivel]?.row]"
                    >
                        <td class="px-5 py-4">
                            <span :class="['px-2 py-1 rounded-full text-xs font-bold', nivelConfig[alerta.nivel]?.badge]">
                                {{ nivelConfig[alerta.nivel]?.icon }} {{ nivelConfig[alerta.nivel]?.label }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-bold text-indigo-600 dark:text-indigo-400 font-mono text-sm">
                            {{ alerta.codigo }}
                        </td>
                        <td class="px-5 py-4 text-gray-800 dark:text-gray-200 font-medium">
                            {{ alerta.cliente }}
                        </td>
                        <td class="px-5 py-4">
                            <a
                                v-if="alerta.telefono"
                                :href="`https://wa.me/${alerta.telefono.replace(/\D/g,'')}`"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 rounded-lg text-xs font-bold hover:bg-green-100 dark:hover:bg-green-500/20 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                {{ alerta.telefono }}
                            </a>
                            <span v-else class="text-gray-400 text-xs italic">Sin teléfono</span>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['text-sm font-bold', alerta.nivel === 'alto' ? 'text-orange-600 dark:text-orange-400' : 'text-yellow-600 dark:text-yellow-400']">
                                {{ alerta.dias_sin_pago }} días
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right font-semibold text-gray-900 dark:text-white">
                            {{ formatCurrency(alerta.monto) }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <Link :href="route('clientes.detalle', alerta.cliente_id)" class="text-indigo-600 dark:text-indigo-400 text-sm font-medium hover:underline">
                                Ver →
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
