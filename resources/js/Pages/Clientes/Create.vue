<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import { UserPlusIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'

const form = useForm({
  nombre: '',
  ci: '',
  direccion: '',
  telefono: '',
  fecha_nacimiento: ''
})

function guardar() {
  form.post(route('clientes.store'))
}
</script>

<template>
  <Layout title="Registrar Cliente">
    <div class="bg-[#0f0f0f] min-h-screen text-gray-300 font-sans pb-20 selection:bg-indigo-500 selection:text-white">
      
      <div class="max-w-2xl mx-auto px-4 py-12">
        
        <!-- Header -->
        <div class="mb-8">
            <Link :href="route('clientes.index')" class="inline-flex items-center gap-2 text-gray-500 hover:text-white transition-colors mb-4 text-sm font-medium">
                <ArrowLeftIcon class="w-4 h-4" />
                Volver a la lista
            </Link>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <UserPlusIcon class="w-6 h-6 text-white" />
                </div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Nuevo Cliente</h1>
            </div>
            <p class="mt-2 text-gray-500">Complete la información para registrar un nuevo cliente en el sistema.</p>
        </div>

        <!-- Form Card -->
        <div class="bg-[#1a1a1a] rounded-[24px] border border-gray-800 shadow-xl p-8">
            <form @submit.prevent="guardar" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nombre Completo</label>
                        <input v-model="form.nombre" type="text" placeholder="Ej: Juan Pérez"
                            class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-600"
                        />
                        <span v-if="form.errors.nombre" class="text-red-400 text-xs mt-1 block">{{ form.errors.nombre }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">CI / Identificación</label>
                        <input v-model="form.ci" type="text" placeholder="Ej: 1234567 LP"
                            class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-600"
                        />
                        <span v-if="form.errors.ci" class="text-red-400 text-xs mt-1 block">{{ form.errors.ci }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Teléfono / Celular</label>
                        <input v-model="form.telefono" type="text" placeholder="Ej: 777-12345"
                            class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-600"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Fecha de Nacimiento</label>
                        <input v-model="form.fecha_nacimiento" type="date"
                            class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-600 appearance-none [&::-webkit-calendar-picker-indicator]:invert"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Dirección Domiciliaria</label>
                        <input v-model="form.direccion" type="text" placeholder="Ej: Av. Principal #123, Zona Norte"
                            class="w-full px-4 py-3 rounded-xl bg-black/50 border border-gray-700 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-600"
                        />
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-800 flex items-center justify-end gap-4">
                    <Link :href="route('clientes.index')" 
                        class="px-6 py-2.5 rounded-xl border border-gray-700 text-gray-400 hover:text-white hover:bg-gray-800 transition-all font-medium text-sm">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-8 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        Guardar Cliente
                    </button>
                </div>

            </form>
        </div>

      </div>
    </div>
  </Layout>
</template>
