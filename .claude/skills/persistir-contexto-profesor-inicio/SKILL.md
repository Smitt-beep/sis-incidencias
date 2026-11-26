---
name: persistir-contexto-profesor-inicio
description: >
  Mantiene persistente la selección de Grado, Sección y Curso del profesor en
  Inicio (dashboard) al navegar a otros apartados y regresar, limpiándose solo
  cuando el usuario pulsa "Limpiar selección".
---

# Persistir contexto de Grado, Sección y Curso en Inicio (Profesor)

Proyecto: sis_incidencias (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue SIEMPRE CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no
reescribir archivos completos, no tocar migraciones ni hacer git add .

## Problema detectado
1. En el dashboard de Inicio del profesor, al seleccionar Grado, Sección y Curso
   y pulsar "Aplicar", se muestran las estadísticas de ese contexto.
2. Al ir a otro apartado (ej. Incidencias o Alumnos) y volver a Inicio, los
   selectores se limpian solos y el dashboard vuelve a cero sin haber pulsado
   "Limpiar selección".
3. También se ha notado diferencia entre el total mensual del contexto en Inicio
   (ej. 11 incidencias) frente a lo que se lista en Incidencias o Alumnos.

## Comportamiento esperado en Inicio (Profesor)
- Una vez que el profesor selecciona Grado, Sección y Curso y pulsa "Aplicar",
  esa selección debe persistir al cambiar de sección y regresar a Inicio.
- Solo debe borrarse/limpiarse (volviendo al estado inicial sin selección)
  cuando el usuario pulse explícitamente "Limpiar selección".
- Respetar el comportamiento de «dashboard en cero sin selección completa»
  cuando no haya selección o tras pulsar "Limpiar selección".

## Pasos
1. Inspecciona `AuthController.php`, las vistas/scripts del dashboard del profesor
   y el flujo del botón "Limpiar selección". Resume en pocas líneas cómo se
   guarda/lee y se limpia hoy el contexto.
2. Aplica el cambio mínimo para que la selección de Grado, Sección y Curso se
   conserve al volver a Inicio y solo se borre al pulsar "Limpiar selección".
3. Revisa si hay algún filtro de contexto mal aplicado al consultar Incidencias o
   Alumnos, sin romper las reglas de privacidad ni otros roles.
4. No hagas `git add .`. Detente al terminar para probar y cierra con el bloque
   RESUMEN PARA CHATGPT.