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

class StandardProductCartDetail extends ObjectModel
{
    public $id_product;
    public $quantity;
    public $id_cart;
    public $htl_cart_booking_id;

    public static $definition = array(
        'table' => 'htl_standard_product_cart_detail',
        'primary' => 'id_standard_product_cart_detail',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'htl_cart_booking_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
        ),
    );

    public function alreadyExists(
        $idProduct,
        $idHtlCartData
    ) {
        return Db::getInstance()->getValue(
            'SELECT `id_standard_product_cart_detail` FROM `'._DB_PREFIX_.'htl_standard_product_cart_detail`
            WHERE `id_product` = '.(int)$idProduct.' AND `htl_cart_booking_id` = '.(int)$idHtlCartData
        );
    }

    public function addStandardProductInCart(
        $idProduct,
        $quantity,
        $idCart,
        $idHtlCartData
    ) {
        if ($this->alreadyExists($idProduct, $idHtlCartData)) {
            return false;
        } else {
            $objStandardProductCartDetail = new StandardProductCartDetail();
            $objStandardProductCartDetail->id_product = $idProduct;
            $objStandardProductCartDetail->quantity = $quantity;
            $objStandardProductCartDetail->id_cart = $idCart;
            $objStandardProductCartDetail->htl_cart_booking_id = $idHtlCartData;
            if ($objStandardProductCartDetail->save()) {
                $objCart = new Cart($idCart);
                $objCart->updateQty((int)($quantity), $idProduct);
            }
        }
    }

    public function removeStandardProductByIdHtlCartBooking($htlCartBookingId)
    {
        if ($stadardProductsData  = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'htl_standard_product_cart_detail`
            WHERE `htl_cart_booking_id` = '.(int)$htlCartBookingId
        )) {
            foreach ($stadardProductsData as $product) {
                if (Validate::isLoadedObject(
                    $objStandardProductCartDetail = new StandardProductCartDetail($product['id_standard_product_cart_detail'])
                )) {
                    if ($objStandardProductCartDetail->delete()) {
                        $objCart = new Cart($product['id_cart']);
                        $objCart->updateQty((int)($product['quantity']), $product['id_product'], null, false, 'down');
                    }
                }
            }
        }

        return true;
    }

    public function getStandardProductsInCart(
        $idCart = 0,
        $idProduct = 0,
        $idHotel = 0,
        $roomTypeIdProduct = 0,
        $dateFrom = 0,
        $dateTo = 0,
        $htlCartBookingId = 0,
        $getTotalPrice = 0,
        $useTax = null,
        $id_address = null
    ) {
        if ($useTax === null)
            $useTax = Product::$_taxCalculationMethod == PS_TAX_EXC ? false : true;

        $sql = 'SELECT scd.`id_product`, scd.`quantity`, cbd.`id_cart`, cbd.`id` as `htl_cart_booking_id` ,
            cbd.`id_product` as `room_type_id_product`, cbd.`adults`, cbd.`children`';
        if (!$getTotalPrice) {
            $sql .= ', cbd.`id_guest`, cbd.`id_customer`,
                cbd.`id_hotel`, cbd.`id_room`, cbd.`date_from`, cbd.`date_to`, cbd.`is_refunded`';
        }
        $sql .= ' FROM `'._DB_PREFIX_.'htl_cart_booking_data` cbd
            LEFT JOIN `'._DB_PREFIX_.'htl_standard_product_cart_detail` scd ON(scd.`htl_cart_booking_id` = cbd.`id`)
            WHERE 1';
        if ($idCart) {
            $sql .= ' AND cbd.`id_cart`='.(int) $idCart;
        }
        if ($idProduct) {
            $sql .= ' AND scd.`id_product`='.(int) $idProduct;
        }
        if ($idHotel) {
            $sql .= ' AND cbd.`id_hotel`='.(int) $idHotel;
        }
        if ($roomTypeIdProduct) {
            $sql .= ' AND cbd.`id_product`='.(int) $roomTypeIdProduct;
        }
        if ($dateFrom && $dateTo) {
            $sql .= ' AND cbd.`date_from` = \''.pSQL($dateFrom).'\' AND cbd.`date_to` = \''.pSQL($dateTo).'\'';
        }
        if ($htlCartBookingId) {
            $sql .= ' AND cbd.`id`='.(int) $htlCartBookingId;
        }
        $sql .= ' ORDER BY cbd.`id`';

        if ($getTotalPrice) {
            $totalPrice = 0;
        }
        $objHotelRoomTypeStandardProductPrice = new HotelRoomTypeStandardProductPrice();
        $objHotelRoomType = new HotelRoomType();
        $selectedStandardProducts = array();

        if ($standardProducts = Db::getInstance()->executeS($sql)) {
            foreach ($standardProducts as $product) {
                if ($getTotalPrice) {
                    $qty = $product['quantity'] ? (int)$product['quantity'] : 1;
                    $totalPrice += $objHotelRoomTypeStandardProductPrice->getProductPrice(
                        (int)$product['id_product'],
                        (int)$product['room_type_id_product'],
                        $qty,
                        $useTax,
                        false,
                        $id_address
                    );

                } else {
                    $roomTypeInfo = $objHotelRoomType->getRoomTypeInfoByIdProduct($product['room_type_id_product']);
                    $qty = $product['quantity'] ? (int)$product['quantity'] : 1;
                    if (isset($selectedStandardProducts[$product['htl_cart_booking_id']])) {
                        if ($product['id_product']) {
                            if ($idProduct) {
                                $selectedStandardProducts[$product['htl_cart_booking_id']]['quantity'] += $product['quantity'];

                            } else {
                                $selectedStandardProducts[$product['htl_cart_booking_id']]['selected_products_info'][$product['id_product']] = array(
                                    'id_product' => $product['id_product'],
                                    'quantity' => $product['quantity'],
                                );
                            }
                        }
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['total_price'] += $objHotelRoomTypeStandardProductPrice->getProductPrice(
                            (int)$product['id_product'],
                            (int)$product['room_type_id_product'],
                            $qty,
                            $useTax,
                            false,
                            $id_address
                        );
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['total_price_tax_excl'] += $objHotelRoomTypeStandardProductPrice->getProductPrice(
                            (int)$product['id_product'],
                            (int)$product['room_type_id_product'],
                            $qty,
                            false,
                            false,
                            $id_address
                        );
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['total_price_tax_incl'] += $objHotelRoomTypeStandardProductPrice->getProductPrice(
                            (int)$product['id_product'],
                            (int)$product['room_type_id_product'],
                            $qty,
                            true,
                            false,
                            $id_address
                        );
                    } else {
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['htl_cart_booking_id'] = $product['htl_cart_booking_id'];
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['id_cart'] = $product['id_cart'];
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['room_type_id_product'] = $product['room_type_id_product'];
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['id_guest'] = $product['id_guest'];
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['id_customer'] = $product['id_customer'];
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['id_hotel'] = $product['id_hotel'];
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['id_room'] = $product['id_room'];
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['date_from'] = $product['date_from'];
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['date_to'] = $product['date_to'];
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['is_refunded'] = $product['is_refunded'];
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['adults'] = $product['adults'];
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['children'] = $product['children'];
                        if ($idProduct) {
                            $selectedStandardProducts[$product['htl_cart_booking_id']]['id_product'] = $product['id_product'];
                            $selectedStandardProducts[$product['htl_cart_booking_id']]['quantity'] = $product['quantity'];
                            $selectedStandardProducts[$product['htl_cart_booking_id']]['unit_price_tax_excl'] = $objHotelRoomTypeStandardProductPrice->getProductPrice(
                                (int)$product['id_product'],
                                (int)$product['room_type_id_product'],
                                1,
                                false,
                                false,
                                $id_address
                            );
                            $selectedStandardProducts[$product['htl_cart_booking_id']]['unit_price_tax_incl'] = $objHotelRoomTypeStandardProductPrice->getProductPrice(
                                (int)$product['id_product'],
                                (int)$product['room_type_id_product'],
                                1,
                                true,
                                false,
                                $id_address
                            );
                        } else {
                            $selectedStandardProducts[$product['htl_cart_booking_id']]['selected_products_info'] = ($product['id_product']) ? array(
                                $product['id_product'] => array(
                                    'id_product' => $product['id_product'],
                                    'quantity' => $product['quantity'],
                                    'unit_price_tax_excl' => $objHotelRoomTypeStandardProductPrice->getProductPrice(
                                        (int)$product['id_product'],
                                        (int)$product['room_type_id_product'],
                                        1,
                                        false,
                                        false,
                                        $id_address
                                    ),
                                    'unit_price_tax_incl' => $objHotelRoomTypeStandardProductPrice->getProductPrice(
                                        (int)$product['id_product'],
                                        (int)$product['room_type_id_product'],
                                        1,
                                        true,
                                        false,
                                        $id_address
                                    ),
                                )
                            ): array();
                        }

                        $selectedStandardProducts[$product['htl_cart_booking_id']]['total_price'] = $objHotelRoomTypeStandardProductPrice->getProductPrice(
                            (int)$product['id_product'],
                            (int)$product['room_type_id_product'],
                            $qty,
                            $useTax,
                            false,
                            $id_address
                        );
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['total_price_tax_excl'] = $objHotelRoomTypeStandardProductPrice->getProductPrice(
                            (int)$product['id_product'],
                            (int)$product['room_type_id_product'],
                            $qty,
                            false,
                            false,
                            $id_address
                        );
                        $selectedStandardProducts[$product['htl_cart_booking_id']]['total_price_tax_incl'] = $objHotelRoomTypeStandardProductPrice->getProductPrice(
                            (int)$product['id_product'],
                            (int)$product['room_type_id_product'],
                            $qty,
                            true,
                            false,
                            $id_address
                        );
                    }
                }
            }

        }

        if ($getTotalPrice) {
            return $totalPrice;
        }
        return $selectedStandardProducts;
    }

    public function updateCartStandardProduct(
        $htl_cart_booking_id,
        $id_product,
        $quantity,
        $id_cart,
        $operator
    ) {
        $id_standard_product_cart_detail = $this->alreadyExists($id_product, $htl_cart_booking_id);

        if ($operator == 'up') {
            if ($id_standard_product_cart_detail) {
                $objStandardProductCartDetail = new StandardProductCartDetail($id_standard_product_cart_detail);
            } else {
                $objStandardProductCartDetail = new StandardProductCartDetail();
                $objStandardProductCartDetail->id_product = $id_product;
                $objStandardProductCartDetail->htl_cart_booking_id = $htl_cart_booking_id;
                $objStandardProductCartDetail->id_cart = $id_cart;
            }
            $updateQty = $quantity - $objStandardProductCartDetail->quantity;
            $way = $updateQty > 0 ? 'up' : 'down';
            $objStandardProductCartDetail->quantity = $quantity;
            if ($objStandardProductCartDetail->save()) {
                $objCart = new Cart($id_cart);
                return $objCart->updateQty((int)abs($updateQty), $id_product, null, false, $way);
            }
        } else {
            if ($id_standard_product_cart_detail) {
                $objStandardProductCartDetail = new StandardProductCartDetail($id_standard_product_cart_detail);
                $updateQty = $objStandardProductCartDetail->quantity;
                if ($objStandardProductCartDetail->delete()) {
                    $objCart = new Cart($id_cart);
                    return $objCart->updateQty((int)abs($updateQty), $id_product, null, false, 'down');
                }
            } else {
                return true;
            }
        }
        return false;
    }
}