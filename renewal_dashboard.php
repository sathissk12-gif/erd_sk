<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SK Renewal Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
<script src="firebase_config.js"></script>
<script>protectPage();</script>
<style>
:root {
    --primary: #6366f1;
    --primary-light: #818cf8;
    --danger: #ef4444;
    --success: #10b981;
    --bg-color: #0f172a;
    --card-bg: rgba(30, 41, 59, 0.45);
    --card-border: rgba(255, 255, 255, 0.08);
    --text-main: #f8fafc;
    --text-muted: #94a3b8;
    --grad-1: #0f172a;
    --grad-2: #1e1b4b;
    --grad-3: #020617;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Outfit', sans-serif;
    color: var(--text-main);
    min-height: 100vh;
    background: linear-gradient(-45deg, var(--grad-1), var(--grad-2), var(--grad-3));
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
    padding-top: env(safe-area-inset-top, 0px);
    padding-bottom: 80px;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: calc(14px + env(safe-area-inset-top, 0px)) 5% 20px;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--card-border);
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-title {
    font-size: 20px;
    font-weight: 700;
    background: linear-gradient(to right, #818cf8, #c084fc);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.header-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.header-btn,
.back-btn {
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--card-border);
    color: var(--text-main);
    padding: 10px 14px;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.header-btn:hover,
.back-btn:hover { background: rgba(99, 102, 241, 0.16); }

.container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 30px 5%;
}

.page-title {
    font-size: 32px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 8px;
}

.page-subtitle {
    text-align: center;
    color: var(--text-muted);
    font-size: 15px;
    margin-bottom: 26px;
}

.toolbar {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin: 20px auto 32px;
    max-width: 1200px;
}

.section { margin-bottom: 40px; animation: fadeIn 0.35s ease-out; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(14px); }
    to { opacity: 1; transform: translateY(0); }
}

.section h2 {
    margin-bottom: 20px;
    font-size: 18px;
    font-weight: 600;
    padding: 8px 15px;
    background: linear-gradient(90deg, rgba(99, 102, 241, 0.1) 0%, transparent 100%);
    border-left: 4px solid var(--primary);
    border-radius: 0 8px 8px 0;
    display: inline-block;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 22px;
}

.card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    backdrop-filter: blur(16px);
    padding: 20px;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.vehicle {
    font-weight: 700;
    font-size: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.vehicle-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: rgba(255,255,255,0.06);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-light);
}

.meta-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    font-size: 13px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--card-border);
}

.meta-label { color: var(--text-muted); text-transform: uppercase; font-size: 11px; letter-spacing: .06em; }
.meta-val { font-weight: 500; text-align: right; word-break: break-word; }
.expiry-val { color: var(--danger); font-weight: 700; }
.amount-val { color: var(--success); font-weight: 700; }

.action-btn {
    margin-top: 6px;
    padding: 12px;
    border-radius: 8px;
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.3);
    color: var(--success);
    font-weight: 700;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    width: 100%;
}

.action-btn:hover { background: var(--success); color: #0f172a; }
.action-btn:disabled { opacity: .5; cursor: not-allowed; }

.badge-sent {
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.4);
    color: #10b981;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 10px rgba(16, 185, 129, 0.1);
}

.badge-failed {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.4);
    color: #ef4444;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 10px rgba(239, 68, 68, 0.1);
}

.popup {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.72);
    backdrop-filter: blur(8px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2000;
    opacity: 0;
    transition: opacity .25s ease;
}

.popup.show { opacity: 1; }

.popup-box {
    background: rgba(30, 41, 59, 0.92);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 28px;
    border-radius: 8px;
    width: 95%;
    max-width: 520px;
    text-align: center;
    box-shadow: 0 25px 50px rgba(0,0,0,.45);
    transform: translateY(14px);
    transition: transform .25s ease;
}

.popup.show .popup-box { transform: translateY(0); }
.popup-box h3 { margin-bottom: 16px; color: var(--primary-light); font-size: 22px; }

.popup-subtitle {
    color: var(--text-muted);
    font-size: 14px;
    margin-bottom: 18px;
}

.dual-btn {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px;
    margin-bottom: 12px;
    padding: 12px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 8px;
}

.dual-number {
    grid-column: 1 / -1;
    text-align: left;
    color: var(--text-main);
    font-weight: 700;
}

