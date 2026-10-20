# Especificación Técnica de UI/UX, Sistema de Diseño Industrial y Estrategia SEO/Analítica (GA4)
**Proyecto:** Portal Corporativo, Catálogo Técnico y Sistema de Cotización — **Import Hns San Luis**  
**Rol:** Diseñador UI/UX Senior & Especialista en SEO Técnico / Analítica Digital  
**Mercado Objetivo:** Sector Ferretero, Industrial, Construcción, Carpintería y Cerrajería en Perú (B2B y B2C)  
**Estándar de Accesibilidad:** WCAG 2.1 Nivel AA  

---

## 1. Fase 1: Investigación de Referencias del Sector Ferretero/Industrial en Perú

Se inspeccionaron en vivo las tres referencias clave del mercado peruano solicitadas (`martin.com.pe`, `carpintec.com.pe` y `promart.pe/ferreteria/accesorios-para-muebles`) para extraer patrones funcionales probados de navegación técnica, presentación de producto y conversión comercial B2B/B2C, sin copiar logotipos, textos ni activos protegidos.

### 1.1 Matriz Comparativa de Referencias Analizadas

| Dimensión Analizada | Representaciones Martin (`martin.com.pe`) | Carpintec (`carpintec.com.pe`) | Promart (`promart.pe/ferreteria/...`) | Patrón Óptimo para **Import Hns San Luis** |
| :--- | :--- | :--- | :--- | :--- |
| **Enfoque de Negocio** | Catálogo técnico B2B/B2C (`is_catalog: true`, precios ocultos por defecto, cotización por asesor/sede). | E-commerce ferretero especializado B2C/B2B con precios visibles, descuentos y compra rápida. | Homecenter masivo + Venta Empresa (`Club Pro`), alta densidad de filtros técnicos. | **Modelo Híbrido Configurable:** Soporte nativo para mostrar u ocultar precios (global o por producto), priorizando la **Solicitud de Cotización** (formal y vía WhatsApp) tanto al por mayor (RUC) como al por menor (DNI). |
| **Navegación y Jerarquía** | Mega-menú vertical desplegable de 3 niveles (Rubro → Familia → Mecanismo/Tipo) + menú horizontal corporativo. | Árbol de colecciones de 3 niveles + grillas visuales de subcategorías con conteo de ítems `(254)`. | Menú departamental profundo + migas de pan (`Breadcrumb`) visibles sobre el título H1. | **Doble Entrada al Catálogo:** Mega-menú técnico de Categorías (3 niveles) en barra azul `#1E3A8A` + cinta de Subcategorías visuales con contador de productos en vistas de categoría. |
| **Presentación de SKU, Medidas y Unidad** | Título incluye medidas exactas (`1.22 X 2.44 09.5 mm`). Código SKU estructurado por prefijos (`MAD.AG.000163`, `FER.ET.000098`). | Título con medida en pulgadas/mm (`17"`), marca clicable, soporte para precio unitario por medida y badge de stock (`En stock`). | Especificaciones normalizadas en facetas: resistencia (`100 kg`), material (`ABS`, `Acero`), acabado (`Mate`, `Zincado`), tipo de fijación. | **Ficha y Card de Precisión Técnica:** SKU monoespaciado visible en tarjeta y ficha, título con dimensión principal, chips de atributo rápido (Material, Medida, Unidad de Venta: `Und`, `Par`, `Caja`, `Millar`, `Plancha`). |
| **Buscador Técnico** | Buscador superior con filtro por categoría padre e hijo (`sprsearch-form`) y autocompletado AJAX. | Buscador predictivo instantáneo por nombre, herramienta, marca o medida. | Buscador central de alta prominencia en el header con botón de limpieza rápida y sugerencias dinámicas. | **Buscador Técnico Central:** Ubicado en el centro del Header con búsqueda simultánea por **Código SKU exacto/parcial, nombre técnico, medida (`3/4"`, `18mm`), marca y categoría**, con atajo de teclado (`/`) y sugerencias rápidas. |
| **Filtros Laterales** | Navegación lateral por subcategorías y carrusel de marcas industriales (`3M`, `Bosch`, `Cantol`, `DeWalt`). | Sidebar acordeón (`productgrid--sidebar`) con checkboxes de Disponibilidad, Tipo de producto, Material y Marca con conteo `(N)`. | Filtrado multifaceta (`Refinar Resultado`) por Marca, Color, Acabado, Material, Resistencia, Uso (`Comercial`/`Industrial`). | **Sidebar Facetado Combinable (Desktop) + Drawer de Filtros (Móvil):** Filtros por Categoría, Marca, Disponibilidad, Unidad de Venta y Atributos Técnicos dinámicos con chips activos y botón "Limpiar todo". |
| **Conversión y WhatsApp** | Widget flotante multi-asesor por sede + cintillo superior e inferior con teléfonos y correos corporativos. | Botones de acción directa en cada tarjeta (`Tienda rápida` + CTA primario) sin salir del listado. | Separación clara entre cliente final y `Venta Empresa`, selector de ubicación geográfica (Departamento/Provincia/Distrito). | **Conversión B2B/B2C sin Fricción:** Botón `"Cotizar"` en `#DC2626` en cada tarjeta (añade al Drawer de cotización sin recargar) + Botón de **WhatsApp Contextual** que envía el SKU y cantidad exacta + Formulario de cotización con soporte RUC/DNI. |

