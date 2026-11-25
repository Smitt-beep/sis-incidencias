---
name: reestructuracion-docente-dashboard-reportes
description: >
  Skill de arquitectura e implementación incremental (fase por fase) en Laravel + PostgreSQL
  para reestructurar el registro docente sin grado/curso/sección previos, habilitar controles
  dinámicos de grado/sección/curso en el Dashboard del profesor (iniciando en cero),
  flexibilizar documentos de identidad de alumnos (DNI/CE/extranjero) y construir el módulo
  de Reporte Profesional estadístico (5 grados -> cursos -> secciones múltiples).
---

# Skill Técnica: Reestructuración Docente, Dashboard Dinámico y Reporte Profesional (`sis_incidencias`)

## 1. Contexto del Problema y Realidad Operativa (Léelo antes de tocar código)
Estás trabajando en **`sis_incidencias`** (Laravel + PostgreSQL + Bootstrap/Chart.js) para un colegio real con **~700 estudiantes** y 4 roles (`Administrador`, `Directora`, `Subdirector`, `Profesor`).

**Por qué hacemos este cambio (contexto real):**
1. El colegio **no dispone de una matriz cerrada** que vincule qué profesor dicta qué curso en qué sección. Solo existen, por separado, los datos de los profesores y un padrón Excel de ~700 alumnos (con documento, grado y sección).
2. Un mismo profesor puede dictar en **varios grados**, **varias secciones** y **varios cursos**, e incluso cambiar de sección según el día o actividad. Además, si un docente se retira más adelante, el Administrador edita esa cuenta (nombre, correo, contraseña, etc.) para asignarla al reemplazo.
3. En el padrón real hay estudiantes peruanos con **DNI (8 dígitos)** y estudiantes extranjeros con **Carné de Extranjería (CE)** u otros documentos alfanuméricos o de distinta longitud.
4. Los profesores necesitan un apartado de **Reporte Profesional** (orientado a exponer estadísticas ante la Directora) donde puedan navegar **5 grados → cursos → múltiples secciones** y obtener indicadores estadísticos claros.

---

## 2. Protocolo de Pensamiento y Precaución Obligatorio (IA ↔ IA)
Antes de escribir o modificar una sola línea de código en cualquier fase:
1. **Inspección real del repositorio:** lee primero `routes/web.php`, `AuthController.php`, `UserController.php`, `IncidenciaController.php`, `EstudianteController.php`, modelos (`User`, `Estudiante`, `Seccion`, `Curso`, `Incidencia`), migraciones recientes y vistas (`login.blade.php`, registro, `dashboard.blade.php`, layouts y vistas de grados/reportes). **Nunca asumas nombres de columnas, tablas ni rutas sin verificarlos.**
2. **Análisis de impacto en Base de Datos antes de crear o eliminar tablas/columnas:**
   - **Prohibido borrar columnas o tablas destructivamente (`dropColumn` / `dropIfExists`) sin auditar todas sus referencias** en controladores, modelos, policies, seeders, tests y vistas. Si una columna como `grado` o `curso` en `users` o asignaciones deja de pedirse en el registro, evalúa primero hacerla `nullable()` o dejar de exigirla en validación antes de eliminarla, para no romper registros históricos ni consultas existentes.
   - Si propones tablas nuevas (por ejemplo, pivotes opcionales de preferencia/asignación docente), justifica por qué son necesarias, cómo se comportan cuando están vacías y qué índices/FKs llevan en PostgreSQL.
3. **Ejecución estricta FASE POR FASE:**
   - **Nunca apliques las 4 fases de golpe.**
   - Al terminar una fase, detente, entrega un resumen claro de archivos tocados, riesgos mitigados y **un plan de pruebas concreto** (navegador + PostgreSQL/Artisan) para que el desarrollador verifique antes de pasar a la siguiente fase.
