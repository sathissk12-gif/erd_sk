<?php
// c:\Users\sathi\.gemini\antigravity\scratch\sales_invoice.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Enterprise Invoice Generator</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<!-- No Auth Required for Customer View -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
/* Reset and Base */
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

/* Controls */
.controls {
    width: 100%; max-width: 210mm; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding: 0 5px;
}
.controls .back-link {
    text-decoration: none; color: var(--muted); font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 6px;
}
.controls .back-link:hover { color: var(--accent); }
.downloadBtn {
    padding: 12px 28px; background: var(--accent); color: white; border: none; border-radius: 12px;
    cursor: pointer; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 10px;
    box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2); font-family: 'Outfit', sans-serif;
}
.downloadBtn:hover { background: #1d4ed8; transform: translateY(-2px); }

/* Invoice Container */
.invoice-wrapper {
    width: 100%; max-width: 210mm; background: var(--white); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
    border-radius: 16px; overflow: hidden; position: relative; border: 1px solid var(--line);
}

.invoice {
    padding: 22px 28px; background: var(--white); position: relative; min-height: 297mm; box-sizing: border-box;
}

/* Topbar matching Renewal Template */
.topbar {
    display: flex; justify-content: space-between; gap: 20px; border-bottom: 2px solid var(--line); padding-bottom: 14px;
}
.brand { display: flex; gap: 14px; align-items: center; }
.brand img {
    width: 60px; height: 60px; object-fit: contain; border-radius: 10px; border: 1px solid var(--line); padding: 4px;
}
.brand h1 { margin: 0; color: var(--brand); font-size: 22px; }
.brand p { margin: 4px 0 0; color: #4b5563; font-size: 11px; line-height: 1.4; }

.invoice-title { text-align: right; }
.invoice-title h2 { margin: 0; font-size: 24px; letter-spacing: 0.04em; color: var(--brand); }
.invoice-title .badge {
    display: inline-block; margin-top: 6px; background: #eff6ff; color: #1d4ed8; padding: 5px 8px;
    border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase;
}

/* Grid & Boxes */
.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
.box { border: 1px solid var(--line); border-radius: 12px; padding: 12px 14px; background: #fafafa; }
.box h3 {
    margin: 0 0 10px; font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em;
    border-bottom: 1px dashed var(--line); padding-bottom: 6px;
}
.box div { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 11.5px; line-height: 1.35; }
.box div b { color: var(--muted); font-weight: 500; }
.box div span { color: var(--brand); font-weight: 600; text-align: right; }

/* Terms & Footer */
.terms-section { margin-top: 20px; border-top: 1px dashed var(--line); padding-top: 16px; }
.terms-section h3 { margin: 0 0 12px; font-size: 11.5px; text-transform: uppercase; color: #374151; }
.terms-list {
    margin: 0; padding-left: 20px; color: #4b5563; font-size: 10.5px; line-height: 1.35; list-style-type: decimal;
}
.terms-list li { margin-bottom: 4px; padding-left: 2px; }

.footer { position: absolute; bottom: 25px; left: 28px; right: 28px; display: flex; justify-content: space-between; align-items: end; }
.footer-note { font-size: 10.5px; color: var(--muted); }
.sign { text-align: right; }
.sign-label { margin-bottom: 45px; font-size: 11.5px; color: var(--brand); font-weight: 500; }
.sign-name { font-size: 13px; font-weight: 700; color: var(--brand); text-transform: uppercase; }

.loading-screen { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(255,255,255,0.9); display:flex; align-items:center; justify-content:center; z-index:9999; }

@media print {
    body { background: white; padding: 0; }
    .controls { display: none; }
    .invoice-wrapper { box-shadow: none; border: none; border-radius: 0; width: 100%; max-width: 100%; }
}
</style>
</head>

<body>

<div id="loadingScreen" class="loading-screen">
    <div style="text-align:center">
        <div style="border:4px solid #f3f3f3; border-top:4px solid var(--accent); border-radius:50%; width:40px; height:40px; animation:spin 1s linear infinite; margin:0 auto 15px"></div>
        <p>Loading Invoice...</p>
    </div>
</div>

<style> @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } } </style>

<div class="controls">
    <a href="sales_invoice_search.php" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Back to Search
    </a>
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
                <div class="badge">Sales / Installation</div>
            </div>
        </div>

        <div class="grid">
            <div class="box">
                <h3>Bill To</h3>
                <div><b>Customer:</b> <span id="customerName">-</span></div>
                <div><b>Vehicle No:</b> <span id="vehicleNo">-</span></div>
                <div><b>Location:</b> <span id="location">-</span></div>
                <div><b>Sales Person:</b> <span id="salesPerson">-</span></div>
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
                <div><b>IMEI Number:</b> <span id="imei">-</span></div>
                <div><b>Hardware Model:</b> <span id="model">-</span></div>
                <div><b>Software/Sim:</b> <span id="software">-</span></div>
                <div><b>Active SIM No:</b> <span id="simNumber">-</span></div>
                <div><b>Relay Status:</b> <span id="relayStatus">-</span></div>
            </div>
            <div class="box">
                <h3>Payment & Details</h3>
                <div><b>Subtotal:</b> <span id="totalAmount">₹0.00</span></div>
                <div><b>Discount:</b> <span id="discountAmount">₹0.00</span></div>
                <div><b>Amount Paid:</b> <span id="paidAmount">₹0.00</span></div>
                <div style="font-size: 15px; margin-top: 4px; border-top:1px dashed #ccc; padding-top:4px;"><b>Amount Due:</b> <span style="color:var(--accent);" id="pendingAmount">₹0.00</span></div>
                
                <div style="margin-top:12px; font-size:10px; opacity:0.8;">Bank: <span id="bankName">-</span> | Acc: <span id="accNo">-</span> | IFSC: <span id="ifsc">-</span></div>
            </div>
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
let uid = params.get('uid');
let invoiceNo = params.get('invoice_no');
let vehicle = params.get('vehicle');
let logoReadyPromise = Promise.resolve();

function currency(v){
    return new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR'}).format(v||0)
}

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
    const opt = {
        margin: 0,
        filename: 'Invoice_' + (invNo || 'Sales') + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, allowTaint: false },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    await html2pdf().set(opt).from(element).save();
}

// Fetch Logic (Prioritize UID for privacy)
fetch(`api_sales.php?action=invoice-data&uid=${uid || ''}&invoice_no=${invoiceNo || ''}&vehicle=${vehicle || ''}`)
.then(r => r.json())
.then(res => {
    document.getElementById("loadingScreen").style.display = "none";
    if(!res.success) {
        alert(res.message || "Failed to load invoice data");
        return;
    }
    render(res.data, res.settings);
    
    if(params.get('download') === '1') {
        setTimeout(downloadPDF, 1500);
    }
})
.catch(e => {
    document.getElementById("loadingScreen").style.display = "none";
    alert("Error fetching data");
});

function render(d, s) {
    logoReadyPromise = applyLogo("logo", s.logo);
    document.getElementById("companyName").innerText = s.company || "SK ENTERPRISES";
    document.getElementById("companyAddress").innerText = s.address || "";
    document.getElementById("companyMobile").innerText = s.mobile || "-";
    document.getElementById("companyEmail").innerText = s.email_id || "-";
    
    document.getElementById("customerName").innerText = d.customer_name;
    document.getElementById("vehicleNo").innerText = d.vehicle_no;
    document.getElementById("location").innerText = d.location;
    document.getElementById("salesPerson").innerText = d.sales_person;
    
    document.getElementById("invoiceNo").innerText = d.invoice_no;
    document.getElementById("invoiceDate").innerText = d.invoice_date;
    
    document.getElementById("imei").innerText = d.imei;
    document.getElementById("model").innerText = d.device_model || "-";
    document.getElementById("software").innerText = d.software;
    document.getElementById("simNumber").innerText = d.sim_number;
    document.getElementById("relayStatus").innerText = d.relay;
    
    document.getElementById("totalAmount").innerText = currency(d.total_amount);
    document.getElementById("discountAmount").innerText = currency(d.discount_amount);
    document.getElementById("paidAmount").innerText = currency(d.paid_amount);
    document.getElementById("pendingAmount").innerText = currency(d.pending_amount);
    
    document.getElementById("bankName").innerText = s.bank_name;
    document.getElementById("accNo").innerText = s.account_number;
    document.getElementById("ifsc").innerText = s.ifsc_code;
    
    document.getElementById("signCompany").innerText = s.company;

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
