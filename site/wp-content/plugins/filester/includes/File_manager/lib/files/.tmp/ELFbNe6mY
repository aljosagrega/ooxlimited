<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="single-content">
        <div class="single-team-hero">
            
        </div>
        <div class="row">
            <div class="column-12 column-tablet-5 team-wrap-left">
                <div class="col-inner">
                    <h2 class="alpha entry-title"><?php the_title(); ?></h2>
                    <?php
                    do_action('omero_team_position');
                    omero_post_thumbnail('full');
                    do_action('omero_team_thumbnail');
                    do_action('omero_team_content');
                    ?>

                    <div class="team-sidebar-meta">
                        <?php
                        /**
                         * @see omero_team_skills - 10
                         * @see omero_team_programs - 20
                         * @see omero_team_projects - 30
                         * @see omero_team_socials - 40
                         */
                        do_action('omero_team_single_sidebar');
                        ?>
                    </div>
                </div>
            </div>
            <div class="column-12 column-tablet-7 team-wrap-right">
                <div class="col-inner">
                    <div class="team_information">
                        <?php
                        /**
                         * @see omero_team_header - 5
                         * @see omero_team_position - 10
                         * @see omero_team_content - 15
                         * @see omero_team_qa - 20
                         */
                        do_action('omero_team_single_infomations');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article><!-- #post-## -->