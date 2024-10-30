<?php
function martini_tag_insights_menu_link()
{
    add_menu_page(
        'Martini Tag Insights Page',
        'Martini Tag Insights',
        'manage_options',
        'martini-insights-menu',
        'martini_tag_insights_page_analytics',
        'dashicons-tag'
    );

    add_submenu_page(
        'martini-insights-menu',
        'Martini Tag Insights Page Analytic',
        'Analytics',
        'manage_options',
        'martini-insights-menu'
    );

    add_submenu_page(
        'martini-insights-menu',
        'Martini Tag Insights Page Settings',
        'Settings',
        'manage_options',
        'page-settings',
        'martini_tag_insights_page_settings'
    );

//    add_submenu_page(
//        'martini-insights-menu',
//        'Martini Tag Insights Page Bulk',
//        'General general tags for posts',
//        'manage_options',
//        'page-bulk',
//        'martini_tag_insights_page_bulk'
//    );
}
add_action( 'admin_menu', 'martini_tag_insights_menu_link' );


function martini_tag_insights_page_settings()
{
    include MARTINI_TAG_INSIGHTS_PLUGIN_DIR . 'template/settings-template.php';
}

function martini_tag_insights_include_page( string $name_page )
{
    if ( martini_tag_insights_get_api_key_from_db() ) {
        include MARTINI_TAG_INSIGHTS_PLUGIN_DIR . 'template/' . $name_page . '-template.php';
    } else {
        include MARTINI_TAG_INSIGHTS_PLUGIN_DIR . 'template/settings-template.php';
    }
}

function martini_tag_insights_page_analytics()
{
    martini_tag_insights_include_page( 'analytics' );
}

function martini_tag_insights_page_bulk()
{
    martini_tag_insights_include_page( 'bulk' );
}

function martini_tag_insights_tags_meta_boxes()
{
    if ( martini_tag_insights_get_api_key_from_db() ) {
        add_meta_box( 'tagsdiv', 'Martini Tag Insights', 'martini_tag_insights_tags_print_box', 'post', 'side', 'high' );
    }
}
add_action( 'admin_menu', 'martini_tag_insights_tags_meta_boxes' );

function martini_tag_insights_tags_print_box( $post )
{
    if ( martini_tag_insights_get_api_key_from_db() ) {
        echo '<button type="button" class="btn-generate" id="martini_tag_insights_generate_tags">Generate Tags</button>';
    }
}

function martini_tag_insights_generate_tags()
{
    $post_id = sanitize_text_field( $_POST['post_id'] );
    $post_url = get_permalink( $post_id );
    $wp_user_id = sanitize_text_field( $_POST['wp_user_id'] );
    $text = sanitize_text_field( $_POST['post_text'] );
    $text = str_replace( '&nbsp;', ' ', $text );

    $tags = json_decode(
        martini_tag_insights_post_request(
            MARTINI_TAG_INSIGHTS_API_URL . 'generate-tags',
            [
                'text'     => $text,
                'post_id'  => $post_id,
                'post_url' => $post_url,
                'user_id'  => $wp_user_id
            ]
        ),
        true
    );

    if ( !empty( $tags ) ) {
        $set_tag = [];
        foreach ( $tags as $tag ) {
            $set_tag[] = $tag[0];
        }

        wp_set_post_tags( $post_id, implode( ',', $set_tag ), true );
    } else {
        $tags = martini_tag_insights_get_settings_default_tags_from_db();
        $tags = explode( ', ', $tags );
        wp_set_post_tags( $post_id, $tags, true );
    }

    wp_die();
}
add_action( 'wp_ajax_martini_tag_insights_generate_tags', 'martini_tag_insights_generate_tags' );

function martini_tag_insights_data_table_posts()
{
    $format_date = martini_tag_insights_date_custom_format( $_POST['data'] );
    $posts = json_decode(
        martini_tag_insights_post_request(
            MARTINI_TAG_INSIGHTS_API_URL . 'posts',
            [
                'start_date' => $format_date[0],
                'end_date'   => $format_date[1],
            ]
        ),
        true
    );

    $mapping = function ( $post ) {
        $post['post_title'] = get_the_title( $post['ID'] );
        $post['post_url'] = get_permalink( $post['ID'] );

        if ( empty( get_post_meta( $post['ID'], 'views', true ) ) ) {
            $post['post_views'] = 0;
        } else {
            $post['post_views'] = get_post_meta( $post['ID'], 'views', true );
        }

        return $post;
    };

    $posts = array_map( $mapping, $posts );
    $data = wp_json_encode( [ 'posts' => $posts ] );
    print_r( $data );
    wp_die();
}
add_action( 'wp_ajax_martini_tag_insights_data_table_posts', 'martini_tag_insights_data_table_posts' );

