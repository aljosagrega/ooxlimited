<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="single-content">
        <div class="row">
            <div class="column-12 column-tablet-5 team-wrap-left">
                <div class="col-inner">
                    <?php
                    omero_post_thumbnail('full');

                    /**
                     * Functions hooked in to omero_team_thumbnail action
                     *
                     */
                    do_action('omero_team_thumbnail');
                    ?>
                </div>
            </div>
            <div class="column-12 column-tablet-7 team-wrap-right">
                <div class="col-inner">
                    <div class="team_information">
                        <?php
                        /**
                         * Functions hooked in to omero_team_single_infomations action
                         * 
                         * @see omero_team_header - 10
                         * @see omero_team_job - 10
                         * @see omero_team_informations_tab - 10
                         *
                         */
                        do_action('omero_team_single_infomations');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</article><!-- #post-## -->