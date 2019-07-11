<?php
/**
  * @author Scott Johnston
  * @license https://www.gnu.org/licenses/gpl-3.0.html
  * @package Astraea
  * @version 1.0.0
 */

//header("Access-Control-Allow-Origin: *");	
class AstraeaAPI extends WP_REST_Controller{
	
	public function __construct(){}
	
	public function register_routes() {			
		$namespace = 'astraea/v1';		
		register_rest_route( $namespace, '/astraea/create', array(
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'jsonAstraeaCreate'),			
		));
		register_rest_route( $namespace, '/astraea/list/(?P<id>\d+)', array(
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'jsonAstraeaList'),
		));	
		register_rest_route( $namespace, '/ping', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array( $this, 'jsonPing'),			
		));		
	}	

	private function decrypt( string $data ) {
		return mcrypt_decrypt ( MCRYPT_RIJNDAEL_256, $api_key, $data, MCRYPT_MODE_CBC ); 
	}

	private function hash( string $data ) {
		return hash ( "sha512", $data); 
	}
	

	public function jsonReviewCreate( WP_REST_Request $request ) {	
		//Check if teacher get call is enabled	
		$review_enable = !empty(get_option('review_enable')) ? get_option('review_enable') : true; 

		//parameters
		$receiverId = filter_var ( $request->get_param( 'receiverId' ) , FILTER_SANITIZE_NUMBER_INT);
		$senderId = filter_var ( $request->get_param( 'senderId' ) , FILTER_SANITIZE_NUMBER_INT);
		$score = filter_var ( $request->get_param( 'score' ) , FILTER_SANITIZE_NUMBER_INT);
		$text = filter_var ( $request->get_param( 'text' ) , FILTER_SANITIZE_NUMBER_INT);
		$type = filter_var ( $request->get_param( 'type' ) , FILTER_SANITIZE_NUMBER_INT);		
		//'04-02-2019-0900-user-1-22'
		$name = $day."-".$month."-".$year."-".$hour."-".$minute."-user-".$student."-".$teacher;
		//'04/02/2019 @ 09:00 (User: 11)'		
		$title = $day."/".$month."/".$year." @ ".$hour.":".$minute." (User: 1)";		
				//http://localhost/wordpress/?post_type=booked_appointments&p=984
		
				//Insert appointment
		global $wpdb;
		$wpdb->insert( $wpdb->base_prefix.'reviews', 
			array( 		
				'receiverId' => $receiverId,					
				'senderId' => $senderId,		
				'score' =>  $score,					
				'text' => $text,						
				'type' =>  $type,				
			) 
		);	

		//Build response
		header('Content-Type: application/json');
		$response = new stdClass();
		$response->type = 'jsonReview';
		$response->message = 'Success'; 

		return new WP_REST_Response($response, 200 );
	}	

	public function jsonReviewList(WP_REST_Request $request  ) {
		header('Content-Type: application/json');
	
		//Check if student get call is enabled	
		$review_enable = !empty(get_option('review_enable')) ? get_option('review_enable') : true; 
		
		//User Id
		$id =  filter_var ($request['id'], FILTER_SANITIZE_NUMBER_INT) ;	

		//Messages
		global $wpdb;
		$selectReviews = "SELECT * FROM ".$wpdb->base_prefix."reviews WHERE receiverId = ".$id." OR senderId = ".$id;
		$reviews = $wpdb->get_results($selectReviews);			

		return new WP_REST_Response($reviews, 200 );
	}

	public function jsonPing(WP_REST_Request $request ) {	
		header('Content-Type: application/json');
		$response = new stdClass();
		$response->type = 'JsonUserExtensionAPI';
		$response->message = 'Success'; 
		
		return new WP_REST_Response( $response, 200 );
	}	
}

/* function prefix_register_routes(){
	$controller = new HermesAPI();
	$controller->register_routes();
	
	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	add_filter( 'rest_pre_serve_request', function( $value ) {	
		//$origin = get_http_origin();
		//$my_sites = array( 'http://localhost:8000/', );	
		//if ( in_array( $origin, $my_sites ) ) {
		//	header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
		//} else {
		//	header( 'Access-Control-Allow-Origin: ' . esc_url_raw( site_url() ) );
		//}
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
		header( 'Access-Control-Allow-Credentials: true' );
		header( 'Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept' );
		//header('Content-Type: application/json');
		return $value;
		
	});
}
add_action('rest_api_init', 'prefix_register_routes', 15); */
?>