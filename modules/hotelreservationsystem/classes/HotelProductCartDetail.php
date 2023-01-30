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

    public function addHotelProductInCart(
        $idProduct,
        $quantity,
        $idHotel,
        $idCart = null
    ) {

        if (!$idCart) {
            $context = context::getContext();
            $idCart = $context->cart->id;
        }
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
            $objCart = new Cart($idCart);
            return $objCart->updateQty($quantity, $idProduct);
        }
        return false;
    }

    public function removeProductFromCart($idProduct, $idHotel, $idCart = null, $quantity = null)
    {
        if (!$idCart) {
            $context = context::getContext();
            $idCart = $context->cart->id;
        }

        $updateQunatity = false;
        if ($idHotelProductCartDetail = $this->alreadyExists($idProduct, $idHotel, $idCart)) {
            $objHotelProductCartDetail = new HotelProductCartDetail($idHotelProductCartDetail);
            if ($quantity) {
                $removedQuantity = $quantity;
                $objHotelProductCartDetail->quantity -= $quantity;
                if ($objHotelProductCartDetail->quantity) {
                    $updateQunatity = $objHotelProductCartDetail->save();
                } else {
                    $updateQunatity = $objHotelProductCartDetail->delete();
                }
            } else {
                $removedQuantity = $objHotelProductCartDetail->quantity;
                $updateQunatity = $objHotelProductCartDetail->delete();
            }
        }

        if ($updateQunatity) {
            $objCart = new Cart($idCart);
            return $objCart->updateQty((int)$removedQuantity, $idProduct, null, false, 'down');
        }

        return false;
    }

    public function getHotelProductUnitPrice(
        $idCart,
        $idProduct,
        $idHotel = 0,
        $useTax = null
    ) {
        $totalPrice = $totalQuantity = 0;

        if ($useTax === null) {
            $useTax = Product::$_taxCalculationMethod == PS_TAX_EXC ? false : true;
        }
        $sql = 'SELECT hpcd.`id_product`, hpcd.`quantity`, hpcd.`id_hotel`
            FROM `'._DB_PREFIX_.'htl_hotel_product_cart_detail` hpcd
            WHERE hpcd.`id_cart`='.(int) $idCart.' AND hpcd.`id_product`='.(int) $idProduct;
        if ($idHotel) {
            $sql .= ' AND hpcd.`id_hotel`='.(int) $idHotel;
        }
        if ($standardProducts = Db::getInstance()->executeS($sql)) {
            foreach ($standardProducts as $product) {
                $objProduct = new Product($product['id_product']);
                if (!$objProduct->booking_product) {
                    $idHotelAddress = Cart::getIdAddressForTaxCalculation($idProduct, $product['id_hotel']);
                    $totalQuantity += $product['quantity'] ? (int)$product['quantity'] : 1;
                    $totalPrice += (ProductCore::getPriceStatic(
                        $idProduct,
                        $useTax,
                        null,
                        6,
                        null,
                        false,
                        true,
                        $product['quantity'],
                        false,
                        null,
                        null,
                        $idHotelAddress
                    )* $product['quantity']);
                }
            }

        }
        if ($totalPrice && $totalQuantity) {
            return $totalPrice/(int)$totalQuantity;
        } else {
            return false;
        }
    }

    public function getHotelProducts(
        $idCart,
        $idProduct = 0,
        $idHotel = 0,
        $getTotalPrice = 0,
        $useTax = null,
        $idLang = null,
        $full = false
    ) {
        if ($useTax === null) {
            $useTax = Product::$_taxCalculationMethod == PS_TAX_EXC ? false : true;
        }

        if (!$idLang) {
            $language = Context::getContext()->language;
        } else {
            $language = new Language($idLang);
        }

        $sql = 'SELECT hpcd.`id_product`, hpcd.`quantity`, hpcd.`id_hotel`';
        if (!$getTotalPrice) {
            $sql .= ', hbil.`hotel_name` ';
        }
        $sql .= ' FROM `'._DB_PREFIX_.'htl_hotel_product_cart_detail` hpcd';
        if (!$getTotalPrice) {
            $sql .= ' INNER JOIN `'._DB_PREFIX_.'htl_branch_info_lang` hbil ON (hbil.`id` = hpcd.`id_hotel` AND hbil.`id_lang` = '. $language->id.')
            INNER JOIN `'._DB_PREFIX_.'address` a ON (a.`id_hotel` = hpcd.`id_hotel`)';
        }
            $sql .= ' WHERE hpcd.`id_cart`='.(int) $idCart;

        if ($idProduct) {
            $sql .= ' AND hpcd.`id_product`='.(int) $idProduct;
        }
        if ($idHotel) {
            $sql .= ' AND hpcd.`id_hotel`='.(int) $idHotel;
        }

        if ($getTotalPrice) {
            $totalPrice = 0;
        }

        $selectedProducts = array();
        if ($standardProducts = Db::getInstance()->executeS($sql)) {
            foreach ($standardProducts as $product) {
                $objProduct = new Product($product['id_product'], false, $language->id);
                if (!$objProduct->booking_product) {
                    if ($getTotalPrice) {
                        $idHotelAddress = Cart::getIdAddressForTaxCalculation($objProduct->id, $product['id_hotel']);
                        $qty = $product['quantity'] ? (int)$product['quantity'] : 1;
                        $totalPrice += Product::getPriceStatic(
                            $objProduct->id,
                            $useTax,
                            null,
                            6,
                            null,
                            false,
                            true,
                            $qty,
                            false,
                            null,
                            null,
                            $idHotelAddress
                        );
                    } else {
                        $context = Context::getContext();
                        $idHotelAddress = Cart::getIdAddressForTaxCalculation($objProduct->id, $product['id_hotel']);
                        $priceTaxIncl = Product::getPriceStatic(
                            $objProduct->id,
                            true,
                            null,
                            6,
                            null,
                            false,
                            true,
                            $product['quantity'],
                            false,
                            null,
                            null,
                            $idHotelAddress
                        );
                        $priceTaxExcl = Product::getPriceStatic(
                            $objProduct->id,
                            false,
                            null,
                            6,
                            null,
                            false,
                            true,
                            $product['quantity'],
                            false,
                            null,
                            null,
                            $idHotelAddress
                        );
                        $coverImageArr = $objProduct->getCover($product['id_product']);
                        if (!empty($coverImageArr)) {
                            $coverImg = $context->link->getImageLink(
                                $objProduct->link_rewrite,
                                $objProduct->id.'-'.$coverImageArr['id_image'],
                                'small_default'
                            );
                        } else {
                            $coverImg = $context->link->getImageLink(
                                $objProduct->link_rewrite,
                                $language->iso_code.'-default',
                                'small_default'
                            );
                        }

                        $selectedProducts[$product['id_hotel'].'-'.$product['id_product']] = array(
                            'id_hotel' => $product['id_hotel'],
                            'hotel_name' => $product['hotel_name'],
                            'id_product' =>$objProduct->id,
                            'name' => $objProduct->name,
                            'unit_price_tax_incl' => $priceTaxIncl,
                            'unit_price_tax_excl' => $priceTaxExcl,
                            'quantity' => $product['quantity'],
                            'total_price_tax_incl' => $priceTaxIncl * (int)$product['quantity'],
                            'total_price_tax_excl' => $priceTaxExcl * (int)$product['quantity'],
                            'cover_img' => $coverImg
                        );
                        if ($full) {
                            $objHotelBranchInformation = new HotelBranchInformation();
                            $hotelInfo = $objHotelBranchInformation->hotelBranchesInfo($language->id, 2, 1, $product['id_hotel']);
                            $hotelInfo['location'] = $hotelInfo['hotel_name'].', '.$hotelInfo['city'].
                                ($hotelInfo['state_name']?', '.$hotelInfo['state_name']:'').', '.
                                $hotelInfo['country_name'].', '.$hotelInfo['postcode'];
                            $selectedProducts[$product['id_hotel'].'-'.$product['id_product']]['hotel_info'] = $hotelInfo;
                        }
                    }
                }
            }

        }
        if ($getTotalPrice) {
            return $totalPrice;
        }
        return $selectedProducts;
    }
}