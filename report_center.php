<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Report Center | SK LOGIC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #030712; color: #f8fafc; }
        .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); border-radius: 1.5rem; }
        .report-btn { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
        .report-btn:hover { transform: translateY(-4px); background: rgba(59, 130, 246, 0.1); border-color: #3b82f6; }
        .report-btn.active { background: #3b82f6; border-color: #3b82f6; color: white; }
        .form-input { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.75rem; padding: 0.75rem 1rem; width: 100%; outline: none; transition: 0.2s; }
        .form-input:focus { border-color: #3b82f6; }
        .loader { width: 40px; height: 40px; border: 4px solid rgba(255,255,255,0.1); border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <a href="index.html" class="text-slate-400 hover:text-white transition mb-4 inline-block"><i class="fas fa-arrow-left mr-2"></i> Back to Console</a>
                <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-400">Advanced Report Center</h1>
                <p class="text-slate-400 mt-1">Export professional data reports in Excel format</p>
            </div>
            <div class="hidden md:block">
                <i class="fas fa-file-excel text-4xl text-emerald-500"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar: Report Selection -->
            <div class="lg:col-span-1 flex lg:flex-col gap-4 overflow-x-auto lg:overflow-visible pb-4 lg:pb-0 custom-scrollbar">
                <div onclick="selectReport('sales')" id="btn-sales" class="report-btn active glass-card p-4 flex items-center gap-4 min-w-[180px] lg:min-w-0">
                    <i class="fas fa-shopping-cart text-blue-400"></i>
                    <span class="font-semibold whitespace-nowrap">Sales Report</span>
                </div>
                <div onclick="selectReport('renewal')" id="btn-renewal" class="report-btn glass-card p-4 flex items-center gap-4 min-w-[180px] lg:min-w-0">
                    <i class="fas fa-sync-alt text-purple-400"></i>
                    <span class="font-semibold whitespace-nowrap">Renewal Report</span>
                </div>
                <div onclick="selectReport('device_stock')" id="btn-device_stock" class="report-btn glass-card p-4 flex items-center gap-4 min-w-[180px] lg:min-w-0">
                    <i class="fas fa-microchip text-emerald-400"></i>
                    <span class="font-semibold whitespace-nowrap">Device Stock</span>
                </div>
                <div onclick="selectReport('software_stock')" id="btn-software_stock" class="report-btn glass-card p-4 flex items-center gap-4 min-w-[180px] lg:min-w-0">
                    <i class="fas fa-code text-amber-400"></i>
                    <span class="font-semibold whitespace-nowrap">Software Stock</span>
                </div>
                <div onclick="selectReport('device_sales')" id="btn-device_sales" class="report-btn glass-card p-4 flex items-center gap-4 min-w-[180px] lg:min-w-0">
                    <i class="fas fa-barcode text-red-400"></i>
                    <span class="font-semibold whitespace-nowrap">Device Sales</span>
                </div>
            </div>

            <!-- Main Panel -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Filters -->
                <div class="glass-card p-4 md:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 items-end">
                        <div id="dateFilters">
                            <label class="block text-xs md:text-sm text-slate-400 mb-2 uppercase font-bold tracking-wider">Start Date</label>
                            <input type="date" id="startDate" class="form-input">
                        </div>
                        <div id="dateFiltersEnd">
                            <label class="block text-xs md:text-sm text-slate-400 mb-2 uppercase font-bold tracking-wider">End Date</label>
                            <input type="date" id="endDate" class="form-input">
                        </div>
                        <div class="flex gap-2">
                            <button onclick="fetchData()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition">
                                <i class="fas fa-search mr-2"></i> Preview
                            </button>
                            <button onclick="exportToExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl transition">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Preview Area -->
                <div class="glass-card overflow-hidden">
                    <div class="p-6 border-b border-white/10 flex justify-between items-center">
                        <h2 id="tableTitle" class="text-lg md:text-xl font-semibold">Report Preview</h2>
                        <span id="rowCount" class="text-[10px] bg-slate-800 px-2 py-1 rounded text-slate-400 font-bold uppercase">0 Records</span>
                    </div>
                    <div id="loading" class="hidden p-20 flex flex-col items-center gap-4">
                        <div class="loader"></div>
                        <p class="text-slate-400">Fetching Data...</p>
                    </div>
                    
                    <!-- Desktop View -->
                    <div id="tableContainer" class="hidden md:block overflow-x-auto max-h-[500px] custom-scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead id="tableHead" class="bg-slate-800/50 sticky top-0 z-10"></thead>
                            <tbody id="tableBody" class="divide-y divide-white/5"></tbody>
                        </table>
                    </div>

                    <!-- Mobile View -->
                    <div id="mobileCards" class="md:hidden divide-y divide-white/5 max-h-[500px] overflow-y-auto custom-scrollbar">
                        <!-- Cards here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentType = 'sales';
        let reportData = [];

        // Set default dates to current month
        window.onload = () => {
            const now = new Date();
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
            const lastDay = new Date().toISOString().split('T')[0];
            document.getElementById('startDate').value = firstDay;
            document.getElementById('endDate').value = lastDay;
            fetchData();
        };

        function selectReport(type) {
            currentType = type;
            document.querySelectorAll('.report-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(`btn-${type}`).classList.add('active');
            
            // Show/Hide date filters based on report type
            const dateSection = document.getElementById('dateFilters');
            const dateSectionEnd = document.getElementById('dateFiltersEnd');
            if (type === 'device_stock' || type === 'software_stock') {
                dateSection.style.opacity = '0.3';
                dateSectionEnd.style.opacity = '0.3';
            } else {
                dateSection.style.opacity = '1';
                dateSectionEnd.style.opacity = '1';
            }
            
            document.getElementById('tableTitle').innerText = document.getElementById(`btn-${type}`).innerText + " Preview";
            fetchData();
        }

        async function fetchData() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('tableContainer').classList.add('hidden');

            try {
                const res = await fetch(`api_reports_export.php?action=get_data&type=${currentType}&start_date=${start}&end_date=${end}`);
                reportData = await res.json();
                renderTable();
            } catch (err) {
                alert("Error fetching data");
            } finally {
                document.getElementById('loading').classList.add('hidden');
                document.getElementById('tableContainer').classList.remove('hidden');
            }
        }

        function renderTable() {
            const head = document.getElementById('tableHead');
            const body = document.getElementById('tableBody');
            const cardContainer = document.getElementById('mobileCards');
            document.getElementById('rowCount').innerText = reportData.length + " Records";

            if (reportData.length === 0) {
                head.innerHTML = "";
                body.innerHTML = '<tr><td colspan="10" class="p-10 text-center text-slate-500 italic">No records found for this period.</td></tr>';
                cardContainer.innerHTML = '<div class="p-10 text-center text-slate-500 italic">No records found.</div>';
                return;
            }

            // Headers (Desktop)
            const keys = Object.keys(reportData[0]);
            head.innerHTML = `<tr>${keys.map(k => `<th class="px-6 py-4 font-bold uppercase text-[10px] tracking-wider text-slate-400">${k.replace(/_/g, ' ')}</th>`).join('')}</tr>`;

            // Rows (Desktop)
            body.innerHTML = reportData.map(row => `
                <tr class="hover:bg-slate-800/30 transition">
                    ${keys.map(k => `<td class="px-6 py-4 text-slate-300">${row[k] || '-'}</td>`).join('')}
                </tr>
            `).join('');

            // Cards (Mobile)
            cardContainer.innerHTML = reportData.map(row => `
                <div class="p-4 space-y-2">
                    ${keys.slice(0, 4).map(k => `
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-bold uppercase text-[9px] tracking-wider">${k.replace(/_/g, ' ')}</span>
                            <span class="text-slate-200 font-semibold">${row[k] || '-'}</span>
                        </div>
                    `).join('')}
                    ${keys.length > 4 ? `
                        <div class="pt-2 border-t border-white/5 flex flex-wrap gap-x-4 gap-y-1">
                            ${keys.slice(4).map(k => `
                                <div class="text-[10px]">
                                    <span class="text-slate-500">${k.replace(/_/g, ' ')}:</span>
                                    <span class="text-slate-300">${row[k] || '-'}</span>
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        function exportToExcel() {
            if (reportData.length === 0) return alert("No data to export!");
            const worksheet = XLSX.utils.json_to_sheet(reportData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Report");
            XLSX.writeFile(workbook, `${currentType}_report_${Date.now()}.xlsx`);
        }
    </script>
</body>
</html>
