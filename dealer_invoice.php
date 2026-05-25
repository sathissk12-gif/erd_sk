<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dealer Invoice Generator</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg: #f1f5f9;
    --text: #1f2937;
    --muted: #6b7280;
    --line: #e5e7eb;
    --brand: #0f172a;
    --accent: #2563eb;
    --white: #ffffff;
}

body {
    margin: 0; background: var(--bg); color: var(--text); font-family: 'Outfit', sans-serif;
    padding: 30px 15px; display: flex; flex-direction: column; align-items: center; min-height: 100vh;
}

.controls {
    width: 100%; max-width: 210mm; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding: 0 5px;
}

.back-link {
    text-decoration: none; color: var(--muted); font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 6px;
}

.downloadBtn {
    padding: 12px 28px; background: var(--accent); color: white; border: none; border-radius: 12px;
    cursor: pointer; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 10px;
    box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2); font-family: 'Outfit', sans-serif;
}
.waBtn {
    padding: 12px 20px; background: #25d366; color: white; border: none; border-radius: 12px;
    cursor: pointer; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px;
    font-family: 'Outfit', sans-serif;
}

.invoice-wrapper {
    width: 100%; max-width: 210mm; background: var(--white); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
    border-radius: 16px; overflow: hidden; position: relative; border: 1px solid var(--line);
}

.invoice {
    padding: 18px 22px; background: var(--white); position: relative; min-height: 297mm; box-sizing: border-box;
}

.topbar {
    display: flex; justify-content: space-between; gap: 16px; border-bottom: 2px solid var(--line); padding-bottom: 10px;
}

