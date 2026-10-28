(() => {
    const form = document.getElementById('asignacion-form');
    if (!form) return;
    const course = document.getElementById('paralelo_id');
    const subject = document.getElementById('asignatura_id');
    const options = [...subject.options].map(option => option.cloneNode(true));
    const filterSubjects = () => {
        const value = subject.value;
        const available = options.filter(option => !option.value || option.dataset.curso === course.selectedOptions[0]?.dataset.curso);
        subject.replaceChildren(...available.map(option => option.cloneNode(true)));
        document.getElementById('curriculo-ayuda').textContent = !course.value ? 'Selecciona primero un curso y paralelo.' : available.length === 1 ? 'Este curso no tiene asignaturas vinculadas en su currículo.' : 'Asignaturas del currículo del curso seleccionado.';
        subject.value = available.some(option => option.value === value) ? value : '';
    };
    const updateType = () => {
        const type = form.querySelector('input[name="tipo"]:checked')?.value;
        const academic = type === 'carga';
        const hoursInput = document.getElementById('horas_semanales');
        hoursInput.min = type === 'comision' ? '0' : '0.25';
        hoursInput.max = academic ? '25' : '168';
        form.querySelectorAll('[data-carga]').forEach(group => {
            group.hidden = !academic;
            group.querySelector('select').disabled = !academic;
            group.querySelector('select').required = academic;
        });
    };
    form.querySelectorAll('input[name="tipo"]').forEach(radio => radio.addEventListener('change', updateType));
    course.addEventListener('change', filterSubjects);
    filterSubjects();
    updateType();
    const hours = document.getElementById('horas_semanales');
    const preview = document.getElementById('asignacion-preview');
    hours.addEventListener('input', () => {
        const value = Number(hours.value);
        preview.textContent = form.querySelector('input[name="tipo"]:checked')?.value === 'comision' ? 'Esta comisión no suma períodos pedagógicos de clase.' : value > 0 ? `${value.toLocaleString('es-EC')} horas por semana para esta responsabilidad.` : 'Solo la carga horaria se suma al máximo de 25 períodos pedagógicos.';
    });
    document.querySelectorAll('[data-delete-assignment]').forEach(deleteForm => {
        deleteForm.addEventListener('submit', event => {
            if (!window.confirm('¿Retirar esta asignación del docente?')) event.preventDefault();
        });
    });
})();
