<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    gastos: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('gastos.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
});

function eliminar(id) {
    if (confirm('¿Estás seguro de eliminar este gasto?')) {
        router.delete(route('gastos.destroy', id));
    }
}

function formatearDinero(monto) {
    return new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(monto);
}
</script>

<template>
    <Layout title="Control de Gastos">
        <div class="bg-gray-50 dark:bg-[#0f0f0f] min-h-screen font-sans pb-20 selection:bg-indigo-500 selection:text-white transition-colors duration-300">
            
            <!-- Header -->
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-lg shadow-orange-500/20">
                            <span class="text-2xl">💰</span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Control de Gastos</h1>
                            <p class="text-sm text-gray-500 font-medium">Registra y administra los egresos del negocio.</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                        <div class="relative group w-full md:w-80">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="🔍 Buscar por descripción..."
                                class="block w-full px-4 py-2.5 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-gray-300 text-sm rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent placeholder-gray-400 dark:placeholder-gray-600 transition-all shadow-sm"
                            />
                        </div>

                        <Link
                            :href="route('gastos.create')"
                            class="flex items-center justify-center gap-2 bg-orange-600 hover:bg-orange-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-all shadow-lg hover:shadow-orange-500/25 whitespace-nowrap"
                        >
                            + Nuevo Gasto
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Main Listing -->
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-[#1a1a1a] rounded-[24px] border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl overflow-hidden transition-all duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-gray-500 font-bold">
                                    <th class="px-6 py-5">Fecha</th>
                                    <th class="px-6 py-5">Descripción</th>
                                    <th class="px-6 py-5 text-right">Monto</th>
                                    <th class="px-6 py-5 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                                <tr
                                    v-for="gasto in gastos.data"
                                    :key="gasto.id"
                                    class="group hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ new Date(gasto.fecha).toLocaleDateString() }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ gasto.descripcion }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-red-500 dark:text-red-400">
                                        - {{ formatearDinero(gasto.monto) }}
                                    </td>
                                    <td class="px-6 py-4 flex justify-center gap-2">
                                        <Link
                                            :href="route('gastos.edit', gasto.id)"
                                            class="px-3 py-1 bg-gray-100 dark:bg-gray-800 hover:bg-indigo-600 hover:text-white text-gray-500 dark:text-gray-400 rounded-lg text-xs font-bold transition-all"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            @click="eliminar(gasto.id)"
                                            class="px-3 py-1 bg-gray-100 dark:bg-gray-800 hover:bg-red-600 hover:text-white text-gray-500 dark:text-gray-400 rounded-lg text-xs font-bold transition-all"
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="gastos.data.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                        No hay gastos registrados que coincidan con tu búsqueda.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-transparent mt-4">
                    <Pagination :links="gastos.links" />
                </div>
            </div>
        </div>
    </Layout>
</template>