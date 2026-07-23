# Módulos del sistema — Soffiweb Design System

> Leer este archivo cuando: trabajes en un módulo específico, necesites saber el color de card-icon de un módulo, o definas componentes propios de un módulo.

---

## Resumen de módulos

Soffiweb tiene 5 módulos. **Todos comparten el mismo sistema de diseño base.** Las diferencias entre módulos son únicamente de contenido y de color de card-icon — nunca de tipografía, espaciado, radios o sombras.

| Módulo | Color card-icon | Nombre en nav | Ícono sugerido |
|---|---|---|---|
| Facturación | navy | Facturas | `file-text` |
| Contabilidad | steel | Contabilidad | `bar-chart-2` |
| Citas médicas | rosa | Citas | `calendar` |
| Nóminas | navy | Nóminas | `users` |
| Bodega | steel | Bodega | `package` |

---

## Facturación

**Color de módulo:** navy  
**Módulo más crítico** — es donde el diseño tuvo más deuda técnica históricamente.

Componentes característicos:
- Panel de agregar ítem (grid de 8 columnas con producto, cantidad, precio, IVA, descuento, subsidio)
- Tabla de detalle de ventas con columnas alineadas a la derecha (DM Mono)
- Panel de totales con IVA 0%, IVA 15%, subtotales y TOTAL A COBRAR
- Exportación a PDF del comprobante

Campos numéricos que usan DM Mono obligatoriamente:
- Número de venta (`001-001-00000XXXX`)
- Precio venta, IVA%, Descuento, Subsidio
- Subtotal IVA, Subtotal 0%, Total a cobrar

---

## Contabilidad

**Color de módulo:** steel  
Componentes característicos:
- Tabla de cuentas con árbol de plan contable
- Gráficos de balance (barras y líneas)
- Asientos contables — tabla de debe/haber en DM Mono

---

## Citas médicas

**Color de módulo:** rosa  
Este módulo usa rosa como color de card-icon porque las citas médicas tienen un tono más cálido y de cuidado que el resto de módulos de gestión.

Componentes característicos:
- Vista de calendario (semanal/mensual)
- Tarjeta de paciente con datos de contacto y historial
- Formulario de agendar cita — fecha, hora, médico, motivo
- Lista de citas del día con estados (pendiente, confirmada, atendida, cancelada)

Estados de cita:
```
pendiente:   chip-warning
confirmada:  chip-info
atendida:    chip-success
cancelada:   chip-error
```

---

## Nóminas

**Color de módulo:** navy  
Componentes característicos:
- Tabla de empleados con cargo, salario base, deducciones, aportes
- Cálculo de roles de pago — tabla con columnas numéricas en DM Mono
- Desglose de aportes al IESS (o equivalente regional)
- Exportación de roles en PDF

Campos que usan DM Mono:
- Salario base, horas extra, comisiones
- IESS personal, IESS patronal
- Impuesto a la renta
- Líquido a recibir

---

## Bodega

**Color de módulo:** steel  
Componentes característicos:
- Tabla de inventario con producto, código, stock, stock mínimo, precio
- Control de entradas y salidas de bodega
- Alerta de stock bajo — chip-warning cuando `stock <= stock_minimo`
- Transferencias entre bodegas (si aplica)

Estados de stock:
```
disponible:    chip-success  (stock > stock_minimo)
stock bajo:    chip-warning  (stock <= stock_minimo y stock > 0)
sin stock:     chip-error    (stock = 0)
```

---

## Reglas comunes a todos los módulos

1. El nav-badge (número de notificaciones pendientes) usa siempre `rosa-500` como background — es el único elemento de acento que aparece en el sidebar oscuro.
2. El chip de "Paso X de Y" en card-headers usa `chip-info` — igual en todos los módulos.
3. Los empty states de tabla tienen siempre el mismo patrón: ícono del módulo + título + subtítulo descriptivo.
4. Los botones de acción principal de cada módulo siguen el mismo orden: Ghost "Cancelar" → Ghost "Borrador" → Primary "[Acción principal]".
