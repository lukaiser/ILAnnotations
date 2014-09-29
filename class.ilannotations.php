<?php

/**
 * Class ILAnnotations
 * Main plugin class
 */
class ILAnnotations {

    /**
     * @var bool if plugin is initiated
     */
    private static $initiated = false;

    /**
     *  Callback for init hook
     */
    public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;

		add_action( 'wp_enqueue_scripts', array( 'ILAnnotations', 'load_resources' ) );
        add_shortcode( 'annot-s', array( 'ILAnnotations', 'annot_start_shortcode' ) );
        add_shortcode( 'annot-e', array( 'ILAnnotations', 'annot_end_shortcode' ) );
        add_filter( 'pre_comment_content' , array( 'ILAnnotations', 'pre_comment_content_handle_annotation'), 11 ); //pre_comment_content
        add_action( 'wp_insert_comment', array( 'ILAnnotations', 'wp_insert_comment_handle_annotation'), 10, 2 );
        add_filter( 'option_page_comments' , array( 'ILAnnotations', 'option_page_comments_handle') );
        
	}

    /**
     * Add resources (css and js) to the page
     */
    public static function load_resources() {
            wp_register_style( 'jquery.qtip.min.css', '//cdn.jsdelivr.net/qtip2/2.2.0/jquery.qtip.min.css', array(), ILANNOTATIONS_VERSION );
			wp_enqueue_style( 'jquery.qtip.min.css');
			wp_register_style( 'Annotations.css', ILANNOTATIONS__PLUGIN_URL . '_inc/Annotations.css', array(), ILANNOTATIONS_VERSION );
			wp_enqueue_style( 'Annotations.css');

            wp_register_script( 'detectmobilebrowser.js', ILANNOTATIONS__PLUGIN_URL . '_inc/detectmobilebrowser.js', array('jquery'), ILANNOTATIONS_VERSION );
            wp_enqueue_script( 'detectmobilebrowser.js' );
            wp_register_script( 'jQuery.highlighter.js', ILANNOTATIONS__PLUGIN_URL . '_inc/jQuery.highlighter.js', array('jquery'), ILANNOTATIONS_VERSION );
			wp_enqueue_script( 'jQuery.highlighter.js' );
            wp_register_script( 'jquery.query-object.js', ILANNOTATIONS__PLUGIN_URL . '_inc/jquery.query-object.js', array('jquery'), ILANNOTATIONS_VERSION );
            wp_enqueue_script( 'jquery.query-object.js' );
            wp_register_script( 'jquery.qtip.min.js', '//cdn.jsdelivr.net/qtip2/2.2.0/jquery.qtip.min.js', array('jquery'), ILANNOTATIONS_VERSION );
			wp_enqueue_script( 'jquery.qtip.min.js' );
			wp_register_script( 'Anotations.js', ILANNOTATIONS__PLUGIN_URL . '_inc/Anotations.js', array('jquery'), ILANNOTATIONS_VERSION );
			
			wp_localize_script( 'Anotations.js', 'WPILAnnotations', array(
				'commentsON' => comments_open()/*,
				'strings' => array(
					'Remove this URL' => __( 'Remove this URL' , 'akismet'),
					'Removing...'     => __( 'Removing...' , 'akismet'),
					'URL removed'     => __( 'URL removed' , 'akismet'),
					'(undo)'          => __( '(undo)' , 'akismet'),
					'Re-adding...'    => __( 'Re-adding...' , 'akismet'),
				)*/
			) );
            wp_enqueue_script( 'Anotations.js' );
	}

    /**
     * Annotaion Shortcode - Start of a annotation
     * @param array $atts Attributes of the shortcode - only c for the comment id is accepted and required
     * @param null $content the Content in the code
     * @return null|string
     */
    public static function annot_start_shortcode( $atts , $content = null ) {

        // Attributes
        extract( shortcode_atts(
            array(
                'c' => false,
            ), $atts )
        );

        //Check if the comment is shown then add the span for the javascript
        if($c && wp_get_comment_status($c) == 'approved'){
            return('<span id="annot-start-'.$c.'" class="annot-start"></span>'.$content);
        }else{
            return($content);  
        }
    }

    /**
     * Annotaion Shortcode - End of a annotation
     * @param array $atts Attributes of the shortcode - only c for the comment id is accepted and required
     * @param null $content the Content in the code
     * @return null|string
     */
    public static function annot_end_shortcode( $atts , $content = null ) {

        // Attributes
        extract( shortcode_atts(
            array(
                'c' => false,
            ), $atts )
        );

        //Check if the comment is shown then add the span for the javascript
        if($c && wp_get_comment_status($c) == 'approved'){
            return('<span id="annot-stop-'.$c.'" class="annot-stop"></span>'.$content);
        }else{
            return($content);  
        }
    }

    /**
     * pre_comment_content hook callback
     * Adds the text that gets annotated to the comment
     * @param string $commentdata the comment
     * @return string
     */
    public static function pre_comment_content_handle_annotation($commentdata){
        if(array_key_exists("comment_quote", $_POST)){
            $quote = $_POST["comment_quote"];
            $commentdata  = '<p class="annot-comment"><span class="annot-comment-intro">Annotation to: </span>"'.$quote.'"</p>'.$commentdata;
        }
        return($commentdata);
    }

    /**
     * wp_insert_comment hook callback
     * Adds the annotation shortcodes to the content of the post
     * @param $id the id of the comment
     * @param $comment the Comment
     */
    public static function wp_insert_comment_handle_annotation($id, $comment){
        if(array_key_exists("comment_quote", $_POST)){
            $postid = $comment->comment_post_ID;
            $post = get_post( $postid );
            require_once('class.ilannotations-searchmanager.php' );
            //find the right regex expression to add the shortcodes
            $sm = new ILAnnotations_Searchmanager($_POST["comment_quote"], $post->post_content, $_POST["comment_quote_prev"], $_POST["comment_quote_after"]);
            $rg = $sm->solve();
            //add the shortcodes to the content
            $nc = preg_replace($rg, '$1[annot-s c="'.$id.'"/]$2[annot-e c="'.$id.'"/]$3', $post->post_content);
            //save the post
            $new_post = array(
                  'ID'           => $postid,
                  'post_content' => $nc
              );
            wp_update_post (add_magic_quotes($new_post));
        }
    }

    /**
     * option_page_comments hook callback
     * Show all comments - no pagination - if comments_all=true is added to the url
     * This is needed for the javascript to load missing comments when rolling of a annotation in the text
     * @param $default
     * @return bool
     */
    public static function option_page_comments_handle($default){
        if(isset($_GET["comments_all"])){
            return false;
        }
        return $default;
    }
        
}