<?php
/**
 * Main template file
 * Career Tracker - Personal Project Documentation App
 */

get_header(); ?>

<div id="main-container" class="glass-container">
    <div class="content-wrapper">
        <?php if (have_posts()) : ?>
            <div class="projects-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" class="project-card glass-card">
                        <div class="project-header">
                            <h2 class="project-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <?php 
                            $github_url = get_post_meta(get_the_ID(), 'github_url', true);
                            if ($github_url && current_user_can('read_private_posts')) : ?>
                                <a href="<?php echo esc_url($github_url); ?>" class="github-link" target="_blank">
                                    <i class="icon-github"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="project-content">
                            <?php the_excerpt(); ?>
                        </div>
                        <div class="project-meta">
                            <span class="date"><?php echo get_the_date(); ?></span>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="no-projects glass-card">
                <h2>No Projects Yet</h2>
                <p>Start by creating your first project.</p>
                <button id="create-project-btn" class="btn btn-primary">
                    <i class="icon-plus"></i> Create Project
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="create-project-modal" class="modal glass-modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Create New Project</h2>
        <form id="create-project-form">
            <div class="form-group">
                <label for="project-title">Project Title</label>
                <input type="text" id="project-title" name="project_title" required 
                       placeholder="Enter project title...">
            </div>
            <div class="form-group">
                <label for="project-description">Description</label>
                <textarea id="project-description" name="project_description"
                          placeholder="Brief project description..."></textarea>
            </div>
            <div class="form-group">
                <label for="github-url">GitHub URL (Optional)</label>
                <input type="url" id="github-url" name="github_url"
                       placeholder="https://github.com/username/repository">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Project</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php get_footer(); ?>