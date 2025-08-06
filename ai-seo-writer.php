<?php
/**
 * Plugin Name:       AI SEO Writer
 * Description:       A full-suite content platform. Generate, refine, repurpose, and link articles with a powerful set of AI tools.
 * Version:           3.1.0
 * Author:            Your Name
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-seo-writer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// --- 1. Admin Menu & Settings (Updated for Multi-LLM) ---
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
    echo '<option value="gemini"' . selected($default_llm, 'gemini', false) . '>Google (Gemini 2.5 Flash)</option>';
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

// --- 2. Main Page HTML Structure (Updated for Multi-LLM) ---
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
                            <div class="form-group"><label for="llm-selector">AI Model</label><select id="llm-selector"><option value="default">Default Model</option><option value="openai">OpenAI</option><option value="gemini">Gemini</option></select></div>
                        </div>
                        <button id="generate-article-btn" class="button button-primary">Generate</button>
                        <div id="aisw-live-progress" style="display:none;"></div>
                    </div>
                    <div id="aisw-step-2-tuneup" style="display:none;">
                        <h2 class="aisw-tuneup-title">Tijuana Tune-Up</h2>
                        <p class="aisw-tuneup-subtitle">Draft created. Now, let's perfect and enhance it.</p>
                        <div class="aisw-tuneup-grid">
                            <div class="tuneup-module"><h3>Meta Description</h3><p>Generate a concise summary for search engines.</p><button class="button tuneup-btn" data-action="generate_meta">Generate Meta</button><textarea id="meta-output" readonly></textarea></div>
                            <div class="tuneup-module"><h3>SEO Tags</h3><p>Generate 5-7 relevant tags for your post.</p><button class="button tuneup-btn" data-action="generate_tags">Suggest Tags</button><div id="tags-output" class="output-box"></div></div>
                            <div class="tuneup-module"><h3>Social Teaser</h3><p>Generate a punchy tweet to promote this article.</p><button class="button tuneup-btn" data-action="generate_social">Create Teaser</button><textarea id="social-output" readonly></textarea></div>
                            <div class="tuneup-module"><h3>SmartLink Internal Linking</h3><p>Scan for relevant internal linking opportunities.</p><button class="button tuneup-btn" data-action="find_links">Find Links</button><div id="links-output" class="output-box"></div></div>
                        </div>
                        <div class="aisw-finish-buttons"><a href="#" id="edit-post-link" class="button button-primary" target="_blank">Finish & Edit Post</a><button id="start-over-btn" class="button button-secondary">Start Over</button></div>
                    </div>
                </div>
            </div>
            <div id="bulk-generate" class="tab-pane">
                <div id="aisw-bulk-app">
                    <p>Enter up to 20 keywords or titles, one per line. The plugin will create a draft for each using your default AI model.</p>
                    <div class="form-group"><label for="bulk-keywords">Keywords / Titles</label><textarea id="bulk-keywords" rows="10" placeholder="The History of Chicano Park Murals&#10;Best Surf Spots in Rosarito&#10;A Guide to Tijuana's Craft Beer Scene"></textarea></div>
                    <button id="start-bulk-btn" class="button button-primary">Unleash Barracuda</button>
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