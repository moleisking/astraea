<?php
 /**
  * @author Scott Johnston
  * @license https://www.gnu.org/licenses/gpl-3.0.html
  * @package Astraea
  * @version 1.0.0
 */

//defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );
class AstraeaShortcode{

	public function __construct(){		
		add_action('init', array($this,'registerAstraeaShortcodes')); 				
		add_action('wp_enqueue_scripts', array($this,'__script_and_style'));
		add_action('wp_ajax_post_review_create', array($this,'post_review_create'));	
	}

	public function __script_and_style(){
		wp_register_script('astraeaScript', plugins_url( '/js/astraea.js', __FILE__ ), array('jquery','jquery-form'), '1.0', true);
		wp_enqueue_script('astraeaScript');
		wp_localize_script('astraeaScript','ajax_object',array( 'ajax_url' => admin_url("admin-ajax.php")));

		wp_register_style('astraeaStyle', plugins_url( '/css/astraea.min.css', __FILE__ ), array(), '1.0',	'all');
		wp_enqueue_style('astraeaStyle');

		wp_register_style('w3Style', plugins_url( '/css/w3.css', __FILE__ ), array(), '1.0',	'all');
		wp_enqueue_style('w3Style');
	}

	public function registerAstraeaShortcodes( $atts ) {		
		add_shortcode( 'astraea_create', array($this ,'shortcode_create' ) );	
		add_shortcode( 'astraea_average', array($this ,'shortcode_average' ) );	
		add_shortcode( 'astraea_list', array($this ,'shortcode_list' ) );	
	}	

	private function average($receiverId  ){	
		global $wpdb;		
		$select = "SELECT ROUND(AVG(score),0) AS average FROM ".$wpdb->base_prefix."reviews".
		" WHERE receiverId=".$receiverId;	
		$results = $wpdb->get_results($select);
		if((sizeof($results) > 0)){
			return $results[0]->average;
		} else {
			return 0;
		}
	}

	private function buildFiveStarHTML($average){
		$icon_width = !empty(get_option('icon_size')) ? get_option('icon_size') : '48rem'; 
		
		$html = "<div id='fiveStarInput' class='w3-container'>".	
			"<div class='btnStar'>".									
				//First review button						
				"<img width='".$icon_width."' height='".$icon_width."'";
				if ($average >= 20) {
					$html .= " src='".plugins_url( '/images/star_full.svg', __FILE__ )."'"."/>"; 
				} else {
					$html .= " src='".plugins_url( '/images/star_empty.svg', __FILE__ )."'"."/>";
				}  
			//Second review button						
			$html .= "<img width='".$icon_width."' height='".$icon_width."'";			
				if ($average >= 40) {
					$html .= " src='".plugins_url( '/images/star_full.svg', __FILE__ )."'"."/>"; 
				} else {
					$html .= " src='".plugins_url( '/images/star_empty.svg', __FILE__ )."'"."/>";
				}
			//Third review button
			$html .= "<img width='".$icon_width."' height='".$icon_width."'";			
				if ($average >= 60) {
					$html .= " src='".plugins_url( '/images/star_full.svg', __FILE__ )."'"."/>";
				} else {
					$html .= " src='".plugins_url( '/images/star_empty.svg', __FILE__ )."'"."/>";
				}   
			//Fourth review button						
			$html .= "<img width='".$icon_width."' height='".$icon_width."'";		
				if ($average >= 80) {
					$html .= " src='".plugins_url( '/images/star_full.svg', __FILE__ )."'"."/>";
				} else {
					$html .= " src='".plugins_url( '/images/star_empty.svg', __FILE__ )."'"."/>";
				} 
			//Fifth review button						
			$html .= "<img width='".$icon_width."' height='".$icon_width."'";			
				if ($average == 100) {
					$html .= " src='".plugins_url( '/images/star_full.svg', __FILE__ )."'"."/>";
				} else {
					$html .= " src='".plugins_url( '/images/star_empty.svg', __FILE__ )."'"."/>";
				}	
			$html .= "</div>".		
		"</div>";

		return $html;
	}

	public function error_dialog(){
		echo "<script>alert('Only one review allowed per user.')</script>";
	}

	private function isDuplicateReview( $senderId, $receiverId ){
		global $wpdb;
		$table = $wpdb->base_prefix."reviews";
		$select = "SELECT COUNT(score) AS score FROM ".$table.
		" WHERE senderId=".$senderId." AND receiverId=".$receiverId;	
		$results = $wpdb->get_results($select);
		if((sizeof($results) > 0) && $results[0]->score > 0){
			return true;
		} else {
			return false;
		}
	}

