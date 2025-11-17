# Gestión de Imágenes - Patitas Felices

## Estructura de Carpetas

El sistema de gestión de imágenes está organizado en la siguiente estructura dentro de `public/img/`:

```
public/img/
├── animales/          # Fotos de animales (NO en git)
│   └── .gitkeep
└── static/            # Imágenes estáticas (SÍ en git)
    ├── .gitkeep
    ├── logo/          # Logos de la fundación
    │   └── .gitkeep
    └── icons/         # Iconos personalizados
        └── .gitkeep
```

## Descripción de Carpetas

### 📁 `public/img/animales/`
**Propósito:** Almacenar las fotografías de animales rescatados subidas por usuarios.

**Características:**
- ❌ **NO se incluye en el control de versiones Git**
- 📸 Contiene fotos dinámicas subidas por usuarios
- 🔄 El contenido cambia frecuentemente
- 💾 Debe respaldarse regularmente en el servidor de producción

**Contenido típico:**
- Fotos de perfil de animales
- Imágenes del estado del animal
- Fotos de seguimiento médico
- Imágenes de adopción

**Ejemplo de nombres de archivo:**
```
animales/
├── animal_123_perfil.jpg
├── animal_123_medico_001.jpg
├── animal_456_rescate.png
└── animal_789_adopcion.jpg
```

### 📁 `public/img/static/`
**Propósito:** Almacenar imágenes estáticas del sitio web que forman parte del diseño.

**Características:**
- ✅ **SÍ se incluye en el control de versiones Git**
- 🎨 Contiene recursos visuales del diseño del sitio
- 🔒 El contenido es estable y controlado
- 📦 Se despliega junto con el código

### 📁 `public/img/static/logo/`
**Propósito:** Logos oficiales de la Fundación Patitas Felices.

**Contenido esperado:**
- Logo principal en diferentes formatos
- Variantes del logo (color, blanco, negro)
- Favicon del sitio
- Logo para redes sociales

**Ejemplo:**
```
logo/
├── patitas-felices-logo.svg
├── patitas-felices-logo.png
├── patitas-felices-logo-white.svg
├── patitas-felices-favicon.ico
└── patitas-felices-social.png
```

### 📁 `public/img/static/icons/`
**Propósito:** Iconos personalizados del sitio (si no se usan librerías externas).

**Contenido esperado:**
- Iconos de navegación
- Iconos de estado (adoptado, disponible, etc.)
- Iconos de acciones (editar, eliminar, ver)
- Ilustraciones decorativas

**Ejemplo:**
```
icons/
├── paw-icon.svg
├── heart-icon.svg
├── medical-icon.svg
└── volunteer-icon.svg
```

## Configuración de .gitignore

El archivo [`.gitignore`](../.gitignore) está configurado para:

```gitignore
# Imágenes subidas por usuarios (no incluir en git)
public/img/animales/*
!public/img/animales/.gitkeep

# Mantener estructura de carpetas estáticas (incluir en git)
!public/img/static/
!public/img/static/**
```

**Explicación:**
1. `public/img/animales/*` - Excluye todas las imágenes de animales
2. `!public/img/animales/.gitkeep` - Pero mantiene el archivo `.gitkeep` para preservar la estructura
3. `!public/img/static/` y `!public/img/static/**` - Incluye explícitamente todas las imágenes estáticas

## Límites y Restricciones Recomendados

### Tamaño de Archivos

| Tipo de Imagen | Tamaño Máximo | Recomendado |
|----------------|---------------|-------------|
| Foto de animal | 5 MB | 1-2 MB |
| Logo | 500 KB | 100-200 KB |
| Icono | 100 KB | 20-50 KB |

### Dimensiones Recomendadas

| Tipo de Imagen | Dimensiones | Proporción |
|----------------|-------------|------------|
| Foto de perfil animal | 800x600 px | 4:3 |
| Foto detalle animal | 1200x900 px | 4:3 |
| Logo principal | 512x512 px | 1:1 |
| Logo horizontal | 400x100 px | 4:1 |
| Icono | 64x64 px | 1:1 |

### Formatos Permitidos

#### Para Fotos de Animales (`public/img/animales/`)
- ✅ **JPEG/JPG** - Recomendado para fotografías (mejor compresión)
- ✅ **PNG** - Permitido (mayor calidad, mayor tamaño)
- ✅ **WEBP** - Recomendado para web moderna (mejor compresión)
- ❌ **GIF** - No recomendado para fotos
- ❌ **BMP** - No permitido (muy pesado)

#### Para Imágenes Estáticas (`public/img/static/`)
- ✅ **SVG** - Recomendado para logos e iconos (escalable)
- ✅ **PNG** - Para logos con transparencia
- ✅ **WEBP** - Para imágenes web optimizadas
- ✅ **ICO** - Para favicons
- ⚠️ **JPEG** - Solo si no se necesita transparencia

## Proceso de Subida de Imágenes

### 1. Validación en el Cliente (JavaScript)

```javascript
// Ejemplo de validación antes de subir
function validarImagen(archivo) {
    const formatosPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    const tamañoMaximo = 5 * 1024 * 1024; // 5 MB
    
    if (!formatosPermitidos.includes(archivo.type)) {
        alert('Formato no permitido. Use JPG, PNG o WEBP');
        return false;
    }
    
    if (archivo.size > tamañoMaximo) {
        alert('La imagen es muy grande. Máximo 5 MB');
        return false;
    }
    
    return true;
}
```

### 2. Procesamiento en el Servidor (PHP)

