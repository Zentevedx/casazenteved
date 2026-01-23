<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import { PencilSquareIcon, TrashIcon, ArrowLeftIcon, PhotoIcon, DocumentTextIcon, PlusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    prestamo: Object,
    clientes: Array, // Passed from controller
});

// BUG FIX: Incluimos TODOS los campos requeridos por el controlador (codigo, cliente_id, multa_por_retraso)
const form = useForm({
    codigo: props.prestamo.codigo,
    cliente_id: props.prestamo.cliente_id, // Necesario para validación
    monto: props.prestamo.monto,
    fecha_prestamo: props.prestamo.fecha_prestamo,
    multa_por_retraso: props.prestamo.multa_por_retraso, // Necesario para validación
    articulos: (props.prestamo.articulos || []).map(a => ({
        id: a.id,
        nombre_articulo: a.nombre_articulo,
        descripcion: a.descripcion,
        foto_url: a.foto_url, // Mantener URL si existe
        vista_previa: a.foto_url ? `/storage/${a.foto_url}` : null // Asumiendo storage público
    })),
});

const agregarArticulo = () => {
    form.articulos.push({ id: null, nombre_articulo: '', descripcion: '', foto_url: null, vista_previa: null });
};

const eliminarArticulo = (index) => {
    if(form.articulos.length > 1) {
         form.articulos.splice(index, 1);
    }
};

const handleFileChange = (e, index) => {
  const file = e.target.files[0]
  if (file) {
    form.articulos[index].foto_url = file
    form.articulos[index].vista_previa = URL.createObjectURL(file)
  }
}

const submit = () => {
    // Para enviar archivos con PUT en Laravel/Inertia, debemos usar POST y enviar _method='put' como campo
    // form.post envía FormData automáticamente cuando hay archivos.
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(route('prestamos.update', props.prestamo.id), {
        forceFormData: true,
    });
};
</script>

