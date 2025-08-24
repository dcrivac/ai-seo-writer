<?php
/**
 * Plugin Name:       AI SEO Writer
 * Description:       A full-suite content platform. Generate, refine, repurpose, and link articles with a powerful set of AI tools.
 * Version:           3.3.0
 * Author:            Your Name
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-seo-writer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// --- 1. Admin Menu & Settings (Updated for Multi-LLM) ---
function aisw_add_admin_menu() {
    add_menu_page( __( 'AI SEO Writer', 'ai-seo-writer' ), __( 'AI Writer', 'ai-seo-writer' ), 'manage_options', 'ai_seo_writer', 'aisw_main_page_html', 'dashicons-edit-large', 6 );
    add_submenu_page( 'ai_seo_writer', __( 'AI Writer Settings', 'ai-seo-writer' ), __( 'Settings', 'ai-seo-writer' ), 'manage_options', 'ai_seo_writer_settings', 'aisw_settings_page_html' );
    add_submenu_page( 'ai_seo_writer', __( 'Error Log', 'ai-seo-writer' ), __( 'Error Log', 'ai-seo-writer' ), 'manage_options', 'ai_seo_writer_errors', 'aisw_error_log_page_html' );
    add_submenu_page( 'ai_seo_writer', __( 'Analytics', 'ai-seo-writer' ), __( 'Analytics', 'ai-seo-writer' ), 'manage_options', 'ai_seo_writer_analytics', 'aisw_analytics_page_html' );
}
add_action( 'admin_menu', 'aisw_add_admin_menu' );

function aisw_settings_init() {
    register_setting( 'ai_seo_writer_settings_group', 'aisw_openai_api_key' );
    register_setting( 'ai_seo_writer_settings_group', 'aisw_gemini_api_key' );
    register_setting( 'ai_seo_writer_settings_group', 'aisw_default_llm' );
    register_setting( 'ai_seo_writer_settings_group', 'aisw_default_tone' );
    register_setting( 'ai_seo_writer_settings_group', 'aisw_default_audience' );
    register_setting( 'ai_seo_writer_settings_group', 'aisw_theme', [ 'default' => 'dark' ] );

    add_settings_section( 'aisw_settings_section', __( 'API Key Settings', 'ai-seo-writer' ), 'aisw_settings_section_callback', 'ai_seo_writer_settings' );
    
    add_settings_field( 'aisw_default_llm_field', __( 'Default AI Model', 'ai-seo-writer' ), 'aisw_default_llm_field_callback', 'ai_seo_writer_settings', 'aisw_settings_section' );
    add_settings_field( 'aisw_openai_api_key_field', __( 'OpenAI API Key', 'ai-seo-writer' ), 'aisw_openai_api_key_field_callback', 'ai_seo_writer_settings', 'aisw_settings_section' );
    add_settings_field( 'aisw_gemini_api_key_field', __( 'Google Gemini API Key', 'ai-seo-writer' ), 'aisw_gemini_api_key_field_callback', 'ai_seo_writer_settings', 'aisw_settings_section' );
    add_settings_field( 'aisw_default_tone_field', __( 'Default Tone', 'ai-seo-writer' ), 'aisw_default_tone_field_callback', 'ai_seo_writer_settings', 'aisw_settings_section' );
    add_settings_field( 'aisw_default_audience_field', __( 'Default Audience', 'ai-seo-writer' ), 'aisw_default_audience_field_callback', 'ai_seo_writer_settings', 'aisw_settings_section' );
    add_settings_field( 'aisw_theme_field', __( 'Theme', 'ai-seo-writer' ), 'aisw_theme_field_callback', 'ai_seo_writer_settings', 'aisw_settings_section' );
}
add_action( 'admin_init', 'aisw_settings_init' );

function aisw_settings_section_callback() { 
    echo '<p>' . __( 'Select your default model and enter the required API keys.', 'ai-seo-writer' ) . '</p>'; 
    echo '<button id="aisw-test-api-btn" class="button button-secondary">' . __( 'Test API Connection', 'ai-seo-writer' ) . '</button>';
    echo '<div id="aisw-test-api-result" style="margin-top: 10px;"></div>';
    echo '<button id="aisw-restart-tour" class="button button-secondary">' . __( 'Restart Tour', 'ai-seo-writer' ) . '</button>';
}

function aisw_default_llm_field_callback() {
    $default_llm = get_option( 'aisw_default_llm', 'openai' );
    echo '<select name="aisw_default_llm">';
    echo '<option value="openai"' . selected( $default_llm, 'openai', false ) . '>' . __( 'OpenAI (GPT-3.5 Turbo)', 'ai-seo-writer' ) . '</option>';
    echo '<option value="gemini"' . selected( $default_llm, 'gemini', false ) . '>' . __( 'Google (Gemini 2.5 Flash)', 'ai-seo-writer' ) . '</option>';
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

function aisw_default_tone_field_callback() {
    $tone = get_option( 'aisw_default_tone', 'Casual' );
    echo '<select name="aisw_default_tone">';
    echo '<option value="Professional"' . selected( $tone, 'Professional', false ) . '>' . __( 'Professional', 'ai-seo-writer' ) . '</option>';
    echo '<option value="Casual"' . selected( $tone, 'Casual', false ) . '>' . __( 'Casual', 'ai-seo-writer' ) . '</option>';
    echo '<option value="Witty"' . selected( $tone, 'Witty', false ) . '>' . __( 'Witty', 'ai-seo-writer' ) . '</option>';
    echo '<option value="Bold"' . selected( $tone, 'Bold', false ) . '>' . __( 'Bold', 'ai-seo-writer' ) . '</option>';
    echo '</select>';
}

function aisw_default_audience_field_callback() {
    $audience = get_option( 'aisw_default_audience' );
    echo '<input type="text" name="aisw_default_audience" value="' . esc_attr( $audience ) . '" class="regular-text" placeholder="' . esc_attr__( 'e.g., Music lovers, audiophiles', 'ai-seo-writer' ) . '">';
}

function aisw_theme_field_callback() {
    $theme = get_option( 'aisw_theme', 'dark' );
    echo '<select name="aisw_theme">';
    echo '<option value="dark"' . selected( $theme, 'dark', false ) . '>' . __( 'Dark (Tijuana After Midnight)', 'ai-seo-writer' ) . '</option>';
    echo '<option value="light"' . selected( $theme, 'light', false ) . '>' . __( 'Light', 'ai-seo-writer' ) . '</option>';
    echo '</select>';
}

// --- Error Log Page ---
function aisw_error_log_page_html() {
    $errors = get_option( 'aisw_error_log', [] );
    ?>
    <div class="wrap aisw-wrap <?php echo esc_attr( get_option( 'aisw_theme', 'dark' ) === 'light' ? 'aisw-light-mode' : '' ); ?>">
        <div class="aisw-header"><h1><?php _e( 'Error Log', 'ai-seo-writer' ); ?></h1></div>
        <?php if ( empty( $errors ) ) : ?>
            <p><?php _e( 'No errors recorded.', 'ai-seo-writer' ); ?></p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th><?php _e( 'Date', 'ai-seo-writer' ); ?></th><th><?php _e( 'Error Message', 'ai-seo-writer' ); ?></th><th><?php _e( 'Action', 'ai-seo-writer' ); ?></th></tr></thead>
                <tbody>
                    <?php foreach ( $errors as $error ) : ?>
                        <tr><td><?php echo esc_html( $error['date'] ); ?></td><td><?php echo esc_html( $error['message'] ); ?></td><td><?php echo esc_html( $error['action'] ); ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

// --- Analytics Page ---
function aisw_analytics_page_html() {
    $stats = get_option( 'aisw_usage_stats', [ 'posts_generated' => 0, 'api_calls' => 0 ] );
    ?>
    <div class="wrap aisw-wrap <?php echo esc_attr( get_option( 'aisw_theme', 'dark' ) === 'light' ? 'aisw-light-mode' : '' ); ?>">
        <div class="aisw-header"><h1><?php _e( 'Analytics', 'ai-seo-writer' ); ?></h1></div>
        <p><?php _e( 'Posts Generated:', 'ai-seo-writer' ); ?> <?php echo esc_html( $stats['posts_generated'] ); ?></p>
        <p><?php _e( 'API Calls Made:', 'ai-seo-writer' ); ?> <?php echo esc_html( $stats['api_calls'] ); ?></p>
    </div>
    <?php
}

// --- 2. Main Page HTML Structure (Updated for Multi-LLM) ---
function aisw_main_page_html() {
    $default_llm = get_option( 'aisw_default_llm', 'openai' );
    $default_tone = get_option( 'aisw_default_tone', 'Casual' );
    $default_audience = get_option( 'aisw_default_audience', '' );
    $theme_class = get_option( 'aisw_theme', 'dark' ) === 'light' ? 'aisw-light-mode' : '';
    ?>
    <div class="wrap aisw-wrap <?php echo esc_attr( $theme_class ); ?>">
        <div class="aisw-header">
            <h1><span class="dashicons dashicons-edit-large"></span> <?php _e( 'AI SEO Writer', 'ai-seo-writer' ); ?></h1>
            <p><?php _e( 'Your complete content strategy and automation suite.', 'ai-seo-writer' ); ?></p>
        </div>

        <nav class="nav-tab-wrapper">
            <a href="#single-post" class="nav-tab nav-tab-active"><?php _e( 'Single Post', 'ai-seo-writer' ); ?></a>
            <a href="#bulk-generate" class="nav-tab"><?php _e( 'Barracuda Bulk', 'ai-seo-writer' ); ?></a>
            <a href="#content-refinery" class="nav-tab"><?php _e( 'Content Refinery', 'ai-seo-writer' ); ?></a>
        </nav>

        <div class="tab-content">
            <div id="single-post" class="tab-pane active">
                <div id="aisw-generator-app">
                    <div id="aisw-step-1-generator">
                        <div class="form-group">
                            <label for="article-topic"><?php _e( 'Article Topic', 'ai-seo-writer' ); ?> <span class="aisw-help">(<?php _e( 'e.g., "Best Hiking Trails in California" – Be specific for better results', 'ai-seo-writer' ); ?>)</span></label>
                            <input type="text" id="article-topic" class="large-text" placeholder="<?php esc_attr_e( 'e.g., The resurgence of vinyl in the digital age', 'ai-seo-writer' ); ?>" aria-label="<?php esc_attr_e( 'Enter the topic for your article', 'ai-seo-writer' ); ?>">
                        </div>
                        <div class="aisw-controls-grid aisw-controls-grid-3">
                            <div class="form-group"><label for="article-tone"><?php _e( 'Tone', 'ai-seo-writer' ); ?></label><select id="article-tone" data-tooltip="<?php esc_attr_e( 'Choose a tone to match your brand voice, e.g., Professional for formal content.', 'ai-seo-writer' ); ?>"><option value="Professional" <?php selected( $default_tone, 'Professional' ); ?>><?php _e( 'Professional', 'ai-seo-writer' ); ?></option><option value="Casual" <?php selected( $default_tone, 'Casual' ); ?>><?php _e( 'Casual', 'ai-seo-writer' ); ?></option><option value="Witty" <?php selected( $default_tone, 'Witty' ); ?>><?php _e( 'Witty', 'ai-seo-writer' ); ?></option><option value="Bold" <?php selected( $default_tone, 'Bold' ); ?>><?php _e( 'Bold', 'ai-seo-writer' ); ?></option></select></div>
                            <div class="form-group"><label for="article-audience"><?php _e( 'Target Audience', 'ai-seo-writer' ); ?> <span class="aisw-help">(<?php _e( 'e.g., Music lovers, audiophiles', 'ai-seo-writer' ); ?>)</span></label><input type="text" id="article-audience" placeholder="<?php esc_attr_e( 'e.g., Music lovers, audiophiles', 'ai-seo-writer' ); ?>" value="<?php echo esc_attr( $default_audience ); ?>" aria-label="<?php esc_attr_e( 'Enter the target audience', 'ai-seo-writer' ); ?>"></div>
                            <div class="form-group"><label for="llm-selector"><?php _e( 'AI Model', 'ai-seo-writer' ); ?></label><select id="llm-selector"><option value="default"><?php _e( 'Default Model', 'ai-seo-writer' ); ?> (<?php echo esc_html( ucfirst( $default_llm ) ); ?>)</option><option value="openai"><?php _e( 'OpenAI', 'ai-seo-writer' ); ?></option><option value="gemini"><?php _e( 'Gemini', 'ai-seo-writer' ); ?></option></select></div>
                        </div>
                        <button id="generate-article-btn" class="button button-primary"><span class="dashicons dashicons-welcome-write-blog"></span> <?php _e( 'Generate', 'ai-seo-writer' ); ?></button>
                        <div id="aisw-live-progress" style="display:none;" aria-live="polite"><?php _e( 'Processing...', 'ai-seo-writer' ); ?></div>
                    </div>
                    <div id="aisw-step-2-tuneup" style="display:none;">
                        <h2 class="aisw-tuneup-title"><?php _e( 'Tijuana Tune-Up', 'ai-seo-writer' ); ?></h2>
                        <p class="aisw-tuneup-subtitle"><?php _e( 'Draft created. Now, let\'s perfect and enhance it.', 'ai-seo-writer' ); ?></p>
                        <div id="aisw-preview-pane" style="display:none;">
                            <h3><?php _e( 'Post Preview', 'ai-seo-writer' ); ?></h3>
                            <div id="aisw-preview-content"></div>
                        </div>
                        <div class="aisw-tuneup-grid">
                            <div class="tuneup-module"><h3><?php _e( 'Meta Description', 'ai-seo-writer' ); ?></h3><p><?php _e( 'Generate a concise summary for search engines.', 'ai-seo-writer' ); ?></p><button class="button tuneup-btn" data-action="generate_meta"><span class="dashicons dashicons-format-quote"></span> <?php _e( 'Generate Meta', 'ai-seo-writer' ); ?></button><textarea id="meta-output" readonly aria-label="<?php esc_attr_e( 'Generated meta description', 'ai-seo-writer' ); ?>"></textarea><button class="copy-btn"><?php _e( 'Copy', 'ai-seo-writer' ); ?></button></div>
                            <div class="tuneup-module"><h3><?php _e( 'SEO Tags', 'ai-seo-writer' ); ?></h3><p><?php _e( 'Generate 5-7 relevant tags for your post.', 'ai-seo-writer' ); ?></p><button class="button tuneup-btn" data-action="generate_tags"><span class="dashicons dashicons-tag"></span> <?php _e( 'Suggest Tags', 'ai-seo-writer' ); ?></button><div id="tags-output" class="output-box" aria-label="<?php esc_attr_e( 'Suggested SEO tags', 'ai-seo-writer' ); ?>"></div><button class="copy-btn"><?php _e( 'Copy', 'ai-seo-writer' ); ?></button></div>
                            <div class="tuneup-module"><h3><?php _e( 'Social Teaser', 'ai-seo-writer' ); ?></h3><p><?php _e( 'Generate a punchy tweet to promote this article.', 'ai-seo-writer' ); ?></p><button class="button tuneup-btn" data-action="generate_social"><span class="dashicons dashicons-share"></span> <?php _e( 'Create Teaser', 'ai-seo-writer' ); ?></button><textarea id="social-output" readonly aria-label="<?php esc_attr_e( 'Generated social teaser', 'ai-seo-writer' ); ?>"></textarea><button class="copy-btn"><?php _e( 'Copy', 'ai-seo-writer' ); ?></button></div>
                            <div class="tuneup-module"><h3><?php _e( 'SmartLink Internal Linking', 'ai-seo-writer' ); ?></h3><p><?php _e( 'Scan for relevant internal linking opportunities.', 'ai-seo-writer' ); ?></p><button class="button tuneup-btn" data-action="find_links"><span class="dashicons dashicons-admin-links"></span> <?php _e( 'Find Links', 'ai-seo-writer' ); ?></button><div id="links-output" class="output-box" aria-label="<?php esc_attr_e( 'Suggested internal links', 'ai-seo-writer' ); ?>"></div><button class="copy-btn"><?php _e( 'Copy', 'ai-seo-writer' ); ?></button></div>
                        </div>
                        <div class="aisw-finish-buttons"><a href="#" id="edit-post-link" class="button button-primary" target="_blank"><?php _e( 'Finish & Edit Post', 'ai-seo-writer' ); ?></a><button id="undo-generate-btn" class="button button-secondary"><?php _e( 'Undo Generation', 'ai-seo-writer' ); ?></button><button id="start-over-btn" class="button button-secondary"><?php _e( 'Start Over', 'ai-seo-writer' ); ?></button></div>
                    </div>
                </div>
            </div>
            <div id="bulk-generate" class="tab-pane">
                <div id="aisw-bulk-app">
                    <p><?php _e( 'Enter up to 10 keywords or titles, one per line. The plugin will create a draft for each using your default AI model.', 'ai-seo-writer' ); ?></p>
                    <div class="form-group"><label for="bulk-keywords"><?php _e( 'Keywords / Titles', 'ai-seo-writer' ); ?> <span class="aisw-help">(<?php _e( 'Limit to 10 for best performance', 'ai-seo-writer' ); ?>)</span></label><textarea id="bulk-keywords" rows="10" placeholder="<?php esc_attr_e( 'The History of Chicano Park Murals&#10;Best Surf Spots in Rosarito&#10;A Guide to Tijuana\'s Craft Beer Scene', 'ai-seo-writer' ); ?>" aria-label="<?php esc_attr_e( 'Enter keywords or titles, one per line', 'ai-seo-writer' ); ?>"></textarea></div>
                    <button id="start-bulk-btn" class="button button-primary"><span class="dashicons dashicons-image-rotate"></span> <?php _e( 'Unleash Barracuda', 'ai-seo-writer' ); ?></button>
                    <button id="pause-bulk-btn" class="button button-secondary" style="display:none;"><?php _e( 'Pause', 'ai-seo-writer' ); ?></button>
                    <div id="bulk-progress-container" style="display:none;"><h3><?php _e( 'Generation Queue', 'ai-seo-writer' ); ?></h3><div id="bulk-progress-bar" class="aisw-progress-bar"><div class="aisw-progress-fill"></div></div><ul id="bulk-queue-list"></ul></div>
                </div>
            </div>
            <div id="content-refinery" class="tab-pane">
                <div id="aisw-refinery-app">
                    <p><?php _e( 'Select an existing post to repurpose its content into new formats using your default AI model.', 'ai-seo-writer' ); ?></p>
                    <div class="form-group"><label for="refinery-post-search"><?php _e( 'Search for a Post to Refine', 'ai-seo-writer' ); ?> <span class="aisw-help">(<?php _e( 'Type to search, or select from recent posts', 'ai-seo-writer' ); ?>)</span></label><input type="text" id="refinery-post-search" class="large-text" placeholder="<?php esc_attr_e( 'Type to search posts...', 'ai-seo-writer' ); ?>" aria-label="<?php esc_attr_e( 'Search for posts to refine', 'ai-seo-writer' ); ?>"><div id="refinery-search-results"></div></div>
                    <div id="refinery-tools" style="display:none;">
                        <h2 class="aisw-tuneup-title"><?php _e( 'Refining:', 'ai-seo-writer' ); ?> <span id="refining-post-title"></span></h2>
                        <div class="aisw-tuneup-grid">
                            <div class="tuneup-module"><h3><?php _e( 'Email Newsletter', 'ai-seo-writer' ); ?></h3><p><?php _e( 'Generate a summary perfect for an email blast.', 'ai-seo-writer' ); ?></p><button class="button refine-btn" data-action="refine_newsletter"><span class="dashicons dashicons-email"></span> <?php _e( 'Generate Newsletter', 'ai-seo-writer' ); ?></button><textarea id="refine-newsletter-output" readonly aria-label="<?php esc_attr_e( 'Generated newsletter', 'ai-seo-writer' ); ?>"></textarea><button class="copy-btn"><?php _e( 'Copy', 'ai-seo-writer' ); ?></button></div>
                            <div class="tuneup-module"><h3><?php _e( 'Social Media Thread', 'ai-seo-writer' ); ?></h3><p><?php _e( 'Create a 5-part Twitter/X thread from the content.', 'ai-seo-writer' ); ?></p><button class="button refine-btn" data-action="refine_thread"><span class="dashicons dashicons-format-chat"></span> <?php _e( 'Generate Thread', 'ai-seo-writer' ); ?></button><textarea id="refine-thread-output" readonly aria-label="<?php esc_attr_e( 'Generated thread', 'ai-seo-writer' ); ?>"></textarea><button class="copy-btn"><?php _e( 'Copy', 'ai-seo-writer' ); ?></button></div>
                            <div class="tuneup-module"><h3><?php _e( 'Key Takeaways', 'ai-seo-writer' ); ?></h3><p><?php _e( 'Extract a "TL;DR" bulleted list of key points.', 'ai-seo-writer' ); ?></p><button class="button refine-btn" data-action="refine_takeaways"><span class="dashicons dashicons-list-view"></span> <?php _e( 'Generate Takeaways', 'ai-seo-writer' ); ?></button><textarea id="refine-takeaways-output" readonly aria-label="<?php esc_attr_e( 'Generated takeaways', 'ai-seo-writer' ); ?>"></textarea><button class="copy-btn"><?php _e( 'Copy', 'ai-seo-writer' ); ?></button></div>
                            <div class="tuneup-module"><h3><?php _e( 'Video Script Outline', 'ai-seo-writer' ); ?></h3><p><?php _e( 'Create a timed outline for a short video.', 'ai-seo-writer' ); ?></p><button class="button refine-btn" data-action="refine_video"><span class="dashicons dashicons-video-alt3"></span> <?php _e( 'Generate Outline', 'ai-seo-writer' ); ?></button><textarea id="refine-video-output" readonly aria-label="<?php esc_attr_e( 'Generated video outline', 'ai-seo-writer' ); ?>"></textarea><button class="copy-btn"><?php _e( 'Copy', 'ai-seo-writer' ); ?></button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="aisw-footer"><a href="#" id="aisw-feedback-link"><?php _e( 'Send Feedback', 'ai-seo-writer' ); ?></a></div>
    </div>
    <?php
}

function aisw_settings_page_html() {
    $theme_class = get_option( 'aisw_theme', 'dark' ) === 'light' ? 'aisw-light-mode' : '';
    ?>
    <div class="wrap aisw-wrap <?php echo esc_attr( $theme_class ); ?>">
        <div class="aisw-header"><h1><?php echo esc_html( get_admin_page_title() ); ?></h1></div>
        <form action="options.php" method="post" class="aisw-settings-form">
            <?php settings_fields( 'ai_seo_writer_settings_group' ); do_settings_sections( 'ai_seo_writer_settings' ); submit_button( __( 'Save Settings', 'ai-seo-writer' ) ); ?>
        </form>
    </div>
    <?php
}

// --- 3. Enqueue Scripts & Styles ---
function aisw_enqueue_admin_scripts( $hook ) {
    // Only load on our plugin pages
    if ( strpos( $hook, 'ai_seo_writer' ) === false ) return;
    
    // Debug: Check if files exist
    $css_path = plugin_dir_path( __FILE__ ) . 'assets/admin.css';
    $js_path = plugin_dir_path( __FILE__ ) . 'assets/admin.js';
    
    error_log('AISW: CSS file exists: ' . (file_exists($css_path) ? 'YES' : 'NO') . ' at ' . $css_path);
    error_log('AISW: JS file exists: ' . (file_exists($js_path) ? 'YES' : 'NO') . ' at ' . $js_path);
    
    // Enqueue jQuery and jQuery UI first
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-autocomplete' );
    
    // Enqueue external dependencies
    wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.min.css', [], '1.14.1' );
    wp_enqueue_script( 'jquery-ui-js', 'https://code.jquery.com/ui/1.14.1/jquery-ui.min.js', [ 'jquery' ], '1.14.1', true );
    
    // Only enqueue Intro.js on the main plugin page (not settings)
    if ( $hook === 'toplevel_page_ai_seo_writer' ) {
        wp_enqueue_script( 'intro-js', 'https://cdn.jsdelivr.net/npm/intro.js@8.3.2/min/intro.min.js', [ 'jquery' ], '8.3.2', true );
        wp_enqueue_style( 'intro-js-css', 'https://cdn.jsdelivr.net/npm/intro.js@8.3.2/min/introjs.min.css', [], '8.3.2' );
    }
    
    // Enqueue our plugin assets with proper URLs
    $css_url = plugin_dir_url( __FILE__ ) . 'assets/admin.css';
    $js_url = plugin_dir_url( __FILE__ ) . 'assets/admin.js';
    
    wp_enqueue_style( 'aisw-admin-css', $css_url, [], '3.3.1' ); // Bumped version to force refresh
    
    $dependencies = [ 'jquery', 'jquery-ui-autocomplete' ];
    if ( $hook === 'toplevel_page_ai_seo_writer' ) {
        $dependencies[] = 'intro-js';
    }
    
    wp_enqueue_script( 'aisw-admin-js', $js_url, $dependencies, '3.3.1', true ); // Bumped version
    
    // Localize script with AJAX data
    wp_localize_script( 'aisw-admin-js', 'aisw_ajax_obj', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'aisw_ajax_nonce' ),
        'default_llm' => get_option( 'aisw_default_llm', 'openai' ),
        'hook' => $hook, // Debug info
        'i18n' => [
            'topic_required' => __( 'Topic is required!', 'ai-seo-writer' ),
            'keywords_limit' => __( 'Limit to 10 keywords for best performance.', 'ai-seo-writer' ),
            'api_test_success' => __( 'API connection successful!', 'ai-seo-writer' ),
            'api_test_fail' => __( 'API connection failed: ', 'ai-seo-writer' ),
            'copied' => __( 'Copied!', 'ai-seo-writer' ),
        ]
    ] );
    
    // Debug: Output file URLs to browser console
    wp_add_inline_script( 'aisw-admin-js', 
        'console.log("AISW CSS URL: ' . $css_url . '");' .
        'console.log("AISW JS URL: ' . $js_url . '");' .
        'console.log("AISW Hook: ' . $hook . '");'
    );
}
// --- 4. AJAX Handlers (Re-engineered for Multi-LLM) ---

// Main API handler for both OpenAI and Gemini
function aisw_call_llm_api( $prompt, $model = 'default', $max_tokens = 1500 ) {
    if ( $model === 'default' ) {
        $model = get_option( 'aisw_default_llm', 'openai' );
    }

    if ( $model === 'openai' ) {
        $api_key = get_option( 'aisw_openai_api_key' );
        if ( empty( $api_key ) ) return new WP_Error( 'api_key_missing', __( 'OpenAI API Key is not set. Please check your settings.', 'ai-seo-writer' ) );
        
        $api_url = 'https://api.openai.com/v1/chat/completions';
        $body = [ 'model' => 'gpt-3.5-turbo', 'messages' => [ [ 'role' => 'user', 'content' => $prompt ] ], 'temperature' => 0.7, 'max_tokens' => $max_tokens ];
        $headers = [ 'Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json' ];

    } elseif ( $model === 'gemini' ) {
        $api_key = get_option( 'aisw_gemini_api_key' );
        if ( empty( $api_key ) ) return new WP_Error( 'api_key_missing', __( 'Gemini API Key is not set. Please check your settings.', 'ai-seo-writer' ) );

        $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $api_key;
        $body = [ 'contents' => [ [ 'parts' => [ [ 'text' => $prompt ] ] ] ] ];
        $headers = [ 'Content-Type' => 'application/json' ];
    } else {
        return new WP_Error( 'invalid_model', __( 'Invalid AI model selected.', 'ai-seo-writer' ) );
    }

    $response = wp_remote_post( $api_url, [ 'headers' => $headers, 'body' => json_encode( $body ), 'timeout' => 120 ] );

    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        if ( strpos( $error_message, '401' ) !== false ) {
            $error_message = __( 'Invalid API key. Please verify your key in Settings.', 'ai-seo-writer' );
        } elseif ( strpos( $error_message, '429' ) !== false ) {
            $error_message = __( 'Rate limit exceeded. Please wait and try again.', 'ai-seo-writer' );
        }
        $response = new WP_Error( 'api_error', $error_message );
    }
    
    $response_data = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( $model === 'openai' ) {
        if ( isset( $response_data['error'] ) ) $response = new WP_Error( 'api_error', $response_data['error']['message'] );
        $content = $response_data['choices'][0]['message']['content'];  // Corrected for OpenAI response structure
    } elseif ( $model === 'gemini' ) {
        if ( isset( $response_data['error'] ) ) $response = new WP_Error( 'api_error', $response_data['error']['message'] );
        $content = $response_data['candidates'][0]['content']['parts'][0]['text'];
    }

    if ( is_wp_error( $response ) ) {
        $errors = get_option( 'aisw_error_log', [] );
        $errors[] = [ 'date' => current_time( 'mysql' ), 'message' => $response->get_error_message(), 'action' => 'API Call' ];
        update_option( 'aisw_error_log', array_slice( $errors, -50 ) ); // Keep last 50 errors
        return $response;
    } else {
        $stats = get_option( 'aisw_usage_stats', [ 'posts_generated' => 0, 'api_calls' => 0 ] );
        $stats['api_calls']++;
        update_option( 'aisw_usage_stats', $stats );
        return $content;
    }
}

// Test API Connection
function aisw_handle_test_api() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $model = get_option( 'aisw_default_llm', 'openai' );
    $prompt = __( 'Test prompt: Hello, world!', 'ai-seo-writer' );
    $result = aisw_call_llm_api( $prompt, $model, 10 );
    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    } else {
        wp_send_json_success( [ 'message' => __( 'Success! Response: ', 'ai-seo-writer' ) . esc_html( substr( $result, 0, 50 ) ) ] );
    }
}
add_action( 'wp_ajax_aisw_test_api', 'aisw_handle_test_api' );

// Single Post Generation
function aisw_handle_generate_article() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $topic = sanitize_text_field( $_POST['topic'] );
    $tone = sanitize_text_field( $_POST['tone'] );
    $audience = sanitize_text_field( $_POST['audience'] );
    $model = sanitize_text_field( $_POST['model'] );

    $prompt = "Write a comprehensive, SEO-optimized blog post about \"{$topic}\". Tone: {$tone}. Audience: {$audience}. Format output as a valid JSON object: {\"title\": \"...\", \"body\": \"...\"}";
    
    $ai_response_json = aisw_call_llm_api( $prompt, $model );
    if ( is_wp_error( $ai_response_json ) ) wp_send_json_error( [ 'message' => $ai_response_json->get_error_message() ] );
    
    $content = json_decode( $ai_response_json, true );
    if ( json_last_error() !== JSON_ERROR_NONE ) wp_send_json_error( [ 'message' => __( 'Failed to parse AI response. The model may have returned an invalid format.', 'ai-seo-writer' ) ] );
    
    $post_id = wp_insert_post( [ 'post_title' => sanitize_text_field( $content['title'] ), 'post_content' => wp_kses_post( $content['body'] ), 'post_status'  => 'draft', 'post_author'  => get_current_user_id() ] );
    
    if ( $post_id ) {
        $stats = get_option( 'aisw_usage_stats', [ 'posts_generated' => 0, 'api_calls' => 0 ] );
        $stats['posts_generated']++;
        update_option( 'aisw_usage_stats', $stats );
        wp_send_json_success( [ 'post_id' => $post_id, 'edit_link' => get_edit_post_link( $post_id, 'raw' ), 'title' => $content['title'] ] );
    } else wp_send_json_error( [ 'message' => __( 'Failed to create WordPress post.', 'ai-seo-writer' ) ] );
}
add_action( 'wp_ajax_aisw_generate_article', 'aisw_handle_generate_article' );

// Undo Generate
function aisw_handle_undo_generate() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $post_id = intval( $_POST['post_id'] );
    if ( wp_delete_post( $post_id, true ) ) {
        wp_send_json_success( [ 'message' => __( 'Post deleted.', 'ai-seo-writer' ) ] );
    } else {
        wp_send_json_error( [ 'message' => __( 'Failed to delete post.', 'ai-seo-writer' ) ] );
    }
}
add_action( 'wp_ajax_aisw_undo_generate', 'aisw_handle_undo_generate' );

// Get Post Content for Preview
function aisw_handle_get_post_content() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $post_id = intval( $_POST['post_id'] );
    $post = get_post( $post_id );
    if ( $post ) {
        wp_send_json_success( [ 'content' => wp_kses_post( $post->post_content ) ] );
    } else {
        wp_send_json_error( [ 'message' => __( 'Post not found.', 'ai-seo-writer' ) ] );
    }
}
add_action( 'wp_ajax_aisw_get_post_content', 'aisw_handle_get_post_content' );

// Tune-Up & Refinery Actions
function aisw_handle_tuneup_refine_actions() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $post_id = intval( $_POST['post_id'] );
    $action = sanitize_text_field( $_POST['action'] );
    $post = get_post( $post_id );
    if ( ! $post ) wp_send_json_error( [ 'message' => __( 'Post not found.', 'ai-seo-writer' ) ] );
    $article_content = "Title: " . $post->post_title . "\n\nContent: " . wp_strip_all_tags( $post->post_content );
    $prompts = [
        'generate_meta' => "Based on the article, write a compelling, SEO-optimized meta description under 160 characters.\n\n{$article_content}",
        'generate_tags' => "Based on the article, suggest 5-7 relevant SEO tags, comma-separated.\n\n{$article_content}",
        'generate_social' => "Based on the article, write a punchy X/Twitter teaser with 2-3 hashtags.\n\n{$article_content}",
        'refine_newsletter' => "Summarize the article into an engaging email newsletter format.\n\n{$article_content}",
        'refine_thread' => "Convert the article into a 5-part numbered Twitter/X thread.\n\n{$article_content}",
        'refine_takeaways' => "Extract the key takeaways from the article as a bulleted list.\n\n{$article_content}",
        'refine_video' => "Create a 2-minute video script outline based on the article.\n\n{$article_content}",
    ];
    $ai_response = aisw_call_llm_api( $prompts[$action], 'default', 250 );
    if ( is_wp_error( $ai_response ) ) wp_send_json_error( [ 'message' => $ai_response->get_error_message() ] );
    wp_send_json_success( [ 'data' => $ai_response ] );
}
add_action( 'wp_ajax_aisw_tuneup_refine_action', 'aisw_handle_tuneup_refine_actions' );

// SmartLink Internal Linking
function aisw_handle_find_internal_links() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $post_id = intval( $_POST['post_id'] );
    $post = get_post( $post_id );
    if ( ! $post ) wp_send_json_error( [ 'message' => __( 'Post not found.', 'ai-seo-writer' ) ] );
    
    $published_posts = get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'exclude' => $post_id ] );
    if ( empty( $published_posts ) ) wp_send_json_success( [ 'data' => __( 'No other published posts found to link to.', 'ai-seo-writer' ) ] );

    $titles_and_urls = array_map( function( $p ) { return [ 'title' => $p->post_title, 'url' => get_permalink( $p->ID ) ]; }, $published_posts );
    $prompt = "Article content:\n\"" . wp_strip_all_tags( $post->post_content ) . "\"\n\nAvailable posts to link to (JSON format):\n" . json_encode( $titles_and_urls ) . "\n\nIdentify 3-5 phrases in the article that are highly relevant to link to one of the available posts. Respond ONLY with a valid JSON array like this: [{\"phrase_to_link\": \"some phrase\", \"link_to_url\": \"full_url_of_matching_post\"}]";
    
    $ai_response = aisw_call_llm_api( $prompt, 'default', 500 );
    if ( is_wp_error( $ai_response ) ) wp_send_json_error( [ 'message' => $ai_response->get_error_message() ] );

    wp_send_json_success( [ 'data' => json_decode( $ai_response, true ) ] );
}
add_action( 'wp_ajax_aisw_find_internal_links', 'aisw_handle_find_internal_links' );

// Search for posts (for Refinery)
function aisw_handle_post_search() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $search_term = sanitize_text_field( $_GET['term'] );
    $posts = get_posts( [ 's' => $search_term, 'post_type' => 'post', 'post_status' => 'publish,draft', 'posts_per_page' => 10 ] );
    $results = array_map( function( $p ) { return [ 'id' => $p->ID, 'title' => $p->post_title ]; }, $posts );
    wp_send_json_success( $results );
}
add_action( 'wp_ajax_aisw_post_search', 'aisw_handle_post_search' );

// Bulk Generation
function aisw_handle_start_bulk() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $keywords = explode( "\n", trim( $_POST['keywords'] ) );
    $keywords = array_slice( array_filter( array_map( 'sanitize_text_field', $keywords ) ), 0, 10 );
    set_transient( 'aisw_bulk_queue', $keywords, HOUR_IN_SECONDS );
    wp_send_json_success( [ 'message' => __( 'Queue started.', 'ai-seo-writer' ) ] );
}
add_action( 'wp_ajax_aisw_start_bulk', 'aisw_handle_start_bulk' );

function aisw_handle_process_bulk_queue() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $queue = get_transient( 'aisw_bulk_queue' );
    if ( empty( $queue ) ) {
        wp_send_json_success( [ 'status' => 'complete' ] );
        return;
    }
    $keyword = array_shift( $queue );
    set_transient( 'aisw_bulk_queue', $queue, HOUR_IN_SECONDS );
    
    $prompt = "Write a comprehensive, SEO-optimized blog post about \"{$keyword}\". Format output as a valid JSON object: {\"title\": \"...\", \"body\": \"...\"}";
    $ai_response_json = aisw_call_llm_api( $prompt );
    
    if ( ! is_wp_error( $ai_response_json ) ) {
        $content = json_decode( $ai_response_json, true );
        if ( json_last_error() === JSON_ERROR_NONE && isset( $content['title'] ) ) {
            wp_insert_post( [ 'post_title' => sanitize_text_field( $content['title'] ), 'post_content' => wp_kses_post( $content['body'] ), 'post_status' => 'draft', 'post_author' => get_current_user_id() ] );
            $stats = get_option( 'aisw_usage_stats', [ 'posts_generated' => 0, 'api_calls' => 0 ] );
            $stats['posts_generated']++;
            update_option( 'aisw_usage_stats', $stats );
            wp_send_json_success( [ 'status' => 'processing', 'keyword' => $keyword, 'remaining' => count( $queue ) ] );
            return;
        }
    }
    wp_send_json_error( [ 'status' => 'error', 'keyword' => $keyword, 'remaining' => count( $queue ) ] );
}
add_action( 'wp_ajax_aisw_process_bulk_queue', 'aisw_handle_process_bulk_queue' );
?>

function aisw_debug_file_structure() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    
    $plugin_dir = plugin_dir_path( __FILE__ );
    $plugin_url = plugin_dir_url( __FILE__ );
    
    echo '<div class="notice notice-info"><p><strong>AISW Debug Info:</strong></p>';
    echo '<p>Plugin Directory: ' . esc_html( $plugin_dir ) . '</p>';
    echo '<p>Plugin URL: ' . esc_html( $plugin_url ) . '</p>';
    
    // Check if assets directory exists
    $assets_dir = $plugin_dir . 'assets/';
    echo '<p>Assets Directory Exists: ' . ( is_dir( $assets_dir ) ? 'YES' : 'NO' ) . '</p>';
    
    // Check individual files
    $css_file = $assets_dir . 'admin.css';
    $js_file = $assets_dir . 'admin.js';
    
    echo '<p>CSS File Exists: ' . ( file_exists( $css_file ) ? 'YES' : 'NO' ) . ' (' . esc_html( $css_file ) . ')</p>';
    echo '<p>JS File Exists: ' . ( file_exists( $js_file ) ? 'YES' : 'NO' ) . ' (' . esc_html( $js_file ) . ')</p>';
    
    // List all files in plugin directory
    if ( is_dir( $plugin_dir ) ) {
        $files = scandir( $plugin_dir );
        echo '<p>Files in plugin directory: ' . esc_html( implode( ', ', array_diff( $files, [ '.', '..' ] ) ) ) . '</p>';
    }
    
    // List all files in assets directory (if it exists)
    if ( is_dir( $assets_dir ) ) {
        $asset_files = scandir( $assets_dir );
        echo '<p>Files in assets directory: ' . esc_html( implode( ', ', array_diff( $asset_files, [ '.', '..' ] ) ) ) . '</p>';
    }
    
    echo '</div>';
}

// Hook it to admin notices (temporary)
add_action( 'admin_notices', 'aisw_debug_file_structure' );