{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/list/list_header.tpl"}

{block name=leadin}
{if isset($updateOrderStatus_mode) && $updateOrderStatus_mode}
	<div class="panel">
		<div class="panel-heading">
			{l s='Choose an order status'}
		</div>
		<form action="{$REQUEST_URI}" method="post">
			<div class="radio">
				<label for="id_order_state">
					<select id="id_order_state" name="id_order_state">
{foreach from=$order_statuses item=order_status_name key=id_order_state}
						<option value="{$id_order_state|intval}">{$order_status_name|escape}</option>
{/foreach}
					</select>
				</label>
			</div>
{foreach $POST as $key => $value}
	{if is_array($value)}
		{foreach $value as $val}
			<input type="hidden" name="{$key|escape:'html':'UTF-8'}[]" value="{$val|escape:'html':'UTF-8'}" />
		{/foreach}
	{elseif strtolower($key) != 'id_order_state'}
			<input type="hidden" name="{$key|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}" />

	{/if}
{/foreach}
			<div class="panel-footer">
				<button type="submit" name="cancel" class="btn btn-default">
					<i class="icon-remove"></i>
					{l s='Cancel'}
				</button>
				<button type="submit" class="btn btn-default" name="submitUpdateOrderStatus">
					<i class="icon-check"></i>
					{l s='Update Order Status'}
				</button>
			</div>
		</form>
	</div>
{/if}
{/block}


{block name="tableFilter"}{/block}
{block name="override_form_extra"}
	<script>
		$(function() {
			if ($("#list_filters_panel .datepicker").length > 0) {
				$("#list_filters_panel .datepicker").datepicker({
					prevText: '',
					nextText: '',
					altFormat: 'yy-mm-dd'
				});
			}
		});
	</script>
	<div class="list_action_wrapper">
		<div class="row">
			<div class="col-sm-2">
				<div class="list_availibility_container">
					<button type="button" class="btn btn-default btn-left btn-block dropdown-toggle" data-toggle="dropdown">
						<span>{l s='Available Fields'}
						<i class="icon-caret-down pull-right"></i>
					</button>
					<ul id="optional-list-toggle" class="dropdown-menu">
						{foreach $fields_optional as $key => $field}
							<li>
								<p>
									<input type="checkbox" name="list_fields_visibility" value="{$key}" {if isset($field['selected']) && $field['selected']}checked="checked"{/if}>
									{$field['title']}
								</p>
							</li>
						{/foreach}
						{assign var="fields_optional" value=array()}
					</ul>
				</div>
			</div>
			<div class="pull-right">
				<div class="list_filter_container">
					<button type="button" class="btn btn-default btn-left btn-block" data-toggle="collapse" data-target="#list_filters_panel">
						<i class="icon-sliders"></i>
						<span>{l s='Filters'}
					</button>
				</div>
			</div>
			<div class="col-sm-12">
				{if $filters_has_value}
					<div id="selected_filter_container">
						{l s='Filters'}
					</div>
				{/if}
				<div class="panel collapse" id="list_filters_panel">
					{if !$simple_header && $show_filters}
						<div class="row">
							<div class="col-sm-12">
								<div class="list_filters">
									{foreach $fields_display AS $key => $params}
										{if !isset($params.search) || $params.search}
											<div class="">
												<div class="row">
													<label class="col-sm-3" for="filter_input_{$key}">{$params['title']|escape:'html':'UTF-8'}</label>
													<div class="col-sm-9">
														{if $params.type == 'bool'}
															<select id="filter_input_{$key}" class="filter fixed-width-sm center" name="{$list_id}Filter_{if isset($params.filter_key)}{$params.filter_key}{else}{$key}{/if}">
																<option value="">-</option>
																<option value="1" {if $params.value == 1} selected="selected" {/if}>{l s='Yes'}</option>
																<option value="0" {if $params.value == 0 && $params.value != ''} selected="selected" {/if}>{l s='No'}</option>
															</select>
														{elseif $params.type == 'date' || $params.type == 'datetime'}
															<div class="date_range">
																<div class="input-group fixed-width-md center">
																	<input type="text" class="filter datepicker date-input form-control" id="local_{$params.id_date}_0" name="local_{$params.name_date}[0]"  placeholder="{l s='From'}" autocomplete="off"/>
																	<input type="hidden" id="{$params.id_date}_0" name="{$params.name_date}[0]" value="{if isset($params.value.0)}{$params.value.0}{/if}">
																	<span class="input-group-addon">
																		<i class="icon-calendar"></i>
																	</span>
																</div>
																<div class="input-group fixed-width-md center">
																	<input type="text" class="filter datepicker date-input form-control" id="local_{$params.id_date}_1" name="local_{$params.name_date}[1]"  placeholder="{l s='To'}"  autocomplete="off"/>
																	<input type="hidden" id="{$params.id_date}_1" name="{$params.name_date}[1]" value="{if isset($params.value.1)}{$params.value.1}{/if}">
																	<span class="input-group-addon">
																		<i class="icon-calendar"></i>
																	</span>
																</div>
																<script>
																	$(function() {
																		var dateStart = parseDate($("#{$params.id_date}_0").val());
																		var dateEnd = parseDate($("#{$params.id_date}_1").val());
																		$("#local_{$params.id_date}_0").datepicker("option", "altField", "#{$params.id_date}_0");
																		$("#local_{$params.id_date}_1").datepicker("option", "altField", "#{$params.id_date}_1");
																		if (dateStart !== null){
																			$("#local_{$params.id_date}_0").datepicker("setDate", dateStart);
																		}
																		if (dateEnd !== null){
																			$("#local_{$params.id_date}_1").datepicker("setDate", dateEnd);
																		}
																	});
																</script>
															</div>
														{elseif $params.type == 'select'}
															{if isset($params.filter_key)}
																<select id="filter_input_{$key}" class="filter{if isset($params.align) && $params.align == 'center'}center{/if}" onchange="$('#submitFilterButton{$list_id}').focus();$('#submitFilterButton{$list_id}').click();" name="{$list_id}Filter_{$params.filter_key}" {if isset($params.width)} style="width:{$params.width}px"{/if}>
																	<option value="" {if $params.value == ''} selected="selected" {/if}>-</option>
																	{if isset($params.list) && is_array($params.list)}
																		{foreach $params.list AS $option_value => $option_display}
																			<option value="{$option_value}" {if (string)$option_display === (string)$params.value ||  (string)$option_value === (string)$params.value} selected="selected"{/if}>{$option_display}</option>
																		{/foreach}
																	{/if}
																</select>
															{/if}
														{else}
															<input type="text" class="filter" name="{$list_id}Filter_{if isset($params.filter_key)}{$params.filter_key}{else}{$key}{/if}" value="{$params.value|escape:'html':'UTF-8'}" {if isset($params.width) && $params.width != 'auto'} style="width:{$params.width}px"{/if} />
														{/if}
													</div>
												</div>
											</div>
										{/if}
									{/foreach}
									{if $has_actions || $show_filters}
										<div class="actions">
											<hr>
											{if $show_filters}
											<span class="pull-right">
												<button type="submit" id="submitFilterButton{$list_id}" name="submitFilter" class="btn btn-default" data-list-id="{$list_id}">
													<i class="icon-search"></i> {l s='Search'}
												</button>
												{if $filters_has_value}
													<button type="submit" name="submitReset{$list_id}" class="btn btn-warning">
														<i class="icon-eraser"></i> {l s='Reset'}
													</button>
												{/if}
											</span>
											{/if}
										</div>
									{/if}
								</div>
							</div>
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
	<style>
		#selected_filter_container {
			background-color: #fff;
			padding: 4px 20px;
			border: solid 1px #d3d8db;
			border-radius: 5px 5px 0 0;
			font-size: 14px;
			margin-bottom: 15px;
		}
		/* #content.bootstrap #list_filters_panel.in + #selected_filter_container {
			border-radius: 5px 5px 0 0;
			margin-bottom: 0;
		} */
		#content.bootstrap  #selected_filter_container + div.panel {
			border-top: transparent;
   			border-radius: 0 0 5px 5px;
		}
		.list_availibility_container {
			padding-bottom: 15px;
			position: relative;}
			.list_availibility_container .dropdown-menu {
				right: 0;
				top: calc(100% - 15px);
				padding: 5px 12px;
			}
		.list_filters {
			padding: 0 30px;
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 8px 40px;}
			.list_filters .date_range {
				display: flex;
				justify-content: space-between;}
			.list_filters .date_range .input-group:first-child {
				margin-bottom: 0;}
			.list_filters label {
				text-align: right;
				font-weight: 400;}
			.list_filters .actions {
				grid-column-start: 1;
				grid-column-end: 4;}
	</style>
{/block}


