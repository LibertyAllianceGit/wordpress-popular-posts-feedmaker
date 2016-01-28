<?php
/*
Plugin Name: WordPress Popular Posts Feedmaker
Plugin URI: http://wpdevelopers.com
Description: Creates a popular feed at /feed/popular, which uses WordPress Popular Posts
Version: 1.7.1
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
Refresh Permalinks on Plugin Activation
**********/

register_activation_hook( __FILE__, 'wpprss_activation_rewrite_permalinks' );

function wpprss_activation_rewrite_permalinks() {
    //Ensure the $wp_rewrite global is loaded
    global $wp_rewrite;
    //Call flush_rules() as a method of the $wp_rewrite object, so that permalinks are flushed
    $wp_rewrite->flush_rules( false );
}


/**********
Add Image Sizes for Patriot Times & Email Newsletters
**********/

// Add Thumbnail Theme Support
add_theme_support( 'post-thumbnails' );

// Add Patriot Times Sizes
add_image_size( 'patriottimes-main', 250, 205, true );
add_image_size( 'patriottimes-headline', 155, 110, true );
add_image_size( 'patriottimes-email', 330, 175, true );

// Fix Cropping for Small Images
function wpdev_thumbnail_crop_fix_pt( $default, $orig_w, $orig_h, $new_w, $new_h, $crop ){
    if ( !$crop ) return null; // let the wordpress default function handle this
 
    $aspect_ratio = $orig_w / $orig_h;
    $size_ratio = max($new_w / $orig_w, $new_h / $orig_h);
 
    $crop_w = round($new_w / $size_ratio);
    $crop_h = round($new_h / $size_ratio);
 
    $s_x = floor( ($orig_w - $crop_w) / 2 );
    $s_y = floor( ($orig_h - $crop_h) / 2 );
 
    return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
}
add_filter( 'image_resize_dimensions', 'wpdev_thumbnail_crop_fix_pt', 10, 6 );


/**********
WPP Feedmaker Options
**********/

// Enqueue Admin Styles
function wpp_rss_plugin_css() {
        wp_register_style( 'wpprss-plugin-css', plugin_dir_url(__FILE__) . 'admin/wpprss-css.css' );
        wp_enqueue_style( 'wpprss-plugin-css' );
}
add_action('admin_enqueue_scripts', 'wpp_rss_plugin_css', 20);


// Create Options Page
add_action( 'admin_menu', 'wpprss_add_admin_menu' );
add_action( 'admin_init', 'wpprss_settings_init' );

function wpprss_add_admin_menu() { 
	add_submenu_page( 
        'options-general.php', 
        'WordPress Popular Posts Feedmaker', 
        'WordPress Popular Posts Feedmaker', 
        'manage_options', 
        'wpprss', 
        'wpprss_options_page' 
    );
}


function wpprss_settings_init() { 
	register_setting( 'wppFeedPage', 'wpprss_settings' );

	add_settings_section(
		'wpprss_wppFeedPage_section', 
		__( 'RSS Feed Options', 'wpprss' ), 
		'wpprss_settings_section_callback', 
		'wppFeedPage'
	);

	add_settings_field( 
		'wpprss_text_limit_field', 
		__( '<span>Limit</span><p>Sets the maximum number of popular posts to be shown in the RSS feed.</p>', 'wpprss' ), 
		'wpprss_text_limit_field_render', 
		'wppFeedPage', 
		'wpprss_wppFeedPage_section' 
	);

	add_settings_field( 
		'wpprss_select_range_field', 
		__( '<span>Range</span><p>Tells WordPress Popular Posts to retrieve the most popular entries within the time range specified for the RSS feed.</p>', 'wpprss' ), 
		'wpprss_select_range_field_render', 
		'wppFeedPage', 
		'wpprss_wppFeedPage_section' 
	);

	add_settings_field( 
		'wpprss_select_freshness_field', 
		__( '<span>Freshness</span><p>Tells WordPress Popular Posts to retrieve the most popular entries published within the time range specified above.</p>', 'wpprss' ), 
		'wpprss_select_freshness_field_render', 
		'wppFeedPage', 
		'wpprss_wppFeedPage_section' 
	);

	add_settings_field( 
		'wpprss_select_orderby_field', 
		__( '<span>Order By</span><p>Sets the sorting option of the popular posts with the RSS feed.</p>', 'wpprss' ), 
		'wpprss_select_orderby_field_render', 
		'wppFeedPage', 
		'wpprss_wppFeedPage_section' 
	);
}


