<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { 
    MagnifyingGlassIcon, 
    UserIcon, 
    DocumentTextIcon, 
    TagIcon, 
    ArchiveBoxIcon 
} from '@heroicons/vue/24/outline'

const q = ref('')
const resultados = ref([])
const showModal = ref(false)
const inputRef = ref(null)

// Lista de colores pastel profesionales (Fondo + Texto)
const avatarColors = [
    'bg-red-100 text-red-700',
    'bg-orange-100 text-orange-700',
    'bg-amber-100 text-amber-700',
    'bg-green-100 text-green-700',
    'bg-emerald-100 text-emerald-700',
    'bg-teal-100 text-teal-700',
    'bg-cyan-100 text-cyan-700',
    'bg-blue-100 text-blue-700',
    'bg-indigo-100 text-indigo-700',
    'bg-violet-100 text-violet-700',
    'bg-purple-100 text-purple-700',
    'bg-fuchsia-100 text-fuchsia-700',
    'bg-pink-100 text-pink-700',
    'bg-rose-100 text-rose-700'
];

// Genera un color consistente basado en el nombre (Hash simple)
const getAvatarColor = (name) => {
    if (!name) return avatarColors[0];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    const index = Math.abs(hash) % avatarColors.length;
    return avatarColors[index];
}

// Obtiene las iniciales (máximo 2 letras)
const getInitials = (name) => {
    if (!name) return '??';
    const parts = name.trim().split(' ');
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

// Buscar mientras se escribe
watch(q, async (val) => {
  if (val.length >= 2) {
    try {
      const { data } = await axios.get(route('buscar.ajax'), { params: { q: val } })
      resultados.value = data
      showModal.value = true
    } catch (e) {
      resultados.value = []
    }
  } else {
    showModal.value = false
    resultados.value = []
  }
})

const irAResultado = (url) => {
  if (url && url !== '#') {
    showModal.value = false
    q.value = ''
    router.visit(url)
  }
}

const closeSearch = () => {
    setTimeout(() => { showModal.value = false }, 200)
}

const handleFocus = () => {
    if (q.value.length >= 2) {
        showModal.value = true
    }
}
</script>

<template>
  <div class="relative w-full">
    <div class="relative group">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" />
        </div>
        <input
            ref="inputRef"
            v-model="q"
            @blur="closeSearch"
            @focus="handleFocus" 
            type="text"
            placeholder="Buscar..."
            class="pl-10 pr-4 py-2 rounded-lg border border-gray-200 bg-gray-50 
                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 
                   w-full text-sm transition-all shadow-sm placeholder-gray-400"
        />
    </div>

    <transition name="fade">
      <div v-if="showModal && resultados.length > 0" 
           class="absolute z-50 mt-2 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden max-h-96 overflow-y-auto
                  w-[90vw] sm:w-[450px] left-0 sm:left-auto sm:right-0 md:right-auto md:left-1/2 md:-translate-x-1/2">
        
        <ul>
          <li v-for="(item, index) in resultados" :key="index" 
              @click="irAResultado(item.url)"
              class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-0 flex items-center gap-4 transition-colors group">
            
            <div class="shrink-0">
                
                <div v-if="item.tipo === 'Cliente'" 
                     :class="getAvatarColor(item.titulo)"
                     class="h-10 w-10 rounded-full flex items-center justify-center text-xs font-bold border border-white shadow-sm">
                    {{ getInitials(item.titulo) }}
                </div>

                <div v-else-if="item.tipo === 'Prestamo'" 
                     class="h-10 w-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                    <DocumentTextIcon class="w-5 h-5" />
                </div>

                <div v-else-if="item.tipo === 'Articulo'" 
                     class="h-10 w-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100">
                    <TagIcon class="w-5 h-5" />
                </div>

                 <div v-else 
                     class="h-10 w-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center">
                    <ArchiveBoxIcon class="w-5 h-5" />
                </div>
            </div>

            <div class="flex-1 min-w-0 text-left">
                <p class="text-sm font-bold text-gray-800 truncate group-hover:text-indigo-600 transition-colors">
                    {{ item.titulo }}
                </p>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded border shrink-0"
                          :class="{
                              'bg-green-50 text-green-700 border-green-100': item.tipo === 'Cliente',
                              'bg-blue-50 text-blue-700 border-blue-100': item.tipo === 'Prestamo',
                              'bg-purple-50 text-purple-700 border-purple-100': item.tipo === 'Articulo',
                              'bg-gray-50 text-gray-600 border-gray-200': !['Cliente', 'Prestamo', 'Articulo'].includes(item.tipo)
                          }">
                        {{ item.tipo }}
                    </span>
                    <p class="text-xs text-gray-500 truncate flex-1">{{ item.subtitulo }}</p>
                </div>
            </div>
          </li>
        </ul>
      </div>

      <div v-else-if="showModal && q.length >= 2" 
           class="absolute z-50 mt-2 bg-white rounded-lg shadow-xl p-8 text-center border border-gray-100
                  w-[90vw] sm:w-[450px] left-0 md:left-1/2 md:-translate-x-1/2">
          <div class="flex flex-col items-center justify-center text-gray-400">
              <MagnifyingGlassIcon class="w-8 h-8 mb-2 opacity-50" />
              <p class="text-sm font-medium text-gray-600">No encontramos resultados</p>
              <p class="text-xs text-gray-400 mt-1">Intenta buscar por otro nombre o código</p>
          </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s, transform 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-5px); }
</style>