<?php
/**
 * File: ai-seo-writer.php
 */

/**
 * Plugin Name:       AI SEO Writer
 * Plugin URI:        https://yourwebsite.com/ai-seo-writer
 * Description:       A full-suite, multi-model content platform with an integrated SEO optimizer.
 * Version:       // AJAX: Prune expired mobile API tokens
function aisw_ajax_prune_expired_tokens() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( ['message' => 'Unauthorized'] );
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $pruned_count = aisw_perform_token_pruning();
    if ($pruned_count > 0) {
        aisw_log_token_pruning($pruned_count, 'manual');
    }
    wp_send_json_success( ['pruned_count' => $pruned_count] );
}
 * Author:            Your Name
 * Author URI:        https://yourwebsite.com
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
    // Register settings
    register_setting( 'ai_seo_writer_settings_group', 'aisw_openai_api_key' );
    register_setting( 'ai_seo_writer_settings_group', 'aisw_gemini_api_key' );
    register_setting( 'ai_seo_writer_settings_group', 'aisw_default_llm' );
    // store multiple hashed mobile tokens as an associative array
    register_setting( 'ai_seo_writer_settings_group', 'aisw_mobile_api_tokens' );
    register_setting( 'ai_seo_writer_settings_group', 'aisw_theme_selector' );

    // Theme Settings Section
    add_settings_section('aisw_theme_settings_section', 'Theme Settings', null, 'ai_seo_writer_settings');
    add_settings_field('aisw_theme_selector_field', 'Select Theme', 'aisw_theme_selector_field_callback', 'ai_seo_writer_settings', 'aisw_theme_settings_section');

    // API Settings Section
    add_settings_section('aisw_api_settings_section', 'API Key Settings', 'aisw_api_settings_section_callback', 'ai_seo_writer_settings');
    add_settings_field('aisw_default_llm_field', 'Default AI Model', 'aisw_default_llm_field_callback', 'ai_seo_writer_settings', 'aisw_api_settings_section');
    add_settings_field('aisw_openai_api_key_field', 'OpenAI API Key', 'aisw_openai_api_key_field_callback', 'ai_seo_writer_settings', 'aisw_api_settings_section');
    add_settings_field('aisw_gemini_api_key_field', 'Google Gemini API Key', 'aisw_gemini_api_key_field_callback', 'ai_seo_writer_settings', 'aisw_api_settings_section');
    add_settings_field('aisw_mobile_api_key_field', 'Mobile API Key', 'aisw_mobile_api_key_field_callback', 'ai_seo_writer_settings', 'aisw_api_settings_section');
    add_settings_field('aisw_audit_log_field', 'Audit Log', 'aisw_audit_log_field_callback', 'ai_seo_writer_settings', 'aisw_api_settings_section');
}
add_action( 'admin_init', 'aisw_settings_init' );

function aisw_theme_selector_field_callback() {
    $current_theme = get_option( 'aisw_theme_selector', 'dark' );
    echo '<select name="aisw_theme_selector">';
    echo '<option value="dark"' . selected( $current_theme, 'dark', false ) . '>Tijuana After Midnight (Dark)</option>';
    echo '<option value="light"' . selected( $current_theme, 'light', false ) . '>Wabi-Sabi (Light)</option>';
    echo '</select>';
}

function aisw_api_settings_section_callback() { echo '<p>Select your default model and enter the required API keys.</p>'; }
function aisw_default_llm_field_callback() {
    $default_llm = get_option( 'aisw_default_llm', 'gpt-5' );
    echo '<select name="aisw_default_llm">';
    echo '<option value="gpt-5"' . selected($default_llm, 'gpt-5', false) . '>OpenAI (GPT-5)</option>';
    echo '<option value="gemini-2.5-flash"' . selected($default_llm, 'gemini-2.5-flash', false) . '>Google (Gemini 2.5 Flash)</option>';
    echo '<option value="gemini-2.5-pro"' . selected($default_llm, 'gemini-2.5-pro', false) . '>Google (Gemini 2.5 Pro)</option>';
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

function aisw_mobile_api_key_field_callback() {
    $tokens = get_option( 'aisw_mobile_api_tokens', array() );

    echo '<div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">';
    echo '<input type="text" id="aisw_mobile_api_key" value="" class="regular-text" readonly placeholder="(will display new key here)" />';
    echo '<select id="aisw_mobile_api_expiry" style="margin-left:8px;">
            <option value="0">Never expires</option>
            <option value="30">Expires in 30 days</option>
            <option value="90" selected>Expires in 90 days</option>
            <option value="180">Expires in 180 days</option>
        </select>';
    echo '<button type="button" id="aisw_generate_mobile_key" class="button">Generate New Token</button>';
    echo '<button type="button" id="aisw_refresh_tokens" class="button">Refresh</button>';
    echo '<button type="button" id="aisw_prune_tokens" class="button">Prune Expired</button>';
    echo '</div>';
    echo '<div id="aisw_mobile_api_scopes" style="margin: 10px 0;">
            <strong>Scopes:</strong>
            <label><input type="checkbox" class="aisw-scope" value="generate" checked> Generate</label>
            <label><input type="checkbox" class="aisw-scope" value="refine" checked> Refine</label>
            <label><input type="checkbox" class="aisw-scope" value="posts" checked> Posts</label>
          </div>';

    echo '<p class="description">Tokens are hashed and stored; the plaintext token is shown only once when generated. Revoke tokens to disable them.</p>';

    echo '<table class="widefat fixed" style="margin-top:12px;">';
        echo '<thead><tr><th>Token ID</th><th>Created</th><th>Created By</th><th>Expires</th><th>Scopes</th><th>Last Used</th><th>Last Used IP</th><th>Last Used By</th><th>Actions</th></tr></thead><tbody id="aisw_tokens_list">';
    if ( empty( $tokens ) ) {
            echo '<tr><td colspan="8">No mobile API tokens created.</td></tr>';
    } else {
        foreach ( $tokens as $tid => $meta ) {
            $created = isset($meta['created_at']) ? esc_html($meta['created_at']) : '';
            $expires = isset($meta['expires_at']) && $meta['expires_at'] ? esc_html($meta['expires_at']) : 'Never';
            $scopes = isset($meta['scopes']) && is_array($meta['scopes']) ? implode(', ', $meta['scopes']) : 'all';
            $by = isset($meta['created_by']) ? get_userdata($meta['created_by']) : null;
            $by_name = $by ? esc_html( $by->display_name ) : 'Unknown';
                $last_used = isset($meta['last_used_at']) ? esc_html($meta['last_used_at']) : '';
                $last_used_ip = isset($meta['last_used_ip']) ? esc_html($meta['last_used_ip']) : '';
                $last_used_by = isset($meta['last_used_by']) && $meta['last_used_by'] ? get_userdata($meta['last_used_by']) : null;
                $last_used_by_name = $last_used_by ? esc_html($last_used_by->display_name) : (isset($meta['last_used_by']) && $meta['last_used_by'] ? esc_html($meta['last_used_by']) : '');
                echo '<tr data-token-id="' . esc_attr($tid) . '"><td>' . esc_html( $tid ) . '</td><td>' . $created . '</td><td>' . $by_name . '</td><td>' . $expires . '</td><td>' . esc_html($scopes) . '</td><td>' . $last_used . '</td><td>' . $last_used_ip . '</td><td>' . $last_used_by_name . '</td><td><button type="button" class="button aisw-rotate-token">Rotate</button> <button type="button" class="button aisw-revoke-token">Revoke</button></td></tr>';
        }
    }
    echo '</tbody></table>';

    // Inline revoke confirmation modal (hidden) - uses CSS classes for styling/animation
    echo '<div id="aisw_revoke_modal" class="aisw-modal" aria-hidden="true">';
    echo '  <div class="aisw-modal__backdrop"></div>';
    echo '  <div class="aisw-modal__dialog" role="dialog" aria-modal="true">';
    echo '    <h2 class="aisw-modal__title">Revoke Token</h2>';
    echo '    <p id="aisw_revoke_modal_text" class="aisw-modal__message">Are you sure you want to revoke this token? This action cannot be undone.</p>';
    echo '    <div class="aisw-modal__details"></div>';
    echo '    <div class="aisw-modal__actions">';
    echo '      <button id="aisw_revoke_cancel" class="button">Cancel</button>';
    echo '      <button id="aisw_revoke_confirm" class="button button-primary">Revoke Token</button>';
    echo '    </div>';
    echo '  </div>';
    echo '</div>';
}

// AJAX: generate/reset mobile API key
function aisw_ajax_generate_mobile_key() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( ['message' => 'Unauthorized'] );
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );

    try {
        $token = bin2hex( random_bytes( 24 ) );
    } catch ( Exception $e ) {
        wp_send_json_error( ['message' => 'Failed to generate token.'] );
    }

    $expires_in_days = intval( $_POST['expires_in_days'] ?? 0 );
    $scopes = isset($_POST['scopes']) && is_array($_POST['scopes']) ? array_map('sanitize_text_field', $_POST['scopes']) : ['generate', 'refine', 'posts'];
    $tokens = get_option( 'aisw_mobile_api_tokens', array() );
    $tid = bin2hex( random_bytes(6) ); // short id
    $hash = password_hash( $token, PASSWORD_DEFAULT );
    $created_at = current_time( 'mysql' );
    $created_by = get_current_user_id();
    $entry = array(
        'hash' => $hash,
        'created_at' => $created_at,
        'created_by' => $created_by,
        'scopes' => $scopes,
    );
    if ( $expires_in_days > 0 ) {
        $expires_ts = current_time( 'timestamp' ) + ( $expires_in_days * DAY_IN_SECONDS );
        $entry['expires_at'] = date( 'Y-m-d H:i:s', $expires_ts );
    }
    $tokens[ $tid ] = $entry;
    update_option( 'aisw_mobile_api_tokens', $tokens );

    $user = get_userdata( $created_by );
    $created_by_name = $user ? $user->display_name : 'Unknown';

    // Log the event
    aisw_log_token_creation( array_merge(['token_id' => $tid], $entry) );

    // one-time reveal, include metadata for immediate UI append
    $response = array( 'token' => $token, 'token_id' => $tid, 'created_at' => $created_at, 'created_by' => $created_by_name, 'scopes' => $scopes );
    if ( isset( $entry['expires_at'] ) ) $response['expires_at'] = $entry['expires_at'];
    wp_send_json_success( $response );
}
add_action( 'wp_ajax_aisw_generate_mobile_key', 'aisw_ajax_generate_mobile_key' );

// Revoke mobile API token
function aisw_ajax_revoke_mobile_token() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( ['message' => 'Unauthorized'] );
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );

    $tid = sanitize_text_field( $_POST['token_id'] ?? '' );
    if ( empty( $tid ) ) wp_send_json_error( ['message' => 'Missing token id'] );

    $tokens = get_option( 'aisw_mobile_api_tokens', array() );
    if ( isset( $tokens[ $tid ] ) ) {
        unset( $tokens[ $tid ] );
        update_option( 'aisw_mobile_api_tokens', $tokens );
        aisw_log_token_revocation( $tid );
        wp_send_json_success( ['message' => 'Token revoked'] );
    }

    wp_send_json_error( ['message' => 'Token not found'] );
}
add_action( 'wp_ajax_aisw_revoke_mobile_token', 'aisw_ajax_revoke_mobile_token' );

// Return current mobile tokens metadata (for admin UI refresh)
function aisw_ajax_get_mobile_tokens() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( ['message' => 'Unauthorized'] );
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );

    $tokens = get_option( 'aisw_mobile_api_tokens', array() );
    $out = array();
    foreach ( $tokens as $tid => $meta ) {
        $created_by = isset($meta['created_by']) ? (get_userdata($meta['created_by']) ? get_userdata($meta['created_by'])->display_name : $meta['created_by']) : '';
        $out[] = array(
            'token_id' => $tid,
            'created_at' => $meta['created_at'] ?? '',
            'created_by' => $created_by,
            'expires_at' => $meta['expires_at'] ?? '',
            'scopes' => isset($meta['scopes']) && is_array($meta['scopes']) ? $meta['scopes'] : ['generate', 'refine', 'posts'],
            'last_used_at' => $meta['last_used_at'] ?? '',
            'last_used_ip' => $meta['last_used_ip'] ?? '',
            'last_used_by' => isset($meta['last_used_by']) && $meta['last_used_by'] ? (get_userdata($meta['last_used_by']) ? get_userdata($meta['last_used_by'])->display_name : $meta['last_used_by']) : '',
        );
    }

    wp_send_json_success( $out );
}
add_action( 'wp_ajax_aisw_get_mobile_tokens', 'aisw_ajax_get_mobile_tokens' );

// Rotate mobile API token (generate new token for given token id)
function aisw_ajax_rotate_mobile_token() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( ['message' => 'Unauthorized'] );
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );

    $tid = sanitize_text_field( $_POST['token_id'] ?? '' );
    if ( empty( $tid ) ) wp_send_json_error( ['message' => 'Missing token id'] );

    $tokens = get_option( 'aisw_mobile_api_tokens', array() );
    if ( ! isset( $tokens[ $tid ] ) ) wp_send_json_error( ['message' => 'Token not found'] );

    try {
        $new_token = bin2hex( random_bytes(24) );
    } catch ( Exception $e ) {
        wp_send_json_error( ['message' => 'Failed to generate token.'] );
    }

    $tokens[ $tid ]['hash'] = password_hash( $new_token, PASSWORD_DEFAULT );
    // update created/rotated metadata
    $tokens[ $tid ]['rotated_at'] = current_time( 'mysql' );
    $tokens[ $tid ]['rotated_by'] = get_current_user_id();
    update_option( 'aisw_mobile_api_tokens', $tokens );

    aisw_log_token_rotation( $tid );

    wp_send_json_success( [ 'token' => $new_token, 'token_id' => $tid, 'rotated_at' => $tokens[$tid]['rotated_at'] ] );
}
add_action( 'wp_ajax_aisw_rotate_mobile_token', 'aisw_ajax_rotate_mobile_token' );

// Core logic for pruning expired tokens.
function aisw_perform_token_pruning() {
    $tokens = get_option( 'aisw_mobile_api_tokens', array() );
    $original_count = count( $tokens );
    $unexpired_tokens = array();

    foreach ( $tokens as $tid => $meta ) {
        if ( isset( $meta['expires_at'] ) && $meta['expires_at'] ) {
            $expires_ts = strtotime( $meta['expires_at'] );
            if ( $expires_ts !== false && current_time( 'timestamp' ) > $expires_ts ) {
                continue; // Expired, so we skip it, effectively removing it.
            }
        }
        $unexpired_tokens[ $tid ] = $meta;
    }

    $pruned_count = $original_count - count( $unexpired_tokens );

    if ( $pruned_count > 0 ) {
        update_option( 'aisw_mobile_api_tokens', $unexpired_tokens );
        aisw_log_token_pruning( $pruned_count, 'cron' );
    }

    return $pruned_count;
}

// AJAX: Prune expired mobile API tokens
function aisw_ajax_prune_expired_tokens() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( ['message' => 'Unauthorized'] );
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );

    $pruned_count = aisw_perform_token_pruning();

    wp_send_json_success( ['pruned_count' => $pruned_count] );
}
add_action( 'wp_ajax_aisw_prune_expired_tokens', 'aisw_ajax_prune_expired_tokens' );

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
            <a href="#bulk-generate" class="nav-tab">Bulk Generator</a>
            <a href="#content-refinery" class="nav-tab">Content Refinery</a>
        </nav>

        <div class="tab-content">
            <div id="single-post" class="tab-pane active">
                <div id="aisw-generator-app">
                    <div id="aisw-step-1-generator">
                        <div class="form-group">
                            <label for="article-topic">Article Topic</label>
                            <input type="text" id="article-topic" class="large-text" placeholder="e.g., The resurgence of vinyl in the digital age">
                        </div>
                        <div class="aisw-controls-grid aisw-controls-grid-3">
                            <div class="form-group"><label for="article-tone">Tone</label><select id="article-tone"><option value="Professional">Professional</option><option value="Casual" selected>Casual</option><option value="Witty">Witty</option><option value="Bold">Bold</option></select></div>
                            <div class="form-group"><label for="article-audience">Target Audience</label><input type="text" id="article-audience" placeholder="e.g., Music lovers, audiophiles"></div>
                            <div class="form-group"><label for="llm-selector">AI Model</label><select id="llm-selector"><option value="default">Default Model</option><option value="gpt-5">OpenAI (GPT-5)</option><option value="gemini-2.5-flash">Google (Gemini 2.5 Flash)</option><option value="gemini-2.5-pro">Google (Gemini 2.5 Pro)</option></select></div>
                        </div>
                        <button id="generate-article-btn" class="button button-primary">Generate</button>
                        <div id="aisw-live-progress" style="display:none;"></div>
                    </div>
                    <div id="aisw-step-2-tuneup" style="display:none;">
                        <h2 class="aisw-tuneup-title">Optimization Suite</h2>
                        <p class="aisw-tuneup-subtitle">Draft created. Now, let's optimize and enhance it.</p>
                        <div class="aisw-tuneup-grid">
                            <div class="tuneup-module seo-module">
                                <h3>SEO Analysis & Optimization</h3>
                                <div class="form-group">
                                    <label for="focus-keyword">Focus Keyword</label>
                                    <input type="text" id="focus-keyword" placeholder="Enter primary keyword">
                                </div>
                                <ul id="seo-checklist" class="seo-checklist"></ul>
                            </div>
                            <div class="tuneup-module">
                                <h3>Social Teaser</h3>
                                <p>Generate a punchy tweet to promote this article.</p>
                                <button class="button tuneup-btn" data-action="generate_social">Create Teaser</button>
                                <textarea id="social-output" readonly></textarea>
                            </div>
                        </div>
                        <div class="aisw-finish-buttons"><a href="#" id="edit-post-link" class="button button-primary" target="_blank">Finish & Edit Post</a><button id="start-over-btn" class="button button-secondary">Start Over</button></div>
                    </div>
                </div>
            </div>
            <div id="bulk-generate" class="tab-pane">
                <div id="aisw-bulk-app">
                    <p>Enter up to 20 keywords or titles, one per line. The plugin will create a draft for each.</p>
                    <div class="aisw-controls-grid">
                        <div class="form-group">
                            <label for="bulk-length">Content Length</label>
                            <select id="bulk-length">
                                <option value="short">Short Post (~300 words)</option>
                                <option value="medium" selected>Medium Post (~800 words)</option>
                                <option value="long">Long Analysis (1500+ words)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bulk-format">Output Format</label>
                            <select id="bulk-format">
                                <option value="blog" selected>Standard Blog Post</option>
                                <option value="faq">FAQ Page</option>
                                <option value="product">Product Description</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label for="bulk-keywords">Keywords / Titles</label><textarea id="bulk-keywords" rows="10" placeholder="The History of Chicano Park Murals&#10;Best Surf Spots in Rosarito&#10;A Guide to Tijuana's Craft Beer Scene"></textarea></div>
                    <button id="start-bulk-btn" class="button button-primary">Start Bulk Generation</button>
                    <div id="bulk-progress-container" style="display:none;"><h3>Generation Queue</h3><ul id="bulk-queue-list"></ul></div>
                </div>
            </div>
            <div id="content-refinery" class="tab-pane">
                <div id="aisw-refinery-app">
                    <p>Select an existing post to repurpose its content into new formats using your default AI model.</p>
                    <div class="form-group"><label for="refinery-post-search">Search for a Post to Refine</label><input type="text" id="refinery-post-search" class="large-text" placeholder="Type to search posts..."><div id="refinery-search-results"></div></div>
                    <div id="refinery-tools" style="display:none;">
                        <h2 class="aisw-tuneup-title">Refining: <span id="refining-post-title"></span></h2>
                        <div class="aisw-tuneup-grid">
                            <div class="tuneup-module"><h3>Email Newsletter</h3><p>Generate a summary perfect for an email blast.</p><button class="button refine-btn" data-action="refine_newsletter">Generate Newsletter</button><textarea id="refine-newsletter-output" readonly></textarea></div>
                            <div class="tuneup-module"><h3>Social Media Thread</h3><p>Create a 5-part Twitter/X thread from the content.</p><button class="button refine-btn" data-action="refine_thread">Generate Thread</button><textarea id="refine-thread-output" readonly></textarea></div>
                            <div class="tuneup-module"><h3>Key Takeaways</h3><p>Extract a "TL;DR" bulleted list of key points.</p><button class="button refine-btn" data-action="refine_takeaways">Generate Takeaways</button><textarea id="refine-takeaways-output" readonly></textarea></div>
                            <div class="tuneup-module"><h3>Video Script Outline</h3><p>Create a timed outline for a short video.</p><button class="button refine-btn" data-action="refine_video">Generate Outline</button><textarea id="refine-video-output" readonly></textarea></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
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

// --- 3. Enqueue Scripts & Styles ---
function aisw_enqueue_admin_scripts( $hook ) {
    if ( strpos($hook, 'ai_seo_writer') === false ) return;

    $version = time(); // Aggressive cache-busting

    wp_enqueue_style('aisw-admin-css', plugin_dir_url( __FILE__ ) . 'assets/admin.css', [], $version);
    wp_enqueue_script('aisw-admin-js', plugin_dir_url( __FILE__ ) . 'assets/admin.js', ['jquery'], $version, true);
    
    wp_localize_script( 'aisw-admin-js', 'aisw_ajax_obj', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'aisw_ajax_nonce' )
    ]);
}
add_action( 'admin_enqueue_scripts', 'aisw_enqueue_admin_scripts' );

// --- Add Theme Class to Body ---
function aisw_add_admin_body_class($classes) {
    if (isset($_GET['page']) && strpos($_GET['page'], 'ai_seo_writer') !== false) {
        $theme = get_option('aisw_theme_selector', 'dark');
        $classes .= ' aisw-theme-' . esc_attr($theme);
    }
    return $classes;
}
add_filter('admin_body_class', 'aisw_add_admin_body_class');


// --- 4. AJAX Handlers ---

// Main API handler for both OpenAI and Gemini
function aisw_call_llm_api( $prompt, $model = 'default', $max_tokens = 1500 ) {
    if ($model === 'default') {
        $model = get_option('aisw_default_llm', 'gpt-5');
    }

    if ($model === 'gpt-5') {
        $api_key = get_option('aisw_openai_api_key');
        if (empty($api_key)) return new WP_Error('api_key_missing', 'OpenAI API Key is not set.');
        
        $api_url = 'https://api.openai.com/v1/chat/completions';
        $body = ['model' => 'gpt-5', 'messages' => [['role' => 'user', 'content' => $prompt]], 'temperature' => 0.7, 'max_tokens' => $max_tokens];
        $headers = ['Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json'];
        $response = wp_remote_post($api_url, ['headers' => $headers, 'body' => json_encode($body), 'timeout' => 120]);
        if (is_wp_error($response)) return $response;
        $response_data = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($response_data['error'])) return new WP_Error('api_error', $response_data['error']['message']);
        return $response_data['choices'][0]['message']['content'];

    } elseif ($model === 'gemini-2.5-flash' || $model === 'gemini-2.5-pro') {
        $api_key = get_option('aisw_gemini_api_key');
        if (empty($api_key)) return new WP_Error('api_key_missing', 'Gemini API Key is not set.');

        $gemini_model_id = ($model === 'gemini-2.5-pro') ? 'gemini-2.5-pro-preview-05-20' : 'gemini-2.5-flash-preview-05-20';
        $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $gemini_model_id . ':generateContent?key=' . $api_key;
        $body = ['contents' => [['parts' => [['text' => $prompt]]]]];
        $headers = ['Content-Type' => 'application/json'];
        $response = wp_remote_post($api_url, ['headers' => $headers, 'body' => json_encode($body), 'timeout' => 120]);
        if (is_wp_error($response)) return $response;
        $response_data = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($response_data['error'])) return new WP_Error('api_error', $response_data['error']['message']);
        
        $text_response = $response_data['candidates'][0]['content']['parts'][0]['text'];
        $cleaned_response = trim($text_response);
        if (substr($cleaned_response, 0, 7) === '```json') {
            $cleaned_response = substr($cleaned_response, 7);
            $cleaned_response = rtrim($cleaned_response, '`');
        }
        return $cleaned_response;
    }
    return new WP_Error('invalid_model', 'Invalid AI model selected.');
}

// Single Post Generation
function aisw_handle_generate_article() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $topic = sanitize_text_field($_POST['topic']);
    $tone = sanitize_text_field($_POST['tone']);
    $audience = sanitize_text_field($_POST['audience']);
    $model = sanitize_text_field($_POST['model']);
    $prompt = "Write a comprehensive, SEO-optimized blog post about \"{$topic}\". Tone: {$tone}. Audience: {$audience}. Format output as a valid JSON object: {\"title\": \"...\", \"body\": \"...\"}";
    $ai_response_json = aisw_call_llm_api( $prompt, $model );
    if ( is_wp_error( $ai_response_json ) ) wp_send_json_error( [ 'message' => 'API Error: ' . $ai_response_json->get_error_message() ] );
    $content = json_decode( $ai_response_json, true );
    if ( json_last_error() !== JSON_ERROR_NONE ) wp_send_json_error( [ 'message' => 'Failed to parse AI response. The model may have returned an invalid format.' ] );
    $post_id = wp_insert_post(['post_title' => sanitize_text_field($content['title']), 'post_content' => wp_kses_post($content['body']), 'post_status'  => 'draft', 'post_author'  => get_current_user_id()]);
    if ( $post_id ) wp_send_json_success(['post_id' => $post_id, 'edit_link' => get_edit_post_link( $post_id, 'raw' )]);
    else wp_send_json_error( [ 'message' => 'Failed to create WordPress post.' ] );
}
add_action( 'wp_ajax_aisw_generate_article', 'aisw_handle_generate_article' );

// Tune-Up & Refinery Actions
function aisw_handle_tuneup_refine_actions() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $post_id = intval($_POST['post_id']);
    $action = sanitize_text_field($_POST['action']);
    $post = get_post($post_id);
    if (!$post) wp_send_json_error(['message' => 'Post not found.']);
    $article_content = "Title: " . $post->post_title . "\n\nContent: " . wp_strip_all_tags($post->post_content);
    $prompts = [
        'generate_meta' => "Based on the article, write a compelling, SEO-optimized meta description under 160 characters.\n\n{$article_content}",
        'generate_tags' => "Based on the article, suggest 5-7 relevant SEO tags, comma-separated.\n\n{$article_content}",
        'generate_social' => "Based on the article, write a punchy X/Twitter teaser with 2-3 hashtags.\n\n{$article_content}",
        'refine_newsletter' => "Summarize the article into an engaging email newsletter format.\n\n{$article_content}",
        'refine_thread' => "Convert the article into a 5-part numbered Twitter/X thread.\n\n{$article_content}",
        'refine_takeaways' => "Extract the key takeaways from the article as a bulleted list.\n\n{$article_content}",
        'refine_video' => "Create a 2-minute video script outline based on the article.\n\n{$article_content}",
    ];
    $ai_response = aisw_call_llm_api($prompts[$action], 'default', 250);
    if (is_wp_error($ai_response)) wp_send_json_error(['message' => 'API Error: ' . $ai_response->get_error_message()]);
    wp_send_json_success(['data' => $ai_response]);
}
add_action( 'wp_ajax_aisw_tuneup_refine_action', 'aisw_handle_tuneup_refine_actions' );

// SmartLink Internal Linking
function aisw_handle_find_internal_links() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $post_id = intval($_POST['post_id']);
    $post = get_post($post_id);
    if (!$post) wp_send_json_error(['message' => 'Post not found.']);
    $published_posts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'exclude' => $post_id]);
    if (empty($published_posts)) wp_send_json_success(['data' => 'No other published posts found to link to.']);
    $titles_and_urls = array_map(function($p) { return ['title' => $p->post_title, 'url' => get_permalink($p->ID)]; }, $published_posts);
    $prompt = "Article content:\n\"" . wp_strip_all_tags($post->post_content) . "\"\n\nAvailable posts to link to (JSON format):\n" . json_encode($titles_and_urls) . "\n\nIdentify 3-5 phrases in the article that are highly relevant to link to one of the available posts. Respond ONLY with a valid JSON array like this: [{\"phrase_to_link\": \"some phrase\", \"link_to_url\": \"full_url_of_matching_post\"}]";
    $ai_response = aisw_call_llm_api($prompt, 'default', 500);
    if (is_wp_error($ai_response)) wp_send_json_error(['message' => 'API Error: ' . $ai_response->get_error_message()]);
    wp_send_json_success(['data' => json_decode($ai_response, true)]);
}
add_action( 'wp_ajax_aisw_find_internal_links', 'aisw_handle_find_internal_links' );

// Search for posts (for Refinery)
function aisw_handle_post_search() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $search_term = sanitize_text_field($_GET['term']);
    $posts = get_posts(['s' => $search_term, 'post_type' => 'post', 'post_status' => 'publish,draft', 'posts_per_page' => 10]);
    $results = array_map(function($p) { return ['id' => $p->ID, 'title' => $p->post_title]; }, $posts);
    wp_send_json_success($results);
}
add_action( 'wp_ajax_aisw_post_search', 'aisw_handle_post_search' );

// Bulk Generation
function aisw_handle_start_bulk() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $keywords = explode("\n", trim($_POST['keywords']));
    $keywords = array_slice(array_filter(array_map('sanitize_text_field', $keywords)), 0, 20);
    $length = sanitize_text_field($_POST['length']);
    $format = sanitize_text_field($_POST['format']);

    $bulk_data = [
        'queue' => $keywords,
        'length' => $length,
        'format' => $format
    ];

    set_transient('aisw_bulk_data', $bulk_data, HOUR_IN_SECONDS);
    wp_send_json_success(['message' => 'Queue started.']);
}
add_action( 'wp_ajax_aisw_start_bulk', 'aisw_handle_start_bulk' );

function aisw_handle_process_bulk_queue() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $bulk_data = get_transient('aisw_bulk_data');
    if (empty($bulk_data['queue'])) {
        delete_transient('aisw_bulk_data');
        wp_send_json_success(['status' => 'complete']);
        return;
    }

    $keyword = array_shift($bulk_data['queue']);
    $length = $bulk_data['length'];
    $format = $bulk_data['format'];
    set_transient('aisw_bulk_data', $bulk_data, HOUR_IN_SECONDS);

    $length_map = ['short' => '~300 words', 'medium' => '~800 words', 'long' => '1500+ words'];
    $format_map = ['blog' => 'a standard blog post', 'faq' => 'an FAQ page', 'product' => 'a product description'];

    $prompt = "Write a comprehensive, SEO-optimized piece of content about \"{$keyword}\". The desired length is {$length_map[$length]} and the format should be {$format_map[$format]}. Format output as a valid JSON object: {\"title\": \"...\", \"body\": \"...\"}";
    
    $ai_response_json = aisw_call_llm_api($prompt);
    
    if (!is_wp_error($ai_response_json)) {
        $content = json_decode($ai_response_json, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($content['title'])) {
            wp_insert_post(['post_title' => sanitize_text_field($content['title']), 'post_content' => wp_kses_post($content['body']), 'post_status' => 'draft', 'post_author' => get_current_user_id()]);
            wp_send_json_success(['status' => 'processing', 'keyword' => $keyword, 'remaining' => count($bulk_data['queue'])]);
            return;
        }
    }
    wp_send_json_error(['status' => 'error', 'keyword' => $keyword, 'remaining' => count($bulk_data['queue'])]);
}
add_action( 'wp_ajax_aisw_process_bulk_queue', 'aisw_handle_process_bulk_queue' );

// --- 5. REST API Endpoints (minimal) ---
/**
 * Permission check for REST endpoints.
 * Accepts either:
 * - a mobile API key sent in the X-AISW-API-KEY header that matches option 'aisw_mobile_api_key', OR
 * - an authenticated user with 'edit_posts' capability.
 */
