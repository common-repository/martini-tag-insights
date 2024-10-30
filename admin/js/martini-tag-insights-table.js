jQuery(document).ready(function () {
    window.tagsTable = null;
    window.postsTable = null;
    window.cloud = null;
    window.fill = d3.scale.category20b();

    if (typeof window.activeResponseMartine !== 'undefined') {
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
    }

    //admin table layout
    function MartinTagInsightsMakeTablePosts(response) {
        if (response) {
            if (response.data) {
                toastada.error(response.data.error_text);
                return;
            }

            jQuery('#loader-table-post').hide();
            jQuery('#block-table-post').show();

            window.postsData = response.posts;
            window.postsTable = jQuery('#admin_table_posts').DataTable(
                {
                    dom: 'lfrtBp',
                    language: {
                        "emptyTable": "No data found. Please expand your search criteria to get more data"
                    },
                    data: response.posts,
                    ordering: true,
                    searching: true,
                    paging: true,
                    responsive: true,
                    processing: true,
                    columns: [
                        {data: 'post_title'},
                        {data: 'post_url'},
                        {data: 'hits_count'},
                        {data: 'last_hit'},
                        {data: 'tags_count'},
                    ]
                }
            );
        }
    }

    function MartinTagInsightsMakeTableTags(response) {
        if (response) {
            if (response.data) {
                toastada.error(response.data.error_text);
                return;
            }

            jQuery('#loader-table-tags').hide();
            jQuery('#block-table-tags').show();

            window.tagsData = response.tags;
            window.tagsTable = jQuery('#admin_table_tags').DataTable(
                {
                    dom: 'lfrtBp',
                    language: {
                        emptyTable: "No data found. Please expand your search criteria to get more data"
                    },
                    data: response.tags,
                    responsive: true,
                    ordering: true,
                    searching: true,
                    paging: true,
                    processing: true,
                    columns: [
                        {data: 'tag_name'},
                        {data: 'tag_type'},
                        {data: 'posts_count'},
                        {data: 'posts_count_current_blog'},
                        {data: 'tag_views'}
                    ]
                }
            );
        }
    }

    function MartinTagInsightsMakeCloudTags(response) {
        if (response.data) {
            toastada.error(response.data.error_text);
            return;
        }

        jQuery('#loader_cloud').hide();
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
});