<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Payment Gateway | SK LOGIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="theme_engine.js"></script>

    <style>
        :root {
            --primary: #6366f1;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-muted: #94a3b8;
            --success: #10b981;
            --danger: #f43f5e;
            --warning: #f59e0b;
        }
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text); min-height: 100vh; padding-bottom: 80px;
        }
        header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(3, 7, 18, 0.7); backdrop-filter: blur(25px);
            padding: calc(12px + env(safe-area-inset-top,0px)) 20px 16px;
            border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .back-link { text-decoration: none; color: white; display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14px; }
        .container { max-width: 700px; margin: 20px auto; padding: 0 16px; }

        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 14px; text-align: center;
            backdrop-filter: blur(10px);
        }
        .stat-card .num { font-size: 20px; font-weight: 800; }
        .stat-card .label { font-size: 9px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }

        .glass-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 22px;
            backdrop-filter: blur(20px); margin-bottom: 16px;
        }
        .section-label {
            font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;
            letter-spacing: 1.5px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;
        }

        .input-group { margin-bottom: 14px; }
        .input-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; }
        .input-field {
            width: 100%; padding: 12px 16px; background: rgba(15,23,42,0.4); border: 1px solid var(--border);
            border-radius: 12px; color: white; font-size: 14px; outline: none; font-family: inherit;
        }
        .input-field:focus { border-color: var(--primary); }

        .btn-main {
            padding: 14px 24px; border: none; border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            color: white; font-weight: 800; cursor: pointer; font-size: 14px; transition: 0.2s; width: 100%;
        }
        .btn-main:active { transform: scale(0.96); }
        .btn-success { background: linear-gradient(135deg, #059669, #10b981); }

        .tab-bar { display: flex; gap: 8px; margin-bottom: 18px; overflow-x: auto; }
        .tab {
            padding: 8px 16px; background: var(--surface); border: 1px solid var(--border); border-radius: 99px;
            font-size: 12px; font-weight: 700; color: var(--text-muted); cursor: pointer; white-space: nowrap;
        }
        .tab.active { background: var(--primary); color: white; border-color: var(--primary); }
        .hidden { display: none; }

        .qr-box { text-align: center; padding: 20px; background: white; border-radius: 20px; margin: 15px 0; }
        .qr-box img { max-width: 250px; border-radius: 12px; }
        .upi-id {
            font-size: 18px; font-weight: 800; color: #1e293b; letter-spacing: 1px;
            background: #f1f5f9; padding: 10px 20px; border-radius: 12px; display: inline-block; margin: 10px 0;
        }

        .txn-item {
            background: rgba(30,41,59,0.3); border: 1px solid var(--border); border-radius: 16px;
            padding: 14px; margin-bottom: 8px; font-size: 13px;
        }
        .txn-item .hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .txn-item .amt { font-weight: 800; font-size: 16px; }
        .badge { font-size: 10px; padding: 3px 10px; border-radius: 99px; font-weight: 800; }
        .badge-paid { background: rgba(16,185,129,0.15); color: var(--success); }
        .badge-created { background: rgba(245,158,11,0.15); color: var(--warning); }
        .badge-failed { background: rgba(244,63,94,0.15); color: var(--danger); }

        .dual-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 500px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .dual-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div style="font-weight:800; font-size:14px;">💳 PAYMENTS</div>
    </header>

    <div class="container">
        <div class="stats-row">
            <div class="stat-card"><div class="num" id="statToday">₹0</div><div class="label">Today</div></div>
            <div class="stat-card"><div class="num" id="statMonth">₹0</div><div class="label">This Month</div></div>
            <div class="stat-card"><div class="num" id="statTotal">₹0</div><div class="label">Total</div></div>
            <div class="stat-card"><div class="num" id="statCount">0</div><div class="label">Payments</div></div>
        </div>

        <div class="tab-bar" id="tabs">
            <div class="tab active" onclick="switchTab('pay')">💳 Pay Now</div>
            <div class="tab" onclick="switchTab('qr')">📱 UPI QR</div>
            <div class="tab" onclick="switchTab('manual')">📝 Record</div>
            <div class="tab" onclick="switchTab('history')">📋 History</div>
        </div>

        <div id="panel-pay" class="glass-card">
            <div class="section-label"><i class="fa-solid fa-credit-card"></i> Generate Payment Link</div>
            <div class="input-group">
                <label>Amount (₹) <span style="color:var(--danger);">*</span></label>
                <input type="number" id="payAmount" class="input-field" placeholder="500" step="1">
            </div>
            <div class="dual-grid">
                <div class="input-group">
                    <label>Customer Name</label>
                    <input type="text" id="payName" class="input-field" placeholder="Vimal">
                </div>
                <div class="input-group">
                    <label>Mobile</label>
                    <input type="text" id="payMobile" class="input-field" placeholder="9876543210">
                </div>
            </div>
            <div class="input-group">
                <label>Reference</label>
                <select id="payRefType" class="input-field">
                    <option value="manual">Manual Payment</option>
                    <option value="sale">Sale</option>
                    <option value="renewal">Renewal</option>
                </select>
            </div>
            <button class="btn-main" onclick="generatePayment()"><i class="fa-solid fa-link"></i> Generate Payment Link</button>
            <div id="payResult" style="margin-top:15px;"></div>
        </div>

        <div id="panel-qr" class="glass-card hidden">
            <div class="section-label"><i class="fa-solid fa-qrcode"></i> UPI QR Code</div>
            <div class="input-group">
                <label>Amount (₹)</label>
                <input type="number" id="qrAmount" class="input-field" placeholder="Enter amount" step="1" oninput="generateQR()">
            </div>
            <div class="input-group">
                <label>Note (optional)</label>
                <input type="text" id="qrNote" class="input-field" placeholder="Payment for..." oninput="generateQR()">
            </div>
            <div id="qrBox">
                <div style="text-align:center; padding:30px; color:var(--text-muted);">
                    <i class="fa-solid fa-qrcode" style="font-size:48px;"></i>
                    <p style="margin-top:10px;">Enter amount to generate QR</p>
                </div>
            </div>
        </div>

        <div id="panel-manual" class="glass-card hidden">
            <div class="section-label"><i class="fa-solid fa-pen"></i> Record Manual Payment</div>
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:14px;">UPI / Cash / Bank transfer received ah? Record pannalam.</p>
            <div class="dual-grid">
                <div class="input-group">
                    <label>Amount (₹) <span style="color:var(--danger);">*</span></label>
                    <input type="number" id="manAmount" class="input-field" placeholder="500" step="1">
                </div>
                <div class="input-group">
                    <label>Payment Method</label>
                    <select id="manMethod" class="input-field">
                        <option value="UPI">UPI</option>
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Card">Card</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>
            </div>
            <div class="dual-grid">
                <div class="input-group">
                    <label>Customer Name <span style="color:var(--danger);">*</span></label>
                    <input type="text" id="manName" class="input-field" placeholder="Customer name">
                </div>
                <div class="input-group">
                    <label>Mobile</label>
                    <input type="text" id="manMobile" class="input-field" placeholder="9876543210">
                </div>
            </div>
            <div class="input-group">
                <label>UPI Reference / Transaction ID</label>
                <input type="text" id="manRef" class="input-field" placeholder="Optional UPI ref number">
            </div>
            <button class="btn-main btn-success" onclick="recordManual()"><i class="fa-solid fa-check"></i> Record Payment</button>
            <div id="manResult" style="margin-top:12px;"></div>
        </div>

        <div id="panel-history" class="glass-card hidden">
            <div class="section-label" style="display:flex; justify-content:space-between;">
                <span><i class="fa-solid fa-clock-rotate-left"></i> Payment History</span>
                <button class="btn-sm" style="padding:6px 14px;background:var(--surface);border:1px solid var(--border);border-radius:10px;color:var(--text);font-weight:700;font-size:11px;cursor:pointer;" onclick="loadHistory()"><i class="fa-solid fa-rotate"></i></button>
            </div>
            <div class="input-group">
                <input type="text" id="historySearch" class="input-field" placeholder="Search by mobile..." oninput="loadHistory()">
            </div>
            <div id="historyList">
                <div style="text-align:center; padding:20px; color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>
            </div>
        </div>
    </div>

    <script>
        const API = 'api_payment_gateway.php';
        window.onload = function() { loadStats(); loadHistory(); };

        async function loadStats() {
            try {
                const res = await fetch(API + '?action=stats');
                const d = await res.json();
                if (d.success) {
                    document.getElementById('statToday').innerText = '₹' + Number(d.today).toLocaleString('en-IN');
                    document.getElementById('statMonth').innerText = '₹' + Number(d.month).toLocaleString('en-IN');
                    document.getElementById('statTotal').innerText = '₹' + Number(d.total).toLocaleString('en-IN');
                    document.getElementById('statCount').innerText = d.count;
                }
            } catch(e) {}
        }

        function switchTab(tab) {
            document.querySelectorAll('#tabs .tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('#tabs .tab')[['pay','qr','manual','history'].indexOf(tab)].classList.add('active');
            ['pay','qr','manual','history'].forEach(p => {
                document.getElementById('panel-' + p).classList.toggle('hidden', p !== tab);
            });
            if (tab === 'history') loadHistory();
        }

        async function generatePayment() {
            const amount = document.getElementById('payAmount').value;
            const name = document.getElementById('payName').value || 'Customer';
            const mobile = document.getElementById('payMobile').value;
            const refType = document.getElementById('payRefType').value;
            const result = document.getElementById('payResult');
            if (!amount || amount <= 0) { result.innerHTML = '<span style="color:var(--danger);">❌ Enter valid amount</span>'; return; }
            result.innerHTML = '<span style="color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Creating payment...</span>';
            try {
                const fd = new FormData();
                fd.append('action', 'create_order'); fd.append('amount', amount);
                fd.append('customer_name', name); fd.append('customer_mobile', mobile);
                fd.append('reference_type', refType);
                const res = await fetch(API, { method: 'POST', body: fd });
                const d = await res.json();
                if (!d.success) { result.innerHTML = `<span style="color:var(--danger);">❌ ${d.error}</span>`; return; }
                result.innerHTML = `<div style="padding:12px; background:rgba(16,185,129,0.1); border-radius:12px; color:var(--success); font-size:13px;"><i class="fa-solid fa-qrcode"></i> Scan & Pay using any UPI app</div>
                    <div class="qr-box">
                        <img src="${d.qr_url}" alt="UPI QR Code">
                        <div style="margin-top:8px;">
                            <div class="upi-id">${d.upi_id}</div>
                            <div style="font-size:16px; color:#1e293b; font-weight:800; margin:8px 0;">₹${Number(amount).toLocaleString('en-IN')}</div>
                            <button style="background:#1e293b;color:white;border:none;padding:8px 20px;border-radius:8px;font-weight:700;cursor:pointer;" onclick="copyUPI('${d.upi_uri}')"><i class="fa-solid fa-copy"></i> Copy UPI Link</button>
                            <p style="margin-top:10px; font-size:11px; color:#64748b;">Open in PhonePe / Google Pay / Paytm</p>
                        </div>
                    </div>
                    <button class="btn-main btn-success" onclick="markAsPaid('${amount}', '${name}', '${mobile}', '${refType}')" style="margin-top:10px;"><i class="fa-solid fa-check"></i> I've Paid — Mark Complete</button>`;
            } catch(e) { result.innerHTML = `<span style="color:var(--danger);">❌ ${e.message}</span>`; }
        }

        async function markAsPaid(amount, name, mobile, refType) {
            const result = document.getElementById('payResult');
            const upiRef = prompt('Enter UPI Reference / Transaction ID (optional):');
            result.innerHTML = '<span style="color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Recording...</span>';
            try {
                const fd = new FormData();
                fd.append('action', 'mark_paid_manual'); fd.append('amount', amount);
                fd.append('customer_name', name); fd.append('customer_mobile', mobile);
                fd.append('reference_type', refType); fd.append('upi_reference', upiRef || '');
                fd.append('payment_method', 'UPI');
                const res = await fetch(API, { method: 'POST', body: fd });
                const d = await res.json();
                if (d.success) { result.innerHTML = `<span style="color:var(--success); font-size:16px;">✅ Payment recorded! ID: ${d.transaction_id}</span>`; loadStats(); }
                else { result.innerHTML = `<span style="color:var(--danger);">❌ ${d.error}</span>`; }
            } catch(e) { result.innerHTML = `<span style="color:var(--danger);">❌ ${e.message}</span>`; }
        }

        let qrTimer;
        function generateQR() {
            clearTimeout(qrTimer);
            qrTimer = setTimeout(async () => {
                const amount = document.getElementById('qrAmount').value;
                const note = document.getElementById('qrNote').value || 'Payment';
                const box = document.getElementById('qrBox');
                if (!amount || amount <= 0) { box.innerHTML = '<div style="text-align:center; padding:30px; color:var(--text-muted);"><i class="fa-solid fa-qrcode" style="font-size:48px;"></i><p style="margin-top:10px;">Enter amount</p></div>'; return; }
                try {
                    const res = await fetch(`${API}?action=generate_qr&amount=${amount}&note=${encodeURIComponent(note)}`);
                    const d = await res.json();
                    if (d.success) {
                        box.innerHTML = `<div class="qr-box">
                            <img src="${d.qr_url}" alt="UPI QR">
                            <div style="margin-top:12px;">
                                <div class="upi-id">${d.upi_id}</div>
                                <div style="color:#1e293b; font-weight:800; font-size:20px; margin:8px 0;">₹${Number(amount).toLocaleString('en-IN')}</div>
                                <button style="background:#1e293b; color:white; border:none; padding:8px 20px; border-radius:8px; font-weight:700; cursor:pointer;" onclick="copyUPI('${d.upi_uri}')"><i class="fa-solid fa-copy"></i> Copy UPI Link</button>
                                <button style="background:#059669; color:white; border:none; padding:8px 20px; border-radius:8px; font-weight:700; cursor:pointer; margin-left:8px;" onclick="window.open('${d.upi_uri}', '_blank')"><i class="fa-solid fa-up-right-from-square"></i> Open in UPI</button>
                            </div>
                        </div>`;
                    }
                } catch(e) {}
            }, 500);
        }

        async function recordManual() {
            const amount = document.getElementById('manAmount').value;
            const name = document.getElementById('manName').value;
            const mobile = document.getElementById('manMobile').value;
            const method = document.getElementById('manMethod').value;
            const upiRef = document.getElementById('manRef').value;
            const result = document.getElementById('manResult');
            if (!amount || amount <= 0) { result.innerHTML = '<span style="color:var(--danger);">❌ Enter valid amount</span>'; return; }
            if (!name) { result.innerHTML = '<span style="color:var(--danger);">❌ Enter customer name</span>'; return; }
            result.innerHTML = '<span style="color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Recording...</span>';
            try {
                const fd = new FormData();
                fd.append('action', 'mark_paid_manual'); fd.append('amount', amount);
                fd.append('customer_name', name); fd.append('customer_mobile', mobile);
                fd.append('payment_method', method); fd.append('upi_reference', upiRef);
                const res = await fetch(API, { method: 'POST', body: fd });
                const d = await res.json();
                if (d.success) {
                    result.innerHTML = `<span style="color:var(--success);">✅ ₹${Number(amount).toLocaleString('en-IN')} recorded!</span>`;
                    document.getElementById('manAmount').value = ''; document.getElementById('manName').value = '';
                    document.getElementById('manMobile').value = ''; document.getElementById('manRef').value = '';
                    loadStats(); loadHistory();
                } else { result.innerHTML = `<span style="color:var(--danger);">❌ ${d.error}</span>`; }
            } catch(e) { result.innerHTML = `<span style="color:var(--danger);">❌ ${e.message}</span>`; }
        }

        async function loadHistory() {
            const mobile = document.getElementById('historySearch').value;
            const list = document.getElementById('historyList');
            try {
                const res = await fetch(`${API}?action=history&mobile=${encodeURIComponent(mobile)}&limit=100`);
                const d = await res.json();
                if (!d.success || !d.data.length) { list.innerHTML = '<div style="text-align:center; padding:30px; color:var(--text-muted);"><i class="fa-solid fa-inbox"></i><br>No payment records</div>'; return; }
                list.innerHTML = d.data.map(t => {
                    const badgeClass = t.status === 'paid' ? 'badge-paid' : t.status === 'created' ? 'badge-created' : 'badge-failed';
                    return `<div class="txn-item">
                        <div class="hdr"><div><span class="amt" style="color:${t.status === 'paid' ? 'var(--success)' : 'var(--text)'};">₹${Number(t.amount).toLocaleString('en-IN')}</span><span style="margin-left:8px; font-size:11px; color:var(--text-muted);">${t.payment_method || 'N/A'}</span></div>
                        <span class="badge ${badgeClass}">${t.status.toUpperCase()}</span></div>
                        <div style="font-size:13px; font-weight:600;">${escHtml(t.customer_name)}</div>
                        <div style="font-size:11px; color:var(--text-muted);">📱 ${t.customer_mobile || 'N/A'} &nbsp;|&nbsp; 📅 ${t.created_at ? t.created_at.substring(0, 16) : 'N/A'} ${t.paid_at ? '✅ ' + t.paid_at.substring(0, 16) : ''}</div>
                        ${t.upi_transaction_id ? `<div style="font-size:10px; color:var(--text-muted);">🔖 Ref: ${escHtml(t.upi_transaction_id)}</div>` : ''}
                    </div>`;
                }).join('');
            } catch(e) { list.innerHTML = '<div style="color:var(--danger);">Error loading history</div>'; }
        }

        function copyUPI(uri) {
            navigator.clipboard.writeText(uri).then(() => { alert('UPI link copied!'); })
            .catch(() => { const inp = document.createElement('input'); inp.value = uri; document.body.appendChild(inp); inp.select(); document.execCommand('copy'); document.body.removeChild(inp); alert('UPI link copied!'); });
        }

        function escHtml(s) { return (s || '').replace(/</g, '<').replace(/>/g, '>'); }
    </script>
</body>
</html>
