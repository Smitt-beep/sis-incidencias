---
name: editar-incidencia-como-acta
description: Permite que la Directora y el Subdirector editen cualquier incidencia (incluidas las de profesores) pasándola al formato de acta, con elección reservada/pública y botón Volver.
---

# Editar cualquier incidencia en el formato de acta (Directora y Subdirector)

Proyecto: Sistema de Incidencias I.E. Tungasuca (Laravel, PostgreSQL, Blade, Bootstrap).
Sigue SIEMPRE las reglas de CLAUDE.md: inspeccionar antes de modificar, cambios mínimos, no reescribir archivos completos, no tocar migraciones antiguas, no usar migrate:fresh/reset/rollback, no borrar datos. Si algo afecta la base de datos o la arquitectura y no está claro, detente y pregunta.

## Qué se quiere

Hoy la Directora y el Subdirector solo pueden editar las actas que ellos mismos crearon. Es ilógico: son la máxima autoridad. Nuevo comportamiento:

1. Directora y Subdirector pueden pulsar **Editar** en TODAS las incidencias (las de profesores y las actas).
2. Al pulsar Editar en una incidencia de un profesor se abre el **formato de acta** (`incidencias/acta-create.blade.php`), ya llenado con los datos que existen.
3. Los datos que el sistema no tiene (domicilio, teléfono, etc.) los rellena la directora a mano. Siguen siendo opcionales como hoy.
4. Debe haber un botón **Volver** dentro de esa pantalla. Volver no guarda nada y deja la incidencia exactamente como estaba.
5. En el formato de acta la directora **elige** si la incidencia queda **Reservada** (privada: el profesor solo ve alumno, fecha y la etiqueta «Reservado», no puede abrirla ni imprimirla) o **Pública** (el profesor la sigue viendo y puede editar lo suyo como antes). Por defecto, la opción es Pública para incidencias que vienen de un profesor, y Reservada para actas nuevas.

Ejemplo de negocio: un profesor tiene un conflicto con un alumno y registra la incidencia a su favor. La directora la edita y la vuelve reservada. En otro caso, la incidencia es grave pero no involucra al profesor: la directora la deja pública.

## Decisiones ya tomadas (no volver a preguntar)

- Es la MISMA incidencia, no una copia. Se agrega la ficha de acta a esa incidencia (relación `acta`, tablas `actas_incidencia` y `acta_agresores`) y se conserva `usuario_id` (el profesor que la registró).
- Reservada o pública la decide la directora con una casilla/selector en el formulario. Se guarda en `incidencias.es_reservada`.
- Supuesto: el alumno de la incidencia entra al formato como **agresor preseleccionado** (tipo estudiante), y la directora puede cambiarlo a agredido y añadir más personas.
- Las reservadas siguen contando en los números del dashboard y del chatbot, pero su contenido y los nombres nunca se envían a la IA.

## Pasos

### 1. Inspeccionar (no modificar todavía)
- `routes/web.php`: rutas `incidencias.acta.create|store|edit|update|pdf` (todas con `role:Directora,Subdirector`) y `incidencias.edit|update` (hoy solo `Administrador,Profesor`).
- `app/Http/Controllers/IncidenciaController.php`: `createActa`, `editActa` (hoy hace `abort_unless($incidencia->acta, 404)`), `storeActa` (hoy fuerza gravedad Grave y estado Pendiente), `updateActa`, `validatedActaData`, `actaAttributes`, `agresorAttributes`, `pdfActa`.
- `app/Policies/IncidenciaPolicy.php`: `update`, `view`, `delete`, `imprimirActa` (hoy exige `es_reservada`), `puedeGestionarActa`.
- `resources/views/incidencias/acta-create.blade.php`, `acta-pdf.blade.php`, `index.blade.php`, `show.blade.php`.
- `app/Models/Incidencia.php`, `ActaIncidencia.php`, `ActaAgresor.php`.

Resume en pocas líneas lo que encontraste antes de cambiar nada.

### 2. Política (`IncidenciaPolicy`)
- Directora y Subdirector pueden ver y gestionar el acta de CUALQUIER incidencia (reservada o no).
- `imprimirActa`: permitido a Directora y Subdirector cuando la incidencia tiene acta (ya no exige `es_reservada`). Los profesores y el administrador nunca imprimen.
- No cambiar quién puede eliminar (solo Directora para reservadas; Administrador y Directora para el resto).
- Un profesor sigue editando solo sus incidencias NO reservadas, como antes.

