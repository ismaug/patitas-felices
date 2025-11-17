# Arquitectura Lógica – “Patitas Felices”

## 1. Vista general por capas

Aplicación monolítica en **3 capas**:

- **Presentación (UI)**: pantallas, formularios, navegación.
    
- **Aplicación (Servicios de Casos de Uso)**: orquesta lógica de negocio.
    
- **Dominio y Datos**: entidades de negocio y repositorios.
    

```mermaid
graph TD
    Usuario[Usuarios] --> UI_Capa[Capa de Presentacion]
    UI_Capa --> App_Capa[Capa de Aplicacion]
    App_Capa --> Dom_Capa[Capa de Dominio_y_Datos]
    Dom_Capa --> Almacen[Almacenamiento]
```

---

## 2. Servicios de aplicación y Casos de Uso

La capa de aplicación se organiza por **servicios**, cada uno ligado directamente a casos de uso (CU):

- **ServicioUsuariosAuth**
    
    - CU-01 Registrar Usuario
        
    - CU-02 Iniciar Sesion
        
- **ServicioAnimales**
    
    - CU-03 Registrar Animal Rescatado
        
    - CU-06 Actualizar Estado y Ubicacion del Animal
        
    - CU-08 Consultar Historial Medico del Animal
        
    - CU-10 Consultar Informacion Detallada del Animal
        
    - CU-13 Consultar Ficha y Seguimiento del Animal
        
- **ServicioAdopciones**
    
    - CU-04 Solicitar Adopcion
        
    - CU-05 Gestionar Solicitudes de Adopcion
        
    - CU-07 Registrar Adopcion
        
    - CU-09 Consultar Mis Solicitudes
        
- **ServicioVoluntariado**
    
    - CU-11 Gestionar Actividades de Voluntariado
        
- **ServicioReportes**
    
    - CU-12 Generar Reportes de Adopcion
        

```mermaid
graph LR
    UI[Capa de Presentacion] --> S_Usuarios["ServicioUsuariosAuth (CU-01, CU-02)"]
    UI --> S_Animales["ServicioAnimales (CU-03, CU-06, CU-08, CU-10, CU-13)"]
    UI --> S_Adop["ServicioAdopciones (CU-04, CU-05, CU-07, CU-09)"]
    UI --> S_Vol["ServicioVoluntariado (CU-11)"]
    UI --> S_Rep["ServicioReportes (CU-12)"]

    S_Usuarios --> DomRepos[Dominio y Repositorios]
    S_Animales --> DomRepos
    S_Adop --> DomRepos
    S_Vol --> DomRepos
    S_Rep --> DomRepos
```

---

## 3. Dominio y Datos (modelo lógico)

Entidades principales (simplificado):

- Usuario, Rol
    
- Animal, EstadoAnimal, Ubicacion
    
- SolicitudAdopcion, HistorialSolicitud, Adopcion
    
- RegistroMedico
    
- ActividadVoluntariado, InscripcionVoluntariado
    

```mermaid
classDiagram
    class Usuario {
        id
        nombre
        correo
        rol
    }

    class Animal {
        id
        nombre
        especie
        estado
    }

    class SolicitudAdopcion {
        id
        fechaSolicitud
        estado
    }

    class Adopcion {
        id
        fechaAdopcion
    }

    Usuario "1" --> "many" SolicitudAdopcion : realiza
    Animal "1" --> "many" SolicitudAdopcion : es_solicitado
    SolicitudAdopcion "1" --> "0..1" Adopcion : genera
```

Los repositorios (acceso a datos) se exponen como interfaces lógicas, por ejemplo:

- `RepositorioUsuarios`
    
- `RepositorioAnimales`
    
- `RepositorioSolicitudes`
    
- `RepositorioAdopciones`
    
- `RepositorioVoluntariado`
    
- `RepositorioReportes`
    


---

## 5. Resumen

- Arquitectura: **monolito en tres capas** (Presentacion, Aplicacion, Dominio/Datos).
    
- Servicios de aplicacion mapeados 1 a 1 con grupos de **Casos de Uso CU-01 a CU-13**.
    
- Dominio centrado en entidades clave (Usuario, Animal, SolicitudAdopcion, Adopcion, etc.) y repositorios.