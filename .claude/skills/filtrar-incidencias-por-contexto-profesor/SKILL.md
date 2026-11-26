---
name: filtrar-incidencias-por-contexto-profesor
description: >
  Alinea el listado de Incidencias del profesor con el contexto seleccionado
  (Grado, Sección y Curso) para que no muestre registros de otras secciones
  o cursos fuera de su selección, respetando privacidad de reservadas.
---

# Alinear listado de Incidencias con el contexto del profesor

Proyecto: sis_incidencias (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue SIEMPRE CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no
reescribir archivos completos, no tocar migraciones ni hacer git add .

## Problema detectado
1. En Inicio, el profesor selecciona Grado, Sección y Curso (por ejemplo, una
   combinación sin incidencias, donde el dashboard marca 0).
2. Al entrar a Incidencias, aparecen incidencias de otras secciones o cursos
   que no coinciden con la selección activa del profesor.

## Reglas a respetar
- No romper la privacidad de incidencias reservadas (contenido oculto y
  sin filtrar datos indebidos).
- No alterar permisos ni vistas de Directora, Subdirector o Administrador.
- Revisar primero cómo se comporta hoy `IncidenciaController::index` y las
  pruebas existentes antes de modificar. Si hay ambigüedad sobre qué debe
  mostrarse cuando no hay contexto seleccionado o cuando hay contexto marcado,
  detente y pregunta.

## Pasos
1. Inspecciona `IncidenciaController.php` (especialmente `index`) y cómo se
   obtiene el contexto activo del profesor. Resume en pocas líneas qué filtra hoy.
2. Si está claro y seguro, aplica el cambio mínimo para que el listado del
   profesor respete su contexto seleccionado; si hay duda de regla de negocio,
   detente y consúltalo antes de editar.
3. No hagas `git add .`. Detente al terminar para probar y cierra con el bloque
   RESUMEN PARA CHATGPT.