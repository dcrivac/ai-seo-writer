
// File: assets/admin.js

jQuery(document).ready(function($) {
    'use strict';

    // --- Element Selectors ---
    const generatorApp = $('#aisw-generator-app');
    const step1 = $('#aisw-step-1-generator');
    const step2 = $('#aisw-step-2-tuneup');
    const generateBtn = $('#generate-article-btn');
    const topicInput = $('#article-topic');
    const toneSelect = $('#article-tone');
    const audienceInput = $('#article-audience');
    const progressDiv = $('#aisw-live-progress');
    const editPostLink = $('#edit-post-link');
    const startOverBtn = $('#start-over-btn');

    let currentPostId = null;

    // --- Live Progress Simulation ---
    const progressSteps = [
        'Analyzing topic...',
        'Crafting compelling headline...',
        'Building article outline...',
        'Writing introduction...',
        'Fleshing out main points...',
        'Polishing conclusion...',
        'Finalizing...'
    ];
    let progressInterval;

    function startProgressIndicator() {
        let currentStep = 0;
        progressDiv.text(progressSteps[currentStep]).fadeIn();
        progressInterval = setInterval(function() {
            currentStep++;
            if (currentStep < progressSteps.length) {
                progressDiv.text(progressSteps[currentStep]);
            } else {
                // Stay on the last step until AJAX completes
                clearInterval(progressInterval);
            }
        }, 1500); // Adjust timing as needed
    }

    function stopProgressIndicator() {
        clearInterval(progressInterval);
        progressDiv.fadeOut();
    }

    // --- Main Article Generation ---
    generateBtn.on('click', function() {
        const topic = topicInput.val();
        if (!topic) {
            alert('Please enter an article topic.');
            return;
        }

        generateBtn.prop('disabled', true);
        startProgressIndicator();

        $.ajax({
            url: aisw_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'aisw_generate_article',
                nonce: aisw_ajax_obj.nonce,
                topic: topic,
                tone: toneSelect.val(),
                audience: audienceInput.val()
            },
            success: function(response) {
                stopProgressIndicator();
                if (response.success) {
                    currentPostId = response.data.post_id;
                    editPostLink.attr('href', response.data.edit_link);
                    step1.fadeOut(function() {
                        step2.fadeIn();
                    });
                } else {
                    alert('Error: ' + response.data.message);
                    generateBtn.prop('disabled', false);
                }
            },
            error: function() {
                stopProgressIndicator();
                alert('An unexpected error occurred. Please try again.');
                generateBtn.prop('disabled', false);
            }
        });
    });

    // --- Tune-Up Panel Actions ---
    $('.tuneup-btn').on('click', function() {
        const btn = $(this);
        const action = btn.data('action');
        const outputArea = $('#' + btn.siblings('textarea, div').attr('id'));

        if (action === 'find_image') {
            const query = encodeURIComponent(topicInput.val());
            const pexelsUrl = `https://www.pexels.com/search/${query}/`;
            outputArea.html(`<a href="${pexelsUrl}" target="_blank">Search for images on Pexels</a>`);
            // Note: A direct API integration would be a great pro feature.
            return;
        }

        btn.prop('disabled', true).text('Working...');

        $.ajax({
            url: aisw_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'aisw_tuneup_action',
                nonce: aisw_ajax_obj.nonce,
                post_id: currentPostId,
                tuneup_action: action
            },
            success: function(response) {
                if (response.success) {
                    outputArea.val(response.data).text(response.data);
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('An unexpected error occurred.');
            },
            complete: function() {
                btn.prop('disabled', false).text(btn.data('original-text') || btn.text());
            }
        });
    });

    // --- Start Over ---
    startOverBtn.on('click', function() {
        step2.fadeOut(function() {
            // Reset fields
            topicInput.val('');
            audienceInput.val('');
            $('.tuneup-module textarea, .tuneup-module .output-box').val('').html('');
            currentPostId = null;
            generateBtn.prop('disabled', false);
            step1.fadeIn();
        });
    });
});

