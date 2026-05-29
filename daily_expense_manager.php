<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Daily Expense Manager | SK LOGIC</title>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="theme_engine.js"></script>

    <style>
        :root {
            --income: #10b981; --income-glow: rgba(16, 185, 129, 0.3);
            --expense: #ef4444; --expense-glow: rgba(239, 68, 68, 0.3);
        }
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--body-gradient, radial-gradient(circle at top right, #1e1b4b, #030712));
            color: var(--text); min-height: 100vh;
            display: flex; flex-direction: column;
            padding-top: env(safe-area-inset-top, 0px); padding-bottom: 100px;
        }

        /* ─── HEADER ─── */
        header {
            background: rgba(3, 7, 18, 0.85); backdrop-filter: blur(25px);
            padding: calc(12px + env(safe-area-inset-top, 0px)) 20px 14px;
            border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
        }
        .header-left { display: flex; align-items: center; gap: 12px; }
        .header-back {
            width: 36px; height: 36px; border-radius: 12px; background: var(--surface);
            border: 1px solid var(--border); display: flex; align-items: center; justify-content: center;
            color: var(--text); cursor: pointer; transition: 0.3s; font-size: 14px;
        }
        .header-back:active { transform: scale(0.92); background: var(--primary); }
        .header-title { font-size: 13px; font-weight: 800; text-transform: uppercase; color: var(--primary); letter-spacing: 1.5px; }
        .header-actions { display: flex; gap: 8px; }
        .header-btn {
            width: 36px; height: 36px; border-radius: 12px; background: var(--surface);
            border: 1px solid var(--border); display: flex; align-items: center; justify-content: center;
            color: var(--text-dim); cursor: pointer; transition: 0.3s; font-size: 13px; text-decoration: none;
        }
        .header-btn:active, .header-btn.active { color: white; background: var(--primary); border-color: var(--primary); }

        /* ─── CONTAINER ─── */
        .container { max-width: 520px; margin: 0 auto; padding: 20px 16px; width: 100%; }

        /* ─── PERIOD NAV ─── */
        .period-nav { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 20px; }
        .period-btn {
            width: 38px; height: 38px; border-radius: 14px; background: var(--surface);
            border: 1px solid var(--border); display: flex; align-items: center; justify-content: center;
            color: var(--text); cursor: pointer; transition: 0.3s; font-size: 13px;
        }
        .period-btn:active { background: var(--primary); transform: scale(0.9); }
        .period-display { display: flex; flex-direction: column; align-items: center; gap: 2px; min-width: 120px; }
        .period-month {
            font-size: 20px; font-weight: 800; font-family: 'Outfit'; letter-spacing: -0.5px;
            cursor: pointer; transition: 0.3s; padding: 4px 12px; border-radius: 10px;
        }
        .period-month:hover { background: var(--surface); }
        .period-year { font-size: 12px; font-weight: 600; color: var(--text-dim); letter-spacing: 1px; cursor: pointer; padding: 2px 10px; border-radius: 8px; }
        .period-year:hover { background: var(--surface); color: var(--primary); }
        .period-today { font-size: 10px; font-weight: 700; color: var(--primary); cursor: pointer; padding: 6px 14px; border-radius: 20px; background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.2); transition: 0.3s; white-space: nowrap; }
        .period-today:active { background: var(--primary); color: white; }

        /* ─── SUMMARY CARDS ─── */
        .summary-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        .summary-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 20px;
            padding: 16px 12px; text-align: center; backdrop-filter: blur(20px);
            transition: 0.3s; cursor: pointer; position: relative; overflow: hidden;
        }
        .summary-card::before { content: ''; position: absolute; top: -20px; right: -20px; width: 60px; height: 60px; border-radius: 50%; opacity: 0.1; transition: 0.4s; }
        .summary-card.income::before { background: var(--income); }
        .summary-card.expense::before { background: var(--expense); }
        .summary-card.balance::before { background: var(--secondary); }
        .summary-card:active { transform: scale(0.96); }
        .summary-card .s-icon { font-size: 18px; margin-bottom: 6px; display: block; }
        .summary-card.income .s-icon { color: var(--income); }
        .summary-card.expense .s-icon { color: var(--expense); }
        .summary-card.balance .s-icon { color: var(--secondary); }
        .summary-card .s-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-dim); }
        .summary-card .s-value { font-size: 18px; font-weight: 800; font-family: 'Outfit'; margin-top: 4px; display: block; }
        .summary-card.income .s-value { color: var(--income); }
        .summary-card.expense .s-value { color: var(--expense); }
        .summary-card.balance .s-value { color: var(--secondary); }

        /* ─── TABS ─── */
        .tab-row { display: flex; background: var(--surface); border-radius: 16px; padding: 4px; margin-bottom: 20px; border: 1px solid var(--border); }
        .tab-btn {
            flex: 1; padding: 12px; text-align: center; border-radius: 13px;
            font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.3s;
            color: var(--text-dim); border: none; background: transparent;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .tab-btn.active { background: var(--primary); color: white; box-shadow: 0 8px 20px var(--primary-glow); }

        /* ─── ITEM CHIPS FOR QUICK ADD ─── */
        .quick-items-section { margin-bottom: 20px; }
        .quick-items-title {
            font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px;
            color: var(--text-dim); margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;
        }
        .quick-items-title a { color: var(--primary); font-size: 10px; text-decoration: none; cursor: pointer; }
        .quick-item-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
        .quick-item-chip {
            background: var(--surface); border: 1px solid var(--border); border-radius: 16px;
            padding: 14px 8px; text-align: center; cursor: pointer; transition: 0.25s;
            display: flex; flex-direction: column; align-items: center; gap: 6px;
        }
        .quick-item-chip:active { transform: scale(0.93); border-color: var(--primary); background: rgba(244,63,94,0.08); }
        .quick-item-chip .qic-icon {
            width: 40px; height: 40px; border-radius: 13px; display: flex; align-items: center;
            justify-content: center; font-size: 16px;
        }
        .quick-item-chip .qic-icon.inc-bg { background: rgba(16,185,129,0.15); color: var(--income); }
        .quick-item-chip .qic-icon.exp-bg { background: rgba(244,63,94,0.15); color: var(--expense); }
        .quick-item-chip .qic-name { font-size: 11px; font-weight: 700; color: var(--text); line-height: 1.2; }
        .quick-item-chip .qic-sub { font-size: 9px; color: var(--text-dim); }

        /* ─── CATEGORY CHIPS ─── */
        .cat-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
        .cat-chip {
            display: flex; align-items: center; gap: 6px; padding: 8px 14px;
            background: var(--surface); border: 1px solid var(--border); border-radius: 25px;
            font-size: 11px; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .cat-chip:active { transform: scale(0.94); border-color: var(--primary); }

        /* ─── TRANSACTION LIST ─── */
        .day-group { margin-bottom: 20px; }
        .day-header { display: flex; justify-content: space-between; align-items: center; padding: 8px 4px; margin-bottom: 8px; }
        .day-label { font-size: 13px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; }
        .day-summary { display: flex; gap: 12px; font-size: 11px; font-weight: 700; }
        .day-inc { color: var(--income); } .day-exp { color: var(--expense); }
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
        .txn-amount { font-family: 'Outfit'; font-size: 16px; font-weight: 800; flex-shrink: 0; }
        .txn-amount.inc { color: var(--income); } .txn-amount.exp { color: var(--expense); }
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-dim); }
        .empty-state i { font-size: 60px; margin-bottom: 16px; opacity: 0.3; display: block; }
        .empty-state p { font-size: 14px; font-weight: 600; }

        /* ─── FAB ─── */
        .fab {
            position: fixed; bottom: 30px; right: 24px; width: 58px; height: 58px;
            border-radius: 20px; background: linear-gradient(135deg, var(--primary), #e11d48);
            color: white; display: flex; align-items: center; justify-content: center;
            font-size: 22px; cursor: pointer; z-index: 999; border: none;
            box-shadow: 0 12px 30px var(--primary-glow); transition: 0.3s;
        }
        .fab:active { transform: scale(0.9) rotate(45deg); }

        /* ─── MODALS ─── */
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
        .modal-handle { width: 40px; height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px; margin: 0 auto 20px; }
        .modal-title { font-size: 16px; font-weight: 800; margin-bottom: 20px; text-align: center; }

        /* ─── FORM ─── */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 10px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .form-input, .form-select {
            width: 100%; padding: 15px 16px; background: rgba(0,0,0,0.25);
            border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 14px; font-family: 'Plus Jakarta Sans', sans-serif; outline: none; transition: 0.3s;
        }
        .form-input:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }

        .type-toggle { display: flex; background: rgba(0,0,0,0.2); border-radius: 14px; padding: 4px; }
        .type-opt {
            flex: 1; padding: 13px; text-align: center; border-radius: 12px;
            font-size: 13px; font-weight: 700; cursor: pointer; transition: 0.3s; color: var(--text-dim);
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .type-opt.income-sel.active { background: var(--income); color: white; }
        .type-opt.expense-sel.active { background: var(--expense); color: white; }

        /* ─── SELECTED ITEM DISPLAY ─── */
        .selected-item-display {
            display: flex; align-items: center; gap: 12px; padding: 14px 16px;
            background: rgba(244,63,94,0.08); border: 1px dashed var(--primary); border-radius: 16px;
            margin-bottom: 8px; transition: 0.3s;
        }
        .selected-item-display .sid-icon {
            width: 42px; height: 42px; border-radius: 13px; display: flex; align-items: center;
            justify-content: center; font-size: 18px;
        }
        .selected-item-display .sid-icon.inc-bg { background: rgba(16,185,129,0.2); color: var(--income); }
        .selected-item-display .sid-icon.exp-bg { background: rgba(244,63,94,0.2); color: var(--expense); }
        .selected-item-display .sid-name { font-weight: 700; font-size: 14px; flex: 1; }
        .selected-item-display .sid-clear { color: var(--text-dim); cursor: pointer; padding: 6px; }

        /* ─── ITEM PICKER GRID (in modal) ─── */
        .item-pick-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px; max-height: 200px; overflow-y: auto; }
        .item-pick-chip {
            background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 14px;
            padding: 10px 6px; text-align: center; cursor: pointer; transition: 0.2s;
            display: flex; flex-direction: column; align-items: center; gap: 4px;
        }
        .item-pick-chip.selected { border-color: var(--primary); background: rgba(244,63,94,0.12); }
        .item-pick-chip:active { transform: scale(0.92); }
        .item-pick-chip .ipc-icon { font-size: 15px; }
        .item-pick-chip .ipc-name { font-size: 10px; font-weight: 600; color: var(--text); line-height: 1.2; }
        .item-pick-add {
            background: transparent; border: 1px dashed var(--border); border-radius: 14px;
            padding: 10px 6px; text-align: center; cursor: pointer; transition: 0.2s;
            display: flex; flex-direction: column; align-items: center; gap: 4px; color: var(--text-dim);
        }
        .item-pick-add:active { border-color: var(--primary); color: var(--primary); }
        .item-pick-add .ipc-icon { font-size: 18px; }

        /* ─── ITEMS MANAGEMENT LIST ─── */
        .item-mgmt-card {
            display: flex; align-items: center; gap: 12px; padding: 14px;
            background: var(--surface); border: 1px solid var(--border); border-radius: 16px;
            margin-bottom: 8px; transition: 0.2s;
        }
        .item-mgmt-card:active { transform: scale(0.98); }
        .item-mgmt-card .imc-icon {
            width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center;
            justify-content: center; font-size: 15px; flex-shrink: 0;
        }
        .item-mgmt-card .imc-icon.inc-bg { background: rgba(16,185,129,0.15); color: var(--income); }
        .item-mgmt-card .imc-icon.exp-bg { background: rgba(244,63,94,0.15); color: var(--expense); }
        .item-mgmt-card .imc-info { flex: 1; }
        .item-mgmt-card .imc-name { font-weight: 700; font-size: 13px; }
        .item-mgmt-card .imc-type { font-size: 9px; color: var(--text-dim); text-transform: uppercase; }
        .item-mgmt-card .imc-actions { display: flex; gap: 6px; }
        .imc-btn {
            width: 32px; height: 32px; border-radius: 10px; border: 1px solid var(--border);
            background: transparent; color: var(--text-dim); cursor: pointer; display: flex;
            align-items: center; justify-content: center; font-size: 12px; transition: 0.2s;
        }
        .imc-btn.del:active { background: #ef4444; color: white; border-color: #ef4444; }
        .imc-btn.edit:active { background: var(--primary); color: white; border-color: var(--primary); }

        /* ─── BUTTONS ─── */
        .btn-row { display: flex; gap: 10px; margin-top: 20px; }
        .btn-primary {
            flex: 1; padding: 16px; border: none; border-radius: 18px;
            background: linear-gradient(135deg, var(--primary), #e11d48);
            color: white; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.3s;
            font-family: 'Plus Jakarta Sans', sans-serif; box-shadow: 0 10px 25px var(--primary-glow);
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
        .btn-sm {
            padding: 10px 18px; border-radius: 14px; border: 1px solid var(--border);
            background: var(--surface); color: var(--text); font-weight: 700; font-size: 12px;
            cursor: pointer; transition: 0.2s; font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .btn-sm:active { background: var(--primary); border-color: var(--primary); color: white; }
        .btn-sm.primary { background: var(--primary); color: white; border: none; }

        .icon-picker-row { display: flex; flex-wrap: wrap; gap: 6px; max-height: 100px; overflow-y: auto; margin-top: 6px; }
        .icon-pick {
            width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-size: 13px; cursor: pointer; background: rgba(255,255,255,0.05); border: 1px solid transparent;
            transition: 0.2s; color: var(--text-dim);
        }
        .icon-pick.selected { border-color: var(--primary); background: rgba(244,63,94,0.15); color: var(--primary); }
        .icon-pick:active { transform: scale(0.85); }

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

        .search-wrap { display: flex; gap: 8px; margin-bottom: 16px; }
        .search-input {
            flex: 1; padding: 13px 16px; background: var(--surface); border: 1px solid var(--border);
            border-radius: 16px; color: white; font-size: 13px; outline: none; transition: 0.3s;
        }
        .search-input:focus { border-color: var(--primary); }

        /* ─── QUICK ADD FILTER ─── */
        .qa-filter-row { display: flex; gap: 4px; background: rgba(0,0,0,0.15); border-radius: 10px; padding: 3px; }
        .qa-filter-btn {
            padding: 6px 13px; border-radius: 8px; border: none; font-size: 10px; font-weight: 700;
            cursor: pointer; transition: 0.2s; background: transparent; color: var(--text-dim);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .qa-filter-btn.active { background: var(--primary); color: white; }

        /* ─── CALCULATOR ─── */
        .calc-btn {
            padding: 12px 6px; border-radius: 10px; border: 1px solid var(--border);
            background: rgba(255,255,255,0.04); color: var(--text); font-size: 14px; font-weight: 700;
            cursor: pointer; transition: 0.15s; font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .calc-btn:active { background: var(--primary); border-color: var(--primary); color: white; transform: scale(0.92); }
        .calc-btn.calc-op { color: var(--primary); font-weight: 800; }
        .calc-btn.calc-eq { font-weight: 800; }

        @media (max-width: 380px) {
            .summary-row { gap: 6px; }
            .summary-card { padding: 12px 8px; }
            .summary-card .s-value { font-size: 15px; }
            .quick-item-grid, .item-pick-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<!-- ═══════════ HEADER ═══════════ -->
<header>
    <div class="header-left">
        <div class="header-back" onclick="window.location='index.html'"><i class="fa-solid fa-chevron-left"></i></div>
        <div class="header-title">💰 Daily Manager</div>
    </div>
    <div class="header-actions">
        <div class="header-btn" onclick="openItemsManager()" title="Manage Items">
            <i class="fa-solid fa-folder-tree"></i>
        </div>
        <div class="header-btn" id="btnSearch" onclick="toggleSearch()" title="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <div class="header-btn" id="btnYearView" onclick="toggleYearView()" title="Year View">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
        <div class="header-btn" onclick="toggleTheme()" title="Theme">
            <i class="fa-solid fa-palette"></i>
        </div>
        <a href="index.html" class="header-btn" title="Home"><i class="fa-solid fa-house"></i></a>
    </div>
</header>

<!-- ═══════════ MAIN ═══════════ -->
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
        <button class="tab-btn active" data-tab="all" onclick="setTab('all')"><i class="fa-solid fa-list"></i> All</button>
        <button class="tab-btn" data-tab="income" onclick="setTab('income')"><i class="fa-solid fa-arrow-down"></i> Income</button>
        <button class="tab-btn" data-tab="expense" onclick="setTab('expense')"><i class="fa-solid fa-arrow-up"></i> Expense</button>
    </div>

    <!-- Quick Add Items Grid with Filter -->
    <div class="quick-items-section" id="quickItemsSection">
        <div class="quick-items-title">
            ⚡ Quick Add
            <div class="qa-filter-row">
                <button class="qa-filter-btn active" data-qfilter="all" onclick="setQuickFilter('all')">All</button>
                <button class="qa-filter-btn" data-qfilter="income" onclick="setQuickFilter('income')">💰 Income</button>
                <button class="qa-filter-btn" data-qfilter="expense" onclick="setQuickFilter('expense')">📤 Expense</button>
            </div>
        </div>
        <div class="quick-item-grid" id="quickItemGrid"></div>
        <div style="text-align:right;margin-top:4px;">
            <a onclick="openItemsManager()" style="color:var(--text-dim);font-size:10px;font-weight:700;cursor:pointer;">⚙️ Manage Items</a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="search-wrap" id="searchWrap" style="display:none;">
        <input type="text" class="search-input" id="searchInput" placeholder="🔍 Search items or notes..." oninput="doSearch()">
    </div>

    <!-- Category Chips -->
    <div class="cat-chips" id="catChips"></div>

    <!-- Year View -->
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
<button class="fab" id="fabBtn" onclick="openQuickAdd()"> <i class="fa-solid fa-plus"></i> </button>

<!-- ═══════════ QUICK ADD MODAL ═══════════ -->
<div class="modal-overlay" id="addModalOverlay" onclick="closeAddModal(event)">
    <div class="modal-sheet" id="addModalSheet" onclick="event.stopPropagation()">
        <div class="modal-handle"></div>
        <div class="modal-title">Quick Add Transaction</div>

        <div class="form-group">
            <label class="form-label">Type</label>
            <div class="type-toggle">
                <div class="type-opt income-sel" id="typeOptIncome" onclick="setAddType('income')"><i class="fa-solid fa-arrow-down"></i> Income</div>
                <div class="type-opt expense-sel active" id="typeOptExpense" onclick="setAddType('expense')"><i class="fa-solid fa-arrow-up"></i> Expense</div>
            </div>
        </div>

        <!-- Selected Item Display -->
        <div class="form-group">
            <label class="form-label">Item</label>
            <div class="selected-item-display" id="selectedItemDisplay" style="display:none;">
                <div class="sid-icon exp-bg" id="sidIcon"><i class="fa-solid fa-tag"></i></div>
                <span class="sid-name" id="sidName">Select an item</span>
                <span class="sid-clear" onclick="clearSelectedItem()"><i class="fa-solid fa-xmark"></i></span>
            </div>
            <div id="noItemSelected" style="padding:12px;text-align:center;color:var(--text-dim);font-size:12px;background:rgba(255,255,255,0.02);border-radius:14px;">
                👆 Tap an item below or add new
            </div>
        </div>

        <!-- Item Picker Grid -->
        <div class="form-group">
            <label class="form-label">Choose Item</label>
            <div class="item-pick-grid" id="addItemGrid"></div>
            <div style="text-align:center;margin-top:8px;">
                <button class="btn-sm" onclick="openNewItemForm()">+ New Item</button>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Amount (₹)</label>
            <input type="number" class="form-input" id="addAmount" placeholder="0.00" step="0.01" min="0" inputmode="decimal">
            <button class="btn-sm" onclick="toggleCalc()" style="margin-top:6px;width:100%;" id="calcToggle">🧮 Calculator</button>
            <div class="calc-wrap" id="calcWrap" style="display:none;margin-top:8px;background:rgba(0,0,0,0.15);border-radius:14px;padding:10px;">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:5px;">
                    <button class="calc-btn" onclick="calcInput('7')">7</button>
                    <button class="calc-btn" onclick="calcInput('8')">8</button>
                    <button class="calc-btn" onclick="calcInput('9')">9</button>
                    <button class="calc-btn calc-op" onclick="calcOp('/')">÷</button>
                    <button class="calc-btn" onclick="calcInput('4')">4</button>
                    <button class="calc-btn" onclick="calcInput('5')">5</button>
                    <button class="calc-btn" onclick="calcInput('6')">6</button>
                    <button class="calc-btn calc-op" onclick="calcOp('*')">×</button>
                    <button class="calc-btn" onclick="calcInput('1')">1</button>
                    <button class="calc-btn" onclick="calcInput('2')">2</button>
                    <button class="calc-btn" onclick="calcInput('3')">3</button>
                    <button class="calc-btn calc-op" onclick="calcOp('-')">−</button>
                    <button class="calc-btn" onclick="calcInput('0')">0</button>
                    <button class="calc-btn" onclick="calcInput('00')">00</button>
                    <button class="calc-btn" onclick="calcInput('.')">.</button>
                    <button class="calc-btn calc-op" onclick="calcOp('+')">+</button>
                </div>
                <div style="display:flex;gap:5px;margin-top:5px;">
                    <button class="calc-btn calc-clr" onclick="calcClear()" style="flex:1;background:rgba(239,68,68,0.2);color:#ef4444;">C</button>
                    <button class="calc-btn calc-eq" onclick="calcEquals()" style="flex:2;background:var(--primary);color:white;">=</button>
                </div>
                <div style="font-size:10px;color:var(--text-dim);text-align:right;margin-top:4px;min-height:14px;" id="calcExpr"></div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Date</label>
            <input type="date" class="form-input" id="addDate">
        </div>
        <div class="form-group">
            <label class="form-label">Note (Optional)</label>
            <input type="text" class="form-input" id="addNote" placeholder="Any remark...">
        </div>

        <input type="hidden" id="addSelectedItem" value="">
        <input type="hidden" id="addSelectedIcon" value="">
        <input type="hidden" id="editTxnId" value="">

        <div class="btn-row">
            <button class="btn-secondary" onclick="closeAddModal()">Cancel</button>
            <button class="btn-primary" id="btnSaveTxn" onclick="saveQuickTransaction()">💾 Save</button>
        </div>
        <div style="text-align:center;margin-top:10px;">
            <button class="btn-danger" id="btnDeleteTxn" style="display:none;" onclick="deleteEditingTxn()"><i class="fa-solid fa-trash"></i> Delete</button>
        </div>
    </div>
</div>

<!-- ═══════════ ITEMS MANAGER MODAL ═══════════ -->
<div class="modal-overlay" id="itemsModalOverlay" onclick="closeItemsManager(event)">
    <div class="modal-sheet" id="itemsModalSheet" onclick="event.stopPropagation()">
        <div class="modal-handle"></div>
        <div class="modal-title">📂 Manage Items</div>

        <!-- Add New Item Form (collapsed by default) -->
        <div id="newItemForm" style="display:none;margin-bottom:16px;padding:16px;background:var(--surface);border-radius:18px;border:1px solid var(--border);">
            <div class="form-group">
                <label class="form-label">Item Name</label>
                <input type="text" class="form-input" id="newItemName" placeholder="e.g. Salary, Rent, Food..." oninput="onNewItemNameInput()">
            </div>
            <div class="form-group">
                <label class="form-label">Type</label>
                <div class="type-toggle">
                    <div class="type-opt income-sel" id="newItemTypeIncome" onclick="setNewItemType('income')"><i class="fa-solid fa-arrow-down"></i> Income</div>
                    <div class="type-opt expense-sel active" id="newItemTypeExpense" onclick="setNewItemType('expense')"><i class="fa-solid fa-arrow-up"></i> Expense</div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Icon <span id="newItemAutoIconHint" style="color:var(--primary);font-weight:400;">(auto)</span></label>
                <input type="hidden" id="newItemIcon" value="">
                <div class="icon-picker-row" id="newItemIconPicker"></div>
            </div>
            <input type="hidden" id="editItemId" value="">
            <div class="btn-row">
                <button class="btn-secondary" onclick="cancelNewItem()">Cancel</button>
                <button class="btn-primary" id="btnSaveItem" onclick="saveItem()">💾 Save Item</button>
            </div>
        </div>

        <!-- Show Add Button (when form hidden) -->
        <div id="showAddItemBtn" style="margin-bottom:16px;">
            <button class="btn-sm primary" onclick="openNewItemForm()" style="width:100%;">+ Add New Item</button>
        </div>

        <!-- Items List -->
        <div id="itemsListContainer"></div>
    </div>
</div>

<!-- ═══════════ SCRIPTS ═══════════ -->
<script>
    // ─── STATE ───
    let currentMonth = new Date().getMonth() + 1;
    let currentYear = new Date().getFullYear();
    let currentTab = 'all';
    let addType = 'expense';
    let newItemType = 'expense';
    let isYearView = false;
    let savedItems = [];
    let allTransactions = [];
    let quickFilterType = 'all';
    let calcExpr = '';
    let calcResetOnInput = false;

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
        document.getElementById('addDate').value = new Date().toISOString().split('T')[0];
        updatePeriodDisplay();
        loadAll();
    };

    function updatePeriodDisplay() {
        document.getElementById('periodMonth').innerText = MONTHS[currentMonth - 1];
        document.getElementById('periodYear').innerText = currentYear;
    }

    // ─── PERIOD NAV ───
    function changeMonth(delta) {
        currentMonth += delta;
        if (currentMonth < 1) { currentMonth = 12; currentYear--; }
        if (currentMonth > 12) { currentMonth = 1; currentYear++; }
        updatePeriodDisplay(); loadAll();
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
        document.querySelectorAll('#tabRow .tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelector(`#tabRow [data-tab="${tab}"]`).classList.add('active');
        renderTransactions();
    }
    function filterByType(type) { setTab(type); }

    // ─── YEAR VIEW ───
    function toggleYearView() {
        isYearView = !isYearView;
        document.getElementById('yearView').style.display = isYearView ? 'block' : 'none';
        document.getElementById('catChips').style.display = isYearView ? 'none' : 'flex';
        document.getElementById('txnList').style.display = isYearView ? 'none' : 'block';
        document.getElementById('quickItemsSection').style.display = isYearView ? 'none' : 'block';
        document.getElementById('btnYearView').classList.toggle('active', isYearView);
        if (isYearView) { document.getElementById('yearViewLabel').innerText = currentYear; loadYearView(); }
    }
    async function loadYearView() {
        const res = await fetch(`api_daily_expense.php?action=get_summary&month=${currentMonth}&year=${currentYear}`);
        const data = await res.json();
        if (data.status !== 'ok') return;
        document.getElementById('yearViewLabel').innerText = currentYear;
        document.getElementById('yearGrid').innerHTML = data.mom.map(m => `
            <div class="year-month-card" onclick="currentMonth=${m.month};updatePeriodDisplay();toggleYearView();loadAll();">
                <div class="ym-name">${MONTHS[m.month-1].substring(0,3)}</div>
                <div class="ym-inc">+₹${m.income.toLocaleString()}</div>
                <div class="ym-exp">-₹${m.expense.toLocaleString()}</div>
            </div>`).join('');
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
        if (data.status === 'ok') { allTransactions = data.results; renderTransactions(); }
    }

    // ─── LOAD ALL ───
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
            updateCategoryChips(summaryData);
            renderQuickItems();
            renderTransactions();
            renderAddItemGrid();
            renderItemsManagerList();
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

    function updateCategoryChips(data) {
        if (!data || data.status !== 'ok') return;
        const cats = data.categories || [];
        const container = document.getElementById('catChips');
        if (cats.length === 0) { container.innerHTML = ''; return; }
        container.innerHTML = cats.slice(0, 8).map(c => `
            <div class="cat-chip" onclick="filterByItem('${c.item_name.replace(/'/g, "\\'")}')">
                <i class="fa-solid ${c.icon || 'fa-tag'}" style="color:${c.type==='income'?'var(--income)':'var(--expense)'};font-size:13px;"></i>
                <span>${c.item_name}</span>
                <span style="font-family:'Outfit';font-weight:700;color:${c.type==='income'?'var(--income)':'var(--expense)'};">₹${parseFloat(c.total).toLocaleString()}</span>
            </div>`).join('');
    }
    function filterByItem(itemName) {
        document.getElementById('searchInput').value = itemName;
        document.getElementById('searchWrap').style.display = 'flex';
        document.getElementById('btnSearch').classList.add('active');
        doSearch();
    }

    // ─── QUICK FILTER ───
    function setQuickFilter(type) {
        quickFilterType = type;
        document.querySelectorAll('.qa-filter-btn').forEach(b => {
            b.classList.toggle('active', b.dataset.qfilter === type);
        });
        renderQuickItems();
    }

    // ─── QUICK ITEMS GRID (on main page) ───
    function renderQuickItems() {
        const grid = document.getElementById('quickItemGrid');
        let items = savedItems;
        if (quickFilterType === 'income') items = items.filter(i => i.type === 'income' || i.type === 'both');
        else if (quickFilterType === 'expense') items = items.filter(i => i.type === 'expense' || i.type === 'both');

        if (items.length === 0) {
            grid.innerHTML = `<div class="item-pick-add" onclick="openItemsManager()" style="grid-column:1/-1;">
                <div class="ipc-icon"><i class="fa-solid fa-plus-circle"></i></div>
                <div class="ipc-name">Add Items</div>
            </div>`;
            return;
        }
        grid.innerHTML = items.slice(0, 9).map(item => `
            <div class="quick-item-chip" onclick="quickAddFromItem('${item.name.replace(/'/g, "\\'")}', '${item.icon}', '${item.type}')">
                <div class="qic-icon ${item.type === 'income' ? 'inc-bg' : 'exp-bg'}">
                    <i class="fa-solid ${item.icon || 'fa-tag'}"></i>
                </div>
                <div class="qic-name">${item.name}</div>
            </div>`).join('');
    }

    function quickAddFromItem(name, icon, itemType) {
        addType = itemType === 'income' ? 'income' : 'expense';
        setAddTypeUI(addType);
        selectAddItem(name, icon);
        document.getElementById('addItemGrid').parentElement.style.display = 'none';
        document.getElementById('addAmount').value = '';
        document.getElementById('addNote').value = '';
        document.getElementById('editTxnId').value = '';
        document.getElementById('btnDeleteTxn').style.display = 'none';
        document.getElementById('btnSaveTxn').innerHTML = '💾 Save';
        calcClear();
        document.getElementById('calcWrap').style.display = 'none';
        document.getElementById('calcToggle').innerHTML = '🧮 Calculator';
        document.getElementById('addModalOverlay').classList.add('show');
        setTimeout(() => document.getElementById('addAmount').focus(), 400);
    }

    // ─── ADD MODAL ───
    function openQuickAdd() {
        addType = 'expense';
        setAddTypeUI(addType);
        clearSelectedItem();
        document.getElementById('addAmount').value = '';
        document.getElementById('addDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('addNote').value = '';
        document.getElementById('editTxnId').value = '';
        document.getElementById('btnDeleteTxn').style.display = 'none';
        document.getElementById('btnSaveTxn').innerHTML = '💾 Save';
        document.getElementById('addItemGrid').parentElement.style.display = 'block';
        renderAddItemGrid();
        calcClear();
        document.getElementById('calcWrap').style.display = 'none';
        document.getElementById('calcToggle').innerHTML = '🧮 Calculator';
        document.getElementById('addModalOverlay').classList.add('show');
    }

    function openEditTxnModal(id) {
        const txn = allTransactions.find(t => t.id == id);
        if (!txn) return;
        addType = txn.type;
        setAddTypeUI(addType);
        selectAddItem(txn.item_name, txn.icon || 'fa-tag');
        document.getElementById('addAmount').value = txn.amount;
        document.getElementById('addDate').value = txn.transaction_date;
        document.getElementById('addNote').value = txn.note || '';
        document.getElementById('editTxnId').value = txn.id;
        document.getElementById('btnDeleteTxn').style.display = 'block';
        document.getElementById('btnSaveTxn').innerHTML = '💾 Update';
        renderAddItemGrid();
        document.getElementById('addModalOverlay').classList.add('show');
    }

    function closeAddModal(e) {
        if (e && e.target !== document.getElementById('addModalOverlay')) return;
        document.getElementById('addModalOverlay').classList.remove('show');
        document.querySelectorAll('.txn-item').forEach(el => el.classList.remove('swiped'));
    }

    function setAddType(type) {
        addType = type;
        setAddTypeUI(type);
        renderAddItemGrid();
    }
    function setAddTypeUI(type) {
        document.getElementById('typeOptIncome').classList.toggle('active', type === 'income');
        document.getElementById('typeOptExpense').classList.toggle('active', type === 'expense');
        addType = type;
    }

    function selectAddItem(name, icon) {
        document.getElementById('addSelectedItem').value = name;
        document.getElementById('addSelectedIcon').value = icon;
        document.getElementById('selectedItemDisplay').style.display = 'flex';
        document.getElementById('noItemSelected').style.display = 'none';
        document.getElementById('sidName').innerText = name;
        const sidIcon = document.getElementById('sidIcon');
        sidIcon.className = 'sid-icon ' + (addType === 'income' ? 'inc-bg' : 'exp-bg');
        sidIcon.innerHTML = `<i class="fa-solid ${icon || 'fa-tag'}"></i>`;
        renderAddItemGrid();
    }

    function clearSelectedItem() {
        document.getElementById('addSelectedItem').value = '';
        document.getElementById('addSelectedIcon').value = '';
        document.getElementById('selectedItemDisplay').style.display = 'none';
        document.getElementById('noItemSelected').style.display = 'block';
        document.getElementById('addItemGrid').parentElement.style.display = 'block';
        renderAddItemGrid();
    }

    function renderAddItemGrid() {
        const grid = document.getElementById('addItemGrid');
        let items = savedItems.filter(i => i.type === addType || i.type === 'both');
        const selectedName = document.getElementById('addSelectedItem').value;

        grid.innerHTML = items.map(item => `
            <div class="item-pick-chip ${item.name === selectedName ? 'selected' : ''}" 
                 onclick="selectAddItem('${item.name.replace(/'/g, "\\'")}', '${item.icon || 'fa-tag'}')">
                <div class="ipc-icon" style="color:${addType==='income'?'var(--income)':'var(--expense)'};">
                    <i class="fa-solid ${item.icon || 'fa-tag'}"></i>
                </div>
                <div class="ipc-name">${item.name}</div>
            </div>`).join('') + `
            <div class="item-pick-add" onclick="openNewItemForm();document.getElementById('addModalOverlay').classList.remove('show');">
                <div class="ipc-icon"><i class="fa-solid fa-plus-circle"></i></div>
                <div class="ipc-name">New</div>
            </div>`;
    }

    async function saveQuickTransaction() {
        const itemName = document.getElementById('addSelectedItem').value;
        const icon = document.getElementById('addSelectedIcon').value;
        const amount = parseFloat(document.getElementById('addAmount').value);
        const date = document.getElementById('addDate').value;
        const note = document.getElementById('addNote').value.trim();
        const editId = document.getElementById('editTxnId').value;

        if (!itemName) { alert('Please select an item'); return; }
        if (!amount || amount <= 0) { alert('Please enter valid amount'); return; }
        if (!date) { alert('Please select date'); return; }

        const fd = new FormData();
        fd.append('action', editId ? 'update_transaction' : 'add_transaction');
        if (editId) fd.append('id', editId);
        fd.append('type', addType);
        fd.append('item_name', itemName);
        fd.append('icon', icon);
        fd.append('amount', amount);
        fd.append('date', date);
        fd.append('note', note);

        try {
            const res = await fetch('api_daily_expense.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'ok') { closeAddModal(); loadAll(); }
            else { alert(data.error || 'Error saving'); }
        } catch(e) { alert('Network error'); }
    }

    async function deleteEditingTxn() {
        const id = document.getElementById('editTxnId').value;
        if (!id || !confirm('Delete this transaction?')) return;
        const fd = new FormData();
        fd.append('action', 'delete_transaction');
        fd.append('id', id);
        await fetch('api_daily_expense.php', { method: 'POST', body: fd });
        closeAddModal();
        loadAll();
    }

    async function deleteTransactionDirect(id) {
        if (!confirm('Delete this transaction?')) return;
        const fd = new FormData();
        fd.append('action', 'delete_transaction');
        fd.append('id', id);
        await fetch('api_daily_expense.php', { method: 'POST', body: fd });
        loadAll();
    }

    // ─── RENDER TRANSACTIONS ───
    function renderTransactions() {
        const container = document.getElementById('txnList');
        let filtered = allTransactions;
        if (currentTab !== 'all') filtered = allTransactions.filter(t => t.type === currentTab);

        if (filtered.length === 0) {
            container.innerHTML = `<div class="empty-state"><i class="fa-solid fa-file-invoice"></i><p>No transactions found</p><span style="font-size:11px;color:var(--text-dim);">Tap + to add your first entry</span></div>`;
            return;
        }

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

            return `<div class="day-group">
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
                         onclick="openEditTxnModal(${t.id})">
                        <div class="txn-icon ${t.type}-bg"><i class="fa-solid ${t.icon || 'fa-tag'}"></i></div>
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
                    </div>`).join('')}
            </div>`;
        }).join('');
    }

    // ─── SWIPE ───
    let touchStartX = 0;
    function touchStart(e, id) { touchStartX = e.touches[0].clientX; }
    function touchEnd(e, id) {
        const diff = touchStartX - e.changedTouches[0].clientX;
        const el = document.getElementById('txn-' + id);
        if (diff > 60) el.classList.add('swiped');
        else if (diff < -30) el.classList.remove('swiped');
    }

    // ─── ITEMS MANAGER ───
    function openItemsManager() {
        document.getElementById('editItemId').value = '';
        document.getElementById('newItemName').value = '';
        document.getElementById('newItemIcon').value = '';
        document.getElementById('newItemAutoIconHint').style.display = 'inline';
        document.getElementById('newItemForm').style.display = 'none';
        document.getElementById('showAddItemBtn').style.display = 'block';
        setNewItemTypeUI('expense');
        renderNewItemIconPicker('');
        renderItemsManagerList();
        document.getElementById('itemsModalOverlay').classList.add('show');
    }

    function closeItemsManager(e) {
        if (e && e.target !== document.getElementById('itemsModalOverlay')) return;
        document.getElementById('itemsModalOverlay').classList.remove('show');
        loadAll();
    }

    function renderItemsManagerList() {
        const container = document.getElementById('itemsListContainer');
        if (savedItems.length === 0) {
            container.innerHTML = `<div class="empty-state" style="padding:30px;"><i class="fa-solid fa-folder-open" style="font-size:40px;"></i><p>No items yet</p><span style="font-size:11px;">Add your first expense/income item</span></div>`;
            return;
        }
        container.innerHTML = savedItems.map(item => `
            <div class="item-mgmt-card">
                <div class="imc-icon ${item.type === 'income' ? 'inc-bg' : 'exp-bg'}">
                    <i class="fa-solid ${item.icon || 'fa-tag'}"></i>
                </div>
                <div class="imc-info">
                    <div class="imc-name">${item.name}</div>
                    <div class="imc-type">${item.type.toUpperCase()}</div>
                </div>
                <div class="imc-actions">
                    <button class="imc-btn edit" onclick="editItem(${item.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="imc-btn del" onclick="deleteItem(${item.id})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>`).join('');
    }

    function openNewItemForm() {
        document.getElementById('editItemId').value = '';
        document.getElementById('newItemName').value = '';
        document.getElementById('newItemIcon').value = '';
        document.getElementById('newItemAutoIconHint').style.display = 'inline';
        document.getElementById('newItemForm').style.display = 'block';
        document.getElementById('showAddItemBtn').style.display = 'none';
        document.getElementById('btnSaveItem').innerHTML = '💾 Save Item';
        setNewItemTypeUI('expense');
        renderNewItemIconPicker('');
        setTimeout(() => document.getElementById('newItemName').focus(), 300);
    }

    function cancelNewItem() {
        document.getElementById('newItemForm').style.display = 'none';
        document.getElementById('showAddItemBtn').style.display = 'block';
    }

    function editItem(id) {
        const item = savedItems.find(i => i.id == id);
        if (!item) return;
        document.getElementById('editItemId').value = item.id;
        document.getElementById('newItemName').value = item.name;
        document.getElementById('newItemIcon').value = item.icon || '';
        document.getElementById('newItemAutoIconHint').style.display = 'none';
        document.getElementById('newItemForm').style.display = 'block';
        document.getElementById('showAddItemBtn').style.display = 'none';
        document.getElementById('btnSaveItem').innerHTML = '💾 Update Item';
        setNewItemTypeUI(item.type === 'income' ? 'income' : 'expense');
        renderNewItemIconPicker(item.icon || '');
    }

    function setNewItemType(type) {
        newItemType = type;
        setNewItemTypeUI(type);
    }
    function setNewItemTypeUI(type) {
        document.getElementById('newItemTypeIncome').classList.toggle('active', type === 'income');
        document.getElementById('newItemTypeExpense').classList.toggle('active', type === 'expense');
        newItemType = type;
    }

    function renderNewItemIconPicker(selectedIcon) {
        const picker = document.getElementById('newItemIconPicker');
        picker.innerHTML = COMMON_ICONS.map(ic => `
            <div class="icon-pick ${ic === selectedIcon ? 'selected' : ''}" onclick="selectNewItemIcon('${ic}')" title="${ic.replace('fa-','')}">
                <i class="fa-solid ${ic}"></i>
            </div>`).join('');
    }

    function selectNewItemIcon(icon) {
        document.getElementById('newItemIcon').value = icon;
        document.getElementById('newItemAutoIconHint').style.display = 'none';
        renderNewItemIconPicker(icon);
    }

    async function onNewItemNameInput() {
        const name = document.getElementById('newItemName').value.trim();
        if (!name || document.getElementById('newItemIcon').value) return;
        try {
            const res = await fetch(`api_daily_expense.php?action=auto_icon&name=${encodeURIComponent(name)}`);
            const data = await res.json();
            if (data.status === 'ok' && data.icon) {
                document.getElementById('newItemIcon').value = data.icon;
                renderNewItemIconPicker(data.icon);
            }
        } catch(e) {}
    }

    async function saveItem() {
        const id = document.getElementById('editItemId').value;
        const name = document.getElementById('newItemName').value.trim();
        const icon = document.getElementById('newItemIcon').value;
        if (!name) { alert('Please enter item name'); return; }

        const fd = new FormData();
        fd.append('action', id ? 'update_item' : 'add_item');
        if (id) fd.append('id', id);
        fd.append('name', name);
        fd.append('icon', icon);
        fd.append('type', newItemType);

        try {
            const res = await fetch('api_daily_expense.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'ok') {
                cancelNewItem();
                // Reload items
                const itemsRes = await fetch('api_daily_expense.php?action=get_items');
                const itemsData = await itemsRes.json();
                if (itemsData.status === 'ok') savedItems = itemsData.items;
                renderItemsManagerList();
                renderQuickItems();
                renderAddItemGrid();
            } else {
                alert(data.error || 'Error saving item');
            }
        } catch(e) { alert('Network error'); }
    }

    async function deleteItem(id) {
        if (!confirm('Delete this item? (Transactions using it will remain)')) return;
        const fd = new FormData();
        fd.append('action', 'delete_item');
        fd.append('id', id);
        await fetch('api_daily_expense.php', { method: 'POST', body: fd });
        const itemsRes = await fetch('api_daily_expense.php?action=get_items');
        const itemsData = await itemsRes.json();
        if (itemsData.status === 'ok') savedItems = itemsData.items;
        renderItemsManagerList();
        renderQuickItems();
        renderAddItemGrid();
    }

    // ─── CALCULATOR ───
    function toggleCalc() {
        const wrap = document.getElementById('calcWrap');
        const btn = document.getElementById('calcToggle');
        if (wrap.style.display === 'none') {
            wrap.style.display = 'block';
            btn.innerHTML = '🔽 Hide Calculator';
        } else {
            wrap.style.display = 'none';
            btn.innerHTML = '🧮 Calculator';
        }
    }
    function calcInput(val) {
        const inp = document.getElementById('addAmount');
        if (calcResetOnInput) { inp.value = ''; calcResetOnInput = false; }
        if (val === '.' && inp.value.includes('.')) return;
        inp.value += val;
        inp.focus();
    }
    function calcOp(op) {
        const inp = document.getElementById('addAmount');
        const currentVal = parseFloat(inp.value) || 0;
        calcExpr = currentVal + ' ' + op + ' ';
        document.getElementById('calcExpr').innerText = calcExpr;
        inp.value = '';
        calcResetOnInput = false;
        inp.focus();
    }
    function calcClear() {
        document.getElementById('addAmount').value = '';
        calcExpr = '';
        document.getElementById('calcExpr').innerText = '';
        calcResetOnInput = false;
    }
    function calcEquals() {
        const inp = document.getElementById('addAmount');
        const currentVal = parseFloat(inp.value) || 0;
        if (calcExpr) {
            const parts = calcExpr.trim().split(' ');
            const prevVal = parseFloat(parts[0]) || 0;
            const op = parts[1];
            let result = 0;
            switch(op) {
                case '+': result = prevVal + currentVal; break;
                case '-': result = prevVal - currentVal; break;
                case '*': result = prevVal * currentVal; break;
                case '/': result = currentVal !== 0 ? prevVal / currentVal : 0; break;
                default: result = currentVal;
            }
            document.getElementById('calcExpr').innerText = calcExpr + currentVal + ' = ' + result.toFixed(2);
            inp.value = result.toFixed(2);
            calcExpr = '';
            calcResetOnInput = true;
        }
        inp.focus();
    }

    // ─── KEYBOARD ───
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeAddModal();
            closeItemsManager();
        }
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); toggleSearch(); }
    });
</script>
</body>
</html>
