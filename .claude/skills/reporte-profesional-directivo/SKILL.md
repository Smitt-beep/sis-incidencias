---
name: reporte-profesional-directivo
description: >
  Skill de implementación incremental para integrar el Reporte Profesional institucional
  (para Administrador, Directora y Subdirector) dentro del apartado existente de Grados
  sin eliminar ni alterar las funciones administrativas actuales ("Nueva sección" y
  "Editar secciones"), guiándose por las métricas globales de datosDashboardAdmin().
---

# Skill Técnica: Reporte Profesional Directivo dentro de Gestión de Grados (`sis_incidencias`)

## 1. Contexto y Reglas Innegociables (Léelo antes de tocar código)
Estás trabajando en **`sis_incidencias`** (Laravel + PostgreSQL + Bootstrap + Chart.js) para un colegio real con **~700 estudiantes** y 4 roles (`Administrador`, `Directora`, `Subdirector`, `Profesor`).

**Qué se necesita ahora:**
1. Los roles directivos/administrativos (**`Administrador`**, **`Directora`** y **`Subdirector`**) ya cuentan con el apartado **`Grados`** (`Gestión de Grados`), donde actualmente existen:
   - El botón superior **`Nueva sección`**.
   - La tabla con `Grado`, `Cursos` (`Ver cursos`) y `Acciones` (`Editar secciones`).
2. **NO SE DEBE ELIMINAR NI ROMPER NADA DE LO EXISTENTE:**
   - Tanto **`Nueva sección`** como **`Editar secciones`** (y sus rutas/formularios de creación y edición de secciones) están pensados para la gestión futura del colegio y deben permanecer **100% intactos y funcionales**.
3. **Qué debes agregar en ese apartado para estos 3 roles:**
   - Integrar el **Reporte Profesional Institucional** (con el mismo formato documento oficial A4 listo para imprimir/guardar en PDF ya implementado para profesores, reutilizando estilos/componentes cuando corresponda).
   - A diferencia del reporte del `Profesor` (que filtra sus propias incidencias por aula/sección específica), el reporte de `Administrador`, `Directora` y `Subdirector` debe guiarse por la lógica institucional de `AuthController::datosDashboardAdmin()` y `tarjetasResumen()`, permitiendo generar:
     - **Reporte General Institucional** (todos los grados o un grado específico, con desglose por curso, gravedad y estado).
     - **Reporte Específico por Grado / Curso / Secciones** (seleccionando un grado, opcionalmente un curso y una o varias secciones, o todas las secciones del grado).

---

## 2. Protocolo de Pensamiento y Precaución (IA ↔ IA)
1. **Inspección previa obligatoria:** lee primero `routes/web.php`, `SeccionController.php` (o controlador que renderiza `Grados`), `AuthController.php` (`datosDashboardAdmin()` y `tarjetasResumen()`), el controlador y vista del **Reporte Profesional del Profesor** (creado en la fase anterior), y la vista actual de `Grados`.
2. **Cero regresiones:**
   - No modifiques ni rompas el **Reporte Profesional del Profesor** ya aprobado.
   - No rompas las rutas ni botones de **`Nueva sección`** ni **`Editar secciones`** en `Gestión de Grados`.
3. **Privacidad y conteo institucional (`es_reservada = true`):**
   - Sigue exactamente la misma regla que `datosDashboardAdmin()` y `tarjetasResumen()` en `AuthController`: las actas reservadas **sí suman en los números estadísticos globales** de `Administrador`, `Directora` y `Subdirector`, pero **nunca** se muestran descripciones confidenciales ni contenido privado del acta en tablas o textos del reporte.
4. **Blindaje PostgreSQL y Chart.js:**
   - Normaliza inputs vacíos `""` a `null` antes de filtrar en PostgreSQL.
   - Protege divisiones por cero (`$total > 0 ? ... : 0`).
   - En el gráfico de evolución mensual (Ene–Dic), mantén siempre un contenedor con alto fijo (`height: 190px` a `210px` tanto en pantalla como en `@media print`), `maintainAspectRatio: false`, `responsive: true` y `maxBarThickness: 36` para evitar que el canvas se estire verticalmente al imprimir/PDF.

---

## 3. Plan de Implementación por Pasos (Ejecutar en Orden y Detenerse para Probar)

### PASO 1: Auditoría + Motor de Datos del Reporte Directivo
1. Inspecciona cómo está construido el reporte del profesor y qué controlador/vista maneja `Grados` para `Administrador`, `Directora` y `Subdirector`.
2. Define el alcance de filtros para el **Reporte Profesional Directivo**:
   - **Modo 1 — Reporte General / Por Grado:** permite elegir **Todos los grados (Institucional)** o **un Grado específico (1° a 5°)**, y opcionalmente filtrar por **Curso** o **Periodo/Año**.
   - **Modo 2 — Comparativa detallada:** si elige un Grado (o Grado + Curso + Secciones), muestra la comparativa por secciones (o por grados si eligió todos) y el ranking/desglose por cursos (igual que `porCursoPorGrado` en `datosDashboardAdmin()`).
3. Verifica que todas las incidencias activas (incluyendo el conteo numérico de `es_reservada = true`, sin exponer detalles privados) se calculen correctamente y soporten `0` incidencias sin errores.

### PASO 2: Integración en la Vista de `Grados` + Documento Imprimible A4
1. En la vista de `Grados` (y menú lateral si aplica, manteniendo claro que es el módulo de **Grados y Reportes** para `Administrador`, `Directora` y `Subdirector`):
   - **Conserva intactos** el botón **`Nueva sección`** arriba y la tabla de `Gestión de Grados` con **`Ver cursos`** y **`Editar secciones`**.
   - Añade de forma ordenada (por ejemplo, una tarjeta/panel superior de **"Generar Reporte Profesional Institucional"** y/o acceso directo desde cada grado) los controles para elegir el alcance (Todos los grados o Grado específico, Curso opcional, Secciones opcionales) y el botón **"Generar reporte"**.
2. Renderiza el **Documento Oficial de Reporte Profesional** (con `.no-print` en controles de navegación y barra de filtros, y botón **"Imprimir / Guardar PDF"**):
   - Membrete oficial (`SECTOR EDUCACIÓN · UGEL 04 COMAS / TUNGASUCA`, `INSTITUCIÓN EDUCATIVA TUNGASUCA`, código dinámico, fecha actual, año lectivo).
   - Contexto seleccionado (Alcance Institucional / Grado / Curso / Secciones + autoridad que emite el reporte: Directora, Subdirector o Administrador).
   - 4 tarjetas KPI (`TOTAL`, `LEVE`, `INTERMEDIA`, `GRAVE` con cantidades y porcentajes).
   - Bloque comparativo (tabla por Grado o por Sección + desglose por Curso) y `ESTADO DE INCIDENCIAS` (`Pendiente`, `En Proceso`, `Finalizado`).
   - Gráfico compacto de `EVOLUCIÓN MENSUAL DE INCIDENCIAS` (Ene–Dic) con badge de pico crítico y altura controlada (`maintainAspectRatio: false`).

---

## 4. Estructura Obligatoria de Respuesta de Claude Code
Al finalizar, entrega:
1. **Resumen de archivos inspeccionados y modificados.**
2. **Confirmación explícita de que `Nueva sección`, `Editar secciones` y el reporte del `Profesor` siguen intactos.**
3. **Checklist de verificación paso a paso** (como `Directora`, `Subdirector` y `Administrador`, incluyendo prueba de impresión/PDF).