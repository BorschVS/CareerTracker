<?php
/**
 * Career Tracker Theme Functions
 * WordPress theme functions and AJAX handlers
 */

// Theme setup
function career_tracker_setup() {
    // Add theme support
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => 'Primary Navigation',
    ));
}
add_action('after_setup_theme', 'career_tracker_setup');

// Enqueue styles and scripts
function career_tracker_scripts() {
    // Enqueue theme stylesheet
    wp_enqueue_style('career-tracker-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // Enqueue jQuery (already included in WordPress)
    wp_enqueue_script('jquery');
    
    // Enqueue custom scripts
    wp_enqueue_script('career-tracker-app', get_template_directory_uri() . '/js/app.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('career-tracker-editor', get_template_directory_uri() . '/js/editor.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('career-tracker-project', get_template_directory_uri() . '/js/project-manager.js', array('jquery'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('career-tracker-app', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('career_tracker_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'career_tracker_scripts');

// Create custom database tables
function create_career_tracker_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Sections table
    $sections_table = $wpdb->prefix . 'career_sections';
    $sections_sql = "CREATE TABLE $sections_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        project_id bigint(20) NOT NULL,
        title varchar(255) NOT NULL,
        content longtext,
        section_order int(11) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY project_id (project_id),
        KEY section_order (section_order)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sections_sql);
}
add_action('after_switch_theme', 'create_career_tracker_tables');

// AJAX: Create new project
function ajax_create_project() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'career_tracker_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user permissions
    if (!current_user_can('publish_posts')) {
        wp_send_json_error('You do not have permission to create projects');
    }
    
    $title = sanitize_text_field($_POST['title']);
    $description = sanitize_textarea_field($_POST['description']);
    $github_url = esc_url_raw($_POST['github_url']);
    
    if (empty($title)) {
        wp_send_json_error('Project title is required');
    }
    
    // Create the project post
    $project_data = array(
        'post_title' => $title,
        'post_content' => $description,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_author' => get_current_user_id(),
    );
    
    $project_id = wp_insert_post($project_data);
    
    if (is_wp_error($project_id)) {
        wp_send_json_error('Unable to create project. Please try again.');
    }
    
    // Save GitHub URL as meta
    if (!empty($github_url)) {
        update_post_meta($project_id, 'github_url', $github_url);
    }
    
    wp_send_json_success(array(
        'project_id' => $project_id,
        'message' => 'Project created successfully!'
    ));
}
add_action('wp_ajax_create_project', 'ajax_create_project');

// AJAX: Update project
function ajax_update_project() {
    if (!wp_verify_nonce($_POST['nonce'], 'career_tracker_nonce')) {
        wp_die('Security check failed');
    }
    
    $project_id = intval($_POST['project_id']);
    $title = sanitize_text_field($_POST['title']);
    $content = wp_kses_post($_POST['content']);
    $github_url = esc_url_raw($_POST['github_url']);
    
    if (!current_user_can('edit_post', $project_id)) {
        wp_send_json_error('You do not have permission to edit this project');
    }
    
    $project_data = array(
        'ID' => $project_id,
        'post_title' => $title,
        'post_content' => $content,
    );
    
    $result = wp_update_post($project_data);
    
    if (is_wp_error($result)) {
        wp_send_json_error('Unable to update project. Please try again.');
    }
    
    // Update GitHub URL
    if (!empty($github_url)) {
        update_post_meta($project_id, 'github_url', $github_url);
    } else {
        delete_post_meta($project_id, 'github_url');
    }
    
    wp_send_json_success('Project updated successfully!');
}
add_action('wp_ajax_update_project', 'ajax_update_project');

// AJAX: Load project sections
function ajax_load_project_sections() {
    if (!wp_verify_nonce($_POST['nonce'], 'career_tracker_nonce')) {
        wp_die('Security check failed');
    }
    
    global $wpdb;
    $project_id = intval($_POST['project_id']);
    
    $sections = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}career_sections 
         WHERE project_id = %d 
         ORDER BY section_order ASC, id ASC",
        $project_id
    ), ARRAY_A);
    
    // Parse content JSON
    foreach ($sections as &$section) {
        if (!empty($section['content'])) {
            $section['content'] = json_decode($section['content'], true);
        } else {
            $section['content'] = array();
        }
    }
    
    wp_send_json_success($sections);
}
add_action('wp_ajax_load_project_sections', 'ajax_load_project_sections');

// AJAX: Create project section
function ajax_create_project_section() {
    if (!wp_verify_nonce($_POST['nonce'], 'career_tracker_nonce')) {
        wp_die('Security check failed');
    }
    
    global $wpdb;
    $project_id = intval($_POST['project_id']);
    $title = sanitize_text_field($_POST['title']);
    
    // Check if user can edit the project
    if (!current_user_can('edit_post', $project_id)) {
        wp_send_json_error('You do not have permission to modify this project');
    }
    
    // Get next order number
    $max_order = $wpdb->get_var($wpdb->prepare(
        "SELECT MAX(section_order) FROM {$wpdb->prefix}career_sections WHERE project_id = %d",
        $project_id
    ));
    $next_order = ($max_order !== null) ? $max_order + 1 : 0;
    
    // Insert section
    $result = $wpdb->insert(
        $wpdb->prefix . 'career_sections',
        array(
            'project_id' => $project_id,
            'title' => $title,
            'content' => '[]',
            'section_order' => $next_order,
        ),
        array('%d', '%s', '%s', '%d')
    );
    
    if ($result === false) {
        wp_send_json_error('Unable to create section. Please try again.');
    }
    
    wp_send_json_success(array(
        'section_id' => $wpdb->insert_id,
        'message' => 'Section created successfully!'
    ));
}
add_action('wp_ajax_create_project_section', 'ajax_create_project_section');

// AJAX: Update section title
function ajax_update_section_title() {
    if (!wp_verify_nonce($_POST['nonce'], 'career_tracker_nonce')) {
        wp_die('Security check failed');
    }
    
    global $wpdb;
    $section_id = intval($_POST['section_id']);
    $title = sanitize_text_field($_POST['title']);
    
    // Get section and check permissions
    $section = $wpdb->get_row($wpdb->prepare(
        "SELECT project_id FROM {$wpdb->prefix}career_sections WHERE id = %d",
        $section_id
    ));
    
    if (!$section || !current_user_can('edit_post', $section->project_id)) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $result = $wpdb->update(
        $wpdb->prefix . 'career_sections',
        array('title' => $title),
        array('id' => $section_id),
        array('%s'),
        array('%d')
    );
    
    if ($result === false) {
        wp_send_json_error('Failed to update section title');
    }
    
    wp_send_json_success('Section title updated');
}
add_action('wp_ajax_update_section_title', 'ajax_update_section_title');

// AJAX: Save section content
function ajax_save_section_content() {
    if (!wp_verify_nonce($_POST['nonce'], 'career_tracker_nonce')) {
        wp_die('Security check failed');
    }
    
    global $wpdb;
    $section_id = intval($_POST['section_id']);
    $content = $_POST['content']; // Already JSON string
    
    // Validate JSON
    $decoded_content = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('Invalid content format');
    }
    
    // Get section and check permissions
    $section = $wpdb->get_row($wpdb->prepare(
        "SELECT project_id FROM {$wpdb->prefix}career_sections WHERE id = %d",
        $section_id
    ));
    
    if (!$section || !current_user_can('edit_post', $section->project_id)) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $result = $wpdb->update(
        $wpdb->prefix . 'career_sections',
        array('content' => $content),
        array('id' => $section_id),
        array('%s'),
        array('%d')
    );
    
    if ($result === false) {
        wp_send_json_error('Failed to save section content');
    }
    
    wp_send_json_success('Section content saved');
}
add_action('wp_ajax_save_section_content', 'ajax_save_section_content');

