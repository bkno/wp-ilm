<? get_header() ?>

<div id="main-content">

	<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    		
    		<div class="entry-content">
        		<div id="content-header-main" class="et_pb_section et_pb_fullwidth_section  et_pb_section_0 et_section_regular">
					<section class="et_pb_fullwidth_header et_pb_module et_pb_bg_layout_dark et_pb_text_align_center  et_pb_fullwidth_header_0">
        				<div class="et_pb_fullwidth_header_container center">
        					<div class="header-content-container center">
            					<div class="header-content">
            						<h1>Permission denied</h1>
            						<span class="et_pb_fullwidth_header_subhead">
            						    
            						</span>
            					</div>
            				</div>
    				    </div>
        			</section>
    			</div> <!-- .et_pb_section -->
    			
    			<div id="post-content" class="et_pb_section et_section_regular">
    				<div class="et_pb_row">
                        <div class="et_pb_text et_pb_module et_pb_text_0">
            				<div class="et_pb_text_inner">
                				<div class="account-prompt">
                                    To see this content please <a href="/login/?destination=<?= urlencode( $_SERVER['REQUEST_URI'] .'?t='.time() ) ?>">sign in</a> first, <a href="/register/">create an account</a> or you may need to <a href="/membership/">become a member</a>.
                                </div>
            				</div>
                        </div> <!-- .et_pb_text -->
    				</div> <!-- .et_pb_row -->
    			</div> <!-- .et_pb_section -->
						
            </div>
					
		</article> <!-- .et_pb_post -->

	<?php endwhile; ?>

</div> <!-- #main-content -->

<? get_footer() ?>