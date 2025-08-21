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
    let currentPostData = {};

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
                // Trigger initial SEO analysis
                $('#focus-keyword').trigger('keyup');
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
            $('#focus-keyword').val('');
            $('#seo-checklist').empty();
            generateBtn.prop('disabled', false);
            step1.fadeIn();
        });
    });

    // --- SEO ANALYSIS SUITE ---
    $('#focus-keyword').on('keyup', debounce(function() {
        const keyword = $(this).val().toLowerCase();
        const checklist = $('#seo-checklist');
        checklist.empty();

        if (!keyword) return;

        // In a real plugin, you'd fetch post title and content via AJAX
        // For this demo, we'll simulate it.
        const postTitle = topicInput.val().toLowerCase(); 
        const postContent = "Simulated content about " + postTitle; // This would be the real content
        const metaDesc = $('#meta-output').val().toLowerCase();

        // 1. Keyword in Title
        let titleCheck = postTitle.includes(keyword);
        checklist.append(`<li class="${titleCheck ? 'pass' : 'fail'}">Focus Keyword in Title</li>`);

        // 2. Keyword in Meta
        let metaCheck = metaDesc.includes(keyword);
        checklist.append(`<li class="${metaCheck ? 'pass' : 'fail'}">Focus Keyword in Meta Description <button class="button-link tuneup-btn" data-action="generate_meta">Generate</button></li>`);

        // 3. Keyword Density (simplified)
        const density = (postContent.split(keyword).length - 1);
        let densityCheck = density > 1 && density < 5;
        checklist.append(`<li class="${densityCheck ? 'pass' : 'fail'}">Keyword Density (${density} found)</li>`);

        // 4. Internal Links
        checklist.append(`<li class="info">Internal Links <button class="button-link tuneup-btn" data-action="find_links">Find Links</button></li>`);
        
    }, 500));


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

        $.post(aisw_ajax_obj.ajax_url, { 
            action: 'aisw_start_bulk', 
            nonce: aisw_ajax_obj.nonce, 
            keywords: bulkKeywords.val(),
            length: $('#bulk-length').val(),
            format: $('#bulk-format').val()
        }).done(() => processNextInBulkQueue());
    });

    function processNextInBulkQueue() {
        const nextItem = bulkQueueList.find('li:contains("QUEUED"):first');
        if (!nextItem.length) {
            isBulkRunning = false;
            startBulkBtn.prop('disabled', false).text('Start Bulk Generation');
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

    // --- MOBILE API KEY GENERATOR (Settings Page) ---
    $('#aisw_generate_mobile_key').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).text('Generating...');
        const expires_in_days = parseInt($('#aisw_mobile_api_expiry').val() || '0', 10);
        const scopes = $('.aisw-scope:checked').map(function() { return $(this).val(); }).get();
        $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_generate_mobile_key', nonce: aisw_ajax_obj.nonce, expires_in_days: expires_in_days, scopes: scopes })
        .done(response => {
            if (response.success && response.data.token) {
                $('#aisw_mobile_api_key').val(response.data.token);
                // copy to clipboard
                navigator.clipboard.writeText(response.data.token).then(() => {
                    alert('Mobile API key generated and copied to clipboard. Store it somewhere safe.');
                }).catch(() => {
                    alert('Mobile API key generated. Copy it now: ' + response.data.token);
                });
                // append new token row to tokens table
                if (response.data.token_id) {
                    const tid = response.data.token_id;
                    const createdAt = response.data.created_at || '';
                    const createdBy = response.data.created_by || '';
                    const expiresAt = response.data.expires_at || 'Never';
                    const scopesText = response.data.scopes ? response.data.scopes.join(', ') : 'all';
                    // columns: Token ID | Created | Created By | Expires | Scopes | Last Used | Last Used IP | Last Used By | Actions
                    const newRow = $(`<tr data-token-id="${tid}"><td>${tid}</td><td>${createdAt}</td><td>${createdBy}</td><td>${expiresAt}</td><td>${scopesText}</td><td></td><td></td><td></td><td><button type="button" class="button aisw-rotate-token">Rotate</button> <button type="button" class="button aisw-revoke-token">Revoke</button></td></tr>`);
                    $('#aisw_tokens_list').prepend(newRow);
                }
            } else {
                alert('Failed to generate key.');
            }
        }).fail(() => { alert('An unexpected error occurred.'); })
        .always(() => btn.prop('disabled', false).text('Generate New Token'));
    });    // Token action handler (revoke / rotate) using shared modal
    let aisw_pending_action = null;
    $(document).on('click', '.aisw-revoke-token', function() {
        const row = $(this).closest('tr');
        const tokenId = row.data('token-id');
        aisw_pending_action = { type: 'revoke', tokenId: tokenId, row: row };
        $('#aisw_revoke_modal_title').text('Revoke Token');
        $('#aisw_revoke_modal_text').text('Are you sure you want to revoke token ' + tokenId + '? This action cannot be undone.');
        updateModalDetails(row);
        $('#aisw_revoke_confirm').text('Revoke');
        $('#aisw_revoke_modal').addClass('aisw-modal--open');
    });

    // Modal cancel
    $(document).on('click', '#aisw_revoke_cancel', function() {
        aisw_pending_action = null;
        $('#aisw_revoke_modal').removeClass('aisw-modal--open');
    });

    // Modal confirm (handles revoke and rotate)
    $(document).on('click', '#aisw_revoke_confirm', function() {
        if (!aisw_pending_action) return;
        const action = aisw_pending_action.type;
        const tokenId = aisw_pending_action.tokenId;
        const row = aisw_pending_action.row;

        if (action === 'revoke') {
            const btn = row.find('.aisw-revoke-token');
            btn.prop('disabled', true).text('Revoking...');
            $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_revoke_mobile_token', nonce: aisw_ajax_obj.nonce, token_id: tokenId })
            .done(response => {
                if (response.success) {
                    row.fadeOut(() => row.remove());
                } else {
                    alert('Failed to revoke token: ' + (response.data && response.data.message ? response.data.message : 'unknown'));
                    btn.prop('disabled', false).text('Revoke');
                }
            }).fail(() => { alert('Unexpected error'); btn.prop('disabled', false).text('Revoke'); })
            .always(() => {
                aisw_pending_action = null;
               $('#aisw_revoke_modal').removeClass('aisw-modal--open');
            });
            return;
        }

        if (action === 'rotate') {
            const btn = row.find('.aisw-rotate-token');
            btn.prop('disabled', true).text('Rotating...');
            $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_rotate_mobile_token', nonce: aisw_ajax_obj.nonce, token_id: tokenId })
            .done(response => {
                if (response.success && response.data.token) {
                    $('#aisw_mobile_api_key').val(response.data.token);
                    navigator.clipboard.writeText(response.data.token).then(() => {
                        alert('Token rotated and new key copied to clipboard.');
                    }).catch(() => alert('Token rotated. Copy it now: ' + response.data.token));
                    // optionally update rotated_at cell or other UI bits
                } else {
                    alert('Failed to rotate token: ' + (response.data && response.data.message ? response.data.message : 'unknown'));
                }
            }).fail(() => alert('Unexpected error'))
            .always(() => {
                btn.prop('disabled', false).text('Rotate');
                aisw_pending_action = null;
               $('#aisw_revoke_modal').removeClass('aisw-modal--open');
            });
            return;
        }

        // unknown action fallback
        aisw_pending_action = null;
        $('#aisw_revoke_modal').removeClass('aisw-modal--open');
    });

    // Rotate token handler (open modal)
    $(document).on('click', '.aisw-rotate-token', function() {
        const row = $(this).closest('tr');
        const tokenId = row.data('token-id');
        aisw_pending_action = { type: 'rotate', tokenId: tokenId, row: row };
        $('#aisw_revoke_modal_title').text('Rotate Token');
        $('#aisw_revoke_modal_text').text('Rotate token ' + tokenId + '? This will invalidate the previous token and return a new one.');
        updateModalDetails(row);
        $('#aisw_revoke_confirm').text('Rotate');
        $('#aisw_revoke_modal').addClass('aisw-modal--open');
    });

    // Refresh tokens list
    $('#aisw_refresh_tokens').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).text('Refreshing...');
        $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_get_mobile_tokens', nonce: aisw_ajax_obj.nonce })
        .done(response => {
            if (response.success && Array.isArray(response.data)) {
                const tbody = $('#aisw_tokens_list');
                tbody.empty();
                if (response.data.length === 0) {
                    tbody.append('<tr><td colspan="8">No mobile API tokens created.</td></tr>');
                } else {
                    response.data.forEach(t => {
                        const scopesText = t.scopes ? t.scopes.join(', ') : 'all';
                        const row = $(`<tr data-token-id="${t.token_id}"><td>${t.token_id}</td><td>${t.created_at}</td><td>${t.created_by}</td><td>${t.expires_at || 'Never'}</td><td>${scopesText}</td><td>${t.last_used_at || ''}</td><td>${t.last_used_ip || ''}</td><td>${t.last_used_by || ''}</td><td><button type="button" class="button aisw-rotate-token">Rotate</button> <button type="button" class="button aisw-revoke-token">Revoke</button></td></tr>`);
                        tbody.append(row);
                    });
                }
            } else {
                alert('Failed to refresh tokens.');
            }
        }).fail(() => alert('Unexpected error')).always(() => btn.prop('disabled', false).text('Refresh'));
    });

    // Prune expired tokens
    $('#aisw_prune_tokens').on('click', function() {
        const btn = $(this);
        if (!confirm('Are you sure you want to permanently delete all expired tokens?')) return;
        btn.prop('disabled', true).text('Pruning...');
        $.post(aisw_ajax_obj.ajax_url, { action: 'aisw_prune_expired_tokens', nonce: aisw_ajax_obj.nonce })
        .done(response => {
            if (response.success) {
                alert(response.data.pruned_count + ' expired tokens were pruned.');
                $('#aisw_refresh_tokens').trigger('click'); // Refresh the list
            } else {
                alert('Failed to prune tokens: ' + (response.data && response.data.message ? response.data.message : 'unknown'));
            }
        }).fail(() => alert('Unexpected error'))
        .always(() => btn.prop('disabled', false).text('Prune Expired'));
    });

    function updateModalDetails(row) {
        const detailsContainer = $('.aisw-modal__details');
        if (!row || !row.length) {
            detailsContainer.empty().hide();
            return;
        }
        const expires = row.find('td').eq(3).text() || 'N/A';
        const lastUsed = row.find('td').eq(4).text() || 'Never';
        const lastUsedIp = row.find('td').eq(5).text() || 'N/A';

        const detailsHtml = `
            <strong>Expires:</strong> ${expires}<br>
            <strong>Last Used:</strong> ${lastUsed} (IP: ${lastUsedIp})
        `;
        detailsContainer.html(detailsHtml).show();
    }
	});