<?php
/**
* Plugin Name: Test plugin
* Description:  
* Version: 0.1
* Author: Samaneh Mirrajabi
**/


class myClass {

    function __construct() {

        add_action( 'init', array( $this, 'sm_video_posttype' )  );
        add_action( 'init', array( $this, 'sm_genre_taxonomy' ) );
        add_action('init',  array( $this, 'sm_tag' ) );
        add_action( 'add_meta_boxes', array( $this, 'iam_add_meta_box' ) );
        add_action( 'save_post', array( $this, 'sm_save_meta_box_data' ) );
        add_shortcode('video', array($this,'sm_video'));
    }

    /**
     * Add Custom Post Type 
     */
    function sm_video_posttype() {
        $labels = array(
            'name'                => _x( 'Videos', 'Post Type General Name', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'singular_name'       => _x( 'Videos', 'Post Type Singular Name', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'menu_name'           => esc_html__( 'Videos', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'parent_item_colon'   => esc_html__( 'Parent Videos', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'all_items'           => esc_html__( 'All Videos', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'view_item'           => esc_html__( 'View Videos', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'add_new_item'        => esc_html__( 'Add New Videos', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'add_new'             => esc_html__( 'Add New', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'edit_item'           => esc_html__( 'Edit Videos', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'update_item'         => esc_html__( 'Update Videos', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'search_items'        => esc_html__( 'Search Videos', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'not_found'           => esc_html__( 'Not Found', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'not_found_in_trash'  => esc_html__( 'Not found in Trash', 'CRUNCHIFY_TEXT_DOMAIN' ),
        );	
        $args = array(
            'label'               => esc_html__( 'videos', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'description'         => esc_html__( 'Videos', 'CRUNCHIFY_TEXT_DOMAIN' ),
            'labels'              => $labels,
            'supports'            => array( 'title','editor','thumbnail'),
            'taxonomies'          => array( 'genre' ),
        
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 100,
            'can_export'          => true,
            'has_archive'         => __( 'videos' ),
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'query_var' 		  => true,
            'show_admin_column'   => true,
            'capability_type'     => 'post',
            'rewrite' => array('slug' => 'videos/%tourist%'),
        );
        register_post_type( 'videos', $args );
    }

    

    /**
     * Add Taxonomies to Custom Post Type
     */
    function sm_genre_taxonomy() {  
        register_taxonomy(  
            'genre',  					// This is a name of the taxonomy.
            'videos',        			//post type name
            array(  
                'hierarchical' => true,  
                'label' => 'Genre',  	//Display name
                'query_var' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'videos')
            )  
        );  
    }  

    /**
     * Add Tag to Custom Post Type
     */
    function sm_tag() {
        register_taxonomy_for_object_type('post_tag', 'videos');
     }
  
 

    /**
     * Adds a meta box to the custom post type
     */
    public function iam_add_meta_box(){

        add_meta_box(
            'custom_meta_box',
            'Meta Box Superscript',
            array( $this, 'sm_custom_meta_box' ),
            'videos',
            'normal',
            'high'
        );

    }

    /**
     * Render Meta Box content.
     */
    public function sm_custom_meta_box() {

        $html = '';

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'sm_nonce_check', 'sm_nonce_check_value' );

        $html = '<label for="super-text" class="prfx-row-title">Superscript: </label>';
        $html .= '<textarea  name="super-text" id="super-text" width="300">' . get_post_meta( get_the_ID(), 'super-text', true ) . '</textarea>';

        echo $html;
    }

    /**
     * Save the meta when the post is saved.
     */
    public function sm_save_meta_box_data( $post_id ){

        if ( $this->sm_user_can_save( $post_id, 'sm_nonce_check_value' ) ){

            // Checks for input and sanitizes/saves if needed
            if( isset( $_POST[ 'super-text' ] ) && 0 < count( strlen( trim( $_POST['super-text'] ) ) ) ) {

                update_post_meta( $post_id, 'super-text', sanitize_text_field( $_POST[ 'super-text' ] ) );

            }

        }

    }

    /**
     * Determines whether or not the current user has the ability to save meta 
     * data associated with this post.
    */
    public function sm_user_can_save( $post_id, $nonce ){

        // Checks save status
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], 'sm_nonce_check' ) ) ? 'true' : 'false';

        if ( $is_autosave || $is_revision || !$is_valid_nonce ) {

            return false;
        }

        return true;

    }


  /**
     * List custom post type posts
     * simple: [video count="5"] 
     * 
    */
     function sm_video($atts) {

        $out='';
        $args = array(
        'post_type' => 'videos',
        'post_per_page' => $atts['count']
         );
        $q = new WP_Query( $args);
        
        if ( $q->have_posts() ) {
            while ( $q->have_posts() ) {
            $q->the_post();        
            $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full');
           
            $out .='<div><img src="'. $featured_img_url .'" width="100%" /></div>';
            $out .='<h2>'.get_the_title(). '</h2>';
            $out .='<div><ul>';
            foreach ( get_the_terms( get_the_ID(), 'genre' ) as $tax ) {
                $out .= '<li><span>' .  $tax->name  . '</span></li> ';
            }
            $out .='</ul></div>';
            $out .='<div><ul>';
            $terms =get_the_terms( $post->ID , 'post_tag' );
            foreach ( $terms as $term ) {
                $out .= '<li><small>' .  $term->name  . '</small></li>';
            }
            $out .='</ul></div>';
            $out .='<div><p>'.get_the_content().'</p></div>';
            }
            wp_reset_postdata();
        }
return $out;
    }



}

$my_class = new myClass();

?>