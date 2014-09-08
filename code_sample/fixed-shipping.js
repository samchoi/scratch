/**
 -- THIS CODE WAS TAKEN WITH THE PERMISSION OF ONE KINGS LANE --
 -- It is proprietary and can only be used for read only purposes to evaluate Samuel Choi --
 -- Original Github Source : https://github.com/okl/assets/blob/master/store/js/fixed-shipping.js --
 */

/**
 * Handles interactions with the fixed-price shipping modal/promo/drawer.
 * @requires modals.js
 */

( function( window, undefined ) {
    var lokl = {
        fixedShipping : {
            init : function() {
                if ($('.desktop').length) {
                    var self = this,
                        links = '.ship-promo-exclusions';
                    $(links).on('click', function(e) {
                        window.open('https://www.onekingslane.com/brands/ship30ext/','_blank','width=760,height=700,scrollbars=yes');
                    });
                    if ($('.cart-summary').length > 0) {
                        self.adjustPositionForCartSummary();
                    }

                }
                else if ($('.iphone').length) {
                    var $promo = $('.fixed-ship-promo'),
                        headerHeight;
                    if ($promo.length === 0 || $.cookie('hideFixShip')) return;
                    headerHeight = $('.page-header').outerHeight(); //add 2 for border
                    $promo
                        .remove()
                        .appendTo('.page-header')
                        .css('top', headerHeight)
                        .find('.dismiss')
                        .on('click', function() {
                            var date = new Date();
                            var five_days_in_ms = 86400000 * 5
                            date.setHours(date.getTime() + five_days_in_ms);
                            $.cookie('hideFixShip', '1', {path: '/', domain: OKL.vars.cookie_domain, expires: date });
                            $promo.fadeOut('fast');
                        });

                    $promo.addClass('ready');
                }

                if ($('#curtain').length){
                    //init top drawer
                    $('#curtain').on('click', '.exclusions', function(){
                        //$('#curtain').addClass('active');
                        window.open('https://www.onekingslane.com/brands/ship30ext/','_blank','width=760,height=700,scrollbars=yes');
                    });

                    $('.close').on('click', function(){
                        $('#curtain').removeClass('active');
                    });

                    var date = new Date();
                    date.setHours(23, 59, 59, 999);

                    $('#curtain .dismiss').on('click', function(){
                        $('#curtain').addClass('hidden');
                        $.cookie('hide-curtain', true, {path: '/', expires: date});
                    });
                    var curtain = $.cookie('curtain');
                    /*
                     going to remove this entire file, but still want to preserve this logic

                     if(!curtain){
                     $.cookie('curtain', 'off', {path: '/', expires: date} );
                     window.setTimeout(function(){
                     $('#curtain').removeClass('hidden');
                     },500);
                     }*/


                }


            },
            adjustPositionForCartSummary: function() {
                // This pushes the promo message down to point to the "Shipping" line, on cart and review pages.
                // If there's no "Shipping" line, promo message points to "Subtotal"
                var row,
                    $shipping, $promoMsg,
                    top,
                    lineHeight;

                $promoMsg = $('.ship-msg');
                if ($promoMsg.length === 0) return;

                $shipping = $('.totals-container .shipping');
                if ($shipping.length > 0) {
                    row = $shipping.index('dt');
                    lineHeight = $shipping.outerHeight() - 1;
                    top = row * lineHeight;
                    $promoMsg.css('top', top + 'px');
                }
            }
        }
    };
    window.OKL = $.extend( true, {}, window.OKL || {}, lokl );
} )( this );