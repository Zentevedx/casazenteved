<template>
  <div class="flex justify-center space-x-1 mt-4 flex-wrap gap-y-2">
    <template v-for="link in links" :key="link.label">
      <div 
        v-if="!link.url" 
        class="px-4 py-2 text-sm text-gray-400 dark:text-gray-500 border border-transparent rounded-xl" 
        v-html="link.label" 
      />
      <Link
        v-else
        :href="link.url"
        class="px-4 py-2 text-sm font-semibold rounded-xl border transition-all duration-200"
        :class="[
            link.active 
                ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-500/30' 
                : 'bg-white dark:bg-transparent border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'
        ]"
        v-html="link.label"
      />
    </template>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ links: Array })
</script>
