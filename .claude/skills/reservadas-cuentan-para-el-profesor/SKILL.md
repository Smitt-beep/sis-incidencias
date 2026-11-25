---
name: reservadas-cuentan-para-el-profesor
description: Unifica el criterio de incidencias reservadas: cuentan siempre en los números del profesor (dashboard, reporte, chatbot y lista); solo el contenido se oculta.
---

# Las incidencias reservadas cuentan para el profesor (el contenido sigue oculto)

Proyecto: Sistema de Incidencias I.E. Tungasuca (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue SIEMPRE CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no reescribir archivos completos, no tocar migraciones, no usar migrate:fresh/reset/rollback, no borrar datos. Si algo afecta la base de datos o la arquitectura y no está claro, detente y pregunta.

## Regla de negocio (ya decidida, no volver a preguntar)

Una incidencia reservada es una incidencia más. Para el PROFESOR que la registró:

1. **Cuenta siempre** en sus números: tarjetas y gráficos del dashboard, reporte profesional, chatbot (TungaBot) y lista de incidencias.
2. **Lo único que cambia es el contenido.** Si es reservada, el profesor solo ve alumno, fecha y la etiqueta «Reservado»: no puede abrirla, editarla ni imprimirla, ni por URL directa.
3. Si la directora la deja **pública** (`es_reservada = false`), el profesor la ve completa como cualquier otra.
4. Cuando la directora convierte una incidencia de un profesor, se conserva su `usuario_id`, `curso_id` y el alumno; por eso sigue contando en el dashboard del profesor con el mismo grado, sección y curso.

Directora, Subdirector y Administrador ya cuentan todas las incidencias; no cambiar nada ahí.

## Estado actual del código (verificado en origin/main, commit 49100bb)

Hoy está inconsistente:
- Chatbot (`app/Services/ChatbotContextoService.php`): YA cuenta las reservadas en los totales del profesor (`base(..., true)`). No tocar.
- Lista (`IncidenciaController::index`): YA muestra al profesor sus incidencias y las reservadas de sus secciones, enmascaradas. No tocar.
- Dashboard del profesor (`app/Http/Controllers/AuthController.php`): EXCLUYE las reservadas en dos consultas. **Corregir.**
- Reporte profesional del profesor (`app/Http/Controllers/ReporteProfesionalController.php`): EXCLUYE las reservadas. **Corregir.**

## Cambios exactos

### 1. `AuthController.php` — borrar la línea `->where('es_reservada', false)` en DOS lugares
- En la consulta base de las tarjetas, dentro del `when($esProfesor, ...)`, justo debajo de `->where('curso_id', $contextoProfesor['curso_id'])`.
- En la consulta base de los gráficos (`$consultaBase`), justo debajo de `->where('curso_id', $contexto['curso_id'])`.
No tocar el filtro por `usuario_id`, curso ni sección del estudiante. No tocar el comportamiento de «dashboard en cero sin selección completa».

### 2. `ReporteProfesionalController.php` — borrar la línea `->where('es_reservada', false)`
Está en la consulta de `$incidencias`, debajo de `->where('curso_id', $curso->id)`. Este reporte solo muestra cantidades y gráficos (estado, gravedad, comparativa por sección, evolución mensual), nunca el detalle de una incidencia. Verifica que siga siendo así: si en algún punto el reporte muestra descripción, nombre de alumno u otro detalle por incidencia, esas filas deben omitirse o enmascararse para las reservadas.

### 3. Pruebas en `tests/Feature/TeacherAssignmentTest.php`
- Renombrar `test_professor_professional_report_supports_multiple_sections_and_excludes_reserved_actas` a `test_professor_professional_report_supports_multiple_sections_and_counts_reserved_without_showing_content`. El cuerpo no cambia: su `assertDontSee('Acta reservada')` sigue siendo válido porque el contenido nunca se muestra.
- Renombrar `test_professor_dashboard_excludes_reserved_actas_and_requires_matching_context` a `test_professor_dashboard_counts_reserved_actas_and_requires_matching_context`.
- En esa misma prueba, en la primera comprobación, cambiar `->assertSee('"total":1', false);` por `->assertSee('"total":2', false);` (la normal y la reservada cuentan). La segunda comprobación (`"total":0`, otra sección) no cambia.
- Añadir, si es razonable, una prueba nueva: un profesor con una incidencia reservada ve el número correcto en el dashboard, ve «Reservado» en la lista y recibe 403/404 al abrir `incidencias/{id}`.

## Lo que NO se debe cambiar

- `EstudianteController` (ficha/reporte PDF del alumno para el apoderado): sigue EXCLUYENDO las reservadas, porque ese documento sale de la institución.
- Políticas (`IncidenciaPolicy`): un profesor nunca ve, edita ni imprime una reservada.
- El chatbot no envía a la IA contenido ni nombres de alumnos de reservadas; solo cuenta en los números. No cambiar `ChatbotContextoService`.
- No tocar otros módulos ni la estructura de la base de datos. No hace falta ninguna migración.

## Comprobación

1. `php artisan test` debe pasar completo.
2. A mano: con la directora, convierte una incidencia de un profesor en Reservada. Entra como ese profesor y verifica que el número del dashboard NO baja, que en la lista aparece «Reservado» sin descripción ni botones de acción, y que abrir la incidencia por URL da error.
3. Pregunta al chatbot «¿cuántas incidencias tengo?» y compara con el dashboard (sin selección de aula puede variar; con grado, sección y curso elegidos deben coincidir en las que correspondan).
4. Con la directora, vuelve esa incidencia Pública: el profesor debe verla completa.

## Al terminar

Indica archivos modificados, qué se modificó, por qué y cómo comprobarlo. No hacer `git add .`. El usuario hace commit y push por su cuenta después de probar. Cierra SIEMPRE con el bloque:

### RESUMEN PARA CHATGPT

- Archivo(s) revisado(s)
- Archivo(s) modificado(s)
- Cambios realizados
- Problemas o errores encontrados
- Decisiones o dudas pendientes
- Resultado final