---
name: filtrar-alumnos-por-contexto-profesor
description: >
  Restringe el listado de Alumnos del profesor al contexto/sección
  seleccionado en vez de listar alumnos de otras secciones fuera de su
  selección activa.
---

# Filtrar Alumnos del profesor según su contexto seleccionado

Proyecto: sis_incidencias (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue SIEMPRE CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no
reescribir archivos completos, no tocar migraciones ni hacer git add .

## Problema detectado
1. En Inicio, el profesor selecciona Grado, Sección y Curso (por ejemplo,
   `PRUEBA_1ro`, `PRUEBA_1ro - A`, `PRUEBA_DASHBOARD_Matemática`).
2. Al ir a Alumnos, aparecen alumnos de otras secciones (por ejemplo, de la
   sección `B`) que no corresponden a la sección/contexto seleccionado.

## Reglas a respetar
- No romper la exclusión de incidencias reservadas en el reporte/ficha PDF
  para el apoderado (`EstudianteController`).
- No alterar vistas ni permisos de Directora, Subdirector o Administrador.
- Si hay ambigüedad sobre qué mostrar cuando no hay contexto seleccionado o
  cuando el selector de sección en Alumnos está en "Todas las secciones",
  detente y pregunta antes de modificar.

## Pasos
1. Inspecciona `EstudianteController.php`, la vista de alumnos y cómo se lee
   el contexto activo del profesor. Resume en pocas líneas qué filtra hoy.
2. Si está claro y seguro, aplica el cambio mínimo para que el profesor solo
   vea alumnos de su contexto/sección activa; si hay duda de regla de
   negocio, detente y consúltalo antes de editar.
3. No hagas `git add .`. Detente al terminar para probar y cierra con el bloque
   RESUMEN PARA CHATGPT.
   