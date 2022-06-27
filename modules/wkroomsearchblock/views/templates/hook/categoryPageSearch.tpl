{*
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
*}

{if isset($hotels_info) && count($hotels_info)}
	<div class="header-rmsearch-wrapper">
        <div class="filter_header">
            <p>{l s='Search Rooms' mod='wkroomsearchblock'}</p>
            <hr class="header-bottom-hr">
        </div>
		<form method="POST" id="search_hotel_block_form">
			{if isset($location_enabled) && $location_enabled}
				<div class="form-group">
					<input type="text" class="form-control header-rmsearch-input" id="hotel_location" name="hotel_location" autocomplete="off" placeholder="{l s='Hotel location' mod='wkroomsearchblock'}" {if isset($search_data)}value="{$search_data['location']|escape:'htmlall':'UTF-8'}" city_cat_id="{$search_data['parent_data']['id_category']|escape:'htmlall':'UTF-8'}"{/if}>
					<div class="dropdown">
						<ul class="location_search_results_ul dropdown-menu"></ul>
					</div>
				</div>
			{/if}
			<div class="form-group {if count($hotels_info) <= 1 && !$show_hotel_name} hidden {/if}">
				{if !$show_hotel_name}
					<input type="hidden" id="max_order_date" name="max_order_date" value="{if isset($hotels_info[0]['max_order_date'])}{$hotels_info[0]['max_order_date']|escape:'htmlall':'UTF-8'}{/if}">
					<input type="hidden" id="hotel_cat_id" name="hotel_cat_id" value="{$hotels_info[0]['id_category']}">
					<input type="hidden" id="id_hotel" name="id_hotel" value="{$hotels_info[0]['id']|escape:'htmlall':'UTF-8'}">
					<input type="text" id="htl_name" class="form-control header-rmsearch-input" value="{$hotels_info[0]['hotel_name']}" readonly>
				{else}
					{if isset($hotels_info) && count($hotels_info)}
						<div class="dropdown">
							<input type="hidden" id="hotel_cat_id" name="hotel_cat_id" {if isset($search_data)}value="{$search_data['htl_dtl']['id_category']|escape:'htmlall':'UTF-8'}"{/if}>
                            <input type="hidden" id="id_hotel" name="id_hotel" {if isset($search_data)}value="{$search_data['htl_dtl']['id']|escape:'htmlall':'UTF-8'}"{/if}>
                            <input type="hidden" id="max_order_date" name="max_order_date" value="{if isset($max_order_date)}{$max_order_date|escape:'htmlall':'UTF-8'}{/if}">

							<button class="form-control header-rmsearch-input {if isset($error) && $error == 1}error_border{/if}" type="button" data-toggle="dropdown" id="id_hotel_button">
								{if isset($search_data)}
                                <span id="hotel_cat_name" class="pull-left">{$search_data['htl_dtl']['hotel_name']|escape:'htmlall':'UTF-8'}</span>
                            {else}
                                <span id="hotel_cat_name" class="pull-left">{l s='Select Hotel' mod='wkroomsearchblock'}</span>
                            {/if}
							</button>
							<ul class="dropdown-menu hotel_dropdown_ul">
								{if isset($hotels_info) && $hotels_info}
									{foreach $hotels_info as $name_val}
										<li tabindex="-1" class="search_result_li" data-id-hotel="{$name_val['id']|escape:'htmlall':'UTF-8'}" data-hotel-cat-id="{$name_val['id_category']|escape:'htmlall':'UTF-8'}" data-max_order_date="{$name_val['max_order_date']}">{$name_val['hotel_name']|escape:'htmlall':'UTF-8'}</li>
									{/foreach}
								{/if}
							</ul>
						</div>
					{/if}
				{/if}
			</div>
            <div class="form-group check_in_field_block">
                <input type="text" class="form-control header-rmsearch-input input-date" id="check_in_time" name="check_in_time" autocomplete="off" placeholder="{l s='Check-In' mod='wkroomsearchblock'}"  {if isset($search_data)}value="{$search_data['date_from']|escape:'htmlall':'UTF-8'}"{/if} readonly>
            </div>
            <div class="form-group check_out_field_block">
                <input type="text" class="form-control header-rmsearch-input input-date" id="check_out_time" name="check_out_time" autocomplete="off" placeholder="{l s='Check-Out' mod='wkroomsearchblock'}"  {if isset($search_data)}value="{$search_data['date_to']|escape:'htmlall':'UTF-8'}"{/if} readonly>
            </div>
			<div class="form-group">
				<div class="dropdown">
					<button class="form-control header-rmsearch-input {if isset($error) && $error == 1}error_border{/if}" type="button" data-toggle="dropdown" id="guest_occupancy">
						<span class="pull-left">{if (isset($search_data['occupancy_adults']) && $search_data['occupancy_adults'])}
							{$search_data['occupancy_adults']} {l s='Adult' mod='wkroomsearchblock'}, {if isset($search_data['occupancy_children']) && $search_data['occupancy_children']}{$search_data['occupancy_children']} {if $search_data['occupancy_children'] > 1}
							{l s='Children' mod='wkroomsearchblock'}{else}{l s='Child' mod='wkroomsearchblock'}{/if}, {/if}{$search_data['occupancies']|count} {l s='Room(s)' mod='wkroomsearchblock'}{else}{l s='1 Adult, 1 Room' mod='wkroomsearchblock'}{/if}</span>
					</button>
					<div id="occupancy_wrapper" class="dropdown-menu">
						<div id="occupancy_inner_wrapper">
							{if isset($search_data['occupancies']) && $search_data['occupancies']}
								{assign var=countRoom value=1}
								{foreach from=$search_data['occupancies'] key=key item=$occupancy name=occupancyInfo}
									<div class="occupancy_info_block">
										<div class="occupancy_info_head"><span class="room_num_wrapper">{l s='Room' mod='wkroomsearchblock'} - {$countRoom|escape:'htmlall':'UTF-8'} </span>{if !$smarty.foreach.occupancyInfo.first}<a class="remove-room-link pull-right" href="#">{l s='Remove' mod='wkroomsearchblock'}</a>{/if}</div>
										<div class="row">
											<div class="form-group col-sm-5 col-xs-6 occupancy_count_block">
												<div class="row">
													<label class="col-sm-12">{l s='Adults' mod='wkroomsearchblock'}</label>
													<div class="col-sm-12">
														<input type="hidden" class="num_occupancy num_adults room_occupancies" name="occupancy[{$key|escape:'htmlall':'UTF-8'}][adults]" value="{$occupancy['adults']|escape:'htmlall':'UTF-8'}">
														<div class="occupancy_count pull-left">
															<span>{$occupancy['adults']|escape:'htmlall':'UTF-8'}</span>
														</div>
														<div class="qty_direction pull-left">
															<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_up">
																<span><i class="icon-plus"></i></span>
															</a>
															<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_down">
																<span><i class="icon-minus"></i></span>
															</a>
														</div>
													</div>
												</div>
											</div>
											<div class="form-group col-sm-7 col-xs-6 occupancy_count_block">
												<div class="row">
													<label class="col-sm-12">{l s='Child' mod='wkroomsearchblock'}<span class="label-desc-txt"> ({l s='Below' mod='wkroomsearchblock'} {$max_child_age|escape:'htmlall':'UTF-8'} {l s='years' mod='wkroomsearchblock'})</span></label>
													<div class="col-sm-12">
														<input type="hidden" class="num_occupancy num_children room_occupancies" name="occupancy[{$key|escape:'htmlall':'UTF-8'}][children]" value="{$occupancy['children']|escape:'htmlall':'UTF-8'}">
														<div class="occupancy_count pull-left">
															<span>{$occupancy['children']|escape:'htmlall':'UTF-8'}</span>
														</div>
														<div class="qty_direction pull-left">
															<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_up">
																<span><i class="icon-plus"></i></span>
															</a>
															<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_down">
																<span><i class="icon-minus"></i></span>
															</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="form-group row children_age_info_block" {if isset($search_data['occupancy_child_ages'][$key]) && $search_data['occupancy_child_ages'][$key]}style="display:block;"{/if}>
											<label class="col-sm-12">{l s='All Children' mod='wkroomsearchblock'}</label>
											<div class="col-sm-12">
												<div class="row children_ages">
													{if isset($occupancy['child_ages']) && $occupancy['child_ages']}
														{foreach $occupancy['child_ages'] as $childAge}
															<div class="col-xs-4">
																<select class="guest_child_age room_occupancies" name="occupancy[{$key|escape:'htmlall':'UTF-8'}][child_ages][]">
																	<option value="-1" {if $childAge == -1}selected{/if}>{l s='Select 1' mod='wkroomsearchblock'}</option>
																	<option value="0" {if $childAge == 0}selected{/if}>{l s='Under 1' mod='wkroomsearchblock'}</option>
																	{for $age=1 to ($max_child_age-1)}
																		<option value="{$age|escape:'htmlall':'UTF-8'}" {if $childAge == $age}selected{/if}>{$age|escape:'htmlall':'UTF-8'}</option>
																	{/for}
																</select>
															</div>
														{/foreach}
													{/if}
												</div>
											</div>
										</div>
										<hr class="occupancy-info-separator">
									</div>
									{assign var=countRoom value=$countRoom+1}
								{/foreach}
							{else}
								<div class="occupancy_info_block">
									<div class="occupancy_info_head"><span class="room_num_wrapper">{l s='Room - 1' mod='wkroomsearchblock'}</span></div>
									<div class="form-group row">
										<div class="form-group col-sm-5 col-xs-6 occupancy_count_block">
											<div class="row">
												<label class="col-sm-12">{l s='Adults' mod='wkroomsearchblock'}</label>
												<div class="col-sm-12">
													<input type="hidden" class="num_occupancy num_adults room_occupancies" name="occupancy[0][adults]" value="1">
													<div class="occupancy_count pull-left">
														<span>1</span>
													</div>
													<div class="qty_direction pull-left">
														<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_up">
															<span>
																<i class="icon-plus"></i>
															</span>
														</a>
														<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_down">
															<span>
																<i class="icon-minus"></i>
															</span>
														</a>
													</div>
												</div>
											</div>
										</div>
										<div class="form-group col-sm-7 col-xs-6 occupancy_count_block">
											<div class="row">
												<label class="col-sm-12">{l s='Child' mod='wkroomsearchblock'} <span class="label-desc-txt">({l s='Below' mod='wkroomsearchblock'}  {$max_child_age|escape:'htmlall':'UTF-8'} {l s='years' mod='wkroomsearchblock'})</span></label>
												<div class="col-sm-12">
													<input type="hidden" class="num_occupancy num_children room_occupancies" name="occupancy[0][children]" value="0">
													<div class="occupancy_count pull-left">
														<span>0</span>
													</div>
													<div class="qty_direction pull-left">
														<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_up">
															<span>
																<i class="icon-plus"></i>
															</span>
														</a>
														<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_down">
															<span>
																<i class="icon-minus"></i>
															</span>
														</a>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group row children_age_info_block">
										<label class="col-sm-12">{l s='All Children' mod='wkroomsearchblock'}</label>
										<div class="col-sm-12">
											<div class="row children_ages">
											</div>
										</div>
									</div>
									<hr class="occupancy-info-separator">
								</div>
							{/if}
						</div>
						<div id="add_new_occupancy">
							<a class="add_new_occupancy_btn" href="#"><i class="icon-plus"></i> <span>{l s='Add Room' mod='wkroomsearchblock'}</span></a>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn btn-lg btn-primary" name="search_room_submit" id="search_room_submit">
					<span>{l s='Search Rooms' mod='wkroomsearchblock'}</span> <i class="icon-arrow-right pull-right"></i>
				</button>
			</div>
		</form>
	</div>
{/if}