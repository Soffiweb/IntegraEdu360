# Soffiweb UI - Shared JS

Usar este JS compartido cuando el layout incluya topbar con menú de usuario, sidebar responsive o select buscador.

```javascript
function swToggleTheme() {
  const root = document.getElementById('root') || document.documentElement;
  root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
  localStorage.setItem('theme', root.dataset.theme);
}

function swToggleSub(trigger) {
  const submenu = trigger.nextElementSibling;
  const open = trigger.classList.toggle('open');

  trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
  if (submenu && submenu.classList.contains('sw-sb-sub')) {
    submenu.classList.toggle('open', open);
  }
}

function swToggleSidebar(trigger) {
  const open = document.body.classList.toggle('sw-sidebar-open');
  trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
  trigger.setAttribute('aria-label', open ? 'Cerrar menu' : 'Abrir menu');
}

function swCloseSidebar() {
  document.body.classList.remove('sw-sidebar-open');
  const trigger = document.querySelector('.sw-sidebar-toggle');
  if (trigger) {
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-label', 'Abrir menu');
  }
}

function swToggleUserMenu(trigger) {
  const menu = document.getElementById(trigger.getAttribute('aria-controls'));
  const open = menu && !menu.classList.contains('open');

  swCloseUserMenu();
  if (open) {
    menu.classList.add('open');
    trigger.setAttribute('aria-expanded', 'true');
  }
}

function swCloseUserMenu(restoreFocus = false) {
  const trigger = document.querySelector('.sw-user-trigger');
  const menu = document.querySelector('.sw-user-dropdown');
  if (!trigger || !menu || !menu.classList.contains('open')) {
    return;
  }

  menu.classList.remove('open');
  trigger.setAttribute('aria-expanded', 'false');
  if (restoreFocus) {
    trigger.focus();
  }
}

function swToggleSS(id) {
  const wrapper = document.getElementById(id);
  const trigger = wrapper.querySelector('.sw-ss-trigger');
  const dropdown = wrapper.querySelector('.sw-ss-dropdown');
  const open = dropdown.classList.toggle('open');

  trigger.classList.toggle('open', open);
  if (open) {
    setTimeout(() => wrapper.querySelector('.sw-ss-search')?.focus(), 40);
  }
}

function swSelectOpt(id, option) {
  const wrapper = document.getElementById(id);
  wrapper.querySelectorAll('.sw-ss-opt').forEach(item => item.classList.remove('selected'));
  option.classList.add('selected');
  const value = wrapper.querySelector('.sw-ss-trigger-val');
  value.textContent = option.dataset.val || option.textContent.trim();
  value.classList.remove('placeholder');
  swToggleSS(id);
}

function swFilterSS(id, query) {
  const list = document.getElementById(id + '-list');
  let found = 0;

  list.querySelectorAll('.sw-ss-opt').forEach(option => {
    const match = option.textContent.toLowerCase().includes(query.toLowerCase());
    option.style.display = match ? '' : 'none';
    if (match) {
      found++;
    }
  });

  let empty = list.querySelector('.sw-ss-empty');
  if (!found && !empty) {
    empty = document.createElement('div');
    empty.className = 'sw-ss-empty';
    empty.textContent = 'Sin resultados';
    list.appendChild(empty);
  }
  if (empty) {
    empty.style.display = found ? 'none' : '';
  }
}

document.addEventListener('click', event => {
  document.querySelectorAll('.sw-ss-wrap').forEach(wrapper => {
    if (!wrapper.contains(event.target)) {
      wrapper.querySelector('.sw-ss-trigger')?.classList.remove('open');
      wrapper.querySelector('.sw-ss-dropdown')?.classList.remove('open');
    }
  });

  if (!event.target.closest('.sw-user-menu')) {
    swCloseUserMenu();
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme) {
    const root = document.getElementById('root') || document.documentElement;
    root.dataset.theme = savedTheme;
  }

  document.querySelectorAll('.sw-user-avatar-img').forEach(image => {
    const fallback = () => image.parentElement.classList.add('is-fallback');
    image.addEventListener('error', fallback);
    if (image.complete && image.naturalWidth === 0) {
      fallback();
    }
  });

  document.querySelectorAll('.sw-sidebar a').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth < 768) {
        swCloseSidebar();
      }
    });
  });
});

document.addEventListener('keydown', event => {
  if (event.key === 'Escape') {
    swCloseSidebar();
    swCloseUserMenu(true);
  }
});

window.addEventListener('resize', () => {
  if (window.innerWidth >= 768) {
    swCloseSidebar();
  }
});
```

## Reglas

- Los submenus usan `<button aria-expanded aria-controls>`; no `div onclick`.
- El menú de usuario cierra por clic externo y por `Escape`.
- En móvil, el sidebar cierra al navegar y al pulsar el backdrop.
- Si hay avatar remoto o cargado, conservar el fallback por error de imagen.
