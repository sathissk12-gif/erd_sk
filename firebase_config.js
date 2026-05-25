// Firebase Configuration for SK TRACKS ERP
const firebaseConfig = {
    apiKey: "AIzaSyCM0Pm0jiSdt9EVAaLGr7Z8gpHwEPHQZJg",
    authDomain: "skcore-4b440.firebaseapp.com",
    projectId: "skcore-4b440",
    storageBucket: "skcore-4b440.firebasestorage.app",
    messagingSenderId: "412987596303",
    appId: "1:412987596303:web:1b5fc67c60e9d30211cd7a"
};

// Initialize Firebase (Compat mode for easy integration)
firebase.initializeApp(firebaseConfig);

// Auth Guard Logic
function protectPage(options = {}) {
    const allowInvoiceAccess = !!options.allowInvoiceAccess;
    const params = new URLSearchParams(window.location.search);
    const hasPublicInvoiceKey = !!(params.get("uid") || params.get("invoice_no") || params.get("id"));

    if (allowInvoiceAccess && hasPublicInvoiceKey) {
        return;
    }

    firebase.auth().onAuthStateChanged((user) => {
        if (!user) {
            // Check if we are already on login page to avoid infinite loop
            if (!window.location.pathname.includes("login.html")) {
                window.location.href = "login.html";
            }
        } else if (user.email !== "sathissk12@gmail.com") {
            // Unauthorized account - Boot them out
            firebase.auth().signOut().then(() => {
                if (!window.location.pathname.includes("login.html")) {
                    window.location.href = "login.html?error=unauthorized";
                }
            });
        }
    });
}
