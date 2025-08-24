// File: assets/admin.js

jQuery(document).ready(function($) {
    'use strict';

    // --- Onboarding Tour with Intro.js ---
    if (!localStorage.getItem('aisw_onboarding_shown')) {
        introJs().setOptions({
            steps: [
                { element: '.nav-tab-wrapper', intro: 'Switch between Single Post, Bulk, and Refinery modes.' },
                { element: '#article-topic', intro: 'Enter a specific topic for your article here.' },
                { element: '#generate-article-btn', intro: 'Click to generate your SEO-optimized post!' },
                { element: '.aisw-settings-form', intro: 'Configure your AI model and API keys in Settings.' }
            ],
            showProgress: true,
            exitOnOverlayClick: true
        }).start();
        localStorage.setItem('aisw_onboarding_shown', 'true');
    }

    $('#aisw-restart-tour').on('click', () => {
        localStorage.removeItem('aisw_onboarding_shown');
        introJs().start();
    });

    // --- Contextual Tooltips ---
    $('[data-tooltip]').hover(function() {
        $(this).after(`<span class="aisw-tooltip">${$(this).data('tooltip')}</span>`);
    }, function() {
        $('.aisw-tooltip').remove();
    });

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
    const previewPane = $('#aisw-preview-pane'), previewContent = $('#aisw-preview-content');
    let currentPostId = null;
    let isPaused = false;

    // LocalStorage for draft saving
    if (localStorage.getItem('aisw_draft_topic')) {
        topicInput.val(localStorage.getItem('aisw_draft_topic'));
        $('#article-tone').val(localStorage.getItem('aisw_draft_tone') || 'Casual');
        $('#article-audience').val(localStorage.getItem('aisw_draft_audience'));
        llmSelector.val(localStorage.getItem('aisw_draft_model') || 'default');
    }

    function saveDraft() {
        localStorage.setItem('aisw_draft_topic', topicInput.val());
        localStorage.setItem('aisw_draft_tone', $('#article-tone').val());
        localStorage.setItem('aisw_draft_audience', $('#article-audience').val());
        localStorage.setItem('aisw_draft_model', llmSelector.val());
    }

    topicInput.on('input', saveDraft);
    $('#article-tone, #article-audience, #llm-selector').on('change', saveDraft);

    // Real-Time Topic Suggestions (Autocomplete with example trending topics)
    const trendingTopics = ['SEO strategies 2025', 'AI content tools', 'Digital marketing trends', 'Sustainable living tips', 'Tech gadgets review'];
    topicInput.autocomplete({
        source: trendingTopics,
        minLength: 0
    }).focus(function() {
        if (!$(this).val()) {
            $(this).autocomplete('search', '');
        }
    });

    function startProgress() {
        progressDiv.fadeIn(300);
    }
    function stopProgress() { progressDiv.fadeOut(300); }

    generateBtn.on('click', function() {
        if (!topicInput.val().trim()) {
            topicInput.addClass('error').attr('placeholder', aisw_ajax_obj.i18n.topic_required);
            setTimeout(() => topicInput.removeClass('error').attr('placeholder', 'e.g., The resurgence of vinyl in the digital age'), 3000);
            return;
        }
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
                localStorage.setItem('aisw_last_post', JSON.stringify({ post_id: response.data.post_id, title: response.data.title }));
                $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_get_post_content', nonce: aisw_ajax_obj.nonce, post_id: currentPostId })
                .done(contentResponse => {
                    if (contentResponse.success) {
                        previewContent.html(contentResponse.data.content);
                        previewPane.fadeIn(300);
                    }
                });
                step1.fadeOut(300, () => step2.fadeIn(300));
                localStorage.removeItem('aisw_draft_topic'); // Clear draft on success
                localStorage.removeItem('aisw_draft_tone');
                localStorage.removeItem('aisw_draft_audience');
                localStorage.removeItem('aisw_draft_model');
            } else { alert('Error: ' + response.data.message); generateBtn.prop('disabled', false); }
        }).fail(() => { alert('An unexpected error occurred.'); generateBtn.prop('disabled', false); })
        .always(() => stopProgress());
    });

    // Undo Generation
    $('#undo-generate-btn').on('click', function() {
        const lastPost = JSON.parse(localStorage.getItem('aisw_last_post'));
        if (lastPost) {
            $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_undo_generate', nonce: aisw_ajax_obj.nonce, post_id: lastPost.post_id })
            .done(response => {
                if (response.success) {
                    alert('Post "' + lastPost.title + '" deleted.');
                    localStorage.removeItem('aisw_last_post');
                    $('#start-over-btn').click();
                } else {
                    alert('Error: ' + response.data.message);
                }
            });
        } else {
            alert('No recent generation to undo.');
        }
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
                    if (response.data.length === 0) outputArea.append('<p>No relevant linking opportunities found.</p>');
                    response.data.forEach(link => {
                        outputArea.append(`<div class="link-suggestion">"${link.phrase_to_link}" <br>→ <a href="${link.link_to_url}" target="_blank">${new URL(link.link_to_url).pathname.replace(/\//g, ' ')}</a></div>`);
                    });
                } else { outputArea.text(response.data.message || 'Error finding links.'); }
            }).always(() => btn.prop('disabled', false).text(btn.text().replace('Working...', 'Find Links')));
            return;
        }

        $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_tuneup_refine_action', nonce: aisw_ajax_obj.nonce, post_id: currentPostId, action: action })
        .done(response => {
            if (response.success) {
                if (action === 'generate_tags') {
                    const tags = response.data.split(',').map(tag => `<span class="aisw-tag">${tag.trim()}</span>`).join('');
                    outputArea.html(tags);
                } else {
                    outputArea.val(response.data).text(response.data);
                }
            } else alert('Error: ' + response.data.message);
        }).always(() => btn.prop('disabled', false).text(btn.text().replace('Working...', 'Generate')));
    });

    $('#start-over-btn').on('click', () => {
        step2.fadeOut(300, () => {
            $('.tuneup-module textarea, .tuneup-module .output-box').val('').html('');
            previewPane.fadeOut(300);
            generateBtn.prop('disabled', false);
            step1.fadeIn(300);
        });
    });

    // Copy Buttons
    $(document).on('click', '.copy-btn', function() {
        const output = $(this).siblings('textarea, div');
        const text = output.is('textarea') ? output.val() : output.text();
        navigator.clipboard.writeText(text).then(() => {
            $(this).text(aisw_ajax_obj.i18n.copied);
            setTimeout(() => $(this).text('Copy'), 2000);
        });
    });

    // --- BARRACUDA BULK ---
    const startBulkBtn = $('#start-bulk-btn'), bulkKeywords = $('#bulk-keywords');
    const bulkProgress = $('#bulk-progress-container'), bulkQueueList = $('#bulk-queue-list');
    const pauseBulkBtn = $('#pause-bulk-btn');
    let isBulkRunning = false;

    startBulkBtn.on('click', function() {
        const keywords = bulkKeywords.val().split('\n').filter(k => k.trim() !== '');
        if (keywords.length > 10) {
            alert(aisw_ajax_obj.i18n.keywords_limit);
            return;
        }
        if (isBulkRunning || !bulkKeywords.val()) return;
        isBulkRunning = true;
        startBulkBtn.prop('disabled', true).text('Processing...');
        pauseBulkBtn.show();
        bulkQueueList.empty();
        keywords.forEach(k => bulkQueueList.append(`<li data-keyword="${k.trim()}"><span>QUEUED</span> ${k.trim()}</li>`));
        bulkProgress.fadeIn(300);

        $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_start_bulk', nonce: aisw_ajax_obj.nonce, keywords: bulkKeywords.val() })
        .done(() => processNextInBulkQueue());
    });

    pauseBulkBtn.on('click', function() {
        isPaused = !isPaused;
        $(this).text(isPaused ? 'Resume' : 'Pause');
        if (!isPaused) processNextInBulkQueue();
    });

    function processNextInBulkQueue() {
        if (isPaused) return;
        const nextItem = bulkQueueList.find('li:contains("QUEUED"):first');
        if (!nextItem.length) {
            isBulkRunning = false;
            startBulkBtn.prop('disabled', false).text('Unleash Barracuda');
            bulkQueueList.append('<li><span>COMPLETE</span> All tasks finished.</li>');
            pauseBulkBtn.hide();
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
            const totalItems = bulkQueueList.find('li').length - (bulkQueueList.find('li span:contains("COMPLETE")').length ? 1 : 0);
            const completedItems = bulkQueueList.find('li span.done, li span.error').length;
            const progressPercent = (completedItems / totalItems) * 100;
            $('.aisw-progress-fill').css('width', progressPercent + '%');
            setTimeout(processNextInBulkQueue, 1000); // Rate limiting: 1s delay
        }).fail(() => {
            nextItem.find('span').text('ERROR').removeClass('processing').addClass('error');
            setTimeout(processNextInBulkQueue, 1000);
        });
    }

    // --- CONTENT REFINERY ---
    const refinerySearch = $('#refinery-post-search'), searchResults = $('#refinery-search-results');
    const refineryTools = $('#refinery-tools');
    let selectedRefineryPostId = null;

    refinerySearch.on('keyup', debounce(function() {
        const searchTerm = $(this).val();
        const cached = localStorage.getItem('aisw_search_' + searchTerm);
        if (cached) {
            searchResults.html(cached).show();
            return;
        }
        if (searchTerm.length < 3) { searchResults.hide(); return; }
        $.get(aisw_ajax_obj.ajax_url, { action: 'aisw_post_search', nonce: aisw_ajax_obj.nonce, term: searchTerm })
        .done(response => {
            searchResults.empty().show();
            if (response.success && response.data.length) {
                const html = response.data.map(post => `<div class="search-result-item" data-id="${post.id}">${post.title}</div>`).join('');
                searchResults.html(html);
                localStorage.setItem('aisw_search_' + searchTerm, html);
            } else { searchResults.append('<div>No posts found.</div>'); }
        });
    }, 500));

    searchResults.on('click', '.search-result-item', function() {
        selectedRefineryPostId = $(this).data('id');
        $('#refining-post-title').text($(this).text());
        refinerySearch.val($(this).text());
        searchResults.hide();
        refineryTools.fadeIn(300);
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

    // --- Test API Button ---
    $('#aisw-test-api-btn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).text('Testing...');
        $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_test_api', nonce: aisw_ajax_obj.nonce })
        .done(response => {
            const resultDiv = $('#aisw-test-api-result');
            if (response.success) {
                resultDiv.html('<p style="color: green;">' + aisw_ajax_obj.i18n.api_test_success + response.data.message + '</p>');
            } else {
                resultDiv.html('<p style="color: red;">' + aisw_ajax_obj.i18n.api_test_fail + response.data.message + '</p>');
            }
        }).always(() => btn.prop('disabled', false).text('Test API Connection'));
    });

    // --- Feedback Link (Placeholder) ---
    $('#aisw-feedback-link').on('click', function(e) {
        e.preventDefault();
        alert('Send feedback to: support@example.com'); // Replace with actual form or email
    });

    // --- Keyboard Shortcuts ---
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter' && step1.is(':visible')) {
            generateBtn.click();
        }
    });

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
});