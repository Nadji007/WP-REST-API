<?php 
/*
  Plugin Name: ACTUALITES REST API
  Plugin URI:
  Description: This plugin provide to display all actualites  with pictures and attachment.
  Version: 1.0
  Author: Fidèle NADJINDO
  Author URI:
  License: GPLv2 

 */
 
define ( 'ACTUS_REST_PLUGIN_URL', plugin_dir_url( __FILE__) );

function my_actus_attachment($post_id) {
    global $wpdb;
	//  fonction permettant de récupérer l'ensemble des fichiers attachés(images, pdf...)
		$args = array(
				'post_type' => 'attachment',
				'posts_per_page' => -1, 
				'post_status' => 'any',
				'post_parent' => $post_id,
				);
			
			$attachments = get_posts($args);
				$post_att = [];
				if( $attachments ) {
					foreach ( $attachments as $attachment ) {
						$post_att [] = array(
							"id" => $attachment->ID,
							"title" => $attachment->post_title,
							"url"	=> $attachment->guid,
							"mime_type" => $attachment->post_mime_type,
						);
						
					}
				}
				return $post_att;
		
		}
	add_action('rest_api_init', 'my_actus_attachment');
		
//Get all the news/récupérer toutes les actualités 
function actus_prepare_read_actualites() {
	global $wpdb;
	$actus_query = new WP_Query(array(
				  'post_parent'=> $post_id,
				  'post_type' => 'post',
				  'category_name' => 'actualites',
				  'post_status' =>'publish',
				  'order' => 'desc', 
				  'posts_per_page' => -1, //-1 will return unlimited posts per page As the others users answer.
				  'orderby' => 'date',
				  ));			  

	$data['posts'] = array();
		if($actus_query->have_posts()){
        while($actus_query->have_posts()) : $actus_query->the_post();
		       $post_item = array(
					"id" => get_the_ID(),
					"title" => get_the_title(),
					"excerpt"	=> get_the_excerpt(),
					"content"   => get_the_content(),
				   	"attachment" => my_actus_attachment(get_the_ID($post->ID)),
					"date"		=> get_the_date( "Y-m-d H:i:s" ),
			);
			array_push($data['posts'], $post_item);		
	    endwhile;
	}

    // rest_ensure_response() wraps the data we want to return into a WP_REST_Response, and ensures it will be properly returned.
    return rest_ensure_response($data);
}
// API route record/Enregistrement de route API
add_action( 'rest_api_init', 'actus_rest_api_actus_route' );

function actus_rest_api_actus_route() {
	//$current_url="http//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];	
	$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	// Our URI to display actualities
	//http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]/wp-json/actus-rest-api/v1/actualites
    register_rest_route('actus-rest-api/v1', '/actualites', array(
        'methods'  => WP_REST_SERVER::READABLE,
        'callback' => 'actus_prepare_read_actualites',
   
    ));

}
 
 