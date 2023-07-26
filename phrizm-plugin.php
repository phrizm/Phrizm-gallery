<?php
/*
Plugin Name: Phrizm Gallery
Plugin URI: http://yourwebsite.com/
Description: Un plugin per gestire le gallerie di immagini.
Version: 1.0
Author: Il tuo nome
Author URI: http://yourwebsite.com/
*/

function phrizm_enqueue_scripts() {
    wp_enqueue_style('swiper', plugin_dir_url(__FILE__) . 'swiper-bundle.min.css');
    wp_enqueue_style('swiper-gallery', plugin_dir_url(__FILE__) . 'swiper-style.css');
    wp_enqueue_script('swiper', plugin_dir_url(__FILE__) . 'swiper-bundle.min.js', array('jquery'), false, true);
    wp_enqueue_script('init', plugin_dir_url(__FILE__) . 'init.js', array('jquery', 'swiper'), false, true);

    // Include scripts for Masonry
    wp_enqueue_script('masonry', plugin_dir_url(__FILE__) . 'masonry.pkgd.min.js', array('jquery'), false, true);
    wp_enqueue_script('imagesloaded', plugin_dir_url(__FILE__) . 'imagesloaded.pkgd.min.js', array('jquery'), false, true);
    wp_enqueue_script('masonry-init', plugin_dir_url(__FILE__) . 'masonry-init.js', array('jquery', 'masonry', 'imagesloaded'), false, true);
}
add_action('wp_enqueue_scripts', 'phrizm_enqueue_scripts');

function phrizm_gallery_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'phrizm_gallery');

    // Search for the post with the specified title
    $posts = get_posts(array(
        'title' => $atts['id'],
        'post_type' => 'phrizm_gallery',
    ));

    // If there is no post with that title, return an empty string
    if (count($posts) === 0) {
        return '';
    }

    // Get the ID of the first post found
    $post_id = $posts[0]->ID;

    // Get the selected template
$selected_template = get_post_meta($post_id, '_phrizm_gallery_template', true);

// Depending on the template, include the correct file and call the correct function
switch ($selected_template) {
    case 'masonry':
        include_once plugin_dir_path(__FILE__) . 'templates/gallery-masonry-template.php';
        $output = phrizm_gallery_template_masonry($atts, $post_id);
        break;
    case 'swiper-fade':
        include_once plugin_dir_path(__FILE__) . 'templates/gallery-swiper-fade.php';
        $output = phrizm_gallery_template_swiper_fade($atts, $post_id);
        break;
    case 'swiper-coverflow':
        include_once plugin_dir_path(__FILE__) . 'templates/gallery-swiper-coverflow.php';
        $output = phrizm_gallery_template_swiper_coverflow($atts, $post_id);
        break;
    case 'swiper-auto':
        include_once plugin_dir_path(__FILE__) . 'templates/gallery-swiper-auto.php';
        $output = phrizm_gallery_template_swiper_auto($atts, $post_id);
        break;
    case 'masonry-equal':
        include_once plugin_dir_path(__FILE__) . 'templates/gallery-masonry-equal.php';
        $output = phrizm_gallery_template_masonry_equal($atts, $post_id);
        break;
    case 'masonry-creative':
        include_once plugin_dir_path(__FILE__) . 'templates/gallery-masonry-creative.php';
        $output = phrizm_gallery_template_masonry_creative($atts, $post_id);
        break;
    default:
        include_once plugin_dir_path(__FILE__) . 'templates/gallery-swiper.php';
        $output = phrizm_gallery_template_swiper($atts, $post_id);
        break;
}

return $output;

}
add_shortcode('phrizm_gallery', 'phrizm_gallery_shortcode');

function phrizm_galleries_post_type() {
    $args = array(
        'public' => true,
        'label'  => 'Phrizm Gallery',
        'menu_icon' => 'dashicons-images-alt2',
        'supports' => array('title'),
        'show_in_rest' => true
    );
    register_post_type('phrizm_gallery', $args);
}
add_action('init', 'phrizm_galleries_post_type');

