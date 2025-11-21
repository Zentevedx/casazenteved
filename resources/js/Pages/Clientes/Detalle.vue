<script setup>
import { computed, ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps(['cliente', 'prestamos'])

// ─── Helpers ───────────────────────────────────────────────────
const getIniciales = n =>
  n.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()

const deuda = p =>
  Math.max(
    0,
    parseFloat(p.monto) -
      p.pagos
        .filter(x => x.tipo_pago === 'Capital')
        .reduce((s, x) => s + parseFloat(x.monto_pagado), 0)
  )

// ─── State ─────────────────────────────────────────────────────
const selectedId   = ref(null)
const showPagoForm = ref(false)
const search       = ref('')
const statusFilter = ref('Todos')            // Todos | Activo | Pagado | Vencido
const pago         = reactive({
  tipo_pago: 'Interes',
  monto_pagado: '',
  fecha_pago: ''
})

// ─── Computed ──────────────────────────────────────────────────
const prestamosFiltrados = computed(() => {
  let list = props.prestamos
  if (statusFilter.value !== 'Todos')
    list = list.filter(p => p.estado === statusFilter.value)

  if (search.value)
    list = list.filter(p =>
      p.codigo.toLowerCase().includes(search.value.toLowerCase())
    )

  return list
})

const selectedPrestamo = computed(
  () => props.prestamos.find(p => p.id === selectedId.value) || null
)

// ─── Actions ───────────────────────────────────────────────────
const selectPrestamo = id => {
  selectedId.value   = id
  showPagoForm.value = false
  Object.assign(pago, { tipo_pago: 'Interes', monto_pagado: '', fecha_pago: '' })
}

const togglePagoForm = () => {
  if (selectedPrestamo.value) showPagoForm.value = !showPagoForm.value
}

const submitPago = () => {
  if (!selectedPrestamo.value) return
  router
    .post(route('pagos.store'), {
      prestamo_id: selectedPrestamo.value.id,
      ...pago
    })
    .then(() => {
      Object.assign(pago, { tipo_pago: 'Interes', monto_pagado: '', fecha_pago: '' })
      showPagoForm.value = false
    })
}
</script>
<template>
  <div class="min-h-screen bg-gray-950 text-white flex">
    <!-- ─────────── SIDEBAR ─────────── -->
    <aside
      class="w-full md:w-1/3 lg:w-1/4 p-6 bg-black border-r border-gray-800
             sticky top-0 h-screen overflow-y-auto space-y-6"
    >
      <!-- Tarjeta del Cliente -->
      <div
        class="bg-gradient-to-br from-orange-500 to-orange-600 p-6 rounded-3xl 
               text-center shadow-xl"
      >
        <div
          class="w-24 h-24 mx-auto bg-black text-white rounded-full flex items-center 
                 justify-center text-4xl font-extrabold mb-3"
        >
          {{ getIniciales(cliente.nombre) }}
        </div>
        <h2 class="text-2xl font-bold mb-1">{{ cliente.nombre }}</h2>
        <p class="text-sm opacity-80">CI: {{ cliente.ci }}</p>
        <p class="text-sm opacity-80 mb-2">Tel: {{ cliente.telefono }}</p>
        <p
          class="text-xs bg-black/30 inline-block px-2 py-0.5 rounded">
          {{ cliente.direccion ?? 'Sin dirección' }}
        </p>

        <hr class="my-4 border-white/30" />

        <p class="text-sm">Préstamos: {{ props.prestamos.length }}</p>
        <p class="text-sm font-semibold text-red-300">
          Deuda total: Bs
          {{ props.prestamos.reduce((t, p) => t + deuda(p), 0).toFixed(2) }}
        </p>
      </div>

      <!-- Chips de Filtro por Estado -->
      <div class="flex gap-2 text-xs font-semibold justify-center">
        <button
          v-for="s in ['Todos', 'Activo', 'Pagado', 'Vencido']"
          :key="s"
          @click="statusFilter = s"
          :class="[
            'px-3 py-1 rounded-full transition',
            statusFilter === s
              ? 'bg-orange-600 text-white'
              : 'bg-gray-800 text-gray-300 hover:bg-gray-700'
          ]"
        >
          {{ s }}
        </button>
      </div>

      <!-- Buscador -->
      <input
        v-model="search"
        placeholder="Buscar código…"
        class="w-full mt-4 mb-3 bg-gray-800 text-white rounded-full
               px-4 py-2 placeholder-gray-400 focus:outline-none"
      />

      <!-- Lista de Préstamos -->
      <div class="space-y-2 max-h-[48vh] overflow-y-auto pr-1">
        <button
          v-for="p in prestamosFiltrados"
          :key="p.id"
          @click="selectPrestamo(p.id)"
          :class="[
            'w-full text-left px-4 py-2 rounded-lg flex justify-between items-center transition',
            selectedId === p.id
              ? 'bg-orange-600 text-white shadow-lg'
              : 'bg-gray-800 hover:bg-gray-700 text-gray-200'
          ]"
        >
          <span>{{ p.codigo }}</span>
          <span class="text-xs">Bs {{ deuda(p).toFixed(0) }}</span>
        </button>
      </div>
    </aside>
    <!-- ─────────── MAIN (detalle) ─────────── -->
    <main class="flex-1 p-8 space-y-8 overflow-y-auto">
      <!-- Si hay préstamo seleccionado -->
      <template v-if="selectedPrestamo">
        <!-- Encabezado del préstamo -->
        <div
          class="bg-gray-900 border border-orange-600/20 rounded-2xl
                 p-6 shadow-lg"
        >
          <div class="flex justify-between items-center mb-4">
            <h1 class="text-3xl font-extrabold">
              Préstamo {{ selectedPrestamo.codigo }}
            </h1>
            <button
              @click="togglePagoForm"
              class="bg-green-600 hover:bg-green-700 px-4 py-2
                     rounded text-sm shadow"
            >
              {{ showPagoForm ? 'Ocultar Pago' : 'Registrar Pago' }}
            </button>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
            <p><strong>Monto:</strong> Bs {{ (+selectedPrestamo.monto).toFixed(2) }}</p>
            <p><strong>Multa:</strong> Bs {{ (+selectedPrestamo.multa_por_retraso).toFixed(2) }}</p>
            <p><strong>Estado:</strong> {{ selectedPrestamo.estado }}</p>
            <p><strong>Fecha:</strong> {{ selectedPrestamo.fecha_prestamo }}</p>
            <p class="text-red-400 font-semibold">
              <strong>Deuda:</strong> Bs {{ deuda(selectedPrestamo).toFixed(2) }}
            </p>
          </div>
        </div>

        <!-- Formulario de pago (transición fade) -->
        <transition name="fade">
          <div
            v-if="showPagoForm"
            class="bg-gray-800 p-6 rounded-2xl shadow-md"
          >
            <h2 class="text-xl font-bold text-green-400 mb-4">
              💰 Registrar Pago
            </h2>
            <form @submit.prevent="submitPago" class="grid md:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs mb-1">Tipo</label>
                <select v-model="pago.tipo_pago" class="w-full bg-gray-700 rounded px-3 py-2">
                  <option value="Interes">Interés</option>
                  <option value="Capital">Capital</option>
                </select>
              </div>
              <div>
                <label class="block text-xs mb-1">Monto</label>
                <input
                  v-model="pago.monto_pagado"
                  type="number"
                  step="0.01"
                  class="w-full bg-gray-700 rounded px-3 py-2"
                  required
                />
              </div>
              <div>
                <label class="block text-xs mb-1">Fecha</label>
                <input
                  v-model="pago.fecha_pago"
                  type="date"
                  class="w-full bg-gray-700 rounded px-3 py-2"
                  required
                />
              </div>

              <div class="md:col-span-3 flex gap-3 mt-2">
                <button
                  type="submit"
                  class="flex-1 bg-green-600 hover:bg-green-700 rounded py-2">
                  Guardar
                </button>
                <button
                  type="button"
                  @click="showPagoForm = false"
                  class="flex-1 bg-gray-600 hover:bg-gray-700 rounded py-2">
                  Cancelar
                </button>
              </div>
            </form>
          </div>
        </transition>

        <!-- Historial de pagos y artículos -->
        <div class="grid md:grid-cols-2 gap-6">
          <!-- Pagos -->
          <div class="bg-gray-900 rounded-2xl p-4 shadow">
            <h3 class="text-lg font-bold text-purple-400 mb-3">Pagos</h3>
            <div
              v-if="selectedPrestamo.pagos.length"
              class="space-y-2 max-h-120 overflow-y-auto pr-1"
            >
              <div
                v-for="pg in selectedPrestamo.pagos"
                :key="pg.id"
                class="bg-gray-800 p-3 rounded">
                <p>
                  <strong>{{ pg.tipo_pago }}</strong>
                  — Bs {{ (+pg.monto_pagado).toFixed(2) }}
                </p>
                <p class="text-xs text-gray-400">{{ pg.fecha_pago }}</p>
              </div>
            </div>
            <p v-else class="text-gray-400 text-sm">Sin pagos registrados.</p>
          </div>

          <!-- Artículos -->
          <div class="bg-gray-900 rounded-2xl p-4 shadow">
            <h3 class="text-lg font-bold text-yellow-400 mb-3">Artículos</h3>
            <div
              v-if="selectedPrestamo.articulos.length"
              class="space-y-2 max-h-64 overflow-y-auto pr-1"
            >
              <div
                v-for="art in selectedPrestamo.articulos"
                :key="art.id"
                class="bg-gray-800 p-3 rounded">
                <p class="font-semibold">{{ art.nombre_articulo }}</p>
                <p class="text-xs text-gray-400 mb-1">Estado: {{ art.estado }}</p>
                <p class="text-xs text-gray-400 mb-1">Descripcion: {{ art.descripcion }}</p>
                <img
                  v-if="art.foto_url"
                  :src="`/storage/${art.foto_url}`"
                  class="w-full h-32 object-cover rounded"
                />
                <div
                  v-else
                  class="w-full h-32 bg-gray-700 flex items-center justify-center
                         text-xs text-gray-300 rounded">
                  Sin foto
                </div>
              </div>
            </div>
            <p v-else class="text-gray-400 text-sm">Sin artículos registrados.</p>
          </div>
        </div>
      </template>

      <!-- Placeholder si no hay selección -->
      <template v-else>
        <div class="text-center mt-20 text-gray-400">
          Selecciona un préstamo del menú para ver su detalle.
        </div>
      </template>
    </main>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
