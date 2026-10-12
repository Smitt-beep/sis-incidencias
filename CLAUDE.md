# Sistema de Incidencias - I.E. Tungasuca

## 1. Contexto del proyecto

Este proyecto es un sistema web de gestión de incidencias escolares.

Tecnologías principales:

- Laravel
- PHP
- PostgreSQL
- Blade
- Bootstrap
- JavaScript

El sistema manejará principalmente los siguientes roles:

- Administrador
- Profesor
- Directora
- Subdirector

El inicio de sesión utiliza DNI y contraseña.

---

## 2. Regla principal de trabajo

Antes de modificar cualquier archivo:

1. Revisar primero su contenido actual.
2. Entender cómo está funcionando.
3. Modificar únicamente lo necesario.
4. No reescribir archivos completos si no es necesario.
5. No eliminar funcionalidades existentes sin autorización.
6. Mantener el código sencillo, ordenado y comentado.
7. Respetar la arquitectura actual del proyecto.

No realizar cambios grandes automáticamente.

---

## 3. Reglas de Base de Datos

La base de datos utilizada es PostgreSQL.

IMPORTANTE:

- No modificar tablas manualmente desde PostgreSQL.
- Los cambios estructurales deben realizarse mediante migraciones de Laravel.
- No modificar migraciones antiguas que ya fueron ejecutadas.
- Para cambios nuevos crear una nueva migración.
- No ejecutar `migrate:fresh`.
- No ejecutar `migrate:reset`.
- No ejecutar `rollback`.
- No eliminar tablas sin autorización.
- No eliminar datos existentes sin autorización.

Los datos iniciales del sistema deben manejarse mediante Seeders cuando corresponda.

---

## 4. Estructura funcional actual

### roles

- id
- nombre_rol

Roles existentes:

- Administrador
- Profesor
- Directora
- Subdirector

Relación:

roles 1:N users

---

### users

Campos importantes:

- id
- name
- email
- dni
- password
- rol_id
- estado_aprobacion
- activo
- created_at
- updated_at

`rol_id` referencia:

roles.id

`estado_aprobacion` puede utilizar estados como:

- Pendiente
- Aprobado
- Rechazado

`activo` permite desactivar una cuenta sin eliminarla.

---

### asignaciones_docentes

Relaciona profesores con cursos y secciones.

Campos:

- id
- usuario_id
- curso_id
- seccion_id
- created_at
- updated_at

Relaciones:

usuario_id -> users.id

curso_id -> cursos.id

seccion_id -> secciones.id

Existe una restricción UNIQUE para:

usuario_id + curso_id + seccion_id

---

### cursos

- id
- nombre

IMPORTANTE:

No cargar datos manualmente todavía.

La institución entregará posiblemente una API con información académica.

---

### secciones

- id
- grado
- seccion

IMPORTANTE:

No cargar ni modificar datos académicos todavía hasta revisar la API.

---

### estudiantes

- id
- dni
- nombre
- seccion_id

Relación:

seccion_id -> secciones.id

IMPORTANTE:

Los estudiantes posiblemente serán obtenidos desde una API externa.

No crear datos ficticios ni modificar su estructura para adaptarla a una API hasta conocer la documentación real.

---

### gravedades

Valores actuales:

- Leve
- Intermedia
- Grave

---

### estados

Valores actuales:

- Pendiente
- En Proceso
- Finalizado

---

### incidencias

Estructura actual:

- id
- estudiante_id
- usuario_id
- curso_id
- fecha
- hora
- descripcion
- gravedad_id
- estado_id

Relaciones:

estudiante_id -> estudiantes.id

usuario_id -> users.id

curso_id -> cursos.id

gravedad_id -> gravedades.id

estado_id -> estados.id

No volver a agregar:

- tipo
- gravedad como texto
- estado como texto
- justificacion

---

## 5. Reglas funcionales

### Administrador

Puede administrar usuarios.

Debe poder:

- revisar cuentas registradas
- aprobar usuarios
- rechazar usuarios
- desactivar usuarios

No eliminar usuarios históricos innecesariamente.

---

### Profesor

El profesor puede tener asignados:

- un curso
- una o varias secciones

Estas relaciones se manejan mediante:

`asignaciones_docentes`

Al registrar una incidencia:

- el profesor NO debe seleccionar libremente cualquier curso
- el curso debe obtenerse de su asignación
- solo debe visualizar sus secciones asignadas
- debe poder buscar estudiantes correspondientes a dichas secciones

---

### Directora y Subdirector

Principalmente deben poder consultar:

- alumnos
- historial de incidencias
- incidencias urgentes

Una urgencia NO es una tabla independiente.

Una incidencia urgente corresponde a:

gravedad = Grave

Y estado:

- Pendiente
- En Proceso

Las incidencias Finalizadas no deben aparecer como urgencias activas.

---

## 6. Historial del estudiante

NO crear una tabla `historial`.

El historial se obtiene consultando las incidencias mediante:

`estudiante_id`

Debe permitir conocer información como:

- fecha
- profesor que registró la incidencia
- gravedad
- descripción
- estado

---

## 7. API académica pendiente

La institución entregará una API.

Todavía NO conocemos exactamente su estructura.

Puede contener:

- estudiantes
- cursos
- secciones
- otros datos académicos

REGLA IMPORTANTE:

No inventar endpoints.

No inventar estructura JSON.

No crear integración con la API hasta recibir documentación, URL o ejemplo real de respuesta.

No poblar manualmente cursos, secciones o estudiantes mientras esta decisión esté pendiente.

---

## 8. Fecha y hora de incidencias

Actualmente existen:

- fecha
- hora

Todavía está pendiente confirmar si estos datos serán ingresados manualmente o generados automáticamente.

No eliminar ni modificar estos campos hasta recibir confirmación.

---

## 9. Dashboard

El dashboard se desarrollará al final.

Está previsto mostrar estadísticas como:

- incidencias mensuales
- Pendientes
- En Proceso
- Finalizadas
- niveles de gravedad
- tendencias mediante gráficos

No implementar todavía sin autorización.

--

## 10. Forma de realizar cambios

Cuando se solicite implementar algo:

1. Inspeccionar los archivos relacionados.
2. Explicar brevemente qué archivos serán modificados.
3. Realizar únicamente los cambios necesarios.
4. No modificar módulos que no estén relacionados.
5. Mantener nombres y estructura existentes siempre que sea posible.
6. No ejecutar comandos destructivos.
7. Al terminar indicar:
   - archivos modificados
   - qué se modificó
   - por qué
   - cómo comprobar que funciona

Si existe una duda que pueda afectar la arquitectura o la base de datos, detenerse y preguntar antes de asumir.
---

## 11. Resumen obligatorio al finalizar

Después de realizar o revisar cualquier tarea, puedes explicar el proceso normalmente, pero SIEMPRE debes terminar tu respuesta con un bloque llamado:

### RESUMEN PARA CHATGPT

Este resumen debe ser corto y contener únicamente:

- Archivo(s) revisado(s)
- Archivo(s) modificado(s)
- Cambios realizados
- Problemas o errores encontrados
- Decisiones o dudas pendientes
- Resultado final

No incluir explicaciones largas dentro de este resumen.

Si no se modificó ningún archivo, indicarlo claramente.

Este bloque será utilizado para comunicar rápidamente el resultado del trabajo a otro asistente.