.brand { display: flex; gap: 14px; align-items: center; }
.brand img {
    width: 52px; height: 52px; object-fit: contain; border-radius: 10px; border: 1px solid var(--line); padding: 4px;
}
.brand h1 { margin: 0; color: var(--brand); font-size: 20px; }
.brand p { margin: 2px 0 0; color: #4b5563; font-size: 10px; line-height: 1.25; }

.invoice-title { text-align: right; }
.invoice-title h2 { margin: 0; font-size: 21px; letter-spacing: 0.04em; color: var(--brand); }
.invoice-title .badge {
    display: inline-block; margin-top: 4px; background: #eff6ff; color: #1d4ed8; padding: 4px 8px;
    border-radius: 999px; font-size: 9px; font-weight: 700; text-transform: uppercase;
}

.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px; }
.box { border: 1px solid var(--line); border-radius: 12px; padding: 10px 12px; background: #fafafa; }
.box h3 {
    margin: 0 0 8px; font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em;
    border-bottom: 1px dashed var(--line); padding-bottom: 4px;
}
.box div { display: flex; justify-content: space-between; margin-bottom: 2px; font-size: 10.5px; line-height: 1.2; gap: 10px; }
.box div b { color: var(--muted); font-weight: 500; }
.box div span { color: var(--brand); font-weight: 600; text-align: right; }

.table-box { margin-top: 14px; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; background: #fafafa; }
table { width: 100%; border-collapse: collapse; }
th { background: rgba(0, 0, 0, 0.03); color: var(--muted); font-weight: 600; font-size: 10px; text-transform: uppercase; padding: 8px 10px; }
td { padding: 7px 10px; border-top: 1px solid var(--line); font-size: 10.5px; line-height: 1.15; }

.terms-section { margin-top: 14px; border-top: 1px dashed var(--line); padding-top: 10px; }
.terms-section h3 { margin: 0 0 8px; font-size: 10.5px; text-transform: uppercase; color: #374151; }
.terms-list {
    margin: 0; padding-left: 18px; color: #4b5563; font-size: 9.5px; line-height: 1.15; list-style-type: decimal;
}
.terms-list li { margin-bottom: 2px; padding-left: 2px; }

.footer { position: absolute; bottom: 18px; left: 22px; right: 22px; display: flex; justify-content: space-between; align-items: end; }
.footer-note { font-size: 9.5px; color: var(--muted); max-width: 55%; }
.sign { text-align: right; }
.sign-label { margin-bottom: 30px; font-size: 10px; color: var(--brand); font-weight: 500; }
.sign-name { font-size: 12px; font-weight: 700; color: var(--brand); text-transform: uppercase; }

.loading-screen { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(255,255,255,0.9); display:flex; align-items:center; justify-content:center; z-index:9999; }

@media print {
    body { background: white; padding: 0; }
    .controls { display: none; }
    .invoice-wrapper { box-shadow: none; border: none; border-radius: 0; width: 100%; max-width: 100%; }
}
@media (max-width: 768px) {
    body { padding: 16px 10px; }
    .controls { flex-wrap: wrap; gap: 10px; }
    .controls > * { width: 100%; justify-content: center; }
    .topbar, .grid { grid-template-columns: 1fr; display: grid; }
    .topbar { gap: 14px; }
    .invoice-title { text-align: left; }
    .invoice { padding: 16px 12px 115px; min-height: auto; }
    .footer { left: 14px; right: 14px; bottom: 18px; flex-direction: column; align-items: flex-start; gap: 18px; }
    .footer-note { max-width: 100%; }
    .brand { align-items: flex-start; }
    th, td { padding: 10px 8px; }
}
</style>
</head>
<body>
<div id="loadingScreen" class="loading-screen">
    <div style="text-align:center">
        <div style="border:4px solid #f3f3f3; border-top:4px solid var(--accent); border-radius:50%; width:40px; height:40px; animation:spin 1s linear infinite; margin:0 auto 15px"></div>
        <p>Loading Dealer Invoice...</p>
    </div>
</div>

<style> @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } } </style>

<div class="controls">
    <a href="dealer_invoice_search.php" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Back to Search
    </a>
    <button class="waBtn" id="waShareBtn" onclick="shareWhatsApp()" type="button">
        <span>WhatsApp Share</span>
    </button>
    <button class="downloadBtn" onclick="downloadPDF()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        Download PDF
    </button>
</div>
<div class="invoice-wrapper">
    <div class="invoice" id="invoiceArea">
        <div class="topbar">
            <div class="brand">
                <img id="logo" alt="Logo">
                <div>
                    <h1 id="companyName">-</h1>
                    <p id="companyAddress">-</p>
                    <p>Mobile: <span id="companyMobile">-</span> | Email: <span id="companyEmail">-</span></p>
                </div>
            </div>
            <div class="invoice-title">
                <h2>BILL OF SUPPLY</h2>
                <div class="badge">Dealer Settlement</div>
            </div>
        </div>

        <div class="grid">
            <div class="box">
                <h3>Bill To</h3>
                <div><b>Dealer Name:</b> <span id="dealerName">-</span></div>
                <div><b>Mobile:</b> <span id="dealerMobile">-</span></div>
                <div><b>Devices:</b> <span id="totalDevices">0</span></div>
                <div><b>Settlement Date:</b> <span id="groupDate">-</span></div>
            </div>
            <div class="box">
                <h3>Invoice Details</h3>
                <div><b>Invoice No:</b> <span id="invoiceNo" style="color:var(--accent); font-weight:700;">-</span></div>
                <div><b>Date:</b> <span id="invoiceDate">-</span></div>
            </div>
        </div>

        <div class="grid">
            <div class="box">
                <h3>Device Description</h3>
                <div><b>Dealer Batch:</b> <span id="batchDealer">-</span></div>
                <div><b>Device Model:</b> <span id="deviceModel">-</span></div>
                <div><b>IMEI Count:</b> <span id="batchCount">-</span></div>
            </div>
            <div class="box">
                <h3>Payment & Details</h3>
                <div><b>Subtotal:</b> <span id="totalSelling">₹0.00</span></div>
                <div><b>Discount:</b> <span>₹0.00</span></div>
                <div><b>Amount Paid:</b> <span id="amountPaid">₹0.00</span></div>
                <div><b>Pending Devices:</b> <span id="pendingDevices">0</span></div>
                <div style="font-size: 15px; margin-top: 4px; border-top:1px dashed #ccc; padding-top:4px;"><b>Amount Due:</b> <span style="color:var(--accent);" id="pendingAmount">₹0.00</span></div>
                <div style="margin-top:12px; font-size:10px; opacity:0.8;">Bank: <span id="bankName">-</span> | Acc: <span id="accNo">-</span> | IFSC: <span id="ifsc">-</span></div>
            </div>
        </div>

        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>IMEI</th>
                        <th>Selling Price</th>
                    </tr>
                </thead>
                <tbody id="itemBody">
                    <tr><td colspan="3">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="terms-section">
            <h3>Terms & Conditions</h3>
            <ol id="termsList" class="terms-list"></ol>
        </div>

        <div class="footer">
            <div class="footer-note">This is a computer-generated invoice. No signature is required.</div>
            <div class="sign">
                <div class="sign-label">Authorized Signatory</div>
                <div class="sign-name" id="signCompany">-</div>
            </div>
        </div>
    </div>
</div>
<script>
const params = new URLSearchParams(window.location.search);
const uid = params.get('uid') || '';
const invoiceNo = params.get('invoice_no') || '';
const dealerName = params.get('dealer_name') || '';
const invoiceDate = params.get('invoice_date') || '';
let currentInvoice = null;
let logoReadyPromise = Promise.resolve();

function currency(v){ return new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR'}).format(v||0); }
function text(v){ return v == null || v === '' ? '-' : v; }
function cleanPhone(num){ return String(num || '').replace(/\D/g, '').replace(/^91(?=\d{10}$)/, ''); }
function resolveAssetUrl(value) {
    const raw = String(value || '').trim();
    if (!raw) return '';
    if (/^(data:|blob:|https?:)/i.test(raw)) return raw;
    if (raw.startsWith('//')) return `${window.location.protocol}${raw}`;
    const normalized = raw.replace(/\\/g, '/');
    if (/^[A-Za-z]:\//.test(normalized)) return normalized.split('/').pop() || '';
    if (normalized.startsWith('/')) return `${window.location.origin}${normalized}`;
    return new URL(normalized, window.location.href).href;
}
function blobToDataURL(blob) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(blob);
    });
}
async function applyLogo(imgId, logoValue) {
    const img = document.getElementById(imgId);
    if (!img) return;
    const resolved = resolveAssetUrl(logoValue);
    if (!resolved) {
        img.removeAttribute('src');
        img.style.display = 'none';
        return;
    }
    img.style.display = '';
    img.crossOrigin = 'anonymous';
    img.src = resolved;
    try {
        const resp = await fetch(resolved, { cache: 'force-cache' });
        if (!resp.ok) throw new Error(`Logo fetch failed: ${resp.status}`);
        img.src = await blobToDataURL(await resp.blob());
    } catch (err) {
        console.warn('Logo fallback used', err);
    }
    await new Promise(resolve => {
        if (img.complete) return resolve();
        img.onload = () => resolve();
        img.onerror = () => resolve();
    });
}
async function downloadPDF(){
    const element = document.getElementById("invoiceArea");
    const invNo = document.getElementById("invoiceNo").innerText;
    await Promise.all([logoReadyPromise, document.fonts ? document.fonts.ready : Promise.resolve()]);
    await html2pdf().set({
        margin:0, filename:'Dealer_' + (invNo || 'Invoice') + '.pdf',
        image:{ type:'jpeg', quality:0.98 }, html2canvas:{ scale:2, useCORS:true, allowTaint:false }, jsPDF:{ unit:'mm', format:'a4', orientation:'portrait' }
    }).from(element).save();
}
function shareWhatsApp(){
    if(!currentInvoice) return alert('Invoice not loaded yet');
    const phone = cleanPhone(currentInvoice.dealer_mobile);
    if(!phone) return alert('Customer table-la mobile number கிடைக்கலை');
    const viewUrl = `${window.location.origin}${window.location.pathname}?uid=${encodeURIComponent(currentInvoice.uid || uid)}&view=public`;
    const msg = `Dear ${currentInvoice.customer_name || currentInvoice.dealer_name},\n\nYour device invoice is ready.\n\nDealer: ${currentInvoice.dealer_name}\nInvoice: ${currentInvoice.invoice_no}\nAmount: Rs. ${new Intl.NumberFormat('en-IN').format(currentInvoice.total_selling_price || 0)}\n\nView Bill: ${viewUrl}\n\n-- SK ENTERPRISES`;
    window.open(`https://wa.me/91${phone}?text=${encodeURIComponent(msg)}`, '_blank');
}

