# 🔄 Flujo de Trabajo, Estructura de Páginas y Sistema Visual

> Documento resumen del flujo operativo, disposición de las páginas,  
> manejo de avatares, iconografía y patrones visuales del sistema ZebtevedLu

---

## 1. Flujo de Trabajo General (User Journey)

### 1.1 Ciclo Operativo Principal

```
ACCESO → DASHBOARD → GESTIÓN → OPERACIONES → REPORTES
```

#### Paso a paso:

```
1. LOGIN (Auth Breeze)
     ↓
2. DASHBOARD — Visión general del negocio
     ├── KPIs (total prestado, recuperado, intereses, mora)
     ├── Cronograma mensual de préstamos (agrupados por mes/semana)
     ├── Gráfico flujo de caja (6 meses)
     ├── Top deudores
     └── Botón "Cobros Próximos" → Tablero de Cobranza
     ↓
3. GESTIÓN DE CLIENTES
     ├── Lista paginada con búsqueda
     ├── Crear cliente (formulario)
     ├── Editar cliente
     └── Detalle del cliente (historial completo)
     ↓
4. GESTIÓN DE PRÉSTAMOS
     ├── Lista paginada con búsqueda
     ├── Crear préstamo (con prendas/fotos)
     ├── Editar préstamo
     ├── Ver PDF del contrato
     └── Registrar pagos
     ↓
5. GESTIÓN DE ARTÍCULOS (prendas)
     └── CRUD asociado a préstamos
     ↓
6. CAJA CHICA
     ├── Saldo actual + ingresos/egresos del período
     ├── Registrar capital
     └── Registrar gastos
     ↓
7. ESTADÍSTICAS
     ├── Gráficos (préstamos/día, capital, rentabilidad)
     ├── KPIs avanzados
     ├── Proyección de ingresos
     └── Ranking
     ↓
8. REPORTE FINANCIERO
     ├── Pestañas: Resumen, Préstamos, Intereses, Gastos, Capital, Remate, Salud
     ├── Filtros: Mensual/Semanal/Global
     ├── Exportar PDF
     └── Exportar Excel
```

### 1.2 Flujo de Cobranza

```
DASHBOARD
  └→ Click "Cobros Próximos"
       └→ TABLERO DE COBRANZA (overlay z-[100])
            ├── Cobros vencidos (pendientes de semanas anteriores)
            ├── 4 semanas horizontales × 7 días (grilla)
            │    └── Cada día: tarjetas de préstamo por cobrar
            └── Lista de Recuperación (préstamos > 120 días para remate)
                 └── Tabla con antigüedad, saldo y artículos
```

### 1.3 Flujo de Navegación por Sidebar

```
SIDEBAR (categorías)
│
├── 📊 Dashboard           → /dashboard
│
├── 👥 Clientes            → /clientes
├── 📄 Préstamos           → /prestamos
├── 🏷️ Artículos           → /articulos
│
├── 📈 Estadísticas        → /estadisticas
├── 🗃️ Caja Chica          → /caja
├── 📉 Gastos              → /gastos
├── 📋 Reporte Financiero  → /reportes/financiero
│
└── (admin) 🗄️ Respaldos BD → /backup/create
```

---

## 2. Estructura y Posiciones de las Páginas

### 2.1 Layout Base (AuthenticatedLayout)

