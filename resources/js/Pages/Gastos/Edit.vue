<script setup>
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    gasto: Object,
});

// Convertir fecha UTC a formato YYYY-MM-DD para el input date
const fechaFormateada = props.gasto.fecha ? props.gasto.fecha.split('T')[0] : '';

const form = useForm({
    descripcion: props.gasto.descripcion,
    monto: props.gasto.monto,
    fecha: fechaFormateada,
});

const submit = () => {
    form.put(route('gastos.update', props.gasto.id));
};
</script>

<template>
    <Layout title="Editar Gasto">
        <div class="py-12 bg-black min-h-screen">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-2xl rounded-2xl">
                    <div class="p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="text-blue-500">✏️</span> Editar Gasto
                        </h2>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="descripcion" value="Descripción" />
                                <TextInput
                                    id="descripcion"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.descripcion"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.descripcion" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="monto" value="Monto (Bs)" />
                                    <TextInput
                                        id="monto"
                                        type="number"
                                        step="0.10"
                                        class="mt-1 block w-full"
                                        v-model="form.monto"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.monto" />
                                </div>
                                <div>
                                    <InputLabel for="fecha" value="Fecha" />
                                    <TextInput
                                        id="fecha"
                                        type="date"
                                        class="mt-1 block w-full"
                                        v-model="form.fecha"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.fecha" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-4 pt-4">
                                <Link :href="route('gastos.index')" class="text-gray-600 underline">Cancelar</Link>
                                <PrimaryButton class="bg-blue-600 hover:bg-blue-700" :disabled="form.processing">
                                    Actualizar Gasto
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>