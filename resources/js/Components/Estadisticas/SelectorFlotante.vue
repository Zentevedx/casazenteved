<script setup>
const props = defineProps({
  selectedModo: String,
  selectedFecha: String,
  opcionesFechas: Array
})

const emit = defineEmits(['update:modo', 'update:fecha'])
</script>

<template>
  <div class="fixed top-4 right-4 z-50 bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 shadow-lg space-y-2 w-64">
    <div>
      <label class="block text-xs font-semibold text-gray-400 mb-1">Modo de visualización</label>
      <select
        :value="selectedModo"
        @change="emit('update:modo', $event.target.value)"
        class="w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2"
      >
        <option value="dia">📅 Día</option>
        <option value="semana">📆 Semana</option>
        <option value="mes">🗓️ Mes</option>
      </select>
    </div>

    <div>
      <label class="block text-xs font-semibold text-gray-400 mb-1">Rango de fechas</label>
      <select
        :value="selectedFecha"
        @change="emit('update:fecha', $event.target.value)"
        class="w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2"
      >
        <option v-for="op in opcionesFechas" :key="op.value" :value="op.value">
          {{ op.label }}
        </option>
      </select>
    </div>
  </div>
</template>
