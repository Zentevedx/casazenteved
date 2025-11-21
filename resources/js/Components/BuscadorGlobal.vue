<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const q = ref('')
const resultados = ref(null)
const showModal = ref(false)

watch(q, async (val) => {
  if (val.length >= 2) {
    try {
      const { data } = await axios.get(route('buscar.ajax'), { params: { q: val } })
      resultados.value = data
      showModal.value = true
    } catch (e) {
      resultados.value = null
    }
  } else {
    showModal.value = false
    resultados.value = null
  }
})

const irADetalle = (cliente_id) => {
  showModal.value = false
  q.value = ''
  router.get(route('clientes.detalle', cliente_id))
}
</script>

<template>
  <div class="relative">
    <input
      v-model="q"
      type="text"
      placeholder="🔍 Buscar cliente, préstamo, artículo..."
      class="px-4 py-2 rounded-full border border-gray-500 w-72 text-sm text-black shadow focus:outline-none"
    />

    <!-- Resultados dinámicos -->
    <transition name="fade">
      <div v-if="showModal && resultados" class="absolute z-50 mt-2 w-96 bg-white text-black rounded shadow-xl max-h-96 overflow-y-auto">

        <!-- Clientes -->
        <div v-if="resultados.clientes.length">
          <h3 class="px-4 py-2 text-xs font-bold bg-orange-100">👤 Clientes</h3>
          <ul>
            <li v-for="c in resultados.clientes" :key="c.id" class="px-4 py-2 hover:bg-gray-100 cursor-pointer" @click="irADetalle(c.id)">
              {{ c.nombre }} — CI: {{ c.ci }}
            </li>
          </ul>
        </div>

        <!-- Préstamos -->
        <div v-if="resultados.prestamos.length">
          <h3 class="px-4 py-2 text-xs font-bold bg-purple-100">💼 Préstamos</h3>
          <ul>
            <li v-for="p in resultados.prestamos" :key="p.id" class="px-4 py-2 hover:bg-gray-100 cursor-pointer" @click="irADetalle(p.cliente_id)">
              Código: {{ p.codigo }} — Cliente: {{ p.cliente.nombre }}
            </li>
          </ul>
        </div>

        <!-- Artículos -->
        <div v-if="resultados.articulos.length">
          <h3 class="px-4 py-2 text-xs font-bold bg-blue-100">📦 Artículos</h3>
          <ul>
            <li v-for="a in resultados.articulos" :key="a.id" class="px-4 py-2 hover:bg-gray-100 cursor-pointer" @click="irADetalle(a.prestamo.cliente.id)">
              {{ a.nombre_articulo }} — Cliente: {{ a.prestamo.cliente.nombre }}
            </li>
          </ul>
        </div>

        <div v-if="!resultados.clientes.length && !resultados.prestamos.length && !resultados.articulos.length" class="px-4 py-2 text-gray-500 text-sm">
          No se encontraron resultados.
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
