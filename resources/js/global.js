class ThemeManager {
    constructor() {
        this.theme = localStorage.getItem("theme") || "dark";
        this.accent = JSON.parse(localStorage.getItem("accent")) || {
            h: 217,
            s: 91,
            l: 60,
        };

        this.init();
    }

    init() {
        document.documentElement.setAttribute("data-theme", this.theme);
        this.applyAccent(this.accent);

        // Listen for system changes if no preference is set (optional enhancement)
        window
            .matchMedia("(prefers-color-scheme: dark)")
            .addEventListener("change", (e) => {
                if (!localStorage.getItem("theme")) {
                    this.setTheme(e.matches ? "dark" : "light");
                }
            });
    }

    setTheme(theme) {
        this.theme = theme;
        document.documentElement.setAttribute("data-theme", theme);
        localStorage.setItem("theme", theme);
    }

    toggleTheme() {
        this.setTheme(this.theme === "dark" ? "light" : "dark");
    }

    applyAccent(accent) {
        this.accent = accent;
        document.documentElement.style.setProperty("--accent-h", accent.h);
        document.documentElement.style.setProperty(
            "--accent-s",
            `${accent.s}%`,
        );
        document.documentElement.style.setProperty(
            "--accent-l",
            `${accent.l}%`,
        );
        localStorage.setItem("accent", JSON.stringify(accent));
    }
}

window.themeManager = new ThemeManager();
window.toggleDarkMode = function () {
    window.themeManager.toggleTheme();
};
window.setAccent = function (h, s, l) {
    window.themeManager.applyAccent({ h, s, l });
};
