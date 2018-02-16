<?php
class Teamwork_Common_Helper_Staging_Relation extends Mage_Core_Helper_Abstract
{
    const CHQ_RELATION_TYPE_CROSS_SELLS = 'cross-sells';
    const CHQ_RELATION_TYPE_RELATED = 'related';
    const CHQ_RELATION_TYPE_UP_SELLS = 'up-sells';
    
    const CHQ_RELATION_HIERARCHY_STYLE = 'RelatedStyle';
    const CHQ_RELATION_HIERARCHY_ITEM = 'RelatedItem';
    
    public function getRelationType($relation)
    {
        switch($relation)
        {
            case self::CHQ_RELATION_TYPE_CROSS_SELLS:
            {
                return Teamwork_Common_Model_Staging_Relation::DB_RELATION_CROSS_SELLS;
            }
            case self::CHQ_RELATION_TYPE_RELATED:
            {
                return Teamwork_Common_Model_Staging_Relation::DB_RELATION_RELATED;
            }
            case self::CHQ_RELATION_TYPE_UP_SELLS:
            {
                return Teamwork_Common_Model_Staging_Relation::DB_RELATION_UP_SELLS;
            }
        }
    }
    
    public function getRelationKind($isItem,$hierarchy)
    {
        switch($isItem)
        {
            case false:
            {
                switch($hierarchy)
                {
                    case self::CHQ_RELATION_HIERARCHY_STYLE:
                    {
                        return Teamwork_Common_Model_Staging_Relation::DB_RELATION_KIND_STYLE_TO_STYLE;
                    }
                    case self::CHQ_RELATION_HIERARCHY_ITEM:
                    {
                        return Teamwork_Common_Model_Staging_Relation::DB_RELATION_KIND_STYLE_TO_ITEM;
                    }
                }
            }
            case true:
            {
                switch($hierarchy)
                {
                    case self::CHQ_RELATION_HIERARCHY_STYLE:
                    {
                        return Teamwork_Common_Model_Staging_Relation::DB_RELATION_KIND_ITEM_TO_STYLE;
                    }
                    case self::CHQ_RELATION_HIERARCHY_ITEM:
                    {
                        return Teamwork_Common_Model_Staging_Relation::DB_RELATION_KIND_ITEM_TO_ITEM;
                    }
                }
            }
        }
    }
}