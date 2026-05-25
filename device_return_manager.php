<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Device Return Manager | SK LOGIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- 🔥 Security -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="theme_engine.js"></script>
    
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.4);
            --secondary: #06b6d4;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --card-base: rgba(30, 41, 59, 0.3);
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
        .stat-card .num { font-size: 22px; font-weight: 800; font-family: 'Outfit'; }
        .stat-card .label { font-size: 9px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        
        .glass-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 22px;
            backdrop-filter: blur(20px); margin-bottom: 16px;
        }
        .section-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        
        .search-box { display: flex; gap: 10px; }
        .search-box input {
            flex: 1; padding: 14px 18px; background: rgba(15,23,42,0.4); border: 1px solid var(--border);
            border-radius: 14px; color: white; font-size: 15px; outline: none; font-family: inherit;
        }
        .search-box input:focus { border-color: var(--primary); }
        .search-box button {
            padding: 12px 22px; border: none; border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white; font-weight: 700; cursor: pointer;
        }
        
        .input-group { margin-bottom: 14px; }
        .input-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; padding-left: 4px; }
        .input-field {
            width: 100%; padding: 12px 16px; background: rgba(15,23,42,0.4); border: 1px solid var(--border);
            border-radius: 12px; color: white; font-size: 14px; outline: none; font-family: inherit;
        }
        .input-field:focus { border-color: var(--primary); }
        
        .dual-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        
        .sale-item {
            background: rgba(30,41,59,0.3); border: 1px solid var(--border); border-radius: 16px;
            padding: 16px; margin-bottom: 10px; cursor: pointer; transition: 0.2s;
        }
        .sale-item:hover { border-color: var(--primary); }
        .sale-item .top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .sale-item .customer { font-weight: 700; font-size: 16px; }
        .sale-item .vehicle { color: var(--secondary); font-weight: 600; font-size: 14px; }
        .sale-item .meta { font-size: 11px; color: var(--text-muted); margin-top: 4px; }
        
        .returned-badge {
            font-size: 10px; padding: 3px 10px; border-radius: 99px; font-weight: 800;
            background: rgba(244,63,94,0.15); color: var(--danger);
        }
        .btn-main {
            width: 100%; padding: 16px; border: none; border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white; font-weight: 800; cursor: pointer; font-size: 15px; transition: 0.2s;
        }
        .btn-main:active { transform: scale(0.96); }
        .btn-danger { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .btn-success { background: linear-gradient(135deg, #059669, #10b981); }
        
        .tab-bar { display: flex; gap: 10px; margin-bottom: 20px; overflow-x: auto; }
        .tab {
            padding: 8px 16px; background: var(--surface); border: 1px solid var(--border); border-radius: 99px;
            font-size: 12px; font-weight: 700; color: var(--text-muted); cursor: pointer; white-space: nowrap;
        }
        .tab.active { background: var(--primary); color: white; border-color: var(--primary); }

        .return-item {
            background: rgba(30,41,59,0.3); border: 1px solid var(--border); border-radius: 16px;
            padding: 16px; margin-bottom: 10px;
        }
        .return-item .hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .return-item .veh { font-weight: 700; color: var(--secondary); }
        .return-type {
            font-size: 10px; padding: 2px 10px; border-radius: 99px; font-weight: 800;
        }
        .type-RETURN { background: rgba(245,158,11,0.15); color: var(--warning); }
        .type-REPLACEMENT { background: rgba(139,92,246,0.15); color: var(--primary); }
        
        .hidden-form { display: none; }
        .hidden-form.show { display: block; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from {opacity:0;max-height:0} to {opacity:1;max-height:800px} }
        
        @media (max-width: 500px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .dual-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div style="font-weight:800; font-size:14px;">🔁 DEVICE RETURN</div>
    </header>

    <div class="container">
        <!-- 📊 Stats -->
        <div class="stats-row">
            <div class="stat-card"><div class="num" id="statTotal">0</div><div class="label">Total Returns</div></div>
            <div class="stat-card"><div class="num" id="statMonthly">0</div><div class="label">This Month</div></div>
            <div class="stat-card"><div class="num" id="statReplace">0</div><div class="label">Replacements</div></div>
            <div class="stat-card"><div class="num" id="statCancel">0</div><div class="label">Cancelled</div></div>
        </div>

        <!-- 🔍 Search Sale -->
        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-search"></i> Search Sale Record</div>
            <div class="search-box">
                <input type="text" id="searchQuery" placeholder="Vehicle No / IMEI / Customer..." oninput="debouncedSearch()">
                <button onclick="searchSales()"><i class="fa-solid fa-search"></i></button>
            </div>
            <div id="searchResults" style="margin-top:12px;"></div>
        </div>

        <!-- 📝 Process Return Form -->
        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-rotate-left"></i> Process Return / Replacement</div>
            
            <div class="tab-bar" id="formTabs">
                <div class="tab active" onclick="switchFormTab('RETURN')">📦 Device Return</div>
                <div class="tab" onclick="switchFormTab('REPLACEMENT')">🔄 Replacement</div>
                <div class="tab" onclick="switchFormTab('HISTORY')">📋 Return History</div>
            </div>
            
            <!-- RETURN Form -->
            <div id="formRETURN" class="hidden-form show">
                <form id="returnForm" onsubmit="processReturn(event)">
                    <input type="hidden" id="saleUid">
                    <input type="hidden" id="retVehicle">
                    <input type="hidden" id="retImei">
                    
                    <div style="padding:12px; background:rgba(245,158,11,0.1); border-radius:12px; margin-bottom:14px; font-size:13px;">
                        <i class="fa-solid fa-info-circle" style="color:var(--warning);"></i> 
                        First search a sale record above, then it will auto-fill here.
                    </div>
                    
                    <div class="dual-grid">
                        <div class="input-group"><label>Customer Name</label><input type="text" id="retCustomer" class="input-field" readonly></div>
                        <div class="input-group"><label>Mobile</label><input type="text" id="retMobile" class="input-field" readonly></div>
                    </div>
                    <div class="dual-grid">
                        <div class="input-group"><label>Vehicle</label><input type="text" id="retVehicleDisplay" class="input-field" readonly></div>
                        <div class="input-group"><label>IMEI</label><input type="text" id="retImeiDisplay" class="input-field" readonly></div>
                    </div>
                    
                    <div class="input-group">
                        <label>Return Reason <span style="color:var(--danger);">*</span></label>
                        <select id="retReason" class="input-field" required>
                            <option value="">Select Reason...</option>
                            <option value="DEVICE_DEFECTIVE">Device Defective / Faulty</option>
                            <option value="CUSTOMER_NOT_SATISFIED">Customer Not Satisfied</option>
                            <option value="WRONG_DEVICE">Wrong Device Delivered</option>
                            <option value="UPGRADE">Upgrade to Better Model</option>
                            <option value="CANCEL_SERVICE">Service Cancellation</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                    
                    <div class="dual-grid">
                        <div class="input-group"><label>Service Charge (₹)</label><input type="number" id="retCharge" class="input-field" value="0"></div>
                        <div class="input-group"><label>Refund Amount (₹)</label><input type="number" id="retRefund" class="input-field" value="0"></div>
                    </div>
                    
                    <div class="input-group"><label>Notes</label><textarea id="retNotes" class="input-field" rows="2" placeholder="Optional notes..."></textarea></div>
                    
                    <button type="submit" class="btn-main btn-danger"><i class="fa-solid fa-rotate-left"></i> Process Return</button>
                </form>
            </div>
            
            <!-- REPLACEMENT Form -->
            <div id="formREPLACEMENT" class="hidden-form">
                <form id="replaceForm" onsubmit="processReturn(event)">
                    <input type="hidden" id="repUid">
                    <input type="hidden" id="repVehicle">
                    <input type="hidden" id="repImei">
                    
                    <div class="dual-grid">
                        <div class="input-group"><label>Customer Name</label><input type="text" id="repCustomer" class="input-field" readonly></div>
                        <div class="input-group"><label>Mobile</label><input type="text" id="repMobile" class="input-field" readonly></div>
                    </div>
                    <div class="dual-grid">
                        <div class="input-group"><label>Old Vehicle</label><input type="text" id="repVehicleDisplay" class="input-field" readonly></div>
                        <div class="input-group"><label>Old IMEI</label><input type="text" id="repImeiDisplay" class="input-field" readonly></div>
                    </div>
                    
                    <div class="input-group">
                        <label>Return Reason <span style="color:var(--danger);">*</span></label>
                        <select id="repReason" class="input-field" required>
                            <option value="">Select Reason...</option>
                            <option value="DEVICE_DEFECTIVE">Device Defective / Faulty</option>
                            <option value="UPGRADE">Upgrade to Better Model</option>
                            <option value="CUSTOMER_REQUEST">Customer Request</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                    
                    <div class="dual-grid">
                        <div class="input-group"><label>New IMEI <span style="color:var(--danger);">*</span></label><input type="text" id="repNewImei" class="input-field" required placeholder="Enter new device IMEI"></div>
                        <div class="input-group"><label>New Vehicle</label><input type="text" id="repNewVehicle" class="input-field" placeholder="Same or new vehicle"></div>
                    </div>
                    
                    <div class="dual-grid">
                        <div class="input-group"><label>Extra Charge (₹)</label><input type="number" id="repCharge" class="input-field" value="0"></div>
                        <div class="input-group"><label>Refund (₹)</label><input type="number" id="repRefund" class="input-field" value="0"></div>
                    </div>
                    
                    <div class="input-group"><label>Notes</label><textarea id="repNotes" class="input-field" rows="2" placeholder="Optional notes..."></textarea></div>
                    
                    <button type="submit" class="btn-main btn-success"><i class="fa-solid fa-arrows-rotate"></i> Process Replacement</button>
                </form>
            </div>
            
            <!-- HISTORY Tab -->
            <div id="formHISTORY" class="hidden-form">
                <div id="historyList">
                    <div style="text-align:center; padding:30px; color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API = 'api_device_return.php';

        // Init: Setup table + load stats
        async function init() {
            await fetch(`${API}?action=setup_table`);
            loadStats();
            loadHistory();
        }
        init();

        // 📊 Load Stats
        async function loadStats() {
            try {
                const res = await fetch(`${API}?action=get_stats`);
                const d = await res.json();
                if (d.status === 'success') {
                    document.getElementById('statTotal').innerText = d.total;
                    document.getElementById('statMonthly').innerText = d.monthly;
                    document.getElementById('statReplace').innerText = d.replacements;
                    document.getElementById('statCancel').innerText = d.cancelled;
                }
            } catch(e) {}
        }

        // 🔍 Search
        let searchTimer;
        function debouncedSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(searchSales, 300);
        }

        async function searchSales() {
            const q = document.getElementById('searchQuery').value;
            const box = document.getElementById('searchResults');
            
            if (q.length < 2) { box.innerHTML = ''; return; }
            
            box.innerHTML = '<div style="padding:12px; color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Searching...</div>';
            
            try {
                const res = await fetch(`${API}?action=search_sale&query=${encodeURIComponent(q)}`);
                const d = await res.json();
                
                if (d.status !== 'success' || !d.data.length) {
                    box.innerHTML = '<div style="padding:12px; color:var(--text-muted);">No records found</div>';
                    return;
                }
                
                box.innerHTML = d.data.map(s => {
                    const returnBadge = s.already_returned 
                        ? `<span class="returned-badge">ALREADY ${s.return_info?.return_type || 'RETURNED'}</span>` 
                        : `<span style="color:var(--success); font-size:11px; font-weight:700;"><i class="fa-solid fa-circle"></i> Active</span>`;
                    
                    return `
                        <div class="sale-item" onclick="selectSale('${s.uid}','${s.vehicle_no}','${s.imei || ''}','${escapeStr(s.customer_name)}','${s.mobile_number || ''}')">
                            <div class="top">
                                <div>
                                    <div class="customer">${escHtml(s.customer_name)}</div>
                                    <div class="vehicle">${escHtml(s.vehicle_no)}</div>
                                </div>
                                ${returnBadge}
                            </div>
                            <div class="meta">
                                <i class="fa-solid fa-microchip"></i> ${s.imei || 'N/A'} &nbsp;|&nbsp; 
                                ${s.device_model || ''} &nbsp;|&nbsp;
                                ${s.selling_price ? '₹' + Number(s.selling_price).toLocaleString() : ''}
                            </div>
                        </div>
                    `;
                }).join('');
            } catch(e) {
                box.innerHTML = '<div style="padding:12px; color:var(--danger);">Error searching</div>';
            }
        }

        function escapeStr(s) { return (s || '').replace(/'/g, "\\'").replace(/"/g, '"'); }
        function escHtml(s) { return (s || '').replace(/</g, '<').replace(/>/g, '>'); }

        // 📝 Select Sale → Fill Form
        function selectSale(uid, vehicle, imei, customer, mobile) {
            document.getElementById('searchQuery').value = vehicle;
            document.getElementById('searchResults').innerHTML = '';
            
            // Fill RETURN form
            document.getElementById('saleUid').value = uid;
            document.getElementById('retVehicle').value = vehicle;
            document.getElementById('retImei').value = imei;
            document.getElementById('retCustomer').value = customer;
            document.getElementById('retMobile').value = mobile;
            document.getElementById('retVehicleDisplay').value = vehicle;
            document.getElementById('retImeiDisplay').value = imei;
            
            // Fill REPLACEMENT form
            document.getElementById('repUid').value = uid;
            document.getElementById('repVehicle').value = vehicle;
            document.getElementById('repImei').value = imei;
            document.getElementById('repCustomer').value = customer;
            document.getElementById('repMobile').value = mobile;
            document.getElementById('repVehicleDisplay').value = vehicle;
            document.getElementById('repImeiDisplay').value = imei;
            
            // Auto-fill vehicle for replacement
            document.getElementById('repNewVehicle').value = vehicle;
            
            // Switch to the active tab
            switchFormTab('RETURN');
            
            // Scroll to form
            document.querySelector('.glass-card:last-child').scrollIntoView({ behavior: 'smooth' });
        }

        // 🔄 Switch Form Tab
        function switchFormTab(tab) {
            document.querySelectorAll('#formTabs .tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('#formTabs .tab')[['RETURN','REPLACEMENT','HISTORY'].indexOf(tab)].classList.add('active');
            
            document.getElementById('formRETURN').classList.toggle('show', tab === 'RETURN');
            document.getElementById('formRETURN').classList.toggle('hidden-form', tab !== 'RETURN');
            document.getElementById('formREPLACEMENT').classList.toggle('show', tab === 'REPLACEMENT');
            document.getElementById('formREPLACEMENT').classList.toggle('hidden-form', tab !== 'REPLACEMENT');
            document.getElementById('formHISTORY').classList.toggle('show', tab === 'HISTORY');
            document.getElementById('formHISTORY').classList.toggle('hidden-form', tab !== 'HISTORY');
            
            if (tab === 'HISTORY') loadHistory();
        }

        // 💾 Process Return / Replacement
        async function processReturn(e) {
            e.preventDefault();
            
            const isReplacement = e.target.id === 'replaceForm';
            const prefix = isReplacement ? 'rep' : 'ret';
            
            const data = {
                action: 'process_return',
                sale_uid: document.getElementById(`${prefix}Uid`).value,
                vehicle: document.getElementById(isReplacement ? 'repVehicle' : 'retVehicle').value,
                imei: document.getElementById(isReplacement ? 'repImei' : 'retImei').value,
                customer: document.getElementById(`${prefix}Customer`).value,
                mobile: document.getElementById(`${prefix}Mobile`).value,
                return_reason: document.getElementById(`${prefix}Reason`).value,
                return_type: isReplacement ? 'REPLACEMENT' : 'RETURN',
                charge: document.getElementById(`${prefix}Charge`).value || 0,
                refund_amount: document.getElementById(`${prefix}Refund`).value || 0,
                notes: document.getElementById(`${prefix}Notes`).value || ''
            };
            
            if (isReplacement) {
                data.new_imei = document.getElementById('repNewImei').value;
                data.new_vehicle = document.getElementById('repNewVehicle').value;
            }
            
            if (!data.return_reason) {
                alert('Please select return reason');
                return;
            }
            
            if (!confirm(`Confirm ${isReplacement ? 'REPLACEMENT' : 'RETURN'} for ${data.customer} (${data.vehicle})?`)) return;
            
            const btn = e.target.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
            
            try {
                const fd = new FormData();
                Object.keys(data).forEach(k => fd.append(k, data[k]));
                
                const res = await fetch(API, { method: 'POST', body: fd });
                const result = await res.json();
                
                if (result.status === 'success') {
                    alert('✅ ' + result.message);
                    location.reload();
                } else {
                    alert('❌ Error: ' + (result.error || 'Unknown'));
                }
            } catch(e) {
                alert('❌ Network Error: ' + e.message);
            }
            
            btn.disabled = false;
            btn.innerHTML = isReplacement ? '<i class="fa-solid fa-arrows-rotate"></i> Process Replacement' : '<i class="fa-solid fa-rotate-left"></i> Process Return';
        }

        // 📋 Load Return History
        async function loadHistory() {
            const box = document.getElementById('historyList');
            try {
                const res = await fetch(`${API}?action=list_returns`);
                const d = await res.json();
                
                if (d.status !== 'success' || !d.data.length) {
                    box.innerHTML = '<div style="text-align:center; padding:30px; color:var(--text-muted);"><i class="fa-solid fa-inbox"></i><br>No return records yet</div>';
                    return;
                }
                
                box.innerHTML = d.data.map(r => {
                    const typeClass = `type-${r.return_type}`;
                    const isCancelled = r.return_status === 'CANCELLED';
                    
                    return `
                        <div class="return-item" style="${isCancelled ? 'opacity:0.5;' : ''}">
                            <div class="hdr">
                                <div class="veh">${escHtml(r.vehicle_no)}</div>
                                <div>
                                    <span class="return-type ${typeClass}">${r.return_type}</span>
                                    ${isCancelled ? '<span style="margin-left:6px; background:rgba(100,100,100,0.2); padding:2px 8px; border-radius:99px; font-size:10px; font-weight:700;">CANCELLED</span>' : ''}
                                </div>
                            </div>
                            <div style="font-size:13px; font-weight:600; margin-bottom:4px;">${escHtml(r.customer_name)}</div>
                            <div style="font-size:11px; color:var(--text-muted);">
                                📅 ${r.return_date || ''} &nbsp;|&nbsp;
                                📱 ${r.imei || 'N/A'}
                                ${r.new_imei ? '→ ' + r.new_imei : ''}
                                ${r.charge > 0 ? '| 💰 Charge: ₹' + Number(r.charge).toLocaleString() : ''}
                                ${r.refund_amount > 0 ? '| ↩️ Refund: ₹' + Number(r.refund_amount).toLocaleString() : ''}
                            </div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                                ${r.return_reason ? '📝 ' + r.return_reason : ''}
                                ${r.notes ? '— ' + r.notes : ''}
                            </div>
                            ${!isCancelled ? `<button onclick="cancelReturn(${r.id})" style="margin-top:8px; padding:6px 12px; background:rgba(100,100,100,0.2); border:1px solid var(--border); border-radius:8px; color:var(--text-muted); font-size:11px; cursor:pointer;"><i class="fa-solid fa-ban"></i> Cancel Return</button>` : ''}
                        </div>
                    `;
                }).join('');
            } catch(e) {
                box.innerHTML = '<div style="color:var(--danger);">Error loading history</div>';
            }
        }

        // ❌ Cancel Return
        async function cancelReturn(id) {
            if (!confirm('Are you sure you want to cancel this return? Device will be restored to SOLD status.')) return;
            
            try {
                const fd = new FormData();
                fd.append('action', 'cancel_return');
                fd.append('id', id);
                
                const res = await fetch(API, { method: 'POST', body: fd });
                const d = await res.json();
                
                if (d.status === 'success') {
                    alert('✅ Return cancelled');
                    loadHistory();
                    loadStats();
                } else {
                    alert('❌ ' + (d.error || 'Error'));
                }
            } catch(e) {
                alert('❌ ' + e.message);
            }
        }
    </script>
</body>
</html>
