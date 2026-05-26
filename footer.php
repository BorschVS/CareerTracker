<footer class="site-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Personal project documentation system.</p>
    </div>
</footer>

<!-- Include custom JavaScript -->
<script src="<?php echo get_template_directory_uri(); ?>/js/app.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/editor.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/project-manager.js"></script>

<?php wp_footer(); ?>
</body>
</html>