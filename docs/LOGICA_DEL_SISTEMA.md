# 🧠 Lógica del Sistema — ZebtevedLu

> Documentación de la lógica de negocio, flujos de datos y arquitectura del sistema  
> Sistema de gestión de préstamos prendarios con control financiero

---

## 1. Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                         FRONTEND (Vue 3)                         │
│  Inertia.js (SPA-like) + Vue Router (Ziggy) + Tailwind CSS      │
│                                                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────┐   │
│  │  Pages   │  │Components│  │Composables│  │   Layouts    │   │
│  │ (Vistas) │  │  (UI)    │  │ (Lógica)  │  │ Authenticated│   │
│  └────┬─────┘  └──────────┘  └──────────┘  │ GuestLayout  │   │
│       │                                     └──────────────┘   │
│       ▼                                                         │
│  Inertia::render() ← ← ← ← ← ← ← ← ← ← ← ← ← ← ← ← ← ← ←     │
└───────────────────────────────────────────────────────────────┘ │
                                                                  │
┌──────────────────────────────────────────────────────────────┐  │
│                    BACKEND (Laravel)                          │  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────┐ │  │
│  │  Routes  │→ │Controllers│→ │  Models  │→ │  Database    │ │  │
│  │ (web.php)│  │  (CRUD)   │  │ (Eloquent)│  │  (MySQL)     │ │  │
│  └──────────┘  └──────────┘  └──────────┘  └──────────────┘ │  │
└──────────────────────────────────────────────────────────────┘  │
                                                                  │
                         ▼                                        │
                   Inertia::render() ──────────────────────────────┘
