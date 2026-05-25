<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Quotation Console | SK LOGIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="theme_engine.js"></script>
    <style>
        :root {
            --primary: #8b5cf6;
            --secondary: #06b6d4;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-muted: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text);
            min-height: 100vh;
            padding-bottom: 50px;
        }

        header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(3, 7, 18, 0.7); backdrop-filter: blur(25px);
            padding: 15px 25px; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .back-link { text-decoration: none; color: white; display: flex; align-items: center; gap: 10px; font-weight: 700; }

        .container { max-width: 500px; margin: 20px auto; padding: 0 20px; }

        .glass-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 28px; padding: 25px;
            backdrop-filter: blur(20px); box-shadow: 0 20px 40px rgba(0,0,0,0.4); margin-bottom: 20px;
        }

        .section-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; }
        .input-field {
            width: 100%; padding: 16px 20px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 15px; outline: none;
        }

        .dual-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

        .btn-main {
            width: 100%; padding: 20px; border: none; border-radius: 22px;
            background: linear-gradient(135deg, var(--secondary), #0891b2);
            color: white; font-weight: 800; font-size: 16px; cursor: pointer; transition: 0.3s;
            box-shadow: 0 15px 30px rgba(6, 182, 212, 0.3);
        }
    </style>
</head>
<body>

    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div style="font-weight: 800; font-size: 14px;">NEW QUOTATION</div>
    </header>

    <div class="container">
        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-user"></i> Customer Info</div>
            <div class="input-group">
                <label>Customer Name</label>
                <input type="text" id="customer" class="input-field" placeholder="Full Name">
            </div>
            <div class="dual-grid">
                <div class="input-group"><label>Mobile</label><input type="tel" id="mobile" class="input-field"></div>
                <div class="input-group"><label>Location</label><input type="text" id="location" class="input-field"></div>
            </div>
        </div>

        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-box"></i> Device & Software</div>
            <div class="input-group">
                <label>Device Model</label>
                <input type="text" id="model" class="input-field" placeholder="eg: AIS 140 / Basic Tracker">
            </div>
            <div class="dual-grid">
                <div class="input-group">
                    <label>Software</label>
                    <select id="software" class="input-field" style="appearance: none;"><option value="GPS Software">GPS Software</option><option value="Tracking App">Tracking App</option></select>
                </div>
                <div class="input-group">
                    <label>Duration</label>
                    <select id="duration" class="input-field" style="appearance: none;"><option value="1_year">1 Year</option><option value="2_year">2 Years</option></select>
                </div>
            </div>
            <div class="dual-grid">
                <div class="input-group">
                    <label>SIM Type</label>
                    <select id="sim" class="input-field" style="appearance: none;"><option value="BASIC">BASIC</option><option value="VOICE">VOICE</option></select>
                </div>
                <div class="input-group">
                    <label>Relay</label>
                    <select id="relay" class="input-field" style="appearance: none;"><option value="NO">NO</option><option value="YES">YES</option></select>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-indian-rupee-sign"></i> Pricing</div>
            <div class="dual-grid">
                <div class="input-group"><label>Quote Price</label><input type="number" id="price" class="input-field" value="0"></div>
                <div class="input-group"><label>Discount</label><input type="number" id="discount" class="input-field" value="0"></div>
            </div>
            <div class="input-group">
                <label>Sales Person</label>
                <input type="text" id="salesPerson" class="input-field">
            </div>
        </div>

        <button class="btn-main" onclick="saveQuotation()">
            <i class="fa-solid fa-file-pdf"></i> Generate & Share Quotation
        </button>
    </div>

    <script>
        async function saveQuotation() {
            const fd = new FormData();
            fd.append('action', 'save');
            fd.append('customer_name', document.getElementById('customer').value);
            fd.append('mobile_number', document.getElementById('mobile').value);
            fd.append('location', document.getElementById('location').value);
            fd.append('device_model', document.getElementById('model').value);
            fd.append('software_name', document.getElementById('software').value);
            fd.append('software_duration', document.getElementById('duration').value);
            fd.append('sim_type', document.getElementById('sim').value);
            fd.append('relay', document.getElementById('relay').value);
            fd.append('total_amount', document.getElementById('price').value);
            fd.append('discount_amount', document.getElementById('discount').value);
            fd.append('sales_person', document.getElementById('salesPerson').value);

            const res = await fetch('api_quotation.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                const url = window.location.href.replace('quotation_entry.php', 'quotation_view.php') + '?uid=' + data.uid;
                if (confirm("🎉 Quotation Generated! Share on WhatsApp?")) {
                    const msg = `Dear ${document.getElementById('customer').value},\n\nThank you for your enquiry. Please find our Quotation ${data.quotation_no} for your vehicle.\n\nView Quotation: ${url}\n\nValid for 7 days.`;
                    window.open(`https://wa.me/91${document.getElementById('mobile').value}?text=${encodeURIComponent(msg)}`, '_blank');
                }
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        }
    </script>
</body>
</html>