function wpprss_text_limit_field_render() { 
	$options = get_option( 'wpprss_settings' );
	?>
	<input type='text' name='wpprss_settings[wpprss_text_limit_field]' value='<?php echo $options['wpprss_text_limit_field']; ?>' placeholder="10">
	<?php
}


function wpprss_select_range_field_render() { 
	$options = get_option( 'wpprss_settings' );
	?>
	<select name='wpprss_settings[wpprss_select_range_field]'>
		<option value='1' <?php selected( $options['wpprss_select_range_field'], 1 ); ?>>Daily</option>
		<option value='2' <?php selected( $options['wpprss_select_range_field'], 2 ); ?>>Weekly</option>
        <option value='3' <?php selected( $options['wpprss_select_range_field'], 3 ); ?>>Monthly</option>
        <option value='4' <?php selected( $options['wpprss_select_range_field'], 4 ); ?>>All Time</option>
	</select>
<?php
}


function wpprss_select_freshness_field_render() { 
	$options = get_option( 'wpprss_settings' );
	?>
	<select name='wpprss_settings[wpprss_select_freshness_field]'>
		<option value='1' <?php selected( $options['wpprss_select_freshness_field'], 1 ); ?>>Enable</option>
		<option value='2' <?php selected( $options['wpprss_select_freshness_field'], 2 ); ?>>Disable</option>
	</select>
<?php
}


function wpprss_select_orderby_field_render() { 
	$options = get_option( 'wpprss_settings' );
	?>
	<select name='wpprss_settings[wpprss_select_orderby_field]'>
		<option value='1' <?php selected( $options['wpprss_select_orderby_field'], 1 ); ?>>Comments</option>
		<option value='2' <?php selected( $options['wpprss_select_orderby_field'], 2 ); ?>>Total Views</option>
        <option value='3' <?php selected( $options['wpprss_select_orderby_field'], 3 ); ?>>Average Views</option>
	</select>
<?php
}


function wpprss_settings_section_callback() { 
	echo __( 'Settings for the most popular posts output within the custom RSS feed located at: ', 'wpprss' );
    echo '<a href="' . get_bloginfo('url') . '/feed/popular/" target="_blank">' . get_bloginfo('url') . '/feed/popular/' . '</a>. If feed displays 404, please save permalinks to refresh the link structure. You can refresh permalinks <a href="' . get_bloginfo('url') . '/wp-admin/options-permalink.php" target="_blank">HERE</a>.';
}

