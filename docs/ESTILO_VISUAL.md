# 🎨 Guía de Estilo Visual — ZebtevedLu

> Proyecto Laravel + Inertia + Vue 3 (Composition API) + Tailwind CSS  
> Sistema de gestión financiera (préstamos, clientes, artículos, caja, reportes)

---

## 1. Paleta de Colores

### Tema Oscuro (predeterminado)

| Contexto | Color | Tailwind | Uso |
|----------|-------|----------|-----|
| **Fondo principal** | Negro azabache | `bg-zinc-950` / `bg-[#0f0f0f]` | Layout base |
| **Fondo tarjetas** | Negro suave | `bg-[#1a1a1a]` / `bg-zinc-900` | Cards, modales, tablas |
| **Bordes** | Gris muy oscuro | `border-gray-800` / `border-gray-700` | Separadores y contenedores |
| **Texto primario** | Blanco humo | `text-gray-200` / `text-white` | Títulos y contenido principal |
| **Texto secundario** | Gris medio | `text-gray-500` / `text-gray-400` | Metadatos, subtítulos |

### Tema Claro (alternativo)

| Contexto | Color | Tailwind |
|----------|-------|----------|
| Fondo principal | `bg-gray-50` | Gris muy claro |
| Fondo tarjetas | Blanco | `bg-white` |
| Bordes | `border-gray-200` / `border-gray-100` | Gris suave |

### Colores de Acento (por contexto semántico)

| Rol | Tailwind | Significado |
|-----|----------|-------------|
| **Primario / Acción** | `indigo-500 / indigo-600` | Links, botones principales, hover, border focus |
| **Éxito / Pagado** | `emerald-400 / emerald-500` | Pagos completados, KPI positivo |
| **Advertencia / Mora leve** | `amber-400 / amber-500` | Riesgo leve, próximos vencimientos |
| **Peligro / Mora alta** | `red-400 / red-500` | Vencido, crítico, errores |
| **Artículos / Tags** | `fuchsia-500` | Iconos de artículos |
| **Información** | `blue-500` | Estados informativos |
| **Neutral / Remate** | `gray-500 / gray-600` | Pagados, remates |

### Fondos de **semáforo** (préstamos en tarjetas compactas)

| Estado | BG | Texto | Borde |
|--------|----|-------|-------|
| 🟢 **Verde** (estable / al día) | `bg-emerald-200` dark: `bg-emerald-500/30` | `text-emerald-800` | `border-emerald-400` |
| 🟡 **Amarillo** (advertencia) | `bg-yellow-200` dark: `bg-yellow-500/30` | `text-yellow-800` | `border-yellow-400` |
| 🔴 **Rojo** (crítico) | `bg-red-200` dark: `bg-red-500/30` | `text-red-800` | `border-red-400` |
| 🔵 **Azul** (por vender) | `bg-blue-200` dark: `bg-blue-500/30` | `text-blue-800` | `border-blue-400` |
| ⚫ **Remate** | `bg-gray-300` dark: `bg-gray-600` | `text-gray-900` | `border-gray-500` |

---

## 2. Tipografía

| Propiedad | Valor |
|-----------|-------|
| **Fuente principal** | `Inter` (Tailwind default) |
| **Fuente numérica/moneda** | `UFCSans` (Bold) — archivo `public/Fuentes/UFCSans-Bold.ttf` |
| **Clase utilitaria** | `.font-ufc` para montos, códigos, KPIs, badges |
| **Tamaños comunes** | Títulos: `text-xl` / `text-2xl` + `font-bold` + `tracking-tight` |
| | Cuerpo: `text-sm` / `text-xs` + `font-medium` |
| | Metadatos: `text-[10px]` + `font-bold` + `uppercase` + `tracking-widest` |
| **Transformación** | Todos los inputs y textareas en **MAYÚSCULAS** automáticas (CSS `text-transform: uppercase`) |
| **Monospace** | `font-mono` para códigos de préstamo |

---

## 3. Bordes y Esquinas

| Elemento | Border-radius | Tailwind |
|----------|---------------|----------|
| Cards principales | `rounded-[24px]` / `rounded-[28px]` | Esquinas muy redondeadas |
| Cards secundarias | `rounded-2xl` | 16px |
| Botones / Inputs | `rounded-xl` | 12px |
| Badges / Chips | `rounded-full` | Totalmente redondo |
| Tarjetas de préstamo | `rounded-[20px]` | Característico |

