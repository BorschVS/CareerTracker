/**
 * Main Application JavaScript
 * Career Tracker - Personal Project Documentation App
 */

$(document).ready(function() {
    
    // Initialize the application
    CareerTracker.init();
    
    // Modal functionality
    $('#create-project-btn, #add-project-nav').on('click', function(e) {
        e.preventDefault();
        openModal();
    });
    
    $('.close, .modal').on('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Form submission
    $('#create-project-form').on('submit', function(e) {
        e.preventDefault();
        createProject();
    });
    
    // Close modal on Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
});

// Main Application Object
const CareerTracker = {
    
    init: function() {
        this.bindEvents();
        this.loadProjects();
        this.initGlassEffects();
        console.log('Career Tracker initialized');
    },
    
    bindEvents: function() {
        // Smooth scrolling for anchor links
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            const target = $(this.getAttribute('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 600);
            }
        });
        
        // Add hover effects to cards
        $('.glass-card').hover(
            function() {
                $(this).addClass('hover-effect');
            },
            function() {
                $(this).removeClass('hover-effect');
            }
        );
    },
    
    loadProjects: function() {
        // This would typically load projects from the database
        // For now, we'll work with what WordPress provides
        console.log('Projects loaded');
    },
    
    initGlassEffects: function() {
        // Add dynamic glass effects on mouse movement
        $(document).mousemove(function(e) {
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;
            
            $('.glass-container').css({
                'background': `rgba(255, 255, 255, ${0.1 + mouseX * 0.05})`,
            });
        });
    }
};

// Modal Functions
function openModal() {
    $('#create-project-modal').fadeIn(300);
    $('body').addClass('modal-open');
    $('#project-title').focus();
}

function closeModal() {
    $('#create-project-modal').fadeOut(300);
    $('body').removeClass('modal-open');
    $('#create-project-form')[0].reset();
}

// Project Creation
function createProject() {
    const formData = {
        title: $('#project-title').val(),
        description: $('#project-description').val(),
        github_url: $('#github-url').val(),
        action: 'create_project',
        nonce: CareerTracker.nonce
    };
    
    if (!formData.title.trim()) {
        showNotification('Project title is required', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = $('#create-project-form button[type="submit"]');
    const originalText = submitBtn.text();
    submitBtn.text('Creating...').prop('disabled', true);
    
    // AJAX call to create project
    $.ajax({
        url: ajaxurl || '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                showNotification('Project created successfully!', 'success');
                closeModal();
                // Reload page to show new project
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification(response.data || 'Failed to create project', 'error');
            }
        },
        error: function() {
            showNotification('Network error occurred. Please try again.', 'error');
        },
        complete: function() {
            submitBtn.text(originalText).prop('disabled', false);
        }
    });
}

// Notification System
function showNotification(message, type = 'info') {
    // Remove existing notifications
    $('.notification').remove();
    
    const notification = $(`
        <div class="notification notification-${type}">
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Animate in
    setTimeout(() => {
        notification.addClass('show');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.removeClass('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
    
    // Manual close
    notification.find('.notification-close').on('click', function() {
        notification.removeClass('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
}

// Utility Functions
const Utils = {
    
    // Debounce function for performance
    debounce: function(func, wait, immediate) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    },
    
    // Generate unique ID
    generateId: function() {
        return 'id_' + Math.random().toString(36).substr(2, 9);
    },
    
    // Sanitize HTML
    sanitizeHtml: function(str) {
        const temp = document.createElement('div');
        temp.textContent = str;
        return temp.innerHTML;
    },
    
    // Format date
    formatDate: function(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
};

// Export for other modules
window.CareerTracker = CareerTracker;
window.Utils = Utils;