### 1.2 Hallazgos Clave del Comportamiento del Comprador Ferretero en Perú
1. **Búsqueda por código o medida antes que por nombre genérico:** El maestro carpintero, contratista o jefe de compras busca `"bisagra cangrejo 35mm"`, `"corredera telescopica 45cm pesada"`, `"disco corte 4 1/2"` o directamente el SKU interno del catálogo.
2. **Importancia crítica de la Unidad de Venta y Empaque:** En ferretería e importación industrial es indispensable distinguir si el producto se cotiza por **Unidad (`UND`), Par (`PAR`), Caja (`CAJA x 100`), Millar (`MLL`), Rollo (`RLL`), Plancha (`PLN`) o Juego (`JGO`)**.
3. **Doble flujo de cotización (Formal vs. Inmediata):**
   - **Comprador Corporativo / B2B (Constructora, Fábrica de Muebles, Ferretería Minorista):** Requiere cotización formal con **RUC (11 dígitos)**, Razón Social, lista de múltiples SKUs con cantidades por volumen y descarga de **Ficha Técnica PDF**.
   - **Comprador Técnico / Maestro de Obra / B2C:** Prefiere validación inmediata de stock y precio por **WhatsApp** enviando el código SKU y la medida desde su celular en obra.

---

## 2. Fase 2A: Sistema de Diseño Industrial y Comercial Accesible (WCAG 2.1 AA)

La identidad visual de **Import Hns San Luis** proyecta **solidez de importador directo, precisión técnica ferretera y agilidad comercial**, utilizando la cuatricromía corporativa definida (**Rojo, Azul, Blanco y Negro/Gris Pizarra**) bajo estrictos criterios de contraste WCAG 2.1 Nivel AA.

### 2.1 Paleta de Colores HEX y Tokens Semánticos

#### A. Rojo Comercial (CTAs Principales, Cotización, Urgencia y Acentos de Marca)
| Token CSS / Variable | Código HEX | Equivalente Tailwind | Uso Específico en la Interfaz | Contraste con Texto Blanco (`#FFFFFF`) |
| :--- | :--- | :--- | :--- | :--- |
| `--color-brand-red` | `#DC2626` | `red-600` | Botón primario `"Solicitar Cotización"`, `"Agregar a Cotización"`, contador de ítems en header. | **4.83 : 1** (Pasa WCAG AA normal y grande) |
| `--color-brand-red-hover` | `#B91C1C` | `red-700` | Estado `:hover` y `:active` de botones primarios rojos; enlaces de énfasis comercial. | **6.47 : 1** (Pasa WCAG AA / AAA grande) |
| `--color-brand-red-dark` | `#991B1B` | `red-800` | Texto rojo sobre fondos claros (`#FEF2F2` o `#FFFFFF`), badges de descuento/oferta. | **8.39 : 1** (Pasa WCAG AAA) |
| `--color-brand-red-soft` | `#FEF2F2` | `red-50` | Fondo suave para alertas comerciales, badges de promoción o resaltado de ítem activo. | Con texto `#991B1B`: **7.82 : 1** (AAA) |
| `--color-brand-red-border`| `#FECACA` | `red-200` | Bordes de contenedores de oferta o llamadas a la acción secundarias en rojo. | Decorativo / Contorno |

#### B. Azul Corporativo y Técnico (Confianza, Navegación, Estructura, Fichas Técnicas)
| Token CSS / Variable | Código HEX | Equivalente Tailwind | Uso Específico en la Interfaz | Contraste con Texto Blanco (`#FFFFFF`) |
| :--- | :--- | :--- | :--- | :--- |
| `--color-brand-blue` | `#1E3A8A` | `blue-900` | Barra de navegación principal, cabeceras de bloques técnicos, encabezados de tabla, botones secundarios B2B. | **10.96 : 1** (Pasa WCAG AAA) |
| `--color-brand-blue-mid` | `#1D4ED8` | `blue-700` | Enlaces interactivos, botones de descarga de Ficha PDF, estados `:focus-visible`, filtros activos. | **6.70 : 1** (Pasa WCAG AA) |
| `--color-brand-blue-hover`| `#1E40AF` | `blue-800` | Estado `:hover` en botones técnicos azules y enlaces sobre fondo blanco. | **8.72 : 1** (Pasa WCAG AAA) |
| `--color-brand-blue-soft` | `#EFF6FF` | `blue-50` | Fondo de chips de categoría/marca, banners informativos B2B y cabeceras de tabla técnica. | Con texto `#1E3A8A`: **9.98 : 1** (AAA) |
| `--color-brand-blue-border`| `#BFDBFE` | `blue-200` | Bordes de cajas de especificaciones técnicas, buscador enfocado y tarjetas destacadas. | Decorativo / Contorno |

