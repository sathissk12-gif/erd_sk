<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Dealer Invoice Search</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="theme_engine.js"></script>
    <style>
        :root { --primary:#0ea5e9; --accent:#14b8a6; --bg:#082f49; --card:rgba(8,47,73,.45); --border:rgba(255,255,255,.08); --text:#f8fafc; --muted:#cbd5e1; }
        * { box-sizing:border-box; margin:0; padding:0; -webkit-tap-highlight-color:transparent; }
        body { font-family:'Outfit',sans-serif; color:var(--text); min-height:100vh; background:radial-gradient(circle at top right,#164e63,#082f49 45%,#020617); padding-top:env(safe-area-inset-top, 0px); padding-bottom:90px; }
        .header { display:flex; justify-content:space-between; align-items:center; padding:calc(14px + env(safe-area-inset-top, 0px)) 5% 20px; position:sticky; top:0; z-index:10; backdrop-filter:blur(20px); background:rgba(2,6,23,.55); border-bottom:1px solid var(--border); }
        .title { font-size:24px; font-weight:800; display:flex; gap:12px; align-items:center; cursor:pointer; }
        .container { max-width:1180px; margin:0 auto; padding:30px 5%; display:grid; gap:24px; }
        .card { background:var(--card); border:1px solid var(--border); border-radius:24px; padding:24px; backdrop-filter:blur(16px); }
        .search-row { display:grid; grid-template-columns:1fr 140px; gap:12px; margin-top:18px; }
        .search-input { width:100%; background:rgba(15,23,42,.5); border:1px solid var(--border); color:var(--text); border-radius:16px; padding:15px 20px; font-family:'Outfit'; font-size:16px; outline:none; }
        .btn-primary { border:none; border-radius:16px; cursor:pointer; font-family:'Outfit'; font-weight:700; color:white; background:linear-gradient(135deg,var(--primary),var(--accent)); }
        .table-wrap { overflow:auto; border-radius:18px; border:1px solid var(--border); }
        table { width:100%; min-width:900px; border-collapse:collapse; }
        th,td { padding:16px 14px; border-bottom:1px solid var(--border); text-align:left; }
        th { color:var(--muted); text-transform:uppercase; font-size:12px; background:rgba(255,255,255,.04); }
        .invoice-no { font-weight:800; color:#67e8f9; }
        .meta { color:var(--muted); font-size:12px; margin-top:4px; }
        .action-stack { display:flex; flex-wrap:wrap; gap:8px; }
        .action-btn { display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:8px 12px; border-radius:10px; text-decoration:none; border:none; color:white; font-size:13px; font-weight:700; cursor:pointer; }
        .view{ background:#0284c7; } .pdf{ background:#0f766e; } .share{ background:#25d366; }
        .empty { padding:40px 20px; text-align:center; color:var(--muted); }
        .mobile-results { display:none; }
        .card-grid { display:grid; gap:14px; }
        .v-card { background:rgba(15,23,42,.38); border:1px solid var(--border); border-radius:20px; padding:16px; display:grid; gap:14px; }
        .v-top { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; }
        .pill { display:inline-flex; align-items:center; padding:6px 10px; border-radius:999px; background:rgba(255,255,255,.08); color:#cffafe; font-size:11px; font-weight:700; }
        .mini-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px; }
        .mini-box { background:rgba(255,255,255,.04); border-radius:14px; padding:10px 12px; }
        .mini-box .label { color:var(--muted); font-size:11px; margin-bottom:4px; }
        .mini-box .value { font-weight:700; }
        @media (max-width:768px) {
            .search-row { grid-template-columns:1fr; }
            .desktop-results { display:none; }
            .mobile-results { display:block; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title" onclick="window.location='index.html'"><i class="fa-solid fa-arrow-left"></i> Dealer Invoices</div>
    </div>
    <div class="container">
        <div class="card">
            <h1 style="font-size:28px; font-weight:800;">Dealer Invoice Portal</h1>
            <p style="color:var(--muted); margin-top:8px;">Dealer name search    <div class="search-row">
                <input id="qInput" class="search-input" type="text" placeholder="Dealer Name" oninput="debouncedSearch()">
                <button class="btn-primary" onclick="search()">Search</button>
            </div>
        </div>
        <div class="card desktop-results">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Dealer</th>
                            <th>Date</th>
                            <th>Devices</th>
                            <th>Total</th>
                            <th>Profit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="resBody">
                        <tr><td colspan="7" class="empty">Type dealer name to search invoices.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card mobile-results">
            <div id="gridContainer" class="card-grid">
                <div class="empty">Type dealer name to search invoices.</div>
            </div>
        </div>
    </div>
    <script>
        function currency(v){ return new Intl.NumberFormat('en-IN').format(v||0); }
        function cleanPhone(num){
            return String(num || '').replace(/\D/g, '').replace(/^91(?=\d{10}$)/, '');
        }
        function escapeHtml(value){
            return String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
        }
        function buildWhatsappUrl(row){
            const phone = cleanPhone(row.dealer_mobile);
            if(!phone) return '';
            const viewUrl = `dealer_invoice.php?uid=${encodeURIComponent(row.uid)}&view=public`;
            const msg = `Dear ${row.customer_name || row.dealer_name},\n\nYour device invoice is ready.\n\nDealer: ${row.dealer_name}\nInvoice: ${row.invoice_no}\nAmount: Rs. ${currency(row.total_selling_price)}\n\nView Bill: https://erd.traxengps.in/${viewUrl}\n\n-- SK ENTERPRISES`;
            return `https://wa.me/91${phone}?text=${encodeURIComponent(msg)}`;
        }
        let timer;
        function debouncedSearch(){ clearTimeout(timer); timer = setTimeout(search, 350); }
        async function search(){
            const q = document.getElementById('qInput').value.trim();
            const body = document.getElementById('resBody');
            const grid = document.getElementById('gridContainer');
            if(!q){
                body.innerHTML = `<tr><td colspan="7" class="empty">Type dealer name to search invoices.</td></tr>`;
                grid.innerHTML = `<div class="empty">Type dealer name to search invoices.</div>`;
                return;
            }
            body.innerHTML = `<tr><td colspan="7" class="empty">Searching...</td></tr>`;
            grid.innerHTML = `<div class="empty">Searching...</div>`;
            const res = await fetch(`api_dealer_invoice.php?action=search&query=${encodeURIComponent(q)}`);
            const rows = await res.json();
            if(!rows.length){
                body.innerHTML = `<tr><td colspan="7" class="empty">No results found.</td></tr>`;
                grid.innerHTML = `<div class="empty">No results found.</div>`;
                return;
            }
            body.innerHTML = rows.map(row => {
                const viewUrl = `dealer_invoice.php?uid=${encodeURIComponent(row.uid)}`;
                const pdfUrl = `${viewUrl}&download=1`;
                const waUrl = buildWhatsappUrl(row);
                const shareBtn = waUrl
                    ? `<a href="${waUrl}" class="action-btn share" target="_blank">Share</a>`
                    : `<button class="action-btn share" disabled style="opacity:.45; cursor:not-allowed;">No Number</button>`;
                return `
                    <tr>
                        <td><div class="invoice-no">${row.invoice_no}</div><div class="meta">${row.invoice_date}</div></td>
                        <td><div style="font-weight:700;">${row.dealer_name}</div><div class="meta">${row.dealer_mobile || ''}</div></td>
                        <td>${row.date}</td>
                        <td>${row.total_devices}<div class="meta">${Number(row.pending_devices || 0) > 0 ? row.pending_devices + ' pending' : 'Paid'}</div></td>
                        <td>Rs. ${currency(row.total_selling_price)}</td>
                        <td>Rs. ${currency(row.total_profit)}<div class="meta">${Number(row.pending_amount || 0) > 0 ? 'Due Rs. ' + currency(row.pending_amount) : 'No pending'}</div></td>
                        <td>
                            <div class="action-stack">
                                <a href="${viewUrl}" class="action-btn view" target="_blank">Open</a>
                                <a href="${pdfUrl}" class="action-btn pdf" target="_blank">PDF</a>
                                ${shareBtn}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            grid.innerHTML = rows.map(row => {
                const viewUrl = `dealer_invoice.php?uid=${encodeURIComponent(row.uid)}`;
                const pdfUrl = `${viewUrl}&download=1`;
                const waUrl = buildWhatsappUrl(row);
                const shareAction = waUrl
                    ? `<a href="${waUrl}" class="action-btn share" style="flex:1;" target="_blank">WhatsApp</a>`
                    : `<button class="action-btn share" style="flex:1; opacity:.45; cursor:not-allowed;" disabled>No Number</button>`;
                return `
                    <div class="v-card">
                        <div class="v-top">
                            <div>
                                <div class="invoice-no">${escapeHtml(row.invoice_no)}</div>
                                <div class="meta">${escapeHtml(row.invoice_date)}</div>
                            </div>
                            <span class="pill">${escapeHtml(row.total_devices)} Devices</span>
                        </div>
                        <div>
                            <div style="font-size:18px; font-weight:800;">${escapeHtml(row.dealer_name)}</div>
                            <div class="meta">${escapeHtml(row.dealer_mobile || 'No mobile in customer table')}</div>
                        </div>
                        <div class="mini-grid">
                            <div class="mini-box"><div class="label">Date</div><div class="value">${escapeHtml(row.date)}</div></div>
                            <div class="mini-box"><div class="label">Profit</div><div class="value">Rs. ${escapeHtml(currency(row.total_profit))}</div></div>
                            <div class="mini-box"><div class="label">Total</div><div class="value">Rs. ${escapeHtml(currency(row.total_selling_price))}</div></div>
                            <div class="mini-box"><div class="label">Pending</div><div class="value">${Number(row.pending_devices || 0) > 0 ? escapeHtml(row.pending_devices + ' / Rs. ' + currency(row.pending_amount)) : 'No pending'}</div></div>
                        </div>
                        <div class="action-stack" style="width:100%;">
                            <a href="${viewUrl}" class="action-btn view" style="flex:1;" target="_blank">Open</a>
                            <a href="${pdfUrl}" class="action-btn pdf" style="flex:1;" target="_blank">PDF</a>
                            ${shareAction}
                        </div>
                    </div>
                `;
            }).join('');
        }
    </script>
</body>
</html>
