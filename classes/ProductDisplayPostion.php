<?php
/**
* 2010-2020 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2020 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

class ProductDisplayPostionCore extends ObjectModel
{
    public $id_product;
    public $id_position;

    const DISPLAY_POSITION_HOMEPAGE = 1;
    const DISPLAY_POSITION_ROOMTYPE = 2;
    const DISPLAY_POSITION_CATEGORY = 3;
    const DISPLAY_POSITION_CHECKOUT = 4;

    public static $definition = array(
        'table' => 'product_display_position',
        'primary' => 'id_product_display_position',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_position' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
        )
    );

    public static function deleteDisplayPosition($idProduct, $idPosition  = false)
    {
        $where = '`id_product`='.(int)$idProduct;

        if ($idPosition) {
            $where .= ' AND `id_position`='.(int)$idPosition;
        }

        return Db::getInstance()->delete(
            'product_display_position',
            $where
        );
    }

    public static function getDisplayPostions($idProduct, $idPosition = false)
    {
        $sql  = 'SELECT * FROM `'._DB_PREFIX_.'product_display_position`
            WHERE `id_product`='.(int)$idProduct;

        if ($idPosition) {
            $sql .= ' AND `id_position` = '.(int)$idPosition;
        }

        return Db::getInstance()->executeS($sql);
    }

    public function getAssociatedProductByPosition($displayPosition, $idHotel = false, $idRoomType = false)
    {
        $sql  = 'SELECT `id_product` FROM `'._DB_PREFIX_.'product_display_position`
            WHERE `id_position`='.(int)$displayPosition;

        return Db::getInstance()->executeS($sql);

    }
}