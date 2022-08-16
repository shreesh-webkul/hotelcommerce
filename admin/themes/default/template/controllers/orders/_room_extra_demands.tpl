<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
	<ul class="nav nav-tabs" role="tablist">
		{if (isset($extraDemands) && $extraDemands) || (isset($roomTypeDemands) && $roomTypeDemands)}
			<li role="presentation" class="active"><a href="#room_type_demands_desc" aria-controls="facilities" role="tab" data-toggle="tab">{l s='Additional Facilities'}</a></li>
		{/if}
		{if (isset($additionalServices) && $additionalServices) || (isset($roomTypeStandardProducts) && $roomTypeStandardProducts)}
			<li role="presentation" {if !(isset($extraDemands) && $extraDemands) && !(isset($roomTypeDemands) && $roomTypeDemands)}class="active"{/if}><a href="#room_type_standard_product_desc" aria-controls="services" role="tab" data-toggle="tab">{l s='Additional Services'}</a></li>
		{/if}
	</ul>
	{* <h3 class="modal-title"><i class="icon-tasks"></i> {l s='Additional Facilities'} {if isset($orderEdit) && $orderEdit}<span class="badge badge-success demand_edit_badge">{l s='edit'}</span>{/if} <button type="button" class="close pull-right" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	</h3> *}
