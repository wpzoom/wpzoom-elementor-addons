<?php

use Elementor\Utils;

$wp_query = new WP_Query( $args );
$count = $wp_query->found_posts;

?>

<?php if ( $enable_dark_mode ) : ?>
<div class="portfolio-with-post-cover">
<?php endif; ?>

<div class="portfolio-archive">


    <section class="portfolio-archive-fresh">
	<?php echo '<div class="portfolio-showcase-header">'; ?>
	<?php
		if( ! empty( $widget_title ) ) {

			$this->add_render_attribute( 'widget_title', 'class', 'wpzoom-portfolio-showcase-widget-title' );
			$this->add_inline_editing_attributes( 'widget_title' );
			
			printf( '<%1$s %2$s>%3$s</%1$s>', Utils::validate_html_tag( $settings['widget_title_tag'] ), $this->get_render_attribute_string( 'widget_title' ), $widget_title );
			
		}
	?>
    <?php 
        if ( ! $single_post ) {
            include( __DIR__ . '/filter.php' );
        }
		echo '</div><!-- // .portfolio-showcase-header -->';
    ?>

    <?php if ( $wp_query->have_posts() ) : ?>

	<?php
		$portfolio_ajax_atts    = array_to_data_atts(
			array(
				'data-nonce'              => esc_attr( wp_create_nonce( 'wpz_get_portfolio_filtered_items' ) ),
				'data-ajax-items-loading' => esc_attr( $enable_ajax_items_loading ),
				'data-callback-template'  => 'archive-eccentric-isotope.php',
			) );
	?>

    <div <?php echo $portfolio_ajax_atts; ?> class="portfolio-grid portfolio-fresh<?php echo $always_play_background_video_class; ?>">

        <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

            <?php include __DIR__ . '/content-fresh.php'; ?>

        <?php endwhile; ?>

    </div>

    <?php get_template_part( 'pagination' ); ?>

    <?php else: ?>

    <?php get_template_part( 'content', 'none' ); ?>

    <?php endif; ?>

    </section><!-- .portfolio-archive-fresh -->
</div>

<?php if ( $enable_dark_mode ) : ?>
</div>
<?php endif; ?>