#### C. Blanco y Grises Claros (Fondos Limpios, Legibilidad de Catálogo y Tablas)
| Token CSS / Variable | Código HEX | Equivalente Tailwind | Uso Específico en la Interfaz |
| :--- | :--- | :--- | :--- |
| `--color-surface-white` | `#FFFFFF` | `white` | Fondo principal de tarjetas de producto, contenedores de imágenes `1:1`, modales y tablas base. |
| `--color-surface-canvas` | `#F8FAFC` | `slate-50` | Fondo general de páginas de catálogo, filas pares (zebra) en tablas de especificaciones, secciones alternas. |
| `--color-surface-subtle` | `#F1F5F9` | `slate-100` | Fondo de barra de búsqueda inactiva, chips de unidades de venta (`UND`, `CAJA`), bloques de código SKU. |
| `--color-border-default` | `#E2E8F0` | `slate-200` | Bordes estructurales de tarjetas de producto, celdas de tabla y separadores de sección. |
| `--color-border-strong` | `#CBD5E1` | `slate-300` | Bordes de inputs de formulario (`<input>`, `<select>`, `<textarea>`) con ratio suficiente para accesibilidad visual. |

#### D. Negro y Gris Pizarra (Textos de Alto Contraste, Topbar Industrial y Footer)
| Token CSS / Variable | Código HEX | Equivalente Tailwind | Uso Específico en la Interfaz | Contraste sobre `#FFFFFF` / `#F8FAFC` |
| :--- | :--- | :--- | :--- | :--- |
| `--color-ink-primary` | `#0F172A` | `slate-900` | Texto principal, títulos H1-H4, precios, valores de tabla técnica, fondo de Topbar y Footer industrial. | **17.85 : 1** sobre `#FFFFFF` (AAA) |
| `--color-ink-dark-surface`| `#1E293B` | `slate-800` | Superficie secundaria en bloques oscuros (Footer inferior, tarjetas corporativas oscuras, tooltips). | **13.56 : 1** con texto `#FFFFFF` (AAA) |
| `--color-ink-secondary` | `#334155` | `slate-700` | Texto de cuerpo secundario, descripciones de producto, etiquetas (`<label>`) de formularios y filtros. | **10.35 : 1** sobre `#FFFFFF` (AAA) |
| `--color-ink-muted` | `#475569` | `slate-600` | Metadatos técnicos, código SKU, migas de pan inactivas, textos de ayuda (`helper text`). | **7.58 : 1** sobre `#FFFFFF` (AAA) |

#### E. Estados Semánticos (Disponibilidad de Stock, Validaciones y WhatsApp)
| Estado / Función | Color Principal (Texto/Icono) | Fondo de Badge / Alerta | Borde | Ratio de Contraste | Uso en el Catálogo |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Éxito / En Stock** | `#15803D` (`green-700`) | `#F0FDF4` (`green-50`) | `#BBF7D0` (`green-200`) | **5.02 : 1** (AA) | Indicador `"En stock disponible"`, confirmación de cotización enviada. |
| **WhatsApp Oficial** | `#FFFFFF` sobre `#15803D` (o `#0F172A` sobre `#25D366`) | Botón `#15803D` (hover `#166534`) | `#14532D` | **5.14 : 1** (AA) | Botón flotante y botón contextual de WhatsApp accesible (evita texto blanco ilegible sobre `#25D366`). |
| **Advertencia / Bajo Pedido**| `#B45309` (`amber-700`) | `#FFFBEB` (`amber-50`) | `#FDE68A` (`amber-200`) | **4.76 : 1** (AA) | Indicador `"Stock por confirmar"` o `"Cotización bajo pedido / importación"`. |
| **Error / Agotado** | `#B91C1C` (`red-700`) | `#FEF2F2` (`red-50`) | `#FECACA` (`red-200`) | **6.12 : 1** (AA) | Indicador `"Sin stock temporal"`, mensajes de error de validación en formularios. |

---

### 2.2 Tipografía Técnica Legible y Jerarquía de Tamaños

Para un catálogo ferretero/industrial donde el usuario compara códigos, milímetros, pulgadas y capacidades de carga, la tipografía debe garantizar **legibilidad instantánea de caracteres alfanuméricos** (distinción clara entre `1`, `l`, `I` y `0`, `O`) y alineación numérica vertical.

#### Familias Tipográficas Definidas
1. **Tipografía de Títulos e Impacto Industrial (`--font-display`):**  
   `Plus Jakarta Sans` o `Inter` (pesos `700 Bold`, `800 ExtraBold`). Proporciona solidez geométrica moderna, limpia y corporativa sin perder ancho de lectura en pantallas móviles.
2. **Tipografía de Cuerpo y UI (`--font-sans`):**  
   `Inter`, `system-ui`, `-apple-system`, `sans-serif` (pesos `400 Regular`, `500 Medium`, `600 SemiBold`). Optimizada para interfaces densas en datos, filtros laterales y tablas técnicas.
3. **Tipografía Monoespaciada y Tabular para Datos Técnicos (`--font-mono`):**  
   `JetBrains Mono`, `ui-monospace`, `SFMono-Regular`, `monospace` (pesos `500 Medium`, `600 SemiBold`). Obligatoria para **Códigos SKU (`FER-BIS-035`), RUC, números de serie y dimensiones técnicas**, acompañada de `font-variant-numeric: tabular-nums` en precios y cantidades.

