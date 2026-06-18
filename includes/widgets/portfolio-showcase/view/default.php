<?php

use Elementor\Utils;

echo '<div class="portfolio-showcase">';

		$wp_query = new \WP_Query( $args );

		$count = $wp_query->found_posts;
		
		if ( $wp_query->have_posts() ) :

			echo '<div class="portfolio-showcase-header">';

			if( ! empty( $widget_title ) ) {

				$this->add_render_attribute( 'widget_title', 'class', 'wpzoom-portfolio-showcase-widget-title' );
				$this->add_inline_editing_attributes( 'widget_title' );
				
				printf( '<%1$s %2$s>%3$s</%1$s>', Utils::validate_html_tag( $settings['widget_title_tag'] ), $this->get_render_attribute_string( 'widget_title' ), $widget_title );
				
			}

			if ( ! $single_post ) {
				include( __DIR__ . '/filter.php' );
			}

			echo '</div><!-- // .portfolio-showcase-header -->';

			if ( $show_masonry ) {
				echo '<div class="portfolio-masonry">';
			}

			if ( 'narrow' == $layout_type ) {
				echo '<div class="inner-wrap portfolio_template_clean">';
			}
			?>
			<div <?php 
				echo( ! empty( $category ) ? 'data-subcategory="' . esc_attr( $category ) . '"' : '' ); ?>
				data-ajax-items-loading="<?php echo esc_attr( $enable_ajax_items_loading ) ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpz_get_portfolio_items' ) ) ?>"
				data-count-nonce="<?php echo esc_attr( wp_create_nonce( 'wpz_count_portfolio_items' ) ) ?>"
				data-items-count="<?php echo esc_attr( $count ) ?>"
				data-instance="<?php echo esc_attr( wp_json_encode( array(
					'layout_type'                  => $layout_type,
					'col_number'                   => $col_number,
					'aspect_ratio'                 => $aspect_ratio,
					'background_video'             => $background_video,
					'show_masonry'                 => $show_masonry,
					'show_popup'                   => $show_popup,
					'show_popup_caption'           => $show_popup_caption,
					'show_popup_excerpt'           => $show_popup_excerpt,
					'show_excerpt'                 => $show_excerpt,
					'view_all_btn'                 => $view_all_btn,
					'readmore_text'                => $readmore_text,
					'enable_director_name'         => $enable_director_name,
					'enable_year'                  => $enable_year,
					'enable_category'              => $enable_category,
					'show_count'                   => $show_count,
					'show_categories'              => true,
					'always_play_background_video' => $always_play_background_video
				) ) ) ?>"
				class="portfolio-grid <?php if ( $show_space ) { ?> portfolio_with_space<?php } ?> col_no_<?php echo esc_attr( $col_number ); ?> <?php echo esc_attr( $always_play_background_video_class ); // WPCS: XSS OK. ?>"
			>
				<?php
					$this->looper( $wp_query,
						array(
							'single_post'                  => $single_post,
							'layout_type'                  => $layout_type,
							'col_number'                   => $col_number,
							'aspect_ratio'                 => $aspect_ratio,
							'background_video'             => $background_video,
							'show_masonry'                 => $show_masonry,
							'show_popup'                   => $show_popup,
							'show_popup_caption'           => $show_popup_caption,
							'show_popup_excerpt'           => $show_popup_excerpt,
							'enable_director_name'         => $enable_director_name,
							'enable_year'                  => $enable_year,
							'enable_category'              => $enable_category,
							'hide_subcategories'           => $hide_subcategories,
							'show_excerpt'                 => $show_excerpt,
							'view_all_btn'                 => $view_all_btn,
							'readmore_text'                => $readmore_text,
							'always_play_background_video' => $always_play_background_video
						)
					);
				?>

			</div><!-- // .portfolio-grid -->

			<?php
				if ( 'narrow' == $layout_type ) { 
					echo '</div>';
				}  

				if ( $show_masonry ) { 
					echo '</div>';
				} 
			?>				

		<?php else: ?>

			<div class="inner-wrap" style="text-align:center;">
				<h3><?php esc_html__( 'No Portfolio Posts Found', 'wpzoom-elementor-addons' ) ?></h3>
				<p class="description"><?php printf( __( 'Please add a few Portfolio Posts first <a href="%1$s">here</a>.', 'wpzoom-elementor-addons' ), esc_url( admin_url( 'post-new.php?post_type=portfolio_item' ) ) ); ?></p>
			</div>

		<?php endif; ?>
		
		<?php if ( ! $single_post ) : ?>
			<div class="portfolio-preloader">
				<div id="loading-39x">
					<div class="spinner">
						<div class="rect1"></div> 
						<div class="rect2"></div> 
						<div class="rect3"></div> 
						<div class="rect4"></div> 
						<div class="rect5"></div>
					</div>
				</div>
			</div>
		<?php endif; ?>

        <?php if ( ! $single_post && $view_all_enabled ) : ?>

            <?php if ( 'narrow' == $layout_type ) { ?>
                <div class="inner-wrap">
            <?php } ?>

            <div class="portfolio-view_all-link">
				<a <?php echo $this->get_render_attribute_string( 'button' ); ?>>
					<?php echo esc_html( $view_all_text ); ?>
				</a>
            </div><!-- .portfolio-view_all-link -->
			<?php
				if ( 'narrow' == $layout_type ) { 
					echo '</div>';
				} 
			?>

        <?php endif; ?>

		</div><!-- // .portfolio-showcase -->