import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Global Functions
window.showNotification = function(message, type = 'success') {
    // Simple notification system
    const notification = document.createElement('div');
    notification.className = `fixed top-4 ${document.dir === 'rtl' ? 'left-4' : 'right-4'} z-50 px-6 py-4 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
    } text-white max-w-md`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' :
                type === 'error' ? 'fa-times-circle' :
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'
            } mr-3"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
};

// Confirm Delete
window.confirmDelete = function(form) {
    window._deleteForm = form;
    window.dispatchEvent(new CustomEvent('open-delete-modal'));
};

// Format Currency
window.formatCurrency = function(amount) {
    return new Intl.NumberFormat('ar-SA', {
        style: 'currency',
        currency: 'SAR'
    }).format(amount);
};

// Format Date
window.formatDate = function(date) {
    return new Intl.DateTimeFormat('ar-SA').format(new Date(date));
};

// Toggle Sidebar (Mobile)
window.toggleSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('-translate-x-full');
};

// Dark Mode Toggle (Future Feature)
window.toggleDarkMode = function() {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
};

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-auto-hide');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
