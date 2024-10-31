<div id="comments">
<?php 
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME'])) die('Please do not load this page directly. Thanks!');
	if ( post_password_required() ) : ?>
    	<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'nggcomments' ); ?></p>
<?php 
   	else:
		if ( have_comments() ) : 
?>
        	<h2 id="comments-title">
<?php
                printf( _n( 'One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'nggcomments' ),                    number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
?>
        	</h2>

<?php 		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
?>
                <nav id="comment-nav-above">
                    <h1 class="assistive-text"><?php _e( 'Comment navigation', 'nggcomments' ); ?></h1>
                    <div class="nav-previous"><?php rcwd_ngg_previous_comments_link( __( '&larr; Older Comments', 'nggcomments' ) ); ?></div>
                    <div class="nav-next"><?php rcwd_ngg_next_comments_link( __( 'Newer Comments &rarr;', 'nggcomments' ) ); ?></div>
                </nav>
<?php
    		endif;
?>
        	<ol class="commentlist">
<?php  			wp_list_comments(); ?>
        	</ol>
<?php 	
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
?>
                <nav id="comment-nav-below">
                    <h1 class="assistive-text"><?php _e( 'Comment navigation', 'nggcomments' ); ?></h1>
                    <div class="nav-previous"><?php rcwd_ngg_previous_comments_link( __( '&larr; Older Comments', 'nggcomments' ) ); ?></div>
                    <div class="nav-next"><?php rcwd_ngg_next_comments_link( __( 'Newer Comments &rarr;', 'nggcomments' ) ); ?></div>
                </nav>
<?php
    		endif;
		elseif ( ! comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) :
?>
    		<p class="nocomments"><?php _e( 'Comments are closed.', 'nggcomments' ); ?></p>
<?php 
		endif; 
		comment_form(); 
	endif; 
?>
</div>