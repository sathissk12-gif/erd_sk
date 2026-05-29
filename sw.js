/**
 * 🚀 SK LOGIC - Smart Notification Service Worker v2.0
 * Handles push notifications, background sync, badge API, snooze
 * Upgraded: Badge API, IndexedDB snooze, notification grouping, type-aware
 */

const CACHE_NAME = 'sk-notif-cache-v2';
const APPT_PAGE = '/appointment_manager.php';

// ─── INSTALL ───
self.addEventListener('install', (event) => {
    console.log('✅ SW v2 installed');
    self.skipWaiting();
});

// ─── ACTIVATE ───
self.addEventListener('activate', (event) => {
    console.log('✅ SW v2 activated');
    event.waitUntil(
        Promise.all([
            clients.claim(),
            // Clean old cache
            caches.keys().then(keys => 
                Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
            )
        ])
    );
});

/**
 * 📊 Update notification badge count via API
 */
async function updateBadgeCount() {
    try {
        const res = await fetch('api_fcm.php?action=get_unread_count');
        const data = await res.json();
        if (data.success && navigator.setAppBadge) {
            navigator.setAppBadge(data.unread_count);
        } else if (data.success && 'setClientBadge' in self) {
            // Fallback for service worker badge
            const clients_list = await self.clients.matchAll({ type: 'window' });
            clients_list.forEach(client => {
                client.postMessage({
                    type: 'UPDATE_BADGE',
                    count: data.unread_count
                });
            });
        }
    } catch (e) {
        console.log('Badge update error:', e);
    }
}

// ─── PUSH EVENT (from Firebase / FCM) ───
self.addEventListener('push', (event) => {
    console.log('📨 Push received:', event.data?.text());
    
    let data = {};
    try {
        data = event.data?.json() || {};
    } catch (e) {
        data = {
            title: 'SK Appointments',
            body: event.data?.text() || 'New notification',
            data: {}
        };
    }

    const notificationPayload = data.notification || {};
    const dataPayload = data.data || {};
    
    const title = notificationPayload.title || data.title || '📅 Appointment Alert';
    const body = notificationPayload.body || data.body || 'You have an appointment';
    const icon = notificationPayload.icon || 'images/logo.png';
    const badge = 'images/logo.png';
    
    // Extract all possible data
    const notificationData = { ...dataPayload };
    const fullScreen = data.full_screen === 'true' || notificationData.full_screen === 'true';
    const type = notificationData.type || 'APPOINTMENT_REMINDER';
    const appointmentId = notificationData.appointment_id || 0;
    const relatedId = notificationData.related_id || appointmentId;
    const notifType = notificationData.notification_type || 'general';
    
    // 🎵 Resolve sound
    const soundName = notificationData.sound || 'default';
    const vibrationPattern = notificationData.vibration || 'standard';
    
    // Determine vibration array based on pattern
    let vibrateArray = [200, 100, 200];
    if (vibrationPattern === 'disabled') vibrateArray = [];
    else if (vibrationPattern === 'double') vibrateArray = [100, 100, 100];
    else if (vibrationPattern === 'long') vibrateArray = [500];
    else if (vibrationPattern === 'rapid') vibrateArray = [200, 100, 200, 100, 200, 100, 500];
    else if (vibrationPattern === 'heartbeat') vibrateArray = [100, 200, 100, 500];
    
    // Determine urgency tag for notification grouping
    let tag = 'notif-general';
    if (type === 'APPOINTMENT_NOW' || type === 'APPOINTMENT_OVERDUE') tag = 'appt-urgent';
    else if (type === 'APPOINTMENT_REMINDER') tag = 'appt-reminder';
    else if (type === 'RENEWAL_REMINDER') tag = 'renewal';
    else if (type === 'PAYMENT_REMINDER') tag = 'payment';
    else if (type === 'SOUND_TEST') tag = 'sound-test';
    
    // Build target URL based on type
    let targetUrl = APPT_PAGE;
    if (type === 'RENEWAL_REMINDER' && relatedId) {
        targetUrl = `renewal_entry.php?id=${relatedId}`;
    } else if (type === 'PAYMENT_REMINDER') {
        targetUrl = 'payment_followup.php';
    } else if (type === 'LEAD' || type === 'META_LEAD') {
        targetUrl = 'meta_leads.php';
    } else if (appointmentId) {
        targetUrl = APPT_PAGE + '?id=' + appointmentId;
    }

    // Create notification options
    const options = {
        body: body,
        icon: icon,
        badge: badge,
        tag: tag,
        renotify: true,
        requireInteraction: fullScreen || type === 'APPOINTMENT_NOW' || type === 'APPOINTMENT_OVERDUE',
        vibrate: vibrateArray,
        silent: soundName === 'disabled' || soundName === 'false',
        data: {
            ...notificationData,
            appointment_id: appointmentId,
            related_id: relatedId,
            type: type,
            notification_type: notifType,
            full_screen: fullScreen ? 'true' : 'false',
            sound: soundName,
            vibration: vibrationPattern,
            timestamp: Date.now(),
            url: targetUrl,
            notif_id: Date.now() // Unique ID for this notification instance
        },
        actions: (fullScreen || type === 'APPOINTMENT_NOW' || type === 'APPOINTMENT_OVERDUE') ? [
            { action: 'acknowledge', title: '✅ Acknowledge' },
            { action: 'open', title: '🔍 View Details' },
            { action: 'snooze', title: '⏰ Snooze 5min' }
        ] : [
            { action: 'open', title: '🔍 View' },
            { action: 'dismiss', title: '✕ Dismiss' }
        ]
    };

    event.waitUntil(
        Promise.all([
            self.registration.showNotification(title, options),
            updateBadgeCount()
        ])
    );
});

