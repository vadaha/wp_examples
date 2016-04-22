<?php /** @package WordPress @subpackage Default_Theme  * */

?>
<?php

/*
Plugin Name: API for dulyart
Plugin URI: http://dulyartweb.localhost/
Description: API to mentioned requirements
Author: Yury Sudakov
Version: 1.0
Author URI: http://dulyartweb.localhost/
*/

class Dulyart_API
{

    public function __construct()
    {

        // add ajax interface:
        add_action('wp_ajax_nopriv_get_video_gallery', array($this, 'get_video_gallery'));
        add_action('wp_ajax_get_video_gallery', array($this, 'get_video_gallery'));

        add_action('wp_ajax_nopriv_get_image_gallery', array($this, 'get_image_gallery'));
        add_action('wp_ajax_get_image_gallery', array($this, 'get_image_gallery'));

        add_action('wp_ajax_nopriv_get_teachers', array($this, 'get_teachers'));
        add_action('wp_ajax_get_teachers', array($this, 'get_teachers'));

        add_action('wp_ajax_nopriv_get_events', array($this, 'get_events'));
        add_action('wp_ajax_get_events', array($this, 'get_events'));

        add_action('wp_ajax_nopriv_get_blogs', array($this, 'get_blogs'));
        add_action('wp_ajax_get_blogs', array($this, 'get_blogs'));

        add_action('wp_ajax_nopriv_get_studios_timetable', array($this, 'get_studios_timetable'));
        add_action('wp_ajax_get_studios_timetable', array($this, 'get_studios_timetable'));

        add_action('wp_ajax_nopriv_email', array($this, 'email_wp'));
        add_action('wp_ajax_email', array($this, 'email_wp'));

    }

    function email_wp()
    {
        header('Content-Type: application/json; charset=utf-8');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST,OPTIONS");

        $content = "";
        $data = json_decode($_POST['request']);
        $to = "coder@sudakovyury.com";
        $subject = "Email from API DulyArt";

        $content .= "Name:" . $_GET['name'] . "\n";
        $content .= "Email:" . $_GET['email'] . "\n";
        $content .= "Group:" . $_GET['group'] . "\n";
        $content .= "Message:" . $_GET['content'] . "\n";

        $status = wp_mail($to, $subject, $content);
        echo json_encode($status);
        exit;

    }

    function get_video_gallery()
    {
        header('Content-Type: application/json; charset=utf-8');
        header("Access-Control-Allow-Origin: *");
        $lang = null;
        if ($_REQUEST['lang'] == 'he') {
            $lang = 0;
        } else if ($_REQUEST['lang'] == 'en') {
            $lang = 2;
        }

        $video_items = get_option('zsvg_items', true);
        foreach ($video_items[$lang] as $video_item) {
            if (isset($video_item['source'])) {
                $videos[] = array(
                    'cover_image' => $video_item['thethumb'],
                    'video_id' => $video_item['source'],
                    'title' => $video_item['title']
                );
            }
        }
        echo json_encode($videos);
        exit;
    }

    function get_image_gallery()
    {
        $req = json_encode($_REQUEST);
        $cache_key = md5($req);
        if (!$my_db_result = wp_cache_get($cache_key)) {

            $images = get_posts(array('post_type' => 'gg_galleries', 'posts_per_page' => -1, 'suppress_filters' => 1));
            header('Content-Type: application/json; charset=utf-8');
            header("Access-Control-Allow-Origin: *");
            $cui1 = 0;
            foreach ($images as $image) {
                $images_list = gg_gall_data_get($image->ID, false);
                foreach ($images_list as $image_item) {
                    $image_data = get_post($image_item['img_src']);
                    $response1[$cui1++] = array(
                        'img_title' => $image_data->post_title,
                        'img_src' => $image_data->guid
                    );
                }
            }
            $count_img = $cui1;


            /*  1- total count of items
                2- total pages
                3- items
                4- current page
                5- link of next page */

            if (isset($_REQUEST['per_page'])) {
                $per_page = $_REQUEST['per_page'];
            } else {
                $per_page = 10;
            }
            if ($per_page < 2) $per_page = 2;
            if ($per_page > 50) $per_page = 50;

            $all_pages = ceil($count_img / $per_page);

            if (isset($_REQUEST['page'])) {
                $page = $_REQUEST['page'];
            } else {
                $page = 1;
            }
            if ($page < 1) $per_page = 1;


            if ($page > $all_pages) $page = $all_pages;
            if ($page < $all_pages) $next_page = $page + 1;
            else $next_page = false;

            $response['pages'] = $all_pages;
            $response['per_page'] = $per_page;
            $response['count_img'] = $count_img;
            $response['current_page'] = $page;
            $response['link'] = $next_page;
            $response['images'] = array();

            for ($i = ($page - 1) * $per_page; $i < $page * ($per_page); $i++) {
                $response['images'][$i] = $response1[$i];
            }

            $my_db_result = json_encode($response);

            wp_cache_set($cache_key, $my_db_result);
        }
        echo $my_db_result;
        exit;
    }

    function get_events()
    {
        $req = json_encode($_REQUEST);
        $cache_key = md5($req);
        if (!$my_db_result = wp_cache_get($cache_key)) {

            $events = get_posts(array('post_type' => 'shows', 'posts_per_page' => -1, 'suppress_filters' => 1, 'post_status' => 'publish',));
            header('Content-Type: application/json; charset=utf-8');
            header("Access-Control-Allow-Origin: *");
            foreach ($events as $event) {
                $custom = get_post_custom($event->ID);
                $response[$event->ID] = array(
                    'post_name' => urldecode($event->post_name),
                    'post_title' => $event->post_title,
                    'show_date' => $custom['show_date'],
                    'show_day' => $custom['show_day'],
                    'show_time' => $custom['show_time'],
                    'show_location' => $custom['show_location'],
                    'show_phone' => $custom['show_phone'],
                    'show_tickets' => $custom['show_tickets'],
                );
            }
            $my_db_result = json_encode($response);

            wp_cache_set($cache_key, $my_db_result);
        }
        echo $my_db_result;
        exit;
    }