</div>
<div class="modal-body" id="rooms_extra_demands">
	<div class="tab-content clearfix">
		{if (isset($extraDemands) && $extraDemands) || (isset($roomTypeDemands) && $roomTypeDemands)}
			<div id="room_type_demands_desc" class="tab-pane active">
				<input type="hidden" value="0" id="id_htl_booking">
				{if isset($orderEdit) && $orderEdit}
					<p class="col-sm-12 facility_nav_btn">
						<button id="btn_new_room_demand" class="btn btn-success"><i class="icon-plus"></i> {l s='Add new facility'}</button>
						<button id="back_to_demands_btn" class="btn btn-default"><i class="icon-arrow-left"></i> {l s='Back'}</button>
					</p>

					{* Already selected room demands *}
					<div class="col-sm-12 room_ordered_demands table-responsive">
						<table class="table">
							<tbody>
								{if isset($extraDemands) && $extraDemands}
									{foreach $extraDemands as $roomDemand}
										{foreach $roomDemand['extra_demands'] as $demand}
											<tr>
												<td>{$demand['name']}</td>
												<td>{displayPrice price=$demand['total_price_tax_incl'] currency=$orderCurrency}</td>
												<td><a class="btn btn-danger pull-right del-order-room-demand" href="#" id_booking_demand="{$demand['id_booking_demand']}"><i class="icon-trash"></i></a></td>
											</tr>
										{/foreach}
									{/foreach}
								{else}
									<tr>
										<td colspan="3">
											<i class="icon-warning"></i> {l s='No additional facilities added yet.'}
										</td>
									</tr>
								{/if}
							</tbody>
						</table>
					</div>

					{* Room demands available for the current editing room*}
					<div class="col-sm-12 room_demands_container">
						<div class="room_demand_detail">
							{if isset($roomTypeDemands) && $roomTypeDemands}
								{foreach $roomTypeDemands as $idGlobalDemand => $demand}
									<div class="row room_demand_block">
										<div class="col-xs-6">
											<div class="row">
												<div class="col-xs-2">
													<input id_cart_booking="{$roomDemand['id']}" value="{$idGlobalDemand|escape:'html':'UTF-8'}" type="checkbox" class="id_room_type_demand" {if  isset($roomDemand['selected_global_demands']) && $roomDemand['selected_global_demands'] && ($idGlobalDemand|in_array:$roomDemand['selected_global_demands'])}checked{/if} />
												</div>
												<div class="col-xs-10 demand_adv_option_block">
													<p>{$demand['name']|escape:'html':'UTF-8'}</p>
													{if isset($demand['adv_option']) && $demand['adv_option']}
														<select class="id_option">
															{foreach $demand['adv_option'] as $idOption => $option}
																{assign var=demand_key value="`$idGlobalDemand`-`$idOption`"}
																<option optionPrice="{$option['price']|escape:'html':'UTF-8'}" value="{$idOption|escape:'html':'UTF-8'}" {if isset($roomDemand['extra_demands'][$demand_key])}selected{/if} key="{$demand_key}">{$option['name']}</option>
																{if isset($roomDemand['extra_demands'][$demand_key])}
																	{assign var=selected_adv_option value="$idOption"}
																{/if}
															{/foreach}
														</select>
													{else}
														<input type="hidden" class="id_option" value="0" />
													{/if}
												</div>
											</div>
										</div>
										<div class="col-xs-6">
											<p><span class="pull-right extra_demand_option_price">{if isset($selected_adv_option) && isset($demand['adv_option'][$selected_adv_option]['price'])}{convertPrice price = $demand['adv_option'][$selected_adv_option]['price']|escape:'html':'UTF-8'}{else}{convertPrice price = $demand['price']|escape:'html':'UTF-8'}{/if}</span></p>
										</div>
									</div>
								{/foreach}
							{/if}
						</div>
						<button type="button" id="save_room_demands" class="btn btn-success pull-right"><i class="icon-save"></i> {l s='Save'}</button>
					</div>
				{elseif isset($extraDemands) && $extraDemands}
					{foreach $extraDemands as $roomDemand}
						<div class="row room_demand_detail">
							{foreach $roomDemand['extra_demands'] as $demand}
								<div class="col-sm-12 room_demand_block">
									<p>
										<span>{$demand['name']}</span>
										<span class="pull-right">{displayPrice price=$demand['total_price_tax_incl'] currency=$orderCurrency}</span>
									</p>
								</div>
							{/foreach}
						</div>
					{/foreach}
				{/if}
			</div>
		{/if}
		{if (isset($additionalServices) && $additionalServices) || (isset($roomTypeStandardProducts) && $roomTypeStandardProducts)}
			<div id="room_type_standard_product_desc" class="tab-pane{if !(isset($extraDemands) && $extraDemands) && !(isset($roomTypeDemands) && $roomTypeDemands)} active{/if}">
				{if isset($orderEdit) && $orderEdit}

					<p class="col-sm-12 facility_nav_btn">
						<button id="btn_new_room_service" class="btn btn-success"><i class="icon-plus"></i> {l s='Add new service'}</button>
						<button id="back_to_service_btn" class="btn btn-default"><i class="icon-arrow-left"></i> {l s='Back'}</button>
					</p>

					{* Already selected room services *}
					<div class="col-sm-12 room_ordered_services table-responsive">
						<table class="table">
							<tbody>
								{if isset($additionalServices) && $additionalServices}
									{foreach $additionalServices['additional_services'] as $service}
										<tr class="room_demand_block">
											<td>
												<div>{$service['name']|escape:'html':'UTF-8'}</div>
													{if $service['allow_multiple_quantity']}
														<div class="qty_container">
															<input type="text" class="form-control qty" data-id_standard_product_order_detail="{$service['id_standard_product_order_detail']}" data-id_product="{$service['id_product']|escape:'html':'UTF-8'}" value="{$service['quantity']|escape:'html':'UTF-8'}">
															<div class="qty_controls">
																<a href="#" class="qty_up"><span><i class="icon-plus"></i></span></a>
																<a href="#" class="qty_down"><span><i class="icon-minus"></i></span></a>
															</div>
														</div>
													{/if}
											</td>
											<td>{displayPrice price=$demand['total_price_tax_incl']|escape:'html':'UTF-8' currency=$orderCurrency}</td>
											<td><a class="btn btn-danger pull-right del_room_additional_service" data-id_standard_product_order_detail="{$service['id_standard_product_order_detail']}" href="#"><i class="icon-trash"></i></a></td>
										</tr>
									{/foreach}
								{else}
									<tr>
										<td colspan="3">
											<i class="icon-warning"></i> {l s='No additional facilities added yet.'}
										</td>
									</tr>
								{/if}
							</tbody>
						</table>
					</div>

					<form id="add_room_services_form" class="col-sm-12 room_services_container">
						<div class="room_demand_detail">
							{if isset($roomTypeStandardProducts) && $roomTypeStandardProducts}
								{foreach $roomTypeStandardProducts as $product}
									<div class="row room_demand_block">
										<div class="col-xs-6">
											<div class="row">
												<div class="col-xs-2">
													<input data-id_booking_detail="{$additionalServices['id_htl_booking_detail']}" value="{$product['id_product']|escape:'html':'UTF-8'}" name="selected_service[]" type="checkbox" class="id_room_type_service"/>
												</div>
												<div class="col-xs-10">
													<p>{$product['name']|escape:'html':'UTF-8'}</p>
													{if $product.allow_multiple_quantity}
														<div class="qty_container">
															<input type="text" class="form-control qty" id="qty_{$product['id_product']|escape:'html':'UTF-8'}" name="service_qty[{$product['id_product']|escape:'html':'UTF-8'}]" data-id-product="{$product.id_product|escape:'html':'UTF-8'}" value="1">
															<div class="qty_controls">
																<a href="#" class="qty_up"><span><i class="icon-plus"></i></span></a>
																<a href="#" class="qty_down"><span><i class="icon-minus"></i></span></a>
															</div>
														</div>
													{/if}
												</div>
											</div>
										</div>
										<div class="col-xs-6">
											<p>
												<span class="pull-right">{convertPrice price=$product.price_tax_incl}</span>
											</p>
										</div>
									</div>
								{/foreach}
							{/if}
						</div>
						<input type="hidden" name="id_booking_detail" value="{$id_booking_detail}">
						<button type="submit" id="save_service_service" class="btn btn-success pull-right"><i class="icon-save"></i> {l s='Save'}</button>
					</form>

				{elseif isset($additionalServices) && $additionalServices}
					<div class="row room_demand_detail">
						{foreach $additionalServices['additional_services'] as $service}
							<div class="col-sm-12 room_demand_block">
								<div class="row">
									<div class="col-xs-8">
										<div>{$service['name']|escape:'html':'UTF-8'}</div>
										{if $service['allow_multiple_quantity']}
											<div class="quantity">{l s='Qty:'}&nbsp;{$service['quantity']|escape:'html':'UTF-8'}</div>
										{/if}
									</div>
									<div class="col-xs-4">
										<span class="pull-right">{displayPrice price=$service['total_price_tax_incl'] currency=$orderCurrency}</span>
									</div>
								</div>
							</div>
						{/foreach}
					</div>
				{/if}
			</div>
		{/if}
	</div>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-secondary pull-left" data-dismiss="modal">{l s='Close'}</button>
</div>



{* Css for handling extra demands changes *}
<style type="text/css">
	/*Extra demands CSS*/
	#room_extra_demand_content .modal-header {
		padding-bottom: 0px;}
	#rooms_type_extra_demands .modal-title {
		margin: 0px;}
	#rooms_type_extra_demands .demand_edit_badge {
		font-size: 14px;}
	#rooms_extra_demands .room_ordered_demands td, #rooms_extra_demands .room_demand_detail {
		font-size: 14px;}
	#rooms_extra_demands .demand_header {
		padding: 10px;
		color: #333;
    	border-bottom: 1px solid #ddd;}
	#rooms_extra_demands .room_demand_block {
		margin-bottom: 15px;
		color: #333;
		font-size: 14px;}
	#rooms_extra_demands .facility_nav_btn {
		margin-bottom: 20px;}
	#rooms_extra_demands .room_demands_container, #rooms_extra_demands .room_services_container {
		display: none;}
	#room_extra_demand_content #save_room_demands, #room_extra_demand_content #back_to_demands_btn, #room_extra_demand_content #save_service_service, #room_extra_demand_content #back_to_service_btn {
		display: none;}
</style>

