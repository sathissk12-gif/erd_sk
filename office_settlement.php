<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Office Settlement | SK LOGIC</title>
    <script src="theme_engine.js"></script>
    
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
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-muted: #94a3b8;
            --success: #10b981;
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
        
        .container { max-width: 500px; margin: 20px auto; padding: 0 20px; animation: slideUp 0.6s ease-out; }
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        .total-glow-card {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(6, 182, 212, 0.15));
            border: 1px solid var(--primary); border-radius: 32px; padding: 40px 25px; text-align: center;
            backdrop-filter: blur(30px); margin-bottom: 25px; box-shadow: 0 0 40px rgba(139, 92, 246, 0.2);
        }
        .total-glow-card .val { font-size: 48px; font-weight: 800; font-family: 'Outfit'; display: block; margin-top: 10px; text-shadow: 0 0 20px var(--primary-glow); }

        .kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
        .kpi-card { background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 25px; backdrop-filter: blur(20px); }
        .kpi-card .label { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; }
        .kpi-card .val { font-size: 24px; font-weight: 800; font-family: 'Outfit'; }

        .btn-stack { display: flex; flex-direction: column; gap: 12px; }
        .btn-main {
            width: 100%; padding: 20px; border: none; border-radius: 22px; 
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white; font-weight: 800; font-size: 16px; cursor: pointer; transition: 0.3s;
            box-shadow: 0 15px 30px rgba(139,92,246,0.3); display: flex; align-items: center; justify-content: center; gap: 12px;
        }

        .list-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 18px;
            backdrop-filter: blur(20px);
            margin-top: 22px;
        }
        .list-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            gap: 12px;
        }
        .list-title { font-size: 12px; font-weight: 800; color: var(--secondary); text-transform: uppercase; letter-spacing: 1.5px; }
        .list-sub { font-size: 11px; color: var(--text-muted); }
        .detail-list { display: flex; flex-direction: column; gap: 10px; max-height: 380px; overflow: auto; }
        .detail-row {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 14px;
        }
        .detail-row-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 8px;
        }
        .detail-vehicle { font-size: 14px; font-weight: 800; color: var(--text); }
        .detail-total { font-size: 15px; font-weight: 800; color: var(--success); }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 12px;
        }
        .detail-chip {
            background: rgba(0,0,0,0.18);
            border-radius: 12px;
            padding: 8px 10px;
        }
        .detail-chip b {
            display: block;
            font-size: 9px;
            color: var(--text-muted);
            margin-bottom: 4px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .detail-chip span { font-size: 12px; color: var(--text); word-break: break-word; }
        .empty-note {
            padding: 18px;
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
            border: 1px dashed var(--border);
            border-radius: 16px;
        }

        /* 📊 Detailed Summary */
        .detail-summary {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;
        }
        .ds-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 18px;
            padding: 16px; backdrop-filter: blur(20px); text-align: center;
        }
        .ds-card .lb { font-size: 8px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .ds-card .vl { font-size: 18px; font-weight: 800; font-family: 'Outfit'; margin-top: 4px; }

        /* 📜 Settlement History */
        .history-section { margin-top: 22px; }
        .history-table-wrap { max-height: 300px; overflow-y: auto; }
        .settle-table { width: 100%; border-collapse: collapse; }
        .settle-table th { text-align: left; font-size: 9px; text-transform: uppercase; color: var(--text-muted); padding: 12px; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        .settle-table td { padding: 12px; font-size: 11px; border-bottom: 1px solid rgba(255,255,255,0.03); }

        /* 🔍 Filter input */
        .search-filter-bar {
            display: flex; gap: 10px; margin-bottom: 15px;
        }
        .search-filter-bar input {
            flex: 1; padding: 12px 16px; background: rgba(15,23,42,0.4); border: 1px solid var(--border); border-radius: 14px;
            color: white; font-size: 13px; font-family: inherit; outline: none;
        }
        .search-filter-bar input:focus { border-color: var(--primary); }

        /* Toggle */
        .toggle-wrap { display: flex; gap: 10px; align-items: center; margin-bottom: 15px; }
        .toggle-btn { 
            padding: 8px 16px; border-radius: 99px; font-size: 10px; font-weight: 800; cursor: pointer; 
            background: rgba(15,23,42,0.4); border: 1px solid var(--border); color: var(--text-muted); transition: 0.3s;
        }
        .toggle-btn.active { background: var(--primary); border-color: var(--primary); color: white; }

        .modal { 
            position: fixed; inset: 0; background: rgba(2,6,23,0.9); backdrop-filter: blur(20px); z-index: 5000;
            display: none; align-items: center; justify-content: center; padding: 25px;
        }
        .modal-body { background: var(--surface); border: 1px solid var(--border); border-radius: 32px; padding: 35px; width: 100%; max-width: 400px; text-align: center; }
        .input-dark { 
            width: 100%; padding: 18px; background: rgba(0,0,0,0.3); border: 1px solid var(--border); border-radius: 16px; 
            color: white; font-size: 18px; font-weight: 700; text-align: center; margin: 20px 0;
        }
    </style>
</head>
<body>

    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div style="font-size: 10px; font-weight: 800; color: #10b981; text-transform: uppercase;">Financial Settlement</div>
    </header>

    <div class="container">
        
        <div class="total-glow-card">
            <span style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 2px;">Outstanding Balance</span>
            <span class="val" id="totalVal">₹ 0</span>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="label">Total Sales</div>
                <div class="val" id="salesVal">₹ 0</div>
            </div>
            <div class="kpi-card">
                <div class="label">Renewals</div>
                <div class="val" id="renVal">₹ 0</div>
            </div>
        </div>

        <!-- 📊 Detailed Summary -->
        <div class="detail-summary" id="detailSummary">
            <div class="ds-card"><div class="lb">Device</div><div class="vl" id="devTotal">₹ 0</div></div>
            <div class="ds-card"><div class="lb">Software</div><div class="vl" id="swTotal">₹ 0</div></div>
            <div class="ds-card"><div class="lb">Relay</div><div class="vl" id="relayTotal">₹ 0</div></div>
            <div class="ds-card"><div class="lb">Renewal</div><div class="vl" id="renTotal">₹ 0</div></div>
        </div>

        <div class="btn-stack">
            <button class="btn-main" onclick="sync()" id="syncBtn"><i class="fa-solid fa-rotate"></i> Sync Cloud Logs</button>
            <button class="btn-main" onclick="openModal()" style="background: var(--surface); border: 1px solid var(--border); box-shadow: none;">Process Payout</button>
        </div>

        <!-- 🔍 Filter -->
        <div class="search-filter-bar">
            <input type="text" id="filterInput" placeholder="🔍 Filter by vehicle / IMEI..." oninput="filterRows()">
        </div>

        <div class="list-card">
            <div class="list-head">
                <div>
                    <div class="list-title">Sales Breakdown</div>
                    <div class="list-sub" id="salesCount">0 rows</div>
                </div>
            </div>
            <div class="detail-list" id="salesDetails">
                <div class="empty-note">No pending sales rows.</div>
            </div>
        </div>

        <div class="list-card">
            <div class="list-head">
                <div>
                    <div class="list-title">Renewal Breakdown</div>
                    <div class="list-sub" id="renewalCount">0 rows</div>
                </div>
            </div>
            <div class="detail-list" id="renewalDetails">
                <div class="empty-note">No pending renewal rows.</div>
            </div>
        </div>

        <!-- 📜 Settlement History -->
        <div class="list-card history-section">
            <div class="list-head">
                <div>
                    <div class="list-title">Settlement History</div>
                    <div class="list-sub" id="historyCount">0 entries</div>
                </div>
                <button class="toggle-btn" onclick="loadHistory()" style="padding:6px 12px;"><i class="fa-solid fa-rotate"></i></button>
            </div>
            <div class="history-table-wrap">
                <table class="settle-table">
                    <thead><tr><th>Date</th><th>Txn ID</th><th>Sales</th><th>Renewal</th><th>Total</th></tr></thead>
                    <tbody id="historyBody">
                        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- 🎭 Modal -->
    <div class="modal" id="modal">
        <div class="modal-body">
            <i class="fa-solid fa-shield-check" style="font-size: 40px; color: var(--primary); margin-bottom: 20px;"></i>
            <h3>Confirm Payout</h3>
            <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Enter reference ID to mark settled.</p>
            <input type="text" id="txid" class="input-dark" placeholder="REF-ID">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <button class="btn-main" style="background: var(--card-base); box-shadow: none; font-size: 14px;" onclick="closeModal()">Cancel</button>
                <button class="btn-main" style="font-size: 14px;" onclick="confirmPay()">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        const API = "api_office_settlement.php";

        function animateValue(obj, start, end, duration) {
            start = Number(start);
            end = Number(end);
            if (!Number.isFinite(start)) start = 0;
            if (!Number.isFinite(end)) end = 0;
            let st = null;
            const step = (ts) => {
                if (!st) st = ts;
                const progress = Math.min((ts - st) / duration, 1);
                const cur = Math.floor(progress * (end - start) + start);
                obj.innerText = '₹ ' + cur.toLocaleString();
                if (progress < 1) window.requestAnimationFrame(step);
            };
            window.requestAnimationFrame(step);
        }

        function formatMoney(value) {
            const num = Number(value);
            return 'Rs. ' + (Number.isFinite(num) ? num : 0).toLocaleString();
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function renderRows(targetId, rows, type) {
            const holder = document.getElementById(targetId);
            if (!rows || !rows.length) {
                holder.innerHTML = `<div class="empty-note">No pending ${type} rows.</div>`;
                return;
            }

            holder.innerHTML = rows.map(row => `
                <div class="detail-row">
                    <div class="detail-row-top">
                        <div class="detail-vehicle">${escapeHtml(row.vehicle_no || '-')}</div>
                        <div class="detail-total">${formatMoney(row.total)}</div>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-chip">
                            <b>IMEI</b>
                            <span>${escapeHtml(row.imei || '-')}</span>
                        </div>
                        <div class="detail-chip">
                            <b>Software</b>
                            <span>${escapeHtml(row.software || '-')}</span>
                        </div>
                        ${type === 'sales' ? `
                            <div class="detail-chip">
                                <b>Device Model</b>
                                <span>${escapeHtml(row.device_model || '-')}</span>
                            </div>
                            <div class="detail-chip">
                                <b>Device Rate</b>
                                <span>${escapeHtml(row.device_model || 'DEVICE')} -> ${formatMoney(row.device_rate)}</span>
                            </div>
                            <div class="detail-chip">
                                <b>Software Rate</b>
                                <span>${escapeHtml(row.software || 'SOFTWARE')} -> ${formatMoney(row.software_rate)}</span>
                            </div>
                            <div class="detail-chip">
                                <b>Relay</b>
                                <span>${escapeHtml(row.relay || 'NO')}</span>
                            </div>
                            <div class="detail-chip">
                                <b>Relay Rate</b>
                                <span>${escapeHtml((row.relay || 'NO').toUpperCase())} -> ${formatMoney(row.relay_rate)}</span>
                            </div>
                            <div class="detail-chip">
                                <b>Breakdown</b>
                                <span>${escapeHtml(row.device_model || 'DEVICE')} ${row.device_rate ? `(${Number(row.device_rate).toLocaleString()})` : '(0)'} + ${escapeHtml(row.software || 'SOFTWARE')} ${row.software_rate ? `(${Number(row.software_rate).toLocaleString()})` : '(0)'} + RELAY ${row.relay_rate ? `(${Number(row.relay_rate).toLocaleString()})` : '(0)'}</span>
                            </div>
                        ` : `
                            <div class="detail-chip">
                                <b>Renewal Rate</b>
                                <span>${escapeHtml(row.software || 'SOFTWARE')} -> ${formatMoney(row.software_rate)}</span>
                            </div>
                            <div class="detail-chip">
                                <b>Row ID</b>
                                <span>${escapeHtml(row.id || '-')}</span>
                            </div>
                        `}
                    </div>
                </div>
            `).join('');
        }

        async function refresh() {
            try {
                const res = await fetch(API + "?action=details");
                const data = await res.json();
                if (!data || data.success === false) {
                    throw new Error(data?.message || 'Failed to load settlement details');
                }

                const sales = Number(data.sales);
                const renewal = Number(data.renewal);
                const total = Number(data.total);

                animateValue(document.getElementById('salesVal'), 0, Number.isFinite(sales) ? sales : 0, 1500);
                animateValue(document.getElementById('renVal'), 0, Number.isFinite(renewal) ? renewal : 0, 1500);
                animateValue(document.getElementById('totalVal'), 0, Number.isFinite(total) ? total : 0, 1500);
                document.getElementById('salesCount').innerText = `${(data.salesDetails || []).length} rows`;
                document.getElementById('renewalCount').innerText = `${(data.renewalDetails || []).length} rows`;
                renderRows('salesDetails', data.salesDetails || [], 'sales');
                renderRows('renewalDetails', data.renewalDetails || [], 'renewal');
            } catch (err) {
                console.error(err);
                animateValue(document.getElementById('salesVal'), 0, 0, 300);
                animateValue(document.getElementById('renVal'), 0, 0, 300);
                animateValue(document.getElementById('totalVal'), 0, 0, 300);
                document.getElementById('salesCount').innerText = `0 rows`;
                document.getElementById('renewalCount').innerText = `0 rows`;
                document.getElementById('salesDetails').innerHTML = `<div class="empty-note">Sales details load ஆகல.</div>`;
                document.getElementById('renewalDetails').innerHTML = `<div class="empty-note">Renewal details load ஆகல.</div>`;
            }
        }

        async function sync() {
            const btn = document.getElementById('syncBtn');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Syncing...';
            const res = await fetch(API + "?action=fetch");
            const data = await res.json();
            if(data.status === 'fetched') {
                await refresh();
                alert("Cloud sync complete!");
            }
            btn.innerHTML = '<i class="fa-solid fa-rotate"></i> Sync Cloud Logs';
        }

        function openModal() { document.getElementById('modal').style.display='flex'; }
        function closeModal() { document.getElementById('modal').style.display='none'; }

        async function confirmPay() {
            const tx = document.getElementById('txid').value;
            if(!tx) return alert("Enter Reference ID");
            const res = await fetch(API + "?action=process&txn=" + encodeURIComponent(tx));
            const data = await res.json();
            if(data.status === 'payment completed') {
                alert("Settlement Successful!");
                location.reload();
            }
        }

        // 📜 Load Settlement History
        async function loadHistory() {
            const body = document.getElementById('historyBody');
            const count = document.getElementById('historyCount');
            try {
                const res = await fetch(API + "?action=history");
                const data = await res.json();
                if (data.success && data.history && data.history.length > 0) {
                    count.innerText = `${data.history.length} entries`;
                    body.innerHTML = data.history.map(h => {
                        const dt = h.created_at ? new Date(h.created_at).toLocaleDateString('en-IN', {day:'2-digit', month:'short', year:'2-digit'}) : '—';
                        return `<tr>
                            <td style="color:var(--text-muted);font-size:10px;">${dt}</td>
                            <td style="font-weight:700;font-size:10px;">${escapeHtml(h.txn_id || '—')}</td>
                            <td style="font-weight:700;color:var(--success);">₹${Number(h.sales_amount||0).toLocaleString()}</td>
                            <td style="font-weight:700;color:var(--secondary);">₹${Number(h.renewal_amount||0).toLocaleString()}</td>
                            <td style="font-weight:800;font-family:'Outfit';">₹${Number(h.total_amount||0).toLocaleString()}</td>
                        </tr>`;
                    }).join('');
                } else {
                    count.innerText = '0 entries';
                    body.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px;">No settlements yet.</td></tr>';
                }
            } catch(e) {
                body.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--danger);padding:20px;">Failed to load history.</td></tr>';
            }
        }

        // 📊 Load Detailed Summary
        async function loadSummary() {
            try {
                const res = await fetch(API + "?action=summary");
                const data = await res.json();
                if (data.success) {
                    document.getElementById('devTotal').innerText = '₹ ' + Number(data.device_total||0).toLocaleString();
                    document.getElementById('swTotal').innerText = '₹ ' + Number(data.software_total||0).toLocaleString();
                    document.getElementById('relayTotal').innerText = '₹ ' + Number(data.relay_total||0).toLocaleString();
                    document.getElementById('renTotal').innerText = '₹ ' + Number(data.renewal_total||0).toLocaleString();
                }
            } catch(e) {}
        }

        // 🔍 Filter rows by vehicle/IMEI
        function filterRows() {
            const q = document.getElementById('filterInput').value.toLowerCase();
            document.querySelectorAll('.detail-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }

        // 🔄 Reload all data
        async function loadAll() {
            await Promise.all([refresh(), loadSummary(), loadHistory()]);
        }

        // ✅ Enhanced refresh to also update summary
        const originalRefresh = refresh;
        refresh = async function() {
            await originalRefresh();
            await loadSummary();
        };

        window.onload = function() {
            loadAll();
            // ⏰ Auto-refresh every 30 seconds
            setInterval(loadAll, 30000);
        };
    </script>
</body>
</html>
