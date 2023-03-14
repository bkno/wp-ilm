jQuery(function($) {

	//$('.wpProQuiz_incorrect').parent().parent().hide();

    /* Registration confirmation page - add return destination to login button */
    $('#post-9813 #content-header-main .et_pb_button').each(function() {
        var href = $('#post-9813 #content-header-main .et_pb_button').attr('href');
        href = href + location.search;
        $('#post-9813 #content-header-main .et_pb_button').attr('href', href)
    });

    /* Membership application logic */
    /*if (typeof sectorYears !== 'undefined') {
        if (sectorYears >= 3) {
            $('.widget.membership-type-associate').remove();        
        } else {
            $('.widget.membership-type-full').remove();
        }
    }*/

    /* Membership & Event payment method logic */    
    $('#payment-method-invoice').click(function() {
        $(this).removeClass('button-unselected').siblings('.et_pb_button').toggleClass('button-unselected');
        $('.payment-method-card').removeClass('visible');
        $('.payment-method-invoice').addClass('visible');
        return false;
    });
    $('#payment-method-card').click(function() {
        $(this).removeClass('button-unselected').siblings('.et_pb_button').toggleClass('button-unselected');
        $('.payment-method-invoice').removeClass('visible');
        $('.payment-method-card').addClass('visible');
        return false;
    });

    $('.equal-height .et_pb_blurb_content').matchHeight({
        byRow: false,
        property: 'height',
    });

    /* Swap author and date - not needed as hidign author
    $('.post-meta').each(function() {
        original_html = $(this).html();
        new_html = $(this).find('span').eq(1).prop('outerHTML') + ' &nbsp;|&nbsp; ' + $(this).find('span').eq(0).prop('outerHTML');
        $(this).html(new_html);
    });*/
    
	$('.logo-carousel').slick({
		slidesToShow: 4,
		slidesToScroll: 1,
		autoplay: true,
		autoplaySpeed: 1500,
		arrows: false,
		dots: false,
		pauseOnHover: false,
		responsive: [
		    {
    			breakpoint: 768,
    			settings: {
    				slidesToShow: 3
    			}
    		},
    		{
    			breakpoint: 520,
    			settings: {
        			slidesToShow: 2
    			}
		    }
        ]
    });
    
    /* Populate the bbPress name and email fields with session data */
    $('#bbp_anonymous_author').val($('#ilm-forum-user').data('name'));
    $('#bbp_anonymous_email').val($('#ilm-forum-user').data('email'));

    /* Wrap blurb blocks in a link so not just the heading is clickable */
    $('.blurb-block').each(function() {
        href = $(this).find('a').first().prop('href');
        $(this).wrapInner('<a href="'+href+'"></a>')
    });

    /* Accordion is closed on load */
    $('.et_pb_accordion .et_pb_toggle_open').addClass('et_pb_toggle_close').removeClass('et_pb_toggle_open');
    $('.et_pb_accordion .et_pb_toggle').click(function() {
      $this = $(this);
      setTimeout(function(){
         $this.closest('.et_pb_accordion').removeClass('et_pb_accordion_toggling');
      },700);
    });

    /* Event booking - move widget into overlay; only way to ensure it is pre-loaded and contains correct ID */
    /*$('#event-book-now').click(function() {
        if ($('#event-booking-widget').is(':empty')) {
            $('#event-booking').appendTo('#event-booking-widget');
        }
    });*/
    
    if ($('#mobile-account-menu-toggle').is(':hidden')) {
        $('.account-sidebar-header').show();
        $('.menu-account-menu-container').show();
    } else {
        $('.account-sidebar-header').hide();
        $('.menu-account-menu-container').hide();
    }
    $('#mobile-account-menu-toggle').click(function() {
        $('.account-sidebar-header, .menu-account-menu-container').toggle();
        return false;
    });

    // Jobs
    $('.jobs-logos img').wrap('<div class="img-wrap"></div>');
    $('.img-wrap').matchHeight({
        byRow: true,
        property: 'height',
    });

});