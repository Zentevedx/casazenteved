<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import ComprobanteImagen from '@/Components/ComprobanteImagen.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { 
    ArrowLeftIcon, DocumentTextIcon, PrinterIcon, PencilSquareIcon, 
    TrashIcon, ClockIcon, CurrencyDollarIcon, BanknotesIcon, 
    ExclamationTriangleIcon, PhotoIcon, ChevronLeftIcon,
    DocumentDuplicateIcon, ReceiptPercentIcon, WalletIcon
} from '@heroicons/vue/24/outline';
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
dayjs.extend(relativeTime)

const props = defineProps({
    cliente: Object,
    prestamos: Array,
    resumenFinanciero: Object,
});

// --- MOBILE VIEW CONTROL ---
const mobileView = ref('list'); // 'list' or 'detail'

// --- LÓGICA DE SELECCIÓN Y UI ---
const selectedLoan = ref(null);

onMounted(() => {
    if (props.prestamos && props.prestamos.length > 0) {
        const activo = props.prestamos.find(p => ['Activo', 'Vencido'].includes(p.estado));
        selectedLoan.value = activo || props.prestamos[0];
    }
});

const seleccionar = (prestamo) => {
    selectedLoan.value = prestamo;
    mobileView.value = 'detail';
};

const volverALista = () => {
    mobileView.value = 'list';
};

// --- CÁLCULO INTELIGENTE DE RETRASO ---
const calcularRetraso = (prestamo) => {
    if (!['Activo', 'Vencido'].includes(prestamo.estado)) return null;

    const pagosInteres = prestamo.pagos.filter(p => p.tipo_pago === 'Interes').length;
    const fechaBase = new Date(prestamo.fecha_prestamo + 'T00:00:00');
    const fechaVencimiento = new Date(fechaBase);
    
    fechaVencimiento.setMonth(fechaBase.getMonth() + pagosInteres + 1);

    if (fechaVencimiento.getDate() !== fechaBase.getDate()) {
        fechaVencimiento.setDate(0);
    }

    const hoy = new Date();
    hoy.setHours(0,0,0,0);
    fechaVencimiento.setHours(0,0,0,0);

    if (hoy <= fechaVencimiento) return null;

    let meses = 0;
    
    let tempDate = new Date(fechaVencimiento);
    
    while(true) {
        const siguienteMes = new Date(tempDate);
        siguienteMes.setMonth(siguienteMes.getMonth() + 1);
        if (siguienteMes.getDate() !== tempDate.getDate()) {
             siguienteMes.setDate(0);
        }
        if (siguienteMes > hoy) break;
        tempDate = siguienteMes;
        meses++;
    }

    const diffTime = Math.abs(hoy - tempDate);
    const dias = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 

    let texto = [];
    if (meses > 0) texto.push(`${meses} ${meses === 1 ? 'mes' : 'meses'}`);
    if (dias > 0) texto.push(`${dias} ${dias === 1 ? 'día' : 'días'}`);
    
    return texto.length > 0 ? texto.join(' y ') + ' de retraso' : 'Vence hoy';
};

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).slice(0, 3).join('').toUpperCase();
};

const getAvatarGradient = (id) => {
    const gradients = [
        'from-blue-500 to-cyan-500', 
        'from-purple-500 to-fuchsia-500', 
        'from-emerald-500 to-teal-500', 
        'from-orange-500 to-amber-500',
        'from-rose-500 to-pink-500'
    ];
    return gradients[id % gradients.length];
};

// --- ESTADO Y MODALES ---
const showPagoModal = ref(false);
const showEstadoModal = ref(false);
const showEditarPagoModal = ref(false);
const pagoEditando = ref(null);

const formPago = useForm({
    prestamo_id: null,
    tipo: 'Interes',
    monto: '',
    fecha_pago: new Date().toISOString().split('T')[0],
});

const formEstado = useForm({ estado: '' });
const formEditPago = useForm({
    monto_pagado: '',
    fecha_pago: '',
    tipo_pago: ''
});

// --- COMPROBANTE IMAGEN ---
const showImageReceipt = ref(false);
const currentReceiptType = ref(''); // 'pago' o 'prestamo'
const currentReceiptData = ref({});

const abrirComprobantePrestamo = () => {
    if (!selectedLoan.value) return;
    currentReceiptType.value = 'prestamo';
    currentReceiptData.value = selectedLoan.value;
    showImageReceipt.value = true;
};

