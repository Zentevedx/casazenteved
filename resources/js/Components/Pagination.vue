<template>
  <div class="flex justify-center space-x-1 mt-4">
    <template v-for="link in links" :key="link.label">
      <span v-if="!link.url" class="px-3 py-1 text-gray-400" v-html="link.label" />
      <a
        v-else
        :href="link.url"
        class="px-3 py-1 rounded hover:bg-gray-200"
        :class="{ 'font-bold underline': link.active }"
        v-html="link.label"
      />
    </template>
  </div>
</template>

<script setup>
defineProps({ links: Array })
</script>