function martini_tag_insights_data_table_tags()
{
    $format_date = martini_tag_insights_date_custom_format( $_POST['data'] );
    $tags = json_decode(
        martini_tag_insights_post_request(
            MARTINI_TAG_INSIGHTS_API_URL . 'tags/full',
            [
                'start_date' => $format_date[0],
                'end_date'   => $format_date[1]
            ]
        ),
        true
    );

    print_r( wp_json_encode( [ 'tags' => $tags ] ) );
    wp_die();
}
add_action( 'wp_ajax_martini_tag_insights_data_table_tags', 'martini_tag_insights_data_table_tags' );

function martini_tag_insights_data_cloud_tags()
{
    $tags = json_decode( martini_tag_insights_get_tags_from_remote(), true );
    print_r( wp_json_encode( [ 'tags' => $tags ] ) );
    wp_die();
}
add_action( 'wp_ajax_martini_tag_insights_data_cloud_tags', 'martini_tag_insights_data_cloud_tags' );

/* count posts views
---------------------------------------------------------- */
function martini_tag_insights_update_post_views()
{
    if ( is_single() ) {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $not_bot = 'Mozilla|Opera';
        $bot = 'Bot|robot|Slurp|yahoo';
        if ( preg_match( "/$not_bot/i", $useragent ) && !preg_match( "/$bot/i", $useragent ) ) {
            global $post;

            if ( $post->post_status === 'publish' ) {
                martini_tag_insights_post_request(
                    MARTINI_TAG_INSIGHTS_API_URL . 'add-ip-for-post',
                    [
                        'ip'      => martini_tag_insights_get_the_user_ip(),
                        'post_id' => $post->ID
                    ]
                );
            }
        }
    }
}
add_action( 'wp_head', 'martini_tag_insights_update_post_views' );

function martini_tag_insights_get_the_user_ip()
{
    if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    return $_SERVER['REMOTE_ADDR'];
}

function martini_tag_insights_post_request( string $url, array $data )
{
    $data['api_key'] = martini_tag_insights_get_api_key_from_db();
    $data['home_url'] = get_option( 'home' );

    if ( $data['api_key'] ) {
        if ( strpos( $data['api_key'], 'dev-' ) !== false ) {
            $url = str_replace( MARTINI_TAG_INSIGHTS_API_URL, MARTINI_TAG_INSIGHTS_DEV_API_URL, $url );
            $data['api_key'] = str_replace( 'dev-', '', $data['api_key'] );
        }

        $http = new GuzzleHttp\Client();
        try {
            $response = $http->request(
                'POST',
                $url,
                [
                    'form_params' => $data,
                ]
            );
        } catch ( GuzzleHttp\Exception\RequestException $request_exception ) {
            $response = $request_exception->getResponse();
        }

        if ( $response->getStatusCode() === 500 ) {
            wp_send_json_error(
                [
                    'error_text' => 'Your API is blank or has not been validated. Please check your API key or generate a new one.'
                ]
            );
        }

        return $response->getBody()->getContents();
    }

    return false;
}

function martini_tag_insights_get_tags_from_remote()
{
    $posts = get_posts(
        [
            'numberposts'      => -1,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'meta_key'         => '',
            'meta_value'       => '',
            'post_status'      => 'any',
            'post_type'        => 'post',
            'suppress_filters' => true,
        ]
    );

    $mapping = function ( $post ) {
        return $post->ID;
    };

    return martini_tag_insights_post_request(
        MARTINI_TAG_INSIGHTS_API_URL . 'search-tag-id',
        [
            'post_ids' => array_map( $mapping, $posts )
        ]
    );
}

