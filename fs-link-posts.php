<?php
/*
Plugin Name: FS Link Posts
Plugin URI: http://github.com/flipstorm/fs-linked-posts/
Description: Allows you to manually link multiple posts with the post you're editing
Author: FlipStorm
Version: 0.1
Author URI: http://flipstorm.co.uk/

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

COMING SOON:
- Admin panel for changing some basic options
- Linking posts of different types
- More flexible rendering of linked posts
- Ordering of linked posts
*/

/*
	Plugin activation handler
*/
function fs_link_posts_activate() {
	// Create default settings
	if ( !get_option( 'fs_link_posts' ) ) {
		$defaults = array();
		
		// Only show posts of the current type or show all posts (regardless of type)
		$defaults[ 'match_post_type' ] = 1;
		
		update_option( 'fs_link_posts', $defaults );
	}
}

/*
	Delegate for the meta box
*/
function fs_link_posts_add_custom_box() {
	/*
		// TODO: Make it configurable in the admin
		$options = get_option ( 'fs_link_posts' );
		foreach ( $options [ 'show_for_post_types' ] as $postType ) ...
	*/
	
	foreach ( get_post_types( array( 'public' => true ), 'object' ) as $postType ) {
		add_meta_box( 'fs_link_posts', __( 'Linked ' . $postType->label, 'fs_link_posts_textdomain' ), 'fs_link_posts_meta_box_inner', $postType->name, 'side' );
	}
}

/*
	Build the meta box
*/
function fs_link_posts_meta_box_inner() {
	// Get the post ID that we're editing
	global $post_ID;
	
	$currentPostType = get_post_type( $post_ID );
	
	$options = get_option( 'fs_link_posts' );
	
	$args[ 'post_status' ] = 'publish';
	$args[ 'orderby' ] = 'title';
	
	if ( $post_ID ) {
		$args[ 'post__not_in' ] = array( $post_ID );
	}
	
	if ( $options[ 'match_post_type' ] ) {
		// Only query for posts that match the current post type
		$otherPostsStr = ' other ' . $currentPostType . 's';
		$args[ 'post_type' ] = $currentPostType;
	}
	else {
		// Query for posts of any type
		$otherPostsStr = ' other posts';
		$args[ 'post_type' ] = 'any';
	}
	
	echo '<p>Link this ' . $currentPostType . ' with ' . $otherPostsStr . '</p>';
	echo '<p>';
	
	$otherPosts = get_posts( $args );
	
	if ( $otherPosts ) {
		foreach ( $otherPosts as $otherPost ) {
			// List the posts already linked with this post, allow them to be removed
			if ( $post_ID && in_array( $otherPost->ID, get_post_meta( $post_ID, 'fs-linked-posts' ) ) ) {
				$checked = ' checked="checked"';
			}
			else {
				$checked = '';
			}
			
			echo '<label><input type="checkbox" name="fs_linked_posts[]" value="' . $otherPost->ID . '"' . $checked . ' /> ' . $otherPost->post_title . '</label><br />';
		}
	}
	else {
		echo 'No ' . $otherPostsStr;
	}
	
	echo '</p>';
	
	echo '<input type="hidden" name="fs_link_posts_noncename" id="fs_link_posts_noncename" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
}

/*
	Delegate for the options page
*/
function fs_link_posts_add_options_page() {
	add_options_page( 'Settings for FS Link Posts', 'Link Posts', 'manage_options', __FILE__, 'fs_link_posts_options_page' );
}

/*
	Build the options page for wp-admin
*/
function fs_link_posts_options_page() {
	$options = get_option( 'fs_link_posts' );
	
	if ( isset( $_POST[ 'fs_link_posts_options_submit' ] ) ) {
		$new_options = array();
		$new_options[ 'match_post_type' ] = attribute_escape( $_POST[ 'fs_link_posts_match_post_type' ] );

		update_option( 'fs_link_posts', $new_options );
		
		$options = $new_options;
		
		echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></div>';
	}
	
	echo $options[ 'match_post_type' ];
}


/*
	Save the changes when the post is saved
*/
function fs_link_posts_save_postdata( $post_id ) {
	if ( !wp_verify_nonce( $_POST[ 'fs_link_posts_noncename' ], plugin_basename( __FILE__ ) ) ) {
		return $post_id;
	}

	if ( !current_user_can( 'edit_pages', $post_id ) ) {
		return $post_id;
	}
	
	// Do not create a relationship with the revisions in WP 2.6
	if ( function_exists( 'wp_is_post_revision' ) ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}
	}
	
	// First, clear the existing references
	delete_post_meta( $post_id, 'fs-linked-posts' );
	
	// Then add the new ones
	if ( is_array( $_POST[ 'fs_linked_posts' ] ) ) {
		foreach ( $_POST[ 'fs_linked_posts' ] as $linkedPostId ) {
			add_post_meta( $post_id, 'fs-linked-posts', $linkedPostId );
		}
	}
}

/*
	Display a list of linked posts
*/
function fs_linked_posts( $post_id ) {
	$linkedPosts = get_post_meta( $post_id, 'fs-linked-posts' );
	
	if ( !empty( $linkedPosts ) ) {
		echo '<ul id="fs-linked-posts">';
		
		foreach ( $linkedPosts as $postID ) {
			echo '<li class="fs-linked-post">
				<a href="' . get_permalink( $postID ) . '">' . get_the_title( $postID ) . '</a>
			</li>';
		}
	}
}

// Finally, add all the hooks
register_activation_hook( __FILE__, 'fs_link_posts_activate' );

add_action( 'admin_menu', 'fs_link_posts_add_custom_box' );
//add_action( 'admin_menu', 'fs_link_posts_add_options_page' );
add_action( 'save_post', 'fs_link_posts_save_postdata' );