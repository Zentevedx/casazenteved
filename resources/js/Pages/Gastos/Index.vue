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
        <div class="m-5 p-6 space-y-6 text-white bg-black min-h-screen rounded-t-3xl shadow-2xl">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-orange-500">💰 Control de Gastos</h1>
                    <p class="text-gray-400 text-sm">Registra y administra los egresos del negocio.</p>
                </div>
                <Link
                    :href="route('gastos.create')"
                    class="bg-orange-600 hover:bg-orange-700 transition text-white px-6 py-2 rounded-full shadow-lg flex items-center gap-2 font-bold"
                >
                    + Nuevo Gasto
                </Link>
            </div>

            <div class="relative max-w-lg">
                <input
                    v-model="search"
                    type="text"
                    placeholder="🔍 Buscar por descripción..."
                    class="w-full px-5 py-3 rounded-full border border-orange-500/50 bg-gray-900 text-white placeholder-gray-500 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                />
            </div>

            <div class="overflow-hidden rounded-2xl shadow-lg border border-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-300">
                        <thead class="bg-gradient-to-r from-orange-600 to-orange-500 text-white uppercase font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Fecha</th>
                                <th class="px-6 py-4">Descripción</th>
                                <th class="px-6 py-4 text-right">Monto</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 bg-gray-900">
                            <tr
                                v-for="gasto in gastos.data"
                                :key="gasto.id"
                                class="hover:bg-gray-800 transition duration-150"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">{{ new Date(gasto.fecha).toLocaleDateString() }}</td>
                                <td class="px-6 py-4 font-medium text-white">{{ gasto.descripcion }}</td>
                                <td class="px-6 py-4 text-right font-bold text-red-400">
                                    - {{ formatearDinero(gasto.monto) }}
                                </td>
                                <td class="px-6 py-4 flex justify-center gap-2">
                                    <Link
                                        :href="route('gastos.edit', gasto.id)"
                                        class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-md text-xs font-semibold transition"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        @click="eliminar(gasto.id)"
                                        class="px-3 py-1 bg-red-600 hover:bg-red-500 text-white rounded-md text-xs font-semibold transition"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="gastos.data.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    No hay gastos registrados que coincidan con tu búsqueda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination :links="gastos.links" />
        </div>
    </Layout>
</template>