<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    cliente: Object,
    prestamos: Array,
});

// --- ESTADO Y LÓGICA DE REGISTRO DE PAGO ---
const showPagoModal = ref(false);
const formPago = useForm({
    prestamo_id: null,
    tipo: 'Interes',
    monto: '',
    fecha_pago: new Date().toISOString().split('T')[0],
});

const abrirModalPago = (prestamoId, tipoDefecto = 'Interes') => {
    formPago.reset();
    formPago.prestamo_id = prestamoId;
    formPago.tipo = tipoDefecto;
    formPago.fecha_pago = new Date().toISOString().split('T')[0];
    showPagoModal.value = true;
};

const guardarPago = () => {
    formPago.post(route('pagos.store'), {
        onSuccess: () => {
            showPagoModal.value = false;
            formPago.reset();
        }
    });
};

// --- NUEVO: LÓGICA DE EDICIÓN DE ESTADO DE PRÉSTAMO ---
const showEstadoModal = ref(false);
const formEstado = useForm({
    estado: ''
});
const prestamoEditandoEstado = ref(null);

const abrirModalEstado = (prestamo) => {
    prestamoEditandoEstado.value = prestamo;
    formEstado.estado = prestamo.estado;
    showEstadoModal.value = true;
};

const guardarEstado = () => {
    formEstado.put(route('prestamos.updateEstado', prestamoEditandoEstado.value.id), {
        onSuccess: () => showEstadoModal.value = false
    });
};

// --- NUEVO: LÓGICA DE EDICIÓN DE PAGO (CORRECCIÓN) ---
const showEditarPagoModal = ref(false);
const pagoEditando = ref(null);
const formEditPago = useForm({
    monto_pagado: '',
    fecha_pago: '',
    tipo_pago: ''
});

const abrirEditarPago = (pago) => {
    pagoEditando.value = pago;
    formEditPago.monto_pagado = pago.monto_pagado;
    formEditPago.fecha_pago = pago.fecha_pago;
    formEditPago.tipo_pago = pago.tipo_pago;
    showEditarPagoModal.value = true;
};

const actualizarPago = () => {
    formEditPago.put(route('pagos.update', pagoEditando.value.id), {
        onSuccess: () => showEditarPagoModal.value = false
    });
};

const eliminarPago = () => {
    if(!confirm('¿Estás seguro de eliminar este pago? Esto afectará los cálculos.')) return;
    router.delete(route('pagos.destroy', pagoEditando.value.id), {
        onSuccess: () => showEditarPagoModal.value = false
    });
};

// --- UTILIDADES ---
const formatCurrency = (value) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(value);
const formatDate = (date) => new Date(date).toLocaleDateString('es-BO');

const getStatusColor = (estado) => {
    if (estado === 'Activo') return 'bg-green-100 text-green-800 border-green-200';
    if (estado === 'Vencido') return 'bg-red-100 text-red-800 border-red-200';
    if (estado === 'Pagado') return 'bg-gray-100 text-gray-800 border-gray-200';
    return 'bg-blue-100 text-blue-800';
};
</script>

