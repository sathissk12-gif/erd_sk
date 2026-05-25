<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Follow-up | SK Enterprises</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #0f172a; color: #f8fafc; }
        .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); border-radius: 1.5rem; }
        .btn-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4); }
        .form-input { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.75rem; padding: 0.75rem 1rem; width: 100%; outline: none; transition: border-color 0.2s; }
        .form-input:focus { border-color: #3b82f6; }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .status-paid { background: rgba(16, 185, 129, 0.15); color: #10b981; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .autocomplete-list { position: absolute; top: 100%; left: 0; right: 0; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 0.75rem; z-index: 50; max-height: 200px; overflow-y: auto; }
        .autocomplete-item { padding: 0.75rem 1rem; cursor: pointer; transition: background 0.2s; }
        .autocomplete-item:hover { background: rgba(59, 130, 246, 0.1); }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-400">Payment Follow-up</h1>
                <p class="text-slate-400 mt-1">Manage pending payments and customer reminders</p>
            </div>
            <button onclick="openModal()" class="btn-primary px-6 py-3 rounded-xl font-semibold flex items-center gap-2">
                <i class="fas fa-plus"></i> Add New Follow-up
            </button>
        </div>

        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="glass-card p-6 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-500">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div>
                    <p class="text-slate-400 text-sm">Today's Reminders</p>
                    <h3 id="statToday" class="text-2xl font-bold">0</h3>
                </div>
            </div>
            <div class="glass-card p-6 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-500">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <p class="text-slate-400 text-sm">Pending Payments</p>
                    <h3 id="statPending" class="text-2xl font-bold">0</h3>
                </div>
            </div>
            <div class="glass-card p-6 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                    <i class="fas fa-file-invoice-dollar text-xl"></i>
                </div>
                <div>
                    <p class="text-slate-400 text-sm">Total Due</p>
                    <h3 id="statTotal" class="text-2xl font-bold">₹0</h3>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="glass-card overflow-hidden">
            <div class="p-4 md:p-6 border-b border-white/10 flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-xl font-semibold">Follow-up List</h2>
                <div class="flex gap-2 w-full md:w-auto overflow-x-auto pb-2 md:pb-0 custom-scrollbar">
                    <button onclick="loadFollowups('today')" class="whitespace-nowrap px-4 py-2 rounded-lg bg-slate-800 text-xs md:text-sm hover:bg-slate-700 transition">Today</button>
                    <button onclick="loadFollowups('pending')" class="whitespace-nowrap px-4 py-2 rounded-lg bg-slate-800 text-xs md:text-sm hover:bg-slate-700 transition">Pending</button>
                    <button onclick="loadFollowups('all')" class="whitespace-nowrap px-4 py-2 rounded-lg bg-slate-800 text-xs md:text-sm hover:bg-slate-700 transition">All</button>
                </div>
            </div>
            
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-400 text-sm bg-slate-800/30">
                            <th class="px-6 py-4 font-medium">Customer / Vehicle</th>
                            <th class="px-6 py-4 font-medium">Software</th>
                            <th class="px-6 py-4 font-medium">Follow-up Date</th>
                            <th class="px-6 py-4 font-medium">Amount Due</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="followupList" class="divide-y divide-white/5">
                        <!-- Items loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div id="followupCards" class="md:hidden divide-y divide-white/5">
                <!-- Cards loaded here -->
            </div>
        </div>
    </div>

    <!-- Floating Add Button for Mobile -->
    <button onclick="openModal()" class="md:hidden fixed bottom-6 right-6 w-14 h-14 btn-primary rounded-full shadow-2xl flex items-center justify-center z-40 text-xl">
        <i class="fas fa-plus"></i>
    </button>

    <!-- Add/Edit Modal -->
    <div id="modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="glass-card w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-300">
            <div class="p-6 border-b border-white/10 flex justify-between items-center">
                <h2 id="modalTitle" class="text-xl font-semibold">New Follow-up</h2>
                <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form id="followupForm" class="p-6 space-y-4">
                <input type="hidden" id="uid" name="uid">
                <div class="relative">
                    <label class="block text-sm text-slate-400 mb-1">Customer Name</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-input" placeholder="Search or enter name" onkeyup="searchCustomer(this.value)" autocomplete="off">
                    <div id="autocomplete" class="autocomplete-list hidden"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-400 mb-1">Mobile No</label>
                        <input type="text" id="mobile_no" name="mobile_no" class="form-input" placeholder="10 digit number">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-400 mb-1">Vehicle No</label>
                        <input type="text" id="vehicle_no" name="vehicle_no" class="form-input" placeholder="e.g. TN 33 AB 1234">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-400 mb-1">Software Type</label>
                        <select id="software" name="software" class="form-input">
                            <option value="">Select Software</option>
                            <option value="TRACK IN">TRACK IN</option>
                            <option value="NAVILAP">NAVILAP</option>
                            <option value="DO TRACK">DO TRACK</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-400 mb-1">Follow-up Date</label>
                        <input type="date" id="followup_date" name="followup_date" class="form-input">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-400 mb-1">Amount Due</label>
                        <input type="number" id="amount_due" name="amount_due" class="form-input" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-400 mb-1">Status</label>
                        <select id="status" name="status" class="form-input">
                            <option value="PENDING">PENDING</option>
                            <option value="PAID">PAID</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Remark</label>
                    <textarea id="remark" name="remark" class="form-input h-20" placeholder="Follow-up notes..."></textarea>
                </div>
                <button type="submit" class="btn-primary w-full py-4 rounded-xl font-bold text-lg mt-4">Save Follow-up</button>
            </form>
        </div>
    </div>

    <script>
        const API = 'api_followup.php';
        let followups = [];

        async function loadFollowups(filter = 'all') {
            const res = await fetch(`${API}?action=list&filter=${filter}`);
            followups = await res.json();
            renderList();
            updateStats();
        }

        function renderList() {
            const list = document.getElementById('followupList');
            const cardContainer = document.getElementById('followupCards');
            
            // Render Table (Desktop)
            list.innerHTML = followups.map(item => `
                <tr class="hover:bg-slate-800/50 transition">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-200">${item.customer_name}</div>
                        <div class="text-xs text-slate-500">${item.vehicle_no || 'No Vehicle'}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-300">${item.software || 'N/A'}</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${item.followup_date}</td>
                    <td class="px-6 py-4 font-bold text-blue-400">₹${item.amount_due}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold ${item.status === 'PENDING' ? 'status-pending' : 'status-paid'}">
                            ${item.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button onclick="shareWhatsApp('${item.uid}')" class="text-green-500 hover:text-green-400 p-2" title="Send WhatsApp Reminder">
                            <i class="fab fa-whatsapp"></i> Share
                        </button>
                        <button onclick="editItem('${item.uid}')" class="text-blue-400 hover:text-blue-300 p-2"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteItem('${item.uid}')" class="text-red-400 hover:text-red-300 p-2"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('') || '<tr><td colspan="6" class="p-10 text-center text-slate-500 italic">No follow-ups found</td></tr>';

            // Render Cards (Mobile)
            cardContainer.innerHTML = followups.map(item => `
                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-bold text-slate-200 text-lg">${item.customer_name}</div>
                            <div class="text-sm text-blue-400 font-semibold">${item.vehicle_no || 'No Vehicle'}</div>
                        </div>
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold ${item.status === 'PENDING' ? 'status-pending' : 'status-paid'}">
                            ${item.status}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-400">
                        <div><i class="fas fa-microchip mr-1"></i> ${item.software || 'N/A'}</div>
                        <div><i class="fas fa-calendar mr-1"></i> ${item.followup_date}</div>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <div class="text-xl font-bold text-slate-100">₹${item.amount_due}</div>
                        <div class="flex gap-2">
                            <button onclick="shareWhatsApp('${item.uid}')" class="bg-green-500/20 text-green-500 p-2 px-3 rounded-lg text-sm flex items-center gap-1">
                                <i class="fab fa-whatsapp"></i> Share
                            </button>
                            <button onclick="editItem('${item.uid}')" class="bg-blue-500/20 text-blue-500 p-2 rounded-lg"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteItem('${item.uid}')" class="bg-red-500/20 text-red-500 p-2 rounded-lg"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `).join('') || '<div class="p-10 text-center text-slate-500 italic">No follow-ups found</div>';
        }

        function updateStats() {
            const todayStr = new Date().toISOString().split('T')[0];
            const todayCount = followups.filter(f => f.followup_date === todayStr && f.status === 'PENDING').length;
            const pendingCount = followups.filter(f => f.status === 'PENDING').length;
            const totalDue = followups.filter(f => f.status === 'PENDING').reduce((acc, curr) => acc + parseFloat(curr.amount_due), 0);

            document.getElementById('statToday').innerText = todayCount;
            document.getElementById('statPending').innerText = pendingCount;
            document.getElementById('statTotal').innerText = '₹' + totalDue.toLocaleString('en-IN');
        }

        async function searchCustomer(query) {
            const list = document.getElementById('autocomplete');
            if (query.length < 2) { list.classList.add('hidden'); return; }
            
            const res = await fetch(`${API}?action=search_customer&query=${query}`);
            const data = await res.json();
            
            if (data.length > 0) {
                list.innerHTML = data.map(c => `
                    <div class="autocomplete-item" onclick="selectCustomer('${c.customer_name}', '${c.mobile_no}')">
                        <div class="font-bold text-sm">${c.customer_name}</div>
                        <div class="text-xs text-slate-500">${c.mobile_no} | ${c.location}</div>
                    </div>
                `).join('');
                list.classList.remove('hidden');
            } else {
                list.classList.add('hidden');
            }
        }

        function selectCustomer(name, mobile) {
            document.getElementById('customer_name').value = name;
            document.getElementById('mobile_no').value = mobile;
            document.getElementById('autocomplete').classList.add('hidden');
        }

        document.getElementById('followupForm').onsubmit = async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const res = await fetch(`${API}?action=save`, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                closeModal();
                loadFollowups();
                alert("Follow-up Saved!");
            } else {
                alert(data.message);
            }
        };

        function shareWhatsApp(uid) {
            const item = followups.find(f => f.uid === uid);
            const msg = `Dear ${item.customer_name},

Greetings from *SK ENTERPRISES*.

This is a friendly reminder regarding your pending payment of *₹${item.amount_due}* for Vehicle *${item.vehicle_no || 'N/A'}* (${item.software || 'Software'}).

அன்புள்ள ${item.customer_name},
*SK ENTERPRISES* நிறுவனத்திலிருந்து உங்களின் வண்டி எண் *${item.vehicle_no || 'N/A'}* தொடர்பான நிலுவைத் தொகை *₹${item.amount_due}* செலுத்துவதற்கான ஒரு நினைவூட்டல் இது.

Kindly clear the balance at the earliest to ensure uninterrupted service.
தொடர்ச்சியான சேவையைப் பெற தயவுசெய்து நிலுவைத் தொகையை விரைவில் செலுத்துமாறு கேட்டுக்கொள்கிறோம்.

*Note:* This is an automated reminder. If you have already paid, please ignore this message.
*குறிப்பு:* இது ஒரு தானியங்கி நினைவூட்டல். நீங்கள் ஏற்கனவே பணம் செலுத்தியிருந்தால், தயவுசெய்து இந்த செய்தியை புறக்கணிக்கவும்.

Thank you!
நன்றி!`;
            window.open(`https://wa.me/91${item.mobile_no}?text=${encodeURIComponent(msg)}`, '_blank');
        }

        function editItem(uid) {
            const item = followups.find(f => f.uid === uid);
            document.getElementById('modalTitle').innerText = "Edit Follow-up";
            document.getElementById('uid').value = item.uid;
            document.getElementById('customer_name').value = item.customer_name;
            document.getElementById('mobile_no').value = item.mobile_no;
            document.getElementById('vehicle_no').value = item.vehicle_no;
            document.getElementById('software').value = item.software;
            document.getElementById('followup_date').value = item.followup_date;
            document.getElementById('amount_due').value = item.amount_due;
            document.getElementById('status').value = item.status;
            document.getElementById('remark').value = item.remark;
            openModal();
        }

        async function deleteItem(uid) {
            if (!confirm("Are you sure you want to delete this?")) return;
            const fd = new FormData(); fd.append('uid', uid);
            await fetch(`${API}?action=delete`, { method: 'POST', body: fd });
            loadFollowups();
        }

        function openModal() { document.getElementById('modal').classList.remove('hidden'); }
        function closeModal() { 
            document.getElementById('modal').classList.add('hidden'); 
            document.getElementById('followupForm').reset();
            document.getElementById('uid').value = "";
            document.getElementById('modalTitle').innerText = "New Follow-up";
        }

        loadFollowups();
    </script>
</body>
</html>