---

## 4. Sombras

| Elemento | Sombra |
|----------|--------|
| Cards principales | `shadow-sm dark:shadow-xl` + `border` |
| Botón primario | `shadow-lg shadow-indigo-500/25` |
| Avatar / Iconos de gradiente | `shadow-[0_4px_12px_rgba(0,0,0,0.15)]` |
| Buscador | `shadow-xl` |
| Tablero de cobranza | `shadow-2xl` |
| Efecto glow en KPI | `blur-2xl` con opacidad |

---

## 5. Layout General

```
┌─────────────────────────────────────────────────────────┐
│ [SIDEBAR]  │              MAIN CONTENT                  │
│            │  ┌─ HEADER (sticky) ───────────────────┐   │
│   w-72     │  │  Search (Ctrl+K)     🌙             │   │
│  colapsable │  └────────────────────────────────────┘   │
│   a w-20   │                                             │
│            │  ┌─ PAGE CONTENT (scrollable) ──────────┐   │
│  NavLinks  │  │                                       │   │
│            │  │   Max width: max-w-[1600px]           │   │
│  User      │  │   Padding: px-4 sm:px-6 lg:px-8      │   │
│  Profile   │  │   Grid: 1/2/3/4 cols responsive      │   │
│            │  └───────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

- **Sidebar** colapsable (guardado en localStorage)
- **Header** sticky con `backdrop-blur-xl`
- **Z-index**: sidebar `z-30`, header `z-40`, modales `z-50`, tablero cobranza `z-[100]`
- **Contenido**: scroll suave `scroll-smooth`
- **Scrollbar personalizado**: 6px, gris oscuro en dark mode

---

## 6. Componentes Reutilizables

### 6.1 Modal
- Usa `<dialog>` nativo de HTML
- Backdrop `bg-gray-900 opacity-75`
- Animación `scale-95 → scale-100` + `opacity 0 → 1`
- `rounded-2xl`, `border`, `shadow-xl`
- Anchos: `sm:max-w-sm` hasta `sm:max-w-2xl`

### 6.2 Pagination
- Links como pills con `rounded-xl`
- Active: fondo `bg-indigo-600` + texto blanco + `shadow-lg shadow-indigo-500/30`
- Inactive: `border border-gray-200` hover `hover:bg-gray-50`

### 6.3 EmptyState
- Icono circular (green/amber/blue según tipo)
- Título + descripción + slot para acciones
- `py-16 text-center`

### 6.4 StatusBadge
- Semáforo con punto (`useDot`) o sin punto
- `rounded-full text-xs font-black` en caps
- Colores por estado: `emerald` / `amber` / `red` / `indigo` / `gray`

### 6.5 SortIcon
- Flecha hacia arriba = `asc`, abajo = `desc`
- Inactivo: gris suave con opacidad reducida
- Tamaño: `w-3 h-3`

### 6.6 Avatar
- Iniciales en gradiente (`bg-gradient-to-br`) desde 5 combinaciones
- Foto real si está disponible
- Tamaño configurable, `object-cover`, `rounded-2xl`
- Fuente: `UFCSansCondensedMedium` para iniciales

### 6.7 Buscador Global
- Atajo `Ctrl+K` / `Cmd+K`
- Input con `rounded-2xl`, `border-2`, fondo oscuro `bg-[#1a1a1a]`
- Resultados con animación `translate-y-2 → 0` y opacidad
- Cada resultado muestra: icono según tipo (Cliente/Prestamo/Artículo) + título + subtítulo + badge de tipo
- Debounce 300ms + cancelación de peticiones previas (AbortController)

---

## 7. Animaciones y Transiciones

| Elemento | Animación |
|----------|-----------|
| Sidebar collapse | `transition-all duration-300 ease-in-out` |
| Hover en links | `hover:pl-5` (desplazamiento suave) |
| KPI cards | `hover:-translate-y-1` + `hover:shadow-lg` |
| Modal | `scale-95 → 100` + opacidad, duración 300ms |
| Flash messages | Slide desde arriba (translate-y), auto-hide 5s |
| Tablero cobranza | `slide-up` (custom keyframe) 0.3s |
| Buscador resultados | `translate-y-2 → 0` + `scale-95 → 100` |
| Hover en botones | `hover:scale-105`, `hover:brightness-105` |
| Spinner loading | `animate-spin` |
| Pulse en indicador rojo | `animate-pulse` |

---

## 8. Iconos

- **Librería**: `@heroicons/vue` (outline)
- Importados como componentes individuales
- Tamaño estándar: `w-5 h-5` / `w-6 h-6`
- Ejemplos de íconos usados:
  - `Squares2X2Icon` — Dashboard
  - `UsersIcon` — Clientes
  - `DocumentTextIcon` — Préstamos, Reportes
  - `TagIcon` — Artículos
  - `ChartBarIcon` — Estadísticas
  - `ArchiveBoxIcon` — Caja Chica, Respaldos
  - `ArrowTrendingDownIcon` — Gastos
  - `SunIcon` / `MoonIcon` — Toggle tema
  - `MagnifyingGlassIcon` — Búsqueda
  - `PlusIcon` — Crear nuevo
  - `PencilSquareIcon` — Editar
  - `ChevronLeftIcon` / `ChevronRightIcon` — Sidebar collapse
  - `FunnelIcon` — Filtros
  - `TrashIcon` — Eliminar
  - `XMarkIcon` — Cerrar
  - `CheckCircleIcon` / `XCircleIcon` — Flash messages

---

## 9. Patrones de Diseño

### Tarjetas de préstamo (LoanItem)
- **Compactas** (sin expandir): solo código + estado + monto + iniciales del cliente
- **Expandidas** (click): Detalle completo con prendas, pagos, fechas, avatar grande
- Colores de fondo según estado (verde/amarillo/rojo/opaco si pagado)
- Fuente `font-ufc` para montos y códigos
- Transiciones `ease-spring` con `scale` y `ring` cuando expandido

### Tablas
- Encabezados: `text-[10px] uppercase font-bold tracking-wider`
- Filas: `hover:bg-gray-50` / `dark:hover:bg-white/[0.02]`
- Divisores: `border-gray-100` / `border-gray-800`
- Acciones: iconos en `w-8 h-8` con `rounded-lg`

### Botones principales
- Fondo `bg-indigo-600` → hover `bg-indigo-700`
- `rounded-xl`, `font-bold`, `text-sm`
- Sombra `shadow-lg shadow-indigo-500/25`
- `whitespace-nowrap` para evitar saltos

### Badges de tipo (buscador global)
| Tipo | Color |
|------|-------|
| Cliente | `bg-green-500/10 text-green-400 border-green-500` |
| Préstamo | `bg-indigo-500/10 text-indigo-400 border-indigo-500` |
| Artículo | `bg-fuchsia-500/10 text-fuchsia-400 border-fuchsia-500` |

---

## 10. Responsive Design

| Breakpoint | Comportamiento |
|------------|----------------|
| `md:hidden` | Sidebar oculta, menú hamburguesa visible |
| `md:flex` | Sidebar visible en desktop |
| Grid: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4` | KPIs responsive |
| Tabla: `overflow-x-auto` | Scroll horizontal en mobile |
| Buscador: `w-[95vw] sm:w-full` | Resultados ocupan ancho en mobile |

---

## 11. Sidebar Colapsable

- **Ancho expandido**: `w-72` (72 = 18rem)
- **Ancho colapsado**: `w-20` (20 = 5rem)
- Guarda estado en `localStorage('sidebarCollapsed')`
- Textos se ocultan con `w-0 opacity-0 ml-0` en colapsado
- Botón de colapso flotante `-right-3 top-6` cuando colapsado
- Categorías: **Gestión** (Clientes, Préstamos, Artículos), **Finanzas** (Estadísticas, Caja Chica, Gastos, Reporte Financiero), **Sistema** (Respaldos BD, solo admin)

---

## 12. Tema Oscuro/Claro

- Clase `dark` en `<html>` (modo oscuro = predeterminado)
- Toggle manual con botón en header (Sun/Moon icons)
- Guarda preferencia en `localStorage('theme')`
- Detecta preferencia del sistema como fallback: `prefers-color-scheme: dark`
- Transiciones suaves con `transition-colors duration-300`

---

## 13. Mensajes Flash (Toast)

- Posición: `absolute top-20 right-4`, `z-50`
- Icono: `CheckCircleIcon` (verde) / `XCircleIcon` (rojo)
- Animación: slide desde arriba (300ms)
- Auto-hide después de 5 segundos
- Cierre manual con botón X
