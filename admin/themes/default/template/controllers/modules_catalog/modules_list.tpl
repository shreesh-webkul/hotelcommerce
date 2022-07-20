<style>
    .suggested-elements {
        margin-left: -15px;
        margin-right: -15px;
    }
    .suggested-elements .module-panel {
        overflow: hidden;
        height: 235px; }
        .suggested-elements .module-panel > .panel {
            margin: 0 10px;
            height: calc(100% - 30px);}
            .suggested-elements .module-panel > .panel > .module-info-wrapper {
                position: relative;
                height: 100%;}
                .suggested-elements .module-panel > .panel > .module-info-wrapper .module-logo {
                    }
                    .suggested-elements .module-panel > .panel > .module-info-wrapper .module-logo img {
                        max-height: 60px;
                        margin-bottom: 20px;
                        margin-right: 10px;
                    }
        .suggested-elements .module-panel > .panel .panel-action {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;}
            .suggested-elements .module-panel > .panel .panel-action .module-price {
                font-size: 14px;
                font-weight: 700;
            }

</style>
<div class="suggested-elements row">
    {foreach $elements as $element}
        <div class="col-sm-6 col-md-4 module-panel">
            <div class="panel">
                <div class="module-info-wrapper">
                    <div class="module-logo clearfix">
                        <img src="{if isset($element->image)}{$element->image}{else}{$modules_uri}/{$element->name}/{$element->logo}{/if}" title="{$element->displayName}" class="pull-left">
                        <h4 class="module-name" data-module="{$element->name}">{$element->displayName}</h4>
                        <p class="text-muted">{$element->version} {l s='By'} {$element->author}</p>
                    </div>
                    <p>{if $element->description_full}{$element->description_full|truncate:90:"..."}{else}{$element->description|truncate:90:"..."}{/if}<a href="{if $element->element_type == $element_type_module}{$currentIndex|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}&amp;ajax=1&amp;action=GetModuleQuickView&amp;module={$element->name|urlencode}{else}{$element->addons_buy_url|replace:' ':'+'|escape:'html':'UTF-8'}{/if}" class="fancybox-quick-view pull-right">{l s='Read more'}</a>
                    </p>
                    <div class="panel-action">
                        {if isset($element->type) && $element->type == 'addonsMustHave'}
                            <span class="module-price">{if isset($element->price)}{if $element->price|floatval == 0}{l s='Free'}{elseif isset($element->id_currency)}{displayPrice price=$element->price currency=$element->id_currency}{/if}{/if}</span>
                            <a class="btn button-action pull-right btn-primary _blank" href="{$element->addons_buy_url|replace:' ':'+'|escape:'html':'UTF-8'}">{l s='Explore'}</a>
                        {else}
                            <a class="btn button-action pull-right btn-primary" href="{$element->options.install_url|escape:'html':'UTF-8'}" title="{l s='Install'}">{l s='Install'}</a>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
</div>