const abrirComprobantePago = (pago) => {
    currentReceiptType.value = 'pago';
    currentReceiptData.value = pago;
    showImageReceipt.value = true;
};

// --- ACCIONES ---
const abrirModalPago = (tipoDefecto = 'Interes') => {
    if (!selectedLoan.value) return;
    formPago.reset();
    formPago.prestamo_id = selectedLoan.value.id;
    formPago.tipo = tipoDefecto;
    formPago.fecha_pago = new Date().toISOString().split('T')[0];
    showPagoModal.value = true;
};

const guardarPago = () => {
    formPago.post(route('pagos.store'), {
        onSuccess: () => {
            showPagoModal.value = false;
            const id = selectedLoan.value.id;
            setTimeout(() => selectedLoan.value = props.prestamos.find(p => p.id === id), 100); 
        }
    });
};

const abrirModalEstado = () => {
    if (!selectedLoan.value) return;
    formEstado.estado = selectedLoan.value.estado;
    showEstadoModal.value = true;
};

const guardarEstado = () => {
    formEstado.put(route('prestamos.updateEstado', selectedLoan.value.id), {
        onSuccess: () => {
            showEstadoModal.value = false;
            const id = selectedLoan.value.id;
            setTimeout(() => selectedLoan.value = props.prestamos.find(p => p.id === id), 100);
        }
    });
};

const abrirEditarPago = (pago) => {
    pagoEditando.value = pago;
    formEditPago.monto_pagado = pago.monto_pagado;
    formEditPago.fecha_pago = pago.fecha_pago;
    formEditPago.tipo_pago = pago.tipo_pago;
    showEditarPagoModal.value = true;
};

const actualizarPago = () => {
    formEditPago.put(route('pagos.update', pagoEditando.value.id), {
        onSuccess: () => {
            showEditarPagoModal.value = false;
            const id = selectedLoan.value.id;
            setTimeout(() => selectedLoan.value = props.prestamos.find(p => p.id === id), 100);
        }
    });
};

const eliminarPago = () => {
    if(!confirm('¿Eliminar pago? Se ajustará la caja.')) return;
    router.delete(route('pagos.destroy', pagoEditando.value.id), {
        onSuccess: () => {
            showEditarPagoModal.value = false;
            const id = selectedLoan.value.id;
            setTimeout(() => selectedLoan.value = props.prestamos.find(p => p.id === id), 100);
        }
    });
};

const imprimirContrato = () => {
    if(!selectedLoan.value) return;
    window.open(route('prestamos.pdf', selectedLoan.value.id), '_blank');
};

const irAEditarPrestamo = () => {
    if(!selectedLoan.value) return;
    router.get(route('prestamos.edit', selectedLoan.value.id));
};

// --- UTILIDADES ---
const dinero = (v) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(v);
const fecha = (d) => new Date(d).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year:'numeric', timeZone: 'UTC' });

