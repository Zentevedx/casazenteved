<script setup>
import { onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    type: {
        type: String,
        default: 'line' 
    },
    data: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
        default: () => ({})
    }
});

const canvasRef = ref(null);
let chartInstance = null;

const renderChart = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }
    
    if (canvasRef.value) {
        chartInstance = new Chart(canvasRef.value, {
            type: props.type,
            data: props.data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#9ca3af' } // gray-400
                    }
                },
                scales: {
                    y: {
                        grid: { color: '#374151' }, // gray-700
                        ticks: { color: '#9ca3af' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af' }
                    }
                },
                ...props.options
            }
        });
    }
};

onMounted(() => {
    renderChart();
});

watch(() => props.data, () => {
    renderChart();
}, { deep: true });

</script>

<template>
    <div class="relative w-full h-full">
        <canvas ref="canvasRef"></canvas>
    </div>
</template>