```

- **Inertia.js**: Conecta Laravel con Vue sin API REST
- **Ziggy**: Genera rutas Laravel en JS (ej: `route('clientes.index')`)
- **Composition API**: Todos los componentes usan `<script setup>`
- **Estados**: Se pasan como `props` desde controllers usando `Inertia::render()` con `with()`

---

## 2. Modelos de Datos Principales

### 2.1 Cliente (`App\Models\Cliente`)

```
id              - BIGINT UNSIGNED (PK)
nombre          - VARCHAR(255)
ci              - VARCHAR(50)      - Cédula de identidad
direccion       - VARCHAR(255)     - Domicilio
celular         - VARCHAR(20)      - Teléfono
foto_url        - VARCHAR(255)     - Nullable, foto del cliente
observaciones   - TEXT             - Nullable
created_at / updated_at - TIMESTAMPS
```

Relaciones:
- `HasMany(Préstamo)` — Un cliente puede tener múltiples préstamos

### 2.2 Préstamo (`App\Models\Prestamo`)

```
id                  - BIGINT UNSIGNED (PK)
codigo              - VARCHAR(50)       - Código único identificatorio (ej: PRE-001)
cliente_id          - BIGINT UNSIGNED   - FK → Cliente
monto               - DECIMAL(10, 2)    - Capital prestado
fecha_prestamo      - DATE              - Fecha de inicio
fecha_proximo_pago  - DATE              - Próximo vencimiento
fecha_ultimo_pago   - DATE              - Nullable
estado              - ENUM('Activo', 'Vencido', 'Pagado')
esta_en_mora        - BOOLEAN           - Flag de mora
multa_por_retraso   - DECIMAL(10, 2)    - Multa diaria opcional
created_at / updated_at
```

Relaciones:
- `BelongsTo(Cliente)`
- `HasMany(Artículo)` — Prendas en garantía
- `HasMany(Pago)` — Historial de pagos

Campos calculados (accesors/casts en backend):
- `capital_recuperado` — Suma de pagos tipo "Capital"
- `intereses_generados` — Suma de pagos tipo "Interés"
- `saldo_pendiente` — `monto - capital_recuperado`

### 2.3 Artículo (Prenda) (`App\Models\Articulo`)

```
id              - BIGINT UNSIGNED (PK)
prestamo_id     - BIGINT UNSIGNED   - FK → Préstamo
nombre          - VARCHAR(255)      - Nombre del artículo
detalle         - TEXT              - Descripción detallada
foto_url        - VARCHAR(255)      - Nullable, foto de la prenda
created_at / updated_at
```

Relaciones:
- `BelongsTo(Préstamo)`

### 2.4 Pago (`App\Models\Pago`)

```
id              - BIGINT UNSIGNED (PK)
prestamo_id     - BIGINT UNSIGNED   - FK → Préstamo
monto           - DECIMAL(10, 2)    - Monto del pago
tipo            - ENUM('Capital', 'Interés', 'Multa', 'Otro')
fecha_pago      - DATE              - Fecha del pago
created_at / updated_at
```

Relaciones:
- `BelongsTo(Préstamo)`

### 2.5 Gasto

```
id              - BIGINT UNSIGNED (PK)
descripcion     - VARCHAR(255)
monto           - DECIMAL(10, 2)
fecha_gasto     - DATE
tipo_gasto      - VARCHAR(100)      - Categoría del gasto
created_at / updated_at
```

### 2.6 Caja Chica (`caja`)

```
id              - BIGINT UNSIGNED (PK)
tipo            - ENUM('ingreso', 'egreso', 'ajuste')
monto           - DECIMAL(10, 2)
concepto        - VARCHAR(255)
saldo_anterior  - DECIMAL(10, 2)
saldo_posterior - DECIMAL(10, 2)
created_at
```

Cada movimiento registra saldo anterior y posterior (histórico).

---

## 3. CRUDs y Funcionalidades Principales

### 3.1 Dashboard (`DashboardController`)
- **Ruta**: `GET /dashboard`
- **Props**: `reporteAgrupado`, `indicadores`, `estadoFiltro`, `alertas`, `topDeudores`
- **Lógica**:
  - Agrupa préstamos por mes-año con sus semanas
  - Calcula indicadores: total prestado, recuperado, interés generado, mora
  - Filtra por estado (Global/Activo/Mora/Pagado) vía query string
  - Top 5 deudores con más préstamos activos
- **Componentes**: KpiCards, MonthGroup/LoanItem, ChartComponent, TableroCobranza

### 3.2 Gestión de Préstamos (`PrestamoController`)
- `index` — Lista paginada + búsqueda por texto
- `create` — Formulario con búsqueda de cliente + artículos dinámicos
- `store` — Crea préstamo + artículos asociados (usa `FormData` para fotos)
- `edit` — Edición completa
- `update` — Actualización
- `destroy` — Eliminación
- `generarPdf` — Genera contrato PDF (usando Blade + Puppeteer)
- `updateEstado` — Cambia estado manualmente
- `actualizarBasico` — Actualización parcial

### 3.3 Gestión de Clientes (`ClienteController`)
- CRUD completo
- `ClienteDetalleController@show` — Vista detallada con historial
- Búsqueda por nombre/CI (autocomplete en formularios)

### 3.4 Gestión de Artículos (`ArticuloController`)
- CRUD completo, vinculados a préstamos

### 3.5 Gestión de Pagos (`PagoController`)
- `store` — Registrar pago contra un préstamo
- `update` — Editar pago existente
- `destroy` — Eliminar pago
- Actualiza `fecha_ultimo_pago` en el préstamo automáticamente

### 3.6 Caja Chica (`CajaController`)
- `index` — Historial de movimientos + saldo actual
- `store` — Registrar ingreso/egreso/ajuste
- Calcula saldo anterior y posterior automáticamente

### 3.7 Gastos (`GastoController`)
- CRUD completo con categorización

### 3.8 Reporte Financiero (`ReporteController`)
- `reporteFinanciero` — Vista completa de reportes
- `generarPdfFinanciero` — Exportación PDF
- `exportarExcelFinanciero` — Exportación Excel
- Componentes de análisis: AgingCartera, AlertasTemprana, DetalleGastos, DetallePagos, DetallePrestamos, DetalleRemate, EficienciaCobranza

### 3.9 Búsqueda Global (`BusquedaGlobalController`)
- `ajax` — Endpoint JSON para búsqueda en tiempo real
- Busca en: Clientes (nombre/CI), Préstamos (código), Artículos (nombre)
- Retorna resultados con url, título, subtítulo y tipo

---

## 4. Lógica Financiera

### 4.1 Cálculo de Estado de Préstamo

El estado se determina así:
1. **Pagado**: Si `capital_recuperado >= monto` → estado = 'Pagado'
2. **Activo**: Si está al día (ha pagado intereses en el último mes)
3. **Vencido**: Si `fecha_proximo_pago` ha pasado sin pago

### 4.2 Sistema de Envejecimiento (Aging)

| Categoría | Días vencidos | Color | Acción |
|-----------|---------------|-------|--------|
| **Verde** | 0-30 días | 🟢 | Normal, sin preocupación |
| **Amarillo** | 31-90 días | 🟡 | Advertencia, seguimiento |
| **Rojo** | 91-180 días | 🔴 | Crítico, contacto urgente |
| **Azul** | 181-365 días | 🔵 | Preparar para venta/remate |
| **Remate** | >365 días | ⚫ | En proceso de remate |

Este envejecimiento se usa en:
- `TableroCobranza` (vista semanal)
- `AgingCartera` (reporte)
- `ClienteCard` (color de tarjeta)

### 4.3 Tablero de Cobranza

**Ruta**: `GET /api/vencimientos`

Devuelve estructura JSON:
```json
{
  "bloquesSemana": [ // 4 semanas
    {
      "semana": 1,
      "rango": "Lun 10 - Sab 15",
      "resumen": { "total": 5, "monto_total": 2500 },
      "dias": [
        { "fecha": "2026-01-10", "nombre_dia": "LUN", "numero_dia": 10, "es_hoy": false, "prestamos": [...] }
      ]
    }
  ],
  "vencidosAntes": [ /* préstamos vencidos de semanas anteriores */ ],
  "listaRecuperacion": [ /* préstamos > 120 días para remate */ ],
  "kpis": { "total_por_cobrar": 15000 },
  "contadores": { "verde": 10, "amarillo": 5, "rojo": 3, "azul": 1, "remate": 2 },
  "fechaHoy": "10/01/2026"
}
```

### 4.4 Estadísticas (`EstadisticasController`)

Gráficos y KPIs:
- **GraficoResumen** — Resumen general mensual
- **GraficoPrestamos** — Cantidad de préstamos por día (barras)
- **GraficoCapital** — Evolución del capital prestado vs recuperado
- **GraficoRentabilidad** — Rentabilidad por período
- **ProyeccionIngresos** — Proyección de ingresos futuros
- **RankingList** — Top clientes/artículos
- **TablaCaja** — Movimientos de caja chica en el período

---

## 5. Utilidades y Composables

### 5.1 `useFormatters.js` (Composable global)

| Función | Descripción |
|---------|-------------|
| `formatCurrency(value)` | Formatea a moneda BOB (ej: `1,234.00`) |
| `formatMonto(valor)` | Formatea sin decimales (ej: `1,234`) |
| `formatDate(date, mask)` | Formatea fecha con dayjs (default: `DD/MM/YYYY`) |
| `formatTimeLabel(dias, meses)` | Etiqueta legible: `"1 año, 2 meses"` |
| `urgencyColor(dias)` | Clases CSS según urgencia |
| `dayjs` | Instancia de dayjs configurada con locale `es` + plugin `utc` |

### 5.2 `imageCompressor.js` (Utils)

- Comprime imágenes antes de subir (para fotos de artículos)
- Usa Canvas API del navegador
- Retorna un `File` comprimido (formato JPEG, calidad reducida)
- Usado en formulario de creación de préstamo

---

## 6. Backup de Base de Datos

- Ruta exclusiva para admin: `GET /backup/create`
- Genera y descarga un dump SQL de la base de datos
- Los dumps se guardan en la raíz del proyecto con nombre `backup_YYYY-MM-DD_HH-MM-SS.sql`
- También existe un comando Artisan (posiblemente programado con cron)

---

## 7. Exportaciones y Reportes

| Tipo | Tecnología | Vista |
|------|------------|-------|
| **PDF Contrato préstamo** | Puppeteer (headless Chrome) | `resources/views/pdf/boleta-prestamo.blade.php` |
| **PDF Reporte Financiero** | Blade → Puppeteer | `resources/views/pdf/reporte-financiero.blade.php` |
| **Excel Reporte Financiero** | Blade export (HTML → XLS) | `resources/views/exports/reporte-financiero.blade.php` |

---

## 8. Roles y Permisos

| Rol | Acceso |
|-----|--------|
| **admin** | Todo el sistema + respaldos BD + botón de backup |
| **user** | CRUD operativos (clientes, préstamos, artículos, pagos, caja, gastos, reportes, estadísticas) |

Control en frontend con: `$page.props.auth.user.role === 'admin'`
Control en backend con: `Route::middleware(['admin'])`

---

## 9. Flujo de Creación de Préstamo

```
1. Usuario ingresa a /prestamos/create
2. Ingresa:
   - Código (manual, en mayúsculas)
   - Cliente (búsqueda con autocomplete)
   - Monto a prestar
   - Fecha de inicio
   - Multa diaria (opcional)
