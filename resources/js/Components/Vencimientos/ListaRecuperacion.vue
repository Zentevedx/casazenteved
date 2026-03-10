<script setup>
/**
 * ListaRecuperacion.vue — Tabla de préstamos con >120 días vencidos (remate/subasta).
 * Excluidos del calendario, mostrados en una tabla de recuperación.
 */
const props = defineProps({
    prestamos: { type: Array, default: () => [] }
})

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val ?? 0)

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    const d = new Date(dateStr + 'T00:00:00')
    return d.toLocaleDateString('es-BO', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>

<template>
    <div v-if="prestamos.length > 0" class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-red-200 dark:border-red-900/40 shadow-sm dark:shadow-lg overflow-hidden">
        
        <!-- Header -->
        <div class="px-5 py-4 border-b border-red-100 dark:border-red-900/30 bg-red-50/50 dark:bg-red-500/5 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg shadow-red-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Registro de Recuperación / Remate</h3>
                <p class="text-[10px] text-gray-500">Préstamos con más de 120 días vencidos — Ordenados por antigüedad</p>
            </div>
            <span class="ml-auto bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 text-xs font-black px-3 py-1 rounded-full">
                {{ prestamos.length }}
            </span>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/30">
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-4 py-2.5 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">Saldo</th>
                        <th class="px-4 py-2.5 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Días Vencido</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Artículos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr 
                        v-for="p in prestamos" 
                        :key="p.id"
                        class="hover:bg-red-50/50 dark:hover:bg-red-500/5 transition-colors"
                    >
                        <td class="px-4 py-3">
                            <p class="font-bold text-gray-900 dark:text-white">{{ p.cliente_nombre }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-gray-500">{{ p.codigo }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-black text-red-600 dark:text-red-400">{{ formatCurrency(p.saldo_pendiente) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 text-xs font-black px-2 py-0.5 rounded-full">
                                {{ p.dias_envejecimiento }}d
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                <span 
                                    v-for="(art, i) in p.articulos" 
                                    :key="i"
                                    class="text-[10px] bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded"
                                >
                                    {{ art.nombre }}
                                </span>
                                <span v-if="!p.articulos || p.articulos.length === 0" class="text-[10px] text-gray-400">—</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
