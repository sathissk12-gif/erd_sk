<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Daily Expense Manager | SK LOGIC</title>

    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Security & Theme -->
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
            --income: #10b981;
            --income-glow: rgba(16, 185, 129, 0.3);
            --expense: #f43f5e;
            --expense-glow: rgba(244, 63, 94, 0.3);
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.7);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-dim: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text);
            min-height: 100vh;
            display: flex; flex-direction: column;
            padding-top: env(safe-area-inset-top, 0px);
            padding-bottom: 100px;
        }

        /* ─── HEADER ─── */
        header {
            background: rgba(3, 7, 18, 0.85); backdrop-filter: blur(25px);
            padding: calc(12px + env(safe-area-inset-top, 0px)) 20px 14px;
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
        }
        .header-left { display: flex; align-items: center; gap: 12px; }
        .header-back {
            width: 36px; height: 36px; border-radius: 12px;
            background: var(--surface); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--text); cursor: pointer; transition: 0.3s; font-size: 14px;
        }
        .header-back:active { transform: scale(0.92); background: var(--primary); }
        .header-title { font-size: 13px; font-weight: 800; text-transform: uppercase; color: var(--primary); letter-spacing: 1.5px; }
        .header-actions { display: flex; gap: 8px; }
        .header-btn {
            width: 36px; height: 36px; border-radius: 12px;
            background: var(--surface); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-dim); cursor: pointer; transition: 0.3s; font-size: 13px;
        }
        .header-btn:active, .header-btn.active { color: white; background: var(--primary); border-color: var(--primary); }

        /* ─── CONTAINER ─── */
        .container { max-width: 520px; margin: 0 auto; padding: 20px 16px; width: 100%; }

        /* ─── MONTH / YEAR NAVIGATOR ─── */
        .period-nav {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-bottom: 20px;
        }
        .period-btn {
            width: 38px; height: 38px; border-radius: 14px;
            background: var(--surface); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--text); cursor: pointer; transition: 0.3s; font-size: 13px;
        }
        .period-btn:active { background: var(--primary); transform: scale(0.9); }
        .period-display {
            display: flex; flex-direction: column; align-items: center; gap: 2px; min-width: 120px;
        }
        .period-month {
            font-size: 20px; font-weight: 800; font-family: 'Outfit'; letter-spacing: -0.5px;
            cursor: pointer; transition: 0.3s; padding: 4px 12px; border-radius: 10px;
        }
        .period-month:hover { background: var(--surface); }
        .period-year {
            font-size: 12px; font-weight: 600; color: var(--text-dim); letter-spacing: 1px;
            cursor: pointer; transition: 0.3s; padding: 2px 10px; border-radius: 8px;
        }
        .period-year:hover { background: var(--surface); color: var(--primary); }
        .period-today {
            font-size: 10px; font-weight: 700; color: var(--primary); cursor: pointer;
            padding: 6px 14px; border-radius: 20px; background: rgba(244,63,94,0.1);
            border: 1px solid rgba(244,63,94,0.2); transition: 0.3s; white-space: nowrap;
        }
        .period-today:active { background: var(--primary); color: white; }

        /* ─── SUMMARY CARDS ─── */
        .summary-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        .summary-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 20px;
            padding: 16px 12px; text-align: center; backdrop-filter: blur(20px);
            transition: 0.3s; cursor: pointer; position: relative; overflow: hidden;
        }
        .summary-card::before {
            content: ''; position: absolute; top: -20px; right: -20px; width: 60px; height: 60px;
            border-radius: 50%; opacity: 0.1; transition: 0.4s;
        }
        .summary-card.income::before { background: var(--income); }
        .summary-card.expense::before { background: var(--expense); }
        .summary-card.balance::before { background: var(--secondary); }
        .summary-card:active { transform: scale(0.96); }
        .summary-card .s-icon {
            font-size: 18px; margin-bottom: 6px; display: block;
        }
        .summary-card.income .s-icon { color: var(--income); }
        .summary-card.expense .s-icon { color: var(--expense); }
        .summary-card.balance .s-icon { color: var(--secondary); }
        .summary-card .s-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-dim); }
        .summary-card .s-value {
            font-size: 18px; font-weight: 800; font-family: 'Outfit'; margin-top: 4px; display: block;
        }
        .summary-card.income .s-value { color: var(--income); }
        .summary-card.expense .s-value { color: var(--expense); }
        .summary-card.balance .s-value { color: var(--secondary); }

        /* ─── TAB SWITCHER ─── */
        .tab-row {
            display: flex; background: var(--surface); border-radius: 16px; padding: 4px;
            margin-bottom: 20px; border: 1px solid var(--border);
        }
        .tab-btn {
            flex: 1; padding: 12px; text-align: center; border-radius: 13px;
            font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.3s;
            color: var(--text-dim); border: none; background: transparent;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .tab-btn.active {
            background: var(--primary); color: white;
            box-shadow: 0 8px 20px var(--primary-glow);
        }

        /* ─── CHART AREA ─── */
        .chart-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 20px;
            padding: 20px; margin-bottom: 20px; backdrop-filter: blur(20px);
        }
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .chart-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--text-dim); }
        .chart-wrap { position: relative; height: 200px; }
        .chart-wrap canvas { width: 100% !important; height: 100% !important; }

        /* ─── CATEGORY CHIPS ─── */
        .cat-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
        .cat-chip {
            display: flex; align-items: center; gap: 6px; padding: 8px 14px;
            background: var(--surface); border: 1px solid var(--border); border-radius: 25px;
            font-size: 11px; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .cat-chip .chip-icon { font-size: 13px; }
        .cat-chip .chip-amt { font-family: 'Outfit'; font-weight: 700; color: var(--text); }
        .cat-chip:active { transform: scale(0.94); border-color: var(--primary); }

        /* ─── TRANSACTION LIST ─── */
        .day-group { margin-bottom: 20px; }
        .day-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 8px 4px; margin-bottom: 8px;
        }
        .day-label { font-size: 13px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; }
        .day-summary { display: flex; gap: 12px; font-size: 11px; font-weight: 700; }
        .day-inc { color: var(--income); }
        .day-exp { color: var(--expense); }

        .txn-item {
            display: flex; align-items: center; gap: 14px;
            background: var(--surface); border: 1px solid var(--border); border-radius: 18px;
            padding: 14px 16px; margin-bottom: 8px;
            backdrop-filter: blur(20px); transition: 0.3s; cursor: pointer;
            position: relative; overflow: hidden;
        }
        .txn-item:active { transform: scale(0.98); }
        .txn-item .delete-swipe {
            position: absolute; right: -80px; top: 0; bottom: 0; width: 80px;
            background: var(--expense); display: flex; align-items: center; justify-content: center;
            color: white; font-size: 18px; transition: 0.3s; border-radius: 0 18px 18px 0;
        }
        .txn-item.swiped .delete-swipe { right: 0; }
        .txn-item.swiped { transform: translateX(-80px); }

        .txn-icon {
            width: 42px; height: 42px; border-radius: 14px; display: flex;
            align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;
        }
        .txn-icon.income-bg { background: rgba(16,185,129,0.15); color: var(--income); }
        .txn-icon.expense-bg { background: rgba(244,63,94,0.15); color: var(--expense); }

        .txn-info { flex: 1; min-width: 0; }
        .txn-name { font-size: 14px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .txn-note { font-size: 10px; color: var(--text-dim); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .txn-amount {
            font-family: 'Outfit'; font-size: 16px; font-weight: 800; flex-shrink: 0;
        }
        .txn-amount.inc { color: var(--income); }
        .txn-amount.exp { color: var(--expense); }

        .empty-state {
            text-align: center; padding: 60px 20px; color: var(--text-dim);
        }
        .empty-state i { font-size: 60px; margin-bottom: 16px; opacity: 0.3; display: block; }
        .empty-state p { font-size: 14px; font-weight: 600; }

        /* ─── FAB ─── */
        .fab {
            position: fixed; bottom: 30px; right: 24px; width: 58px; height: 58px;
            border-radius: 20px; background: linear-gradient(135deg, var(--primary), #e11d48);
            color: white; display: flex; align-items: center; justify-content: center;
            font-size: 22px; cursor: pointer; z-index: 999; border: none;
            box-shadow: 0 12px 30px var(--primary-glow);
            transition: 0.3s;
        }
        .fab:active { transform: scale(0.9) rotate(45deg); }

        /* ─── MODAL OVERLAY ─── */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 2000;
            display: flex; align-items: flex-end; justify-content: center;
            opacity: 0; pointer-events: none; transition: 0.3s;
        }
        .modal-overlay.show { opacity: 1; pointer-events: auto; }
        .modal-sheet {
            background: #0f172a; border: 1px solid var(--border);
            border-radius: 28px 28px 0 0; width: 100%; max-width: 520px;
            max-height: 85vh; overflow-y: auto; padding: 20px;
            transform: translateY(100%); transition: 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .modal-overlay.show .modal-sheet { transform: translateY(0); }
        .modal-handle {
            width: 40px; height: 4px; background: rgba(255,255,255,0.2);
            border-radius: 2px; margin: 0 auto 20px;
        }
        .modal-title { font-size: 16px; font-weight: 800; margin-bottom: 20px; text-align: center; }

        /* ─── FORM ELEMENTS ─── */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 10px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .form-input, .form-select {
            width: 100%; padding: 15px 16px; background: rgba(0,0,0,0.25);
            border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 14px; font-family: 'Plus Jakarta Sans', sans-serif; outline: none;
            transition: 0.3s;
        }
        .form-input:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
        .form-select { appearance: none; cursor: pointer; }
        .form-select option { background: #0f172a; color: white; }

        .type-toggle {
            display: flex; background: rgba(0,0,0,0.2); border-radius: 14px; padding: 4px;
        }
        .type-opt {
            flex: 1; padding: 13px; text-align: center; border-radius: 12px;
            font-size: 13px; font-weight: 700; cursor: pointer; transition: 0.3s; color: var(--text-dim);
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .type-opt.income-sel.active { background: var(--income); color: white; }
        .type-opt.expense-sel.active { background: var(--expense); color: white; }

        .icon-picker-row { display: flex; flex-wrap: wrap; gap: 6px; max-height: 120px; overflow-y: auto; margin-top: 8px; }
        .icon-pick {
            width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-size: 14px; cursor: pointer; background: rgba(255,255,255,0.05); border: 1px solid transparent;
            transition: 0.2s; color: var(--text-dim);
        }
        .icon-pick.selected { border-color: var(--primary); background: rgba(244,63,94,0.15); color: var(--primary); }
        .icon-pick:active { transform: scale(0.85); }

        .btn-row { display: flex; gap: 10px; margin-top: 20px; }
        .btn-primary {
            flex: 1; padding: 16px; border: none; border-radius: 18px;
            background: linear-gradient(135deg, var(--primary), #e11d48);
            color: white; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.3s;
            font-family: 'Plus Jakarta Sans', sans-serif;
            box-shadow: 0 10px 25px var(--primary-glow);
        }
        .btn-primary:active { transform: scale(0.96); }
        .btn-secondary {
            flex: 1; padding: 16px; border: 1px solid var(--border); border-radius: 18px;
            background: var(--surface); color: var(--text); font-weight: 700; font-size: 14px;
            cursor: pointer; transition: 0.3s; font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .btn-secondary:active { transform: scale(0.96); }
        .btn-danger {
            padding: 14px 20px; border: none; border-radius: 16px;
            background: rgba(239,68,68,0.15); color: #ef4444; font-weight: 700; font-size: 13px;
            cursor: pointer; transition: 0.3s; font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* ─── YEAR VIEW ─── */
        .year-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
        .year-month-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 16px;
            padding: 14px 10px; text-align: center; cursor: pointer; transition: 0.3s;
        }
        .year-month-card:active { transform: scale(0.94); border-color: var(--primary); }
        .year-month-card .ym-name { font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); letter-spacing: 1px; }
        .year-month-card .ym-inc { font-size: 11px; font-weight: 700; color: var(--income); margin-top: 4px; }
        .year-month-card .ym-exp { font-size: 11px; font-weight: 700; color: var(--expense); }

        /* ─── SEARCH ─── */
        .search-wrap {
            display: flex; gap: 8px; margin-bottom: 16px;
        }
        .search-input {
            flex: 1; padding: 13px 16px; background: var(--surface); border: 1px solid var(--border);
            border-radius: 16px; color: white; font-size: 13px; outline: none; transition: 0.3s;
        }
        .search-input:focus { border-color: var(--primary); }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 380px) {
            .summary-row { gap: 6px; }
            .summary-card { padding: 12px 8px; }
            .summary-card .s-value { font-size: 15px; }
        }
    </style>
</head>
<body>

    <!-- ═══════════ HEADER ═══════════ -->
    <header>
        <div class="header-left">
            <div class="header-back" onclick="window.location='index.html'">
                <i class="fa-solid fa-chevron-left"></i>
            </div>
            <div class="header-title">💰 Daily Manager</div>
        </div>
        <div class="header-actions">
            <div class="header-btn" id="btnSearch" onclick="toggleSearch()" title="Search">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <div class="header-btn" id="btnYearView" onclick="toggleYearView()" title="Year View">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div class="header-btn" onclick="toggleTheme()" title="Theme">
                <i class="fa-solid fa-palette"></i>
            </div>
            <a href="index.html" class="header-btn" title="Home">
                <i class="fa-solid fa-house"></i>
            </a>
        </div>
    </header>

    <!-- ═══════════ MAIN CONTAINER ═══════════ -->
    <div class="container" id="mainContainer">

        <!-- Period Navigator -->
        <div class="period-nav">
            <button class="period-btn" onclick="changeMonth(-1)"><i class="fa-solid fa-chevron-left"></i></button>
            <div class="period-display">
                <span class="period-month" id="periodMonth" onclick="showMonthPicker()">January</span>
                <span class="period-year" id="periodYear" onclick="showYearPicker()">2026</span>
            </div>
            <button class="period-btn" onclick="changeMonth(1)"><i class="fa-solid fa-chevron-right"></i></button>
            <button class="period-today" onclick="goToToday()">Today</button>
        </div>

        <!-- Summary Cards -->
        <div class="summary-row" id="summaryRow">
            <div class="summary-card income" onclick="filterByType('income')">
                <span class="s-icon"><i class="fa-solid fa-arrow-down"></i></span>
                <span class="s-label">Income</span>
                <span class="s-value" id="sumIncome">₹0</span>
            </div>
            <div class="summary-card expense" onclick="filterByType('expense')">
                <span class="s-icon"><i class="fa-solid fa-arrow-up"></i></span>
                <span class="s-label">Expense</span>
                <span class="s-value" id="sumExpense">₹0</span>
            </div>
            <div class="summary-card balance" onclick="filterByType('all')">
                <span class="s-icon"><i class="fa-solid fa-wallet"></i></span>
                <span class="s-label">Balance</span>
                <span class="s-value" id="sumBalance">₹0</span>
            </div>
        </div>

        <!-- Tab Switcher -->
        <div class="tab-row" id="tabRow">
            <button class="tab-btn active" data-tab="all" onclick="setTab('all')">
                <i class="fa-solid fa-list"></i> All
            </button>
            <button class="tab-btn" data-tab="income" onclick="setTab('income')">
                <i class="fa-solid fa-arrow-down"></i> Income
            </button>
            <button class="tab-btn" data-tab="expense" onclick="setTab('expense')">
                <i class="fa-solid fa-arrow-up"></i> Expense
            </button>
        </div>

        <!-- Search Bar (hidden by default) -->
        <div class="search-wrap" id="searchWrap" style="display:none;">
            <input type="text" class="search-input" id="searchInput" placeholder="🔍 Search items or notes..." oninput="doSearch()">
        </div>

        <!-- Chart Card -->
        <div class="chart-card" id="chartCard">
            <div class="chart-header">
                <span class="chart-title">📊 Daily Trend</span>
                <span style="font-size:10px;color:var(--text-dim);" id="chartRange"></span>
            </div>
            <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
        </div>

        <!-- Category Chips -->
        <div class="cat-chips" id="catChips"></div>

        <!-- Year View (hidden by default) -->
        <div id="yearView" style="display:none;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <button class="period-btn" onclick="changeYear(-1)"><i class="fa-solid fa-chevron-left"></i></button>
                <span style="font-size:18px;font-weight:800;font-family:'Outfit';" id="yearViewLabel">2026</span>
                <button class="period-btn" onclick="changeYear(1)"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
            <div class="year-grid" id="yearGrid"></div>
        </div>

        <!-- Transaction List -->
        <div id="txnList"></div>
    </div>

    <!-- ═══════════ FAB ═══════════ -->
    <button class="fab" id="fabBtn" onclick="openAddModal()">
        <i class="fa-solid fa-plus"></i>
    </button>

    <!-- ═══════════ ADD/EDIT MODAL ═══════════ -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
        <div class="modal-sheet" id="modalSheet" onclick="event.stopPropagation()">
            <div class="modal-handle"></div>
            <div class="modal-title" id="modalTitle">Add Transaction</div>

            <!-- Type Toggle -->
            <div class="form-group">
                <label class="form-label">Type</label>
                <div class="type-toggle">
                    <div class="type-opt income-sel active" onclick="setTxnType('income')">
                        <i class="fa-solid fa-arrow-down"></i> Income
                    </div>
                    <div class="type-opt expense-sel" onclick="setTxnType('expense')">
                        <i class="fa-solid fa-arrow-up"></i> Expense
                    </div>
                </div>
            </div>

            <!-- Item Name with Suggestions -->
            <div class="form-group">
                <label class="form-label">Item / Category Name</label>
                <input type="text" class="form-input" id="itemName" placeholder="e.g. Salary, Rent, Food..." 
                       oninput="onItemNameInput()" list="itemSuggestions" autocomplete="off">
                <datalist id="itemSuggestions"></datalist>
            </div>

            <!-- Icon Picker -->
            <div class="form-group">
                <label class="form-label">Icon <span id="autoIconHint" style="color:var(--primary);font-weight:400;">(auto-assigned)</span></label>
                <input type="hidden" id="selectedIcon" value="">
                <div class="icon-picker-row" id="iconPicker"></div>
            </div>

            <!-- Amount -->
            <div class="form-group">
                <label class="form-label">Amount (₹)</label>
                <input type="number" class="form-input" id="txnAmount" placeholder="0.00" step="0.01" min="0">
            </div>

            <!-- Date -->
            <div class="form-group">
                <label class="form-label">Date</label>
                <input type="date" class="form-input" id="txnDate">
            </div>

            <!-- Note -->
            <div class="form-group">
                <label class="form-label">Note (Optional)</label>
                <input type="text" class="form-input" id="txnNote" placeholder="Any remark...">
            </div>

            <input type="hidden" id="editId" value="">

            <div class="btn-row">
                <button class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn-primary" id="btnSave" onclick="saveTransaction()">💾 Save</button>
            </div>
            <div style="text-align:center;margin-top:10px;">
                <button class="btn-danger" id="btnDelete" style="display:none;" onclick="deleteTransaction()">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════ SCRIPTS ═══════════ -->
    <script>
        // ─── STATE ───
        let currentMonth = new Date().getMonth() + 1; // 1-12
        let currentYear = new Date().getFullYear();
        let currentTab = 'all';
        let currentTxnType = 'expense';
        let isYearView = false;
        let trendChart = null;
        let savedItems = [];
        let allTransactions = [];

        const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const COMMON_ICONS = [
            'fa-money-bill-wave','fa-building','fa-utensils','fa-gas-pump','fa-bolt',
            'fa-wifi','fa-mobile-screen','fa-bag-shopping','fa-shirt','fa-kit-medical',
            'fa-graduation-cap','fa-film','fa-chart-line','fa-piggy-bank','fa-hand-holding-dollar',
            'fa-shield-halved','fa-screwdriver-wrench','fa-broom','fa-gift','fa-bullhorn',
            'fa-laptop','fa-car','fa-plane','fa-book','fa-mug-hot',
            'fa-cart-shopping','fa-store','fa-coins','fa-wallet','fa-star',
            'fa-heart','fa-comments','fa-gear','fa-toolbox','fa-circle-dollar'
        ];

        // ─── INIT ───
        window.onload = () => {
            document.getElementById('txnDate').value = new Date().toISOString().split('T')[0];
            updatePeriodDisplay();
            loadAll();
        };

        function updatePeriodDisplay() {
            document.getElementById('periodMonth').innerText = MONTHS[currentMonth - 1];
            document.getElementById('periodYear').innerText = currentYear;
        }

        // ─── PERIOD NAVIGATION ───
        function changeMonth(delta) {
            currentMonth += delta;
            if (currentMonth < 1) { currentMonth = 12; currentYear--; }
            if (currentMonth > 12) { currentMonth = 1; currentYear++; }
            updatePeriodDisplay();
            loadAll();
        }

        function changeYear(delta) {
            currentYear += delta;
            updatePeriodDisplay();
            if (isYearView) loadYearView(); else loadAll();
        }

        function goToToday() {
            currentMonth = new Date().getMonth() + 1;
            currentYear = new Date().getFullYear();
            updatePeriodDisplay();
            if (isYearView) { isYearView = false; toggleYearView(); }
            loadAll();
        }

        function showMonthPicker() {
            const m = prompt('Enter month (1-12):', currentMonth);
            if (m && m >= 1 && m <= 12) { currentMonth = parseInt(m); updatePeriodDisplay(); loadAll(); }
        }

        function showYearPicker() {
            const y = prompt('Enter year:', currentYear);
            if (y && y >= 2000 && y <= 2100) { currentYear = parseInt(y); updatePeriodDisplay(); loadAll(); }
        }

        // ─── TABS ───
        function setTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
            renderTransactions();
        }

        function filterByType(type) {
            setTab(type);
        }

        // ─── YEAR VIEW ───
        function toggleYearView() {
            isYearView = !isYearView;
            document.getElementById('yearView').style.display = isYearView ? 'block' : 'none';
            document.getElementById('chartCard').style.display = isYearView ? 'none' : 'block';
            document.getElementById('catChips').style.display = isYearView ? 'none' : 'flex';
            document.getElementById('txnList').style.display = isYearView ? 'none' : 'block';
            const btn = document.getElementById('btnYearView');
            btn.classList.toggle('active', isYearView);
            if (isYearView) {
                document.getElementById('yearViewLabel').innerText = currentYear;
                loadYearView();
            }
        }

        async function loadYearView() {
            const res = await fetch(`api_daily_expense.php?action=get_summary&month=${currentMonth}&year=${currentYear}`);
            const data = await res.json();
            if (data.status !== 'ok') return;

            document.getElementById('yearViewLabel').innerText = currentYear;
            const grid = document.getElementById('yearGrid');
            grid.innerHTML = data.mom.map(m => `
                <div class="year-month-card" onclick="currentMonth=${m.month};updatePeriodDisplay();toggleYearView();loadAll();">
                    <div class="ym-name">${MONTHS[m.month-1].substring(0,3)}</div>
                    <div class="ym-inc">+₹${m.income.toLocaleString()}</div>
                    <div class="ym-exp">-₹${m.expense.toLocaleString()}</div>
                </div>
            `).join('');
        }

        // ─── SEARCH ───
        function toggleSearch() {
            const wrap = document.getElementById('searchWrap');
            const btn = document.getElementById('btnSearch');
            wrap.style.display = wrap.style.display === 'none' ? 'flex' : 'none';
            btn.classList.toggle('active', wrap.style.display === 'flex');
            if (wrap.style.display === 'flex') document.getElementById('searchInput').focus();
            else { document.getElementById('searchInput').value = ''; loadAll(); }
        }

        async function doSearch() {
            const q = document.getElementById('searchInput').value.trim();
            if (q.length < 2) { loadAll(); return; }
            const res = await fetch(`api_daily_expense.php?action=search&q=${encodeURIComponent(q)}&year=${currentYear}`);
            const data = await res.json();
            if (data.status === 'ok') {
                allTransactions = data.results;
                renderTransactions();
            }
        }

        // ─── LOAD ALL DATA ───
        async function loadAll() {
            try {
                const [txnRes, summaryRes, itemsRes] = await Promise.all([
                    fetch(`api_daily_expense.php?action=get_transactions&month=${currentMonth}&year=${currentYear}&type=${currentTab}`),
                    fetch(`api_daily_expense.php?action=get_summary&month=${currentMonth}&year=${currentYear}`),
                    fetch(`api_daily_expense.php?action=get_items`)
                ]);

                const txnData = await txnRes.json();
                const summaryData = await summaryRes.json();
                const itemsData = await itemsRes.json();

                if (txnData.status === 'ok') allTransactions = txnData.all;
                if (itemsData.status === 'ok') savedItems = itemsData.items;

                updateSummaryCards(summaryData);
                updateChart(summaryData);
                updateCategoryChips(summaryData);
                renderTransactions();
                updateItemSuggestions();
            } catch (e) { console.error('Load error:', e); }
        }

        function updateSummaryCards(data) {
            if (!data || data.status !== 'ok') return;
            document.getElementById('sumIncome').innerText = '₹' + (data.monthly.income || 0).toLocaleString();
            document.getElementById('sumExpense').innerText = '₹' + (data.monthly.expense || 0).toLocaleString();
            const bal = (data.monthly.income || 0) - (data.monthly.expense || 0);
            const balEl = document.getElementById('sumBalance');
            balEl.innerText = (bal >= 0 ? '₹' : '-₹') + Math.abs(bal).toLocaleString();
            balEl.style.color = bal >= 0 ? 'var(--income)' : 'var(--expense)';
        }

        function updateChart(data) {
            if (!data || data.status !== 'ok') return;
            const daily = data.daily || [];
            document.getElementById('chartRange').innerText = `${MONTHS[currentMonth-1]} ${currentYear}`;

            const labels = daily.map(d => {
                const dt = new Date(d.date + 'T00:00:00');
                return dt.getDate();
            });
            const incomeData = daily.map(d => d.income || 0);
            const expenseData = daily.map(d => d.expense || 0);

            const ctx = document.getElementById('trendChart').getContext('2d');
            if (trendChart) trendChart.destroy();

            const isDark = document.documentElement.getAttribute('data-theme') !== 'light' && 
                           document.documentElement.getAttribute('data-theme') !== 'traxen';
            const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

            trendChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Income',
                            data: incomeData,
                            backgroundColor: 'rgba(16,185,129,0.6)',
                            borderColor: '#10b981',
                            borderWidth: 1,
                            borderRadius: 8,
                            borderSkipped: false
                        },
                        {
                            label: 'Expense',
                            data: expenseData,
                            backgroundColor: 'rgba(244,63,94,0.6)',
                            borderColor: '#f43f5e',
                            borderWidth: 1,
                            borderRadius: 8,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: isDark ? '#94a3b8' : '#475569', font: { family: 'Plus Jakarta Sans', size: 11 }, usePointStyle: true, padding: 16 }
                        }
                    },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: isDark ? '#64748b' : '#64748b', font: { size: 10 } } },
                        y: { grid: { color: gridColor }, ticks: { color: isDark ? '#64748b' : '#64748b', font: { size: 10 }, callback: v => '₹' + v } }
                    }
                }
            });
        }

        function updateCategoryChips(data) {
            if (!data || data.status !== 'ok') return;
            const cats = data.categories || [];
            const container = document.getElementById('catChips');
            if (cats.length === 0) { container.innerHTML = ''; return; }
            container.innerHTML = cats.slice(0, 8).map(c => `
                <div class="cat-chip" onclick="filterByItem('${c.item_name.replace(/'/g, "\\'")}')">
                    <i class="fa-solid ${c.icon || 'fa-tag'} chip-icon" style="color:${c.type==='income'?'var(--income)':'var(--expense)'};"></i>
                    <span>${c.item_name}</span>
                    <span class="chip-amt" style="color:${c.type==='income'?'var(--income)':'var(--expense)'};">₹${parseFloat(c.total).toLocaleString()}</span>
                </div>
            `).join('');
        }

        function filterByItem(itemName) {
            document.getElementById('searchInput').value = itemName;
            document.getElementById('searchWrap').style.display = 'flex';
            document.getElementById('btnSearch').classList.add('active');
            doSearch();
        }

        // ─── RENDER TRANSACTIONS ───
        function renderTransactions() {
            const container = document.getElementById('txnList');
            let filtered = allTransactions;
            if (currentTab !== 'all') {
                filtered = allTransactions.filter(t => t.type === currentTab);
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fa-solid fa-file-invoice"></i>
                        <p>No transactions found</p>
                        <span style="font-size:11px;color:var(--text-dim);">Tap + to add your first entry</span>
                    </div>`;
                return;
            }

            // Group by date
            const grouped = {};
            filtered.forEach(t => {
                const d = t.transaction_date;
                if (!grouped[d]) grouped[d] = [];
                grouped[d].push(t);
            });

            const dates = Object.keys(grouped).sort((a,b) => b.localeCompare(a));

            container.innerHTML = dates.map(date => {
                const items = grouped[date];
                const dayInc = items.filter(i => i.type === 'income').reduce((s,i) => s + parseFloat(i.amount), 0);
                const dayExp = items.filter(i => i.type === 'expense').reduce((s,i) => s + parseFloat(i.amount), 0);
                const dt = new Date(date + 'T00:00:00');
                const dayName = dt.toLocaleDateString('en-US', { weekday: 'short' });
                const dayNum = dt.getDate();
                const monthShort = MONTHS[dt.getMonth()].substring(0, 3);

                return `
                <div class="day-group">
                    <div class="day-header">
                        <span class="day-label">📅 ${dayName}, ${monthShort} ${dayNum}</span>
                        <div class="day-summary">
                            ${dayInc > 0 ? `<span class="day-inc">+₹${dayInc.toLocaleString()}</span>` : ''}
                            ${dayExp > 0 ? `<span class="day-exp">-₹${dayExp.toLocaleString()}</span>` : ''}
                        </div>
                    </div>
                    ${items.map(t => `
                        <div class="txn-item" id="txn-${t.id}" 
                             ontouchstart="touchStart(event, ${t.id})" ontouchend="touchEnd(event, ${t.id})"
                             onclick="openEditModal(${t.id})">
                            <div class="txn-icon ${t.type}-bg">
                                <i class="fa-solid ${t.icon || 'fa-tag'}"></i>
                            </div>
                            <div class="txn-info">
                                <div class="txn-name">${t.item_name}</div>
                                <div class="txn-note">${t.note || 'No note'}</div>
                            </div>
                            <div class="txn-amount ${t.type === 'income' ? 'inc' : 'exp'}">
                                ${t.type === 'income' ? '+' : '-'}₹${parseFloat(t.amount).toLocaleString()}
                            </div>
                            <div class="delete-swipe" onclick="event.stopPropagation();deleteTransactionDirect(${t.id})">
                                <i class="fa-solid fa-trash"></i>
                            </div>
                        </div>
                    `).join('')}
                </div>`;
            }).join('');
        }

        // ─── SWIPE TO DELETE ───
        let touchStartX = 0, touchCurrentId = null;
        function touchStart(e, id) { touchStartX = e.touches[0].clientX; touchCurrentId = id; }
        function touchEnd(e, id) {
            const diff = touchStartX - e.changedTouches[0].clientX;
            const el = document.getElementById('txn-' + id);
            if (diff > 60) el.classList.add('swiped');
            else if (diff < -30) el.classList.remove('swiped');
        }

        // ─── MODAL MANAGEMENT ───
        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'Add Transaction';
            document.getElementById('editId').value = '';
            document.getElementById('btnDelete').style.display = 'none';
            document.getElementById('btnSave').innerHTML = '💾 Save';
            setTxnTypeUI('expense');
            document.getElementById('itemName').value = '';
            document.getElementById('txnAmount').value = '';
            document.getElementById('txnDate').value = new Date().toISOString().split('T')[0];
            document.getElementById('txnNote').value = '';
            document.getElementById('selectedIcon').value = '';
            document.getElementById('autoIconHint').style.display = 'inline';
            currentTxnType = 'expense';
            renderIconPicker('');
            document.getElementById('modalOverlay').classList.add('show');
            setTimeout(() => document.getElementById('itemName').focus(), 400);
        }

        function openEditModal(id) {
            const txn = allTransactions.find(t => t.id == id);
            if (!txn) return;

            document.getElementById('modalTitle').innerText = 'Edit Transaction';
            document.getElementById('editId').value = txn.id;
            document.getElementById('btnDelete').style.display = 'block';
            document.getElementById('btnSave').innerHTML = '💾 Update';
            setTxnTypeUI(txn.type);
            document.getElementById('itemName').value = txn.item_name;
            document.getElementById('txnAmount').value = txn.amount;
            document.getElementById('txnDate').value = txn.transaction_date;
            document.getElementById('txnNote').value = txn.note || '';
            document.getElementById('selectedIcon').value = txn.icon || '';
            document.getElementById('autoIconHint').style.display = 'none';
            currentTxnType = txn.type;
            renderIconPicker(txn.icon || '');
            document.getElementById('modalOverlay').classList.add('show');
        }

        function closeModal(e) {
            if (e && e.target !== document.getElementById('modalOverlay')) return;
            document.getElementById('modalOverlay').classList.remove('show');
            document.querySelectorAll('.txn-item').forEach(el => el.classList.remove('swiped'));
        }

        function setTxnType(type) {
            currentTxnType = type;
            setTxnTypeUI(type);
        }

        function setTxnTypeUI(type) {
            document.querySelectorAll('.type-opt').forEach(o => o.classList.remove('active'));
            document.querySelector(`.type-opt.${type}-sel`).classList.add('active');
            currentTxnType = type;
        }

        // ─── ICON PICKER ───
        function renderIconPicker(selectedIcon) {
            const picker = document.getElementById('iconPicker');
            picker.innerHTML = COMMON_ICONS.map(ic => `
                <div class="icon-pick ${ic === selectedIcon ? 'selected' : ''}" 
                     onclick="selectIcon('${ic}')" title="${ic.replace('fa-','')}">
                    <i class="fa-solid ${ic}"></i>
                </div>
            `).join('');
        }

        function selectIcon(icon) {
            document.getElementById('selectedIcon').value = icon;
            document.getElementById('autoIconHint').style.display = 'none';
            renderIconPicker(icon);
        }

        async function onItemNameInput() {
            const name = document.getElementById('itemName').value.trim();
            if (!name) return;

            // Check saved items for matching icon
            const match = savedItems.find(si => si.name.toLowerCase() === name.toLowerCase());
            if (match && match.icon) {
                document.getElementById('selectedIcon').value = match.icon;
                document.getElementById('autoIconHint').style.display = 'none';
                renderIconPicker(match.icon);
            } else if (!document.getElementById('selectedIcon').value) {
                // Auto-fetch icon
                try {
                    const res = await fetch(`api_daily_expense.php?action=auto_icon&name=${encodeURIComponent(name)}`);
                    const data = await res.json();
                    if (data.status === 'ok' && data.icon) {
                        document.getElementById('selectedIcon').value = data.icon;
                        renderIconPicker(data.icon);
                    }
                } catch(e) {}
            }
        }

        function updateItemSuggestions() {
            const datalist = document.getElementById('itemSuggestions');
            datalist.innerHTML = savedItems.map(si => 
                `<option value="${si.name}">${si.icon ? '●' : ''} ${si.name}</option>`
            ).join('');
        }

        // ─── SAVE TRANSACTION ───
        async function saveTransaction() {
            const id = document.getElementById('editId').value;
            const itemName = document.getElementById('itemName').value.trim();
            const icon = document.getElementById('selectedIcon').value;
            const amount = parseFloat(document.getElementById('txnAmount').value);
            const date = document.getElementById('txnDate').value;
            const note = document.getElementById('txnNote').value.trim();

            if (!itemName) { alert('Please enter item name'); return; }
            if (!amount || amount <= 0) { alert('Please enter valid amount'); return; }
            if (!date) { alert('Please select date'); return; }

            const fd = new FormData();
            fd.append('action', id ? 'update_transaction' : 'add_transaction');
            if (id) fd.append('id', id);
            fd.append('type', currentTxnType);
            fd.append('item_name', itemName);
            fd.append('icon', icon);
            fd.append('amount', amount);
            fd.append('date', date);
            fd.append('note', note);

            try {
                const res = await fetch('api_daily_expense.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.status === 'ok') {
                    closeModal();
                    loadAll();
                } else {
                    alert(data.error || 'Error saving');
                }
            } catch(e) { alert('Network error'); }
        }

        async function deleteTransaction() {
            const id = document.getElementById('editId').value;
            if (!id || !confirm('Delete this transaction?')) return;
            await deleteTransactionDirect(id);
            closeModal();
        }

        async function deleteTransactionDirect(id) {
            const fd = new FormData();
            fd.append('action', 'delete_transaction');
            fd.append('id', id);
            await fetch('api_daily_expense.php', { method: 'POST', body: fd });
            loadAll();
        }

        // ─── KEYBOARD SHORTCUTS ───
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModal();
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                toggleSearch();
            }
        });
    </script>
</body>
</html>
