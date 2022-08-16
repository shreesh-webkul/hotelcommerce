{if isset($booking_data) && $booking_data}
    <div class="col-sm-12">
        <div class="row margin-lr-0 calender-main-div">
            <div class="hotel_date col-sm-7">
                <div class="row margin-leftrgt-0">
                    <div class="col-sm-12 htl_date_header">
                        <div class="col-sm-3">
                            <p class="htl_date_disp">{$date_from|escape:'htmlall':'UTF-8'|date_format:"%d"}</p>
                            <span class="htl_month_disp">{$date_from|escape:'htmlall':'UTF-8'|date_format:"%b"}</span>
                        </div>
                        <div class="col-sm-1">
                            <p class="htl_date_disp">-</p>
                        </div>
                        <div class="col-sm-3">
                            <p class="htl_date_disp">{$date_to|escape:'htmlall':'UTF-8'|date_format:"%d"}</p>
                            <span class="htl_month_disp">{$date_to|escape:'htmlall':'UTF-8'|date_format:"%b"}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-5 htl_room_data_cont">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-12 htl_room_cat_data">
                                <p class="room_cat_header">{l s='Total Rooms' mod='hotelreservationsystem'}</p>
                                <p class="room_cat_data">{if isset($booking_data) && $booking_data}{$booking_data['stats']['total_rooms']|escape:'htmlall':'UTF-8'}{else}00{/if}</p>
                            </div>
                        </div>
                        <hr class="hr_style" />
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-12 htl_room_cat_data no_border">
                                <p class="room_cat_header">{l s='Partially Available' mod='hotelreservationsystem'}</p>
                                <p class="room_cat_data" id="num_part">{if isset($booking_data) && $booking_data}{$booking_data['stats']['num_part_avai']|escape:'htmlall':'UTF-8'}{else}00{/if}</p>
                            </div>
                        </div>
                        <hr class="hr_style" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-12 htl_room_cat_data">
                                <p class="room_cat_header">{l s='Available Rooms' mod='hotelreservationsystem'}</p>
                                <p class="room_cat_data" id="num_avail">{if isset($booking_data) && $booking_data}{$booking_data['stats']['num_avail']|escape:'htmlall':'UTF-8'}{else}00{/if}</p>
                            </div>
                        </div>
                        <hr class="hr_style" />
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-12 htl_room_cat_data no_border">
                                <p class="room_cat_header">{l s='Booked Rooms' mod='hotelreservationsystem'}</p>
                                <p class="room_cat_data">{if isset($booking_data) && $booking_data}{$booking_data['stats']['num_booked']|escape:'htmlall':'UTF-8'}{else}00{/if}</p>
                            </div>
                        </div>
                        <hr class="hr_style" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-12 htl_room_cat_data">
                                <p class="room_cat_header">{l s='Unavailable Rooms' mod='hotelreservationsystem'}</p>
                                <p class="room_cat_data">{if isset($booking_data) && $booking_data}{$booking_data['stats']['num_unavail']|escape:'htmlall':'UTF-8'}{else}00{/if}</p>
                            </div>
                        </div>
                        <hr class="hr_style" />
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-12 htl_room_cat_data">
                                <p class="room_cat_header">{l s='In-Cart Rooms' mod='hotelreservationsystem'}</p>
                                <p class="room_cat_data" id="cart_stats">{if isset($booking_data) && $booking_data}{$booking_data['stats']['num_cart']|escape:'htmlall':'UTF-8'}{else}00{/if}</p>
                            </div>
                        </div>
                        <hr class="hr_style" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 indi_cont clearfix">
                        <div class="color_indicate bg-green"></div>
                        <span class="indi_label">{l s='Available Rooms' mod='hotelreservationsystem'}</span>
                    </div>
                    <div class="col-sm-6 indi_cont clearfix">
                        <div class="color_indicate bg-yellow"></div>
                        <span class="indi_label">{l s='Partially Available' mod='hotelreservationsystem'}</span>
                    </div>
                    <div class="col-sm-6 indi_cont clearfix">
                        <div class="color_indicate bg-red"></div>
                        <span class="indi_label">{l s='Unavailable Rooms' mod='hotelreservationsystem'}</span>
                    </div>
                    <!-- <div class="col-sm-6 indi_cont clearfix">
                        <div class="color_indicate bg-gray"></div>
                        <span class="indi_label">{l s='Hold For Maintenance' mod='hotelreservationsystem'}</span>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
{else}
    <div class="col-sm-12">
        <p class="alert alert-warning">	{l s="No booking information found. Please make sure at least one active hotel and room type must be available." mod="hotelreservationsystem"}</p>
    </div>
{/if}