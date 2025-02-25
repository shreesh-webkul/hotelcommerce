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

class HotelServiceProductCartDetail extends ObjectModel
{
    public $id_hotel_service_product_cart_detail;
    public $id_cart;
    public $id_product;
    public $id_hotel;
    public $quantity;
    public $id_service_product_option;

    public static $definition = array(
        'table' => 'htl_hotel_service_product_cart_detail',
        'primary' => 'id_hotel_service_product_cart_detail',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_hotel' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_service_product_option' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
        ),
    );

    public function alreadyExists(
        $idProduct,
        $idHotel = null,
        $idCart = null,
        $idServiceProductOption = null
    ) {

        $sql = 'SELECT `id_hotel_service_product_cart_detail` FROM `'._DB_PREFIX_.'htl_hotel_service_product_cart_detail`
            WHERE `id_product` = '.(int)$idProduct;

        if ($idHotel) {
            $sql .= ' AND `id_hotel` = '.(int)$idHotel;
        }
        if ($idCart) {
            $sql .= ' AND `id_cart` = '.(int)$idCart;
        }
        if ($idServiceProductOption) {
            $sql .= ' AND `id_service_product_option` = '.(int)$idServiceProductOption;
        }

        return Db::getInstance()->getValue($sql);
    }
    public function updateHotelProductInCart(
        $idCart,
        $idProduct,
        $idHotel,
        $operator,
        $quantity = null,
        $idServiceProductOption = null
    ) {
        if ($operator == 'up') {
            return $this->addHotelProductInCart(
                $idProduct,
                $quantity,
                $idHotel,
                $idCart,
                $idServiceProductOption
            );
        } else {
            return $this->removeProductFromCart(
                $idCart,
                $idProduct,
                $idHotel,
                $quantity,
                $idServiceProductOption
            );

        }
    }

    public function addHotelProductInCart(
        $idProduct,
        $quantity,
        $idHotel,
        $idCart = null,
        $idServiceProductOption = null
    ) {

        if (!$idCart) {
            $context = context::getContext();
            $idCart = $context->cart->id;
        }

        if ($idHotelServiceProductCartDetail = $this->alreadyExists($idProduct, $idHotel, $idCart, $idServiceProductOption)) {
            $objHotelServiceProductCartDetail = new HotelServiceProductCartDetail($idHotelServiceProductCartDetail);
            $objHotelServiceProductCartDetail->quantity += $quantity;
        } else {
            $objHotelServiceProductCartDetail = new HotelServiceProductCartDetail();
            $objHotelServiceProductCartDetail->id_product = $idProduct;
            $objHotelServiceProductCartDetail->quantity = $quantity;
            $objHotelServiceProductCartDetail->id_hotel = $idHotel;
            $objHotelServiceProductCartDetail->id_cart = $idCart;
            $objHotelServiceProductCartDetail->id_service_product_option = $idServiceProductOption;
        }
        if ($objHotelServiceProductCartDetail->save()) {
            $objCart = new Cart($idCart);
            return $objCart->updateQty($quantity, $idProduct);
        }
        return false;
    }

    public function removeProductFromCart($idCart, $idProduct, $idHotel = null, $quantity = null, $idServiceProductOption = null)
    {
        $updateQunatity = false;
        $res = true;
        if ($products = $this->getHotelProducts($idCart, $idProduct, $idHotel, $idServiceProductOption)) {
            foreach ($products as $product) {
                $objHotelServiceProductCartDetail = new HotelServiceProductCartDetail($product['id_hotel_service_product_cart_detail']);
                if ($quantity) {
                    $removedQuantity = $quantity;
                    $objHotelServiceProductCartDetail->quantity -= $quantity;
                    if ($objHotelServiceProductCartDetail->quantity) {
                        $updateQunatity = $objHotelServiceProductCartDetail->save();
                    } else {
                        $updateQunatity = $objHotelServiceProductCartDetail->delete();
                    }
                } else {
                    $removedQuantity = $objHotelServiceProductCartDetail->quantity;
                    $updateQunatity = $objHotelServiceProductCartDetail->delete();
                }
                if ($updateQunatity) {
                    $objCart = new Cart($idCart);

                    if (isset(Context::getContext()->controller->controller_type)) {
                        $controllerType = Context::getContext()->controller->controller_type;
                    } else {
                        $controllerType = 'front';
                    }
                    if ($controllerType == 'admin' || $controllerType == 'moduleadmin') {
                        if ($cartQty = Cart::getProductQtyInCart($idCart, $idProduct)) {
                            if ($removedQuantity < $cartQty) {
                                $res = $res && Db::getInstance()->update(
                                    'cart_product',
                                    array('quantity' => (int)($cartQty - $removedQuantity)),
                                    '`id_product` = '.(int)$idProduct.' AND `id_cart` = '.(int)$idCart
                                );
                            } else {
                                //if room type has no qty remaining in cart then delete row
                                $res = $res && Db::getInstance()->delete(
                                    'cart_product',
                                    '`id_product` = '.(int)$idProduct.' AND `id_cart` = '.(int)$idCart
                                );
                            }
                        }
                    } else {
                        $res = $res && $objCart->updateQty((int)($removedQuantity), $idProduct, null, false, 'down');
                    }
                }
            }
        }

        return $res;
    }

    public function getHotelProductUnitPrice(
        $idCart,
        $idProduct,
        $idHotel = 0,
        $idServiceProductOption = null,
        $useTax = null
    ) {
        $totalPrice = $totalQuantity = 0;

        if ($useTax === null) {
            $useTax = Product::$_taxCalculationMethod == PS_TAX_EXC ? false : true;
        }
        $sql = 'SELECT spcd.`id_product`, spcd.`quantity`, spcd.`id_hotel`
            FROM `'._DB_PREFIX_.'htl_hotel_service_product_cart_detail` spcd
            WHERE spcd.`id_cart`='.(int) $idCart.' AND spcd.`id_product`='.(int) $idProduct;
        if ($idHotel) {
            $sql .= ' AND spcd.`id_hotel`='.(int) $idHotel;
        }
        if ($idHotel) {
            $sql .= ' AND spcd.`id_hotel`='.(int) $idHotel;
        }
        if ($idServiceProductOption) {
            $sql .= ' AND spcd.`id_service_product_option` = '.(int)$idServiceProductOption;
        }
        if ($serviceProducts = Db::getInstance()->executeS($sql)) {
            foreach ($serviceProducts as $product) {
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
        $idServiceProductOption = null,
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

        $sql = 'SELECT spcd.`id_hotel_service_product_cart_detail`, spcd.`id_product`, spcd.`quantity`, spcd.`id_hotel`,
            spcd.`id_service_product_option`';
        if (!$getTotalPrice) {
            $sql .= ', hbil.`hotel_name` ';
        }
        $sql .= ' FROM `'._DB_PREFIX_.'htl_hotel_service_product_cart_detail` spcd';
        if (!$getTotalPrice) {
            $sql .= ' INNER JOIN `'._DB_PREFIX_.'htl_branch_info_lang` hbil ON (hbil.`id` = spcd.`id_hotel` AND hbil.`id_lang` = '. $language->id.')
            INNER JOIN `'._DB_PREFIX_.'address` a ON (a.`id_hotel` = spcd.`id_hotel`)';
        }
            $sql .= ' WHERE spcd.`id_cart`='.(int) $idCart;

        if ($idProduct) {
            $sql .= ' AND spcd.`id_product`='.(int) $idProduct;
        }
        if ($idHotel) {
            $sql .= ' AND spcd.`id_hotel`='.(int) $idHotel;
        }
        if ($idServiceProductOption) {
            $sql .= ' AND spcd.`id_service_product_option` = '.(int)$idServiceProductOption;
        }
        if ($getTotalPrice) {
            $totalPrice = 0;
        }

        $selectedProducts = array();
        $objServiceProductOption = new ServiceProductOption();
        if ($serviceProducts = Db::getInstance()->executeS($sql)) {
            foreach ($serviceProducts as $product) {
                $objProduct = new Product($product['id_product'], false, $language->id);
                if (!$objProduct->booking_product) {
                    if ($getTotalPrice) {
                        $qty = $product['quantity'] ? (int)$product['quantity'] : 1;
                        $totalPrice += HotelServiceProductCartDetail::getPrice(
                            $objProduct->id,
                            $product['id_hotel'],
                            $product['id_service_product_option'],
                            $useTax,
                            $qty
                        );
                    } else {
                        $context = Context::getContext();
                        $priceTaxIncl = HotelServiceProductCartDetail::getPrice(
                            $objProduct->id,
                            $product['id_hotel'],
                            $product['id_service_product_option'],
                            true,
                            $product['quantity']
                        );
                        $priceTaxExcl = HotelServiceProductCartDetail::getPrice(
                            $objProduct->id,
                            $product['id_hotel'],
                            $product['id_service_product_option'],
                            false,
                            $product['quantity']
                        );
                        $optionDetails = false;
                        if (ServiceProductOption::productHasOptions($product['id_product'])) {
                            $optionDetails = $objServiceProductOption->getProductOptions(
                                $objProduct->id,
                                $product['id_service_product_option']
                            );
                        }
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
                        $productInfo = array(
                            'id_hotel_service_product_cart_detail' => $product['id_hotel_service_product_cart_detail'],
                            'id_hotel' => $product['id_hotel'],
                            'hotel_name' => $product['hotel_name'],
                            'id_product' =>$objProduct->id,
                            'id_service_product_option' => $product['id_service_product_option'],
                            'name' => $objProduct->name,
                            'option_name' => isset($optionDetails['name']) ? $optionDetails['name'] : false,
                            'minimal_quantity' => $objProduct->minimal_quantity,
                            'allow_multiple_quantity' => $objProduct->allow_multiple_quantity,
                            'max_quantity' => $objProduct->max_quantity,
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
                            $productInfo['hotel_info'] = $hotelInfo;
                        }
                        $selectedProducts[] = $productInfo;
                    }
                }
            }

        }
        if ($getTotalPrice) {
            return $totalPrice;
        }
        return $selectedProducts;
    }

    public static function getPrice(
        $idProduct,
        $idHotel,
        $idServiceProductOption = null,
        $useTax = null,
        $quantity = 1
    ) {
        $idHotelAddress = Cart::getIdAddressForTaxCalculation($idProduct, $idHotel);
        $price =  ProductCore::getPriceStatic(
            $idProduct,
            $useTax,
            $idServiceProductOption,
            6,
            null,
            false,
            true,
            $quantity,
            false,
            null,
            null,
            $idHotelAddress,
        );

        return $price;
    }
}