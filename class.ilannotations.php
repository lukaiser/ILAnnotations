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
        add_filter( 'the_content', array( 'ILAnnotations', 'add_marker_to_content' ), 1 );

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

            $content = $post->post_content;

            $idprev = intval($_POST["comment_quote_idprev"]);
            $idafter = intval($_POST["comment_quote_idafter"]);

            $elements = preg_split('/\n\s*\n/', $content, -1, PREG_SPLIT_NO_EMPTY);
            if($idprev > 0){
                $elements[$idprev-1] = $elements[$idprev-1].'[ILAS-aasdfklahsdkfahslödfkahsdöf]';
            }
            if($idafter > 0){
                $elements[$idafter-1] = $elements[$idafter-1].'[ILAS-aasdfklahsdkfahslödfkahsdöf]';
            }

            $content = implode("\n\n", $elements);

            if(!($idprev > 0)){
                $content = '[ILAS-aasdfklahsdkfahslödfkahsdöf]'.$content;
            }
            if(!($idafter > 0)){
                $content = $content.'[ILAS-aasdfklahsdkfahslödfkahsdöf]';
            }

            $contents = explode('[ILAS-aasdfklahsdkfahslödfkahsdöf]', $content);


            require_once('class.ilannotations-searchmanager.php' );
            //find the right regex expression to add the shortcodes
            $sm = new ILAnnotations_Searchmanager($_POST["comment_quote"], $contents[1], $_POST["comment_quote_prev"], $_POST["comment_quote_after"]);
            $rg = $sm->solve();
            //add the shortcodes to the content
            if($rg !== false && $rg != ""){
                $contents[1] = preg_replace($rg, '$1[annot-s c="'.$id.'"/]$2[annot-e c="'.$id.'"/]$3', $contents[1]);
            }else{
                $contents[1] = '[annot-s c="'.$id.'"/]'.$contents[1].'[annot-e c="'.$id.'"/]';
            }
            $nc = implode("", $contents);

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

    public static function add_marker_to_content($content){
        $elements = preg_split('/\n\s*\n/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $i = 1;
        foreach($elements as &$element){
            $element = $element.'<span id="ILAS-'.$i.'" class="ILAS"></span>';
            $i ++;
        }
        $content = implode("\n\n", $elements);
        return $content;
    }
        
}