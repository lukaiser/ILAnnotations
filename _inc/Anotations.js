/**
 * Created by lukas on 17.06.14.
 */

(function($, window, document) {

    // The $ is now locally scoped

    // Listen for the jQuery ready event on the document
    $(function() {
        // Check if comments are enabled
        if(WPILAnnotations.commentsON){
            //Add Annotation Button
            $('body').append('<span class="highlighter-container"><div><a href="#respond"><span class="annot-hide">Comment</span></a></div></span>');
            //Initiate the highlighter
            $('.entry-content').highlighter({
                'selector': '.highlighter-container',
                'minWords': 0,
                'complete': function (data) {
                }
            });
            //Add function to Annotation Button
            $('.highlighter-container a').mousedown(comment);
        }

        //Prepare existing comments
        $('.annot-start').each(annot);
        $('.annot-comment').each(existingcomments);

    });
    /*
     * Handles the mouse down on the Annotation Button
     */
    function comment() {
        //Disselect all highlighted annotations
        $( ".annot").removeClass( "annot-highlight");
        //Hide the comment button
        $('.highlighter-container').hide();
        //Scroll to the comment form
        $(document).scrollTop( $("#respond").offset().top );

        var html = $('.highlighter-container').data("htmlSel");

        if(html != ''){
            //Add form fields to the comment form
            if($(".comment-form-quote").length == 0){
                $(".comment-form-comment").before( '<p class="comment-form-quote"></p>' );
            }
            $(".comment-form-quote").html('<span class="annot-hide">Comment on Text</span> "'+html+'"');

            if($("#comment_quote").length == 0){
                $("#comment_parent").after('<input type="hidden" name="comment_quote" id="comment_quote">');
            }
            $("#comment_quote").attr("value", html);

            var htmlPrev = $('.highlighter-container').data("htmlPrev");
            if($("#comment_quote_prev").length == 0){
                $("#comment_quote").after('<input type="hidden" name="comment_quote_prev" id="comment_quote_prev">');
            }
            $("#comment_quote_prev").attr("value", htmlPrev);

            var htmlAfter = $('.highlighter-container').data("htmlAfter");
            if($("#comment_quote_after").length == 0){
                $("#comment_quote_prev").after('<input type="hidden" name="comment_quote_after" id="comment_quote_after">');
            }
            $("#comment_quote_after").attr("value", htmlAfter);
        }
    }
    /*
     * Display existing comments in the text
     */
    function annot(){
        //get comment id
        var id = $(this).attr('id');
        id = id.substring(12);
        //add a span around the annotation (multiple if necessary)
        $(this).nextUntilExtended('#annot-stop-'+id).wrap('<span class="annot annot-'+id+'"/>');
        //get all wrapper spans
        w = $('.annot-'+id);
        //add id
        w.prop("cid", id);
        //on roll over highlight all wrapper spans
        w.hover(
            function() {
                $( ".annot-"+$(this).prop("cid")).addClass( "annot-highlight");
            }, function() {
                $( ".annot-"+$(this).prop("cid") ).removeClass( "annot-highlight" );
            }
        );
        //if touch device add close button to qtip
        var buttonYes = ('ontouchstart' in window || navigator.msMaxTouchPoints);
        //add qtip with the comment
        $( w ).qtip({
            content:{
                text: function(event, api) {
                    var cid = $(this).prop("cid");
                    if($("#li-comment-"+cid).length > 0){
                        var html = $("#li-comment-"+cid).html();
                    }else{
                        //if commetn is not loaded yet, load it
                        $.ajax({
                            url: jQuery.query.set("comments_all", 1)
                        })
                        .then(function(content) {
                            // Set the tooltip content upon successful retrieval
                            api.set('content.text', $("#li-comment-"+cid, content));
                        }, function(xhr, status, error) {
                            // Upon failure... set the tooltip content to the status and error value
                            api.set('content.text', status + ': ' + error);
                        });

                        return 'Loading...';
                    }
                    return('<ol class="commentlist">'+html+'</ol>');
                },
                button: buttonYes
            },
            hide: { event: 'mouseleave', fixed: true, delay:200 },

            position: {
                my: 'top center',
                at: 'bottom center',
                viewport: $(window),
                adjust: {
                    method: 'shift none'
                }
            },
            style: {
                classes: 'qtip-light qtip-shadow'
            }
        });
    }
    function existingcomments(){
        //on click on quote jump to the annotation and highlight it
        $(this).mouseup(function() {
            var id = $(this).parents('.comment').attr('id');
            id  = id.substring(11);
            $( ".annot").removeClass( "annot-highlight");
            $( ".annot-"+id).addClass( "annot-highlight");
            $(document).scrollTop( $('#annot-start-'+id).offset().top );
        });

    }

    (function($){

        $.fn.extend({
            /*
             * Get everything till
             * @param the end of the selection
             * @return
             */
            nextUntilExtended: function(until) {
                var $set = $();
                var nxt = this.get(0).nextSibling;
                while(nxt) {
                    if(!$(nxt).is(until)) {
                        if(nxt.nodeType != 3 && $(nxt).has(until)){
                            nxt = nxt.firstChild;
                        }else{
                            $set.push(nxt);
                            if(nxt.nextSibling){
                                nxt = nxt.nextSibling;
                            }else{
                                nxt = nxt.parentNode.nextSibling;
                            }
                        }
                    } else break;
                }
                return($set);
            }
        });

    })(jQuery);

}(window.jQuery, window, document));
