<?php
add_action('contextual_help', 'sfs_plugin_help', 10, 2);
function sfs_plugin_help( $old_help, $screen_id ) {
	
	if ($screen_id != 'settings_page_sfs_admin')
		return $old_help;
	
	$screen = get_current_screen();
	
	$screen->add_help_tab( array(
		'id'		=> 'sfs-general',
		'title'		=> 'Simple Feed Sorter',
		'content'	=> sfs_text_help_tab('sfs-general')
	) );
	
	$screen->add_help_tab( array(
		'id'		=> 'sfs-settings',
		'title'		=> __('Settings', 'simple-feed-sorter'),
		'content'	=> sfs_text_help_tab('sfs-settings')
	) );
 	
 	$screen->add_help_tab( array(
		'id'		=> 'sfs-shortcode',
		'title'		=> 'Shortcode',
		'content'	=> sfs_text_help_tab('sfs-shortcode')
	) );
	
	$screen->set_help_sidebar('
		  		<p><strong>' . __('For more information', 'simple-feed-sorter') . ' :</strong></p>
		  		<p><a href="http://www.geekpress.fr/wordpress/extension/simple-feed-sorter/">' . __('Documentation', 'simple-feed-sorter') . '</a></p>
		  ');
		/* <p><a href="">' . __('Premium Version', 'simple-feed-sorter') . '</a></p> */
 
	
 
}

function sfs_text_help_tab( $tabs = 'sfs-general' ) {
	
	if( $tabs == 'sfs-general' ) {
		ob_start(); ?>
		
		<p><?php _e('Simple Feed Sorter is a plugin which takes a list of Atom feed and merge them to make a regrouped list of feed defined by the number of item you want to display.', 'simple-feed-sorter'); ?></p>
		<p><?php _e('You have the possibility to get every feed you want if it matches to a correct URL. You can access to twitter feeds, and blogs, feeds, etc...', 'simple-feed-sorter'); ?></p>
		<p><span class="description"><?php _e('Example of a feed from a twitter account', 'simple-feed-sorter'); ?>  :</span><br/>
		http://api.twitter.com/1/statuses/user_timeline.<strong>atom</strong>?screen_name=<strong>peexeo</strong></p>
		
		<?php
		return ob_get_clean();
	}
	else if( $tabs == 'sfs-settings' ) {
		ob_start(); ?>
		
		<h3><?php _e('Settings', 'simple-feed-sorter'); ?></h3>
		<p><?php _e('You can display author, date and an icon as you want. You can also choose from the list of predefined theme to display the list with style.', 'simple-feed-sorter'); ?></p>
		
		<h3><?php _e('Feeds', 'simple-feed-sorter'); ?></h3>
		<p><?php _e('The name field allows you to write the name you want to display before the title entry.<br />The URL must be a valid URL in order to process the information. If it\'s not, the field you filled will be deleted. The URL must reference a valid RSS feed.', 'simple-feed-sorter'); ?></p>
		<p><span class="description"><?php _e('Here an example of valid feed', 'simple-feed-sorter'); ?> :</span><br/>
		<?php _e('Name', 'simple-feed-sorter'); ?>: <input type="text" value="Geekpress" disabled="disabled" /> URL: <input type="text" value="http://feeds.feedburner.com/geekpress-fr" disabled="disabled" /></p>
		
		<?php
		return ob_get_clean();	
	}
	else if( $tabs == 'sfs-shortcode' ) {
		ob_start(); ?>
		<p><?php _e('You can display your feed on any page or article using a shortcode : [SimpleFeed].', 'simple-feed-sorter'); ?></p>
		<h3><?php _e('Settings', 'simple-feed-sorter'); ?></h3>
		<p><?php _e('<strong>width</strong> : in px, the width of the container', 'simple-feed-sorter'); ?><br/>
		   <?php _e('<strong>items</strong> : the number if item to display in the container', 'simple-feed-sorter'); ?></p>
		
		<p><span class="description"><?php _e('Here an example of a valid shortcode', 'simple-feed-sorter'); ?> :</span><br/>
		   [SimpleFeed width="300px" items="15"]
		</p>
		
		<?php
		return ob_get_clean();	
	}
	
}