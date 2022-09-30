<div class="row">
    <div class="col-sm-4">
        <div class="panel">
            <h3 class="tab">
                <i class="icon-info"></i> {l s='Booking Information' mod='hotelreservationsystem'}
                {* <button type="button" class="btn btn-primary pull-right margin-right-10" id="cart_btn" data-toggle="modal" data-target="#cartModal"><i class="icon-shopping-cart"></i> {l s='Cart' mod='hotelreservationsystem'} <span class="badge" id="cart_record">{$rms_in_cart}</span></button> *}
            </h3>
            <div class="panel-body padding-0">
                <div class="row margin-right-10">
                    <form method="post" action="">
                        <div class="row">
                            <div class="form-group col-sm-12">
                                <label for="booking_product" class="control-label col-sm-4 required">
                                    <span title="" data-toggle="tooltip" class="label-tooltip">{l s='Product type' mod='hotelreservationsystem'}</span>
                                </label>
                                <div class="col-sm-8">
                                    <select name="booking_product" class="form-control" id="booking_product">
                                        <option value="1" {if isset($booking_product) && $booking_product == 1}selected{/if}>{l s='Rooms'}</option>
                                        <option value="0" {if isset($booking_product) && $booking_product == 0}selected{/if}>{l s='Service Products'}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="hotel_id" class="control-label col-sm-4 required">
                                    <span title="" data-toggle="tooltip" class="label-tooltip">{l s='Hotel Name' mod='hotelreservationsystem'}</span>
                                </label>
                                <div class="col-sm-8">
                                    <select name="hotel_id" class="form-control" id="hotel_id">
                                        {if isset($hotel_list) && $hotel_list}
                                            {foreach $hotel_list as $name_val}
                                                <option value="{$name_val['id']|escape:'htmlall':'UTF-8'}" {if isset($hotel_id) && ($name_val['id'] == $hotel_id)}selected{/if}>{$name_val['hotel_name']|escape:'htmlall':'UTF-8'}</option>
                                            {/foreach}
                                        {else}
                                            <option value="0">{l s='No hotels available' mod='hotelreservationsystem'}</option>
                                        {/if}
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="date_from" class="control-label col-sm-4 required">
                                    <span title="" data-toggle="tooltip" class="label-tooltip">{l s='Check-In' mod='hotelreservationsystem'}</span>
                                </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="date_from" {if isset($date_from)}value="{$date_from|escape:'htmlall':'UTF-8'|date_format:"%d-%m-%Y"}"{/if}>
                                    <input type="hidden" name="from_date" id="from_date" {if isset($date_from)}value="{$date_from|escape:'htmlall':'UTF-8'}"{/if}>
                                    <input type="hidden" name="search_date_from" id="search_date_from" {if isset($date_from)}value="{$date_from|escape:'htmlall':'UTF-8'}"{/if}>
                                </div>
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="date_to" class="control-label col-sm-4 required">
                                    <span title="" data-toggle="tooltip" class="label-tooltip">{l s='Check-Out' mod='hotelreservationsystem'}</span>
                                </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="date_to" {if isset($date_to)}value="{$date_to|escape:'htmlall':'UTF-8'|date_format:"%d-%m-%Y"}"{/if}>
                                    <input type="hidden" name="to_date" id="to_date" {if isset($date_to)}value="{$date_to|escape:'htmlall':'UTF-8'}"{/if}>
                                    <input type="hidden" name="search_date_to" id="search_date_to" {if isset($date_to)}value="{$date_to|escape:'htmlall':'UTF-8'}"{/if}>
                                </div>
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="room_type" class="control-label col-sm-4">
                                    <span title="" data-toggle="tooltip" class="label-tooltip">{l s='Room Type' mod='hotelreservationsystem'}</span>
                                </label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="room_type" id="room_type">
                                            <option value='0' {if  isset($room_type) && ($room_type == 0)}selected{/if}>{l s='All Types' mod='hotelreservationsystem'}</option>
                                            {if (isset($all_room_type) && $all_room_type)}
                                                {foreach $all_room_type as $val_type}
                                                    <option value="{$val_type['id_product']|escape:'htmlall':'UTF-8'}" {if isset($room_type) && ($val_type['id_product'] == $room_type)}selected{/if}>{$val_type['room_type']|escape:'htmlall':'UTF-8'}</option>
                                                {/foreach}
                                            {/if}
                                    </select>
                                    <input type="hidden" name="search_id_prod" id="search_id_prod" value="{if isset($room_type)}{$room_type}{else}0{/if}">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <button id="search_hotel_list" name="search_hotel_list" type="submit" class="btn btn-primary pull-right">
                                    <i class="icon-search"></i>&nbsp;&nbsp;{l s='Search' mod='hotelreservationsystem'}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="panel">
            <h3 class="tab">
                <i class="icon-info"></i> {l s='Booking Information' mod='hotelreservationsystem'}
                {* <button type="button" class="btn btn-primary pull-right margin-right-10" id="cart_btn" data-toggle="modal" data-target="#cartModal"><i class="icon-shopping-cart"></i> {l s='Cart' mod='hotelreservationsystem'} <span class="badge" id="cart_record">{$rms_in_cart}</span></button> *}
            </h3>
            <div class="panel-body padding-0">
                <div class="row">
                    {if !isset($booking_product) || (isset($booking_product) && $booking_product == 1)}
                        {include file="../../_partials/booking-calender.tpl"}
                    {else if isset($booking_product) && $booking_product == 0}
                        {include file="../../_partials/service-products.tpl"}
                    {/if}
                </div>
            </div>
        </div>
    </div>
    {if !isset($booking_product) || (isset($booking_product) && $booking_product == 1)}
        {include file="../../_partials/booking-rooms.tpl"}
    {/if}