```
┌─────────────────────────────────────────────────────────────────┐
│ [SIDEBAR]                  │ [HEADER - sticky z-40]              │
│                            │  ┌─────────────────────────────┐    │
│   ┌── Logo ────┐          │  │ [☰]  Buscador Global     🌙 │    │
│   │    Z       │          │  │      (Ctrl+K)               │    │
│   │ ZebtevedLu │          │  └─────────────────────────────┘    │
│   └────────────┘          │                                      │
│                            │ [MAIN CONTENT - scrollable]         │
│   ┌── Nav Links ──┐       │                                      │
│   │ 📊 Dashboard   │       │  ┌──────────────────────────┐       │
│   │                │       │  │                          │       │
│   │ GESTIÓN        │       │  │   max-w-[1600px] mx-auto │       │
│   │ 👥 Clientes    │       │  │   px-4 sm:px-6 lg:px-8   │       │
│   │ 📄 Préstamos   │       │  │                          │       │
│   │ 🏷️ Artículos   │       │  │   PAGE CONTENT           │       │
│   │                │       │  │   (slot)                  │       │
│   │ FINANZAS       │       │  │                          │       │
│   │ 📈 Estadísticas│       │  │                          │       │
│   │ 🗃️ Caja Chica  │       │  │                          │       │
│   │ 📉 Gastos      │       │  └──────────────────────────┘       │
│   │ 📋 Rpte Financ │       │                                      │
│   │                │       │                                      │
│   └────────────────┘       │                                      │
│                            │                                      │
│   ┌── User Profile ─┐     │                                      │
│   │  [A] Admin       │     │                                      │
│   │  → Ver perfil   │     │                                      │
│   └─────────────────┘     │                                      │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Página de Dashboard

```
┌── Sticky Header ─────────────────────────────────────────────┐
│ [📊] Dashboard General   │ [Global][Activos][Mora][Pagados]  │
└──────────────────────────────────────────────────────────────┘

┌── KPIs ──────────────────────────────────────────────────────┐
│ [Total Colocado] [Recuperado] [Ganancia Interés] [Riesgo/Mora]│
└──────────────────────────────────────────────────────────────┘

┌── 2/3  ────────────────────┐  ┌── 1/3 ────────────────────┐
│  Cronograma de Actividad    │  │ Quick Actions             │
│  ┌─ Mes 1 ──────────────┐  │  │ [Nuevo Préstamo]          │
│  │ Semana 1              │  │  │ [Nuevo Cliente]           │
│  │ [card][card][card]... │  │  │                            │
│  │ Semana 2              │  │  │ [Cobros Próximos] btn     │
│  │ [card][card]...       │  │  │                            │
│  └───────────────────────┘  │  │ Gráfico Flujo de Caja     │
│  ┌─ Mes 2 ──────────────┐  │  │                            │
│  │ ...                    │  │  │ Top Deudores              │
│  └───────────────────────┘  │  │ ┌─ Cliente 1 ─────┐      │
└─────────────────────────────┘  │ │ Nombre    $XXXX │      │
                                 │ └─────────────────┘      │
                                 └──────────────────────────┘
```

### 2.3 Páginas de Listado (Clientes, Préstamos, Gastos, Artículos)

```
┌── Header ────────────────────────────────────────────────────┐
│ [icon] Título          │ [🔍 Buscar...]  [➕ Nuevo]          │
│         Subtítulo      │                                      │
└──────────────────────────────────────────────────────────────┘

┌── Card principal (rounded-[24px]) ──────────────────────────┐
│  ┌── Tabla ───────────────────────────────────────────┐     │
│  │ Header │ Header │ Header │ Header │     Acciones   │     │
│  ├────────┼────────┼────────┼────────┼────────────────┤     │
│  │ [Avatar]│ Dato   │ Dato   │ Dato   │ [✏️] [🗑️]      │     │
│  │ Nombre  │        │        │        │                │     │
│  ├────────┼────────┼────────┼────────┼────────────────┤     │
│  │ ...     │        │        │        │                │     │
│  └────────────────────────────────────────────────────┘     │
│  ┌── Pagination ───────────────────────────────────────────┐│
│  │ [1] [2] [3] ...                                        ││
│  └─────────────────────────────────────────────────────────┘│
└──────────────────────────────────────────────────────────────┘
```

### 2.4 Páginas de Formulario (Crear/Editar)

```
┌── Header ────────────────────────────────────────────────────┐
│ [← Volver]  [icon] Título                                    │
│                    Subtítulo / código                         │
└──────────────────────────────────────────────────────────────┘

