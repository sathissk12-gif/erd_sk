<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Cloud Master | SK LOGIC</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #8b5cf6; --primary-glow: rgba(139, 92, 246, 0.4);
            --bg: #030712; --surface: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.08); --text: #ffffff; --text-muted: #94a3b8;
        }
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text); min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .glass-card { 
            background: var(--surface); border: 1px solid var(--border); border-radius: 32px; padding: 40px; 
            backdrop-filter: blur(30px); box-shadow: 0 40px 80px rgba(0,0,0,0.5); 
            width: 100%; max-width: 900px; animation: slideUp 0.6s ease-out;
        }
        @keyframes slideUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .tabs { display: flex; gap: 10px; margin-bottom: 30px; background: rgba(0,0,0,0.2); padding: 5px; border-radius: 18px; }
        .tab { flex: 1; padding: 12px; border-radius: 14px; text-align: center; cursor: pointer; font-weight: 700; transition: 0.3s; color: var(--text-muted); }
        .tab.active { background: var(--primary); color: white; box-shadow: 0 10px 20px var(--primary-glow); }
        .main-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 40px; }
        h2 { font-size: 26px; font-weight: 800; margin-bottom: 5px; letter-spacing: -0.5px; }
        p { color: var(--text-muted); font-size: 14px; margin-bottom: 30px; }
        .field { margin-bottom: 25px; }
        .field label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1.5px; }
        .input-field { 
            width: 100%; padding: 18px 20px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 16px; transition: 0.3s; outline: none;
        }
        .input-field:focus { border-color: var(--primary); background: rgba(15, 23, 42, 0.82); box-shadow: 0 0 20px var(--primary-glow); }
        .btn-main {
            width: 100%; padding: 20px; border: none; border-radius: 20px; 
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white; font-weight: 800; font-size: 16px; cursor: pointer; transition: 0.3s;
            box-shadow: 0 15px 30px rgba(139, 92, 246, 0.3);
        }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; color: var(--text-muted); padding: 10px; border-bottom: 1px solid var(--border); }
        td { padding: 15px 10px; font-size: 14px; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .back-link { display: block; text-align: center; margin-top: 25px; color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 700; }
        @media (max-width: 800px) { .main-grid { grid-template-columns: 1fr; } .glass-card { padding: 25px; } }
    </style>
</head>
<body>

    <div class="glass-card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h2 id="headerTitle">Cloud Master Data</h2>
                <p id="headerSub">Configure models and software pricing</p>
            </div>
            <a href="master_dealer.php" class="btn-main" style="width:auto; padding:12px 20px; font-size:12px; margin:0;"><i class="fa-solid fa-users"></i> Dealer Suite</a>
        </div>
        
        <div class="tabs">
            <div class="tab active" onclick="switchTab('DEVICE', this)"><i class="fa-solid fa-microchip"></i> Hardware</div>
            <div class="tab" onclick="switchTab('SOFTWARE', this)"><i class="fa-solid fa-code"></i> Software</div>
            <div class="tab" onclick="switchTab('TOOL', this)"><i class="fa-solid fa-screwdriver-wrench"></i> Tools</div>
        </div>

        <div class="main-grid">
            <form id="masterForm">
                <div class="field">
                    <label id="labelName">Model Identifier</label>
                    <input type="text" id="name" class="input-field" placeholder="e.g. GV300 / BASIC" required oninput="this.value = this.value.toUpperCase()">
                </div>
                <div class="field">
                    <label>Standard Cost (₹)</label>
                    <input type="number" id="rate" class="input-field" placeholder="0" required>
                </div>
                <button type="submit" id="saveBtn" class="btn-main">Sync to Cloud</button>
                <a href="index.html" class="back-link">← Main Dashboard</a>
            </form>

            <div style="max-height: 450px; overflow-y: auto; background: rgba(0,0,0,0.2); border-radius: 24px; padding: 20px;">
                <table>
                    <thead>
                        <tr>
                            <th id="thName">Identifier</th>
                            <th style="text-align:right">Rate</th>
                        </tr>
                    </thead>
                    <tbody id="masterList">
                        <tr><td colspan="2" style="text-align:center; padding:30px; opacity:0.5;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let currentType = 'DEVICE';
        let fullData = { devices: [], software: [], tools: [] };

        async function loadData() {
            const res = await fetch('api_master_data.php?action=get_inventory_config').then(r => r.json());
            const resSw = await fetch('api_master_data.php?action=get_software_list').then(r => r.json());
            
            fullData.devices = res.models || [];
            fullData.software = resSw.map(s => ({ device_model: s.name, rate: 0 })); // Note: software rate needs to be fetched correctly if available
            
            // Re-fetch software with rates if possible, or just use price_master
            renderList();
        }

        async function fetchEverything() {
            // Get all from price_master
            const res = await fetch('api_dealers_v2.php?action=get_masters').then(r => r.json());
            // Wait, api_dealers_v2.php get_masters only returns software names.
            // Let's use a better approach: api_master_data.php get_data for price_master
            const resMaster = await fetch('api_master_data.php?action=get_data&table=price_master').then(r => r.json());
            if(resMaster.data) {
                fullData.devices = resMaster.data.filter(i => i.type === 'DEVICE');
                fullData.software = resMaster.data.filter(i => i.type === 'SOFTWARE');
                fullData.tools = resMaster.data.filter(i => i.type === 'TOOL');
                renderList();
            }
        }

        function switchTab(type, el) {
            currentType = type;
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            
            let title = "Hardware Master";
            let label = "Model Identifier";
            let th = "Model";
            let placeholder = "e.g. GV300, OB22";

            if(type === 'SOFTWARE') {
                title = "Software Master";
                label = "Software Package Name";
                th = "Package";
                placeholder = "e.g. BASIC, PRO, RENEWAL";
            } else if(type === 'TOOL') {
                title = "Tool Master";
                label = "Tool Name";
                th = "Tool";
                placeholder = "e.g. SIM CARD, RELAY, WRENCH";
            }

            document.getElementById('headerTitle').innerText = title;
            document.getElementById('labelName').innerText = label;
            document.getElementById('thName').innerText = th;
            document.getElementById('name').placeholder = placeholder;
            
            renderList();
        }

        function renderList() {
            const list = document.getElementById('masterList');
            let data = [];
            if(currentType === 'DEVICE') data = fullData.devices;
            else if(currentType === 'SOFTWARE') data = fullData.software;
            else data = fullData.tools;
            
            if (data.length > 0) {
                list.innerHTML = data.map(m => `
                    <tr>
                        <td style="font-weight:700;">${m.name || m.device_model}</td>
                        <td style="text-align:right; color:var(--primary); font-weight:800;">₹ ${parseFloat(m.cost || m.rate || 0).toLocaleString()}</td>
                    </tr>
                `).join('');
            } else {
                list.innerHTML = '<tr><td colspan="2" style="text-align:center; padding:30px; opacity:0.5;">No records in this category</td></tr>';
            }
        }

        document.getElementById('masterForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('saveBtn');
            btn.disabled = true; btn.textContent = 'Syncing...';

            const fd = new FormData();
            fd.append('action', 'update_price_master');
            fd.append('model', document.getElementById('name').value);
            fd.append('rate', document.getElementById('rate').value);
            fd.append('type', currentType);

            try {
                const res = await fetch('api_master_data.php', { method: 'POST', body: fd }).then(r => r.json());
                if (res.status === 'success') {
                    document.getElementById('name').value = "";
                    document.getElementById('rate').value = "";
                    fetchEverything();
                } else { alert('Error: ' + res.error); }
            } catch (err) { alert('Network Error'); }
            
            btn.disabled = false; btn.textContent = 'Sync to Cloud';
        };

        window.onload = () => {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab === 'SOFTWARE') switchTab('SOFTWARE', document.querySelectorAll('.tab')[1]);
            else if (tab === 'TOOL') switchTab('TOOL', document.querySelectorAll('.tab')[2]);
            
            fetchEverything();
        };
    </script>
</body>
</html>
