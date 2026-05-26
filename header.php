<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
    
    <!-- Preload critical resources for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- jQuery library -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<header class="site-header">
    <div class="container">
        <a href="<?php echo home_url(); ?>" class="site-logo">
            <?php bloginfo('name'); ?>
        </a>
        
        <nav class="main-nav">
            <ul>
                <li><a href="<?php echo home_url(); ?>">Projects</a></li>
                <?php if (is_user_logged_in()) : ?>
                    <li><a href="#" id="add-project-nav">+ New Project</a></li>
                    <li><a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a></li>
                <?php else : ?>
                    <li><a href="<?php echo wp_login_url(); ?>">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>