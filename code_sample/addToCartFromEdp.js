/**
 -- THIS CODE WAS TAKEN WITH THE PERMISSION OF ONE KINGS LANE --
 -- It is proprietary and can only be used for read only purposes to evaluate Samuel Choi --
 -- Original Github Source : https://github.com/okl/assets/blob/b65dd65361361f46562e8e068722c17cd39e9240/store/js/SalesEvent/addToCartFromEdp.js --
 */

( function( window, undefined ) {
    var lokl = {
        addToCartFromEdp: {
            init: function() {
                if($(".addToCartFromEDP").length > 0) {
                    $(".addToCartFromEDP").click(function(e){
                        this.addToCartFromEDP(e);
                    }.bind(this));

                    $('select.opt').change(function(e){
                        this.updateQuantity(e.target);
                    }.bind(this));
                }
            },

            addToCartFromEDP:function(event){
                var target = $(event.target);
                var targetData = event.target.dataset;

                var productId = targetData.product_id;
                var salesEventId = targetData.sales_event_id;
                var searchUuid = targetData.search_uuid;
                var dsCatId = "-1";
                var skuId = target.siblings('.opt').val() || targetData.sku_id;
                var quantity = target.siblings('.qty').val() || targetData.quantity;

                var prodData = {
                    "skuId":skuId,
                    "productId":productId,
                    "salesEventId":salesEventId,
                    "quantity":quantity,
                    "dsCatId":dsCatId,
                    "searchUuid":searchUuid
                };

                $.ajax({
                    context:this,
                    type:'POST',
                    url: '/cart/add_cart_line',
                    data: prodData ,
                    success: function(response){
                        // inventory error is baked in the status attr, even though the response returns successfully
                        if(response.status == "ERROR"){
                            alert(response.error_msg);
                            return;
                        }
                        this.handleAddToCartSuccess(response, prodData);

                        // update available quantity in dropdown menu when ATC is successful
                        var qtyMenu = target.siblings('.qty');
                        var selectedQty = qtyMenu.find(':selected').val();
                        var totQty =qtyMenu.attr('tot_qty');
                        qtyMenu.attr('tot_qty', totQty - selectedQty);


                        var selectedSkuMenu = target.siblings('.opt').find(':selected');
                        // the sku selection menu might not exist
                        if(selectedSkuMenu.length > 0){
                            selectedSkuMenu.attr("quantity", selectedSkuMenu.attr('quantity')-selectedQty);
                            this.updateQuantity(target.siblings('.opt'));
                        }
                        else {
                            this.updateQuantity(target);
                        }

                    }

                });
            },

            // update and render quantity options in each sku
            updateQuantity:function(target){
                var target = $(target);
                var qtyMenu = target.siblings('.qty');
                var selectedSkuMenu = target.parent().children('.opt').find(':selected');
                // depends on the number of skus a product hold, sku menu might not be displayed
                if(selectedSkuMenu){
                    qtyMenu.attr('sku', selectedSkuMenu.val());
                    qtyMenu.attr('tot_qty', selectedSkuMenu.attr('quantity'));
                }

                var totalQty =qtyMenu.attr('tot_qty');
                qtyMenu.empty();
                _.each( _.range(0,totalQty), function(qty){
                    var optionTemp = $("<option></option>");
                    optionTemp.val(qty+1);
                    optionTemp.html(qty+1);
                    qtyMenu.append(optionTemp);

                });
            },

            // TODO: this is copied over from product-detail, how do we keep dry
            handleAddToCartSuccess: function(response, data) {
                var cartLineCount, $cartLineCounter = $('.cart-line-counter');

                if(response.new_cart_line_added) {    //TODO implement this server side or in backbone??
                    //TODO test this
                    if (typeof OKL.ometric === 'object' && OKL.ometric.hasOwnProperty('track')) {
                        OKL.ometric.track({
                            name : 'add_cart',
                            id: data.productId,
                            sku_id: data.skuId,
                            event_id: data.salesEventId,
                            quantity: data.quantity
                        });
                    }
                }

                //workaround for weird DB replication issue SFFO-2082
                if (response.display_microcart) {
                    cartLineCount = response.cart_lines.length;
                    OKL.vars.cart = response;
                }
                else {
                    cartLineCount = parseInt($cartLineCounter.text() || 0)+1;
                }

                //update cart line counter quantity
                $cartLineCounter.text(cartLineCount).show();

                //need to reset microcart after updating counter on page
                if (response.display_microcart) {
                    //reload the cart with the data
                    OKL.microCart.reset(response.cart_lines, response.expiration_time);
                }

            }
        }
    };
    window.OKL.extend( lokl );
} )( this );