<template>
  <Layout title="Editar Préstamo">
    <div class="bg-[#0f0f0f] min-h-screen text-gray-300 font-sans pb-20 selection:bg-indigo-500 selection:text-white">
      
      <div class="max-w-5xl mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row justify-between md:items-end gap-4">
            <div>
                <Link :href="route('prestamos.index')" class="inline-flex items-center gap-2 text-gray-500 hover:text-white transition-colors mb-4 text-sm font-medium">
                    <ArrowLeftIcon class="w-4 h-4" />
                    Volver al registro
                </Link>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/20">
                        <PencilSquareIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white tracking-tight">Editar Préstamo</h1>
                        <p class="text-sm text-gray-500 font-medium font-mono text-amber-500">{{ prestamo.codigo }}</p>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Cliente</span>
                <span class="text-lg font-bold text-white">{{ prestamo.cliente?.nombre || 'Desconocido' }}</span>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- LEFT: LOAN DETAILS -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-[#1a1a1a] rounded-[24px] border border-gray-800 shadow-xl p-6">
                    <h2 class="text-sm font-bold text-amber-500 uppercase tracking-wider mb-6 flex items-center gap-2">
                        <DocumentTextIcon class="w-4 h-4" />
                        Datos del Contrato
                    </h2>
                    
                    <div class="space-y-5">
                        <!-- Código (Editable if needed, but risky) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Código Préstamo</label>
                            <input v-model="form.codigo" type="text"
                                class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all placeholder-gray-600 font-mono"
                            />
                            <span v-if="form.errors.codigo" class="text-red-400 text-xs mt-1 block">{{ form.errors.codigo }}</span>
                        </div>

                        <!-- Monto -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Monto (Bs)</label>
                            <input v-model="form.monto" type="number" step="0.01"
                                class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-amber-400 font-bold text-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all placeholder-gray-600"
                            />
                             <span v-if="form.errors.monto" class="text-red-400 text-xs mt-1 block">{{ form.errors.monto }}</span>
                        </div>

                        <!-- Fecha -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Fecha Original</label>
                            <input v-model="form.fecha_prestamo" type="date"
                                class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                            />
                             <p class="text-[10px] text-gray-500 mt-1">⚠️ Modificar la fecha afectará el cálculo de vencimientos.</p>
                        </div>

                        <!-- Multa -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Multa Diaria</label>
                            <input v-model="form.multa_por_retraso" type="number" step="0.01"
                                class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all placeholder-gray-600"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: ARTICLES -->
            <div class="lg:col-span-2 space-y-6">
                 <div class="bg-[#1a1a1a] rounded-[24px] border border-gray-800 shadow-xl p-6 min-h-full flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-sm font-bold text-indigo-400 uppercase tracking-wider flex items-center gap-2">
                            <PhotoIcon class="w-4 h-4" />
                            Prendas / Artículos
                        </h2>
                        <button type="button" @click="agregarArticulo" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1 transition-colors">
                            <PlusIcon class="w-4 h-4" />
                            Agregar Artículo
                        </button>
                    </div>

                    <div class="space-y-4 flex-grow">
                        <div v-for="(articulo, index) in form.articulos" :key="index" class="bg-black/30 border border-gray-800 rounded-2xl p-4 relative group hover:border-gray-700 transition-all">
                            
                            <span class="absolute -top-3 -left-2 bg-gray-800 text-gray-400 text-[10px] uppercase font-bold px-2 py-1 rounded-lg border border-gray-700">
                                #{{ index + 1 }}
                            </span>

                            <button v-if="form.articulos.length > 1" type="button" @click="eliminarArticulo(index)" 
                                class="absolute top-4 right-4 text-gray-600 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-black">
                                <TrashIcon class="w-5 h-5" />
                            </button>

                            <div class="flex flex-col sm:flex-row gap-6 mt-2">
                                <!-- Image Upload -->
                                <div class="shrink-0">
                                    <div class="w-32 h-32 rounded-xl bg-black border-2 border-dashed border-gray-700 flex items-center justify-center overflow-hidden relative cursor-pointer hover:border-amber-500 transition-colors group-image">
                                        <img v-if="articulo.vista_previa" :src="articulo.vista_previa" class="w-full h-full object-cover" />
                                        <div v-else class="text-center p-2">
                                            <PhotoIcon class="w-8 h-8 text-gray-600 mx-auto mb-1" />
                                            <span class="text-[10px] text-gray-500">Subir foto</span>
                                        </div>
                                        <input type="file" @change="e => handleFileChange(e, index)" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" />
                                    </div>
                                </div>

                                <!-- Fields -->
                                <div class="flex-grow space-y-4">
                                    <div>
                                        <input v-model="articulo.nombre_articulo" @input="articulo.nombre_articulo = articulo.nombre_articulo.toUpperCase()" 
                                            placeholder="Nombre del artículo"
                                            class="w-full px-0 py-2 bg-transparent border-b border-gray-700 text-white placeholder-gray-600 focus:ring-0 focus:border-amber-500 transition-all font-bold text-lg"
                                        />
                                    </div>
                                    <div>
                                        <textarea v-model="articulo.descripcion" @input="articulo.descripcion = articulo.descripcion.toUpperCase()" 
                                            placeholder="Descripción detallada"
                                            rows="2"
                                            class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-sm text-gray-300 placeholder-gray-600 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all resize-none"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <p class="text-xs text-red-500 mt-2" v-if="form.errors.articulos">Debes registrar al menos un artículo.</p>
                    </div>
                </div>
            </div>

            <!-- ACTION BAR -->
            <div class="lg:col-span-3">
                 <div class="bg-[#1a1a1a] p-4 rounded-2xl border border-gray-800 shadow-xl flex justify-end gap-4">
                     <Link :href="route('prestamos.index')" 
                        class="px-6 py-3 rounded-xl border border-gray-700 text-gray-400 hover:text-white hover:bg-gray-800 transition-all font-bold text-sm">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-8 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-lg shadow-amber-500/25 transition-all w-full sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed">
                        Guardar Cambios
                    </button>
                 </div>
            </div>

        </form>

      </div>
    </div>
  </Layout>
</template>