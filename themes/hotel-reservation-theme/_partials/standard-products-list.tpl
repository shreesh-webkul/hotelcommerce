{foreach $standard_products as $product}
    {if !($product@first && isset($init) && $init == true)}
        <hr>
    {/if}
    {include file="{$tpl_dir}_partials/standard-products-list-row.tpl" product=$product}
{/foreach}