function aisw_rest_permission_check( WP_REST_Request $request ) {
    // accept header in typical forms
    $header_token = $request->get_header('x-aisw-api-key') ?: $request->get_header('X-AISW-API-KEY');
    $tokens = get_option('aisw_mobile_api_tokens', array());
    $route = $request->get_route();

    if ( ! empty( $tokens ) && ! empty( $header_token ) ) {
        foreach ( $tokens as $tid => $meta ) {
            // Check scope
            $scopes = isset($meta['scopes']) && is_array($meta['scopes']) ? $meta['scopes'] : ['generate', 'refine', 'posts']; // Default to all if not set
            $endpoint = basename($route);
            if (!in_array($endpoint, $scopes)) {
                continue;
            }

            // ignore expired tokens
            if ( isset( $meta['expires_at'] ) && $meta['expires_at'] ) {
                $expires_ts = strtotime( $meta['expires_at'] );
                if ( $expires_ts !== false && current_time( 'timestamp' ) > $expires_ts ) {
                    continue;
                }
            }
            if ( isset( $meta['hash'] ) && function_exists('password_verify') && password_verify( (string) $header_token, (string) $meta['hash'] ) ) {
                // record last-used metadata for auditing
                $tokens = get_option('aisw_mobile_api_tokens', array());
                if ( isset( $tokens[ $tid ] ) ) {
                    $tokens[ $tid ]['last_used_at'] = current_time( 'mysql' );
                    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
                    $tokens[ $tid ]['last_used_ip'] = $ip;
                    $tokens[ $tid ]['last_used_by'] = get_current_user_id() ?: 0;
                    update_option( 'aisw_mobile_api_tokens', $tokens );
                }
                return true;
            }
        }
    }

    if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
        return true;
    }

    // Log failed attempt if token was provided but was invalid
    if ( ! empty( $header_token ) ) {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : 'unknown';
        aisw_log_event( "Failed auth attempt. Route: {$route}. IP: {$ip}. Reason: Invalid or insufficient scope token." );
    }

    return new WP_Error( 'rest_forbidden', 'You are not authorized to use this endpoint.', array( 'status' => 401 ) );
}

