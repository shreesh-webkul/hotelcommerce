{if isset($service_products) && $service_products}
    <div class="col-sm-12">
        <div class="row margin-lr-0 service-products-div">
            {foreach $service_products as $product}
                <div class="col-sm-6 col-md-4">
                    <div class="product-container well">
                        <div class="product-image-container">
                            <img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'large_default')|escape:'html':'UTF-8'}" alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" title="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" itemprop="image" />
                        </div>
                        <div class="product-info-container">
                            <h4 class="">
                                <a class="product-name" href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}&amp;id_product={$product.id_product|intval}&amp;updateproduct&amp;token={getAdminToken tab='AdminProducts'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url" >
                                    {$product.name|truncate:45:'...'|escape:'html':'UTF-8'}
                                </a>
                            </h4>
                            <h2>
                                {convertPrice price=$product.price}
                            </h2>
                            {if $product.allow_multiple_quantity}
                                <div class="form-group quantity-container">
                                    <input type="number" name="product_quantity_{$product.id_product|intval}" class="product_quantity" value="1">
                                </div>
                            {/if}
                            <div class="product-actions">
                                <button type="button" data-id-product="{$product.id_product|intval}" data-id-hotel="{$id_hotel|intval}" class="btn btn-primary service_product_add_to_cart">{l s='Add To Cart' mod='hotelreservationsystem'}</button>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{else}
    <div class="col-sm-12">
        <p class="alert alert-warning">	{l s="No service products found." mod="hotelreservationsystem"}</p>
    </div>
{/if}