<script setup>
import { ref, onMounted, watch } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  fechas: Array,
  rentabilidad: Array
})

let chartInstance = null

const renderChart = () => {
  const ctx = document.getElementById('graficoRentabilidad')
  if (!ctx) return

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.fechas,
      datasets: [
        {
          label: 'Rentabilidad Neta Bs',
          data: props.rentabilidad,
          borderColor: '#8b5cf6',
          backgroundColor: 'rgba(139, 92, 246, 0.15)',
          fill: true,
          tension: 0.3
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

onMounted(() => {
  renderChart()
})

watch(() => props.rentabilidad, () => {
  if (chartInstance) chartInstance.destroy()
  renderChart()
})
</script>

<template>
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow">
    <h3 class="text-md font-semibold text-purple-400 mb-4">💹 Rentabilidad</h3>
    <canvas id="graficoRentabilidad" height="120"></canvas>
  </div>
</template>
