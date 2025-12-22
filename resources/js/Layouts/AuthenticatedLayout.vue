<script setup>
import { ref } from 'vue';
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
    ArrowTrendingDownIcon // Para gastos
} from '@heroicons/vue/24/outline';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="border-b border-gray-100 bg-white shadow-sm">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between items-center gap-4">
                        
                        <div class="flex items-center">
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo class="block h-9 w-auto fill-current text-indigo-600" />
                                </Link>
                            </div>

                            <div class="hidden space-x-6 sm:-my-px sm:ms-10 lg:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    <Squares2X2Icon class="w-5 h-5 mr-1.5 opacity-70" />
                                    Dashboard
                                </NavLink>
                                <NavLink :href="route('clientes.index')" :active="route().current('clientes.index')">
                                    <UsersIcon class="w-5 h-5 mr-1.5 opacity-70" />
                                    Clientes
                                </NavLink>
                                <NavLink :href="route('prestamos.index')" :active="route().current('prestamos.index')">
                                    <DocumentTextIcon class="w-5 h-5 mr-1.5 opacity-70" />
                                    Préstamos
                                </NavLink>
                                <NavLink :href="route('articulos.index')" :active="route().current('articulos.index')">
                                    <TagIcon class="w-5 h-5 mr-1.5 opacity-70" />
                                    Artículos
                                </NavLink>
                                <NavLink :href="route('estadisticas')" :active="route().current('estadisticas')">
                                    <ChartBarIcon class="w-5 h-5 mr-1.5 opacity-70" />
                                    Estadísticas
                                </NavLink>
                                <NavLink :href="route('gastos.index')" :active="route().current('gastos.*')">
                                    <ArrowTrendingDownIcon class="w-5 h-5 mr-1.5 opacity-70" />
                                    Gastos
                                </NavLink>
                                <NavLink :href="route('caja.index')" :active="route().current('caja.*')">
                                    <ArchiveBoxIcon class="w-5 h-5 mr-1.5 opacity-70" />
                                    Caja
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden md:flex flex-1 max-w-lg mx-4">
                            <BuscadorGlobal />
                        </div>

                        <div class="hidden sm:flex sm:items-center">
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}
                                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')"> Perfil </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button"> Cerrar Sesión </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center lg:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="lg:hidden">
                    
                    <div class="px-4 pt-2 pb-2 border-b border-gray-200">
                         <BuscadorGlobal />
                    </div>

                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('clientes.index')" :active="route().current('clientes.index')">
                            Clientes
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('prestamos.index')" :active="route().current('prestamos.index')">
                            Préstamos
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('articulos.index')" :active="route().current('articulos.index')">
                            Artículos
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('estadisticas')" :active="route().current('estadisticas')">
                            Estadísticas
                        </ResponsiveNavLink>
                         <ResponsiveNavLink :href="route('gastos.index')" :active="route().current('gastos.*')">
                            Gastos
                        </ResponsiveNavLink>
                         <ResponsiveNavLink :href="route('caja.index')" :active="route().current('caja.*')">
                            Caja
                        </ResponsiveNavLink>
                    </div>

                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">{{ $page.props.auth.user.name }}</div>
                            <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"> Perfil </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button"> Cerrar Sesión </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>