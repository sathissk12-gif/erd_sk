<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>Cloud Master Console | SK LOGIC</title>
    
    <!-- Ultra Modern UI -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Security -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="theme_engine.js"></script>

    <style>
        :root {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.4);
            --secondary: #06b6d4;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.7);
            --border: rgba(255, 255, 255, 0.1);
            --text: #ffffff;
            --text-dim: #94a3b8;
            --text-muted: #94a3b8;
            --danger: #f43f5e;
            --success: #10b981;
            --warning: #f59e0b;
            --card-bg: rgba(30, 41, 59, 0.5);
            --batch-bar-bg: rgba(15, 23, 42, 0.95);
        }

        :root[data-theme="light"] {
            --text-dim: #64748b;
            --text-muted: #64748b;
            --card-bg: rgba(255, 255, 255, 0.6);
            --batch-bar-bg: rgba(255, 255, 255, 0.95);
        }
        :root[data-theme="dark"] {
            --text-dim: #94a3b8;
            --text-muted: #94a3b8;
            --card-bg: rgba(30, 41, 59, 0.3);
        }
        :root[data-theme="oled"] {
            --text-dim: #a1a1aa;
            --text-muted: #a1a1aa;
            --card-bg: rgba(20, 20, 20, 0.8);
        }
        :root[data-theme="traxen"] {
            --text-dim: #475569;
            --text-muted: #475569;
            --card-bg: rgba(255, 255, 255, 0.85);
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--body-gradient, radial-gradient(circle at top right, #1e1b4b, #030712));
            color: var(--text);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding-top: env(safe-area-inset-top, 0px);
        }

        /* 📱 Mobile Optimized Header */
        header {
            background: var(--header-bg, rgba(3, 7, 18, 0.85)); backdrop-filter: blur(25px);
            padding: calc(12px + env(safe-area-inset-top, 0px)) 16px 14px; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
            z-index: 2000; flex-shrink: 0;
        }
        .header-left { display: flex; align-items: center; gap: 12px; }
        .header-title { font-size: 16px; font-weight: 800; letter-spacing: -0.5px; color: var(--text); }
        .header-title span { color: var(--primary); }
        
        .icon-btn { 
            width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; 
            background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--text); cursor: pointer; transition: 0.2s;
        }
        .icon-btn:active { transform: scale(0.9); background: rgba(255,255,255,0.1); }

        /* 📂 Sidebar */
        .workspace { flex: 1; display: flex; flex-direction: row; overflow: hidden; position: relative; }

        .sidebar {
            width: 300px; background: var(--nav-dock-bg, #030712); border-right: 1px solid var(--border);
            z-index: 3000; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; flex-direction: column; height: 100%; position: fixed; left: -300px; top: 0;
            backdrop-filter: blur(30px);
        }
        .sidebar.open { left: 0; box-shadow: 20px 0 60px rgba(0,0,0,0.8); }

        @media (min-width: 1024px) {
            .sidebar { position: relative; left: 0; flex-shrink: 0; }
            .icon-btn.menu { display: none; }
            .overlay { display: none !important; }
        }

        .scroll-list { flex: 1; overflow-y: auto; padding: 20px; padding-bottom: 100px; }
        .nav-label { font-size: 11px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 2px; margin: 30px 0 12px; }
        .nav-item { 
            padding: 14px 18px; border-radius: 16px; margin-bottom: 6px; cursor: pointer; transition: 0.3s;
            font-size: 14px; color: var(--text-dim); display: flex; align-items: center; gap: 14px; font-weight: 600;
        }
        .nav-item i { font-size: 16px; opacity: 0.7; }
        .nav-item:hover { background: rgba(255,255,255,0.03); color: var(--text); }
        .nav-item.active { background: var(--primary); color: white !important; box-shadow: 0 10px 20px var(--primary-glow); }
        .nav-item.active i { opacity: 1; }

        /* 🛠️ Main Body */
        .main-body { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; background: rgba(0,0,0,0.2); }
        
        .toolbar { 
            padding: 14px 20px; background: rgba(3, 7, 18, 0.4); border-bottom: 1px solid var(--border); 
            display: flex; gap: 10px; align-items: center; flex-wrap: wrap; flex-shrink: 0;
        }
        .search-container { flex: 1; min-width: 200px; position: relative; }
        .search-container i { position: absolute; left: 14px; top: 50%; translate: 0 -50%; color: var(--text-dim); font-size: 13px; }
        .search-container input { 
            width: 100%; padding: 12px 12px 12px 40px; background: var(--input-bg, var(--surface)); border: 1px solid var(--border); 
            border-radius: 14px; color: var(--text); font-size: 14px; outline: none; transition: 0.3s;
        }
        .search-container input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-glow); }

        .toolbar-actions { display: flex; gap: 8px; }
        .btn-tool { 
            width: 44px; height: 44px; border-radius: 14px; border: 1px solid var(--border); background: var(--surface); 
            color: var(--text); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;
        }
        .btn-tool:active { transform: scale(0.9); }
        .btn-tool.active { background: var(--primary); border-color: var(--primary); color: white; }

        /* 📊 Selection Counter Badge */
        .sel-badge {
            display: none; align-items: center; gap: 8px;
            background: var(--primary); color: white; padding: 8px 16px;
            border-radius: 100px; font-size: 13px; font-weight: 700;
            white-space: nowrap; cursor: pointer;
            box-shadow: 0 4px 15px var(--primary-glow);
            transition: 0.2s;
        }
        .sel-badge.show { display: flex; }
        .sel-badge:active { transform: scale(0.95); }

        /* 📱 Grid View (Desktop) vs Card View (Mobile) */
        .content-frame { flex: 1; overflow: auto; padding: 16px; scroll-behavior: smooth; position: relative; }
        
        /* Desktop Table */
        .desktop-table-container { background: var(--surface); border-radius: 20px; border: 1px solid var(--border); overflow: auto; display: none; }
        table { border-collapse: separate; border-spacing: 0; width: 100%; }
        th { 
            position: sticky; top: 0; z-index: 100; background: var(--header-bg, #1e1b4b); padding: 16px 20px; text-align: left; 
            font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--primary); letter-spacing: 1.5px; 
            border-bottom: 2px solid var(--border); white-space: nowrap;
        }
        th.col-hideable { cursor: pointer; }
        th.col-hideable:hover { color: var(--text); }
        th .col-visible-toggle { opacity: 0.4; margin-left: 6px; font-size: 10px; }
        td { 
            padding: 16px 20px; font-size: 14px; border-bottom: 1px solid var(--border); 
            white-space: nowrap; color: var(--text); transition: 0.2s;
        }
        tr:hover td { background: rgba(255,255,255,0.03); color: var(--text); }
        tr.selected td { background: rgba(139, 92, 246, 0.1) !important; }
        tr.selected td:first-child { border-left: 3px solid var(--primary); }

        /* Checkbox Styles */
        .chk-cell { width: 40px; min-width: 40px; text-align: center; padding: 12px 8px !important; }
        .chk-all { cursor: pointer; }
        .row-checkbox {
            width: 20px; height: 20px; border-radius: 6px; cursor: pointer;
            accent-color: var(--primary);
        }
        .chk-cell .row-checkbox { display: block; margin: 0 auto; }

        /* Row Action Buttons */
        .row-actions { display: flex; gap: 6px; justify-content: center; }
        .row-action-btn {
            width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border);
            background: rgba(255,255,255,0.03); color: var(--text-dim); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; transition: 0.2s;
        }
        .row-action-btn:hover { background: rgba(255,255,255,0.08); color: var(--text); }
        .row-action-btn.danger:hover { background: rgba(244,63,94,0.15); color: var(--danger); border-color: rgba(244,63,94,0.3); }
        .row-action-btn.dup:hover { background: rgba(16,185,129,0.15); color: var(--success); border-color: rgba(16,185,129,0.3); }

        /* Mobile Cards */
        .mobile-card-list { display: flex; flex-direction: column; gap: 12px; }
        .data-card { 
            background: var(--card-base, var(--card-bg)); border: 1px solid var(--border); border-radius: 20px; padding: 18px;
            display: flex; flex-direction: column; gap: 12px; backdrop-filter: blur(10px); position: relative;
            transition: 0.2s;
        }
        .data-card.selected { border-color: var(--primary); box-shadow: 0 0 0 2px var(--primary-glow); }
        .data-card .card-chk {
            position: absolute; top: 12px; right: 12px; z-index: 2;
        }
        .data-card .card-chk input { width: 22px; height: 22px; accent-color: var(--primary); cursor: pointer; }
        .card-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border); padding-bottom: 10px; }
        .card-id { font-size: 12px; font-weight: 800; color: var(--primary); }
        .card-title { font-size: 15px; font-weight: 700; color: var(--text); }
        .card-body { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .data-item { display: flex; flex-direction: column; gap: 2px; }
        .data-label { font-size: 9px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.5px; }
        .data-value { font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card-actions {
            display: flex; gap: 8px; justify-content: flex-end; border-top: 1px solid var(--border); padding-top: 12px;
        }
        .card-action-btn {
            padding: 8px 14px; border-radius: 10px; border: 1px solid var(--border);
            background: rgba(255,255,255,0.03); color: var(--text-dim); cursor: pointer;
            font-size: 12px; font-weight: 600; transition: 0.2s;
            display: flex; align-items: center; gap: 6px;
        }
        .card-action-btn:hover { background: rgba(255,255,255,0.08); color: var(--text); }
        .card-action-btn.danger:hover { background: rgba(244,63,94,0.15); color: var(--danger); }

        @media (min-width: 1024px) {
            .desktop-table-container { display: block; }
            .mobile-card-list { display: none; }
        }

        /* 📋 Batch Operations Bar (Floating) */
        .batch-bar {
            position: fixed; bottom: -100px; left: 50%; translate: -50% 0;
            background: var(--batch-bar-bg); backdrop-filter: blur(30px);
            border: 1px solid var(--border); border-radius: 24px;
            padding: 16px 24px; display: flex; align-items: center; gap: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.7);
            z-index: 4000; transition: 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            white-space: nowrap;
        }
        .batch-bar.show { bottom: 30px; }
        .batch-bar .batch-count {
            font-size: 14px; font-weight: 800; color: var(--text);
            padding-right: 16px; border-right: 1px solid var(--border);
        }
        .batch-bar .batch-count span { color: var(--primary); }
        .batch-btn {
            padding: 10px 18px; border-radius: 14px; border: none; cursor: pointer;
            font-weight: 700; font-size: 13px; transition: 0.2s;
            display: flex; align-items: center; gap: 8px;
        }
        .batch-btn:active { transform: scale(0.93); }
        .batch-btn.danger { background: rgba(244,63,94,0.15); color: var(--danger); border: 1px solid rgba(244,63,94,0.2); }
        .batch-btn.danger:hover { background: rgba(244,63,94,0.25); }
        .batch-btn.primary { background: rgba(139,92,246,0.15); color: var(--primary); border: 1px solid rgba(139,92,246,0.2); }
        .batch-btn.primary:hover { background: rgba(139,92,246,0.25); }
        .batch-btn.success { background: rgba(16,185,129,0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.2); }
        .batch-btn.success:hover { background: rgba(16,185,129,0.25); }
        .batch-btn.outline { background: transparent; color: var(--text-dim); border: 1px solid var(--border); }
        .batch-btn.outline:hover { color: var(--text); background: rgba(255,255,255,0.05); }

        /* Column Visibility Toggle Panel */
        .col-toggle-panel {
            position: absolute; top: 60px; right: 60px;
            background: rgba(15, 23, 42, 0.98); border: 1px solid var(--border);
            border-radius: 20px; padding: 18px; width: 240px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.7); backdrop-filter: blur(25px);
            z-index: 1000; display: none;
            flex-direction: column; gap: 4px;
            animation: slideDown 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .col-toggle-item {
            display: flex; align-items: center; gap: 12px; padding: 8px 10px;
            border-radius: 10px; cursor: pointer; transition: 0.2s;
            font-size: 13px; color: var(--text-dim); font-weight: 600;
        }
        .col-toggle-item:hover { background: rgba(255,255,255,0.05); color: var(--text); }
        .col-toggle-item input[type="checkbox"] { accent-color: var(--primary); width: 16px; height: 16px; }

        /* Column Filter Panel */
        .col-filter-panel {
            position: absolute; top: 60px; right: 16px;
            background: rgba(15, 23, 42, 0.98); border: 1px solid var(--border);
            border-radius: 20px; padding: 18px; width: 280px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.7); backdrop-filter: blur(25px);
            z-index: 1000; display: none;
            flex-direction: column; gap: 10px;
            animation: slideDown 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .col-filter-select {
            width: 100%; padding: 12px; border-radius: 12px;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            color: var(--text); font-size: 13px; outline: none;
        }
        .col-filter-value {
            width: 100%; padding: 12px; border-radius: 12px;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            color: var(--text); font-size: 13px; outline: none;
        }
        .col-filter-value:focus { border-color: var(--primary); }
        .filter-tags {
            display: flex; flex-wrap: wrap; gap: 6px;
        }
        .filter-tag {
            display: flex; align-items: center; gap: 6px;
            background: rgba(139,92,246,0.15); color: var(--primary);
            padding: 6px 12px; border-radius: 100px; font-size: 11px; font-weight: 700;
        }
        .filter-tag i { cursor: pointer; opacity: 0.7; }
        .filter-tag i:hover { opacity: 1; }
        .filter-tag .tag-col { color: var(--text-dim); font-weight: 500; }

        /* 🔄 Components */
        .loader { position: fixed; inset: 0; background: rgba(3,7,18,0.85); z-index: 5000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(15px); }
        .spinner { width: 45px; height: 45px; border: 4px solid rgba(255,255,255,0.05); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s cubic-bezier(0.4, 0, 0.2, 1) infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Modal */
        .modal { 
            position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(20px); 
            z-index: 6000; display: none; align-items: center; justify-content: center; padding: 20px;
        }
        .modal-card { 
            background: var(--bg, #030712); border: 1px solid var(--border); border-radius: 32px; width: 100%; max-width: 500px; 
            padding: 30px; max-height: 85vh; display: flex; flex-direction: column;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        #recordForm { flex: 1; overflow-y: auto; padding-right: 5px; }
        .field-input { width: 100%; padding: 14px; border-radius: 14px; background: var(--input-bg, rgba(255,255,255,0.03)); border: 1px solid var(--border); color: var(--text); outline: none; margin-bottom:15px; font-family: inherit; font-size: 14px; }
        .field-input:focus { border-color: var(--primary); background: rgba(255,255,255,0.06); }
        .field-label { display: block; font-size: 10px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 1px; }

        .modal-actions { margin-top: 25px; display: grid; grid-template-columns: 1fr 2fr; gap: 12px; }
        .btn-modal { padding: 15px; border-radius: 16px; border: none; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.2s; }
        .btn-modal.delete { background: rgba(244,63,94,0.1); color: var(--danger); border: 1px solid rgba(244,63,94,0.2); }
        .btn-modal.save { background: var(--primary); color: white; box-shadow: 0 10px 20px var(--primary-glow); }
        .btn-modal:active { transform: scale(0.95); }

        /* Batch Edit Modal - Single Field Update */
        .batch-field-group { margin-bottom: 18px; }
        .batch-field-select {
            width: 100%; padding: 14px; border-radius: 14px;
            background: rgba(255,255,255,0.03); border: 1px solid var(--border);
            color: var(--text); font-size: 14px; outline: none; margin-bottom: 12px;
        }
        .batch-field-select:focus { border-color: var(--primary); }
        .batch-info { 
            font-size: 12px; color: var(--text-dim); text-align: center;
            background: rgba(139,92,246,0.08); padding: 10px; border-radius: 12px;
            margin-bottom: 15px; border: 1px solid rgba(139,92,246,0.15);
        }

        /* Floating Buttons */
        .fab-group { position: fixed; bottom: 25px; right: 25px; display: flex; flex-direction: column; gap: 15px; z-index: 500; }
        .fab { width: 60px; height: 60px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; border: none; cursor: pointer; transition: 0.3s; }
        .fab-add { background: var(--primary); box-shadow: 0 15px 30px var(--primary-glow); }
        .fab:active { transform: scale(0.9) rotate(90deg); }

        .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2500; display: none; backdrop-filter: blur(8px); }
        .overlay.open { display: block; }

        @media (max-width: 640px) {
            .modal-card { padding: 25px 20px; border-radius: 24px; height: 100%; max-height: 100vh; }
            .toolbar { padding: 12px 16px; }
            .content-frame { padding: 12px; }
            .batch-bar { padding: 12px 16px; gap: 10px; border-radius: 20px; width: calc(100% - 32px); }
            .batch-bar .batch-count { font-size: 12px; padding-right: 10px; }
            .batch-btn { padding: 8px 12px; font-size: 11px; }
        }
        
        .date-picker-input {
            padding: 10px 14px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: white;
            font-size: 13px;
            outline: none;
            cursor: pointer;
            transition: 0.2s;
        }
        .date-picker-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }
        .date-detected-badge {
            font-size: 10px;
            font-weight: 800;
            color: var(--secondary);
            background: rgba(6, 182, 212, 0.1);
            padding: 4px 10px;
            border-radius: 8px;
            margin-left: 10px;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }

        .export-panel {
            position: absolute;
            top: 60px;
            right: 16px;
            background: rgba(15, 23, 42, 0.98);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 18px;
            width: 280px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.7);
            backdrop-filter: blur(25px);
            z-index: 1000;
            display: none;
            flex-direction: column;
            gap: 12px;
            animation: slideDown 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Toast Notification */
        .toast {
            position: fixed; top: 80px; right: 20px; z-index: 9999;
            background: var(--batch-bar-bg); backdrop-filter: blur(30px);
            border: 1px solid var(--border); border-radius: 16px;
            padding: 16px 24px; display: flex; align-items: center; gap: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
            font-size: 14px; font-weight: 600;
            transform: translateX(120%); transition: 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .toast.show { transform: translateX(0); }
        .toast.success i { color: var(--success); }
        .toast.error i { color: var(--danger); }
        .toast.info i { color: var(--primary); }
        .toast i { font-size: 20px; }
    </style>
</head>
<body>

    <!-- Toast Notification -->
    <div class="toast" id="toast"><i class="fa-solid fa-circle-check"></i><span id="toastMsg">Success</span></div>

    <header>
        <div class="header-left">
            <div class="icon-btn menu" onclick="toggleMenu()"><i class="fa-solid fa-bars-staggered"></i></div>
            <div class="header-title">CLOUD<span>CONSOLE</span></div>
        </div>
        <div style="display:flex; gap:10px;">
            <div class="icon-btn" onclick="toggleTheme()" title="Change Theme"><i class="fa-solid fa-circle-half-stroke"></i></div>
            <div class="icon-btn" onclick="loadTableData()" title="Refresh"><i class="fa-solid fa-rotate"></i></div>
            <a href="index.html" class="icon-btn" style="text-decoration:none;"><i class="fa-solid fa-house"></i></a>
        </div>
    </header>

    <div class="workspace">
        <div class="overlay" id="overlay" onclick="toggleMenu()"></div>
        
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header" style="padding: 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight:900; color:var(--primary); font-size: 20px; letter-spacing: -0.5px;">EXPLORER</span>
                <i class="fa-solid fa-xmark" onclick="toggleMenu()" style="cursor:pointer; color:var(--text-dim); font-size: 20px;"></i>
            </div>
            <div class="scroll-list" id="tableList"></div>
        </aside>

        <div class="main-body">
            <div class="toolbar" style="gap:15px; position: relative; z-index: 1000;">
                <div class="search-container" style="flex: 1.5; min-width: 180px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="masterSearch" placeholder="Quick search data..." oninput="filterLocal()">
                </div>
                
                <!-- Tiny visual indicator for matched date column -->
                <div id="dateIndicatorArea" style="display:flex; align-items:center;"></div>

                <!-- Selection Counter Badge -->
                <div class="sel-badge" id="selBadge" onclick="clearSelection()">
                    <i class="fa-solid fa-check-circle"></i>
                    <span id="selCount">0</span> selected
                    <i class="fa-solid fa-xmark" style="font-size:12px; opacity:0.7;"></i>
                </div>

                <div class="toolbar-actions" style="display:flex; gap:8px;">
                    <!-- Column Visibility Toggle -->
                    <div class="btn-tool" onclick="toggleColPanel(event)" title="Column Visibility" style="position:relative;">
                        <i class="fa-solid fa-table-cells"></i>
                    </div>

                    <!-- Column Filter -->
                    <div class="btn-tool" onclick="toggleFilterPanel(event)" title="Column Filters" style="position:relative;">
                        <i class="fa-solid fa-filter-list"></i>
                    </div>

                    <!-- 📥 Unified Export / Date Filter Dropdown Icon -->
                    <div class="btn-tool" onclick="toggleExportPanel(event)" title="Export & Date Filter" style="position: relative; background: var(--primary-glow); border-color: var(--primary);">
                        <i class="fa-solid fa-file-export" style="color:var(--primary); font-size:16px;"></i>
                    </div>

                    <div class="btn-tool" onclick="document.getElementById('csvInput').click()" title="Import CSV"><i class="fa-solid fa-file-import"></i></div>
                    <input type="file" id="csvInput" style="display:none;" accept=".csv" onchange="handleImport(event)">
                    <div class="btn-tool" onclick="addField()" title="Add Field"><i class="fa-solid fa-plus-minus"></i></div>
                </div>

                <!-- Column Visibility Toggle Panel -->
                <div class="col-toggle-panel" id="colTogglePanel" onclick="event.stopPropagation()">
                    <h4 style="font-weight: 800; font-size: 13px; color: var(--text); border-bottom: 1px solid var(--border); padding-bottom: 8px; margin-bottom: 8px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-eye" style="color:var(--primary);"></i> Visible Columns
                    </h4>
                    <div id="colToggleList"></div>
                </div>

                <!-- Column Filter Panel -->
                <div class="col-filter-panel" id="colFilterPanel" onclick="event.stopPropagation()">
                    <h4 style="font-weight: 800; font-size: 13px; color: var(--text); border-bottom: 1px solid var(--border); padding-bottom: 8px; margin-bottom: 8px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-filter" style="color:var(--primary);"></i> Column Filters
                        <span style="flex:1;"></span>
                        <span onclick="clearAllColumnFilters()" style="font-size:10px; color:var(--text-dim); cursor:pointer;">Clear all</span>
                    </h4>
                    <select class="col-filter-select" id="colFilterSelect" onchange="document.getElementById('colFilterValue').focus()">
                        <option value="">-- Select column --</option>
                    </select>
                    <input type="text" class="col-filter-value" id="colFilterValue" placeholder="Filter value..." oninput="addColumnFilter()">
                    <div class="filter-tags" id="filterTags"></div>
                </div>

                <!-- 📂 Glassmorphic Dropdown Panel for Date Filtering & Exporting -->
                <div class="export-panel" id="exportPanel" onclick="event.stopPropagation()">
                    <h4 style="font-weight: 800; font-size: 13px; color: var(--text); border-bottom: 1px solid var(--border); padding-bottom: 8px; margin-bottom: 12px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-filter" style="color:var(--primary);"></i> Filter & Export
                    </h4>
                    
                    <div id="panelDateSection" style="display:flex; flex-direction:column; gap:10px;">
                        <div class="field-group">
                            <label class="field-label" id="panelDateLabel" style="font-size:9px; color:var(--text-dim); text-transform:uppercase; margin-bottom:4px; display:block;">Filter Date Range</label>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <input type="date" id="startDate" class="date-picker-input" onchange="filterLocal()" style="width:100%; font-size:12px; padding:8px 10px;">
                                <input type="date" id="endDate" class="date-picker-input" onchange="filterLocal()" style="width:100%; font-size:12px; padding:8px 10px;">
                            </div>
                        </div>
                        <button class="btn-modal delete" onclick="clearDateFilter()" style="padding:8px; font-size:11px; margin-top:4px; border-radius:10px; cursor:pointer;">
                            <i class="fa-solid fa-calendar-xmark"></i> Clear Dates
                        </button>
                    </div>

                    <div style="border-top:1px solid var(--border); padding-top:12px; margin-top:8px;">
                        <button class="btn-modal save" onclick="exportCSV()" style="padding:10px; font-size:12px; width:100%; display:flex; align-items:center; justify-content:center; gap:8px; border-radius:10px; cursor:pointer;">
                            <i class="fa-solid fa-download"></i> EXPORT TO CSV
                        </button>
                    </div>
                </div>
            </div>

            <div class="content-frame">
                <!-- Desktop View -->
                <div class="desktop-table-container">
                    <table id="mainGrid">
                        <thead id="gridHead"></thead>
                        <tbody id="gridBody"></tbody>
                    </table>
                </div>

                <!-- Mobile View -->
                <div class="mobile-card-list" id="mobileCards"></div>
            </div>
        </div>
    </div>

    <!-- Batch Operations Bar -->
    <div class="batch-bar" id="batchBar">
        <div class="batch-count"><span id="batchCount">0</span> selected</div>
        <button class="batch-btn danger" onclick="deleteSelected()" title="Delete selected"><i class="fa-solid fa-trash-can"></i> Delete</button>
        <button class="batch-btn primary" onclick="batchEdit()" title="Batch Edit"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
        <button class="batch-btn success" onclick="duplicateSelected()" title="Duplicate selected"><i class="fa-solid fa-copy"></i> Duplicate</button>
        <button class="batch-btn outline" onclick="clearSelection()" title="Clear selection"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div class="fab-group">
        <button class="fab fab-add" onclick="addRow()"><i class="fa-solid fa-plus"></i></button>
    </div>

    <div class="loader" id="loader"><div class="spinner"></div></div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                <h3 id="modalTitle" style="font-size:18px; font-weight:800;">Edit Record</h3>
                <div class="icon-btn" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></div>
            </div>
            <form id="recordForm"></form>
            <div class="modal-actions">
                <button class="btn-modal delete" onclick="deleteSingle(); return false;">Delete</button>
                <button class="btn-modal save" onclick="saveRecord(event)">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Batch Edit Modal -->
    <div class="modal" id="batchEditModal">
        <div class="modal-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3 style="font-size:18px; font-weight:800;">Batch Edit</h3>
                <div class="icon-btn" onclick="closeBatchModal()"><i class="fa-solid fa-xmark"></i></div>
            </div>
            <div class="batch-info" id="batchInfo">Updating <strong>0</strong> records</div>
            <div class="batch-field-group">
                <label class="field-label">Select Column</label>
                <select class="batch-field-select" id="batchFieldSelect">
                    <option value="">-- Choose column --</option>
                </select>
            </div>
            <div class="batch-field-group">
                <label class="field-label">New Value</label>
                <input type="text" class="field-input" id="batchFieldValue" placeholder="Enter new value for all selected records...">
            </div>
            <div class="modal-actions" style="grid-template-columns: 1fr 2fr;">
                <button class="btn-modal delete" onclick="closeBatchModal()">Cancel</button>
                <button class="btn-modal save" onclick="executeBatchEdit()">Update All</button>
            </div>
        </div>
    </div>

    <script>
        const AUTH_PWD = "8508200253";
        let currentTable = "sales_log";
        let tableData = [];
        let columns = [];
        let allDatabaseTables = [];
        let currentEditingId = null;
        let activeFilters = {};
        let sortState = { column: null, direction: 'asc' };
        
        // Selection System
        let selectedIds = new Set();
        let hiddenColumns = new Set();

        const tableGroups = {
            "Operations": ['sales_log', 'invoice_log', 'renewal_log', 'renewal_invoice_log', 'customerdatas'],
            "Inventory": ['device_master', 'stock_ledger', 'software_master'],
            "Accounts": ['office_sales', 'office_renewal', 'sim_settlement', 'dealer_ledger'],
            "System": ['price_master', 'settings']
        };

        // ========== TOAST SYSTEM ==========
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = toast.querySelector('i');
            document.getElementById('toastMsg').textContent = msg;
            icon.className = type === 'success' ? 'fa-solid fa-circle-check' : type === 'error' ? 'fa-solid fa-circle-xmark' : 'fa-solid fa-circle-info';
            toast.className = 'toast show ' + type;
            clearTimeout(toast._timer);
            toast._timer = setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // ========== SIDEBAR ==========
        function toggleMenu() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('open');
        }

        async function init() {
            await fetchAllTables();
            loadTableData();
        }

        async function fetchAllTables() {
            try {
                const res = await fetch('api_master_data.php?action=list_tables').then(r => r.json());
                if (res.status === 'success' && res.tables) {
                    allDatabaseTables = res.tables;
                } else {
                    allDatabaseTables = Object.values(tableGroups).flat();
                }
            } catch(e) {
                allDatabaseTables = Object.values(tableGroups).flat();
            }
            renderSidebar();
        }

        function renderSidebar() {
            const list = document.getElementById('tableList');
            list.innerHTML = "";
            
            const groups = {
                "Operations": [],
                "Inventory": [],
                "Accounts": [],
                "System": [],
                "Archive & Others": []
            };
            
            allDatabaseTables.forEach(t => {
                let grouped = false;
                for (const groupName in tableGroups) {
                    if (tableGroups[groupName].includes(t)) {
                        groups[groupName].push(t);
                        grouped = true;
                        break;
                    }
                }
                if (!grouped) {
                    groups["Archive & Others"].push(t);
                }
            });

            for (const groupName in groups) {
                const tablesInGroup = groups[groupName];
                if (tablesInGroup.length === 0) continue;
                
                list.innerHTML += `<div class="nav-label">${groupName}</div>`;
                tablesInGroup.forEach(t => {
                    list.innerHTML += `<div class="nav-item ${currentTable === t ? 'active' : ''}" onclick="switchTable('${t}')">
                        <i class="fa-solid fa-database"></i> ${t.replace(/_/g, ' ')}
                    </div>`;
                });
            }
        }

        function findDateColumn() {
            const dateCols = ['date', 'issue_date', 'sold_date', 'purchase_date', 'created_at', 'created_date', 'payment_date'];
            for (let c of dateCols) {
                if (columns.includes(c)) return c;
            }
            for (let c of columns) {
                if (c.toLowerCase().includes('date') || c.toLowerCase().includes('time')) return c;
            }
            return null;
        }

        // ========== PANEL TOGGLES ==========
        function toggleExportPanel(event) {
            if (event) event.stopPropagation();
            hideAllPanels();
            const panel = document.getElementById('exportPanel');
            panel.style.display = panel.style.display === 'flex' ? 'none' : 'flex';
        }

        function toggleColPanel(event) {
            if (event) event.stopPropagation();
            hideAllPanels();
            const panel = document.getElementById('colTogglePanel');
            panel.style.display = panel.style.display === 'flex' ? 'none' : 'flex';
        }

        function toggleFilterPanel(event) {
            if (event) event.stopPropagation();
            hideAllPanels();
            const panel = document.getElementById('colFilterPanel');
            panel.style.display = panel.style.display === 'flex' ? 'none' : 'flex';
            if (panel.style.display === 'flex') populateFilterSelect();
        }

        function hideAllPanels() {
            document.getElementById('exportPanel').style.display = 'none';
            document.getElementById('colTogglePanel').style.display = 'none';
            document.getElementById('colFilterPanel').style.display = 'none';
        }

        document.addEventListener('click', hideAllPanels);

        // ========== DATA LOADING ==========
        async function loadTableData() {
            showLoader(true);
            try {
                const res = await fetch(`api_master_data.php?action=get_data&table=${currentTable}`).then(r => r.json());
                columns = res.columns || [];
                tableData = res.data || [];
                activeFilters = {};
                sortState = { column: null, direction: 'asc' };
                selectedIds = new Set();
                document.getElementById('masterSearch').value = '';
                
                // Reset Date Filters
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';

                // Reset hidden columns
                hiddenColumns = new Set();

                // Dynamically configure Date picker inside the panel
                const dateCol = findDateColumn();
                const panelDateSec = document.getElementById('panelDateSection');
                const indicatorArea = document.getElementById('dateIndicatorArea');
                
                indicatorArea.innerHTML = '';
                if (dateCol) {
                    panelDateSec.style.display = 'block';
                    
                    const badge = document.createElement('span');
                    badge.className = 'date-detected-badge';
                    badge.style.margin = '0';
                    badge.style.background = 'rgba(139, 92, 246, 0.1)';
                    badge.style.color = 'var(--primary)';
                    badge.style.borderColor = 'rgba(139, 92, 246, 0.2)';
                    badge.innerHTML = `<i class="fa-solid fa-calendar-day"></i> ${dateCol.replace(/_/g, ' ')}`;
                    indicatorArea.appendChild(badge);
                } else {
                    panelDateSec.style.display = 'none';
                }

                updateSelectionUI();
                renderUI();
            } catch (e) { console.error(e); }
            showLoader(false);
        }

        // ========== SELECTION SYSTEM ==========
        function toggleSelectAll() {
            const visibleRows = getVisibleRows();
            const visibleIds = new Set(visibleRows.map(r => r.id.toString()));
            
            // Check if all visible are selected
            const allSelected = visibleRows.every(r => selectedIds.has(r.id.toString()));
            
            if (allSelected) {
                // Deselect all visible
                visibleIds.forEach(id => selectedIds.delete(id));
            } else {
                // Select all visible
                visibleIds.forEach(id => selectedIds.add(id));
            }
            updateSelectionUI();
            renderUI();
        }

        function toggleSelect(id) {
            const idStr = id.toString();
            if (selectedIds.has(idStr)) {
                selectedIds.delete(idStr);
            } else {
                selectedIds.add(idStr);
            }
            updateSelectionUI();
            renderUI();
        }

        function clearSelection() {
            selectedIds = new Set();
            updateSelectionUI();
            renderUI();
        }

        function updateSelectionUI() {
            const count = selectedIds.size;
            const badge = document.getElementById('selBadge');
            const batchBar = document.getElementById('batchBar');
            const batchCount = document.getElementById('batchCount');
            
            if (count > 0) {
                badge.classList.add('show');
                document.getElementById('selCount').textContent = count;
                batchBar.classList.add('show');
                batchCount.textContent = count;
            } else {
                badge.classList.remove('show');
                batchBar.classList.remove('show');
            }
        }

        // ========== RENDERING ==========
        function renderUI() {
            renderGrid();
            renderCards();
            renderColToggle();
        }

        function renderColToggle() {
            const list = document.getElementById('colToggleList');
            if (!list) return;
            list.innerHTML = columns.map(c => `
                <label class="col-toggle-item">
                    <input type="checkbox" ${hiddenColumns.has(c) ? '' : 'checked'} 
                           onchange="toggleColumnVisibility('${c}')">
                    ${c.replace(/_/g, ' ')}
                </label>
            `).join('');
        }

        function toggleColumnVisibility(col) {
            if (hiddenColumns.has(col)) {
                hiddenColumns.delete(col);
            } else {
                hiddenColumns.add(col);
            }
            renderUI();
        }

        function renderGrid() {
            const head = document.getElementById('gridHead');
            const body = document.getElementById('gridBody');
            const visibleCols = columns.filter(c => !hiddenColumns.has(c));
            
            head.innerHTML = `
                <tr>
                    <th class="chk-cell">
                        <input type="checkbox" class="row-checkbox chk-all" onchange="toggleSelectAll()"
                               ${getVisibleRows().length > 0 && getVisibleRows().every(r => selectedIds.has(r.id.toString())) ? 'checked' : ''}>
                    </th>
                    ${visibleCols.map(c => `
                        <th onclick="toggleSort('${c}')" style="cursor:pointer;">
                            ${c.replace(/_/g, ' ')}${getSortIcon(c)}
                        </th>
                    `).join('')}
                    <th style="width:90px; text-align:center;">Actions</th>
                </tr>`;
            
            const rows = getVisibleRows();
            body.innerHTML = rows.map(row => {
                const isSelected = selectedIds.has(row.id.toString());
                return `
                    <tr class="${isSelected ? 'selected' : ''}">
                        <td class="chk-cell" onclick="event.stopPropagation()">
                            <input type="checkbox" class="row-checkbox" 
                                   ${isSelected ? 'checked' : ''}
                                   onchange="toggleSelect('${row.id}')">
                        </td>
                        ${visibleCols.map(c => `<td>${escapeHtml(row[c] || '-')}</td>`).join('')}
                        <td onclick="event.stopPropagation()">
                            <div class="row-actions">
                                <button class="row-action-btn" onclick="openRecord('${row.id}')" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="row-action-btn dup" onclick="duplicateRecord('${row.id}')" title="Duplicate">
                                    <i class="fa-solid fa-copy"></i>
                                </button>
                                <button class="row-action-btn danger" onclick="quickDelete('${row.id}')" title="Delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('') || '<tr><td colspan="100" style="text-align:center; padding:50px;">No data</td></tr>';
        }

        function renderCards() {
            const container = document.getElementById('mobileCards');
            const rows = getVisibleRows();
            
            if(!rows.length) {
                container.innerHTML = '<div class="empty" style="text-align:center; padding:40px; color:var(--text-dim);">No records found.</div>';
                return;
            }

            const visibleCols = columns.filter(c => !hiddenColumns.has(c));
            const titleKey = visibleCols[1] || visibleCols[0] || 'id';

            container.innerHTML = rows.map(row => {
                const isSelected = selectedIds.has(row.id.toString());
                return `
                    <div class="data-card ${isSelected ? 'selected' : ''}">
                        <div class="card-chk" onclick="event.stopPropagation()">
                            <input type="checkbox" ${isSelected ? 'checked' : ''}
                                   onchange="toggleSelect('${row.id}')">
                        </div>
                        <div class="card-header" onclick="openRecord('${row.id}')">
                            <div class="card-title">${escapeHtml(row[titleKey] || 'No Title')}</div>
                            <div class="card-id">#${row.id}</div>
                        </div>
                        <div class="card-body" onclick="openRecord('${row.id}')">
                            ${visibleCols.slice(2, 6).map(c => `
                                <div class="data-item">
                                    <div class="data-label">${c.replace(/_/g, ' ')}</div>
                                    <div class="data-value">${escapeHtml(row[c] || '-')}</div>
                                </div>
                            `).join('')}
                        </div>
                        <div class="card-actions">
                            <button class="card-action-btn" onclick="openRecord('${row.id}')">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                            <button class="card-action-btn" onclick="duplicateRecord('${row.id}')">
                                <i class="fa-solid fa-copy"></i> Dup
                            </button>
                            <button class="card-action-btn danger" onclick="quickDelete('${row.id}')">
                                <i class="fa-solid fa-trash-can"></i> Del
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function escapeHtml(value) {
            if (value === null || value === undefined) return '';
            return String(value)
                .replace(/&/g, '&')
                .replace(/</g, '<')
                .replace(/>/g, '>')
                .replace(/"/g, '"')
                .replace(/'/g, ''');
        }

        function getSortIcon(column) {
            if (sortState.column !== column) return '';
            return sortState.direction === 'asc' ? ' <i class="fa-solid fa-arrow-up-short-wide"></i>' : ' <i class="fa-solid fa-arrow-down-short-wide"></i>';
        }

        function toggleSort(column) {
            if (sortState.column === column) {
                sortState.direction = sortState.direction === 'asc' ? 'desc' : 'asc';
            } else {
                sortState = { column, direction: 'asc' };
            }
            renderUI();
        }

        // ========== FILTERING ==========
        function getVisibleRows() {
            let rows = tableData;

            // 1. Text Search Filter
            const globalTerm = document.getElementById('masterSearch').value.toLowerCase();
            if (globalTerm) {
                rows = rows.filter(row => {
                    return Object.values(row).some(v => v && v.toString().toLowerCase().includes(globalTerm));
                });
            }

            // 2. Date Range Filter
            const dateCol = findDateColumn();
            if (dateCol) {
                const sDate = document.getElementById('startDate').value;
                const eDate = document.getElementById('endDate').value;
                if (sDate) {
                    rows = rows.filter(row => row[dateCol] && row[dateCol] >= sDate);
                }
                if (eDate) {
                    rows = rows.filter(row => row[dateCol] && row[dateCol] <= eDate);
                }
            }

            // 3. Column-specific Filters
            for (const [col, val] of Object.entries(activeFilters)) {
                if (val) {
                    const lowerVal = val.toLowerCase();
                    rows = rows.filter(row => {
                        const cellVal = (row[col] || '').toString().toLowerCase();
                        return cellVal.includes(lowerVal);
                    });
                }
            }

            // 4. Sorting
            if (sortState.column) {
                const col = sortState.column;
                const dir = sortState.direction === 'asc' ? 1 : -1;
                rows = [...rows].sort((a, b) => {
                    const av = (a[col] || '').toString().toLowerCase();
                    const bv = (b[col] || '').toString().toLowerCase();
                    const an = Number(av);
                    const bn = Number(bv);
                    if (!Number.isNaN(an) && !Number.isNaN(bn) && av !== '' && bv !== '') {
                        return (an - bn) * dir;
                    }
                    return av.localeCompare(bv) * dir;
                });
            }

            return rows;
        }

        // ========== COLUMN FILTERS ==========
        function populateFilterSelect() {
            const sel = document.getElementById('colFilterSelect');
            const currentVal = sel.value;
            sel.innerHTML = '<option value="">-- Select column --</option>' + 
                columns.filter(c => c !== 'id').map(c => 
                    `<option value="${c}" ${c === currentVal ? 'selected' : ''}>${c.replace(/_/g, ' ')}</option>`
                ).join('');
            renderFilterTags();
        }

        function addColumnFilter() {
            const col = document.getElementById('colFilterSelect').value;
            const val = document.getElementById('colFilterValue').value.trim();
            if (col && val) {
                activeFilters[col] = val;
                renderFilterTags();
                renderUI();
            }
        }

        function removeColumnFilter(col) {
            delete activeFilters[col];
            renderFilterTags();
            renderUI();
        }

        function clearAllColumnFilters() {
            activeFilters = {};
            document.getElementById('colFilterSelect').value = '';
            document.getElementById('colFilterValue').value = '';
            renderFilterTags();
            renderUI();
        }

        function renderFilterTags() {
            const container = document.getElementById('filterTags');
            const entries = Object.entries(activeFilters);
            if (entries.length === 0) {
                container.innerHTML = '<div style="font-size:11px; color:var(--text-dim); padding:4px 0;">No active filters</div>';
                return;
            }
            container.innerHTML = entries.map(([col, val]) => `
                <span class="filter-tag">
                    <span class="tag-col">${col.replace(/_/g, ' ')}:</span> ${escapeHtml(val)}
                    <i class="fa-solid fa-xmark" onclick="removeColumnFilter('${col}')"></i>
                </span>
            `).join('');
        }

        // ========== RECORD OPERATIONS ==========
        function openRecord(id) {
            currentEditingId = id;
            const record = tableData.find(r => r.id.toString() === id.toString());
            const form = document.getElementById('recordForm');
            form.innerHTML = columns.filter(c => c !== 'id').map(c => `
                <div class="field-group">
                    <label class="field-label">${c.replace(/_/g, ' ')}</label>
                    <input type="text" name="${c}" class="field-input" value="${record ? escapeHtml(record[c] || '') : ''}">
                </div>
            `).join('');
            document.getElementById('modalTitle').innerText = record ? `Edit #${id}` : "Create Entry";
            document.getElementById('editModal').style.display = 'flex';
        }

        async function saveRecord(e) {
            e.preventDefault();
            const fd = new FormData(document.getElementById('recordForm'));
            fd.append('action', 'save_full_row');
            fd.append('table', currentTable);
            showLoader(true);
            
            try {
                if(currentEditingId !== 'NEW') {
                    for(let [key, value] of fd.entries()) {
                        if(['action', 'table'].includes(key)) continue;
                        const cfd = new FormData();
                        cfd.append('action', 'update_cell');
                        cfd.append('table', currentTable);
                        cfd.append('id', currentEditingId);
                        cfd.append('column', key);
                        cfd.append('value', value);
                        await fetch('api_master_data.php', { method: 'POST', body: cfd });
                    }
                } else {
                    await fetch('api_master_data.php', { method: 'POST', body: fd });
                }
                closeModal();
                showToast('Record saved successfully');
                loadTableData();
            } catch(err) {
                showToast('Error saving data', 'error');
                showLoader(false);
            }
        }

        function addRow() {
            currentEditingId = 'NEW';
            openRecord(null);
        }

        async function deleteSingle() {
            if(!confirm("Are you sure? This cannot be undone.")) return;
            const pwd = prompt("Enter Admin Password to confirm:");
            if(pwd !== AUTH_PWD) return showToast("Unauthorized access denied.", 'error');
            
            const fd = new FormData();
            fd.append('action', 'delete_row');
            fd.append('table', currentTable);
            fd.append('ids', currentEditingId);
            
            showLoader(true);
            const res = await fetch('api_master_data.php', { method: 'POST', body: fd }).then(r => r.json());
            closeModal();
            if (res.status === 'success') showToast('Record deleted');
            loadTableData();
        }

        async function quickDelete(id) {
            if(!confirm(`Delete record #${id}? This cannot be undone.`)) return;
            const pwd = prompt("Enter Admin Password to confirm:");
            if(pwd !== AUTH_PWD) return showToast("Unauthorized access denied.", 'error');
            
            const fd = new FormData();
            fd.append('action', 'delete_row');
            fd.append('table', currentTable);
            fd.append('ids', id);
            
            showLoader(true);
            const res = await fetch('api_master_data.php', { method: 'POST', body: fd }).then(r => r.json());
            if (res.status === 'success') showToast('Record deleted');
            loadTableData();
        }

        async function duplicateRecord(id) {
            const fd = new FormData();
            fd.append('action', 'duplicate_row');
            fd.append('table', currentTable);
            fd.append('ids', id);
            
            showLoader(true);
            const res = await fetch('api_master_data.php', { method: 'POST', body: fd }).then(r => r.json());
            if (res.status === 'success') {
                showToast(`Record #${id} duplicated successfully`);
                loadTableData();
            } else {
                showToast(res.error || 'Duplicate failed', 'error');
                showLoader(false);
            }
        }

        // ========== BATCH OPERATIONS ==========
        async function deleteSelected() {
            if (selectedIds.size === 0) return;
            if(!confirm(`Delete ${selectedIds.size} selected records? This cannot be undone.`)) return;
            const pwd = prompt("Enter Admin Password to confirm:");
            if(pwd !== AUTH_PWD) return showToast("Unauthorized access denied.", 'error');
            
            const fd = new FormData();
            fd.append('action', 'delete_row');
            fd.append('table', currentTable);
            fd.append('ids', [...selectedIds].join(','));
            
            showLoader(true);
            const res = await fetch('api_master_data.php', { method: 'POST', body: fd }).then(r => r.json());
            if (res.status === 'success') {
                showToast(`Deleted ${res.deleted || selectedIds.size} records`);
                selectedIds = new Set();
                loadTableData();
            } else {
                showToast(res.error || 'Delete failed', 'error');
                showLoader(false);
            }
        }

        function batchEdit() {
            if (selectedIds.size === 0) return;
            
            const select = document.getElementById('batchFieldSelect');
            select.innerHTML = '<option value="">-- Choose column --</option>' + 
                columns.filter(c => c !== 'id').map(c => 
                    `<option value="${c}">${c.replace(/_/g, ' ')}</option>`
                ).join('');
            
            document.getElementById('batchInfo').innerHTML = `Updating <strong>${selectedIds.size}</strong> records in <strong>${currentTable.replace(/_/g, ' ')}</strong>`;
            document.getElementById('batchFieldValue').value = '';
            document.getElementById('batchEditModal').style.display = 'flex';
        }

        async function executeBatchEdit() {
            const column = document.getElementById('batchFieldSelect').value;
            const value = document.getElementById('batchFieldValue').value.trim();
            
            if (!column) return showToast('Please select a column', 'error');
            if (!value) return showToast('Please enter a value', 'error');
            
            const fd = new FormData();
            fd.append('action', 'batch_update');
            fd.append('table', currentTable);
            fd.append('ids', [...selectedIds].join(','));
            fd.append('column', column);
            fd.append('value', value);
            
            showLoader(true);
            try {
                const res = await fetch('api_master_data.php', { method: 'POST', body: fd }).then(r => r.json());
                if (res.status === 'success') {
                    showToast(`Updated ${res.updated || selectedIds.size} records`);
                    closeBatchModal();
                    selectedIds = new Set();
                    loadTableData();
                } else {
                    showToast(res.error || 'Batch update failed', 'error');
                    showLoader(false);
                }
            } catch(err) {
                showToast('Network error', 'error');
                showLoader(false);
            }
        }

        async function duplicateSelected() {
            if (selectedIds.size === 0) return;
            if(!confirm(`Duplicate ${selectedIds.size} selected records?`)) return;
            
            const fd = new FormData();
            fd.append('action', 'duplicate_row');
            fd.append('table', currentTable);
            fd.append('ids', [...selectedIds].join(','));
            
            showLoader(true);
            const res = await fetch('api_master_data.php', { method: 'POST', body: fd }).then(r => r.json());
            if (res.status === 'success') {
                showToast(`Duplicated ${res.duplicated || selectedIds.size} records`);
                selectedIds = new Set();
                loadTableData();
            } else {
                showToast(res.error || 'Duplicate failed', 'error');
                showLoader(false);
            }
        }

        // ========== FIELD MANAGEMENT ==========
        async function addField() {
            const name = prompt("Enter New Field Name (No spaces, use _):");
            if(!name) return;
            const fd = new FormData();
            fd.append('action', 'add_column');
            fd.append('table', currentTable);
            fd.append('name', name);
            showLoader(true);
            const res = await fetch('api_master_data.php', { method: 'POST', body: fd }).then(r => r.json());
            if (res.status === 'success') showToast(`Field "${name}" added`);
            loadTableData();
        }

        // ========== FILTER & UTILITY ==========
        function filterLocal() {
            renderUI();
        }

        function clearDateFilter() {
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            renderUI();
        }

        function switchTable(t) {
            currentTable = t;
            selectedIds = new Set();
            hiddenColumns = new Set();
            renderSidebar();
            if(window.innerWidth < 1024) toggleMenu();
            loadTableData();
        }

        function showLoader(show) { document.getElementById('loader').style.display = show ? 'flex' : 'none'; }
        function closeModal() { document.getElementById('editModal').style.display = 'none'; }
        function closeBatchModal() { document.getElementById('batchEditModal').style.display = 'none'; }

        // ========== EXPORT ==========
        function exportCSV() {
            const rowsToExport = getVisibleRows();
            if(rowsToExport.length === 0) {
                showToast("No records to export matching current filters.", 'error');
                return;
            }
            
            const visibleCols = columns.filter(c => !hiddenColumns.has(c));
            let csv = visibleCols.join(",") + "\n";
            rowsToExport.forEach(row => {
                let values = visibleCols.map(c => `"${(row[c] || '').toString().replace(/"/g, '""')}"`);
                csv += values.join(",") + "\n";
            });
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('hidden', '');
            a.setAttribute('href', url);
            a.setAttribute('download', `${currentTable}_filtered_export_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            showToast(`Exported ${rowsToExport.length} records`);
        }

        async function handleImport(e) {
            const file = e.target.files[0];
            if (!file) return;
            if(!confirm("Are you sure? This will update existing records and add new ones.")) return;

            const fd = new FormData();
            fd.append('action', 'import_csv');
            fd.append('table', currentTable);
            fd.append('file', file);

            showLoader(true);
            try {
                const res = await fetch('api_master_data.php', { method: 'POST', body: fd }).then(r => r.json());
                if(res.status === 'success') {
                    showToast("Import successful: " + res.message);
                    loadTableData();
                } else {
                    showToast(res.error || "Import failed. Please check CSV format.", 'error');
                }
            } catch (err) {
                showToast("Network error during import", 'error');
            }
            showLoader(false);
            e.target.value = ""; 
        }

        init();
    </script>
</body>
</html>
