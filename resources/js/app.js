import { initializeTranslations } from './translation';

const root = document.documentElement;
const themeToggles = document.querySelectorAll('[data-theme-toggle]');

function applyTheme(theme) {
    root.dataset.theme = theme;
    document.body.classList.toggle('dark', theme === 'dark');
    themeToggles.forEach(button => button.setAttribute('aria-pressed', String(theme === 'dark')));
}

applyTheme(root.dataset.theme === 'dark' ? 'dark' : 'light');

themeToggles.forEach(button => {
    button.addEventListener('click', () => {
        const theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
        applyTheme(theme);

        try {
            localStorage.setItem('resolveiq-theme', theme);
        } catch {}
    });
});

document.querySelectorAll('[data-password-toggle]').forEach(button => {
    const input = document.getElementById(button.dataset.passwordToggle);
    if (! input) return;

    button.setAttribute('aria-controls', input.id);
    button.setAttribute('aria-pressed', 'false');
    button.addEventListener('click', () => {
        const showPassword = input.type === 'password';
        input.type = showPassword ? 'text' : 'password';
        button.classList.toggle('is-visible', showPassword);
        button.setAttribute('aria-pressed', String(showPassword));
        button.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
    });
});

document.querySelectorAll('.demo-account-form').forEach(form => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.querySelector('.demo-action').textContent = 'Signing in…';
    });
});

const redirect = document.querySelector('[data-redirect-after]');
if (redirect) window.setTimeout(() => window.location.assign(redirect.href), Number(redirect.dataset.redirectAfter));

const sidebar = document.getElementById('appSidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarClose = document.getElementById('sidebarClose');
const sidebarBackdrop = document.getElementById('sidebarBackdrop');
const main = document.getElementById('main-content');
const narrow = window.matchMedia('(max-width: 860px)');

if (sidebar && sidebarToggle && sidebarClose && sidebarBackdrop && main) {
    function setSidebar(open, restoreFocus = false) {
        document.body.classList.toggle('sidebar-open', open);
        sidebarBackdrop.hidden = ! open;
        sidebarToggle.setAttribute('aria-expanded', String(open));
        sidebar.inert = narrow.matches && ! open;
        main.inert = open;

        if (open) sidebarClose.focus();
        if (restoreFocus && narrow.matches) sidebarToggle.focus();
    }

    setSidebar(false);
    sidebarToggle.addEventListener('click', () => setSidebar(true));
    sidebarClose.addEventListener('click', () => setSidebar(false, true));
    sidebarBackdrop.addEventListener('click', () => setSidebar(false, true));
    narrow.addEventListener('change', () => setSidebar(false));

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && ! sidebarBackdrop.hidden) setSidebar(false, true);
    });
}

const notifications = document.getElementById('notificationsDropdown');
const notificationsToggle = document.getElementById('notificationsToggle');

if (notifications && notificationsToggle) {
    function setNotifications(open) {
        notifications.classList.toggle('open', open);
        notificationsToggle.setAttribute('aria-expanded', String(open));
    }

    notificationsToggle.addEventListener('click', () => setNotifications(! notifications.classList.contains('open')));
    document.addEventListener('click', event => {
        if (! notifications.contains(event.target)) setNotifications(false);
    });
    notifications.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            setNotifications(false);
            notificationsToggle.focus();
        }
    });
}

initializeTranslations();
