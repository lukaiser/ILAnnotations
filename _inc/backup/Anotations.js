/**
 * Created by lukas on 17.06.14.
 */

(function($, window, document) {

    // The $ is now locally scoped

    // Listen for the jQuery ready event on the document
    $(function() {
        if(WPILAnnotations.commentsON){
            $('body').append('<span class="highlighter-container"><div><a href="#respond"><span class="annot-hide">Comment</span></a></div></span>');
            $('.entry-content').highlighter({
                'selector': '.highlighter-container',
                'minWords': 0,
                'complete': function (data) {
                }
            });
            $('.highlighter-container a').mousedown(comment);
        }

        $('.annot-start').each(annot);
        $('.annot-comment').each(existingcomments);

    });

    function comment() {
        $( ".annot").removeClass( "annot-highlight");
        $('.highlighter-container').hide();
        $(document).scrollTop( $("#respond").offset().top );
        var sel = $('.highlighter-container').data("sel");
        console.log(sel);
        var html = "";
        if(sel){
            html = getHtmlFromRange(sel);
        }else{
            if (typeof window.getSelection != "undefined") {
                sel = window.getSelection();
                html = getHtmlFromRange(sel);

            } else if (typeof document.selection != "undefined") {
                /*if (document.selection.type == "Text") {
                    container = document.selection.createRange();
                    html = container.htmlText;
                }TODO*/
            }
        }
        console.log("GO:");
        console.log(html);
        if(html != ''){

            if($(".comment-form-quote").length == 0){
                $(".comment-form-comment").before( '<p class="comment-form-quote"></p>' );
            }
            $(".comment-form-quote").html('<span class="annot-hide">Comment on Text</span> "'+html+'"');

            if($("#comment_quote").length == 0){
                $("#comment_parent").after('<input type="hidden" name="comment_quote" id="comment_quote">');
            }
            $("#comment_quote").attr("value", html);

            var prev = document.createRange();
            prev.setStart($('#content').get(0),0)
            prev.setEnd(sel.getRangeAt(0).startContainer, sel.getRangeAt(0).startOffset);
            var htmlPrev = getHtmlFromRange(prev);
            if($("#comment_quote_prev").length == 0){
                $("#comment_quote").after('<input type="hidden" name="comment_quote_prev" id="comment_quote_prev">');
            }
            $("#comment_quote_prev").attr("value", htmlPrev);

            var after = document.createRange();
            after.setStart(sel.getRangeAt(sel.rangeCount-1).endContainer, sel.getRangeAt(sel.rangeCount-1).endOffset);
            after.setEnd($('#content').get(0), $('#content').get(0).childNodes.length);
            var htmlAfter = getHtmlFromRange(after);
            if($("#comment_quote_after").length == 0){
                $("#comment_quote_prev").after('<input type="hidden" name="comment_quote_after" id="comment_quote_after">');
            }
            $("#comment_quote_after").attr("value", htmlAfter);
        }
    }
    function getHtmlFromRange(sel){
        var container = document.createElement("div");
        if (sel instanceof Selection){
            if(sel.rangeCount > 0){
                for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                    container.appendChild(sel.getRangeAt(i).cloneContents());
                }
            }else{
                return(sel.toString());
            }
        }else{
            container.appendChild(sel.cloneContents());
        }
        return ($(container).text());
    }
    function annot(){
        var id = $(this).attr('id');
        id = id.substring(12);
        $(this).nextUntilExtended('#annot-stop-'+id).wrap('<span class="annot annot-'+id+'"/>');
        w = $('.annot-'+id);
        w.prop("cid", id);
        w.hover(
            function() {
                $( ".annot-"+$(this).prop("cid")).addClass( "annot-highlight");
            }, function() {
                $( ".annot-"+$(this).prop("cid") ).removeClass( "annot-highlight" );
            }
        );
        $( w ).qtip({
            content: function() {
                return('<ol class="commentlist">'+$("#li-comment-"+$(this).prop("cid")).html()+'</ol>');
            },
            hide: { event: 'mouseleave', fixed: true, delay:200 },

            position: {
                my: 'top center',
                at: 'bottom center'
            },
            style: {
                classes: 'qtip-light qtip-shadow'
            }
        });
    }
    function existingcomments(){
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
