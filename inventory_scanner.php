<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Stock Scanner | SK LOGIC</title>
    
    <!-- Ultra Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://unpkg.com/html5-qrcode"></script>

    <!-- 🔥 Security -->
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
        
        .status-chip { 
            background: rgba(16, 185, 129, 0.1); padding: 8px 16px; border-radius: 99px; font-size: 10px; font-weight: 800; color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .container { max-width: 500px; margin: 20px auto; padding: 0 20px; animation: slideUp 0.6s ease-out; }
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        .glass-card { 
            background: var(--surface); border: 1px solid var(--border); border-radius: 28px; padding: 25px; 
            backdrop-filter: blur(20px); box-shadow: 0 20px 40px rgba(0,0,0,0.4); margin-bottom: 20px;
        }

        .section-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; padding-left: 4px; }
        .input-field { 
            width: 100%; padding: 16px 20px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 15px; font-family: inherit; transition: 0.3s;
        }

        /* 📸 Camera UI */
        #reader { width: 100%; border-radius: 24px; overflow: hidden; background: #000; border: 2px solid var(--primary); display: none; margin-bottom: 20px; box-shadow: 0 0 30px var(--primary-glow); }
        
        .camera-btn { 
            width: 100%; padding: 18px; border: none; border-radius: 20px; background: var(--border); color: white;
            font-weight: 800; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 20px;
        }
        .camera-btn.active { background: #f43f5e; box-shadow: 0 10px 20px rgba(244, 63, 94, 0.3); }

        .scan-viewfinder { 
            background: rgba(15, 23, 42, 0.4); border: 2px dashed var(--border); border-radius: 24px; padding: 25px; position: relative;
            min-height: 150px; display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .scan-viewfinder.active { border-color: var(--primary); }
        
        .count-tag { position: absolute; top: 12px; right: 15px; background: var(--primary); color: white; padding: 4px 12px; border-radius: 99px; font-size: 10px; font-weight: 800; }

        textarea {
            width: 100%; min-height: 100px; background: transparent; border: none; color: white; font-family: 'Outfit'; font-size: 18px; font-weight: 700;
            text-align: center; resize: none; letter-spacing: 2px;
        }
        textarea::placeholder { color: var(--text-muted); font-size: 14px; letter-spacing: 0; }

        .btn-main {
            width: 100%; padding: 20px; border: none; border-radius: 22px; 
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white; font-weight: 800; font-size: 16px; cursor: pointer; transition: 0.3s;
            box-shadow: 0 15px 30px rgba(139, 92, 246, 0.3);
        }

        .loader { position: fixed; inset: 0; background: rgba(2,6,23,0.8); backdrop-filter: blur(10px); display: none; align-items: center; justify-content: center; z-index: 5000; }
        .spinner { width: 35px; height: 35px; border: 3px solid rgba(255,255,255,0.1); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div class="status-chip" id="slLabel">Next SL: #---</div>
    </header>

    <div class="container">
        
        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-truck-ramp-box"></i> Stock Origins</div>
            <div class="input-group">
                <label>Supplier / Vendor Name</label>
                <input type="text" id="supplier" class="input-field" placeholder="Enter Supplier Name" oninput="this.value = this.value.toUpperCase()">
            </div>
            <div class="input-group">
                <label>Device Model</label>
                <select id="model" class="input-field" style="appearance: none; cursor: pointer;"><option value="">Loading Models...</option></select>
            </div>
        </div>

        <button class="camera-btn" id="camBtn" onclick="toggleCam()">
            <i class="fa-solid fa-camera"></i> <span>Open Scanner</span>
        </button>

        <div id="reader"></div>

        <div class="glass-card" style="padding: 10px;">
            <div class="scan-viewfinder" id="viewfinder">
                <div class="count-tag" id="count">0 Units</div>
                <textarea id="list" placeholder="Scanned serials appear here..." oninput="updateCount()"></textarea>
            </div>
        </div>

        <div style="padding: 0 10px;">
            <button class="btn-main" onclick="submitStock()">
                <i class="fa-solid fa-cloud-arrow-up"></i> Save Current Batch
            </button>
        </div>

    </div>

    <div class="loader" id="loader"><div class="spinner"></div></div>

    <script>
        let models = [];
        let scanner = null;
        let isCam = false;

        async function init() {
            const res = await fetch('api_master_data.php?action=get_inventory_config');
            const data = await res.json();
            if(data.status === 'success') {
                models = data.models;
                const sel = document.getElementById('model');
                sel.innerHTML = '<option value="">Select Device Type</option>';
                models.forEach(m => {
                    let o = document.createElement('option');
                    o.value = m.device_model; o.innerText = m.device_model;
                    sel.appendChild(o);
                });
                document.getElementById('slLabel').innerText = `Next SL: #${data.next_sl}`;
            }
        }

        async function toggleCam() {
            const btn = document.getElementById('camBtn');
            const div = document.getElementById('reader');
            const vf = document.getElementById('viewfinder');
            
            if(!isCam) {
                div.style.display = 'block';
                vf.classList.add('active');
                btn.classList.add('active');
                btn.innerHTML = `<i class="fa-solid fa-stop-circle"></i> Stop Scanner`;
                startScanner();
            } else {
                stopScanner();
                div.style.display = 'none';
                vf.classList.remove('active');
                btn.classList.remove('active');
                btn.innerHTML = `<i class="fa-solid fa-camera"></i> Open Scanner`;
            }
            isCam = !isCam;
        }

        function startScanner() {
            scanner = new Html5Qrcode("reader");
            scanner.start({ facingMode: "environment" }, { fps: 15, qrbox: 250 }, (id) => {
                const list = document.getElementById('list');
                if(!list.value.includes(id)) {
                    if (window.navigator.vibrate) window.navigator.vibrate(50);
                    list.value += (list.value ? '\n' : '') + id;
                    updateCount();
                }
            });
        }

        function stopScanner() { if(scanner) scanner.stop().then(() => scanner.clear()); }
        
        function updateCount() { 
            const n = document.getElementById('list').value.split('\n').filter(x => x.trim()).length;
            document.getElementById('count').innerText = `${n} Units`;
        }

        async function submitStock() {
            const supplier = document.getElementById('supplier').value;
            const model = document.getElementById('model').value;
            const imeis = document.getElementById('list').value;
            if(!supplier || !model || !imeis) return alert("Please fill all fields!");

            const rate = models.find(m => m.device_model === model)?.rate || 0;
            document.getElementById('loader').style.display = 'flex';
            
            const fd = new FormData();
            fd.append('action', 'add_inventory_stock');
            fd.append('supplier', supplier); 
            fd.append('model', model); 
            fd.append('rate', rate); 
            fd.append('imeis', imeis);

            const res = await fetch('api_master_data.php', { method: 'POST', body: fd });
            const r = await res.json();
            document.getElementById('loader').style.display = 'none';
            
            if(r.status === 'success') {
                alert("🎉 Stock successfully registered!");
                location.reload();
            } else {
                alert("Error: " + r.error);
            }
        }

        window.onload = init;
    </script>
</body>
</html>
