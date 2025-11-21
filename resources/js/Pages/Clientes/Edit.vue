<script setup>
import { useForm } from '@inertiajs/vue3'
import Layout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ cliente: Object })

const form = useForm({
  nombre: props.cliente.nombre,
  ci: props.cliente.ci,
  direccion: props.cliente.direccion,
  telefono: props.cliente.telefono
})

function actualizar() {
  form.put(route('clientes.update', props.cliente.id))
}
</script>

<template>
  <Layout title="Editar Cliente">
    <div class="m-5 p-6 space-y-6 bg-black text-white min-h-screen rounded-t-3xl">
      <h1 class="text-3xl font-bold text-orange-500">Editar Cliente</h1>

      <form @submit.prevent="actualizar" class="space-y-5 max-w-xl">
        <div>
          <label class="block text-sm mb-1">Nombre</label>
          <input v-model="form.nombre" type="text"
            class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400"
          />
          <span v-if="form.errors.nombre" class="text-red-400 text-sm">{{ form.errors.nombre }}</span>
        </div>

        <div>
          <label class="block text-sm mb-1">CI</label>
          <input v-model="form.ci" type="text"
            class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400"
          />
          <span v-if="form.errors.ci" class="text-red-400 text-sm">{{ form.errors.ci }}</span>
        </div>

        <div>
          <label class="block text-sm mb-1">Dirección</label>
          <input v-model="form.direccion" type="text"
            class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400"
          />
        </div>

        <div>
          <label class="block text-sm mb-1">Teléfono</label>
          <input v-model="form.telefono" type="text"
            class="w-full px-4 py-2 rounded-full bg-white text-black border border-orange-400"
          />
        </div>

        <div class="flex gap-3">
          <button type="submit"
            class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-full shadow-md transition"
          >
            Actualizar
          </button>
          <a :href="route('clientes.index')"
            class="bg-white text-orange-600 hover:bg-gray-200 px-6 py-2 rounded-full border border-orange-300"
          >
            Cancelar
          </a>
        </div>
      </form>
    </div>
  </Layout>
</template>
