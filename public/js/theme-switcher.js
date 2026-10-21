/**
 * Sistem Laboratorium Lingkungan Terpadu 2026
 * Theme Switcher: Default Sistem, Light, and Dark Mode
 */
(function() {
    function getStoredTheme() {
        try {
            return localStorage.getItem('theme_preference') || 'system';
        } catch (e) {
            return 'system';
        }
    }

    function isDarkActive(theme) {
        if (theme === 'dark') return true;
        if (theme === 'light') return false;
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    function applyTheme(theme) {
        const dark = isDarkActive(theme);
        if (dark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        try {
            document.cookie = 'theme_preference=' + encodeURIComponent(theme) + ';path=/;max-age=31536000;SameSite=Lax';
        } catch (e) {}
    }

    // Apply immediately to avoid FOUC
    const initialTheme = getStoredTheme();
    applyTheme(initialTheme);

    // Listen to device / OS color scheme changes for 'system' option
    if (window.matchMedia) {
        try {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (getStoredTheme() === 'system') {
                    if (e.matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                    window.dispatchEvent(new CustomEvent('theme-changed', {
                        detail: { theme: 'system', isDark: e.matches }
                    }));
                }
            });
        } catch (e) {}
    }

    window.getThemePreference = getStoredTheme;
    window.isDarkModeActive = function() {
        return document.documentElement.classList.contains('dark');
    };
    window.setThemePreference = function(theme) {
        if (!['system', 'light', 'dark'].includes(theme)) {
            theme = 'system';
        }
        try {
            localStorage.setItem('theme_preference', theme);
        } catch (e) {}
        applyTheme(theme);
        window.dispatchEvent(new CustomEvent('theme-changed', {
            detail: { theme: theme, isDark: isDarkActive(theme) }
        }));
    };

    // Alpine.js component helper for theme picker
    window.themePicker = function() {
        return {
            open: false,
            theme: getStoredTheme(),
            init() {
                window.addEventListener('theme-changed', (e) => {
                    this.theme = e.detail.theme;
                });
            },
            setTheme(newTheme) {
                this.theme = newTheme;
                window.setThemePreference(newTheme);
            },
            get icon() {
                if (this.theme === 'light') return 'fa-solid fa-sun';
                if (this.theme === 'dark') return 'fa-solid fa-moon';
                return 'fa-solid fa-desktop';
            },
            get label() {
                if (this.theme === 'light') return 'Light';
                if (this.theme === 'dark') return 'Dark';
                return 'Sistem';
            }
        };
    };
})();