┌── Grid 1/3 | 2/3 ────────────────────────────────────────────┐
│                                                               │
│  ┌── Col 1 (1/3) ────────────┐  ┌── Col 2 (2/3) ─────────┐ │
│  │  Detalles del Contrato     │  │  Prendas / Artículos    │ │
│  │  ───────────────────       │  │  ─────────────────      │ │
│  │  ○ Código                  │  │  ┌─ Artículo #1 ────┐  │ │
│  │  ○ Cliente (autocomplete)  │  │  │ [📷] Nombre      │  │ │
│  │  ○ Monto                   │  │  │       Descripción │  │ │
│  │  ○ Fecha                   │  │  └──────────────────┘  │ │
│  │  ○ Multa                   │  │  ┌─ Artículo #2 ────┐  │ │
│  │                            │  │  │ [📷] Nombre      │  │ │
│  └────────────────────────────┘  │  └──────────────────┘  │ │
│                                  │  [➕ Agregar Artículo]  │ │
│                                  └────────────────────────┘ │
│                                                               │
│  ┌── Action Bar (full width) ──────────────────────────────┐ │
│  │                     [Cancelar]  [✅ Confirmar y Crear]  │ │
│  └──────────────────────────────────────────────────────────┘ │
└───────────────────────────────────────────────────────────────┘
```

### 2.5 Página de Reporte Financiero

```
┌── Filtros ───────────────────────────────────────────────────┐
│ [Mensual][Semanal][Global]  [Año ▼] [Mes ▼] [Semana ▼]      │
└──────────────────────────────────────────────────────────────┘

┌── Tabs ──────────────────────────────────────────────────────┐
│ [Resumen][Préstamos][Intereses][Gastos][Capital][Remate][Salud]│
└──────────────────────────────────────────────────────────────┘

┌── Tab Content (ej: Resumen) ────────────────────────────────┐
│  [📥 Exportar Excel]  [📥 Descargar PDF]                    │
│                                                              │
│  KPIs: [Préstamos] [Intereses] [Cap. Recup.] [Gastos]       │
│                                                              │
│  ┌── 3 Columnas ────────────────────────────────────────┐   │
│  │ [Resultados]  │ [Estado Cartera]  │ [Semáforo]       │   │
│  │  Período      │  Vigente/Riesgo   │  🟢🟡🟠🔴       │   │
│  └──────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────┘
```

### 2.6 Tablero de Cobranza (Overlay)

```
┌─────── OVERLAY z-[100] ─────────────────────────────────────┐
│ [🗓️] Tablero de Cobranza  [🟢5][🟡3][🔴2]      [✖️ Cerrar] │
│        Hoy + 4 semanas · $XX,XXX por cobrar                 │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ ┌── Cobros Pendientes (ya vencidos) ──────────────────────┐ │
│ │ [card][card][card][card][card]...                        │ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                              │
│ ┌── Semana 1 (Lun 10 - Sáb 15) ──────────────── $X,XXX ──┐ │
│ │ LUN    MAR    MIE    JUE    VIE    SAB    DOM            │ │
│ │  10     11     12     13     14     15     16            │ │
│ │ [card] [card]  [card]   —     [card]   —      —         │ │
│ │ [card]                                                  │ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                              │
│ ┌── Semana 2 ... ──────────────────────────────────────────┐│
│ └───────────────────────────────────────────────────────────┘│
│                                                              │
│ ┌── Registro de Recuperación / Remate ─────────────────────┐ │
│ │ ⚠️ Préstamos > 120 días vencidos                         │ │
│ │ ┌─ Tabla ─────────────────────────────────────────────┐  │ │
│ │ │ Cliente │ Código │ Saldo │ Días │ Artículos         │  │ │
│ │ │ ...     │ ...    │ ...   │ ...  │ ...               │  │ │
│ │ └─────────────────────────────────────────────────────┘  │ │
│ └──────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────┘
```

### 2.7 Página de Caja Chica

```
┌── Grid ──────────────────────────────────────────────────────┐
│ [Saldo Actual]    │ [+ Ingresos] │ [- Egresos] │ [+ Capital]│
│    $XX,XXX.XX     │  $X,XXX      │  $X,XXX     │  [Gasto]   │
└──────────────────────────────────────────────────────────────┘

