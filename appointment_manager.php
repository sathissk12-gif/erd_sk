<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Manager | SK LOGIC</title>
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
            padding: 20px;
        }

        .container { max-width: 800px; margin: 0 auto; }

        .glass-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 25px;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            margin-bottom: 25px;
        }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; }
        .input-field {
            width: 100%; padding: 14px 18px; background: rgba(15, 23, 42, 0.4);
            border: 1px solid var(--border); border-radius: 12px; color: white; font-size: 14px;
        }
        .input-field:focus { border-color: var(--primary); outline: none; }

        .btn {
            padding: 14px 25px; border-radius: 12px; border: none; font-weight: 700; cursor: pointer; transition: 0.3s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary { background: linear-gradient(135deg, var(--primary), #6366f1); color: white; box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 15px 25px rgba(139, 92, 246, 0.4); }

        .appt-list { margin-top: 30px; }
        .appt-item {
            background: rgba(30, 41, 59, 0.3); border: 1px solid var(--border); border-radius: 16px;
            padding: 18px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;
            transition: 0.3s;
        }
        .appt-item:hover { background: rgba(30, 41, 59, 0.5); border-color: var(--primary); }
        .appt-info h4 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .appt-info p { font-size: 12px; color: var(--text-muted); }
        .appt-status { font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 4px 10px; border-radius: 99px; }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
        .status-completed { background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }

        .reminder-badge {
            font-size: 10px; background: var(--primary); color: white; padding: 2px 8px; border-radius: 4px; margin-left: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1 class="title"><i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i> Appointment Manager</h1>
        <a href="index.html" style="color: var(--text-muted); text-decoration: none; font-size: 14px;"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    <div class="glass-card">
        <div style="font-size: 12px; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">Schedule New Appointment</div>
        <form id="apptForm">
            <div class="form-grid">
                <div class="input-group">
                    <label>Customer Name</label>
                    <input list="customerList" id="customer_name" class="input-field" placeholder="Search or Type Name" required onchange="handleCustomerSelect(this.value)">
                    <datalist id="customerList"></datalist>
                </div>
                <div class="input-group">
                    <label>Mobile Number</label>
                    <input type="tel" id="mobile_number" class="input-field" placeholder="9876543210" required>
                </div>
            </div>
            <div class="form-grid">
                <div class="input-group">
                    <label>Vehicle No</label>
                    <input type="text" id="vehicle_no" class="input-field" placeholder="TN01AA1234">
                </div>
                <div class="input-group">
                    <label>Appointment Date</label>
                    <input type="date" id="appointment_date" class="input-field" required>
                </div>
            </div>
            <div class="form-grid">
                <div class="input-group">
                    <label>Time</label>
                    <input type="time" id="appointment_time" class="input-field" required>
                </div>
                <div class="input-group">
                    <label>Purpose</label>
                    <input type="text" id="purpose" class="input-field" placeholder="eg: GPS Installation">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-plus-circle"></i> Create Appointment & Set Reminder
            </button>
        </form>
    </div>

    <div class="appt-list" id="apptList">
        <!-- Appointments will be loaded here -->
    </div>
</div>

<script>
    const API = 'api_appointments.php';
    let allCustomers = [];

    async function loadCustomers() {
        try {
            const res = await fetch('api_master_data.php?action=get_customer_names');
            allCustomers = await res.json();
            const list = document.getElementById('customerList');
            list.innerHTML = allCustomers.map(c => `<option value="${c.name}">`).join('');
        } catch(e) {}
    }

    function handleCustomerSelect(name) {
        const customer = allCustomers.find(c => c.name === name);
        if(customer) {
            document.getElementById('mobile_number').value = customer.mobile || '';
        }
    }

    async function loadAppointments() {
        const res = await fetch(API + '?action=list');
        const data = await res.json();
        const list = document.getElementById('apptList');
        list.innerHTML = '<div style="font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Upcoming Appointments</div>';
        
        data.forEach(it => {
            const item = document.createElement('div');
            item.className = 'appt-item';
            const reminderText = it.reminder_sent == 1 ? '<span class="reminder-badge">Reminded</span>' : '';
            item.innerHTML = `
                <div class="appt-info">
                    <h4>${it.customer_name} ${reminderText}</h4>
                    <p><i class="fa-solid fa-car"></i> ${it.vehicle_no || 'N/A'} | <i class="fa-solid fa-clock"></i> ${it.appointment_date} ${it.appointment_time}</p>
                    <p style="margin-top:5px; opacity:0.8;">${it.purpose || ''}</p>
                </div>
                <div>
                    <span class="appt-status status-${it.status.toLowerCase()}">${it.status}</span>
                </div>
            `;
            list.appendChild(item);
        });
    }

    document.getElementById('apptForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('action', 'save');
        fd.append('customer_name', document.getElementById('customer_name').value);
        fd.append('mobile_number', document.getElementById('mobile_number').value);
        fd.append('vehicle_no', document.getElementById('vehicle_no').value);
        fd.append('appointment_date', document.getElementById('appointment_date').value);
        fd.append('appointment_time', document.getElementById('appointment_time').value);
        fd.append('purpose', document.getElementById('purpose').value);

        const res = await fetch(API, { method: 'POST', body: fd });
        const data = await res.json();
        if(data.success) {
            alert("✅ Appointment Scheduled! Notification will trigger 1 hour before.");
            document.getElementById('apptForm').reset();
            loadAppointments();
        } else {
            alert("Error: " + data.message);
        }
    });

    loadAppointments();
    loadCustomers();
</script>

</body>
</html>
