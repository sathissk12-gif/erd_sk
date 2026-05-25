<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Hostinger Cloud | SK LOGIC</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text);
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .bg-glow {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: 
                radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(244, 63, 94, 0.05) 0%, transparent 40%);
            z-index: -1; pointer-events: none;
        }

        header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(3, 7, 18, 0.7); backdrop-filter: blur(25px);
            padding: 20px 25px; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }

        .header-logo { display: flex; align-items: center; gap: 14px; }
        .logo-box { 
            width: 44px; height: 44px; background: linear-gradient(135deg, #8b5cf6, #3b82f6); 
            border-radius: 14px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 20px var(--primary-glow);
        }
        .logo-box i { font-size: 22px; color: white; }
        .header-info h1 { font-size: 19px; font-weight: 800; letter-spacing: -0.8px; }
        .header-info span { font-size: 9px; text-transform: uppercase; color: var(--secondary); font-weight: 800; letter-spacing: 1px; }

        .container { max-width: 600px; margin: 0 auto; padding: 20px; }

        .vps-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 20px;
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .vps-status {
            position: absolute; top: 25px; right: 25px;
            padding: 6px 14px; border-radius: 20px;
            font-size: 10px; font-weight: 800; text-transform: uppercase;
            display: flex; align-items: center; gap: 6px;
        }
        .status-running { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .status-stopped { background: rgba(239, 68, 68, 0.15); color: var(--danger); }

        .vps-name { font-size: 22px; font-weight: 800; margin-bottom: 5px; font-family: 'Outfit'; }
        .vps-ip { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }

        .vps-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-item { background: var(--card-base); padding: 15px; border-radius: 18px; text-align: center; border: 1px solid var(--border); }
        .stat-label { font-size: 10px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; font-weight: 700; }
        .stat-val { font-size: 16px; font-weight: 800; font-family: 'Outfit'; }

        .vps-actions { display: flex; gap: 10px; }
        .btn-action { 
            flex: 1; padding: 14px; border-radius: 15px; border: 1px solid var(--border);
            font-size: 12px; font-weight: 800; cursor: pointer; transition: 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: var(--card-base); color: white;
        }
        .btn-action:hover { background: rgba(255,255,255,0.1); }
        .btn-action:active { transform: scale(0.95); }
        .btn-power-on { background: linear-gradient(135deg, #10b981, #059669); border: none; }
        .btn-power-off { background: linear-gradient(135deg, #ef4444, #dc2626); border: none; }

        .section-title { 
            font-size: 12px; font-weight: 800; color: var(--text-muted); 
            text-transform: uppercase; letter-spacing: 2px; margin: 30px 0 20px;
            display: flex; align-items: center; gap: 10px;
        }
        .section-title::after { content: ''; flex: 1; height: 1px; background: linear-gradient(to right, var(--border), transparent); }

        .domain-item {
            background: var(--surface); border: 1px solid var(--border); border-radius: 20px;
            padding: 18px 22px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;
        }
        .domain-info h4 { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
        .domain-info p { font-size: 11px; color: var(--text-muted); }

        .loading-spinner {
            text-align: center; padding: 50px; color: var(--text-muted);
        }
        .loading-spinner i { font-size: 30px; margin-bottom: 15px; color: var(--primary); }

        .back-btn {
            width: 44px; height: 44px; background: var(--card-base); border-radius: 14px;
            display: flex; align-items: center; justify-content: center; cursor: pointer; color: white;
            text-decoration: none; border: 1px solid var(--border);
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <header>
        <div class="header-logo">
            <a href="index.html" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
            <div class="header-info" style="margin-left: 10px;">
                <h1>HOSTINGER CLOUD</h1>
                <span><i class="fa-solid fa-cloud"></i> Infrastructure Manager</span>
            </div>
        </div>
        <div class="logo-box" style="background: var(--card-base); cursor: pointer;" onclick="loadData()">
            <i class="fa-solid fa-rotate" id="refreshIcon"></i>
        </div>
    </header>

    <div class="container">
        <div id="vpsSection">
            <div class="section-title"><i class="fa-solid fa-server"></i> Virtual Private Servers</div>
            <div id="vpsList">
                <div class="loading-spinner"><i class="fa-solid fa-circle-notch fa-spin"></i><br>Fetching server data...</div>
            </div>
        </div>

        <div id="domainSection">
            <div class="section-title"><i class="fa-solid fa-globe"></i> Active Domains</div>
            <div id="domainList">
                <div class="loading-spinner">Searching domains...</div>
            </div>
        </div>
    </div>

    <script>
        async function loadData() {
            const refreshIcon = document.getElementById('refreshIcon');
            refreshIcon.classList.add('fa-spin');
            
            try {
                // Fetch VPS
                console.log("Fetching VPS list...");
                const vpsRes = await fetch('api_hostinger.php?action=vps_list');
                const vpsData = await vpsRes.json();
                console.log("VPS Data Received:", vpsData);
                
                if (vpsData.error) {
                    throw new Error(vpsData.error);
                }
                renderVPS(vpsData);

                // Fetch Domains
                console.log("Fetching Domains...");
                const domainRes = await fetch('api_hostinger.php?action=domain_list');
                const domainData = await domainRes.json();
                console.log("Domain Data Received:", domainData);
                
                if (domainData.error) {
                    throw new Error(domainData.error);
                }
                renderDomains(domainData);

            } catch (e) {
                console.error("Load Error:", e);
                const vpsList = document.getElementById('vpsList');
                vpsList.innerHTML = `<div style="color:var(--danger); text-align:center; padding:20px;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size:30px; margin-bottom:10px;"></i><br>
                    ${e.message}<br>
                    <small style="opacity:0.6;">Check console for details</small>
                </div>`;
            } finally {
                refreshIcon.classList.remove('fa-spin');
            }
        }

        function renderVPS(rawData) {
            const container = document.getElementById('vpsList');
            
            // Handle different data structures (array vs object with data key)
            let data = Array.isArray(rawData) ? rawData : (rawData.data || []);
            
            if (rawData.error) {
                container.innerHTML = `<div style="color:var(--danger); text-align:center; padding:20px;">${rawData.error}</div>`;
                return;
            }

            if (!data || data.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:30px; color:var(--text-muted);"><i class="fa-solid fa-ghost" style="font-size:30px; margin-bottom:10px; opacity:0.3;"></i><br>No VPS instances found in this account.</div>';
                return;
            }

            container.innerHTML = data.map(vps => {
                // Map API fields to our UI fields (Flexible mapping)
                const name = vps.name || vps.displayName || 'Unnamed Server';
                const ip = vps.ip || vps.ipv4 || vps.address || '0.0.0.0';
                const status = (vps.status || vps.state || 'unknown').toLowerCase();
                const cpu = vps.cpu_usage || vps.cpuUsage || 0;
                const ram = vps.memory_usage || vps.memoryUsage || 0;
                const disk = vps.disk_usage || vps.diskUsage || 0;
                const id = vps.id || vps.vpsId;

                return `
                    <div class="vps-card">
                        <div class="vps-status ${status === 'running' || status === 'active' ? 'status-running' : 'status-stopped'}">
                            <i class="fa-solid fa-circle"></i> ${status}
                        </div>
                        <div class="vps-name">${name}</div>
                        <div class="vps-ip"><i class="fa-solid fa-network-wired"></i> ${ip}</div>
                        
                        <div class="vps-stats">
                            <div class="stat-item">
                                <div class="stat-label">CPU</div>
                                <div class="stat-val">${cpu}%</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">RAM</div>
                                <div class="stat-val">${ram}%</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Disk</div>
                                <div class="stat-val">${disk}%</div>
                            </div>
                        </div>

                        <div class="vps-actions">
                            ${status === 'running' || status === 'active'
                                ? `<button class="btn-action btn-power-off" onclick="controlVPS('${id}', 'power-off')"><i class="fa-solid fa-power-off"></i> STOP</button>`
                                : `<button class="btn-action btn-power-on" onclick="controlVPS('${id}', 'power-on')"><i class="fa-solid fa-bolt"></i> START</button>`
                            }
                            <button class="btn-action" onclick="controlVPS('${id}', 'reboot')"><i class="fa-solid fa-rotate-right"></i> REBOOT</button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderDomains(rawData) {
            const container = document.getElementById('domainList');
            let data = Array.isArray(rawData) ? rawData : (rawData.data || []);

            if (!data || data.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:30px; color:var(--text-muted);">No domains found.</div>';
                return;
            }

            container.innerHTML = data.map(dom => `
                <div class="domain-item">
                    <div class="domain-info">
                        <h4>${dom.domain || dom.domainName}</h4>
                        <p>Expires: ${dom.expires_at || dom.expiresAt || 'N/A'}</p>
                    </div>
                    <div style="color:var(--success); font-weight:800; font-size:10px;">${(dom.status || 'ACTIVE').toUpperCase()}</div>
                </div>
            `).join('');
        }

        async function controlVPS(id, command) {
            if (!confirm(`Are you sure you want to ${command} this server?`)) return;
            
            const fd = new FormData();
            fd.append('id', id);
            fd.append('command', command);

            try {
                const res = await fetch('api_hostinger.php?action=vps_control', {
                    method: 'POST',
                    body: fd
                });
                const result = await res.json();
                alert(result.message || 'Command sent successfully');
                loadData();
            } catch (e) {
                alert('Action failed');
            }
        }

        window.onload = loadData;
    </script>
</body>
</html>
