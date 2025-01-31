<?php
/**
 * Template for displaying single digests
 */

get_header();

while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <div class="entry-meta">
                <?php the_date(); ?>
            </div>
        </header>

        <div class="entry-content">
            <?php
            $co = $array = json_decode(stripslashes(get_the_content()), true);

            print_r($co[0]);
            ?>  
        </div>

        <?php 
        // Add custom meta display here if needed
        ?>
    </article>
<?php
endwhile;

get_footer();