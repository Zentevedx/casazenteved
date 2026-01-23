<script setup>
defineProps({
    title: String,
    items: Array, // [{ nombre, secondary_text, value, value_label, rank_color }]
    icon: Object, // Heroicon component
    colorTheme: {
        type: String,
        default: 'emerald' // emerald, red
    }
});

const formatCurrency = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);
</script>

<template>
    <div class="bg-[#1a1a1a] rounded-2xl border border-gray-800 p-6 shadow-lg h-full">
        <h3 class="text-sm font-bold flex items-center gap-2 mb-5 tracking-wide text-gray-300">
            <component :is="icon" class="w-4 h-4" :class="colorTheme === 'emerald' ? 'text-emerald-400' : 'text-red-400'" />
            {{ title }}
        </h3>
        
        <div class="space-y-4">
            <div v-for="(item, idx) in items" :key="idx" 
                 class="group flex items-center justify-between p-3 rounded-xl hover:bg-white/5 transition-colors border border-transparent hover:border-gray-700/50 cursor-default">
                
                <div class="flex items-center gap-4">
                    <!-- Rank Badge -->
                    <div :class="['w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold border shadow-inner transition-transform group-hover:scale-110', 
                        colorTheme === 'emerald' ? 'bg-emerald-900/30 text-emerald-400 border-emerald-500/20' : 'bg-red-900/30 text-red-400 border-red-500/20']">
                        {{ idx + 1 }}
                    </div>
                    
                    <div>
                        <p class="text-sm font-bold text-gray-200 group-hover:text-white transition-colors">{{ item.nombre }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">{{ item.secondary_text }}</p>
                    </div>
                </div>

                <div class="text-right">
                    <p :class="['text-sm font-mono font-bold', colorTheme === 'emerald' ? 'text-emerald-400' : 'text-red-400']">
                        {{ typeof item.value === 'number' ? formatCurrency(item.value) : item.value }}
                    </p>
                    <p class="text-[10px] text-gray-600">{{ item.value_label }}</p>
                </div>
            </div>
            
            <div v-if="items.length === 0" class="text-center py-8 text-gray-600 text-xs italic">
                No hay datos suficientes
            </div>
        </div>
    </div>
</template>