function wpprss_options_page() { 
	?>
<div class="wpp-rss-admin-page">
	<form action='options.php' method='post'>
		<h2><img src="<?php echo plugin_dir_url(__FILE__) . 'admin/wpp-rss-logo.png'; ?>" /></h2>
		
		<?php
		settings_fields( 'wppFeedPage' );
		do_settings_sections( 'wppFeedPage' );
		submit_button();
		?>
	</form>
    <hr/>
    <h3>RSS Shortcode Output &mdash; Instructions</h3>
    <div class="wpp-rss-admin-section">
    <p>In order to embed RSS feeds, please use the shortcode. The base shortcode requires and URL setting and is like this:</p>
    <code>[wpp_rss url="http://feeds.feedburner.com/TechCrunch/"]</code>
    <p>Add additonal RSS feeds by placing commas between the feed URLs, like so:</p>
    <code>[aggregation_rss url="http://feeds.feedburner.com/TechCrunch/,http://feeds.feedburner.com/TechCrunch/"]</code>
    </div><div class="wpp-rss-admin-section">
    <p>Additional shortcode properties include:</p>
    <ul>
        <li>type - Specifies the output structure of the RSS. (reg for regular output, emailfeat for featured spot in email output, and email for list/top stories spot in email template). <em>NOTE: Using type="emailfeat" or type="email" removes all other options.</em></li>
        <li>items – Number of items from the feed you wish to fetch. Default is 10.</li>
        <li>orderby – Order the items by date or reverse date (date or reverse_date).</li>
        <li>title – Choose whether to display the title or not (true or false, defaults to true).</li>
        <li>excerpt – How many words you want to display for each item’s excerpt (default is 0 or infinite, use ‘none’ to remove the excerpt).</li>
        <li>read_more – Choose whether to display a read more link or not (true or false, defaults to true).</li>
        <li>new_window – Choose whether to open the title link and read more link in a new window (true or false, defaults to true).</li>
        <li>thumbnail – Choose whether or not you want to display a thumbnail, and if so, what size you want it to be (true or false, defaults to true, inserting a number will change the size, default is 150).</li>
        <li>source – Choose whether to display the source or note (true or false, defaults to true).</li>
        <li>date – Choose whether to display the publish date of the post or not (true or false, defaults to true).</li>
        <li>cache – Choose how long you want the feed to cache in seconds(default is 43200, which is 12 hours).</li>
    </ul>
</div>
<div class="wpp-rss-admin-section">
    <p>Full example:</p>
    <code>[wpp_rss type="reg" url="http://feeds.feedburner.com/TechCrunch/,http://feeds.feedburner.com/TechCrunch/" items="10" excerpt="50" read_more="true" new_window="true" thumbnail="200" cache="7200"]</code>
</div>
<div class="wpp-rss-plugin-footer">
    <p>Copyright &copy; <?php echo date('Y'); ?> <a href="//wpdevelopers.com" target="_blank">WP Developers</a> &amp; <a href="//libertyalliance.com" target="_blank">Liberty Alliance</a>. All Rights Reserved.</p>
</div>
</div>
	<?php
}


/**********
Options Loaded
**********/

// Get Options
$wpprssoptions = get_option( 'wpprss_settings' );

// Assign Options to Variables
$wpprsslimit        = $wpprssoptions['wpprss_text_limit_field'];
$wpprssrange        = $wpprssoptions['wpprss_select_range_field'];
$wpprssfreshness    = $wpprssoptions['wpprss_select_freshness_field'];
$wpprssorderby      = $wpprssoptions['wpprss_select_orderby_field'];


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
            $date = get_the_date( 'r', $popular->id );
            $authid = get_post_field( 'post_author', $popular->id );
            $author = get_the_author_meta( 'display_name', $authid );
            $wppexcerpt = wpdev_wpp_rss_get_excerpt_by_id( $popular->id ); // Excerpt placeholder
            $image = wp_get_attachment_image_src(get_post_thumbnail_id( $popular->id ), 'full' );
            $imagereplace = preg_replace('/-\d+[Xx]\d+\./', '.', $image[0]);
            $pathinfo = pathinfo($image[0]);

            $output .= '<item>';
            $output .= '<title>' . $title . '</title>';
            $output .= '<link>' . $link . '</link>';
            $output .= '<pubDate>' . $date . '</pubDate>';
            $output .= '<dc:creator><![CDATA[' . $author . ']]></dc:creator>';
            $output .= '<enclosure url="' . $imagereplace . '" type="image/' . $pathinfo['extension'] . '" />';
            $output .= '<guid isPermaLink=\'true\'>' . $link . '</guid>';
            $output .= '<description><![CDATA[<img src="' . $imagereplace . '" />' . $wppexcerpt . ']]></description>';
            $output .= '<content:encoded><![CDATA[<img src="' . $imagereplace . '" />' . $wppexcerpt . ']]></content:encoded>';
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


/**********
RSS Output for Newsletter - Shortcode
**********/

// Main Shortcode
add_shortcode( 'wpp_rss', 'wpp_rss_function' );

