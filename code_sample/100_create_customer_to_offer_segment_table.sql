-- THIS CODE WAS TAKEN WITH THE PERMISSION OF ONE KINGS LANE --
-- It is proprietary and can only be used for read only purposes to evaluate Samuel Choi --
-- Original Github Source : https://github.com/okl/common/blob/7ceb59d0f866f7ea111af95fd75849cc1176f4f9/scripts/sql/deltas/cart/98/100_create_customer_to_offer_segment_table.sql --
CREATE TABLE `customer_to_offer_segment` (
  `customer_to_offer_segment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `offer_segment_id` int(11) NOT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `modified_date` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`customer_to_offer_segment_id`),
  KEY `idx_customer_id_offer_segment_id` (`customer_id`,`offer_segment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--//@UNDO
DROP TABLE `customer_to_offer_segment`;