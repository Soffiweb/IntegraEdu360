const filtrosEstudiantes = document.getElementById('estudiantes-curso-filtros');
if (filtrosEstudiantes) {
    document.getElementById('estudiantes-periodo').addEventListener('change', () => {
        document.getElementById('estudiantes-curso').disabled = true;
        document.getElementById('estudiantes-paralelo').disabled = true;
        filtrosEstudiantes.requestSubmit();
    });
    document.getElementById('estudiantes-curso').addEventListener('change', () => {
        document.getElementById('estudiantes-paralelo').disabled = true;
        filtrosEstudiantes.requestSubmit();
    });
}