// AJAX: Delete project section
function ajax_delete_project_section() {
    if (!wp_verify_nonce($_POST['nonce'], 'career_tracker_nonce')) {
        wp_die('Security check failed');
    }
    
    global $wpdb;
    $section_id = intval($_POST['section_id']);
    
    // Get section and check permissions
    $section = $wpdb->get_row($wpdb->prepare(
        "SELECT project_id FROM {$wpdb->prefix}career_sections WHERE id = %d",
        $section_id
    ));
    
    if (!$section || !current_user_can('edit_post', $section->project_id)) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $result = $wpdb->delete(
        $wpdb->prefix . 'career_sections',
        array('id' => $section_id),
        array('%d')
    );
    
    if ($result === false) {
        wp_send_json_error('Failed to delete section');
    }
    
    wp_send_json_success('Section deleted');
}
add_action('wp_ajax_delete_project_section', 'ajax_delete_project_section');

// AJAX: Update section order
function ajax_update_section_order() {
    if (!wp_verify_nonce($_POST['nonce'], 'career_tracker_nonce')) {
        wp_die('Security check failed');
    }
    
    global $wpdb;
    $project_id = intval($_POST['project_id']);
    $order = json_decode($_POST['order'], true);
    
    if (!current_user_can('edit_post', $project_id)) {
        wp_send_json_error('Insufficient permissions');
    }
    
    // Update each section's order
    foreach ($order as $item) {
        $wpdb->update(
            $wpdb->prefix . 'career_sections',
            array('section_order' => intval($item['order'])),
            array('id' => intval($item['id'])),
            array('%d'),
            array('%d')
        );
    }
    
    wp_send_json_success('Section order updated');
}
add_action('wp_ajax_update_section_order', 'ajax_update_section_order');

