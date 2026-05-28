/**
 * 🚀 SK LOGIC - Smart Appointment Service Worker
 * Handles push notifications and background sync for instant alerts
 * Version: 1.0.0
 */

const CACHE_NAME = 'sk-appt-cache-v1';
const APPT_PAGE = '/appointment_manager.php';

// ─── INSTALL ───
self.addEventListener('install', (event) => {
    console.log('✅ SW installed');
    self.skipWaiting();
});

// ─── ACTIVATE ───
self.addEventListener('activate', (event) => {
    console.log('✅ SW activated');
    event.waitUntil(clients.claim());
});

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

    const title = data.notification?.title || data.title || '📅 Appointment Alert';
    const body = data.notification?.body || data.body || 'You have an appointment';
    const icon = data.notification?.icon || 'images/logo.png';
    const badge = 'images/logo.png';
    
    // Extract all possible data
    const notificationData = data.data || data.notification?.data || {};
    const fullScreen = data.full_screen === 'true' || notificationData.full_screen === 'true';
    const soundEnabled = data.sound === 'true' || notificationData.sound === 'true';
    const type = notificationData.type || 'APPOINTMENT_REMINDER';
    const appointmentId = notificationData.appointment_id || data.appointment_id || 0;
    
    // Determine urgency tag for notification grouping
    const tag = type === 'APPOINTMENT_NOW' ? 'appt-now' : 'appt-reminder';

    // Create notification options
    const options = {
        body: body,
        icon: icon,
        badge: badge,
        tag: tag,
        renotify: true,
        requireInteraction: fullScreen, // Keep notification until user interacts
        vibrate: fullScreen ? [200, 100, 200, 100, 200, 100, 200] : [200, 100, 200],
        data: {
            ...notificationData,
            appointment_id: appointmentId,
            type: type,
            full_screen: fullScreen ? 'true' : 'false',
            timestamp: Date.now(),
            url: APPT_PAGE + (appointmentId ? '?id=' + appointmentId : '')
        },
        actions: fullScreen ? [
            { action: 'acknowledge', title: '✅ Acknowledge' },
            { action: 'open', title: '🔍 View Details' },
            { action: 'snooze', title: '⏰ Snooze 5min' }
        ] : [
            { action: 'open', title: '🔍 View' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// ─── NOTIFICATION CLICK ───
self.addEventListener('notificationclick', (event) => {
    const notification = event.notification;
    const action = event.action;
    const data = notification.data || {};

    notification.close();

    // Handle different actions
    switch (action) {
        case 'acknowledge':
            // Send acknowledge request to API
            acknowledgeAppointment(data.appointment_id);
            // Focus or open the app
            focusOrOpenClient(APPT_PAGE);
            break;
            
        case 'snooze':
            // Reschedule notification after 5 minutes
            snoozeNotification(notification.title, notification.body, data);
            break;
            
        case 'open':
        default:
            // Open appointment page
            focusOrOpenClient(data.url || APPT_PAGE);
            break;
    }
});

// ─── HELPER: Focus existing client or open new window ───
async function focusOrOpenClient(url) {
    const clientList = await clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    });

    // Check if there's already a client with this URL
    for (const client of clientList) {
        if (client.url.includes('appointment_manager') && 'focus' in client) {
            return client.focus();
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
        const data = await response.json();
        console.log('📡 Acknowledge response:', data);

        // Notify all clients about the acknowledgement
        const clients_list = await clients.matchAll({ type: 'window' });
        clients_list.forEach(client => {
            client.postMessage({
                type: 'APPOINTMENT_ACKNOWLEDGED',
                appointment_id: id,
                success: data.success
            });
        });
    } catch (e) {
        console.log('Acknowledge error:', e);
    }
}

// ─── HELPER: Snooze notification ───
async function snoozeNotification(title, body, data) {
    const snoozeTime = 5 * 60 * 1000; // 5 minutes
    const snoozeUntil = Date.now() + snoozeTime;
    
    // Store snoozed notification in cache for later
    const cache = await caches.open(CACHE_NAME);
    const snoozedData = {
        title: title,
        body: body,
        data: data,
        snooze_until: snoozeUntil
    };
    
    const response = new Response(JSON.stringify(snoozedData), {
        headers: { 'Content-Type': 'application/json' }
    });
    cache.put('/snoozed-notification', response);
    
    // Show a confirmation notification
    self.registration.showNotification('⏰ Snoozed', {
        body: 'We\'ll remind you again in 5 minutes',
        icon: 'images/logo.png',
        tag: 'snooze-confirm',
        requireInteraction: false,
        timestamp: snoozeUntil
    });

    // Schedule the re-notification
    setTimeout(async () => {
        const cachedResponse = await cache.match('/snoozed-notification');
        if (cachedResponse) {
            const cached = await cachedResponse.json();
            if (cached.snooze_until <= Date.now()) {
                self.registration.showNotification(cached.title, {
                    body: cached.body,
                    icon: 'images/logo.png',
                    tag: 'appt-now',
                    requireInteraction: true,
                    vibrate: [200, 100, 200, 100, 200],
                    data: cached.data,
                    actions: [
                        { action: 'acknowledge', title: '✅ Acknowledge' },
                        { action: 'snooze', title: '⏰ Snooze Again' }
                    ]
                });
                cache.delete('/snoozed-notification');
            }
        }
    }, snoozeTime);
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
            // Client wants to know if there's a snoozed notification
            event.waitUntil(
                caches.open(CACHE_NAME).then(cache => 
                    cache.match('/snoozed-notification').then(response => {
                        if (response) {
                            event.ports[0]?.postMessage({ type: 'SNOOZED_EXISTS' });
                        }
                    })
                )
            );
            break;
    }
});

// ─── FETCH EVENT (Cache static assets for offline) ───
self.addEventListener('fetch', (event) => {
    // Only cache the appointment page
    if (event.request.url.includes('appointment_manager')) {
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

console.log('🚀 SK Appointment Service Worker loaded');
