<div class="rm_qty_cont">
    <input type="hidden" class="text-center form-control quantity_wanted" min="1" name="qty" value="1">
    <input type="hidden" class="max_avail_type_qty" value="{if isset($total_available_rooms)}	{$total_available_rooms|escape:'html':'UTF-8'}{/if}">
    <div class="qty_count pull-left">
        <span>1</span>
    </div>
    <div class="qty_direction pull-left">
        <a href="#" data-field-qty="qty" class="btn btn-default rm_quantity_up">
            <span><i class="icon-plus"></i></span>
        </a>
        <a href="#" data-field-qty="qty" class="btn btn-default rm_quantity_down">
            <span><i class="icon-minus"></i></span>
        </a>
    </div>
</div>