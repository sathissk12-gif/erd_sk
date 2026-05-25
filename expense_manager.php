<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Financial Ledger | SK LOGIC</title>
    
    <!-- Ultra Modern UI -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Security -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="theme_engine.js"></script>

    <style>
        :root {
            --primary: #f43f5e;
            --primary-glow: rgba(244, 63, 94, 0.4);
            --secondary: #8b5cf6;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.7);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-dim: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: env(safe-area-inset-top, 0px);
            padding-bottom: 50px;
        }

        header {
            background: rgba(3, 7, 18, 0.8); backdrop-filter: blur(25px);
            padding: calc(12px + env(safe-area-inset-top, 0px)) 25px 18px; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 1000;
        }
        .header-title { font-size: 14px; font-weight: 800; text-transform: uppercase; color: var(--primary); letter-spacing: 1px; }

        .container { max-width: 500px; margin: 0 auto; padding: 25px; width: 100%; }

        .balance-card { 
            background: linear-gradient(135deg, #f43f5e, #e11d48); border-radius: 28px; padding: 30px; 
            margin-bottom: 30px; box-shadow: 0 20px 40px rgba(244, 63, 94, 0.2); 
            text-align: center; color: white;
        }
        .balance-label { font-size: 11px; font-weight: 800; text-transform: uppercase; opacity: 0.8; letter-spacing: 2px; }
        .balance-val { font-size: 38px; font-weight: 800; font-family: 'Outfit'; margin: 10px 0; display: block; }

        .glass-card { 
            background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 25px; 
            margin-bottom: 25px; backdrop-filter: blur(20px);
        }

        .input-group { margin-bottom: 18px; }
        .input-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px; }
        .input-field { 
            width: 100%; padding: 16px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); 
            border-radius: 16px; color: white; font-size: 15px; outline: none;
        }

        .btn-main {
            width: 100%; padding: 18px; border: none; border-radius: 20px; 
            background: linear-gradient(135deg, var(--primary), #e11d48);
            color: white; font-weight: 800; font-size: 15px; cursor: pointer; transition: 0.3s;
            box-shadow: 0 10px 25px rgba(244, 63, 94, 0.2);
        }

        .expense-item {
            background: var(--surface); border: 1px solid var(--border); border-radius: 18px; padding: 18px;
            margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;
        }
        .item-info div { font-size: 14px; font-weight: 700; }
        .item-info span { font-size: 10px; color: var(--text-dim); text-transform: uppercase; }
        .price-tag { color: var(--primary); font-weight: 800; font-family: 'Outfit'; font-size: 16px; }

    </style>
</head>
<body>

    <header>
        <div class="header-title" onclick="window.location='index.html'">
            <i class="fa-solid fa-chevron-left"></i> Expense Manager
        </div>
        <a href="index.html" style="color:white;"><i class="fa-solid fa-house"></i></a>
    </header>

    <div class="container">
        <!-- 💰 Total Outflow Card -->
        <div class="balance-card">
            <span class="balance-label">Monthly Expense</span>
            <span class="balance-val" id="totalExp">₹ 0</span>
        </div>

        <div class="glass-card">
            <div class="input-group">
                <label>Category</label>
                <select id="cat" class="input-field" style="appearance:none;">
                    <option value="STAFF SALARY">STAFF SALARY</option>
                    <option value="RENT">SHOP RENT</option>
                    <option value="EB BILL">EB / UTILITIES</option>
                    <option value="CONVEYANCE">PETROL / CONVEYANCE</option>
                    <option value="TEA / SNACKS">OFFICE REFRESHMENTS</option>
                    <option value="MARKETING">ADS / MARKETING</option>
                    <option value="OTHERS">OTHER MISC</option>
                </select>
            </div>
            <div class="input-group">
                <label>Amount (₹)</label>
                <input type="number" id="amt" class="input-field" placeholder="Enter Amount">
            </div>
            <div class="input-group">
                <label>Remark / Note</label>
                <input type="text" id="rem" class="input-field" placeholder="e.g. Month Rent Paid">
            </div>
            <button class="btn-main" onclick="addExpense()">Add Outward Entry</button>
        </div>

        <div id="log">
            <!-- List injected here -->
        </div>
    </div>

    <script>
        async function load() {
            const res = await fetch('api_reports.php?action=expense_log');
            const data = await res.json();
            
            document.getElementById('totalExp').innerText = '₹ ' + (data.total || 0).toLocaleString();
            
            const box = document.getElementById('log');
            box.innerHTML = (data.rows || []).map(it => `
                <div class="expense-item">
                    <div class="item-info">
                        <div>${it.category}</div>
                        <span>${it.date} • ${it.remark || 'N/A'}</span>
                    </div>
                    <div class="price-tag">₹${parseFloat(it.amount).toLocaleString()}</div>
                </div>
            `).join('');
        }

        async function addExpense() {
            const fd = new FormData();
            fd.append('action', 'add_expense');
            fd.append('category', document.getElementById('cat').value);
            fd.append('amount', document.getElementById('amt').value);
            fd.append('remark', document.getElementById('rem').value);

            const res = await fetch('api_reports.php', { method: 'POST', body: fd });
            const r = await res.json();
            if(r.status === 'ok') {
                document.getElementById('amt').value = '';
                document.getElementById('rem').value = '';
                load();
            }
        }

        window.onload = load;
    </script>
</body>
</html>
