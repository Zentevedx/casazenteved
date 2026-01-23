<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden relative">
        <!-- Badge Placeholder -->
        <div class="absolute top-2 right-2 z-10 flex flex-col gap-1 items-end">
            <div class="w-16 h-5 bg-gray-200 dark:bg-gray-800 rounded-lg animate-pulse"></div>
        </div>

        <!-- Image Area Placeholder -->
        <div class="aspect-square bg-gray-100 dark:bg-[#141414] animate-pulse relative">
             <div class="absolute bottom-2 left-2 right-2 flex justify-center">
                <div class="h-6 w-20 bg-gray-200 dark:bg-gray-800 rounded-full"></div>
            </div>
        </div>

        <!-- Info Area Placeholder -->
        <div class="p-3 space-y-3">
            <div class="space-y-1">
                <div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-3/4 animate-pulse"></div>
                <div class="flex items-center gap-1.5">
                    <div class="w-3 h-3 bg-gray-200 dark:bg-gray-800 rounded-full animate-pulse"></div>
                    <div class="h-2 bg-gray-200 dark:bg-gray-800 rounded w-1/2 animate-pulse"></div>
                </div>
            </div>
            
            <!-- Time Badge Placeholder -->
            <div class="h-8 bg-gray-50 dark:bg-[#141414] rounded-lg border border-gray-100 dark:border-gray-800 animate-pulse"></div>
        </div>
    </div>
</template>