	public function post_review_create(){			
		if  (is_user_logged_in() && isset($_POST['toId']) && isset($_POST['uniqueId']) ) {	
			
			//Collect post information
			$uniqueId =  filter_var ($_POST['uniqueId'], FILTER_SANITIZE_STRING);
			$receiverId =  filter_var ($_POST['toId'], FILTER_SANITIZE_NUMBER_INT);		
			$senderId = get_current_user_id();
			$numerator =  filter_var ($_POST['numerator'.$uniqueId], FILTER_SANITIZE_NUMBER_INT);	
			$denominator =  filter_var ($_POST['denominator'.$uniqueId], FILTER_SANITIZE_NUMBER_INT);	
			$text = filter_var ($_POST['comment'] , FILTER_SANITIZE_STRING);	
			if ($denominator > 0) {
				$score = round(($numerator/$denominator)*100);
			} else {
				$score = 0;
			}				
			
			//Write to database
			if (($senderId != $receiverId) && (!$this->isDuplicateReview($senderId, $receiverId))) {	
				//Add audit trail for review							
				global $wpdb;
				$wpdb->insert( $wpdb->base_prefix.'reviews', 
					array( 	
						'senderId' => filter_var ($senderId, FILTER_SANITIZE_NUMBER_INT),				
						'receiverId' => filter_var ($receiverId, FILTER_SANITIZE_NUMBER_INT),
						'score' => filter_var ($score, FILTER_SANITIZE_NUMBER_INT),						
						'text' =>  filter_var ($text, FILTER_SANITIZE_SPECIAL_CHARS)
					) 
				);	
				//Update meta field data
				update_user_meta( $receiverId, 'custom_field_review', $this->average( $receiverId ) );	 

			} else {
				$this->error_dialog();
			}			
		} else {
			echo "<script>alert('User not logged in or missing user id.')</script>";
			//echo "no post_review_create".$_POST['btnReviewCreate'].":".$_POST['toId'].":".$_POST['uniqueId'];
		}
	}

	public function shortcode_create( $atts ) {		
		//Get look and feel options
		$icon_width = !empty(get_option('icon_size')) ? get_option('icon_size') : '48rem'; 
		$textarea_rows = !empty(get_option('textarea_rows')) ? get_option('textarea_rows') : 3; 
		$textarea_length = !empty(get_option('textarea_length')) ? get_option('textarea_length') : 100 ; 

		//Extract lowercase only parameters from shortcode	
		$atts = shortcode_atts( array(
			'to' => null	
		), $atts, 'astraea_create' );
		$toId = filter_var($atts['to'], FILTER_SANITIZE_NUMBER_INT); 
						
		//Generate uniqueId id for javascript calls
		$uniqueId = uniqid("StrPrefix");
		
		//Build review create html		
		$html .= "<div class='w3-container'><br>".
			//First review button							
			"<button id='btnStar1".$uniqueId."' name='btnStar1".$uniqueId."' class='btnStar'".
				" onclick=".'"'."starChange(1,'".$uniqueId."','".plugins_url( '/images', __FILE__  )."')".'"'.">".
				"<img id='imgStar1".$uniqueId."' name='imgStar1".$uniqueId."' width='".$icon_width."' height='".$icon_width."'".'"'.
				" src='".plugins_url( '/images/star_empty.svg', __FILE__ )."'"."/>".
			"</button>".
			//Second review button
			"<button id='btnStar2".$uniqueId."' name='btnStar2".$uniqueId."' class='btnStar'".
				" onclick=".'"'."starChange(2,'".$uniqueId."','".plugins_url( '/images', __FILE__  )."')".'"'.">".
				"<img id='imgStar2".$uniqueId."' name='imgStar2".$uniqueId."' width='".$icon_width."' height='".$icon_width."'".'"'.			
				" src='".plugins_url( '/images/star_empty.svg', __FILE__ )."'"."/>".
			"</button>".
			//Third review button
			"<button id='btnStar3".$uniqueId."' name='btnStar3".$uniqueId."' class='btnStar'".
				" onclick=".'"'."starChange(3,'".$uniqueId."','".plugins_url( '/images', __FILE__  )."')".'"'.">".
				"<img id='imgStar3".$uniqueId."' name='imgStar3".$uniqueId."' width='".$icon_width."' height='".$icon_width."'".'"'.			
				" src='".plugins_url( '/images/star_empty.svg', __FILE__ )."'"."/>".
			"</button>".
			//Fourth review button
			"<button id='btnStar4".$uniqueId."' name='btnStar4".$uniqueId."' class='btnStar'".
				" onclick=".'"'."starChange(4,'".$uniqueId."','".plugins_url( '/images', __FILE__  )."')".'"'.">".
				"<img id='imgStar4".$uniqueId."' name='imgStar4".$uniqueId."' width='".$icon_width."' height='".$icon_width."'".'"'.			
				" src='".plugins_url( '/images/star_empty.svg', __FILE__ )."'"."/>".
			"</button>".
			//Fifth review button
			"<button id='btnStar5".$uniqueId."' name='btnStar5".$uniqueId."' class='btnStar'".
				" onclick=".'"'."starChange(5,'".$uniqueId."','".plugins_url( '/images', __FILE__  )."')".'"'.">".
				"<img id='imgStar5".$uniqueId."' name='imgStar5".$uniqueId."' width='".$icon_width."' height='".$icon_width."'".'"'.		
				" src='".plugins_url( '/images/star_empty.svg', __FILE__ )."'"."/>".
			"</button>";
			if (is_user_logged_in())	{
				//Form
				$html .= "<form id='frmReviewCreate".$uniqueId."' name='frmReviewCreate".$uniqueId."' class='frmReviewCreate' method='post' action='".admin_url('admin-ajax.php')."'>".
					"<input type='hidden' id='uniqueId' name='uniqueId' value='".$uniqueId."'>".
					"<input type='hidden' id='toId' name='toId' value='".$toId."'>".
					"<input type='hidden' id='numerator".$uniqueId."' name='numerator".$uniqueId."' value='0'>".
					"<input type='hidden' id='denominator".$uniqueId."' name='denominator".$uniqueId."' value='5'>".
					"<input type='hidden' name='action' value='post_review_create'>".																		
					"<textarea id='comment' name='comment' rows='".$textarea_rows."' width='100%' maxlength='".$textarea_length."'></textarea>".	
				"</form>".
				//Post message button
				"<button id='btnReviewCreate' name='btnReviewCreate' type='submit' width='100%' form='frmReviewCreate".$uniqueId."'>Send</button>";										
			}						
				
		$html .= "</div>";			
   		
		return $html;
	}	
	
