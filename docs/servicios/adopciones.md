
# ServicioAdopciones – Contrato

## CU-04 Crear Solicitud
`crearSolicitudAdopcion(id_animal, id_adoptante, input): ServiceResult`

## CU-05 Gestionar Solicitudes
`listarSolicitudes(filtros): ServiceResult`
`obtenerSolicitud(id): ServiceResult`
`evaluarSolicitud(id, id_coord, nuevo_estado, datosRevision): ServiceResult`

## CU-07 Registrar Adopción
`registrarAdopcion(id_solicitud, input, id_coord): ServiceResult`

## CU-09 Solicitudes por Usuario
`obtenerSolicitudesPorUsuario(id_usuario): ServiceResult`
