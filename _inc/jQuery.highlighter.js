/* global jQuery */

/*
 * Highlighter.js 1.0
 *
 * Author: Matthew Conlen <matt.conlen@huffingtonpost.com>
 *         Huffington Post Labs
 *
 * Copyright 2012: Huffington Post Labs
 *
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the WTFPL, Version 2, as
 * published by Sam Hocevar. See http://sam.zoy.org/wtfpl/
 * for more details.
 */

   (function ($) {
    /*
     * Code for triple click from
     * http://css-tricks.com/snippets/jquery/triple-click-event/
     */
    $.event.special.tripleclick = {

        setup: function (data, namespaces) {
            var elem = this,
                $elem = jQuery(elem);
            $elem.bind('click', jQuery.event.special.tripleclick.handler);
        },

        teardown: function (namespaces) {
            var elem = this,
                $elem = jQuery(elem);
            $elem.unbind('click', jQuery.event.special.tripleclick.handler);
        },

        handler: function (event) {
            var elem = this,
                $elem = jQuery(elem),
                clicks = $elem.data('clicks') || 0;
            clicks += 1;
            if (clicks === 3) {
                clicks = 0;

                // set event type to "tripleclick"
                event.type = "tripleclick";

                // let jQuery handle the triggering of "tripleclick" event handlers
                jQuery.event.dispatch.apply(this, arguments);
            }
            $elem.data('clicks', clicks);
        }
    };

    /*
     * Attempt to get the previous sibling
     * of a container in the event of a triple
     * click.
     *
     * Adapted from http://stackoverflow.com/a/574922
     */
    function get_previoussibling(n) {
        var y = n, x;
        try {
            x = n.previousSibling;
            while (x && x.nodeType != 1) {
                y = x;
                x = x.previousSibling;
            }
        } catch (err) {
            console.log(err);
            topOffset = -15;
            return y;
        }
        return x ? x : y;
    }

    var methods = {
        init: function (options) {
            var settings = $.extend({
                'selector': '.highlighter-container',
                'minWords': 0,
                'complete': function() {}
            }, options);
            var numClicks = 0;
            var topOffset = 0;
            var leftOffset = 0;
            var isDown = false;

            var selText;


            return this.each(function () {
                /*
                 * Insert an html <span> after a user selects text.
                 * We then use the X-Y coordinates of that span
                 * to place our tooltip.
                 * Thanks to http://stackoverflow.com/a/3599599 for
                 * some inspiration.
                 */
                function insertSpanAfterSelection(clicks) {
                    if('ontouchstart' in window || navigator.msMaxTouchPoints){
                        $(settings.selector).hide(); //ADDON
                    }
                    var html = "<span class='dummy'><span>";
                    topOffset = 0;
                    leftOffset = 0;
                    if (numClicks !== clicks) return;
                    var isIE = (navigator.appName === "Microsoft Internet Explorer");
                    var range, expandedSelRange, node;
                    var position;
                    if (window.getSelection) {
                        var sel = window.getSelection();
                        selText = getHtmlFromRange(sel);


                        if ($.trim(selText) === '' || selText.split(' ').length < settings.minWords) return;

                        var selRange = sel.getRangeAt (0);
                        //MY ADDON: In Container?
                        var c = document.createRange();
                        c.selectNode($('.entry-content').get(0));
                        if(selRange.compareBoundaryPoints (Range.START_TO_START, c) == -1 || selRange.compareBoundaryPoints (Range.END_TO_END, c) == 1){
                            return;
                        }

                        //MY ADDON: Check overlap
                        var valid = true;
                        $('.annot-start').each(function(){
                            var id = $(this).attr('id');
                            id = id.substring(12);
                            var end = $('#annot-stop-'+id);
                            var anoRange = document.createRange();
                            anoRange.setStartAfter($(this).get(0));
                            anoRange.setEndBefore($(end).get(0));
                            if(!(selRange.compareBoundaryPoints (Range.START_TO_END, anoRange) <= 0 || selRange.compareBoundaryPoints (Range.END_TO_START, anoRange) >= 0)){
                                valid = false;
                            }
                        });
                        if(!valid) return;
                        //END



                        if (sel.getRangeAt && sel.rangeCount) {



                            //ADD BOX

                            range = window.getSelection().getRangeAt(0);

                            expandedSelRange = range.cloneRange();
                            expandedSelRange.collapse(false);

                            // Range.createContextualFragment() would be useful here but is
                            // non-standard and not supported in all browsers (IE9, for one)
                            var el = document.createElement("div");
                            el.innerHTML = html;
                            var dummy = document.createElement("span");

                            if (range.startOffset === 0 && range.endOffset === 0) {

                                var cont = expandedSelRange.startContainer;
                                var prev = get_previoussibling(cont);
                                try {
                                    expandedSelRange.selectNode(prev.lastChild);
                                } catch (err) {
                                    leftOffset = 40;
                                    topOffset = -15;
                                    expandedSelRange.selectNode(prev);
                                }
                                // console.log(expandedSelRange);
                                expandedSelRange.collapse(false);
                            } else if(range.endOffset === 0 ) {
                                topOffset = -25;
                                leftOffset = 40;
                            }


                            if (numClicks !== clicks) return;
                            $(settings.selector).hide();
                            if (!isIE && $.trim(selText) === $.trim(expandedSelRange.startContainer.innerText)) {
                                expandedSelRange.startContainer.innerHTML += "<span class='dummy'>&nbsp;</span>";
                                position = $(".dummy").offset();
                                $(".dummy").remove();
                            } else if (!isIE && $.trim(selText) === $.trim(expandedSelRange.endContainer.innerText)) {
                                expandedSelRange.endContainer.innerHTML += "<span class='dummy'>&nbsp;</span>";
                                position = $(".dummy").offset();
                                $(".dummy").remove();
                            } else {
                                expandedSelRange.insertNode(dummy);
                                position = $(dummy).offset();
                                dummy.parentNode.removeChild(dummy);
                            }


                            //GET CONTENT
                            $(settings.selector).data("htmlSel", selText);

                            var prev = document.createRange();
                            prev.setStart($('#content').get(0),0)
                            prev.setEnd(sel.getRangeAt(0).startContainer, sel.getRangeAt(0).startOffset);
                            var htmlPrev = getHtmlFromRange(prev);
                            $(settings.selector).data("htmlPrev", htmlPrev);

                            var after = document.createRange();
                            after.setStart(sel.getRangeAt(sel.rangeCount-1).endContainer, sel.getRangeAt(sel.rangeCount-1).endOffset);
                            after.setEnd($('#content').get(0), $('#content').get(0).childNodes.length);
                            var htmlAfter = getHtmlFromRange(after);
                            $(settings.selector).data("htmlAfter", htmlAfter);

                        }
                    } else if (document.selection && document.selection.createRange) {

                        //TODO everything I added for the other case
                        range = document.selection.createRange();
                        expandedSelRange = range.duplicate();

                        selText = expandedSelRange.text;
                        if ($.trim(selText) === '' || selText.split(' ').length < settings.minWords) return;

                        range.collapse(false);
                        range.pasteHTML(html);

                        expandedSelRange.setEndPoint("EndToEnd", range);
                        expandedSelRange.select();
                        position = $(".dummy").offset();
                        $(".dummy").remove();
                    }

                    if('ontouchstart' in window || navigator.msMaxTouchPoints){
                        topOffset += 23;
                    }

                    $(settings.selector).css("top", position.top + topOffset + "px");
                    $(settings.selector).css("left", position.left + leftOffset + "px");
                    $(settings.selector).fadeIn(100, function() {
                        settings.complete(selText);
                    });
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

                $(settings.selector).hide();
                $(settings.selector).css("position", "absolute");

                if(!('ontouchstart' in window || navigator.msMaxTouchPoints)){
                    $(document).bind('mouseup.highlighter', function (e) {
                        if (isDown) {
                            numClicks = 1;
                            clicks = 0;
                            setTimeout(function () {
                                insertSpanAfterSelection(1);
                            }, 300);
                            isDown = false;
                        }
                    });
                    $(this).bind('mouseup.highlighter', function (e) {
                        numClicks = 1;
                        clicks = 0;
                        setTimeout(function () {
                            insertSpanAfterSelection(1);
                        }, 300);
                    });
                    $(this).bind('tripleclick.highlighter', function (e) {
                        numClicks = 3;
                        setTimeout(function () {
                            insertSpanAfterSelection(3);
                        }, 200);
                    });

                    $(this).bind('dblclick.highlighter', function (e) {
                        numClicks = 2;
                        setTimeout(function () {
                            insertSpanAfterSelection(2);
                        }, 300);
                    });
                    $(this).bind('mousedown.highlighter', function (e) {
                        $(settings.selector).hide();
                        isDown = true;
                    });
                    $(document).bind('mousedown.highlighter', function (e) {
                        $(settings.selector).hide();
                    });
                }else{
                    //MY addon

                    document.onselectionchange = userSelectionChanged;

                    function userSelectionChanged() {
                        // wait 500 ms after the last selection change event
                        if (typeof selectionEndTimeout != 'undefined') {
                            clearTimeout(selectionEndTimeout);
                        }

                        selectionEndTimeout = setTimeout(function () {
                            $(window).trigger('selectionEnd');
                        }, 500);
                    }

                    $(window).bind('selectionEnd', function () {
                        numClicks = 1;
                        // reset selection timeout
                        selectionEndTimeout = null;

                        insertSpanAfterSelection(1);
                    });

                    //ENDE
                }

            });
        },
        destroy: function (content) {
            return this.each(function () {
                $(document).unbind('mouseup.highlighter');
                $(this).unbind('mouseup.highlighter');
                $(this).unbind('tripleclick.highlighter');
                $(this).unbind('dblclick.highlighter');
                $(this).unbind('mousedown.highlighter');
            });
        }
    };

    /*
     * Method calling logic taken from the
     * jQuery article on best practices for
     * plugins.
     *
     * http://docs.jquery.com/Plugins/Authoring
     */
    $.fn.highlighter = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.highlighter');
        }

    };

})(jQuery);