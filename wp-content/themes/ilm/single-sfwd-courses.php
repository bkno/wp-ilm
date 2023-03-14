<?php get_header(); ?>

<div id="main-content">

	<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    		
    		<div class="entry-content">
        		<div id="content-header-main" class="et_pb_section et_pb_fullwidth_section  et_pb_section_0 et_section_regular">
					<section class="et_pb_fullwidth_header et_pb_module et_pb_bg_layout_dark et_pb_text_align_center  et_pb_fullwidth_header_0"<?php if (has_post_thumbnail() && get_field('feature_hide_single') != true): echo ' style="background-image: url(\''.get_the_post_thumbnail_url().'\'"'; endif ?>>
				
        				<div class="et_pb_fullwidth_header_container center">
        					<div class="header-content-container center">
            					<div class="header-content">
	            					<h2>Course</h2>
            						<h1><?php the_title(); ?></h1>
            						<div class="et_pb_header_content_wrapper"></div>
            					</div>
            				</div>
    				    </div>
        				<div class="et_pb_fullwidth_header_overlay"></div>
        				<div class="et_pb_fullwidth_header_scroll"></div>
        			</section>
    			</div> <!-- .et_pb_section -->
    			
    			<div id="post-content" class="et_pb_section et_section_regular">
    				<div class="et_pb_row">
                        <div class="et_pb_column et_pb_column_1_1  et_pb_column_0">
                            <div class="et_pb_text et_pb_module et_pb_text_align_left  et_pb_text_0">
                				<div class="et_pb_text_inner">
                    				<?php the_content(); ?>
                				</div>
                            </div> <!-- .et_pb_text -->
        			    </div> <!-- .et_pb_column -->
    				</div> <!-- .et_pb_row -->
    			</div> <!-- .et_pb_section -->
			
            </div>
					
		</article> <!-- .et_pb_post -->

	<?php endwhile; ?>

</div> <!-- #main-content -->

<?php get_footer(); ?>