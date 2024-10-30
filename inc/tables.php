<?php
// table classes for settings page
	class LazyestSlideViewTable extends LazyestTable {
		
		function __construct( $items ) {
			parent::__construct( $items );
			$this->table['class'] = 'widefat select';
	    $this->table['id'] = 'slideview-select';
	    $this->table['style'] = 'clear:none;';
	    $this->start = 1;
		}
		
		function columns() {
	    return array(
	      'name' => esc_html__( 'Slide View', 'lazyest-slides' ),
	      'description' => esc_html__( 'Description', 'lazyest-slides' ),
	      'screenshot' => esc_html__( 'Screenshot', 'lazyest-slides' ),
	    );
	  }
	  
	  function head_cell( $key, $value ) {
	  	return sprintf( '<th scope="col">%s</th>', $value );  	
	  }	
	  
	  function body_cell( $key, $value, $i ) {
	  	$viewer = $this->items[$i];
	  	switch( $key ) {
	 			case 'name' :
	 				$cell = sprintf( '<td><input id="%s" type="radio" name="lazyest-slides[slideview]" %s value="%s" /> <label class="%s" for="%s">%s</label></td>',					 						 
	  					$viewer['view'], 				
	  					checked( $viewer['selected'], true, false ),
	  					$viewer['view'],
	  					$viewer['selected'] ? 'select selected' : 'select',
	  					$viewer['view'],
							$viewer['name'] 
					  );
	 				break;
	 			case 'description' :
	 				$cell = sprintf( '<td><p>%s</p></td>',
							$viewer['description'] 
					  );
					break;	
	 			case 'screenshot' :
	 				$cell = sprintf( '<td><img src="%s" alt="%s" /></td>',
							plugins_url( 'images/', dirname(__FILE__) ) . $viewer['view'] . '.jpg',
							esc_html__( 'Screenshot', 'lazyest-slides' ) 
					  );
					break;	
	  	}
	  	return $cell;
	  }
	} // LazyestSlideViewTable
?>