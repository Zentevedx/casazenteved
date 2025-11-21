<script setup>
import { onMounted, watch } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  fechas: Array,
  prestamos: Array
})

let chartInstance = null

const renderChart = () => {
  const ctx = document.getElementById('graficoPrestamos')
  if (!ctx) return

  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.fechas,
      datasets: [
        {
          label: 'Cantidad de Préstamos',
          data: props.prestamos,
          backgroundColor: '#f97316'
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true },
        tooltip: { enabled: true }
      },
      scales: {
        y: {
          ticks: { color: '#ccc' },
          grid: { color: '#444' }
        },
        x: {
          ticks: { color: '#ccc' },
          grid: { color: '#444' }
        }
      }
    }
  })
}

onMounted(renderChart)

watch([
  () => props.prestamos,
  () => props.fechas
], () => {
  if (chartInstance) chartInstance.destroy()
  renderChart()
})
</script>

<template>
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow">
    <h3 class="text-md font-semibold text-orange-400 mb-4">📈 Préstamos por Día</h3>
    <canvas id="graficoPrestamos" height="120"></canvas>
  </div>
</template>
