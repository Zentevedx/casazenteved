<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import 'dayjs/locale/es'
dayjs.locale('es')

// 📦 Componentes modularizados
import SelectorFlotante from '@/Components/Estadisticas/SelectorFlotante.vue'
import TablaCaja from '@/Components/Estadisticas/TablaCaja.vue'
import GraficoResumen from '@/Components/Estadisticas/GraficoResumen.vue'
import GraficoPrestamos from '@/Components/Estadisticas/GraficoPrestamos.vue'
import GraficoRentabilidad from '@/Components/Estadisticas/GraficoRentabilidad.vue'

const props = defineProps({
  fechas: Array,
  prestamos: Array,
  ingresos: Array,
  egresos: Array,
  rentabilidad: Array,
  reporteCaja: Object,
  from: String,
  to: String,
  modo: String,
  fecha: String
})

const selectedModo = ref(props.modo)
const selectedFecha = ref(props.fecha)

watch([selectedModo, selectedFecha], () => {
  router.get('/estadisticas', {
    modo: selectedModo.value,
    fecha: selectedFecha.value
  }, {
    preserveScroll: true,
    preserveState: true,
    replace: true
  })
})

const generarFechas = computed(() => {
  const opciones = []
  const hoy = dayjs()

  if (selectedModo.value === 'dia') {
    for (let i = 0; i < 15; i++) {
      const fecha = hoy.subtract(i, 'day')
      opciones.push({
        value: fecha.format('YYYY-MM-DD'),
        label: fecha.format('dddd D [de] MMMM') // lunes 3 de junio
      })
    }
  }

  else if (selectedModo.value === 'semana') {
    for (let i = 0; i < 12; i++) {
      const base = hoy.subtract(i, 'week').startOf('week')
      const fin = base.endOf('week')
      opciones.push({
        value: base.format('YYYY-MM-DD'),
        label: `Semana ${base.format('W')} de ${base.format('MMMM')} (${base.format('D')} al ${fin.format('D')} de ${fin.format('MMMM')})`
        // Semana 23 de junio (3 al 9 de junio)
      })
    }
  }

  else if (selectedModo.value === 'mes') {
    for (let i = 0; i < 12; i++) {
      const base = hoy.subtract(i, 'month').startOf('month')
      opciones.push({
        value: base.format('YYYY-MM-DD'),
        label: base.format('MMMM [de] YYYY') // junio de 2025
      })
    }
  }

  return opciones.reverse()
})



const opcionesFechas = generarFechas
</script>

<template>
  <div class="min-h-screen bg-gray-950 text-white">
    <!-- Selector Flotante -->
    <SelectorFlotante
      v-model:modo="selectedModo"
      v-model:fecha="selectedFecha"
      :opcionesFechas="opcionesFechas"
    />

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 p-6">
      <div class="col-span-1">
        <TablaCaja :reporteCaja="props.reporteCaja" />
      </div>
      <div class="col-span-1 xl:col-span-2 space-y-6">
        
        <GraficoResumen :totales="props.reporteCaja.totales" />

        <GraficoPrestamos :fechas="props.fechas" :prestamos="props.prestamos" />
        <GraficoRentabilidad :fechas="props.fechas" :rentabilidad="props.rentabilidad" />
      </div>
    </div>
  </div>
</template>