function martini_tag_insights_check_api( $api_key )
{
    $http = new GuzzleHttp\Client();

    try {
        $url = MARTINI_TAG_INSIGHTS_API_URL . 'validate-key';
        if ( strpos( $api_key, 'dev-' ) !== false ) {
            $url = str_replace( MARTINI_TAG_INSIGHTS_API_URL, MARTINI_TAG_INSIGHTS_DEV_API_URL, $url );
            $api_key = str_replace( 'dev-', '', $api_key );
        }

        $response = $http->request(
            'POST',
            $url,
            [
                'form_params' => [
                    'home_url' => get_option( 'home' ),
                    'api_key'  => $api_key,
                ],
            ]
        );
    } catch ( GuzzleHttp\Exception\RequestException $request_exception ) {
        $response = $request_exception->getResponse();
    }

    if ( $response->getStatusCode() === 500 ) {
        wp_send_json_error(
            [
                'error_text' => 'Your API is blank or has not been validated. Please check your API key or generate a new one.'
            ]
        );
    }

    return $response->getStatusCode() === 200;
}

function martini_tag_insights_add_api_in_db()
{
    if ( !isset( $_POST['api_key'] ) ) {
        wp_send_json_error(
            [
                'error_text' => 'Your API is blank or has not been validated. Please check your API key or generate a new one.'
            ]
        );
    }

    $api_key = sanitize_text_field( $_POST['api_key'] );
    $martini_tag_insights_check_api_key = martini_tag_insights_check_api( $api_key );
    $check_db_api_key = martini_tag_insights_get_api_key_from_db();

    if ( $check_db_api_key ) {
        wp_send_json_error( [ 'error_text' => 'API KEY is in db' ] );
    }

    if ( !$martini_tag_insights_check_api_key ) {
        wp_send_json_error( [ 'error_text' => 'API KEY is\'t validate api to server' ] );
    }

    global $wpdb;
    $wpdb->insert(
        MARTINI_TAG_INSIGHTS_DB_TABLE_NAME,
        [
            'name'  => 'api_key',
            'value' => $api_key
        ],
        [
            '%s',
            '%s'
        ]
    );

    wp_send_json_success( [ 'success_text' => 'Your API key has been validated successfully.' ] );
}
add_action( 'wp_ajax_martini_tag_insights_add_api_in_db', 'martini_tag_insights_add_api_in_db' );

function martini_tag_insights_date_custom_format( $dates )
{
    $dates = explode( '-', sanitize_text_field( $dates ) );
    $firstDate = $dates[0] ? strtotime( $dates[0] ) : '';
    $secondDate = $dates[1] ? strtotime( $dates[1] ) : '';

    return [ $firstDate, $secondDate ];
}

function martini_tag_insights_update_api_in_db()
{
    if ( !isset( $_POST['api_key'] ) ) {
        wp_send_json_error(
            [
                'error_text' => 'Your API is blank or has not been validated. Please check your API key or generate a new one.'
            ]
        );
    }

    $api_key = sanitize_text_field( $_POST['api_key'] );
    if ( !$api_key ) {
        wp_send_json_error(
            [
                'error_text' => 'Your API is blank or has not been validated. Please check your API key or generate a new one.'
            ]
        );
    }

    if ( !martini_tag_insights_check_api( $api_key ) ) {
        wp_send_json_error( [ 'error_text' => 'API KEY is\'t validate api to server' ] );
    }

    global $wpdb;
    $wpdb->update(
        MARTINI_TAG_INSIGHTS_DB_TABLE_NAME,
        [ 'value' => $api_key ],
        [ 'name' => 'api_key' ]
    );

    wp_send_json_success( [ 'success_text' => 'Your API key has been validated successfully.' ] );
}
add_action( 'wp_ajax_martini_tag_insights_update_api_key', 'martini_tag_insights_update_api_in_db' );

function martini_tag_insights_check_api_field()
{
    global $wpdb;
    return $wpdb->get_var(
        "SELECT `name` FROM " . MARTINI_TAG_INSIGHTS_DB_TABLE_NAME . " WHERE name='api_key';"
    ) ? true : false;
}

function martini_tag_insights_get_api_key_from_db()
{
    global $wpdb;
    return $wpdb->get_var(
        "SELECT `value` FROM " . MARTINI_TAG_INSIGHTS_DB_TABLE_NAME . " WHERE name='api_key';"
    );
}

function martini_tag_insights_format_tags_for_include_exclude_filter( string $tags )
{
    $tags = str_replace( [ ' ', ',,' ], '', sanitize_text_field( $tags ) );
    return explode( ',', $tags );
}

