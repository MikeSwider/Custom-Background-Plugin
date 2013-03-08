<?php
/*
Plugin Name: Custom-Background
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: The Plugin's Version Number, e.g.: 1.0
Author: Name Of The Plugin Author
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

add_action('wp_head', 'updateBG');
	
function updateBG(){
	
	global $post;

	$post_id = $post->ID;
	$color = get_post_meta($post_id, 'BackgroundColor', 'true' );
	$template_name = get_post_meta( $post_id, '_wp_page_template', true );
	$newtemplate_name =  str_replace('.php', '', $template_name);
	echo'<style>
		body.page-template-'.$newtemplate_name.'-php{
		background-color: '.$color.';}
	
	 </style>';
}


add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );
function mw_enqueue_color_picker( $hook_suffix ) {
    // first check that $hook_suffix is appropriate for your admin page
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'custom-color', plugins_url('custom-background.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}


//Adds color picker

add_action( 'add_meta_boxes', 'CustomBackground_add_custom_box' );

// backwards compatible (before WP 3.0)
// add_action( 'admin_init', 'CustomBackground_add_custom_box', 1 );

/* Do something with the data entered */
add_action( 'save_post', 'CustomBackground_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function CustomBackground_add_custom_box() {
    $screens = array( 'post', 'page' );
    foreach ($screens as $screen) {
        add_meta_box(
            'CustomBackground_sectionid',
            __( 'Custom Background Color', 'CustomBackground_textdomain' ),
            'CustomBackground_inner_custom_box',
            $screen,
            'side'
        );
    }
}

/* Prints the box content */
function CustomBackground_inner_custom_box( $post ) {
	
	global $post;
	
  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'CustomBackground_noncename' );

  // The actual fields for data entry
  // Use get_post_meta to retrieve an existing value from the database and use the value for the form
  $value = get_post_meta( $post->ID, $key = 'BackgroundColor', $single = true );
  echo '<label for="CustomBackground_new_field">';
       _e("Background Color", 'CustomBackground_textdomain' );
  echo '</label> ';
  echo '<input type="text" value="'.esc_attr($value).'" name="BackgroundColor" class="BackgroundColor-field" data-default-color="#ffffff" />';
}

/* When the post is saved, saves our custom data */
function CustomBackground_save_postdata( $post_id ) {
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !isset( $_POST['CustomBackground_noncename'] ) || !wp_verify_nonce( $_POST['CustomBackground_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data

  //if saving in a custom table, get post_ID
  $post_ID = $_POST['post_ID'];
  //sanitize user input
  $mydata = sanitize_text_field( $_POST['BackgroundColor'] );

  // Do something with $mydata 
  // either using 
  add_post_meta($post_ID, 'BackgroundColor', $mydata, true) or
   update_post_meta($post_ID, 'BackgroundColor', $mydata);
  // or a custom table (see Further Reading section below)  
 
};
?>