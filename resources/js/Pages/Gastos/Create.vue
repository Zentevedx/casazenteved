<script setup>
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    descripcion: '',
    monto: '',
    fecha: new Date().toISOString().split('T')[0], // Fecha de hoy por defecto
});

const submit = () => {
    form.post(route('gastos.store'));
};
</script>

<template>
    <Layout title="Nuevo Gasto">
        <div class="py-12 bg-black min-h-screen">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-2xl rounded-2xl">
                    <div class="p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="text-orange-500">💸</span> Registrar Nuevo Gasto
                        </h2>

                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div>
                                <InputLabel for="descripcion" value="Descripción del Gasto" />
                                <TextInput
                                    id="descripcion"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm"
                                    v-model="form.descripcion"
                                    required
                                    autofocus
                                    placeholder="Ej: Pago de luz, Compra de papelería..."
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
                                        class="mt-1 block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm"
                                        v-model="form.monto"
                                        required
                                        placeholder="0.00"
                                    />
                                    <InputError class="mt-2" :message="form.errors.monto" />
                                </div>

                                <div>
                                    <InputLabel for="fecha" value="Fecha del Gasto" />
                                    <TextInput
                                        id="fecha"
                                        type="date"
                                        class="mt-1 block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm"
                                        v-model="form.fecha"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.fecha" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                                <Link
                                    :href="route('gastos.index')"
                                    class="text-sm text-gray-600 underline hover:text-gray-900"
                                >
                                    Cancelar
                                </Link>

                                <PrimaryButton
                                    class="bg-orange-600 hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-800"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Guardar Gasto
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>