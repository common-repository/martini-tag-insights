jQuery(function () {
    if (typeof window.activeResponseMartine !== 'undefined') {
        jQuery('input[name=daterange]').daterangepicker({
            autoUpdateInput: false,
            opens: 'right',
            locale: {
                format: 'YYYY/MM/DD',
                cancelLabel: 'Clear'
            }
        });

        jQuery('input[name=daterange]').on('apply.daterangepicker', function (ev, picker) {
            jQuery(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        jQuery('input[name=daterange]').on('cancel.daterangepicker', function (ev, picker) {
            jQuery(this).val('');
        });
    }
});

jQuery(window).load(function () {
    window.tagsTable = null;
    window.postsTable = null;
    window.checkDataPosts = null;
    window.checkDataTags = null;
    window.cloud = null;
    window.bar = null;
    window.fill = d3.scale.category20b();

    jQuery('#martini_tag_insights_generate_tags').on('click', function () {
        jQuery('#martini_tag_insights_generate_tags').text('Waiting...');

        var post_id = jQuery('input[name=post_ID]').val(),
            wp_user_id = jQuery('input[name=user_ID]').val(),
            generate_tag_data = new FormData(),
            text = jQuery('.block-editor-block-list__layout').text();

        generate_tag_data.append('post_id', post_id);
        generate_tag_data.append('wp_user_id', wp_user_id);
        generate_tag_data.append('post_text', text);
        generate_tag_data.append('action', 'martini_tag_insights_generate_tags');

        jQuery.ajax({
            type: 'POST',
            contentType: false,
            processData: false,
            url: ajaxurl,
            data: generate_tag_data,
            success: function (data) {
                jQuery('.editor-post-publish-button').click();
                jQuery('.editor-post-save-draft').click();
                setTimeout(function () {
                    window.location.reload();
                }, 2000);
            },
        });
    });

    function MartinTagInsightsMakeCloudTags(response) {
        if (response.data) {
            toastada.error(response.data.error_text);
            return;
        }

        if (response && response.tags) {
            jQuery("#cloud_tags svg").remove();

            window.cloud = d3.layout.cloud()
                .size([705, 500])
                .words(response.tags.map(function (d) {
                    return {text: d.text, size: d.weight + 10};
                }))
                .padding(5)
                .rotate(function () {
                    return ~~(Math.random() * 2) * 90;
                })
                .font("Impact")
                .fontSize(function (d) {
                    return d.size;
                })
                .text(function (d) {
                    return d.text;
                })
                .on("end", MartinTagInsightsDrawCloud);

            window.cloud.start();
            jQuery('#cloud_tags').show();
        }
    }

    function MartinTagInsightsDrawCloud(words) {
        d3.select("#cloud_tags").append("svg")
            .attr("width", window.cloud.size()[0])
            .attr("height", window.cloud.size()[1])
            .append("g")
            .attr("transform", "translate(" + window.cloud.size()[0] / 2 + "," + window.cloud.size()[1] / 2 + ")")
            .selectAll("text")
            .data(words)
            .enter().append("text")
            .style("font-size", function (d) {
                return d.size + "px";
            })
            .style("font-family", "Impact")
            .attr("text-anchor", "middle")
            .attr("transform", function (d) {
                return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
            })
            .style("fill", function (d) {
                return window.fill(d.text.toLowerCase())
            })
            .text(function (d) {
                return d.text;
            });
    }

    function MartinTagInsightsMakeTableTags(response) {
        if (response.data) {
            toastada.error(response.data.error_text);
            return;
        }

        if (response != null) {
            if (!window.tagsTable) {
                window.tagsData = response.tags;
                window.tagsTable = jQuery('#admin_table_tags').DataTable(
                    {
                        dom: 'lfrtBp',
                        "language": {
                            "emptyTable": "No data found. Please expand your search criteria to get more data"
                        },
                        ordering: true,
                        searching: true,
                        paging: true,
                        pagingType: 'simple',
                        data: response.tags,
                        responsive: true,
                        columns: [
                            {data: 'tag_name'},
                            {data: 'tag_type'},
                            {data: 'posts_count'},
                            {data: 'posts_count_current_blog'},
                            {data: 'tag_views'}
                        ]
                    }
                );
            } else {
                window.tagsTable.clear();
                if (response.tags) {
                    window.tagsTable.rows.add(response.tags);
                }

                window.tagsTable.draw();
                window.tagsData = response.tags;
            }
        }
    }

    function MartinTagInsightsMakeTablePosts(response) {
        if (response.data) {
            toastada.error(response.data.error_text);
            return;
        }

        if (response != null) {
            if (!window.postsTable) {
                window.postsTable = jQuery('#admin_table_posts').DataTable(
                    {
                        dom: 'lfrtBp',
                        "language": {
                            "emptyTable": "No data found. Please expand your search criteria to get more data"
                        },
                        ordering: true,
                        searching: true,
                        paging: true,
                        pagingType: 'simple',
                        data: response.posts,
                        responsive: true,
                        columns: [
                            {data: 'post_title'},
                            {data: 'post_url'},
                            {data: 'hits_count'},
                            {data: 'last_hit'},
                            {data: 'tags_count'},
                        ]
                    }
                );
            } else {
                window.postsTable.clear();
                if (response.posts) {
                    window.postsTable.rows.add(response.posts);
                }

                window.postsTable.draw();
                window.postsData = response.posts;
            }
        }
    }

    jQuery('#add_api_key').on('click', function () {
        jQuery('#form-activate').hide();
        jQuery('#loader-activate').show();

        var api_key = jQuery('input[name=api_key]').val();
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {action: 'martini_tag_insights_add_api_in_db', api_key: api_key},
            success: function (data) {
                jQuery('#loader-activate').hide();
                jQuery('#form-activate').show();

                if (data.success) {
                    toastada.success(data.data.success_text);
                    window.location.reload();
                } else {
                    toastada.error(data.data.error_text);
                }
            },
        });
    });

    jQuery('#update_api_key').on('click', function () {
        jQuery('#form-activate').hide();
        jQuery('#loader-activate').show();

        var api_key = jQuery('input[name=api_key]').val();
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {action: 'martini_tag_insights_update_api_key', api_key: api_key},
            success: function (data) {
                jQuery('#loader-activate').hide();
                jQuery('#form-activate').show();

                if (data.success) {
                    toastada.success(data.data.success_text);
                    window.location.reload();
                } else {
                    toastada.error(data.data.error_text);
                }
            },
        });
    });

    jQuery('#general_filter').on('click', function () {
        var include = jQuery('input[name=include_tags]').val().trim(),
            date = jQuery('input[name=daterange]').val().trim(),
            exclude = jQuery('input[name=exclude_tags]').val().trim(),
            authors = Array.isArray(jQuery('#author').val()) ? jQuery('#author').val() : [jQuery('#author').val()];

        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {
                action: 'martini_tag_insights_tags_cloud_general_filter',
                date: date,
                include_tags: include,
                exclude_tags: exclude,
                authors: authors,
            },
            success: MartinTagInsightsMakeCloudTags,
        });

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            dataType: 'json',
            data: {
                action: 'martini_tag_insights_table_tags_general_filter',
                date: date,
                include_tags: include,
                exclude_tags: exclude,
                authors: authors,
            },
            success: MartinTagInsightsMakeTableTags,
        });
    });

    jQuery('#reset_filter').on('click', function () {
        jQuery('input[name=include_tags]').val('');
        jQuery('input[name=daterange]').val('');
        jQuery('input[name=exclude_tags]').val('');

        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            dataType: 'json',
            data: {action: 'martini_tag_insights_data_table_posts'},
            success: MartinTagInsightsMakeTablePosts,
        });

        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            dataType: 'json',
            data: {action: 'martini_tag_insights_data_table_tags'},
            success: MartinTagInsightsMakeTableTags,
        });

        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            dataType: 'json',
            data: {action: 'martini_tag_insights_data_cloud_tags'},
            success: MartinTagInsightsMakeCloudTags,
        });
    });

    jQuery('#sync-post-and-tags').on('click', function () {
        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            dataType: 'json',
            data: {action: 'martini_tag_insights_synchronization_tags_project'},
            success: function (data) {
                toastada.success('Tags synchronization');

                jQuery.ajax({
                    type: 'GET',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {action: 'martini_tag_insights_synchronization_posts_and_tags_project'},
                    success: function (data) {
                        toastada.success('Post synchronization');
                    },
                });
            },
        });
    });

    jQuery('#all-sync-post-and-tags').on('click', function () {
        var setting = jQuery("[name='all-sync-post-and-tags-value'][checked]").attr('value');
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {action: 'martini_tag_insights_update_settings_sync_all_posts', setting: setting},
            success: function (response) {
                if (response.success) {
                    toastada.success(response.data.success_text);
                } else {
                    toastada.error(response.data.error_text);
                }
            },
        });
    });

    jQuery("[name='all-sync-post-and-tags-value']").on('click', function () {
        jQuery("[name='all-sync-post-and-tags-value']").each(function () {
            jQuery(this).attr('checked', false);
        });
        jQuery(this).attr('checked', true);
    });

    jQuery('#martini-tag-insights-default-tags-update').on('click', function () {
        var setting = jQuery('input[name=martini-tag-insights-default-tags]').val();
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {action: 'martini_tag_insights_update_settings_default_tags', setting: setting},
            success: function (response) {
                if (response.success) {
                    toastada.success(response.data.success_text);
                    jQuery('input[name=martini-tag-insights-default-tags]').val(response.data.value);
                } else {
                    toastada.error(response.data.error_text);
                }
            },
        });
    });

    jQuery('#general_tags_all').on('click', function () {
        window.bar = new ldBar('#progress-bar');

        respons_bulk();

        jQuery('#general_tags_all').hide();
    });

    function respons_bulk() {
        jQuery.ajax(
            {
                type: 'POST',
                dataType: 'json',
                url: ajaxurl,
                data: {action: 'martini_tag_insights_generate_tags_use_bulk_page'},
                success: success_response_bulk,
            }
        );
    }

    function success_response_bulk() {
        window.clearPostsMartine--;
        jQuery('#post_with_not_tags').text(window.clearPostsMartine);
        var pr = ((window.countPostsMartine - window.clearPostsMartine) / window.countPostsMartine) * 100;
        window.bar.set(pr);

        if (window.clearPostsMartine !== 0) {
            respons_bulk();
        }
    }
});
