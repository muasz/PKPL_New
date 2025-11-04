// Form Validation
document.addEventListener('DOMContentLoaded', function() {
    // Register Form Validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const phone = document.getElementById('phone').value;
            
            // Validasi password
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }
            
            // Validasi password match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password tidak cocok!');
                return false;
            }
            
            // Validasi nomor HP
            if (phone.length < 10) {
                e.preventDefault();
                alert('Nomor HP tidak valid!');
                return false;
            }
        });
        
        // Real-time password match indicator
        const confirmPasswordInput = document.getElementById('confirm_password');
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const password = document.getElementById('password').value;
                const confirmPassword = this.value;
                
                if (confirmPassword.length > 0) {
                    if (password === confirmPassword) {
                        this.style.borderColor = '#10b981';
                    } else {
                        this.style.borderColor = '#ef4444';
                    }
                } else {
                    this.style.borderColor = '#e5e7eb';
                }
            });
        }
    }
    
    // Login Form Validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Email dan password harus diisi!');
                return false;
            }
            
            // Validasi format email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Format email tidak valid!');
                return false;
            }
        });
    }
    
    // Booking Form Validation
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            const serviceId = document.getElementById('service_id').value;
            const date = document.getElementById('date').value;
            const time = document.getElementById('time').value;
            
            if (!serviceId || !date || !time) {
                e.preventDefault();
                alert('Semua field wajib diisi!');
                return false;
            }
            
            // Validasi tanggal tidak boleh di masa lalu
            const selectedDate = new Date(date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                e.preventDefault();
                alert('Tanggal booking tidak boleh di masa lalu!');
                return false;
            }
        });
        
        // Auto-highlight selected date
        const dateInput = document.getElementById('date');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                this.style.borderColor = '#8b5cf6';
            });
        }
    }
    
    // Alert auto-close
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Konfirmasi untuk aksi berbahaya
    const dangerButtons = document.querySelectorAll('.btn-danger');
    dangerButtons.forEach(function(button) {
        if (!button.hasAttribute('onclick')) {
            button.addEventListener('click', function(e) {
                if (!confirm('Apakah Anda yakin?')) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
    
    // Table row hover effect enhancement
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(function(row) {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'transform 0.2s';
        });
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Service card animation
    const serviceCards = document.querySelectorAll('.service-card');
    serviceCards.forEach(function(card, index) {
        card.style.animationDelay = (index * 0.1) + 's';
    });
    
    // Form input focus effect
    const formInputs = document.querySelectorAll('input, select, textarea');
    formInputs.forEach(function(input) {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateX(5px)';
            this.parentElement.style.transition = 'transform 0.3s';
        });
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateX(0)';
        });
    });
});

// Phone number formatting
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 13) {
        value = value.slice(0, 13);
    }
    input.value = value;
}

// Email validation
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Date formatting helper
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
}

// Currency formatting helper
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Loading indicator
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loading-overlay';
    loading.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;
    loading.innerHTML = '<div style="color: white; font-size: 1.5rem;">Loading...</div>';
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        loading.remove();
    }
}

// Print function for admin
function printBookingReport() {
    window.print();
}

// Export table to CSV (basic implementation)
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(function(row) {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(function(col) {
            csvRow.push(col.innerText);
        });
        csv.push(csvRow.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename || 'export.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Update booking status function
function updateBookingStatus(bookingId, newStatus, currentStatus) {
    // Jika status sama, tidak perlu update
    if (newStatus === currentStatus) {
        return;
    }
    
    // Konfirmasi perubahan status
    const statusNames = {
        'pending': 'Menunggu',
        'confirmed': 'Dikonfirmasi',
        'cancelled': 'Dibatalkan', 
        'rejected': 'Ditolak'
    };
    
    const message = `Ubah status booking #${bookingId} menjadi "${statusNames[newStatus]}"?`;
    
    if (confirm(message)) {
        // Submit form
        document.getElementById('statusForm' + bookingId).submit();
    } else {
        // Reset select ke status semula
        const select = document.querySelector(`#statusForm${bookingId} select[name="status"]`);
        select.value = currentStatus;
    }
}

// ===== MODERN INTERACTIVE UTILITIES =====

// Modern Smooth Animations with Performance Optimization
const ModernUI = {
    // Initialize modern behaviors
    init() {
        this.setupModernButtons();
        this.setupModernInputs();
        this.setupModernTable();
        this.setupLoadingStates();
        console.log('🎨 Modern UI System initialized');
    },

    // Setup modern button behaviors (for complex interactions only)
    setupModernButtons() {
        document.querySelectorAll('.btn-modern').forEach(btn => {
            // Add ripple effect for complex buttons
            btn.addEventListener('click', function(e) {
                if (this.classList.contains('no-ripple')) return;
                
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.height, rect.width);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255,255,255,0.4);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;
                
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });
    },

    // Setup modern input behaviors
    setupModernInputs() {
        document.querySelectorAll('.input-modern, .select-modern').forEach(input => {
            // Add floating label effect
            input.addEventListener('focus', function() {
                this.parentElement?.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement?.classList.remove('focused');
                if (!this.value) {
                    this.parentElement?.classList.remove('filled');
                } else {
                    this.parentElement?.classList.add('filled');
                }
            });
        });
    },

    // Setup modern table behaviors
    setupModernTable() {
        document.querySelectorAll('.table-row-modern').forEach(row => {
            // Add click to select functionality
            row.addEventListener('click', function(e) {
                if (e.target.closest('button, a, select')) return;
                
                document.querySelectorAll('.table-row-modern.selected').forEach(r => {
                    r.classList.remove('selected');
                });
                this.classList.add('selected');
            });
        });
    },

    // Loading states management
    setupLoadingStates() {
        // Auto-add loading states to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.classList.contains('no-loading')) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                    
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="loading-modern"></span> Processing...';
                    
                    // Reset after 3 seconds if not redirected
                    setTimeout(() => {
                        if (submitBtn) {
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }, 3000);
                }
            });
        });
    },

    // Utility: Show toast notification
    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            font-weight: 600;
        `;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        setTimeout(() => toast.style.transform = 'translateX(0)', 100);
        setTimeout(() => {
            toast.style.transform = 'translateX(400px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    // Utility: Smooth scroll to element
    scrollTo(selector) {
        const element = document.querySelector(selector);
        if (element) {
            element.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }
    }
};

// Add ripple animation to CSS
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .table-row-modern.selected {
        background-color: #eff6ff !important;
        border-left: 4px solid #10b981;
    }
    
    .toast {
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from { transform: translateX(400px); }
        to { transform: translateX(0); }
    }
`;
document.head.appendChild(rippleStyle);

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ModernUI.init());
} else {
    ModernUI.init();
}

console.log('🚀 PierceFlow Modern UI loaded successfully! ✨');
