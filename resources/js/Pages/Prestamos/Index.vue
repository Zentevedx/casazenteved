// resources/js/Pages/Prestamos/Index.vue
<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({ prestamos: Object, filters: Object })
const search = ref(props.filters?.search || '')

watch(search, (value) => {
  router.get(route('prestamos.index'), { search: value }, {
    preserveState: true,
    replace: true,
  })
})

function generarColor(seed) {
  const colores = ['#f97316', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#ef4444', '#14b8a6']
  return colores[seed % colores.length]
}

function obtenerIniciales(nombre) {
  const partes = nombre.trim().split(' ')
  const nombre1 = partes[0] || ''
  const apellido = partes[1] || ''
  return (nombre1.charAt(0) + apellido.charAt(0)).toUpperCase()
}
</script>

<template>
  <Layout title="Préstamos">
    <div class="m-5 p-6 space-y-6 text-white bg-black min-h-screen rounded-t-3xl">
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-orange-500">Préstamos</h1>
        <a
          :href="route('prestamos.create')"
          class="bg-orange-500 hover:bg-orange-600 transition text-white px-5 py-2 rounded-full shadow-md"
        >
          + Nuevo Préstamo
        </a>
      </div>

      <div class="relative">
        <input
          v-model="search"
          type="text"
          placeholder="🔍 Buscar por cliente..."
          class="w-full px-5 py-3 rounded-full border border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white text-black placeholder-gray-500 shadow-sm"
        />
        <span
          v-if="search"
          class="absolute right-5 top-3 text-gray-400 cursor-pointer"
          @click="search = ''"
        >
          ✖
        </span>
      </div>

      <div class="overflow-x-auto rounded-2xl shadow-md bg-white text-black">
        <table class="w-full table-auto text-sm rounded-2xl">
          <thead class="bg-orange-500 text-white text-left">
            <tr>
                <th class="px-6 py-4">Codigo</th>
              <th class="px-6 py-4">Cliente</th>
              <th class="px-6 py-4">Monto</th>
              <th class="px-6 py-4">Fecha</th>
              <th class="px-6 py-4">Estado</th>
              <th class="px-6 py-4">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="prestamo in prestamos.data" :key="prestamo.id" class="border-b hover:bg-orange-50 transition">
              <td class="px-6 py-4"> {{ prestamo.codigo }}</td>

              <td class="px-6 py-4 flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shadow"
                  :style="{ backgroundColor: generarColor(prestamo.cliente?.id || 0) }"
                >
                  {{ obtenerIniciales(prestamo.cliente?.nombre || '') }}
                </div>
                <span>{{ prestamo.cliente?.nombre || 'Sin cliente' }}</span>
              </td>
              <td class="px-6 py-4">Bs {{ prestamo.monto }}</td>
              <td class="px-6 py-4">{{ prestamo.fecha_prestamo }}</td>
              <td class="px-6 py-4">{{ prestamo.estado }}</td>
              <td class="px-6 py-4 space-x-2">
                 <a
        :href="route('prestamos.edit', prestamo.id)"
        class="px-3 py-1 bg-black text-orange-400 hover:text-orange-600 rounded-full text-xs font-semibold transition"
      >
        Editar
      </a>
      
                <a
                  :href="route('prestamos.pdf', prestamo.id)"
                  target="_blank"
                  class="px-3 py-1 bg-green-600 text-white hover:bg-green-700 rounded-full text-xs font-semibold transition"
                >
                  PDF
                </a>
              </td>
            </tr>
            <tr v-if="prestamos.data.length === 0">
              <td colspan="5" class="text-center py-6 text-gray-500">No se encontraron resultados.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="prestamos.links" class="mt-4" />
    </div>
  </Layout>
</template>