function martini_tag_insights_table_tags_general_filter()
{
    $format_date = [];
    if ( isset( $_POST['date'] ) && !empty( $_POST['date'] ) ) {
        $date = martini_tag_insights_date_custom_format( $_POST['date'] );
        $format_date['start_date'] = $date[0];
        $format_date['end_date'] = $date[1];
    }

    if ( isset( $_POST['include_tags'] ) && !empty( $_POST['include_tags'] ) ) {
        $format_date['include_tags'] = martini_tag_insights_format_tags_for_include_exclude_filter( $_POST['include_tags'] );
    }

    if ( isset( $_POST['exclude_tags'] ) && !empty( $_POST['exclude_tags'] ) ) {
        $format_date['exclude_tags'] = martini_tag_insights_format_tags_for_include_exclude_filter( $_POST['exclude_tags'] );
    }

    if ( isset( $_POST['authors'] ) && !empty( $_POST['authors'] ) ) {
        $authors = $_POST['authors'];
        if ( $_POST['authors'][0] === 'default' ) {
            array_shift( $authors );
        }

        if ( !empty( $authors ) ) {
            $format_date['authors'] = $authors;
        }
    }

    $tags = json_decode(
        martini_tag_insights_post_request(
            MARTINI_TAG_INSIGHTS_API_URL . 'tags/table/filter-general-tags',
            $format_date
        ),
        true
    );

    $data = wp_json_encode( [ 'tags' => $tags ] );
    print_r( $data );
    wp_die();
}
add_action( 'wp_ajax_martini_tag_insights_table_tags_general_filter', 'martini_tag_insights_table_tags_general_filter' );

function martini_tag_insights_tags_cloud_general_filter()
{
    $format_date = [];
    if ( isset( $_POST['date'] ) && !empty( $_POST['date'] ) ) {
        $date = martini_tag_insights_date_custom_format( $_POST['date'] );
        $format_date['start_date'] = $date[0];
        $format_date['end_date'] = $date[1];
    }

    if ( isset( $_POST['include_tags'] ) && !empty( $_POST['include_tags'] ) ) {
        $format_date['include_tags'] = martini_tag_insights_format_tags_for_include_exclude_filter( $_POST['include_tags'] );
    }

    if ( isset( $_POST['exclude_tags'] ) && !empty( $_POST['exclude_tags'] ) ) {
        $format_date['exclude_tags'] = martini_tag_insights_format_tags_for_include_exclude_filter( $_POST['exclude_tags'] );
    }

    if ( isset( $_POST['authors'] ) && !empty( $_POST['authors'] ) ) {
        $authors = $_POST['authors'];
        if ( $_POST['authors'][0] === 'default' ) {
            array_shift( $authors );
        }

        if ( !empty( $authors ) ) {
            $format_date['authors'] = $authors;
        }
    }

    $tags = json_decode(
        martini_tag_insights_post_request(
            MARTINI_TAG_INSIGHTS_API_URL . 'tags/cloud/filter-general-tags',
            $format_date
        ),
        true
    );

    $data = wp_json_encode( [ 'tags' => $tags ] );
    print_r( $data );
    wp_die();
}
add_action( 'wp_ajax_martini_tag_insights_tags_cloud_general_filter', 'martini_tag_insights_tags_cloud_general_filter' );

function martini_tag_insights_synchronization_tags_project()
{
    $tags = get_tags();
    $format_date['tags'] = [];
    foreach ( $tags as $tag ) {
        $format_date['tags'][] = $tag->name;
    }

    $tags = json_decode(
        martini_tag_insights_post_request(
            MARTINI_TAG_INSIGHTS_API_URL . 'sync/tags-project',
            $format_date
        ),
        true
    );

    $data = wp_json_encode( [ 'tags' => $tags ] );
    print_r( $data );
    wp_die();
}
add_action( 'wp_ajax_martini_tag_insights_synchronization_tags_project', 'martini_tag_insights_synchronization_tags_project' );

function martini_tag_insights_synchronization_posts_and_tags_project()
{
    $posts = get_posts(
        [
            'numberposts' => 100,
        ]
    );

    $format_date['posts'] = [];
    $i = 0;
    foreach ( $posts as $post ) {
        $format_date['posts'][ $i ]['ID'] = $post->ID;
        $format_date['posts'][ $i ]['user_id'] = $post->post_author;
        $format_date['posts'][ $i ]['post_url'] = get_post_permalink( $post->ID );

        $format_date['posts'][ $i ]['tags'] = wp_get_post_tags( $post->ID, [ 'fields' => 'names' ] );
        $i++;
    }

    $tags = json_decode(
        martini_tag_insights_post_request(
            MARTINI_TAG_INSIGHTS_API_URL . 'sync/posts-and-tags-project',
            $format_date
        ),
        true
    );

    $data = wp_json_encode( [ 'tags' => $tags ] );
    print_r( $data );
    wp_die();
}
add_action( 'wp_ajax_martini_tag_insights_synchronization_posts_and_tags_project', 'martini_tag_insights_synchronization_posts_and_tags_project' );

