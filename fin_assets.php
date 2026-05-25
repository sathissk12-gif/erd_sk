<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Bank & Assets | SK LOGIC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #030712; color: #f8fafc; }
        .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); border-radius: 1.5rem; }
        .grad-bank { background: linear-gradient(135deg, #06b6d4, #3b82f6); }
        .grad-stock { background: linear-gradient(135deg, #8b5cf6, #d946ef); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <a href="index.html" class="text-slate-400 hover:text-white transition mb-2 inline-block"><i class="fas fa-arrow-left mr-2"></i> Back</a>
                <h1 class="text-3xl font-bold text-white">Financial Asset Tracker</h1>
            </div>
            <div class="flex gap-2">
                <button onclick="resetBalance()" class="px-4 py-2 bg-red-500/10 text-red-500 border border-red-500/20 rounded-xl hover:bg-red-500 hover:text-white transition text-xs font-bold uppercase"><i class="fas fa-trash-can mr-2"></i> Reset Data</button>
                <button onclick="location.reload()" class="p-3 bg-slate-800 rounded-xl hover:bg-slate-700 transition"><i class="fas fa-sync-alt text-white"></i></button>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="glass-card grad-bank p-6 relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-2">
                        <div class="text-white/70 text-[10px] font-bold uppercase tracking-wider">Live Bank Balance</div>
                        <button onclick="editOpeningBalance()" class="text-white/40 hover:text-white transition"><i class="fas fa-pen-to-square"></i></button>
                    </div>
                    <div id="bankBalance" class="text-3xl font-black text-white">₹0</div>
                    <div class="mt-2 text-white/80 text-[10px] flex items-center gap-2">
                        <i class="fas fa-flag-checkered"></i> Initial: <span id="openingBal">₹0</span>
                    </div>
                </div>
            </div>
            
            <div class="glass-card grad-stock p-6 relative overflow-hidden">
                <div class="relative z-10">
                    <div class="text-white/70 text-[10px] font-bold uppercase tracking-wider mb-2">Total Stock Value</div>
                    <div id="stockValue" class="text-3xl font-black text-white">₹0</div>
                    <div class="mt-2 text-white/80 text-[10px] flex gap-3">
                        <span><i class="fas fa-microchip mr-1"></i> <span id="devStockVal">₹0</span></span>
                        <span><i class="fas fa-code mr-1"></i> <span id="swStockVal">₹0</span></span>
                    </div>
                </div>
            </div>

            <div class="glass-card p-6 border-emerald-500/30 bg-emerald-500/5">
                <div class="text-emerald-500 text-[10px] font-bold uppercase tracking-wider mb-2">Total Inward (Money In)</div>
                <div id="totalInflow" class="text-3xl font-black text-emerald-400">₹0</div>
                <div class="text-[10px] text-slate-500 mt-2 italic">From Office Settlements</div>
            </div>

            <div class="glass-card p-6 border-red-500/30 bg-red-500/5">
                <div class="text-red-500 text-[10px] font-bold uppercase tracking-wider mb-2">Total Outward (Money Out)</div>
                <div id="totalOutflow" class="text-3xl font-black text-red-400">₹0</div>
                <div class="text-[10px] text-slate-500 mt-2 italic">From Stock Purchases</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Stock Details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="glass-card">
                    <div class="p-6 border-b border-white/10 flex justify-between items-center">
                        <h2 class="text-xl font-bold"><i class="fas fa-list-check mr-2 text-blue-400"></i> Stock Breakdown</h2>
                    </div>
                    <div class="overflow-x-auto max-h-[400px] custom-scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-800/50 sticky top-0">
                                <tr>
                                    <th class="px-6 py-4 font-bold uppercase text-[10px] text-slate-400">Item Name</th>
                                    <th class="px-6 py-4 font-bold uppercase text-[10px] text-slate-400">Qty</th>
                                    <th class="px-6 py-4 font-bold uppercase text-[10px] text-slate-400">Purchase Rate</th>
                                    <th class="px-6 py-4 font-bold uppercase text-[10px] text-slate-400">Total Value</th>
                                </tr>
                            </thead>
                            <tbody id="stockDetails" class="divide-y divide-white/5">
                                <!-- Data here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Recent Transactions -->
            <div class="lg:col-span-1">
                <div class="glass-card">
                    <div class="p-6 border-b border-white/10">
                        <h2 class="text-xl font-bold"><i class="fas fa-clock-rotate-left mr-2 text-purple-400"></i> Recent Movements</h2>
                    </div>
                    <div id="txnList" class="divide-y divide-white/5 max-h-[500px] overflow-y-auto custom-scrollbar">
                        <!-- Data here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        async function loadData() {
            const t = Date.now();
            try {
                // Load Summary
                const summary = await fetch(`api_fin_assets.php?action=get_summary&t=${t}`).then(r => r.json());
                document.getElementById('bankBalance').innerText = '₹' + Math.round(summary.bank_balance).toLocaleString();
                document.getElementById('stockValue').innerText = '₹' + Math.round(summary.stock_value).toLocaleString();
                document.getElementById('openingBal').innerText = '₹' + Math.round(summary.breakdown.opening_bal).toLocaleString();
                document.getElementById('totalInflow').innerText = '₹' + Math.round(summary.breakdown.settlements).toLocaleString();
                document.getElementById('totalOutflow').innerText = '₹' + Math.round(summary.breakdown.device_purchases + summary.breakdown.software_purchases).toLocaleString();
                document.getElementById('devStockVal').innerText = '₹' + Math.round(summary.breakdown.device_stock_val).toLocaleString();
                document.getElementById('swStockVal').innerText = '₹' + Math.round(summary.breakdown.software_stock_val).toLocaleString();

                // Load Stock Details
                const stock = await fetch(`api_fin_assets.php?action=get_stock_details&t=${t}`).then(r => r.json());
                const stockHtml = [
                    ...stock.devices.map(d => `
                        <tr>
                            <td class="px-6 py-4"><span class="bg-blue-500/10 text-blue-400 px-2 py-1 rounded text-[10px] font-bold mr-2">DEVICE</span> ${d.name}</td>
                            <td class="px-6 py-4 font-bold">${d.qty}</td>
                            <td class="px-6 py-4 text-slate-400">₹${Math.round(d.value/d.qty).toLocaleString()}</td>
                            <td class="px-6 py-4 font-bold text-white">₹${parseFloat(d.value).toLocaleString()}</td>
                        </tr>
                    `),
                    ...stock.software.map(s => `
                        <tr>
                            <td class="px-6 py-4"><span class="bg-purple-500/10 text-purple-400 px-2 py-1 rounded text-[10px] font-bold mr-2">SOFTWARE</span> ${s.name}</td>
                            <td class="px-6 py-4 font-bold">${s.qty}</td>
                            <td class="px-6 py-4 text-slate-400">₹${parseFloat(s.rate).toLocaleString()}</td>
                            <td class="px-6 py-4 font-bold text-white">₹${parseFloat(s.value).toLocaleString()}</td>
                        </tr>
                    `)
                ];
                document.getElementById('stockDetails').innerHTML = stockHtml.join('') || '<tr><td colspan="4" class="p-10 text-center">No Stock</td></tr>';

                // Load Transactions
                const txns = await fetch(`api_fin_assets.php?action=get_transactions&t=${t}`).then(r => r.json());
                document.getElementById('txnList').innerHTML = txns.map(t => `
                    <div class="p-4 flex justify-between items-center">
                        <div>
                            <div class="text-sm font-bold text-slate-200">${t.description}</div>
                            <div class="text-[10px] text-slate-500 uppercase">${t.date}</div>
                        </div>
                        <div class="text-sm font-black ${t.type === 'INFLOW' ? 'text-emerald-400' : 'text-red-400'}">
                            ${t.type === 'INFLOW' ? '+' : '-'} ₹${parseFloat(t.amount).toLocaleString()}
                        </div>
                    </div>
                `).join('') || '<div class="p-10 text-center">No Transactions</div>';

            } catch (err) {
                console.error(err);
            }
        }

        async function editOpeningBalance() {
            const val = prompt("Enter your CURRENT Bank Balance (as per your bank statement):");
            if (val === null || isNaN(val)) return;

            const fd = new FormData();
            fd.append('target_balance', val);
            
            const res = await fetch('api_fin_assets.php?action=calibrate_balance', { method: 'POST', body: fd }).then(r => r.json());
            if (res.success) {
                alert("Bank Balance Calibrated Successfully!");
                loadData();
            }
        }

        async function resetBalance() {
            if (!confirm("Are you sure you want to reset the Initial Balance to ₹0? This will recalibrate your live bank balance.")) return;
            
            const fd = new FormData();
            fd.append('opening_bank_balance', '0');
            
            const res = await fetch('api_fin_assets.php?action=update_settings', { method: 'POST', body: fd }).then(r => r.json());
            if (res.success) {
                alert("Balance Reset Successfully!");
                loadData();
            }
        }

        window.onload = loadData;
    </script>
</body>
</html>
