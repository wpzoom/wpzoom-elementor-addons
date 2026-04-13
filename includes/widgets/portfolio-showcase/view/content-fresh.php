<?php
/**
 * Custom content-fresh template for the Eccentric portfolio layout.
 * Uses widget settings instead of theme options for display toggles.
 *
 * Variables available from parent scope (eccentric.php -> render()):
 * $eccentric_enable_year, $eccentric_enable_category, $eccentric_show_excerpt,
 * $eccentric_enable_btn, $eccentric_btn_text
 */

$instance = init_video_background_on_hover_module();
$final_background_src = $instance->get_data( get_the_ID() );
$is_video_background = ! empty( $final_background_src );
$year = get_post_meta( get_the_ID(), 'su_portfolio_item_year', true ) ?: ' ';
$articleClass = ( ! has_post_thumbnail() && ! $is_video_background ) ? 'no-thumbnail ' : '';

$post_thumbnail = get_the_post_thumbnail_url( get_the_ID() );

$video_background_popup_url      = get_post_meta( get_the_ID(), 'wpzoom_portfolio_video_popup_url', true );
$background_popup_url            = ! empty( $video_background_popup_url ) ? $video_background_popup_url : $post_thumbnail;
$video_background_popup_url_mp4  = get_post_meta( get_the_ID(), 'wpzoom_portfolio_video_popup_url_mp4', true );
$video_background_popup_url_webm = get_post_meta( get_the_ID(), 'wpzoom_portfolio_video_popup_url_webm', true );
$video_background_popup_video_type = get_post_meta( get_the_ID(), 'wpzoom_portfolio_popup_video_type', true );
$popup_video_type = ! empty( $video_background_popup_video_type ) ? $video_background_popup_video_type : 'external_hosted';
$is_video_popup   = $video_background_popup_url_mp4 || $video_background_popup_url_webm;

if ( wp_doing_ajax() ) {
    $articleClass .= ' ' . get_post_type( get_the_ID() );
}

$portfolios = wp_get_post_terms( get_the_ID(), 'portfolio' );

if ( is_array( $portfolios ) ) {
    foreach ( $portfolios as $portfolio ) {
        $articleClass .= ' portfolio_' . $portfolio->term_id . '_item ';
    }
}

if ( $is_video_background ) {
    $filetype = wp_check_filetype( $final_background_src );

    $video_atts = array(
        'loop',
        'muted',
        'playsinline',
    );

    $video_atts = implode( ' ', $video_atts );
    $is_video_popup_class = ' is-portfolio-gallery-video-background';
    $articleClass .= $is_video_popup_class;
}

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $articleClass ); ?>>

    <div class="item-content">

        <div class="main-area">

            <?php
            the_title(
                sprintf( '<h3 class="portfolio_item-title"><a href="%s" rel="bookmark">', esc_attr( esc_url( get_permalink() ) ) ),
                '</a></h3>'
            );
            ?>

            <?php if ( $eccentric_enable_year && $year ) : ?>
                <div class="portfolio_item-year"><?php echo esc_html( $year ); ?></div>
            <?php endif; ?>

            <?php if ( $eccentric_enable_category || $eccentric_show_excerpt ) : ?>

                <div class="cat-and-excerpt-wrapper">

                    <?php if ( $eccentric_enable_category ) : ?>
                        <div class="portfolio_item-category">
                            <?php
                            $terms = get_the_terms( get_the_ID(), 'portfolio' );
                            if ( is_array( $terms ) ) {
                                echo esc_html( implode( ' / ', wp_list_pluck( $terms, 'name' ) ) );
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $eccentric_show_excerpt ) : ?>
                        <div class="portfolio_item-excerpt"><?php the_excerpt(); ?></div>
                    <?php endif; ?>

                </div>

            <?php endif; ?>

            <?php if ( ( 'self_hosted' === $popup_video_type && $is_video_popup ) || ! empty( $video_background_popup_url ) ) : ?>
                <span class="expand-btn"></span>
            <?php endif; ?>

        </div>

        <?php if ( false !== get_the_post_thumbnail_url( null, 'portfolio_item-thumbnail_wide' ) ) : ?>

            <div class="item-background">

                <?php if ( $eccentric_enable_btn ) : ?>
                    <a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" class="button portfolio_item-btn"><?php echo esc_html( $eccentric_btn_text ); ?></a>
                <?php endif; ?>

                <?php if ( $is_video_background ) : ?>
                    <video class="portfolio-gallery-video-background" <?php echo $video_atts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> style="width:100%; height:auto; vertical-align: middle; display:block;">
                        <source src="<?php echo esc_url( $final_background_src ); ?>" type="<?php echo esc_attr( $filetype['type'] ); ?>">
                    </video>
                <?php else : ?>
                    <?php the_post_thumbnail( 'entry-cover' ); ?>
                <?php endif; ?>

            </div>

        <?php endif; ?>

    </div>

</article>
