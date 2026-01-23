<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import { PlusIcon, TrashIcon, ArrowLeftIcon, PhotoIcon, DocumentTextIcon } from '@heroicons/vue/24/outline'

const props = defineProps({ clientes: Array })

const clientesFiltrados = ref([])

const form = useForm({
  codigo: '',
  cliente_id: '',
  cliente_search: '',
  monto: '',
  fecha_prestamo: new Date().toISOString().split('T')[0], // Default to today
  multa_por_retraso: '0',
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

function removeArticulo (index) {
  if (form.articulos.length > 1) {
    form.articulos.splice(index, 1)
  }
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
    <div class="bg-gray-50 dark:bg-[#0f0f0f] min-h-screen font-sans pb-20 selection:bg-indigo-500 selection:text-white transition-colors duration-300">
      
      <div class="max-w-5xl mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row justify-between md:items-end gap-4">
            <div>
                <Link :href="route('prestamos.index')" class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors mb-4 text-sm font-medium">
                    <ArrowLeftIcon class="w-4 h-4" />
                    Volver al registro
                </Link>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <PlusIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Nuevo Préstamo</h1>
                        <p class="text-sm text-gray-500 font-medium">Registrar contrato y prendas</p>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- LEFT: LOAN DETAILS -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-[#1a1a1a] rounded-[24px] border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl p-6 transition-colors duration-300">
                    <h2 class="text-sm font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-wider mb-6 flex items-center gap-2">
                        <DocumentTextIcon class="w-4 h-4" />
                        Detalles del Contrato
                    </h2>
                    
                    <div class="space-y-5">
                        <!-- Código -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Código Préstamo</label>
                            <input v-model="form.codigo" @input="form.codigo = form.codigo.toUpperCase()" type="text" placeholder="Ej: PRE-2024-001"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-black/50 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-400 dark:placeholder-gray-600 font-mono"
                            />
                            <span v-if="form.errors.codigo" class="text-red-500 dark:text-red-400 text-xs mt-1 block">{{ form.errors.codigo }}</span>
                        </div>

                        <!-- Cliente -->
                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Cliente</label>
                            <input
                                v-model="form.cliente_search"
                                @input="buscarClientes"
                                type="text"
                                placeholder="Buscar cliente..."
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-black/50 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-400 dark:placeholder-gray-600"
                            />
                            <ul v-if="clientesFiltrados.length" class="absolute z-10 w-full mt-1 bg-white dark:bg-[#252525] border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-2xl max-h-48 overflow-y-auto">
                                <li v-for="cliente in clientesFiltrados" :key="cliente.id" @click="seleccionarCliente(cliente)"
                                    class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-600 text-gray-700 dark:text-gray-300 hover:text-indigo-700 dark:hover:text-white cursor-pointer transition-colors text-sm border-b border-gray-100 dark:border-gray-800 last:border-0">
                                    {{ cliente.nombre }}
                                </li>
                            </ul>
                            <span v-if="form.errors.cliente_id" class="text-red-500 dark:text-red-400 text-xs mt-1 block">Debe seleccionar un cliente registrado.</span>
                        </div>

                        <!-- Monto -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Monto a Prestar (Bs)</label>
                            <input v-model="form.monto" type="number" step="0.01" placeholder="0.00"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-black/50 border border-gray-200 dark:border-gray-700 text-indigo-600 dark:text-indigo-400 font-bold text-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-400 dark:placeholder-gray-600"
                            />
                        </div>

                        <!-- Fecha -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Fecha Inicio</label>
                            <input v-model="form.fecha_prestamo" type="date"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-black/50 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            />
                        </div>

                        <!-- Multa -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Multa Diaria (Opcional)</label>
                            <input v-model="form.multa_por_retraso" type="number" step="0.01" placeholder="0.00"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-black/50 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-400 dark:placeholder-gray-600"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: ARTICLES -->
            <div class="lg:col-span-2 space-y-6">
                 <div class="bg-white dark:bg-[#1a1a1a] rounded-[24px] border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl p-6 min-h-full flex flex-col transition-colors duration-300">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-sm font-bold text-amber-500 uppercase tracking-wider flex items-center gap-2">
                            <PhotoIcon class="w-4 h-4" />
                            Prendas / Artículos
                        </h2>
                        <button type="button" @click="addArticulo" class="text-xs font-bold text-indigo-500 dark:text-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-300 flex items-center gap-1 transition-colors">
                            <PlusIcon class="w-4 h-4" />
                            Agregar Artículo
                        </button>
                    </div>

                    <div class="space-y-4 flex-grow">
                        <div v-for="(articulo, index) in form.articulos" :key="index" class="bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 relative group hover:border-gray-300 dark:hover:border-gray-700 transition-all">
                            
                            <span class="absolute -top-3 -left-2 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[10px] uppercase font-bold px-2 py-1 rounded-lg border border-gray-200 dark:border-gray-700">
                                #{{ index + 1 }}
                            </span>

                            <button v-if="form.articulos.length > 1" type="button" @click="removeArticulo(index)" 
                                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-white dark:hover:bg-black shadow-sm">
                                <TrashIcon class="w-5 h-5" />
                            </button>

                            <div class="flex flex-col sm:flex-row gap-6 mt-2">
                                <!-- Image Upload -->
                                <div class="shrink-0">
                                    <div class="w-32 h-32 rounded-xl bg-gray-100 dark:bg-black border-2 border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center overflow-hidden relative cursor-pointer hover:border-indigo-500 transition-colors group-image">
                                        <img v-if="articulo.vista_previa" :src="articulo.vista_previa" class="w-full h-full object-cover" />
                                        <div v-else class="text-center p-2">
                                            <PhotoIcon class="w-8 h-8 text-gray-400 dark:text-gray-600 mx-auto mb-1" />
                                            <span class="text-[10px] text-gray-500">Subir foto</span>
                                        </div>
                                        <input type="file" @change="e => handleFileChange(e, index)" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" />
                                    </div>
                                </div>

                                <!-- Fields -->
                                <div class="flex-grow space-y-4">
                                    <div>
                                        <input v-model="articulo.nombre_articulo" @input="articulo.nombre_articulo = articulo.nombre_articulo.toUpperCase()" 
                                            placeholder="Nombre del artículo (Ej: Anillo de Oro)"
                                            class="w-full px-0 py-2 bg-transparent border-b border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-600 focus:ring-0 focus:border-indigo-500 transition-all font-bold text-lg"
                                        />
                                    </div>
                                    <div>
                                        <textarea v-model="articulo.descripcion" @input="articulo.descripcion = articulo.descripcion.toUpperCase()" 
                                            placeholder="Descripción detallada (estado, peso, marca, serie...)"
                                            rows="2"
                                            class="w-full px-4 py-3 rounded-xl bg-white dark:bg-black/50 border border-gray-200 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION BAR -->
            <div class="lg:col-span-3">
                 <div class="bg-white dark:bg-[#1a1a1a] p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl flex justify-end gap-4">
                     <Link :href="route('prestamos.index')" 
                        class="px-6 py-3 rounded-xl border border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-all font-bold text-sm">
                        Cancelar Operación
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 transition-all w-full sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed">
                        Confirmar y Crear Préstamo
                    </button>
                 </div>
            </div>

        </form>

      </div>
    </div>
  </Layout>
</template>