### 3. Controlador
- `editActa`: si la incidencia NO tiene acta, no devolver 404. Abrir el formulario en modo «convertir», con los datos de la incidencia ya cargados (alumno y su sección, fecha y hora, gravedad, estado, descripción como relato si el formato tiene ese campo, alumno como agresor preseleccionado). No crear ni guardar nada al abrir.
- `updateActa`: si no existe acta, crearla (por ejemplo con `updateOrCreate` o creando la relación dentro de la transacción existente, manteniendo `lockForUpdate`). Si ya existe, actualizarla como hoy. Guardar `es_reservada` según la elección de la directora. No modificar `usuario_id` ni borrar fotografías ni tipos de la incidencia original.
- En modo convertir respetar gravedad y estado actuales de la incidencia, salvo que la directora los cambie en el formulario. No forzar Grave/Pendiente (eso es solo para actas nuevas en `storeActa`).
- Validar `es_reservada` como booleano. Mantener todas las validaciones y el manejo de errores existentes.

### 4. Vista del formato (`acta-create.blade.php`)
- Reutilizar el mismo formulario para crear, editar y convertir. Añadir el control «Visibilidad»: Reservada / Pública.
- Botón **Volver** junto al botón Guardar: enlace a `incidencias.index` (o a la lista desde la que se entró). Sin envío de formulario, sin cambios en la base de datos.
- Si es una conversión, mostrar un aviso corto: «Estás editando una incidencia registrada por el profesor X. Los datos se trasladaron a este formato.»

### 5. Lista y botones (`incidencias/index.blade.php`)
- Para Directora y Subdirector, el botón de cada fila (tanto en la tabla normal como en la de Urgencias, que son dos bloques distintos) se llama **Editar** y lleva a `incidencias.acta.edit` en todas las incidencias. Para las que ya son actas se mantiene el comportamiento actual.
- Los profesores y el administrador siguen con su botón Editar actual.
- Mantener `@continue` al final de la fila de urgencias (sin él, cada incidencia se dibuja dos veces).
- Mantener la fila enmascarada para profesores en incidencias reservadas.

### 6. Rutas
- Cambios mínimos. Las rutas `incidencias.acta.*` ya están restringidas a Directora y Subdirector; no abrirlas a otros roles. Añadir solo lo que haga falta.

### 7. Privacidad (revisar sí o sí)
- Un profesor nunca debe poder abrir, ver ni imprimir una incidencia reservada ni el contenido del acta, ni por URL directa (probar con la ruta `incidencias/{id}`, `.../acta/edit` y `.../acta/pdf`). Para incidencias públicas con acta, tampoco debe ver los datos del acta: `show.blade.php` no debe mostrarlos a quien no sea Directora o Subdirector.
- El chatbot (`ChatbotContextoService`) no debe enviar a la IA ni el contenido del acta ni nombres de alumnos de incidencias reservadas. Verificar que sigue así.

### 8. Pruebas
Probar a mano y, si es razonable, con tests de Feature:
1. Directora abre Editar en una incidencia de un profesor → ve el formato lleno con sus datos.
2. Volver no cambia nada en la base de datos.
3. Guardar como Pública → el profesor sigue viéndola completa y puede editar lo suyo.
4. Guardar como Reservada → el profesor solo ve alumno, fecha y «Reservado», y recibe 403/404 al abrirla.
5. Subdirector puede hacer lo mismo; profesor y administrador no acceden a las rutas del acta.
6. Imprimir el acta funciona para Directora/Subdirector en pública y reservada, y falla para el resto.
7. Los números del dashboard y del chatbot no cambian por convertir (la incidencia ya contaba).

## Al terminar

Indica archivos modificados, qué se modificó, por qué y cómo comprobarlo. No hacer `git add .`. El usuario hace commit y push por su cuenta después de probar. Cierra SIEMPRE con el bloque:

### RESUMEN PARA CHATGPT

- Archivo(s) revisado(s)
- Archivo(s) modificado(s)
- Cambios realizados
- Problemas o errores encontrados
- Decisiones o dudas pendientes
- Resultado final