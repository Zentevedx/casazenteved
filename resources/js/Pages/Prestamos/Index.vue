<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { MagnifyingGlassIcon, PlusIcon, PencilSquareIcon, DocumentTextIcon, FunnelIcon } from '@heroicons/vue/24/outline'

const props = defineProps({ prestamos: Object, filters: Object })
const search = ref(props.filters?.search || '')

// Watcher for search
watch(search, (value) => {
  router.get(route('prestamos.index'), { search: value }, {
    preserveState: true,
    replace: true,
  })
})

function obtenerIniciales(nombre) {
    if (!nombre) return '??';
  const partes = nombre.trim().split(' ')
  const nombre1 = partes[0] || ''
  const apellido = partes[1] || ''
  return (nombre1.charAt(0) + apellido.charAt(0)).toUpperCase()
}

function getAvatarGradient(id) {
    const gradients = [
        'from-blue-500 to-cyan-500', 
        'from-purple-500 to-fuchsia-500', 
        'from-emerald-500 to-teal-500',
        'from-orange-500 to-amber-500'
    ];
    return gradients[id % gradients.length];
}

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);

const getStatusColor = (status) => {
    switch(status) {
        case 'Activo': return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
        case 'Vencido': return 'bg-red-500/10 text-red-400 border-red-500/20';
        case 'Pagado': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        default: return 'bg-gray-800 text-gray-400 border-gray-700';
    }
}
</script>

<template>
  <Layout title="Gestión de Préstamos">
    <div class="bg-gray-50 dark:bg-[#0f0f0f] min-h-screen font-sans pb-20 selection:bg-indigo-500 selection:text-white transition-colors duration-300">
      
      <!-- Header -->
      <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <DocumentTextIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Registro de Préstamos</h1>
                    <p class="text-sm text-gray-500 font-medium">Control de créditos y contratos</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                <div class="relative group w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <MagnifyingGlassIcon class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors"/>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por cliente o código..."
                        class="block w-full pl-10 pr-3 py-2.5 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-gray-300 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-400 dark:placeholder-gray-600 transition-all shadow-sm"
                    />
                </div>

                <Link
                    :href="route('prestamos.create')"
                    class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-all shadow-lg hover:shadow-indigo-500/25 whitespace-nowrap"
                >
                    <PlusIcon class="w-5 h-5" />
                    Nuevo Préstamo
                </Link>
            </div>
        </div>
      </div>

      <!-- Main Table Card -->
      <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-[#1a1a1a] rounded-[24px] border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl overflow-hidden transition-all duration-300">
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-gray-500 font-bold">
                            <th class="px-6 py-5">Código</th>
                            <th class="px-6 py-5">Cliente</th>
                            <th class="px-6 py-5">Capital Prestado</th>
                            <th class="px-6 py-5">Fecha Inicio</th>
                            <th class="px-6 py-5">Estado Actual</th>
                            <th class="px-6 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                        <tr v-for="prestamo in prestamos.data" :key="prestamo.id" class="group hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-black/30 px-2 py-1 rounded border border-gray-200 dark:border-gray-800 text-xs">
                                    {{ prestamo.codigo }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div :class="['w-8 h-8 rounded-full bg-gradient-to-br flex items-center justify-center text-white font-bold text-[10px] shadow-sm', getAvatarGradient(prestamo.cliente?.id || 0)]">
                                        {{ obtenerIniciales(prestamo.cliente?.nombre || '') }}
                                    </div>
                                    <span class="text-gray-900 dark:text-gray-200 font-medium">{{ prestamo.cliente?.nombre || 'Desconocido' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(prestamo.monto) }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ prestamo.fecha_prestamo }}
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['px-3 py-1 rounded-full text-xs font-bold border inline-flex items-center gap-1.5', getStatusColor(prestamo.estado)]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ prestamo.estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <Link
                                    :href="route('prestamos.edit', prestamo.id)"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-400 hover:bg-indigo-600 hover:text-white transition-all dash-icon-btn"
                                    title="Editar Préstamo"
                                >
                                    <PencilSquareIcon class="w-4 h-4" />
                                </Link>
                                
                                <a
                                    :href="route('prestamos.pdf', prestamo.id)"
                                    target="_blank"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-100 hover:text-black transition-all dash-icon-btn"
                                    title="Descargar Contrato PDF"
                                >
                                    <DocumentTextIcon class="w-4 h-4" />
                                </a>
                            </td>
                        </tr>

                        <tr v-if="prestamos.data.length === 0">
                             <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <FunnelIcon class="w-8 h-8 opacity-50" />
                                    <p>No se encontraron préstamos.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/30">
                <Pagination :links="prestamos.links" />
            </div>

        </div>
      </div>
    </div>
  </Layout>
</template>
