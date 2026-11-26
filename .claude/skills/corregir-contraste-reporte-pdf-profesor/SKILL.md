---
name: corregir-contraste-reporte-pdf-profesor
description: >
  Corrige los textos, números y etiquetas blanqueados o de bajo contraste en
  el reporte profesional del profesor (tanto en pantalla como al descargar en
  PDF), manteniendo intactos los datos, cálculos y privacidad de reservadas.
---

# Corregir datos blanqueados en Reporte y PDF del profesor

Proyecto: sis_incidencias (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue SIEMPRE CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no
reescribir archivos completos, no tocar migraciones ni hacer git add .

## Problema detectado
Al generar el reporte profesional del profesor y al descargarlo en PDF, varios
números y etiquetas aparecen blanqueados o con muy bajo contraste (casi
ilegibles sobre fondo claro), como los conteos de Leve/Intermedia/Grave, las
filas de Comparativa por sección y las etiquetas/valores de Estado de
incidencias.

## Reglas a respetar
- No modificar cálculos, conteos ni reglas de privacidad de incidencias
  reservadas.
- Asegurar contraste claro y legible tanto en la vista del reporte como en el
  PDF descargado (revisando estilos inline, clases CSS, `@media print` y la
  plantilla/generación PDF correspondiente).
- No romper otros roles (Directora, Subdirector, Administrador).

## Pasos
1. Inspecciona `ReporteProfesionalController.php`, las vistas Blade del reporte
   y del PDF, y sus estilos CSS asociados. Resume en pocas líneas qué está
   causando el bajo contraste / blanqueado.
2. Aplica el cambio mínimo necesario para que todos los textos, números y
   etiquetas tengan contraste legible en pantalla y en PDF.
3. No hagas `git add .`. Detente al terminar para probar y cierra con el bloque
   RESUMEN PARA CHATGPT.