#### Escala Tipográfica Responsiva
| Nivel / Elemento | Tamaño Desktop (`>=1024px`) | Tamaño Mobile (`<768px`) | Peso / Line-Height | Reglas Aplicadas |
| :--- | :--- | :--- | :--- | :--- |
| **Display Hero** | `2.75rem` (`44px`) | `2.0rem` (`32px`) | `800` / `1.15` | `letter-spacing: -0.02em`, color `#FFFFFF` (sobre hero oscuro) o `#0F172A`. |
| **H1 (Título de Página / Producto)** | `2.0rem` (`32px`) | `1.5rem` (`24px`) | `700` / `1.2` | Único por página. En ficha de producto incluye nombre + medida clave. |
| **H2 (Encabezado de Sección)** | `1.5rem` (`24px`) | `1.25rem` (`20px`) | `700` / `1.25` | Con barra lateral de acento industrial (`border-left: 4px solid #DC2626`). |
| **H3 (Subsección / Categoría)** | `1.25rem` (`20px`) | `1.125rem` (`18px`) | `600` / `1.3` | Color `#0F172A` o `#1E3A8A`. |
| **Título de Tarjeta de Producto** | `0.9375rem` (`15px`) | `0.875rem` (`14px`) | `600` / `1.35` | `line-clamp-2`, altura mínima reservada (`2.7em`) para alineación perfecta de grilla. |
| **Body Base (Descripciones)** | `1.0rem` (`16px`) | `0.9375rem` (`15px`) | `400` / `1.6` | Color `#334155`, ancho máximo de lectura `65ch-75ch`. |
| **Body Técnico (Tablas / Filtros)**| `0.875rem` (`14px`) | `0.875rem` (`14px`) | `500` / `1.45` | `font-variant-numeric: tabular-nums` para medidas y conteos. |
| **Código SKU / Badge Técnico** | `0.75rem` (`12px`) | `0.75rem` (`12px`) | `600` / `1.2` | `font-mono`, `uppercase`, `letter-spacing: 0.04em`. |
| **Precio Principal** | `1.375rem` (`22px`) | `1.25rem` (`20px`) | `700` / `1.2` | `tabular-nums`, color `#0F172A` (o `#DC2626` en oferta). |

---

### 2.3 Especificación de Componentes Blade Reutilizables

Todos los componentes se diseñan bajo arquitectura modular en `resources/views/components/` utilizando **Tailwind CSS + Alpine.js** (ligero, sin dependencias pesadas de SPA, ideal para SEO server-side rendering y Core Web Vitals).

#### 1. `<x-header />` (`resources/views/components/header.blade.php`)
- **Estructura en 3 niveles:**
  1. **Topbar Industrial (`bg-[#0F172A] text-slate-200 text-xs py-2`):**
     - Izquierda: Especialistas en Importación y Distribución Ferretera en Perú | Ventas al por Mayor (RUC) y Menor.
     - Derecha: Teléfono clicable (`tel:`), Correo (`mailto:`), Horario de atención y enlace directo a `"Seguimiento / Cotización Corporativa"`.
  2. **Header Principal (`bg-white border-b border-slate-200 py-3.5 sticky top-0 z-40 shadow-sm`):**
     - **Logo Institucional:** Isotipado industrial en Rojo `#DC2626` y Azul `#1E3A8A` con texto **IMPORT HNS SAN LUIS**.
     - **Buscador Técnico Central (`form action="{{ route('catalog.index') }}" method="GET"`):**
       - Selector integrado de Categoría (`Todas las categorías` + categorías principales).
       - Input con placeholder explícito: `"Buscar por producto, código SKU (ej. FER-001), medida o marca..."`.
       - Botón de acción en Azul `#1E3A8A` (hover `#1D4ED8`) con icono de lupa y etiqueta accesible.
     - **Acciones Rápidas Derecha:**
       - Botón **"Cotizador Rápido"** que abre el `<x-quote-drawer />` o lleva a `/cotizar`, mostrando un **contador reactivo en `#DC2626`** con el número total de ítems agregados a la solicitud de cotización (`x-text="quoteCount"` sincronizado con `localStorage` / sesión).
  3. **Barra de Navegación Técnica (`bg-[#1E3A8A] text-white`):**
     - Botón desplegable **"CATEGORÍAS TÉCNICAS"** con fondo `#DC2626` que despliega el Mega-menú de rubros y subcategorías.
     - Enlaces directos: `Inicio`, `Catálogo Completo`, `Marcas`, `Fichas Técnicas`, `Nosotros`, `Contacto`.

