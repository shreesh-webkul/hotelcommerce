{foreach $standard_products as $product}
    {include file="{$tpl_dir}_partials/standard-products-list-row.tpl" product=$product}
    <hr>
{/foreach}