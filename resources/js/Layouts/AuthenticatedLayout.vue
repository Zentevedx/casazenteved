<script setup>
import { ref, onMounted, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import BuscadorGlobal from '@/Components/BuscadorGlobal.vue';

// Importamos iconos profesionales de Heroicons (ya incluido en tu package.json)
import { 
    Squares2X2Icon, 
    UsersIcon, 
    DocumentTextIcon, 
    TagIcon, 
    ChartBarIcon, 
    BanknotesIcon, 
    ArchiveBoxIcon, // Para caja
    ArrowTrendingDownIcon, // Para gastos
    SunIcon,
    MoonIcon,
    ChevronLeftIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';

const showingNavigationDropdown = ref(false);

const isDark = ref(true);
const isSidebarCollapsed = ref(false);

const toggleTheme = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    localStorage.setItem('sidebarCollapsed', isSidebarCollapsed.value);
}

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    }

    const savedSidebarState = localStorage.getItem('sidebarCollapsed');
    if (savedSidebarState !== null) {
        isSidebarCollapsed.value = savedSidebarState === 'true';
    }
});
</script>

<template>
    <div class="h-screen bg-gray-50 dark:bg-dark-bg flex overflow-hidden">
        
        <!-- SIDEBAR (Desktop) -->
        <aside 
            class="hidden md:flex flex-col bg-white dark:bg-[#141414] border-r border-gray-200 dark:border-gray-800 transition-all duration-300 ease-in-out relative"
            :class="isSidebarCollapsed ? 'w-20' : 'w-72'"
        >
            <!-- Logo area -->
            <div class="h-16 flex items-center border-b border-gray-200 dark:border-gray-800 transition-all duration-300"
                :class="isSidebarCollapsed ? 'justify-center px-0' : 'px-6 justify-between'"
            >
                <Link :href="route('dashboard')" class="flex items-center gap-3 group overflow-hidden whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-black shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition shrink-0">
                        <span>P</span>
                    </div>
                    <span 
                        class="text-gray-900 dark:text-white font-bold text-lg tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition duration-300"
                        :class="[isSidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100 w-auto']"
                    >
                        PrestaMax
                    </span>
                </Link>

                <!-- Collapse Button (Only shown when expanded to push it to right, or absolutely positioned) -->
               <button 
                    @click="toggleSidebar" 
                    class="p-1 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors"
                    :class="isSidebarCollapsed ? 'absolute -right-3 top-6 bg-[#141414] border border-gray-700 shadow-md rounded-full w-6 h-6 flex items-center justify-center z-50' : ''"
                >
                    <ChevronLeftIcon v-if="!isSidebarCollapsed" class="w-5 h-5" />
                    <ChevronRightIcon v-else class="w-3 h-3" />
                </button>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 overflow-y-auto py-6 space-y-1 custom-scrollbar" :class="isSidebarCollapsed ? 'px-2' : 'px-3'">
                <NavLink :href="route('dashboard')" :active="route().current('dashboard')" :class="{'justify-center': isSidebarCollapsed}" :title="isSidebarCollapsed ? 'Dashboard' : ''">
                    <Squares2X2Icon class="w-5 h-5 shrink-0" />
                    <span class="transition-all duration-300 overflow-hidden whitespace-nowrap" :class="[isSidebarCollapsed ? 'w-0 opacity-0 ml-0' : 'w-auto opacity-100 ml-3']">
                        Dashboard
                    </span>
                </NavLink>
                
                <div class="pt-4 pb-2 transition-all duration-300" :class="isSidebarCollapsed ? 'opacity-0 h-0 border-t border-gray-800 my-2' : 'opacity-100 h-auto'">
                    <p class="px-4 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest whitespace-nowrap overflow-hidden">Gestión</p>
                </div>
                
                <NavLink :href="route('clientes.index')" :active="route().current('clientes.*')" :class="{'justify-center': isSidebarCollapsed}" :title="isSidebarCollapsed ? 'Clientes' : ''">
                    <UsersIcon class="w-5 h-5 shrink-0" />
                    <span class="transition-all duration-300 overflow-hidden whitespace-nowrap" :class="[isSidebarCollapsed ? 'w-0 opacity-0 ml-0' : 'w-auto opacity-100 ml-3']">
                        Clientes
                    </span>
                </NavLink>
                <NavLink :href="route('prestamos.index')" :active="route().current('prestamos.*')" :class="{'justify-center': isSidebarCollapsed}" :title="isSidebarCollapsed ? 'Préstamos' : ''">
                    <DocumentTextIcon class="w-5 h-5 shrink-0" />
                    <span class="transition-all duration-300 overflow-hidden whitespace-nowrap" :class="[isSidebarCollapsed ? 'w-0 opacity-0 ml-0' : 'w-auto opacity-100 ml-3']">
                        Préstamos
                    </span>
                </NavLink>
                <NavLink :href="route('articulos.index')" :active="route().current('articulos.*')" :class="{'justify-center': isSidebarCollapsed}" :title="isSidebarCollapsed ? 'Artículos' : ''">
                    <TagIcon class="w-5 h-5 shrink-0" />
                    <span class="transition-all duration-300 overflow-hidden whitespace-nowrap" :class="[isSidebarCollapsed ? 'w-0 opacity-0 ml-0' : 'w-auto opacity-100 ml-3']">
                        Artículos
                    </span>
                </NavLink>

                <div class="pt-4 pb-2 transition-all duration-300" :class="isSidebarCollapsed ? 'opacity-0 h-0 border-t border-gray-800 my-2' : 'opacity-100 h-auto'">
                    <p class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap overflow-hidden">Finanzas</p>
                </div>

                <NavLink :href="route('estadisticas')" :active="route().current('estadisticas')" :class="{'justify-center': isSidebarCollapsed}" :title="isSidebarCollapsed ? 'Estadísticas' : ''">
                    <ChartBarIcon class="w-5 h-5 shrink-0" />
                     <span class="transition-all duration-300 overflow-hidden whitespace-nowrap" :class="[isSidebarCollapsed ? 'w-0 opacity-0 ml-0' : 'w-auto opacity-100 ml-3']">
                        Estadísticas
                    </span>
                </NavLink>
                <NavLink :href="route('caja.index')" :active="route().current('caja.*')" :class="{'justify-center': isSidebarCollapsed}" :title="isSidebarCollapsed ? 'Caja Chica' : ''">
                    <ArchiveBoxIcon class="w-5 h-5 shrink-0" />
                     <span class="transition-all duration-300 overflow-hidden whitespace-nowrap" :class="[isSidebarCollapsed ? 'w-0 opacity-0 ml-0' : 'w-auto opacity-100 ml-3']">
                        Caja Chica
                    </span>
                </NavLink>
                <NavLink :href="route('gastos.index')" :active="route().current('gastos.*')" :class="{'justify-center': isSidebarCollapsed}" :title="isSidebarCollapsed ? 'Gastos' : ''">
                    <ArrowTrendingDownIcon class="w-5 h-5 shrink-0" />
                     <span class="transition-all duration-300 overflow-hidden whitespace-nowrap" :class="[isSidebarCollapsed ? 'w-0 opacity-0 ml-0' : 'w-auto opacity-100 ml-3']">
                        Gastos
                    </span>
                </NavLink>
            </nav>

            <!-- User Profile (Bottom Sidebar) -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-800 relative">
                <Dropdown align="top" width="48">
                    <template #trigger>
                        <button class="flex items-center gap-3 w-full p-2 rounded-xl hover:bg-gray-800 transition text-left group"
                             :class="{'justify-center': isSidebarCollapsed}"
                        >
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shadow-inner shrink-0">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <div class="flex-1 overflow-hidden transition-all duration-300"
                                :class="[isSidebarCollapsed ? 'w-0 opacity-0 ml-0' : 'w-auto opacity-100']"
                            >
                                <p class="text-sm font-medium text-gray-700 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">{{ $page.props.auth.user.name }}</p>
                                <p class="text-xs text-gray-500 truncate">Ver opciones</p>
                            </div>
                            <svg v-if="!isSidebarCollapsed" class="w-4 h-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <div class="py-1 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-gray-700 rounded-md shadow-xl" :class="{'ml-12': isSidebarCollapsed}">
                             <!-- Adjustment for dropdown position when collapsed might be needed or let it float -->
                            <DropdownLink :href="route('profile.edit')" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white"> Perfil </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="text-gray-300 hover:bg-gray-800 hover:text-red-400"> Cerrar Sesión </DropdownLink>
                        </div>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 dark:bg-dark-bg transition-all duration-300">
            
            <!-- TOP NAVBAR (Mobile & Search) -->
            <header class="h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-[#0f0f0f]/80 backdrop-blur-sm z-20">
                
                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="text-gray-400 hover:text-white p-2">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Global Search -->
                <div class="flex-1 max-w-2xl mx-auto flex items-center px-4 gap-4">
                    <BuscadorGlobal />
                    
                    <!-- Theme Toggle -->
                    <button @click="toggleTheme" class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Cambiar Tema">
                        <SunIcon v-if="!isDark" class="w-6 h-6 text-orange-500" />
                        <MoonIcon v-else class="w-6 h-6 text-indigo-400" />
                    </button>
                </div>

            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto scroll-smooth custom-scrollbar relative">
                <slot />
            </main>
        </div>

        <!-- MOBILE MENU OVERLAY -->
        <div v-show="showingNavigationDropdown" class="fixed inset-0 z-40 md:hidden bg-black/50 backdrop-blur-sm" @click="showingNavigationDropdown = false"></div>
        
        <div v-show="showingNavigationDropdown" 
            class="fixed inset-y-0 left-0 z-50 w-64 bg-[#141414] border-r border-gray-800 transform transition-transform duration-300 ease-in-out md:hidden"
            :class="showingNavigationDropdown ? 'translate-x-0' : '-translate-x-full'">
            
            <div class="h-16 flex items-center px-6 border-b border-gray-800 justify-between">
                <span class="text-white font-bold text-lg">Menú</span>
                <button @click="showingNavigationDropdown = false" class="text-gray-500 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="py-4 space-y-1 px-2">
                <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                    Dashboard
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('clientes.index')" :active="route().current('clientes.*')">
                    Clientes
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('prestamos.index')" :active="route().current('prestamos.*')">
                    Préstamos
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('articulos.index')" :active="route().current('articulos.*')">
                    Artículos
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('estadisticas')" :active="route().current('estadisticas')">
                    Estadísticas
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('caja.index')" :active="route().current('caja.*')">
                    Caja
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('gastos.index')" :active="route().current('gastos.*')">
                    Gastos
                </ResponsiveNavLink>

                <div class="border-t border-gray-800 mt-4 pt-4 px-4">
                    <div class="text-base font-medium text-white">{{ $page.props.auth.user.name }}</div>
                    <div class="text-sm font-medium text-gray-500 mb-3">{{ $page.props.auth.user.email }}</div>
                    <ResponsiveNavLink :href="route('profile.edit')"> Perfil </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-red-400"> Cerrar Sesión </ResponsiveNavLink>
                </div>
            </div>
        </div>

    </div>
</template>

<style>
/* Global scrollbar for the main content area */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #333; 
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #555; 
}
</style>