// ─── NOTIFICATION CLICK ───
self.addEventListener('notificationclick', (event) => {
    const notification = event.notification;
    const action = event.action;
    const data = notification.data || {};

    notification.close();

    switch (action) {
        case 'acknowledge':
            acknowledgeAppointment(data.appointment_id);
            focusOrOpenClient(data.url || APPT_PAGE);
            break;
            
        case 'snooze':
            snoozeNotification(notification.title, notification.body, data);
            break;
            
        case 'open':
        default:
            focusOrOpenClient(data.url || APPT_PAGE);
            break;
            
        case 'dismiss':
            // Just close, do nothing else
            break;
    }
    
    // Update badge after action
    event.waitUntil(updateBadgeCount());
});

// ─── HELPER: Focus existing client or open new window ───
async function focusOrOpenClient(url) {
    const clientList = await clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    });

    // Check if there's already a relevant client
    for (const client of clientList) {
        const clientUrl = client.url;
        // Match any relevant page
        if (url.includes('?') ? 
            clientUrl.includes(url.split('?')[0]) : 
            clientUrl.includes(url)) {
            if ('focus' in client) {
                return client.focus();
            }
        }
    }

    // Open new window
    if (clients.openWindow) {
        return clients.openWindow(url);
    }
}

// ─── HELPER: Acknowledge appointment via API ───
async function acknowledgeAppointment(id) {
    if (!id) return;
    try {
        const response = await fetch('api_appointments.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=acknowledge&id=${id}`
        });
        const result = await response.json();
        console.log('📡 Acknowledge response:', result);

        // Notify all clients
        const clientList = await clients.matchAll({ type: 'window' });
        clientList.forEach(client => {
            client.postMessage({
                type: 'APPOINTMENT_ACKNOWLEDGED',
                appointment_id: id,
                success: result.success
            });
        });
    } catch (e) {
        console.log('Acknowledge error:', e);
    }
}

// ─── HELPER: Snooze notification using IndexedDB ───
async function snoozeNotification(title, body, data) {
    const snoozeTime = 5 * 60 * 1000; // 5 minutes
    const snoozeUntil = Date.now() + snoozeTime;
    
    // Store in IndexedDB for reliability
    try {
        const db = await openSnoozeDB();
        const tx = db.transaction('snoozed', 'readwrite');
        const store = tx.objectStore('snoozed');
        await store.put({
            id: 'current',
            title: title,
            body: body,
            data: data,
            snooze_until: snoozeUntil,
            created_at: Date.now()
        });
        await tx.done;
    } catch (e) {
        console.log('IndexedDB snooze error:', e);
    }
    
    // Show confirmation
    self.registration.showNotification('⏰ Snoozed', {
        body: 'We\'ll remind you again in 5 minutes',
        icon: 'images/logo.png',
        tag: 'snooze-confirm',
        requireInteraction: false,
        timestamp: snoozeUntil
    });

    // Schedule using setTimeout AND alarm API (if available)
    setTimeout(async () => {
        try {
            const db = await openSnoozeDB();
            const tx = db.transaction('snoozed', 'readonly');
            const store = tx.objectStore('snoozed');
            const cached = await store.get('current');
            
            if (cached && cached.snooze_until <= Date.now()) {
                self.registration.showNotification(cached.title, {
                    body: cached.body,
                    icon: 'images/logo.png',
                    tag: 'snoozed-reminder',
                    requireInteraction: true,
                    vibrate: [200, 100, 200, 100, 200],
                    data: cached.data,
                    actions: [
                        { action: 'acknowledge', title: '✅ Acknowledge' },
                        { action: 'snooze', title: '⏰ Snooze Again' }
                    ]
                });
                
                // Clean up
                const tx2 = db.transaction('snoozed', 'readwrite');
                const store2 = tx2.objectStore('snoozed');
                await store2.delete('current');
                await tx2.done;
            }
        } catch (e) {
            console.log('Snooze fire error:', e);
        }
    }, snoozeTime);
}

/**
 * 🔄 Open IndexedDB for snooze storage
 */
function openSnoozeDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('SKSnoozeDB', 1);
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('snoozed')) {
                db.createObjectStore('snoozed', { keyPath: 'id' });
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

// ─── LISTEN FOR MESSAGES FROM CLIENTS ───
self.addEventListener('message', (event) => {
    const data = event.data;
    if (!data) return;

    switch (data.type) {
        case 'PING':
            event.ports[0]?.postMessage({ type: 'PONG', timestamp: Date.now() });
            break;
            
        case 'CHECK_SNOOZED':
            event.waitUntil(
                (async () => {
                    try {
                        const db = await openSnoozeDB();
                        const tx = db.transaction('snoozed', 'readonly');
                        const store = tx.objectStore('snoozed');
                        const cached = await store.get('current');
                        if (cached) {
                            event.ports[0]?.postMessage({ type: 'SNOOZED_EXISTS', data: cached });
                        }
                    } catch (e) {}
                })()
            );
            break;
            
        case 'UPDATE_BADGE':
            // Client asking SW to update badge
            event.waitUntil(updateBadgeCount());
            break;
    }
});

// ─── FETCH EVENT (Cache static assets for offline + API) ───
self.addEventListener('fetch', (event) => {
    const url = event.request.url;
    
    // Cache notification-related pages
    if (url.includes('notification_center') || url.includes('appointment_manager')) {
        event.respondWith(
            caches.match(event.request).then(cachedResponse => {
                const fetchPromise = fetch(event.request).then(networkResponse => {
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, networkResponse.clone());
                    });
                    return networkResponse;
                }).catch(() => cachedResponse);
                return cachedResponse || fetchPromise;
            })
        );
    }
});

console.log('🚀 SK Notification Service Worker v2 loaded');
