<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>SIM Settlement | SK LOGIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            --whatsapp: #25D366;
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
        .container { max-width: 900px; margin: 20px auto; padding: 0 20px; animation: slideUp 0.6s ease-out; }
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        .glass-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 28px; padding: 25px;
            backdrop-filter: blur(20px); box-shadow: 0 20px 40px rgba(0,0,0,0.4); margin-bottom: 20px;
        }
        .section-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .kpi-block { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
        .kpi-mini { background: rgba(255,255,255,0.04); padding: 20px; border-radius: 20px; border: 1px solid var(--border); }
        .kpi-mini .label { font-size: 9px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .kpi-mini .val { font-size: 20px; font-weight: 800; font-family: 'Outfit'; }
        .total-banner {
            background: linear-gradient(135deg, var(--primary), #6366f1); border-radius: 24px; padding: 25px; text-align: center;
            margin-bottom: 20px; box-shadow: 0 10px 30px var(--primary-glow);
        }
        .total-banner .val { font-size: 38px; font-weight: 800; font-family: 'Outfit'; display: block; margin-top: 5px; }
        .input-field {
            width: 100%; padding: 18px 20px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 16px; margin-bottom: 12px; outline: none;
        }
        .btn-action {
            width: 100%; padding: 18px; border: none; border-radius: 18px; font-weight: 800; font-size: 14px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s;
        }
        .btn-primary { background: rgba(255,255,255,0.08); color: white; }
        .btn-confirm { background: var(--success); color: white; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); }
        .btn-share { background: var(--whatsapp); color: white; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px; }
        .detail-box { background: rgba(255,255,255,0.04); border: 1px solid var(--border); border-radius: 18px; padding: 16px; }
        .detail-box h4 { font-size: 11px; font-weight: 800; color: var(--secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .detail-box .meta { color: var(--text-muted); font-size: 12px; margin-bottom: 10px; }
        .vehicle-lines { color: var(--text); font-size: 13px; line-height: 1.7; white-space: pre-wrap; word-break: break-word; }

        /* 📊 SIM Type Breakdown */
        .sim-breakdown { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        .sb-card { background: rgba(255,255,255,0.04); border-radius: 18px; padding: 16px; text-align: center; border: 1px solid var(--border); }
        .sb-card .lb { font-size: 8px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .sb-card .vl { font-size: 20px; font-weight: 800; font-family: 'Outfit'; margin-top: 4px; }

        /* 📜 Settlement History */
        .sim-history { margin-top: 22px; }
        .sim-table-wrap { max-height: 300px; overflow-y: auto; }
        .sim-table { width: 100%; border-collapse: collapse; }
        .sim-table th { text-align: left; font-size: 9px; text-transform: uppercase; color: var(--text-muted); padding: 12px; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        .sim-table td { padding: 12px; font-size: 11px; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 99px; font-size: 9px; font-weight: 800; }
        .badge-done { background: rgba(16,185,129,0.15); color: var(--success); }
        .badge-pending { background: rgba(245,158,11,0.15); color: #f59e0b; }

        /* 🔍 Filter */
        .sim-filter { margin-bottom: 15px; }
        .sim-filter input { width: 100%; padding: 12px 16px; background: rgba(15,23,42,0.4); border: 1px solid var(--border); border-radius: 14px; color: white; font-size: 13px; outline: none; }

        /* Status Banner */
        .status-banner { display: none; padding: 12px 16px; border-radius: 14px; font-size: 11px; font-weight: 800; margin-top: 10px; }
        .status-banner.show { display: flex; align-items: center; gap: 8px; }
        .status-banner.pending { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); color: #f59e0b; }
        .status-banner.done { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: var(--success); }
        textarea {
            width: 100%; min-height: 180px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 18px;
            color: var(--text-muted); padding: 15px; font-size: 12px; font-family: 'Courier New', monospace; line-height: 1.6; margin-bottom: 15px; resize: vertical;
        }
        @media (max-width: 700px) {
            .detail-grid, .kpi-block { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div style="font-size: 10px; font-weight: 800; color: #f59e0b; text-transform: uppercase;">SIM Settlement</div>
    </header>

    <div class="container">
        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-tower-cell"></i> Carrier Summary</div>
            <div class="total-banner">
                <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; opacity: 0.8;">SIM Settlement Total</span>
                <span class="val" id="totalVal">Rs. 0</span>
            </div>

            <div class="kpi-block">
                <div class="kpi-mini">
                    <div class="label">Sales SIM</div>
                    <div class="val" id="salesVal">Rs. 0</div>
                </div>
                <div class="kpi-mini">
                    <div class="label">Renewal SIM</div>
                    <div class="val" id="renVal">Rs. 0</div>
                </div>
            </div>

            <!-- 📊 SIM Type Breakdown -->
            <div class="sim-breakdown" id="simBreakdown">
                <div class="sb-card"><div class="lb">🔊 Voice</div><div class="vl" id="voiceTotal">₹ 0</div><div style="font-size:9px;color:var(--text-muted);" id="voiceCount">0</div></div>
                <div class="sb-card"><div class="lb">📡 Basic</div><div class="vl" id="basicTotal">₹ 0</div><div style="font-size:9px;color:var(--text-muted);" id="basicCount">0</div></div>
            </div>

            <!-- 🟢 Status Banner -->
            <div class="status-banner" id="statusBanner">
                <i class="fa-solid fa-circle-info"></i>
                <span id="statusText">No active settlement</span>
            </div>

            <button class="btn-action btn-primary" onclick="generateSettlement()">
                <i class="fa-solid fa-file-invoice"></i> Generate Settlement (<span id="count">0</span>)
            </button>

            <div style="height: 12px;"></div>
            <input type="text" id="txid" class="input-field" placeholder="Transaction Ref / UPI ID">
            <button class="btn-action btn-confirm" onclick="confirmSettlement()">
                <i class="fa-solid fa-check-double"></i> Finalize & Confirm Payment
            </button>
        </div>

        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-list-check"></i> Settlement Details</div>
            <div class="detail-grid">
                <div class="detail-box">
                    <h4>Sales Vehicles</h4>
                    <div class="meta">Count: <span id="salesCount">0</span> | Amount: <span id="salesAmountText">Rs. 0</span></div>
                    <div class="vehicle-lines" id="salesVehicles">No records.</div>
                </div>
                <div class="detail-box">
                    <h4>Renewal Vehicles</h4>
                    <div class="meta">Count: <span id="renewalCount">0</span> | Amount: <span id="renewalAmountText">Rs. 0</span></div>
                    <div class="vehicle-lines" id="renewalVehicles">No records.</div>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="section-label"><i class="fa-brands fa-whatsapp"></i> Report Content</div>
            <textarea id="report" readonly></textarea>
            <button class="btn-action btn-share" onclick="share()">
                <i class="fa-brands fa-whatsapp"></i> Share Settlement
            </button>
        </div>

        <!-- 🔍 Filter -->
        <div class="sim-filter">
            <input type="text" id="simFilterInput" placeholder="🔍 Filter vehicles..." oninput="filterSimVehicles()">
        </div>

        <!-- 📜 Settlement History -->
        <div class="glass-card sim-history">
            <div class="section-label">
                <i class="fa-solid fa-clock-rotate-left"></i> Settlement History
                <span style="font-size:10px;color:var(--text-muted);margin-left:auto;" id="historyCount">0 entries</span>
                <button class="btn-action" style="width:auto;padding:6px 12px;font-size:10px;background:rgba(255,255,255,0.06);" onclick="loadHistory()"><i class="fa-solid fa-rotate"></i></button>
            </div>
            <div class="sim-table-wrap">
                <table class="sim-table">
                    <thead><tr><th>Date</th><th>Txn ID</th><th>Sales</th><th>Renewal</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody id="historyBody">
                        <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:20px;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = 'api_sim_settlement.php';

        function animate(obj, start, end, duration) {
            start = Number(start);
            end = Number(end);
            if (!Number.isFinite(start)) start = 0;
            if (!Number.isFinite(end)) end = 0;
            let st = null;
            const step = (ts) => {
                if (!st) st = ts;
                const progress = Math.min((ts - st) / duration, 1);
                obj.innerText = 'Rs. ' + Math.floor(progress * (end - start) + start).toLocaleString();
                if (progress < 1) window.requestAnimationFrame(step);
            };
            window.requestAnimationFrame(step);
        }

        function formatMoney(value) {
            const num = Number(value);
            return 'Rs. ' + (Number.isFinite(num) ? num : 0).toLocaleString();
        }

        function renderVehicleBlock(elementId, lines) {
            document.getElementById(elementId).textContent = lines && lines.length ? lines.join('\n') : 'No records.';
        }

        function renderReport(report) {
            if (!report) return;
            document.getElementById('report').value = report.message || '';
            document.getElementById('salesCount').innerText = report.salesCount || 0;
            document.getElementById('renewalCount').innerText = report.renewalCount || 0;
            document.getElementById('salesAmountText').innerText = formatMoney(report.salesAmount);
            document.getElementById('renewalAmountText').innerText = formatMoney(report.renewalAmount);
            renderVehicleBlock('salesVehicles', (report.salesVehicles || []).map(v => `SALES - ${v}`));
            renderVehicleBlock('renewalVehicles', (report.renewalVehicles || []).map(v => `RENEWAL - ${v}`));
        }

        async function loadSummary() {
            const res = await fetch(API + '?action=summary');
            const data = await res.json();
            animate(document.getElementById('salesVal'), 0, data.sales, 1000);
            animate(document.getElementById('renVal'), 0, data.renewal, 1000);
            animate(document.getElementById('totalVal'), 0, data.total, 1000);
            document.getElementById('count').innerText = data.count || 0;
            if (data.latestReport) {
                renderReport(data.latestReport);
            }
            // Update status banner
            if (data.latestStatus === 'DONE') {
                showStatus('✅ Settlement Completed', 'done');
            } else if (data.latestStatus === 'PENDING') {
                showStatus('⏳ Pending settlement — Generate & confirm', 'pending');
            } else {
                hideStatus();
            }
        }

        // 📊 Load SIM Type Breakdown
        async function loadBreakdown() {
            try {
                const res = await fetch(API + '?action=breakdown');
                const data = await res.json();
                if (data.success) {
                    document.getElementById('voiceTotal').innerText = '₹ ' + Number(data.voice_total||0).toLocaleString();
                    document.getElementById('voiceCount').innerText = data.voice_count + ' vehicles';
                    document.getElementById('basicTotal').innerText = '₹ ' + Number(data.basic_total||0).toLocaleString();
                    document.getElementById('basicCount').innerText = data.basic_count + ' vehicles';
                }
            } catch(e) {}
        }

        // 🔍 Filter vehicles in vehicle blocks
        function filterSimVehicles() {
            const q = document.getElementById('simFilterInput').value.toLowerCase();
            document.querySelectorAll('.vehicle-lines').forEach(block => {
                const lines = block.innerText.split('\n');
                const filtered = lines.filter(l => l.toLowerCase().includes(q));
                block.innerText = filtered.length ? filtered.join('\n') : (q ? '— No match —' : 'No records.');
            });
        }

        // 🟢 Status banner helpers
        function showStatus(text, type) {
            const banner = document.getElementById('statusBanner');
            const span = document.getElementById('statusText');
            banner.className = 'status-banner show ' + type;
            span.innerText = text;
        }
        function hideStatus() {
            document.getElementById('statusBanner').className = 'status-banner';
        }

        // 📜 Load Settlement History
        async function loadHistory() {
            const body = document.getElementById('historyBody');
            const count = document.getElementById('historyCount');
            try {
                const res = await fetch(API + '?action=history');
                const data = await res.json();
                if (data.success && data.history && data.history.length > 0) {
                    count.innerText = `${data.history.length} entries`;
                    body.innerHTML = data.history.map(h => {
                        const dt = h.settle_date ? new Date(h.settle_date).toLocaleDateString('en-IN', {day:'2-digit', month:'short', year:'2-digit'}) : '—';
                        const status = (h.status || 'PENDING').toUpperCase();
                        const badgeClass = status === 'DONE' ? 'badge-done' : 'badge-pending';
                        return `<tr>
                            <td style="color:var(--text-muted);font-size:10px;">${dt}</td>
                            <td style="font-weight:700;font-size:10px;">${h.txn_id || '—'}</td>
                            <td style="font-weight:700;color:var(--success);">₹${Number(h.sales_amount||0).toLocaleString()}</td>
                            <td style="font-weight:700;color:var(--secondary);">₹${Number(h.renewal_amount||0).toLocaleString()}</td>
                            <td style="font-weight:800;font-family:'Outfit';">₹${Number(h.total_amount||0).toLocaleString()}</td>
                            <td><span class="badge ${badgeClass}">${status}</span></td>
                        </tr>`;
                    }).join('');
                } else {
                    count.innerText = '0 entries';
                    body.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:20px;">No settlement history yet.</td></tr>';
                }
            } catch(e) {
                body.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--danger);padding:20px;">Failed to load history.</td></tr>';
            }
        }

        // 🔄 Load everything
        async function loadAll() {
            await Promise.all([loadSummary(), loadBreakdown(), loadHistory()]);
        }

        async function generateSettlement() {
            const res = await fetch(API, { method: 'POST', body: new URLSearchParams({ action: 'generate' }) });
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'No pending records to settle');
                return;
            }
            if (data.report) renderReport(data.report);
            await loadSummary();
            alert('Settlement Compiled!');
        }

        async function confirmSettlement() {
            const tx = document.getElementById('txid').value.trim();
            if (!tx) return alert('Enter Transaction Reference');
            const res = await fetch(API, { method: 'POST', body: new URLSearchParams({ action: 'confirm', txnId: tx }) });
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Settlement confirmation failed');
                return;
            }
            if (data.report) renderReport(data.report);
            alert('Settlement Confirmed!');
            location.reload();
        }

        function share() {
            const msg = document.getElementById('report').value;
            if (!msg) return;
            window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
        }

        window.onload = function() {
            loadAll();
            // ⏰ Auto-refresh every 30 seconds
            setInterval(loadAll, 30000);
        };
    </script>
</body>
</html>
