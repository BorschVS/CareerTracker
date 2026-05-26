/**
 * Project Management JavaScript
 * Handles project creation, editing, and section management
 */

$(document).ready(function() {
    if ($('.single-project').length) {
        ProjectManager.init();
    }
});

const ProjectManager = {
    
    currentProject: null,
    
    init: function() {
        this.bindEvents();
        this.loadProjectData();
        this.initSortable();
        console.log('Project Manager initialized');
    },
    
    bindEvents: function() {
        // Add new section
        $(document).on('click', '.add-section-btn', this.showSectionModal);
        
        // Edit section title
        $(document).on('click', '.section-title', this.editSectionTitle);
        
        // Delete section
        $(document).on('click', '.delete-section', this.deleteSection);
        
        // Add content to section
        $(document).on('click', '.add-content-btn', this.addContentElement);
        
        // Save section
        $(document).on('click', '.save-section', this.saveSection);
        
        // Section modal events
        $('#section-modal .close, #section-modal').on('click', function(e) {
            if (e.target === this) {
                ProjectManager.closeSectionModal();
            }
        });
        
        $('#create-section-form').on('submit', function(e) {
            e.preventDefault();
            ProjectManager.createSection();
        });
    },
    
    loadProjectData: function() {
        const projectId = $('body').data('project-id');
        if (projectId) {
            this.currentProject = projectId;
            this.loadSections();
        }
    },
    
    loadSections: function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'load_project_sections',
                project_id: this.currentProject,
                nonce: CareerTracker.nonce
            },
            success: function(response) {
                if (response.success) {
                    ProjectManager.renderSections(response.data);
                }
            }
        });
    },
    
    renderSections: function(sections) {
        const container = $('#project-sections');
        container.empty();
        
        if (sections.length === 0) {
            container.html(`
                <div class="empty-project glass-card">
                    <h3>Start Building Your Project Documentation</h3>
                    <p>Add sections to organize your content, tutorials, and notes.</p>
                    <button class="btn btn-primary add-section-btn">
                        <i class="icon-plus"></i> Add First Section
                    </button>
                </div>
            `);
        } else {
            sections.forEach(section => {
                container.append(this.createSectionElement(section));
            });
            
            // Add the "Add Section" button at the end
            container.append(`
                <div class="add-section-container">
                    <button class="btn btn-primary add-section-btn">
                        <i class="icon-plus"></i> Add Section
                    </button>
                </div>
            `);
        }
    },
    
    createSectionElement: function(section) {
        return $(`
            <div class="project-section glass-card" data-section-id="${section.id}">
                <div class="section-header">
                    <h3 class="section-title editable" contenteditable="false">${section.title}</h3>
                    <div class="section-controls">
                        <button class="btn-icon edit-section" title="Edit Title">
                            <i class="icon-edit"></i>
                        </button>
                        <button class="btn-icon delete-section" title="Delete Section">
                            <i class="icon-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="section-content">
                    ${this.renderSectionContent(section.content || [])}
                </div>
                <div class="section-footer">
                    <button class="btn btn-secondary add-content-btn">
                        <i class="icon-plus"></i> Add Content
                    </button>
                    <button class="btn btn-primary save-section" style="display: none;">
                        Save Changes
                    </button>
                </div>
            </div>
        `);
    },
    
    renderSectionContent: function(content) {
        if (!content || content.length === 0) {
            return '<div class="empty-content">Click "Add Content" to start adding content to this section.</div>';
        }
        
        let html = '';
        content.forEach(item => {
            html += this.createContentElement(item);
        });
        return html;
    },
    
    createContentElement: function(item) {
        switch (item.type) {
            case 'text':
                return `<div class="content-item" data-type="text">
                    <div class="rich-editor" data-content='${JSON.stringify(item.data)}'></div>
                </div>`;
            case 'subtitle':
                return `<div class="content-item" data-type="subtitle">
                    <h4 class="subtitle-element" contenteditable="true">${item.data.text}</h4>
                </div>`;
            case 'image':
                return `<div class="content-item" data-type="image">
                    <div class="image-element">
                        <img src="${item.data.url}" alt="${item.data.alt || ''}" />
                        <div class="image-actions">
                            <button class="btn-icon download-image" data-url="${item.data.url}">
                                <i class="icon-download"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
            case 'code':
                return `<div class="content-item" data-type="code">
                    <div class="code-element">
                        <pre><code contenteditable="true">${item.data.code}</code></pre>
                        <div class="code-language">${item.data.language || 'text'}</div>
                    </div>
                </div>`;
            default:
                return '';
        }
    },
    
    showSectionModal: function(e) {
        e.preventDefault();
        $('#section-modal').fadeIn(300);
        $('#section-title-input').focus();
    },
    
    closeSectionModal: function() {
        $('#section-modal').fadeOut(300);
        $('#create-section-form')[0].reset();
    },
    
    createSection: function() {
        const title = $('#section-title-input').val().trim();
        
        if (!title) {
            showNotification('Please enter a section title', 'error');
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'create_project_section',
                project_id: ProjectManager.currentProject,
                title: title,
                nonce: CareerTracker.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Section created successfully!', 'success');
                    ProjectManager.closeSectionModal();
                    ProjectManager.loadSections();
                } else {
                    showNotification(response.data || 'Error creating section', 'error');
                }
            },
            error: function() {
                showNotification('Network error. Please try again.', 'error');
            }
        });
    },
    
    editSectionTitle: function(e) {
        const titleElement = $(this);
        titleElement.attr('contenteditable', 'true').focus();
        
        // Save on Enter or blur
        titleElement.on('keydown blur', function(e) {
            if (e.type === 'keydown' && e.key !== 'Enter') return;
            if (e.type === 'keydown' && e.key === 'Enter') {
                e.preventDefault();
            }
            
            titleElement.attr('contenteditable', 'false').off('keydown blur');
            ProjectManager.saveSectionTitle(titleElement);
        });
    },
    
    saveSectionTitle: function(titleElement) {
        const section = titleElement.closest('.project-section');
        const sectionId = section.data('section-id');
        const newTitle = titleElement.text().trim();
        
        if (!newTitle) {
            showNotification('Section title cannot be empty', 'error');
            ProjectManager.loadSections();
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_section_title',
                section_id: sectionId,
                title: newTitle,
                nonce: CareerTracker.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Section title updated', 'success');
                } else {
                    showNotification(response.data || 'Error updating title', 'error');
                    ProjectManager.loadSections();
                }
            }
        });
    },
    
    deleteSection: function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
            return;
        }
        
        const section = $(this).closest('.project-section');
        const sectionId = section.data('section-id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_project_section',
                section_id: sectionId,
                nonce: CareerTracker.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Section deleted', 'success');
                    section.fadeOut(300, function() {
                        section.remove();
                    });
                } else {
                    showNotification(response.data || 'Error deleting section', 'error');
                }
            }
        });
    },
    
    addContentElement: function(e) {
        e.preventDefault();
        
        const section = $(this).closest('.project-section');
        const contentContainer = section.find('.section-content');
        
        // Show content type selector
        const selector = $(`
            <div class="content-type-selector glass-card">
                <h4>Add Content</h4>
                <div class="content-types">
                    <button class="content-type-btn" data-type="text">
                        <i class="icon-text"></i> Rich Text
                    </button>
                    <button class="content-type-btn" data-type="subtitle">
                        <i class="icon-heading"></i> Subtitle
                    </button>
                    <button class="content-type-btn" data-type="code">
                        <i class="icon-code"></i> Code Block
                    </button>
                    <button class="content-type-btn" data-type="image">
                        <i class="icon-image"></i> Image
                    </button>
                </div>
                <button class="btn btn-secondary cancel-content">Cancel</button>
            </div>
        `);
        
        contentContainer.append(selector);
        
        // Handle content type selection
        selector.find('.content-type-btn').on('click', function() {
            const type = $(this).data('type');
            selector.remove();
            ProjectManager.createContentByType(contentContainer, type);
        });
        
        selector.find('.cancel-content').on('click', function() {
            selector.remove();
        });
    },
    
    createContentByType: function(container, type) {
        let element;
        
        switch (type) {
            case 'text':
                element = $('<div class="content-item" data-type="text"><div class="rich-editor" contenteditable="true">Start typing...</div></div>');
                break;
            case 'subtitle':
                element = $('<div class="content-item" data-type="subtitle"><h4 class="subtitle-element" contenteditable="true">Enter subtitle...</h4></div>');
                break;
            case 'code':
                element = $(`
                    <div class="content-item" data-type="code">
                        <div class="code-element">
                            <pre><code contenteditable="true">// Enter your code here</code></pre>
                            <select class="code-language-select">
                                <option value="javascript">JavaScript</option>
                                <option value="html">HTML</option>
                                <option value="css">CSS</option>
                                <option value="php">PHP</option>
                                <option value="python">Python</option>
                                <option value="text">Plain Text</option>
                            </select>
                        </div>
                    </div>
                `);
                break;
            case 'image':
                element = $(`
                    <div class="content-item" data-type="image">
                        <div class="image-upload-area">
                            <input type="file" class="image-input" accept="image/*" />
                            <div class="upload-placeholder">
                                <i class="icon-image"></i>
                                <p>Click to upload an image</p>
                            </div>
                        </div>
                    </div>
                `);
                break;
        }
        
        if (element) {
            container.find('.empty-content').remove();
            container.append(element);
            
            // Initialize rich editor if needed
            if (type === 'text') {
                RichEditor.init(element.find('.rich-editor'));
            }
            
            // Show save button
            container.closest('.project-section').find('.save-section').show();
        }
    },
    
    saveSection: function(e) {
        e.preventDefault();
        
        const section = $(this).closest('.project-section');
        const sectionId = section.data('section-id');
        const contentItems = [];
        
        section.find('.content-item').each(function() {
            const item = $(this);
            const type = item.data('type');
            let data = {};
            
            switch (type) {
                case 'text':
                    data = {
                        html: item.find('.rich-editor').html(),
                        text: item.find('.rich-editor').text()
                    };
                    break;
                case 'subtitle':
                    data = {
                        text: item.find('.subtitle-element').text()
                    };
                    break;
                case 'code':
                    data = {
                        code: item.find('code').text(),
                        language: item.find('.code-language-select').val() || 'text'
                    };
                    break;
                case 'image':
                    const img = item.find('img');
                    if (img.length) {
                        data = {
                            url: img.attr('src'),
                            alt: img.attr('alt') || ''
                        };
                    }
                    break;
            }
            
            contentItems.push({ type, data });
        });
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_section_content',
                section_id: sectionId,
                content: JSON.stringify(contentItems),
                nonce: CareerTracker.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Section saved successfully!', 'success');
                    section.find('.save-section').hide();
                } else {
                    showNotification(response.data || 'Error saving section', 'error');
                }
            }
        });
    },
    
    initSortable: function() {
        // Initialize sortable for sections (if jQuery UI is available)
        if ($.fn.sortable) {
            $('#project-sections').sortable({
                handle: '.section-header',
                placeholder: 'section-placeholder',
                update: function(event, ui) {
                    ProjectManager.updateSectionOrder();
                }
            });
        }
    },
    
    updateSectionOrder: function() {
        const order = [];
        $('#project-sections .project-section').each(function(index) {
            order.push({
                id: $(this).data('section-id'),
                order: index
            });
        });
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_section_order',
                project_id: this.currentProject,
                order: JSON.stringify(order),
                nonce: CareerTracker.nonce
            }
        });
    }
};

// Export for global access
window.ProjectManager = ProjectManager;