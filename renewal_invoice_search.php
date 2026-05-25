<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Renewal Bill Archive | SK LOGIC</title>
    
    <!-- Ultra Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Security -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="theme_engine.js"></script>

    <style>
        :root {
            --primary: #ec4899;
            --primary-glow: rgba(236, 72, 153, 0.4);
            --secondary: #8b5cf6;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.7);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-dim: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: env(safe-area-inset-top, 0px);
        }

        header {
            background: rgba(3, 7, 18, 0.8); backdrop-filter: blur(25px);
            padding: calc(12px + env(safe-area-inset-top, 0px)) 25px 18px; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 1000;
        }
        .header-title { font-size: 16px; font-weight: 800; text-transform: uppercase; color: var(--primary); display: flex; align-items: center; gap: 10px; }

        .container { max-width: 600px; margin: 0 auto; padding: 25px; width: 100%; }

        .search-card { 
            background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 25px; 
            margin-bottom: 25px; backdrop-filter: blur(20px);
        }
        .search-area { position: relative; }
        .search-area i { position: absolute; left: 18px; top: 50%; translate: 0 -50%; color: var(--primary); }
        .search-input { 
            width: 100%; padding: 16px 16px 16px 48px; background: rgba(0,0,0,0.2); 
            border: 1px solid var(--border); border-radius: 16px; color: white; font-size: 16px; outline: none;
        }

        .result-item {
            background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 20px;
            margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;
            animation: slideUp 0.4s ease-out;
        }
        @keyframes slideUp { from { opacity:0; transform:translateY(15px); } to { opacity:1; transform:translateY(0); } }

        .bill-info div { font-size: 14px; font-weight: 700; }
        .bill-info span { font-size: 11px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.5px; }

        .action-btns { display: flex; gap: 10px; }
        .btn { 
            width: 44px; height: 44px; border-radius: 12px; border: none; color: white; cursor: pointer;
            display: flex; align-items: center; justify-content: center; font-size: 16px; transition: 0.2s;
        }
        .btn-view { background: var(--secondary); }
        .btn-wa { background: #25d366; }
        .btn:active { transform: scale(0.9); }

        .empty { text-align: center; padding: 50px; opacity: 0.5; font-size: 14px; }
    </style>
</head>
<body>

    <header>
        <div class="header-title" onclick="window.location='index.html'">
            <i class="fa-solid fa-chevron-left"></i> RENEWAL ARCHIVE
        </div>
        <a href="index.html" style="color:white; font-size:20px;"><i class="fa-solid fa-house"></i></a>
    </header>

    <div class="container">
        <div class="search-card">
            <div class="search-area">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="qInput" class="search-input" placeholder="Vehicle No / Customer Name" oninput="doSearch()">
            </div>
        </div>

        <div id="results">
            <div class="empty">Type vehicle number to find renewal bills</div>
        </div>
    </div>

    <script>
        let t;
        function doSearch() {
            const q = document.getElementById('qInput').value;
            if(q.length < 2) return;
            clearTimeout(t);
            t = setTimeout(async () => {
                const res = await fetch(`api_renewal_invoice.php?action=search&query=${encodeURIComponent(q)}`);
                const data = await res.json();
                render(data);
            }, 300);
        }

        function render(data) {
            const box = document.getElementById('results');
            if(!data.length) { box.innerHTML = '<div class="empty">No renewal records found.</div>'; return; }
            
            box.innerHTML = data.map(it => {
                const shareKey = it.uid || it.invoice_num;
                const viewUrl = `renewal_invoice.php?${it.uid ? 'uid=' : 'invoice_no='}${shareKey}`;
                
                const waMsg = `Dear ${it.customer_name},\n\nPayment Received for Vehicle ${it.vehicle_num}. Your Renewal Bill (${it.invoice_num}) is ready.\n\nView Bill: https://erd.traxengps.in/billing_app/${viewUrl}`;
                const waUrl = `https://wa.me/?text=${encodeURIComponent(waMsg)}`;

                return `
                    <div class="result-item">
                        <div class="bill-info">
                            <div>${it.vehicle_num}</div>
                            <span>${it.customer_name} • ${it.date}</span><br>
                            <span style="color:var(--primary); font-weight:800;">${it.invoice_num}</span>
                        </div>
                        <div class="action-btns">
                            <button class="btn btn-view" onclick="window.location.href='${viewUrl}'"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn btn-wa" onclick="window.open('${waUrl}', '_blank')"><i class="fa-brands fa-whatsapp"></i></button>
                        </div>
                    </div>
                `;
            }).join('');
        }
    </script>
</body>
</html>
