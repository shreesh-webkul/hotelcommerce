{if isset($product) && $product}
    <li class="row standard-product-element">
        <div class="col-xs-4 col-sm-3 col-md-2">
            <a href="{$link->getImageLink($product.link_rewrite, $product.id_image, 'large_default')|escape:'html':'UTF-8'}" data-fancybox-group="standard-products-{$group}" class="fancybox" title="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}">
                <img class="img-responsive standard-product-img" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" title="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}">
            </a>
        </div>
        <div class="col-xs-8 col-sm-9 col-md-10">
            <div class="row">
                <div class="col-sm-12 col-md-8 col-lg-9 clearfix">
                    <p class="standard-product-name">{$product['name']}</p>
                    {if $product['description_short']}
                        <div class="standard-product-short-desc">
                            {$product['description_short']}
                        </div>
                    {/if}
                    {if $product.allow_multiple_quantity && $product.available_for_order}
                        <div class="qty_container">
                            <input type="text" class="standard_product_qty" id="standard_product_qty_{$product.id_product}" name="standard_product_qty_{$product.id_product}" data-id-product="{$product.id_product}" value="{if isset($product.quantity_added) && $product.quantity_added}{$product.quantity_added|escape:'html':'UTF-8'}{else}1{/if}">
                            <div class="qty_controls">
                                <a href="#" class="standard_product_qty_up"><span><i class="icon-plus"></i></span></a>
                                <a href="#" class="standard_product_qty_down"><span><i class="icon-minus"></i></span></a>
                            </div>
                        </div>
                    {/if}
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 text-right product_actions">
                    {if ($product.show_price && !isset($restricted_country_mode)) || isset($groups)}
                        <p class="standard-product-price">
                            {if !$priceDisplay}{convertPrice price=$product.price_tax_incl}{else}{convertPrice price=$product.price_tax_exc}{/if}
                        </p>
                        {if $product.available_for_order && !$PS_CATALOG_MODE && !$order_date_restrict && !((isset($restricted_country_mode) && $restricted_country_mode))}
                            <button class="btn btn-standard-product{if isset($product.selected) && $product.selected} btn-danger remove_roomtype_product{else} btn-success add_product_to_roomtype{/if}" data-id-product="{$product.id_product}">{if isset($product.selected) && $product.selected}{l s='Remove'}{else}{l s='Select'}{/if}</button>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>
    </li>
{/if}