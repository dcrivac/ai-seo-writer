<?php
/**
 * Plugin Name:       AI SEO Writer
 * Description:       A full-suite, multi-model content platform. Generate, refine, repurpose, and link articles with your choice of AI, all within a unique, dark-mode interface.
 * Version:           3.1.1
 * Author:            Your Name
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-seo-writer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// --- 1. Admin Menu & Settings ---
function aisw_add_admin_menu() {
    add_menu_page('AI SEO Writer', 'AI Writer', 'manage_options', 'ai_seo_writer', 'aisw_main_page_html', 'dashicons-edit-large', 6);
    add_submenu_page('ai_seo_writer', 'AI Writer Settings', 'Settings', 'manage_options', 'ai_seo_writer_settings', 'aisw_settings_page_html');
}
add_action( 'admin_menu', 'aisw_add_admin_menu' );

function aisw_settings_init() {
    register_setting( 'ai_seo_writer_settings_group', 'aisw_openai_api_key' );
    register_setting( 'ai_seo_writer_settings_group', 'aisw_gemini_api_key' );
    register_setting( 'ai_seo_writer_settings_group', 'aisw_default_llm' );
    add_settings_section('aisw_settings_section', 'API Key Settings', 'aisw_settings_section_callback', 'ai_seo_writer_settings');
    add_settings_field('aisw_default_llm_field', 'Default AI Model', 'aisw_default_llm_field_callback', 'ai_seo_writer_settings', 'aisw_settings_section');
    add_settings_field('aisw_openai_api_key_field', 'OpenAI API Key', 'aisw_openai_api_key_field_callback', 'ai_seo_writer_settings', 'aisw_settings_section');
    add_settings_field('aisw_gemini_api_key_field', 'Google Gemini API Key', 'aisw_gemini_api_key_field_callback', 'ai_seo_writer_settings', 'aisw_settings_section');
}
add_action( 'admin_init', 'aisw_settings_init' );

function aisw_settings_section_callback() { echo '<p>Select your default model and enter the required API keys.</p>'; }
function aisw_default_llm_field_callback() {
    $default_llm = get_option( 'aisw_default_llm', 'openai' );
    echo '<select name="aisw_default_llm">';
    echo '<option value="openai"' . selected($default_llm, 'openai', false) . '>OpenAI (GPT-3.5 Turbo)</option>';
    echo '<option value="gemini"' . selected($default_llm, 'gemini', false) . '>Google (Gemini 1.5 Flash)</option>';
    echo '</select>';
}
function aisw_openai_api_key_field_callback() {
    $api_key = get_option( 'aisw_openai_api_key' );
    echo '<input type="password" name="aisw_openai_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text" placeholder="sk-...">';
}
function aisw_gemini_api_key_field_callback() {
    $api_key = get_option( 'aisw_gemini_api_key' );
    echo '<input type="password" name="aisw_gemini_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text" placeholder="AIzaSy...">';
}

// --- 2. Main Page HTML Structure ---
function aisw_main_page_html() {
    ?>
    <div class="wrap aisw-wrap">
        <div class="aisw-header">
            <h1>AI SEO Writer</h1>
            <p>Your complete content strategy and automation suite.</p>
        </div>
        <nav class="nav-tab-wrapper">
            <a href="#single-post" class="nav-tab nav-tab-active">Single Post</a>
            <a href="#bulk-generate" class="nav-tab">Barracuda Bulk</a>
            <a href="#content-refinery" class="nav-tab">Content Refinery</a>
        </nav>
        <div class="tab-content">
            <div id="single-post" class="tab-pane active"><!-- Single Post Tab Content --></div>
            <div id="bulk-generate" class="tab-pane"><!-- Barracuda Bulk Tab Content --></div>
            <div id="content-refinery" class="tab-pane"><!-- Content Refinery Tab Content --></div>
        </div>
    </div>
    <?php
    // --- DEBUGGING OUTPUT ---
    echo '<!-- DEBUGGING INFO -->';
    echo '<!-- CSS URL: ' . plugin_dir_url( __FILE__ ) . 'assets/admin.css' . ' -->';
    echo '<!-- JS URL: ' . plugin_dir_url( __FILE__ ) . 'assets/admin.js' . ' -->';
    // --- END DEBUGGING ---
}

function aisw_settings_page_html() {
    ?>
    <div class="wrap aisw-wrap">
        <div class="aisw-header"><h1><?php echo esc_html( get_admin_page_title() ); ?></h1></div>
        <form action="options.php" method="post" class="aisw-settings-form">
            <?php settings_fields( 'ai_seo_writer_settings_group' ); do_settings_sections( 'ai_seo_writer_settings' ); submit_button( 'Save Settings' ); ?>
        </form>
    </div>
    <?php
}

// --- 3. Enqueue Scripts & Styles (ROBUST VERSION) ---
function aisw_enqueue_admin_scripts( $hook ) {
    if ( strpos($hook, 'ai_seo_writer') === false ) return;

    $css_path = plugin_dir_path( __FILE__ ) . 'assets/admin.css';
    $js_path = plugin_dir_path( __FILE__ ) . 'assets/admin.js';

    $css_url = plugin_dir_url( __FILE__ ) . 'assets/admin.css';
    $js_url = plugin_dir_url( __FILE__ ) . 'assets/admin.js';

    $css_version = file_exists($css_path) ? filemtime($css_path) : '1.0.0';
    $js_version = file_exists($js_path) ? filemtime($js_path) : '1.0.0';

    wp_enqueue_style( 'aisw-admin-css', $css_url, [], $css_version );
    wp_enqueue_script( 'aisw-admin-js', $js_url, [ 'jquery' ], $js_version, true );
    
    wp_localize_script( 'aisw-admin-js', 'aisw_ajax_obj', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'aisw_ajax_nonce' )
    ]);
}
add_action( 'admin_enqueue_scripts', 'aisw_enqueue_admin_scripts' );

// --- 4. AJAX Handlers (No changes needed here) ---
// ... (All the AJAX handler functions remain the same as the previous version) ...
2