4. **Blindaje PostgreSQL y Escala (~700 alumnos):**
   - Normaliza strings vacíos `""` a `null` antes de consultar o guardar en PostgreSQL (evita `SQLSTATE[22P02]` en enteros/fechas).
   - Evita consultas `N+1` (`with([...])`), usa paginación en listados grandes y protege cualquier división por cero (`$total > 0 ? ... : 0`).
   - Respeta siempre la privacidad de actas reservadas (`es_reservada = true`): nunca expongas contenido confidencial al `Profesor` ni al `Administrador`.

---

## 3. Plan de Trabajo por Fases (Ejecutar en Orden)

### FASE 1: Limpieza del Registro Docente + Flexibilización de Documento del Alumno (DNI / CE / Extranjero)
**Objetivo:** Desacoplar grado, curso y sección del formulario público de registro/inscripción y permitir documentos de identidad nacionales y extranjeros en estudiantes.

**Alcance técnico:**
1. **Registro / Creación de cuenta:**
   - Inspecciona la vista de registro y `UserController` (o controlador equivalente).
   - Elimina del formulario de inscripción los campos de **grado**, **curso** y **sección**.
   - Mantén el flujo intacto de creación de cuenta con estado `Pendiente` hasta aprobación del `Administrador` y el login actual por DNI + contraseña en `AuthController`.
   - Revisa `IncidenciaController` y `EstudianteController`: si alguna validación exige `asignaciones_docentes` rígidas para un profesor recién aprobado sin asignaciones previas, adáptala para que el profesor pueda operar con las secciones y cursos disponibles sin quedar bloqueado.
2. **Documento del alumno (`estudiantes.dni` / formularios):**
   - Localiza todas las validaciones que exigen `digits:8` para estudiantes (en `EstudianteController`, requests, importadores o vistas Blade con `maxlength="8"` / `pattern="[0-9]{8}"`).
   - Cambia la validación a formato flexible (por ejemplo: `string`, `min:6`, `max:20`, alfanumérico/guiones permitidos según corresponda, manteniendo unicidad `unique:estudiantes,dni` ignorando el registro actual al editar).
   - Verifica en migraciones que la columna en PostgreSQL tenga longitud suficiente (`varchar(20)` o similar); si es `varchar(8)`, crea una migración segura para ampliarla sin pérdida de datos.

**Criterio de parada Fase 1:** Reportar cambios, migración (si aplica) y pruebas de registro de profesor + creación/edición de alumno con DNI de 8 dígitos y con CE/documento extranjero. Esperar confirmación antes de Fase 2.

---

### FASE 2: Dashboard Dinámico del Profesor (Controles de Grado, Sección y Curso + Inicio en Cero)
**Objetivo:** Reestructurar `AuthController::dashboard()` y `dashboard.blade.php` para que el dashboard del profesor parta en **cero** y responda dinámicamente según el **grado**, **sección** y **curso** seleccionados internamente.

**Alcance técnico:**
1. **Controles en el Dashboard del Profesor (`dashboard.blade.php`):**
   - Coloca en la parte superior del dashboard del profesor un panel limpio de selección con controles para **Grado**, **Sección** y **Curso**, más acciones para **Aplicar** y **Limpiar selección**.
   - Filtra dinámicamente las secciones disponibles según el grado elegido (en frontend con JSON precargado de grados/secciones o recarga limpia) para evitar combinaciones imposibles (ej. *1er grado + Sección de 5to*).
2. **Comportamiento "En Cero" por defecto (`AuthController.php`):**
   - Cuando el profesor entra sin haber seleccionado el contexto requerido (grado/sección/curso), **no cargues métricas globales ni mezcladas**:
     - Las 4 tarjetas de resumen (`tarjetasResumen`) deben devolver `0` (`total = 0`, `finalizadas = 0`, `abiertas = 0`, `pendientes = 0`, `en_proceso = 0`, `graves_activas = 0`, `mes_actual = 0`, `variacion_mes = null`, `porcentaje_finalizadas = 0`).
     - Los gráficos (`datosDashboardProfesor`) deben devolver estructuras válidas con arrays en `0` y mostrar un estado guiado (*"Selecciona grado, sección y curso para visualizar tus estadísticas"*).
   - Cuando sí hay selección válida:
     - Normaliza y valida los IDs recibidos (`grado`, `seccion_id`, `curso_id`).
     - Consulta `Incidencia` filtrando por el contexto seleccionado (y respetando la privacidad de `es_reservada = true`).
     - Si la combinación elegida tiene `0` incidencias, los gráficos y tarjetas deben renderizar en `0` sin errores de JavaScript ni `null` en PHP.
