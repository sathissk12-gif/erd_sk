<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Enterprise Quotation Generator</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --bg: #f1f5f9; --text: #1f2937; --muted: #6b7280; --line: #e5e7eb; --brand: #0f172a; --accent: #06b6d4; --white: #ffffff;
}
body {
    margin: 0; background: var(--bg); color: var(--text); font-family: 'Outfit', sans-serif;
    padding: 30px 15px; display: flex; flex-direction: column; align-items: center; min-height: 100vh;
}
.controls { width: 100%; max-width: 210mm; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.back-link { text-decoration: none; color: var(--muted); font-size: 14px; font-weight: 500; }
.downloadBtn {
    padding: 12px 28px; background: var(--accent); color: white; border: none; border-radius: 12px;
    cursor: pointer; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 10px;
}
.invoice-wrapper { width: 100%; max-width: 210mm; background: var(--white); border-radius: 16px; border: 1px solid var(--line); overflow: hidden; }
.invoice { padding: 22px 28px; min-height: 297mm; position: relative; }
.topbar { display: flex; justify-content: space-between; gap: 20px; border-bottom: 2px solid var(--line); padding-bottom: 14px; }
.brand { display: flex; gap: 14px; align-items: center; }
.brand img { width: 60px; height: 60px; object-fit: contain; border-radius: 10px; border: 1px solid var(--line); padding: 4px; }
.brand h1 { margin: 0; color: var(--brand); font-size: 22px; }
.brand p { margin: 4px 0 0; color: #4b5563; font-size: 11px; }
.invoice-title { text-align: right; }
.invoice-title h2 { margin: 0; font-size: 24px; letter-spacing: 0.04em; color: var(--brand); }
.invoice-title .badge { display: inline-block; margin-top: 6px; background: #ecfeff; color: #0891b2; padding: 5px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
.box { border: 1px solid var(--line); border-radius: 12px; padding: 12px 14px; background: #fafafa; }
.box h3 { margin: 0 0 10px; font-size: 11px; color: var(--muted); text-transform: uppercase; border-bottom: 1px dashed var(--line); padding-bottom: 6px; }
.box div { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 11.5px; }
.box div b { color: var(--muted); font-weight: 500; }
.box div span { color: var(--brand); font-weight: 600; text-align: right; }
.terms-section { margin-top: 20px; border-top: 1px dashed var(--line); padding-top: 16px; }
.terms-list { margin: 0; padding-left: 20px; color: #4b5563; font-size: 10.5px; line-height: 1.35; list-style-type: decimal; }
.footer { position: absolute; bottom: 25px; left: 28px; right: 28px; display: flex; justify-content: space-between; align-items: end; }
.footer-note { font-size: 10.5px; color: var(--muted); }
.sign { text-align: right; }
.sign-label { margin-bottom: 45px; font-size: 11.5px; color: var(--brand); }
.sign-name { font-size: 13px; font-weight: 700; color: var(--brand); text-transform: uppercase; }
</style>
</head>
<body>

<div class="controls">
    <a href="index.html" class="back-link">← Back to Console</a>
    <button class="downloadBtn" onclick="downloadPDF()">Download PDF</button>
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
                <h2>PRICE QUOTATION</h2>
                <div class="badge">Valid for 7 Days</div>
            </div>
        </div>

        <div class="grid">
            <div class="box">
                <h3>Quote To</h3>
                <div><b>Customer:</b> <span id="customerName">-</span></div>
                <div><b>Mobile:</b> <span id="mobileNumber">-</span></div>
                <div><b>Location:</b> <span id="location">-</span></div>
                <div><b>Sales Person:</b> <span id="salesPerson">-</span></div>
            </div>
            <div class="box">
                <h3>Reference Details</h3>
                <div><b>Quotation No:</b> <span id="quotationNo" style="color:var(--accent); font-weight:700;">-</span></div>
                <div><b>Date:</b> <span id="quotationDate">-</span></div>
                <div><b>Valid Until:</b> <span id="validUntil">-</span></div>
            </div>
        </div>

        <div class="grid">
            <div class="box">
                <h3>Requirement Description</h3>
                <div><b>Device Model:</b> <span id="model">-</span></div>
                <div><b>Software/Subscription:</b> <span id="software">-</span></div>
                <div><b>Duration:</b> <span id="duration">-</span></div>
                <div><b>Relay Option:</b> <span id="relayStatus">-</span></div>
                <div><b>SIM Support:</b> <span id="simType">-</span></div>
            </div>
            <div class="box">
                <h3>Estimated Pricing</h3>
                <div><b>Subtotal:</b> <span id="totalAmount">₹0.00</span></div>
                <div><b>Discount:</b> <span id="discountAmount">₹0.00</span></div>
                <div style="font-size: 15px; margin-top: 10px; border-top:1px dashed #ccc; padding-top:8px;"><b>Estimated Total:</b> <span style="color:var(--accent);" id="finalAmount">₹0.00</span></div>
                <div style="margin-top:12px; font-size:10px; opacity:0.8;">Bank: <span id="bankName">-</span> | Acc: <span id="accNo">-</span> | IFSC: <span id="ifsc">-</span></div>
            </div>
        </div>

        <div class="terms-section">
            <h3>Terms & Conditions</h3>
            <ol id="termsList" class="terms-list"></ol>
        </div>

        <div class="footer">
            <div class="footer-note">This is a quotation for information purposes only.</div>
            <div class="sign">
                <div class="sign-label">For SK ENTERPRISES</div>
                <div class="sign-name" id="signCompany">-</div>
            </div>
        </div>
    </div>
</div>

<script>
const params = new URLSearchParams(window.location.search);
let uid = params.get('uid');

function currency(v){ return new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR'}).format(v||0) }

fetch(`api_quotation.php?action=get&uid=${uid}`)
.then(r => r.json())
.then(res => {
    if(!res.success) return alert("Quotation not found");
    const d = res.data;
    const s = res.settings;

    document.getElementById("logo").src = s.logo;
    document.getElementById("companyName").innerText = s.company;
    document.getElementById("companyAddress").innerText = s.address;
    document.getElementById("companyMobile").innerText = s.mobile;
    document.getElementById("companyEmail").innerText = s.email_id;

    document.getElementById("customerName").innerText = d.customer_name;
    document.getElementById("mobileNumber").innerText = d.mobile_number;
    document.getElementById("location").innerText = d.location;
    document.getElementById("salesPerson").innerText = d.sales_person;

    document.getElementById("quotationNo").innerText = d.quotation_no;
    document.getElementById("quotationDate").innerText = d.quotation_date;
    document.getElementById("validUntil").innerText = d.valid_until;

    document.getElementById("model").innerText = d.device_model;
    document.getElementById("software").innerText = d.software_name;
    document.getElementById("duration").innerText = d.software_duration;
    document.getElementById("simType").innerText = d.sim_type;
    document.getElementById("relayStatus").innerText = d.relay;

    document.getElementById("totalAmount").innerText = currency(d.total_amount);
    document.getElementById("discountAmount").innerText = currency(d.discount_amount);
    document.getElementById("finalAmount").innerText = currency(d.total_amount - d.discount_amount);

    document.getElementById("bankName").innerText = s.bank_name;
    document.getElementById("accNo").innerText = s.account_number;
    document.getElementById("ifsc").innerText = s.ifsc_code;
    document.getElementById("signCompany").innerText = s.company;

    const list = document.getElementById("termsList");
    for(let i=1; i<=10; i++) { if(s['terms_'+i]) { let li = document.createElement("li"); li.innerText = s['terms_'+i]; list.appendChild(li); } }
});

async function downloadPDF(){
    const element = document.getElementById("invoiceArea");
    const qno = document.getElementById("quotationNo").innerText;
    html2pdf().set({ margin: 0, filename: 'Quotation_'+qno+'.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } }).from(element).save();
}
</script>
</body>
</html>
