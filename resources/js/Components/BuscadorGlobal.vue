<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { 
    MagnifyingGlassIcon, 
    UserIcon, 
    DocumentTextIcon, 
    TagIcon, 
    ArchiveBoxIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const q = ref('')
const resultados = ref([])
const showModal = ref(false)
const inputRef = ref(null)
const loading = ref(false)

// Colors gradients for avatars
const avatarGradients = [
    'from-red-500 to-orange-500',
    'from-amber-500 to-yellow-500',
    'from-green-500 to-emerald-500',
    'from-teal-500 to-cyan-500',
    'from-blue-500 to-indigo-500',
    'from-violet-500 to-purple-500',
    'from-fuchsia-500 to-pink-500',
    'from-rose-500 to-red-500'
];

const getAvatarGradient = (name) => {
    if (!name) return avatarGradients[0];
    let hash = 0;
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return avatarGradients[Math.abs(hash) % avatarGradients.length];
}

const getInitials = (name) => {
    if (!name) return '??';
    const parts = name.trim().split(' ');
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

let debounceTimer = null;
let abortController = null;

watch(q, (val) => {
    // 1. Cancel previous pending request immediately
    if (abortController) abortController.abort();
    if (debounceTimer) clearTimeout(debounceTimer);

    // 2. Clear if input is too short
    if (val.length < 2) {
        showModal.value = false;
        resultados.value = [];
        loading.value = false;
        return;
    }

    // 3. Debounce execution (300ms)
    loading.value = true;
    debounceTimer = setTimeout(async () => {
        abortController = new AbortController();
        try {
            const { data } = await axios.get(route('buscar.ajax'), { 
                params: { q: val },
                signal: abortController.signal 
            });
            resultados.value = data;
            showModal.value = true;
        } catch (e) {
            if (axios.isCancel(e)) return; // Ignore cancelled requests
            console.error(e);
            resultados.value = [];
        } finally {
            // Only turn off loading if this specific request wasn't cancelled
            if (!abortController?.signal.aborted) {
                loading.value = false;
            }
        }
    }, 300);
});

const irAResultado = (url) => {
  if (url && url !== '#') {
    showModal.value = false
    q.value = ''
    router.visit(url)
  }
}

const closeSearch = () => {
    // Delay to allow click event on results
    setTimeout(() => { showModal.value = false }, 200)
}

const handleFocus = () => {
    if (q.value.length >= 2) {
        showModal.value = true
    }
}

// Keyboard Shortcuts (Ctrl+K or Cmd+K)
const handleKeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        inputRef.value?.focus();
    }
    if (e.key === 'Escape') {
        inputRef.value?.blur();
        showModal.value = false;
    }
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
  <div class="relative w-full max-w-2xl mx-auto">
    <!-- Search Bar -->
    <div class="relative group">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-300">
            <MagnifyingGlassIcon 
                class="h-5 w-5 transition-colors duration-300"
                :class="showModal ? 'text-indigo-400' : 'text-gray-500 group-hover:text-gray-300'" 
            />
        </div>
        <input
            ref="inputRef"
            v-model="q"
            @blur="closeSearch"
            @focus="handleFocus" 
            type="text"
            placeholder="Buscar clientes, préstamos, artículos... (Ctrl+K)"
            class="pl-12 pr-12 py-3 rounded-2xl border-2 bg-[#1a1a1a] text-gray-200 placeholder-gray-500
                   border-gray-800 focus:border-indigo-600 focus:bg-[#0f0f0f] focus:ring-4 focus:ring-indigo-900/20
                   w-full text-sm font-medium transition-all duration-300 shadow-xl"
        />
        
        <!-- Loading Indicator / Clear Button -->
        <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
            <div v-if="loading" class="animate-spin rounded-full h-4 w-4 border-2 border-indigo-500 border-t-transparent"></div>
            <button v-else-if="q.length > 0" @click="q = ''; inputRef.focus()" class="text-gray-500 hover:text-white transition">
                <XMarkIcon class="w-5 h-5" />
            </button>
            <div v-else class="hidden md:flex items-center gap-1">
                <kbd class="hidden sm:inline-block px-2 py-0.5 text-[10px] font-bold text-gray-500 bg-gray-800 border-gray-700 rounded-lg">Ctrl</kbd>
                <kbd class="hidden sm:inline-block px-2 py-0.5 text-[10px] font-bold text-gray-500 bg-gray-800 border-gray-700 rounded-lg">K</kbd>
            </div>
        </div>
    </div>

    <!-- Results Dropdown -->
    <transition 
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-2 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-2 scale-95"
    >
      <div v-if="showModal" 
           class="absolute z-50 mt-4 bg-[#1a1a1a] rounded-2xl shadow-2xl border border-gray-800 overflow-hidden 
                  w-[95vw] sm:w-full -left-4 sm:left-0 origin-top transform backdrop-blur-xl ring-1 ring-white/5">
        
        <!-- Results List -->
        <ul v-if="resultados.length > 0" class="max-h-[60vh] overflow-y-auto custom-scrollbar p-2">
            <li v-for="(item, index) in resultados" :key="index" 
                @click="irAResultado(item.url)"
                class="group rounded-xl p-3 flex items-center gap-4 cursor-pointer transition-all duration-200 hover:bg-white/5 border border-transparent hover:border-gray-700"
            >
                <!-- Avatar / Icon -->
                <div class="shrink-0 relative">
                    <div class="absolute -inset-1 rounded-full blur opacity-0 group-hover:opacity-40 transition duration-300"
                        :class="item.tipo === 'Cliente' ? 'bg-gradient-to-r ' + getAvatarGradient(item.titulo) : 'bg-indigo-500'">
                    </div>
                    
                    <div v-if="item.tipo === 'Cliente'" 
                         class="relative h-12 w-12 rounded-xl bg-gradient-to-br flex items-center justify-center text-sm font-bold text-white shadow-lg"
                         :class="getAvatarGradient(item.titulo)">
                        {{ getInitials(item.titulo) }}
                    </div>

                    <div v-else-if="item.tipo === 'Prestamo'" 
                         class="relative h-12 w-12 rounded-xl bg-indigo-900/50 text-indigo-400 border border-indigo-500/30 flex items-center justify-center">
                        <DocumentTextIcon class="w-6 h-6" />
                    </div>

                    <div v-else-if="item.tipo === 'Articulo'" 
                         class="relative h-12 w-12 rounded-xl bg-fuchsia-900/50 text-fuchsia-400 border border-fuchsia-500/30 flex items-center justify-center">
                        <TagIcon class="w-6 h-6" />
                    </div>
                    <div v-else 
                         class="relative h-12 w-12 rounded-xl bg-gray-800 text-gray-400 flex items-center justify-center border border-gray-700">
                        <ArchiveBoxIcon class="w-6 h-6" />
                    </div>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-bold text-gray-200 truncate group-hover:text-white transition-colors">
                            {{ item.titulo }}
                        </p>
                        <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-lg border border-opacity-30 tracking-wider"
                              :class="{
                                  'bg-green-500/10 text-green-400 border-green-500': item.tipo === 'Cliente',
                                  'bg-indigo-500/10 text-indigo-400 border-indigo-500': item.tipo === 'Prestamo',
                                  'bg-fuchsia-500/10 text-fuchsia-400 border-fuchsia-500': item.tipo === 'Articulo',
                                  'bg-gray-700 text-gray-400 border-gray-600': !['Cliente', 'Prestamo', 'Articulo'].includes(item.tipo)
                              }">
                            {{ item.tipo }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 truncate group-hover:text-gray-400 transition-colors">
                        {{ item.subtitulo }}
                    </p>
                </div>
                
                <!-- Chevron -->
                <svg class="w-5 h-5 text-gray-600 group-hover:text-indigo-400 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </li>
        </ul>

        <!-- Empty State -->
        <div v-else-if="q.length >= 2 && !loading" class="p-12 text-center text-gray-500 select-none">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-800/50 flex items-center justify-center animate-pulse">
                <MagnifyingGlassIcon class="w-8 h-8 opacity-40" />
            </div>
            <p class="text-sm font-medium text-gray-400">Sin resultados para "{{ q }}"</p>
            <p class="text-xs text-gray-600 mt-2">Intenta buscar por CI, código o nombre diferente</p>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* Scrollbar Dark Theme */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #333;
    border-radius: 10px;
    border: 2px solid #1a1a1a;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #444;
}
</style>