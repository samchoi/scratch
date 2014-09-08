# -- THIS CODE WAS TAKEN WITH THE PERMISSION OF ONE KINGS LANE --
# -- It is proprietary and can only be used for read only purposes to evaluate Samuel Choi --
# -- Original Github Source : https://github.com/okl/ewok/blob/911b247352918d6b18c501a084be07963f4e048a/app/models/json/product_json.rb#L76 --
class Json::ProductJson
  # code redacted by request of One Kings Lane

  # looks up all products'
  def self.products_from_cache product_ids
    return if product_ids.blank?
    cache_keys = product_ids.map{ |pid| "okl:product-skus:#{pid}"}
    cached_objects = Rails.cache.read_multi(cache_keys)
    products = {}
    #all cache keys that did not have an entry should get replaced
    (cache_keys - cached_objects.keys).each do |key|
      pid = key.split(':').last.to_i
      product = Product.find_by_product_id(pid)
      next if product.nil?
      # getting the sku option name
      sku_option_name = product.skus.first.sku_option_values.first ? product.skus.first.sku_option_values.first.sku_option.sku_option_description.name : nil
      products[pid] = { skus: {}, option_name: sku_option_name}
      product.skus.each do |sku|
        sku_name = sku.sku_option_values.first ? sku.sku_option_values.first.sku_option_value_description.name : nil
        products[pid][:skus][sku.sku_id] = { id: sku.sku_id, name: sku_name}
      end
      Rails.cache.write(key, products[pid])
      cached_objects[key] = products[pid]
    end
    cached_objects
  end


  def self.product_inventories product_ids
    product_skus = products_from_cache(product_ids)
    products = {};
    product_skus.each do |key, ps|
      sku_qtys = Json::ProductInventoryStateJson.get_skus_qty(ps[:skus].map{ |sid, sku| sku[:id]})
      pid = key.split(':').last.to_i
      products[pid] = {skus: [], option_name: nil}
      products[pid][:option_name] = ps[:option_name]
      ps[:skus].each do |sid, sku|
        qty = sku_qtys[sid]
        products[pid][:skus] << {id:sku[:id], qty: qty, name: sku[:name]} if qty > 0
      end
    end
    products
  end
end