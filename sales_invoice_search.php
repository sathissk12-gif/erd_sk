<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>Invoice Archive & Search</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- 🔥 Security Framework -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="theme_engine.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --danger: #ef4444;
            --success: #10b981;
            --info: #3b82f6;
            --whatsapp: #25d366;
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.45);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --header-bg: rgba(15, 23, 42, 0.8);
            --card-hover-bg: rgba(30, 41, 59, 0.85);
            --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            --icon-bg: rgba(255, 255, 255, 0.05);
            --grad-1: #0f172a; --grad-2: #1e1b4b; --grad-3: #020617;
            --input-bg: rgba(15, 23, 42, 0.6);
        }

        [data-theme="light"] {
            --bg-color: #f8fafc;
            --card-bg: rgba(255, 255, 255, 0.85);
            --card-border: rgba(0, 0, 0, 0.05);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --header-bg: rgba(255, 255, 255, 0.95);
            --card-hover-bg: rgba(255, 255, 255, 1);
            --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
            --icon-bg: rgba(0, 0, 0, 0.03);
            --grad-1: #f8fafc; --grad-2: #e0e7ff; --grad-3: #f1f5f9;
            --input-bg: rgba(255, 255, 255, 0.8);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: 'Outfit', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex; flex-direction: column; overflow-x: hidden;
            background: linear-gradient(-45deg, var(--grad-1), var(--grad-2), var(--grad-3));
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            padding-top: env(safe-area-inset-top, 0px);
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .header {
            display: flex; justify-content: space-between; align-items: center;
            padding: calc(16px + env(safe-area-inset-top, 0px)) 20px 16px; background: var(--header-bg); border-bottom: 1px solid var(--card-border);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            position: sticky; top: 0; z-index: 1000;
        }

        .header-title {
            font-size: 19px; font-weight: 700;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
            display: flex; align-items: center; gap: 10px; cursor: pointer;
        }

        .view-toggle { display: flex; background: var(--icon-bg); padding: 4px; border-radius: 12px; border: 1px solid var(--card-border); }
        .view-toggle button {
            border: none; background: transparent; color: var(--text-muted);
            padding: 8px 12px; border-radius: 8px; cursor: pointer; font-family: inherit; font-weight: 600; font-size: 13px;
            display: flex; align-items: center; gap: 6px; transition: 0.3s;
        }
        .view-toggle button.active { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }

        .container { max-width: 1180px; margin: 0 auto; padding: 20px; width: 100%; flex: 1; display: flex; flex-direction: column; gap: 20px;}

        .card {
            background: var(--card-bg); border: 1px solid var(--card-border);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border-radius: 24px; padding: 24px; box-shadow: var(--card-shadow);
        }

        .search-row { display: flex; gap: 12px; margin-top: 20px; }
        .search-input {
            flex: 1; background: var(--input-bg); border: 1px solid var(--card-border); color: var(--text-main);
            border-radius: 16px; padding: 15px 20px; font-family: 'Outfit'; outline: none; transition: 0.3s; font-size: 16px;
        }
        .search-input:focus { border-color: var(--primary-light); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #ec4899); border: none; font-family: 'Outfit';
            color: white; font-weight: 700; font-size: 16px; border-radius: 16px; cursor: pointer;
            padding: 0 25px; transition: 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-primary:active { transform: scale(0.95); }

        /* Horizontal Layout (Table) */
        .table-wrap { overflow-x: auto; border-radius: 18px; border: 1px solid var(--card-border); background: rgba(0,0,0,0.1); width: 100%; }
        table { width: 100%; border-collapse: collapse; min-width: 850px; text-align: left; }
        th { background: rgba(0, 0, 0, 0.2); color: var(--text-muted); font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 16px 20px; }
        td { padding: 18px 20px; border-bottom: 1px solid var(--card-border); font-size: 14px; }

        /* Vertical Layout (Cards) */
        .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
        .v-card { 
            background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 24px; padding: 20px; 
            display: flex; flex-direction: column; gap: 15px; transition: 0.3s;
            backdrop-filter: blur(10px);
        }
        .v-card:active { transform: scale(0.98); background: var(--card-hover-bg); }
        .v-header { display: flex; justify-content: space-between; align-items: flex-start; }

        .invoice-no { font-weight: 800; color: var(--primary-light); font-size: 16px; letter-spacing: -0.5px; }
        .meta { color: var(--text-muted); font-size: 12px; font-weight: 500; margin-top: 2px; }
        .amount { font-weight: 800; font-size: 17px; color: var(--text-main); }
        .status-chip { 
            padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 800; 
            text-transform: uppercase; letter-spacing: 0.5px; background: rgba(99, 102, 241, 0.15); color: var(--primary-light);
        }

        .action-stack { display: flex; gap: 8px; width: 100%; }
        .action-btn {
            flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px; border-radius: 12px; text-decoration: none; border: none; font-family: 'Outfit';
            color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; transition: 0.2s;
        }
        .view { background: rgba(59, 130, 246, 0.15); color: var(--info); border: 1px solid rgba(59, 130, 246, 0.2); }
        .pdf { background: rgba(99, 102, 241, 0.15); color: var(--primary-light); border: 1px solid rgba(99, 102, 241, 0.2); }
        .share { background: #25d366; color: white; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3); }

        .empty { padding: 60px 20px; text-align: center; color: var(--text-muted); grid-column: 1 / -1; }
        .empty i { font-size: 40px; margin-bottom: 15px; opacity: 0.3; display: block; }

        @media (max-width: 768px) {
            .header { padding: calc(12px + env(safe-area-inset-top, 0px)) 16px 12px; }
            .header-title { font-size: 17px; }
            .container { padding: 16px; gap: 16px; }
            .search-row { flex-direction: column; }
            .btn-primary { padding: 15px; width: 100%; }
            .card { padding: 20px; border-radius: 20px; }
            
            /* On mobile, table is usually bad, so we'll likely force vertical or make it very scrollable */
            #tableContainer { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            
            .card-grid { grid-template-columns: 1fr; }
            .view-toggle { display: none; } /* Hide toggle on mobile, force card view */
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-title" onclick="window.location='index.html'">
            <i class="fa-solid fa-chevron-left" style="font-size: 14px;"></i>
            Sales Search
        </div>
        <div class="view-toggle" id="viewToggle">
            <button class="active" id="btnH" onclick="setView('H')"><i class="fa-solid fa-table-list"></i></button>
            <button id="btnV" onclick="setView('V')"><i class="fa-solid fa-grip-vertical"></i></button>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h1 style="font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">Sales Invoices</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Find and share professional GST bills instantly.</p>
            
            <div class="search-row">
                <input id="qInput" class="search-input" type="text" placeholder="Invoice No / Vehicle / Name" oninput="debouncedSearch()">
                <button class="btn-primary" onclick="search()">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Search</span>
                </button>
            </div>
        </div>

        <div id="tableContainer">
            <div class="card" style="padding: 0; overflow: hidden;">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="resBody">
                            <tr><td colspan="5" class="empty"><i class="fa-solid fa-search"></i>Type to search records.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="gridContainer" style="display:none;"></div>
    </div>

    <script>
        let currentView = 'H';
        let latestData = [];

        // Auto-switch to Vertical view on Mobile
        if(window.innerWidth <= 768) {
            currentView = 'V';
        }

        function setView(v) {
            currentView = v;
            const btnH = document.getElementById('btnH');
            const btnV = document.getElementById('btnV');
            if(btnH) btnH.classList.toggle('active', v === 'H');
            if(btnV) btnV.classList.toggle('active', v === 'V');
            
            document.getElementById('tableContainer').style.display = (v === 'H') ? 'block' : 'none';
            document.getElementById('gridContainer').style.display = (v === 'V') ? 'block' : 'none';
            if(latestData.length) render(latestData);
        }

        // Initialize view
        window.addEventListener('DOMContentLoaded', () => {
            if(window.innerWidth <= 768) setView('V');
            else setView('H');
        });

        function currency(v){ return new Intl.NumberFormat('en-IN').format(v||0); }

        let searchTimer;
        function debouncedSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(search, 350); 
        }

        async function search() {
            const q = document.getElementById("qInput").value;
            const resBody = document.getElementById("resBody");
            const gridContainer = document.getElementById("gridContainer");

            if(!q) {
                resBody.innerHTML = `<tr><td colspan="5" class="empty"><i class="fa-solid fa-search"></i>Type to search records.</td></tr>`;
                gridContainer.innerHTML = "";
                return;
            }

            resBody.innerHTML = `<tr><td colspan="5" class="empty"><i class="fa-solid fa-spinner fa-spin"></i>Searching...</td></tr>`;
            gridContainer.innerHTML = `<div class="empty"><i class="fa-solid fa-spinner fa-spin"></i>Searching...</div>`;

            try {
                const res = await fetch(`api_sales.php?action=search-invoices&query=${encodeURIComponent(q)}`);
                latestData = await res.json();
                render(latestData);
            } catch(e) {
                resBody.innerHTML = `<tr><td colspan="5" class="empty">Error fetching data.</td></tr>`;
            }
        }

        function render(rows) {
            const tableBody = document.getElementById("resBody");
            const gridContainer = document.getElementById("gridContainer");

            if(!rows || !rows.length) {
                tableBody.innerHTML = `<tr><td colspan="5" class="empty">No results found.</td></tr>`;
                gridContainer.innerHTML = `<div class="empty">No results found.</div>`;
                return;
            }
            
            let tableHtml = "";
            let gridHtml = `<div class="card-grid">`;

            rows.forEach(row => {
                const shareKey = row.uid || row.invoice_no;
                const viewUrl = `sales_invoice.php?${row.uid ? 'uid=' : 'invoice_no='}${shareKey}`;
                const pdfUrl = `${viewUrl}&download=1`;
                const waMsg = `Dear ${row.customer_name},\n\nYour Sales Invoice for Vehicle ${row.vehicle_no} is ready.\n\nInvoice: ${row.invoice_no}\nAmount: ₹${currency(row.paid_amount)}\n\nView Bill: https://erd.traxengps.in/${viewUrl}\n\n-- SK ENTERPRISES`;
                const waUrl = `https://wa.me/91${row.mobile_number || ''}?text=${encodeURIComponent(waMsg)}`;

                tableHtml += `
                    <tr>
                        <td>
                            <div class="invoice-no">${row.invoice_no}</div>
                            <div class="meta">${row.invoice_date}</div>
                        </td>
                        <td>
                            <div style="font-weight:700; color:var(--text-main)">${row.customer_name}</div>
                            <div class="meta">${row.mobile_number || ''}</div>
                        </td>
                        <td><div style="font-weight:600;">${row.vehicle_no}</div></td>
                        <td>
                            <div class="amount">₹ ${currency(row.paid_amount)}</div>
                            <div class="meta">Total: ₹ ${currency(row.total_amount)}</div>
                        </td>
                        <td>
                            <div class="action-stack">
                                <a href="${viewUrl}" class="action-btn view" target="_blank"><i class="fa-solid fa-eye"></i></a>
                                <a href="${pdfUrl}" class="action-btn pdf" target="_blank"><i class="fa-solid fa-file-pdf"></i></a>
                                <a href="${waUrl}" class="action-btn share" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
                            </div>
                        </td>
                    </tr>
                `;

                gridHtml += `
                    <div class="v-card">
                        <div class="v-header">
                            <div>
                                <div class="invoice-no">${row.invoice_no}</div>
                                <div class="meta">${row.invoice_date}</div>
                            </div>
                            <span class="status-chip">${row.status || 'PAID'}</span>
                        </div>
                        <div>
                            <div style="font-weight:800; font-size:18px; color:var(--text-main); letter-spacing:-0.3px;">${row.customer_name}</div>
                            <div class="meta" style="font-size:13px; margin-top:4px;">
                                <i class="fa-solid fa-phone" style="font-size:10px; margin-right:4px;"></i> ${row.mobile_number || 'N/A'} • 
                                <i class="fa-solid fa-car" style="font-size:10px; margin-right:4px;"></i> ${row.vehicle_no}
                            </div>
                        </div>
                        <div style="padding:15px; background:rgba(0,0,0,0.15); border-radius:16px; display:flex; justify-content:space-between; align-items:center; border: 1px solid var(--card-border);">
                            <span class="meta" style="text-transform:uppercase; letter-spacing:1px; font-weight:700;">Paid Amount</span>
                            <span class="amount" style="color: var(--primary-light);">₹ ${currency(row.paid_amount)}</span>
                        </div>
                        <div class="action-stack">
                            <a href="${viewUrl}" class="action-btn view" target="_blank"><i class="fa-solid fa-eye"></i> View</a>
                            <a href="${pdfUrl}" class="action-btn pdf" target="_blank"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                            <a href="${waUrl}" class="action-btn share" target="_blank"><i class="fa-brands fa-whatsapp"></i> Share</a>
                        </div>
                    </div>
                `;
            });
            gridHtml += `</div>`;

            tableBody.innerHTML = tableHtml;
            gridContainer.innerHTML = gridHtml;
        }
    </script>
</body>
</html>
