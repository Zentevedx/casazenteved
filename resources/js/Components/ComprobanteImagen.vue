<template>
  <!-- Modal Contenedor -->
  <Transition name="fade">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" style="background: #000000;">
      
      <div class="relative w-full max-w-md flex flex-col my-8" style="background: #000000;">
        
        <!-- Header Modal Preview -->
        <div class="p-4 border-b border-gray-900 flex justify-between items-center" style="background:#0a0a0a;">
          <h3 class="text-white font-bold text-lg flex items-center gap-2">
             <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
             Generador de Recibo HD
          </h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-white transition-colors bg-gray-900 hover:bg-red-600 rounded-full p-1.5 focus:outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>

        <!-- SCROLLABLE AREA -->
        <div class="flex-1 overflow-y-auto max-h-[75vh]" style="background:#ffffff;">
          <div class="p-5 flex flex-col items-center" style="background:#ffffff;">

            <!-- ================================ -->
            <!--  ZONA CAPTURADA (RECEIPT)         -->
            <!-- ================================ -->
            <div ref="receiptContent"
              class="w-full rounded-2xl overflow-hidden flex flex-col relative"
              style="background:#0a0a0a; border: 3px solid #1a1a1a; box-shadow: 0 0 0 1px #2a2a2a, 0 8px 40px rgba(0,0,0,0.35);">

              <!-- Watermark fondo -->
              <div class="absolute inset-0 flex items-center justify-center pointer-events-none" style="opacity:0.04; z-index:0;">
                <img src="/logo3.png" class="w-56" alt="" />
              </div>

              <!-- ──── HEADER ──── -->
              <div class="relative overflow-hidden shrink-0" style="background:#0a0a0a; padding: 18px 20px;">
                <!-- Red accent bar -->
                <div class="absolute top-0 right-0 h-full w-2" style="background:#dc2626;"></div>
                <div class="absolute -top-8 -right-8 w-24 h-24 rounded-full" style="background:rgba(220,38,38,0.3); filter:blur(20px);"></div>

                <div class="flex items-center gap-3 relative z-10">
                  <!-- Logo -->
                  <div style="background:#fff; border-radius:10px; width:44px; height:44px; display:flex; align-items:center; justify-content:center; flex-shrink:0; padding:4px;">
                    <img src="/logo3.png" alt="Logo" style="width:100%; height:100%; object-fit:contain;" />
                  </div>
                  <div>
                    <h2 style="color:#fff; font-size:17px; font-weight:900; letter-spacing:-0.5px; text-transform:uppercase; margin:0; line-height:1;">CASA DE EMPEÑOS ZENTEVEDLU</h2>
                    <p style="color:#9ca3af; font-size:9px; margin:4px 0 0; text-transform:uppercase; letter-spacing:2px; font-weight:700;">
                      {{ tipo === 'pago' ? 'COMPROBANTE DE PAGO' : 'COMPROBANTE DE PRÉSTAMO' }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- ──── DARK BODY START ──── -->
              <div style="background:#111111; flex:1; display:flex; flex-direction:column;">

              <!-- ──── AVATAR + CLIENTE ──── -->
              <div class="relative z-10 flex items-center gap-3 px-5 pt-5 pb-3" style="border-bottom: 1px solid #222;">
                <!-- Avatar con iniciales -->
                <div :style="avatarStyle" style="width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:18px; font-weight:900; color:#fff; letter-spacing:-1px; text-shadow: 0 1px 3px rgba(0,0,0,0.4);">
                  {{ getInitials(cliente.nombre + ' ' + (cliente.apellidos || '')) }}
                </div>
                <div style="flex:1; min-width:0;">
                  <p style="font-size:8px; color:#6b7280; font-weight:700; text-transform:uppercase; letter-spacing:2px; margin:0 0 3px;">Cliente</p>
                  <p style="font-size:13px; font-weight:900; color:#f9fafb; margin:0; line-height:1.3; word-break:break-word;">
                    {{ cliente.nombre }} {{ cliente.apellidos || '' }}
                  </p>
                  <p v-if="cliente.ci" style="font-size:9px; color:#4b5563; font-weight:600; margin:3px 0 0; font-family:monospace;">CI: {{ cliente.ci }}</p>
                </div>
                <!-- Código -->
                <div style="text-align:right; flex-shrink:0;">
                  <p style="font-size:8px; color:#6b7280; font-weight:700; text-transform:uppercase; letter-spacing:2px; margin:0 0 3px;">Código</p>
                  <p style="font-size:16px; font-weight:900; color:#ef4444; font-family:monospace; letter-spacing:3px; margin:0; line-height:1;">{{ computedCodigo }}</p>
                </div>
              </div>

              <!-- ──── BODY ──── -->
              <div class="relative z-10 px-5 py-4 flex-1 flex flex-col gap-3">

                <!-- Fecha + Detalle Row -->
                <div style="display:flex; gap:8px;">
                  <div style="flex:1; background:#1a1a1a; border-radius:10px; padding:10px 12px; border:1px solid #222;">
                    <p style="font-size:8px; color:#6b7280; font-weight:700; text-transform:uppercase; letter-spacing:2px; margin:0 0 4px;">Fecha</p>
                    <p style="font-size:11px; font-weight:700; color:#e5e7eb; margin:0;">{{ formatearFecha(tipo === 'pago' ? (data.fecha_pago || data.created_at) : (data.fecha_prestamo || data.created_at)) }}</p>
                  </div>
                  <div style="flex:1; background:#1a1a1a; border-radius:10px; padding:10px 12px; border:1px solid #222;">
                    <p style="font-size:8px; color:#6b7280; font-weight:700; text-transform:uppercase; letter-spacing:2px; margin:0 0 4px;">
                      {{ tipo === 'pago' ? 'Concepto' : 'Nro. Préstamo' }}
                    </p>
                    <p style="font-size:11px; font-weight:700; color:#e5e7eb; margin:0; font-family: monospace;">
                      {{ tipo === 'pago' ? data.tipo_pago : data.codigo }}
                    </p>
                  </div>
                  <div v-if="tipo === 'pago'" style="flex:1; background:#1c1208; border-radius:10px; padding:10px 12px; border:1px solid #2d1c07;">
                    <p style="font-size:8px; color:#d97706; font-weight:700; text-transform:uppercase; letter-spacing:2px; margin:0 0 4px;">Préstamo</p>
                    <p style="font-size:11px; font-weight:700; color:#fbbf24; margin:0; font-family: monospace;">{{ prestamoReferencia ? prestamoReferencia.codigo : 'N/A' }}</p>
                  </div>
                </div>

                <!-- Articulos Section (solo prestamo) -->
                <template v-if="tipo === 'prestamo' && data.articulos && data.articulos.length > 0">
                  <div style="background:#1a1a1a; border-radius:12px; overflow:hidden; border: 1px solid #2a2a2a;">
                    <div style="padding: 8px 12px; background:#141414; border-bottom: 1px solid #222;">
                      <p style="font-size:8px; color:#6b7280; font-weight:700; text-transform:uppercase; letter-spacing:2px; margin:0;">
                        Prendas en Garantía ({{ data.articulos.length }})
                      </p>
                    </div>
                    <div v-for="(art, idx) in data.articulos" :key="art.id"
                      :style="idx < data.articulos.length - 1 ? 'border-bottom: 1px solid #222;' : ''"
                      style="padding: 8px 12px; display:flex; align-items:center; gap:10px;">
                      <!-- Dot indicator -->
                      <div style="width:6px; height:6px; border-radius:50%; background:#ef4444; flex-shrink:0;"></div>
                      <div style="flex:1; min-width:0;">
                        <p style="font-size:10px; font-weight:800; color:#f3f4f6; margin:0; text-transform:uppercase; letter-spacing:0.3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ art.nombre_articulo }}</p>
                        <p v-if="art.descripcion" style="font-size:9px; color:#6b7280; margin:2px 0 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ art.descripcion }}</p>
                      </div>
                      <div v-if="art.estado" style="flex-shrink:0;">
                        <span :style="art.estado === 'Retirado' ? 'background:#052e16; color:#4ade80;' : 'background:#1c0505; color:#f87171;'"
                          style="font-size:8px; font-weight:700; padding: 2px 7px; border-radius:20px; text-transform:uppercase; letter-spacing:1px;">
                          {{ art.estado }}
                        </span>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Multa (solo prestamo) -->
                <div v-if="tipo === 'prestamo' && data.multa_por_retraso > 0"
                  style="background:#1c0505; border-radius:10px; padding:10px 12px; display:flex; justify-content:space-between; align-items:center; border:1px solid #3b0a0a;">
                  <p style="font-size:9px; color:#f87171; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin:0;">⚠ Multa Acordada / Mes</p>
                  <p style="font-size:13px; font-weight:900; color:#ef4444; margin:0; font-family:monospace;">{{ formatCurrency(data.multa_por_retraso) }}</p>
                </div>

              </div>

              </div><!-- /dark body -->

              <!-- ──── MONTO FOOTER ──── -->
              <div style="background:#0a0a0a; padding: 16px 20px; position:relative; overflow:hidden; flex-shrink:0;">
                <div style="position:absolute; inset:0; background: linear-gradient(135deg, rgba(220,38,38,0.15) 0%, transparent 60%);"></div>
                <div style="position:absolute; right:-20px; bottom:-20px; width:80px; height:80px; border-radius:50%; background:rgba(220,38,38,0.2); filter:blur(20px);"></div>
                <div style="display:flex; justify-content:space-between; align-items:center; position:relative; z-index:1;">
                  <div>
                    <p style="font-size:8px; color:#6b7280; font-weight:700; text-transform:uppercase; letter-spacing:2px; margin:0 0 4px;">
                      {{ tipo === 'pago' ? 'Total Abonado' : 'Total Entregado' }}
                    </p>
                    <p style="font-size:28px; font-weight:900; color:#ffffff; margin:0; letter-spacing:-1px; line-height:1;">
                      {{ tipo === 'pago' ? formatCurrency(data.monto_pagado) : formatCurrency(data.monto) }}
                    </p>
                  </div>
                  <div style="width:44px; height:44px; border-radius:50%; background:#1a1a1a; border:1px solid #333; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg style="width:22px; height:22px; color:#22c55e;" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                  </div>
                </div>

                <!-- Separador dashed -->
                <div style="border-top: 1px dashed #333; margin: 12px 0;"></div>

                <!-- Footer info -->
                <div style="display:flex; justify-content:space-between; align-items:center;">
                  <p style="font-size:8px; color:#4b5563; font-weight:600; margin:0; letter-spacing:1px; text-transform:uppercase;">CASA ZENTEVED © {{ currentYear }}</p>
                  <p style="font-size:8px; color:#374151; font-weight:700; margin:0; font-family:monospace; letter-spacing:1px;">{{ computedCodigo }}</p>
                </div>
              </div>

            </div>
            <!-- /receipt -->

          </div>
        </div>

        <!-- Acciones Footer Modal -->
        <div class="p-5 border-t border-gray-900 flex flex-col gap-3 relative shrink-0" style="background:#0a0a0a;">
          <!-- Loader -->
          <div v-if="capturando" class="absolute inset-0 z-10 flex flex-col items-center justify-center" style="background:rgba(10,10,10,0.85);">
            <div style="width:40px; height:40px; border:4px solid #dc2626; border-top-color:transparent; border-radius:50; animation: spin 0.8s linear infinite;" class="rounded-full animate-spin"></div>
            <p class="text-white font-bold text-sm mt-4 uppercase tracking-widest text-xs animate-pulse">Guardando HD...</p>
          </div>

          <button 
            @click="descargarImagen" 
            class="w-full py-4 rounded-xl font-black transition-all shadow-lg flex items-center justify-center gap-2 overflow-hidden relative group uppercase tracking-widest text-xs"
            :class="capturando ? 'bg-neutral-800 text-neutral-500' : 'bg-red-600 hover:bg-red-500 text-white shadow-red-600/20'"
            :disabled="capturando"
          >
            <span class="absolute right-0 w-32 h-64 -mt-12 transition-all duration-1000 transform translate-x-12 bg-white opacity-10 rotate-12 group-hover:-translate-x-96 ease"></span>
            <svg class="w-5 h-5 z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span class="z-10">Exportar &amp; Compartir</span>
          </button>
          <p class="text-xs text-gray-600 flex items-center justify-center font-bold gap-1 uppercase tracking-widest">
            Optimizado para envío por celular
          </p>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import html2canvas from 'html2canvas';

const props = defineProps({
  show: Boolean,
  tipo: String, // 'pago' o 'prestamo'
  data: Object, // Pago o Prestamo
  cliente: Object,
  prestamoReferencia: Object // Requerido si tipo === 'pago'
});

const emit = defineEmits(['close']);

const receiptContent = ref(null);
const capturando = ref(false);
const currentYear = new Date().getFullYear();

// ── Iniciales del cliente ──
const getInitials = (fullName) => {
    if (!fullName) return '?';
    return fullName.trim().split(/\s+/).map(n => n[0]).filter(Boolean).slice(0, 2).join('').toUpperCase();
};

// ── Gradiente para avatar (basado en ID del cliente) ──
const avatarColors = [
    ['#7c3aed','#4f46e5'], // violet-indigo
    ['#dc2626','#b91c1c'], // red
    ['#0891b2','#0e7490'], // cyan
    ['#059669','#047857'], // emerald
    ['#d97706','#b45309'], // amber
    ['#db2777','#be185d'], // pink
];

const avatarStyle = computed(() => {
    const id = props.cliente?.id || 0;
    const [c1, c2] = avatarColors[id % avatarColors.length];
    return `background: linear-gradient(135deg, ${c1}, ${c2}); box-shadow: 0 4px 12px rgba(0,0,0,0.3);`;
});

// ── Código alfanumérico ──
const random5CharString = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < 5; i++) result += chars.charAt(Math.floor(Math.random() * chars.length));
    return result;
};

