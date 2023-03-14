<?php
/**
 * Page 4 of setup wizard template file
 *
 * @package LearnDash_Design_Wizard
 *
 * @var array<string, mixed> $template_details
 * @var LearnDash_Design_Wizard $design_wizard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

?>
<div class="design-wizard layout-2">
	<div class="header">
		<div class="logo">
            <?php // phpcs:ignore Generic.Files.LineLength.TooLong?>
			<img src="<?php echo esc_url( \LEARNDASH_LMS_PLUGIN_URL . '/assets/images/learndash.svg' ); ?>" alt="LearnDash" >
		</div>
		<div class="exit">
			<span class="text"><?php esc_html_e( 'Exit to Setup', 'learndash' ); ?></span> <img
				src="<?php echo esc_url( \LEARNDASH_LMS_PLUGIN_URL . '/assets/images/design-wizard/svg/exit.svg' ); ?>"
			>
		</div>
	</div>
	<div class="content">
		<?php
			SFWD_LMS::get_view(
				'design-wizard/live-preview',
				compact( 'template_details', 'design_wizard' ),
				true
			);
			?>
	</div>
	<div class="footer">
		<div class="back">
			<img
				class="icon"
				src="<?php echo esc_url( \LEARNDASH_LMS_PLUGIN_URL . '/assets/images/design-wizard/svg/back.svg' ); ?>"
			> <span class="text"><?php esc_html_e( 'Back', 'learndash' ); ?></span>
		</div>
		<div class="steps">
			<ol class="list">
				<li class="active"><span class="number">1</span> <span
						class="text"><?php esc_html_e( 'Choose a template', 'learndash' ); ?></span></li>
				<li class="active"><span class="number">2</span> <span
						class="text"><?php esc_html_e( 'Fonts', 'learndash' ); ?></span></li>
				<li class="active"><span class="number">3</span> <span
						class="text"><?php esc_html_e( 'Colors', 'learndash' ); ?></span></li>
			</ol>
		</div>
		<div class="buttons">
			<a
				href="#"
				class="button init-button next-button"
			><?php esc_html_e( 'Save & Continue', 'learndash' ); ?></a>
		</div>
	</div>
</div>
