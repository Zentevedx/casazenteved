¡Entendido! Esa es la lógica correcta para un sistema de empeños. Se debe respetar el **"Ciclo de Corte"** (el día del mes que le toca pagar), independientemente de si paga un poco antes o después.

La lógica ajustada será:

1. **Fecha Base:** El día que se hizo el préstamo (ej: el **10** de Enero).
2. **Pagos Realizados:** Contamos cuántas veces ha pagado "Interés".
3. **Nuevo Vencimiento:** Si ha pagado 3 veces interés, el sistema asume que ha cubierto 3 meses. Entonces el próximo vencimiento es: `Fecha Original + 3 meses + 1 mes corriente`.
4. **Resultado:** Si hoy superamos esa fecha, mostramos los días de retraso.

Aquí tienes el archivo `resources/js/Pages/Clientes/Detalle.vue` actualizado con esta función `calcularRetraso` inteligente:

```vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
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

// --- LÓGICA DE SELECCIÓN Y UI ---
const selectedLoan = ref(null);

onMounted(() => {
    if (props.prestamos.length > 0) {
        const activo = props.prestamos.find(p => ['Activo', 'Vencido'].includes(p.estado));
        selectedLoan.value = activo || props.prestamos[0];
    }
});

const seleccionar = (prestamo) => {
    selectedLoan.value = prestamo;
};

// --- CÁLCULO INTELIGENTE DE RETRASO (CORREGIDO) ---
const calcularRetraso = (prestamo) => {
    // 1. Solo aplica a préstamos activos o vencidos
    if (!['Activo', 'Vencido'].includes(prestamo.estado)) return null;

    // 2. Contar cuántos pagos de INTERÉS se han hecho
    // (Asumimos que cada pago cubre un mes completo, independientemente de la fecha exacta de pago)
    const pagosInteres = prestamo.pagos.filter(p => p.tipo_pago === 'Interes').length;

    // 3. Calcular la fecha de vencimiento teórica
    // Fecha Base + (Meses Pagados + 1 Mes de plazo inicial)
    const fechaBase = new Date(prestamo.fecha_prestamo + 'T00:00:00'); // Forzar hora local
    const fechaVencimiento = new Date(fechaBase);
    
    // Sumamos los meses pagados + el mes corriente que está corriendo
    fechaVencimiento.setMonth(fechaBase.getMonth() + pagosInteres + 1);

    // Ajuste de fin de mes (ej: si era 31 de Enero y pasamos a Febrero, que no salte a Marzo)
    if (fechaVencimiento.getDate() !== fechaBase.getDate()) {
        fechaVencimiento.setDate(0);
    }

    const hoy = new Date();
    hoy.setHours(0,0,0,0);
    fechaVencimiento.setHours(0,0,0,0);

    // 4. Si hoy todavía no pasamos la fecha de vencimiento, todo bien.
    if (hoy <= fechaVencimiento) return null;

    // 5. Cálculo exacto de tiempo transcurrido desde el vencimiento
    let meses = 0;
    let dias = 0;
    
    // Clonamos para iterar
    let tempDate = new Date(fechaVencimiento);
    
    // Contamos meses completos de retraso
    while(true) {
        const siguienteMes = new Date(tempDate);
        siguienteMes.setMonth(siguienteMes.getMonth() + 1);
        // Ajuste de días para meses cortos
        if (siguienteMes.getDate() !== tempDate.getDate()) {
             siguienteMes.setDate(0);
        }
        
        if (siguienteMes > hoy) break;
        
        tempDate = siguienteMes;
        meses++;
    }

    // Contamos los días restantes
    const diffTime = Math.abs(hoy - tempDate);
    dias = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 

    // 6. Formatear mensaje
    let texto = [];
    if (meses > 0) texto.push(`${meses} ${meses === 1 ? 'mes' : 'meses'}`);
    if (dias > 0) texto.push(`${dias} ${dias === 1 ? 'día' : 'días'}`);
    
    return texto.length > 0 ? texto.join(' y ') + ' de retraso' : 'Vence hoy';
};

// --- PERSONALIZACIÓN ---
const getMaterialColor = (id, name) => {
    const colors = [
        'bg-red-500', 'bg-pink-500', 'bg-purple-500', 'bg-deep-purple-500',
        'bg-indigo-500', 'bg-blue-500', 'bg-cyan-500', 'bg-teal-500',
        'bg-green-500', 'bg-lime-600', 'bg-orange-500', 'bg-brown-500'
    ];
    const index = (id + name.length) % colors.length;
    return colors[index];
};

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const clienteColorClass = computed(() => getMaterialColor(props.cliente.id, props.cliente.nombre));

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
            // Refrescar selección tras actualización de Inertia
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

// --- UTILIDADES ---
const dinero = (v) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(v);
const fecha = (d) => new Date(d).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year:'numeric' });

const getStatusBadge = (estado) => {
    const styles = {
        'Activo': 'bg-green-500/20 text-green-400 border-green-500/50',
        'Vencido': 'bg-red-500/20 text-red-400 border-red-500/50',
        'Pagado': 'bg-blue-500/20 text-blue-400 border-blue-500/50',
        'Cancelado': 'bg-gray-700 text-gray-400 border-gray-600',
    };
    return styles[estado] || 'bg-gray-800 text-white';
};

const prestamosActivos = computed(() => props.prestamos.filter(p => ['Activo', 'Vencido'].includes(p.estado)));
const prestamosHistorial = computed(() => props.prestamos.filter(p => ['Pagado', 'Cancelado'].includes(p.estado)));
</script>

<template>
    <Head :title="cliente.nombre" />

    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-65px)] bg-black overflow-hidden">
            
            <div class="w-full md:w-1/3 lg:w-1/4 bg-gray-900 border-r border-gray-800 flex flex-col">
                
                <div class="p-6 border-b border-gray-800 flex flex-col items-center text-center bg-gray-900 z-10 shadow-lg">
                    <div 
                        class="w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold text-white shadow-2xl mb-3 ring-4 ring-black ring-opacity-50"
                        :class="clienteColorClass"
                    >
                        {{ getInitials(cliente.nombre) }}
                    </div>
                    <h2 class="text-xl font-bold text-white leading-tight">{{ cliente.nombre }}</h2>
                    <p class="text-sm text-gray-400 mt-1">CI: {{ cliente.ci }}</p>
                    <p class="text-sm text-gray-400 mt-1">TELEFONO: {{ cliente.telefono }}</p>
                    
                    <button @click="router.visit(route('prestamos.create', { cliente_id: cliente.id }))"
                        class="mt-4 w-full py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-sm text-orange-500 font-bold transition flex items-center justify-center gap-2">
                        + Nuevo Préstamo
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-6 custom-scrollbar">
                    
                    <div v-if="prestamosActivos.length > 0">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 px-2">En Curso</h3>
                        <div class="space-y-2">
                            <div v-for="p in prestamosActivos" :key="p.id" 
                                @click="seleccionar(p)"
                                class="cursor-pointer rounded-xl p-3 border transition-all duration-200 relative overflow-hidden group"
                                :class="selectedLoan?.id === p.id 
                                    ? 'bg-gray-800 border-orange-500 shadow-lg transform scale-[1.02]' 
                                    : 'bg-transparent border-transparent hover:bg-gray-800/50 hover:border-gray-700'"
                            >
                                <div v-if="selectedLoan?.id === p.id" class="absolute left-0 top-0 bottom-0 w-1 bg-orange-500"></div>
                                
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-mono font-bold text-gray-300">{{ p.codigo }}</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded border" :class="getStatusBadge(p.estado)">{{ p.estado }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">{{ fecha(p.fecha_prestamo) }}</span>
                                    <span class="font-bold text-white">{{ dinero(p.monto) }}</span>
                                </div>

                                <div v-if="calcularRetraso(p)" class="mt-2 text-[10px] text-red-400 font-bold flex items-center gap-1 bg-red-900/20 rounded px-2 py-1 w-max border border-red-900/30">
                                    <span>⏱️</span> {{ calcularRetraso(p) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="prestamosHistorial.length > 0">
                        <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3 px-2 mt-4">Historial</h3>
                        <div class="space-y-2">
                            <div v-for="p in prestamosHistorial" :key="p.id" 
                                @click="seleccionar(p)"
                                class="cursor-pointer rounded-xl p-3 border transition-all duration-200 opacity-70 hover:opacity-100"
                                :class="selectedLoan?.id === p.id 
                                    ? 'bg-gray-800 border-gray-500 shadow' 
                                    : 'bg-transparent border-transparent hover:bg-gray-800/30'"
                            >
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-mono font-bold text-gray-400 line-through">{{ p.codigo }}</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded border border-gray-700 text-gray-500">{{ p.estado }}</span>
                                </div>
                                <div class="text-right text-sm font-bold text-gray-500">{{ dinero(p.monto) }}</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="prestamos.length === 0" class="text-center py-10 text-gray-600 italic">
                        Sin préstamos registrados.
                    </div>
                </div>
            </div>

            <div class="flex-1 bg-black flex flex-col h-full overflow-hidden relative">
                
                <div class="absolute top-0 right-0 w-full h-96 opacity-10 pointer-events-none"
                    :class="clienteColorClass.replace('bg-', 'bg-gradient-to-b from-') + ' to-transparent'">
                </div>

                <div v-if="selectedLoan" class="flex-1 overflow-y-auto p-6 md:p-10 z-10">
                    
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8 pb-6 border-b border-gray-800">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-4xl font-black text-white tracking-tighter">
                                    <span class="text-gray-600 text-2xl font-normal">PRÉSTAMO</span> 
                                    {{ selectedLoan.codigo }}
                                </h1>
                                <button @click="abrirModalEstado" class="hover:scale-105 transition">
                                    <span class="text-sm px-3 py-1 rounded-full border uppercase font-bold tracking-wide" :class="getStatusBadge(selectedLoan.estado)">
                                        {{ selectedLoan.estado }} ✏️
                                    </span>
                                </button>
                            </div>
                            <div class="flex flex-wrap items-center gap-4">
                                <p class="text-gray-400">Inicio: <span class="text-gray-300">{{ fecha(selectedLoan.fecha_prestamo) }}</span></p>
                                
                                <div v-if="calcularRetraso(selectedLoan)" class="flex items-center gap-2 bg-red-600/20 text-red-400 px-4 py-1.5 rounded-full border border-red-600/40 shadow-[0_0_15px_rgba(220,38,38,0.3)] animate-pulse">
                                    <span class="text-lg">⚠️</span> 
                                    <span class="font-bold uppercase tracking-wide text-sm">{{ calcularRetraso(selectedLoan) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3" v-if="['Activo', 'Vencido'].includes(selectedLoan.estado)">
                            <button @click="abrirModalPago('Interes')" 
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-2xl shadow-lg shadow-blue-900/20 hover:shadow-blue-900/40 transition flex items-center gap-2 group">
                                💰 <span class="group-hover:translate-x-0.5 transition-transform">Cobrar Interés</span>
                            </button>
                            <button @click="abrirModalPago('Capital')" 
                                class="px-6 py-3 bg-green-600 hover:bg-green-500 text-white font-bold rounded-2xl shadow-lg shadow-green-900/20 hover:shadow-green-900/40 transition flex items-center gap-2 group">
                                📉 <span class="group-hover:translate-x-0.5 transition-transform">Amortizar</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                        
                        <div class="xl:col-span-1 space-y-6">
                            <div class="bg-gray-900/50 backdrop-blur-sm rounded-3xl p-6 border border-gray-800">
                                <h3 class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-4">Artículos en Prenda</h3>
                                <div class="space-y-4">
                                    <div v-for="art in selectedLoan.articulos" :key="art.id" 
                                        class="flex gap-4 items-start p-3 rounded-2xl hover:bg-white/5 transition border border-transparent hover:border-gray-700">
                                        <div class="w-16 h-16 rounded-xl bg-gray-800 flex items-center justify-center flex-shrink-0 overflow-hidden border border-gray-700 shadow-inner">
                                            <img v-if="art.foto_url" :src="`/storage/${art.foto_url}`" class="w-full h-full object-cover" />
                                            <span v-else class="text-2xl text-gray-600">📦</span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-white leading-tight">{{ art.nombre_articulo }}</p>
                                            <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ art.descripcion }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-900/50 backdrop-blur-sm rounded-3xl p-6 border border-gray-800">
                                <h3 class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-4">Resumen Financiero</h3>
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-gray-400">Monto Prestado</span>
                                    <span class="text-xl font-bold text-white">{{ dinero(selectedLoan.monto) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-4 border-t border-gray-800">
                                    <span class="text-gray-400">Total Recuperado</span>
                                    <span class="text-xl font-bold text-green-400">
                                        {{ dinero(selectedLoan.pagos.reduce((a, b) => a + parseFloat(b.monto_pagado), 0)) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="xl:col-span-2">
                            <div class="bg-gray-900 rounded-3xl border border-gray-800 overflow-hidden shadow-xl">
                                <div class="p-6 border-b border-gray-800 bg-gray-800/30 flex justify-between items-center">
                                    <h3 class="font-bold text-white flex items-center gap-2">
                                        <span class="text-orange-500">📄</span> Historial de Pagos
                                    </h3>
                                    <span class="text-xs bg-gray-800 text-gray-400 px-3 py-1 rounded-full font-mono">{{ selectedLoan.pagos.length }} mov.</span>
                                </div>
                                
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="bg-black/20 text-gray-500 text-xs uppercase">
                                            <tr>
                                                <th class="px-6 py-4 font-bold tracking-wider">Fecha</th>
                                                <th class="px-6 py-4 font-bold tracking-wider">Concepto</th>
                                                <th class="px-6 py-4 font-bold tracking-wider text-right">Monto</th>
                                                <th class="px-6 py-4 font-bold tracking-wider text-center">Opción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-800">
                                            <tr v-for="pago in selectedLoan.pagos" :key="pago.id" class="hover:bg-white/5 transition group">
                                                <td class="px-6 py-4 text-sm text-gray-300 font-mono">{{ fecha(pago.fecha_pago) }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="text-[11px] font-bold px-2 py-1 rounded border uppercase tracking-wider"
                                                        :class="pago.tipo_pago === 'Capital' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20'">
                                                        {{ pago.tipo_pago }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right font-mono font-bold text-white text-base">{{ dinero(pago.monto_pagado) }}</td>
                                                <td class="px-6 py-4 text-center">
                                                    <button @click="abrirEditarPago(pago)" class="text-gray-600 hover:text-orange-500 transition p-2 hover:bg-orange-500/10 rounded-lg">✏️</button>
                                                </td>
                                            </tr>
                                            <tr v-if="selectedLoan.pagos.length === 0">
                                                <td colspan="4" class="px-6 py-12 text-center text-gray-600 italic flex flex-col items-center">
                                                    <span class="text-2xl mb-2 opacity-20">📭</span>
                                                    No se han registrado pagos aún.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-700 select-none">
                    <div class="text-8xl mb-6 opacity-10 animate-pulse">👈</div>
                    <p class="text-xl font-light text-gray-500">Selecciona un préstamo para ver el detalle</p>
                </div>

            </div>
        </div>

        <Modal :show="showPagoModal" @close="showPagoModal = false">
            <div class="p-6">
                <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                    <span class="text-2xl">💰</span> Registrar Pago
                </h2>
                <div class="space-y-5">
                    <div>
                        <InputLabel value="Concepto del Pago" />
                        <select v-model="formPago.tipo" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            <option value="Interes">Interés (Ganancia)</option>
                            <option value="Capital">Capital (Devolución)</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Monto (Bs)" />
                            <TextInput v-model="formPago.monto" type="number" step="0.1" class="w-full mt-1 font-bold text-lg" autofocus placeholder="0.00" />
                        </div>
                        <div>
                            <InputLabel value="Fecha Real" />
                            <TextInput v-model="formPago.fecha_pago" type="date" class="w-full mt-1" />
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <SecondaryButton @click="showPagoModal = false">Cancelar</SecondaryButton>
                    <PrimaryButton @click="guardarPago" :disabled="formPago.processing" class="bg-orange-600 hover:bg-orange-700">Confirmar Pago</PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal :show="showEstadoModal" @close="showEstadoModal = false">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Estado del Préstamo</h2>
                <p class="text-sm text-gray-500 mb-4">Cambiar el estado manualmente puede afectar los reportes.</p>
                <select v-model="formEstado.estado" class="w-full border-gray-300 rounded-lg mb-6 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                    <option value="Activo">🟢 Activo</option>
                    <option value="Vencido">🔴 Vencido</option>
                    <option value="Pagado">🔵 Pagado</option>
                    <option value="Cancelado">⚫ Cancelado</option>
                </select>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="showEstadoModal = false">Cancelar</SecondaryButton>
                    <PrimaryButton @click="guardarEstado" class="bg-orange-600">Guardar Cambio</PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal :show="showEditarPagoModal" @close="showEditarPagoModal = false">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Editar Registro</h2>
                    <button @click="eliminarPago" class="text-red-500 hover:text-red-700 text-sm font-bold underline">Eliminar este pago</button>
                </div>
                
                <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                     <div>
                        <InputLabel value="Tipo" />
                        <select v-model="formEditPago.tipo_pago" class="w-full border-gray-300 rounded-md">
                            <option value="Interes">Interés</option>
                            <option value="Capital">Capital</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Monto" />
                        <TextInput v-model="formEditPago.monto_pagado" type="number" step="0.1" class="w-full font-mono" />
                    </div>
                    <div>
                        <InputLabel value="Fecha" />
                        <TextInput v-model="formEditPago.fecha_pago" type="date" class="w-full" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showEditarPagoModal = false">Cancelar</SecondaryButton>
                    <PrimaryButton @click="actualizarPago" class="bg-blue-600 hover:bg-blue-700">Guardar Cambios</PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #374151; 
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #4b5563; 
}
</style>

```