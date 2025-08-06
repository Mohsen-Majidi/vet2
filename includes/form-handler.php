<?php
add_shortcode('vetonsite_form', function() {
    ob_start();
    include VOS_PATH . 'templates/vetonsite-page.php';
    return ob_get_clean();
});
