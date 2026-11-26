---
name: tungabot-filtrar-por-contexto-activo
description: >
  Hace que TungaBot, cuando el profesor tiene un contexto activo (grado,
  sección y curso), cuente y desglose únicamente las incidencias de ese
  contexto seleccionado y no de otras secciones ni cursos fuera de él.
---

# TungaBot: respetar el contexto activo del profesor

Proyecto: sis_incidencias (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue SIEMPRE CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no
reescribir archivos completos, no tocar migraciones ni hacer git add .

## Problema detectado
Al preguntar a TungaBot cuántas incidencias tiene un profesor con un contexto
activo (grado, sección y curso), el bot responde con el total general de su
alcance (incluyendo otras secciones como `PRUEBA_1ro B` o registros de otro
curso/sección) en vez de limitarse a lo marcado.

## Reglas a respetar
- Si hay contexto activo completo (grado, sección y curso del profesor):
  limitar los conteos y resúmenes del chatbot a ese contexto.
- Incluir las incidencias reservadas del contexto activo en los conteos, pero
  nunca exponer contenido/descripción ni nombres de alumnos de reservadas a la IA.
- No alterar reglas de Directora, Subdirector o Administrador, ni tocar
  `AuthController.php`, `IncidenciaController.php` o `IncidenciaPolicy.php`
  salvo que sea estrictamente necesario y esté claro.

## Pasos
1. Inspecciona `app/Services/ChatbotContextoService.php` y cómo obtiene el
   contexto activo del profesor. Resume en pocas líneas qué filtra hoy.
2. Aplica el cambio mínimo para que el resumen/conteo del chatbot respete el
   contexto activo del profesor cuando esté definido.
3. Actualiza o agrega pruebas si corresponde, sin hacer `git add .`.
4. Detente al terminar para probar y cierra con el bloque RESUMEN PARA CHATGPT.