```php
<?php
// Ejemplo de procesamiento de subida
function procesarImagenAnimal($archivo, $idAnimal) {
    // Validar tipo MIME
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoMime = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($tipoMime, $tiposPermitidos)) {
        throw new Exception('Formato de imagen no permitido');
    }
    
    // Validar tamaño
    if ($archivo['size'] > 5 * 1024 * 1024) {
        throw new Exception('La imagen excede el tamaño máximo de 5 MB');
    }
    
    // Generar nombre único
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombreArchivo = "animal_{$idAnimal}_" . time() . "." . $extension;
    $rutaDestino = __DIR__ . "/public/img/animales/" . $nombreArchivo;
    
    // Mover archivo
    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        throw new Exception('Error al guardar la imagen');
    }
    
    // Opcional: Redimensionar/optimizar imagen
    optimizarImagen($rutaDestino);
    
    return $nombreArchivo;
}
```

### 3. Optimización de Imágenes

**Recomendaciones:**
- Redimensionar imágenes grandes automáticamente
- Comprimir JPEGs con calidad 85%
- Convertir a WEBP cuando sea posible
- Generar miniaturas para listados

**Herramientas sugeridas:**
- PHP: GD Library o Imagick
- Línea de comandos: ImageMagick, cwebp
- Servicios: TinyPNG API, Cloudinary

### 4. Nomenclatura de Archivos

**Para imágenes de animales:**
```
Patrón: animal_{id}_{tipo}_{timestamp}.{ext}

Ejemplos:
- animal_123_perfil_1699123456.jpg
- animal_123_medico_1699123457.jpg
- animal_456_rescate_1699123458.png
```

**Para imágenes estáticas:**
```
Patrón: {descripcion}-{variante}.{ext}

Ejemplos:
- patitas-felices-logo.svg
- patitas-felices-logo-white.png
- paw-icon-blue.svg
```

## Respaldo y Mantenimiento

### Respaldo de Imágenes de Animales

Como las imágenes en `public/img/animales/` no están en Git, es crucial:

1. **Respaldo automático diario** del directorio completo
2. **Almacenamiento en la nube** (AWS S3, Google Cloud Storage, etc.)
3. **Política de retención** (ej: mantener imágenes de animales adoptados por 1 año)

### Limpieza Periódica

```bash
# Ejemplo de script para limpiar imágenes huérfanas
# (imágenes sin registro en la base de datos)

# Listar imágenes en disco
find public/img/animales/ -type f -name "animal_*.jpg" > imagenes_disco.txt

# Comparar con base de datos y eliminar huérfanas
php scripts/limpiar_imagenes_huerfanas.php
```

### Monitoreo de Espacio

```bash
# Verificar espacio usado por imágenes
du -sh public/img/animales/
du -sh public/img/static/

# Contar archivos
find public/img/animales/ -type f | wc -l
```

## Seguridad

### Prevención de Ataques

1. **Validar tipo MIME real** (no solo extensión)
2. **Sanitizar nombres de archivo**
3. **Limitar tamaño de subida** en php.ini:
   ```ini
   upload_max_filesize = 5M
   post_max_size = 6M
   ```
4. **No ejecutar archivos** subidos
5. **Usar directorios sin permisos de ejecución**

### Permisos de Carpetas

```bash
# Permisos recomendados en Linux
chmod 755 public/img/
chmod 755 public/img/animales/
chmod 755 public/img/static/
chmod 644 public/img/animales/*.jpg
chmod 644 public/img/static/**/*
```

## Integración con la Base de Datos

Las rutas de las imágenes se almacenan en la tabla `animales`:

```sql
-- Ejemplo de registro
INSERT INTO animales (nombre, foto_url, ...) 
VALUES ('Firulais', 'animales/animal_123_perfil_1699123456.jpg', ...);

-- Consulta con URL completa
SELECT 
    id,
    nombre,
    CONCAT('/img/', foto_url) as foto_url_completa
FROM animales;
```

## Ejemplo de Uso en HTML

```html
<!-- Mostrar foto de animal -->
<img 
    src="/img/<?php echo htmlspecialchars($animal['foto_url']); ?>" 
    alt="<?php echo htmlspecialchars($animal['nombre']); ?>"
    class="w-full h-64 object-cover rounded-lg"
    loading="lazy"
>

<!-- Logo del sitio -->
<img 
    src="/img/static/logo/patitas-felices-logo.svg" 
    alt="Patitas Felices"
    class="h-12"
>

<!-- Icono personalizado -->
<img 
    src="/img/static/icons/paw-icon.svg" 
    alt="Huella"
    class="w-6 h-6"
>
```

## Checklist de Implementación

- [x] Crear estructura de carpetas
- [x] Configurar .gitignore
- [x] Documentar proceso de gestión
- [ ] Implementar validación de subida en cliente
- [ ] Implementar procesamiento de subida en servidor
- [ ] Configurar límites en php.ini
- [ ] Implementar optimización automática de imágenes
- [ ] Configurar respaldo automático
- [ ] Crear script de limpieza de imágenes huérfanas
- [ ] Implementar generación de miniaturas
- [ ] Documentar API de subida de imágenes

## Referencias

- [PHP File Upload](https://www.php.net/manual/en/features.file-upload.php)
- [Image Optimization Best Practices](https://web.dev/fast/#optimize-your-images)
- [Git Ignore Patterns](https://git-scm.com/docs/gitignore)
- [OWASP File Upload Security](https://owasp.org/www-community/vulnerabilities/Unrestricted_File_Upload)