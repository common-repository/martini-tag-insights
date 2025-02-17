<?php
$api_key = martini_tag_insights_get_api_key_from_db();
$have_field = martini_tag_insights_check_api_field();
$settings_sync_post = martini_tag_insights_get_settings_sync_post_in_template();
$default_tags = martini_tag_insights_get_settings_default_tags_from_db();
?>
<p class="text-center">This plugin is currently in beta.
    <a href="https://martini.technology/plugin-feedback?utm_source=wp-plugin&utm_medium=referral&utm_campaign=feedback&utm_content=settings-page"
       target="_blank">Please let us know about any issues/comments/feature requests you may have</a>.
</p>

<h1>Settings</h1>
<hr>

<div class="wrap martini-wrapper">
    <div id="form-activate">
        <label >In order to use Martini Tag Insights you need to register your website and get an API key:</label><br>
        <input class="api-text-input" type="text" name="api_key" value="<?php echo $api_key; ?>"/>
        <?php
        if ( $have_field ) {
            ?>
            <button type="button" id="update_api_key" class="btn-search">Update API Key</button>
            <?php
        } else {
            ?>
            <button type="button" id="add_api_key" class="btn-search">Add API Key</button>
            <?php
        }
        ?>

        <a href="<?php echo MARTINI_TAG_INSIGHTS_LINK_FOR_SERVICE; ?>" target="_blank">
            <button type="button" class="btn-sync-getapi">Get API Key</button>
        </a>

        <?php
        if ( $_GET['page'] !== 'page-settings' ) {
            ?>
            <p style="color: red">Your API key can no longer be found. Please check your settings.</p>
            <?php
        }
        ?>
    </div>
    <div id="loader-activate" style="display: none;">
        <img src="<?php echo plugin_dir_url( __FILE__ ) . '../admin/img/5.gif'; ?>" alt="loader">
    </div>
</div>

<hr>

<div class="wrap">
    <div id="form-activate">
        <label>Manually synchronise all your published posts & tags with Martini Tag Insights analytics?</label>
        <button type="button" class="btn-sync-getapi" id="sync-post-and-tags">Sync all posts</button>
    </div>
</div>

<hr>

<div class="wrap">
    <div id="form-activate">
        <label>Do you want to synchronise both automated tags and manual tags, or just automated tags generated by
            Martini Tag Insights?</label>
        <div>
            <input type="radio"
                   name="all-sync-post-and-tags-value"
                   value="1"
                   id="all-sync-post-and-tags-value-yes"
                <?php echo $settings_sync_post == 1 ? 'checked="checked"' : ''; ?>>
            <label class="yes" for="all-sync-post-and-tags-value-yes">Yes</label>
            <input type="radio"
                   name="all-sync-post-and-tags-value"
                   value="0"
                   id="all-sync-post-and-tags-value-no"
                <?php echo $settings_sync_post == 0 ? 'checked="checked"' : ''; ?>>
            <label for="all-sync-post-and-tags-value-no">No</label>
            <button type="button" class="btn-sync-getapi btn-sync-getapi-save" id="all-sync-post-and-tags">Save</button>
        </div>
    </div>
</div>

<hr>

<div class="wrap">
    <div id="form-activate">
        <label>Defaults tags</label>
        <div>
            <input class="api-text-input" type="text" name="martini-tag-insights-default-tags" value="<?php echo $default_tags; ?>"/>
            <button type="button" class="btn-sync-getapi btn-sync-getapi-save"
                    id="martini-tag-insights-default-tags-update">Save</button>
        </div>
    </div>
</div>

<hr>

<div class="feedback">
    <a href="https://martini.technology/plugin-feedback?utm_source=wp-plugin&utm_medium=referral&utm_campaign=feedback&utm_content=analytics-page"
       target="_blank">
        <button type="button" class="btn-search">Submit feedback</button>
    </a>
</div>

<script>
    var activeResponseMartine = true;
</script>