<script setup>
import { ref, watch } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    movimientos: Object,
    balance: Object,
    filtros: Object,
});

// --- Filtros ---
const rangoSeleccionado = ref(props.filtros.rango || 'mes');

const filtrar = (rango) => {
    rangoSeleccionado.value = rango;
    router.get(route('caja.index'), { rango: rango }, { preserveState: true, replace: true });
};

// --- Modales ---
const showCapitalModal = ref(false);
const showGastoModal = ref(false);

// Fecha actual en formato YYYY-MM-DD para los inputs
const hoy = new Date().toISOString().split('T')[0];

const form = useForm({
    tipo: '',
    descripcion: '',
    monto: '',
    fecha: hoy, // Por defecto hoy
});

const abrirModalCapital = () => {
    form.reset();
    form.tipo = 'Capital';
    form.fecha = hoy;
    showCapitalModal.value = true;
};

const abrirModalGasto = () => {
    form.reset();
    form.tipo = 'Gasto';
    form.fecha = hoy;
    showGastoModal.value = true;
};

const cerrarModales = () => {
    showCapitalModal.value = false;
    showGastoModal.value = false;
    form.reset();
};

const submit = () => {
    form.post(route('caja.store'), {
        onSuccess: () => cerrarModales(),
        preserveScroll: true,
    });
};

// --- Formatos ---
const dinero = (monto) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(monto);
const fechaFormato = (f) => new Date(f).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', hour:'2-digit', minute:'2-digit' });

const getIcono = (origen) => {
    const mapas = { 'Gasto': '💸', 'Aporte': '🏦', 'Pago': '💰', 'Prestamo': '🤝' };
    return mapas[origen] || '📝';
};
</script>

<template>
    <Layout title="Flujo de Caja">
        <div class="min-h-screen bg-black text-white p-4 md:p-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 p-6 rounded-3xl border border-gray-700 shadow-2xl">
                    <h2 class="text-gray-400 text-xs uppercase tracking-wider font-semibold">Saldo Actual (Efectivo)</h2>
                    <div class="text-4xl md:text-5xl font-bold text-white mt-2">{{ dinero(balance.total_real) }}</div>
                </div>

                <div class="md:col-span-2 grid grid-cols-2 gap-4">
                    <div class="bg-gray-900 p-4 rounded-3xl border border-green-900/30">
                        <span class="text-gray-400 text-xs block mb-1">Ingresos ({{ rangoSeleccionado }})</span>
                        <span class="text-2xl font-bold text-green-400">+ {{ dinero(balance.ingresos_periodo) }}</span>
                    </div>
                    <div class="bg-gray-900 p-4 rounded-3xl border border-red-900/30">
                        <span class="text-gray-400 text-xs block mb-1">Egresos ({{ rangoSeleccionado }})</span>
                        <span class="text-2xl font-bold text-red-400">- {{ dinero(balance.egresos_periodo) }}</span>
                    </div>
                    
                    <button @click="abrirModalCapital" class="bg-blue-600 hover:bg-blue-500 rounded-2xl p-3 font-bold text-sm shadow-lg transition">
                        🏦 Ingresar Capital
                    </button>
                    <button @click="abrirModalGasto" class="bg-orange-600 hover:bg-orange-500 rounded-2xl p-3 font-bold text-sm shadow-lg transition">
                        💸 Registrar Gasto
                    </button>
                </div>
            </div>

            <div class="bg-white text-black rounded-3xl shadow-xl overflow-hidden">
                
                <div class="flex border-b border-gray-200 overflow-x-auto">
                    <button 
                        v-for="rango in ['hoy', 'semana', 'mes', 'todo']" 
                        :key="rango"
                        @click="filtrar(rango)"
                        class="px-6 py-4 text-sm font-medium capitalize transition-colors focus:outline-none"
                        :class="rangoSeleccionado === rango ? 'text-orange-600 border-b-2 border-orange-600 bg-orange-50' : 'text-gray-500 hover:text-gray-700'"
                    >
                        {{ rango }}
                    </button>
                </div>

                <div class="divide-y divide-gray-100">
                    <div v-for="mov in movimientos.data" :key="mov.id" class="p-4 hover:bg-gray-50 transition flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xl bg-gray-100 shadow-sm">
                                {{ getIcono(mov.origen) }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">{{ mov.descripcion }}</p>
                                <p class="text-xs text-gray-500 capitalize">{{ mov.origen }} • {{ fechaFormato(mov.fecha) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-sm" :class="mov.tipo_movimiento === 'Ingreso' ? 'text-green-600' : 'text-red-600'">
                                {{ mov.tipo_movimiento === 'Ingreso' ? '+' : '-' }} {{ dinero(mov.monto) }}
                            </p>
                            <p class="text-xs text-gray-400 font-mono">Saldo: {{ dinero(mov.saldo_caja) }}</p>
                        </div>
                    </div>
                    <div v-if="movimientos.data.length === 0" class="p-8 text-center text-gray-500">
                        No hay movimientos en este periodo.
                    </div>
                </div>

                <div class="p-4 bg-gray-50 border-t border-gray-100">
                    <Pagination :links="movimientos.links" />
                </div>
            </div>
        </div>

        <Modal :show="showGastoModal" @close="cerrarModales">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">💸 Registrar Gasto</h2>
                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <div>
                            <InputLabel value="Descripción" />
                            <TextInput v-model="form.descripcion" class="w-full" placeholder="Ej: Factura de luz" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel value="Monto" />
                                <TextInput v-model="form.monto" type="number" step="0.10" class="w-full" placeholder="0.00" required />
                            </div>
                            <div>
                                <InputLabel value="Fecha" />
                                <TextInput v-model="form.fecha" type="date" class="w-full" required />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton @click="cerrarModales">Cancelar</SecondaryButton>
                        <PrimaryButton class="bg-orange-600" :disabled="form.processing">Registrar</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showCapitalModal" @close="cerrarModales">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">🏦 Ingresar Capital</h2>
                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <div>
                            <InputLabel value="Descripción" />
                            <TextInput v-model="form.descripcion" class="w-full" placeholder="Ej: Aporte extra" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel value="Monto" />
                                <TextInput v-model="form.monto" type="number" step="0.10" class="w-full" placeholder="0.00" required />
                            </div>
                            <div>
                                <InputLabel value="Fecha" />
                                <TextInput v-model="form.fecha" type="date" class="w-full" required />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton @click="cerrarModales">Cancelar</SecondaryButton>
                        <PrimaryButton class="bg-blue-600" :disabled="form.processing">Ingresar</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </Layout>
</template>