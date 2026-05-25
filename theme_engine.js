(function() {
    // 🎨 Ultra Universal Theme Engine v2.5 (With Traxen SaaS Premium Theme!)
    const themes = ['light', 'dark', 'oled', 'traxen'];
    let theme = localStorage.getItem('erp_theme') || 'dark';
    if (!themes.includes(theme)) theme = 'dark';
    
    document.documentElement.setAttribute('data-theme', theme);

    const style = document.createElement('style');
    style.innerHTML = `
        /* 🟠 THEME 1: WHITE & ORANGE (Day Mode) */
        :root[data-theme="light"] {
            --primary: #f97316; 
            --primary-glow: rgba(249, 115, 22, 0.3);
            --secondary: #0ea5e9;
            --bg: #f8fafc;
            --surface: rgba(255, 255, 255, 0.95);
            --border: rgba(0, 0, 0, 0.08);
            --text: #0f172a;
            --text-muted: #64748b;
            --text-dim: #64748b;
            --header-bg: rgba(255, 255, 255, 0.85);
            --card-base: rgba(255, 255, 255, 0.6);
            --nav-dock-bg: rgba(255, 255, 255, 0.98);
            --input-bg: #ffffff;
            --body-gradient: radial-gradient(circle at top right, #fff7ed, #f8fafc);
        }

        /* 🟣 THEME 2: DARK PURPLE (Night Mode) */
        :root[data-theme="dark"] {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.4);
            --secondary: #06b6d4;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-muted: #94a3b8;
            --text-dim: #94a3b8;
            --header-bg: rgba(3, 7, 18, 0.7);
            --card-base: rgba(30, 41, 59, 0.3);
            --nav-dock-bg: rgba(15, 23, 42, 0.85);
            --input-bg: rgba(15, 23, 42, 0.4);
            --body-gradient: radial-gradient(circle at top right, #1e1b4b, #030712);
        }

        /* 🌑 THEME 3: OLED BLACK (Midnight Gold) */
        :root[data-theme="oled"] {
            --primary: #eab308;
            --primary-glow: rgba(234, 179, 8, 0.4);
            --secondary: #10b981;
            --bg: #000000;
            --surface: rgba(10, 10, 10, 0.98);
            --border: rgba(255, 255, 255, 0.12);
            --text: #ffffff;
            --text-muted: #a1a1aa;
            --text-dim: #a1a1aa;
            --header-bg: rgba(0, 0, 0, 0.9);
            --card-base: rgba(20, 20, 20, 0.8);
            --nav-dock-bg: rgba(5, 5, 5, 0.98);
            --input-bg: #000000;
            --body-gradient: linear-gradient(180deg, #000000 0%, #0a0a0a 100%);
        }

        /* 🧡 THEME 4: TRAXEN SAAS (Premium Day Orange & Blue) */
        :root[data-theme="traxen"] {
            --primary: #ea580c; 
            --primary-glow: rgba(234, 88, 12, 0.25);
            --secondary: #2563eb;
            --bg: #f8fafc;
            --surface: rgba(255, 255, 255, 0.95);
            --border: rgba(15, 23, 42, 0.08);
            --text: #0f172a;
            --text-muted: #475569;
            --text-dim: #475569;
            --header-bg: rgba(255, 255, 255, 0.9);
            --card-base: rgba(255, 255, 255, 0.9);
            --nav-dock-bg: rgba(255, 255, 255, 0.98);
            --input-bg: #ffffff;
            --body-gradient: radial-gradient(circle at top right, #fff7ed, #f8fafc);
        }

        /* Force Global Styles with High-Precedence Selectors */
        body { 
            background: var(--body-gradient) !important; 
            color: var(--text) !important; 
            transition: background 0.3s ease, color 0.3s ease;
        }
        
        header { 
            background: var(--header-bg) !important; 
            border-bottom: 1px solid var(--border) !important; 
        }

        h1, h2, h3, h4, h5, h6, th, td, .card-title, .data-value, .header-title, .nav-item, .btn-tool, .icon-btn, .header-title span {
            color: var(--text) !important;
        }

        .data-label, .field-label, .nav-label, .text-muted, .label-sec, .app-label, .section-title, .label, .field-note {
            color: var(--text-muted) !important;
        }

        .nav-item.active, .btn-modal.save, .fab, .nav-dock-item.active, .btn-primary {
            color: white !important;
            background: var(--primary) !important;
        }

        .sidebar, .nav-dock, aside {
            background: var(--nav-dock-bg) !important;
            border-color: var(--border) !important;
        }

        .glass-card, .kpi-chip, .profit-panel, .nav-dock, .suggestion-box, .modal-card, .data-card, .desktop-table-container { 
            background: var(--card-base, var(--surface)) !important; 
            border: 1px solid var(--border) !important; 
            color: var(--text) !important;
        }

        input, select, textarea, .input-field, .search-input, .select-field, .input-box, .field-input, .search-container input {
            background: var(--input-bg) !important;
            color: var(--text) !important;
            border: 1px solid var(--border) !important;
        }

        /* Update Dynamic Icon Colors */
        .icon-circle i, .section-title i, .back-link i {
            color: white !important;
        }
        
        :root[data-theme="light"] .icon-circle i, 
        :root[data-theme="light"] .section-title i, 
        :root[data-theme="light"] .back-link i,
        :root[data-theme="traxen"] .icon-circle i, 
        :root[data-theme="traxen"] .section-title i, 
        :root[data-theme="traxen"] .back-link i {
            color: white !important; /* Keep icons white on gradients */
        }
        
        .logo-box {
            background: linear-gradient(135deg, var(--primary), var(--secondary)) !important;
            box-shadow: 0 0 20px var(--primary-glow) !important;
        }
    `;
    document.head.appendChild(style);

    // 🔄 Cycle Toggle Function
    window.toggleTheme = function() {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        let idx = themes.indexOf(current);
        let next = themes[(idx + 1) % themes.length];
        
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('erp_theme', next);
        
        if(window.navigator.vibrate) window.navigator.vibrate([15, 5, 15]);
    };
})();
