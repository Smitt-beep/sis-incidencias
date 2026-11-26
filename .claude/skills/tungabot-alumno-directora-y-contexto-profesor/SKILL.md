---
name: tungabot-alumno-directora-y-contexto-profesor
description: >
  Corrige TungaBot para que en perfil Directora encuentre y cuente las
  incidencias de un alumno (incluyendo reservadas sin exponer su contenido),
  y en perfil Profesor con contexto activo (grado, sección y curso) cuente las
  incidencias del alumno filtradas estrictamente a ese grado, sección y curso.
---

# TungaBot: consulta por alumno en Directora y en Profesor con contexto activo

Proyecto: sis_incidencias (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue SIEMPRE CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no
reescribir archivos completos, no tocar migraciones ni hacer git add .

## Problemas detectados
1. En perfil Directora, al preguntar "cuantas incidencias tiene elmer" (cuando
   existen incidencias para ese alumno, ej. 5), TungaBot responde que no tiene
   información disponible sobre ese alumno.
2. En perfil Profesor, cuando tiene seleccionado Grado, Sección y Curso y
   pregunta cuántas incidencias tiene un alumno, debe calcular el conteo
   restringido a ese Grado, Sección y Curso seleccionados.

## Reglas de privacidad (innegociables)
- Nunca envíes a la IA la descripción/contenido de incidencias reservadas.
- Nunca envíes nombres de alumnos vinculados a incidencias reservadas al
  contexto de la IA.
- Incluir en el conteo numérico las incidencias reservadas que correspondan
  al alcance/contexto, manteniendo oculto su contenido.

## Pasos
1. Inspecciona `app/Services/ChatbotContextoService.php` y
   `ChatbotMensajeController.php`. Resume en pocas líneas por qué Directora
   no ve/encuentra al alumno y cómo se filtra por alumno cuando hay contexto
   activo del profesor.
2. Aplica el cambio mínimo para corregir ambos casos en una sola pasada sin
   romper otros roles ni pruebas existentes.
3. Ejecuta las pruebas relevantes y no hagas `git add .`.
4. Detente al terminar para probar y cierra con el bloque RESUMEN PARA CHATGPT.