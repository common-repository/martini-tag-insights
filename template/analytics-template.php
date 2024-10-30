<p class="text-center">This plugin is currently in beta.
    <a href="https://martini.technology/plugin-feedback?utm_source=wp-plugin&utm_medium=referral&utm_campaign=feedback&utm_content=analytics-page"
       target="_blank">Please let us know about any issues/comments/feature requests you may have</a>.
</p>

<h1>Analytics</h1>

<hr>

<div class="wrap">
    <div style="display: flex; justify-content: space-around;">
        <div>
            <div id="cloud_tags" class="cloud-tags block-cloud"></div>
            <div id="loader_cloud" class="loader-cloud">
                <img src="<?php echo plugin_dir_url( __FILE__ ) . '../admin/img/5.gif'; ?>" alt="loader">
            </div>
        </div>

        <div class="cloud-filters">
            <div class="cloud-filter-date">
                <label>Filter tags by date:</label><br>
                <input class="cloud-datepicker input-mb-15" type="text" name="daterange"/>
            </div>

            <div>
                <label>Filter exclude tags:</label><br>
                <input class="cloud-text-input input-mb-15" type="text" name="exclude_tags" value=""
                       placeholder="one, two"/>
            </div>

            <div>
                <label>Filter include tags:</label><br>
                <input class="cloud-text-input input-mb-15" type="text" name="include_tags" value=""
                       placeholder="one, two"/>
            </div>

            <div>
                <?php
                $users = get_users(
                    [
                        'role__in'            => [ 'administrator', 'editor', 'author', 'contributor' ],
                        'has_published_posts' => [ 'post' ],
                    ]
                );
                ?>
                <label>Filter author:</label><br>
                <select class="cloud-text-input input-mb-15" id="author" name="author">
                    <option value="default">Change author</option>
                    <?php
                    foreach ( $users as $user ) {
                        ?>
                        <option value="<?php echo $user->ID; ?>"><?php echo $user->display_name; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>

            <button type="button" id="general_filter" class="btn-search">Apply filters</button>
            <button type="button" id="reset_filter" class="btn-search btn-search-danger">Reset</button>
        </div>
    </div>

    <h1>Tags:</h1>
    <div>
        <div id="block-table-tags" style="display: none">
            <table data-order='[[ 0, "asc" ]]' data-page-length="10" id="admin_table_tags"
                   class="table table-striped table-bordered" style="width:100%">
                <thead>
                <tr>
                    <th>Tag name</th>
                    <th>Tag type</th>
                    <th>Posts count</th>
                    <th>Used on posts & pages</th>
                    <th>Tag views</th>
                </tr>
                </thead>
            </table>
        </div>

        <div id="loader-table-tags">
            <img src="<?php echo plugin_dir_url( __FILE__ ) . '../admin/img/5.gif'; ?>" alt="loader">
        </div>
    </div>

    <h1>Posts:</h1>
    <div>
        <div id="block-table-post" style="display: none">
            <table data-order='[[ 1, "asc" ]]' data-page-length="10" id="admin_table_posts" class="table table-striped
            table-bordered" style="width:100%">
                <thead>
                <tr>
                    <th>Post title</th>
                    <th>Post url</th>
                    <th>Post view</th>
                    <th>Last hit</th>
                    <th>Tags count</th>
                </tr>
                </thead>
            </table>
        </div>

        <div id="loader-table-post">
            <img src="<?php echo plugin_dir_url( __FILE__ ) . '../admin/img/5.gif'; ?>" alt="loader">
        </div>
    </div>
</div>

<div class="feedback pg-analytics">
    <a href="https://martini.technology/plugin-feedback?utm_source=wp-plugin&utm_medium=referral&utm_campaign=feedback&utm_content=analytics-page"
       target="_blank">
        <button type="button" class="btn-search">Submit feedback</button>
    </a>
</div>

<script>
    var activeResponseMartine = true;
</script>