function aisw_rest_generate( WP_REST_Request $request ) {
    $topic = sanitize_text_field( $request->get_param('topic') );
    $tone = sanitize_text_field( $request->get_param('tone') );
    $audience = sanitize_text_field( $request->get_param('audience') );
    $model = sanitize_text_field( $request->get_param('model') ?: 'default' );

    if ( empty( $topic ) ) {
        return new WP_Error( 'missing_topic', 'The "topic" parameter is required.', array( 'status' => 400 ) );
    }

    $prompt = "Write a comprehensive, SEO-optimized blog post about \"{$topic}\". Tone: {$tone}. Audience: {$audience}. Format output as a valid JSON object: {\"title\": \"...\", \"body\": \"...\"}";

    $ai_response_json = aisw_call_llm_api( $prompt, $model );
    if ( is_wp_error( $ai_response_json ) ) {
        return new WP_Error( 'ai_error', $ai_response_json->get_error_message(), array( 'status' => 500 ) );
    }

    $content = json_decode( $ai_response_json, true );
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        return new WP_Error( 'ai_parse_error', 'Failed to parse AI response. The model may have returned an invalid format.', array( 'status' => 500 ) );
    }

    $post_id = wp_insert_post( array(
        'post_title'   => isset($content['title']) ? sanitize_text_field($content['title']) : wp_trim_words( wp_strip_all_tags( $content['body'] ?? '' ), 10 ),
        'post_content' => isset($content['body']) ? wp_kses_post($content['body']) : '',
        'post_status'  => 'draft',
        'post_author'  => get_current_user_id() ?: 0,
    ) );

    if ( ! $post_id ) {
        return new WP_Error( 'post_create_failed', 'Failed to create WordPress post.', array( 'status' => 500 ) );
    }

    return rest_ensure_response( array( 'post_id' => $post_id, 'edit_link' => get_edit_post_link( $post_id, 'raw' ) ) );
}

