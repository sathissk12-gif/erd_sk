<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKE Enterprise | Ultimate Dealer Suite v6.0</title>
    <script src="theme_engine.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg: #0a0b1e; --panel: rgba(20, 22, 45, 0.7); --primary: #6366f1; --accent: #f43f5e;
            --success: #10b981; --warning: #f59e0b; --text: #e2e8f0; --text-dim: #94a3b8;
            --grad: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --glass: rgba(255, 255, 255, 0.03); --border: rgba(255, 255, 255, 0.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg); color: var(--text); overflow-x: hidden; min-height: 100vh; }
        body::before {
            content: ''; position: fixed; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle at center, rgba(99, 102, 241, 0.1) 0%, transparent 40%);
            z-index: -1; animation: float 20s infinite linear;
        }
        @keyframes float { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; margin-bottom: 30px; }
        .logo-area h1 { font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 800; background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .logo-area p { color: var(--text-dim); font-size: 12px; letter-spacing: 2px; }
        .stats-strip { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--panel); border: 1px solid var(--border); padding: 25px; border-radius: 24px; backdrop-filter: blur(20px); transition: 0.3s; }
        .stat-card i { font-size: 24px; margin-bottom: 15px; display: block; color: var(--primary); }
        .stat-card span { font-size: 13px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; }
        .stat-card h2 { font-size: 26px; margin-top: 5px; font-weight: 800; }
        .controls { display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; }
        .search-box { flex: 1; position: relative; min-width: 300px; }
        .search-box input { width: 100%; background: var(--panel); border: 1px solid var(--border); padding: 18px 25px 18px 55px; border-radius: 20px; color: white; font-size: 16px; backdrop-filter: blur(10px); outline: none; transition: 0.3s; }
        .search-box i { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: var(--text-dim); }
        .btn-add { background: var(--grad); color: white; border: none; padding: 0 30px; border-radius: 20px; font-weight: 700; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 10px; font-size: 14px; height: 55px; }
        .btn-add:hover { transform: scale(1.05); filter: brightness(1.2); }
        .dealer-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .dealer-card { background: var(--panel); border: 1px solid var(--border); border-radius: 30px; padding: 30px; position: relative; transition: 0.4s; overflow: hidden; cursor: pointer; }
        .dealer-card:hover { background: rgba(255,255,255,0.05); transform: translateY(-10px); }
        .dealer-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; }
        .dealer-info h3 { font-size: 20px; font-weight: 800; margin-bottom: 5px; }
        .dealer-info p { color: var(--text-dim); font-size: 14px; }
        .office-badge { background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 6px 15px; border-radius: 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .dealer-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .d-stat { background: rgba(0,0,0,0.2); padding: 15px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.02); }
        .d-stat label { display: block; font-size: 11px; color: var(--text-dim); text-transform: uppercase; margin-bottom: 5px; }
        .d-stat value { font-size: 18px; font-weight: 700; color: white; }
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(15px); z-index: 1000; display: none; align-items: center; justify-content: center; }
        .modal.show { display: flex; animation: fadeIn 0.3s; }
        .modal-content { background: #15172b; width: 95%; max-width: 1000px; max-height: 90vh; border-radius: 40px; border: 1px solid var(--border); overflow: hidden; display: flex; flex-direction: column; position: relative; }
        .modal-header { padding: 30px 40px; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(to bottom, rgba(99, 102, 241, 0.1), transparent); }
        .modal-body { padding: 0 40px 40px; overflow-y: auto; }
        .console-tabs { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 10px; }
        .c-tab { padding: 10px 25px; border-radius: 12px; cursor: pointer; font-weight: 600; color: var(--text-dim); transition: 0.3s; }
        .c-tab.active { background: var(--primary); color: white; }
        .ledger-list { display: flex; flex-direction: column; gap: 12px; }
        .ledger-item { background: var(--glass); border: 1px solid var(--border); padding: 20px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; }
        .l-type-issue { border-left: 4px solid var(--accent); }
        .l-type-pay { border-left: 4px solid var(--success); }
        .l-main h4 { font-weight: 800; margin-bottom: 2px; }
        .l-main p { font-size: 12px; color: var(--text-dim); }
        .l-amt { text-align: right; }
        .l-amt h3 { font-size: 20px; font-weight: 800; }
        .l-amt.plus { color: var(--success); }
        .l-amt.minus { color: var(--accent); }
        .field { margin-bottom: 20px; }
        .field label { display: block; font-size: 13px; font-weight: 600; color: var(--text-dim); margin-bottom: 8px; }
        .field input, .field select { width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--border); padding: 15px 20px; border-radius: 15px; color: white; outline: none; }
        .field input:focus { border-color: var(--primary); }
        .btn-icon { background: var(--glass); border: 1px solid var(--border); padding: 10px; border-radius: 12px; color: white; cursor: pointer; transition: 0.3s; }
        .btn-icon:hover { background: var(--accent); }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo-area">
                <h1>SKE ENTERPRISE</h1>
                <p id="branchLabel">DEALER MANAGEMENT SUITE v6.0</p>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="master_device.php" class="btn-add" style="background:var(--panel); border:1px solid var(--border);"><i class="fa-solid fa-gear"></i> MASTERS</a>
                <button class="btn-add" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> ADD PARTNER</button>
            </div>
        </header>

        <div class="stats-strip">
            <div class="stat-card"><i class="fa-solid fa-users"></i><span>Partners</span><h2 id="statTotalDealers">0</h2></div>
            <div class="stat-card"><i class="fa-solid fa-box"></i><span>Total Devices</span><h2 id="statTotalStock">0</h2></div>
            <div class="stat-card"><i class="fa-solid fa-wallet"></i><span>Market Balance</span><h2 id="statTotalBalance">₹0</h2></div>
        </div>

        <div class="controls">
            <div class="search-box"><i class="fa-solid fa-search"></i><input type="text" placeholder="Search Partners..." id="dealerSearch" oninput="renderDealers()"></div>
        </div>

        <div class="dealer-grid" id="dealerGrid"></div>
    </div>

    <!-- Modals -->
    <div class="modal" id="dealerModal"><div class="modal-content" style="max-width: 500px;"><div class="modal-header"><h2>New Partner</h2><button class="btn-icon" onclick="closeModal('dealerModal')"><i class="fa-solid fa-times"></i></button></div><div class="modal-body"><form id="dealerForm"><div class="field"><label>Business Name</label><input type="text" name="name" required></div><div class="field"><label>Phone</label><input type="text" name="phone" required></div><div class="field"><label>Branch</label><select name="office"><option value="Erode">Erode</option><option value="Salem">Salem</option></select></div><div class="field"><label>Location</label><input type="text" name="location" placeholder="City / Area"></div><button type="submit" class="btn-add" style="width:100%; justify-content:center;">SAVE PARTNER</button></form></div></div></div>

    <div class="modal" id="consoleModal"><div class="modal-content"><div class="modal-header"><div><h2 id="consoleDealerName">Console</h2><p id="consoleDealerPhone" style="color:var(--text-dim)"></p></div><div style="display:flex; gap:10px;"><button class="btn-add" style="background:var(--success)" onclick="openModal('paymentModal')"><i class="fa-solid fa-plus"></i> PAYMENT</button><button class="btn-add" onclick="openIssueModal()"><i class="fa-solid fa-truck"></i> ISSUE STOCK</button><button class="btn-icon" onclick="closeModal('consoleModal')"><i class="fa-solid fa-times"></i></button></div></div><div class="modal-body"><div class="console-tabs"><div class="c-tab active" onclick="setTab(this, 'tab-stock')">Inventory</div><div class="c-tab" onclick="setTab(this, 'tab-ledger')">Ledger</div><div class="c-tab" onclick="setTab(this, 'tab-analytics')">Financials</div></div><div id="tab-stock" class="tab-pane"><div class="dealer-grid" id="consoleDeviceList" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));"></div></div><div id="tab-ledger" class="tab-pane" style="display:none;"><div class="ledger-list" id="consoleLedgerList"></div></div><div id="tab-analytics" class="tab-pane" style="display:none;"><canvas id="dealerChart" height="300"></canvas></div></div></div></div>

    <div class="modal" id="paymentModal" style="z-index: 1100;"><div class="modal-content" style="max-width: 400px;"><div class="modal-header"><h2>Record Payment</h2><button class="btn-icon" onclick="closeModal('paymentModal')"><i class="fa-solid fa-times"></i></button></div><div class="modal-body"><div class="field"><label>Amount (₹)</label><input type="number" id="payAmount" style="font-size:24px; font-weight:800;"></div><div class="field"><label>Mode</label><select id="payMode"><option value="CASH">Cash</option><option value="G-PAY">G-Pay / UPI</option><option value="BANK">Bank Transfer</option></select></div><button class="btn-add" style="width:100%; justify-content:center;" onclick="savePayment()">CONFIRM PAYMENT</button></div></div></div>

    <div class="modal" id="issueModal" style="z-index: 1100;"><div class="modal-content" style="max-width: 550px;"><div class="modal-header"><h2>Issue Stock</h2><button class="btn-icon" onclick="closeModal('issueModal')"><i class="fa-solid fa-times"></i></button></div><div class="modal-body">
        <div style="margin-bottom:20px; display:flex; gap:10px;">
            <button class="c-tab active" id="btnModeSingle" onclick="toggleIssueMode('single')">Scan Mode</button>
            <button class="c-tab" id="btnModeBulk" onclick="toggleIssueMode('bulk')">Bulk Paste</button>
        </div>
        
        <div class="field" id="singleScanArea">
            <label>Scan IMEI (Auto-adds at 15 digits)</label>
            <div style="display:flex; gap:10px;">
                <input type="text" id="issueImei" style="flex:1;" oninput="handleImeiInput(this.value)" placeholder="Scan or type IMEI...">
                <button class="btn-add" onclick="validateImei()">ADD</button>
            </div>
        </div>

        <div class="field" id="bulkPasteArea" style="display:none;">
            <label>Bulk Paste (IMEIs separated by comma or newline)</label>
            <textarea id="bulkImeiList" style="width:100%; background:rgba(0,0,0,0.3); border:1px solid var(--border); padding:15px; border-radius:15px; color:white; min-height:120px; outline:none; font-family:monospace;" placeholder="IMEI1&#10;IMEI2&#10;..."></textarea>
            <button class="btn-add" style="margin-top:10px; width:100%; justify-content:center; background:var(--primary);" onclick="validateBulkImeis()">VALIDATE & ADD ALL</button>
        </div>

        <div id="addedImeis" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:15px; max-height:100px; overflow-y:auto; padding:5px; background:rgba(0,0,0,0.2); border-radius:10px;"></div>
        
        <div class="field"><label>Software Type</label><select id="issueSoft"><option value="">Loading Master...</option></select></div>
        <div class="field"><label>Selling Rate (₹)</label><input type="number" id="issueRate"></div>
        <button class="btn-add" id="btnConfirmIssue" style="width:100%; justify-content:center;" onclick="processIssue()">COMPLETE ASSIGNMENT</button>
    </div></div></div>

    <script>
        const API = "api_dealers_v2.php";
        const API_ISSUE = "api_dealers.php";
        const GATEWAY = "api_dealers_v2.php";
        let allDealers = [], currentDealer = null, bulkImeis = [], myChart = null;

        window.onload = () => { 
            const isSlm = window.location.pathname.includes('SLM');
            document.getElementById('branchLabel').innerText = isSlm ? "SALEM BRANCH PRO v6.0" : "ERODE HEAD OFFICE v6.0";
            loadDealers(); loadMasters();
        };

        async function loadDealers() {
            const res = await fetch(`${API}?action=list`).then(r => r.json());
            if (res.success) { allDealers = res.data; renderDealers(); updateStats(); }
        }

        async function loadMasters() {
            const res = await fetch(`${API}?action=get_masters`).then(r => r.json());
            if (res.success) {
                const options = ['NONE', ...res.software];
                document.getElementById('issueSoft').innerHTML = options.map(s => `<option value="${s}">${s === 'NONE' ? 'NO SOFTWARE' : s}</option>`).join('');
            }
        }

        function renderDealers() {
            const search = document.getElementById('dealerSearch').value.toLowerCase();
            const grid = document.getElementById('dealerGrid');
            const filtered = allDealers.filter(d => d.name.toLowerCase().includes(search) || d.phone.includes(search));
            grid.innerHTML = filtered.map(d => `
                <div class="dealer-card" onclick="openConsole(${d.id})">
                    <div class="dealer-header">
                        <div><h3>${d.name}</h3><p>${d.phone} | ${d.location || 'No Location'}</p></div>
                        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
                            <span class="office-badge">${d.office}</span>
                            <button class="btn-icon" onclick="event.stopPropagation(); editDealer(${d.id})" style="background:rgba(99,102,241,0.1); color:var(--primary); border:none;"><i class="fa-solid fa-pen-to-square"></i></button>
                        </div>
                    </div>
                    <div class="dealer-stats"><div class="d-stat"><label>Stock</label><value>${d.total_devices}</value></div><div class="d-stat"><label>Pending</label><value>${d.pending_devices}</value></div></div>
                </div>
            `).join('');
        }

        function updateStats() {
            document.getElementById('statTotalDealers').innerText = allDealers.length;
            document.getElementById('statTotalStock').innerText = allDealers.reduce((a,b) => a + (parseInt(b.total_devices)||0), 0);
        }

        function openModal(id) { document.getElementById(id).classList.add('show'); }
        function closeModal(id) { document.getElementById(id).classList.remove('show'); }

        function openAddModal() {
            const form = document.getElementById('dealerForm');
            form.reset();
            const idInput = form.querySelector('input[name="id"]');
            if(idInput) idInput.value = "";
            document.querySelector('#dealerModal h2').innerText = "New Partner";
            openModal('dealerModal');
        }

        function editDealer(id) {
            const dealer = allDealers.find(d => d.id == id);
            if (!dealer) return;
            const form = document.getElementById('dealerForm');
            form.name.value = dealer.name;
            form.phone.value = dealer.phone;
            form.office.value = dealer.office;
            form.location.value = dealer.location || '';
            
            let idInput = form.querySelector('input[name="id"]');
            if(!idInput) {
                idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                form.appendChild(idInput);
            }
            idInput.value = id;
            document.querySelector('#dealerModal h2').innerText = "Edit Partner";
            openModal('dealerModal');
        }

        async function openConsole(id) {
            const res = await fetch(`${API}?action=get_details&id=${id}`).then(r => r.json());
            if (res.success) { currentDealer = res; renderConsoleData(); openModal('consoleModal'); }
        }

        function renderConsoleData() {
            document.getElementById('consoleDealerName').innerText = currentDealer.data.name;
            document.getElementById('consoleDealerPhone').innerText = currentDealer.data.phone;
            document.getElementById('consoleDeviceList').innerHTML = currentDealer.devices.map(d => `
                <div class="d-stat" style="background:var(--glass); padding:15px; border:1px solid var(--border);">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div><label style="color:var(--primary); font-weight:800;">${d.imei}</label><p style="font-size:11px;">${d.device_model}</p></div>
                        <button class="btn-icon" onclick="returnDevice('${d.imei}')"><i class="fa-solid fa-rotate-left"></i></button>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-top:10px; font-size:12px;"><span>${d.issue_date||'N/A'}</span><span style="font-weight:700">₹${d.rate}</span></div>
                </div>
            `).join('');

            let balance = 0;
            document.getElementById('consoleLedgerList').innerHTML = currentDealer.ledger.map(l => {
                const isPay = l.imei === 'PAYMENT' || l.software === 'STOCK RETURN';
                if(isPay) balance -= parseFloat(l.selling_price); else balance += parseFloat(l.selling_price);
                return `
                    <div class="ledger-item ${isPay ? 'l-type-pay' : 'l-type-issue'}">
                        <div><h4>${isPay ? (l.software||'Payment') : 'Issue: '+l.imei}</h4><p>${l.date}</p></div>
                        <div style="display:flex; align-items:center; gap:15px;">
                            <div class="l-amt ${isPay ? 'plus' : 'minus'}"><h3>${isPay ? '+' : '-'} ₹${l.selling_price}</h3></div>
                            <button class="btn-icon" onclick="event.stopPropagation(); deleteLedgerRow(${l.id}, '${l.branch}')" style="background:rgba(244,63,94,0.1); color:var(--accent); border:none;"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function deleteLedgerRow(id, branch) {
            if (!confirm("Are you sure? Deleting an IMEI issue will return the device to In Stock.")) return;
            const res = await fetch(API, { 
                method: 'POST', 
                body: new URLSearchParams({ action: 'delete_ledger', id: id, branch: branch }) 
            }).then(r => r.json());
            if (res.success) {
                openConsole(currentDealer.data.id);
                loadDealers();
            } else {
                alert("Error: " + res.message);
            }
        }

        function setTab(el, tabId) {
            document.querySelectorAll('.c-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
            el.classList.add('active'); document.getElementById(tabId).style.display = 'block';
            if(tabId === 'tab-analytics') initChart();
        }

        function openIssueModal() { 
            bulkImeis = []; 
            renderAddedImeis(); 
            toggleIssueMode('single');
            document.getElementById('issueImei').value = "";
            document.getElementById('bulkImeiList').value = "";
            openModal('issueModal'); 
        }

        function toggleIssueMode(mode) {
            document.getElementById('singleScanArea').style.display = (mode === 'single') ? 'block' : 'none';
            document.getElementById('bulkPasteArea').style.display = (mode === 'bulk') ? 'block' : 'none';
            document.getElementById('btnModeSingle').classList.toggle('active', mode === 'single');
            document.getElementById('btnModeBulk').classList.toggle('active', mode === 'bulk');
        }

        async function handleImeiInput(val) {
            if(val.trim().length === 15) {
                await validateImei();
            }
        }

        async function validateImei() {
            const imei = document.getElementById('issueImei').value.trim();
            if(!imei) return;
            const res = await fetch(`${GATEWAY}?action=check_imei&imei=${imei}`).then(r => r.json());
            if(res.success) {
                const status = res.data.status.toLowerCase();
                if(status !== 'in stock') return alert("Device "+imei+" is already " + res.data.status);
                if(bulkImeis.includes(imei)) {
                    document.getElementById('issueImei').value = "";
                    return;
                }
                bulkImeis.push(imei); renderAddedImeis(); document.getElementById('issueImei').value="";
            } else {
                alert("Invalid IMEI or Not Found: " + imei);
                document.getElementById('issueImei').value = "";
            }
        }

        async function validateBulkImeis() {
            const text = document.getElementById('bulkImeiList').value;
            const imeis = text.split(/[\n, ]+/).map(i => i.trim()).filter(i => i.length >= 8);
            if(imeis.length === 0) return alert("Paste some IMEIs first");
            
            const btn = document.querySelector('#bulkPasteArea button');
            btn.innerText = "Validating " + imeis.length + " items...";
            btn.disabled = true;

            for(let imei of imeis) {
                if(bulkImeis.includes(imei)) continue;
                try {
                    const res = await fetch(`${GATEWAY}?action=check_imei&imei=${imei}`).then(r => r.json());
                    if(res.success && res.data.status.toLowerCase() === 'in stock') {
                        bulkImeis.push(imei);
                    }
                } catch(e) {}
            }
            
            renderAddedImeis();
            document.getElementById('bulkImeiList').value = "";
            btn.innerText = "VALIDATE & ADD ALL";
            btn.disabled = false;
            toggleIssueMode('single');
        }

        function renderAddedImeis() { 
            const container = document.getElementById('addedImeis');
            container.innerHTML = bulkImeis.map(i => `<span class="office-badge" style="background:var(--grad); color:white; cursor:pointer;" onclick="removeImei('${i}')">${i} ×</span>`).join(''); 
            container.scrollTop = container.scrollHeight;
        }

        function removeImei(i) { bulkImeis = bulkImeis.filter(x => x!==i); renderAddedImeis(); }

        async function processIssue() {
            const d = currentDealer.data.name, rate = document.getElementById('issueRate').value, soft = document.getElementById('issueSoft').value;
            if(!rate || !bulkImeis.length) return alert("Fill all details");
            document.getElementById('btnConfirmIssue').innerText = "Processing...";
            for(let imei of bulkImeis) {
                await fetch(API_ISSUE, { method:'POST', body: new URLSearchParams({action:'update', dealer:d, imei, selling_rate:rate, software:soft}) });
            }
            closeModal('issueModal'); openConsole(currentDealer.data.id); loadDealers();
            document.getElementById('btnConfirmIssue').innerText = "COMPLETE ASSIGNMENT";
        }

        async function savePayment() {
            const amt = document.getElementById('payAmount').value, mode = document.getElementById('payMode').value;
            const res = await fetch(API_ISSUE, { method:'POST', body: new URLSearchParams({action:'payment', dealer:currentDealer.data.name, amount:amt, mode:mode}) }).then(r => r.json());
            if(res.status==='success') { closeModal('paymentModal'); openConsole(currentDealer.data.id); }
        }

        async function returnDevice(imei) {
            if(!confirm("Return "+imei+" to stock?")) return;
            const res = await fetch(API_ISSUE, { method:'POST', body: new URLSearchParams({action:'return', dealer:currentDealer.data.name, imei:imei}) }).then(r => r.json());
            if(res.status==='success') { openConsole(currentDealer.data.id); loadDealers(); }
        }

        function initChart() {
            const ctx = document.getElementById('dealerChart').getContext('2d');
            if(myChart) myChart.destroy();
            const ledger = currentDealer.ledger.slice(0, 10).reverse();
            myChart = new Chart(ctx, {
                type: 'line', data: { labels: ledger.map(l=>l.date), datasets: [{ label:'Value', data: ledger.map(l=>l.selling_price), borderColor: '#6366f1', tension: 0.4, fill: true, backgroundColor: 'rgba(99, 102, 241, 0.1)' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } } } }
            });
        }

        document.getElementById('dealerForm').onsubmit = async (e) => {
            e.preventDefault(); const fd = new FormData(e.target); fd.append('action', 'save');
            await fetch(API, { method: 'POST', body: fd }); closeModal('dealerModal'); loadDealers();
        };
    </script>
</body>
</html>
