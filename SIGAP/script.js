// Main JavaScript file untuk SIGAP
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistem Pendataan Anggota loaded successfully!');
    
    // Toast Notification System
    window.showToast = function(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-icon">${type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ'}</span>
                <span class="toast-message">${message}</span>
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // Search functionality
    const searchInputs = document.querySelectorAll('.search input, #search-input');
    searchInputs.forEach(input => {
        if (input.dataset.searchable) {
            let searchTimeout;
            input.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const searchTerm = this.value.trim();
                searchTimeout = setTimeout(() => {
                    performSearch(searchTerm);
                }, 300);
            });
        }
    });
    
    // Form validation with real-time feedback
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });
        
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast('Mohon lengkapi semua field yang wajib diisi!', 'error');
            }
        });
    });
    
    // Validate field function
    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'Field ini wajib diisi';
        } else if (field.type === 'email' && value && !isValidEmail(value)) {
            isValid = false;
            errorMessage = 'Format email tidak valid';
        // --- PERBAIKAN: Pesan error diubah agar lebih spesifik ---
        } else if (field.type === 'tel' && value && !isValidPhone(value)) {
            isValid = false;
            errorMessage = 'Format tidak valid (cth: 08123456789)';
        }
        
        if (isValid) {
            field.classList.remove('error');
            removeFieldError(field);
        } else {
            field.classList.add('error');
            showFieldError(field, errorMessage);
        }
        
        return isValid;
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    // --- PERBAIKAN: Logika validasi telepon diperketat ---
    function isValidPhone(phone) {
        // Hapus spasi, strip, tanda kurung, atau +62 di awal
        const cleanedPhone = phone.replace(/[\s\-\(\)]+/g, '').replace(/^\+62/, '0');
        // Validasi: harus 9-13 digit dan diawali 0
        return /^0[0-9]{8,12}$/.test(cleanedPhone);
    }
    
    function showFieldError(field, message) {
        removeFieldError(field);
        const error = document.createElement('div');
        error.className = 'field-error';
        error.textContent = message;
        field.parentElement.appendChild(error);
    }
    
    function removeFieldError(field) {
        const error = field.parentElement.querySelector('.field-error');
        if (error) error.remove();
    }
    
    // Perform search
    function performSearch(term) {
        const url = new URL(window.location.href);
        if (term) {
            url.searchParams.set('search', term);
        } else {
            url.searchParams.delete('search');
        }
        window.location.href = url.toString();
    }
    
    // Confirm delete with better UI
    const deleteButtons = document.querySelectorAll('a[href*="delete"], .btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
    });
    
    // Bulk operations
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name="member_ids[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });
    }
    
    const memberCheckboxes = document.querySelectorAll('input[type="checkbox"][name="member_ids[]"]');
    memberCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
    
    function updateBulkActions() {
        const checked = document.querySelectorAll('input[type="checkbox"][name="member_ids[]"]:checked');
        const bulkActions = document.getElementById('bulk-actions');
        if (bulkActions) {
            if (checked.length > 0) {
                bulkActions.style.display = 'flex';
                bulkActions.querySelector('.selected-count').textContent = checked.length;
            } else {
                bulkActions.style.display = 'none';
            }
        }
    }
    
    // Loading states
    const formsWithLoading = document.querySelectorAll('form[data-loading]');
    formsWithLoading.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner"></span> Memproses...';
            }
        });
    });
    
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (mobileMenu && !mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
            mobileMenu.classList.remove('active');
        }
    });
    
    // Notification badge update
    updateNotificationBadge();
    setInterval(updateNotificationBadge, 60000); // Update every minute
    
    function updateNotificationBadge() {
        fetch('api/get_notification_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(err => console.error('Error updating notification badge:', err));
    }
    
    // Table row click for detail view
    const tableRows = document.querySelectorAll('.data-table tbody tr[data-member-id]');
    tableRows.forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            if (!e.target.closest('a, button')) {
                const memberId = this.dataset.memberId;
                window.location.href = `member_detail.php?id=${memberId}`;
            }
        });
    });
    
    // Auto-save form drafts
    const autoSaveForms = document.querySelectorAll('form[data-autosave]');
    autoSaveForms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            const key = `draft_${form.id || 'form'}_${input.name}`;
            const saved = localStorage.getItem(key);
            if (saved) input.value = saved;
            
            input.addEventListener('input', function() {
                localStorage.setItem(key, this.value);
            });
        });
        
        form.addEventListener('submit', function() {
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                localStorage.removeItem(`draft_${form.id || 'form'}_${input.name}`);
            });
        });
    });
    
    // Tooltip initialization
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            
            this._tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
    
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
    
    // User Profile Menu Dropdown
    const userProfileBtn = document.getElementById('user-profile-btn');
    const userMenuDropdown = document.getElementById('user-menu-dropdown');
    
    if (userProfileBtn && userMenuDropdown) {
        userProfileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenuDropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking on a link
        const menuItems = userMenuDropdown.querySelectorAll('a');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                userMenuDropdown.classList.remove('active');
            });
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (userMenuDropdown && !userMenuDropdown.contains(e.target) && userProfileBtn && !userProfileBtn.contains(e.target)) {
            userMenuDropdown.classList.remove('active');
        }
    });
});