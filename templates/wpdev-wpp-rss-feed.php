<?php
/**
 * Template Name: Custom RSS Template - WPP RSS FEED
 */
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

        <?php echo do_shortcode('[wpp]'); ?>
    
		<?php // wpp_get_mostpopular( 'wpp_start="<!-- start list -->"&wpp_end="<!-- end list -->"&header_start=""&header_end=""&post_type=post&stats_author=1&excerpt_length=800&title_length=100&range=daily&stats_date_format="D, d M Y H:i:s T"&thumbnail_width=336&thumbnail_height=140&limit=10&order_by="avg"&post_html="<item><title>{text_title}</title><link>{url}</link><pubDate>{date}</pubDate><!--<dc:creator>{author}</dc:creator>--><guid isPermaLink=\'true\'>{url}</guid><description>{summary}</description><content:encoded>{summary}</content:encoded><!--<media:thumbnail url=\'{thumb_img}\' height=\'336\' width=\'140\' />--></item>"'); ?>
                
</channel>
</rss>