function martini_tag_insights_synchronization_post_save( $post_id, $post, $update )
{
    if ( wp_is_post_revision( $post_id ) && $post->post_type !== 'post' ) {
        return;
    }

    $url = MARTINI_TAG_INSIGHTS_API_URL . 'sync/posts-save-or-update';
    if ( martini_tag_insights_get_settings_sync_post_in_template() ) {
        if ( $post->post_status === 'trash' ) {
            $url = MARTINI_TAG_INSIGHTS_API_URL . 'sync/posts-remove';
        }
    } else {
        $exclude_status_post = [ 'trash' ];
        if ( in_array( $post->post_status, $exclude_status_post ) ) {
            return;
        }
    }

    if ( martini_tag_insights_get_api_key_from_db() ) {
        $format_date['post']['ID'] = $post_id;
        $format_date['post']['user_id'] = $post->post_author;
        $format_date['post']['post_url'] = get_post_permalink( $post_id );
        $format_date['post']['tags'] = wp_get_post_tags( $post_id, [ 'fields' => 'names' ] );

        martini_tag_insights_post_request(
            $url,
            $format_date
        );
    }
}
add_action( 'save_post', 'martini_tag_insights_synchronization_post_save', 25, 3 );

function martini_tag_insights_update_tags( $post_id, $post_after, $post_before )
{
    if ( wp_is_post_revision( $post_id ) && $post_after->post_type !== 'post' ) {
        return;
    }

    $url = MARTINI_TAG_INSIGHTS_API_URL . 'sync/posts-save-or-update';
    if ( martini_tag_insights_get_settings_sync_post_in_template() ) {
        if ( $post_before->post_status === 'trash' && $post_after->post_status !== 'trash' ) {
            $url = MARTINI_TAG_INSIGHTS_API_URL . 'sync/posts-restore';
        } elseif ( $post_after->post_status === 'trash' ) {
            $url = MARTINI_TAG_INSIGHTS_API_URL . 'sync/posts-remove';
        }
    } else {
        $exclude_status_post = [ 'trash' ];
        if ( in_array( $post_after->post_status, $exclude_status_post ) ) {
            return;
        }
    }

    if ( martini_tag_insights_get_api_key_from_db() ) {
        $format_date['post']['ID'] = $post_id;
        $format_date['post']['user_id'] = $post_after->post_author;
        $format_date['post']['post_url'] = get_post_permalink( $post_id );
        $format_date['post']['tags'] = wp_get_post_tags( $post_id, [ 'fields' => 'names' ] );

        martini_tag_insights_post_request(
            $url,
            $format_date
        );
    }
}
add_action( 'post_updated', 'martini_tag_insights_update_tags', 10, 3 );

function martini_tag_insights_update_settings_sync_all_posts()
{
    if ( !isset( $_POST['setting'] ) ) {
        wp_send_json_error(
            [
                'error_text' => 'Your API is blank or has not been validated. Please check your API key or generate a new one.'
            ]
        );
    }

    global $wpdb;
    $value_setting = sanitize_text_field( $_POST['setting'] );
    $success_text = 'Update settings';
    if ( martini_tag_insights_check_settings_sync_post() ) {
        $wpdb->update(
            MARTINI_TAG_INSIGHTS_DB_TABLE_NAME,
            [ 'value' => $value_setting ],
            [ 'name' => 'settings-sync-posts' ]
        );
        wp_send_json_success( [ 'success_text' => $success_text ] );
    }

    $wpdb->insert(
        MARTINI_TAG_INSIGHTS_DB_TABLE_NAME,
        [
            'name'  => 'settings-sync-posts',
            'value' => $value_setting
        ],
        [
            '%s',
            '%s'
        ]
    );
    wp_send_json_success( [ 'success_text' => $success_text ] );
}
add_action( 'wp_ajax_martini_tag_insights_update_settings_sync_all_posts', 'martini_tag_insights_update_settings_sync_all_posts' );

