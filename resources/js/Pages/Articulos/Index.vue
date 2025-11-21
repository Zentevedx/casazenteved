<script setup>
import Layout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ articulos: Object })
</script>

<template>
  <Layout title="Artículos">
    <div class="p-6 bg-black min-h-screen text-white">
      <h1 class="text-3xl font-bold text-orange-500 mb-6">Artículos Prendados</h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div
          v-for="articulo in articulos.data"
          :key="articulo.id"
          class="bg-white text-black rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300"
        >
          <img
            v-if="articulo.foto_url"
            :src="articulo.foto_url"
            alt="Foto del artículo"
            class="w-full h-48 object-cover"
          />
          <div class="p-4 space-y-2">
            <h2 class="text-lg font-bold text-orange-600">{{ articulo.nombre_articulo }}</h2>
            <p class="text-sm text-gray-700">{{ articulo.descripcion }}</p>
            <p class="text-xs text-gray-500">Estado: {{ articulo.estado }}</p>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>
