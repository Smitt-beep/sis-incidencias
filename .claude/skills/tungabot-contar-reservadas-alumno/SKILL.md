---
name: tungabot-contar-reservadas-alumno
description: >
  Corrige el conteo de TungaBot cuando se consulta cuántas incidencias tiene un
  alumno (incluyendo incidencias reservadas en el total numérico del profesor),
  sin enviar contenido ni nombres de alumnos de incidencias reservadas a la IA.
---

# TungaBot: contar incidencias reservadas sin exponer contenido

Proyecto: sis_incidencias (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no reescribir
archivos completos, no tocar migraciones ni hacer git add .

## Objetivo
Cuando se pregunta al chatbot por las incidencias de un alumno (ej. "cuántas
incidencias tiene el alumno elmer"), el total numérico debe incluir las
incidencias reservadas que correspondan al alcance del usuario/profesor, igual
que la lista y el dashboard.

## Reglas de privacidad (innegociables)
- Nunca envíes a la IA la descripción/contenido de incidencias reservadas.
- Nunca envíes nombres de alumnos asociados a incidencias reservadas al contexto de la IA.
- Solo suma en el conteo numérico cuando corresponda.

## Pasos
1. Inspecciona `app/Services/ChatbotContextoService.php` y el punto de entrada del chatbot. Resume en pocas líneas qué filtra al buscar por alumno o por profesor.
2. Corrige únicamente el conteo para que no excluya incidencias reservadas por error.
3. No toques `AuthController.php`, `ReporteProfesionalController.php`, `IncidenciaPolicy.php` ni `EstudianteController.php`.
4. Detente al terminar para probar y cierra con el bloque RESUMEN PARA CHATGPT.