┌── Tabs de filtro: [hoy][semana][mes][todo] ─────────────────┐
│                                                              │
│ ┌── Lista de movimientos ───────────────────────────────┐   │
│ │ [🏦] Descripción             [+ $X,XXX]  Saldo: $XX   │   │
│ │ [💸] Descripción             [- $X,XXX]  Saldo: $XX   │   │
│ │ [💰] Descripción             [+ $X,XXX]  Saldo: $XX   │   │
│ └────────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Manejo de Avatares

### 3.1 Componente `<Avatar>`

El sistema usa un componente **unificado** `Avatar.vue` para mostrar la imagen o iniciales de cualquier entidad.

**Ubicación**: `resources/js/Components/Avatar.vue`

**Props**:

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `name` | String | *requerido* | Nombre del usuario/cliente |
| `identifier` | Number\|String | *requerido* | ID único para hash de color |
| `fotoUrl` | String | `null` | URL de la foto (si existe) |
| `sizeClass` | String | `'w-10 h-10 text-sm'` | Clases Tailwind para tamaño |
| `roundedClass` | String | `'rounded-2xl'` | Clase de border-radius |

### 3.2 Lógica de Avatares

```
¿Tiene foto (fotoUrl)?
  ├── SÍ → Muestra <img> con la foto
  │        src = fotoUrl si empieza con "http" o "data:"
  │        src = "/storage/{fotoUrl}" si es path local
  │        Clases: object-cover + shadow + hover:scale-105
  │
  └── NO  → Muestra iniciales con gradiente
           ├── Iniciales: primeras 1-3 letras del nombre en MAYÚSCULAS
           ├── Gradiente: seleccionado por hash del nombre/ID
           │    └── 5 gradientes posibles:
           │        • from-blue-500 to-cyan-500
           │        • from-purple-500 to-fuchsia-500
           │        • from-emerald-500 to-teal-500
           │        • from-orange-500 to-amber-500
           │        • from-rose-500 to-pink-500
           ├── Fuente: UFCSansCondensedMedium
           └── Tamaño texto: responsive via container queries (cqi)
```

### 3.3 Dónde se usan Avatares

| Página / Componente | Tamaño | Forma | Contexto |
|---------------------|--------|-------|----------|
| **Sidebar** (usuario actual) | `w-9 h-9` | `rounded-full` | Gradiente con inicial |
| **Lista Clientes** (cada fila) | `w-10 h-10` | `rounded-full` | Puede tener foto real |
| **Lista Préstamos** (cada fila) | `w-8 h-8` | `rounded-full` | Iniciales del cliente |
| **Top Deudores** (Dashboard) | `w-10 h-10` | `rounded-full` | Puede tener foto real |
| **Buscador Global** (resultados) | `w-12 h-12` | `rounded-xl` | Cliente con iniciales |
| **LoanItem expandido** | `w-12 h-12` | `rounded-full` | Iniciales del cliente |
| **LoanItem colapsado** | `w-6 h-6` | `rounded-full` | Mini avatar |
| **Profile Edit** | (estándar) | `rounded-full` | Foto de perfil |

### 3.4 Sistema Alternativo de Avatares (Legacy)

Antes del componente unificado, algunos componentes tenían su propia lógica de avatares con colores planos:

```javascript
const getAvatarColor = (name) => {
    const colors = [
        'bg-red-200 text-red-900', 'bg-pink-200 text-pink-900', ...
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return colors[Math.abs(hash) % colors.length];
};
```

