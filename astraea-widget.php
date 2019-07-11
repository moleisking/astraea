<?php
 /**
  * @author Scott Johnston
  * @license https://www.gnu.org/licenses/gpl-3.0.html
  * @package Astraea
  * @version 1.0.0
 */

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

class AstraeaWidget extends WP_Widget{

	public function __construct(){
		parent::__construct(
			'AstraeaWidget',
			'Astraea Widget', 
			array('description' => 'A shortcode and widget to send reviews to a user by id.'));	
	}	

	public function form( $instance ) {	
		isset( $instance[ 'toId' ] ) ? $toId = $instance[ 'toId' ] : $toId = 1;	
		isset( $instance[ 'fromUserId' ] ) ? $fromUserId = $instance[ 'fromUserId' ] : $fromUserId = 1;	
		isset( $instance[ 'type' ] ) ? $type = $instance[ 'type' ] : $type = 'star';
	
		echo "form:".$type.",".$toId;
		?>
		<div>
			<label for="<?php echo $this->get_field_id( 'toId' ); ?>"><?php _e( 'ToUserID' ); ?></label> <br>
			<input id="<?php echo $this->get_field_id( 'toId' ); ?>" name="<?php echo $this->get_field_name( 'toId' ); ?>" type="text" value="<?php echo esc_attr( $toId ); ?>"  width ="100%" /><br>	
			<label>Type</label><br>                
			<select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>"  value = '<?php  echo esc_attr($type); ?>' width ="100%">
				<option value='star' <?php if($type == 'star'): ?> selected='selected'<?php endif; ?>>Star</option>				                  
			</select>			
		</div>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
        $instance = array();
		$instance['toId'] = strip_tags( $new_instance['toId'] );
		$instance['type'] = strip_tags( $new_instance['type'] );	
		return $instance;
    }
	
	public function widget( $args, $instance ) {
		extract( $args );
		$toId = apply_filters( 'toId', $instance['toId'] );		
		$type = apply_filters( 'type', $instance['type'] );			
		echo "update:".$type.",".$toId;		
		if ($type == 'star'){
			echo do_shortcode("[astraea_star to='".$toId."']"); 
		} 		
	}
}

function astraea_widget_init(){
	register_widget( 'AstraeaWidget' );
}
add_action('widgets_init','astraea_widget_init');
?>