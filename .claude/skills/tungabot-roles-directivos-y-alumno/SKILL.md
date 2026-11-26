---
name: tungabot-roles-directivos-y-alumno
description: >
  Permite que TungaBot responda consultas de incidencias por alumno para
  Directora, Subdirector y Administrador (incluyendo conteo de reservadas sin
  exponer contenido), y que en Profesor con contexto activo cuente solo lo
  correspondiente a su grado, sección y curso.
---

# TungaBot: acceso por alumno para Directora, Subdirector y Admin

Proyecto: sis_incidencias (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue SIEMPRE CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no
reescribir archivos completos, no tocar migraciones ni hacer git add .

## Problema detectado
Al preguntar a TungaBot cuántas incidencias tiene un alumno (ejemplo: "cuantas
incidencias tiene elmer"), responde "No tengo información disponible sobre
incidencias de Elmer para tu rol" estando en perfil Directora, pese a que
Directora, Subdirector y Administrador deben tener acceso institucional.

## Reglas de rol y privacidad
- Roles Directora, Subdirector y Administrador: deben poder consultar y
  obtener el conteo de incidencias por alumno en todo el alcance que les
  corresponda, contando las reservadas pero sin exponer contenido ni
  descripciones de incidencias reservadas.
- Rol Profesor: si tiene contexto activo (grado, sección y curso), limitar
  el conteo del alumno a ese contexto; nunca enviar contenido ni nombres de
  alumnos vinculados a incidencias reservadas al contexto de la IA.

## Pasos
1. Inspecciona `app/Services/ChatbotContextoService.php` (y puntos donde se
   evalúa el rol y la búsqueda por alumno). Resume en pocas líneas qué bloquea
   hoy a Directora, Subdirector y Admin.
2. Aplica el cambio mínimo para habilitar a Directora, Subdirector y Admin y
   mantener el filtro de contexto en Profesor.
3. Ejecuta las pruebas enfocadas del chatbot y no hagas `git add .`.
4. Detente al terminar para probar y cierra con el bloque RESUMEN PARA CHATGPT.