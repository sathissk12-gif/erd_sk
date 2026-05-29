<?php
/**
 * 📬 NOTIFICATION CENTER v1.0
 * Full notification inbox with read/unread tracking, filtering, and management
 */
include 'db_connect.php';

// Load settings
$settings = [];
$res = $conn->query("SELECT key_name, key_value FROM system_settings")->fetchAll(PDO::FETCH_ASSOC);
foreach ($res as $row) { $settings[$row['key_name']] = $row['key_value']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=0">
    <title>Notification Center | SK LOGIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #8b5cf6; --secondary: #06b6d4; --bg: #030712; --surface: rgba(15, 23, 42, 0.6); --border: rgba(255, 255, 255, 0.08); }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: radial-gradient(circle at top right, #1e1b4b, #030712); color: white; min-height: 100vh; padding-bottom: 100px; }
        .container { max-width: 720px; margin: 0 auto; padding: 16px; }

        /* Header */
        .header { position: sticky; top: 0; z-index: 100; background: rgba(3,7,18,0.85); backdrop-filter: blur(25px); padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
        .header h1 { font-size: 18px; font-weight: 800; flex: 1; }
        .header .count-badge { font-size: 11px; background: var(--primary); padding: 4px 12px; border-radius: 99px; font-weight: 700; }
        .back-btn { width: 38px; height: 38px; border-radius: 12px; background: var(--surface); border: 1px solid var(--border); color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px; transition: 0.2s; }
        .back-btn:active { transform: scale(0.9); }

        /* Filter Tabs */
        .filter-bar { display: flex; gap: 8px; padding: 12px 0; overflow-x: auto; scrollbar-width: none; }
        .filter-bar::-webkit-scrollbar { display: none; }
        .filter-tab { padding: 8px 16px; border-radius: 99px; border: 1px solid var(--border); background: transparent; color: #94a3b8; font-size: 11px; font-weight: 700; cursor: pointer; white-space: nowrap; transition: 0.2s; font-family: inherit; }
        .filter-tab.active { background: var(--primary); color: white; border-color: var(--primary); }
        .filter-tab:active { transform: scale(0.95); }

        /* Action Bar */
        .action-bar { display: flex; gap: 8px; padding: 4px 0 12px; }
        .action-btn { padding: 8px 14px; border-radius: 10px; border: 1px solid var(--border); background: var(--surface); color: #94a3b8; font-size: 10px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s; font-family: inherit; }
        .action-btn:active { transform: scale(0.95); }
        .action-btn.danger { border-color: rgba(239,68,68,0.3); color: #ef4444; }
        .action-btn.primary { background: var(--primary); color: white; border-color: var(--primary); }

        /* Notification List */
        .notif-list { display: flex; flex-direction: column; gap: 8px; }
        .notif-item {
            display: flex; gap: 14px; padding: 16px; border-radius: 16px;
            background: var(--surface); border: 1px solid var(--border);
            transition: 0.2s; cursor: pointer; position: relative;
        }
        .notif-item:active { transform: scale(0.98); }
        .notif-item.unread { border-left: 3px solid var(--primary); background: rgba(139, 92, 246, 0.06); }
        .notif-item.read { opacity: 0.7; }

        .notif-icon {
            width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .notif-icon.type-appointment { background: rgba(139,92,246,0.15); color: #8b5cf6; }
        .notif-icon.type-renewal { background: rgba(236,72,153,0.15); color: #ec4899; }
        .notif-icon.type-payment { background: rgba(16,185,129,0.15); color: #10b981; }
        .notif-icon.type-lead { background: rgba(24,119,242,0.15); color: #1877F2; }
        .notif-icon.type-general { background: rgba(99,102,241,0.15); color: #6366f1; }

        .notif-content { flex: 1; min-width: 0; }
        .notif-title { font-size: 13px; font-weight: 700; margin-bottom: 3px; display: flex; align-items: center; gap: 6px; }
        .notif-title .unread-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--primary); flex-shrink: 0; }
        .notif-body { font-size: 12px; color: #94a3b8; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .notif-time { font-size: 10px; color: #64748b; margin-top: 6px; display: flex; align-items: center; gap: 8px; }
        .notif-type-tag { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; padding: 2px 8px; border-radius: 99px; font-weight: 700; }
        .tag-appointment { background: rgba(139,92,246,0.12); color: #8b5cf6; }
        .tag-renewal { background: rgba(236,72,153,0.12); color: #ec4899; }
        .tag-payment { background: rgba(16,185,129,0.12); color: #10b981; }
        .tag-lead { background: rgba(24,119,242,0.12); color: #1877F2; }
        .tag-general { background: rgba(99,102,241,0.12); color: #6366f1; }

        .notif-actions { display: flex; gap: 4px; flex-shrink: 0; align-items: flex-start; }
        .notif-action-btn { width: 30px; height: 30px; border-radius: 8px; border: none; background: transparent; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; transition: 0.2s; }
        .notif-action-btn:hover { background: rgba(255,255,255,0.05); color: white; }
        .notif-action-btn.danger:hover { background: rgba(239,68,68,0.1); color: #ef4444; }

        /* Empty State */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 48px; color: var(--border); margin-bottom: 16px; }
        .empty-state h3 { font-size: 18px; color: #64748b; font-weight: 600; }
        .empty-state p { font-size: 13px; color: #475569; margin-top: 8px; }

        /* Pagination */
        .pagination { display: flex; justify-content: center; gap: 8px; padding: 20px 0; }
        .page-btn { padding: 8px 16px; border-radius: 10px; border: 1px solid var(--border); background: var(--surface); color: #94a3b8; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s; font-family: inherit; }
        .page-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
        .page-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        /* Loading */
        .loading { text-align: center; padding: 40px; color: #64748b; }
        .loading i { font-size: 24px; margin-bottom: 10px; }

        /* Toast */
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%); background: rgba(16,185,129,0.9); color: white; padding: 12px 24px; border-radius: 12px; font-weight: 700; font-size: 13px; z-index: 9999; animation: toastIn 0.3s ease-out; display: none; }
        .toast.error { background: rgba(239,68,68,0.9); }
        @keyframes toastIn { from { opacity: 0; transform: translateX(-50%) translateY(20px); } to { opacity: 1; transform: translateX(-50%) translateY(0); } }

        @media (max-width: 600px) {
            .container { padding: 12px; }
            .notif-item { padding: 12px; }
            .notif-icon { width: 36px; height: 36px; font-size: 14px; }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <a href="index.html" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <h1><i class="fa-solid fa-bell" style="color:var(--primary)"></i> Notifications</h1>
    <span class="count-badge" id="unreadBadge">0</span>
</div>

<div class="container">
    <!-- Filter Tabs -->
    <div class="filter-bar" id="filterBar">
        <button class="filter-tab active" data-type="all">📬 All</button>
        <button class="filter-tab" data-type="appointment">📅 Appointment</button>
        <button class="filter-tab" data-type="renewal">🔄 Renewal</button>
        <button class="filter-tab" data-type="payment">💰 Payment</button>
        <button class="filter-tab" data-type="lead">📘 Lead</button>
        <button class="filter-tab" data-type="general">📋 General</button>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <button class="action-btn primary" onclick="markAllRead()"><i class="fa-solid fa-check-double"></i> Mark All Read</button>
        <button class="action-btn" onclick="refreshList()"><i class="fa-solid fa-rotate"></i> Refresh</button>
        <button class="action-btn danger" onclick="clearAll()"><i class="fa-solid fa-trash-can"></i> Clear All</button>
    </div>

    <!-- Notification List -->
    <div id="notifList" class="notif-list">
        <div class="loading"><i class="fa-solid fa-spinner fa-spin"></i><br>Loading notifications...</div>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="pagination"></div>
</div>

<!-- Toast -->
<div id="toast" class="toast"></div>

<script>
let currentPage = 1;
let currentType = 'all';
let totalPages = 1;

// 🎨 Notification icon & color mapping
const TYPE_META = {
    appointment: { icon: 'fa-solid fa-calendar-check', css: 'type-appointment', tag: 'tag-appointment', label: 'Appointment' },
    renewal:     { icon: 'fa-solid fa-rotate',        css: 'type-renewal',     tag: 'tag-renewal',     label: 'Renewal' },
    payment:     { icon: 'fa-solid fa-credit-card',    css: 'type-payment',     tag: 'tag-payment',     label: 'Payment' },
    lead:        { icon: 'fa-brands fa-facebook',      css: 'type-lead',        tag: 'tag-lead',        label: 'Lead' },
    general:     { icon: 'fa-solid fa-bell',           css: 'type-general',     tag: 'tag-general',     label: 'General' }
};

function getTypeMeta(type) {
    return TYPE_META[type] || TYPE_META['general'];
}

// 📥 Fetch notifications
async function fetchNotifications(page = 1, type = 'all') {
    const list = document.getElementById('notifList');
    list.innerHTML = '<div class="loading"><i class="fa-solid fa-spinner fa-spin"></i><br>Loading notifications...</div>';
    
    try {
        let url = `api_fcm.php?action=get_inbox&page=${page}&limit=20`;
        if (type !== 'all') url += `&type=${encodeURIComponent(type)}`;
        
        const res = await fetch(url);
        const data = await res.json();
        
        if (!data.success) {
            list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-triangle-exclamation"></i><h3>Error loading</h3><p>${data.message || 'Unknown error'}</p></div>`;
            return;
        }
        
        renderNotifications(data.notifications || []);
        renderPagination(data.total_pages || 1, page);
        totalPages = data.total_pages || 1;
        
    } catch (e) {
        list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-wifi-slash"></i><h3>Connection Error</h3><p>Could not load notifications. Check your connection.</p></div>`;
    }
}

// 🖼️ Render notifications
function renderNotifications(notifications) {
    const list = document.getElementById('notifList');
    
    if (!notifications || notifications.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                <i class="fa-solid fa-bell-slash"></i>
                <h3>All Clear!</h3>
                <p>No notifications to show. You're up to date.</p>
            </div>
        `;
        return;
    }
    
    list.innerHTML = notifications.map(n => {
        const meta = getTypeMeta(n.type);
        const isUnread = n.is_read == 0;
        const timeAgo = getTimeAgo(n.created_at);
        const data = n.data || {};
        
        // Determine click action based on type
        let clickAction = '';
        if (n.type === 'appointment' && data.appointment_id) {
            clickAction = `window.location.href='appointment_manager.php?id=${data.appointment_id}'`;
        } else if (n.type === 'renewal' && data.renewal_id) {
            clickAction = `window.location.href='renewal_entry.php?id=${data.renewal_id}'`;
        } else if (n.type === 'payment') {
            clickAction = `window.location.href='payment_followup.php'`;
        } else if (n.type === 'lead') {
            clickAction = `window.location.href='meta_leads.php'`;
        }
        
        return `
            <div class="notif-item ${isUnread ? 'unread' : 'read'}" onclick="${clickAction || 'markRead(' + n.id + ')'}">
                <div class="notif-icon ${meta.css}"><i class="${meta.icon}"></i></div>
                <div class="notif-content">
                    <div class="notif-title">
                        ${isUnread ? '<span class="unread-dot"></span>' : ''}
                        ${escapeHtml(n.title || 'Notification')}
                    </div>
                    <div class="notif-body">${escapeHtml(n.message || '')}</div>
                    <div class="notif-time">
                        <span class="notif-type-tag ${meta.tag}">${meta.label}</span>
                        <i class="fa-regular fa-clock"></i> ${timeAgo}
                    </div>
                </div>
                <div class="notif-actions">
                    ${isUnread ? `<button class="notif-action-btn" onclick="event.stopPropagation();markRead(${n.id})" title="Mark read"><i class="fa-solid fa-check"></i></button>` : ''}
                    <button class="notif-action-btn danger" onclick="event.stopPropagation();deleteNotif(${n.id})" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>
        `;
    }).join('');
}

// 📄 Render pagination
function renderPagination(total, current) {
    const el = document.getElementById('pagination');
    if (total <= 1) { el.innerHTML = ''; return; }
    
    let html = '';
    html += `<button class="page-btn" onclick="goToPage(${current - 1})" ${current <= 1 ? 'disabled' : ''}><i class="fa-solid fa-chevron-left"></i></button>`;
    
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
            html += `<button class="page-btn ${i === current ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
        } else if (i === current - 2 || i === current + 2) {
            html += `<button class="page-btn" disabled>...</button>`;
        }
    }
    
    html += `<button class="page-btn" onclick="goToPage(${current + 1})" ${current >= total ? 'disabled' : ''}><i class="fa-solid fa-chevron-right"></i></button>`;
    el.innerHTML = html;
}

function goToPage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    fetchNotifications(currentPage, currentType);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ✅ Mark single notification as read
async function markRead(id) {
    try {
        const res = await fetch(`api_fcm.php?action=mark_read&id=${id}`);
        const data = await res.json();
        if (data.success) {
            fetchNotifications(currentPage, currentType);
            updateUnreadCount();
        }
    } catch (e) {
        showToast('Error marking as read', true);
    }
}

// ✅ Mark all as read
async function markAllRead() {
    try {
        const res = await fetch(`api_fcm.php?action=mark_all_read${currentType !== 'all' ? '&type=' + currentType : ''}`);
        const data = await res.json();
        if (data.success) {
            showToast(`✅ ${data.updated} notifications marked as read`);
            fetchNotifications(currentPage, currentType);
            updateUnreadCount();
        }
    } catch (e) {
        showToast('Error', true);
    }
}

// 🗑️ Delete single notification
async function deleteNotif(id) {
    if (!confirm('Delete this notification?')) return;
    try {
        const res = await fetch(`api_fcm.php?action=delete_notification&id=${id}`);
        const data = await res.json();
        if (data.success) {
            fetchNotifications(currentPage, currentType);
            updateUnreadCount();
        }
    } catch (e) {
        showToast('Error deleting', true);
    }
}

// 🗑️ Clear all notifications
async function clearAll() {
    if (!confirm('Delete all notifications? This cannot be undone.')) return;
    try {
        const res = await fetch(`api_fcm.php?action=clear_all${currentType !== 'all' ? '&type=' + currentType : ''}`);
        const data = await res.json();
        if (data.success) {
            showToast(`🗑️ ${data.deleted} notifications cleared`);
            fetchNotifications(currentPage, currentType);
            updateUnreadCount();
        }
    } catch (e) {
        showToast('Error clearing', true);
    }
}

// 🔄 Refresh
function refreshList() {
    fetchNotifications(currentPage, currentType);
    updateUnreadCount();
}

// 📊 Update unread count badge
async function updateUnreadCount() {
    try {
        const res = await fetch('api_fcm.php?action=get_unread_count');
        const data = await res.json();
        const badge = document.getElementById('unreadBadge');
        if (data.success) {
            badge.textContent = data.unread_count;
            badge.style.display = data.unread_count > 0 ? 'inline' : 'none';
            
            // Update document title
            if (data.unread_count > 0) {
                document.title = `(${data.unread_count}) Notification Center | SK LOGIC`;
            } else {
                document.title = 'Notification Center | SK LOGIC';
            }
        }
    } catch (e) {}
}

// 🔔 Filter tabs
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        currentType = this.dataset.type;
        currentPage = 1;
        fetchNotifications(currentPage, currentType);
    });
});

// ⏱️ Time ago helper
function getTimeAgo(dateStr) {
    if (!dateStr) return '';
    const now = new Date();
    const date = new Date(dateStr.replace(' ', 'T') + '+05:30');
    const diff = Math.floor((now - date) / 1000);
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
}

// 🛡️ Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// 🍞 Toast message
function showToast(msg, isError = false) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.className = 'toast' + (isError ? ' error' : '');
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3000);
}

// Auto-refresh every 60 seconds
setInterval(() => {
    fetchNotifications(currentPage, currentType);
    updateUnreadCount();
}, 60000);

// ─── INIT ───
fetchNotifications(1, 'all');
updateUnreadCount();
</script>

</body>
</html>
