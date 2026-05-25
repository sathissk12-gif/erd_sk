<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Stock Transfer | SK LOGIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
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
            --warn: #f59e0b;
            --danger: #ef4444;
        }
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text);
            min-height: 100vh;
            padding: 20px;
            padding-bottom: 100px;
        }
        .panel {
            max-width: 600px; margin: 0 auto;
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 24px; padding: 24px; backdrop-filter: blur(20px);
        }
        .panel-header-inline { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .panel-header-inline h2 { margin:0; font-size:20px; }
        .btn-secondary-sm {
            background: var(--card-base); border: 1px solid var(--border); color: var(--text);
            padding: 10px 16px; border-radius: 14px; font-size: 13px; font-weight:700; cursor:pointer;
        }
        .stock-tabs {
            display: flex; gap: 8px; margin-bottom: 15px;
            background: rgba(255,255,255,0.05); padding: 5px; border-radius: 12px;
        }
        .stock-tab {
            flex: 1; text-align: center; padding: 10px; font-size: 12px;
            font-weight: 700; border-radius: 8px; cursor: pointer; transition: 0.3s; color: var(--text-muted);
        }
        .stock-tab.active { background: var(--primary); color: white; box-shadow: 0 4px 10px rgba(99,102,241,0.3); }
        .field { margin-bottom: 12px; }
        .field label { display:block; font-size:11px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px; }
        .field select, .field input {
            width:100%; padding:12px; border-radius:12px; border:1px solid var(--border);
            background:var(--card-base); color:white; font-size:14px;
        }
        .field select option { background:#1e293b; }
        .btn-primary-large {
            width:100%; padding:16px; border:none; border-radius:16px;
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            color:white; font-size:15px; font-weight:800; cursor:pointer; transition:0.3s;
        }
        .btn-primary-large:active { transform:scale(0.97); }
        .st-history-item {
            display:grid; grid-template-columns: auto 1fr auto; gap:12px;
            padding:14px 16px; background:var(--card-base); border-radius:14px;
            margin-bottom:10px; border:1px solid var(--border); align-items:center;
        }
        .st-dir-badge { padding:4px 10px; border-radius:8px; font-size:10px; font-weight:800; white-space:nowrap; }
        .st-dir-slm-to-erd { background:rgba(139,92,246,0.15); color:#8b5cf6; }
        .st-dir-erd-to-slm { background:rgba(6,182,212,0.15); color:#06b6d4; }
        .st-qty-badge {
            font-size:16px; font-weight:800; padding:4px 12px; border-radius:10px;
            background:rgba(16,185,129,0.15); color:#10b981;
        }
        .loading-shimmer { text-align:center; padding:20px; color:var(--text-muted); }
        .back-link {
            display:inline-flex; align-items:center; gap:8px; padding:12px 18px;
            background:var(--card-base); border:1px solid var(--border); border-radius:14px;
            color:var(--text); text-decoration:none; font-weight:600; font-size:13px; margin-bottom:20px;
        }
    </style>
</head>
<body>
    <a href="index.html" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <div class="panel">
        <div class="panel-header-inline">
            <h2><i class="fa-solid fa-arrows-left-right"></i> Stock Transfer</h2>
            <button class="btn-secondary-sm" onclick="loadStockTransfer()"><i class="fa-solid fa-rotate"></i> Refresh</button>
        </div>

        <!-- Tabs -->
        <div class="stock-tabs">
            <div id="stTabTransfer" class="stock-tab active" onclick="showStTab('transfer')">🔄 Transfer</div>
            <div id="stTabHistory" class="stock-tab" onclick="showStTab('history')">📋 History</div>
        </div>

        <!-- Transfer Panel -->
        <div id="stTransferPanel">
            <!-- Direction -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:15px;">
                <div id="stDirErdToSlm" class="stock-tab active" style="font-size:11px; padding:12px;" onclick="setStDirection('ERD_TO_SLM')">
                    <i class="fa-solid fa-arrow-left" style="color:#06b6d4;"></i> ERD → SLM
                </div>
                <div id="stDirSlmToErd" class="stock-tab" style="font-size:11px; padding:12px;" onclick="setStDirection('SLM_TO_ERD')">
                    <i class="fa-solid fa-arrow-right" style="color:#8b5cf6;"></i> SLM → ERD
                </div>
            </div>

            <!-- Type -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:15px;">
                <div id="stTypeHw" class="stock-tab active" onclick="setStType('HARDWARE')">📦 Hardware</div>
                <div id="stTypeSw" class="stock-tab" onclick="setStType('SOFTWARE')">💿 Software</div>
            </div>

            <!-- Source -->
            <div class="field">
                <label id="stSourceLabel">📍 Available in ERD</label>
                <select id="stSourceStock" onchange="updateStTargetQty()">
                    <option value="">Select item...</option>
                </select>
            </div>

            <!-- IMEI List (for HARDWARE) -->
            <div id="stImeiSection" style="display:none; margin-bottom:12px;">
                <label style="font-size:11px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px; display:block;">📱 Select IMEIs</label>
                <div id="stImeiList" style="background:var(--card-base); border:1px solid var(--border); border-radius:12px; padding:12px; max-height:200px; overflow-y:auto;">
                    <div style="color:var(--text-muted); font-size:13px;">Select a model to see IMEIs...</div>
                </div>
                <small style="color:var(--text-muted);">Selected: <strong id="stImeiCount">0</strong></small>
            </div>

            <div class="field">
                <label>🔢 Quantity</label>
                <input type="number" id="stQty" min="1" value="1" style="width:100%; padding:12px; border-radius:12px; border:1px solid var(--border); background:var(--card-base); color:white; font-size:15px; font-weight:700;">
            </div>

            <div class="field">
                <label>📝 Remark</label>
                <input type="text" id="stRemark" placeholder="e.g. Stock relocation" style="width:100%; padding:12px; border-radius:12px; border:1px solid var(--border); background:var(--card-base); color:white; font-size:13px;">
            </div>

            <div id="stSourceInfo" style="background:rgba(139,92,246,0.08); border:1px solid rgba(139,92,246,0.2); border-radius:12px; padding:12px; margin-bottom:15px; display:none;">
                <small style="color:var(--text-muted);">Available: <strong id="stAvailQty">0</strong> units</small>
            </div>

            <button class="btn-primary-large" onclick="executeStockTransfer()">
                <i class="fa-solid fa-arrows-left-right"></i> <span id="stBtnText">Transfer to SLM</span>
            </button>
            <div id="stTransferStatus" style="margin-top:15px; display:none;"></div>
        </div>

        <!-- History -->
        <div id="stHistoryPanel" style="display:none;">
            <div id="stHistoryList">
                <div class="loading-shimmer"><i class="fa-solid fa-spinner fa-spin"></i> Loading history...</div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = 'api_master_data.php';
        let stDirection = 'ERD_TO_SLM';
        let stType = 'HARDWARE';
        let stCache = { erd_stock: [], slm_stock: [], history: [] };

        async function callApi(action, data = {}) {
            const params = new URLSearchParams({ action, ...data });
            try {
                const r = await fetch(API_URL + '?' + params.toString());
                return await r.json();
            } catch(e) {
                return { ok: false, error: 'Network error: ' + e.message };
            }
        }

        function showStTab(tab) {
            document.getElementById('stTransferPanel').style.display = tab === 'transfer' ? 'block' : 'none';
            document.getElementById('stHistoryPanel').style.display = tab === 'history' ? 'block' : 'none';
            document.getElementById('stTabTransfer').classList.toggle('active', tab === 'transfer');
            document.getElementById('stTabHistory').classList.toggle('active', tab === 'history');
            if (tab === 'history' && !stCache.history.length) loadStockTransfer();
        }

        function setStDirection(dir) {
            stDirection = dir;
            document.getElementById('stDirErdToSlm').classList.toggle('active', dir === 'ERD_TO_SLM');
            document.getElementById('stDirSlmToErd').classList.toggle('active', dir === 'SLM_TO_ERD');
            document.getElementById('stBtnText').innerText = dir === 'ERD_TO_SLM' ? 'Transfer to SLM' : 'Transfer from SLM';
            populateStStockList();
        }

        function setStType(type) {
            stType = type;
            document.getElementById('stTypeHw').classList.toggle('active', type === 'HARDWARE');
            document.getElementById('stTypeSw').classList.toggle('active', type === 'SOFTWARE');
            populateStStockList();
        }

        async function loadStockTransfer() {
            const statusEl = document.getElementById('stSourceStock');
            statusEl.innerHTML = '<option value="">⏳ Loading...</option>';
            const r = await callApi('stock-transfer-init');
            console.log('[StockTransfer] API response:', JSON.stringify(r).substring(0, 500));
            
            if (r.ok || r.erd_stock || r.slm_stock) {
                stCache = r;
                populateStStockList();
                renderStHistory(r.history || []);
            } else {
                console.error('[StockTransfer] API failed:', r);
                statusEl.innerHTML = '<option value="">⚠️ Error: ' + (r.error || 'No data') + '</option>';
                document.getElementById('stHistoryList').innerHTML = 
                    '<div style="text-align:center;padding:40px;color:var(--text-muted);">' +
                    '<i class="fa-solid fa-exclamation-triangle" style="font-size:40px;display:block;margin-bottom:15px;"></i>' +
                    'Failed to load stock data.<br><small>' + (r.error || '') + '</small></div>';
            }
        }

        function populateStStockList() {
            const sel = document.getElementById('stSourceStock');
            const isErdToSlm = stDirection === 'ERD_TO_SLM';
            document.getElementById('stSourceLabel').innerText = isErdToSlm ? '📍 Available in ERD' : '📍 Available in SLM';
            const stock = isErdToSlm ? stCache.erd_stock : stCache.slm_stock;
            const filtered = stock.filter(i => i.type === stType);
            
            if (!filtered.length) {
                sel.innerHTML = '<option value="">🔍 No ' + stType.toLowerCase() + ' items available</option>';
                updateStTargetQty();
                return;
            }
            
            let html = '<option value="">📋 Select item...</option>';
            filtered.forEach(i => {
                html += '<option value="' + i.name.replace(/'/g, "'") + '" data-qty="' + i.qty + '">' 
                    + i.name + ' (' + i.qty + ' available)</option>';
            });
            sel.innerHTML = html;
            console.log('[StockTransfer] Dropdown:', filtered.length, stType, 'items');
            updateStTargetQty();
        }

        // 📱 IMEI selection state
        let stSelectedImeis = [];

        function updateStTargetQty() {
            const sel = document.getElementById('stSourceStock');
            const opt = sel.options[sel.selectedIndex];
            let qty = 0;
            let itemName = '';
            
            if (opt && opt.value) {
                itemName = opt.value;
                if (opt.dataset && opt.dataset.qty !== undefined) {
                    qty = parseInt(opt.dataset.qty) || 0;
                } else {
                    const match = opt.text.match(/\((\d+)\s*available\)/);
                    qty = match ? parseInt(match[1]) : 0;
                }
            }
            
            document.getElementById('stAvailQty').innerText = qty;
            document.getElementById('stSourceInfo').style.display = qty > 0 ? 'block' : 'none';
            
            // Show IMEI list for hardware items
            const imeiSection = document.getElementById('stImeiSection');
            if (itemName && stType === 'HARDWARE' && qty > 0) {
                loadImeisForModel(itemName);
            } else {
                imeiSection.style.display = 'none';
                stSelectedImeis = [];
                updateImeiCount();
            }
            
            const inp = document.getElementById('stQty');
            inp.max = qty || 1;
            inp.min = 1;
            if (qty > 0) {
                inp.value = Math.min(stSelectedImeis.length > 0 ? stSelectedImeis.length : (parseInt(inp.value) || 1), qty);
            } else {
                inp.value = 1;
            }
            
            console.log('[StockTransfer] Selected:', itemName, '| Avail:', qty);
        }

        async function loadImeisForModel(model) {
            const list = document.getElementById('stImeiList');
            const branch = stDirection === 'ERD_TO_SLM' ? 'ERD' : 'SLM';
            list.innerHTML = '<div style="color:var(--text-muted); font-size:13px;"><i class="fa-solid fa-spinner fa-spin"></i> Loading IMEIs...</div>';
            
            const r = await callApi('get_imeis_for_model', { model: model, branch: branch });
            if (!r.ok || !r.imeis || !r.imeis.length) {
                list.innerHTML = '<div style="color:var(--text-muted); font-size:13px;">No IMEI records found for this model</div>';
                document.getElementById('stImeiSection').style.display = 'none';
                return;
            }
            
            document.getElementById('stImeiSection').style.display = 'block';
            stSelectedImeis = [];
            
            let html = '';
            r.imeis.forEach((imei, idx) => {
                const shortImei = imei.imei.length > 12 ? '...' + imei.imei.slice(-12) : imei.imei;
                html += '<label style="display:flex; align-items:center; gap:8px; padding:6px 0; border-bottom:1px solid var(--border); font-size:12px; cursor:pointer;">' +
                    '<input type="checkbox" class="st-imei-cb" value="' + imei.imei + '" data-idx="' + idx + '" style="width:18px;height:18px;accent-color:#8b5cf6;">' +
                    '<span>' + shortImei + '</span></label>';
            });
            list.innerHTML = html;
            
            // Attach change listener to all checkboxes
            document.querySelectorAll('.st-imei-cb').forEach(cb => {
                cb.addEventListener('change', function() {
                    if (this.checked) {
                        stSelectedImeis.push(this.value);
                    } else {
                        stSelectedImeis = stSelectedImeis.filter(i => i !== this.value);
                    }
                    updateImeiCount();
                    // Auto-update quantity
                    const qtyInput = document.getElementById('stQty');
                    qtyInput.value = stSelectedImeis.length > 0 ? stSelectedImeis.length : 1;
                });
            });
            
            updateImeiCount();
        }

        function updateImeiCount() {
            document.getElementById('stImeiCount').innerText = stSelectedImeis.length;
        }

        async function executeStockTransfer() {
            const sel = document.getElementById('stSourceStock');
            const itemName = sel.value;
            const qty = parseInt(document.getElementById('stQty').value);
            const remark = document.getElementById('stRemark').value.trim();
            if (!itemName || qty <= 0) { alert('⚠️ Select an item and enter valid quantity'); return; }
            
            const opt = sel.options[sel.selectedIndex];
            const avail = parseInt(opt.dataset.qty || '0');
            if (qty > avail) { alert('⚠️ Only ' + avail + ' units available'); return; }
            
            const dirText = stDirection === 'ERD_TO_SLM' ? 'ERD → SLM' : 'SLM → ERD';
            if (!confirm('🔄 Transfer ' + qty + ' x ' + itemName + '\n' + dirText + '?')) return;

            const statusDiv = document.getElementById('stTransferStatus');
            statusDiv.style.display = 'block';
            statusDiv.innerHTML = '<div style="text-align:center; padding:15px;"><i class="fa-solid fa-spinner fa-spin"></i> Transferring...</div>';

            // Build IMEI string if hardware type
            const imeisParam = (stType === 'HARDWARE' && stSelectedImeis.length > 0) ? stSelectedImeis.join(',') : '';

            const r = await callApi('stock-transfer-execute', {
                direction: stDirection,
                item_name: itemName,
                item_type: stType,
                qty: qty,
                remark: remark,
                imeis: imeisParam
            });

            if (r.ok) {
                statusDiv.innerHTML = '<div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); border-radius:12px; padding:15px; text-align:center;">' +
                    '<i class="fa-solid fa-circle-check" style="color:#10b981; font-size:24px; display:block; margin-bottom:8px;"></i>' +
                    '<strong style="color:#10b981;">✅ ' + (r.message || 'Transfer success') + '</strong></div>';
                document.getElementById('stQty').value = '1';
                document.getElementById('stRemark').value = '';
                loadStockTransfer();
            } else {
                statusDiv.innerHTML = '<div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); border-radius:12px; padding:15px; text-align:center;">' +
                    '<i class="fa-solid fa-circle-exclamation" style="color:#ef4444; font-size:24px; display:block; margin-bottom:8px;"></i>' +
                    '<strong style="color:#ef4444;">❌ ' + (r.error || 'Transfer failed') + '</strong></div>';
            }
            setTimeout(() => { statusDiv.style.display = 'none'; }, 6000);
        }

        function renderStHistory(history) {
            const list = document.getElementById('stHistoryList');
            if (!history || !history.length) {
                list.innerHTML = '<div style="text-align:center; padding:40px; color:var(--text-muted);">' +
                    '<i class="fa-solid fa-clock-rotate-left" style="font-size:40px; display:block; margin-bottom:15px;"></i>' +
                    'No transfer history yet.</div>';
                return;
            }
            list.innerHTML = history.map(h => {
                const dir = h.direction === 'ERD_TO_SLM' ? 'ERD → SLM' : 'SLM → ERD';
                const dirClass = h.direction === 'ERD_TO_SLM' ? 'st-dir-erd-to-slm' : 'st-dir-slm-to-erd';
                return '<div class="st-history-item">' +
                    '<div><span class="st-dir-badge ' + dirClass + '">' + dir + '</span></div>' +
                    '<div><div style="font-size:13px; font-weight:700;">' + h.item_name + 
                    ' <small style="color:var(--text-muted);font-weight:400;">(' + h.item_type + ')</small></div>' +
                    '<div style="font-size:11px; color:var(--text-muted);">' + (h.created_at || '') + 
                    (h.remark ? ' · ' + h.remark : '') + '</div></div>' +
                    '<div class="st-qty-badge">+' + parseInt(h.qty || 0) + '</div></div>';
            }).join('');
        }

        // 🔁 Auto-load & attach dropdown events
        function initStockTransfer() {
            loadStockTransfer();
            
            // Attach reliable dropdown change handler
            const sel = document.getElementById('stSourceStock');
            if (sel) {
                // Remove old onchange to avoid double-fire, re-add via listener
                sel.removeAttribute('onchange');
                sel.addEventListener('change', function(e) {
                    console.log('[StockTransfer] Dropdown changed to:', this.value);
                    updateStTargetQty();
                });
                // Also handle blur (when user closes the dropdown on mobile)
                sel.addEventListener('blur', function() {
                    setTimeout(updateStTargetQty, 200);
                });
            }
        }

        // Run after page fully loads
        if (document.readyState === 'complete') {
            initStockTransfer();
        } else {
            window.addEventListener('load', initStockTransfer);
        }
    </script>
</body>
</html>
