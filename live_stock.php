<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Live Stock | SK LOGIC</title>
    
    <!-- Ultra Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- 🔥 Security -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>

    <style>
        :root {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.4);
            --secondary: #06b6d4;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --card-base: rgba(30, 41, 59, 0.3);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-muted: #94a3b8;
            --success: #10b981;
            --warn: #f59e0b;
            --danger: #ef4444;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text);
            min-height: 100vh;
            padding-top: env(safe-area-inset-top, 0px);
            padding-bottom: 50px;
        }

        header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(3, 7, 18, 0.7); backdrop-filter: blur(25px);
            padding: calc(12px + env(safe-area-inset-top, 0px)) 25px 18px; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .back-link { text-decoration: none; color: white; display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14px; }
        
        .container { max-width: 720px; margin: 20px auto; padding: 0 20px; animation: slideUp 0.6s ease-out; }
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        /* 📊 Summary Bar */
        .summary-bar {
            display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 25px;
        }
        .sum-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 18px; padding: 18px 16px;
            backdrop-filter: blur(20px); text-align: center;
        }
        .sum-card .label { font-size: 9px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1px; }
        .sum-card .val { font-size: 22px; font-weight: 800; font-family: 'Outfit'; margin-top: 6px; }

        /* 🔍 IMEI Trace Search */
        .trace-section {
            background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 20px;
            backdrop-filter: blur(20px); margin-bottom: 25px;
        }
        .trace-section .section-title {
            font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1.5px;
            margin-bottom: 15px; display: flex; align-items: center; gap: 8px;
        }
        .trace-input-wrap {
            display: flex; gap: 10px;
        }
        .trace-input-wrap input {
            flex: 1; padding: 16px 20px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 15px; font-family: 'Outfit'; font-weight: 700; letter-spacing: 1px; outline: none; transition: 0.3s;
        }
        .trace-input-wrap input:focus { border-color: var(--primary); box-shadow: 0 0 20px var(--primary-glow); }
        .trace-input-wrap input::placeholder { font-weight: 400; letter-spacing: 0; opacity: 0.5; }
        .trace-btn {
            padding: 16px 24px; border: none; border-radius: 16px; 
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white; font-weight: 800; font-size: 14px; cursor: pointer; white-space: nowrap;
            transition: 0.3s; box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
        }
        .trace-btn:active { transform: scale(0.95); }
        .trace-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        /* 🕵️ Trace Results */
        .trace-panel { display: none; margin-top: 18px; animation: slideUp 0.4s ease-out; }
        .trace-panel.open { display: block; }
        .trace-tabs { display: flex; gap: 6px; margin-bottom: 15px; flex-wrap: wrap; }
        .trace-tab {
            padding: 8px 16px; border-radius: 99px; font-size: 10px; font-weight: 800; cursor: pointer;
            background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); color: var(--text-muted); transition: 0.3s;
            text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;
        }
        .trace-tab.active { background: var(--primary); border-color: var(--primary); color: white; box-shadow: 0 5px 15px var(--primary-glow); }
        .trace-tab .badge { background: rgba(255,255,255,0.15); padding: 2px 8px; border-radius: 99px; font-size: 9px; }

        .trace-content { max-height: 400px; overflow-y: auto; border-radius: 16px; background: rgba(0,0,0,0.2); padding: 5px; }
        .trace-table { width: 100%; border-collapse: collapse; }
        .trace-table th { text-align: left; font-size: 9px; text-transform: uppercase; color: var(--text-muted); padding: 12px 10px 8px; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        .trace-table td { padding: 12px 10px; font-size: 12px; border-bottom: 1px solid rgba(255,255,255,0.03); vertical-align: top; }

        .device-banner {
            background: rgba(139, 92, 246, 0.08); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 16px;
            padding: 16px 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;
        }
        .device-banner .info { display: flex; flex-direction: column; gap: 4px; }
        .device-banner .info .model { font-size: 16px; font-weight: 800; }
        .device-banner .info .detail { font-size: 11px; color: var(--text-muted); }
        .device-banner .status-tag {
            padding: 6px 16px; border-radius: 99px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;
        }
        .status-tag.in-stock { background: rgba(16,185,129,0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }
        .status-tag.sold { background: rgba(59,130,246,0.15); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); }
        .status-tag.returned { background: rgba(239,68,68,0.15); color: var(--danger); border: 1px solid rgba(239,68,68,0.3); }

        .empty-state { text-align: center; padding: 30px 20px; color: var(--text-muted); font-size: 13px; }
        .timeline { position: relative; padding-left: 25px; }
        .timeline::before { content: ''; position: absolute; left: 8px; top: 5px; bottom: 5px; width: 2px; background: var(--border); }
        .tl-item { position: relative; padding: 12px 0 12px 20px; border-left: 2px solid var(--primary); margin-left: -2px; }
        .tl-item::before { content: ''; position: absolute; left: -7px; top: 16px; width: 12px; height: 12px; border-radius: 50%; background: var(--primary); border: 2px solid var(--bg); }
        .tl-item .tl-title { font-size: 13px; font-weight: 700; }
        .tl-item .tl-meta { font-size: 10px; color: var(--text-muted); margin-top: 3px; }
        .tl-item .tl-detail { font-size: 11px; margin-top: 4px; color: rgba(255,255,255,0.7); }

        .cat-label { font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 2px; margin: 25px 5px 12px; display: flex; align-items: center; gap: 10px; }
        .cat-label::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .stock-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        
        .stock-card { 
            background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 20px; 
            backdrop-filter: blur(20px); display: flex; flex-direction: column; gap: 10px;
            transition: 0.3s; position: relative; overflow: hidden; cursor: default;
        }
        .stock-card::before { content: ''; position: absolute; top:0; left:0; width:4px; height:100%; background: var(--secondary); opacity: 0.5; }
        .stock-card.low::before { background: var(--warn); }
        .stock-card.critical::before { background: var(--danger); }
        .stock-card.critical { border-color: rgba(239,68,68,0.3); background: rgba(239,68,68,0.04); }
        .stock-card.low { border-color: rgba(245,158,11,0.3); background: rgba(245,158,11,0.04); }

        .stock-card .name { font-size: 13px; font-weight: 700; color: var(--text-muted); }
        .stock-card .val { font-size: 32px; font-weight: 800; font-family: 'Outfit'; color: var(--text); }
        .stock-card .type { font-size: 9px; font-weight: 800; text-transform: uppercase; color: var(--secondary); letter-spacing: 1px; }
        .stock-card .alert-chip {
            position: absolute; top: 10px; right: 10px; font-size: 8px; font-weight: 800; padding: 3px 8px;
            border-radius: 99px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .alert-chip.low { background: rgba(245,158,11,0.2); color: var(--warn); border: 1px solid rgba(245,158,11,0.3); }
        .alert-chip.critical { background: rgba(239,68,68,0.2); color: var(--danger); border: 1px solid rgba(239,68,68,0.3); }

        .loader { text-align: center; padding: 50px 0; color: var(--text-muted); }
        .spinner { width: 30px; height: 30px; border: 3px solid rgba(255,255,255,0.05); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 15px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .text-green { color: var(--success); }
        .text-warn { color: var(--warn); }
        .text-danger { color: var(--danger); }

        /* 🪟 Software Detail Drawer */
        .detail-overlay {
            position: fixed; inset: 0; z-index: 2000;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(8px);
            display: none; animation: fadeIn 0.25s ease;
        }
        .detail-overlay.open { display: block; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .detail-drawer {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 2001;
            max-height: 85vh; overflow-y: auto;
            background: rgba(10, 14, 23, 0.96); backdrop-filter: blur(30px);
            border: 1px solid var(--border); border-radius: 24px 24px 0 0;
            padding: 0 0 30px; transform: translateY(100%);
            transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
            box-shadow: 0 -20px 60px rgba(0,0,0,0.6);
        }
        .detail-drawer.open { transform: translateY(0); }

        .detail-handle {
            width: 40px; height: 4px; border-radius: 99px;
            background: rgba(255,255,255,0.15); margin: 10px auto 6px;
        }
        .detail-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 20px 15px; border-bottom: 1px solid var(--border);
        }
        .detail-header h2 {
            font-size: 16px; font-weight: 800; font-family: 'Outfit';
            display: flex; align-items: center; gap: 10px;
        }
        .detail-header .close-btn {
            width: 36px; height: 36px; border-radius: 50%; border: none;
            background: rgba(255,255,255,0.06); color: white; font-size: 18px;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: 0.2s;
        }
        .detail-header .close-btn:hover { background: rgba(239,68,68,0.2); color: var(--danger); }

        .detail-body { padding: 16px 20px; }

        /* Summary mini-cards inside drawer */
        .detail-summary {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;
        }
        .detail-summary .d-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 16px; padding: 14px; text-align: center;
        }
        .detail-summary .d-card .d-label {
            font-size: 8px; font-weight: 800; text-transform: uppercase;
            color: var(--text-muted); letter-spacing: 1px;
        }
        .detail-summary .d-card .d-val {
            font-size: 20px; font-weight: 800; font-family: 'Outfit'; margin-top: 4px;
        }
        .detail-summary .d-card .d-sub {
            font-size: 9px; color: var(--text-muted); margin-top: 2px;
        }

        .detail-section-title {
            font-size: 10px; font-weight: 800; text-transform: uppercase;
            color: var(--primary); letter-spacing: 1.5px; margin: 20px 0 10px;
            display: flex; align-items: center; gap: 8px;
        }
        .detail-section-title::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }

        .detail-table { width: 100%; border-collapse: collapse; }
        .detail-table th {
            text-align: left; font-size: 8px; text-transform: uppercase;
            color: var(--text-muted); padding: 10px 8px 6px;
            letter-spacing: 1px; border-bottom: 1px solid var(--border);
        }
        .detail-table td {
            padding: 10px 8px; font-size: 11px; border-bottom: 1px solid rgba(255,255,255,0.03);
            vertical-align: middle;
        }
        .detail-table tr:last-child td { border-bottom: none; }

        .detail-empty {
            text-align: center; padding: 30px 10px; color: var(--text-muted);
            font-size: 12px;
        }
        .detail-empty i { font-size: 28px; margin-bottom: 8px; opacity: 0.4; }

        .stock-card.clickable { cursor: pointer; }
        .stock-card.clickable:active { transform: scale(0.97); }

        @media (max-width: 500px) {
            .detail-drawer { max-height: 90vh; }
            .detail-summary { grid-template-columns: 1fr 1fr; gap: 8px; }
            .detail-summary .d-card { padding: 10px; }
            .detail-summary .d-card .d-val { font-size: 17px; }
        }

        @media (max-width: 500px) {
            .summary-bar { grid-template-columns: 1fr 1fr; }
            .stock-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            .stock-card { padding: 16px; }
            .stock-card .val { font-size: 26px; }
        }
    </style>
</head>
<body>

    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div style="font-size: 10px; font-weight: 800; color: #10b981; text-transform: uppercase;">Live Inventory • IMEI Trace</div>
    </header>

    <div class="container">

        <!-- 📊 Summary Bar -->
        <div class="summary-bar">
            <div class="sum-card">
                <div class="label">Total Devices</div>
                <div class="val" id="totalDevices">0</div>
            </div>
            <div class="sum-card">
                <div class="label">Models</div>
                <div class="val" id="totalModels">0</div>
            </div>
            <div class="sum-card">
                <div class="label">Low Stock ⚠️</div>
                <div class="val" id="lowCount" style="color: var(--warn);">0</div>
            </div>
        </div>

        <!-- 🔍 IMEI Trace Section -->
        <div class="trace-section">
            <div class="section-title"><i class="fa-solid fa-fingerprint"></i> IMEI Full Trace</div>
            <div class="trace-input-wrap">
                <input type="text" id="imeiInput" placeholder="Enter IMEI Number..." autocomplete="off" spellcheck="false">
                <button class="trace-btn" id="traceBtn" onclick="traceIMEI()"><i class="fa-solid fa-search"></i> Trace</button>
            </div>
            <div id="tracePanel" class="trace-panel"></div>
        </div>

        <!-- 📦 Live Stock Grid -->
        <div id="loading" class="loader"><div class="spinner"></div>Syncing Stock Data...</div>
        <div id="content"></div>
    </div>

    <!-- 🪟 Software Detail Drawer -->
    <div class="detail-overlay" id="detailOverlay" onclick="closeSoftwareDetail()"></div>
    <div class="detail-drawer" id="detailDrawer">
        <div class="detail-handle"></div>
        <div class="detail-header">
            <h2><i class="fa-solid fa-cube" style="color:var(--secondary)"></i> <span id="detailTitle">Software</span></h2>
            <button class="close-btn" onclick="closeSoftwareDetail()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="detail-body" id="detailBody">
            <div class="loader" style="padding:30px 0"><div class="spinner"></div>Loading Details...</div>
        </div>
    </div>

    <script>
        // 📊 Number Animation
        function animateValue(obj, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                obj.innerText = Math.floor(progress * (end - start) + start);
                if (progress < 1) window.requestAnimationFrame(step);
            };
            window.requestAnimationFrame(step);
        }

        function formatCurrency(n) {
            return '₹' + parseInt(n).toLocaleString('en-IN');
        }

        // 🔍 IMEI Trace
        async function traceIMEI() {
            const input = document.getElementById('imeiInput');
            const btn = document.getElementById('traceBtn');
            const panel = document.getElementById('tracePanel');
            const imei = input.value.trim();

            if (!imei) { input.style.borderColor = '#ef4444'; setTimeout(() => input.style.borderColor = '', 1500); return; }

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Tracing...';
            panel.innerHTML = '<div class="loader" style="padding:20px 0"><div class="spinner"></div>Scanning Records...</div>';
            panel.classList.add('open');

            try {
                const res = await fetch('api_master_data.php?action=get_imei_trace&imei=' + encodeURIComponent(imei));
                const data = await res.json();

                if (data.status === 'error') {
                    panel.innerHTML = `
                        <div style="text-align:center; padding:30px; color: var(--text-muted);">
                            <i class="fa-solid fa-circle-exclamation" style="font-size: 40px; color: var(--danger); margin-bottom: 10px;"></i>
                            <div style="font-weight:700; font-size:14px;">${data.error || 'IMEI not found'}</div>
                            <div style="font-size:11px; margin-top:6px;">Check the IMEI number and try again</div>
                        </div>
                    `;
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-search"></i> Trace';
                    return;
                }

                const device = data.device;
                const sales = data.sales || [];
                const invoices = data.invoices || [];
                const returns = data.returns || [];
                const ledger = data.ledger || [];

                // Build the trace panel
                let traceHtml = '';

                // Device Banner
                const status = (device.status || '').toUpperCase();
                let statusClass = 'in-stock';
                let statusLabel = device.status || '—';
                if (status === 'SOLD') { statusClass = 'sold'; }
                else if (status === 'RETURNED' || status.includes('RETURN')) { statusClass = 'returned'; }

                traceHtml += `
                    <div class="device-banner">
                        <div class="info">
                            <div class="model">${device.device_model || '—'}</div>
                            <div class="detail">
                                SL #${device.sl_no || '—'} &middot; ${device.supplier_name || '—'} &middot; Rate: ${formatCurrency(device.rate || 0)}
                                ${device.holder ? '&middot; ' + device.holder : ''}
                                ${device.date ? '&middot; Added: ' + device.date : ''}
                            </div>
                            <div class="detail" style="font-family:'Outfit'; letter-spacing:1px; color:var(--secondary);">${device.imei || device.imei_no || imei}</div>
                        </div>
                        <div class="status-tag ${statusClass}">${statusLabel}</div>
                    </div>
                `;

                // Tabs
                const tabCounts = {
                    sales: sales.length,
                    invoices: invoices.length,
                    returns: returns.length,
                    ledger: ledger.length
                };

                traceHtml += `
                    <div class="trace-tabs">
                        <div class="trace-tab active" onclick="switchTraceTab(this, 'tab-sales')">
                            <i class="fa-solid fa-cart-shopping"></i> Sales <span class="badge">${tabCounts.sales}</span>
                        </div>
                        <div class="trace-tab" onclick="switchTraceTab(this, 'tab-invoices')">
                            <i class="fa-solid fa-file-invoice"></i> Invoices <span class="badge">${tabCounts.invoices}</span>
                        </div>
                        <div class="trace-tab" onclick="switchTraceTab(this, 'tab-returns')">
                            <i class="fa-solid fa-rotate-left"></i> Returns <span class="badge">${tabCounts.returns}</span>
                        </div>
                        <div class="trace-tab" onclick="switchTraceTab(this, 'tab-ledger')">
                            <i class="fa-solid fa-journal-whills"></i> Ledger <span class="badge">${tabCounts.ledger}</span>
                        </div>
                    </div>
                    <div id="traceContent" class="trace-content">
                `;

                // Tab: Sales
                traceHtml += `<div id="tab-sales" class="trace-tab-content">`;
                if (sales.length > 0) {
                    traceHtml += `
                        <table class="trace-table">
                            <tr><th>Date</th><th>Customer</th><th>Vehicle</th><th>Amount</th></tr>
                            ${sales.map(s => `
                                <tr>
                                    <td>${s.sale_date || '—'}</td>
                                    <td>${s.customer_name || '—'}<br><span style="font-size:9px;color:var(--text-muted)">${s.sales_person || ''}</span></td>
                                    <td>${s.vehicle_no || '—'}</td>
                                    <td>${formatCurrency(s.selling_price || 0)}</td>
                                </tr>
                            `).join('')}
                        </table>
                    `;
                } else {
                    traceHtml += `<div class="empty-state"><i class="fa-regular fa-rectangle-list" style="font-size:24px;margin-bottom:8px;"></i><br>No sales records for this IMEI</div>`;
                }
                traceHtml += `</div>`;

                // Tab: Invoices
                traceHtml += `<div id="tab-invoices" class="trace-tab-content" style="display:none">`;
                if (invoices.length > 0) {
                    traceHtml += `
                        <table class="trace-table">
                            <tr><th>Invoice #</th><th>Date</th><th>Amount</th><th>Status</th></tr>
                            ${invoices.map(inv => `
                                <tr>
                                    <td style="font-family:'Outfit';font-weight:700;">${inv.invoice_no || '—'}</td>
                                    <td>${inv.invoice_date || '—'}</td>
                                    <td>${formatCurrency(inv.total_amount || 0)}<br><span style="font-size:9px;color:var(--text-muted)">Paid: ${formatCurrency(inv.paid_amount || 0)}</span></td>
                                    <td><span style="font-size:10px;font-weight:700;${(inv.status||'').toLowerCase()==='closed'?'color:var(--success)':'color:var(--warn)'}">${inv.status || '—'}</span></td>
                                </tr>
                            `).join('')}
                        </table>
                    `;
                } else {
                    traceHtml += `<div class="empty-state"><i class="fa-regular fa-file" style="font-size:24px;margin-bottom:8px;"></i><br>No invoices for this IMEI</div>`;
                }
                traceHtml += `</div>`;

                // Tab: Returns
                traceHtml += `<div id="tab-returns" class="trace-tab-content" style="display:none">`;
                if (returns.length > 0) {
                    traceHtml += `
                        <table class="trace-table">
                            <tr><th>Date</th><th>Type</th><th>Reason</th><th>Status</th></tr>
                            ${returns.map(r => `
                                <tr>
                                    <td>${r.return_date || '—'}</td>
                                    <td>${r.return_type || '—'}</td>
                                    <td>${r.return_reason || '—'}<br><span style="font-size:9px;color:var(--text-muted)">${r.notes || ''}</span></td>
                                    <td><span style="font-size:10px;font-weight:700;${(r.return_status||'')==='CANCELLED'?'color:var(--danger)':'color:var(--success)'}">${r.return_status || '—'}</span></td>
                                </tr>
                            `).join('')}
                        </table>
                    `;
                } else {
                    traceHtml += `<div class="empty-state"><i class="fa-solid fa-rotate-left" style="font-size:24px;margin-bottom:8px;"></i><br>No return/replacement records</div>`;
                }
                traceHtml += `</div>`;

                // Tab: Ledger
                traceHtml += `<div id="tab-ledger" class="trace-tab-content" style="display:none">`;
                if (ledger.length > 0) {
                    traceHtml += `
                        <table class="trace-table">
                            <tr><th>Date</th><th>Item</th><th>Qty</th><th>Remark</th></tr>
                            ${ledger.map(l => `
                                <tr>
                                    <td>${l.date || '—'}</td>
                                    <td>${l.item_name || '—'}<br><span style="font-size:9px;color:var(--text-muted)">${l.item_type || ''}</span></td>
                                    <td style="font-family:'Outfit';font-weight:700;${parseInt(l.qty) < 0 ? 'color:var(--danger)' : 'color:var(--success)'}">${l.qty || 0}</td>
                                    <td style="font-size:10px">${l.remark || '—'}</td>
                                </tr>
                            `).join('')}
                        </table>
                    `;
                } else {
                    traceHtml += `<div class="empty-state"><i class="fa-solid fa-journal-whills" style="font-size:24px;margin-bottom:8px;"></i><br>No stock ledger entries</div>`;
                }
                traceHtml += `</div>`;

                traceHtml += `</div>`; // close trace-content
                panel.innerHTML = traceHtml;

            } catch (e) {
                panel.innerHTML = `<div style="text-align:center;padding:30px;color:var(--text-muted);">
                    <i class="fa-solid fa-wifi-slash" style="font-size:40px;color:var(--danger);margin-bottom:10px;"></i>
                    <div style="font-weight:700;">Connection Error</div>
                    <div style="font-size:11px;margin-top:6px;">Check network and try again</div>
                </div>`;
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-search"></i> Trace';
        }

        function switchTraceTab(el, tabId) {
            document.querySelectorAll('.trace-tab').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            document.querySelectorAll('.trace-tab-content').forEach(t => t.style.display = 'none');
            document.getElementById(tabId).style.display = 'block';
        }

        // 🪟 Software Detail Drawer
        function closeSoftwareDetail() {
            document.getElementById('detailOverlay').classList.remove('open');
            document.getElementById('detailDrawer').classList.remove('open');
        }

        async function showStockDetail(name, type) {
            const overlay = document.getElementById('detailOverlay');
            const drawer = document.getElementById('detailDrawer');
            const title = document.getElementById('detailTitle');
            const body = document.getElementById('detailBody');

            title.textContent = name;
            body.innerHTML = '<div class="loader" style="padding:30px 0"><div class="spinner"></div>Loading Details...</div>';
            overlay.classList.add('open');
            drawer.classList.add('open');

            try {
                if (type === 'software') {
                    await showSoftwareDetail(name, body);
                } else {
                    await showDeviceDetail(name, body);
                }
            } catch (e) {
                body.innerHTML = `
                    <div class="detail-empty">
                        <i class="fa-solid fa-wifi-slash" style="color:var(--danger);font-size:32px"></i>
                        <div style="font-weight:700;margin-top:6px;">Connection Error</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Failed to load details</div>
                    </div>
                `;
            }
        }

        // 📦 Software Detail (sales + renewals)
        async function showSoftwareDetail(name, body) {
            const res = await fetch('api_master_data.php?action=get_software_sales_detail&software_name=' + encodeURIComponent(name));
            const data = await res.json();

            if (data.status === 'error') {
                body.innerHTML = `
                    <div class="detail-empty">
                        <i class="fa-solid fa-circle-exclamation" style="color:var(--danger)"></i>
                        <div style="font-weight:700;margin-top:6px;">${data.error || 'Failed to load details'}</div>
                    </div>
                `;
                return;
            }

            const sales = data.sales || [];
            const renewals = data.renewals || [];
            const stockMovement = data.stock_movement || [];
            const sc = data.sales_count || 0;
            const rc = data.renewals_count || 0;
            const stock = data.current_stock || 0;
            const totalAmt = data.sales_total_amount || 0;
            const paidAmt = data.sales_paid_amount || 0;
            const renewTotal = data.renewals_total || 0;
            const totalStockIn = data.total_stock_in || 0;
            const totalStockOut = data.total_stock_out || 0;

            let html = '';

            // Summary mini-cards — Updated with Stock In/Out
            html += `
                <div class="detail-summary">
                    <div class="d-card" style="border-left:3px solid var(--secondary);">
                        <div class="d-label">Current Stock</div>
                        <div class="d-val" style="color:var(--secondary)">${stock}</div>
                        <div class="d-sub">${totalStockIn} added · ${totalStockOut} reduced</div>
                    </div>
                    <div class="d-card" style="border-left:3px solid var(--success);">
                        <div class="d-label">Sales</div>
                        <div class="d-val" style="color:var(--success)">${sc}</div>
                        <div class="d-sub">${formatCurrency(totalAmt)} total · ${formatCurrency(paidAmt)} paid</div>
                    </div>
                    <div class="d-card" style="border-left:3px solid var(--warn);">
                        <div class="d-label">Renewals</div>
                        <div class="d-val" style="color:var(--warn)">${rc}</div>
                        <div class="d-sub">${formatCurrency(renewTotal)} collected</div>
                    </div>
                    <div class="d-card" style="border-left:3px solid var(--primary);">
                        <div class="d-label">Total Revenue</div>
                        <div class="d-val" style="color:var(--primary)">${formatCurrency(paidAmt + renewTotal)}</div>
                        <div class="d-sub">Sales + Renewals</div>
                    </div>
                </div>
            `;

            // 📦 Stock Movement History (NEW)
            html += `<div class="detail-section-title"><i class="fa-solid fa-arrow-trend-up"></i> Stock Movement History (${stockMovement.length})</div>`;
            if (stockMovement.length > 0) {
                html += `
                    <table class="detail-table">
                        <tr><th>Date</th><th>Qty</th><th>Type</th><th>Remark / Reference</th></tr>
                        ${stockMovement.map(m => {
                            const q = parseInt(m.qty);
                            const isIn = q > 0;
                            // Build remark/reference display
                            let refText = m.remark || '';
                            const refExtra = m.reference || m.vehicle_no || '';
                            if (refText && refExtra) refText += ' — ' + refExtra;
                            else if (!refText && refExtra) refText = refExtra;
                            if (!refText) refText = '—';
                            return `
                                <tr>
                                    <td style="white-space:nowrap;font-size:10px">${m.date || '—'}</td>
                                    <td style="font-family:'Outfit';font-weight:800;${isIn ? 'color:var(--success)' : 'color:var(--danger)'}">${isIn ? '+' : ''}${q}</td>
                                    <td><span style="font-size:9px;font-weight:700;padding:2px 8px;border-radius:99px;${isIn ? 'background:rgba(16,185,129,0.15);color:var(--success)' : 'background:rgba(239,68,68,0.15);color:var(--danger)'}">${isIn ? 'STOCK IN' : 'STOCK OUT'}</span></td>
                                    <td style="font-size:10px">${refText}</td>
                                </tr>
                            `;
                        }).join('')}
                    </table>
                `;
            } else {
                html += `<div class="detail-empty"><i class="fa-solid fa-arrow-trend-up"></i><br>No stock movement history found</div>`;
            }

            // Sales Table
            html += `<div class="detail-section-title"><i class="fa-solid fa-cart-shopping"></i> Sales Records (${sc})</div>`;
            if (sales.length > 0) {
                html += `
                    <table class="detail-table">
                        <tr><th>Date</th><th>Customer</th><th>Vehicle No</th><th>Amount</th><th>Paid</th></tr>
                        ${sales.map(s => `
                            <tr>
                                <td style="white-space:nowrap;font-size:10px">${s.invoice_date || '—'}</td>
                                <td>
                                    <div style="font-weight:600">${s.customer_name || '—'}</div>
                                    <div style="font-size:9px;color:var(--text-muted)">${s.mobile_number || ''}</div>
                                </td>
                                <td style="font-family:'Outfit';font-weight:700;letter-spacing:1px;color:var(--secondary)">${s.vehicle_no || '—'}</td>
                                <td style="font-family:'Outfit';font-weight:700">${formatCurrency(s.total_amount || 0)}</td>
                                <td style="font-family:'Outfit';font-weight:700;color:var(--success)">${formatCurrency(s.paid_amount || 0)}</td>
                            </tr>
                        `).join('')}
                    </table>
                `;
            } else {
                html += `<div class="detail-empty"><i class="fa-regular fa-rectangle-list"></i><br>No sales records found for this software</div>`;
            }

            // Renewals Table
            html += `<div class="detail-section-title"><i class="fa-solid fa-arrows-rotate"></i> Renewal Records (${rc})</div>`;
            if (renewals.length > 0) {
                html += `
                    <table class="detail-table">
                        <tr><th>Date</th><th>Customer</th><th>Vehicle</th><th>Amount</th></tr>
                        ${renewals.map(r => `
                            <tr>
                                <td style="white-space:nowrap;font-size:10px">${r.date || '—'}</td>
                                <td>
                                    <div style="font-weight:600">${r.customer_name || r.customer || '—'}</div>
                                    <div style="font-size:9px;color:var(--text-muted)">${r.mobile_no || ''}</div>
                                </td>
                                <td style="font-family:'Outfit';font-weight:700;letter-spacing:1px;color:var(--secondary)">${r.vehicle || r.vehicle_no || '—'}</td>
                                <td style="font-family:'Outfit';font-weight:700">${formatCurrency(r.amount || r.received_amount || 0)}</td>
                            </tr>
                        `).join('')}
                    </table>
                `;
            } else {
                html += `<div class="detail-empty"><i class="fa-solid fa-arrows-rotate"></i><br>No renewal records found</div>`;
            }

            body.innerHTML = html;
        }

        // 📱 Device Detail (stock additions + sales)
        async function showDeviceDetail(name, body) {
            const res = await fetch('api_master_data.php?action=get_device_stock_detail&model_name=' + encodeURIComponent(name));
            const data = await res.json();

            if (data.status === 'error') {
                body.innerHTML = `
                    <div class="detail-empty">
                        <i class="fa-solid fa-circle-exclamation" style="color:var(--danger)"></i>
                        <div style="font-weight:700;margin-top:6px;">${data.error || 'Failed to load details'}</div>
                    </div>
                `;
                return;
            }

            const inStock = data.in_stock || 0;
            const sold = data.sold || 0;
            const returned = data.returned || 0;
            const total = data.total || 0;
            const additions = data.recent_additions || [];
            const sales = data.sales || [];
            const sc = data.sales_count || 0;
            const totalAmt = data.sales_total_amount || 0;
            const paidAmt = data.sales_paid_amount || 0;

            let html = '';

            // Summary mini-cards
            html += `
                <div class="detail-summary">
                    <div class="d-card" style="border-left:3px solid var(--success);">
                        <div class="d-label">In Stock</div>
                        <div class="d-val" style="color:var(--success)">${inStock}</div>
                        <div class="d-sub">Available units</div>
                    </div>
                    <div class="d-card" style="border-left:3px solid #3b82f6;">
                        <div class="d-label">Sold</div>
                        <div class="d-val" style="color:#3b82f6">${sold}</div>
                        <div class="d-sub">${formatCurrency(paidAmt)} collected</div>
                    </div>
                    <div class="d-card" style="border-left:3px solid var(--warn);">
                        <div class="d-label">Returned</div>
                        <div class="d-val" style="color:var(--warn)">${returned}</div>
                        <div class="d-sub">Out of ${total} total</div>
                    </div>
                    <div class="d-card" style="border-left:3px solid var(--secondary);">
                        <div class="d-label">Reduction</div>
                        <div class="d-val" style="color:var(--secondary)">${total - inStock}</div>
                        <div class="d-sub">${sold} sold + ${returned} returned</div>
                    </div>
                </div>
            `;

            // Recent Additions Table (last stock added)
            html += `<div class="detail-section-title"><i class="fa-solid fa-boxes-stacked"></i> Recent Stock Additions (${data.additions_count || 0})</div>`;
            if (additions.length > 0) {
                html += `
                    <table class="detail-table">
                        <tr><th>Date</th><th>IMEI</th><th>Supplier</th><th>Rate</th><th>Status</th></tr>
                        ${additions.map(a => `
                            <tr>
                                <td style="white-space:nowrap;font-size:10px">${a.date || '—'}</td>
                                <td style="font-family:'Outfit';font-weight:700;letter-spacing:1px;font-size:10px">${a.imei || '—'}</td>
                                <td style="font-size:11px">${a.supplier_name || '—'}</td>
                                <td style="font-family:'Outfit';font-weight:700">${formatCurrency(a.rate || 0)}</td>
                                <td><span style="font-size:9px;font-weight:700;padding:2px 8px;border-radius:99px;${(a.status||'').toLowerCase()==='in stock'?'background:rgba(16,185,129,0.15);color:var(--success)':'background:rgba(59,130,246,0.15);color:#3b82f6'}">${a.status || '—'}</span></td>
                            </tr>
                        `).join('')}
                    </table>
                `;
            } else {
                html += `<div class="detail-empty"><i class="fa-solid fa-boxes-stacked"></i><br>No stock addition records found</div>`;
            }

            // Sales Records
            html += `<div class="detail-section-title"><i class="fa-solid fa-cart-shopping"></i> Sales Records (${sc})</div>`;
            if (sales.length > 0) {
                html += `
                    <table class="detail-table">
                        <tr><th>Date</th><th>Customer</th><th>Vehicle No</th><th>IMEI</th><th>Amount</th></tr>
                        ${sales.map(s => `
                            <tr>
                                <td style="white-space:nowrap;font-size:10px">${s.invoice_date || '—'}</td>
                                <td>
                                    <div style="font-weight:600">${s.customer_name || '—'}</div>
                                </td>
                                <td style="font-family:'Outfit';font-weight:700;letter-spacing:1px;color:var(--secondary)">${s.vehicle_no || '—'}</td>
                                <td style="font-family:'Outfit';font-size:10px;letter-spacing:1px;color:var(--text-muted)">${s.imei || '—'}</td>
                                <td style="font-family:'Outfit';font-weight:700">${formatCurrency(s.total_amount || 0)}<br><span style="font-size:9px;color:var(--success)">Paid: ${formatCurrency(s.paid_amount || 0)}</span></td>
                            </tr>
                        `).join('')}
                    </table>
                `;
            } else {
                html += `<div class="detail-empty"><i class="fa-regular fa-rectangle-list"></i><br>No sales records found for this device model</div>`;
            }

            body.innerHTML = html;
        }

        // Enter key to trace
        document.getElementById('imeiInput').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') traceIMEI();
        });

        // Set initial values
        function setSummary(total, models, low) {
            const safe = v => (v === undefined || v === null || isNaN(v)) ? 0 : v;
            document.getElementById('totalDevices').textContent = safe(total);
            document.getElementById('totalModels').textContent = safe(models);
            document.getElementById('lowCount').textContent = safe(low);
        }

        // 📦 Load Stock Grid
        async function loadStock() {
            try {
                const res = await fetch('api_master_data.php?action=get_live_stock');
                const rawText = await res.text();
                let data;
                try {
                    data = JSON.parse(rawText);
                    console.log('📦 Stock API response:', JSON.stringify(data).substring(0, 300));
                } catch(e) {
                    console.error('API returned non-JSON:', rawText.substring(0, 500));
                    setSummary(0, 0, 0);
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('content').innerHTML = '<div class="empty-state" style="padding:40px;"><i class="fa-solid fa-bug" style="font-size:40px;color:var(--danger);margin-bottom:10px;"></i><br>API Error. Check console for details.</div>';
                    return;
                }
                
                document.getElementById('loading').style.display = 'none';
                
                // Validate data is an array
                if (!data || !Array.isArray(data)) {
                    setSummary(0, 0, 0);
                    document.getElementById('totalDevices').textContent = '0';
                    document.getElementById('totalModels').textContent = '0';
                    document.getElementById('lowCount').textContent = '0';
                    document.getElementById('content').innerHTML = '<div class="empty-state" style="padding:40px;"><i class="fa-solid fa-database" style="font-size:40px;color:var(--text-muted);margin-bottom:10px;"></i><br>No stock data available</div>';
                    return;
                }

                const container = document.getElementById('content');
                let lastType = "";
                let html = "";
                let totalQty = 0;
                let modelCount = 0;
                let lowStockItems = 0;

                data.forEach(it => {
                    if (!it || !it.name) return; // Skip invalid entries
                    
                    const type = (it.type || '').toUpperCase();
                    if(type !== lastType) {
                        if(html !== "") html += `</div>`; // Close previous grid
                        html += `<div class="cat-label">${type || 'ITEM'}S</div><div class="stock-grid">`;
                        lastType = type;
                    }

                    const rawQty = parseInt(it.qty);
                    const qty = isNaN(rawQty) ? 0 : rawQty;
                    const isNegative = qty < 0;
                    const isLow = qty > 0 && qty <= 3;
                    const isCritical = qty > 0 && qty <= 1;

                    totalQty += Math.max(0, qty);
                    if (qty > 0) modelCount++;
                    if (isLow && !isNegative) lowStockItems++;

                    // Negative qty gets red; low stock gets warning
                    let cardClass = '';
                    let alertChip = '';
                    if (isCritical) { cardClass = 'critical'; alertChip = '<div class="alert-chip critical">🔥 Critical</div>'; }
                    else if (isLow) { cardClass = 'low'; alertChip = '<div class="alert-chip low">⚠️ Low</div>'; }

                    const extraClass = type === 'DEVICE' || type === 'MIXED' ? 'clickable' : 'clickable';
                    const clickFn = type === 'SOFTWARE' || type === 'RELAY' || type === 'TOOL'
                        ? `showStockDetail('${it.name.replace(/'/g, "\\'")}', 'software')`
                        : `showStockDetail('${it.name.replace(/'/g, "\\'")}', 'device')`;
                    const hint = type === 'DEVICE' || type === 'MIXED'
                        ? '<div style="font-size:8px;color:var(--secondary);margin-top:2px;display:flex;align-items:center;gap:4px"><i class="fa-solid fa-microchip"></i> Click for stock history</div>'
                        : '<div style="font-size:8px;color:var(--primary);margin-top:2px;display:flex;align-items:center;gap:4px"><i class="fa-solid fa-magnifying-glass"></i> Click to view sales</div>';
                    html += `
                        <div class="stock-card ${cardClass} clickable" onclick="${clickFn}" style="${isNegative ? 'border-color: var(--danger); background: rgba(239, 68, 68, 0.05);' : ''}">
                            ${alertChip}
                            <div class="name">${it.name}</div>
                            <div class="val ${isNegative ? 'text-danger' : ''}" data-target="${qty}">0</div>
                            <div class="type">${it.type}</div>
                            ${hint}
                        </div>
                    `;
                });
                html += `</div>`; // Close last grid
                container.innerHTML = html;

                // Update summary bar
                setSummary(totalQty, modelCount, lowStockItems);

                // Animate ONLY stock card values (skip summary bar which has class="val" too)
                document.querySelectorAll('.stock-card .val').forEach(el => {
                    const target = el.dataset.target;
                    if (target !== undefined && target !== null && !isNaN(target)) {
                        animateValue(el, 0, parseInt(target), 1500);
                    }
                });

            } catch(e) { 
                document.getElementById('loading').style.display = 'none';
                setSummary(0, 0, 0);
                document.getElementById('content').innerHTML = '<div class="empty-state" style="padding:40px;"><i class="fa-solid fa-wifi-slash" style="font-size:40px;color:var(--danger);margin-bottom:10px;"></i><br>Connection failed. Refresh to retry.</div>';
            }
        }

        window.onload = loadStock;
    </script>
</body>
</html>