function martini_tag_insights_check_settings_sync_post()
{
    global $wpdb;
    return $wpdb->get_var(
        "SELECT `name` FROM " . MARTINI_TAG_INSIGHTS_DB_TABLE_NAME . " WHERE name='settings-sync-posts';"
    ) ? true : false;
}

function martini_tag_insights_get_settings_sync_post()
{
    global $wpdb;
    return $wpdb->get_var(
        "SELECT `value` FROM " . MARTINI_TAG_INSIGHTS_DB_TABLE_NAME . " WHERE name='settings-sync-posts';"
    );
}

function martini_tag_insights_get_settings_sync_post_in_template()
{
    if ( martini_tag_insights_check_settings_sync_post() ) {
        return martini_tag_insights_get_settings_sync_post();
    }

    return 0;
}

function martini_tag_insights_generate_tags_use_bulk_page()
{
    $tags = get_terms( 'post_tag', [ 'fields' => 'ids' ] );
    $post = get_posts(
                [
                    'numberposts' => 1,
                    'post_type'   => 'post',
                    'post_status' => 'publish',
                    'tax_query'   => [
                        [
                            'taxonomy' => 'post_tag',
                            'field'    => 'id',
                            'terms'    => $tags,
                            'operator' => 'NOT IN'
                        ],
                    ]
                ]
            )[0];

    $text = str_replace( '&nbsp;', ' ', wp_strip_all_tags( $post->post_content ) );
    $post_id = $post->ID;
    $post_url = get_post_permalink( $post_id );
    $user_id = $post->post_author;

    $tags = json_decode(
        martini_tag_insights_post_request(
            MARTINI_TAG_INSIGHTS_API_URL . 'generate-tags',
            [
                'text'     => $text,
                'post_id'  => $post_id,
                'post_url' => $post_url,
                'user_id'  => $user_id
            ]
        ),
        true
    );

    $set_tag = [];
    foreach ( $tags as $tag ) {
        $set_tag[] = $tag[0];
    }

    wp_set_post_tags( $post_id, implode( ',', $set_tag ) );

    wp_die();
}
add_action( 'wp_ajax_martini_tag_insights_generate_tags_use_bulk_page', 'martini_tag_insights_generate_tags_use_bulk_page' );

function martini_tag_insights_get_settings_default_tags_from_db()
{
    global $wpdb;
    return $wpdb->get_var(
        "SELECT `value` FROM " . MARTINI_TAG_INSIGHTS_DB_TABLE_NAME . " WHERE name='settings-default-tags';"
    );
}

function martini_tag_insights_update_settings_default_tags()
{
    if ( !isset( $_POST['setting'] ) ) {
        wp_send_json_error(
            [
                'error_text' => 'Your API is blank or has not been validated. Please check your API key or generate a new one.'
            ]
        );
    }

    $value_setting = str_replace( [ ' ', ',,' ], '', sanitize_text_field( $_POST['setting'] ) );
    $value_setting = trim( $value_setting );
    if ( $value_setting[ mb_strlen( $value_setting ) - 1 ] === ',' ) {
        $value_setting = substr( $value_setting, 0, -1 );
    }
    $value_setting = str_replace( ',', ', ', $value_setting );

    global $wpdb;
    $wpdb->update(
        MARTINI_TAG_INSIGHTS_DB_TABLE_NAME,
        [ 'value' => $value_setting ],
        [ 'name' => 'settings-default-tags' ]
    );

    martini_tag_insights_post_request(
        MARTINI_TAG_INSIGHTS_API_URL . 'sync/default-tags',
        [
            'tags' => explode( ', ', $value_setting )
        ]
    );

    wp_send_json_success( [ 'success_text' => 'Update settings', 'value' => $value_setting ] );
}
add_action( 'wp_ajax_martini_tag_insights_update_settings_default_tags', 'martini_tag_insights_update_settings_default_tags' );