#### 2. `<x-product-card :product="$product" />` (`resources/views/components/product-card.blade.php`)
- **Props recibidos:** `$product` (modelo con relaciones `category`, `brand`, `specifications`), `$showPrice` (booleano evaluado desde configuración global y del producto).
- **Anatomía visual:**
  - **Zona Superior (Imagen + Badges):** Contenedor `aspect-square bg-white p-4 relative group border-b border-slate-100`.
    - Badges en esquina superior izquierda: `Destacado` (`#1E3A8A`), `Oferta` (`#DC2626`), o `Ficha PDF` (icono documento en `#1D4ED8` si posee `technical_sheet_path`).
    - Badge de Disponibilidad en esquina superior derecha: punto verde `#15803D` (`En stock`) o ámbar `#B45309` (`Bajo pedido`).
  - **Zona de Identificación Técnica:**
    - Fila superior: **Marca** en texto azul `#1D4ED8` (`text-xs font-semibold uppercase`) + **Código SKU** en caja monoespaciada (`bg-slate-100 text-slate-700 font-mono text-[11px] px-1.5 py-0.5 rounded`).
    - **Título del Producto:** Enlace semántico `<h3>` hacia `route('catalog.show', $product->slug)` con `line-clamp-2 font-semibold text-[#0F172A] group-hover:text-[#1D4ED8]`.
  - **Zona de Especificaciones Rápidas (Chips):**
    - Muestra hasta 2 atributos clave (ej. `Medida: 35mm`, `Acabado: Niquelado`) + **Unidad de Venta** (`Unidad: CAJA x 100` o `UND`).
  - **Zona de Precio / Modo Catálogo (Configurable):**
    - Si `$product->shouldShowPrice()` es `true`: muestra `S/ {{ number_format($product->price, 2) }}` en `#0F172A` `font-bold text-lg tabular-nums` + etiqueta `Inc. IGV / por {{ $product->unit_of_measure }}`.
    - Si `$product->shouldShowPrice()` es `false`: muestra bloque técnico `"Cotización por volumen y unidad"` en `#1E3A8A` sobre fondo `#EFF6FF`.
  - **Zona de Conversión (Doble CTA):**
    - Botón Primario (`bg-[#DC2626] hover:bg-[#B91C1C] text-white font-semibold py-2 px-3 rounded text-sm flex-1`): **"Cotizar"** (añade al carrito de cotización y dispara evento GA4 `begin_quote`).
    - Botón Contextual Secundario (`bg-[#15803D] hover:bg-[#166534] text-white p-2 rounded` con icono WhatsApp): Abre consulta directa de WhatsApp con el SKU y nombre del producto, disparando evento GA4 `whatsapp_click`.

#### 3. `<x-catalog-filters />` (`resources/views/components/catalog-filters.blade.php`)
- **Diseño Responsivo:** En Desktop (`lg:block lg:w-72 shrink-0`) funciona como panel fijo lateral con borde `#E2E8F0`; en Móvil se activa mediante barra superior fija `"Filtrar y Ordenar (N)"` que abre un Drawer lateral accesible (`role="dialog"`, `aria-modal="true"`).
- **Bloques de Filtro Acordeón:**
  1. **Búsqueda dentro de resultados** (input rápido por texto/SKU).
  2. **Categorías y Subcategorías** (árbol jerárquico con contador de productos activos).
  3. **Marca** (lista de checkboxes o selector con contador).
  4. **Disponibilidad** (`En Stock`, `Bajo Pedido`, `Con Ficha Técnica PDF`).
  5. **Unidad de Venta** (`Unidad`, `Par`, `Caja`, `Millar`, `Plancha`, `Juego`).
  6. **Especificaciones Técnicas Clave** (filtro por atributo/valor: ej. Medida, Material, Acabado).
- **Barra de Filtros Activos (Pills):** Muestra en la parte superior de la grilla cada filtro aplicado (`Marca: Ducasse [×]`, `Categoría: Bisagras [×]`) + enlace `"Limpiar todos los filtros"`. Cada aplicación de filtro dispara el evento GA4 `filter_catalog`.

#### 4. `<x-tech-specs-table :product="$product" />` (`resources/views/components/tech-specs-table.blade.php`)
- **Propósito:** Presentar la información de ingeniería/ferretería con máxima claridad para compradores técnicos e ingenieros de compras.
- **Diseño:**
  - Cabecera en Azul Corporativo `#1E3A8A` con texto blanco, mostrando el **Código SKU**, **Marca** y **Unidad de Empaque**.
  - Tabla HTML semántica (`<table>`, `<tbody>`, `<tr>`, `<th scope="row">`, `<td>`) con filas cebreadas (`odd:bg-white even:bg-[#F8FAFC]`), bordes `#E2E8F0`.
  - Primera columna (`<th>`): `w-2/5 text-[#334155] font-semibold text-sm py-3 px-4 bg-slate-50/60`.
  - Segunda columna (`<td>`): `w-3/5 text-[#0F172A] font-medium text-sm py-3 px-4 tabular-nums`.
  - **Tarjeta de Descarga de Ficha Técnica PDF (si existe archivo adjunto):** Bloque destacado con fondo `#EFF6FF`, borde `#BFDBFE`, icono PDF en rojo `#DC2626`, nombre del documento y botón `"Descargar Ficha Técnica (PDF)"` que dispara el evento GA4 `file_download`.

#### 5. `<x-quote-drawer />` y `<x-quote-modal />` (`resources/views/components/quote-drawer.blade.php`)
- **Propósito:** Permitir que el cliente arme un pliego o lista de cotización con 1 o múltiples SKUs mientras navega por el catálogo, sin interrumpir su búsqueda.
- **Funcionalidades clave:**
  - Lista de productos seleccionados mostrando miniatura, nombre, **SKU monoespaciado**, **selector de cantidad** (`-` / `+` / input numérico con mínimo 1) y unidad de venta (`UND`, `CAJA`, etc.).
  - Selector de Tipo de Cliente: **Empresa / Negocio (RUC)** vs. **Persona Natural / Maestro (DNI)**.
  - Si elige **Empresa (B2B)**: solicita RUC (validación de 11 dígitos numéricos que inician en `10` o `20`) y Razón Social.
  - Botones de cierre de conversión:
    1. **"Enviar Solicitud Formal"** (guarda en base de datos, genera código de cotización `COT-2026-XXXX`, notifica por correo y dispara `submit_quote`).
    2. **"Enviar Lista por WhatsApp"** (formatea automáticamente todos los SKUs y cantidades en un mensaje estructurado hacia el número oficial de ventas y dispara `whatsapp_click`).

