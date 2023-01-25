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

class HotelProductCartDetail extends ObjectModel
{
    public $id_hotel_product_cart_detail;
    public $id_cart;
    public $id_product;
    public $id_hotel;
    public $quantity;

    public static $definition = array(
        'table' => 'htl_hotel_product_cart_detail',
        'primary' => 'id_hotel_product_cart_detail',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_hotel' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
        ),
    );

    public function alreadyExists(
        $idProduct,
        $idHotel,
        $idCart
    ) {
        return Db::getInstance()->getValue(
            'SELECT `id_hotel_product_cart_detail` FROM `'._DB_PREFIX_.'htl_hotel_product_cart_detail`
            WHERE `id_cart` = '.(int)$idCart.' AND `id_product` = '.(int)$idProduct.' AND `id_hotel` = '.(int)$idHotel
        );
    }

    public function updateProduct($idProduct, $quantity, $opt, $idHotel)
    {
        if ($opt == 'up') {
            return $this->addHotelProductInCart($idProduct, $quantity, $idHotel);
        } else {
            // return $this->context->cart->deleteProduct($idProduct);
        }
    }

    public function addHotelProductInCart(
        $idProduct,
        $quantity,
        $idHotel
    ) {
        $context = context::getContext();
        $idCart = $context->cart->id;
        if ($idHotelProductCartDetail = $this->alreadyExists($idProduct, $idHotel, $idCart)) {
            $objHotelProductCartDetail = new HotelProductCartDetail($idHotelProductCartDetail);
            $objHotelProductCartDetail->quantity += $quantity;
        } else {
            $objHotelProductCartDetail = new HotelProductCartDetail();
            $objHotelProductCartDetail->id_product = $idProduct;
            $objHotelProductCartDetail->quantity = $quantity;
            $objHotelProductCartDetail->id_hotel = $idHotel;
            $objHotelProductCartDetail->id_cart = $idCart;

        }
        if ($objHotelProductCartDetail->save()) {
            return $context->cart->updateQty($quantity, $idProduct);
        }
        return false;
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

    public function getHotelProducts(
        $idCart,
        $idProduct = 0,
        $idHotel = 0,
        $getTotalPrice = 0,
        $useTax = null,
        $id_address = null
    ) {
        if ($useTax === null)
            $useTax = Product::$_taxCalculationMethod == PS_TAX_EXC ? false : true;

        $sql = 'SELECT hpcd.`id_product`, hpcd.`quantity`, hpcd.`id_hotel`';
        // if (!$getTotalPrice) {
        //     $sql .= ', cbd.`id_guest`, cbd.`id_customer`,
        //         cbd.`id_hotel`, cbd.`id_room`, cbd.`date_from`, cbd.`date_to`, cbd.`is_refunded`';
        // }
        $sql .= ' FROM `'._DB_PREFIX_.'htl_hotel_product_cart_detail` hpcd
            WHERE hpcd.`id_cart`='.(int) $idCart;

        if ($idProduct) {
            $sql .= ' AND hpcd.`id_product`='.(int) $idProduct;
        }
        if ($idHotel) {
            $sql .= ' AND hpcd.`id_hotel`='.(int) $idHotel;
        }

        if ($getTotalPrice) {
            $totalPrice = 0;
        }
        // $objHotelRoomTypeStandardProductPrice = new HotelRoomTypeStandardProductPrice();
        // $objHotelRoomType = new HotelRoomType();
        // $selectedStandardProducts = array();
        ddd(Db::getInstance()->executeS($sql));
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