// AJAX: Upload editor image
function ajax_upload_editor_image() {
    if (!wp_verify_nonce($_POST['nonce'], 'career_tracker_nonce')) {
        wp_die('Security check failed');
    }
    
    if (!current_user_can('upload_files')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    if (!isset($_FILES['file'])) {
        wp_send_json_error('No file uploaded');
    }
    
    $file = $_FILES['file'];
    
    // Check file type
    $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
    if (!in_array($file['type'], $allowed_types)) {
        wp_send_json_error('Invalid file type. Only images are allowed.');
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        wp_send_json_error('File too large. Maximum size is 5MB.');
    }
    
    // Upload file
    $upload = wp_handle_upload($file, array('test_form' => false));
    
    if (isset($upload['error'])) {
        wp_send_json_error($upload['error']);
    }
    
    // Create attachment
    $attachment = array(
        'guid' => $upload['url'],
        'post_mime_type' => $upload['type'],
        'post_title' => sanitize_file_name($file['name']),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    
    $attachment_id = wp_insert_attachment($attachment, $upload['file']);
    
    if (is_wp_error($attachment_id)) {
        wp_send_json_error('Failed to create attachment');
    }
    
    // Generate metadata
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
    wp_update_attachment_metadata($attachment_id, $attachment_data);
    
    wp_send_json_success(array(
        'url' => $upload['url'],
        'attachment_id' => $attachment_id,
        'message' => 'Image uploaded successfully'
    ));
}
add_action('wp_ajax_upload_editor_image', 'ajax_upload_editor_image');

// Custom login styles
function career_tracker_login_styles() {
    ?>
    <style>
        body.login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }
        
        .login h1 a {
            background-image: none !important;
            color: white !important;
            font-size: 24px !important;
            font-weight: bold !important;
            text-decoration: none !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
        }
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'career_tracker_login_styles');

// Add custom CSS for editor toolbar and notifications
function career_tracker_custom_styles() {
    ?>
    <style>
    /* Editor Toolbar Styles */
    .editor-toolbar {
        display: none;
        margin-bottom: 10px;
        padding: 10px 15px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .editor-toolbar.active {
        display: flex;
    }
    
    .toolbar-group {
        display: flex;
        gap: 2px;
    }
    
    .toolbar-btn {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: rgba(255, 255, 255, 0.8);
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }
    
    .toolbar-btn:hover,
    .toolbar-btn.active {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }
    
    .toolbar-separator {
        width: 1px;
        background: rgba(255, 255, 255, 0.3);
        margin: 0 8px;
    }
    
    /* Icon definitions */
    .icon-bold::before { content: 'B'; font-weight: bold; }
    .icon-italic::before { content: 'I'; font-style: italic; }
    .icon-underline::before { content: 'U'; text-decoration: underline; }
    .icon-strikethrough::before { content: 'S'; text-decoration: line-through; }
    .icon-list-ul::before { content: '•'; }
    .icon-list-ol::before { content: '1.'; font-size: 10px; }
    .icon-quote::before { content: '"'; }
    .icon-code::before { content: '<>'; font-size: 10px; }
    .icon-code-block::before { content: '{}'; font-size: 10px; }
    .icon-link::before { content: '🔗'; }
    .icon-image::before { content: '📷'; }
    .icon-undo::before { content: '↶'; }
    .icon-redo::before { content: '↷'; }
    .icon-edit::before { content: '✏️'; }
    .icon-trash::before { content: '🗑️'; }
    .icon-text::before { content: 'T'; }
    .icon-heading::before { content: 'H'; }
    .icon-download::before { content: '⬇'; }
    
    /* Notification System */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 10px;
        padding: 15px 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        min-width: 300px;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification-success {
        border-left: 4px solid #4ade80;
    }
    
    .notification-error {
        border-left: 4px solid #ef4444;
    }
    
    .notification-info {
        border-left: 4px solid #3b82f6;
    }
    
    .notification-message {
        flex: 1;
        color: #333;
    }
    
    .notification-close {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: #666;
        padding: 0;
        width: 20px;
        height: 20px;
    }
    
    .notification-close:hover {
        color: #333;
    }
    
    /* Content Type Selector */
    .content-type-selector {
        margin: 20px 0;
        text-align: center;
    }
    
    .content-type-selector h4 {
        color: white;
        margin-bottom: 20px;
    }
    
    .content-types {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .content-type-btn {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 15px 10px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        text-align: center;
    }
    
    .content-type-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
    }
    
    /* Image Upload */
    .image-upload-area {
        border: 2px dashed rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .image-upload-area:hover {
        border-color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.05);
    }
    
    .image-input {
        display: none;
    }
    
    .upload-placeholder {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .upload-placeholder i {
        font-size: 48px;
        margin-bottom: 15px;
        display: block;
    }
    
    /* Image Element */
    .image-element {
        position: relative;
        text-align: center;
    }
    
    .image-element img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
    }
    
    .image-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        display: flex;
        gap: 5px;
    }
    
    /* Modal Adjustments */
    body.modal-open {
        overflow: hidden;
    }
    
    .glass-modal .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .notification {
            right: 10px;
            left: 10px;
            min-width: auto;
        }
        
        .editor-toolbar {
            padding: 8px 10px;
        }
        
        .toolbar-btn {
            width: 28px;
            height: 28px;
            font-size: 12px;
        }
        
        .content-types {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    </style>
    <?php
}
add_action('wp_head', 'career_tracker_custom_styles');

// Add JavaScript variables to head
function career_tracker_js_vars() {
    if (is_user_logged_in()) {
        ?>
        <script>
            // Pass AJAX URL and nonce to JavaScript
            window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            window.CareerTracker = window.CareerTracker || {};
            window.CareerTracker.nonce = '<?php echo wp_create_nonce('career_tracker_nonce'); ?>';
        </script>
        <?php
    }
}
add_action('wp_head', 'career_tracker_js_vars');

// Prevent non-logged-in users from accessing the site
function career_tracker_auth_redirect() {
    if (!is_user_logged_in() && !is_page('wp-login.php') && !is_admin()) {
        wp_redirect(wp_login_url());
        exit();
    }
}
add_action('template_redirect', 'career_tracker_auth_redirect');

?>