Este sistema legacy se usa todavía en:
- `LoanItem.vue` (Dashboard) — para tarjetas expandidas/colapsadas
- ~~Clientes~~ — **ya migrado** al Avatar.vue unificado
- ~~Préstamos~~ — **ya migrado** al Avatar.vue unificado

---

## 4. Iconografía

### 4.1 Librería

```json
"@heroicons/vue": "^2.2.0"
```

Importados como **componentes individuales** desde `@heroicons/vue/24/outline`

### 4.2 Catálogo de Iconos Usados

| Icono | Nombre | Uso Principal |
|-------|--------|---------------|
| `Squares2X2Icon` | 📊 Cuadrado 2x2 | Dashboard (sidebar) |
| `UsersIcon` | 👥 Usuarios | Clientes |
| `DocumentTextIcon` | 📄 Documento | Préstamos, Reporte Financiero, PDF |
| `TagIcon` | 🏷️ Etiqueta | Artículos |
| `ChartBarIcon` | 📈 Barra gráfico | Estadísticas |
| `BanknotesIcon` | 💵 Billetes | Préstamos (reporte) |
| `ArchiveBoxIcon` | 🗃️ Caja | Caja Chica, Respaldos BD |
| `ArrowTrendingDownIcon` | 📉 Tendencia baja | Gastos |
| `MagnifyingGlassIcon` | 🔍 Lupa | Búsqueda global y por tabla |
| `PlusIcon` | ➕ Más | Crear nuevo (préstamo, cliente, etc.) |
| `PencilSquareIcon` | ✏️ Lápiz | Editar |
| `TrashIcon` | 🗑️ Papelera | Eliminar |
| `ArrowLeftIcon` | ← Flecha | Volver atrás |
| `SunIcon` | ☀️ Sol | Toggle tema claro |
| `MoonIcon` | 🌙 Luna | Toggle tema oscuro |
| `ChevronLeftIcon` / `ChevronRightIcon` | ◀ ▶ | Colapsar/expandir sidebar |
| `CheckCircleIcon` | ✅ Check círculo | Flash success |
| `XCircleIcon` | ❌ X círculo | Flash error |
| `XMarkIcon` | ✖️ X | Cerrar modales, limpiar búsqueda |
| `FunnelIcon` | 🔻 Embuido | Filtros, empty state |
| `UserPlusIcon` | 👤➕ | Nuevo cliente |
| `PhotoIcon` | 📷 Foto | Subir imágenes de artículos |
| `ArrowDownTrayIcon` | ⬇️ Descarga | Exportar PDF |
| `PrinterIcon` | 🖨️ Impresora | Imprimir |
| `TableCellsIcon` | 📋 Tabla | Exportar Excel |
| `ExclamationTriangleIcon` | ⚠️ Triángulo | Alertas, remate |
| `HeartIcon` | ❤️ Corazón | Salud del negocio |
| `GlobeAltIcon` | 🌐 Globo | Filtro global |
| `ClockIcon` | 🕐 Reloj | Filtro semanal |
| `CalendarDaysIcon` | 📅 Calendario | Cobros próximos, filtro mensual |
| `ChartPieIcon` | 📊 Circular | Resumen |
| `ReceiptPercentIcon` | % Recibo | Intereses |
| `ShoppingBagIcon` | 🛍️ Bolsa | Gastos |
| `ArrowPathIcon` | 🔄 Flechas | Capital recuperado |
| `ListBulletIcon` | 📋 Lista | Tabs de listados |

### 4.3 Patrón de Uso de Iconos

