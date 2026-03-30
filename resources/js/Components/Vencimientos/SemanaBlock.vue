<script setup>
/**
 * SemanaBlock.vue — Fila horizontal de 7 días con tarjetas compactas.
 * Cada día es una columna; días vacíos se muestran con placeholder.
 */
import ClienteCard from './ClienteCard.vue'

const props = defineProps({
    bloque: { type: Object, required: true }
})

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val ?? 0)
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-md overflow-hidden">
        
        <!-- Header de la semana (Más prominente) -->
        <div class="px-5 py-3 bg-gradient-to-r from-gray-800 to-gray-900 dark:from-gray-900 dark:to-black text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="font-ufc text-sm font-black uppercase tracking-widest text-amber-400">
                    Semana {{ bloque.semana }}
                </span>
                <span class="text-xs font-medium text-gray-300 border-l border-gray-600 pl-3">{{ bloque.rango }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="font-ufc text-xs font-bold text-emerald-400 block pb-0.5">{{ formatCurrency(bloque.resumen.monto_total) }}</span>
                <span class="font-ufc bg-white/10 text-white text-xs font-black px-3 py-1 rounded-full border border-white/20">
                    {{ bloque.resumen.total }}
                </span>
            </div>
        </div>

        <!-- Grilla horizontal de 7 días -->
        <div class="grid grid-cols-7 divide-x divide-gray-100 dark:divide-gray-800">
            <div
                v-for="dia in bloque.dias"
                :key="dia.fecha"
                :class="[
                    'min-h-[120px] flex flex-col',
                    dia.es_hoy ? 'bg-indigo-50/60 dark:bg-indigo-500/5' : ''
                ]"
            >
                <!-- Cabecera del día -->
                <div :class="[
                    'px-2 py-2 text-center border-b',
                    dia.es_hoy 
                        ? 'bg-indigo-100 dark:bg-indigo-500/15 border-indigo-200 dark:border-indigo-500/20 shadow-inner' 
                        : 'bg-gray-50/80 dark:bg-gray-900/40 border-gray-200 dark:border-gray-800'
                ]">
                    <p :class="['text-[10px] font-extrabold uppercase tracking-widest', dia.es_hoy ? 'text-indigo-700 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400']">
                        {{ dia.nombre_dia }}
                    </p>
                    <p :class="['font-ufc text-2xl font-black leading-tight mt-1', dia.es_hoy ? 'text-indigo-800 dark:text-indigo-300' : 'text-gray-800 dark:text-gray-200']">
                        {{ dia.numero_dia }}
                    </p>
                </div>

                <!-- Tarjetas del día -->
                <div class="flex-1 p-1 overflow-y-auto max-h-[250px]">
                    <div v-if="dia.prestamos.length > 0" class="grid grid-cols-2 gap-1">
                        <ClienteCard
                            v-for="prestamo in dia.prestamos"
                            :key="prestamo.id"
                            :prestamo="prestamo"
                        />
                    </div>
                    <!-- Vacío -->
                    <div v-else class="flex items-center justify-center h-full min-h-[40px]">
                        <span class="text-gray-300 dark:text-gray-700 text-[10px]">—</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
