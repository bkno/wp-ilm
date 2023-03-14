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
									<h2>Training</h2>
									<h1>Assignment</h1>
									<div class="et_pb_header_content_wrapper">
										<!--<img style="max-width: 150px;" src="/wp-content/uploads/graphic-book.png" alt="">-->
									</div>
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

									<h3>Details</h3>
									<?php $postmeta = get_post_meta(get_the_ID()); ?>
									<ul>
										<li>Submitted: <?php echo get_the_date( 'j M Y', get_the_ID() ) ?> by <?php echo $postmeta['disp_name'][0]; ?></li>
										<li>Course: <?php echo '<a href="' . get_the_permalink( $postmeta['course_id'][0] ) . '">' . get_the_title( $postmeta['course_id'][0] ) . '</a>'; ?></li>
										<li>Lesson: <?php echo '<a href="' . get_the_permalink( $postmeta['lesson_id'][0] ) . '">' . get_the_title( $postmeta['lesson_id'][0] ) . '</a>'; ?></li>
									</ul>

									<br>

									<h3>Uploaded assignment:</h3>

									<?php the_content(); ?>

									<br><br>

									<?php $comments = get_comments( array('post_id' => get_the_id()) ) ?>
									<div id="comments" class="comments-area">

										<?php if ( count($comments) > 0 ) : ?>
											<h3 class="comments-title">Comments</h3>

											<ol class="commentlist">
												<?php wp_list_comments(['per_page' => 0], $comments) ?>
											</ol>

											<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
											<nav role="navigation" id="comment-nav-below" class="site-navigation comment-navigation">
												<div class="nav-previous">
													<?php previous_comments_link( '&larr; Older Comments' ); ?>
												</div>
												<div class="nav-next">
													<?php next_comments_link( 'Newer Comments &rarr;' ); ?>
												</div>
											</nav>
											<?php endif; ?>
										<?php endif; ?>

										<?php comment_form(['class_submit' => 'et_pb_button', 'title_reply' => 'Post a comment']); ?>

									</div><!-- #comments .comments-area -->

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