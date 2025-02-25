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

class StandaloneServiceProductCart extends ObjectModel
{
    public $id_standalone_service_product_cart_detail;
    public $id_cart;
    public $id_product;
    public $quantity;
    public $id_service_product_option;

    public static $definition = array(
        'table' => 'standalone_service_product_cart_detail',
        'primary' => 'id_standalone_service_product_cart_detail',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_service_product_option' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
        ),
    );

    public function updateCartServiceProduct(
        $idCart,
        $idProduct,
        $quantity,
        $operator,
        $idOption = null
    ) {
        if ($operator == 'up') {
            return $this->addServiceProductInCart(
                $idCart,
                $idProduct,
                $quantity,
                $idOption
            );
        } else {
            return $this->removeServiceProductInCart(
                $idCart,
                $idProduct,
                $quantity,
                $idOption
            );
        }
        return false;
    }

    public function addServiceProductInCart(
        $idCart,
        $idProduct,
        $quantity,
        $idServiceProductOption = null
    ) {
        if ($id_standalone_service_product_cart_detail = $this->alreadyExists($idProduct, $idCart, $idServiceProductOption)) {
            $objStandaloneServiceProductCart = new StandaloneServiceProductCart($id_standalone_service_product_cart_detail);
        } else {
            $objStandaloneServiceProductCart = new StandaloneServiceProductCart();
            $objStandaloneServiceProductCart->id_product = $idProduct;
            $objStandaloneServiceProductCart->id_cart = $idCart;
            $objStandaloneServiceProductCart->id_service_product_option = $idServiceProductOption;
            $objStandaloneServiceProductCart->quantity = 0;
        }
        $objStandaloneServiceProductCart->quantity += $quantity;
        if ($objStandaloneServiceProductCart->save()) {
            $objCart = new Cart($idCart);
            $objCart->updateQty((int)abs($quantity), $idProduct);
            return $objStandaloneServiceProductCart->id;
        }
        return false;
    }

    public function removeServiceProductInCart(
        $idCart,
        $idProduct,
        $updateQty = null,
        $idServiceProductOption = null
    ) {
        $res = false;
        if ($products = $this->getProducts($idCart, $idProduct, $idServiceProductOption)) {
            $res = true;
            foreach ($products as $product) {
                $objStandaloneServiceProductCart = new StandaloneServiceProductCart($product['id_standalone_service_product_cart_detail']);

                if ($updateQty) {
                    $objStandaloneServiceProductCart->quantity -= $updateQty;
                } else {
                    $updateQty = $objStandaloneServiceProductCart->quantity;
                    $objStandaloneServiceProductCart->quantity = 0;
                }
                if ($objStandaloneServiceProductCart->quantity <= 0) {
                    $objStandaloneServiceProductCart->delete();
                } else {
                    $objStandaloneServiceProductCart->save();
                }

                $objCart = new Cart($idCart);
                if (isset(Context::getContext()->controller->controller_type)) {
                    $controllerType = Context::getContext()->controller->controller_type;
                } else {
                    $controllerType = 'front';
                }
                if ($controllerType == 'admin' || $controllerType == 'moduleadmin') {
                    if ($cartQty = Cart::getProductQtyInCart($idCart, $idProduct)) {
                        if ($updateQty < $cartQty) {
                            $res = $res && Db::getInstance()->update(
                                'cart_product',
                                array('quantity' => (int)($cartQty - $updateQty)),
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
                    $res = $res && $objCart->updateQty((int)($updateQty), $idProduct, null, false, 'down');
                }
            }
        }
        return $res;
    }

    public function getProducts(
        $idCart,
        $idProduct = 0,
        $idServiceProductOption = null,
        $getTotalPrice = 0,
        $useTax = null,
        $idLang = null
    ) {
        if ($useTax === null) {
            $useTax = Product::$_taxCalculationMethod == PS_TAX_EXC ? false : true;
        }

        if (!$idLang) {
            $language = Context::getContext()->language;
        } else {
            $language = new Language($idLang);
        }

        $sql = 'SELECT spcd.`id_product`, spcd.`quantity`, spcd.`id_service_product_option`, p.`minimal_quantity`, spcd.`id_standalone_service_product_cart_detail`';
        $sql .= ' FROM `'._DB_PREFIX_.'standalone_service_product_cart_detail` spcd';
        $sql .= ' INNER JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = spcd.`id_product`)';
        $sql .= ' WHERE spcd.`id_cart`='.(int) $idCart;

        if ($idProduct) {
            $sql .= ' AND spcd.`id_product`='.(int) $idProduct;
        }
        if ($idServiceProductOption != null) {
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
                        $totalPrice += StandaloneServiceProductCart::getPrice(
                            $objProduct->id,
                            $product['id_service_product_option'],
                            $useTax,
                            $qty
                        );
                    } else {
                    $context = Context::getContext();
                        $priceTaxIncl = StandaloneServiceProductCart::getPrice(
                            $objProduct->id,
                            $product['id_service_product_option'],
                            true,
                            $product['quantity'],
                        );
                        // $priceTaxIncl = Product::getPriceStatic(
                        //     $objProduct->id,
                        //     true,
                        //     null,
                        //     6,
                        //     null,
                        //     false,
                        //     true,
                        //     $product['quantity']
                        // );
                        $priceTaxExcl = StandaloneServiceProductCart::getPrice(
                            $objProduct->id,
                            $product['id_service_product_option'],
                            false,
                            $product['quantity']
                        );
                        // $priceTaxExcl = Product::getPriceStatic(
                        //     $objProduct->id,
                        //     false,
                        //     null,
                        //     6,
                        //     null,
                        //     false,
                        //     true,
                        //     $product['quantity'],
                        //     false,
                        //     null,
                        //     null,
                        //     $idHotelAddress
                        // );
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

                        $selectedProducts[$product['id_product'].'-'.$product['id_service_product_option']] = array_merge(
                            $product,
                            array(
                                'name' => $objProduct->name,
                                'option_name' => isset($optionDetails['name']) ? $optionDetails['name'] : false,
                                'unit_price_tax_incl' => $priceTaxIncl,
                                'unit_price_tax_excl' => $priceTaxExcl,
                                'total_price_tax_incl' => $priceTaxIncl * (int)$product['quantity'],
                                'total_price_tax_excl' => $priceTaxExcl * (int)$product['quantity'],
                                'cover_img' => $coverImg
                            )
                        );

                    }
                }
            }
        }
        if ($getTotalPrice) {
            return $totalPrice;
        }

        return $selectedProducts;
    }


    // public function getAllServiceProduct($idCart)
    // {
    //     return Db::getInstance()->executeS(
    //         'SELECT spcd.*,  cbd.`id_product` as `id_product_room_type`, cbd.`id_room`, cbd.`id_hotel`, cbd.`date_from`, cbd.`date_to`
    //         FROM `' . _DB_PREFIX_ . 'service_product_cart_detail` spcd
    //         INNER JOIN `'._DB_PREFIX_.'htl_cart_booking_data` cbd
    //         ON(spcd.`htl_cart_booking_id` = cbd.`id`)
    //         WHERE spcd.`id_cart` = ' . (int)$idCart
    //     );
    // }

    public function alreadyExists($idProduct, $idCart, $idServiceProductOption = null)
    {
        $sql = 'SELECT `id_standalone_service_product_cart_detail` FROM `'._DB_PREFIX_.'standalone_service_product_cart_detail`
            WHERE `id_cart` = '.(int)$idCart.' AND `id_product` = '.(int)$idProduct;

        if ($idServiceProductOption) {
            $sql .= ' AND `id_service_product_option` = '.(int)$idServiceProductOption;
        }
        return Db::getInstance()->getValue($sql);
    }

    public static function getPrice(
        $idProduct,
        $idOption = null,
        $useTax = null,
        $quantity = 1
    ) {
        $price =  ProductCore::getPriceStatic(
            $idProduct,
            $useTax,
            $idOption,
            6,
            null,
            false,
            true,
            $quantity,
            false,
            null,
            null,
            null,
            $specific,
            true,
            true,
            null,
            true,
            false
        );

        return $price;
    }
}