const getStatusBadge = (estado) => {
    const styles = {
        'Activo': 'bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20',
        'Vencido': 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
        'Pagado': 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
        'Cancelado': 'bg-gray-100 text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-500 dark:border-gray-700',
    };
    return styles[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-white';
};

const prestamosActivos = computed(() => (props.prestamos || []).filter(p => ['Activo', 'Vencido'].includes(p.estado)));
const prestamosHistorial = computed(() => (props.prestamos || []).filter(p => ['Pagado', 'Cancelado'].includes(p.estado)));

const calcularEdad = (fecha) => {
    if (!fecha) return null;
    return dayjs().diff(dayjs(fecha), 'year') + ' años';
}
</script>

<template>
    <Head :title="cliente.nombre" />

    <AuthenticatedLayout>
        <div class="detalle-layout flex h-[calc(100vh-65px)] bg-gray-50 dark:bg-[#0f0f0f] overflow-hidden font-sans selection:bg-indigo-500 selection:text-white">
            
            <!-- LEFT SIDEBAR: CLIENT & LIST -->
            <div 
                class="w-full md:w-80 xl:w-[400px] bg-white dark:bg-[#0f0f0f] border-r border-gray-200 dark:border-gray-800 flex flex-col z-20"
                :class="{ 'hidden md:flex': mobileView === 'detail' }"
            >
                
                <!-- Client Info Card -->
                <div class="p-5 sm:p-6 border-b border-gray-200 dark:border-gray-800 flex flex-col items-center text-center bg-white dark:bg-[#141414] shadow-sm relative shrink-0">
                    <button @click="router.visit(route('clientes.index'))" class="absolute top-4 left-4 text-gray-400 hover:text-gray-900 dark:text-gray-500 dark:hover:text-white transition-colors">
                        <ArrowLeftIcon class="w-5 h-5" />
                    </button>

                    <div 
                        class="avatar-initials w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-gradient-to-br flex items-center justify-center text-2xl sm:text-3xl text-white shadow-xl mb-3 sm:mb-4 ring-4 ring-white dark:ring-[#0f0f0f]"
                        :class="getAvatarGradient(cliente.id)"
                    >
                        {{ getInitials(cliente.nombre) }}
                    </div>
                    <h2 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white leading-tight px-4">{{ cliente.nombre }}</h2>
                    <div class="mt-2 space-y-1">
                        <div class="flex items-center justify-center gap-2 flex-wrap">
                            <p class="text-xs font-mono font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800/50 border border-gray-200 dark:border-transparent px-2 py-1 rounded inline-block">{{ cliente.ci }}</p>
                            <p v-if="cliente.fecha_nacimiento" class="text-xs font-bold text-gray-500 bg-gray-100 dark:bg-gray-800/30 border border-gray-200 dark:border-transparent px-2 py-1 rounded inline-block">
                                {{ calcularEdad(cliente.fecha_nacimiento) }}
                            </p>
                        </div>
                        <p class="text-sm font-medium text-gray-500">{{ cliente.telefono }}</p>
                    </div>

                    <!-- Financial Summary Cards (replaces Desempeño) -->
                    <div v-if="resumenFinanciero" class="mt-4 w-full grid grid-cols-2 gap-2">
                        <div class="rounded-xl border border-indigo-200 dark:border-indigo-500/20 bg-indigo-50 dark:bg-indigo-500/10 p-3 text-left">
                            <div class="flex items-center gap-1.5 mb-1">
                                <DocumentDuplicateIcon class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Préstamos</span>
                            </div>
                            <p class="text-sm font-black text-indigo-700 dark:text-indigo-300">{{ dinero(resumenFinanciero.total_prestamos) }}</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 dark:border-amber-500/20 bg-amber-50 dark:bg-amber-500/10 p-3 text-left">
                            <div class="flex items-center gap-1.5 mb-1">
                                <ReceiptPercentIcon class="w-4 h-4 text-amber-500 dark:text-amber-400" />
                                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Intereses</span>
                            </div>
                            <p class="text-sm font-black text-amber-700 dark:text-amber-300">{{ dinero(resumenFinanciero.total_intereses) }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-200 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 p-3 text-left">
                            <div class="flex items-center gap-1.5 mb-1">
                                <WalletIcon class="w-4 h-4 text-emerald-500 dark:text-emerald-400" />
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Capital</span>
                            </div>
                            <p class="text-sm font-black text-emerald-700 dark:text-emerald-300">{{ dinero(resumenFinanciero.total_pagos_capital) }}</p>
                        </div>
                        <div class="rounded-xl border border-rose-200 dark:border-rose-500/20 bg-rose-50 dark:bg-rose-500/10 p-3 text-left">
                            <div class="flex items-center gap-1.5 mb-1">
                                <ExclamationTriangleIcon class="w-4 h-4 text-rose-500 dark:text-rose-400" />
                                <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Deuda</span>
                            </div>
                            <p class="text-sm font-black text-rose-700 dark:text-rose-300">{{ dinero(resumenFinanciero.deuda_pendiente) }}</p>
                        </div>
                    </div>
                    
                    <button @click="router.visit(route('prestamos.create', { cliente_id: cliente.id }))"
                        class="mt-5 sm:mt-6 w-full py-3 bg-indigo-600 hover:bg-indigo-700 rounded-xl text-white text-sm font-bold transition shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2">
                        <span>+</span> Nuevo Préstamo
                    </button>
                </div>

                <!-- Loan List -->
                <div class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-6 custom-scrollbar bg-gray-50/50 dark:bg-[#0f0f0f]">
                    
                    <!-- Active Loans -->
                    <div v-if="prestamosActivos.length > 0">
                        <h3 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3 px-2 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> En Curso
                        </h3>
                        <div class="space-y-3">
                            <div v-for="p in prestamosActivos" :key="p.id" 
                                @click="seleccionar(p)"
                                class="cursor-pointer rounded-2xl p-4 border transition-all duration-300 relative overflow-hidden group shadow-sm bg-white dark:bg-[#141414]"
                                :class="selectedLoan?.id === p.id 
                                    ? 'border-indigo-500 ring-1 ring-indigo-500/50 shadow-md' 
                                    : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow'"
                            >
                                <div v-if="selectedLoan?.id === p.id" class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></div>
                                
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-mono font-bold text-gray-900 dark:text-gray-200">{{ p.codigo }}</span>
                                    <span :class="['text-[10px] px-2 py-0.5 rounded-full border font-bold', getStatusBadge(p.estado)]">{{ p.estado }}</span>
                                </div>
                                <div class="flex justify-between text-sm items-end">
                                    <span class="text-gray-500 text-xs flex items-center gap-1 font-medium"><ClockIcon class="w-3 h-3"/> {{ fecha(p.fecha_prestamo) }}</span>
                                    <span class="font-black text-gray-900 dark:text-white text-lg">{{ dinero(p.monto) }}</span>
                                </div>

                                <div v-if="calcularRetraso(p)" class="mt-3 text-[10px] text-red-700 dark:text-red-300 font-bold flex items-center gap-1.5 bg-red-50 dark:bg-red-500/10 rounded-lg px-2 py-1.5 border border-red-200 dark:border-red-500/20">
                                    <ExclamationTriangleIcon class="w-3.5 h-3.5 text-red-500 shrink-0" /> {{ calcularRetraso(p) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- History -->
                    <div v-if="prestamosHistorial.length > 0">
                        <h3 class="text-[10px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-widest mb-3 px-2 mt-2 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-gray-600"></span> Finalizados
                        </h3>
                        <div class="space-y-2">
                            <div v-for="p in prestamosHistorial" :key="p.id" 
                                @click="seleccionar(p)"
                                class="cursor-pointer rounded-xl p-3 border transition-all duration-200"
                                :class="selectedLoan?.id === p.id 
                                    ? 'bg-white dark:bg-[#1a1a1a] border-gray-300 dark:border-gray-600 shadow opacity-100' 
                                    : 'bg-transparent border-transparent text-gray-500 hover:bg-white dark:hover:bg-[#141414] opacity-70'"
                            >
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-mono text-sm font-medium line-through decoration-gray-400 dark:decoration-gray-600 text-gray-600 dark:text-gray-400">{{ p.codigo }}</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 border border-gray-200 dark:border-gray-700">{{ p.estado }}</span>
                                </div>
                                <div class="text-right text-sm font-bold text-gray-800 dark:text-gray-300 opacity-80">{{ dinero(p.monto) }}</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="(props.prestamos || []).length === 0" class="text-center py-12 text-gray-400 dark:text-gray-600">
                        <DocumentTextIcon class="w-10 h-10 mx-auto opacity-20 mb-2"/>
                        <p class="text-sm">Sin historial de préstamos.</p>
                    </div>
                </div>
            </div>

            <!-- RIGHT CONTENT: LOAN DETAIL -->
            <div 
                class="flex-1 bg-gray-50 dark:bg-[#0f0f0f] flex flex-col h-full overflow-hidden relative"
                :class="{ 'hidden md:flex': mobileView === 'list' }"
            >
                
                <!-- Background decoration -->
                <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-indigo-600/5 rounded-full blur-[100px] pointer-events-none"></div>

                <div v-if="selectedLoan" class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8 lg:p-10 z-10 custom-scrollbar">
                    
                    <!-- Mobile Back Button -->
                    <button 
                        @click="volverALista" 
                        class="md:hidden flex items-center gap-2 text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 mb-4 transition-colors"
                    >
                        <ChevronLeftIcon class="w-4 h-4" />
                        Volver al cliente
                    </button>

                    <!-- Header Actions -->
                    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 sm:gap-6 mb-6 sm:mb-8 pb-6 sm:pb-8 border-b border-gray-200 dark:border-gray-800">
                        <div>
                            <div class="flex items-center gap-3 sm:gap-4 mb-2 sm:mb-3">
                                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tighter">
                                    {{ selectedLoan.codigo }}
                                </h1>
                                <button @click="abrirModalEstado" class="hover:scale-105 transition-transform">
                                    <span class="text-xs px-3 py-1.5 rounded-full border uppercase font-bold tracking-wide flex items-center gap-2" :class="getStatusBadge(selectedLoan.estado)">
                                        {{ selectedLoan.estado }} <PencilSquareIcon class="w-3 h-3" />
                                    </span>
                                </button>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-sm">
                                <p class="text-gray-500 dark:text-gray-400 flex items-center gap-2 font-medium">
                                    <ClockIcon class="w-4 h-4 text-gray-400 dark:text-gray-600"/>
                                    Creado el <span class="text-gray-900 dark:text-gray-200 font-bold">{{ fecha(selectedLoan.fecha_prestamo) }}</span>
                                </p>
                                
                                <button @click="irAEditarPrestamo" class="text-gray-600 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1 text-xs font-bold underline decoration-dotted">
                                    Editar Datos
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3 w-full xl:w-auto">
                            <button @click="abrirComprobantePrestamo"
                                class="px-4 sm:px-5 py-2.5 sm:py-3 bg-white dark:bg-indigo-600/10 hover:bg-gray-50 dark:hover:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-600/30 rounded-xl font-bold transition shadow-sm dark:shadow-lg flex items-center justify-center gap-2 text-sm">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> 
                                <span class="hidden sm:inline">Imagen</span> Recibo
                            </button>
                            <button @click="imprimirContrato"
                                class="px-4 sm:px-5 py-2.5 sm:py-3 bg-white dark:bg-[#1a1a1a] hover:bg-gray-50 dark:hover:bg-[#252525] text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-700 rounded-xl font-bold transition shadow-sm dark:shadow-lg flex items-center justify-center gap-2 text-sm">
                                <PrinterIcon class="w-4 h-4 sm:w-5 sm:h-5" /> PDF
                            </button>
                            
                            <template v-if="['Activo', 'Vencido'].includes(selectedLoan.estado)">
                                <button @click="abrirModalPago('Interes')" 
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 bg-amber-500 hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-500 text-white font-bold rounded-xl shadow-md dark:shadow-lg dark:shadow-amber-900/20 transition flex items-center justify-center gap-2 group text-sm">
                                    <BanknotesIcon class="w-4 h-4 sm:w-5 sm:h-5 text-amber-100 dark:text-amber-200"/> <span class="hidden xs:inline">Cobrar</span> Interés
                                </button>
                                <button @click="abrirModalPago('Capital')" 
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 bg-emerald-500 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-bold rounded-xl shadow-md dark:shadow-lg dark:shadow-emerald-900/20 transition flex items-center justify-center gap-2 group text-sm">
                                    <CurrencyDollarIcon class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-100 dark:text-emerald-200"/> Amortizar
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Dashboard Grid for Loan -->
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 sm:gap-8">
                        
                        <!-- Left Column: Items & Summary -->
                        <div class="xl:col-span-1 space-y-4 sm:space-y-6">
                            
                            <!-- Financial Summary Card -->
                            <div class="bg-white dark:bg-[#1a1a1a] rounded-[20px] sm:rounded-[24px] p-5 sm:p-6 border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl">
                                <h3 class="text-gray-500 text-[10px] font-bold uppercase tracking-widest mb-4 sm:mb-6 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-indigo-500 rounded-full"></span> Resumen Financiero
                                </h3>
                                
                                <div class="space-y-4">
                                    <div class="flex justify-between items-end">
                                        <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">Capital Prestado</span>
                                        <span class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white">{{ dinero(selectedLoan.monto) }}</span>
                                    </div>
                                    
                                    <div class="w-full h-px bg-gray-100 dark:bg-gray-800"></div>

                                    <div v-if="selectedLoan.multa_por_retraso > 0" class="flex justify-between items-center">
                                        <span class="text-rose-600 dark:text-rose-400 text-sm font-bold flex items-center gap-1">
                                            <ExclamationTriangleIcon class="w-4 h-4" /> Multa Retraso
                                        </span>
                                        <span class="text-lg font-black text-rose-600 dark:text-rose-500">
                                            {{ dinero(selectedLoan.multa_por_retraso) }}
                                        </span>
                                    </div>
                                    
                                    <div class="w-full h-px bg-gray-100 dark:bg-gray-800"></div>
                                    
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Pagado</span>
                                        <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">
                                            {{ dinero(selectedLoan.pagos.reduce((a, b) => a + parseFloat(b.monto_pagado), 0)) }}
                                        </span>
                                    </div>

                                    <div class="bg-gray-50 dark:bg-black/30 rounded-xl p-3 text-center border border-dashed border-gray-300 dark:border-gray-800">
                                        <p class="text-xs text-gray-500 mb-1 font-medium">Saldo Restante (aprox)</p>
                                        <p class="text-gray-900 dark:text-gray-300 font-mono font-bold text-lg">
                                            {{ dinero(Math.max(0, selectedLoan.monto - selectedLoan.pagos.filter(p => p.tipo_pago === 'Capital').reduce((a, b) => a + parseFloat(b.monto_pagado), 0))) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Card -->
                            <div class="bg-white dark:bg-[#1a1a1a] rounded-[20px] sm:rounded-[24px] p-5 sm:p-6 border border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-xl">
                                <h3 class="text-gray-500 text-[10px] font-bold uppercase tracking-widest mb-4 sm:mb-6 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-amber-500 rounded-full"></span> Prendas en Garantía
                                </h3>
                                <div class="space-y-3">
                                    <div v-for="art in selectedLoan.articulos" :key="art.id" 
                                        class="flex gap-3 sm:gap-4 items-center p-3 rounded-2xl bg-gray-50 dark:bg-[#141414] hover:bg-gray-100 dark:hover:bg-[#181818] transition border border-gray-200 dark:border-gray-800">
                                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white dark:bg-black border border-gray-200 dark:border-gray-800 flex items-center justify-center overflow-hidden shrink-0">
                                            <img v-if="art.foto_url" :src="`/storage/${art.foto_url}`" class="w-full h-full object-cover" />
                                            <PhotoIcon v-else class="w-5 h-5 sm:w-6 sm:h-6 text-gray-400 dark:text-gray-700" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-gray-900 dark:text-gray-200 text-sm leading-tight truncate">{{ art.nombre_articulo }}</p>
                                            <p class="text-xs font-medium text-gray-500 mt-1 line-clamp-1">{{ art.descripcion }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Payments History -->
                        <div class="xl:col-span-2">
                            <div class="bg-white dark:bg-[#1a1a1a] rounded-[20px] sm:rounded-[24px] border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm dark:shadow-xl min-h-[300px] sm:min-h-[400px]">
                                <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-[#1a1a1a]">
                                    <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2 text-sm">
                                        Historial de Movimientos
                                    </h3>
                                    <span class="text-[10px] bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-md font-mono font-bold">{{ selectedLoan.pagos.length }} LOGS</span>
                                </div>
                                
                                <!-- Desktop Table -->
                                <div class="hidden sm:block overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="bg-gray-50 dark:bg-black/20 text-gray-500 text-[10px] uppercase tracking-wider border-b border-gray-200 dark:border-gray-800">
                                            <tr>
                                                <th class="px-6 py-4 font-bold">Fecha</th>
                                                <th class="px-6 py-4 font-bold">Concepto</th>
                                                <th class="px-6 py-4 font-bold text-right">Monto</th>
                                                <th class="px-6 py-4 font-bold text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                                            <tr v-for="pago in selectedLoan.pagos" :key="pago.id" class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 font-mono text-xs font-medium">{{ fecha(pago.fecha_pago) }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="text-[10px] font-black px-2 py-1 rounded border uppercase tracking-wider inline-block min-w-[80px] text-center"
                                                        :class="pago.tipo_pago === 'Capital' ? 'bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20' : 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20'">
                                                        {{ pago.tipo_pago }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right font-mono font-black text-gray-900 dark:text-white">{{ dinero(pago.monto_pagado) }}</td>
                                                <td class="px-6 py-4 text-center">
                                                    <div class="flex justify-center items-center gap-2">
                                                        <button @click="abrirComprobantePago(pago)" class="text-emerald-600 dark:text-emerald-500 hover:text-emerald-500 dark:hover:text-emerald-400 transition p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800" title="Descargar Imagen">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4 4m0 0L8 8m4 4v-4"></path></svg>
                                                        </button>
                                                        <button @click="abrirEditarPago(pago)" class="text-gray-500 dark:text-gray-600 hover:text-indigo-600 dark:hover:text-indigo-400 transition p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800" title="Editar">
                                                            <PencilSquareIcon class="w-4 h-4" />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="selectedLoan.pagos.length === 0">
                                                <td colspan="4" class="px-6 py-16 text-center text-gray-400 dark:text-gray-600">
                                                    <div class="flex flex-col items-center gap-3">
                                                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800/50 flex items-center justify-center">
                                                            <BanknotesIcon class="w-6 h-6 opacity-30 text-gray-500"/>
                                                        </div>
                                                        <p class="text-sm font-medium">No se han registrado pagos aún.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mobile Cards -->
                                <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-800">
                                    <div v-for="pago in selectedLoan.pagos" :key="'m-'+pago.id" class="p-4 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-[10px] font-black px-2 py-1 rounded border uppercase tracking-wider"
                                                :class="pago.tipo_pago === 'Capital' ? 'bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20' : 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20'">
                                                {{ pago.tipo_pago }}
                                            </span>
                                            <span class="font-mono font-black text-gray-900 dark:text-white text-lg">{{ dinero(pago.monto_pagado) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-500 dark:text-gray-400 font-mono text-xs font-medium flex items-center gap-1">
                                                <ClockIcon class="w-3 h-3" /> {{ fecha(pago.fecha_pago) }}
                                            </span>
                                            <div class="flex items-center gap-1">
                                                <button @click="abrirComprobantePago(pago)" class="text-emerald-600 dark:text-emerald-500 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4 4m0 0L8 8m4 4v-4"></path></svg>
                                                </button>
                                                <button @click="abrirEditarPago(pago)" class="text-gray-500 dark:text-gray-600 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                                    <PencilSquareIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="selectedLoan.pagos.length === 0" class="p-8 text-center text-gray-400 dark:text-gray-600">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800/50 flex items-center justify-center">
                                                <BanknotesIcon class="w-6 h-6 opacity-30 text-gray-500"/>
                                            </div>
                                            <p class="text-sm font-medium">No se han registrado pagos aún.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-400 dark:text-gray-800 select-none pb-20">
                    <DocumentTextIcon class="w-24 h-24 mb-4 opacity-20 dark:opacity-10 animate-pulse"/>
                    <p class="text-lg font-bold text-gray-500 dark:text-gray-600">Selecciona un préstamo</p>
                    <p class="text-sm font-medium text-gray-400 dark:text-gray-700">para ver el detalle completo</p>
                </div>

            </div>
        </div>

        <!-- MODAL PAGO -->
        <Modal :show="showPagoModal" @close="showPagoModal = false">
            <div class="bg-white dark:bg-[#1a1a1a] p-6 sm:p-8 rounded-[24px] border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-200 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-[40px]"></div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <BanknotesIcon class="w-6 h-6 text-white" />
                    </div>
                    Registrar Pago
                </h2>
                
                <div class="space-y-6 relative z-10">
                    <div>
                        <InputLabel value="Concepto del Pago" class="text-gray-600 dark:text-gray-400 font-bold" />
                        <select v-model="formPago.tipo" class="w-full mt-2 bg-gray-50 dark:bg-black border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                            <option value="Interes">Interés (Ganancia)</option>
                            <option value="Capital">Capital (Amortización)</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Monto (Bs)" class="text-gray-600 dark:text-gray-400 font-bold" />
                            <div class="relative mt-2">
                                <span class="absolute left-3 top-3 text-gray-400 dark:text-gray-500 font-bold">Bs</span>
                                <input v-model="formPago.monto" type="number" step="0.1" 
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-black border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white font-black text-lg focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400 dark:placeholder-gray-700 shadow-inner" 
                                    autofocus placeholder="0.00" 
                                />
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Fecha Real" class="text-gray-600 dark:text-gray-400 font-bold" />
                            <input v-model="formPago.fecha_pago" type="date" 
                                class="w-full mt-2 py-2.5 bg-gray-50 dark:bg-black border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 font-medium shadow-inner" 
                            />
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-800 relative z-10">
                    <button @click="showPagoModal = false" class="px-4 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors text-sm font-bold">Cancelar</button>
                    <button @click="guardarPago" :disabled="formPago.processing" 
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 dark:hover:bg-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all hover:translate-y-[-1px]">
                        Confirmar Transacción
                    </button>
                </div>
            </div>
        </Modal>

        <!-- MODAL ESTADO -->
        <Modal :show="showEstadoModal" @close="showEstadoModal = false">
             <div class="bg-white dark:bg-[#1a1a1a] p-6 sm:p-8 rounded-[24px] border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-200">
                <h2 class="text-lg font-black text-gray-900 dark:text-white mb-2">Modificar Estado</h2>
                <p class="text-xs font-medium text-amber-700 dark:text-yellow-500 mb-6 bg-amber-50 dark:bg-yellow-900/10 p-3 rounded-xl border border-amber-200 dark:border-yellow-900/20 flex gap-2 shadow-sm">
                    <ExclamationTriangleIcon class="w-4 h-4 text-amber-500 shrink-0" /> Cambiar el estado manualmente puede afectar la consistencia de los reportes automáticos.
                </p>
                
                <select v-model="formEstado.estado" class="w-full bg-gray-50 dark:bg-black border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white mb-8 p-3 font-bold shadow-inner focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="Activo">Activo</option>
                    <option value="Vencido">Vencido (Mora)</option>
                    <option value="Pagado">Pagado (Cerrado)</option>
                    <option value="Cancelado">Cancelado (Anulado)</option>
                </select>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button @click="showEstadoModal = false" class="px-4 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors text-sm font-bold">Cancelar</button>
                    <button @click="guardarEstado" class="px-6 py-2 bg-gray-900 hover:bg-black dark:bg-white text-white dark:text-black dark:hover:bg-gray-200 rounded-xl font-bold transition-colors shadow-md">
                        Guardar
                    </button>
                </div>
            </div>
        </Modal>

        <!-- MODAL EDITAR PAGO -->
        <Modal :show="showEditarPagoModal" @close="showEditarPagoModal = false">
            <div class="bg-white dark:bg-[#1a1a1a] p-6 sm:p-8 rounded-[24px] border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">Editar Movimiento</h2>
                    <button @click="eliminarPago" class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 text-xs font-bold flex items-center gap-1 px-3 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <TrashIcon class="w-4 h-4"/> Eliminar
                    </button>
                </div>
                
                <div class="space-y-4 bg-gray-50 dark:bg-black/50 p-4 sm:p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-inner">
                     <div>
                        <InputLabel value="Tipo" class="text-gray-500 text-xs mb-1 font-bold" />
                        <select v-model="formEditPago.tipo_pago" class="w-full bg-white dark:bg-[#1a1a1a] border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold text-gray-900 dark:text-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="Interes">Interés</option>
                            <option value="Capital">Capital</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Monto" class="text-gray-500 text-xs mb-1 font-bold"/>
                            <input v-model="formEditPago.monto_pagado" type="number" step="0.1" class="w-full bg-white dark:bg-[#1a1a1a] border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono font-black text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                        <div>
                            <InputLabel value="Fecha" class="text-gray-500 text-xs mb-1 font-bold"/>
                            <input v-model="formEditPago.fecha_pago" type="date" class="w-full bg-white dark:bg-[#1a1a1a] border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium text-gray-900 dark:text-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <button @click="showEditarPagoModal = false" class="px-4 py-2 text-gray-500 hover:text-gray-900 dark:hover:text-white text-sm font-bold transition-colors">Cancelar</button>
                    <button @click="actualizarPago" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 dark:hover:bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 transition-all hover:translate-y-[-1px]">Actualizar</button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>

    <!-- COMPONENTE: Imagen de Comprobante -->
    <ComprobanteImagen 
        :show="showImageReceipt" 
        :tipo="currentReceiptType"
        :data="currentReceiptData"
        :cliente="cliente"
        :prestamoReferencia="selectedLoan"
        @close="showImageReceipt = false" 
    />

</template>

<style scoped>
@font-face {
    font-family: 'UFCSans';
    src: url('/Fuentes/UFCSans-Bold.ttf') format('truetype');
    font-weight: 700;
    font-style: normal;
    font-display: swap;
}

.avatar-initials {
    font-family: 'UFCSans', sans-serif;
    font-weight: 700;
    letter-spacing: 0.05em;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    @apply bg-gray-300 border-gray-50 dark:bg-[#262626] dark:border-[#0f0f0f];
    border-radius: 10px;
    border-width: 2px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    @apply bg-gray-400 dark:bg-[#404040];
}

/* Hide scrollbars on mobile for native feel */
@media (max-width: 767px) {
    .custom-scrollbar::-webkit-scrollbar {
        width: 0;
        display: none;
    }
    .custom-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
}
</style>