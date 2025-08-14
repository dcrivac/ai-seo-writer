/**
 * File: assets/admin.js
 */
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