function martini_tag_insights_add_form_feedback()
{
    $screen = get_current_screen();

    // Early Bail!
    if ( empty( $screen ) || !in_array( $screen->id, [ 'plugins', 'plugins-network' ], true ) ) {
        return;
    }

    wp_enqueue_script(
        'feedback-js',
        MARTINI_TAG_INSIGHTS_PLUGIN_DIR . 'admin/js/feedback.js',
        [ 'jquery' ],
        MARTINI_TAG_INSIGHTS_VERSION,
        true
    );

    wp_enqueue_style(
        'feedback-style',
        MARTINI_TAG_INSIGHTS_PLUGIN_DIR . 'admin/css/feedback.css',
        [],
        MARTINI_TAG_INSIGHTS_VERSION
    );
    ?>
    <div class="martini_tag_insights_modal_window_first" id="martini-tag-insights-form" style="display: none">
        <div class="martini_tag_insights_modal_window">
            <div class="martini_tag_insights_container">
                <div class="martini_tag_insights_head">
                    <h2>Help Us Improve</h2>
                    <a href="" id="martini_tag_insights_close">
                        <img src="<?php echo MARTINI_TAG_INSIGHTS_PLUGIN_DIR_URL . 'admin/img/times-solid.svg'; ?>"
                             alt=""
                             class="martini_tag_insights_close"
                        >
                    </a>
                </div>
                <hr>

                <div class="martini_tag_insights_wrapper">
                    <p>Please share why you are deactivating Martini Tag</p>

                    <?php
                    $i = 1;
                    foreach ( get_uninstall_reasons() as $value ) {
                        ?>
                        <div class="martini_tag_insights_form_radio_btn">
                            <input id="radio-<?= $i; ?>"
                                   type="radio"
                                   name="martini-tag-insights-feeedback-radio-btn"
                                   value="<?= $value['title']; ?>"
                                <?php echo $i === 1 ? 'checked' : ''; ?>
                            >
                            <label for="radio-<?= $i; ?>"><?= $value['title']; ?></label>
                        </div>
                        <?php
                        $i++;
                    }
                    ?>

                    <textarea type="text"
                              id="martini-tag-insight-textarea"
                              name="text"
                              class="martini_tag_insights_message"
                              placeholder="Please share the reason" rows="1"
                              style="display: none"></textarea>

                    <div class="martini_tag_insights_buttons">
                        <button type="submit" class="martini_tag_insights_skip" id="martini-tag-insight-skip">Skip &
                            Deactive
                        </button>

                        <button type="submit" class="martini_tag_insights_submit" id="martini-tag-insight-add-feedback">
                            Submit & Deactive
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'admin_footer', 'martini_tag_insights_add_form_feedback' );

function get_uninstall_reasons()
{
    return [
        'no_longer_needed'           => [
            'title'       => 'I no longer need the plugin',
            'placeholder' => '',
        ],
        'found_a_better_plugin'      => [
            'title'       => 'I found a better plugin',
            'placeholder' => 'Please share which plugin',
        ],
        'couldnt_get_plugin_to_work' => [
            'title'       => 'I couldn\'t get the plugin to work',
            'placeholder' => '',
        ],
        'temporary_deactivation'     => [
            'title'       => 'It\'s a temporary deactivation',
            'placeholder' => '',
        ],
        'other'                      => [
            'title'       => 'Other',
            'placeholder' => 'Please share the reason',
        ],
    ];
}

function martini_tag_insights_add_feedback()
{
    martini_tag_insights_post_request_without_api_key(
        MARTINI_TAG_INSIGHTS_API_URL . 'feedback',
        [
            'text' => $_GET['text'],
        ]
    );
    wp_die();
}
add_action( 'wp_ajax_martini_tag_insights_add_feedback', 'martini_tag_insights_add_feedback' );

function martini_tag_insights_post_request_without_api_key( string $url, array $data )
{
    $data['api_key'] = martini_tag_insights_get_api_key_from_db();
    $data['home_url'] = get_option( 'home' );

    if ( $data['api_key'] && strpos( $data['api_key'], 'dev-' ) !== false ) {
        $url = str_replace( MARTINI_TAG_INSIGHTS_API_URL, MARTINI_TAG_INSIGHTS_DEV_API_URL, $url );
        $data['api_key'] = str_replace( 'dev-', '', $data['api_key'] );
    }

    $http = new GuzzleHttp\Client();
    try {
        $response = $http->request(
            'POST',
            $url,
            [
                'form_params' => $data,
            ]
        );
    } catch ( GuzzleHttp\Exception\RequestException $request_exception ) {
        $response = $request_exception->getResponse();
    }

    if ( $response->getStatusCode() === 500 ) {
        wp_send_json_error(
            [
                'error_text' => 'Error'
            ]
        );
    }

    wp_send_json_success( [ 'success_text' => 'Feedback send' ] );
}