	public function shortcode_list( $atts ) {		
		//Get look and feel options
		$icon_width = !empty(get_option('icon_size')) ? get_option('icon_size') : '48rem'; 
		
		//Extract lowercase only parameters from shortcode	
		$atts = shortcode_atts( array(
			'to' => null,
			'write' => null		
		), $atts, 'astraea_list' );
		$toId = filter_var($atts['to'], FILTER_SANITIZE_NUMBER_INT);  		
		$write = filter_var($atts['write'], FILTER_VALIDATE_BOOLEAN);
				
		//Generate uniqueId id for javascript calls
		$uniqueId = uniqid("StrPrefix");
		
		//Average
		$reviewField = !empty(get_option('review_field_name')) ? get_option('review_field_name') : 'custom_field_review'; 	
		$reviews = json_decode(get_userdata($toId)->$reviewField);	//$reviewField json_decode(	
		$average = $this->average($toId);
		
		//Build reviews list html
		global $wpdb;
		$select = "SELECT * FROM ".$wpdb->base_prefix."reviews". 
			" WHERE receiverId =".$toId. 
			" ORDER BY ts ASC".
			" LIMIT 20";		
		$results = $wpdb->get_results($select);
		if (sizeof($results) > 0){
			foreach ($results as $result) {
				//Stars
				$html .= $this->buildFiveStarHTML($result->score);

				//Text
				if ($write) {	
					$html .= "<div class='w3-container'>".
						"<br><div id='divCommentary'>".$result->text."</div>".						
					"</div>";
				}				
			}		
		} else {
			$html .= "<div id='divCommentary'></div>";
		}
   		
		return $html;
	}	

	public function shortcode_average( $atts ) {		
		//Get look and feel options
		$icon_width = !empty(get_option('icon_size')) ? get_option('icon_size') : '48rem'; 		

		//Extract lowercase only parameters from shortcode	
		$atts = shortcode_atts( array(
			'to' => null,
			'average' => null			
		), $atts, 'astraea_average' );
		$toId = filter_var($atts['to'], FILTER_SANITIZE_NUMBER_INT);  
		$average = filter_var( $atts['average'], FILTER_SANITIZE_NUMBER_INT);
				
		//Generate uniqueId id for javascript calls
		$uniqueId = uniqid("StrPrefix");
				
		//Build review average html		
		if ($toId){
			$html = $this->buildFiveStarHTML($this->average($toId)); 
		} else {
			$html = $this->buildFiveStarHTML($average); 
		}

		return $html;
	}	
}	

$astraeaShortcode = new AstraeaShortcode();
?>