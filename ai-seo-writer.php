<?php
/**
 * Plugin Name:       AI SEO Writer
 * Description:       Generate and enhance SEO-friendly articles using a full suite of AI tools.
 * Version:           2.0.2
 * Author:            Your Name
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-seo-writer
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --- 1. Admin Menu & Settings Setup ---
// This section remains largely the same, setting up our menu pages.

function aisw_add_admin_menu() {
    add_menu_page('AI SEO Writer', 'AI Writer', 'manage_options', 'ai_seo_writer', 'aisw_main_page_html', 'dashicons-edit-large', 6);
    add_submenu_page('ai_seo_writer', 'AI Writer Settings', 'Settings', 'manage_options', 'ai_seo_writer_settings', 'aisw_settings_page_html');
}
add_action( 'admin_menu', 'aisw_add_admin_menu' );

function aisw_settings_init() {
    register_setting( 'ai_seo_writer_settings_group', 'aisw_openai_api_key' );
    add_settings_section('aisw_settings_section', 'OpenAI API Settings', 'aisw_settings_section_callback', 'ai_seo_writer_settings');
    add_settings_field('aisw_openai_api_key_field', 'OpenAI API Key', 'aisw_api_key_field_callback', 'ai_seo_writer_settings', 'aisw_settings_section');
}
add_action( 'admin_init', 'aisw_settings_init' );

function aisw_settings_section_callback() {
    echo '<p>Enter your OpenAI API key below. This is required to generate content.</p>';
}

function aisw_api_key_field_callback() {
    $api_key = get_option( 'aisw_openai_api_key' );
    echo '<input type="password" name="aisw_openai_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text">';
}


// --- 2. Main Page HTML Structure ---
// This has been significantly updated with the new controls and the hidden "Tune-Up" panel.

function aisw_main_page_html() {
    ?>
    <div class="wrap aisw-wrap">
        <div class="aisw-header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p>Define the new reality. Enter a topic and generate the narrative.</p>
        </div>
        
        <!-- Step 1: Generator Form -->
        <div id="aisw-generator-app">
            <div id="aisw-step-1-generator">
                <div class="form-group">
                    <label for="article-topic">Article Topic</label>
                    <input type="text" id="article-topic" class="large-text" placeholder="e.g., The resurgence of vinyl in the digital age">
                </div>

                <div class="aisw-controls-grid">
                    <div class="form-group">
                        <label for="article-tone">Tone</label>
                        <select id="article-tone">
                            <option value="Professional">Professional</option>
                            <option value="Casual" selected>Casual</option>
                            <option value="Witty">Witty</option>
                            <option value="Bold">Bold</option>
                            <option value="Technical">Technical</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="article-audience">Target Audience</label>
                        <input type="text" id="article-audience" placeholder="e.g., Music lovers, audiophiles">
                    </div>
                </div>

                <button id="generate-article-btn" class="button button-primary">Generate</button>
                <div id="aisw-live-progress" style="display:none;"></div>
            </div>

            <!-- Step 2: Tune-Up Panel (Initially Hidden) -->
            <div id="aisw-step-2-tuneup" style="display:none;">
                <h2 class="aisw-tuneup-title">Tijuana Tune-Up</h2>
                <p class="aisw-tuneup-subtitle">Article draft created. Now, let's perfect it.</p>
                
                <div class="aisw-tuneup-grid">
                    <!-- Meta Description -->
                    <div class="tuneup-module">
                        <h3>Meta Description</h3>
                        <p>Generate a concise, 160-character summary for search engines.</p>
                        <button class="button tuneup-btn" data-action="generate_meta">Generate Meta</button>
                        <textarea id="meta-output" readonly></textarea>
                    </div>
                    <!-- SEO Tags -->
                    <div class="tuneup-module">
                        <h3>SEO Tags</h3>
                        <p>Generate 5-7 relevant tags for your post.</p>
                        <button class="button tuneup-btn" data-action="generate_tags">Suggest Tags</button>
                        <div id="tags-output" class="output-box"></div>
                    </div>
                    <!-- Social Teaser -->
                    <div class="tuneup-module">
                        <h3>Social Teaser (X/Twitter)</h3>
                        <p>Generate a punchy tweet to promote this article.</p>
                        <button class="button tuneup-btn" data-action="generate_social">Create Teaser</button>
                        <textarea id="social-output" readonly></textarea>
                    </div>
                    <!-- Featured Image -->
                    <div class="tuneup-module">
                        <h3>Featured Image</h3>
                        <p>Find a high-quality, royalty-free image for your post.</p>
                        <button class="button tuneup-btn" data-action="find_image">Find Image</button>
                        <div id="image-output" class="output-box"></div>
                    </div>
                </div>
                <div class="aisw-finish-buttons">
                    <a href="#" id="edit-post-link" class="button button-primary" target="_blank">Finish & Edit Post</a>
                    <button id="start-over-btn" class="button button-secondary">Start Over</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Settings Page HTML (no changes needed here)
function aisw_settings_page_html() {
    ?>
    <div class="wrap aisw-wrap">
        <div class="aisw-header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        </div>
        <form action="options.php" method="post" class="aisw-settings-form">
            <?php
            settings_fields( 'ai_seo_writer_settings_group' );
            do_settings_sections( 'ai_seo_writer_settings' );
            submit_button( 'Save Key' );
            ?>
        </form>
    </div>
    <?php
}

// --- 3. Enqueue Scripts & Styles ---
// Version bumped to 2.0.2 to ensure cache busting.

function aisw_enqueue_admin_scripts( $hook ) {
    if ( 'toplevel_page_ai_seo_writer' !== $hook && 'ai-writer_page_ai_seo_writer_settings' !== $hook ) {
        return;
    }
    wp_enqueue_style( 'aisw-admin-css', plugin_dir_url( __FILE__ ) . 'assets/admin.css', [], '2.0.2' ); // Version bump
    wp_enqueue_script( 'aisw-admin-js', plugin_dir_url( __FILE__ ) . 'assets/admin.js', [ 'jquery' ], '2.0.0', true );
    wp_localize_script( 'aisw-admin-js', 'aisw_ajax_obj', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'aisw_ajax_nonce' )
    ]);
}
add_action( 'admin_enqueue_scripts', 'aisw_enqueue_admin_scripts' );

// --- 4. AJAX Handlers ---
// This is where all the new backend logic lives.

// Helper function to make API calls, reducing code repetition.
function aisw_call_openai_api( $prompt, $max_tokens = 1500 ) {
    $api_key = get_option( 'aisw_openai_api_key' );
    if ( empty( $api_key ) ) {
        return new WP_Error('api_key_missing', 'OpenAI API Key is not set.');
    }

    $api_url = 'https://api.openai.com/v1/chat/completions';
    $api_body = [
        'model'       => 'gpt-3.5-turbo',
        'messages'    => [['role' => 'user', 'content' => $prompt]],
        'temperature' => 0.7,
        'max_tokens'  => $max_tokens,
    ];

    $response = wp_remote_post( $api_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body'    => json_encode( $api_body ),
        'timeout' => 60,
    ]);

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $response_body = wp_remote_retrieve_body( $response );
    $response_data = json_decode( $response_body, true );

    if ( isset( $response_data['error'] ) ) {
        return new WP_Error('api_error', $response_data['error']['message']);
    }

    return $response_data['choices'][0]['message']['content'];
}

// Main article generation
function aisw_handle_generate_article() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );

    $topic = sanitize_text_field($_POST['topic']);
    $tone = sanitize_text_field($_POST['tone']);
    $audience = sanitize_text_field($_POST['audience']);

    $prompt = "You are an expert SEO content writer. Your task is to write a comprehensive, engaging, and SEO-optimized blog post on the topic of \"{$topic}\". The tone should be {$tone} and the target audience is {$audience}.\n\nThe article should have a compelling title, an introduction that hooks the reader, a well-structured body with H2 and H3 headings, and a concluding summary.\n\nPlease format the output as a valid JSON object with the following structure:\n{\n  \"title\": \"Your Generated Title\",\n  \"body\": \"Your generated article content here, using HTML tags for formatting.\"\n}";

    $ai_response_json = aisw_call_openai_api( $prompt );
    if ( is_wp_error( $ai_response_json ) ) {
        wp_send_json_error( [ 'message' => 'API Error: ' . $ai_response_json->get_error_message() ] );
    }

    $content = json_decode( $ai_response_json, true );
    if ( json_last_error() !== JSON_ERROR_NONE || !isset($content['title']) || !isset($content['body']) ) {
        wp_send_json_error( [ 'message' => 'Failed to parse AI response.' ] );
    }
    
    $post_id = wp_insert_post([
        'post_title'   => sanitize_text_field($content['title']),
        'post_content' => wp_kses_post($content['body']),
        'post_status'  => 'draft',
        'post_author'  => get_current_user_id(),
    ]);

    if ( $post_id ) {
        wp_send_json_success([ 
            'message'   => 'Successfully created draft post!',
            'post_id'   => $post_id,
            'edit_link' => get_edit_post_link( $post_id, 'raw' )
        ]);
    } else {
        wp_send_json_error( [ 'message' => 'Failed to create WordPress post.' ] );
    }
}
add_action( 'wp_ajax_aisw_generate_article', 'aisw_handle_generate_article' );

// Tune-Up: Generic handler for Meta, Tags, and Social
function aisw_handle_tuneup_actions() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );

    $post_id = intval($_POST['post_id']);
    $action = sanitize_text_field($_POST['tuneup_action']);
    $post = get_post($post_id);

    if (!$post) {
        wp_send_json_error(['message' => 'Post not found.']);
    }

    $article_content = "Title: " . $post->post_title . "\n\nContent: " . wp_strip_all_tags($post->post_content);
    $prompt = '';
    $max_tokens = 100;

    switch ($action) {
        case 'generate_meta':
            $prompt = "Based on the following article, write a compelling, SEO-optimized meta description. It must be under 160 characters.\n\n{$article_content}";
            break;
        case 'generate_tags':
            $prompt = "Based on the following article, suggest 5 to 7 relevant SEO tags or keywords, separated by commas.\n\n{$article_content}";
            break;
        case 'generate_social':
            $prompt = "Based on the following article, write a punchy and engaging teaser for X (formerly Twitter). Include 2-3 relevant hashtags.\n\n{$article_content}";
            break;
    }

    if (empty($prompt)) {
        wp_send_json_error(['message' => 'Invalid tune-up action.']);
    }

    $ai_response = aisw_call_openai_api($prompt, $max_tokens);
    if (is_wp_error($ai_response)) {
        wp_send_json_error(['message' => 'API Error: ' . $ai_response->get_error_message()]);
    }

    wp_send_json_success(['data' => $ai_response]);
}
add_action( 'wp_ajax_aisw_tuneup_action', 'aisw_handle_tuneup_actions' );
