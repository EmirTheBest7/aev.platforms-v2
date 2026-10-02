// Rail menu: toggle button <-> panel, Escape and outside-click to close.
// Without JS the panel stays closed and the brand link still reaches "/".
const toggle = document.querySelector('.rail__toggle');
const menu = document.getElementById('site-menu');

if (toggle && menu) {
  const setOpen = (open) => {
    toggle.setAttribute('aria-expanded', String(open));
    menu.classList.toggle('is-open', open);
  };

  toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
      setOpen(false);
      toggle.focus();
    }
  });

  document.addEventListener('click', (event) => {
    if (!menu.contains(event.target) && !toggle.contains(event.target)) setOpen(false);
  });
}
