ALTER TABLE `messagedata` ADD `isPromotionCard` INT(11) NULL AFTER `align_desc`, ADD `promotionCardDiscountCode` VARCHAR(255) NULL AFTER `isPromotionCard`, ADD `isPromotionCardImage` INT(11) NULL AFTER `promotionCardDiscountCode`, ADD `promotionCardImageLink` VARCHAR(255) NULL AFTER `isPromotionCardImage`;