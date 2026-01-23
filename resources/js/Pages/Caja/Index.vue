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
        <div class="min-h-screen bg-gray-50 dark:bg-black text-gray-900 dark:text-gray-100 p-4 md:p-8 transition-colors duration-300">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 dark:from-gray-900 dark:to-black p-6 rounded-3xl border border-gray-700 shadow-2xl relative overflow-hidden group">
                     <!-- Shine Effect -->
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-white opacity-5 rounded-full blur-2xl group-hover:opacity-10 transition-opacity"></div>
                    
                    <h2 class="text-gray-400 text-xs uppercase tracking-wider font-semibold">Saldo Actual (Efectivo)</h2>
                    <div class="text-4xl md:text-5xl font-bold text-white mt-2">{{ dinero(balance.total_real) }}</div>
                    <p class="text-xs text-gray-500 mt-4 font-mono">Actualizado: {{ new Date().toLocaleTimeString() }}</p>
                </div>

                <div class="md:col-span-2 grid grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-[#1a1a1a] p-4 rounded-3xl border border-gray-100 dark:border-green-900/30 shadow-sm dark:shadow-none">
                        <span class="text-gray-500 dark:text-gray-400 text-xs block mb-1">Ingresos ({{ rangoSeleccionado }})</span>
                        <span class="text-2xl font-bold text-emerald-500 dark:text-emerald-400">+ {{ dinero(balance.ingresos_periodo) }}</span>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] p-4 rounded-3xl border border-gray-100 dark:border-red-900/30 shadow-sm dark:shadow-none">
                        <span class="text-gray-500 dark:text-gray-400 text-xs block mb-1">Egresos ({{ rangoSeleccionado }})</span>
                        <span class="text-2xl font-bold text-red-500 dark:text-red-400">- {{ dinero(balance.egresos_periodo) }}</span>
                    </div>
                    
                    <button @click="abrirModalCapital" class="bg-blue-600 hover:bg-blue-500 text-white rounded-2xl p-3 font-bold text-sm shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02]">
                        🏦 Ingresar Capital
                    </button>
                    <button @click="abrirModalGasto" class="bg-orange-600 hover:bg-orange-500 text-white rounded-2xl p-3 font-bold text-sm shadow-lg shadow-orange-500/30 transition transform hover:scale-[1.02]">
                        💸 Registrar Gasto
                    </button>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1a1a1a] text-gray-900 dark:text-gray-200 rounded-3xl shadow-sm dark:shadow-xl overflow-hidden border border-gray-100 dark:border-gray-800 transition-colors duration-300">
                
                <div class="flex border-b border-gray-100 dark:border-gray-800 overflow-x-auto">
                    <button 
                        v-for="rango in ['hoy', 'semana', 'mes', 'todo']" 
                        :key="rango"
                        @click="filtrar(rango)"
                        class="px-6 py-4 text-sm font-medium capitalize transition-colors focus:outline-none"
                        :class="rangoSeleccionado === rango ? 'text-orange-600 border-b-2 border-orange-600 bg-orange-50 dark:bg-orange-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                    >
                        {{ rango }}
                    </button>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-for="mov in movimientos.data" :key="mov.id" class="p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xl bg-gray-100 dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
                                {{ getIcono(mov.origen) }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white text-sm">{{ mov.descripcion }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ mov.origen }} • {{ fechaFormato(mov.fecha) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-sm" :class="mov.tipo_movimiento === 'Ingreso' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                                {{ mov.tipo_movimiento === 'Ingreso' ? '+' : '-' }} {{ dinero(mov.monto) }}
                            </p>
                            <p class="text-xs text-gray-400 font-mono">Saldo: {{ dinero(mov.saldo_caja) }}</p>
                        </div>
                    </div>
                    <div v-if="movimientos.data.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No hay movimientos en este periodo.
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-black/20 border-t border-gray-100 dark:border-gray-800">
                    <Pagination :links="movimientos.links" />
                </div>
            </div>
        </div>

        <Modal :show="showGastoModal" @close="cerrarModales">
            <div class="p-6 bg-white dark:bg-[#1a1a1a] text-gray-900 dark:text-white">
                <h2 class="text-lg font-bold mb-4">💸 Registrar Gasto</h2>
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
                        <PrimaryButton class="bg-orange-600 hover:bg-orange-500 border-transparent text-white" :disabled="form.processing">Registrar</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showCapitalModal" @close="cerrarModales">
            <div class="p-6 bg-white dark:bg-[#1a1a1a] text-gray-900 dark:text-white">
                <h2 class="text-lg font-bold mb-4">🏦 Ingresar Capital</h2>
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
                        <PrimaryButton class="bg-blue-600 hover:bg-blue-500 border-transparent text-white" :disabled="form.processing">Ingresar</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </Layout>
</template>