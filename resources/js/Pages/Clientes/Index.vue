<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { MagnifyingGlassIcon, UserPlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  clientes: Object,
  filters: Object,
})

const search = ref(props.filters.search || '')

watch(search, (value) => {
  router.get(route('clientes.index'), { search: value }, {
    preserveState: true,
    replace: true,
  })
})

function obtenerIniciales(nombreCompleto) {
  if (!nombreCompleto) return '??'
  const partes = nombreCompleto.trim().split(' ')
  const nombre = partes[0] || ''
  const apellido = partes.length > 1 ? partes[partes.length - 1] : ''
  return (nombre.charAt(0) + apellido.charAt(0)).toUpperCase()
}

// Consistent gradients for avatars
function getAvatarGradient(id) {
    const gradients = [
        'from-indigo-500 to-purple-500', 
        'from-emerald-500 to-teal-500', 
        'from-orange-500 to-amber-500', 
        'from-pink-500 to-rose-500', 
        'from-cyan-500 to-blue-500'
    ];
    return gradients[id % gradients.length];
}

function eliminar(id) {
  if (confirm('¿Estás seguro de eliminar este cliente? Se borrará su historial.')) {
    router.delete(route('clientes.destroy', id))
  }
}
</script>

<template>
  <Layout title="Gestión de Clientes">
    <div class="bg-gray-50 dark:bg-[#0f0f0f] min-h-screen font-sans pb-20 selection:bg-indigo-500 selection:text-white transition-colors duration-300">
      
      <!-- Header Section -->
      <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-lg shadow-orange-500/20">
                    <UserPlusIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Cartera de Clientes</h1>
                    <p class="text-sm text-gray-500 font-medium">Administración de usuarios</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                <!-- Search Bar -->
                <div class="relative group w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <MagnifyingGlassIcon class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors"/>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por nombre o CI..."
                        class="block w-full pl-10 pr-3 py-2.5 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-gray-300 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-400 dark:placeholder-gray-600 transition-all shadow-sm"
                    />
                </div>

                <!-- Add Button -->
                <Link
                    :href="route('clientes.create')"
                    class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-all shadow-lg hover:shadow-indigo-500/25 whitespace-nowrap"
                >
                    <UserPlusIcon class="w-5 h-5" />
                    Nuevo Cliente
                </Link>
            </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-[#1a1a1a] rounded-[24px] border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl overflow-hidden transition-all duration-300">
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-gray-500 font-bold">
                            <th class="px-6 py-5">Cliente</th>
                            <th class="px-6 py-5">Identificación (CI)</th>
                            <th class="px-6 py-5">Contacto</th>
                            <th class="px-6 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                        <tr v-for="cliente in clientes.data" :key="cliente.id" class="group hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div :class="['w-10 h-10 rounded-full bg-gradient-to-br flex items-center justify-center text-white font-bold text-xs shadow-inner', getAvatarGradient(cliente.id)]">
                                        {{ obtenerIniciales(cliente.nombre) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ cliente.nombre }}</p>
                                        <p class="text-xs text-gray-500">{{ cliente.direccion || 'Sin dirección' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 px-3 py-1 rounded-lg border border-gray-200 dark:border-gray-800 font-mono text-xs">
                                    {{ cliente.ci }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ cliente.telefono || ' - ' }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <Link 
                                    :href="route('clientes.edit', cliente.id)" 
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-400 hover:bg-indigo-600 hover:text-white transition-all"
                                    title="Editar"
                                >
                                    <PencilSquareIcon class="w-4 h-4" />
                                </Link>
                                <button 
                                    @click="eliminar(cliente.id)"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-400 hover:bg-red-600 hover:text-white transition-all"
                                    title="Eliminar"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                        
                        <tr v-if="clientes.data.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <MagnifyingGlassIcon class="w-8 h-8 opacity-50" />
                                    <p>No se encontraron clientes coincidentes.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/30">
                <Pagination :links="clientes.links" />
            </div>

        </div>
      </div>
    
    </div>
  </Layout>
</template>
