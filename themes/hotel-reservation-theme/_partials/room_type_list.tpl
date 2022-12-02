{if isset($booking_data['rm_data']) && $booking_data['rm_data']}
	{foreach from=$booking_data['rm_data'] key=room_k item=room_v}
		<div class="col-sm-12 room_cont" data-id-product="{$room_v['id_product']}">
			<div class="row">
				<div class="col-sm-4">
					<a href="{$room_v['product_link']}">
						<img src="{$room_v['image']}" class="img-responsive">
						{hook h='displayRoomTypeListImageAfter' product=$room_v}
					</a>
				</div>
				<div class="col-sm-8">
					<p class="rm_heading">{$room_v['name']}</p>
					<div class="rm_desc">{$room_v['description']}&nbsp;<a href="{$room_v['product_link']}">{l s='View More'}....</a></div>

					<div class="pull-left">

						<p><span class="capa_txt">{l s='Max Capacity:'}</span><span class="capa_data"> {$room_v['adult']} {l s='Adults'}, {$room_v['children']} {l s='child'}</span></p>
						{if isset($room_v['num_review'])}
							<div class="rm_review_cont">
								{for $foo=1 to 5}
									{if $foo <= $room_v['ratting']}
										<div class="rm_ratting_yes" style="background-image:url({$ratting_img});"></div>
									{else}
										<div class="rm_ratting_no" style="background-image:url({$ratting_img});"></div>
									{/if}
								{/for}
								<span class="rm_review">{$room_v['num_review']} {l s='Reviews'}</span>
							</div>
						{/if}
					</div>
					<div class="pull-right">
						{if !isset($restricted_country_mode) && !$PS_CATALOG_MODE && !$order_date_restrict}
							<span class="rm_left" {if $room_v['room_left']>$warning_num}  style="display:none"{/if}>{l s='Hurry!'} <span class="remain_rm_qty">{$room_v['room_left']}</span> {l s='rooms left'}</span>
						{/if}
						<div class="row margin-lr-0 rm_price_cont">
							{if $room_v['feature_price_diff'] >= 0}
								<span class="pull-left rm_price_val {if $room_v['feature_price_diff']>0}room_type_old_price{/if}">
									{displayPrice price = $room_v['price_without_reduction']|round:2|floatVal}
								</span>
							{/if}
							{if $room_v['feature_price_diff']}
								<span class="pull-left rm_price_val">
									{displayPrice price = $room_v['feature_price']|round:2|floatVal}
								</span>
							{/if}
							<span class="pull-left rm_price_txt">/{l s='Per Night'}</span>
						</div>
					</div>
					{if !empty($room_v['feature'])}
						<div class="rm_amenities_cont">
							{foreach from=$room_v['feature'] key=feat_k item=feat_v}
								<img title="{$feat_v.name}" src="{$link->getMediaLink("`$feat_img_dir`{$feat_v.value}")}" class="rm_amen">  {* by webkul change meddia link*}
								{* <img src="{$feat_img_dir}{$feat_v['value']}" class="rm_amen"> *}
							{/foreach}
						</div>
					{/if}


					{if !isset($restricted_country_mode) && !$PS_CATALOG_MODE && !$order_date_restrict}
						<div class="booking_room_fields">
						<!-- ################################################ -->
    						{if isset($occupancy_wise_search) && $occupancy_wise_search}
								<div class="booking_guest_occupancy_conatiner">
									{include file="./occupancy_field.tpl" room_type_info=$room_v total_available_rooms=$room_v['room_left']}
								</div>
							{else}
								<div>
									<label>{l s='Qty:'}</label>
								</div>
								{include file="./quantity_field.tpl" total_available_rooms=$room_v['room_left']}
							{/if}
							<a cat_rm_check_in="{$booking_date_from}" cat_rm_check_out="{$booking_date_to}" href="" rm_product_id="{$room_v['id_product']}" cat_rm_book_nm_days="{$num_days}" data-id-product-attribute="0" data-id-product="{$room_v['id_product']|intval}" class="btn btn-default button button-medium ajax_add_to_cart_button pull-right"><span>{l s='Book Now'}</span></a>
						</div>
					{/if}
					<!-- ################################################ -->
				</div>
			</div>
		</div>
	{/foreach}
{else}
	<div class="noRoomsAvailAlert">
		<span>{l s='No room available for this hotel!'}</span>
	</div>
{/if}
