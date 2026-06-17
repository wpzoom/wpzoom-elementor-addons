<?php
while ( $all_posts->have_posts() ) :

    $all_posts->the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('wpz-post'); ?>>

            <?php $this->render_thumbnail(); ?>

            <div class="post-grid-overlay-content">
                <?php $this->render_meta( array( 'categories' ), false ); ?>
                <?php $this->render_title(); ?>
                <?php $this->render_meta( array( 'categories' ), true ); ?>
                <?php $this->render_excerpt(); ?>
                <?php $this->render_readmore(); ?>
            </div>

            <?php // Stretched link makes the whole card clickable without nesting <a> tags. ?>
            <a class="post-grid-overlay-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>" tabindex="-1" aria-hidden="true"></a>

        </article>

        <?php

endwhile;

wp_reset_postdata();
