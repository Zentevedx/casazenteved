<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const q = ref('')
const resultados = ref([])
const showModal = ref(false)
const inputRef = ref(null)

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

// Navegar al resultado
const irAResultado = (url) => {
  if (url && url !== '#') {
    showModal.value = false
    q.value = ''
    router.visit(url)
  }
}

// Cerrar al hacer clic fuera
const closeSearch = () => {
    setTimeout(() => { showModal.value = false }, 200)
}

// CORRECCIÓN AQUÍ: Función dedicada para el foco
const handleFocus = () => {
    if (q.value.length >= 2) {
        showModal.value = true
    }
}
</script>

<template>
  <div class="relative w-full max-w-md mx-auto">
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
        </div>
        <input
            ref="inputRef"
            v-model="q"
            @blur="closeSearch"
            @focus="handleFocus" 
            type="text"
            placeholder="Buscar Cliente, Préstamo o Artículo..."
            class="pl-10 pr-4 py-2 rounded-full border border-gray-300 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 w-full text-sm transition-all shadow-sm"
        />
    </div>

    <transition name="fade">
      <div v-if="showModal && resultados.length > 0" class="absolute z-50 mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden max-h-96 overflow-y-auto">
        <ul>
          <li v-for="(item, index) in resultados" :key="index" 
              @click="irAResultado(item.url)"
              class="px-4 py-3 hover:bg-indigo-50 cursor-pointer border-b border-gray-50 last:border-0 flex items-center gap-3 transition-colors">
            
            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-lg">
                {{ item.icono }}
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-800 truncate">{{ item.titulo }}</p>
                <div class="flex justify-between items-center">
                    <p class="text-xs text-gray-500 truncate">{{ item.subtitulo }}</p>
                    <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-gray-200 text-gray-600 ml-2">
                        {{ item.tipo }}
                    </span>
                </div>
            </div>
          </li>
        </ul>
      </div>

      <div v-else-if="showModal && q.length >= 2" class="absolute z-50 mt-2 w-full bg-white rounded-lg shadow-xl p-4 text-center text-gray-500 text-sm">
          No se encontraron resultados para "{{ q }}"
      </div>
    </transition>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s, transform 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-5px); }
</style>