{if isset($product->id)}
    <div id="product-configuration" class="panel product-tab">
		<input type="hidden" name="submitted_tabs[]" value="ServiceProduct" />
		<h3 class="tab"> <i class="icon-AdminAdmin"></i> {l s='Service Products'}</h3>

        <div class="from-group table-responsive-row clearfix">
			<table class="table hotel-roomtype-link-table">
				<thead>
                    <tr class="nodrag nodrop">
                        <th class="col-sm-1">
                            <span>{l s='Id Product'}</span>
                        </th>
                        <th class="col-sm-3">
							<span>{l s='name'}</span>
						</th>
                        <th class="">
                            <span>{l s='Category'}</span>
                        </th>
						<th class="">
							<span>{l s='Price'}</span>
                        </th>
                        <th class="">
                            <span>{l s='Tax'}</span>
                        </th>
                        <th class="text-right">
                            <span>{l s='Action'}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {if isset($service_products) && $service_products}
						{foreach from=$service_products key=key item=product}
                            <tr>
                                <td class="col-sm-1">{$product.id_product|escape:'html':'UTF-8'} <a target="blank" href="{$link->getAdminLink('AdminNormalProducts')|escape:'html':'UTF-8'}&amp;id_product={$product.id_product|escape:'html':'UTF-8'}&amp;updateproduct"><i class="icon-external-link-sign"></i></a></td>
                                <td class="">{$product.name}</td>
                                <td class="">{$product.category}</td>
                                <td class="">
                                    <span class="field-view service-product-price-text">{if isset($product.custom_price) && $product.custom_price}{displayPrice price=$product.custom_price currency=$id_currency}{else}{displayPrice price=$product.default_price currency=$id_currency}{/if}</span>
                                    <div class="field-edit" style="display:none">
                                        <div class="input-group">
                                            <input type="text" value="{if isset($product.custom_price) && $product.custom_price}{$product.custom_price|escape:'html':'UTF-8'}{else}{$product.default_price|escape:'html':'UTF-8'}{/if}" class="service-product-price" data-id_product="{$product.id_product|escape:'html':'UTF-8'}">
                                            <span class="input-group-addon">{$currency->prefix}{$currency->suffix}</span>
                                        </div>
                                    </div>
                                    <div class="help-block">{l s='Default price: %s' sprintf={displayPrice price=$product.default_price currency=$id_currency}}
                                </td>
                                <td>
                                    <span class="field-view service_product_tax_text">{if isset($product.tax_rules_group_name) && $product.tax_rules_group_name}{$product.tax_rules_group_name}{else}{$product.default_tax_rules_group_name}{/if}</span>
                                    <div class="field-edit" style="display:none">
                                        <select class="service_product_id_tax_rules_group"{if $tax_exclude_taxe_option}disabled="disabled"{/if}>
                                            <option value="0">{l s='No Tax'}</option>
                                            {foreach from=$tax_rules_groups item=tax_rules_group}
                                                <option value="{$tax_rules_group.id_tax_rules_group}" {if $product.id_tax_rules_group == $tax_rules_group.id_tax_rules_group}selected="selected"{/if} >
                                                    {$tax_rules_group['name']|htmlentitiesUTF8}
                                                </option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="help-block">{l s='Default tax rule: %s' sprintf=$product.default_tax_rules_group_name}
                                </td>
                                <td class="text-right">
                                    <a href="#" class="btn btn-default button-edit-price field-view"><i class="icon-pencil"></i></a>
                                    <span class="field-edit" style="display:none">
                                        <a href="#" class="btn btn-default btn-save" data-roomtype_url="{$link->getAdminLink('AdminProducts', true)|addslashes}" data-id_product="{$product.id_product|escape:'html':'UTF-8'}" data-id_room_type_service_product_price="{$product.id_room_type_service_product_price|escape:'html':'UTF-8'}"><i class="icon-save"></i> {l s='save'}</a>
                                        <a href="#" class="btn btn-default btn-cancel"><i class="icon-times"></i></a>
                                    </span>
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
{/if}