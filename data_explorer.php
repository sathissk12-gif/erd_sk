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
            --card-bg: rgba(30, 41, 59, 0.5);
        }

        :root[data-theme="light"] {
            --text-dim: #64748b;
            --text-muted: #64748b;
            --card-bg: rgba(255, 255, 255, 0.6);
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
        td { 
            padding: 16px 20px; font-size: 14px; border-bottom: 1px solid var(--border); 
            white-space: nowrap; color: var(--text); transition: 0.2s;
        }
        tr:hover td { background: rgba(255,255,255,0.03); color: var(--text); }

        /* Mobile Cards */
        .mobile-card-list { display: flex; flex-direction: column; gap: 12px; }
        .data-card { 
            background: var(--card-base, var(--card-bg)); border: 1px solid var(--border); border-radius: 20px; padding: 18px;
            display: flex; flex-direction: column; gap: 12px; backdrop-filter: blur(10px);
        }
        .card-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border); padding-bottom: 10px; }
        .card-id { font-size: 12px; font-weight: 800; color: var(--primary); }
        .card-title { font-size: 15px; font-weight: 700; color: var(--text); }
        .card-body { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .data-item { display: flex; flex-direction: column; gap: 2px; }
        .data-label { font-size: 9px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.5px; }
        .data-value { font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        @media (min-width: 1024px) {
            .desktop-table-container { display: block; }
            .mobile-card-list { display: none; }
        }

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
    </style>
</head>
<body>

    <header>
        <div class="header-left">
            <div class="icon-btn menu" onclick="toggleMenu()"><i class="fa-solid fa-bars-staggered"></i></div>
            <div class="header-title">CLOUD<span>CONSOLE</span></div>
        </div>
        <div style="display:flex; gap:10px;">
            <!-- 🎨 Ultra Modern Theme Cycle Button -->
            <div class="icon-btn" onclick="toggleTheme()" title="Change Theme"><i class="fa-solid fa-circle-half-stroke"></i></div>
            <div class="icon-btn" onclick="loadTableData()"><i class="fa-solid fa-rotate"></i></div>
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

                <div class="toolbar-actions" style="display:flex; gap:8px;">
                    <!-- 📥 Unified Export / Date Filter Dropdown Icon -->
                    <div class="btn-tool" onclick="toggleExportPanel(event)" title="Export & Date Filter" style="position: relative; background: var(--primary-glow); border-color: var(--primary);">
                        <i class="fa-solid fa-file-export" style="color:var(--primary); font-size:16px;"></i>
                    </div>

                    <div class="btn-tool" onclick="document.getElementById('csvInput').click()" title="Import CSV"><i class="fa-solid fa-file-import"></i></div>
                    <input type="file" id="csvInput" style="display:none;" accept=".csv" onchange="handleImport(event)">
                    <div class="btn-tool" onclick="addField()" title="Add Field"><i class="fa-solid fa-plus-minus"></i></div>
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

    <script>
        const AUTH_PWD = "8508200253";
        let currentTable = "sales_log";
        let tableData = [];
        let columns = [];
        let allDatabaseTables = [];
        let currentEditingId = null;
        let activeFilters = {};
        let sortState = { column: null, direction: 'asc' };

        const tableGroups = {
            "Operations": ['sales_log', 'invoice_log', 'renewal_log', 'renewal_invoice_log', 'customerdatas'],
            "Inventory": ['device_master', 'stock_ledger', 'software_master'],
            "Accounts": ['office_sales', 'office_renewal', 'sim_settlement', 'dealer_ledger'],
            "System": ['price_master', 'settings']
        };

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

        function toggleExportPanel(event) {
            if (event) event.stopPropagation();
            const panel = document.getElementById('exportPanel');
            const isVisible = panel.style.display === 'flex';
            panel.style.display = isVisible ? 'none' : 'flex';
        }

        document.addEventListener('click', function() {
            const panel = document.getElementById('exportPanel');
            if (panel) panel.style.display = 'none';
        });

        async function loadTableData() {
            showLoader(true);
            try {
                const res = await fetch(`api_master_data.php?action=get_data&table=${currentTable}`).then(r => r.json());
                columns = res.columns || [];
                tableData = res.data || [];
                activeFilters = {};
                sortState = { column: null, direction: 'asc' };
                document.getElementById('masterSearch').value = '';
                
                // Reset Date Filters
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';

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

                renderUI();
            } catch (e) { console.error(e); }
            showLoader(false);
        }

        function renderUI() {
            renderGrid(); // Desktop
            renderCards(); // Mobile
        }

        function renderGrid() {
            const head = document.getElementById('gridHead');
            const body = document.getElementById('gridBody');
            head.innerHTML = `
                <tr>
                    ${columns.map(c => `
                        <th onclick="toggleSort('${c}')" style="cursor:pointer;">
                            ${c.replace(/_/g, ' ')}${getSortIcon(c)}
                        </th>
                    `).join('')}
                </tr>`;
            
            const rows = getVisibleRows();
            body.innerHTML = rows.map(row => `
                <tr onclick="openRecord('${row.id}')">
                    ${columns.map(c => `<td>${escapeHtml(row[c] || '-')}</td>`).join('')}
                </tr>
            `).join('') || '<tr><td colspan="100" style="text-align:center; padding:50px;">No data</td></tr>';
        }

        function renderCards() {
            const container = document.getElementById('mobileCards');
            const rows = getVisibleRows();
            
            if(!rows.length) {
                container.innerHTML = '<div class="empty" style="text-align:center; padding:40px; color:var(--text-dim);">No records found.</div>';
                return;
            }

            container.innerHTML = rows.map(row => {
                const titleKey = columns[1] || 'id';
                return `
                    <div class="data-card" onclick="openRecord('${row.id}')">
                        <div class="card-header">
                            <div class="card-title">${escapeHtml(row[titleKey] || 'No Title')}</div>
                            <div class="card-id">#${row.id}</div>
                        </div>
                        <div class="card-body">
                            ${columns.slice(2, 6).map(c => `
                                <div class="data-item">
                                    <div class="data-label">${c.replace(/_/g, ' ')}</div>
                                    <div class="data-value">${escapeHtml(row[c] || '-')}</div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
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

            // 3. Sorting
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
                loadTableData();
            } catch(err) {
                alert("Error saving data");
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
            if(pwd !== AUTH_PWD) return alert("Unauthorized access denied.");
            
            const fd = new FormData();
            fd.append('action', 'delete_row');
            fd.append('table', currentTable);
            fd.append('ids', currentEditingId);
            
            showLoader(true);
            await fetch('api_master_data.php', { method: 'POST', body: fd });
            closeModal();
            loadTableData();
        }

        async function addField() {
            const name = prompt("Enter New Field Name (No spaces, use _):");
            if(!name) return;
            const fd = new FormData();
            fd.append('action', 'add_column');
            fd.append('table', currentTable);
            fd.append('name', name);
            showLoader(true);
            await fetch('api_master_data.php', { method: 'POST', body: fd });
            loadTableData();
        }

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
            renderSidebar();
            if(window.innerWidth < 1024) toggleMenu();
            loadTableData();
        }

        function showLoader(show) { document.getElementById('loader').style.display = show ? 'flex' : 'none'; }
        function closeModal() { document.getElementById('editModal').style.display = 'none'; }

        function exportCSV() {
            const rowsToExport = getVisibleRows();
            if(rowsToExport.length === 0) {
                alert("No records to export matching current filters.");
                return;
            }
            
            let csv = columns.join(",") + "\n";
            rowsToExport.forEach(row => {
                let values = columns.map(c => `"${(row[c] || '').toString().replace(/"/g, '""')}"`);
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
                    alert("Import successful: " + res.message);
                    loadTableData();
                } else {
                    alert(res.error || "Import failed. Please check CSV format.");
                }
            } catch (err) {
                alert("Network error during import");
            }
            showLoader(false);
            e.target.value = ""; 
        }

        init();
    </script>
</body>
</html>
