# Configuración de API Keys - Sistema de Alquiler

## 🔐 Variables de Entorno

Este proyecto utiliza variables de entorno para almacenar API keys de forma segura.

### Configuración

1. **Duplica el archivo `.env.example`** o crea un nuevo `.env` en la raíz del proyecto:

```bash
cp .env.example .env
```

2. **Completa las API keys** en el archivo `.env`:

```env
# SerpApi - Google Image Search
SERPAPI_API_KEY=tu_clave_aqui

# Pexels - Stock Photos
PEXELS_API_KEY=tu_clave_aqui

# Pixabay - Stock Photos
PIXABAY_API_KEY=tu_clave_aqui
```

### Obteniendo las API Keys

#### SerpApi (Búsqueda de Imágenes en Google)
1. Ve a [https://serpapi.com/](https://serpapi.com/)
2. Regístrate y crea una cuenta
3. Accede a tu dashboard y copia la API key

#### Pexels (Stock de Fotos)
1. Ve a [https://www.pexels.com/api/](https://www.pexels.com/api/)
2. Regístrate con tu email
3. Copia la API key generada

#### Pixabay (Stock de Fotos)
1. Ve a [https://pixabay.com/api/](https://pixabay.com/api/)
2. Regístrate
3. Copia la API key

## ⚠️ Seguridad

- **NUNCA** hagas commit del archivo `.env` con las claves reales
- El archivo `.gitignore` está configurado para ignorar `.env` automáticamente
- Las claves en el repositorio son solo placeholders

## 📝 Endpoints para Buscar Imágenes

### 1. Buscar por Modelo ID y Año

```
POST /Vehiculos/obtenerImagenApi
```

**Parámetros:**
- `modelo` (integer): ID del modelo del vehículo
- `anio` (string): Año del vehículo

**Respuesta:**
```json
{
  "img": "uploads/vehiculos/marca_modelo_2023.jpg",
  "url": "http://tudominio.com/uploads/vehiculos/marca_modelo_2023.jpg"
}
```

### 2. Buscar por Marca, Modelo y Año (NUEVO)

```
POST /Vehiculos/obtenerImagenDirecta
```

**Parámetros:**
- `marca` (string): Marca del vehículo
- `modelo` (string): Modelo del vehículo
- `anio` (string): Año del vehículo

**Respuesta:**
```json
{
  "img": "uploads/vehiculos/marca_modelo_2023.jpg",
  "url": "http://tudominio.com/uploads/vehiculos/marca_modelo_2023.jpg"
}
```

## 🔄 Flujo de Búsqueda de Imágenes

El sistema intenta obtener imágenes en este orden:

1. **SerpApi** - Google Image Search (más rápido, mejor calidad)
2. **Pexels** - Stock profesional
3. **Pixabay** - Stock alternativo
4. **Default** - Imagen por defecto si no encuentra nada

Cada búsqueda intenta con diferentes variaciones:
- `"marca modelo año car"`
- `"marca modelo car"`
- `"marca modelo año vehicle"`

## 💾 Almacenamiento Local

Las imágenes descargadas se guardan en:
```
uploads/vehiculos/marca_modelo_año.jpg
```

Las búsquedas posteriores usan la copia local si ya existe.

---

**Última actualización:** 2026-04-10
