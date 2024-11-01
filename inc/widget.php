<?php
class Simple_Feed_Sorter_Widget extends WP_Widget {

	function Simple_Feed_Sorter_Widget() {  
		
		$options = array(
			"classname" => 'simple-feed-sorter-class',
			"description" => __('Display the feed defined in the sidebar', 'simple-feed-sorter')
		);
		$this->WP_Widget('imple-feed-sorter-widget', 'Simple Feed Sorter', $options);
	}

	public function widget( $args, $instance ) {
		extract($args);
		global $Simple_Feed_Sorter;
		
		echo $before_widget;
		echo $before_title . $instance['title'] . $after_title;
		$Simple_Feed_Sorter->display_feeds_by_shortcode($instance['width'], $Simple_Feed_Sorter->get_options_total_items());
		echo $after_widget;
	}
	
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = wp_strip_all_tags($new_instance['title']);
		$instance['width'] = wp_strip_all_tags($new_instance['width']);
		return $instance;
	}
		
	public function form( $instance ) {
		$defaults = array(
			'title'		=> __('Feeds', 'simple-feed-sorter'),
			'width' 	=> ''
		);
		$instance = wp_parse_args((array)$instance, $defaults);
		$title = $instance["title"];
		$width = $instance["width"];
	
		?>
		<p><?php _e('Title', 'simple-feed-sorter'); ?><input type="text" class="widefat" name="<?php echo esc_attr($this->get_field_name("title")); ?>" value="<?php echo esc_attr($title);?>"/></p>
		<p><?php _e('Width', 'simple-feed-sorter'); ?><input type="text" class="widefat" name="<?php echo esc_attr($this->get_field_name("width")); ?>" value="<?php echo esc_attr($width);?>"/></p>
		<?php 
	}
		
}

add_action( 'widgets_init', 'sfs_register_widgets');
function sfs_register_widgets() {
	register_widget('Simple_Feed_Sorter_Widget');
}
?>