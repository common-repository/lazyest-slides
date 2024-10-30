<?php
// the slide view classes

/**
 * LazyestCarousel
 * Based on Elastislide - A Responsive jQuery Carousel Plugin | Codrops
 * http://tympanus.net/codrops/2011/09/12/elastislide-responsive-carousel/
 * 
 * @package Lazyest Gallery
 * @subpackage Lazyest Slides
 * @author Marcel Brinkkemper
 * @copyright 2011 Marcel Brinkkemper 
 * @version 0.1.0
 * @access public
 */
class LazyestCarousel {
	
	var $index;
	
	/**
	 * LazyestCarousel::__construct()
	 * 
	 * @return void
	 */
	function __construct() {
		add_action( 'wp_footer', array( &$this, 'footer' ), 100 );
		$this->index = 0;
	}
	
	/**
	 * LazyestCarousel::footer()
	 * 
	 * @return void
	 */
	function footer() {
		?>
		<script type='text/javascript'>
		/* <![CDATA[ */
		var carousel = {
			imageW: "<?php echo apply_filters( 'lazyest-slides-carousel-imagew', 100 ) ?>",
			minItems: "<?php echo apply_filters( 'lazyest-slides-carousel-minitems', 6 ) ?>",
			current: <?php echo $this->index; ?>
		};
		/* ]]> */
		</script>
		<?php
	}
	
	/**
	 * LazyestCarousel::thumb_image()
	 * 
	 * @param LazyestThumb $image
	 * @return
	 */
	function thumb_image( $image ) {  	
	 	global $post, $lg_gallery; 
	  $onclick = $image->on_click();
	  $class= 'thumb';
	  if ( 'TRUE' != $lg_gallery->get_option( 'enable_cache' )  || 
			( ( 'TRUE' == $lg_gallery->get_option( 'async_cache' ) ) 
				&& ! file_exists( $image->loc() ) ) ) {
			$class .= ' lg_ajax';	
		}	
		$postid = is_object ( $post ) ? $post->ID : $lg_gallery->get_option( 'gallery_id' ); 
	  return sprintf( '<a id="%s_%s" href="%s" class="%s" rel="%s" title="%s" ><img class="%s" src="%s" alt="%s" /></a>',          
	    $onclick['id'],
	    $postid,
	    $onclick['href'],
	    $onclick['class'],
	    $onclick['rel'],
	    $image->title(),
	    $class,
	    $image->src(),
	    $image->alt()  
	  );    
  }
	
	/**
	 * LazyestCarousel::show_slide()
	 * 
	 * @param LazyestFolder $folder
	 * @param string $filename
	 * @return
	 */
	function show_slide( $folder, $filename ) {
		global $lg_gallery, $post;
		
		if ( ! $lg_gallery->access_check( $folder ) ) 
  		return;  		  		
  		
		$folder->load( 'thumbs' );
		if ( 1 < count( $folder->list ) ) :
			$i= 0;
			foreach( $folder->list as $thumb ) {
				if( $thumb->image == $filename ) {
					$this->index = $i;
					break;
				}
				$i++;
			}
		?>
		<!-- Elastislide Carousel Thumbnail Viewer -->
		<div id="carousel"  class="es-carousel-wrapper">				
			<div class="es-carousel">
				<ul>
					<?php foreach( $folder->list as $thumb ) : ?>
					<li><?php echo $this->thumb_image( $thumb ) ?></li>
					<?php endforeach; ?>			
				</ul>
			</div>
		</div>
		<!-- End Elastislide Carousel Thumbnail Viewer -->
		<?php
		endif;
				
	}	 
} // LazyestCarousel
?>