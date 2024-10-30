<?php
/*
Plugin Name: Lazyest Slides
Plugin URI: http://brimosoft.nl/lazyest/slides/
Description: Adds extra Slide View options and Slideshows to Lazyest Gallery 
Date: 2012 July
Author: Brimosoft
Author URI: http://brimosoft.nl
Version: 0.6.0-alpha
License: GNU GPLv2
*/
 
 

/**
 * LazyestSlides
 * 
 * @package Lazyest Gallery
 * @subpackage Lazyest Slides
 * @author Marcel Brinkkemper
 * @copyright 2011 Marcel Brinkkemper
 * @version 0.6
 * @access public
 */
class LazyestSlides {
	
	var $options;
	var $click_filters;
	var $plugin_url;
	
	/**
	 * LazyestSlides::__construct()
	 * 
	 * @return void
	 */
	function __construct() {			
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$options = get_option( 'lazyest-slides' );
		$this->options = $options ? $options : $this->defaults();
		$this->init();				
	}
	
	// lazyest-slides core functions
		
	function init() {		
		global $lg_gallery;
			
		$this->click_filters = array();
		if ( isset( $this->options['thickbox'] ) && $this->options['thickbox'] ) {
			if ( in_array( $lg_gallery->get_option( 'on_thumb_click' ), array( 'thickslide', 'thickbox') ) ) {
				switch( $lg_gallery->get_option( 'on_thumb_click' ) ) {
					case 'thickslide' :
						$this->click_filters['lazyest_thumb_onclick'] = 'slide';
						break;
					case 'thickbox' :	
						$this->click_filters['lazyest_thumb_onclick'] = 'full';
						break;
				}
			} 
			if ( 'thickbox' == $lg_gallery->get_option( 'on_slide_click' ) ) {
				$this->click_filters['lazyest_slide_onclick'] = 'full';
			}
		}
		$this->filters();	
	}
	
	function filters() {
		// wordpress hooks
		register_uninstall_hook( __FILE__, array( 'LazyestSlides', 'uninstall' ) );
		register_activation_hook( __FILE__, array( &$this, 'activation' ) );
		
		// wordpress actions and filters	
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_scripts' ), 1 );		
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 2 );
		add_action( 'wp_print_styles', array( &$this, 'styles' ), 1 );
		add_action( 'admin_action_lazyest-slides', array( &$this, 'do_action' ) );		
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		add_action( "admin_print_scripts-settings_page_lazyest-gallery", array( &$this, 'manager_js' ) );		
		add_action( "admin_print_styles-settings_page_lazyest-gallery", array( &$this, 'manager_css' ) );
		