fetch(`api_dealer_invoice.php?action=invoice-data&uid=${encodeURIComponent(uid)}&invoice_no=${encodeURIComponent(invoiceNo)}&dealer_name=${encodeURIComponent(dealerName)}&invoice_date=${encodeURIComponent(invoiceDate)}`)
    .then(r => r.json())
    .then(res => {
        document.getElementById('loadingScreen').style.display = 'none';
        
        // Hide back link for public/dealer view
        if (params.get('view') === 'public') {
            const backLink = document.querySelector('.back-link');
            if (backLink) backLink.style.display = 'none';
        }

        if(!res.success) return alert(res.message || 'Failed to load dealer invoice');
        render(res.data, res.items || [], res.settings || {}, res.txn_column || '');
        if(params.get('download') === '1') setTimeout(downloadPDF, 1200);
    })
    .catch(() => {
        document.getElementById('loadingScreen').style.display = 'none';
        alert('Error fetching dealer invoice');
    });

function render(d, items, s, txnColumn) {
    currentInvoice = d;
    logoReadyPromise = applyLogo("logo", s.logo);
    document.getElementById("companyName").innerText = s.company || "SK ENTERPRISES";
    document.getElementById("companyAddress").innerText = s.address || "";
    document.getElementById("companyMobile").innerText = s.mobile || "-";
    document.getElementById("companyEmail").innerText = s.email_id || "-";
    document.getElementById("signCompany").innerText = s.company || "SK ENTERPRISES";

    document.getElementById("dealerName").innerText = text(d.dealer_name);
    document.getElementById("dealerMobile").innerText = text(d.dealer_mobile);
    document.getElementById("totalDevices").innerText = d.total_devices || items.length || 0;
    document.getElementById("groupDate").innerText = text(d.invoice_date);
    document.getElementById("invoiceNo").innerText = text(d.invoice_no);
    document.getElementById("invoiceDate").innerText = text(d.invoice_date);
    document.getElementById("totalSelling").innerText = currency(d.total_selling_price);
    document.getElementById("amountPaid").innerText = currency(d.paid_amount || 0);
    document.getElementById("pendingDevices").innerText = d.pending_devices || 0;
    document.getElementById("pendingAmount").innerText = currency(d.pending_amount || 0);
    document.getElementById("bankName").innerText = s.bank_name || "-";
    document.getElementById("accNo").innerText = s.account_number || "-";
    document.getElementById("ifsc").innerText = s.ifsc_code || "-";

    document.getElementById("batchDealer").innerText = text(d.dealer_name);
    document.getElementById("batchCount").innerText = items.length || 0;
    const uniqueModels = [...new Set(items.map(item => text(item.device_model)).filter(model => model !== '-'))];
    document.getElementById("deviceModel").innerText = uniqueModels.length ? uniqueModels.join(', ') : '-';

    const body = document.getElementById("itemBody");
    if(!items.length) {
        body.innerHTML = `<tr><td colspan="3">No invoice items found.</td></tr>`;
        return;
    }

    body.innerHTML = items.map((item, idx) => `
        <tr>
            <td>${idx + 1}</td>
            <td>${text(item.imei)}</td>
            <td>${currency(item.selling_price)}</td>
        </tr>
    `).join('');

    const list = document.getElementById("termsList");
    for(let i=1; i<=10; i++) {
        if(s['terms_'+i]) {
            let li = document.createElement("li");
            li.innerText = s['terms_'+i];
            list.appendChild(li);
        }
    }
}
</script>
</body>
</html>
