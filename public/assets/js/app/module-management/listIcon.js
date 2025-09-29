const iconList = [
    // 📊 Dashboard & Navigasi
    { id: 'fas fa-home', text: 'Home' },
    { id: 'fas fa-tachometer-alt', text: 'Dashboard' },
    { id: 'fas fa-th', text: 'Grid' },
    { id: 'fas fa-th-large', text: 'Grid Large' },
    { id: 'fas fa-th-list', text: 'Grid List' },
    { id: 'fas fa-bars', text: 'Menu Bars' },
    { id: 'fas fa-ellipsis-v', text: 'Ellipsis Vertical' },
    { id: 'fas fa-ellipsis-h', text: 'Ellipsis Horizontal' },

    // 👤 User & Security
    { id: 'fas fa-users', text: 'Users' },
    { id: 'fas fa-user', text: 'User' },
    { id: 'fas fa-user-plus', text: 'User Plus' },
    { id: 'fas fa-user-minus', text: 'User Minus' },
    { id: 'fas fa-user-edit', text: 'User Edit' },
    { id: 'fas fa-user-cog', text: 'User Settings' },
    { id: 'fas fa-users-cog', text: 'Users Cog' },
    { id: 'fas fa-user-shield', text: 'User Shield' },
    { id: 'fas fa-user-lock', text: 'User Lock' },
    { id: 'fas fa-user-tie', text: 'User Tie' },
    { id: 'fas fa-user-circle', text: 'User Circle' },

    // ⚙️ Settings & Tools
    { id: 'fas fa-cog', text: 'Cog' },
    { id: 'fas fa-cogs', text: 'Cogs' },
    { id: 'fas fa-wrench', text: 'Wrench' },
    { id: 'fas fa-tools', text: 'Tools' },
    { id: 'fas fa-sliders-h', text: 'Sliders' },
    { id: 'fas fa-sync-alt', text: 'Sync' },
    { id: 'fas fa-undo', text: 'Undo' },
    { id: 'fas fa-redo', text: 'Redo' },

    // 📁 File & Data
    { id: 'fas fa-box', text: 'Box' },
    { id: 'fas fa-boxes', text: 'Boxes' },
    { id: 'fas fa-archive', text: 'Archive' },
    { id: 'fas fa-database', text: 'Database' },
    { id: 'fas fa-file', text: 'File' },
    { id: 'fas fa-file-alt', text: 'File Alt' },
    { id: 'fas fa-file-upload', text: 'File Upload' },
    { id: 'fas fa-file-download', text: 'File Download' },
    { id: 'fas fa-folder', text: 'Folder' },
    { id: 'fas fa-folder-open', text: 'Folder Open' },
    { id: 'fas fa-save', text: 'Save' },
    { id: 'fas fa-upload', text: 'Upload' },
    { id: 'fas fa-download', text: 'Download' },

    // 📊 Statistik & Grafik
    { id: 'fas fa-chart-bar', text: 'Chart Bar' },
    { id: 'fas fa-chart-pie', text: 'Chart Pie' },
    { id: 'fas fa-chart-line', text: 'Chart Line' },
    { id: 'fas fa-percentage', text: 'Percentage' },
    { id: 'fas fa-table', text: 'Table' },
    { id: 'fas fa-list', text: 'List' },
    { id: 'fas fa-list-alt', text: 'List Alt' },
    { id: 'fas fa-tasks', text: 'Tasks' },
    { id: 'fas fa-project-diagram', text: 'Project Diagram' },

    // 📅 Kalender & Waktu
    { id: 'fas fa-calendar', text: 'Calendar' },
    { id: 'fas fa-calendar-alt', text: 'Calendar Alt' },
    { id: 'fas fa-clock', text: 'Clock' },
    { id: 'fas fa-history', text: 'History' },
    { id: 'fas fa-hourglass-half', text: 'Hourglass' },
    { id: 'fas fa-stopwatch', text: 'Stopwatch' },

    // ✉️ Komunikasi & Notifikasi
    { id: 'fas fa-envelope', text: 'Envelope' },
    { id: 'fas fa-inbox', text: 'Inbox' },
    { id: 'fas fa-paper-plane', text: 'Paper Plane' },
    { id: 'fas fa-bell', text: 'Bell' },
    { id: 'fas fa-comments', text: 'Comments' },
    { id: 'fas fa-comment-dots', text: 'Comment Dots' },
    { id: 'fas fa-phone', text: 'Phone' },
    { id: 'fas fa-phone-alt', text: 'Phone Alt' },

    // 🛍️ E-commerce & Transaksi
    { id: 'fas fa-shopping-cart', text: 'Shopping Cart' },
    { id: 'fas fa-shopping-basket', text: 'Shopping Basket' },
    { id: 'fas fa-credit-card', text: 'Credit Card' },
    { id: 'fas fa-wallet', text: 'Wallet' },
    { id: 'fas fa-money-bill-wave', text: 'Money Bill' },
    { id: 'fas fa-receipt', text: 'Receipt' },
    { id: 'fas fa-dollar-sign', text: 'Dollar' },
    { id: 'fas fa-coins', text: 'Coins' },

    // 📍 Lokasi & Peta
    { id: 'fas fa-map', text: 'Map' },
    { id: 'fas fa-map-marker-alt', text: 'Map Marker' },
    { id: 'fas fa-compass', text: 'Compass' },
    { id: 'fas fa-location-arrow', text: 'Location Arrow' },
    { id: 'fas fa-route', text: 'Route' },

    // 🔐 Akses & Keamanan
    { id: 'fas fa-lock', text: 'Lock' },
    { id: 'fas fa-unlock', text: 'Unlock' },
    { id: 'fas fa-key', text: 'Key' },
    { id: 'fas fa-sign-in-alt', text: 'Sign In' },
    { id: 'fas fa-sign-out-alt', text: 'Sign Out' },
    { id: 'fas fa-fingerprint', text: 'Fingerprint' },
    { id: 'fas fa-shield-alt', text: 'Shield' },

    // 🛈 Informasi & Bantuan
    { id: 'fas fa-info-circle', text: 'Info Circle' },
    { id: 'fas fa-question-circle', text: 'Question Circle' },
    { id: 'fas fa-exclamation-circle', text: 'Exclamation Circle' },
    { id: 'fas fa-exclamation-triangle', text: 'Warning' },
    { id: 'fas fa-lightbulb', text: 'Lightbulb' },

    // 📎 Lain-lain
    { id: 'fas fa-bookmark', text: 'Bookmark' },
    { id: 'fas fa-star', text: 'Star' },
    { id: 'fas fa-heart', text: 'Heart' },
    { id: 'fas fa-tag', text: 'Tag' },
    { id: 'fas fa-tags', text: 'Tags' },
    { id: 'fas fa-link', text: 'Link' },
    { id: 'fas fa-external-link-alt', text: 'External Link' },
    { id: 'fas fa-trash', text: 'Trash' },
    { id: 'fas fa-recycle', text: 'Recycle' },
    { id: 'fas fa-print', text: 'Print' },
    { id: 'fas fa-camera', text: 'Camera' },
    { id: 'fas fa-image', text: 'Image' },
    { id: 'fas fa-video', text: 'Video' },
    { id: 'fas fa-microphone', text: 'Microphone' },
];
