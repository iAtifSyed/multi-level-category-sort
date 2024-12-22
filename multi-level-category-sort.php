<?php
/*
Plugin Name: Multi-Level Category Sort WooCommerce
Description: A custom plugin to sort multi-level WooCommerce categories dynamically with dropdown options in the admin panel.
Version: 1.5
Author: Atif Syed
Author URI: https://iatifsyed.github.io/
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add admin menu
add_action('admin_menu', 'dropdown_category_sort_admin_menu');

function dropdown_category_sort_admin_menu() {
    add_menu_page(
        'Dropdown Category Sorter',
        'Category Sorter',
        'manage_options',
        'dropdown-category-sort',
        'dropdown_category_sort_admin_page',
        'dashicons-sort',
        56
    );

    add_submenu_page(
        'dropdown-category-sort',
        'Support & About',
        'Support & About',
        'manage_options',
        'dropdown-category-sort-support',
        'dropdown_category_sort_support_page'
    );
}

// Admin page content
function dropdown_category_sort_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sorted_categories'])) {
        $parent_id = intval($_POST['parent_id']);
        $sorted_data = json_decode(stripslashes($_POST['sorted_categories']), true);
        update_option("dropdown_category_sort_data_{$parent_id}", $sorted_data);
        echo '<div class="notice notice-success"><p>Sorting saved successfully!</p></div>';
    }

    $parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
    $saved_sorting = get_option("dropdown_category_sort_data_{$parent_id}", []);

    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent' => $parent_id,
    ]);

    echo '<div class="wrap">';
    echo '<h1>Dropdown Category Sorter</h1>';
    echo '<p>Select a parent category to view and sort its subcategories.</p>';

    echo '<form method="GET" action="">';
    echo '<input type="hidden" name="page" value="dropdown-category-sort">';
    echo '<select name="parent_id" onchange="this.form.submit()">';
    echo '<option value="0"' . selected(0, $parent_id, false) . '>Top Level</option>';
    render_parent_dropdown_options(0, $parent_id);
    echo '</select>';
    echo '</form>';

    echo '<form method="POST" id="dropdown-category-sort-form">';
    echo '<input type="hidden" name="parent_id" value="' . esc_attr($parent_id) . '">';
    echo '<ul class="category-tree">';
    
    $sorted_categories = array_merge(
        array_intersect($saved_sorting, wp_list_pluck($categories, 'term_id')),
        array_diff(wp_list_pluck($categories, 'term_id'), $saved_sorting)
    );

    foreach ($sorted_categories as $category_id) {
        $category = get_term($category_id, 'product_cat');
        if ($category) {
            echo '<li class="category-item" data-id="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</li>';
        }
    }

    echo '</ul>';
    echo '<input type="hidden" name="sorted_categories" id="sorted-categories" value="">';
    echo '<button type="submit" class="button button-primary">Save Sorting</button>';
    echo '</form>';
    echo '</div>';
}

// Support & About page content
function dropdown_category_sort_support_page() {
    echo '<div class="wrap">';
    echo '<h1>Support & About</h1>';
    echo '<p>🌟 <strong>Passionate Innovator | Entrepreneur | Digital Creator | Tech Enthusiast</strong> 🌟</p>';
    echo '<p>Hi, I’m Atif Syed, a WordPress and Android expert, eCommerce specialist, and YouTube pioneer since 2010. With a creative spark and a love for technology, I bring ideas to life through innovative solutions. From running 100+ websites to building plugins, apps, and thriving businesses, I embrace challenges with passion and purpose.</p>';
    echo '<ul>';
    echo '<li><a href="https://iatifsyed.github.io/" target="_blank">Hire me on a project</a></li>';
    echo '<li><a href="https://github.com/iAtifSyed/multi-level-category-sort" target="_blank">Plugin Changelog & Updates</a></li>';
    echo '<li><a href="mailto:atifsyedlive@gmail.com" target="_blank">Write me an Email</a></li>';
    echo '</ul>';
    echo '<p>Developed by: <a href="https://iatifsyed.github.io/" target="_blank">Atif Syed</a></p>';
    echo '</div>';
}

// Recursive function to render parent dropdown options
function render_parent_dropdown_options($parent_id, $current_parent) {
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent' => $parent_id,
    ]);

    foreach ($categories as $category) {
        $selected = selected($category->term_id, $current_parent, false);
        echo '<option value="' . esc_attr($category->term_id) . '"' . $selected . '>' . esc_html($category->name) . '</option>';
        render_parent_dropdown_options($category->term_id, $current_parent);
    }
}

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', 'dropdown_category_sort_admin_scripts');

function dropdown_category_sort_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_dropdown-category-sort') {
        return;
    }

    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('dropdown-category-sort-script', plugins_url('dropdown-category-sort.js', __FILE__), ['jquery-ui-sortable'], null, true);
    wp_enqueue_style('dropdown-category-sort-style', plugins_url('dropdown-category-sort.css', __FILE__));
}

// Apply sorting to frontend
add_filter('woocommerce_product_subcategories_args', 'apply_dropdown_sorting');

function apply_dropdown_sorting($args) {
    $current_term = get_queried_object();

    if ($current_term && isset($current_term->term_id)) {
        $saved_sorting = get_option("dropdown_category_sort_data_{$current_term->term_id}", []);

        if (!empty($saved_sorting)) {
            $args['orderby'] = 'include';
            $args['include'] = $saved_sorting;
        }
    }

    return $args;
}
?>
