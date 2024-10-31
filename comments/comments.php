<?php

/* REGISTER POST TYPE _______________________________________________________________________________________________________ */

	function register_post_types(){
		register_post_type( 'rcwd-ngg', array( 'has_archive' => true ));		
	}
	add_action('init', 'register_post_types');

/* ___________________________________________________________________________________________________________________________ */
	
if (is_admin()){	

	global $pagenow;

	// FUNC: create custom post id and post meta on gallery creation __________________________________________________
	
		function rcwd_ngg_gallery_date_insert($gid){
			global $wpdb, $nggdb;
			$gallery = $nggdb->find_gallery($gid);
			$post_id = wp_insert_post(array( 'post_type' => 'rcwd-ngg', 'post_title' => $gallery->title, 'post_author' => $gallery->author, 'post_status' => 'publish', 'comment_status' => 'open' ));
			update_post_meta($post_id, '_rcwd_nggid', $gid);
		}
		add_action('ngg_created_new_gallery', 'rcwd_ngg_gallery_date_insert');

	// FUNC: check custom post id and post meta on gallery update _____________________________________________________
	
		function rcwd_ngg_gallery_date_update($gid, $post){
			global $wpdb, $nggdb;
			$post_id	= rcwd_get_postid_from_custom('_rcwd_nggid', $gid);
			$the_post 	= false;
			if($post_id !== false) $the_post = get_post($post_id);
			if( $post_id === false or !$the_post or ( $the_post->post_type != 'rcwd-ngg')){
				$gallery = $nggdb->find_gallery($gid);
				$post_id = wp_insert_post(array('post_type' => 'rcwd-ngg', 'post_title' => $gallery->title, 'post_author' => $gallery->author, 'post_status' => 'publish', 'comment_status' => 'open' ));
				update_post_meta($post_id, '_rcwd_nggid', $gid);	
			}
		}
		add_action('ngg_update_gallery', 'rcwd_ngg_gallery_date_update', 10, 2);
				
	// FUNC: add stylesheet to comments page _________________________________________________________________________________________________

		function rcwd_ngg_add_post_comments_stylesheet(){
			$stylesheet_url = (is_ssl()?str_replace('http://', 'https://', WP_PLUGIN_URL):WP_PLUGIN_URL).'/'.RCWDNGGCOMMENTS_DIRNAME.'/admin-style.css';
			wp_register_style(RCWDNGGCOMMENTS_DIRNAME, $stylesheet_url);
			wp_enqueue_style(RCWDNGGCOMMENTS_DIRNAME);
		}
		add_action('admin_print_styles-edit-comments.php', 'rcwd_ngg_add_post_comments_stylesheet');
	
	// FUNC: add comments columns header to comments page  ___________________________________________________________________________________

		function rcwd_ngg_add_comment_column_header($_columns){
			unset($_columns['comment']);
			unset($_columns['response']);
			$_columns['rcwd-comment'] 	= _x( 'Comment', 'column name' );
			$_columns['rcwd-response'] 	= _x( 'In Response To', 'column name' );
			return $_columns;
		}
		add_filter('manage_edit-comments_columns', 'rcwd_ngg_add_comment_column_header');
	
	// FUNC: add content to comment column _________________________________________________________________________________________________

		function rcwd_ngg_add_comment_column($_column_name){
			if ($_column_name != 'rcwd-response' and $_column_name != 'rcwd-comment') return;
			global $comment, $rcwd_ngg_base_page, $wp_list_table;
			switch($_column_name){
				case'rcwd-response':
					switch(get_post_type($comment->comment_post_ID)){
						case 'rcwd-ngg':
							$pending_count 	= get_pending_comments_num($comment->comment_post_ID);
							$gid 			= get_post_meta($comment->comment_post_ID, '_rcwd_nggid', true); 
							$gtitle			= rcwd_ngg_get_gallery_title($gid);
							if(current_user_can('edit_post')){			
								$post_link = "<a href='".wp_nonce_url($rcwd_ngg_base_page.'&amp;mode=edit&amp;gid='.$gid, 'ngg_editgallery')."'>".$gtitle.'</a>';
							}else{
								$post_link = $gtitle;
							}	
							echo '<div class="response-links"><span class="post-com-count-wrapper">';
							echo $post_link.'<br />';
							$wp_list_table->comments_bubble( $comment->comment_post_ID, $pending_count );
							echo '</span> ';
							echo '</div>';
							break;
						default:
							$wp_list_table->column_response($comment);
					}
					break;
				case'rcwd-comment':
					switch(get_post_type($comment->comment_post_ID)){
						case 'rcwd-ngg':	
							global $nggdb;
							$wp_list_table->column_comment($comment);
							break;
						default:				
						$wp_list_table->column_comment($comment);
					}
			}
		}	
		add_action('manage_comments_custom_column', 'rcwd_ngg_add_comment_column');
	
	// FUNC: set # to gallery comment link (TODO: find a way to get the correct album linked!!!)_______________________________________________________

		function rcwd_ngg_get_comment_link($link, $comment, $args){
			switch(get_post_type($comment->comment_post_ID)){
				case 'rcwd-ngg':
					$link = '#';
					break;
			}
			return $link;
		}
		add_filter('get_comment_link', 'rcwd_ngg_get_comment_link', 10, 3);
	
	/* ___________________________________________________________________________________________________________________________ */

	if( $pagenow == 'admin.php' and $_GET['page'] == 'nggallery-manage-gallery' and isset($_GET['gid']) ){

		// FUNC: add necessary js scripts to use comments in manage gallery page_______________________________________________________
		
			function rcwd_ngg_mg_init(){
				wp_enqueue_script('post');
				wp_enqueue_script('admin-comments');
				enqueue_comment_hotkeys_js();		
			}
			add_action( 'admin_init', 'rcwd_ngg_mg_init' );
		
		// FUNC: create comments box _______________________________________________________
		
			function rcwd_ngg_mg_add_post_comments_box(){
?>
				<script language="javascript" type="text/javascript">
				jQuery(document).ready(function(){		
					jQuery('#commentsdiv').insertAfter(jQuery('#gallerydiv')).show();
					postboxes.add_postbox_toggles('#commentsdiv');
				});		
				</script>
<?php
				global $wpdb;
				$gid 		= $_GET['gid'];
				$post_ID 	= rcwd_get_postid_from_custom('_rcwd_nggid', $gid);
				$total 		= $wpdb->get_var($wpdb->prepare("SELECT count(1) FROM $wpdb->comments WHERE comment_post_ID = '%d' AND ( comment_approved = '0' OR comment_approved = '1')", $post_ID));
			
				if ( 1 > $total ) {
					echo '<p>' . __('No comments yet.', 'nggcomments') . '</p>';
					return;
				}
?>
				<div class="postbox" id="commentsdiv" style="display:none">
					<div title="<?php _e('Click to toggle'); ?>" class="handlediv"><br></div><h3 class="hndle"><span><?php echo _e('Comments') ?></span></h3>
					<div class="inside">
						<input type="hidden" name="post_ID" id="post_ID" value="<?php echo $post_ID ?>" />
<?php 					wp_nonce_field( 'get-comments', 'add_comment_nonce', false ); 
						$wp_list_table = _get_list_table('WP_Post_Comments_List_Table');
						$wp_list_table->display( true );
?>		
						<p class="hide-if-no-js"><a href="#commentstatusdiv" id="show-comments" onclick="commentsBox.get(<?php echo $total; ?>);return false;"><?php _e('Show comments'); ?></a> <img class="waiting" style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" /></p>
<?php
						wp_comment_trashnotice();
						$hidden = get_hidden_meta_boxes('post');
?>	
					</div>
				</div>
<?php	
				if ( ! in_array('commentsdiv', $hidden) ) { ?>
					<script type="text/javascript">jQuery(document).ready(function(){commentsBox.get(<?php echo $total; ?>, 10);});</script>
<?php
				}
				wp_comment_reply();
			}
			add_action( 'admin_footer', 'rcwd_ngg_mg_add_post_comments_box' );
		}

}else{
	
	// FUNC: add comments box to gallery page _______________________________________________________
	
		function rcwd_ngg_gallery_output($out, $picturelist){
			global $rcwd_ngg_comments_are_displayed;
			if(!isset($rcwd_ngg_comments_are_displayed)) $rcwd_ngg_comments_are_displayed = false;
			echo $out;
			if($rcwd_ngg_comments_are_displayed === false) rcwd_ngg_add_comment_to_gallery();
		}
		add_filter('ngg_gallery_output', 'rcwd_ngg_gallery_output', 10, 2);

		function rcwd_ngg_gallery_object($gallery, $galleryID){
			global $rcwd_ngg_gallery, $gallery_ID;
			$rcwd_ngg_gallery 	= $gallery;
			$gallery_ID			= $gallery->ID;
			return $gallery;
		}
		add_filter('ngg_gallery_object', 'rcwd_ngg_gallery_object', 10, 2);
		

		function rcwd_ngg_comments_template($template){
			if(file_exists(STYLESHEETPATH.'/'.RCWDNGGCOMMENTS_THEME_TEMPLATE_FOLDER.'/'.RCWDNGGCOMMENTS_TEMPLATE)){
				$template = STYLESHEETPATH.'/'.RCWDNGGCOMMENTS_THEME_TEMPLATE_FOLDER.'/'.RCWDNGGCOMMENTS_TEMPLATE;
			}else{
				$template = RCWDNGGCOMMENTS_TEMPLATE_PATH;	
			}
			return $template;
		}
		
		function rcwd_ngg_add_comment_to_gallery($args = array()){
			global $wp_query, $ngg_query, $temp_wp_query, $ngg_post_id, $gallery_ID, $rcwd_ngg_comments_are_displayed;
			if(!isset($rcwd_ngg_comments_are_displayed)) $rcwd_ngg_comments_are_displayed = false;
			if($rcwd_ngg_comments_are_displayed === false){
				$is_html5		= isset($args['is_html5']) 	? (bool)$args['is_html5'] 	: true;
				$gallery_ID 	= isset($args['gid']) 		? (int)$args['gid'] 		: $gallery_ID;
				$template 		= isset($args['template']) 	? $args['template'] 		: '';
				if($template == '/comments.php') die('Non puoi utilizzare il file dei commenti di wordpress, mi dispiace...');
				if($template == ''){				
					add_filter('comments_template', 'rcwd_ngg_comments_template');
				}
				$ngg_post_id 	= rcwd_get_postid_from_custom('_rcwd_nggid', $gallery_ID);
				if($ngg_post_id != ''){
					if($is_html5 === true){
						$open_tag 	= '<section class="comments-box">';
						$close_tag 	= '</section>';
					}else{
						$open_tag 	= '<div class="comments-box">';
						$close_tag 	= '</div>';					
					}
					$temp_wp_query 	= $wp_query;
					$ngg_query 		= new WP_Query();
					$ngg_query->query('p='.$ngg_post_id.'&post_type=rcwd-ngg');
					while ($ngg_query->have_posts()){
						$ngg_query->the_post();
						echo $open_tag;
						comments_template( $template, true ); 	
						echo $close_tag;
					}
					$wp_query = $temp_wp_query;
					wp_reset_postdata();
					$rcwd_ngg_comments_are_displayed = true;
					return;		
				}
			}
			return false;	
		}
			
	// FUNCS: custom gallery comments navigation links _______________________________________________________
		
		function rcwd_ngg_get_comments_pagenum_link( $pagenum = 1, $max_page = 0 ) {
			global $wp_rewrite, $nggRewrite, $gallery_ID, $wp_query,  $temp_wp_query;
			$ngg_post_id 		= rcwd_get_postid_from_custom('_rcwd_nggid', $gallery_ID);
			$temp_wp_query_2 	= $wp_query;
			$wp_query 			= $temp_wp_query;
			wp_reset_postdata();
			
			$pagenum 	= (int) $pagenum;
			$result 	= $nggRewrite->get_permalink( array( 'album' => get_query_var('album'), 'gallery' => get_query_var('gallery'), 'nggpage' => false) );
					
			if ( 'newest' == get_option('default_comments_page') ) {
				if ( $pagenum != $max_page ) {
					if ( $wp_rewrite->using_permalinks() )
						$result = user_trailingslashit( trailingslashit($result) . 'comment-page-' . $pagenum, 'commentpaged');
					else
						$result = add_query_arg( 'cpage', $pagenum, $result );
				}
			} elseif ( $pagenum > 1 ) {
				if ( $wp_rewrite->using_permalinks() )
					$result = user_trailingslashit( trailingslashit($result) . 'comment-page-' . $pagenum, 'commentpaged');
				else
					$result = add_query_arg( 'cpage', $pagenum, $result );
			}
			$result 	.= '#comments';
			$result 	= apply_filters('get_comments_pagenum_link', $result);
			$wp_query 	= $temp_wp_query_2;
			wp_reset_postdata();
			return $result;
		}
		
		function rcwd_ngg_get_previous_comments_link( $label = '' ) {
			if ( !get_option('page_comments') )
				return;
		
			$page = get_query_var('cpage');
		
			if ( intval($page) <= 1 )
				return;
		
			$prevpage = intval($page) - 1;
		
			if ( empty($label) ) 
				$label = __( '&larr; Older Comments', 'nggcomments' );
		
			return '<a href="' . esc_url( rcwd_ngg_get_comments_pagenum_link( $prevpage ) ) . '" ' . apply_filters( 'previous_comments_link_attributes', '' ) . '>' . preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label) .'</a>';
		}
			
		function rcwd_ngg_previous_comments_link( $label = '' ) {
			echo rcwd_ngg_get_previous_comments_link( $label );
		}		
		
		function rcwd_ngg_get_next_comments_link( $label = '' ) {
		global $wp_query;
		
			if ( !get_option('page_comments') )
			return;
		
		$page = get_query_var('cpage');
		
		$nextpage = intval($page) + 1;
		
		if ( empty($max_page) )
			$max_page = $wp_query->max_num_comment_pages;
		
		if ( empty($max_page) )
			$max_page = get_comment_pages_count();
		
		if ( $nextpage > $max_page )
			return;
		
		if ( empty($label) )
			$label = __( 'Newer Comments &rarr;', 'nggcomments' ); 
		
		return '<a href="' . esc_url( rcwd_ngg_get_comments_pagenum_link( $nextpage, $max_page ) ) . '" ' . apply_filters( 'next_comments_link_attributes', '' ) . '>'. preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label) .'</a>';
		}
			
		function rcwd_ngg_next_comments_link( $label = '' ) {
			echo rcwd_ngg_get_next_comments_link( $label );
		}

	// FUNCS: settings for comments template _______________________________________________________


			function rcwd_ngg_comment_form_defaults($defaults) {
				$defaults['id_submit'] = 'thesubmit';
				return $defaults;
			}
			
			function rcwd_ngg_comment_id_fields($result, $id, $replytoid){
				global $ngg_post_id;
				$result  = 	"<input type='hidden' name='comment_post_ID' value='$ngg_post_id' id='comment_post_ID' />\n".
							"<input type='hidden' name='comment_parent' id='comment_parent' value='$replytoid' />\n".
/*							'<input type="hidden" name="album" value="'.get_query_var('album').'" />'.
							'<input type="hidden" name="gallery" value="'.get_query_var('gallery').'" />'.*/
							'<input type="hidden" name="redirect_to" value="'.esc_attr($_SERVER["REQUEST_URI"]).'" />';
				return $result;
			}
			
			function rcwd_ngg_wp(){
				if(get_query_var('gallery') != ''){
					add_filter('comment_form_defaults','rcwd_ngg_comment_form_defaults');
					add_filter('comment_id_fields','rcwd_ngg_comment_id_fields', 10, 3);
				}
			}
			add_action('wp', 'rcwd_ngg_wp');	
}		
?>