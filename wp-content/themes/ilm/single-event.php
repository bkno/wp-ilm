<?php get_header(); ?>

<div id="main-content">

	<?php while ( have_posts() ) : the_post(); ?>

        <?php
            $event_passed = false;
        	if ( strtotime(get_field('event_date')) < time() ) {
        		$event_passed = true;
        	}
        ?>
        
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    		
    		<div class="entry-content">
        		<div id="content-header-main" class="et_pb_section et_pb_fullwidth_section  et_pb_section_0 et_section_regular">
					<section class="et_pb_fullwidth_header et_pb_module et_pb_bg_layout_dark et_pb_text_align_center  et_pb_fullwidth_header_0"<?php if (has_post_thumbnail() && get_field('feature_hide_single') != true): echo ' style="background-image: url(\''.get_the_post_thumbnail_url().'\'"'; endif ?>>
				
        				<div class="et_pb_fullwidth_header_container center">
        					<div class="header-content-container center">
            					<div class="header-content">
            						<h1><?php the_title(); ?></h1>
            						<div class="et_pb_header_content_wrapper"></div>
            					</div>
            				</div>
    				    </div>
        				<div class="et_pb_fullwidth_header_overlay"></div>
        				<div class="et_pb_fullwidth_header_scroll"></div>
        			</section>
    			</div> <!-- .et_pb_section -->
			
			<div id="event-details" class="et_pb_section  et_pb_section_1 et_section_regular">				
				<div class=" et_pb_row et_pb_row_0">
				    <div class="et_pb_column et_pb_column_1_2  et_pb_column_0">
                        <div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_0">
            				<div class="et_pb_text_inner">
                				<h2>Event details</h2>
            					<p>
                                    <?php if (get_field('event_date')): ?>
                    					<strong>Date:</strong> <?php the_field('event_date') ?><?php if (get_field('event_date_end')): echo ' - ' . get_field('event_date_end'); endif ?><br>
                                    <?php endif ?>
                					
                                    <?php if (get_field('event_time')): ?>
                                        <strong>Time:</strong> <?php the_field('event_time') ?><br>
                                    <?php endif ?>
                                    
                                    <?php if (get_field('event_venue')): ?>
                                        <strong>Venue:</strong> <?php the_field('event_venue') ?><br>
                                    <?php endif ?>
                                    
                                    <?php if (get_field('event_who_should_attend')): ?>
                                        <strong>Who should attend:</strong> <?php the_field('event_who_should_attend') ?>
                                    <?php endif ?>
                                </p>
            				</div>
                        </div> <!-- .et_pb_text -->
    			    </div> <!-- .et_pb_column -->
    			    <div class="et_pb_column et_pb_column_1_2  et_pb_column_1">
    				    <div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_1">
            				<div class="et_pb_text_inner">
                				<?php if ($event_passed): ?>
                    				<p>This event has already happened.</p>
                				<?php elseif (!$event_passed): ?>
	                				<h2>Book this event</h2>
                                    <p><?php the_field('event_cost') ?></p>
                    				<?php if (get_field('event_fully_booked')): ?>
                    				    <p><a href="#" class="et_pb_button button_disabled">Fully booked</a></p>
                    				<?php elseif (get_field('event_url')): /* Booking via custom URL eg Zoom */ ?>
                        				<?php
	                        				if (
	                        					( get_field('event_members_only') && ilm_signed_in_member() ) || 
	                        					( !get_field('event_members_only') && ilm_signed_in() ) 
	                        				/*	( !get_field('event_members_only') && has_term( 'partner-event', 'event_type' ) )*/
	                        				): ?>
                                            <p><a id="event-book-now" href="<?php the_field('event_url'); ?>" class="et_pb_button">Book now</a></p>
                        				<?php elseif (get_field('event_members_only') && !ilm_signed_in_member()): ?>
										    <?php get_template_part( 'partials/prompt-members-only-compact'); ?>
										<?php else: ?>
										    <?php get_template_part( 'partials/prompt-user-only'); ?>
										<?php endif; ?>
                                    <?php elseif (get_field('event_crm_id')): ?>
                                        <p><a id="event-book-now" href="#event-booking" class="et_pb_button">Book now</a></p>
                                    <?php endif; ?>
                                    <?php if ( !has_term( 'partner-event', 'event_type' ) ) : ?>
                                    <p><a id="event-book-terms" href="/booking-terms-and-conditions/">Terms & Conditions</a></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!--<p>No booking details are available yet.</p>-->
                                <?php endif ?>
                				</div>
                			</div> <!-- .et_pb_text -->
            			</div> <!-- .et_pb_column -->
        			</div> <!-- .et_pb_row -->
    			</div> <!-- .et_pb_section -->
			
                <div id="event-description">
        			<?php the_content(); ?>
                </div>
                
                <!--
	                ilm_signed_in
	                ilm_signed_in_member
	                
	                event_url
	                event_members_only
	                
                -->
    
				<div id="event-booking" class=" et_pb_row et_pb_row_0">
    				<div class="et_pb_column et_pb_column_4_4  et_pb_column_0 et_pb_css_mix_blend_mode_passthrough et-last-child">
        				<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_0">				
            				<div class="et_pb_text_inner">
				        		<?php
                            		if (!$event_passed && !get_field('event_fully_booked') && !get_field('event_url') && get_field('event_crm_id')):
                            		    echo '<hr>';
                                        echo '<h2 style="text-align: center;">Event booking</h2>';
                                        #echo do_shortcode('[ILM restrict=user class="payment-method"]<h3>Payment method:</h3><p><a class="et_pb_button" href="#" id="payment-method-invoice">Invoice</a> <a class="et_pb_button" href="#" id="payment-method-card">Credit Card</a></p>[/ILM]');

                                        echo do_shortcode('[ILM widget="EventBookingWizard" restrict="user" eventid="'.get_field('event_crm_id').'" nexturl="event-booking-confirmation"]');
                                        
                                        #echo do_shortcode('[ILM widget="EventBookingWizard" paymenttype="Invoice" restrict="user" eventid="'.get_field('event_crm_id').'" nexturl="event-booking-confirmation" class="payment-method-invoice"]');
                                        
                                        #echo do_shortcode('[ILM widget="EventBookingWizard" paymenttype="Credit Card" restrict="user" eventid="'.get_field('event_crm_id').'" nexturl="event-booking-confirmation" class="payment-method-card"]');
                                    endif;
                                ?>
            				</div>
            			</div> <!-- .et_pb_text -->
        			</div> <!-- .et_pb_column -->
    			</div> <!-- .et_pb_row -->
    
            </div>
					
		</article> <!-- .et_pb_post -->

	<?php endwhile; ?>

</div> <!-- #main-content -->

<?php get_footer(); ?>