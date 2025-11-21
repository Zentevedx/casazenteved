<script setup>
import { onMounted, watch } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  fechas: Array,
  salientes: Array,
  entrantes: Array,
  intereses: Array,
  gastos: Array
})

let chartInstance = null

const renderChart = () => {
  const ctx = document.getElementById('graficoCajaTemporal')
  if (!ctx) return

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.fechas,
      datasets: [
        {
          label: 'Capital Saliente',
          data: props.salientes,
          borderColor: '#dc2626',
          backgroundColor: '#dc262688',
          fill: true,
          tension: 0.4,
          stack: 'caja'
        },
        {
          label: 'Capital Entrante',
          data: props.entrantes,
          borderColor: '#16a34a',
          backgroundColor: '#16a34a88',
          fill: true,
          tension: 0.4,
          stack: 'caja'
        },
        {
          label: 'Intereses',
          data: props.intereses,
          borderColor: '#3b82f6',
          backgroundColor: '#3b82f688',
          fill: true,
          tension: 0.4,
          stack: 'caja'
        },
        {
          label: 'Gastos',
          data: props.gastos,
          borderColor: '#eab308',
          backgroundColor: '#eab30888',
          fill: true,
          tension: 0.4,
          stack: 'caja'
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        tooltip: { mode: 'index', intersect: false },
        legend: { position: 'top' }
      },
      interaction: {
        mode: 'index',
        intersect: false
      },
      scales: {
        x: {
          stacked: true,
          ticks: { color: '#ccc' },
          grid: { color: '#444' }
        },
        y: {
          stacked: true,
          beginAtZero: true,
          ticks: { color: '#ccc' },
          grid: { color: '#444' }
        }
      }
    }
  })
}

onMounted(renderChart)

watch(
  () => [props.salientes, props.entrantes, props.intereses, props.gastos],
  () => {
    if (chartInstance) chartInstance.destroy()
    renderChart()
  }
)
</script>

<template>
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow">
    <h3 class="text-md font-semibold text-teal-300 mb-4">📈 Caja Apilada por Periodo</h3>
    <canvas id="graficoCajaTemporal" height="120"></canvas>
  </div>
</template>