#### 6. `<x-whatsapp-button />` (`resources/views/components/whatsapp-button.blade.php`)
- **Dos modos de uso mediante prop `variant="floating" | "inline"`:**
  - **Modo Flotante (`floating`):** Fijo en `bottom-6 right-6 z-50`. Incluye etiqueta desplegable en desktop `"¿Buscas un código o medida? Cotiza por WhatsApp"`, botón circular/píldora con alto contraste `#15803D`, anillo de pulso sutil y atributos `aria-label="Contactar con un asesor de Import Hns San Luis por WhatsApp"`.
  - **Modo Contextual (`inline` en Ficha de Producto y Catálogo):** Genera dinámicamente el enlace `https://wa.me/{{ $whatsappNumber }}?text={{ urlencode($contextualMessage) }}` incluyendo:
    > *"Hola Import Hns San Luis, deseo cotizar el producto: [Nombre] | SKU: [SKU] | Unidad: [Unidad] | Cantidad: [N] | Enlace: [URL Canónica]"*

---

## 3. Fase 2B: Especificación Técnica de SEO y Analítica (GA4 + Search Console)

### 3.1 Arquitectura de Metadatos Dinámicos (`<x-seo-meta />`)

Todas las páginas públicas renderizan en el `<head>` el componente `<x-seo-meta :seo="$seoData" />`, garantizando indexación limpia en Google Search Console y previsualización enriquecida en WhatsApp, LinkedIn y Facebook.

#### Plantillas de `<title>` y `<meta name="description">` por Tipo de Vista
| Vista / Ruta | Plantilla de `<title>` (Máx. 60-65 caracteres) | Plantilla de `<meta name="description">` (Máx. 150-160 caracteres) | Regla de Canonical y Robots |
| :--- | :--- | :--- | :--- |
| **Inicio (`/`)** | `Import Hns San Luis | Catálogo Ferretero e Industrial en Perú` | `Importación y distribución de artículos de ferretería, cerrajería, accesorios para muebles y herramientas en Perú. Cotiza al por mayor y menor.` | `canonical`: `https://dominio.pe/` <br> `robots`: `index, follow` |
| **Catálogo General (`/catalogo`)** | `Catálogo Técnico de Ferretería e Industria | Import Hns San Luis` | `Explora nuestro catálogo técnico ferretero por código SKU, medidas y marcas. Fichas técnicas en PDF y cotización rápida para empresas y negocios.` | Si tiene filtros complejos (`?brand=...&q=...`): `canonical` hacia `/catalogo` y `robots`: `noindex, follow` (evita contenido duplicado / crawl budget). |
| **Categoría (`/categoria/{slug}`)** | `{Categoría}: Catálogo y Cotización en Perú | Import Hns San Luis` | `Cotiza {Categoría} al por mayor y menor en Import Hns San Luis. Revisa medidas, especificaciones técnicas, códigos SKU y solicita tu cotización.` | `canonical`: `https://dominio.pe/categoria/{slug}` <br> `robots`: `index, follow` |
| **Marca (`/marca/{slug}`)** | `Productos {Marca} en Perú: Catálogo Técnico | Import Hns San Luis` | `Distribuidor de productos {Marca} en Perú. Consulta códigos SKU, fichas técnicas y solicita cotización por volumen o unidad en Import Hns San Luis.` | `canonical`: `https://dominio.pe/marca/{slug}` <br> `robots`: `index, follow` |
| **Ficha de Producto (`/producto/{slug}`)** | `{Nombre Producto} (SKU: {SKU}) | Import Hns San Luis` | `{Resumen Técnico Corto}. Código SKU: {SKU}. Marca: {Marca}. Unidad: {Unidad}. Revisa ficha técnica y cotiza en línea o por WhatsApp.` | `canonical`: `https://dominio.pe/producto/{slug}` <br> `robots`: `index, follow` |
| **Cotizar (`/cotizar`)** | `Solicitar Cotización Ferretera B2B y B2C | Import Hns San Luis` | `Envía tu lista de productos o códigos SKU para cotización inmediata. Atención a ferreterías, constructoras, carpinterías y clientes finales en todo el Perú.` | `canonical`: `https://dominio.pe/cotizar` <br> `robots`: `index, follow` |
| **Admin (`/admin/*`)** | `{Título Sección} - Panel Admin | Import Hns San Luis` | *(Omitido)* | `robots`: `noindex, nofollow, noarchive` |

#### Etiquetas Open Graph y Twitter Cards Incluidas
```html
<meta property="og:locale" content="es_PE">
<meta property="og:site_name" content="Import Hns San Luis">
<meta property="og:type" content="{{ $ogType ?? 'website' }}"> <!-- 'product' en fichas -->
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImage }}">
```

---

### 3.2 Datos Estructurados JSON-LD (Schema.org)

Se implementan **4 esquemas JSON-LD reales y validados**, respetando estrictamente la directriz de **no inventar precios, reseñas falsas ni disponibilidad inexistente**.

