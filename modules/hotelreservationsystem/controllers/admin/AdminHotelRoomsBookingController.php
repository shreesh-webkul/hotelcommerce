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

class AdminHotelRoomsBookingController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'htl_booking_detail';
        $this->className = 'HotelBookingDetail';
        $this->lang = false;
        $this->bootstrap = true;
        $this->context = Context::getContext();

        parent::__construct();

        //unset($_COOKIE['wk_id_guest']);
        //unset($_COOKIE['wk_id_cart']);
        $this->setPhpCookieData();
    }

    public function setPhpCookieData()
    {
        if (!isset($_COOKIE['wk_id_guest']) || !$_COOKIE['wk_id_guest']) {
            if (!isset($this->context->cookie->id_guest)) {
                Guest::setNewGuest($this->context->cookie);
            }

            setcookie('wk_id_guest', $this->context->cookie->id_guest, time() + 86400, "/");
        } else {
            $this->context->cookie->id_guest = $_COOKIE['wk_id_guest'];
            setcookie('wk_id_guest', $this->context->cookie->id_guest, time() + 86400, "/");
        }
        $guest = new Guest($this->context->cookie->id_guest);

        if (!isset($_COOKIE['wk_id_cart']) && !isset($this->context->cart->id)) {
            $cart = new Cart();

            $cart->recyclable = 0;
            $cart->gift = 0;
            $cart->id_shop = (int)$this->context->shop->id;
            $cart->id_lang = (($id_lang = (int)Tools::getValue('id_lang')) ? $id_lang : Configuration::get('PS_LANG_DEFAULT'));
            $cart->id_currency = (($id_currency = (int)Tools::getValue('id_currency')) ? $id_currency : Configuration::get('PS_CURRENCY_DEFAULT'));
            $cart->id_address_delivery = 0;
            $cart->id_address_invoice = 0;
            $cart->id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
            $cart->id_guest = (int)$this->context->cookie->id_guest;
            $cart->setNoMultishipping();
            $cart->save();

            $this->context->cart = $cart;
            $this->context->cookie->id_cart = $cart->id;

            setcookie('wk_id_cart', $cart->id, time() + 86400, "/");
        } else {
            $cart = new Cart((int)$_COOKIE['wk_id_cart']);

            $this->context->cart = $cart;
            $this->context->cookie->id_cart = $cart->id;
            setcookie('wk_id_cart', $cart->id, time() + 86400, "/");
        }

        $customer = new Customer();
        $customer->id_gender = 0;
        $customer->id_default_group = 1;
        $customer->outstanding_allow_amount = 0;
        $customer->show_public_prices = 0;
        $customer->max_payment_days = 0;
        $customer->active = 1;
        $customer->is_guest = 0;
        $customer->deleted = 0;
        $customer->logged = 0;
        $customer->id_guest = $this->context->cookie->id_guest;

        $this->context->customer = $customer;

        return true;
    }

    public function init()
    {
        parent::init();
        $this->initdefaultData();
    }

    public function initdefaultData()
    {
    }

    public function postProcess()
    {
        if (Tools::getValue('from_date')) {
            $date_from = Tools::getValue('from_date');
        } else {
            $date_from = date('Y-m-d');
        }
        if (Tools::getValue('to_date')) {
            $date_to = Tools::getValue('to_date');
        } else {
            $date_to = date('Y-m-d');
            if (strtotime($date_from) >= strtotime($date_to)) {
                $date_to = date('Y-m-d', strtotime('+1 day', strtotime($date_to)));
            }
        }

        if (Tools::getValue('hotel_id')) {
            $hotel_id = Tools::getValue('hotel_id');
        } else {
            $obj_htl_info = new HotelBranchInformation();
            if ($htl_info = $obj_htl_info->hotelBranchesInfo(false, 1)) {
                // filter hotels as per accessed hotels
                $htl_info = HotelBranchInformation::filterDataByHotelAccess(
                    $htl_info,
                    $this->context->employee->id_profile,
                    1
                );
                $hotel_id = reset($htl_info)['id'];
            }
        }

        if (Tools::getValue('room_type')) {
            $room_type = Tools::getValue('room_type');
        } else {
            $room_type = 0;
        }

        $booking_product = 1;
        if (Tools::getisset('booking_product')) {
            $booking_product = Tools::getValue('booking_product');
        }

        // if search then redirect to prevent form resubmition
        if (Tools::isSubmit('search_hotel_list')) {
            $urlData = array (
                'booking_product' => $booking_product,
                'from_date' => $date_from,
                'to_date' => $date_to,
                'hotel_id' => $hotel_id,
                'room_type' => $room_type
            );
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHotelRoomsBooking').'&'.http_build_query($urlData));
        }

        $this->id_cart = $this->context->cart->id;
        $this->id_guest = $this->context->cookie->id_guest;
        $this->booking_product = $booking_product;
        $this->id_hotel = $hotel_id;
        $this->room_type = $room_type;
        $this->date_from = $date_from;
        $this->date_to = $date_to;

        parent::postprocess();
    }

    public function initContent()
    {
        // $this->show_toolbar = false;
        $this->toolbar_title = $this->l('Book Now');
        $this->display = 'view';
        parent::initContent();

        $this->content = $this->renderKpis();
        $this->content .= $this->renderView();
        $this->context->smarty->assign('content', $this->content);
    }

    public function initSearchFromData()
    {
        $obj_htl_info = new HotelBranchInformation();
        $obj_rm_type = new HotelRoomType();
        $objHotelCartBookingData = new HotelCartBookingData();


        $hotel_list = $obj_htl_info->hotelBranchesInfo(false, 1);
        // filter hotels as per accessed hotels
        $hotel_list = HotelBranchInformation::filterDataByHotelAccess(
            $hotel_list,
            $this->context->employee->id_profile,
            1
        );
        $all_room_type = $obj_rm_type->getRoomTypeByHotelId($this->id_hotel, Configuration::get('PS_LANG_DEFAULT'), 1);
        // $rms_in_cart = $objHotelCartBookingData->getCountRoomsInCart($this->id_cart, $this->id_guest);

        $this->tpl_view_vars = array(
            'hotel_list' => $hotel_list,
            // 'rms_in_cart' => $rms_in_cart,
            'all_room_type' => $all_room_type,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'booking_product' => $this->booking_product,
            'hotel_id' => $this->id_hotel,
            'room_type' => $this->room_type,
        );
    }

    public function initCartData()
    {
        $smartyVars = array(
            'id_cart' => $this->id_cart,
            'id_guest' => $this->id_guest,
        );

        $objHotelCartBookingData = new HotelCartBookingData();
        if ($cartProducts = $this->context->cart->getProducts()) {
            if ($cart_bdata = $objHotelCartBookingData->getCartBookingDetailsByIdCartIdGuest(
                $this->id_cart,
                $this->id_guest,
                Configuration::get('PS_LANG_DEFAULT')
            )) {
                $smartyVars['cart_bdata'] = $cart_bdata;
            }

            if ($normalCartProduct = $this->context->cart->getServiceProducts($cartProducts)) {
                $smartyVars['cart_normal_data'] = $normalCartProduct;
            }
        }
        $rms_in_cart = $objHotelCartBookingData->getCountRoomsInCart($this->id_cart, $this->id_guest);
        $products_in_cart = array_sum(array_column($this->context->cart->getServiceProducts(), 'cart_quantity'));
        $smartyVars['total_products_in_cart'] = (int)$rms_in_cart + (int)$products_in_cart;
        $smartyVars['cart_tamount'] = $this->context->cart->getOrderTotal();

        return $smartyVars;
    }

    public function renderView()
    {
        if ($this->booking_product) {
            $this->assignRoomBookingForm();
        } else {
            $this->assignServiceProductsForm();
        }
        $this->initSearchFromData();
        $this->context->smarty->assign($this->initCartData());
        return parent::renderView();
    }

    public function assignRoomBookingForm()
    {
        $objHotelBookingDetail = new HotelBookingDetail();

        $adult = 0;
        $children = 0;
        $num_rooms = 1;
        $check_css_condition_var = '';

        $booking_data = $this->getAllBookingDataInfo(
            $this->date_from,
            $this->date_to,
            $this->id_hotel,
            $this->room_type,
            $adult,
            $children,
            $num_rooms,
            $this->id_cart,
            $this->id_guest
        );

        //To show info of every date
        $bookingParams = array();
        $bookingParams['hotel_id'] = $this->id_hotel;
        $bookingParams['room_type'] = $this->room_type;
        $bookingParams['adult'] = $adult;
        $bookingParams['children'] = $children;
        $bookingParams['num_rooms'] = $num_rooms;
        $bookingParams['for_calendar'] = 1;
        $bookingParams['search_available'] = 1;
        $bookingParams['search_partial'] = 1;
        $bookingParams['search_booked'] = 1;
        $bookingParams['search_unavai'] = 1;
        $bookingParams['id_cart'] = $this->id_cart;
        $bookingParams['id_guest'] = $this->id_guest;
        $bookingParams['search_cart_rms'] = 1;

        $start_date = $this->date_from; // hard-coded '01' for first day
        $last_day_this_month  = date("Y-m-t", strtotime($this->date_from));;

        while ($start_date <= $last_day_this_month) {
            $cal_date_from = $start_date;
            $cal_date_to = date('Y-m-d', strtotime('+1 day', strtotime($cal_date_from)));
            $bookingParams['date_from'] = $cal_date_from;
            $bookingParams['date_to'] = $cal_date_to;

            $booking_calendar_data[$cal_date_from] = $objHotelBookingDetail->getBookingData($bookingParams);
            $start_date = date('Y-m-d', strtotime('+1 day', strtotime($start_date)));
        }

        if (isset($booking_data)) {
            if ($num_rooms <= $booking_data['stats']['num_avail']) {
                $check_css_condition_var = 'default_available';
            } elseif ($num_rooms <= $booking_data['stats']['num_part_avai']) {
                $check_css_condition_var = 'default_part_available';
            } else {
                $check_css_condition_var = 'default_unavailable';
            }
        }
        $currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
        // booking allotment types
        $allotmentTypes = HotelBookingDetail::getAllAllotmentTypes();

        $this->context->smarty->assign(array(
            'adult' => $adult,
            'children' => $children,
            'num_rooms' => $num_rooms,
            'booking_data' => $booking_data,
            'booking_calendar_data' => $booking_calendar_data,
            'check_css_condition_var' => $check_css_condition_var,
            'currency' => $currency,
            'allotment_types' => $allotmentTypes,
        ));
    }

    public function assignServiceProductsForm()
    {
        $objProduct = new Product();
        $serviceProducts = $objProduct->getServiceProducts($this->context->language->id);
        $hotelAddressInfo = HotelBranchInformation::getAddress($this->id_hotel);
        // $serviceProducts = Product::getProductsProperties($this->context->language->id, $serviceProducts);
        $this->context->smarty->assign(array(
            'service_products' => $serviceProducts
        ));
    }

    public function getAllBookingDataInfo(
        $date_from,
        $date_to,
        $hotel_id,
        $room_type,
        $adult,
        $children,
        $num_rooms,
        $id_cart,
        $id_guest
    ) {
        $objHotelBookingDetail = new HotelBookingDetail();
        $objHotelCartBookingData = new HotelCartBookingData();

        $bookingParams = array();
        $bookingParams['date_from'] = $date_from;
        $bookingParams['date_to'] = $date_to;
        $bookingParams['hotel_id'] = $hotel_id;
        $bookingParams['room_type'] = $room_type;
        $bookingParams['adult'] = $adult;
        $bookingParams['children'] = $children;
        $bookingParams['num_rooms'] = $num_rooms;
        $bookingParams['for_calendar'] = 0;
        $bookingParams['search_available'] = 1;
        $bookingParams['search_partial'] = 1;
        $bookingParams['search_booked'] = 1;
        $bookingParams['search_unavai'] = 1;
        $bookingParams['id_cart'] = $id_cart;
        $bookingParams['id_guest'] = $id_guest;
        $bookingParams['search_cart_rms'] = 1;

        $booking_data = $objHotelBookingDetail->getBookingData($bookingParams);
        if ($booking_data) {
            foreach ($booking_data['rm_data'] as $key_bk_data => $value_bk_data) {
                if (isset($value_bk_data['data']['booked']) && $value_bk_data['data']['booked']) {
                    foreach ($value_bk_data['data']['booked'] as $booked_k1 => $booked_v1) {
                        if (isset($booked_v1['detail']) && $booked_v1['detail']) {
                            foreach ($booked_v1['detail'] as $kDtl => $bookedDtls) {
                                $cust_obj = new Customer($booked_v1['detail'][$kDtl]['id_customer']);
                                if ($cust_obj->firstname) {
                                    $booking_data['rm_data'][$key_bk_data]['data']['booked'][$booked_k1]['detail'][$kDtl]['alloted_cust_name'] = $cust_obj->firstname.' '.$cust_obj->lastname;
                                } else {
                                    $booking_data['rm_data'][$key_bk_data]['data']['booked'][$booked_k1]['detail'][$kDtl]['alloted_cust_name'] = "No customer name found";
                                }
                                if ($cust_obj->email) {
                                    $booking_data['rm_data'][$key_bk_data]['data']['booked'][$booked_k1]['detail'][$kDtl]['alloted_cust_email'] = $cust_obj->email;
                                } else {
                                    $booking_data['rm_data'][$key_bk_data]['data']['booked'][$booked_k1]['detail'][$kDtl]['alloted_cust_email'] = "No customer email found";
                                }
                                $booking_data['rm_data'][$key_bk_data]['data']['booked'][$booked_k1]['detail'][$kDtl]['avail_rooms_to_realloc'] = $objHotelBookingDetail->getAvailableRoomsForReallocation($booked_v1['detail'][$kDtl]['date_from'], $booked_v1['detail'][$kDtl]['date_to'], $booked_v1['id_product'], $booked_v1['id_hotel']);
                                $booking_data['rm_data'][$key_bk_data]['data']['booked'][$booked_k1]['detail'][$kDtl]['avail_rooms_to_swap'] = $objHotelBookingDetail->getAvailableRoomsForSwapping($booked_v1['detail'][$kDtl]['date_from'], $booked_v1['detail'][$kDtl]['date_to'], $booked_v1['id_product'], $booked_v1['id_hotel'], $booked_v1['id_room']);
                            }
                        }
                    }
                }
            }
        }
        return $booking_data;
    }

    public function renderKpis()
    {
        $kpis = array();

        $helper = new HelperKpi();
        $helper->id = 'box-order-total';
        $helper->icon = 'icon-money';
        $helper->color = 'color3';
        $helper->title = $this->l('Total Order Amount');
        $helper->source = $this->context->link->getAdminLink('AdminHotelRoomsBooking').'&ajax=1&action=getKpi&kpi=order_total';
        $helper->value = Tools::displayPrice($this->context->cart->getOrderTotal());
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-room-in-cart';
        $helper->icon = 'icon-shopping-cart';
        $helper->color = 'color1';
        $helper->title = $this->l('Products In Cart', null, null, false);
        $helper->href = '#cartModal';
        $helper->value = 0;
        $objHotelCartBookingData = new HotelCartBookingData();
        $rms_in_cart = $objHotelCartBookingData->getCountRoomsInCart($this->id_cart, $this->id_guest);
        $products_in_cart = array_sum(array_column($this->context->cart->getServiceProducts(), 'cart_quantity'));
        $helper->value = (int)$rms_in_cart + (int)$products_in_cart;
        $helper->source = $this->context->link->getAdminLink('AdminHotelRoomsBooking').'&ajax=1&action=getKpi&kpi=room_in_cart';
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-book-now';
        $helper->icon = 'icon-arrow-right';
        $helper->color = 'color4';
        $helper->refresh = 0;
        $helper->value = $this->l('Book Now');
        $helper->title = $this->l('Proceed to checkout');
        $helper->href = $this->context->link->getAdminLink('AdminOrders').'&addorder&cart_id='.$this->id_cart.'&guest_id='.$this->id_guest;
        $helper->source = $this->context->link->getAdminLink('AdminHotelRoomsBooking').'&ajax=1&action=getKpi&kpi=book_now';
        $kpis[] = $helper->generate();

        $helper = new HelperKpiRow();
        $helper->kpis = $kpis;

        return $helper->generate();
    }

    public function ajaxProcessGetKpi()
    {
        $kpi = Tools::getValue('kpi');
        if ('room_in_cart' == $kpi) {
            $objHotelCartBookingData = new HotelCartBookingData();
            $rms_in_cart = $objHotelCartBookingData->getCountRoomsInCart($this->id_cart, $this->id_guest);
            $products_in_cart = array_sum(array_column($this->context->cart->getServiceProducts(), 'cart_quantity'));
            die(json_encode(array('value' => (int)$rms_in_cart + (int)$products_in_cart)));
        }

        if ('order_total' == $kpi) {
            die(json_encode(array('value' => Tools::displayPrice($this->context->cart->getOrderTotal()))));
        }

        die(json_encode(array('status' => false)));
    }

    public function ajaxProcessGetRoomType()
    {
        $hotel_id  = Tools::getValue('hotel_id');
        $obj_room_type = new HotelRoomType();
        $room_type_info = $obj_room_type->getRoomTypeByHotelId($hotel_id, Configuration::get('PS_LANG_DEFAULT'), 1);
        die(json_encode($room_type_info));
    }

    public function ajaxProcessUpdateProductInCart()
    {
        $response = array(
            'status' => false
        );
        $id_product = Tools::getValue('id_product');
        $quantity = Tools::getValue('qty', 1);
        $id_cart = $this->context->cart->id;
        $id_hotel = Tools::getValue('id_hotel');
        $opt = Tools::getValue('opt', 1);

        if ($opt) {
            // validation for adding product in cart
            $product = new Product($id_product, true, $this->context->language->id);
            if (!$product->id || !$product->active) {
                $this->errors[] = $this->l('This product is no longer available.');
            }
            if ($product->booking_product || ($product->service_product_type != Product::SERVICE_PRODUCT_WITHOUT_ROOMTYPE)) {
                // cannot be added without room type or is a booking product.
                $this->errors[] = $this->l('This product is either a room type or additional service and cannot be added thorugh this method.');
            } elseif (!$product->allow_multiple_quantity) {
                // check if product already exists in cart.
                if ($id_cart) {
                    if (cart::getProductQtyInCart($id_cart, $product->id)) {
                        $this->errors[] = Tools::displayError('You can only order one quantity for this product.');
                    }
                }
            }
            // get hotel address for product tax calculation
            if (validate::isLoadedObject($objHotelBranch = new HotelBranchInformation($id_hotel))) {
                $hotelIdAddress = $objHotelBranch->getHotelIdAddress();
            } else {
                $this->errrors[] = $this->l('Hotel not found');
            }

        }

        if (empty($this->errors)) {
            if ($opt) {
                $direction = 'up';
                if ($this->context->cart->updateQty($quantity, $product->id, null, false, $direction, $hotelIdAddress)) {
                    $response = array(
                        'status' => true,
                        'total_amount' => $this->context->cart->getOrderTotal()
                    );
                    die(json_encode($response));
                }
            } else {
                if ($this->context->cart->deleteProduct($id_product)) {
                    $response = array(
                        'status' => true,
                        'total_amount' => $this->context->cart->getOrderTotal()
                    );
                    die(json_encode($response));
                }

            }
        } else {
            $response['errors'] = $this->errors;
        }

        die(json_encode($response));
    }

    public function ajaxProcessAddRoomToCart()
    {
        // for Add Quantity
        $id_room = Tools::getValue('id_room');
        $booking_type = Tools::getValue('booking_type');
        $comment = Tools::getValue('comment');

        // for both (add , delete)
        $id_hotel = Tools::getValue('id_hotel');
        $id_product = Tools::getValue('id_prod');
        $date_from = Tools::getValue('date_from');
        $date_to = Tools::getValue('date_to');

        $date_from = date("Y-m-d", strtotime($date_from));
        $date_to = date("Y-m-d", strtotime($date_to));

        $search_id_prod = Tools::getValue('search_id_prod');
        $search_date_from = Tools::getValue('search_date_from');
        $search_date_to = Tools::getValue('search_date_to');

        // for delete quantity
        $id_cart = Tools::getValue('id_cart');
        $id_cart_book_data = Tools::getValue('id_cart_book_data');
        $ajax_delete = Tools::getValue('ajax_delete'); // If delete from cart(not for room list delete(pagebottom tabs))

        $opt = Tools::getValue('opt'); // if 1 then add quantity or if 0 means delete quantity

        $objHotelBookingDetail = new HotelBookingDetail();
        $num_day = $objHotelBookingDetail->getNumberOfDays($date_from, $date_to); //quantity of product
        $product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'));

        if ($opt) {
            $unit_price = Product::getPriceStatic(
                $id_product,
                HotelBookingDetail::useTax(),
                null,
                6,
                null,
                false,
                true,
                $num_day
            );
        }

        if ($opt) {
            $direction = 'up';
        } else {
            $direction = 'down';
        }

        $this->context->cart->updateQty($num_day, $id_product, null, false, $direction);


        $id_cart = $this->context->cart->id;
        $id_guest = $this->context->cookie->id_guest;

        $objHotelCartBookingData = new HotelCartBookingData();
        if ($opt) {
            $objHotelCartBookingData->id_cart = $id_cart;
            $objHotelCartBookingData->id_guest = $id_guest;
            $objHotelCartBookingData->id_product = $id_product;
            $objHotelCartBookingData->id_room = $id_room;
            $objHotelCartBookingData->id_hotel = $id_hotel;
            $objHotelCartBookingData->quantity = $num_day;
            $objHotelCartBookingData->id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
            $objHotelCartBookingData->booking_type = $booking_type;
            $objHotelCartBookingData->comment = $comment;
            $objHotelCartBookingData->date_from = $date_from;
            $objHotelCartBookingData->date_to = $date_to;
            $objHotelCartBookingData->save();

            $obj_rm_info = new HotelRoomInformation($id_room);
            $total_amount = $this->context->cart->getOrderTotal();
            $rms_in_cart = $objHotelCartBookingData->getCountRoomsInCart($id_cart, $id_guest);
            $products_in_cart = array_sum(array_column($this->context->cart->getServiceProducts(), 'cart_quantity'));

            $bookingParams = array();
            $bookingParams['date_from'] = $search_date_from;
            $bookingParams['date_to'] = $search_date_to;
            $bookingParams['hotel_id'] = $id_hotel;
            $bookingParams['room_type'] = $search_id_prod;
            $bookingParams['adult'] = 0;
            $bookingParams['children'] = 0;
            $bookingParams['num_rooms'] = 1;
            $bookingParams['for_calendar'] = 1;
            $bookingParams['search_available'] = 1;
            $bookingParams['search_partial'] = 1;
            $bookingParams['search_booked'] = 0;
            $bookingParams['search_unavai'] = 0;
            $bookingParams['id_cart'] = $id_cart;
            $bookingParams['id_guest'] = $id_guest;
            $bookingParams['search_cart_rms'] = 1;

            $booking_stats = $objHotelBookingDetail->getBookingData($bookingParams);
            //$rm_amount = $unit_price * (int)$num_day;
            //$rm_amount = Tools::ps_round($rm_amount, 2);
            //
            //// By webkul New way to calculate product prices with feature Prices
            $roomTypeDateRangePrice = HotelRoomTypeFeaturePricing::getRoomTypeTotalPrice(
                $id_product,
                $date_from,
                $date_to
            );
            $rm_amount = $roomTypeDateRangePrice['total_price_tax_incl'];

            $cart_data = array('room_num' => $obj_rm_info->room_num,
                'room_type' => Product::getProductName((int)$id_product),
                'date_from' => date('Y-M-d', strtotime($date_from)),
                'date_to' => date('Y-M-d', strtotime($date_to)),
                'amount' => $rm_amount,
                'qty' => $num_day,
                'total_products_in_cart' => (int)$rms_in_cart + (int)$products_in_cart,
                'total_amount' => $total_amount,
                'booking_stats' => $booking_stats,
                'id_cart_book_data' => $objHotelCartBookingData->id
            );

            if ($objHotelCartBookingData->id) {
                die(json_encode($cart_data));
            } else {
                die(0);
            }
        } else {
            $total_amount = $this->context->cart->getOrderTotal();
            $data_dlt = $objHotelCartBookingData->deleteRowById($id_cart_book_data);
            if ($data_dlt) {
                $rms_in_cart = $objHotelCartBookingData->getCountRoomsInCart($id_cart, $id_guest);
                $products_in_cart = array_sum(array_column($this->context->cart->getServiceProducts(), 'cart_quantity'));

                if (!$ajax_delete) {
                    $bookingParams = array();
                    $bookingParams['date_from'] = $search_date_from;
                    $bookingParams['date_to'] = $search_date_to;
                    $bookingParams['hotel_id'] = $id_hotel;
                    $bookingParams['room_type'] = $search_id_prod;
                    $bookingParams['adult'] = 0;
                    $bookingParams['children'] = 0;
                    $bookingParams['num_rooms'] = 1;
                    $bookingParams['for_calendar'] = 1;
                    $bookingParams['search_available'] = 1;
                    $bookingParams['search_partial'] = 1;
                    $bookingParams['search_booked'] = 0;
                    $bookingParams['search_unavai'] = 0;
                    $bookingParams['id_cart'] = $id_cart;
                    $bookingParams['id_guest'] = $id_guest;
                    $bookingParams['search_cart_rms'] = 1;

                    $booking_stats = $objHotelBookingDetail->getBookingData($bookingParams);
                    $cart_data = array(
                        'total_amount' => $total_amount,
                        'total_products_in_cart' => (int)$rms_in_cart + (int)$products_in_cart,
                        'booking_stats' => $booking_stats
                    );
                }

                if ($ajax_delete) {
                    $obj_htl_info = new HotelBranchInformation();
                    $obj_rm_type = new HotelRoomType();

                    $this->context->smarty->assign(
                        array(
                            'id_cart' => $id_cart,
                            'id_guest' => $id_guest
                        )
                    );

                    // No use of adult, child, num_rooms
                    $adult = 0;
                    $children = 0;
                    $num_rooms = 1;

                    $booking_data = array();

                    $booking_data = $this->getAllBookingDataInfo(
                        $search_date_from,
                        $search_date_to,
                        $id_hotel,
                        $search_id_prod,
                        $adult,
                        $children,
                        $num_rooms,
                        $id_cart,
                        $id_guest
                    );
                    $allotmentTypes = HotelBookingDetail::getAllAllotmentTypes();

                    $this->context->smarty->assign(
                        array(
                            'date_from' => $search_date_from,
                            'date_to'=>$search_date_to,
                            'booking_data'=>$booking_data,
                            'ajax_delete'=>$ajax_delete,
                            'allotment_types' => $allotmentTypes,
                        )
                    );
                    $tpl_path = 'hotelreservationsystem/views/templates/admin/hotel_rooms_booking/_partials/booking-rooms.tpl';
                    $room_tpl = $this->context->smarty->fetch(_PS_MODULE_DIR_.$tpl_path);

                    $cart_data = array(
                        'total_amount' => $total_amount,
                        'room_tpl' => $room_tpl,
                        'total_products_in_cart' => (int)$rms_in_cart + (int)$products_in_cart,
                        'booking_data' => $booking_data,
                    );
                }
                die(json_encode($cart_data));
            } else {
                die(0);
            }
        }
    }

    public function ajaxProcessGetDataOnMonthChange()
    {
        $month = Tools::getValue('month');
        $year = Tools::getValue('year');
        $query_date = $year.'-'.$month.'-04';
        $start_date = date('Y-m-01', strtotime($query_date)); // hard-coded '01' for first day
        $last_day_this_month  = date('Y-m-t', strtotime($query_date));
        $hotel_id = 1;
        $room_type = 0;
        $adult = 1;
        $children = 0;
        $num_rooms = 1;

        $objHotelBookingDetail = new HotelBookingDetail();

        $bookingParams = array();
        $bookingParams['hotel_id'] = $hotel_id;
        $bookingParams['room_type'] = $room_type;
        $bookingParams['adult'] = $adult;
        $bookingParams['children'] = $children;
        $bookingParams['num_rooms'] = $num_rooms;
        while ($start_date <= $last_day_this_month) {
            $cal_date_from = $start_date;
            $cal_date_to = date('Y-m-d', strtotime('+1 day', strtotime($cal_date_from)));

            $bookingParams['date_from'] = $cal_date_from;
            $bookingParams['date_to'] = $cal_date_to;

            $booking_calendar_data[$cal_date_from] = $objHotelBookingDetail->getBookingData($bookingParams);
            $start_date = date('Y-m-d', strtotime('+1 day', strtotime($start_date)));
        }
        if ($booking_calendar_data) {
            die(json_encode($booking_calendar_data));
        } else {
            die(0);
        }
    }

    public function ajaxProcessUpdateCartData()
    {
        $tplVars = $this->initCartData();
        // ddd($tplVars);
        $tplVars['link'] = $this->context->link;
        $this->context->smarty->assign($tplVars);
        $room_tpl = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/hotel_rooms_booking/_partials/cart-modal.tpl'
        );

        die(json_encode(array('cart_content' => $room_tpl)));
    }

    public function setMedia()
    {
        parent::setMedia();
        $check_calender_var = 1;
        $currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
        $jsVars = array(
            'currency_prefix' => $currency->prefix,
            'currency_suffix' => $currency->suffix,
            'currency_sign' => $currency->sign,
            'currency_format' => $currency->format,
            'currency_blank' => $currency->blank,
            'rooms_booking_url' => $this->context->link->getAdminLink('AdminHotelRoomsBooking'),
            'opt_select_all' => $this->l('All Types'),
            'slt_another_htl' => $this->l('Select Another Hotel'),
            'product_type_cond' => $this->l('Product type is required'),
            'from_date_cond' => $this->l('From date is required'),
            'to_date_cond' => $this->l('To date is required'),
            'hotel_name_cond' => $this->l('Hotel Name is required'),
            'num_rooms_cond' => $this->l('Number of Rooms is required'),
            'add_to_cart' => $this->l('Add To Cart'),
            'remove' => $this->l('Remove'),
            'noRoomTypeAvlTxt' => $this->l('No room type available.'),
            'no_rm_avail_txt' => $this->l('No rooms available.'),
            'slct_rm_err' => $this->l('Please select a room first.'),
            'product_added_cart_txt' => $this->l('Product added in cart'),
            'check_calender_var' => $check_calender_var,
        );
        MediaCore::addJsDef($jsVars);

        $this->addCSS(array(_MODULE_DIR_.'hotelreservationsystem/views/css/HotelReservationAdmin.css'));
        $this->addJs(_MODULE_DIR_.$this->module->name.'/views/js/admin/book-now.js');
    }
}
