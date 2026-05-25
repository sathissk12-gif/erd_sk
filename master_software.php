<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Stock Master | SK LOGIC</title>
    
    <!-- Ultra Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- 🔥 Security -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>

    <style>
        :root {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.4);
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-muted: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: calc(20px + env(safe-area-inset-top, 0px)) 20px 20px;
        }

        .glass-card { 
            background: var(--surface); border: 1px solid var(--border); border-radius: 32px; padding: 40px; 
            backdrop-filter: blur(30px); box-shadow: 0 40px 80px rgba(0,0,0,0.5); 
            width: 100%; max-width: 440px; animation: slideUp 0.6s ease-out;
        }
        @keyframes slideUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }

        h2 { font-size: 26px; font-weight: 800; text-align: center; margin-bottom: 30px; letter-spacing: -0.5px; }

        .input-group { margin-bottom: 25px; }
        .input-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1.5px; }
        .input-field { 
            width: 100%; padding: 18px 20px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 16px; transition: 0.3s;
        }
        .input-field:focus { border-color: var(--primary); background: rgba(15, 23, 42, 0.82); box-shadow: 0 0 20px var(--primary-glow); }

        .btn-main {
            width: 100%; padding: 20px; border: none; border-radius: 20px; 
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white; font-weight: 800; font-size: 16px; cursor: pointer; transition: 0.3s;
            box-shadow: 0 15px 30px rgba(139, 92, 246, 0.3); margin-top: 10px;
        }
        .btn-main:disabled { opacity: 0.5; cursor: not-allowed; }

        .back-link { display: block; text-align: center; margin-top: 25px; color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 700; }
    </style>
</head>
<body>

    <div class="glass-card">
        <h2>Stock Injection</h2>
        <form id="swForm">
            <div class="input-group">
                <label>Select Package / Item</label>
                <select id="swSelect" class="input-field" style="appearance: none; cursor: pointer;">
                    <option value="">Syncing Master Data...</option>
                </select>
            </div>
            <div class="input-group">
                <label>Quantity to Inject</label>
                <input type="number" id="qty" class="input-field" placeholder="0" min="1" required>
            </div>
            <button type="submit" id="saveBtn" class="btn-main">Update Stock Cloud</button>
        </form>
        <a href="index.html" class="back-link">← Return to Console</a>
    </div>

    <script>
        async function load() {
            try {
                const res = await fetch('api_master_data.php?action=get_software_list');
                const list = await res.json();
                const sel = document.getElementById('swSelect');
                sel.innerHTML = '<option value="">-- Choose Platform --</option>';
                list.forEach(item => {
                    const o = document.createElement('option');
                    const name = item.name || item;
                    const type = (item.type || 'SOFTWARE').toUpperCase();
                    o.value = name;
                    o.text = `${name} (${type})`;
                    o.dataset.type = type;
                    sel.appendChild(o);
                });
            } catch (err) { alert('Sync Failed'); }
        }

        document.getElementById('swForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('saveBtn');
            btn.disabled = true; btn.textContent = 'Syncing...';

            const fd = new FormData();
            const selected = document.getElementById('swSelect').selectedOptions[0];
            fd.append('action', 'add_software_stock');
            fd.append('name', document.getElementById('swSelect').value);
            fd.append('item_type', selected?.dataset.type || 'SOFTWARE');
            fd.append('qty', document.getElementById('qty').value);

            try {
                const res = await fetch('api_master_data.php', { method: 'POST', body: fd });
                const r = await res.json();
                if (r.status === 'success') {
                    alert('🎉 Stock Updated Successfully!');
                    document.getElementById('swForm').reset();
                } else { alert('Error: ' + r.error); }
            } catch (err) { alert('Network Error'); }
            
            btn.disabled = false; btn.textContent = 'Update Stock Cloud';
        };

        load();
    </script>
</body>
</html>
