<section>
	{if (isset($extraDemands) && $extraDemands) || (isset($additionalServices) && $additionalServices)}
		<ul class="nav nav-tabs">
			{if isset($extraDemands) && $extraDemands}
				<li class="active"><a href="#room_type_demands_desc" data-toggle="tab">{l s='Facilities'}</a></li>
			{/if}
			{if isset($additionalServices) && $additionalServices}
				<li{if !isset($extraDemands) || !$extraDemands} class="active"{/if}><a href="#room_type_standard_product_desc" data-toggle="tab">{l s='Products'}</a></li>
			{/if}
		</ul>
		<div class="tab-content">
			{if isset($extraDemands) && $extraDemands}
				<div id="room_type_demands_desc" class="tab-pane active">
					<div class="rooms_extra_demands_head">
						<p class="rooms_extra_demands_text">{l s='Below are the facilities chosen by you in this booking'}</p>
					</div>
					{assign var=roomCount value=1}
					{foreach $extraDemands as $roomDemand}
						<div class="card">
							<div class="row">
								<div class="col-sm-12 demand_header">
									{l s='Room'} {$roomCount|escape:'html':'UTF-8'}
								</div>
								<div class="col-sm-12 room_demand_detail">
									{foreach $roomDemand['extra_demands'] as $demand}
										<div class="row room_demand_block">
											<div class="col-xs-6">{$demand['name']|escape:'html':'UTF-8'}</div>
											<div class="col-xs-6">
												<span class="pull-right">
													{if $useTax}
														{displayPrice price="{$demand['total_price_tax_incl']|escape:'html':'UTF-8'}"}
													{else}
														{displayPrice price="{$demand['total_price_tax_excl']|escape:'html':'UTF-8'}"}
													{/if}
												</span>
											</div>
										</div>
									{/foreach}
								</div>
							</div>
						</div>
						{assign var=roomCount value=$roomCount+1}
					{/foreach}
				</div>
			{/if}

			{if isset($additionalServices) && $additionalServices}
				<div id="room_type_standard_product_desc" class="tab-pane{if !isset($extraDemands) || !$extraDemands} active{/if}">
					<div class="rooms_extra_demands_head">
						<p class="rooms_extra_demands_text">{l s='Below are the products chosen by you in this booking'}</p>
					</div>
					{assign var=roomCount value=1}
					{foreach $additionalServices as $key => $roomAdditionalService}
						<div class="card">
							<div class="row">
								<div class="col-sm-12 demand_header">
									{l s='Room'} {$roomCount|escape:'html':'UTF-8'}
								</div>
								<div class="col-sm-12 room_demand_detail">
									{foreach $roomAdditionalService['additional_services'] as $additionalService}
										<div class="row room_demand_block">
											<div class="col-xs-6">
												<div>{$additionalService['name']|escape:'html':'UTF-8'}</div>
												{if $additionalService['allow_multiple_quantity']}
													<div class="quantity">{l s='Qty:'}&nbsp;{$additionalService['quantity']|escape:'html':'UTF-8'}</div>
												{/if}
											</div>

											<div class="col-xs-6">
												<span class="pull-right">
													{if $useTax}
														{displayPrice price=$additionalService['total_price_tax_incl']|escape:'html':'UTF-8'}
													{else}
														{displayPrice price=$additionalService['total_price_tax_excl']|escape:'html':'UTF-8'}
													{/if}
												</span>
											</div>
										</div>
									{/foreach}
								</div>
							</div>
						</div>
					{/foreach}
				</div>
			{/if}
		</div>
	{/if}
</section>