#### 1. `Organization` / `HardwareStore` (Global o en Inicio/Contacto)
```json
{
  "@context": "https://schema.org",
  "@type": "HardwareStore",
  "@id": "https://sanluis.pe/#organization",
  "name": "Import Hns San Luis",
  "url": "https://sanluis.pe",
  "logo": "https://sanluis.pe/images/logo-sanluis.svg",
  "description": "Importación y comercialización de productos ferreteros, cerrajería, herramientas y suministros industriales en Perú.",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Lima",
    "addressCountry": "PE"
  },
  "areaServed": {
    "@type": "Country",
    "name": "PE"
  },
  "contactPoint": {
    "@type": "ContactPoint",
    "contactType": "sales",
    "availableLanguage": ["Spanish"],
    "areaServed": "PE"
  }
}
```

#### 2. `WebSite` con `SearchAction` (Sitelinks Searchbox en Inicio)
```json
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "@id": "https://sanluis.pe/#website",
  "url": "https://sanluis.pe",
  "name": "Import Hns San Luis",
  "inLanguage": "es-PE",
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "https://sanluis.pe/catalogo?q={search_term_string}"
    },
    "query-input": "required name=search_term_string"
  }
}
```

#### 3. `BreadcrumbList` (En Categorías, Subcategorías, Marcas y Fichas de Producto)
```json
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Inicio",
      "item": "https://sanluis.pe/"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Accesorios para Muebles",
      "item": "https://sanluis.pe/categoria/accesorios-para-muebles"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "Bisagra Cangrejo 35mm Cierre Suave",
      "item": "https://sanluis.pe/producto/bisagra-cangrejo-35mm-cierre-suave"
    }
  ]
}
```

#### 4. `Product` (En Ficha de Producto `/producto/{slug}`)
- **Regla de Integridad de Datos:**
  - Siempre incluye `name`, `sku`, `description`, `image`, `url`, `brand` y `additionalProperty` (mapa real de especificaciones técnicas de la tabla `product_specifications`).
  - El bloque `offers` (`Offer`) solo expone `"price"` y `"priceCurrency": "PEN"` **si y solo si** el producto tiene precio público activo (`$product->shouldShowPrice() === true && $product->price > 0`). Si el producto opera en modo "Solo Cotización", no se inyecta un precio ficticio `0.00` (que genera errores en Google Merchant / Search Console), sino que se mantiene el esquema `Product` técnico limpio con sus propiedades de ingeniería.
  - El campo `availability` mapea exactamente el estado real del producto en base de datos:
    - `in_stock` → `https://schema.org/InStock`
    - `on_order` / `low_stock` → `https://schema.org/PreOrder`
    - `out_of_stock` → `https://schema.org/OutOfStock`

```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Bisagra Cangrejo 35mm Cierre Suave Acero Niquelado",
  "sku": "FER-BIS-035CS",
  "mpn": "FER-BIS-035CS",
  "description": "Bisagra cangrejo de cazoleta 35mm con pistón hidráulico de cierre suave (soft closing) para puertas de melamina y madera.",
  "image": [
    "https://sanluis.pe/storage/products/bisagra-35mm.webp"
  ],
  "brand": {
    "@type": "Brand",
    "name": "San Luis Pro"
  },
  "category": "Ferretería > Accesorios para Muebles > Bisagras",
  "additionalProperty": [
    {
      "@type": "PropertyValue",
      "name": "Diámetro de cazoleta",
      "value": "35 mm"
    },
    {
      "@type": "PropertyValue",
      "name": "Material",
      "value": "Acero laminado en frío niquelado"
    },
    {
      "@type": "PropertyValue",
      "name": "Unidad de venta",
      "value": "Par / Caja x 100 pares"
    }
  ],
  "offers": {
    "@type": "Offer",
    "url": "https://sanluis.pe/producto/bisagra-cangrejo-35mm-cierre-suave",
    "priceCurrency": "PEN",
    "price": "6.50",
    "availability": "https://schema.org/InStock",
    "itemCondition": "https://schema.org/NewCondition"
  }
}
```

---

### 3.3 Sitemap XML Dinámico y `robots.txt`

#### Especificación de `/sitemap.xml`
- Generado dinámicamente por controlador dedicado (`SitemapController@index`) con cabecera `Content-Type: application/xml; charset=utf-8` y caché invalidable al crear/editar productos o categorías.
- Incluye únicamente URLs canónicas públicas con código HTTP `200`:
  - Páginas institucionales (`/`, `/catalogo`, `/nosotros`, `/contacto`, `/cotizar`) — `priority: 1.0 / 0.8`.
  - Categorías y subcategorías activas (`is_active = true`) — `changefreq: weekly`, `priority: 0.8`.
  - Marcas activas (`is_active = true`) — `changefreq: weekly`, `priority: 0.7`.
  - Productos activos (`is_active = true`) con su `updated_at` en formato W3C (`toAtomString()`) — `changefreq: weekly`, `priority: 0.9`.

#### Especificación de `/robots.txt`
```txt
User-agent: *
Allow: /
Disallow: /admin
Disallow: /admin/
Disallow: /login
Disallow: /logout
Disallow: /*?sort=
Disallow: /*&sort=

Sitemap: https://sanluis.pe/sitemap.xml
```

---

### 3.4 Plan de Medición de Eventos en Google Analytics 4 (GA4) — Cero PII

