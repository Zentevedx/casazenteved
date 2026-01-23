<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    prestamo: Object,
    isExpanded: Boolean
});

const emit = defineEmits(['toggle']);

const formatCurrency = (value) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(value);

const getAvatar = (name) => name ? name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() : '??';

const getAvatarColor = (name) => {
    if (!name) return 'bg-gray-700 text-gray-300';
    const colors = [
        'bg-red-200 text-red-900', 'bg-pink-200 text-pink-900', 'bg-purple-200 text-purple-900',
        'bg-indigo-200 text-indigo-900', 'bg-blue-200 text-blue-900', 'bg-cyan-200 text-cyan-900',
        'bg-teal-200 text-teal-900', 'bg-emerald-200 text-emerald-900', 'bg-lime-200 text-lime-900',
        'bg-orange-200 text-orange-900', 'bg-amber-200 text-amber-900'
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return colors[Math.abs(hash) % colors.length];
};

const statusBadge = computed(() => {
    if (props.prestamo.estado === 'Pagado') return { class: 'bg-emerald-100 text-emerald-800 border-emerald-200', textClass: 'text-emerald-400', label: 'PAGADO' };
    if (props.prestamo.estado === 'Vencido' || props.prestamo.esta_en_mora) return { class: 'bg-red-100 text-red-800 border-red-200', textClass: 'text-red-400', label: 'VENCIDO' };
    return { class: 'bg-indigo-100 text-indigo-800 border-indigo-200', textClass: 'text-indigo-400', label: 'ACTIVO' };
});

const cardStatusColor = computed(() => {
    // 1. Estado Pagado: Gris/Opaco
    if (props.prestamo.estado === 'Pagado') {
        return 'bg-gray-800 opacity-70 border-gray-700';
    }

    // Calcular antigüedad en meses
    const fechaPrestamo = new Date(props.prestamo.fecha_prestamo + 'T00:00:00'); // Append time to avoid timezone issues
    const now = new Date();
    const diffTime = Math.abs(now - fechaPrestamo);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
    const diffMonths = diffDays / 30.44; // Approx

    // Verificar si tiene pagos de interés
    // (Asumimos que si tiene historial o monto > 0 en intereses_generados PAGADOS? 
    // El controlador pasa 'intereses_generados' como SUMA de PAGOS tipo Interes. 
    // Si es > 0, ha pagado algo.)
    const haPagadoInteres = props.prestamo.intereses_generados > 0;

    // 2. Verde: Primer mes (activo) O Tiene pagos de interés (al día)
    // Material Design Green (Teal/Emerald 800 approx) - Más claro que el anterior
    if (diffMonths < 1 || haPagadoInteres) {
        return 'bg-[#2e7d32] border-[#1b5e20] shadow-green-500/20'; 
    }

    // 3. Amarillo: 1 a 3 meses SIN pagos
    // Material Design Orange 800 - Más vivo que el marrón anterior
    if (diffMonths >= 1 && diffMonths < 3) {
        return 'bg-[#ef6c00] border-[#e65100] shadow-orange-500/20'; 
    }

    // 4. Rojo: Más de 3 meses SIN pagos (Riesgo/Venta)
    // Material Design Red 800 - Rojo clásico legible
    if (diffMonths >= 3) {
        return 'bg-[#c62828] border-[#b71c1c] shadow-red-500/20'; 
    }

    // Fallback/Default (Material Surface)
    return 'bg-[#424242] border-gray-600 opacity-90'; 
});
</script>

<template>
    <div :class="cardStatusColor" class="rounded-[20px] overflow-hidden border hover:border-white/20 transition-all duration-300 shadow-lg">
        
        <div class="cursor-pointer h-full relative" @click="$emit('toggle')">
            
            <div v-show="!isExpanded" class="p-3 flex flex-col items-center justify-between h-64 gap-2">
                <!-- TOP: Code (Prominent) & Status -->
                <div class="w-full flex flex-col items-center gap-1 border-b border-white/10 pb-2">
                    <p class="text-lg font-black text-white font-mono tracking-tighter hover:scale-110 transition-transform">{{ prestamo.codigo }}</p>
                    <div class="bg-black/30 rounded-full px-2 py-0.5 border border-white/10">
                         <span class="text-[9px] font-bold uppercase" :class="statusBadge.textClass">
                            {{ statusBadge.label }}
                        </span>
                    </div>
                </div>

                <!-- MIDDLE: Vertical Text Info (Spine Style) - 2 Lines allowed -->
                <div class="flex-grow flex items-center justify-center py-1 overflow-hidden h-full">
                    <h3 class="text-xs font-bold text-gray-100 leading-snug tracking-wide uppercase [writing-mode:vertical-rl] rotate-180 opacity-90 line-clamp-2 text-center max-h-full w-full">
                        {{ prestamo.cliente_nombre }}
                    </h3>
                </div>

                <!-- BOTTOM: Amount & Mini Avatar -->
                <div class="w-full text-center space-y-2 pt-2 border-t border-white/10">
                    <div class="w-full bg-black/20 rounded-md py-1 border border-white/5">
                        <p class="text-xs font-bold text-white">{{ formatCurrency(prestamo.monto) }}</p>
                    </div>
                     <!-- Avatar (Demoted/Small) -->
                    <div class="flex justify-center">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-[9px] shadow-sm opacity-80"
                             :class="getAvatarColor(prestamo.cliente_nombre)">
                            {{ getAvatar(prestamo.cliente_nombre) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- EXPANDED STATE (Detailed Card) -->
            <div v-if="isExpanded" class="p-4 h-full flex flex-col">
                 <div class="flex justify-between items-start gap-3 mb-4">
                    <div class="flex items-center gap-3">
                         <Link 
                            v-if="prestamo.cliente_id" 
                            :href="route('clientes.detalle', prestamo.cliente_id)" 
                            class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm shadow-sm hover:scale-110 transition-transform"
                            :class="getAvatarColor(prestamo.cliente_nombre)"
                            @click.stop> 
                            {{ getAvatar(prestamo.cliente_nombre) }}
                        </Link>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight opacity-95">{{ prestamo.cliente_nombre }}</h3>
                             <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-white/10" :class="statusBadge.class">
                                    {{ statusBadge.label }}
                                </span>
                                <p class="text-xs text-gray-400 font-mono">{{ prestamo.codigo }}</p>
                            </div>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rotate-180" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-2 mb-4">
                     <div class="bg-black/20 p-2 rounded-lg text-center border border-white/10">
                        <span class="text-[10px] text-gray-400 uppercase block">Fecha</span>
                         <p class="text-xs font-bold text-white">{{ prestamo.fecha_prestamo }}</p>
                     </div>
                     <div class="bg-black/20 p-2 rounded-lg text-center border border-white/10">
                        <span class="text-[10px] text-gray-400 uppercase block">Monto</span>
                         <p class="text-sm font-bold text-white">{{ formatCurrency(prestamo.monto) }}</p>
                     </div>
                     <div class="bg-black/20 p-2 rounded-lg text-center border border-white/10">
                        <span class="text-[10px] text-gray-400 uppercase block">Vence</span>
                         <p class="text-xs font-bold text-white">{{ prestamo.fecha_proximo_pago }}</p>
                     </div>
                </div>
            </div>
        </div>

        <div v-show="isExpanded" class="px-4 pb-4 pt-0 bg-black/10">
            <hr class="border-gray-700/50 mb-4" />
            
            <div class="space-y-4">
                <!-- Detalles de Prendas -->
                <div>
                    <p class="text-[10px] text-gray-500 uppercase tracking-widest mb-2 font-bold flex items-center gap-1">
                        <div class="w-1 h-3 bg-indigo-500 rounded-full"></div>
                        Prendas en Garantía
                    </p>
                    <div class="bg-black/20 rounded-xl p-3 border border-gray-800">
                        <ul class="text-xs space-y-2 text-gray-300">
                            <li v-for="(art, idx) in prestamo.articulos" :key="idx" class="flex items-start gap-2">
                                <span class="bg-indigo-500/20 text-indigo-300 px-1.5 rounded text-[10px]">#{{ idx + 1 }}</span>
                                <span>
                                    <strong class="text-gray-200">{{ art.nombre }}</strong> 
                                    <span class="text-gray-500 mx-1">|</span>
                                    <span class="opacity-80">{{ art.detalle }}</span>
                                </span>
                            </li>
                            <li v-if="prestamo.articulos.length === 0" class="italic text-gray-500 pl-2">Sin prendas registradas</li>
                        </ul>
                    </div>
                </div>

                <!-- Financiero & Historial -->
                <div class="space-y-4">
                    <!-- Stats Mini -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-emerald-900/10 p-2.5 rounded-xl border border-emerald-500/20 text-center">
                            <span class="block text-emerald-500 text-[10px] font-bold uppercase mb-1">Pagado</span>
                            <span class="block font-mono text-emerald-400 font-bold">{{ formatCurrency(prestamo.capital_recuperado) }}</span>
                        </div>
                        <div class="bg-amber-900/10 p-2.5 rounded-xl border border-amber-500/20 text-center">
                            <span class="block text-amber-500 text-[10px] font-bold uppercase mb-1">Interés</span>
                            <span class="block font-mono text-amber-400 font-bold">{{ formatCurrency(prestamo.intereses_generados) }}</span>
                        </div>
                    </div>

                    <!-- Next Payment Alert -->
                     <div class="bg-[#2A2A2A] rounded-xl p-3 flex justify-between items-center border border-gray-700/50">
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Próximo Vencimiento</p>
                        <p class="text-xs font-bold text-white bg-gray-700 px-2 py-1 rounded">{{ prestamo.fecha_proximo_pago }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