**En Sidebar**: `w-5 h-5 shrink-0` + texto al lado  
**En headers de página**: Icono en contenedor `w-12 h-12 rounded-2xl bg-gradient-to-br`  
**En acciones de tabla**: Icono solo en `w-8 h-8 rounded-lg`  
**En botones**: Icono + texto (gap-2)  
**En tabs**: Icono `w-5 h-5` + label  
**En estados vacíos**: Icono en círculo `w-16 h-16 rounded-full`  

### 4.4 Gradientes por Contexto (Headers de Página)

| Página | Gradiente |
|--------|-----------|
| **Dashboard** | `from-indigo-500 to-blue-600` |
| **Clientes** | `from-orange-500 to-amber-600` |
| **Préstamos (crear)** | `from-indigo-500 to-blue-600` |
| **Préstamos (editar)** | `from-amber-500 to-orange-600` |
| **Gastos** | `from-orange-500 to-amber-600` |
| **Caja Chica** | `from-gray-900 to-gray-800` (sin gradiente) |
| **Estadísticas** | (varía) |
| **Reporte Financiero** | (varía según tab) |
| **Tablero Cobranza** | `from-amber-500 to-orange-600` |
| **Nuevo Cliente** | `from-indigo-500 to-purple-600` |
| **Respaldos** | `ArchiveBoxIcon` (simple) |

---

## 5. Patrones Visuales por Tipo de Página

### 5.1 Páginas de Listado (Index)

```
Header:
  [icon gradient] Título + Subtítulo
  [search input]  [action button primary]

Card principal:
  rounded-[24px] + border + shadow
  Tabla con hover en filas
  Acciones en iconos w-8 h-8
  
Footer:
  Pagination component (pills)
```

### 5.2 Páginas de Formulario (Create/Edit)

```
Header:
  [← Volver] + [icon gradient] Título
  Subtítulo / código

Grid 1/3 : 2/3
  Col 1: Datos principales (contrato)
  Col 2: Datos secundarios (artículos)
  
Action Bar (full width):
  [Cancelar]  [Botón primario]
```

### 5.3 Páginas de Detalle

```
Header con información del registro
Secciones en cards separadas
Grids de datos y métricas
```

### 5.4 Dashboard

```
Sticky header con filtros tipo tabs
KPIs en grid 4 columnas
Contenido principal 2/3 : 1/3
  Izquierda: cronograma mensual
  Derecha: acciones rápidas + gráficos + top listas
```

### 5.5 Overlays / Modales

```
Modal nativo <dialog>:
  Backdrop: bg-gray-500 opacity-75 / bg-gray-900 opacity-75 (dark)
  Animación: scale-95 → scale-100 + fade
  rounded-2xl + shadow-xl + border
  
Tablero Cobranza (Teleport to body):
  fixed inset-0 z-[100]
  Backdrop: bg-black/60 backdrop-blur-sm
  Panel: max-w-[1600px] + rounded-3xl
  Animación: slide-up custom
```

---

## 6. Resumen de Z-index

| Elemento | z-index |
|----------|---------|
| Sidebar | `z-30` |
| Header (sticky) | `z-40` |
| Mobile menu overlay | `z-40` |
| Mobile menu panel | `z-50` |
| Modal backdrop | `z-50` |
| Modal dialog | `z-50` |
| Tablero Cobranza | `z-[100]` |
| Buscador resultados | `z-[100]` |

---

## 7. Resumen de Colores Semánticos por Tipo

| Tipo | Sidebar | Botón primario | Hover icono | Badge tipo |
|------|---------|----------------|-------------|------------|
| **Dashboard** | Indigo | — | — | — |
| **Clientes** | — | Indigo | Indigo | Green |
| **Préstamos** | — | Indigo | Indigo | Indigo |
| **Artículos** | — | — | — | Fuchsia |
| **Estadísticas** | — | — | — | — |
| **Caja Chica** | — | Blue (capital) / Orange (gasto) | — | — |
| **Gastos** | — | Orange | Red | — |
| **Edit** (form) | — | Amber | Amber | — |
| **Eliminar** | — | Red | Red | — |
