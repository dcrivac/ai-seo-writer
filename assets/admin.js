function aisw_enqueue_admin_scripts( $hook ) {
    if ( strpos($hook, 'ai_seo_writer') === false ) return;
    wp_enqueue_style( 'aisw-admin-css', plugin_dir_url( __FILE__ ) . 'assets/admin.css', [], '3.1.0' );
    wp_enqueue_script( 'aisw-admin-js', plugin_dir_url( __FILE__ ) . 'assets/admin.js', [ 'jquery' ], '3.1.0', true );
    wp_localize_script( 'aisw-admin-js', 'aisw_ajax_obj', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'aisw_ajax_nonce' )
    ]);
}
add_action( 'admin_enqueue_scripts', 'aisw_enqueue_admin_scripts' );

// --- 4. AJAX Handlers (Re-engineered for Multi-LLM) ---

// Main API handler for both OpenAI and Gemini
function aisw_call_llm_api( $prompt, $model = 'default', $max_tokens = 1500 ) {
    if ($model === 'default') {
        $model = get_option('aisw_default_llm', 'openai');
    }

    if ($model === 'openai') {
        $api_key = get_option('aisw_openai_api_key');
        if (empty($api_key)) return new WP_Error('api_key_missing', 'OpenAI API Key is not set.');
        
        $api_url = 'https://api.openai.com/v1/chat/completions';
        $body = ['model' => 'gpt-3.5-turbo', 'messages' => [['role' => 'user', 'content' => $prompt]], 'temperature' => 0.7, 'max_tokens' => $max_tokens];
        $headers = ['Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json'];

    } elseif ($model === 'gemini') {
        $api_key = get_option('aisw_gemini_api_key');
        if (empty($api_key)) return new WP_Error('api_key_missing', 'Gemini API Key is not set.');

        $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $api_key;
        $body = ['contents' => [['parts' => [['text' => $prompt]]]]];
        $headers = ['Content-Type' => 'application/json'];
    } else {
        return new WP_Error('invalid_model', 'Invalid AI model selected.');
    }

    $response = wp_remote_post($api_url, ['headers' => $headers, 'body' => json_encode($body), 'timeout' => 120]);

    if (is_wp_error($response)) return $response;
    
    $response_data = json_decode(wp_remote_retrieve_body($response), true);

    if ($model === 'openai') {
        if (isset($response_data['error'])) return new WP_Error('api_error', $response_data['error']['message']);
        return $response_data['candidates'][0]['content']['parts'][0]['text'];
    } elseif ($model === 'gemini') {
        if (isset($response_data['error'])) return new WP_Error('api_error', $response_data['error']['message']);
        return $response_data['candidates'][0]['content']['parts'][0]['text'];
    }
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
    set_transient('aisw_bulk_queue', $keywords, HOUR_IN_SECONDS);
    wp_send_json_success(['message' => 'Queue started.']);
}
add_action( 'wp_ajax_aisw_start_bulk', 'aisw_handle_start_bulk' );