</div>
<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    {include file="../../_partials/cart-modal.tpl"}
</div>

<!-- Modal for reallocation of rooms -->
<div class="modal fade" id="mySwappigModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
	<div class="modal-content">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#reallocate_room_tab" aria-controls="reallocate" role="tab" data-toggle="tab">{l s='Room Reallocation' mod='hotelreservationsystem'}</a></li>
			<li role="presentation"><a href="#swap_room_tab" aria-controls="swap" role="tab" data-toggle="tab">{l s='Swap Room' mod='hotelreservationsystem'}</a></li>
			</ul>
		<div class="tab-content panel active">
			<div role="tabpanel" class="tab-pane active" id="reallocate_room_tab">
				<form method="post" action="">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="realloc_myModalLabel">{l s='Reallocate Rooms'}</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="curr_room_num" class="control-label">{l s='Current Room Number:' mod='hotelreservationsystem'}</label>
							<input type="text" class="form-control modal_curr_room_num" name="modal_curr_room_num" readonly="true">
							<input type="hidden" class="form-control modal_date_from" name="modal_date_from">
							<input type="hidden" class="form-control modal_date_to" name="modal_date_to">
							<input type="hidden" class="form-control modal_id_room" name="modal_id_room">
						</div>
						<div class="form-group">
							<label for="realloc_avail_rooms" class="control-label">{l s='Available Rooms To Reallocate:' mod='hotelreservationsystem'}</label>
							<div style="width: 195px;">
								<select class="form-control" name="realloc_avail_rooms" id="realloc_avail_rooms">
									<option value="0" selected="selected">{l s='Select Rooms' mod='hotelreservationsystem'}</option>
								</select>
								<p class="error_text" id="realloc_sel_rm_err_p"></p>
							</div>
						</div>
						<div class="form-group">
							<label style="text-decoration:underline;margin-top:5px;" for="message-text" class="col-sm-12 control-label"><i class="icon-info-circle"></i>&nbsp;{l s='Currently Alloted Customer Information:' mod='hotelreservationsystem'}</label>
							<dl class="well list-detail">
								<dt>{l s='Name' mod='hotelreservationsystem'}</dt>
								<dd class="cust_name"></dd><br>
								<dt>{l s='Email' mod='hotelreservationsystem'}</dt>
								<dd class="cust_email"></dd><br>
							</dl>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">{l s="Close" mod="hotelreservationsyatem"}</button>
						<input type="submit" id="realloc_allocated_rooms" name="realloc_allocated_rooms" class="btn btn-primary" value="Reallocate">
					</div>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="swap_room_tab">
				<form method="post" action="">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="swap_myModalLabel">{l s='Swap Rooms' mod='hotelreservationsystem'}</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="swap_curr_room_num" class="control-label">{l s='Current Room Number:' mod='hotelreservationsystem'}</label>
							<input type="text" class="form-control modal_curr_room_num" name="modal_curr_room_num" readonly="true">
							<input type="hidden" class="form-control modal_date_from" name="modal_date_from">
							<input type="hidden" class="form-control modal_date_to" name="modal_date_to">
							<input type="hidden" class="form-control modal_id_room" name="modal_id_room">
						</div>
						<div class="form-group">
							<label for="swap_avail_rooms" class="control-label">{l s='Available Rooms To Swap:' mod='hotelreservationsystem'}</label>
							<div style="width: 195px;">
								<select class="form-control" name="swap_avail_rooms" id="swap_avail_rooms">
									<option value="0" selected="selected">{l s='Select Rooms' mod='hotelreservationsystem'}</option>
								</select>
								<p class="error_text" id="swap_sel_rm_err_p"></p>
							</div>
						</div>
						<div class="form-group">
							<label style="text-decoration:underline;margin-top:5px;" for="message-text" class="col-sm-12 control-label"><i class="icon-info-circle"></i>&nbsp;{l s='Currently Alloted Customer Information:' mod='hotelreservationsystem'}</label>
							<dl class="well list-detail">
								<dt>{l s='Name' mod='hotelreservationsystem'}</dt>
								<dd class="cust_name"></dd><br>
								<dt>{l s='Email' mod='hotelreservationsystem'}</dt>
								<dd class="cust_email"></dd><br>
							</dl>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">{l s="Close" mod="hotelreservationsyatem"}</button>
						<input type="submit" id="swap_allocated_rooms" name="swap_allocated_rooms" class="btn btn-primary" value="Swap">
					</div>
				</form>
			</div>
		</div>
    </div>
  </div>
</div>

{strip}
    {if isset($booking_calendar_data)}
	    {addJsDef booking_calendar_data = $booking_calendar_data|@json_encode}
    {/if}
    {if isset($check_css_condition_var)}
	    {addJsDef check_css_condition_var = $check_css_condition_var}
    {/if}
{/strip}
