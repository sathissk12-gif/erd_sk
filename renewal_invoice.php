<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Subscription Invoice Generator</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<!-- 🔥 Security Framework -->
<script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
<script src="firebase_config.js"></script>
<script>protectPage({ allowInvoiceAccess: true });</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>
:root {
    --bg: #eef2f7;
    --text: #1f2937;
    --muted: #6b7280;
    --line: #e5e7eb;
    --brand: #111827;
    --accent: #2563eb;
}
* { box-sizing: border-box; }
body {
    margin: 0; background: var(--bg); color: var(--text); font-family: 'Outfit', sans-serif; padding: 18px;
}
.toolbar {
    max-width: 920px; margin: 0 auto 16px; display: flex; justify-content: flex-end; gap: 12px;
}
.toolbar button {
    border: none; border-radius: 12px; padding: 12px 16px; cursor: pointer; font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 600;
}
.btn-primary { background: var(--accent); color: white; }
.btn-dark { background: #111827; color: white; }
.btn-copy { background: #f3f4f6; color: #111827; border: 1px solid #d1d5db; }
.page {
    max-width: 920px; margin: 0 auto; background: white; border-radius: 18px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); padding: 30px 34px;
}
.loading, .error-box { max-width: 920px; margin: 0 auto; background: white; padding: 24px; border-radius: 18px; text-align: center; }
.error-box { color: #b91c1c; }
.topbar { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid var(--line); padding-bottom: 18px; }
.brand { display: flex; gap: 16px; }
.brand img { width: 84px; height: 84px; object-fit: contain; border-radius: 14px; border: 1px solid var(--line); padding: 8px; }
.brand h1 { margin: 0; color: var(--brand); font-size: 28px; }
.brand p { margin: 4px 0 0; color: #4b5563; font-size: 13px; line-height: 1.5; white-space: pre-line; }
.invoice-title { text-align: right; }
.invoice-title h2 { margin: 0; font-size: 30px; letter-spacing: 0.04em; }
.invoice-title .badge { display: inline-block; margin-top: 8px; background: #eff6ff; color: #1d4ed8; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 22px; }
.box { border: 1px solid var(--line); border-radius: 14px; padding: 16px 18px; background: #fafafa; }
.box h3 { margin: 0 0 10px; font-size: 13px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; }
.box p { margin: 4px 0; font-size: 14px; line-height: 1.5; }
table { width: 100%; border-collapse: collapse; margin-top: 24px; }
th, td { border: 1px solid var(--line); padding: 12px; font-size: 14px; vertical-align: top; }
th { background: #f8fafc; color: #374151; text-align: left; }
.summary { margin-top: 18px; display: flex; justify-content: flex-end; }
.summary table { width: 320px; margin-top: 0; }
.amount-words { margin-top: 16px; padding: 14px 16px; background: #f9fafb; border: 1px solid var(--line); border-radius: 12px; font-size: 14px; }
.bank { margin-top: 22px; display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px; }
.terms { margin-top: 22px; border-top: 1px dashed #d1d5db; padding-top: 18px; }
.terms ol { margin: 0; padding-left: 20px; color: #4b5563; font-size: 13px; line-height: 1.7; }
.footer { margin-top: 24px; display: flex; justify-content: space-between; align-items: end; gap: 20px; }
.sign { text-align: right; }
@media print { body { background: white; padding: 0; } .toolbar { display: none; } .page { box-shadow: none; border-radius: 0; max-width: none; padding: 0; } }
@media (max-width: 700px) {
    .topbar, .footer { flex-direction: column; }
    .grid, .bank { grid-template-columns: 1fr; }
    .invoice-title { text-align: left; }
}
</style>
</head>
<body>
<div class="toolbar">
    <div style="flex-grow: 1; position: relative;">
        <input type="text" id="masterSearch" placeholder="🔍 Search Vehicle or Customer..." style="width:100%; padding:12px 15px; border-radius:12px; border:1px solid #d1d5db; font-family:'Outfit'; outline:none;">
        <div id="searchResults" style="position:absolute; top:100%; left:0; right:0; background:white; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.1); z-index:1000; overflow:hidden; display:none;"></div>
    </div>
    <button class="btn-primary" onclick="downloadPDF()">Download PDF</button>
    <button class="btn-dark" style="background:#25D366; color:white;" id="waShareBtn">WhatsApp Share</button>
    <button class="btn-copy" onclick="window.location.href='renewal_entry.php'">Back</button>
</div>

<div class="loading" id="loadingBox">Search for a vehicle or customer to load invoice...</div>
<div class="error-box" id="errorBox" style="display:none;"></div>

<div class="page" id="invoicePage" style="display:none;">
    <div class="topbar">
        <div class="brand">
            <img id="companyLogo" alt="Logo">
            <div>
                <h1 id="companyName">-</h1>
                <p id="companyAddress"></p>
                <p id="companyMeta"></p>
            </div>
        </div>
        <div class="invoice-title">
            <h2>BILL OF SUPPLY</h2>
            <div class="badge">Renewal Invoice</div>
        </div>
    </div>

    <div class="grid">
        <div class="box">
            <h3>Bill To</h3>
            <p><strong id="customerName">-</strong></p>
            <p>Vehicle Number: <span id="vehicleNumber">-</span></p>
            <p>Software Type: <span id="softwareType">-</span></p>
        </div>
        <div class="box">
            <h3>Invoice Details</h3>
            <p><strong>Invoice No:</strong> <span id="invoiceNo">-</span></p>
            <p><strong>Date:</strong> <span id="invoiceDate">-</span></p>
            <p><strong>Nature of Supply:</strong> Renewal Subscription</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:60px">S.No</th>
                <th>Description</th>
                <th style="width:90px" style="text-align:center">Qty</th>
                <th style="width:120px" style="text-align:right">Rate</th>
                <th style="width:140px" style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><strong id="lineDescription">-</strong><br><small style="color:#666" id="lineSubtext">-</small></td>
                <td style="text-align:center">1</td>
                <td style="text-align:right" id="lineRate">0.00</td>
                <td style="text-align:right" id="lineAmount">0.00</td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td><strong>Total</strong></td>
                <td style="text-align:right"><strong id="totalAmount">0.00</strong></td>
            </tr>
            <tr>
                <td>Received Amount</td>
                <td style="text-align:right" id="receivedAmount">0.00</td>
            </tr>
        </table>
    </div>

    <div class="amount-words"><strong>Amount in Words:</strong> <span id="amountWords">-</span> only.</div>

    <div class="bank">
        <div class="box">
            <h3>Bank / UPI Details</h3>
            <p><strong>UPI ID:</strong> <span id="upiId">-</span></p>
            <p><strong>Bank:</strong> <span id="bankName">-</span></p>
            <p><strong>Account Name:</strong> <span id="accountName">-</span></p>
            <p><strong>Account Number:</strong> <span id="accountNumber">-</span></p>
            <p><strong>IFSC:</strong> <span id="ifscCode">-</span></p>
        </div>
        <div class="box">
            <h3>Notes</h3>
            <p>This is a computer generated Bill of Supply for renewal services.</p>
            <p>For support, contact <span id="noteMobile">-</span> or <span id="noteEmail">-</span>.</p>
        </div>
    </div>

    <div class="terms">
        <h3>Terms & Conditions</h3>
        <ol id="termsList"></ol>
    </div>

    <div class="footer">
        <div><strong id="footerCompany">-</strong></div>
        <div class="sign">
            <div style="margin-bottom:34px;">Authorized Signatory</div>
            <strong id="signCompany">-</strong>
        </div>
    </div>
</div>

<script>
const params = new URLSearchParams(window.location.search);
let uid = params.get('uid');
let currentInvoiceData = null;
const isPublicInvoiceView = !!(params.get("uid") || params.get("invoice_no") || params.get("id"));
let logoReadyPromise = Promise.resolve();

// Search Logic
const masterSearch = document.getElementById("masterSearch");
const searchResults = document.getElementById("searchResults");

if (isPublicInvoiceView) {
    masterSearch.parentElement.style.display = "none";
}

masterSearch.addEventListener("input", (e) => {
    const q = e.target.value;
    if(q.length < 2) { searchResults.style.display="none"; return; }
    
    fetch(`api_renewal_invoice.php?action=search&query=${encodeURIComponent(q)}`)
    .then(r=>r.json()).then(data => {
        if(!data.length) { searchResults.style.display="none"; return; }
        
        let html = "";
        data.forEach(inv => {
            html += `<div style="padding:12px; border-bottom:1px solid #eee; cursor:pointer;" onclick="loadSpecificInvoice('${inv.invoice_num}')">
                <div style="font-weight:700;">${inv.vehicle_num} - ${inv.invoice_num}</div>
                <div style="font-size:12px; color:#666;">${inv.customer_name} | ${inv.date}</div>
            </div>`;
        });
        searchResults.innerHTML = html;
        searchResults.style.display = "block";
    });
});

document.addEventListener("click", (e) => { if(e.target !== masterSearch) searchResults.style.display="none"; });

function loadSpecificInvoice(num) {
    searchResults.style.display = "none";
    document.getElementById("loadingBox").style.display = "block";
    document.getElementById("invoicePage").style.display = "none";
    loadInvoice(null, num);
}

// WhatsApp Share
document.getElementById("waShareBtn").addEventListener("click", () => {
    if(!currentInvoiceData) return alert("Please load an invoice first");
    
    const d = currentInvoiceData;
    // Use UID for sharing if available, fallback to invoice_no
    const shareKey = d.uid || d.invoiceNo;
    const itemUrl = window.location.href.split('?')[0] + (d.uid ? "?uid=" : "?invoice_no=") + shareKey;
    const msg = `Dear ${d.customerName},\n\nYour Renewal Bill for Vehicle ${d.vehicleNumber} is ready.\n\nInvoice: ${d.invoiceNo}\nAmount: ₹${d.receivedAmount}\n\nView Bill: ${itemUrl}\n\n-- SK ENTERPRISES`;
    
    const waUrl = `https://wa.me/91${d.customerMobile}?text=${encodeURIComponent(msg)}`;
    window.open(waUrl, "_blank");
});

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
        img.removeAttribute("src");
        img.style.display = "none";
        return;
    }
    img.style.display = "";
    img.crossOrigin = "anonymous";
    img.src = resolved;
    try {
        const resp = await fetch(resolved, { cache: "force-cache" });
        if (!resp.ok) throw new Error(`Logo fetch failed: ${resp.status}`);
        img.src = await blobToDataURL(await resp.blob());
    } catch (err) {
        console.warn("Logo fallback used", err);
    }
    await new Promise(resolve => {
        if (img.complete) return resolve();
        img.onload = () => resolve();
        img.onerror = () => resolve();
    });
}

async function downloadPDF() {
    if(!currentInvoiceData) return;
    const element = document.getElementById("invoicePage");
    await Promise.all([logoReadyPromise, document.fonts ? document.fonts.ready : Promise.resolve()]);
    const opt = {
        margin: 0.5,
        filename: `Invoice_${currentInvoiceData.invoiceNo}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, allowTaint: false },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };
    await html2pdf().set(opt).from(element).save();
}

function loadInvoice(vehicleNum, invoiceNum) {
    const v = vehicleNum || params.get("vehicle") || "";
    const inv = invoiceNum || params.get("invoice_no") || "";
    const rid = params.get("id") || "";
    
    if(!v && !inv && !uid && !rid) return;

    fetch(`api_renewal_invoice.php?action=invoice-data&uid=${uid || ''}&vehicle=${v}&invoice_no=${inv}&id=${rid}`)
    .then(r=>r.json()).then(res => {
        console.log("Invoice API Response:", res);
        if(!res.success) {
            document.getElementById("loadingBox").style.display = "none";
            const err = document.getElementById("errorBox");
            err.style.display = "block";
            err.innerText = res.message || "Failed to load invoice";
            return;
        }
        currentInvoiceData = res.data;
        render(res.data);
    }).catch(err => {
        console.error("Fetch Error:", err);
        document.getElementById("loadingBox").innerText = "Network Error: Could not connect to API";
    });
}

loadInvoice();

function render(data) {
    const s = data.settings || {};
    const amt = Number(data.receivedAmount).toFixed(2);
    
    document.getElementById("loadingBox").style.display = "none";
    document.getElementById("invoicePage").style.display = "block";
    
    logoReadyPromise = applyLogo("companyLogo", s.logo);
    document.getElementById("companyName").innerText = s.company || "-";
    document.getElementById("companyAddress").innerText = s.address || "";
    document.getElementById("companyMeta").innerText = `Mobile: ${s.mobile} | Email: ${s.email_id}`;
    document.getElementById("customerName").innerText = data.customerName;
    document.getElementById("vehicleNumber").innerText = data.vehicleNumber;
    document.getElementById("softwareType").innerText = data.softwareType;
    document.getElementById("invoiceNo").innerText = data.invoiceNo;
    document.getElementById("invoiceDate").innerText = data.invoiceDate;
    document.getElementById("lineDescription").innerText = data.softwareType + " Renewal";
    document.getElementById("lineSubtext").innerText = "Vehicle No: " + data.vehicleNumber;
    document.getElementById("lineRate").innerText = Number(data.amount).toFixed(2);
    document.getElementById("lineAmount").innerText = amt;
    document.getElementById("totalAmount").innerText = amt;
    document.getElementById("receivedAmount").innerText = amt;
    document.getElementById("amountWords").innerText = data.amountWords;
    document.getElementById("upiId").innerText = s.upi_id;
    document.getElementById("bankName").innerText = s.bank_name;
    document.getElementById("accountName").innerText = s.account_name;
    document.getElementById("accountNumber").innerText = s.account_number;
    document.getElementById("ifscCode").innerText = s.ifsc_code;
    document.getElementById("noteMobile").innerText = s.mobile;
    document.getElementById("noteEmail").innerText = s.email_id;
    document.getElementById("footerCompany").innerText = "For " + s.company;
    document.getElementById("signCompany").innerText = s.company;

    const list = document.getElementById("termsList");
    list.innerHTML = "";
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
