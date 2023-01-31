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

class RoomTypeServiceProduct extends ObjectModel
{
    /** @var int id_product */
    public $id_product;

    /** @var int id_hotel or id_room_type */
    public $id_element;

    /** @var int define element type hotel or room type (refer RoomTypeServiceProduct class for constants) */
    public $element_type;

    const WK_ELEMENT_TYPE_HOTEL = 1;
    const WK_ELEMENT_TYPE_ROOM_TYPE = 2;

    const WK_NUM_RESULTS = 2;

    public static $definition = array(
        'table' => 'htl_room_type_service_product',
        'primary' => 'id_room_type_service_product',
        'fields' => array(
            'id_product' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_element' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'element_type' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId')
        )
    );

    public function deleteRoomProductLink($idProduct, $elementType = 0)
    {
        $where = '`id_product`='.(int)$idProduct;

        if ($elementType) {
            $where .= ' AND `element_type`='.(int)$elementType;
        }

        return Db::getInstance()->delete(
            $this->def['table'],
            $where
        );
    }

    public function addRoomProductLink($idProduct, $values, $elementType)
    {
        if(!is_array($values)) {
            $values = (array)$values;
        }

        $rowData = array();
        foreach($values as $value) {
            $rowData[] = array(
                'id_product' => $idProduct,
                'id_element' =>$value,
                'element_type' => $elementType
            );
        }

        return Db::getInstance()->insert($this->def['table'], $rowData);
    }

    public function getAssociatedHotelsAndRoomType($idProduct, $formated = true) {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'htl_room_type_service_product` AS rsp
            WHERE `id_product` = '.(int)$idProduct
        );

        if ($formated) {
            $response = array('hotels' => array(), 'room_types' => array());
            foreach($rows as $row) {
                $key = $row['element_type'] == self::WK_ELEMENT_TYPE_HOTEL ? 'hotels' : 'room_types';
                $response[$key][] = $row['id_element'];
            }
            return $response;
        }
        return $rows;
    }

    public function getIdProductsForHotelAndRoomType($idHotel = false, $idProductRoomType = false)
    {
        if (!$idHotel && !$idProductRoomType) {
            return false;
        }

        if ($idHotel) {
            $sql = 'SELECT `id_product` FROM `'._DB_PREFIX_.'htl_room_type_service_product`
                WHERE `element_type` = '.self::WK_ELEMENT_TYPE_HOTEL.' AND `id_element` = '.(int)$idHotel;
        }
        if ($idHotel && $idProductRoomType) {
            $sql  .= ' UNION ';
        } else {
            $sql = '';
        }
        if ($idProductRoomType) {
            $sql .= 'SELECT `id_product` FROM `'._DB_PREFIX_.'htl_room_type_service_product`
                WHERE `element_type` = '.self::WK_ELEMENT_TYPE_ROOM_TYPE.' AND `id_element` = '.(int)$idProductRoomType;
        }

        return Db::getInstance()->executeS($sql);
    }

    public function isRoomTypeLinkedWithProduct($idProductRoomType, $idServiceProduct)
    {
        $sql = 'SELECT `id_room_type_service_product` FROM  `'._DB_PREFIX_.'htl_room_type_service_product`
            WHERE `id_product` = '.(int)$idServiceProduct.' AND `id_element` = '.(int)$idProductRoomType.'
            AND `element_type` = '.self::WK_ELEMENT_TYPE_ROOM_TYPE;

        return Db::getInstance()->getValue($sql);
    }

    public function getServiceProductsData($idProductRoomType, $p = 1, $n = 0, $front = false, $available_for_order = 2, $subCategory = false, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }
        $objProduct = new Product($idProductRoomType);
        if ($serviceProducts = $objProduct->getProductServiceProducts(
            $idLang,
            $p,
            $n,
            $front,
            $available_for_order,
            false,
            true,
            $subCategory
        )) {
            $objHotelRoomType = new HotelRoomType();
            $serviceProducts = Product::getProductsProperties($idLang, $serviceProducts);
            $objRoomTypeServiceProductPrice = new RoomTypeServiceProductPrice();
            foreach($serviceProducts as &$serviceProduct) {
                $serviceProduct['price_tax_exc'] = $objRoomTypeServiceProductPrice->getProductPrice(
                    (int)$serviceProduct['id_product'],
                    (int)$idProductRoomType,
                    1,
                    false
                );

                $serviceProduct['price_tax_incl'] = $objRoomTypeServiceProductPrice->getProductPrice(
                    (int)$serviceProduct['id_product'],
                    (int)$idProductRoomType,
                    1,
                    true
                );
                $useTax = Product::$_taxCalculationMethod == PS_TAX_EXC ? false : true;
                $serviceProduct['price_without_reduction'] = $objRoomTypeServiceProductPrice->getProductPrice(
                    (int)$serviceProduct['id_product'],
                    (int)$idProductRoomType,
                    1,
                    $useTax
                );
            }
        }

        return $serviceProducts;
    }

    public function getServiceProductsGroupByCategory($idProduct, $p = 1, $n = 0, $front = false, $available_for_order = 2, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $objProduct = new Product($idProduct);
        if ($serviceProductsCategories = $objProduct->getAvailableServiceProductsCategories($idLang)) {
            foreach ($serviceProductsCategories as $key => $category) {
                if ($products = $this->getServiceProductsData($idProduct, $p, $n, $front, $available_for_order, $category['id_category'], $idLang)) {
                    $serviceProductsCategories[$key]['products'] = $products;
                } else {
                    unset($serviceProductsCategories[$key]);
                }
            }
        }
        return $serviceProductsCategories;
    }


}