/**
 * General purpose script for the public-facing site.
 *
 * This includes:
 * 1. Smooth scrolling for anchor links.
 * 2. A "back to top" button.
 * 3. Logic for the public notice modal on the homepage.
 */

jQuery(document).ready(function ($) {
    // Smooth scroll for anchor links
    $(".scroll").click(function (event) {
        event.preventDefault();
        $('html,body').animate({
            scrollTop: $(this.hash).offset().top
        }, 900);
    });

    // Back to top button
    var $toTop = $('#toTop');
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $toTop.fadeIn();
        } else {
            $toTop.fadeOut();
        }
    });

    $toTop.click(function () {
        $("html, body").animate({
            scrollTop: 0
        }, 600);
        return false;
    });

    /**
     * Public Notice Modal Handler
     *
     * This part of the script handles opening the public notice modal
     * and populating it with the correct content from the page.
     */
    var $modalOverlay = $('#noticeModalOverlay');
    var $modal = $modalOverlay.find('.new-modal');
    var $modalTitle = $modal.find('.new-modal-title .notice-title-text');
    var $modalBody = $modal.find('.new-modal-body');
    var $modalDate = $modal.find('.new-modal-footer .notice-meta');
    var $closeButtons = $modalOverlay.find('.new-close-btn, .new-btn-cancel');

    // Use event delegation for dynamically added or existing notice links
    $(document).on('click', '.modern-notice-link', function (e) {
        e.preventDefault();

        var $link = $(this);
        var noticeId = $link.data('id');

        if (!noticeId) {
            console.error("Notice link is missing a 'data-id' attribute.");
            return;
        }

        // Get data from the clicked link and its corresponding data container
        var title = $link.find('.modern-notice-title').text().trim();
        var date = $link.find('.modern-notice-date').text().trim();

        // The content is stored in a hidden div with a specific ID
        var contentContainer = $('#notice-data-' + noticeId);

        if (contentContainer.length === 0) {
            console.error("Could not find notice content for ID: " + noticeId);
            $modalTitle.text('Error');
            $modalBody.html('<p class="text-danger">Could not load notice content.</p>');
            $modalDate.text(''); // Clear date on error
            $modalOverlay.addClass('active');
            return;
        }

        // Get the HTML content from the hidden div
        var content = contentContainer.html();

        // Populate the modal
        $modalTitle.text(title);
        $modalBody.html(content); // Use .html() to render any HTML tags in the message
        $modalDate.text('Posted on: ' + date);

        // Show the modal
        $modalOverlay.addClass('active');
        $('body').css('overflow', 'hidden'); // Prevent background scrolling
    });

    // Function to close the modal
    function closeModal() {
        $modalOverlay.removeClass('active');
        $('body').css('overflow', 'auto'); // Restore scrolling

        // Reset modal content after a short delay to allow for the closing animation
        setTimeout(function() {
            $modalTitle.text('Loading...');
            $modalBody.html('<div class="text-center">Please wait...</div>');
            $modalDate.text('');
        }, 300); // Matches the CSS transition duration
    }

    // Event listeners for closing the modal
    $closeButtons.on('click', closeModal);
    $modalOverlay.on('click', function(e) {
        if ($(e.target).is($modalOverlay)) {
            closeModal();
        }
    });
    $(document).on('keydown', function(e) {
        if (e.key === "Escape" && $modalOverlay.hasClass('active')) {
            closeModal();
        }
    });

    // Reset modal content when it's closed to avoid showing stale data - old BS logic removed
    /* $modal.on('hidden.bs.modal', function () {
        $modalTitle.text('Loading...');
        $modalBody.html('<div class="text-center">Please wait...</div>');
        $modalDate.text('');
    }); */
});