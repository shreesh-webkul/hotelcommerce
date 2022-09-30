
{if isset($standard_products_exists) && $standard_products_exists}
    <ul class="nav nav-tabs product_description_tabs">
        {if $PS_STANDARD_PRODUCT_DISPLAY_TYPE == 'list' || ($PS_STANDARD_PRODUCT_DISPLAY_TYPE == 'slider' && !$PS_SHOW_STANDARD_PRODUCT_CATEGORY_FILTER)}
            <li class="active"><a href="#all_products" class="idTabHrefShort" data-toggle="tab">{l s='Products'}</a></li>
        {else}
            {foreach $standard_products_categories as $category}
                <li {if $category@iteration == 1}class="active"{/if}><a class="idTabHrefShort" href="#category_{$category['id_category']}" data-toggle="tab">{$category['name']}</a></li>
            {/foreach}
        {/if}
    </ul>
    <div class="card">
        <div class="row">
            {if $PS_STANDARD_PRODUCT_DISPLAY_TYPE == 'list'}
                {if $PS_SHOW_STANDARD_PRODUCT_CATEGORY_FILTER}
                    <div class="col-md-2">
                        <ul class="nav nav-pills nav-stacked standard-product-nav">
                            {foreach $standard_products_categories as $category}
                                <li {if $category@iteration == 1}class="active"{/if}><a href="#category_{$category['id_category']}" data-toggle="tab">{$category['name']}</a></li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                <div class="col-md-{if $PS_SHOW_STANDARD_PRODUCT_CATEGORY_FILTER}10 tab-content{else}12{/if}">
                    {if $PS_SHOW_STANDARD_PRODUCT_CATEGORY_FILTER}
                        {foreach $standard_products_by_category as $standard_product_category}
                            <div class="tab-pane {if $standard_product_category@iteration == 1}active{/if}" id="category_{$standard_product_category['id_category']}">
                                <ul class="product-list">
                                    {include file="{$tpl_dir}_partials/standard-products-list.tpl" standard_products=$standard_product_category.products group=$standard_product_category['id_category']}
                                </ul>
                                {if HotelRoomTypeStandardProduct::WK_NUM_RESULTS < $standard_product_category.num_products}
                                    <div class="show_more_btn_container">
                                        <button class="btn btn-default get-standard-products" data-id_category="{$standard_product_category['id_category']}" data-page="2" data-num_total="{$standard_product_category.num_products}">{l s='Show More'}</button>
                                    </div>
                                {/if}
                            </div>
                        {/foreach}
                    {else}
                        <ul class="product-list">
                            {include file="{$tpl_dir}_partials/standard-products-list.tpl" standard_products=$standard_products group='all'}
                        </ul>
                        {if HotelRoomTypeStandardProduct::WK_NUM_RESULTS < $num_total_standard_products}
                            <div class="show_more_btn_container">
                                <button class="btn btn-default get-standard-products" data-page="2" data-num_total="{$num_total_standard_products}">{l s='Show More'}</button>
                            </div>
                        {/if}
                    {/if}
                </div>
            {else}
                <div class="col-sm-12 {if $PS_SHOW_STANDARD_PRODUCT_CATEGORY_FILTER}tab-content{/if}">
                    {if $PS_SHOW_STANDARD_PRODUCT_CATEGORY_FILTER}
                        {foreach $standard_products_by_category as $standard_product_category}
                            <div class="tab-pane row {if $standard_product_category@iteration == 1}active{/if}" id="category_{$standard_product_category['id_category']}">
                                <ul class="standard-products-slider">
                                    {foreach $standard_product_category.products as $product}
                                        <li class="col-xs-4">
                                            {include file="{$tpl_dir}_partials/standard-products-card.tpl" product=$product group=$standard_product_category@iteration}
                                        </li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/foreach}
                    {else}
                        <div class="row" id="all_products">
                            <ul class="standard-products-slider">
                                {foreach $standard_products as $product}
                                    <li class="col-xs-4">
                                        {include file="{$tpl_dir}_partials/standard-products-card.tpl" product=$product group='all'}
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                </div>
            {/if}

        </div>
    </div>
{/if}