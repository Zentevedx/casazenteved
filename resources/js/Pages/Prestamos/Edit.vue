<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ prestamo: Object, clientes: Array })
const clientesFiltrados = ref([])
const modalError = ref(false)
const mensajeError = ref('')

const form = useForm({
  codigo: props.prestamo.codigo || '',
  cliente_id: props.prestamo.cliente_id || '',
  cliente_search: props.prestamo.cliente?.nombre || '',
  monto: props.prestamo.monto || '',
  fecha_prestamo: props.prestamo.fecha_prestamo || '',
  multa_por_retraso: props.prestamo.multa_por_retraso || '',
  articulos: props.prestamo.articulos.map(a => ({
    nombre_articulo: a.nombre_articulo || '',
    descripcion: a.descripcion || '',
    foto_url: a.foto_url || null,
    vista_previa: a.foto_url || null
  }))
})

function buscarClientes() {
  const q = form.cliente_search.toLowerCase()
  clientesFiltrados.value = props.clientes.filter(c => c.nombre.toLowerCase().includes(q))
}

function seleccionarCliente(cliente) {
  form.cliente_id = cliente.id
  form.cliente_search = cliente.nombre
  clientesFiltrados.value = []
}

function handleFileChange(e, index) {
  const file = e.target.files[0]
  if (file) {
    form.articulos[index].foto_url = file
    form.articulos[index].vista_previa = URL.createObjectURL(file)
  }
}

function addArticulo() {
  form.articulos.push({ nombre_articulo: '', descripcion: '', foto_url: null, vista_previa: null })
}

function quitarArticulo(index) {
  form.articulos.splice(index, 1)
}

function submit() {
  if (!form.codigo || !form.cliente_id || !form.monto || !form.fecha_prestamo || !form.multa_por_retraso) {
    mensajeError.value = 'Completa todos los campos obligatorios.'
    modalError.value = true
    return
  }

  try {
    form.put(route('prestamos.update', { prestamo: props.prestamo.id }), {
      forceFormData: true,
      onError: errors => {
        mensajeError.value = Object.values(errors).join('\n')
        modalError.value = true
      },
      onSuccess: () => {
        console.log('Actualizado correctamente')
      },
      onFinish: () => {
        console.log('Petición finalizada')
      }
    })
  } catch (error) {
    mensajeError.value = 'Error inesperado:\n' + (error.message || error)
    modalError.value = true
  }
}
</script>

<template>
  <Layout title="Editar Préstamo">
    <div v-if="modalError" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
      <div class="bg-white text-black rounded-xl p-6 max-w-lg w-full shadow-xl">
        <h2 class="text-lg font-semibold text-red-600 mb-4">Error</h2>
        <p class="text-sm whitespace-pre-wrap">{{ mensajeError }}</p>
        <div class="text-right mt-4">
          <button @click="modalError = false" class="bg-red-600 text-white px-4 py-1 rounded">Cerrar</button>
        </div>
      </div>
    </div>

    <div class="p-6 bg-black text-white min-h-screen rounded-t-3xl max-w-5xl mx-auto">
      <h1 class="text-3xl font-bold text-orange-500 mb-6">Editar Préstamo</h1>

      <form @submit.prevent="submit" class="grid md:grid-cols-2 gap-8">
        <div class="space-y-4">
          <div>
            <label class="block text-sm mb-1">Código</label>
            <input v-model="form.codigo" type="text" class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400" />
          </div>
          <div>
            <label class="block text-sm mb-1">Cliente</label>
            <input v-model="form.cliente_search" @input="buscarClientes" type="text" class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400" />
            <ul v-if="clientesFiltrados.length" class="bg-white text-black border rounded mt-1 max-h-40 overflow-y-auto">
              <li v-for="cliente in clientesFiltrados" :key="cliente.id" @click="seleccionarCliente(cliente)" class="px-4 py-2 hover:bg-orange-100 cursor-pointer">
                {{ cliente.nombre }}
              </li>
            </ul>
            <input type="hidden" v-model="form.cliente_id" />
          </div>
          <div>
            <label class="block text-sm mb-1">Monto</label>
            <input v-model="form.monto" type="number" step="0.01" class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400" />
          </div>
          <div>
            <label class="block text-sm mb-1">Fecha préstamo</label>
            <input v-model="form.fecha_prestamo" type="date" class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400" />
          </div>
          <div>
            <label class="block text-sm mb-1">Multa por retraso</label>
            <input v-model="form.multa_por_retraso" type="number" step="0.01" class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400" />
          </div>
        </div>

        <div class="space-y-4">
          <div v-for="(articulo, index) in form.articulos" :key="index" class="border border-orange-400 p-4 rounded-xl">
            <div>
              <label class="text-sm">Artículo {{ index + 1 }}</label>
              <input v-model="articulo.nombre_articulo" class="w-full mt-1 mb-2 px-4 py-2 rounded-full bg-white text-black border border-orange-400" />
            </div>
            <textarea v-model="articulo.descripcion" class="w-full px-4 py-2 rounded-xl bg-white text-black border border-orange-400" placeholder="Descripción"></textarea>
            <input type="file" @change="e => handleFileChange(e, index)" class="block mt-2 text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" />
            <img v-if="articulo.vista_previa" :src="articulo.vista_previa" class="w-24 h-24 mt-2 rounded border" />
            <button type="button" @click="quitarArticulo(index)" class="mt-2 bg-red-600 hover:bg-red-700 text-white px-3 py-1 text-sm rounded-full">Eliminar</button>
          </div>
          <button type="button" @click="addArticulo" class="w-full bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-full">+ Agregar Artículo</button>
        </div>

        <div class="md:col-span-2 flex gap-3 justify-center mt-6">
          <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-2 rounded-full shadow-md">Guardar cambios</button>
          <a :href="route('prestamos.index')" class="bg-white text-orange-600 hover:bg-gray-200 px-8 py-2 rounded-full border border-orange-300">Cancelar</a>
        </div>
      </form>
    </div>
  </Layout>
</template>