function aisw_rest_refine( WP_REST_Request $request ) {
    $post_id = intval( $request->get_param('post_id') );
    $action = sanitize_text_field( $request->get_param('action') );

    if ( ! $post_id || ! $action ) {
        return new WP_Error( 'missing_params', 'Both "post_id" and "action" are required.', array( 'status' => 400 ) );
    }

    $post = get_post( $post_id );
    if ( ! $post ) {
        return new WP_Error( 'post_not_found', 'Post not found.', array( 'status' => 404 ) );
    }

    $article_content = "Title: " . $post->post_title . "\n\nContent: " . wp_strip_all_tags( $post->post_content );
    $prompts = array(
        'generate_meta' => "Based on the article, write a compelling, SEO-optimized meta description under 160 characters.\n\n{$article_content}",
        'generate_tags' => "Based on the article, suggest 5-7 relevant SEO tags, comma-separated.\n\n{$article_content}",
        'generate_social' => "Based on the article, write a punchy X/Twitter teaser with 2-3 hashtags.\n\n{$article_content}",
        'refine_newsletter' => "Summarize the article into an engaging email newsletter format.\n\n{$article_content}",
        'refine_thread' => "Convert the article into a 5-part numbered Twitter/X thread.\n\n{$article_content}",
        'refine_takeaways' => "Extract the key takeaways from the article as a bulleted list.\n\n{$article_content}",
        'refine_video' => "Create a 2-minute video script outline based on the article.\n\n{$article_content}",
    );

    if ( ! isset( $prompts[ $action ] ) ) {
        return new WP_Error( 'invalid_action', 'Invalid refine action specified.', array( 'status' => 400 ) );
    }

    $ai_response = aisw_call_llm_api( $prompts[ $action ], 'default', 250 );
    if ( is_wp_error( $ai_response ) ) {
        return new WP_Error( 'ai_error', $ai_response->get_error_message(), array( 'status' => 500 ) );
    }

    return rest_ensure_response( array( 'data' => $ai_response ) );
}

