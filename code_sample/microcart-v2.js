/**
 -- THIS CODE WAS TAKEN WITH THE PERMISSION OF ONE KINGS LANE --
 -- It is proprietary and can only be used for read only purposes to evaluate Samuel Choi --
 -- Original Github Source : https://github.com/okl/assets/blob/master/store/js/Checkout/microcart-v2.js --
 */

(function( window, undefined ) {
    var timer, hover_timer;
    var lokl = {
        microcart_v2: {
            init: function () {
                this.bindCartEvents();
                this.bindHoverEvents();
                this.restartTimer();
            },
            bindHoverEvents: function(){
                //hover over
                $('.cart-icon').on('mouseenter', function(){
                    if($('.micro-cart-line').length){
                        $('#micro-cart-container').addClass('active'); //css transition this sucker
                    }
                });

                //hover out: fading out when the mouse leaves the parent container of cart icon
                $('.offer-account-cart').on('mouseleave', function(){
                    hover_timer = setTimeout(function(){  $('#micro-cart-container').removeClass('active') }, 500);
                });

                //toggle remove button on hover
                $('.cart')
                    .on('mouseenter', '#micro-cart-container', function(){
                        clearTimeout(hover_timer);
                    })
                    .on('mouseenter', '#micro-cart-container .micro-cart-line', function(){
                        $(this).find('.remove').removeClass('hidden');
                    })
                    .on('mouseleave', '#micro-cart-container .micro-cart-line', function(){
                        $(this).find('.remove').addClass('hidden');
                    });
            },
            bindCartEvents: function() {
                //remove button
                $('.cart').on('click', '#micro-cart-container .remove', function(e){
                    e.preventDefault();
                    var _self = $(this);
                    var $cartlineCounter = $('.cart-line-counter');

                    if (parseInt($cartlineCounter.text()) > 1){
                        _self.parents('.micro-cart-line').addClass('deleted');
                    }else{
                        $('#micro-cart-container').removeClass('active');
                    }

                    OKL.microcart_v2.trackEvent(
                        {   action_type:"submit",
                            action_name:"cart_remove_from",
                            params_json: {
                                "remove_from_minicart": true
                            }
                        }, null, null);

                    $.getJSON(_self.attr('href'), function(data){
                        if(data.count){
                            _self.parents('.micro-cart-line').remove();
                            $cartlineCounter.text(data.count).removeClass('hidden');
                            $('#micro-cart-v2 .subtotal .amount').html(data.subtotal)
                        }else{
                            $('.cart-lines li').remove();
                            $cartlineCounter.text('').addClass('hidden');
                        }
                    });
                });

                //bind checkout button tracking
                //all it does now is track
                $('.cart a').on('click', '#micro-cart-container .checkout-btn', function(e) {
                    e.preventDefault();
                    var _self = $(this);
                    function defaultAction(){ window.location = _self.attr('href'); }
                    OKL.microcart_v2.trackEvent(
                        {   action_type: "submit",
                            action_name: "minicart_checkout",
                        }, defaultAction, defaultAction);
                });

                //binding add to cart success
                $(document).on('cart:add', function(e, response){
                    OKL.microcart_v2.handleAddToCart(response);
                });


            },
            /* building this from scratch because the first one is bloated as hell*/
            restartTimer: function() {
                //start cart timer
                var date = $('#micro-cart-v2').data('expiration_time');
                if(!date){
                    return;
                }
                var end = new Date(Number(date));

                var _second = 1000;
                var _minute = _second * 60;
                var _hour = _minute * 60;

                if(timer != undefined){
                    clearInterval(timer);
                }

                function showRemaining() {
                    var now = new Date();
                    var timeLeft = end - now;
                    if (timeLeft < 0) {
                        clearInterval(timer);
                        $('.timer-text').html('CART TIMED OUT');
                        $('.time-left').html('00:00').addClass('cart-expired');
                        return;
                    }
                    var minutes = Math.floor((timeLeft % _hour) / _minute);
                    var seconds = Math.floor((timeLeft % _minute) / _second);
                    var seconds_string = ("0" + seconds).slice(-2); //pad integer with 0 if needed

                    $('.time-left').html(minutes+':'+seconds_string);
                }
                showRemaining();
                timer = setInterval(showRemaining, 1000);
            },
            handleAddToCart: function(response){
                if(response.cart_markup != undefined && response.cart_markup){
                    $('#micro-cart-container').replaceWith(response.cart_markup);
                    OKL.microcart_v2.restartTimer();
                    setTimeout(function(){  $('#micro-cart-container').addClass('active') }, 50); //fade in the cart. need to delay just a bit to ease it in
                    hover_timer = setTimeout(function(){  $('#micro-cart-container').removeClass('active') }, 5000);
                    $('.cart-line-counter').text(response.count).removeClass('hidden');
                }
            },
            trackEvent: function(data, success, error){
                $.ajax({
                    type: "POST",
                    url: "/tracking",
                    data: data,
                    async: true,
                    dataType: "json",
                    success: success,
                    error: error
                });
            }


        }
    };
    window.OKL.extend( lokl );
} )( this );
