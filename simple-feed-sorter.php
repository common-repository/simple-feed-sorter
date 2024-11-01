<?php

/*
Plugin Name: Simple Feed Sorter
Plugin URI: http://www.geekpress.fr/wordpress/extension/simple-feed-sorter/
Description: This plugin takes a list of Atom feed and merge them to make a regrouped list of feed defined by the number of item you want to display.
Version: 1.1
Author: Jean-David, GeekPress & Peexeo
Author URI: http://www.geekpress.fr
Text Domain: simple-feed-sorter
Domain Path: /languages/

	Copyright 2011 Jean-David Daviet & Jonathan Buttigieg
	
	This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

require_once(plugin_dir_path( __FILE__ ).'/admin/contextual-help.php');
require_once(plugin_dir_path( __FILE__ ).'/inc/widget.php');

class Simple_Feed_Sorter {

	private $options = array(); // Set $options in array
	private $content = array(); // Set $content in array
	private $fields = array(); // Set $fields in array
	private $settings = array(); // Set $setting in array
	
	function __construct(){
		
		// Add translations
		if (function_exists('load_plugin_textdomain'))
			load_plugin_textdomain('simple-feed-sorter', false, dirname(plugin_basename( __FILE__ )) . '/languages/');
		
		
		// Add menu page
		add_action('admin_menu', array(&$this, 'add_submenu'));

		// Check if they empty fields
		$this->check_empty_fields();
		
		// load the values recorded
		$this->options = get_option('_sfs_options');
		$this->fields = get_option('_sfs_feeds');
		$this->load_css();
		$this->load_feeds();
		$this->sort_by_date();
		
		// Shortocde API
		add_shortcode('SimpleFeed', array(&$this, 'display_shortcode'));
		
		// Settings API
		add_action('admin_init', array(&$this, 'settings_api_init'));
		
		//tell wp what to do when plugin is activated
		if (function_exists('register_activation_hook') && !$this->fields)
			register_activation_hook(__FILE__, array(&$this, 'activate'));	
	}
	
	
	/**
	 * method activate
	 *
	 * This function is called when plugin is activated.
	 *
	 * @since 1.0
	**/
	
	function activate(){
		$options = array(
			"author" 		=>  "on",
			"date" 			=> "on", 
			"image" 		=> "on",
			"css" 			=> "sweet",
			"total_items" 	=> 5,
			"words"			=> 30
		);
		
		if( !is_array( $this->options ))
			update_option('_sfs_options', $options);
	}
	
	
	/**
	* method add_submenu
	*
	* since 1.0
	*/
	function add_submenu(){
		add_options_page( 'Simple Feed Sorter', 'Simple Feed Sorter', 'manage_options', 'sfs_admin', array(&$this, 'display_page') );
	}
	
	
	/**
	 * method load_css
	 *
	 * This function insert file css in the <head>
	 *
	 * @since 1.0
	**/
	function load_css() {
		global $pagenow;
		if( !is_admin() || ( is_admin() && $pagenow == 'options-general.php' && $_GET['page'] == 'sfs_admin' ) ) {		
			if(isset($this->options['css']) && $this->options['css'] != 'none' && $this->options['css'] != ""){
					$css = WP_PLUGIN_URL.'/'.dirname(plugin_basename( __FILE__ )).'/css/simple-feed-sorter-'.$this->options['css'].'.css';	
					wp_register_style('simple-feed-sorter-sweet', $css, '', '1.0');
					wp_enqueue_style( 'simple-feed-sorter-sweet');
			}
		}
	}
	
	
	/**
	 * method load_feeds
	 *
	 *
	 * @since 1.0
	**/
	function load_feeds()
	{
		if( !empty($this->fields) )
			foreach( $this->fields as $feed )
				$this->process_items($feed['name'], $feed['url']);
	}
	
	
	/**
	 * method process_items
	 *
	 * @since 1.0
	**/
	function process_items($name , $feed)
	{
		$rss = fetch_feed($feed);
		if (!is_wp_error($rss)){
			
			$rss_items = $rss->get_items(0);
			
			foreach( $rss_items as $item ){
				
				$this->content[] = array (
					"name"		=> $name,
					"date" 		=> $item->get_date(),
					"site" 		=> $feed,
					"link" 		=> $item->get_permalink(),
					"content" 	=> $item->get_content(),
					"title" 	=> $item->get_title(),
					"twitter"	=> (preg_match("/twitter.com/i", $feed) > 0),
				);
			}
		}
	}
	
	
	/*
	 * method get_settings
	 *
	 * @since 1.0
	*/
	function get_settings()
	{
		// Check if $this->fields is not empty
		if( !$this->fields ) return;
		
		foreach( $this->fields as $key => $row )
		{

			$this->settings[$key] = array(
				'name'     	=> $row['name'],
				'url'		=> $row['url']
			);
		}
		
	}
	
	
	/**
	 * method display_settings
	 *
	 * HTML output for text field
	 *
	 * @since 1.0
	 */
	function display_settings( $args = array() ) 
	{
		extract($args);
 		
 		global $i;
 		$i = ( $i === NULL ) ? 0 : (int)$i;
 		
 		echo '<div><label for="name_'. $i .'">'. __('Name','simple-feed-sorter') .' :</label> <input class="regular-text" type="text" id="name_' . $i . '" name="_sfs_feeds['. $i .'][name]" value="' . esc_attr( $name ) . '" style="width:100px; margin: 0 10px 10px 0" />';
 		echo '<label for="feed_'. $i .'">'. __('URL','simple-feed-sorter') .' :</label> <input class="regular-text" type="text" id="feed_' . $i . '" name="_sfs_feeds['. $i .'][url]" value="' . esc_attr( $url ) . '" style="width:200px; margin: 0 10px 10px 0" />';
 		echo '<a href="#" class="help deleteRow">' . __('Remove', 'simple-feed-sorter') . '</a><br/></div>';
 		
 		$i++;
	}


	/**
	 * method settings_api_init
	 *
	 * Register settings with the WP Settings API
	 *
	 * @since 1.0
	 */	
	function settings_api_init() 
	{
		register_setting('_simple_feed_sorter', '_sfs_feeds' , array(&$this, 'validate_settings_feeds'));
		register_setting('_simple_feed_sorter', '_sfs_options', array(&$this, 'validate_settings_options'));
		add_settings_section('general', __('New Feed', 'simple-feed-sorter'), create_function('' , 'return false;'), __FILE__);
		
		// Get the configuration of fields
		$this->get_settings();
	}
	
	
	/**
	*  method check_empty_fields
	*
	* @since 1.0
	*/
	function check_empty_fields() {
		
		$fields = get_option( '_sfs_feeds' );
		if( !$fields ) return false;
		
		foreach( $fields as $key => $row ) {
			if( empty($row['name']) || preg_match('#(((https?|ftp)://(w{3}\.)?)(?<!www)(\w+-?)*\.([a-z]{2,4})(/[a-zA-Z0-9_\?\=-]+)?)#', $row['url']) == 0 )
				unset($fields[$key]);
		}
		
		// Update the new values
		update_option('_sfs_feeds', $fields);
	}
	
	
	/**
	*  method validate_settings_feeds
	*
	* @since 1.0
	*/
	function validate_settings_feeds($input)
	{
		for ( $i=0; $i <= count($input)-1; $i++ ) {
			$input[$i]['name'] = wp_strip_all_tags($input[$i]['name']);			
		}
		return $input;
	}

	
	/**
	*  method validate_settings_options
	*
	* @since 1.0
	*/
	function validate_settings_options($input)
	{
		$input['total_items'] = ( empty( $input['total_items']) ) ? 5 : (int)$input['total_items'];
		$input['words'] = ( empty($input['words']) ) ? null : (int)$input['words'] ;
		return $input;
	}
	
	
	/**
	*  method sort_by_date
	*
	* @since 1.0
	*/
	function sort_by_date()
	{
		usort($this->content, array(&$this, 'compare'));
	}
	
	
	/**
	*  method sort_by_date
	*
	* @since 1.0.1
	*/
	function compare( $a, $b ) {
		$aSort = strtotime($a['date']);
		$bSort = strtotime($b['date']);
		if($aSort === $bSort) return 0;
		return ($aSort > $bSort) ? -1 : 1;
	}
	
	
	/**
	*  method display_title
	*
	* @since 1.0
	*/
	function display_title( $item )
	{
		switch($item["twitter"]){
			case true: // if it's twitter
				return "@".$item["name"]; // display author
				break;
			case false: // else, display the title of item
				return $item["title"];
				break;
		}
	}


	/**
	*  method display_name
	*
	* @since 1.0
	*/
	function display_name( $item )
	{
		switch($item["twitter"]){
			case true:
				return false;
				break;
			default :
			if($this->options['author'] == 'on'){
				if($item["name"] != null){					
					return $item["name"] . ' : ';
				}
			}
		}
	}
	
	/**
	*  method modify_content
	*
	* @since 1.0
	*/
	function modify_content($item)
	{
		
		if( $item['twitter'] ) {
			$item["content"] = preg_replace('/#([a-zA-Z0-9éèâîàôù]+)/', '<a href="http://www.twitter.com/search/%23$1" rel="external">#$1</a>', $item["content"]);	
			$item["content"] = preg_replace('/@([a-zA-Z0-9_]+)/', '<a href="http://www.twitter.com/$1" rel="external">@$1</a>', $item["content"]);
			$item['content'] = preg_replace('#^[a-zA-Z0-9_ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ]*: +#', '', $item['content']);
		}
		
		$item['content'] = strip_tags($item['content']);
		$item['content'] = preg_split("# #", $item["content"]);
		
		if(count($item['content']) > $this->options['words'] ) {
			$item['content'] = array_slice($item["content"], 0, $this->options['words']);
			$item['content'] = implode(" ",$item['content']) . ' ...';
		}
		else {
			$item['content'] = implode(" ",$item['content']);
		}
		
		$item["content"] = preg_replace('#(((https?|ftp)://(w{3}\.)?)(?<!www)(\w+-?)*\.([a-z]{2,4})(/[a-zA-Z0-9_\?\=-]+)?)#', '<a href="$1" rel="external">$1</a>', $item["content"]);
		
		return $item["content"];
	}
	
	
	/*
	* get_options_total_items
	*
	* allow to widget.php to access to the total_items variable
	*
	* since 1.0
	*/
	function get_options_total_items()
	{
		return $this->options['total_items']; 
	}
	
	
	/**
	* method display_shortcode
	*
	* since 1.0
	*/
	function display_shortcode($atts){
		
		$this->load_css();
		$this->load_feeds();
		
		$atts = shortcode_atts( 
			array(
				  'width'	=> '',
				  'items'	=> '',
			)
		, $atts);
		extract( $atts );
		
		if ( $items == '' ) $items = $this->options['total_items'];

		return $this->display_feeds_by_shortcode($width, $items);
	}
	
	
	/**
	* method display_feeds_by_shortcode
	*
	* since 1.0
	*/
	function display_feeds_by_shortcode($width, $items = '')
	{
		$items = ( intval($items) == 0 ) ? count($this->content) : (int)$items;		
		$items = ( count($this->content) < $items ) ? count($this->content) : $items;

		if( $items >= 1 ){ ?>
			
			<div class="simple-feed-sorter">
				<ul <?php if($width != '') echo 'style="width:' . esc_attr($width) . ';"'?>>	
				<?php
				for( $i=0; $i < $items; $i++ ) {
					/*
$this->content[$i]['content'] = preg_replace('#^[a-zA-Z0-9_ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ]*: +#', '', $this->content[$i]['content']);
					$this->content[$i]['content'] = preg_split("# #", $this->content[$i]["content"]);
					$this->content[$i]['content'] = array_slice($this->content[$i]["content"], 0, $this->options['words']);
					$this->content[$i]['content'] = implode(" ",$this->content[$i]['content']);
					$this->content[$i]['content'] .= ' ...';
*/
				?>
					
					<li class="<?php if( $i % 2 !=0 ) echo 'odd'; else echo 'even'; ?> <?php echo sanitize_key($this->content[$i]["name"]); ?> <?php if( $this->content[$i]["twitter"] ) echo 'twitter'; ?>">
						<a href="<?php echo $this->content[$i]["link"];?>"
							<?php echo 'class="author"';?>><?php echo $this->display_name($this->content[$i]) . $this->display_title($this->content[$i]);?>
							
							<?php 
							
							if($this->options['date'] == 'on') {
								
								$date = sprintf( '%s ' . __('\a\t', 'simple-feed-sorter') . ' %s',
										get_option('date_format'),
										get_option('time_format'));
								
								echo '<span class="sfs-date"> - ' . date_i18n( $date, strtotime($this->content[$i]["date"])) . '</span>';
							}
							
							if($this->options['image'] == 'on') {
							
								$type = ( $this->content[$i]['twitter'] ) ? 'twitter' : 'rss';
								echo '<span class="sfs-image">
									<img src="' . plugin_dir_url( __FILE__ ).'/images/' . $type . '.png" alt="" />
								</span>';
							}
							
							?>
						</a>
						<p><?php echo $this->modify_content($this->content[$i]);?></p>
					</li>
				<?php
				}
				echo '</ul>';
			echo '</div>';
		}
	}
	
	
	/**
	*  method display_page
	*
	* @since 1.O
	*/
	function display_page()
	{ ?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Simple Feed Sorter</h2>
			<form method="post" action="options.php">
				<?php settings_fields('_simple_feed_sorter');?>
				
				<h3><?php _e('Settings', 'simple-feed-sorter'); ?></h3>
				<table class="form-table">
		    		<tr valign="top">
					
				    	<th scope="row">
					    	<label for="total_items"><?php _e('Total number of item to display', 'simple-feed-sorter');?></label>
						</th>
						<td>
							<input type="text" name="_sfs_options[total_items]" id="total_items" value="<?php echo $this->options['total_items'];?>"/>
							</td>
			    	</tr>
					<tr valign="top">
					
				    	<th scope="row">
					    	<label for="words"><?php _e('Total number of words to display', 'simple-feed-sorter');?></label>
						</th>
						<td>
							<input type="text" name="_sfs_options[words]" id="words" value="<?php echo $this->options['words'];?>"/>
							</td>
			    	</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Do you want to', 'simple-feed-sorter');?> :</th>
						<td>
							<fieldset>
								<label for="sfs-author">
									<input type="checkbox" <?php if($this->options['author'] == 'on' )echo'checked="checked"';?> id="sfs-author" name="_sfs_options[author]"> <?php _e('Display author\'s name before title', 'simple-feed-sorter'); ?>
								</label>
								<br />
								<label for="sfs-date">
									<input type="checkbox" <?php if($this->options['date'] == 'on' )echo'checked="checked"';?> id="sfs-date" name="_sfs_options[date]"> <?php _e('Display date of publication', 'simple-feed-sorter'); ?>
								</label>
								<br />
								<label for="sfs-image">
									<input type="checkbox" <?php if($this->options['image'] == 'on' )echo'checked="checked"';?> id="sfs-image" name="_sfs_options[image]"> <?php _e('Display icons of twitter or rss feed', 'simple-feed-sorter'); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Theme', 'simple-feed-sorter'); ?>  :</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php //_e('Themes', 'simple-feed-sorter');?>  :</span></legend>
									<select name="_sfs_options[css]" id="sfs-css">
										<option value="sweet" <?php selected($this->options['css'], "sweet") ?>>Sweet Theme</option>
										<option value="sober" <?php selected($this->options['css'], "sober") ?>>Sober Theme</option>
										<option value="none" <?php selected($this->options['css'], "none") ?>><?php _e('Do not use theme', 'simple-feed-sorter'); ?></option>
									</select>
							</fieldset>
						</td>
					</tr>

	    		</table>
		    	
		    	
		    	<h3><?php _e('Feeds', 'simple-feed-sorter'); ?></h3>
		    	<table class="form-table">
		    		<tr valign="top">
		    			<th scope="row"><label for="default"><?php _e('List of Feeds', 'simple-feed-sorter'); ?></label></th>
			    		<td>	
			    		<?php
			    		if( $this->fields ) {
			    		
				    		// Get the configuration of fields
							$this->get_settings();
							
							// Generate fields
							foreach ( $this->settings as $key => $setting ) {
								$this->display_settings( $setting );
							}
						}
						?>
			    		</td>
		    		</tr>
	    		</table>
			
				<p>
					<button id="addRow"  class="button button-secondary button-highlighted"><?php _e('Add new Feed', 'simple-feed-sorter');?></button>
				</p>
				<?php submit_button(__('Save Changes')); ?>
			</form>
		</div>
		
		<script type="text/javascript">
			jQuery(function($){
				/* Add field */
				$('#addRow').click(function() {
					
					var length = jQuery('.form-table:last div').length;
					
					/* Clone last input */
					$('.form-table:last tr td').append('<div><label for="name_'+length+'"><?php _e('Name', 'simple-feed-sorter'); ?> :</label> <input class="regular-text" id="name_'+length+'" name="_sfs_feeds['+length+'][name]" value="" type="text" style="width:100px; margin: 0 10px 10px 0"><label for="feed_'+length+'"><?php _e('URL', 'simple-feed-sorter'); ?> :</label> <input class="regular-text" id="feed_'+length+'" name="_sfs_feeds['+length+'][url]" value="" type="text" style="width:200px; margin: 0 10px 10px 0"> <a href="#" class="help deleteRow"><?php _e('Remove', 'simple-feed-sorter'); ?></a><br/></div>' );
					
					return false;
				});
				
				/* Delete Field */
			 	 jQuery('.deleteRow').live('click', function() {
			 		jQuery(this).siblings().remove();
					jQuery(this).remove();
			 		return false;
			 	 });
			});
		</script>
		
		<h3><?php _e('Preview', 'simple-feed-sorter'); ?></h3>
		<?php
		if( !empty($this->fields) ) {
			
			$items = ( count($this->content) < $this->options['total_items'] ) ? count($this->content) : $this->options['total_items'];
			
			$this->display_feeds_by_shortcode('300px', $items);
			
		}
	}
}

global $Simple_Feed_Sorter; $Simple_Feed_Sorter = new Simple_Feed_Sorter();