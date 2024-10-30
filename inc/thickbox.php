<?php

/**
 * LazyestThickBox
 * 
 * @package Lazyest Slides
 * @author Marcel Brinkkemper
 * @copyright 2011 Brimosoft
 * @version 0.3.0
 * @access public
 */
class LazyestThickBox {
	
	/**
	 * LazyestThickBox::check_referer()
	 * Check if popup is called from this website
	 * die if from external site
	 * 
	 * @return void
	 */
	function check_referer() {
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$this_domain = parse_url( get_bloginfo( 'url' ) );
			$referer = parse_url( $_SERVER['HTTP_REFERER'] );
			if ( $this_domain['host'] == $referer['host'] )
				return;
		}
		die(0);
	}
	
	/**
	 * LazyestThickBox::display()
	 * Output thickbox iframe markup
	 * 
	 * @return void
	 */
	function display() {		
		global $lg_gallery, $lazyest_slides;		
		$this->check_referer();
		require_once( trailingslashit( $lg_gallery->plugin_dir ) .  'inc/frontend.php' );
		$lg_gallery = new LazyestFrontend;
		if ( $lg_gallery->valid() ) {
			$what = ( 'slide' == $_REQUEST['size'] ) ? 'slides' : 'images';
			$folder = new LazyestFolder( $lg_gallery->currentdir );			
			$basename = basename( $lg_gallery->file );
			if ( $image = $folder->single_image( $basename, $what ) ) {
				$number = 0;
				for ( $i = 0; $i < count( $folder->list ); $i++ ) {
			  	$test = $folder->list[$i];        
					if( $test->image == $basename ) {
						$number = $i + 1;
						$previous = ( 0 == $i ) ? end( $folder->list ) : $folder->list[$i-1];  				  
						$next = ( ( $i + 1 ) == count( $folder->list ) ) ? $folder->list[0] : $folder->list[$i+1];
						break;
					}
				}
				$args = array( 
					'action' => 'lazyest_slides_thickbox', 
					'file' => lg_nice_link( $image->folder->curdir . $previous->image ), 
					'size' => $_REQUEST['size'],
					'TB_iframe' => 1 
				);
				$previous_url = add_query_arg( $args, admin_url( 'admin-ajax.php' ) );
				$args['file'] = lg_nice_link( $image->folder->curdir . $next->image );
				$next_url = add_query_arg( $args, admin_url( 'admin-ajax.php' ) );
				$comment_reply = '';
				if ( $lg_gallery->get_option( 'allow_comments' ) ) {
					if ( 'LazyestSlide' != get_class( $image ) ) {
						$slide = new LazyestSlide( $folder );
						$slide->image = $image->image;
						$comment_url = $slide->uri('widget') . '#comments';
					} else {
						$comment_url = $image->uri('widget');
					}
					$comment_title = esc_html__( 'Leave a reply', 'lazyest-slides' );
					$comment_reply = "<a href='$comment_url' title='$comment_title'>$comment_title</a>";
				}
				$image_file = ( 'full' == $_REQUEST['size'] ) ? $image->original() : $image->loc();
				list( $width, $height, $type ) = @getimagesize( $image_file );
				?>
				<!DOCTYPE html>
				<html>
					<head>
						<title><?php echo $image->title() ?></title>
						<link rel="stylesheet" type="text/css" media="all" href="<?php echo includes_url( '/js/thickbox/thickbox.css' ); ?>" />
						<link rel="stylesheet" type="text/css" media="all" href="<?php echo $lazyest_slides->plugin_url . "/css/thickbox.css"; ?>" />
					</head>
					<body id="lazyest_thickbox">
						<div id="wrapper">
							<div id="content">						
								<img id="img" src="<?php echo $image->src(); ?>" title="<?php echo lg_html( $image->caption() ); ?>" alt="<?php echo $image->title() ?>" style="width:<?php echo $width ?>px; height:<?php echo $height ?>px;" />
								<h1><?php echo $image->caption() ?></h1>
								<div id="description"><?php echo lg_html( $image->description() ) ?></div>
								<div class="nav">
									<a class="previous" title="<?php esc_html_e( 'Previous', 'lazyest-slides' ); ?>" href="<?php echo $previous_url ?>"></a>
									<a class="next" title="<?php esc_html_e( 'Next', 'lazyest-slides' ); ?>" href="<?php echo $next_url; ?>"></a>
								</div>
							</div>
							<div id="count"><?php printf( esc_html__( '%d of %d', 'lazyest-slides' ), $number, count( $folder->list ) ); ?></div>
							<div id="add-reply"><?php echo $comment_reply; ?></div>
						</div>					
						<script src="<?php echo includes_url( '/js/jquery/jquery.js' ) ?>"></script>
						<script src="<?php echo $lazyest_slides->plugin_url . '/js/thickbox.js'; ?>"></script>
						<script>var tb_closeImage = "<?php echo $lazyest_slides->plugin_url . '/js/images/close.png'; ?>";</script>
						<script> lazyestSlidesThickBox(); </script>
					</body>
				</html>
				<?php	
			}		
		}
	}
}