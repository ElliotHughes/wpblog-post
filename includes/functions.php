<?php

// Function to get user city based on IP address
function get_user_ip_address_info($ip)
{
    return IpCheckerService::getInstance()->getIpCheckerByIp($ip);
}


// Function to handle comment display
if (!function_exists('wpblog_post_handle_comment')) {
    function wpblog_post_handle_comment($comment_text, $comment = null) {
        if (!$comment) {
            $comment_ID = get_comment_ID();
            $comment = get_comment($comment_ID);
        }
        $show_comment_location = get_option('wpblog_post_show_comment_location', false);
        $ipTips = get_user_ip_address_info($comment->comment_author_IP);
        if ($show_comment_location && $comment && $comment->comment_author_IP && $ipTips) {
            $comment_text .= WpBlogTemplate::return_comment_man_location($ipTips);
        }

        return $comment_text;
    }
}
add_filter('comment_text', 'wpblog_post_handle_comment', 10, 2);


// Function to handle post editing
if (!function_exists('wpblog_post_handle_edit_post')) {
    function wpblog_post_handle_edit_post($post_id) {
        $onlineip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
        if (!$onlineip) return;
        update_post_meta($post_id, 'wpblog_post_ip', $onlineip);
    }
}

add_action('save_post', 'wpblog_post_handle_edit_post');


// Function to handle adding author location information to post content
if (!function_exists('wpblog_post_handle_post_content')) {
    function wpblog_post_handle_post_content($content) {
        global $post;
        $show_author_location = get_option('wpblog_post_show_author_location', false);

        if ($show_author_location && get_post_meta($post->ID, 'wpblog_post_ip', true)) {
            $ip_address_custom_for_admin = get_option(
                'wpblog_post_ip_address_custom_for_admin',
                WpBlogConst::WPBLOG_POST_DEFAULT_FALSE
            );


            if ($ip_address_custom_for_admin != WpBlogConst::WPBLOG_POST_DEFAULT_FALSE) {
                $city = $ip_address_custom_for_admin;
            } else{
                $city = get_user_ip_address_info(get_post_meta($post->ID, 'wpblog_post_ip', true));
            }

            $location_info = WpBlogTemplate::return_post_author_location($city);
            $content = $location_info . $content;
        }

        return $content;
    }
}


// Function to handle adding post location information to post content
function wpblog_post_handle_post_content_end($content) {
    global $post;
    $location_info = '';
    $show_post_location = get_option('wpblog_post_show_post_location', false);

    $ip_address_custom_for_admin = get_option(
        'wpblog_post_ip_address_custom_for_admin',
        WpBlogConst::WPBLOG_POST_DEFAULT_FALSE
    );

    if ($ip_address_custom_for_admin != WpBlogConst::WPBLOG_POST_DEFAULT_FALSE) {
        $city = $ip_address_custom_for_admin;
    }

    if (get_option('wpblog_post_show', true) && get_post_meta($post->ID, 'wpblog_post_ip', true) && $show_post_location) {
        $location_info = WpBlogTemplate::return_post_author_location($city);
    }

    return $content . $location_info;
}

add_filter('the_content', 'wpblog_post_handle_post_content');
add_filter('the_content', 'wpblog_post_handle_post_content_end');


// Add a shortcode to show the author location
function wpblog_post_shortcode($atts) {
    $a = shortcode_atts( array(
        'ip' => ''
    ), $atts );

    $ip = $a['ip'] ? $a['ip'] : filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
    $city = get_user_ip_address_info($ip);
    if ($city) {
        return WpBlogTemplate::return_comment_man_location($city);
    }
    return '';
}
add_shortcode( 'wpblog_post_location', 'wpblog_post_shortcode' );


// Add a shortcode to show the post author location
function wpblog_author_location_shortcode() {
    $ip = get_post_meta(get_the_ID(), 'wpblog_post_ip', true);
    $city = get_user_ip_address_info($ip);

    $ip_address_custom_for_admin = get_option(
        'wpblog_post_ip_address_custom_for_admin',
        WpBlogConst::WPBLOG_POST_DEFAULT_FALSE
    );

    if ($ip_address_custom_for_admin !== WpBlogConst::WPBLOG_POST_DEFAULT_FALSE) {
        $city = $ip_address_custom_for_admin;
    }

    if ($city) {
        return WpBlogTemplate::return_post_author_location($city);
    }
    return '';
}
add_shortcode( 'wpblog_author_location', 'wpblog_author_location_shortcode' );

// support bbpress reply
if (!function_exists('reply_content_filter_func')) {
    function reply_content_filter_func( $original_value, $reply = null) {
        $reply_id = bbp_get_reply_id( $reply );
        $user_ip_arr = get_post_meta($reply_id, '_bbp_author_ip');
        $user_ip = '';
        if (isset($user_ip_arr[0]) && $user_ip_arr[0] != '') {
            $user_ip = $user_ip_arr[0];
        }

        $content = '';
        if ($user_ip != '') {
            $show_comment_location = get_option('wpblog_post_show_comment_location', false);
            $city = get_user_ip_address_info($user_ip);
            if ($show_comment_location && $city) {
                $content = WpBlogTemplate::return_comment_man_location($city, true);
            }
        }
        return $original_value . $content;
    }
}
add_filter( 'bbp_get_reply_content', 'reply_content_filter_func', 10, 2);