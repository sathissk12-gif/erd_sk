<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>BI Intelligence | SK LOGIC</title>
    
    <!-- Modern Enterprise UI -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            --accent: #f43f5e;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --card-bg: rgba(30, 41, 59, 0.45);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #ffffff;
            --text-dim: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text-main);
            min-height: 100vh; 
            padding-top: env(safe-area-inset-top, 0px); 
            padding-bottom: 100px;
        }

        header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(3, 7, 18, 0.85); backdrop-filter: blur(30px);
            padding: calc(12px + env(safe-area-inset-top, 0px)) 20px 14px; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .header-left { display: flex; align-items: center; gap: 12px; }
        .logo { font-size: 17px; font-weight: 800; letter-spacing: -0.5px; background: linear-gradient(to right, #8b5cf6, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        .icon-btn { 
            width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; 
            background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: white; text-decoration: none;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 16px; animation: fadeIn 0.8s ease-out; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

        /* KPI Grid */
        .kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 24px; }
        @media (min-width: 768px) { .kpi-grid { grid-template-columns: repeat(4, 1fr); } }

        .kpi-card { 
            background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 18px; 
            backdrop-filter: blur(20px); position: relative; overflow: hidden;
            display: flex; flex-direction: column; gap: 6px;
        }
        .kpi-card::after { content:''; position:absolute; top:0; left:0; width:100%; height:3px; background: var(--primary); opacity: 0.5; }
        .kpi-label { font-size: 10px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; }
        .kpi-value { font-size: 20px; font-weight: 800; font-family: 'Outfit'; color: white; }
        .kpi-sub { font-size: 11px; color: var(--text-dim); font-weight: 600; }

        /* Tools Bar */
        .tool-bar { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
        @media (min-width: 768px) { .tool-bar { flex-direction: row; } }
        
        .search-container { flex: 1; position: relative; }
        .search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-dim); font-size: 14px; }
        .input-box { 
            width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border); border-radius: 16px; 
            padding: 14px 14px 14px 44px; color: white; font-size: 15px; transition: 0.3s; font-family: inherit; outline: none;
        }
        .input-box:focus { border-color: var(--primary); background: rgba(15, 23, 42, 0.8); box-shadow: 0 0 0 4px var(--primary-glow); }

        .select-box { 
            width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border); border-radius: 16px; 
            padding: 14px 18px; color: white; font-size: 15px; cursor: pointer; appearance: none; outline: none;
        }

        .btn-action {
            background: var(--primary); color: white; border: none; border-radius: 16px;
            padding: 14px 24px; font-weight: 800; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: 0.3s; box-shadow: 0 8px 16px rgba(139, 92, 246, 0.2);
        }
        .btn-action:active { transform: scale(0.96); }
        .btn-action.alt { background: var(--secondary); box-shadow: 0 8px 16px rgba(6, 182, 212, 0.2); }

        /* Visual Layouts */
        .chart-box { background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 20px; margin-bottom: 24px; height: 280px; }
        
        .financial-box { 
            background: linear-gradient(135deg, rgba(2, 6, 23, 0.8), rgba(15, 23, 42, 0.6));
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; padding: 24px; margin-bottom: 24px;
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; text-align: center;
        }
        .fin-item div:first-child { font-size: 9px; font-weight: 800; opacity: 0.6; margin-bottom: 6px; letter-spacing: 1px; }
        .fin-item div:last-child { font-size: 18px; font-weight: 800; font-family: 'Outfit'; }

        /* Report View */
        .report-section { background: var(--surface); border: 1px solid var(--border); border-radius: 28px; padding: 0; overflow: hidden; backdrop-filter: blur(25px); }
        .report-header { padding: 20px; border-bottom: 1px solid var(--border); }
        .report-title { font-size: 18px; font-weight: 800; font-family: 'Outfit'; letter-spacing: -0.5px; }
        .report-sub { font-size: 12px; color: var(--text-dim); margin-top: 4px; }

        /* Desktop Table */
        .desktop-table { width: 100%; border-collapse: collapse; display: none; }
        th { text-align: left; padding: 14px 20px; color: var(--text-dim); font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.2); }
        td { padding: 16px 20px; font-size: 14px; border-bottom: 1px solid rgba(255,255,255,0.03); }
        @media (min-width: 1024px) { .desktop-table { display: table; } }

        /* Mobile Cards */
        .mobile-list { display: flex; flex-direction: column; gap: 0; }
        @media (min-width: 1024px) { .mobile-list { display: none; } }
        
        .data-card { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; flex-direction: column; gap: 10px; transition: 0.2s; }
        .data-card:active { background: rgba(255,255,255,0.03); }
        .card-row { display: flex; justify-content: space-between; align-items: center; }
        .card-main { font-weight: 800; font-family: 'Outfit'; font-size: 16px; }
        .card-meta { font-size: 12px; color: var(--text-dim); font-weight: 500; }
        .tag { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .tag-blue { background: rgba(6, 182, 212, 0.15); color: #06b6d4; }

        .call-btn { 
            display: flex; align-items: center; justify-content: center; gap: 8px; background: #22c55e; 
            color: white; text-decoration: none; padding: 12px; border-radius: 12px; font-weight: 800; font-size: 13px;
        }
        .wa-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px; background: #25D366;
            color: white; text-decoration: none; padding: 12px; border-radius: 12px; font-weight: 800; font-size: 13px;
        }
        .wa-btn.disabled { opacity: 0.45; pointer-events: none; }
        .action-row { display: flex; gap: 8px; flex-wrap: wrap; }
        .action-row .call-btn, .action-row .wa-btn { flex: 1; min-width: 90px; }

        .date-divider { 
            background: rgba(139, 92, 246, 0.1); color: var(--primary); font-size: 11px; font-weight: 800; 
            padding: 10px 20px; letter-spacing: 1px; border-left: 4px solid var(--primary);
        }

        .loader { position: fixed; inset: 0; background: rgba(3,7,18,0.8); z-index: 5000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(10px); }
        .spinner { width: 40px; height: 40px; border: 4px solid rgba(255,255,255,0.1); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <header>
        <div class="header-left">
            <a href="index.html" class="icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="logo">BI CONSOLE v3.0</div>
        </div>
        <div class="icon-btn" onclick="boot()"><i class="fa-solid fa-rotate"></i></div>
    </header>

    <div class="container">
        
        <!-- 🚀 Time Analytics KPIs -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <span class="kpi-label">Today</span>
                <span class="kpi-value" id="valToday">₹0</span>
                <div class="kpi-sub" id="pToday">Profit: ₹0</div>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Week</span>
                <span class="kpi-value" id="valWeek">₹0</span>
                <div class="kpi-sub" id="pWeek">Profit: ₹0</div>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Month</span>
                <span class="kpi-value" id="valMonth">₹0</span>
                <div class="kpi-sub" id="pMonth">Profit: ₹0</div>
            </div>
            <div class="kpi-card" style="border-color: var(--primary);">
                <span class="kpi-label">Year</span>
                <span class="kpi-value" id="valYear">₹0</span>
                <div class="kpi-sub" id="pYear">Profit: ₹0</div>
            </div>
        </div>

        <!-- 🔧 Filters -->
        <div class="tool-bar">
            <div class="search-container">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="masterSearch" class="input-box" placeholder="Vehicle or IMEI..." oninput="handleSearch(this.value)">
            </div>
            <select id="dealerSelect" class="select-box" onchange="loadDealerReport(this.value)">
                <option value="">All Dealers</option>
            </select>
        </div>

        <div class="tool-bar">
            <input type="month" id="renewalMonth" class="select-box" style="flex:1;">
            <button class="btn-action alt" style="flex:1;" onclick="loadPendingRenewals()"><i class="fa-solid fa-phone-volume"></i> Renewals</button>
            <button class="btn-action" onclick="downloadCSV()"><i class="fa-solid fa-download"></i></button>
        </div>

        <!-- 📈 Sales Trend -->
        <div class="chart-box">
            <canvas id="salesTrend"></canvas>
        </div>

        <!-- 💰 Financial Snapshot -->
        <div class="financial-box">
            <div class="fin-item">
                <div>GROSS</div>
                <div id="sumGross">₹ 0</div>
            </div>
            <div class="fin-item">
                <div>COST</div>
                <div style="color:var(--accent);" id="sumExp">₹ 0</div>
            </div>
            <div class="fin-item">
                <div>NET</div>
                <div style="color:#10b981;" id="sumNet">₹ 0</div>
            </div>
        </div>

        <!-- 📊 Detailed Data Feed -->
        <div class="report-section">
            <div class="report-header">
                <div class="report-title" id="reportTitle">Data Stream</div>
                <div class="report-sub" id="reportHint">Filters use pannunga data view panna.</div>
            </div>
            
            <!-- Desktop Table -->
            <table class="desktop-table">
                <thead id="reportHeadRow"></thead>
                <tbody id="reportData"></tbody>
            </table>

            <!-- Mobile Card List -->
            <div class="mobile-list" id="reportList"></div>
        </div>

    </div>

    <div class="loader" id="loader"><div class="spinner"></div></div>

    <script>
        const API = "api_reports.php";
        let currentReportData = [];
        let currentReportMode = "sales";

        function setCurrentMonth() {
            const dt = new Date();
            const month = String(dt.getMonth() + 1).padStart(2, '0');
            document.getElementById('renewalMonth').value = `${dt.getFullYear()}-${month}`;
        }

        async function boot() {
            setCurrentMonth();
            loadAnalytics();
            loadTrend();
            loadDealers();
            fetchKPI();
            loadPendingRenewals();
        }

        async function loadAnalytics() {
            const data = await fetch(`${API}?action=time_analytics`).then(r=>r.json());
            const periods = ['Today', 'Week', 'Month', 'Year'];
            periods.forEach(p => {
                animateValue(`val${p}`, data[p].sales);
                animateValue(`p${p}`, data[p].profit, 'Profit: ₹');
            });
        }

        async function loadDealers() {
            const res = await fetch(`api_dealers.php?action=dealerlist`).then(r=>r.json());
            const sel = document.getElementById('dealerSelect');
            sel.innerHTML = '<option value="">All Dealers</option>';
            res.forEach(d => {
                let o = document.createElement('option');
                o.value = d; o.innerText = d;
                sel.appendChild(o);
            });
        }

        async function handleSearch(q) {
            if(q.length < 3) return;
            showLoader(true);
            const res = await fetch(`${API}?action=detailed_search&q=${encodeURIComponent(q)}`).then(r=>r.json());
            renderSalesTable(res, false, "Search Results", `Query: ${q}`);
            showLoader(false);
        }

        async function loadDealerReport(dealer) {
            if(!dealer) return;
            showLoader(true);
            const res = await fetch(`${API}?action=dealer_performance&dealer=${encodeURIComponent(dealer)}`).then(r=>r.json());
            renderSalesTable(res, true, "Dealer Performance", dealer);
            showLoader(false);
        }

        async function loadPendingRenewals() {
            const month = document.getElementById('renewalMonth').value;
            showLoader(true);
            const res = await fetch(`${API}?action=pending_renewals_month&month=${encodeURIComponent(month)}`).then(r=>r.json());
            renderPendingRenewals(res, month);
            showLoader(false);
        }

        function renderSalesTable(data, groupMode, title = "Data Stream", hint = "") {
            currentReportData = data;
            currentReportMode = 'sales';
            document.getElementById('reportTitle').innerText = title;
            document.getElementById('reportHint').innerText = hint;

            // Desktop Headers
            document.getElementById('reportHeadRow').innerHTML = `
                <tr>
                    <th>Vehicle / Time</th>
                    <th>Device / IMEI</th>
                    <th>Outlet / Status</th>
                    <th>Customer</th>
                    <th>Revenue</th>
                </tr>
            `;

            const body = document.getElementById('reportData');
            const list = document.getElementById('reportList');
            
            if(!data.length) {
                body.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:100px;">No records.</td></tr>';
                list.innerHTML = '<div style="text-align:center; padding:50px;">No records found.</div>';
                return;
            }

            let tableHtml = "";
            let cardHtml = "";
            let lastDate = "";

            data.forEach(it => {
                let date = it.sale_date || it.issue_date || 'N/A';
                if(groupMode && date !== lastDate) {
                    tableHtml += `<tr><td colspan="5"><div class="date-divider">${date}</div></td></tr>`;
                    cardHtml += `<div class="date-divider">${date}</div>`;
                    lastDate = date;
                }

                tableHtml += `
                    <tr>
                        <td><div style="font-weight:800;">${it.vehicle_no || 'OFFICE'}</div><div class="card-meta">${date}</div></td>
                        <td><div style="font-weight:700;">${it.model || '-'}</div><div class="card-meta">${it.imei || '-'}</div></td>
                        <td><span class="tag tag-blue">${it.holder || 'Direct'}</span></td>
                        <td><div style="font-weight:700;">${it.customer_name || 'Stock'}</div></td>
                        <td style="font-weight:800; font-family:'Outfit';">₹${currency(it.selling_price)}</td>
                    </tr>
                `;

                cardHtml += `
                    <div class="data-card">
                        <div class="card-row">
                            <div class="card-main">${it.vehicle_no || 'OFFICE'}</div>
                            <div class="card-main" style="color:var(--primary);">₹${currency(it.selling_price)}</div>
                        </div>
                        <div class="card-row">
                            <div class="card-meta">${it.customer_name || 'No Customer'}</div>
                            <span class="tag tag-blue">${it.holder || 'Direct'}</span>
                        </div>
                        <div class="card-meta">${it.model || '-'} • ${it.imei || '-'}</div>
                    </div>
                `;
            });

            body.innerHTML = tableHtml;
            list.innerHTML = cardHtml;
        }

        function cleanMobileForWa(mobile) {
            const digits = String(mobile || '').replace(/\D/g, '');
            if (digits.length === 10) return '91' + digits;
            if (digits.length === 12 && digits.startsWith('91')) return digits;
            if (digits.length === 11 && digits.startsWith('0')) return '91' + digits.slice(1);
            return '';
        }

        function formatExpiryDate(d) {
            if (!d || d === '0000-00-00') return '-';
            const dt = new Date(d + 'T00:00:00');
            return isNaN(dt.getTime()) ? d : dt.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function buildRenewalWaMessage(item) {
            const name = item.customer_name || 'Customer';
            const vehicle = item.vehicle_no || 'N/A';
            const expiry = formatExpiryDate(item.due_date);
            const software = item.software || '-';
            const ta = `*இறுதி புதுப்பிப்பு நினைவூட்டல்*\n\nவணக்கம் ${name},\n\nஉங்கள் வாகன GPS/மென்பொருள் புதுப்பிப்பு காலாவதியாக உள்ளது.\n\n*வாகன எண்:* ${vehicle}\n*காலாவதி தேதி:* ${expiry}\n*மென்பொருள்:* ${software}\n\n⚠️ புதுப்பிப்பு செய்யாவிட்டால் SIM disconnect ஆகும்.\nSIM replacement-க்கு கூடுதல் *Rs 300* கட்டணம் வசூலிக்கப்படும்.\n\nசேவை நிறுத்தம் தவிர்க்க உடனே புதுப்பிக்கவும்.\n\n— SK LOGIC`;
            const en = `*FINAL RENEWAL REMINDER*\n\nDear ${name},\n\nYour GPS/software renewal is due.\n\n*Vehicle:* ${vehicle}\n*Expiry Date:* ${expiry}\n*Software:* ${software}\n\n⚠️ If renewal is not done, the SIM will be disconnected.\nSIM replacement will incur an extra charge of *Rs 300*.\n\nPlease renew immediately to avoid service interruption.\n\n— SK LOGIC`;
            return ta + '\n\n---\n\n' + en;
        }

        function buildRenewalWaLink(item) {
            const phone = cleanMobileForWa(item.mobile_no);
            if (!phone) return '';
            return `https://wa.me/${phone}?text=${encodeURIComponent(buildRenewalWaMessage(item))}`;
        }

        function renderPendingRenewals(data, month) {
            currentReportData = data;
            currentReportMode = 'renewal';
            document.getElementById('reportTitle').innerText = "Renewal Follow-up";
            document.getElementById('reportHint').innerText = `${formatMonth(month)} Pending List`;

            document.getElementById('reportHeadRow').innerHTML = `
                <tr>
                    <th>Due / Software</th>
                    <th>Vehicle</th>
                    <th>Customer / Loc</th>
                    <th>Mobile</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            `;

            const body = document.getElementById('reportData');
            const list = document.getElementById('reportList');

            if(!data.length) {
                body.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:100px;">No pending renewals.</td></tr>';
                list.innerHTML = '<div style="text-align:center; padding:50px;">Clear! No pending calls.</div>';
                return;
            }

            const renewalRowHtml = (it) => {
                const waLink = buildRenewalWaLink(it);
                const waAttrs = waLink ? `href="${waLink}" target="_blank" rel="noopener"` : '';
                const waClass = waLink ? 'wa-btn' : 'wa-btn disabled';
                return { waAttrs, waClass };
            };

            body.innerHTML = data.map(it => {
                const { waAttrs, waClass } = renewalRowHtml(it);
                return `
                <tr>
                    <td><div style="font-weight:800;">${it.due_date}</div><div class="card-meta">${it.software}</div></td>
                    <td style="font-weight:800;">${it.vehicle_no}</td>
                    <td><div style="font-weight:700;">${it.customer_name}</div><div class="card-meta">${it.location}</div></td>
                    <td>${it.mobile_no}</td>
                    <td>
                        <select class="select-box" style="padding:8px; border-radius:8px;" onchange="updateStatus('${it.id}', this.value)">
                            <option value="PENDING" selected>PENDING</option>
                            <option value="NO">NO</option>
                        </select>
                    </td>
                    <td>
                        <div class="action-row">
                            <a class="call-btn" href="tel:+91${it.mobile_no}"><i class="fa-solid fa-phone"></i> Call</a>
                            <a class="${waClass}" ${waAttrs}><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                        </div>
                    </td>
                </tr>`;
            }).join('');

            list.innerHTML = data.map(it => {
                const { waAttrs, waClass } = renewalRowHtml(it);
                return `
                <div class="data-card">
                    <div class="card-row">
                        <div class="card-main">${it.vehicle_no}</div>
                        <div class="card-meta" style="font-weight:800; color:var(--accent);">${it.due_date}</div>
                    </div>
                    <div class="card-meta" style="font-weight:700; color:white;">${it.customer_name} • ${it.location}</div>
                    <div class="card-row" style="margin-top:5px; gap:10px;">
                        <select class="select-box" style="flex:1; padding:10px; border-radius:10px;" onchange="updateStatus('${it.id}', this.value)">
                            <option value="PENDING" selected>PENDING</option>
                            <option value="NO">NO</option>
                        </select>
                    </div>
                    <div class="card-row" style="gap:10px;">
                        <a class="call-btn" style="flex:1;" href="tel:+91${it.mobile_no}"><i class="fa-solid fa-phone"></i> CALL</a>
                        <a class="${waClass}" style="flex:1;" ${waAttrs}><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                    </div>
                </div>`;
            }).join('');
        }

        async function updateStatus(id, value) {
            const fd = new FormData();
            fd.append('id', id); fd.append('status', value);
            await fetch(`${API}?action=update_pending_renewal_status`, { method: 'POST', body: fd });
            if(value === 'NO') loadPendingRenewals();
        }

        function currency(v){ return new Intl.NumberFormat('en-IN').format(v||0); }
        function formatMonth(v){ return v ? new Date(v+'-01').toLocaleString('en-IN', {month:'long', year:'numeric'}) : ''; }

        function animateValue(id, value, prefix = '₹') {
            const obj = document.getElementById(id);
            let start = 0; const end = parseFloat(value);
            const step = end / 60;
            const timer = setInterval(() => {
                start += step;
                if(start >= end) { clearInterval(timer); obj.innerText = prefix + end.toLocaleString(); return; }
                obj.innerText = prefix + Math.floor(start).toLocaleString();
            }, 16);
        }

        async function loadTrend() {
            const res = await fetch(`${API}?action=sales_trend`).then(r=>r.json());
            const ctx = document.getElementById('salesTrend').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: res.labels,
                    datasets: [{
                        data: res.data,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139,92,246,0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { display: false }, x: { grid: { display:false }, ticks: { color: '#94a3b8', font: { size: 10 } } } }
                }
            });
        }

        function downloadCSV() {
            if(!currentReportData.length) return alert("No data!");
            const rows = currentReportData.map(it => Object.values(it).join(",")).join("\n");
            const blob = new Blob([Object.keys(currentReportData[0]).join(",") + "\n" + rows], { type: 'text/csv' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob); a.download = `report_${Date.now()}.csv`;
            a.click();
        }

        async function fetchKPI() {
            const d = await fetch('api_reports.php?action=kpi').then(r=>r.json());
            document.getElementById('sumGross').innerText = '₹' + currency(d.monthlySales);
            document.getElementById('sumExp').innerText = '₹' + currency(d.monthlyExpenses);
            document.getElementById('sumNet').innerText = '₹' + currency(d.netProfit);
        }

        function showLoader(show) { document.getElementById('loader').style.display = show ? 'flex' : 'none'; }
        window.onload = boot;
    </script>
</body>
</html>