function aisw_handle_process_bulk_queue() {
    check_ajax_referer( 'aisw_ajax_nonce', 'nonce' );
    $queue = get_transient('aisw_bulk_queue');
    if (empty($queue)) {
        wp_send_json_success(['status' => 'complete']);
        return;
    }
    $keyword = array_shift($queue);
    set_transient('aisw_bulk_queue', $queue, HOUR_IN_SECONDS);
    
    $prompt = "Write a comprehensive, SEO-optimized blog post about \"{$keyword}\". Format output as a valid JSON object: {\"title\": \"...\", \"body\": \"...\"}";
    $ai_response_json = aisw_call_llm_api($prompt);
    
    if (!is_wp_error($ai_response_json)) {
        $content = json_decode($ai_response_json, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($content['title'])) {
            wp_insert_post(['post_title' => sanitize_text_field($content['title']), 'post_content' => wp_kses_post($content['body']), 'post_status' => 'draft', 'post_author' => get_current_user_id()]);
            wp_send_json_success(['status' => 'processing', 'keyword' => $keyword, 'remaining' => count($queue)]);
            return;
        }
    }
    wp_send_json_error(['status' => 'error', 'keyword' => $keyword, 'remaining' => count($queue)]);
}
add_action( 'wp_ajax_aisw_process_bulk_queue', 'aisw_handle_process_bulk_queue' );
```javascript
// File: assets/admin.js

jQuery(document).ready(function($) {
    'use strict';

    // --- Tab Navigation ---
    $('.nav-tab-wrapper .nav-tab').on('click', function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $('.tab-pane').removeClass('active');
        $(this).addClass('nav-tab-active');
        $($(this).attr('href')).addClass('active');
    });

    // --- SINGLE POST GENERATOR ---
    const step1 = $('#aisw-step-1-generator'), step2 = $('#aisw-step-2-tuneup');
    const generateBtn = $('#generate-article-btn'), topicInput = $('#article-topic');
    const progressDiv = $('#aisw-live-progress'), editPostLink = $('#edit-post-link');
    const llmSelector = $('#llm-selector');
    let currentPostId = null;

    const progressSteps = ['Analyzing topic...', 'Crafting headline...', 'Building outline...', 'Writing introduction...', 'Fleshing out points...', 'Finalizing...'];
    let progressInterval;

    function startProgress() {
        let currentStep = 0;
        progressDiv.text(progressSteps[currentStep]).fadeIn();
        progressInterval = setInterval(() => {
            currentStep = (currentStep + 1) % progressSteps.length;
            progressDiv.text(progressSteps[currentStep]);
        }, 1500);
    }
    function stopProgress() { clearInterval(progressInterval); progressDiv.fadeOut(); }

    generateBtn.on('click', function() {
        if (!topicInput.val()) { alert('Please enter a topic.'); return; }
        generateBtn.prop('disabled', true);
        startProgress();
        $.post(aisw_ajax_obj.ajax_url, {
            action: 'aisw_generate_article', nonce: aisw_ajax_obj.nonce,
            topic: topicInput.val(), tone: $('#article-tone').val(), audience: $('#article-audience').val(),
            model: llmSelector.val()
        }).done(response => {
            if (response.success) {
                currentPostId = response.data.post_id;
                editPostLink.attr('href', response.data.edit_link);
                step1.fadeOut(() => step2.fadeIn());
            } else { alert('Error: ' + response.data.message); generateBtn.prop('disabled', false); }
        }).fail(() => { alert('An unexpected error occurred.'); generateBtn.prop('disabled', false); })
        .always(() => stopProgress());
    });

    $('.tuneup-btn').on('click', function() {
        const btn = $(this), action = btn.data('action');
        const outputArea = btn.siblings('textarea, div');
        btn.prop('disabled', true).text('Working...');

        if (action === 'find_links') {
            $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_find_internal_links', nonce: aisw_ajax_obj.nonce, post_id: currentPostId })
            .done(response => {
                outputArea.empty();
                if (response.success && Array.isArray(response.data)) {
                    if(response.data.length === 0) outputArea.append('<p>No relevant linking opportunities found.</p>');
                    response.data.forEach(link => {
                        outputArea.append(`<div class="link-suggestion">"${link.phrase_to_link}" <br>→ <a href="${link.link_to_url}" target="_blank">${new URL(link.link_to_url).pathname.replace(/\//g, ' ')}</a></div>`);
                    });
                } else { outputArea.text(response.data.message || 'Error finding links.'); }
            }).always(() => btn.prop('disabled', false).text('Find Links'));
            return;
        }

        $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_tuneup_refine_action', nonce: aisw_ajax_obj.nonce, post_id: currentPostId, action: action })
        .done(response => {
            if (response.success) outputArea.val(response.data).text(response.data);
            else alert('Error: ' + response.data.message);
        }).always(() => btn.prop('disabled', false).text(btn.text().replace('Working...', 'Suggest Tags'))); // Simple text reset
    });

    $('#start-over-btn').on('click', () => {
        step2.fadeOut(() => {
            topicInput.val(''); $('#article-audience').val('');
            $('.tuneup-module textarea, .tuneup-module .output-box').val('').html('');
            generateBtn.prop('disabled', false);
            step1.fadeIn();
        });
    });

    // --- BARRACUDA BULK ---
    const startBulkBtn = $('#start-bulk-btn'), bulkKeywords = $('#bulk-keywords');
    const bulkProgress = $('#bulk-progress-container'), bulkQueueList = $('#bulk-queue-list');
    let isBulkRunning = false;

    startBulkBtn.on('click', function() {
        if (isBulkRunning || !bulkKeywords.val()) return;
        isBulkRunning = true;
        startBulkBtn.prop('disabled', true).text('Processing...');
        const keywords = bulkKeywords.val().split('\n').filter(k => k.trim() !== '');
        bulkQueueList.empty();
        keywords.forEach(k => bulkQueueList.append(`<li data-keyword="${k.trim()}"><span>QUEUED</span> ${k.trim()}</li>`));
        bulkProgress.fadeIn();

        $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_start_bulk', nonce: aisw_ajax_obj.nonce, keywords: bulkKeywords.val() })
        .done(() => processNextInBulkQueue());
    });

    function processNextInBulkQueue() {
        const nextItem = bulkQueueList.find('li:contains("QUEUED"):first');
        if (!nextItem.length) {
            isBulkRunning = false;
            startBulkBtn.prop('disabled', false).text('Unleash Barracuda');
            bulkQueueList.append('<li><span>COMPLETE</span> All tasks finished.</li>');
            return;
        }
        nextItem.find('span').text('GENERATING').addClass('processing');

        $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_process_bulk_queue', nonce: aisw_ajax_obj.nonce })
        .done(response => {
            if (response.success) {
                nextItem.find('span').text('DONE').removeClass('processing').addClass('done');
            } else {
                nextItem.find('span').text('ERROR').removeClass('processing').addClass('error');
            }
            processNextInBulkQueue();
        }).fail(() => {
            nextItem.find('span').text('ERROR').removeClass('processing').addClass('error');
            processNextInBulkQueue();
        });
    }

    // --- CONTENT REFINERY ---
    const refinerySearch = $('#refinery-post-search'), searchResults = $('#refinery-search-results');
    const refineryTools = $('#refinery-tools');
    let selectedRefineryPostId = null;

    refinerySearch.on('keyup', debounce(function() {
        const searchTerm = $(this).val();
        if (searchTerm.length < 3) { searchResults.hide(); return; }
        $.get(aisw_ajax_obj.ajax_url, { action: 'aisw_post_search', nonce: aisw_ajax_obj.nonce, term: searchTerm })
        .done(response => {
            searchResults.empty().show();
            if (response.success && response.data.length) {
                response.data.forEach(post => {
                    searchResults.append(`<div class="search-result-item" data-id="${post.id}">${post.title}</div>`);
                });
            } else { searchResults.append('<div>No posts found.</div>'); }
        });
    }, 500));

    searchResults.on('click', '.search-result-item', function() {
        selectedRefineryPostId = $(this).data('id');
        $('#refining-post-title').text($(this).text());
        refinerySearch.val($(this).text());
        searchResults.hide();
        refineryTools.fadeIn();
    });

    $('.refine-btn').on('click', function() {
        const btn = $(this), action = btn.data('action');
        const outputArea = btn.siblings('textarea');
        btn.prop('disabled', true).text('Refining...');
        $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_tuneup_refine_action', nonce: aisw_ajax_obj.nonce, post_id: selectedRefineryPostId, action: action })
        .done(response => {
            if (response.success) outputArea.val(response.data);
            else alert('Error: ' + response.data.message);
        }).always(() => btn.prop('disabled', false).text(btn.text().replace('Refining...', 'Generate')));
    });

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
});