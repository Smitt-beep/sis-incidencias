---
name: mostrar-tipo-documento-alumnos
description: >
  Cambia la columna DNI del listado de Alumnos por "Documento" mostrando el
  tipo (DNI o CE) al costado de cada número en todos los roles que usan el
  apartado de Alumnos, sin agregar otros tipos de documento.
---

# Columna Documento (DNI / CE) en el listado de Alumnos

Proyecto: sis_incidencias (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue SIEMPRE CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no
reescribir archivos completos, no tocar migraciones ni hacer git add .

## Requisito
1. No todos los alumnos tienen DNI; existen alumnos con Carnet de Extranjería
   (CE). Solo existen dos tipos: `DNI` y `CE`.
2. En el apartado de Alumnos (para todos los roles que lo muestran: Directora,
   Subdirector, Administrador y Profesor):
   - Renombrar la cabecera de columna de `DNI` a `Documento`.
   - Mostrar al costado de cada número el tipo correspondiente (`DNI` o `CE`),
     por ejemplo `91839728328483 · CE` o `12345678 · DNI`.

## Pasos
1. Inspecciona `EstudianteController.php`, el modelo `Estudiante` y la vista
   Blade del listado de alumnos (y el buscador asociado) para ver cómo se
   almacena o identifica si el documento es `DNI` o `CE`. Resume en pocas
   líneas qué campo o regla existe hoy.
2. Si no existe un campo explícito de tipo de documento, detente y dime cómo
   están guardados los números en la tabla antes de inventar una regla (por
   ejemplo, si se distinguen por algún campo, longitud o cómo se registran).
3. Si sí está claro, aplica el cambio mínimo en la vista/controlador sin tocar
   migraciones ni hacer `git add .`.
4. Detente al terminar para probar y cierra con el bloque RESUMEN PARA CHATGPT.
