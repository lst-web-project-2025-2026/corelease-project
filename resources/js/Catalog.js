document.addEventListener('DOMContentLoaded', () => {
    const modeToggle = document.getElementById('modeToggle');
    const themeDots = document.querySelectorAll('.dot');
    const body = document.body;

    // 1. Dark Mode Logic
    const toggleDarkMode = () => {
        body.classList.toggle('dark-mode');
        const isDark = body.classList.contains('dark-mode');
        modeToggle.innerText = isDark ? '☀️' : '🌙';
        localStorage.setItem('darkMode', isDark);
    };

    modeToggle.addEventListener('click', toggleDarkMode);

    // 2. Color Theme Logic
    const setTheme = (themeName) => {
        // Remove existing theme classes
        body.classList.forEach(className => {
            if (className.startsWith('theme-')) {
                body.classList.remove(className);
            }
        });
        
        // Add new theme
        body.classList.add(`theme-${themeName}`);
        localStorage.setItem('themeColor', themeName);
    };

    themeDots.forEach(dot => {
        dot.addEventListener('click', () => {
            const theme = dot.getAttribute('data-theme');
            setTheme(theme);
        });
    });

    // 3. Load Saved Preferences
    const loadPreferences = () => {
        if (localStorage.getItem('darkMode') === 'true') {
            body.classList.add('dark-mode');
            modeToggle.innerText = '☀️';
        }

        const savedTheme = localStorage.getItem('themeColor');
        if (savedTheme) {
            setTheme(savedTheme);
        }
    };

    loadPreferences();
});