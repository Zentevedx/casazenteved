<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import { PencilSquareIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({ cliente: Object })

const form = useForm({
  nombre: props.cliente.nombre,
  ci: props.cliente.ci,
  direccion: props.cliente.direccion,
  telefono: props.cliente.telefono
})

function actualizar() {
  form.put(route('clientes.update', props.cliente.id))
}
</script>

<template>
  <Layout title="Editar Cliente">
    <div class="bg-[#0f0f0f] min-h-screen text-gray-300 font-sans pb-20 selection:bg-indigo-500 selection:text-white">
      
      <div class="max-w-2xl mx-auto px-4 py-12">
        
        <!-- Header -->
        <div class="mb-8">
            <Link :href="route('clientes.index')" class="inline-flex items-center gap-2 text-gray-500 hover:text-white transition-colors mb-4 text-sm font-medium">
                <ArrowLeftIcon class="w-4 h-4" />
                Volver a la lista
            </Link>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/20">
                    <PencilSquareIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Editar Cliente</h1>
                    <p class="text-sm text-gray-500 font-medium font-mono text-amber-500">#{{ cliente.id }} - {{ cliente.nombre }}</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-[#1a1a1a] rounded-[24px] border border-gray-800 shadow-xl p-8">
            <form @submit.prevent="actualizar" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nombre Completo</label>
                        <input v-model="form.nombre" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all placeholder-gray-600"
                        />
                        <span v-if="form.errors.nombre" class="text-red-400 text-xs mt-1 block">{{ form.errors.nombre }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">CI / Identificación</label>
                        <input v-model="form.ci" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all placeholder-gray-600"
                        />
                        <span v-if="form.errors.ci" class="text-red-400 text-xs mt-1 block">{{ form.errors.ci }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Teléfono / Celular</label>
                        <input v-model="form.telefono" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all placeholder-gray-600"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Dirección Domiciliaria</label>
                        <input v-model="form.direccion" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all placeholder-gray-600"
                        />
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-800 flex items-center justify-end gap-4">
                    <Link :href="route('clientes.index')" 
                        class="px-6 py-2.5 rounded-xl border border-gray-700 text-gray-400 hover:text-white hover:bg-gray-800 transition-all font-medium text-sm">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-8 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-lg shadow-amber-500/25 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        Actualizar Datos
                    </button>
                </div>

            </form>
        </div>

      </div>
    </div>
  </Layout>
</template>