function phrizm_galleries_metabox() {
    add_meta_box(
        'phrizm_gallery_images',
        'Immagini della Galleria',
        'phrizm_galleries_metabox_callback',
        'phrizm_gallery',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'phrizm_galleries_metabox');

function phrizm_galleries_admin_scripts($hook) {
    global $post;

    if ($hook == 'post-new.php' || $hook == 'post.php') {
        if ('phrizm_gallery' === $post->post_type) {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('phrizm-galleries-admin-script', plugin_dir_url(__FILE__) . 'admin.js', array('jquery', 'jquery-ui-sortable'));

            // Add this line to include your CSS file
            wp_enqueue_style('phrizm-galleries-admin-style', plugin_dir_url(__FILE__) . 'adminstyle.css');

            // Pass the security nonce to your JavaScript code
            wp_localize_script('phrizm-galleries-admin-script', 'my_plugin', array(
                'security' => wp_create_nonce('save-phrizm-images'),
            ));
        }
    }
}
add_action('admin_enqueue_scripts', 'phrizm_galleries_admin_scripts');


function phrizm_galleries_metabox_callback($post) {
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");

    // Add the select field here
    $selected_template = get_post_meta($post->ID, '_phrizm_gallery_template', true);
    ?>
    <div>
    <label for="phrizm-gallery-template">Template:</label>
    <select id="phrizm-gallery-template" name="phrizm_gallery_template">
        <option value="swiper" <?php selected($selected_template, 'swiper'); ?>>Swiper</option>
        <option value="swiper-fade" <?php selected($selected_template, 'swiper-fade'); ?>>Swiper Fade</option>
        <option value="swiper-coverflow" <?php selected($selected_template, 'swiper-coverflow'); ?>>Swiper Coverflow</option>
        <option value="swiper-auto" <?php selected($selected_template, 'swiper-auto'); ?>>Swiper Auto</option>
        <option value="masonry" <?php selected($selected_template, 'masonry'); ?>>Masonry</option>
        <option value="masonry-equal" <?php selected($selected_template, 'masonry-equal'); ?>>Masonry Equal</option>
        <option value="masonry-creative" <?php selected($selected_template, 'masonry-creative'); ?>>Masonry Creative</option>
    </select>
</div>
    <div id='phrizm-images-preview'>
    <?php
    $gallery_images = get_post_meta($post->ID, '_phrizm_gallery_images', true);
    if ($gallery_images) {
        foreach ($gallery_images as $image_id) {
            $image_src = wp_get_attachment_image_src($image_id);
            if ($image_src) {
                echo '<div class="phrizm-image" data-id="' . $image_id . '"><img src="' . $image_src[0] . '" style="max-width: 100%;" /><button class="remove-image">&times;</button></div>';
            }
        }
    }
    ?>
    </div>
    <input id="upload_image_button" type="button" class="button" value="Carica immagini" />
    <?php
}

function save_phrizm_images() {
    check_ajax_referer('save-phrizm-images', 'security');

    if (!isset($_POST['post_id']) || !isset($_POST['image_ids'])) {
        return;
    }

    $post_id = $_POST['post_id'];
    $image_ids = $_POST['image_ids'];
    $template = $_POST['template'];  // Aggiungi questa riga per ottenere il valore del template

    update_post_meta($post_id, '_phrizm_gallery_images', $image_ids);
    update_post_meta($post_id, '_phrizm_gallery_template', $template);  // Aggiungi questa riga per salvare il valore del template

    wp_die();

}
add_action('wp_ajax_save_phrizm_images', 'save_phrizm_images');
add_action('wp_ajax_nopriv_save_phrizm_images', 'save_phrizm_images');

function save_phrizm_gallery_template($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['phrizm_gallery_template'])) {
        update_post_meta($post_id, '_phrizm_gallery_template', $_POST['phrizm_gallery_template']);
    }
}
add_action('save_post', 'save_phrizm_gallery_template');

function phrizm_galleries_columns($columns) {
    $columns['shortcode'] = 'Shortcode';
    return $columns;
}
add_filter('manage_phrizm_gallery_posts_columns', 'phrizm_galleries_columns');

function phrizm_galleries_columns_data($column, $post_id) {
    if ($column == 'shortcode') {
        $post = get_post($post_id);
        echo '[phrizm_gallery id="' . $post->post_title . '"]';
    }
}
add_action('manage_phrizm_gallery_posts_custom_column', 'phrizm_galleries_columns_data', 10, 2);
?>
