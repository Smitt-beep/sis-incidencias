---
name: tungabot-guia-navegacion-pdf
description: >
  Hace que TungaBot guíe con rutas y apartados reales de sis_incidencias (en vez
  de respuestas genéricas) cuando le pregunten cómo sacar un PDF o dónde entrar
  según el rol del usuario, respetando permisos y privacidad de reservadas.
---

# TungaBot: guía real de navegación y exportación/impresión de PDF

Proyecto: sis_incidencias (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no reescribir
archivos completos, no tocar migraciones ni hacer git add .

## Problema detectado
Cuando un usuario pregunta cómo sacar incidencias en PDF (por ejemplo, de un
alumno), TungaBot responde con pasos genéricos ("buscar Exportar a PDF o
Imprimir en el menú de opciones") sin indicar en qué apartado real entrar ni
qué aplica según su rol.

## Pasos
1. Inspecciona `app/Services/ChatbotContextoService.php`, el controlador/prompt
   del chatbot y las rutas/vistas relacionadas a PDF o reportes de incidencias/alumnos.
   Resume en pocas líneas cómo se generan los PDFs reales y qué sabe hoy el bot
   sobre navegación/exportación.
2. Agrega al contexto o instrucciones de TungaBot la guía real de apartados y
   reglas según el rol del usuario autenticado (Directora, Subdirector,
   Administrador, Profesor), sin inventar botones o funciones que no existan.
3. Aclara en la guía cuándo aplica cada opción y recuerda las reglas de
   privacidad/alcance (por ejemplo, incidencias reservadas y permisos por rol),
   sin exponer contenido ni nombres de alumnos de incidencias reservadas a la IA.
4. No toques módulos ajenos ni hagas `git add .`. Detente al terminar para
   probar y cierra con el bloque RESUMEN PARA CHATGPT.