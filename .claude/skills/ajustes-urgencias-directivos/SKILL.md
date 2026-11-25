---
name: ajustes-urgencias-directivos
description: >
  Corrige el módulo de Urgencias y el menú lateral para Directora y Subdirector
  en sis_incidencias (sin alterar Administrador): fecha y hora juntas en una sola
  celda sin columnas corridas ni botones salidos, activación exclusiva del botón
  Urgencias en el menú lateral y eliminación del apartado "Todas las incidencias"
  para esos roles.
---

# Skill Técnica: Ajustes de Urgencias y Menú Directivo (`sis_incidencias`)

## 1. Alcance y Regla de Roles
- Estos cambios aplican específicamente a los roles **Directora** y **Subdirector** (y a la vista/menú que usan).
- **NO alteres la estructura del `Administrador`**, ni toques `Grado`, `Reporte Profesional`, `Motivo` ni el flujo de `Profesor`.
- Inspecciona primero los archivos reales (rutas, controlador de urgencias, vista de urgencias y layout del sidebar) y detente al terminar para probar.

---

## 2. Correcciones Obligatorias

### A. Fecha y hora juntas + alineación de tabla y acciones
- En la tabla de **Urgencias**, cada fila debe mostrar **fecha y hora juntas** dentro de la misma celda de `Fecha y hora` (nunca la hora suelta/desplazada en otra parte ni desfasada entre filas).
- Verifica que los valores de `ID`, `Estudiante`, `Grado / Sección`, `Curso`, `Fecha y hora` y `Acciones` correspondan exactamente a cada registro y no se corran entre filas.
- Asegúrate de que los botones de `Acciones` (`Ver`, `Editar acta`, `Eliminar`) quepan completos dentro del ancho disponible sin desbordarse ni depender de scroll horizontal molesto.

### B. Solo un ítem activo en el menú lateral al entrar a Urgencias
- Revisa la lógica de clase activa (`active`) del sidebar para `Incidencias` y `Urgencias`.
- Al estar en **Urgencias**, solo debe iluminarse en celeste/azul el botón **Urgencias**; **Incidencias** NO debe encenderse al mismo tiempo.

### C. Eliminar "Todas las incidencias" para Directora y Subdirector
- En el menú lateral de **Directora** y **Subdirector**, `Incidencias` y `Todas las incidencias` duplican el mismo apartado.
- Elimina el enlace/apartado **Todas las incidencias** para esos roles y deja solamente **Incidencias** (sin tocar la estructura del `Administrador`).

---

## 3. Cierre Esperado
Al terminar, indica brevemente:
1. Archivos revisados y modificados.
2. Confirmación de los 3 puntos aplicados (fecha/hora juntas y filas/acciones alineadas, solo `Urgencias` activo al entrar ahí, y `Todas las incidencias` eliminado para Directora y Subdirector sin tocar `Administrador`).
3. Pasos rápidos para probar en el navegador.