function wpp_rss_function( $atts, $content = null ){
	extract( shortcode_atts( array(
        'type' => 'reg',
		'url' => '#',
		'items' => '10',
        'itemauthor' => 'false',
        'orderby' => 'default',
        'title' => 'true',
		'excerpt' => '0',
		'read_more' => 'true',
		'new_window' => 'true',
        'thumbnail' => 'false',
        'randomthumbnail' => 'false',
        'source' => 'true',
        'date' => 'true',
        'cache' => '43200'
	), $atts ) );

    update_option( 'wp_rss_cache', $cache );

    //multiple urls
    $urls = explode(',', $url);

    add_filter( 'wp_feed_cache_transient_lifetime', 'wpp_rss_cache' );
    
    $rss = fetch_feed( $urls );

    remove_filter( 'wp_feed_cache_transient_lifetime', 'wpp_rss_cache' );

    if ( ! is_wp_error( $rss ) ) :

        if ($orderby == 'date' || $orderby == 'date_reverse') {
            $rss->enable_order_by_date(true);
        }
        $maxitems = $rss->get_item_quantity( $items ); 
        $rss_items = $rss->get_items( 0, $maxitems );
        if ( $new_window != 'false' ) {
            $newWindowOutput = 'target="_blank" ';
        } else {
            $newWindowOutput = NULL;
        }

        if ($orderby == 'date_reverse') {
            $rss_items = array_reverse($rss_items);
        }

    endif;
    
    // Shortcode Output
    if($type == 'reg') {
        $output = '<div class="post_item">';
            $output .= '<ul class="post_item_list">';
                if ( !isset($maxitems) ) : 
                    $output .= '<li>' . _e( 'No items', 'wpp-rss-retriever' ) . '</li>';
                else : 
                    //loop through each feed item and display each item.
                    foreach ( $rss_items as $item ) :
                        //variables
                        $content = $item->get_content();
                        $the_title = $item->get_title();
                        $enclosure = $item->get_enclosure();

                        //build output
                        $output .= '<li class="post_item_item"><div class="post_item_item_wrapper">';
                            //random thumbnail
                            $randnums = range(1,$items);
                            $maxnums = $items * 0.2;
                            shuffle($randnums);
                            $maximumnums = round($maxnums);
                            $arraynums = array_slice($randnums, 0, $maximumnums);


                            if ($randomthumbnail != 'false' && $enclosure && in_array($postcount, $arraynums)) {
                                $thumbnail_image = $enclosure->get_thumbnail();                     
                                if ($thumbnail_image) {
                                    //use thumbnail image if it exists
                                    $resize = wpp_rss_resize_thumbnail($thumbnail);
                                    $class = wpp_rss_get_image_class($thumbnail_image);
                                    $output .= '<div class="post_item_image"' . $resize . '><a ' . $newWindowOutput . ' href="' . esc_url( $item->get_permalink() ) . '"><img' . $class . ' src="' . $thumbnail_image . '" alt="' . $title . '"></a></div>';
                                } else {
                                    //if not than find and use first image in content
                                    preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $first_image);
                                    if ($first_image){    
                                        $resize = wpp_rss_resize_thumbnail($thumbnail);                                
                                        $class = wpp_rss_get_image_class($first_image["src"]);
                                        $output .= '<div class="post_item_image"' . $resize . '><a ' . $newWindowOutput . ' href="' . esc_url( $item->get_permalink() ) . '"><img' . $class . ' src="' . $first_image["src"] . '" alt="' . $title . '"></a></div>';
                                    }
                                }
                                $hasrandthumbnail = 'wpdev-has-thumb';
                            } else {
                                $hasrandthumbnail = '';
                            }

                            //title
                            if ($title == 'true') {
                                $output .= '<a class="post_item_title ' . $hasrandthumbnail . '" ' . $newWindowOutput . 'href="' . esc_url( $item->get_permalink() ) . '"
                                    title="' . $the_title . '"><h1>';
                                    $output .= $the_title;
                                $output .= '</h1></a>';   
                            }
                            //thumbnail
                            if ($thumbnail != 'false' && $enclosure) {
                                $thumbnail_image = $enclosure->get_thumbnail();                     
                                if ($thumbnail_image) {
                                    //use thumbnail image if it exists
                                    $resize = wpp_rss_resize_thumbnail($thumbnail);
                                    $class = wpp_rss_get_image_class($thumbnail_image);
                                    $output .= '<div class="post_item_image"' . $resize . '><a ' . $newWindowOutput . ' href="' . esc_url( $item->get_permalink() ) . '"><img' . $class . ' src="' . $thumbnail_image . '" alt="' . $title . '"></a></div>';
                                } else {
                                    //if not than find and use first image in content
                                    preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $first_image);
                                    if ($first_image){    
                                        $resize = wpp_rss_resize_thumbnail($thumbnail);                                
                                        $class = wpp_rss_get_image_class($first_image["src"]);
                                        $output .= '<div class="post_item_image"' . $resize . '><a ' . $newWindowOutput . ' href="' . esc_url( $item->get_permalink() ) . '"><img' . $class . ' src="' . $first_image["src"] . '" alt="' . $title . '"></a></div>';
                                    }
                                }
                            }
                            //content
                            $output .= '<div class="post_item_container">';
                            if ( $excerpt != 'none' ) {
                                if ( $excerpt > 0 ) {
                                    $output .= esc_html(implode(' ', array_slice(explode(' ', strip_tags($content)), 0, $excerpt))) . "...";
                                } else {
                                    $output .= $content;
                                }
                                if( $read_more == 'true' ) {
                                    $output .= ' <div class="post_item_readmore_cont"><a class="post_item_readmore" ' . $newWindowOutput . 'href="' . esc_url( $item->get_permalink() ) . '"
                                            title="' . sprintf( __( 'Posted %s', 'wpp-rss-retriever' ), $item->get_date('j F Y | g:i a') ) . '">';
                                            $output .= __( 'Read more <i class="fa fa-angle-right"></i>', 'wpp-rss-retriever' );
                                    $output .= '</a></div>';
                                }
                            }
                            //metadata
                            if ($source == 'true' || $date == 'true') {
                                $output .= '<div class="post_item_metadata">';
                                    $source_title = $item->get_feed()->get_title();
                                    $time = $item->get_date('F j, Y - g:i a');
                                    if ($source == 'true' && $source_title) {
                                        $output .= '<span class="post_item_source">' . sprintf( __( 'Source: %s', 'wpp-rss-retriever' ), $source_title ) . '</span>';
                                    }
                                    if ($source == 'true' && $date == 'true') {
                                        $output .= '';
                                    }
                                    if ($date == 'true' && $time) {
                                        $output .= '<span class="post_item_date">' . sprintf( __( 'Published: %s', 'wpp-rss-retriever' ), $time ) . '</span>';
                                    }
                                    if ($itemauthor == 'true') {
                                        $author = $item->get_author();
                                        $authorname = ' | ' . $author->get_name();
                                        $output .= $authorname;
                                    }
                                $output .= '</div>';
                            }

                        $output .= '</div></div></li>';
                    endforeach;
                endif;
            $output .= '</ul>';
        $output .= '</div>';
    } elseif($type == 'emailfeat') {
        $output = '<table border="0" cellpadding="0" cellspacing="0" class="columns-container">';
                if ( !isset($maxitems) ) : 
                    $output .= '<tr>' . _e( 'No items', 'wpp-rss-retriever' ) . '</tr>';
                else : 
                    //loop through each feed item and display each item.
                    foreach ( $rss_items as $item ) :
                        //variables
                        $content = $item->get_content();
                        $the_title = $item->get_title();
                        $enclosure = $item->get_enclosure();

                        //build output
                        $output .= '<tr>';

                            //title
                                $output .= '<td class="force-col" style="padding-right: 20px;" valign="top"><table border="0" cellspacing="0" cellpadding="0" width="324" align="left" class="featured"><tr><td align="left" valign="top" style="font-size:28px; line-height: 32px; font-family: Arial, sans-serif; padding-bottom: 30px;"><br>';
                                $output .= '<a href="' . esc_url($item->get_permalink()) . '" title="' . $the_title . '" style="font-weight:bold">' . $the_title . '</a>';
                                $output .= '<br><br>';
                                $output .= '<a href="' . esc_url($item->get_permalink()) . '" title="' . $the_title . '" style=" line-height: 16px; font-size: 16px; font-style: italic; font-family: Arial, sans-serif; text-decoration: none; border: 2px solid; padding: 8px; border-radius: 3px;">Read this article</a>';
                                $output .= '<br></td></tr></table></td>';

        
                            //thumbnail
                            if ($thumbnail != 'false' && $enclosure) {
                                $thumbnail_image = $enclosure->get_thumbnail();                     
                                if ($thumbnail_image) {
                                    //use thumbnail image if it exists
                                    $resize = wpp_rss_resize_thumbnail($thumbnail);
                                    $class = wpp_rss_get_image_class($thumbnail_image);
                                    $output .= '<td class="force-col"  valign="top"><table border="0" cellspacing="0" cellpadding="0" width="324" align="right" class="featured" id="featured-last"><tr><td align="left" valign="top" style="font-size:13px; line-height: 20px; font-family: Arial, sans-serif;">';
                                    $output .= '<a href="' . esc_url($item->get_permalink()) . '"><img src="' . $thumbnail_image . '" alt="' . $the_title . '" border="0" hspace="0" vspace="0" style="vertical-align:top; max-width: 324px;" class="emailimg"></a>';
                                    $output .= '<br></td></tr></table></td>';
                                } else {
                                    //if not than find and use first image in content
                                    preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $first_image);
                                    if ($first_image){    
                                        $resize = wpp_rss_resize_thumbnail($thumbnail);                                
                                        $class = wpp_rss_get_image_class($first_image["src"]);
                                        $output .= '<td class="force-col"  valign="top"><table border="0" cellspacing="0" cellpadding="0" width="324" align="right" class="featured" id="featured-last"><tr><td align="left" valign="top" style="font-size:13px; line-height: 20px; font-family: Arial, sans-serif;">';
                                    $output .= '<a href="' . esc_url($item->get_permalink()) . '"><img src="' . $first_image["src"] . '" alt="' . $the_title . '" border="0" hspace="0" vspace="0" style="vertical-align:top; max-width: 324px;" class="emailimg"></a>';
                                    $output .= '<br></td></tr></table></td>';
                                    }
                                }
                            }

                        $output .= '</tr>';
                    endforeach;
                endif;
        $output .= '</table>';
    } elseif($type == 'email') {
                if ( !isset($maxitems) ) : 
                    $output .= '<tr>' . _e( 'No items', 'wpp-rss-retriever' ) . '</tr>';
                else : 
                    //loop through each feed item and display each item.
                    foreach ( $rss_items as $item ) :
                        //variables
                        $content = $item->get_content();
                        $the_title = $item->get_title();
                        $enclosure = $item->get_enclosure();

                        //build output
                        $output .= '<tr>';

                            //title
                                $output .= '<td align="left" valign="top" style="font-size:22px; line-height: 26px; font-family: Arial, sans-serif; padding-bottom: 30px;">';
                                $output .= '<a href="' . esc_url($item->get_permalink()) . '" title="' . $the_title . '" style="font-weight:bold; font-size:22px; line-height: 26px; font-family: Arial, sans-serif;">' . $the_title . '</a><br>';
                                $output .= '</td>';
                    endforeach;
                endif;
    }
    return $output;
}
add_option( 'wp_rss_cache', 43200 );

function wpp_rss_cache() {
    //change the default feed cache
    $cache = get_option( 'wp_rss_cache', 43200 );
    return $cache;
}

function wpp_rss_get_image_class($image_src) {
    list($width, $height) = getimagesize($image_src);
    if ($height > $width) {
        $class = ' class="portrait"';
    } else {
        $class = '';
    }
    return $class;
}

function wpp_rss_resize_thumbnail($thumbnail) {
    if (is_numeric($thumbnail)){
        $resize = ' style="width:' . $thumbnail . 'px; height:' . $thumbnail . 'px;"';
    } else {
        $resize = '';
    }
    return $resize;
}
