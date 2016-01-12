<?php
/**
 * Template Name: Custom RSS Template - WPP RSS FEED
 */

// Establish Options
global $wpprsslimit;
global $wpprssrange;
global $wpprssfreshness;
global $wpprssorderby;

// Limit Settings
if($wpprsslimit) {
    $rssoutput .= ' limit="' . $limit .'"';
} else {
    $rssoutput .= ' limit="10"';
}

// Range Settings
if($wpprssrange == 1) {
    $rssoutput .= ' range="daily"';
} elseif($wpprssrange == 2) {
    $rssoutput .= ' range="weekly"';
} elseif($wpprssrange == 3) {
    $rssoutput .= ' range="monthly"';
} elseif($wpprssrange == 4) {
    $rssoutput .= ' range="all"';
} else {
    $rssoutput .= ' range="daily"';
}

// Freshness Settings
if($wpprssfreshness == 1) {
    $rssoutput .= ' freshness="1"';
} elseif($wpprssfreshness == 2) {
    $rssoutput .= ' freshness="0"';
} else {
    $rssoutput .= ' freshness="1"';
}

// Order By Settings
if($wpprssorderby == 1) {
    $rssoutput .= ' order_by="comments"';
} elseif($wpprssorderby == 2) {
    $rssoutput .= ' order_by="views"';
} elseif($wpprssorderby == 3) {
    $rssoutput .= ' order_by="avg"';
} else {
    $rssoutput .= ' order_by="views"';
}

header('Content-Type: '.feed_content_type('rss-http').'; charset='.get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
?>
<rss version="2.0"
        xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:wfw="http://wellformedweb.org/CommentAPI/"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:atom="http://www.w3.org/2005/Atom"
        xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
        xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
        <?php do_action('rss2_ns'); ?>>
<channel>
        <title><?php bloginfo_rss('name'); ?> - Feed</title>
        <atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
        <link><?php bloginfo_rss('url') ?></link>
        <description><?php bloginfo_rss('description') ?></description>
        <lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
        <language>en</language>
        <sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
        <sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
        <?php do_action('rss2_head'); ?>

        <?php echo do_shortcode('[wpp' . $rssoutput . ']'); ?>
                
</channel>
</rss>