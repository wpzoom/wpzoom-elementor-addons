<?php
while ( $all_posts->have_posts() ) :

    $all_posts->the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('wpz-post'); ?>>

            <div class="post-grid-inner">

                <?php $this->render_thumbnail(); ?>

                <div class="post-grid-overlay-content">
                    <?php $this->render_meta( array( 'categories' ), false ); ?>
                    <?php $this->render_title(); ?>
                    <?php $this->render_meta( array( 'categories' ), true ); ?>
                </div>

            </div><!-- .post-grid-inner -->

        </article>

        <?php

endwhile;

wp_reset_postdata();
