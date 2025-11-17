## Estructura común de un Servicio
- Nombre del servicio
- Métodos (uno por CU)
- Entradas (campos, tipos)
- Salidas (`ServiceResult`)
- Reglas de negocio
- Permisos por rol
- Errores comunes

## Formato de ServiceResult
```json
{
  "success": true,
  "message": "string",
  "errors": { "campo": "mensaje" },
  "data": null
}
```