    function get_teachers()
    {
        $req = json_encode($_REQUEST);
        $cache_key = md5($req);
        if (!$my_db_result = wp_cache_get($cache_key)) {

            $teachers = get_posts(array('post_type' => 'staff', 'posts_per_page' => -1, 'suppress_filters' => 1, 'post_status' => 'publish',));
            header('Content-Type: application/json; charset=utf-8');
            header("Access-Control-Allow-Origin: *");
            foreach ($teachers as $teacher) {
                if (has_post_thumbnail($teacher->ID)) {
                    $image = wp_get_attachment_image_src(get_post_thumbnail_id($teacher->ID), 'single-post-thumbnail');
                    $image_url = $image[0];
                } else {
                    $image_url = "";
                }

                $response[$teacher->ID] = array(
                    'post_name' => urldecode($teacher->post_name),
                    'post_title' => $teacher->post_title,
                    'post_content' => $teacher->post_content,
                    'image' => $image_url,
                );
            }
            $my_db_result = json_encode($response);

            wp_cache_set($cache_key, $my_db_result);
        }
        echo $my_db_result;
        exit;
    }

    function get_blogs()
    {
        $req = json_encode($_REQUEST);
        $cache_key = md5($req);
        if (!$my_db_result = wp_cache_get($cache_key)) {

            $blog = get_posts(array('post_type' => 'post', 'posts_per_page' => -1, 'suppress_filters' => 1, 'post_status' => 'publish',));
            header('Content-Type: application/json; charset=utf-8');
            header("Access-Control-Allow-Origin: *");
            foreach ($blog as $blog1) {
                $comments = get_comments(array(
                    'post_id' => $blog1->ID,
                    'status' => 'approve',
                    'number' => 20));
                $comm = array();


                foreach ($comments as $comment) :
                    $comm[$comment->comment_ID] = array(
                        'comment_author' => $comment->comment_author,
                        'comment_date' => $comment->comment_date,
                        'comment_content' => $comment->comment_content,
                    );
                endforeach;
                $categories = get_the_category($blog1->ID);
                if (!empty($categories)) {
                    $catt = esc_html($categories[0]->name);
                } else $catt = "";


                $response[$blog1->ID] = array(
                    'post_name' => urldecode($blog1->post_name),
                    'post_title' => $blog1->post_title,
                    'post_date' => $blog1->post_date,
                    'comment_count' => $blog1->comment_count,
                    'category' => $catt,
                    'post_content' => $blog1->post_content,
                    'post_author' => get_the_author_meta('display_name', $blog1->post_author),
                    'time' => get_the_time(get_option('date_format'), $blog1->ID),
                    'comm' => $comm

                );

            }
            $my_db_result = json_encode($response);

            wp_cache_set($cache_key, $my_db_result);
        }
        echo $my_db_result;
        exit;
    }

    /* get_studios_timetable JSON Structure:
      studio[id]{
        ["name"]
        ["slug"]
        ["days"][id]{
            ["day_name"]
            ["day_events"][event->ID]{
                [event_hours_id]{
                  ["post_name"]
                  ["post_title"]
                  ["start"]
                  ["end"]
                  ["tooltip"]
                  ["descr1"]
                  ["descr2"]
                }
            }
        }
      }
    */
    function get_studios_timetable()
    {
        global $wpdb;
        $req = json_encode($_REQUEST);
        $cache_key = md5($req);
        if (!$my_db_result = wp_cache_get($cache_key)) {

            $events = get_posts(array('post_type' => 'events', 'posts_per_page' => -1, 'suppress_filters' => 1, 'post_status' => 'publish',));
            header('Content-Type: application/json; charset=utf-8');
            header("Access-Control-Allow-Origin: *");
            foreach ($events as $event) {
                $query = "SELECT * FROM `" . $wpdb->prefix . "event_hours` AS t1  WHERE t1.event_id='" . $event->ID . "'";
                $event_hours = $wpdb->get_results($query);
                foreach ($event_hours as $event_hour) {
                    $args = array('orderby' => 'none');
                    $event_terms = wp_get_post_terms($event->ID, "events_category", $args);
                    foreach ($event_terms as $term) {
                        $current_day = get_post($event_hour->weekday_id);
                        $response[$term->term_id]['name'] = $term->name;
                        $response[$term->term_id]['slug'] = $term->slug;
                        $response[$term->term_id]['days'][$event_hour->weekday_id]['day_name'] = $current_day->post_title;
                        $response[$term->term_id]['days'][$event_hour->weekday_id]['day_events'][$event->ID][$event_hour->event_hours_id] = array(
                            'post_name' => urldecode($event->post_name),
                            'post_title' => $event->post_title,
                            'start' => $event_hour->start,
                            'end' => $event_hour->end,
                            'tooltip' => $event_hour->tooltip,
                            'descr1' => $event_hour->before_hour_text,
                            'descr2' => $event_hour->after_hour_text,
                        );
                    }
                }
            }
            $my_db_result = json_encode($response);

            wp_cache_set($cache_key, $my_db_result);
        }
        echo $my_db_result;
        exit;
    }
}

$dulyart_api = new Dulyart_API();