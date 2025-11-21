<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  clientes: Object,
  filters: Object,
})

const search = ref(props.filters.search || '')

// Buscar automáticamente al escribir
watch(search, (value) => {
  router.get(route('clientes.index'), { search: value }, {
    preserveState: true,
    replace: true,
  })
})
function obtenerIniciales(nombreCompleto) {
  const partes = nombreCompleto.trim().split(' ')
  const nombre = partes[0] || ''
  const apellido = partes[1] || ''
  return (nombre.charAt(0) + apellido.charAt(0)).toUpperCase()
}

function generarColor(seed) {
  const colores = [
    '#f97316', '#10b981', '#3b82f6', '#8b5cf6',
    '#ec4899', '#f59e0b', '#ef4444', '#14b8a6',
  ]
  return colores[seed % colores.length]
}


function eliminar(id) {
  if (confirm('¿Eliminar este cliente?')) {
    router.delete(route('clientes.destroy', id))
  }
}
</script>

<template>
  <Layout title="Clientes">
    <div class="m-5 p-6 space-y-6 text-white bg-black min-h-screen rounded-t-3xl">
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-orange-500">Clientes</h1>
        <a
          :href="route('clientes.create')"
          class="bg-orange-500 hover:bg-orange-600 transition text-white px-5 py-2 rounded-full shadow-md"
        >
          + Agregar Cliente
        </a>
      </div>

      <div class="relative">
        <input
          v-model="search"
          type="text"
          placeholder="🔍 Buscar por nombre o CI..."
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
              <th class="px-6 py-4">Nombre</th>
              <th class="px-6 py-4">CI</th>
              <th class="px-6 py-4">Dirección</th>
              <th class="px-6 py-4">Teléfono</th>
              <th class="px-6 py-4">Acciones</th>
            </tr>
          </thead>
          <tbody>
  <tr
    v-for="cliente in clientes.data"
    :key="cliente.id"
    class="border-b hover:bg-orange-50 transition"
  >
    <td class="px-6 py-4 flex items-center gap-3">
      <!-- Avatar con iniciales -->
      <div
        class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shadow"
        :style="{ backgroundColor: generarColor(cliente.id) }"
      >
        {{ obtenerIniciales(cliente.nombre) }}
      </div>
      <span>{{ cliente.nombre }}</span>
    </td>
    <td class="px-6 py-4">{{ cliente.ci }}</td>
    <td class="px-6 py-4">{{ cliente.direccion }}</td>
    <td class="px-6 py-4">{{ cliente.telefono }}</td>
    <td class="px-6 py-4 space-x-2">
      <a
        :href="route('clientes.edit', cliente.id)"
        class="px-3 py-1 bg-black text-orange-400 hover:text-orange-600 rounded-full text-xs font-semibold transition"
      >
        Editar
      </a>
      <button
        @click="eliminar(cliente.id)"
        class="px-3 py-1 bg-red-600 text-white hover:bg-red-700 rounded-full text-xs font-semibold transition"
      >
        Eliminar
      </button>
    </td>
  </tr>
  <tr v-if="clientes.data.length === 0">
    <td colspan="5" class="text-center py-6 text-gray-500">
      No se encontraron resultados.
    </td>
  </tr>
</tbody>

        </table>
      </div>

      <!-- ✅ Paginación real -->
      <Pagination :links="clientes.links" class="mt-4" />
    </div>
  </Layout>
</template>