3. Agrega artículos (prendas):
   - Nombre (mayúsculas automáticas)
   - Descripción detallada (mayúsculas)
   - Foto (con compresión automática)
4. Submit → POST /prestamos
5. Backend:
   - Valida datos
   - Crea Préstamo
   - Crea Artículos asociados
   - Sube fotos a storage
6. Redirecciona a /prestamos/index
```

---

## 10. Dependencias Clave

| Frontend | Backend (Laravel) |
|----------|-------------------|
| Vue 3 (Composition API) | Laravel 11.x |
| Inertia.js v2 | Eloquent ORM |
| Tailwind CSS v3 | MySQL |
| Heroicons v2 | Ziggy (rutas) |
| Chart.js | Puppeteer (PDF) |
| Dayjs (locales: es) | Breeze (auth) |
| Axios | Spatie Backup? |
| html2canvas | |

---

## 11. Eventos y Ciclo de Vida de Componentes

### Dashboard
- `onMounted`: Carga tema y estado sidebar desde localStorage
- `watch(flashSuccess/Error)`: Muestra toast por 5s
- `router.get` con `preserveState` y `preserveScroll` para filtros

### Buscador Global
- `watch(q)`: Debounce 300ms + AbortController para cancelar requests previos
- `onMounted/addEventListener('keydown')`: Atajo Ctrl+K
- `onUnmounted/removeEventListener`: Cleanup
- Manejo de `axios.isCancel` para ignorar respuestas canceladas

### Tablero de Cobranza
- `onMounted`: Carga datos de `/api/vencimientos`
- `Teleport to="body"`: Renderiza fuera del flujo normal
- Animación `slide-up` en CSS keyframes

### Modal (nativo `<dialog>`)
- `watch(show)`: Abre/cierra el dialog nativo, controla overflow del body
- `onMounted`: Escucha tecla Escape
- `onUnmounted`: Limpia event listener + restaura overflow
