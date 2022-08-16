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

class HotelRoomTypeStandardProductPrice extends ObjectModel
{
    /** @var int id_product */
    public $id_product;

    /** @var float price for specific room type */
    public $price;

    public $id_tax_rules_group;

    /** @var int id_hotel or id_room_type */
    public $id_element;

    /** @var int define element type hotel or room type (refer HotelRoomTypeStandardProduct clas for constants) */
    public $element_type;

    // public $id_room_type_standard_product;

    public static $definition = array(
        'table' => 'htl_room_type_standard_product_price',
        'primary' => 'id_room_type_standard_product_price',
        'fields' => array(
            // 'id_room_type_standard_product' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'price' =>          array('type' => self::TYPE_FLOAT),
            'id_tax_rules_group' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_element' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'element_type' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId')
        )
    );

    public static function getProductRoomTypeLinkPriceAndTax($idProduct, $idElement, $elementType)
    {
        return Db::getInstance()->getRow(
            'SELECT `price`, `id_tax_rules_group` FROM `'._DB_PREFIX_.'htl_room_type_standard_product_price`
            WHERE `id_product`='.(int)$idProduct.
            ' AND `id_element`='.(int)$idElement.
            ' AND `element_type`='.(int)$elementType
        );
    }

    public function getProductRoomTypeLinkPriceInfo($idProduct, $idElement, $elementType)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'htl_room_type_standard_product_price`
            WHERE `id_product`='.(int)$idProduct.
            ' AND `id_element`='.(int)$idElement.
            ' AND `element_type`='.(int)$elementType
        );
    }

    public function getProductPrice($idProduct, $idRoomType, $quantity, $useTax = null, $id_cart = false)
    {
        if ($useTax === null)
            $useTax = Product::$_taxCalculationMethod == PS_TAX_EXC ? false : true;

        $price = Product::getPriceStatic(
            (int)$idProduct,
            $useTax,
            null,
            6,
            null,
            false,
            true,
            (int)$quantity,
            false,
            null,
            null,
            null,
            $specificPrice,
            true,
            true,
            null,
            true,
            (int)$idRoomType
        );

        return $price * (int)$quantity;
    }

    public function getPrice($tax = true, $decimals = 6, $quantity = 1)
    {

    }
}

