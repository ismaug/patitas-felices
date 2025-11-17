# Guía de Diseño UX/UI - Patitas Felices

_Versión 3.0 · Material Design 3 · HTML + CSS + Inter · Sistema de Diseño Centralizado_

---

## Índice

- [Introducción](#introducción)
- [Sistema de Diseño Material Design 3](#sistema-de-diseño-material-design-3)
- [Identidad Visual](#identidad-visual)
- [Tipografía](#tipografía)
- [Componentes Reutilizables](#componentes-reutilizables)
- [Patrones UX](#patrones-ux)
- [Accesibilidad](#accesibilidad)
- [Animaciones y Transiciones](#animaciones-y-transiciones)
- [Guía de Implementación](#guía-de-implementación)
- [Ejemplos de Uso](#ejemplos-de-uso)

---

## Introducción

Esta guía define el sistema de diseño de Patitas Felices basado en **Material Design 3**, el lenguaje de diseño moderno de Google. Nuestro objetivo es crear interfaces consistentes, accesibles y hermosas que faciliten la conexión entre humanos y animales en busca de hogar.

### Principios de Diseño

1. **Consistencia**: Componentes reutilizables con comportamiento predecible
2. **Accesibilidad**: Diseño inclusivo que cumple con WCAG 2.1 AA
3. **Claridad**: Jerarquía visual clara y navegación intuitiva
4. **Calidez**: Paleta de colores que transmite confianza y alegría
5. **Modernidad**: Implementación de las últimas tendencias de Material Design 3

---

## Sistema de Diseño Material Design 3

### Archivo Central

Todos los estilos del sistema están centralizados en:
```
public/css/material-design.css
```

Este archivo incluye:
- Variables CSS para colores, espaciado y tipografía
- Componentes reutilizables (botones, inputs, cards)
- Sistema de elevación y superficies
- Animaciones y transiciones
- Utilidades de diseño

### Cómo Usar

Incluye el archivo CSS en tu HTML:

```html
<link rel="stylesheet" href="/css/material-design.css">
```

---

## Identidad Visual

### Paleta de Colores

Nuestra paleta mantiene los colores originales del proyecto, organizados según Material Design 3:

#### Colores Principales

| Rol | Color | HEX | Uso |
|-----|-------|-----|-----|
| **Primary** | Azul profundo | `#0D3B66` | Acciones principales, navegación |
| **Secondary** | Naranja suave | `#EE964B` | Acciones secundarias, énfasis |
| **Tertiary** | Amarillo | `#F4D35E` | Acentos, destacados |
| **Accent** | Naranja vivo | `#F95738` | Llamadas a la acción críticas |
| **Background** | Crema | `#FAF0CA` | Fondo general de la aplicación |

#### Colores de Estado

| Estado | Color | HEX | Uso |
|--------|-------|-----|-----|
| **Success** | Verde | `#4CAF50` | Confirmaciones, éxito |
| **Error** | Rojo | `#D32F2F` | Errores, alertas críticas |
| **Warning** | Amarillo | `#FFC107` | Advertencias, precauciones |
| **Info** | Azul | `#0288D1` | Información, ayuda |

#### Variables CSS

```css
/* Colores principales */
--md-primary: #0D3B66;
--md-secondary: #EE964B;
--md-tertiary: #F4D35E;
--md-accent: #F95738;
--md-background: #FAF0CA;

/* Colores de estado */
--md-success: #4CAF50;
--md-error: #D32F2F;
--md-warning: #FFC107;
--md-info: #0288D1;
```

### Sistema de Elevación

Material Design 3 usa sombras para crear jerarquía visual:

| Nivel | Clase | Uso |
|-------|-------|-----|
| 0 | `md-elevation-0` | Sin elevación |
| 1 | `md-elevation-1` | Cards en reposo |
| 2 | `md-elevation-2` | Cards elevadas, botones |
| 3 | `md-elevation-3` | Modales, menús |
| 4 | `md-elevation-4` | Navegación flotante |
| 5 | `md-elevation-5` | Diálogos importantes |

### Superficies

```css
.md-surface              /* Superficie básica blanca */
.md-surface-variant      /* Superficie con tinte gris */
.md-surface-container    /* Contenedor con elevación media */
```

---

## Tipografía

### Familia Tipográfica

**Inter** - Fuente humanista moderna, optimizada para legibilidad en pantallas.

```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
```

### Escala Tipográfica Material Design 3

#### Display (Títulos Grandes)

```css
.md-display-large   /* 56px - Títulos hero */
.md-display-medium  /* 45px - Títulos de sección */
.md-display-small   /* 36px - Subtítulos principales */
```

#### Headline (Encabezados)

```css
.md-headline-large  /* 32px - Títulos de página */
.md-headline-medium /* 28px - Títulos de sección */
.md-headline-small  /* 24px - Subtítulos */
```

#### Title (Títulos de Componentes)

```css
.md-title-large     /* 22px - Títulos de cards */
.md-title-medium    /* 16px - Títulos de listas */
.md-title-small     /* 14px - Títulos compactos */
```

#### Body (Texto de Cuerpo)

```css
.md-body-large      /* 16px - Texto principal */
.md-body-medium     /* 14px - Texto secundario */
.md-body-small      /* 12px - Texto auxiliar */
```

#### Label (Etiquetas)

```css
.md-label-large     /* 14px - Botones, tabs */
.md-label-medium    /* 12px - Labels de inputs */
.md-label-small     /* 11px - Captions */
```

### Ejemplo de Uso

```html
<h1 class="md-display-large">Encuentra tu Compañero Perfecto</h1>
<h2 class="md-headline-medium">Animales Disponibles</h2>
<p class="md-body-large">Explora nuestra galería de animales en busca de hogar.</p>
```

---

## Componentes Reutilizables

### Botones

Material Design 3 define 5 tipos de botones:

#### 1. Filled Button (Primario)

```html
<button class="md-button md-button-filled">
  Adoptar Ahora
</button>
```

**Uso**: Acción principal más importante de la pantalla.

#### 2. Filled Tonal Button

```html
<button class="md-button md-button-filled-tonal">
  Ver Detalles
</button>
```

**Uso**: Acciones importantes pero menos prominentes que el primario.

#### 3. Outlined Button

```html
<button class="md-button md-button-outlined">
  Cancelar
</button>
```

**Uso**: Acciones secundarias con énfasis medio.

#### 4. Text Button

```html
<button class="md-button md-button-text">
  Más Información
</button>
```

**Uso**: Acciones de baja prioridad, enlaces.

#### 5. Elevated Button

```html
<button class="md-button md-button-elevated">
  Compartir
</button>
```

**Uso**: Acciones que necesitan separación visual del fondo.

#### Variantes de Color

```html
<button class="md-button md-button-filled md-button-secondary">Secundario</button>
<button class="md-button md-button-filled md-button-accent">Acento</button>
<button class="md-button md-button-filled md-button-success">Éxito</button>
<button class="md-button md-button-filled md-button-error">Error</button>
```

#### Tamaños

```html
<button class="md-button md-button-filled md-button-small">Pequeño</button>
<button class="md-button md-button-filled">Normal</button>
<button class="md-button md-button-filled md-button-large">Grande</button>
```

### Inputs y Formularios

#### Input Básico

```html
<div class="md-input-container">
  <label class="md-input-label" for="nombre">Nombre</label>
  <input type="text" id="nombre" class="md-input" placeholder="Ingresa tu nombre">
  <span class="md-input-helper">Este campo es requerido</span>
</div>
```

#### Input con Estados

```html
<!-- Error -->
<input type="email" class="md-input md-input-error" placeholder="correo@ejemplo.com">
<span class="md-input-helper md-input-error-text">Email inválido</span>

<!-- Éxito -->
<input type="email" class="md-input md-input-success" placeholder="correo@ejemplo.com">
<span class="md-input-helper md-input-success-text">Email válido</span>

<!-- Deshabilitado -->
<input type="text" class="md-input" disabled placeholder="Campo deshabilitado">
```

#### Textarea

```html
<textarea class="md-input md-textarea" placeholder="Describe tu experiencia..."></textarea>
```

#### Select

```html
<select class="md-input md-select">
  <option>Selecciona una opción</option>
  <option>Perro</option>
  <option>Gato</option>
</select>
```

#### Checkbox y Radio

```html
<label>
  <input type="checkbox" class="md-checkbox">
  Acepto los términos y condiciones
</label>

<label>
  <input type="radio" name="tipo" class="md-radio">
  Perro
</label>
```

### Cards

#### Card Básica

```html
<div class="md-card md-card-elevated">
  <img src="animal.jpg" alt="Perro Max" class="md-card-media">
  <div class="md-card-content">
    <h3 class="md-title-large">Max</h3>
    <p class="md-body-medium">Perro juguetón de 2 años</p>
  </div>
</div>
```

#### Card con Header y Footer

```html
<div class="md-card md-card-outlined">
  <div class="md-card-header">
    <h3 class="md-title-medium">Información del Animal</h3>
  </div>
  <div class="md-card-content">
    <p class="md-body-medium">Contenido de la card...</p>
  </div>
  <div class="md-card-footer">
    <button class="md-button md-button-text">Ver Más</button>
  </div>
</div>
```

#### Variantes de Card

```html
<div class="md-card md-card-elevated">Elevada (con sombra)</div>
<div class="md-card md-card-filled">Rellena (fondo gris)</div>
<div class="md-card md-card-outlined">Con borde</div>
```

---

## Patrones UX

### Progressive Disclosure

Muestra información gradualmente para no abrumar al usuario:

```html
<details class="md-card md-card-outlined md-p-md">
  <summary class="md-title-medium">Información Médica</summary>
  <div class="md-body-medium md-m-md">
    <p>Vacunas al día, esterilizado...</p>
  </div>
</details>
```

### Loading States

```html
<!-- Spinner -->
<div class="md-spin" style="width: 24px; height: 24px; border: 2px solid var(--md-primary); border-top-color: transparent; border-radius: 50%;"></div>

<!-- Skeleton -->
<div class="md-pulse" style="background: var(--md-surface-variant); height: 20px; border-radius: 4px;"></div>
```

### Estados Interactivos

```html
<!-- Hover effect -->
<div class="md-card md-card-elevated md-hover">
  Pasa el mouse sobre mí
</div>

<!-- Focus ring -->
<button class="md-button md-button-filled md-focus-ring">
  Enfócame con Tab
</button>

<!-- Active state -->
<button class="md-button md-button-filled md-active">
  Presióname
</button>
```

---

## Accesibilidad

### Principios WCAG 2.1 AA

1. **Contraste de Color**: Ratio mínimo 4.5:1 para texto normal
2. **Navegación por Teclado**: Todos los elementos interactivos accesibles con Tab
3. **Focus Visible**: Indicadores claros de foco (`.md-focus-ring`)
4. **ARIA Labels**: Etiquetas descriptivas para lectores de pantalla
5. **Touch Targets**: Mínimo 44x44px para elementos táctiles

### Implementación

```html
<!-- Botón accesible -->
<button 
  class="md-button md-button-filled md-focus-ring" 
  aria-label="Adoptar a Max, perro de 2 años"
>
  Adoptar
</button>

<!-- Input accesible -->
<label for="email" class="md-input-label">
  Correo Electrónico
  <span aria-label="requerido">*</span>
</label>
<input 
  type="email" 
  id="email" 
  class="md-input" 
  aria-required="true"
  aria-describedby="email-helper"
>
<span id="email-helper" class="md-input-helper">
  Usaremos tu email para contactarte
</span>
```

### Movimiento Reducido

El sistema respeta la preferencia `prefers-reduced-motion`:

```css
@media (prefers-reduced-motion: reduce) {
  /* Todas las animaciones se reducen automáticamente */
}
```

---

## Animaciones y Transiciones

### Transiciones Predefinidas

```css
--md-transition-fast: 150ms   /* Hover, focus */
--md-transition-base: 250ms   /* Transiciones generales */
--md-transition-slow: 350ms   /* Animaciones complejas */
```

### Animaciones Disponibles

```html
<!-- Fade In -->
<div class="md-fade-in">Aparezco gradualmente</div>

<!-- Slide Up -->
<div class="md-slide-up">Me deslizo hacia arriba</div>

<!-- Scale In -->
<div class="md-scale-in">Crezco desde el centro</div>

<!-- Pulse -->
<div class="md-pulse">Pulso continuamente</div>

<!-- Spin -->
<div class="md-spin">Giro continuamente</div>
```

### Easing Functions

Material Design 3 usa curvas de aceleración específicas:

```css
cubic-bezier(0.4, 0, 0.2, 1)  /* Estándar */
```

---

## Guía de Implementación

### Estructura de Archivos

```
public/
├── css/
│   ├── material-design.css  ← Sistema de diseño centralizado
│   ├── styles.css           ← Estilos personalizados
│   └── output.css           ← Tailwind (si se usa)
```

### Orden de Carga

```html
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- 1. Material Design System -->
  <link rel="stylesheet" href="/css/material-design.css">
  
  <!-- 2. Estilos personalizados -->
  <link rel="stylesheet" href="/css/styles.css">
  
  <title>Patitas Felices</title>
</head>
<body>
  <!-- Contenido -->
</body>
</html>
```

### Buenas Prácticas

1. **Usa clases del sistema**: Prefiere `md-button-filled` sobre estilos inline
2. **Mantén consistencia**: Usa las mismas variantes de componentes en toda la app
3. **Respeta la jerarquía**: Display > Headline > Title > Body > Label
4. **Espaciado consistente**: Usa las variables `--md-spacing-*`
5. **Colores semánticos**: Usa `--md-success` para éxito, no verde directo

---

## Ejemplos de Uso

### Página de Login

```html
<div class="md-surface-container md-p-xl md-rounded-lg" style="max-width: 400px; margin: 0 auto;">
  <h1 class="md-headline-large md-text-primary">Iniciar Sesión</h1>
  <p class="md-body-medium md-text-on-surface-variant md-m-md">
    Accede a tu cuenta de Patitas Felices
  </p>
  
  <form>
    <div class="md-input-container">
      <label class="md-input-label" for="email">Correo Electrónico</label>
      <input type="email" id="email" class="md-input" placeholder="correo@ejemplo.com">
    </div>
    
    <div class="md-input-container">
      <label class="md-input-label" for="password">Contraseña</label>
      <input type="password" id="password" class="md-input" placeholder="••••••••">
    </div>
    
    <button type="submit" class="md-button md-button-filled md-w-full">
      Iniciar Sesión
    </button>
    
    <button type="button" class="md-button md-button-text md-w-full">
      ¿Olvidaste tu contraseña?
    </button>
  </form>
</div>
```

### Card de Animal

```html
<div class="md-card md-card-elevated md-hover">
  <img src="/img/max.jpg" alt="Max, perro labrador" class="md-card-media">
  
  <div class="md-card-content">
    <h3 class="md-title-large md-text-primary">Max</h3>
    <p class="md-body-medium md-text-on-surface-variant">
      Labrador · 2 años · Macho
    </p>
    
    <div class="md-flex md-gap-sm md-m-md">
      <span class="md-label-small md-bg-success md-text-on-success md-p-xs md-rounded-full">
        Vacunado
      </span>
      <span class="md-label-small md-bg-info md-text-on-info md-p-xs md-rounded-full">
        Esterilizado
      </span>
    </div>
    
    <p class="md-body-small">
      Max es un perro juguetón y cariñoso que busca un hogar lleno de amor.
    </p>
  </div>
  
  <div class="md-card-footer md-flex md-justify-between">
    <button class="md-button md-button-text">Ver Más</button>
    <button class="md-button md-button-filled">Adoptar</button>
  </div>
</div>
```

### Formulario de Adopción

```html
<form class="md-surface-container md-p-xl md-rounded-lg">
  <h2 class="md-headline-medium md-text-primary">Solicitud de Adopción</h2>
  
  <div class="md-input-container">
    <label class="md-input-label" for="nombre">Nombre Completo *</label>
    <input type="text" id="nombre" class="md-input" required>
    <span class="md-input-helper">Como aparece en tu identificación</span>
  </div>
  
  <div class="md-input-container">
    <label class="md-input-label" for="telefono">Teléfono *</label>
    <input type="tel" id="telefono" class="md-input" required>
  </div>
  
  <div class="md-input-container">
    <label class="md-input-label" for="experiencia">Experiencia con Mascotas</label>
    <textarea id="experiencia" class="md-input md-textarea" 
              placeholder="Cuéntanos sobre tu experiencia..."></textarea>
  </div>
  
  <div class="md-input-container">
    <label>
      <input type="checkbox" class="md-checkbox" required>
      <span class="md-label-medium">Acepto los términos y condiciones *</span>
    </label>
  </div>
  
  <div class="md-flex md-gap-md md-justify-end">
    <button type="button" class="md-button md-button-outlined">Cancelar</button>
    <button type="submit" class="md-button md-button-filled">Enviar Solicitud</button>
  </div>
</form>
```

### Dashboard de Coordinador

```html
<div class="md-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--md-spacing-lg);">
  
  <!-- Card de Estadística -->
  <div class="md-card md-card-filled md-p-lg">
    <div class="md-flex md-items-center md-gap-md">
      <div class="md-bg-primary md-rounded-full" style="width: 48px; height: 48px;"></div>
      <div>
        <p class="md-label-medium md-text-on-surface-variant">Animales Disponibles</p>
        <h3 class="md-headline-medium md-text-primary">24</h3>
      </div>
    </div>
  </div>
  
  <!-- Card de Estadística -->
  <div class="md-card md-card-filled md-p-lg">
    <div class="md-flex md-items-center md-gap-md">
      <div class="md-bg-success md-rounded-full" style="width: 48px; height: 48px;"></div>
      <div>
        <p class="md-label-medium md-text-on-surface-variant">Adopciones Este Mes</p>
        <h3 class="md-headline-medium md-text-success">12</h3>
      </div>
    </div>
  </div>
  
  <!-- Card de Estadística -->
  <div class="md-card md-card-filled md-p-lg">
    <div class="md-flex md-items-center md-gap-md">
      <div class="md-bg-warning md-rounded-full" style="width: 48px; height: 48px;"></div>
      <div>
        <p class="md-label-medium md-text-on-surface-variant">Solicitudes Pendientes</p>
        <h3 class="md-headline-medium md-text-warning">8</h3>
      </div>
    </div>
  </div>
  
</div>
```

---

## Recursos Adicionales

### Referencias

- [Material Design 3 Guidelines](https://m3.material.io/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [Inter Font](https://fonts.google.com/specimen/Inter)

### Herramientas Recomendadas

- **Contrast Checker**: Para verificar ratios de contraste
- **axe DevTools**: Para auditorías de accesibilidad
- **Lighthouse**: Para performance y mejores prácticas

### Mantenimiento

Este sistema de diseño debe evolucionar con el proyecto. Cuando agregues nuevos componentes:

1. Documenta su uso en esta guía
2. Mantén consistencia con Material Design 3
3. Prueba la accesibilidad
4. Verifica en múltiples dispositivos

---

**Versión**: 3.0  
**Última actualización**: 2025  
**Mantenido por**: Equipo Patitas Felices
