<?php
$tags = get_terms( 'post_tag', [ 'fields' => 'ids' ] );
$post_with_not_tags = count(
    get_posts(
        [
            'numberposts' => -1,
            'post_type'   => 'post',
            'post_status' => 'publish',
            'tax_query'   => [
                [
                    'taxonomy' => 'post_tag',
                    'field'    => 'id',
                    'terms'    => $tags,
                    'operator' => 'NOT IN'
                ]
            ]
        ]
    )
);
$count_post_all_type = wp_count_posts();
$count_post = $count_post_all_type->publish;
?>
<p class="text-center">This plugin is currently in beta.
    <a href="https://martini.technology/plugin-feedback?utm_source=wp-plugin&utm_medium=referral&utm_campaign=feedback&utm_content=analytics-page"
       target="_blank">Please let us know about any issues/comments/feature requests you may have</a>.
</p>

<h1>Add all</h1>

<hr>

<div class="wrap wrap-flex">
    <h3>Count posts: <?php echo $count_post; ?></h3>
    <h3>Count post with not tags: <span id="post_with_not_tags"><?php echo $post_with_not_tags; ?></span></h3>

    <div id="progress-bar"
         class="ldBar label-center"
         data-preset="circle"
         style="width: 500px; height: 500px"
         data-value="<?php echo ( ( $count_post - $post_with_not_tags ) / $count_post ) * 100; ?>"></div>

    <br>
    <?php
    if ( $post_with_not_tags !== 0 ) {
        ?>
        <button id="general_tags_all" class="btn-sync-getapi">General Tags</button>
        <?php
    }
    ?>
</div>

<div class="feedback pg-analytics">
    <a href="https://martini.technology/plugin-feedback?utm_source=wp-plugin&utm_medium=referral&utm_campaign=feedback&utm_content=analytics-page"
       target="_blank">
        <button type="button" class="btn-search">Submit feedback</button>
    </a>
</div>

<script>
    var activeResponseMartine = true,
        clearPostsMartine = parseInt(<?php echo $post_with_not_tags; ?>),
        countPostsMartine = parseInt(<?php echo $count_post; ?>);
</script>