Quiero que me ayudes a refactorizar la logica backend del modulo [NOMBRE_MODULO].

Alcance:
- Refactoriza controller, validacion, service y logica de negocio.
- No cambies rutas ni contratos de la vista salvo que sea estrictamente necesario.
- Manten compatibilidad con el modulo actual.

Instrucciones:
- Lee y aplica mis skills `soffiweb-laravel-architecture` y `laravel-best-practices`.
- Usa el patron del proyecto: Controller delgado + FormRequest + Service.
- Mueve queries, transacciones y reglas de negocio al service.
- Deja validacion en FormRequest.
- Evita refactors cosmeticos innecesarios.
- Si existe un modulo ya refactorizado similar, usalo como patron real.
- Todo debe quedar listo para produccion.

Flujo:
1. Lee las skills.
2. Revisa el controller actual y el modulo de referencia.
3. Detecta responsabilidades mal ubicadas.
4. Refactoriza a Controller + Request + Service.
5. Valida sintaxis y rutas afectadas.

Archivos:
- Controller actual: [RUTA_CONTROLLER]
- Modelo(s): [RUTAS_MODELOS]
- Vista principal relacionada: [RUTA_BLADE]
- Modulo backend de referencia: [RUTA_CONTROLLER_REFERENCIA]