<template>
    <Head :title="`Cliente: ${cliente.nombre}`" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ cliente.nombre }}</h1>
                    <div class="flex gap-4 mt-2 text-sm text-gray-600">
                        <span class="flex items-center gap-1"><i class="font-mono text-gray-400">CI:</i> {{ cliente.ci }}</span>
                        <span class="flex items-center gap-1"><i class="font-mono text-gray-400">Tel:</i> {{ cliente.telefono || 'S/N' }}</span>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Préstamos Totales</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ prestamos.length }}</p>
                </div>
            </div>

            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    📂 Historial de Préstamos
                </h2>

                <div v-if="prestamos.length === 0" class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-gray-500">Este cliente no tiene préstamos registrados.</p>
                    <PrimaryButton class="mt-4" @click="router.visit(route('prestamos.create', { cliente_id: cliente.id }))">
                        Crear Nuevo Préstamo
                    </PrimaryButton>
                </div>

                <div v-else v-for="prestamo in prestamos" :key="prestamo.id" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex flex-wrap justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-lg text-gray-700">#{{ prestamo.codigo }}</span>
                            <button 
                                @click="abrirModalEstado(prestamo)"
                                class="px-3 py-1 rounded-full text-xs font-bold border cursor-pointer hover:opacity-80 transition-opacity flex items-center gap-1"
                                :class="getStatusColor(prestamo.estado)"
                                title="Clic para cambiar estado manualmente"
                            >
                                {{ prestamo.estado.toUpperCase() }} ✏️
                            </button>
                        </div>
                        <div class="flex gap-4 text-sm">
                            <div>
                                <span class="block text-xs text-gray-500">Monto Prestado</span>
                                <span class="font-bold text-gray-800">{{ formatCurrency(prestamo.monto) }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Fecha Inicio</span>
                                <span class="font-bold text-gray-800">{{ formatDate(prestamo.fecha_prestamo) }}</span>
                            </div>
                        </div>
                        
                        <div class="flex gap-2" v-if="prestamo.estado !== 'Pagado' && prestamo.estado !== 'Cancelado'">
                            <button @click="abrirModalPago(prestamo.id, 'Interes')" 
                                class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                + Cobrar Interés
                            </button>
                            <button @click="abrirModalPago(prestamo.id, 'Capital')" 
                                class="bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                + Amortizar Capital
                            </button>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-1 space-y-3">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Artículos en Prenda</h3>
                            <ul class="space-y-3">
                                <li v-for="articulo in prestamo.articulos" :key="articulo.id" class="flex gap-3 items-start bg-gray-50 p-2 rounded">
                                    <div class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center text-xl">📦</div>
                                    <div>
                                        <p class="font-bold text-sm text-gray-800">{{ articulo.nombre_articulo }}</p>
                                        <p class="text-xs text-gray-500">{{ articulo.descripcion }}</p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="lg:col-span-2">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Historial de Pagos</h3>
                                <span class="text-xs text-gray-500">Total Pagado: 
                                    <span class="font-bold text-emerald-600">{{ formatCurrency(prestamo.pagos.reduce((acc, p) => acc + parseFloat(p.monto_pagado), 0)) }}</span>
                                </span>
                            </div>

                            <div class="overflow-hidden rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="pago in prestamo.pagos" :key="pago.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm text-gray-600">{{ formatDate(pago.fecha_pago) }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="px-2 py-0.5 rounded text-xs font-medium"
                                                    :class="pago.tipo_pago === 'Capital' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800'">
                                                    {{ pago.tipo_pago }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-mono font-bold text-gray-700">{{ formatCurrency(pago.monto_pagado) }}</td>
                                            <td class="px-4 py-2 text-center">
                                                <button @click="abrirEditarPago(pago)" class="text-gray-400 hover:text-indigo-600 transition-colors">
                                                    ✏️
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="prestamo.pagos.length === 0">
                                            <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500 italic">No hay pagos registrados aún.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showPagoModal" @close="showPagoModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Registrar Nuevo Pago</h2>
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Tipo de Pago" />
                        <select v-model="formPago.tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Interes">Interés</option>
                            <option value="Capital">Capital (Amortización)</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Monto (Bs)" />
                        <TextInput v-model="formPago.monto" type="number" step="0.1" class="mt-1 block w-full" autofocus />
                    </div>
                    <div>
                        <InputLabel value="Fecha de Pago" />
                        <TextInput v-model="formPago.fecha_pago" type="date" class="mt-1 block w-full" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showPagoModal = false">Cancelar</SecondaryButton>
                    <PrimaryButton @click="guardarPago" :disabled="formPago.processing">Registrar Pago</PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal :show="showEstadoModal" @close="showEstadoModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-2">Editar Estado del Préstamo</h2>
                <p class="text-sm text-gray-500 mb-4">⚠️ Cambiar el estado manualmente puede afectar los reportes. Úsalo con precaución.</p>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Estado Actual" />
                        <select v-model="formEstado.estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Activo">Activo (En curso)</option>
                            <option value="Vencido">Vencido (Mora)</option>
                            <option value="Pagado">Pagado (Finalizado)</option>
                            <option value="Cancelado">Cancelado (Anulado)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showEstadoModal = false">Cancelar</SecondaryButton>
                    <PrimaryButton @click="guardarEstado" :disabled="formEstado.processing">Actualizar Estado</PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal :show="showEditarPagoModal" @close="showEditarPagoModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Corregir Registro de Pago</h2>
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Tipo de Pago" />
                        <select v-model="formEditPago.tipo_pago" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Interes">Interés</option>
                            <option value="Capital">Capital</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Monto (Bs)" />
                        <TextInput v-model="formEditPago.monto_pagado" type="number" step="0.1" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Fecha" />
                        <TextInput v-model="formEditPago.fecha_pago" type="date" class="mt-1 block w-full" />
                    </div>
                </div>

                <div class="mt-6 flex justify-between">
                    <DangerButton @click="eliminarPago">Eliminar Pago</DangerButton>
                    <div class="flex gap-3">
                        <SecondaryButton @click="showEditarPagoModal = false">Cancelar</SecondaryButton>
                        <PrimaryButton @click="actualizarPago" :disabled="formEditPago.processing">Guardar Cambios</PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>