<?php
/**
 * File: ai-seo-writer.php
 */

/**
 * Plugin Name:       AI SEO Writer
 * Plugin URI:        https://yourwebsite.com/ai-seo-writer
 * Description:       A full-suite, multi-model content platform with an integrated SEO optimizer.
 * Version:           3.4.0
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
    register_setting( 'ai_seo_writer_settings_group', 'aisw_theme_selector' );

    // Theme Settings Section
    add_settings_section('aisw_theme_settings_section', 'Theme Settings', null, 'ai_seo_writer_settings');
    add_settings_field('aisw_theme_selector_field', 'Select Theme', 'aisw_theme_selector_field_callback', 'ai_seo_writer_settings', 'aisw_theme_settings_section');

    // API Settings Section
    add_settings_section('aisw_api_settings_section', 'API Key Settings', 'aisw_api_settings_section_callback', 'ai_seo_writer_settings');
    add_settings_field('aisw_default_llm_field', 'Default AI Model', 'aisw_default_llm_field_callback', 'ai_seo_writer_settings', 'aisw_api_settings_section');
    add_settings_field('aisw_openai_api_key_field', 'OpenAI API Key', 'aisw_openai_api_key_field_callback', 'ai_seo_writer_settings', 'aisw_api_settings_section');
    add_settings_field('aisw_gemini_api_key_field', 'Google Gemini API Key', 'aisw_gemini_api_key_field_callback', 'ai_seo_writer_settings', 'aisw_api_settings_section');
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