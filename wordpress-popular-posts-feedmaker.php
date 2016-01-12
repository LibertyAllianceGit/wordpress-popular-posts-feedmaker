<?php
/*
Plugin Name: WordPress Popular Posts Feedmaker
Plugin URI: http://wpdevelopers.com
Description: Creates a popular feed at /feed/popular, which uses WordPress Popular Posts
Version: 1.0.0
Author: Ted Slater & Tyler Johnson
Author URI: http://libertyalliance.com
Author Email: tyler@libertyalliance.com
Text Domain: wordpress-popular-posts-feedmaker

Copyright 2016 WP Developers & Liberty Alliance

*/


/**********
Check for Plugin Updates
**********/

require 'plugin-update-checker-2.2/plugin-update-checker.php';
$className = PucFactory::getLatestClassVersion('PucGitHubChecker');
$myUpdateChecker = new $className(
    'https://github.com/LibertyAllianceGit/wordpress-popular-posts-feedmaker',
    __FILE__,
    'master'
);


/**********
Setup RSS Feed 
**********/

// Create RSS
function wpdev_custom_rss_feed() {
    add_feed('popular', 'wpdev_custom_rss_temp');
}
add_action('init', 'wpdev_custom_rss_feed');


// RSS Template
function wpdev_custom_rss_temp() {
    include( plugin_dir_path(__FILE__) . 'templates/wpdev-wpp-rss-feed.php');
}


/**********
Create WPP Template for RSS
**********/

// Create Excerpt for WPP
function wpdev_wpp_rss_get_excerpt_by_id($post_id){
    $the_post = get_post($post_id); //Gets post ID
    $the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
    $excerpt_length = 35; //Sets excerpt length by word count
    $the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
    $words = explode(' ', $the_excerpt, $excerpt_length + 1);

    if (count($words) > $excerpt_length) :
        array_pop($words);
        array_push($words, '...');
        $the_excerpt = implode(' ', $words);
    endif;

    $the_excerpt = $the_excerpt;
    return $the_excerpt;
}


// Build Custom HTML for WPP
function wpdev_wpp_rss_custom_html( $mostpopular, $instance ){
        // loop the array of popular posts objects
        foreach( $mostpopular as $popular ) {
            
            $title = esc_attr( $popular->title );
            $link = get_the_permalink( $popular->id );
            $date = get_the_date('r', $popular->id);
            $author = get_the_author_meta( 'display_name', $popular->id );
            $wppexcerpt = wpdev_wpp_rss_get_excerpt_by_id( $popular->id ); // Excerpt placeholder
            $image = wp_get_attachment_image_src(get_post_thumbnail_id( $popular->ID ));

            $output .= '<item>';
            $output .= '<title>' . $title . '</title>';
            $output .= '<link>' . $link . '</link>';
            $output .= '<pubDate>' . $date . '</pubDate>';
            $output .= '<dc:creator>' . $author . '</dc:creator>';
            $output .= '<guid isPermaLink=\'true\'>' . $link . '</guid>';
            $output .= '<description><![CDATA[<img src="' . $image[0] . '" />' . $wppexcerpt . ']]></description>';
            $output .= '<content:encoded><![CDATA[<img src="' . $image[0] . '" />' . $wppexcerpt . ']]></content:encoded>';
            $output .= '</item>';  
            
            //Wed, 02 Oct 2002 08:00:00 EST

        } 
        return $output;
}

// Output Custom HTML on RSS Feeds ONLY
function wpdev_wpp_rss_custom_html_output() {
    if(is_feed()) {
        add_filter( 'wpp_custom_html', 'wpdev_wpp_rss_custom_html', 10, 2 );
    }
}
add_action('wp', 'wpdev_wpp_rss_custom_html_output');