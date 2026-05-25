<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>CRM Lead Manager | SK LOGIC</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); padding-bottom: 50px; }

        header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(3, 7, 18, 0.7); backdrop-filter: blur(25px);
            padding: 15px 25px; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .back-link { text-decoration: none; color: white; display: flex; align-items: center; gap: 10px; font-weight: 700; }

        .container { max-width: 800px; margin: 20px auto; padding: 0 20px; }

        .glass-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 25px;
            backdrop-filter: blur(20px); margin-bottom: 20px;
        }

        .section-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; }
        .input-field {
            width: 100%; padding: 14px 18px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 12px;
            color: white; font-size: 14px; outline: none;
        }

        .btn-main {
            width: 100%; padding: 16px; border: none; border-radius: 15px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white; font-weight: 800; cursor: pointer; transition: 0.3s;
        }

        .lead-item {
            background: rgba(30, 41, 59, 0.3); border: 1px solid var(--border); border-radius: 16px;
            padding: 18px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;
        }
        .lead-info h4 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .lead-info p { font-size: 12px; color: var(--text-muted); }

        .status-badge {
            font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 99px; text-transform: uppercase;
        }
        .status-NEW { background: rgba(59, 130, 246, 0.1); color: #60a5fa; }
        .status-HOT { background: rgba(239, 68, 68, 0.1); color: #f87171; }
        .status-FOLLOWUP { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }

        .tabs { display: flex; gap: 10px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 5px; }
        .tab {
            padding: 8px 16px; background: var(--surface); border: 1px solid var(--border); border-radius: 99px;
            font-size: 12px; font-weight: 700; color: var(--text-muted); cursor: pointer; white-space: nowrap;
        }
        .tab.active { background: var(--primary); color: white; border-color: var(--primary); }
    </style>
</head>
<body>

    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div style="font-weight: 800; font-size: 14px;">CRM LEAD MANAGER</div>
    </header>

    <div class="container">
        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-plus-circle"></i> Add New Lead</div>
            <form id="leadForm">
                <div class="form-grid">
                    <div class="input-group"><label>Customer Name</label><input type="text" id="name" class="input-field" required></div>
                    <div class="input-group"><label>Mobile No</label><input type="tel" id="mobile" class="input-field" required></div>
                </div>
                <div class="form-grid">
                    <div class="input-group"><label>Interest</label><input type="text" id="interest" class="input-field" placeholder="eg: GPS Tracker"></div>
                    <div class="input-group">
                        <label>Status</label>
                        <select id="status" class="input-field" style="appearance:none;"><option value="NEW">NEW</option><option value="INTERESTED">INTERESTED</option><option value="HOT">HOT</option><option value="FOLLOWUP">FOLLOW UP</option></select>
                    </div>
                </div>
                <div class="input-group"><label>Follow-up Date</label><input type="date" id="followup" class="input-field"></div>
                <button type="submit" class="btn-main">Save Lead to CRM</button>
            </form>
        </div>

        <div class="tabs">
            <div class="tab active">All Leads</div>
            <div class="tab">Today Follow-ups</div>
            <div class="tab">Hot Leads</div>
        </div>

        <div id="leadList">
            <!-- Leads will load here -->
        </div>
    </div>

    <script>
        async function loadLeads() {
            const res = await fetch('api_crm.php?action=list_leads');
            const data = await res.json();
            const list = document.getElementById('leadList');
            list.innerHTML = '';
            data.forEach(l => {
                const item = document.createElement('div');
                item.className = 'lead-item';
                item.innerHTML = `
                    <div class="lead-info">
                        <h4>${l.customer_name}</h4>
                        <p><i class="fa-solid fa-phone"></i> ${l.mobile_number} | <i class="fa-solid fa-tag"></i> ${l.interest || 'General'}</p>
                        <p style="margin-top:5px; opacity:0.7;">Next Follow-up: ${l.followup_date || 'Not set'}</p>
                    </div>
                    <div>
                        <span class="status-badge status-${l.status}">${l.status}</span>
                    </div>
                `;
                list.appendChild(item);
            });
        }

        document.getElementById('leadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action', 'save_lead');
            fd.append('customer_name', document.getElementById('name').value);
            fd.append('mobile_number', document.getElementById('mobile').value);
            fd.append('interest', document.getElementById('interest').value);
            fd.append('status', document.getElementById('status').value);
            fd.append('followup_date', document.getElementById('followup').value);

            const res = await fetch('api_crm.php', { method: 'POST', body: fd });
            const data = await res.json();
            if(data.success) {
                alert("Lead Saved!");
                document.getElementById('leadForm').reset();
                loadLeads();
            }
        });

        loadLeads();
    </script>
</body>
</html>