3. **Cero regresiones en otros roles y en Chart.js:**
   - Mantén intacto el flujo de `Administrador`, `Directora` y `Subdirector` (`datosDashboardAdmin()`).
   - Evita cargar `Chart.js` duplicado en `dashboard.blade.php` y blinda las lecturas JS con valores por defecto (`?? 0`, `?? []`).
   - Evalúa qué secciones de gráficos inferiores (como el resumen global de los 5 grados) corresponden al profesor cuando aún está en cero vs. cuando ya filtró un grado/sección/curso, para no contradecir el estado "en cero".

**Criterio de parada Fase 2:** Reportar análisis de edge cases (sin selección, selección sin datos, cambio de sección/curso, rol Admin/Directora intacto) y plan de pruebas. Esperar confirmación antes de Fase 3.

---

### FASE 3: Nuevo Apartado "Reporte Profesional" (5 Grados → Cursos → Secciones Múltiples → Estadísticas)
**Objetivo:** Construir el módulo de **Reporte Profesional** para que los profesores puedan seleccionar **Grado → Curso → una o varias Secciones** y generar un reporte estadístico listo para exponer a la Directora.

**Alcance técnico:**
1. **Arquitectura de navegación y rutas:**
   - Revisa cómo está estructurada actualmente la vista de grados/menú lateral y define la ruta y controlador dedicados para **Reporte Profesional** (accesible por `Profesor`, y evalúa si `Directora`/`Subdirector`/`Administrador` también lo visualizan).
   - Organiza la interfaz en 3 niveles claros:
     1. **Los 5 Grados** (*Primer grado* a *Quinto grado*).
     2. **Cursos dentro del grado** (desplegable/acordeón o panel interactivo).
     3. **Secciones dentro de cada curso** con checkboxes/selección múltiple (ej. *1er grado → Matemática → marcar Sección A y Sección B*) + botón para generar el reporte estadístico.
2. **Motor estadístico y tolerancia a nulos:**
   - Valida que se haya elegido al menos un curso y una o más secciones pertenecientes al grado seleccionado.
   - Calcula estadísticas claras y útiles para exposición:
     - Total de incidencias en el curso y secciones elegidas.
     - Desglose por **gravedad** (`Leve`, `Intermedia`, `Grave` con cantidades y porcentajes).
     - Desglose por **estado** (`Pendiente`, `En Proceso`, `Finalizado`).
     - Comparativa **por cada sección seleccionada** (ej. Sección A vs. Sección B) y evolución mensual/semanal.
   - Excluye el contenido sensible de `es_reservada = true` (o aplica la regla vigente del colegio) y maneja `curso_id IS NULL` o secciones sin datos con `0` sin errores.
3. **Presentación visual lista para exponer (y opción de imprimir/exportar si aporta valor sin romper tiempos):**
   - Tarjetas KPI, tabla comparativa por sección y gráficos limpios con Chart.js, con estilos preparados para proyección/exposición ante la Directora.

**Criterio de parada Fase 3:** Reportar rutas, controlador, vista, consultas SQL/Eloquent y pruebas con combinaciones de 1 sección, múltiples secciones y secciones sin datos.

---

## 4. Estructura Obligatoria de Cada Respuesta de Claude Code
En cada fase, responde siempre en este orden:
1. **Diagnóstico del código actual y decisiones de arquitectura** (qué archivos existen, qué se reutiliza y qué NO se borra para evitar regresiones).
2. **Riesgos y edge cases detectados** (PostgreSQL, ~700 alumnos, nulos, roles cruzados).
3. **Implementación limpia** (migraciones si hacen falta, controlador, rutas y vistas Blade).
4. **Checklist de verificación paso a paso** antes de pasar a la siguiente fase.