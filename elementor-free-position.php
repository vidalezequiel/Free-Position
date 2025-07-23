<?php
/*
Plugin Name: Free Position
Plugin URI: https://github.com/ezeevidal
Description: Activa el posicionamiento libre sobre cualquier widget de Elementor configurandolo en "Avanzado/Posicion: Absoluto". Permite mover elementos libremente en el lienzo y guardar su ubicación de forma persistente.
Version: 1.0
Author: Ezequiel Vidal
Author URI: https://linkedin.com/in/ezeevidal
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Free Position
*/


if (!defined('ABSPATH')) exit;

function efp_force_control($element) {
    $element->add_control(
        'efp_enable_free_position',
        [
            'label' => __('Posición libre', 'plugin-name'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __('Sí', 'plugin-name'),
            'label_off' => __('No', 'plugin-name'),
            'return_value' => 'yes',
            'default' => 'no',
        ]
    );
}

// Hooks para todos los tipos de elementos posibles
foreach (['common', 'section', 'column', 'container'] as $type) {
    add_action("elementor/element/{$type}/after_add_controls", 'efp_force_control');
}

add_action('elementor/frontend/widget/before_render', function($widget) {
    $settings = $widget->get_settings();
    if (!empty($settings['efp_enable_free_position']) && $settings['efp_enable_free_position'] === 'yes') {
        $widget->add_render_attribute('_wrapper', [
            'class' => 'efp-draggable efp-enabled',
            'data-id' => $widget->get_id(),
            'data-post' => get_the_ID()
        ]);
    }
});

function efp_enqueue_assets() {
    wp_enqueue_style('efp-style', plugins_url('assets/style.css', __FILE__));
    wp_enqueue_script('efp-script', plugins_url('assets/script.js', __FILE__), ['jquery'], false, true);
    wp_localize_script('efp-script', 'efp_ajax_obj', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('efp_save_position')
    ]);
}
add_action('wp_enqueue_scripts', 'efp_enqueue_assets');
add_action('elementor/editor/after_enqueue_scripts', 'efp_enqueue_assets');

add_action('wp_ajax_efp_save_position', function () {
    check_ajax_referer('efp_save_position', 'nonce');

    $post_id = intval($_POST['post_id']);
    $widget_id = sanitize_text_field($_POST['widget_id']);
    $position = $_POST['position'];

    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error('No permission');
    }

    update_post_meta($post_id, '_efp_' . $widget_id, $position);
    wp_send_json_success();
});
