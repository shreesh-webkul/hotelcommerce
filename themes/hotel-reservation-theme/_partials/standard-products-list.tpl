{foreach $standard_products as $product}
    {if !($product@first && $p == 1)}
        <hr>
    {/if}
    {include file="{$tpl_dir}_partials/standard-products-list-row.tpl" product=$product}
{/foreach}