		// lazyest gallery actions and filters		
		add_action( 'lazyest-gallery-settings_slides', array( &$this, 'settings_slides' ) );
		add_action( 'lazyest-gallery-settings_pages', array( &$this, 'settings_page' ) );
		add_filter( 'lazyest_do_slide', array( &$this, 'do_slide' ), 10, 3 );
		if ( 0 != count( $this->click_filters ) ) {
			foreach( $this->click_filters as $key => $filter )
				add_filter( $key, array( &$this, 'thickbox' ), 1, 2 );
			add_action( 'wp_ajax_lazyest_slides_thickbox', array( &$this, 'lazyest_slides_thickbox' ) );			
			add_action( 'wp_ajax_nopriv_lazyest_slides_thickbox', array( &$this, 'lazyest_slides_thickbox' ) );
		}
		if ( isset( $this->options['thickbox'] ) && $this->options['thickbox'] ) {
			add_action( 'wp_footer', array( &$this, 'thickbox_footer') );
		}
	}
	
	function defaults() {
		return array( 'slideview' => 'default', 'slideshow' => 'default', 'thickbox', true );
	}
	
	function activation() {
		$this->upgrade();
	}
	
	function uninstall() {
		if ( __FILE__ != WP_UNINSTALL_PLUGIN )
  		return;
  	delete_option( 'lazyest-slides' );	
	}
	
	function upgrade() {
		if ( ! isset( $this->options['thickbox'] ) ) {
			$this->options['thickbox'] = false;
			update_option( 'lazyest-slides', $this->options );
		}
	}
	
	function viewer_pack() {
		$viewer_pack = array();
		$viewer_pack[] = array( 
			'view' => 'default',
			'selected' => 'default' == $this->options['slideview'],
			'name' => esc_html__( 'Default', 'lazyest-slides' ),
			'description' => esc_html__( 'The default Lazyest Gallery slide view', 'lazyest-slides' )
		);
		$viewer_pack[] = array( 
			'view' => 'carousel',
			'selected' => 'carousel' == $this->options['slideview'],
			'name' => esc_html__( 'Carousel', 'lazyest-slides' ),
			'description' => esc_html__( 'Show thumbs slider above slide' ) . '<br />' . esc_html__('This viewer works best with cropped thumbnails', 'lazyest-slides' )
		);
		return $viewer_pack; 
	}
	
	// wordpress actions and filters
	/**
	 * LazyestSlides::scripts()
	 * 
	 * @return void
	 */
	function register_scripts() {
		wp_register_script( 'jquery-easing', plugins_url( 'js/jquery.easing.1.3.js',  __FILE__ ), array( 'jquery' ), '1.3', true );
		wp_register_script( 'jquery-elastislide', plugins_url( 'js/jquery.elastislide.js',  __FILE__ ), array( 'jquery-easing' ), $this->version(), true );
		wp_register_script( 'lazyest-slide-carousel', plugins_url( 'js/carousel.js',  __FILE__ ), array( 'jquery-elastislide' ), $this->version(), true );
	}
	
	function do_action() {
		$nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';		
		$message = array( 'action' => 'slides-updated', 'result' => 'true' );
		if ( wp_verify_nonce( $nonce, 'lazyest_slides') ) {
			$options = $_REQUEST['lazyest-slides'];
			unset( $options['update'] );
			$options['slideview'] = isset( $options['slideview'] ) ? $options['slideview'] : 'default';	
			$options['slideshow'] = isset( $options['slideshow'] ) ? $options['slideshow'] : 'default';	
			$options['thickbox'] = isset( $options['thickbox'] ) ? true : false;			
			update_option( 'lazyest-slides', $options );	
		}
		$redirect = admin_url( 'admin.php?page=lazyest-gallery&subpage=lazyest-slides' );
		$redirect = add_query_arg( $message['action'], $message['result'], $redirect );
		wp_redirect( $redirect );
    exit();
	}
	
	function admin_notices() {
		if ( isset( $_REQUEST['slides-updated'] ) && 'true' == $_REQUEST['slides-updated'] ) {
			$message = esc_html__( 'Slides Settings saved', 'lazyest-slides' );
			echo "<div class='updated'><p>$message</p></div>";
		}
	}
	
	/**
	 * LazyestSlides::enqueue_scripts()
	 * 
	 * @return void
	 */
	function enqueue_scripts() {
		$slidescript = $this->options['slideview'];
		if ( 'default' != $slidescript )
			wp_enqueue_script( "lazyest-slide-$slidescript" );
		$showscript = $this->options['slideshow'];
		if ( 'default' != $showscript )	
			wp_enqueue_script( "lazyest-show-$showscript" );
		if ( isset( $this->options['thickbox'] ) && $this->options['thickbox'] )
			wp_enqueue_script( 'thickbox' );	
	}
	
	/**
	 * LazyestSlides::styles()
	 * 
	 * @return void
	 */
	function styles() {
		$slidestyle =  $this->options['slideview'];
		if ( 'default' != $slidestyle )
			wp_enqueue_style( 'lazyest_slides', plugins_url( "css/$slidestyle.css",  __FILE__ ) );		
		if ( isset( $this->options['thickbox'] ) && $this->options['thickbox'] ) {
    	wp_enqueue_style('thickbox', get_bloginfo( 'url' ) . WPINC . '/js/thickbox/thickbox.css' );
			wp_enqueue_style( 'lazyest-thickbox',plugins_url( "css/thickbox.css",  __FILE__ ) );	
		}
	}
	
	function manager_css() {
		wp_enqueue_style( 'lazyest_slides', plugins_url( 'css/admin.css',  __FILE__ ) );
	}
		
	function manager_js() {
		wp_enqueue_script( 'lazyest_slides', plugins_url( 'js/admin.js',  __FILE__ ) );
	}
	
	function thickbox_footer() {
		?>
		<script>var tb_closeImage = "<?php echo plugins_url( 'js/images/close.png', __FILE__ );?>";</script>
		<?php
	}
	
	// lazyest gallery actions and filters
	
	function thickbox( $onclick, $image ) {
		if ( 'LazyestThumb' == get_class( $image ) )
			$size = $this->click_filters['lazyest_thumb_onclick'];
		else 
			$size = 'full';	
		$image_path = lg_nice_link( $image->folder->curdir . $image->image );
		$onclick['href'] = add_query_arg( array( 
			'action' => 'lazyest_slides_thickbox', 
			'file' => $image_path, 
			'size' => $size,
			'TB_iframe' => 1
		), admin_url( 'admin-ajax.php' ) );
		$onclick['class'] = 'thickbox';
		$onclick['rel'] = 'nofollow';
		return $onclick;
	}
	
	function settings_slides() {
		?>
		<tr>
      <th scope="row"><?php esc_html_e( 'Alternative Display', 'lazyest-slides' ); ?></th>
      <td>
        <p><a class="button" href="admin.php?page=lazyest-gallery&amp;subpage=lazyest-slides"><?php esc_html_e( 'Setup Slides' ) ?></a></p>
        <p><?php esc_html_e( 'Setup an alternative way to show your slides', 'lazyest-slides' ) ?></p>
      </td>  
    </tr>
		<?php
	}
	
	function version() {		  	
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_data = get_plugin_data( __FILE__ );
  	return $plugin_data['Version'];
	}	
	
	function settings_page( $settings ) {
		global $lg_gallery, $wp_version;
		if ( ! isset( $_REQUEST['subpage'] ) || 'lazyest-slides' != $_REQUEST['subpage'] )
			return;
		$settings->other_page = true;	
		require_once( plugin_dir_path( __FILE__ ) . 'inc/tables.php' );	
		?>
		<div class="wrap">
			<?php screen_icon( 'slides' ); ?>
      <h2><?php echo esc_html_e( 'Enhance your Lazyest Gallery Slides', 'lazyest-slides' ); ?></h2>      
			<?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?>
			<div id="poststuff" class="metabox-holder has-right-sidebar">
			<?php else : ?>	
			<div id="poststuff" class="metabox-holder">
			<?php endif; ?>
				<form id="lazyest-slides" method="post" action="admin.php">
					<?php wp_nonce_field( 'lazyest_slides' );  ?>
					<input type="hidden" name="action" value="lazyest-slides" />
					<?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?> 
						<?php $this->sidebar( $settings ) ?>         		
          	<div id="post-body">
         	<?php else : ?>
						<div id="post-body" class="metabox-holder columns-2">		              	
         		<?php $this->sidebar( $settings) ?>
         	<?php endif; ?>
						<div id="post-body-content">
							<fieldset>
							<legend><?php esc_html__( 'Which Slide View do you like?', 'lazyest-slides' ); ?></legend>							
							<?php $this->slideview_select() ?>
							</fieldset>
						</div>
					</div>
				</form>	
			</div>					 
		</div>
		<?php
	}
	
	// settings page boxes
	
	function sidebar( $settings ) {
  	global $wp_version;
		?>
    <?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?> 
	    <div id="side-info-column" class="inner-sidebar">
	  <?php else : ?>
	    <div id="postbox-container-1" class="postbox-container">
    <?php endif; ?>
      <div id="side-sortables" class="meta-box-sortables">
        <?php $settings->aboutbox(); ?>
        <?php $this->utilities(); ?>
        <?php $this->submitbox(); ?>
      </div>
    </div>
    <?php		
	}
	
	function utilities() {
		?>		
		<div id="utilities" class="postbox">
			<h3 class="hndle"><span><?php esc_html_e( 'Utility functions', 'lazyest-slides' ); ?></span></h3>
			<div class="inside">
				<div id="lazyest-slides-thickbox-select">
					<p><label for="lazyest-slides-thickbox"><input type="checkbox" id="lazyest-slides-thickbox" name="lazyest-slides[thickbox]" <?php checked( true, $this->options['thickbox'] ) ?> /> <?php esc_html_e( 'Use Lazyest Slides Thickbox image popup', 'lazyest-slides' ) ?></label></p>
				</div>
			</div>
		</div>
		<?php
	}
	
	function submitbox() {
		?>
		<div id="submitdiv" class="postbox">
		<h3 class="hndle"><span><?php esc_html_e( 'Lazyest Slides', 'lazyest-slides' ) ?></span></h3>
		<div id="version" class="misc-pub-section">               
      <div class="versions">
        <p><span id="ls-version-message"><strong><?php echo esc_html_e( 'Version', 'lazyest-slides' ); ?></strong> <?php echo $this->version(); ?></span></p>
      </div>
    </div>
    <div class="misc-pub-section misc-pub-section-last">
      <p><a id="back_link" href="admin.php?page=lazyest-gallery" title="<?php esc_html_e( 'Back to Lazyest Gallery Settings', 'lazyest-slides' ) ?>"><?php esc_html_e( 'Back to Lazyest Gallery Settings', 'lazyest-slides' ) ?></a></p>            
    </div>     
		<div id="major-publishing-actions">       
      <div id="publishing-action">
        <input class="button-primary" type="submit" name="lazyest-slides[update]" value="<?php	esc_html_e( 'Save Changes', 'lazyest-slides' )	?>" />
      </div> 
      <div class="clear"></div>
    </div>
		</div>
		<?php
	}
	
	function slideview_select( ) {
		$slideviewtable = new LazyestSlideViewTable( $this->viewer_pack() );
		?>
		<div id="slideview" class="postbox">
			<?php $slideviewtable->display() ?>
		</div>
		<?php
	}
	
	
	// the workings
	
	/**
	 * LazyestSlides::do_slide()
	 * 
	 * @param string $markup
	 * @param LazyestFrontendFolder $folder
	 * @param string $filevar
	 * @return string
	 */
	function do_slide( $markup, $folder, $filevar ) {
		if( 'default' == $this->options['slideview'] )
			return $markup;
		require_once( plugin_dir_path( __FILE__ ) . 'inc/viewers.php' );
		$viewclass = 'Lazyest' . ucfirst( $this->options['slideview'] );		
		$slideview = new $viewclass;
		ob_start();
		$slideview->show_slide( $folder, $filevar );
		$do_slide = ob_get_contents();
    ob_end_clean(); 
		return $do_slide . $markup;
	}	
	
	/**
	 * LazyestSlides::lazyest_slides_thickbox()
	 * show thickbox on AJAX call
	 *  
	 * @return void
	 */
	function lazyest_slides_thickbox() {
		require_once( plugin_dir_path( __FILE__ ) . 'inc/thickbox.php' );
		$thickbox = new LazyestThickBox;
		$thickbox->display();
		die();
	}
}


global $lazyest_slides;
/**
 * lazyest_slides()
 * Do not start Lazyest slides if the Lazyest Gallery plugin is not active
 * @return void
 */ 
function lazyest_slides() {
	global $lazyest_slides;
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
	if ( is_plugin_active( 'lazyest-gallery/lazyest-gallery.php' ) )
		$lazyest_slides = new LazyestSlides;
}
add_action( 'init', 'lazyest_slides' );
?>