const finalGeneratedCode = ref('');

watch(() => props.show, (newVal) => {
    if (newVal) {
        finalGeneratedCode.value = random5CharString();
    }
}, { immediate: true });

const computedCodigo = computed(() => finalGeneratedCode.value);

// ── Formateo ──
const formatearFecha = (fechaStr) => {
    if (!fechaStr) return '';
    const d = new Date(fechaStr);
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' }).toUpperCase();
};

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);

// ── Descargar ──
const descargarImagen = async () => {
    if (!receiptContent.value) return;
    capturando.value = true;

    try {
        const canvas = await html2canvas(receiptContent.value, {
            scale: 3,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false,
        });

        // Añadir padding negro alrededor → composición final
        const pad = 48;
        const final = document.createElement('canvas');
        final.width = canvas.width + pad * 2;
        final.height = canvas.height + pad * 2;
        const ctx = final.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, final.width, final.height);
        ctx.drawImage(canvas, pad, pad);

        const image = final.toDataURL('image/jpeg', 0.96);
        const link = document.createElement('a');
        link.href = image;
        link.download = `CASA_ZENTEVED_${computedCodigo.value}.jpg`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        setTimeout(() => { capturando.value = false; }, 600);
    } catch (error) {
        console.error('Error al capturar recibo:', error);
        alert('Ocurrió un error al generar la imagen. Intenta de nuevo.');
        capturando.value = false;
    }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
