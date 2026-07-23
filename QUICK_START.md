# 🚀 Quick Start - Sistema de Búsqueda de Imágenes

## Inicio Rápido (3 pasos)

### 1️⃣ Obtener API Keys

- **SerpApi**: https://serpapi.com/ → Copiar API key
- **Pexels**: https://www.pexels.com/api/ → Copiar API key  
- **Pixabay**: https://pixabay.com/api/ → Copiar API key (opcional)

### 2️⃣ Configurar .env

Edita `/opt/lampp/htdocs/alquiler/.env`:

```env
SERPAPI_API_KEY=tu_clave_aqui
PEXELS_API_KEY=tu_clave_aqui
PIXABAY_API_KEY=tu_clave_aqui
```

### 3️⃣ ¡Listo! Usa el Sistema

- Abre: **Gestión de Vehículos → Nuevo Vehículo**
- Selecciona: Marca → Modelo → Año
- ¡La imagen se carga automáticamente! 📸

---

## Testing

**Página de prueba:** http://localhost/alquiler/test_imagen_api.php

```
1. Abre el link anterior
2. Ingresa marca, modelo, año
3. Click "Buscar Imagen"
4. Observa los logs
```

---

## Debugging en Navegador

```
F12 → Console → Realiza la búsqueda → Observa los logs
```

Ejemplos de logs:
- ✅ "actualizarPreviewImagen called - modelo: 1 anio: 2002"
- ✅ "Response status: 200"
- ✅ "Respuesta parseada: {...}"

---

## Estructura de Datos

```json
{
  "img": "uploads/vehiculos/toyota_corolla_2002.jpg",
  "url": "http://localhost/alquiler/uploads/vehiculos/toyota_corolla_2002.jpg",
  "source": "Google/SerpApi"
}
```

---

## Errores Comunes

| Error | Solución |
|-------|----------|
| 404 Not Found | Verifica que el controlador existe |
| API key inválida | Regenera en https://serpapi.com |
| Imagen no aparece | Abre F12 Console para ver logs |
| Sin respuesta | Verifica conexión a internet |

---

## 📞 Soporte

**Guía completa:** Lee `SOLUCION_FINAL.md`  
**Debugging avanzado:** Lee `DEBUG.md`  
**Documentación API:** Lee `API_KEYS_README.md`

---

**¡Listo para usar! ✅**
