<script setup>
import { computed } from 'vue'

const props = defineProps({
  reporteCaja: Object
})

const format = n => Number(n).toFixed(2)
const formatDate = d => new Date(d).toLocaleDateString('es-BO', {
  weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
})

const maxItems = computed(() => Math.max(
  props.reporteCaja.capital_saliente.length,
  props.reporteCaja.capital_entrante.length,
  props.reporteCaja.gastos.length,
  props.reporteCaja.intereses.length
))
</script>

<template>
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow">
    <h2 class="text-lg font-bold text-orange-400 mb-4">📋 Reporte de Caja Detallado</h2>
    <div class="overflow-x-auto">
      <table class="table-auto w-full text-sm">
        <thead class="text-xs text-gray-400 bg-gray-800">
          <tr>
            <th class="px-4 py-2">Capital Saliente</th>
            <th class="px-4 py-2">Capital Entrante</th>
            <th class="px-4 py-2">Gastos</th>
            <th class="px-4 py-2">Intereses</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
          <tr v-for="i in maxItems" :key="i">
            <td class="px-4 py-2">
              <div v-if="props.reporteCaja.capital_saliente[i - 1]">
                <span class="text-red-400 font-semibold">
                  #{{ props.reporteCaja.capital_saliente[i - 1].codigo }}
                </span>
                — Bs {{ format(props.reporteCaja.capital_saliente[i - 1].monto) }}
                <div class="text-xs text-gray-400">
                  {{ formatDate(props.reporteCaja.capital_saliente[i - 1].fecha) }}
                </div>
              </div>
            </td>
            <td class="px-4 py-2">
              <div v-if="props.reporteCaja.capital_entrante[i - 1]">
                <span class="text-green-400 font-semibold">
                  #{{ props.reporteCaja.capital_entrante[i - 1].codigo }}
                </span>
                — Bs {{ format(props.reporteCaja.capital_entrante[i - 1].monto) }}
                <div class="text-xs text-gray-400">
                  {{ formatDate(props.reporteCaja.capital_entrante[i - 1].fecha) }}
                </div>
              </div>
            </td>
            <td class="px-4 py-2">
              <div v-if="props.reporteCaja.gastos[i - 1]">
                <span class="text-yellow-400 font-semibold">
                  {{ props.reporteCaja.gastos[i - 1].motivo }}
                </span>
                — Bs {{ format(props.reporteCaja.gastos[i - 1].monto) }}
                <div class="text-xs text-gray-400">
                  {{ formatDate(props.reporteCaja.gastos[i - 1].fecha) }}
                </div>
              </div>
            </td>
            <td class="px-4 py-2">
              <div v-if="props.reporteCaja.intereses[i - 1]">
                <span class="text-blue-400 font-semibold">
                  #{{ props.reporteCaja.intereses[i - 1].codigo }}
                </span>
                — Bs {{ format(props.reporteCaja.intereses[i - 1].monto) }}
                <div class="text-xs text-gray-400">
                  {{ formatDate(props.reporteCaja.intereses[i - 1].fecha) }}
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tfoot class="text-xs text-gray-300 border-t border-gray-700">
          <tr>
            <td class="px-4 py-2 text-red-400 font-semibold">
              Total: Bs {{ format(props.reporteCaja.totales.saliente) }}
            </td>
            <td class="px-4 py-2 text-green-400 font-semibold">
              Total: Bs {{ format(props.reporteCaja.totales.entrante) }}
            </td>
            <td class="px-4 py-2 text-yellow-400 font-semibold">
              Total: Bs {{ format(props.reporteCaja.totales.gastos) }}
            </td>
            <td class="px-4 py-2 text-blue-400 font-semibold">
              Total: Bs {{ format(props.reporteCaja.totales.intereses) }}
            </td>
          </tr>
          <tr>
            <td colspan="4" class="px-4 py-3 text-center font-bold text-white">
              Diferencia neta = Bs {{ format(
                (props.reporteCaja.totales.entrante + props.reporteCaja.totales.intereses)
                - (props.reporteCaja.totales.saliente + props.reporteCaja.totales.gastos)
              ) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>
