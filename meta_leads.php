<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Meta Leads | SK LOGIC</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script src="theme_engine.js"></script>

    <style>
        :root {
            --primary: #8b5cf6;
            --secondary: #06b6d4;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --card-base: rgba(30, 41, 59, 0.3);
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
            padding-bottom: 100px;
        }

        .header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(3, 7, 18, 0.7); backdrop-filter: blur(25px);
            padding: 20px 25px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 15px;
        }

        .back-btn {
            width: 40px; height: 40px; border-radius: 12px; background: var(--card-base);
            display: flex; align-items: center; justify-content: center; color: white; text-decoration: none;
        }

        .container { max-width: 600px; margin: 0 auto; padding: 20px; }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 25px; }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border); padding: 15px;
            border-radius: 20px; text-align: center; backdrop-filter: blur(10px);
        }
        .stat-card .label { font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: 800; margin-bottom: 5px; }
        .stat-card .value { font-size: 20px; font-weight: 800; font-family: 'Outfit'; }

        .lead-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 24px;
            padding: 20px; margin-bottom: 15px; backdrop-filter: blur(15px);
            position: relative; overflow: hidden; transition: 0.3s;
        }
        .lead-card::before {
            content: ''; position: absolute; left: 0; top: 0; width: 4px; height: 100%;
            background: var(--primary); opacity: 0.5;
        }
        .lead-card.new::before { background: #10b981; opacity: 1; }

        .lead-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .lead-name { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .lead-time { font-size: 11px; color: var(--text-muted); }

        .lead-info { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
        .info-item { display: flex; align-items: center; gap: 12px; font-size: 14px; color: #e2e8f0; }
        .info-item i { width: 32px; height: 32px; border-radius: 10px; background: var(--card-base); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 14px; }

        .lead-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn {
            padding: 12px; border-radius: 14px; border: none; font-weight: 700; font-size: 13px;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
            text-decoration: none; transition: 0.2s;
        }
        .btn-call { background: #10b981; color: white; }
        .btn-whatsapp { background: #25d366; color: white; }
        .btn-done { background: var(--card-base); color: var(--text-muted); grid-column: span 2; }
        .btn:active { transform: scale(0.95); }

        .badge {
            padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .badge-new { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }

        #emptyState { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        #emptyState i { font-size: 50px; margin-bottom: 20px; opacity: 0.2; }
    </style>
</head>
<body>

    <div class="header">
        <a href="index.html" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <div>
            <h1 style="font-size: 20px; font-weight: 800;">Meta Ads Leads</h1>
            <p style="font-size: 10px; color: #10b981; font-weight: 800; text-transform: uppercase;">Real-time Integration</p>
        </div>
    </div>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <p class="label">Total</p>
                <p class="value" id="statTotal">0</p>
            </div>
            <div class="stat-card" style="border-color: rgba(16, 185, 129, 0.3);">
                <p class="label" style="color: #10b981;">Today</p>
                <p class="value" id="statToday">0</p>
            </div>
            <div class="stat-card" style="border-color: rgba(139, 92, 246, 0.3);">
                <p class="label" style="color: var(--primary);">Pending</p>
                <p class="value" id="statPending">0</p>
            </div>
        </div>

        <div id="leadsList">
            <div id="emptyState">
                <i class="fa-solid fa-users-slash"></i>
                <p>No leads captured yet.<br>Ads run panna start pannunga!</p>
            </div>
        </div>
    </div>

    <script>
        async function fetchStats() {
            try {
                const res = await fetch('api_meta_leads.php?action=stats');
                const data = await res.json();
                document.getElementById('statTotal').innerText = data.total || 0;
                document.getElementById('statToday').innerText = data.today || 0;
                document.getElementById('statPending').innerText = data.pending || 0;
            } catch(e) {}
        }

        async function fetchLeads() {
            try {
                const res = await fetch('api_meta_leads.php?action=list');
                const leads = await res.json();
                const container = document.getElementById('leadsList');
                
                if (leads.length > 0) {
                    container.innerHTML = leads.map(lead => `
                        <div class="lead-card ${lead.is_processed == 0 ? 'new' : ''}">
                            <div class="lead-header">
                                <div>
                                    <div class="lead-name">${lead.full_name || 'Unknown Name'}</div>
                                    <div class="lead-time"><i class="fa-regular fa-clock"></i> ${formatTime(lead.created_time)}</div>
                                </div>
                                ${lead.is_processed == 0 ? '<span class="badge badge-new">New Lead</span>' : ''}
                            </div>
                            
                            <div class="lead-info">
                                <div class="info-item">
                                    <i class="fa-solid fa-phone"></i>
                                    <span>${lead.phone_number || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <i class="fa-solid fa-envelope"></i>
                                    <span>${lead.email || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <i class="fa-solid fa-file-lines"></i>
                                    <span style="font-size: 11px; opacity: 0.7;">Form ID: ${lead.form_id}</span>
                                </div>
                            </div>

                            <div class="lead-actions">
                                <a href="tel:${lead.phone_number}" class="btn btn-call"><i class="fa-solid fa-phone"></i> Call</a>
                                <a href="https://wa.me/${formatWhatsApp(lead.phone_number)}" class="btn btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                                ${lead.is_processed == 0 ? `
                                    <button onclick="markProcessed(${lead.id})" class="btn btn-done">
                                        <i class="fa-solid fa-check-double"></i> Mark as Processed
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    `).join('');
                }
            } catch(e) {}
        }

        async function markProcessed(id) {
            if(confirm('Mark this lead as processed?')) {
                await fetch(`api_meta_leads.php?action=mark_processed&id=${id}`);
                fetchStats();
                fetchLeads();
            }
        }

        function formatTime(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-IN', { 
                day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' 
            });
        }

        function formatWhatsApp(phone) {
            if(!phone) return '';
            return phone.replace(/[^0-9]/g, '');
        }

        window.onload = () => {
            fetchStats();
            fetchLeads();
        };
    </script>
</body>
</html>