.popup-box button {
    padding: 12px;
    border-radius: 8px;
    border: none;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.wp-btn { background: rgba(37, 211, 102, 0.14); color: #25D366; border: 1px solid rgba(37, 211, 102, 0.3) !important; }
.wp-btn:hover { background: #25D366; color: white; }
.call-btn { background: rgba(56, 189, 248, 0.14); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3) !important; }
.call-btn:hover { background: #38bdf8; color: #0f172a; }
.close-btn { background: rgba(255,255,255,0.06); color: var(--text-muted); border: 1px solid var(--card-border) !important; width: 100%; margin-top: 10px; }
.close-btn:hover { background: var(--danger); color: white; }

.loading {
    text-align: center;
    padding: 40px;
    color: var(--text-muted);
    font-size: 18px;
}

@media (max-width: 768px) {
    .header { padding: 15px; }
    .header-title { font-size: 18px; }
    .container { padding: 26px 18px; }
    .page-title { font-size: 26px; }
    .toolbar { flex-direction: column; }
    .cards-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div class="header">
    <div class="header-title">BILLING CENTER</div>
    <div class="header-actions">
        <button class="header-btn" onclick="location.reload()" title="Refresh">
            <i class="fa-solid fa-rotate"></i>
        </button>
        <button class="header-btn" onclick="firebase.auth().signOut()" title="Secure Exit">
            <i class="fa-solid fa-power-off"></i>
        </button>
    </div>
</div>

<div class="container">
    <h1 class="page-title">Renewal Reminders</h1>
    <p class="page-subtitle">Automated WhatsApp reminders to customers instantly.</p>

    <div class="toolbar">
        <a href="index.html" class="back-btn"><i class="fa-solid fa-house"></i> Dashboard</a>
        <button id="btn-auto-broadcast" class="back-btn" onclick="triggerAutoBroadcast()" style="background: rgba(37, 211, 102, 0.15); color: #25D366; border-color: rgba(37, 211, 102, 0.3) !important;"><i class="fa-solid fa-robot"></i> Run Auto Broadcast</button>
        <button class="back-btn" onclick="loadReminders()"><i class="fa-solid fa-arrows-rotate"></i> Refresh</button>
    </div>

    <div id="content">
        <div class="loading"><i class="fa-solid fa-spinner fa-spin"></i> Fetching renewal statuses...</div>
    </div>
</div>

<script>
const API = "api_renewal_automation.php?action=list_due";
let stockData = [];
let graceLimit = 5;

function escapeHtml(value){
    return String(value ?? "").replace(/[&<>"']/g, ch => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    }[ch]));
}

function loadReminders(){
    document.getElementById("content").innerHTML = `<div class="loading"><i class="fa-solid fa-spinner fa-spin"></i> Fetching renewal statuses...</div>`;
    fetch(API)
        .then(res => res.json())
        .then(res => {
            if(!res.success) throw new Error(res.message || "Unable to load reminders");
            stockData = res.data || [];
            graceLimit = res.graceTotal || 5;
            render(stockData);
        })
        .catch(() => {
            document.getElementById("content").innerHTML = `<div class="loading" style="color:#ef4444;">Failed to load reminders. Please refresh the page.</div>`;
        });
}

function getGroupTitle(type){
    if(type === "failed_broadcasts") return "⚠️ Failed Broadcasts";
    if(type === "expired") return "Grace Period (Expired)";
    if(type === "today") return "Expires Today";
    if(type && /^d\d+$/.test(type)) {
        const days = type.replace("d", "");
        return `${days} Day${days === "1" ? "" : "s"} Remaining`;
    }
    return "Upcoming Renewals";
}

function getGroupOrder(type){
    if(type === "failed_broadcasts") return -2;
    if(type === "expired") return -1;
    if(type === "today") return 0;
    if(type && /^d\d+$/.test(type)) return parseInt(type.replace("d", ""), 10);
    return 99;
}

function render(data){
    // Update Auto Broadcast button state dynamically
    const btnBroadcast = document.getElementById("btn-auto-broadcast");
    if (btnBroadcast) {
        if (!data || data.length === 0) {
            btnBroadcast.disabled = true;
            btnBroadcast.style.opacity = "0.5";
            btnBroadcast.style.cursor = "not-allowed";
            btnBroadcast.innerHTML = `<i class="fa-solid fa-ban"></i> No Renewals Due`;
        } else {
            const sendableItems = data.filter(item => item.hasMobile);
            const alreadySentCount = sendableItems.filter(item => item.sent_today).length;
            
            if (sendableItems.length > 0 && alreadySentCount === sendableItems.length) {
                btnBroadcast.disabled = true;
                btnBroadcast.style.opacity = "0.6";
                btnBroadcast.style.background = "rgba(100, 116, 139, 0.1)";
                btnBroadcast.style.color = "#94a3b8";
                btnBroadcast.style.borderColor = "rgba(148, 163, 184, 0.2)";
                btnBroadcast.style.cursor = "not-allowed";
                btnBroadcast.innerHTML = `<i class="fa-solid fa-circle-check"></i> Broadcast Completed`;
            } else {
                btnBroadcast.disabled = false;
                btnBroadcast.style.opacity = "1";
                btnBroadcast.style.background = "rgba(37, 211, 102, 0.15)";
                btnBroadcast.style.color = "#25D366";
                btnBroadcast.style.borderColor = "rgba(37, 211, 102, 0.3)";
                btnBroadcast.style.cursor = "pointer";
                btnBroadcast.innerHTML = `<i class="fa-solid fa-robot"></i> Run Auto Broadcast`;
            }
        }
    }

    if(!data || data.length === 0){
        document.getElementById("content").innerHTML = `<div class="loading">No upcoming renewals found.</div>`;
        return;
    }

    const groups = {};
    
    // Separate failed broadcast items into their own group
    const failedItems = data.filter(item => item.send_status === 'failed');
    if (failedItems.length > 0) {
        groups['failed_broadcasts'] = failedItems.map((item, fi) => {
            item._is_failed = true;
            return item;
        });
    }

    data.forEach((item, idx) => {
        item._idx = idx;
        const key = item.type || "upcoming";
        if(!groups[key]) groups[key] = [];
        groups[key].push(item);
    });

    let html = "";
    Object.keys(groups)
        .sort((a, b) => getGroupOrder(a) - getGroupOrder(b))
        .forEach(type => {
            html += `<div class="section"><h2>${getGroupTitle(type)}</h2><div class="cards-grid">`;

            groups[type].forEach(item => {
                const mobileCount = item.mobiles ? item.mobiles.length : 0;
                const mobileText = mobileCount ? item.mobiles.map(escapeHtml).join(", ") : "N/A";
                const isExpired = type === "expired" || type === "today";
                const buttonText = mobileCount > 1 ? `Message / Call (${mobileCount})` : "Send Reminder";
                const button = mobileCount
                    ? `<button class="action-btn" onclick="openPopup(${item._idx})"><i class="fa-brands fa-whatsapp"></i> ${buttonText}</button>`
                    : `<button class="action-btn" disabled><i class="fa-solid fa-phone-slash"></i> No Mobile</button>`;

                const isFailed = item.send_status === 'failed';
                const isSent = item.send_status === 'sent';
                const sendErrorMsg = item.send_error ? escapeHtml(item.send_error) : '';
                
                // Build send status badges
                let statusBadges = '';
                if (item.sent_today && isSent) {
                    statusBadges = '<span class="badge-sent"><i class="fa-solid fa-circle-check"></i> Sent Today</span>';
                } else if (isFailed) {
                    statusBadges = `<span class="badge-failed" title="${sendErrorMsg}"><i class="fa-solid fa-circle-exclamation"></i> Failed</span>`;
                }

                // Build error message display for failed items
                let errorDisplay = '';
                if (isFailed && sendErrorMsg) {
                    errorDisplay = `<div style="margin-top:4px;padding:8px 10px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.15);border-radius:6px;font-size:12px;color:#f87171;word-break:break-word;">
                        <i class="fa-solid fa-triangle-exclamation" style="margin-right:4px;"></i> ${sendErrorMsg}
                    </div>`;
                }

                html += `
                    <div class="card" style="${isFailed ? 'border-color: rgba(239,68,68,0.3);' : ''}">
                        <div class="vehicle">
                            <span>${escapeHtml(item.vehicle)}</span>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                ${statusBadges}
                                <span class="vehicle-icon"><i class="fa-solid fa-car-side"></i></span>
                            </div>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Expiry Date</span>
                            <span class="meta-val ${isExpired ? "expiry-val" : ""}">${escapeHtml(item.expiry)}</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Customer</span>
                            <span class="meta-val">${escapeHtml(item.customerName || item.customer || "N/A")}</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Contact</span>
                            <span class="meta-val">${mobileText}</span>
                        </div>
                        <div class="meta-row" style="border:none;">
                            <span class="meta-label">Renewal Due</span>
                            <span class="meta-val amount-val">₹${escapeHtml(item.amount)}</span>
                        </div>
                        ${errorDisplay}
                        ${button}
                    </div>`;
            });

            html += `</div></div>`;
        });

    document.getElementById("content").innerHTML = html;
}

function openPopup(idx){
    const item = stockData[idx];
    if(!item || !item.mobiles || item.mobiles.length === 0) return alert("No mobile number found");

    const buttons = item.mobiles.map(num => {
        const cleanNum = String(num).replace(/\D/g, "");
        const autoSendButton = item.sent_today
            ? `<button class="wp-btn" disabled style="background: rgba(100, 116, 139, 0.1); color: #94a3b8; border-color: rgba(148, 163, 184, 0.2) !important; cursor: not-allowed;" title="Already Sent Today"><i class="fa-solid fa-circle-check"></i> Sent Today</button>`
            : `<button class="wp-btn" onclick="sendAutoWhatsApp(${idx}, '${cleanNum}', this)" style="background: rgba(37, 211, 102, 0.2); color: #25D366; border-color: rgba(37, 211, 102, 0.4) !important;" title="Fully Automated Background Send"><i class="fa-solid fa-robot"></i> Auto Send</button>`;
        return `
            <div class="dual-btn">
                <div class="dual-number">${escapeHtml(cleanNum)}</div>
                <button class="wp-btn" onclick="sendWhatsApp(${idx}, '${cleanNum}')" title="Open Manual WhatsApp Web">
                    <i class="fa-brands fa-whatsapp"></i> Manual
                </button>
                ${autoSendButton}
                <button class="call-btn" onclick="callNumber('${cleanNum}')" title="Call Customer">
                    <i class="fa-solid fa-phone"></i> Call
                </button>
            </div>`;
    }).join("");

    const popupHTML = `
        <div class="popup" id="popup">
            <div class="popup-box">
                <h3>Contact Customer</h3>
                <div class="popup-subtitle">Vehicle: <b>${escapeHtml(item.vehicle)}</b></div>
                ${buttons}
                <button class="close-btn" onclick="closePopup()">
                    <i class="fa-solid fa-xmark"></i> Close
                </button>
            </div>
        </div>`;

    closePopup(true);
    document.body.insertAdjacentHTML("beforeend", popupHTML);
    setTimeout(() => document.getElementById("popup")?.classList.add("show"), 10);
}

function closePopup(immediate = false){
    const popup = document.getElementById("popup");
    if(!popup) return;
    if(immediate) {
        popup.remove();
        return;
    }
    popup.classList.remove("show");
    setTimeout(() => popup.remove(), 250);
}

function callNumber(num){
    if(!num) return;
    const cleanNum = String(num).replace(/\D/g, "");
    window.location.href = "tel:+91" + cleanNum;
    setTimeout(closePopup, 500);
}

function buildRenewalMessage(item, num){
    const vehicle = item.vehicle || "N/A";
    const amount = item.amount || 0;
    const expiry = item.expiry || "";
    const customerName = item.customerName || item.customer || "Customer";
    const header = `📌 *SK RENEWAL ALERT* 📌

Name: *${customerName}*
Vehicle: *${vehicle}*
Mobile: *${num}*

`;

    if(item.type === "expired"){
        const diff = Math.abs(item.daysRemaining || 0);
        const graceTotal = graceLimit;
        const graceLeft = Math.max(0, graceTotal - diff);
        
        let graceMsgEn = graceLeft > 0 
            ? `Grace period: ${graceLeft} day${graceLeft > 1 ? "s" : ""} remaining.`
            : `FINAL NOTICE: Today is the last day of your grace period.`;
            
        let graceMsgTa = graceLeft > 0
            ? `${graceLeft} நாள் grace period உள்ளது.`
            : `இறுதி எச்சரிக்கை: இன்று சலுகை காலத்தின் கடைசி நாள்.`;

        return header + `Important Notice:

Your GPS service for vehicle ${vehicle} has expired.

Renewal Amount: ₹${amount}

${graceMsgEn}
If not renewed, SIM will be disconnected.
Reactivation charge ₹300 extra.

Payment:
GPay / PhonePe - 9750776198

- SK ENTERPRISES

--------------------------------

முக்கிய அறிவிப்பு:

${vehicle} GPS சேவை காலாவதியாகியுள்ளது.

புதுப்பிப்பு தொகை: ₹${amount}

${graceMsgTa}
அதற்குப் பிறகு SIM நிறுத்தப்படும்.
மீண்டும் செயல்படுத்த ₹300 கூடுதல் கட்டணம்.

பணம் செலுத்த:
9750776198

- SK ENTERPRISES`;
    }

    if(item.type === "today"){
        return header + `Final Reminder:

Your GPS service for vehicle ${vehicle}
expires TODAY (${expiry}).

Renewal Amount: ₹${amount}

Please renew immediately.

Payment:
GPay / PhonePe - 9750776198

- SK ENTERPRISES

--------------------------------

இறுதி நினைவூட்டல்:

${vehicle} GPS சேவை இன்று (${expiry}) முடிவடைகிறது.

புதுப்பிப்பு தொகை: ₹${amount}

உடனே புதுப்பிக்கவும்.

- SK ENTERPRISES`;
    }

    if(item.type === "d1"){
        return header + `Urgent Reminder:

GPS service for vehicle ${vehicle}
expires tomorrow (${expiry}).

Renewal Amount: ₹${amount}

Renew immediately.

Payment:
9750776198

- SK ENTERPRISES

--------------------------------

அவசர நினைவூட்டல்:

${vehicle} GPS சேவை நாளை (${expiry}) முடிவடைகிறது.

புதுப்பிப்பு தொகை: ₹${amount}

உடனே புதுப்பிக்கவும்.

- SK ENTERPRISES`;
    }

    return header + `Reminder:

GPS service for vehicle ${vehicle}
will expire on ${expiry}.

Renewal Amount: ₹${amount}

Please renew in time.

Payment:
9750776198

- SK ENTERPRISES

--------------------------------

நினைவூட்டல்:

${vehicle} GPS சேவை ${expiry} அன்று முடிவடைகிறது.

புதுப்பிப்பு தொகை: ₹${amount}

தயவுசெய்து புதுப்பிக்கவும்.

- SK ENTERPRISES`;
}

function sendWhatsApp(idx, num){
    const item = stockData[idx];
    if(!item) return;

    const cleanNum = String(num).replace(/\D/g, "");
    const link = "https://wa.me/91" + cleanNum + "?text=" + encodeURIComponent(buildRenewalMessage(item, cleanNum));
    window.open(link, "_blank");
    closePopup();
}

function sendAutoWhatsApp(idx, num, btnElement){
    const item = stockData[idx];
    if(!item) return;

    const cleanNum = String(num).replace(/\D/g, "");
    const message = buildRenewalMessage(item, cleanNum);
    
    const originalContent = btnElement.innerHTML;
    btnElement.disabled = true;
    btnElement.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Sending...`;

    const formData = new FormData();
    formData.append('number', cleanNum);
    formData.append('message', message);
    if (item.id) {
        formData.append('renewal_id', item.id);
    }

    fetch('api_whatsapp_send.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            btnElement.style.background = 'rgba(16, 185, 129, 0.2)';
            btnElement.style.color = '#10b981';
            btnElement.style.borderColor = '#10b981';
            btnElement.innerHTML = `<i class="fa-solid fa-check"></i> Sent!`;
            
            // Mark as sent today in memory and reload list
            item.sent_today = true;
            
            setTimeout(() => {
                closePopup();
                render(stockData);
            }, 1000);
        } else {
            alert('Error sending: ' + res.error);
            btnElement.disabled = false;
            btnElement.innerHTML = originalContent;
        }
    })
    .catch(err => {
        alert('Network or Gateway connection error. Please make sure the WhatsApp gateway server is running.');
        btnElement.disabled = false;
        btnElement.innerHTML = originalContent;
    });
}

function triggerAutoBroadcast(){
    if(!confirm('Are you sure you want to run the fully automated background WhatsApp broadcast for all due/expired renewals? This will instantly send messages to all customers in the background.')) return;
    
    document.getElementById("content").innerHTML = `
        <div class="loading">
            <i class="fa-solid fa-robot fa-bounce" style="font-size:48px; color:#25D366; margin-bottom: 20px;"></i>
            <h3>Automated WhatsApp Broadcast in Progress...</h3>
            <p style="color:var(--text-muted); font-size:14px; margin-top:8px;">Running daily checks and dispatching messages in background. Please wait...</p>
        </div>`;

    fetch('send_daily_reminders.php')
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            const summary = res.summary;
            const details = res.details || [];
            
            // Build failed items detail list
            let failedDetailsHtml = '';
            const failedItems = details.filter(d => d.status === 'failed');
            if (failedItems.length > 0) {
                failedDetailsHtml = `
                    <div style="margin-top: 20px; border-top: 1px solid var(--card-border); padding-top: 18px;">
                        <h4 style="color:#ef4444; font-size:14px; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-circle-exclamation"></i> Failed Broadcast Details (${failedItems.length})
                        </h4>
                        <div style="max-height:300px; overflow-y:auto; display:flex; flex-direction:column; gap:8px;">
                            ${failedItems.map(f => `
                                <div style="background:rgba(239,68,68,0.06); padding:10px 12px; border-radius:6px; border:1px solid rgba(239,68,68,0.15); font-size:13px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                        <strong style="color:#f8fafc;">${escapeHtml(f.vehicle || f.customer || 'N/A')}</strong>
                                        <span style="color:#94a3b8; font-size:11px;">${escapeHtml(f.mobile || '')}</span>
                                    </div>
                                    <div style="color:#f87171; font-size:12px; word-break:break-word;">
                                        <i class="fa-solid fa-triangle-exclamation" style="margin-right:4px;"></i>
                                        ${escapeHtml(f.reason || 'Unknown error')}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            let reportHtml = `
                <div class="card" style="max-width: 600px; margin: 40px auto; text-align: left; padding: 30px;">
                    <h3 style="color:#25D366; display:flex; align-items:center; gap:10px; margin-bottom:20px;">
                        <i class="fa-solid fa-circle-check"></i> Broadcast Completed
                    </h3>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom: 20px;">
                        <div style="background:rgba(255,255,255,0.03); padding:12px; border-radius:8px; border:1px solid var(--card-border);">
                            <span class="meta-label">Total Records Found</span>
                            <div style="font-size:24px; font-weight:800; color:var(--text-main);">${summary.total_due_records}</div>
                        </div>
                        <div style="background:rgba(37, 211, 102, 0.08); padding:12px; border-radius:8px; border:1px solid rgba(37, 211, 102, 0.2);">
                            <span class="meta-label" style="color:#25D366;">WhatsApp Messages Sent</span>
                            <div style="font-size:24px; font-weight:800; color:#25D366;">${summary.sent_successfully}</div>
                        </div>
                        <div style="background:rgba(239, 68, 68, 0.08); padding:12px; border-radius:8px; border:1px solid rgba(239, 68, 68, 0.2);">
                            <span class="meta-label" style="color:#ef4444;">Failed Broadcasts</span>
                            <div style="font-size:24px; font-weight:800; color:#ef4444;">${summary.failed}</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.03); padding:12px; border-radius:8px; border:1px solid var(--card-border);">
                            <span class="meta-label">Skipped (No Mobile)</span>
                            <div style="font-size:24px; font-weight:800; color:var(--text-muted);">${summary.skipped_no_mobile}</div>
                        </div>
                    </div>
                    ${failedDetailsHtml}
                    <button class="action-btn" onclick="loadReminders()" style="background:var(--primary); color:white; border:none; width:100%;"><i class="fa-solid fa-arrows-rotate"></i> Close & Reload Dashboard</button>
                </div>
            `;
            document.getElementById("content").innerHTML = reportHtml;
        } else {
            alert('Broadcast Error: ' + res.error);
            loadReminders();
        }
    })
    .catch(err => {
        alert('Could not trigger broadcast. Please ensure the local Node.js WhatsApp Gateway is running (double-click run_gateway.bat).');
        loadReminders();
    });
}

loadReminders();
</script>
</body>
</html>
