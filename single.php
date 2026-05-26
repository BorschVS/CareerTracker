<?php
/**
 * Single Project Template
 * Career Tracker - Project Detail Page
 */

get_header(); ?>

<div id="project-container" class="glass-container">
    <?php while (have_posts()) : the_post(); ?>
        <div class="project-detail">
            <div class="project-hero glass-card">
                <div class="project-header-info">
                    <h1 class="project-title"><?php the_title(); ?></h1>
                    <div class="project-meta">
                        <span class="created-date">Created: <?php echo get_the_date(); ?></span>
                        <span class="last-modified">Modified: <?php echo get_the_modified_date(); ?></span>
                        <?php 
                        $github_url = get_post_meta(get_the_ID(), 'github_url', true);
                        if ($github_url && current_user_can('read_private_posts')) : ?>
                            <a href="<?php echo esc_url($github_url); ?>" class="github-link" target="_blank">
                                <i class="icon-github"></i> View on GitHub
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="project-actions">
                    <?php if (current_user_can('edit_posts')) : ?>
                        <button id="edit-project-btn" class="btn btn-secondary">
                            <i class="icon-edit"></i> Edit Project
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-primary add-section-btn">
                        <i class="icon-plus"></i> Add Section
                    </button>
                </div>
            </div>
            
            <div class="project-description glass-card">
                <?php if (get_the_content()) : ?>
                    <div class="description-content">
                        <?php the_content(); ?>
                    </div>
                <?php else : ?>
                    <div class="empty-description">
                        <p>No description available. Click "Edit Project" to add one.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div id="project-sections" class="sections-container">
                <!-- Sections will be loaded via JavaScript -->
            </div>
        </div>
        
        <script>
            // Pass project ID to JavaScript
            document.body.setAttribute('data-project-id', <?php echo get_the_ID(); ?>);
        </script>
    <?php endwhile; ?>
</div>

<!-- Section Creation Modal -->
<div id="section-modal" class="modal glass-modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Create New Section</h2>
        <form id="create-section-form">
            <div class="form-group">
                <label for="section-title-input">Section Title</label>
                <input type="text" id="section-title-input" name="section_title" required 
                       placeholder="Enter section title...">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Section</button>
                <button type="button" class="btn btn-secondary" onclick="ProjectManager.closeSectionModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Project Modal -->
<div id="edit-project-modal" class="modal glass-modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Project</h2>
        <form id="edit-project-form">
            <div class="form-group">
                <label for="edit-project-title">Project Title</label>
                <input type="text" id="edit-project-title" name="project_title" 
                       value="<?php echo esc_attr(get_the_title()); ?>" required>
            </div>
            <div class="form-group">
                <label for="edit-project-description">Description</label>
                <div id="edit-project-description" class="rich-editor">
                    <?php echo get_the_content(); ?>
                </div>
            </div>
            <div class="form-group">
                <label for="edit-github-url">GitHub URL</label>
                <input type="url" id="edit-github-url" name="github_url" 
                       value="<?php echo esc_attr(get_post_meta(get_the_ID(), 'github_url', true)); ?>">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Additional styles for single project page */
#project-container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 30px;
}

.project-hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
    padding: 30px;
}

.project-header-info h1 {
    color: white;
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 15px 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.project-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
}

.github-link {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px 10px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.github-link:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.project-actions {
    display: flex;
    gap: 15px;
}

.project-description {
    margin-bottom: 30px;
    padding: 25px;
}

.description-content {
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.7;
}

.empty-description {
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
    font-style: italic;
}

.sections-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.project-section {
    position: relative;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.section-title {
    color: white;
    font-size: 24px;
    font-weight: 600;
    margin: 0;
    cursor: pointer;
    transition: color 0.3s ease;
}

.section-title:hover {
    color: rgba(255, 255, 255, 0.8);
}

.section-controls {
    display: flex;
    gap: 10px;
}

.btn-icon {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.8);
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-icon:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.section-content {
    margin-bottom: 20px;
    min-height: 50px;
}

.empty-content {
    color: rgba(255, 255, 255, 0.6);
    font-style: italic;
    text-align: center;
    padding: 30px;
}

.section-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.add-section-container {
    text-align: center;
    padding: 40px;
}

.empty-project {
    text-align: center;
    padding: 60px 30px;
}

.empty-project h3 {
    color: white;
    font-size: 24px;
    margin-bottom: 15px;
}

.empty-project p {
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 30px;
}

/* Content Items */
.content-item {
    margin: 15px 0;
    position: relative;
}

.rich-editor {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 15px;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.6;
    min-height: 60px;
    transition: all 0.3s ease;
}

.rich-editor:focus,
.rich-editor.editor-focus {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.3);
    outline: none;
}

.subtitle-element {
    color: white;
    font-size: 20px;
    font-weight: 600;
    margin: 20px 0 10px 0;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    padding-bottom: 8px;
}

.code-element {
    position: relative;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    overflow: hidden;
}

.code-element pre {
    margin: 0;
    padding: 20px;
    color: #f8f8f2;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    line-height: 1.5;
    overflow-x: auto;
}

.code-language-select {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

/* Responsive */
@media (max-width: 768px) {
    .project-hero {
        flex-direction: column;
        gap: 20px;
    }
    
    .project-actions {
        width: 100%;
        justify-content: center;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .section-controls {
        align-self: flex-end;
    }
}
</style>

<script>
$(document).ready(function() {
    // Edit project functionality
    $('#edit-project-btn').on('click', function() {
        $('#edit-project-modal').fadeIn(300);
        // Initialize rich editor for description
        RichEditor.init($('#edit-project-description'));
    });
    
    $('#edit-project-modal .close, #edit-project-modal').on('click', function(e) {
        if (e.target === this) {
            $('#edit-project-modal').fadeOut(300);
        }
    });
    
    $('#edit-project-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'update_project',
            project_id: document.body.getAttribute('data-project-id'),
            title: $('#edit-project-title').val(),
            content: $('#edit-project-description').html(),
            github_url: $('#edit-github-url').val(),
            nonce: CareerTracker.nonce
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotification('Project updated successfully!', 'success');
                    $('#edit-project-modal').fadeOut(300);
                    // Refresh page to show changes
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(response.data || 'Error updating project', 'error');
                }
            },
            error: function() {
                showNotification('Network error. Please try again.', 'error');
            }
        });
    });
});
</script>

<?php get_footer(); ?>