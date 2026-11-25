---
name: rediseno-urgencias
description: >
  Rediseña el apartado de Urgencias en sis_incidencias (Laravel + Bootstrap) para
  Directora, Subdirector y Administrador, mejorando la visibilidad de casos graves
  activos, mostrando el contador de urgencias activas, eliminando el scroll
  horizontal molesto de la tabla y los botones ocultos, limpiando textos en
  inglés y omitiendo campos inexistentes como "Motivo" o tarjetas duplicadas.
---

# Skill Técnica: Rediseño del Módulo de Urgencias (`sis_incidencias`)

## 1. Contexto y Objetivo
Vas a rediseñar la vista y presentación del módulo de **Urgencias** (incidencias graves en estado `Pendiente` o `En Proceso`) en **`sis_incidencias`** (Laravel + PostgreSQL + Bootstrap), pensada principalmente para la **Directora** (y usable también por `Subdirector` y `Administrador`).

El objetivo es que el apartado se sienta claro, prioritario y cómodo de operar sin arrastrar horizontalmente hasta el fondo de la tabla para ver datos o botones de acción.

---

## 2. Reglas Innegociables (Cero Inventos y Cero Regresiones)
1. **Solo datos y columnas reales:**
   - NO crees ni agregues la columna o campo **Motivo** ni textos descriptivos inventados de incidentes.
   - NO agregues tarjetas resumen duplicadas encima de la tabla para estados que ya están cubiertos por el filtro y el alcance de urgencias.
   - NO agregues botones extra no solicitados (como exportar).
   - El detalle completo de cada incidencia ya se consulta desde la acción **Ver** de cada fila.
2. **Contador de urgencias activas visible:**
   - Muestra el contador numérico de urgencias activas (casos graves `Pendiente` o `En Proceso`) en el menú lateral / cabecera del módulo usando el conteo real del controlador.
3. **Adiós al scroll horizontal molesto y acciones escondidas:**
   - Ajusta anchos, `table-layout` / `white-space` / truncado limpio de textos largos (como nombres o cursos de prueba) para que la tabla quepa bien en el ancho disponible y las columnas clave + botones de **Acciones** sean visibles de inmediato sin obligar a bajar hasta el final y deslizar a la derecha.
4. **Todo en español:**
   - Reemplaza cualquier texto de paginación o interfaz en inglés (como `Showing ... of ... results`) por español claro (`Mostrando ... de ... registros`, `Anterior`, `Siguiente`).
5. **No romper lo demás:**
   - Conserva el botón **`+ Registrar acta`** y todas las rutas/acciones reales de gestión de incidencias (`Ver`, cambio de estado, etc.), sin alterar `Grado`, `Reporte Profesional` ni el flujo de `Profesor`.
   - Mantén el blindaje de PostgreSQL (`""` -> `null` y protección ante división por cero) y respeta la privacidad de `es_reservada = true` (sin exponer contenido confidencial en el listado).

---

## 3. Inspección Previa Obligatoria
Antes de modificar código, revisa:
1. `routes/web.php` y el controlador/método que consulta y envía los datos de **Urgencias**.
2. La vista Blade de **Urgencias** y el layout del menú lateral (`resources/views/layouts/...`).
3. Las acciones disponibles por fila (ver detalle, editar/gestionar estado, etc.) y la paginación actual.

---

## 4. Plan de Implementación por Pasos
### Paso 1: Auditoría de datos y contador de urgencias
- Verifica qué consulta alimenta **Urgencias** (`grave` y estado `Pendiente` o `En Proceso`), qué campos reales tiene cada registro (`id`, `estudiante`, `grado`, `seccion`, `curso`, `fecha`, `estado`) y cómo se calcula el total de urgencias activas.
- Asegúrate de que `Curso` muestre el nombre real del curso o `—` cuando no tenga curso asignado, sin inventar motivos.

### Paso 2: Rediseño de la vista de Urgencias y acciones
- Aplica una estética clara de prioridad/urgencia alineada con la imagen de referencia aprobada y la paleta institucional del sistema.
- Conecta el badge numérico de urgencias activas al dato real.
- Deja columnas limpias (`ID`, `Estudiante`, `Grado / Sección`, `Curso`, `Fecha y hora`, `Acciones`), con textos largos bien contenidos para evitar el scroll horizontal molesto y mantener visibles los botones de acción (`Ver`, etc.).
- Traduce la paginación al español y detente al terminar para probar en el navegador.

---

## 5. Formato de Cierre Esperado
Al finalizar, entrega:
1. Archivos inspeccionados y modificados.
2. Confirmación de que **Motivo** y tarjetas duplicadas no se agregaron y que el contador de urgencias activas está conectado.
3. Checklist rápido de verificación (sin scroll horizontal molesto, acciones visibles, paginación en español y `+ Registrar acta` operativo).