<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License version 3.0
* that is bundled with this package in the file LICENSE.md
* It is also available through the world-wide-web at this URL:
* https://opensource.org/license/osl-3-0-php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@qloapps.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your needs
* please refer to https://store.webkul.com/customisation-guidelines for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/license/osl-3-0-php Open Software License version 3.0
*/


class HotelServiceProductOrderDetail extends ObjectModel
{
    public $id_product;
    public $id_service_product_option;
    public $id_order;
    public $id_order_detail;
    public $id_cart;
    public $id_hotel;
    public $unit_price_tax_excl;
    public $unit_price_tax_incl;
    public $total_price_tax_excl;
    public $total_price_tax_incl;
    public $name;
    public $option_name;
    public $quantity;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'hotel_service_product_order_detail',
        'primary' => 'id_hotel_service_product_order_detail',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_service_product_option' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_order_detail' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_hotel' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'unit_price_tax_excl' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'unit_price_tax_incl' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'total_price_tax_excl' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'total_price_tax_incl' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'name' => array('type' => self::TYPE_STRING, 'required' => true),
            'option_name' => array('type' => self::TYPE_STRING, 'required' => true),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

    public function getProducts($idOrder, $idOrderDetail = 0, $idProduct = 0)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'hotel_service_product_order_detail` hspo
            WHERE 1 AND hspo.`id_order` = '.(int)$idOrder;

        if ($idOrderDetail) {
            $sql .= ' AND hspo.`id_order_detail` = '.(int)$idOrderDetail;
        }

        if ($idProduct) {
            $sql .= ' AND hspo.`id_product` = '.(int)$idProduct;
        }


        return Db::getInstance()->executeS($sql);
    }
}