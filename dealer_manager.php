<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Dealer Manager | SK LOGIC</title>
    <script src="theme_engine.js"></script>
    
    <!-- Ultra Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- 🔥 Security -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>

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
            --warning: #f59e0b;
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

        .container { max-width: 600px; margin: 20px auto; padding: 0 20px; animation: slideUp 0.6s ease-out; }
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        /* Menu Tabs */
        .tabs { display: flex; background: rgba(0,0,0,0.3); padding: 5px; border-radius: 20px; margin-bottom: 25px; border: 1px solid var(--border); }
        .tab { flex:1; padding: 12px; border-radius: 16px; text-align: center; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; color: var(--text-muted); }
        .tab.active { background: var(--primary); color: white; box-shadow: 0 5px 15px var(--primary-glow); }

        .glass-card { background: var(--surface); border: 1px solid var(--border); border-radius: 28px; padding: 25px; backdrop-filter: blur(20px); margin-bottom: 20px; }
        
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; }
        .input-field { 
            width: 100%; padding: 16px 20px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 15px; transition: 0.3s;
        }
        .input-field:focus { border-color: var(--primary); background: rgba(15, 23, 42, 0.8); }

        .btn-main {
            width: 100%; padding: 18px; border: none; border-radius: 18px; font-weight: 800; font-size: 14px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 12px; transition: 0.3s; margin-top: 10px;
        }
        .btn-primary { background: linear-gradient(135deg, var(--primary), #6366f1); color: white; box-shadow: 0 10px 20px rgba(139,92,246,0.3); }

        #scannerPanel { display: none; }
        #reader { border-radius: 24px; overflow: hidden; margin-bottom: 12px; display: none; border: 2px solid var(--primary); min-height: 280px; }
        #scanHint { font-size: 12px; color: var(--text-muted); text-align: center; }

        /* Pending List */
        .dealer-box { background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 24px; padding: 20px; margin-bottom: 30px; transition: 0.3s; }
        .dealer-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
        .dealer-name { font-size: 18px; font-weight: 800; color: var(--primary); letter-spacing: -0.5px; }
        .item-count { font-size: 10px; background: var(--primary); color: white; padding: 2px 8px; border-radius: 6px; }

        .bulk-payout-box { background: rgba(139, 92, 246, 0.05); border: 1px dashed var(--primary); border-radius: 18px; padding: 15px; margin-bottom: 20px; }
        .payout-title { font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 12px; display: block; }

        .pending-item { 
            background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 18px; padding: 15px; margin-bottom: 10px;
            position: relative; overflow: hidden;
        }
        .item-imei { font-weight: 800; font-family: 'Outfit'; font-size: 15px; display: block; margin-bottom: 4px; }
        .item-model { font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; }

        .pay-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 12px; }
        .mini-input { padding: 10px; font-size: 12px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: white; outline: none; }
        .mini-input:focus { border-color: var(--primary); }

        .section { display: none; }
        .section.active { display: block; }

        .loader { text-align: center; padding: 40px; color: var(--text-muted); font-size: 14px; }
    </style>
</head>
<body>

    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div style="font-size: 10px; font-weight: 800; color: var(--secondary); text-transform: uppercase;">Dealer Hub</div>
    </header>

    <div class="container">
        
        <div class="tabs">
            <div class="tab active" onclick="setTab('issue', this)"><i class="fa-solid fa-truck-ramp-box"></i> Issue</div>
            <div class="tab" onclick="setTab('bulk', this)"><i class="fa-solid fa-layer-group"></i> Bulk</div>
            <div class="tab" onclick="setTab('pending', this)"><i class="fa-solid fa-hourglass-half"></i> Pending</div>
        </div>

        <!-- 📤 ISSUE SECTION -->
        <div id="issue" class="section active">
            <div class="glass-card">
                <div class="input-group">
                    <label>Dealer Outlet</label>
                    <input type="text" id="dealerName" class="input-field" placeholder="Enter Dealer Name">
                </div>
                <div class="input-group">
                    <label>Individual IMEI</label>
                    <div style="display:flex; gap:10px; margin-bottom: 5px;">
                        <input type="text" id="imei" class="input-field" placeholder="Current Serial" oninput="checkImeiStatus(this.value)">
                        <button class="btn-main btn-primary" style="width:50px; margin:0;" onclick="startScan('ime')"><i class="fa-solid fa-qrcode"></i></button>
                    </div>
                    <div id="imeiStatus" style="font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 8px; display: none;"></div>
                </div>
                <button class="btn-main btn-primary" onclick="updateDevice()">Assign to Dealer</button>
            </div>
        </div>

        <!-- 📦 BULK SECTION (Issue to Dealer) -->
        <div id="bulk" class="section">
            <div class="glass-card">
                <div class="input-group">
                    <label>Bulk Dealer</label>
                    <input type="text" id="bulkDealer" class="input-field" placeholder="Target Outlet">
                </div>
                <div class="input-group">
                    <label>Scan List (IMEIs)</label>
                    <textarea id="bulkList" class="input-field" style="min-height: 120px;" placeholder="Scanning will add here..."></textarea>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <button class="btn-main" style="background:var(--secondary); color:white;" onclick="startScan('bulk')">Start Scanner</button>
                    <button class="btn-main" style="background:var(--warning); color:white;" onclick="stopScan()">Stop</button>
                </div>
                <button class="btn-main btn-primary" onclick="bulkUpdate()">Commit Bulk Upload</button>
            </div>
        </div>

        <!-- ⏳ PENDING SECTION (Payouts from Dealer) -->
        <div id="pending" class="section">
            <button class="btn-main btn-primary" style="margin-bottom:25px;" onclick="loadPending()"><i class="fa-solid fa-magnifying-glass-chart"></i> Find Pending Payouts</button>
            <div id="pendingList"></div>
        </div>

        <div id="scannerPanel" class="glass-card">
            <div id="reader"></div>
            <div id="scanHint">Point camera at barcode / QR code</div>
        </div>

    </div>

    <script>
        const API = "api_dealers.php";
        let scanner;
        let activeScanType = null;

        function setTab(id, btn) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            stopScan();
        }

        async function startScan(type) {
            const rd = document.getElementById('reader');
            const panel = document.getElementById('scannerPanel');
            const hint = document.getElementById('scanHint');

            activeScanType = type;
            panel.style.display = 'block';
            rd.style.display = 'block';
            hint.innerText = type === 'bulk' ? "Bulk scan active: each code will be added to the list." : "Scan one IMEI code.";

            try {
                if (scanner) {
                    await stopScan(false);
                }

                scanner = new Html5Qrcode("reader");
                const cameras = await Html5Qrcode.getCameras();
                const rearCamera = cameras.find(cam => /back|rear|environment/i.test(cam.label || ""));
                const cameraConfig = rearCamera ? { deviceId: { exact: rearCamera.id } } : { facingMode: "environment" };

                await scanner.start(cameraConfig, { fps: 20, qrbox: 250 }, (text) => {
                let code = text.replace(/\D/g,'');
                if(!code) return;
                if(type === 'ime') {
                    document.getElementById('imei').value = code;
                    stopScan();
                } else {
                    let area = document.getElementById('bulkList');
                    const existing = area.value.split('\n').map(v => v.trim()).filter(Boolean);
                    if(!existing.includes(code)) {
                        area.value += (area.value.trim() ? "\n" : "") + code;
                        if(navigator.vibrate) navigator.vibrate(100);
                    }
                }
                });
            } catch (err) {
                console.error("Scanner start failed:", err);
                hint.innerText = "Camera open ஆகலை. Browser camera permission allow பண்ணி மறுபடியும் try பண்ணுங்க.";
                alert("Camera open ஆகலை. Browser permission allow பண்ணி மீண்டும் try பண்ணுங்க.");
                await stopScan(false);
            }
        }

        async function stopScan(hidePanel = true) {
            const rd = document.getElementById('reader');
            const panel = document.getElementById('scannerPanel');

            if (scanner) {
                try {
                    await scanner.stop();
                } catch (e) {
                    console.warn("Scanner stop warning:", e);
                }
                try {
                    await scanner.clear();
                } catch (e) {
                    console.warn("Scanner clear warning:", e);
                }
                scanner = null;
            }

            rd.style.display = 'none';
            if (hidePanel) {
                panel.style.display = 'none';
            }
            activeScanType = null;
        }

        async function updateDevice() {
            let d = document.getElementById('dealerName').value;
            let i = document.getElementById('imei').value;
            if(!d || !i) return alert("Missing Dealer or IMEI");
            const res = await fetch(`${API}?action=update&dealer=${encodeURIComponent(d)}&imei=${encodeURIComponent(i)}`).then(r=>r.json());
            alert(res.message);
        }

        async function bulkUpdate() {
            let d = document.getElementById('bulkDealer').value;
            let list = document.getElementById('bulkList').value.split('\n').map(i=>i.trim()).filter(i=>i);
            if(!d || list.length === 0) return alert("Missing Dealer or List");
            
            for(let imei of list) {
                await fetch(`${API}?action=update&dealer=${encodeURIComponent(d)}&imei=${encodeURIComponent(imei)}`);
            }
            alert("Bulk Assign Complete!");
        }

        async function loadPending() {
            const listDiv = document.getElementById('pendingList');
            listDiv.innerHTML = '<div class="loader"><i class="fa-solid fa-circle-notch fa-spin"></i> Fetching records...</div>';
            
            await fetch(`${API}?action=sync_pending_ledger`).then(r=>r.json()).catch(() => null);
            const res = await fetch(`${API}?action=pending`).then(r=>r.json());
            if(res.length === 0) { listDiv.innerHTML = '<div style="text-align:center; padding:50px;">🎉 All Dealer Accounts Settled!</div>'; return; }

            // Group by Dealer
            let groups = {};
            res.forEach(item => {
                if(!groups[item.holder]) groups[item.holder] = [];
                groups[item.holder].push(item);
            });

            let html = "";
            for (let dealer in groups) {
                let items = groups[dealer];
                let safeId = dealer.replace(/[^a-zA-Z0-9]/g, '');
                let imeis = items.map(it => it.imei).join(',');

                html += `
                    <div class="dealer-box">
                        <div class="dealer-header">
                            <div>
                                <div class="dealer-name">${dealer}</div>
                                <span class="item-count">${items.length} Pending</span>
                            </div>
                        </div>

                        <!-- 🚀 Bulk Settle Option -->
                        <div class="bulk-payout-box">
                            <span class="payout-title">Bulk Payout (Whole Group)</span>
                                <div class="pay-inputs">
                                    <input type="number" id="br_${safeId}" class="mini-input" placeholder="Selling Price Per Unit">
                                    <input type="text" id="bt_${safeId}" class="mini-input" placeholder="Bulk TXN ID">
                                </div>
                            <button class="btn-main btn-primary" style="font-size:12px; padding:12px; margin-top:10px;" onclick="bulkPay('${dealer}', '${imeis}')">
                                <i class="fa-solid fa-receipt"></i> Settle All ${items.length} Devices
                            </button>
                        </div>

                        <!-- 📄 Individual Items -->
                        ${items.map(it => `
                            <div class="pending-item">
                                <span class="item-imei">${it.imei}</span>
                                <span class="item-model">${it.model || 'Standard'}</span>
                                <div class="pay-inputs">
                                    <input type="number" id="r_${it.imei}" class="mini-input" placeholder="Selling Price">
                                    <input type="text" id="t_${it.imei}" class="mini-input" placeholder="TXN ID">
                                </div>
                                <button class="btn-main" style="background:var(--surface); border:1px solid var(--border); font-size:11px; padding:10px; margin-top:10px;" onclick="payDevice('${it.imei}')">
                                    Update Single
                                </button>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
            listDiv.innerHTML = html;
        }

        async function payDevice(imei) {
            let r = document.getElementById('r_'+imei).value;
            let t = document.getElementById('t_'+imei).value;
            if(!r || !t) return alert("Enter Selling Price and TXN ID");
            const form = new URLSearchParams({ action: 'payment', imei, txn: t, sale_rate: r });
            const res = await fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: form.toString(),
                cache: 'no-store'
            }).then(r=>r.json());
            if(res.status==="success") {
                alert(`Payment updated\nDB: ${res.db_name || '-'}\nIMEI: ${res.imei || imei}\nTXN: ${res.txn || t}\nColumns: ${(res.updated_columns || []).join(', ') || '-'}\nSaved: ${JSON.stringify(res.txn_columns || {})}`);
                loadPending();
            }
            else alert(`${res.message}\nDB: ${res.db_name || '-'}\nIMEI: ${res.imei || imei}\nTXN: ${res.txn || t}\nColumns: ${(res.updated_columns || []).join(', ') || '-'}`);
        }

        async function bulkPay(dealer, imeiString) {
            let safeId = dealer.replace(/[^a-zA-Z0-9]/g, '');
            let r = document.getElementById('br_'+safeId).value;
            let t = document.getElementById('bt_'+safeId).value;
            if(!r || !t) return alert("Enter Bulk Selling Price and TXN ID");

            let imeis = imeiString.split(',');
            if(!confirm(`Confirm payment for ${imeis.length} devices at ₹${r} each?`)) return;

            // Process all
            for(let imei of imeis) {
                const form = new URLSearchParams({ action: 'payment', imei, txn: t, sale_rate: r });
                const res = await fetch(API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: form.toString(),
                    cache: 'no-store'
                }).then(r=>r.json());
                if(res.status !== "success") {
                    return alert(`${imei} update failed\n${res.message}`);
                }
            }
            alert("Bulk Payout Successful!");
            loadPending();
        }

        let statusT;
        async function checkImeiStatus(imei) {
            const statusDiv = document.getElementById('imeiStatus');
            if (!statusDiv) return;
            if (imei.length < 5) { statusDiv.style.display = 'none'; return; }

            clearTimeout(statusT);
            statusT = setTimeout(async () => {
                try {
                    const res = await fetch(`api_master_data.php?action=check_imei&imei=${encodeURIComponent(imei)}`).then(r => r.json());
                    statusDiv.style.display = 'block';
                    if (res.status === 'success') {
                        const d = res.data;
                        const statusStr = (d.status || "").toLowerCase();
                        const isStock = statusStr.includes('stock');
                        const branch = d.branch || 'ERD';
                        const otherBranch = (branch === 'ERD') ? 'SLM' : 'ERD';
                        
                        statusDiv.style.background = isStock ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)';
                        statusDiv.style.color = isStock ? 'var(--success)' : '#f87171';
                        
                        let displayStatus = d.status.replace('_', ' ').toUpperCase();
                        let html = `<i class="fa-solid ${isStock ? 'fa-check-circle' : 'fa-triangle-exclamation'}"></i> ` + 
                            (isStock ? `DEVICE ${displayStatus} [${branch}]` : `ALREADY SOLD [${branch}] / ISSUED TO: ${d.holder || 'UNKNOWN'}`);
                        
                        // Add warning if multi-branch record exists
                        if (d.multi_branch) {
                            html += `<br><span style="font-size:10px; color:var(--warning); font-weight:800; display:block; margin-top:5px;">
                                <i class="fa-solid fa-circle-info"></i> DUPLICATE RECORD FOUND IN ${otherBranch} BRANCH
                            </span>`;
                        }
                        
                        html += `<br><span style="font-size:9px; opacity:0.8;">Model: ${d.device_model || 'Standard'}</span>`;
                        statusDiv.innerHTML = html;
                    } else {
                        statusDiv.style.background = 'rgba(255, 255, 255, 0.05)';
                        statusDiv.style.color = '#94a3b8';
                        statusDiv.innerHTML = `<i class="fa-solid fa-circle-question"></i> NOT FOUND IN ERD/SLM`;
                    }
                } catch (e) { statusDiv.style.display = 'none'; }
            }, 300);
        }
    </script>
</body>
</html>
