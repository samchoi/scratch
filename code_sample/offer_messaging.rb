# -- THIS CODE WAS TAKEN WITH THE PERMISSION OF ONE KINGS LANE --
# -- It is proprietary and can only be used for read only purposes to evaluate Samuel Choi --
# -- Original Github Source : https://github.com/okl/ewok/blob/35dc16310be823e16f594045519db580a9169382/app/controllers/concerns/offer_messaging.rb --

# Rspecs for this file are in application_controller_spec.rb
module OfferMessaging
  extend ActiveSupport::Concern

  # This is reading a cookie for a cached offer_segment
  def cached_offer_segment(customer_id)
    # 1: cookie is not set - go get it and cache based on result for an hour or the offer expiration date
    cookies['cached_offer_segment'] = OfferSegment.cookie_value(customer_id) if cookies['cached_offer_segment'].nil?

    # 2: cookie is set, but equal 'none' - customer IS NOT targeted, return nil
    return nil if cookies['cached_offer_segment'] == 'none'

    # 3. Cookie is set, not equal to empty hash - customer IS targeted
    OfferSegment.instantiate(JSON.parse(cookies['cached_offer_segment']))
  end

  def set_session_offer_to_cart
    return if cookies['offer_code'].nil?
    get_cart if @cart.nil?
    @cart = cart_client.activate_offer_code(@cart.cart_id, cookies['offer_code'])
    get_cart
    cookies.delete('offer_code') unless hide_cart_timer_abtest?
  end

  def reset_offer_cookies
    cookies.delete('cached_offer_segment')
    cookies.delete('offer_code')

    cached_offer_segment(current_user.customer_id) if current_user && current_user.customer_id
  end

end
