(() => {

        const createModal = document.getElementById('create-institucion-modal');
        const editModal = document.getElementById('edit-institucion-modal');
        const createForm = document.getElementById('create-institucion-form');
        const editForm = document.getElementById('edit-institucion-form');
        const openCreateButtons = document.querySelectorAll('[data-open-create]');
        const closeCreateButtons = document.querySelectorAll('[data-close-create]');
        const closeEditButtons = document.querySelectorAll('[data-close-edit]');

        openCreateButtons.forEach((button) => {
            button.addEventListener('click', () => createModal.showModal());
        });

        closeCreateButtons.forEach((button) => {
            button.addEventListener('click', () => createModal.close());
        });

        closeEditButtons.forEach((button) => {
            button.addEventListener('click', () => editModal?.close());
        });

        const syncFormFields = (form) => {
            if (!form) {
                return;
            }

            ['codigo_amie', 'distrito_id', 'sostenimiento_id', 'regimen_id', 'provincia', 'canton', 'estado'].forEach((name) => {
                const field = form.querySelector(`[name="${name}"]`);

                if (!field) {
                    return;
                }

                if (field.tagName === 'SELECT') {
                    field.value = field.options[field.selectedIndex]?.value ?? field.value;
                    return;
                }

                field.value = field.value.trim();
            });
        };

        createForm?.addEventListener('submit', () => syncFormFields(createForm));
        editForm?.addEventListener('submit', () => syncFormFields(editForm));

        createModal?.addEventListener('click', (event) => {
            const rect = createModal.getBoundingClientRect();
            const inside = rect.top <= event.clientY && event.clientY <= rect.top + rect.height && rect.left <= event.clientX && event.clientX <= rect.left + rect.width;
            if (!inside) {
                createModal.close();
            }
        });

        editModal?.addEventListener('click', (event) => {
            const rect = editModal.getBoundingClientRect();
            const inside = rect.top <= event.clientY && event.clientY <= rect.top + rect.height && rect.left <= event.clientX && event.clientX <= rect.left + rect.width;
            if (!inside) {
                editModal.close();
            }
        });


        if (editModal) editModal.showModal();
        if (createModal?.dataset.open === "true") createModal.showModal();

})();