function aisw_rest_posts_search( WP_REST_Request $request ) {
    $q = sanitize_text_field( $request->get_param('q') );
    $posts = get_posts( array( 's' => $q, 'post_type' => 'post', 'post_status' => 'publish,draft', 'posts_per_page' => 10 ) );
    $results = array_map( function( $p ) { return array( 'id' => $p->ID, 'title' => $p->post_title ); }, $posts );
    return rest_ensure_response( $results );
}

function aisw_register_rest_routes() {
    register_rest_route( 'aisw/v1', '/generate', array(
        'methods' => 'POST',
        'callback' => 'aisw_rest_generate',
        'permission_callback' => 'aisw_rest_permission_check',
    ) );

    register_rest_route( 'aisw/v1', '/refine', array(
        'methods' => 'POST',
        'callback' => 'aisw_rest_refine',
        'permission_callback' => 'aisw_rest_permission_check',
    ) );

    register_rest_route( 'aisw/v1', '/posts', array(
        'methods' => 'GET',
        'callback' => 'aisw_rest_posts_search',
        'permission_callback' => 'aisw_rest_permission_check',
    ) );
}
add_action( 'rest_api_init', 'aisw_register_rest_routes' );

// --- 6. Cron Job for Automated Pruning ---
if ( ! wp_next_scheduled( 'aisw_daily_prune_tokens_event' ) ) {
    wp_schedule_event( time(), 'daily', 'aisw_daily_prune_tokens_event' );
}
add_action( 'aisw_daily_prune_tokens_event', 'aisw_perform_token_pruning' );

