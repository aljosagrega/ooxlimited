<?php
get_header(); ?>

<div id="primary" class="content-area omero-archive-game-page">
    <main id="main" class="site-main">

        <?php
        if (have_posts()) {
            ?>
            <header class="page-header">
                <?php
                /**
                 * Functions hooked in to omero_header_archive_game action.
                 *
                 * @see omero_header_archive_game_content         - 10
                 */
                do_action('omero_header_archive_game');
                ?>
            </header><!-- .page-header -->
            <div class="omero-games-list">
                <?php
                /**
                 * Functions hooked in to omero_archive_games_list action.
                 *
                 * @see omero_render_game_archive_list         - 10
                 */
                do_action('omero_archive_games_list');
                ?>
            </div>
            <?php
        } else {
            get_template_part('content', 'none');
        }
        ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
