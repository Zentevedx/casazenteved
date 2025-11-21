<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ clientes: Array })

const clientesFiltrados = ref([])

const form = useForm({
  codigo: '',
  cliente_id: '',
  cliente_search: '',
  monto: '',
  fecha_prestamo: '',
  multa_por_retraso: '',
  articulos: [
    { nombre_articulo: '', descripcion: '', foto_url: null, vista_previa: null }
  ]
})

function buscarClientes () {
  const q = form.cliente_search.toLowerCase()
  clientesFiltrados.value = props.clientes.filter(c =>
    c.nombre.toLowerCase().includes(q)
  )
}

function seleccionarCliente (cliente) {
  form.cliente_id = cliente.id
  form.cliente_search = cliente.nombre
  clientesFiltrados.value = []
}

function addArticulo () {
  form.articulos.push({
    nombre_articulo: '',
    descripcion: '',
    foto_url: null,
    vista_previa: null
  })
}

function handleFileChange (e, index) {
  const file = e.target.files[0]
  if (file) {
    form.articulos[index].foto_url = file
    form.articulos[index].vista_previa = URL.createObjectURL(file)
  }
}

function submit () {
  form.post('/prestamos', { forceFormData: true })
}
</script>

<template>
  <Layout title="Nuevo Préstamo">
    <div class="p-6 bg-black text-white min-h-screen rounded-t-3xl">
      <h1 class="text-3xl font-bold text-orange-500 mb-6">Registrar Préstamo</h1>

      <!-- GRID DE 2 COLUMNAS -->
      <form
        @submit.prevent="submit"
        class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto"
      >
        <!-- Columna 1 ▸ Datos del préstamo -->
        <div class="space-y-5">
          <h2 class="text-xl font-semibold text-orange-300 mb-2">
            Datos del préstamo
          </h2>

          <!-- Código -->
          <div>
            <label class="block text-sm mb-1">Código</label>
            <input
              v-model="form.codigo"
              @input="form.codigo = form.codigo.toUpperCase()"
              type="text"
              placeholder="Ej: PRE-001-A"
              class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400"
            />
            <span v-if="form.errors.codigo" class="text-red-400 text-sm">{{
              form.errors.codigo
            }}</span>
          </div>

          <!-- Cliente con búsqueda -->
          <div>
            <label class="block text-sm mb-1">Cliente</label>
            <input
              v-model="form.cliente_search"
              @input="buscarClientes"
              type="text"
              placeholder="Escriba el nombre del cliente"
              class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400"
            />
            <ul
              v-if="clientesFiltrados.length"
              class="bg-white text-black border rounded mt-1 max-h-40 overflow-y-auto"
            >
              <li
                v-for="cliente in clientesFiltrados"
                :key="cliente.id"
                @click="seleccionarCliente(cliente)"
                class="px-4 py-2 hover:bg-orange-100 cursor-pointer"
              >
                {{ cliente.nombre }}
              </li>
            </ul>
            <span v-if="form.errors.cliente_id" class="text-red-400 text-sm">{{
              form.errors.cliente_id
            }}</span>
          </div>

          <!-- Monto -->
          <div>
            <label class="block text-sm mb-1">Monto</label>
            <input
              v-model="form.monto"
              type="number"
              step="0.01"
              class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400"
            />
          </div>

          <!-- Fecha -->
          <div>
            <label class="block text-sm mb-1">Fecha de préstamo</label>
            <input
              v-model="form.fecha_prestamo"
              type="date"
              class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400"
            />
          </div>

          <!-- Multa por retraso -->
          <div>
            <label class="block text-sm mb-1">Multa por retraso</label>
            <input
              v-model="form.multa_por_retraso"
              type="number"
              step="0.01"
              placeholder="Monto de multa"
              class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400"
            />
          </div>
        </div>

        <!-- Columna 2 ▸ Artículos -->
        <div class="space-y-5">
          <h2 class="text-xl font-semibold text-orange-300 mb-2">Artículos</h2>

          <!-- Lista de artículos -->
          <div
            v-for="(articulo, index) in form.articulos"
            :key="index"
            class="border border-orange-400 p-4 rounded-xl space-y-3 relative"
          >
            <span
              class="absolute -top-3 left-3 bg-orange-500 text-xs px-2 py-0.5 rounded-full"
              >Artículo {{ index + 1 }}</span
            >

            <input
              v-model="articulo.nombre_articulo"
              @input="
                articulo.nombre_articulo =
                  articulo.nombre_articulo.toUpperCase()
              "
              placeholder="Nombre del artículo"
              class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400"
            />

            <textarea
              v-model="articulo.descripcion"
              @input="articulo.descripcion = articulo.descripcion.toUpperCase()"
              placeholder="Descripción"
              class="w-full px-4 py-2 rounded-xl bg-white text-black border border-orange-400"
            ></textarea>

            <div>
              <input
                type="file"
                @change="e => handleFileChange(e, index)"
                class="block text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"
              />
              <div v-if="articulo.vista_previa" class="mt-3">
                <img
                  :src="articulo.vista_previa"
                  alt="Foto del artículo"
                  class="w-32 h-32 object-cover rounded-xl border border-white"
                />
              </div>
            </div>
          </div>

          <!-- Botón para agregar artículos -->
          <button
            type="button"
            @click="addArticulo"
            class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-full shadow-md w-full"
          >
            + Agregar artículo
          </button>
        </div>

        <!-- Botones finales (ocupan ambas columnas) -->
        <div class="md:col-span-2 flex gap-3 justify-center mt-6">
          <button
            type="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-8 py-2 rounded-full shadow-md"
          >
            Guardar
          </button>
          <a
            :href="route('prestamos.index')"
            class="bg-white text-orange-600 hover:bg-gray-200 px-8 py-2 rounded-full border border-orange-300"
          >
            Cancelar
          </a>
        </div>
      </form>
    </div>
  </Layout>
</template>