#### Regla Estricta de Privacidad y Cumplimiento (Zero-PII Policy)
Bajo ninguna circunstancia se enviarán a `gtag('event', ...)` ni a `window.dataLayer` datos de identificación personal (**PII**):
- **PROHIBIDO enviar:** Nombres del cliente, Razón Social, RUC, DNI, correo electrónico, número de teléfono/WhatsApp, dirección física o contenido libre de mensajes de contacto.
- **PERMITIDO enviar:** Identificadores técnicos del catálogo (`sku`, `product_id`, `category_slug`, `brand_slug`), métricas agregadas (`items_count`, `quantity`), tipo de cliente anónimo (`client_type: 'empresa_ruc' | 'persona_dni'`), ubicación general de UI (`cta_location: 'product_card' | 'product_detail' | 'floating_button' | 'quote_drawer'`).

#### Diccionario Técnico de los 8 Eventos GA4 Requeridos

| # | Nombre del Evento GA4 | Disparador (Trigger en la Interfaz) | Parámetros Exactos Enviados (JSON Payload) | Valor Analítico para el Negocio |
| :--- | :--- | :--- | :--- | :--- |
| **1** | `search` | Al enviar el formulario del buscador técnico del header o buscador interno del catálogo con un término no vacío. | `{ search_term: string, category_scope: string \| 'all', results_count: number }` *(Nota: se sanitiza `search_term` si parece email/teléfono).* | Identificar qué códigos SKU, medidas o productos buscan los clientes y qué búsquedas dan `0` resultados para ampliar stock. |
| **2** | `view_item` | Al cargar la vista de detalle de un producto (`/producto/{slug}`). | `{ currency: 'PEN', value: number \| null, items: [{ item_id: string (SKU), item_name: string, item_category: string, item_brand: string, item_variant: string (unidad), price: number \| null }] }` | Medir los productos e importaciones con mayor interés técnico en el catálogo. |
| **3** | `filter_catalog` | Al aplicar o combinar filtros en el catálogo lateral o móvil (categoría, marca, disponibilidad, unidad, orden). | `{ filter_category: string \| null, filter_brand: string \| null, filter_availability: string \| null, filter_unit: string \| null, sort_by: string, results_count: number }` | Entender qué combinaciones de marcas, medidas y disponibilidad utilizan más los compradores. |
| **4** | `file_download` | Al hacer clic en el botón de descarga de una Ficha Técnica PDF o catálogo descargable. | `{ file_name: string, file_extension: 'pdf', link_text: string, item_id: string (SKU), item_name: string, item_category: string }` | Medir la intención de compra técnica/ingenieril (quienes descargan fichas PDF suelen ser compradores B2B). |
| **5** | `begin_quote` | Al hacer clic en `"Agregar a cotización"` / `"Cotizar"` desde una tarjeta de producto, ficha técnica o al abrir el formulario con ítems. | `{ cta_location: 'product_card' \| 'product_detail' \| 'header_drawer', item_id: string (SKU), item_name: string, item_category: string, item_brand: string, unit_of_measure: string, quantity: number, quote_items_total: number }` | Medir la tasa de adición al pliego de cotización por producto y ubicación del CTA. |
| **6** | `submit_quote` | Al completar exitosamente el envío formal de una solicitud de cotización (confirmada por el servidor). | `{ quote_reference: string (ej. 'COT-2026-0001'), client_type: 'b2b_ruc' \| 'b2c_dni', department: string, items_count: number, total_units: number, has_estimated_value: boolean, estimated_value: number \| null, currency: 'PEN' }` **(Sin nombre, RUC, email ni teléfono)**. | Conversión principal (Macro-conversión) del portal: volumen de cotizaciones B2B vs B2C y regiones con mayor demanda. |
| **7** | `whatsapp_click` | Al hacer clic en el botón flotante de WhatsApp, en el botón de WhatsApp de una tarjeta/ficha de producto o en "Enviar cotización por WhatsApp". | `{ cta_location: 'floating_widget' \| 'product_card' \| 'product_detail' \| 'quote_drawer' \| 'contact_page', context_type: 'general' \| 'single_product' \| 'quote_list', item_id: string \| null (SKU), item_name: string \| null, items_count: number }` | Segunda conversión principal: medir cuántos prospectos prefieren cierre inmediato por WhatsApp y desde qué SKU exacto. |
| **8** | `contact_form_submit` | Al enviar exitosamente el formulario institucional de Contacto (`/contacto`). | `{ inquiry_type: 'ventas_mayor' \| 'cotizacion_especial' \| 'distribuidor' \| 'postventa' \| 'general', client_type: 'b2b_ruc' \| 'b2c_dni', department: string }` **(Sin PII)**. | Medir leads institucionales y solicitudes de distribución mayorista. |

#### Implementación Técnica del Helper Analítico (`window.SanLuisAnalytics`)
Se implementará un módulo JS ligero y resiliente en el layout principal que:
1. Verifica si `window.gtag` está activo (solo cuando el administrador ha configurado un `GA4_MEASUREMENT_ID` válido tipo `G-XXXXXXXXXX`).
2. Expone un método seguro `SanLuisAnalytics.track(eventName, params)` que filtra automáticamente cualquier clave prohibida (`name`, `email`, `phone`, `ruc`, `dni`, `message`, `company_name`) antes de enviarla a `gtag('event', eventName, cleanParams)`.
3. Permite declaratividad en componentes Blade mediante atributos `data-ga-event="..."` y `data-ga-params='{"item_id":"..."}'`, evitando acoplar lógica pesada en cada vista.
