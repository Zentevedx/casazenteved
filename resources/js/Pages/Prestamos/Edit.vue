<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    prestamo: Object,
    cliente: Object,
    articulos: Array,
});

const form = useForm({
    monto: props.prestamo.monto,
    fecha_prestamo: props.prestamo.fecha_prestamo,
    // Mapeamos los artículos existentes para editarlos
    articulos: props.articulos.map(a => ({
        id: a.id,
        nombre_articulo: a.nombre_articulo,
        descripcion: a.descripcion,
        observaciones: a.estado // Usamos 'estado' de la BD como 'observaciones' visuales
    })),
});

const agregarArticulo = () => {
    form.articulos.push({ id: null, nombre_articulo: '', descripcion: '', observaciones: '' });
};

const eliminarFila = (index) => {
    form.articulos.splice(index, 1);
};

const submit = () => {
    form.put(route('prestamos.update', props.prestamo.id));
};
</script>

<template>
    <Head title="Editar Préstamo" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-8 px-4">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                
                <div class="bg-amber-50 px-6 py-4 border-b border-amber-100 flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-bold text-amber-800">Corrigiendo Préstamo #{{ prestamo.codigo }}</h1>
                        <p class="text-sm text-amber-600">Cliente: {{ cliente.nombre }}</p>
                    </div>
                    <div class="text-2xl">✏️</div>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-8">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Monto del Préstamo (Bs)" />
                            <TextInput v-model="form.monto" type="number" step="0.1" class="mt-1 block w-full bg-gray-50 border-gray-300" />
                            <p class="text-xs text-red-500 mt-1" v-if="form.errors.monto">{{ form.errors.monto }}</p>
                        </div>
                        <div>
                            <InputLabel value="Fecha Original" />
                            <TextInput v-model="form.fecha_prestamo" type="date" class="mt-1 block w-full bg-gray-50 border-gray-300" />
                            <p class="text-xs text-gray-500 mt-1">⚠️ Cambiar esto afectará el cálculo de vencimientos.</p>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-700">Artículos / Prendas</h3>
                            <SecondaryButton @click="agregarArticulo" type="button" class="text-xs">
                                + Agregar Fila
                            </SecondaryButton>
                        </div>

                        <div class="space-y-3">
                            <div v-for="(item, index) in form.articulos" :key="index" 
                                 class="flex gap-3 items-start p-3 bg-gray-50 rounded-lg border border-gray-200 transition-all hover:shadow-md">
                                
                                <div class="w-8 h-8 flex items-center justify-center bg-white rounded-full text-xs font-bold text-gray-400 mt-1 shadow-sm border">
                                    {{ index + 1 }}
                                </div>
                                
                                <div class="flex-grow grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <TextInput v-model="item.nombre_articulo" placeholder="Ej: Anillo de Oro" class="w-full text-sm" />
                                    </div>
                                    <div>
                                        <TextInput v-model="item.descripcion" placeholder="Descripción detallada..." class="w-full text-sm" />
                                    </div>
                                    <div>
                                        <TextInput v-model="item.observaciones" placeholder="Estado (Nuevo, Rayado...)" class="w-full text-sm" />
                                    </div>
                                </div>

                                <button @click="eliminarFila(index)" type="button" 
                                        class="text-red-400 hover:text-red-600 p-2 hover:bg-red-50 rounded transition-colors"
                                        title="Quitar artículo">
                                    🗑️
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-red-500 mt-2" v-if="form.errors.articulos">Debes registrar al menos un artículo.</p>
                    </div>

                    <div class="flex justify-end gap-4 pt-4 border-t border-gray-100">
                        <Link :href="route('clientes.detalle', cliente.id)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                            Cancelar
                        </Link>
                        <PrimaryButton :disabled="form.processing">
                            Guardar Correcciones
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>