<?php
	
	/**
		This template was used as the forum root, however it stopped working with Divi and wouldn't render the template correctly.
		
		We now just use this to redirect to the Legacies Discusion forum.
	*/
	
	if ( $_SERVER['REQUEST_URI'] == '/members/forum/' ) {
		header("Location: /members/members-forum");
	}
	
	
?>

<?php get_header(); ?>

<div id="main-content">

	<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    		
    		<div class="entry-content">
				<div class="et-l et-l--post">
					<div class="et_builder_inner_content et_pb_gutters3">
						<div id="members" class="et_pb_section et_pb_section_0 et_pb_specialty_fullwidth et_pb_equal_columns et_section_specialty">
		    				<div class="et_pb_row et_pb_gutters2 et_pb_row_1-4_3-4">
		    					<div class="et_pb_column et_pb_column_1_4 et_pb_column_0 et_pb_css_mix_blend_mode_passthrough et_pb_column_single" id="members-sidebar">
		            				<div id="account-sidebar" class="et_pb_module et_pb_sidebar_0 et_pb_widget_area clearfix et_pb_widget_area_left et_pb_bg_layout_light et_pb_sidebar_no_border">
									    <?php echo do_shortcode('[et_pb_section global_module="10161"][/et_pb_section]'); ?>
									</div> <!-- .et_pb_widget_area -->
					            </div> <!-- .et_pb_column -->
		                        <div class="et_pb_column et_pb_column_3_4  et_pb_column_1 et_pb_specialty_column" id="members-content">
		                            <div class=" et_pb_row_inner et_pb_row_inner_0">
		                				<div class="et_pb_column et_pb_column_4_4 et_pb_column_inner  et_pb_column_inner_0">
		                                    <div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_0">
		    				                    <div class="et_pb_text_inner">
		    					
		                                            <h1><?php the_title(); ?></h1>
		    
		                                            <?php the_content(); ?>
		    
		                        				</div>
		                        			</div> <!-- .et_pb_text -->
		                        		</div> <!-- .et_pb_column -->
		                        	</div> <!-- .et_pb_row_inner -->
		                        </div> <!-- .et_pb_column -->
		    				</div> <!-- .et_pb_row -->
		    			</div> <!-- .et_pb_section -->
		    		</div>
	    		</div>
    		</div>

		</article> <!-- .et_pb_post -->

	<?php endwhile; ?>

</div> <!-- #main-content -->

<?php get_footer(); ?>