// Add a deactivation hook to unschedule the event
function aisw_deactivate() {
    $timestamp = wp_next_scheduled( 'aisw_daily_prune_tokens_event' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'aisw_daily_prune_tokens_event' );
    }
}
register_deactivation_hook( __FILE__, 'aisw_deactivate' );

// --- 7. Audit Logging ---

/**
 * Writes a message to the plugin's log file.
 *
 * @param string $message The message to log.
 */
function aisw_log_event( $message ) {
    $upload_dir = wp_upload_dir();
    $log_file = $upload_dir['basedir'] . '/ai-seo-writer.log';
    $timestamp = current_time( 'Y-m-d H:i:s' );
    $log_entry = "[$timestamp] " . $message . "\n";
    file_put_contents( $log_file, $log_entry, FILE_APPEND );
}

// Log token creation
function aisw_log_token_creation( $data ) {
    $scopes = isset($data['scopes']) ? implode(', ', $data['scopes']) : 'all';
    $expires = isset($data['expires_at']) ? 'until ' . $data['expires_at'] : 'never';
    $user = wp_get_current_user();
    aisw_log_event( "Token '{$data['token_id']}' created by user '{$user->user_login}' (ID: {$user->ID}). Scopes: [{$scopes}]. Expires: {$expires}." );
}

// Log token revocation
function aisw_log_token_revocation( $tid ) {
    $user = wp_get_current_user();
    aisw_log_event( "Token '{$tid}' revoked by user '{$user->user_login}' (ID: {$user->ID})." );
}

// Log token rotation
function aisw_log_token_rotation( $tid ) {
    $user = wp_get_current_user();
    aisw_log_event( "Token '{$tid}' rotated by user '{$user->user_login}' (ID: {$user->ID})." );
}

// Log token pruning
function aisw_log_token_pruning( $count, $context = 'manual' ) {
    if ($context === 'cron') {
        aisw_log_event( "Automated cron job pruned {$count} expired tokens." );
    } else {
        $user = wp_get_current_user();
        aisw_log_event( "User '{$user->user_login}' (ID: {$user->ID}) manually pruned {$count} expired tokens." );
    }
}