{* {if isset($product)} *}
	<div id="product-associations" class="panel product-tab">
		<input type="hidden" name="submitted_tabs[]" value="Configuration" />
		<h3>{l s='Configuration'}</h3>
		<div class="form-group">
			<div class="col-lg-1"><span class="pull-right">{include file="controllers/products/multishop/checkbox.tpl" field="category_box" type="category_box"}</span></div>
			<label class="control-label col-lg-2" for="category_block">
				{l s='Associated Hotels and Room Types'}
			</label>
			<div class="col-lg-9">
				<div id="category_block">
					{$hotel_tree}
				</div>
				{* <a class="btn btn-link bt-icon confirm_leave" href="{$link->getAdminLink('AdminCategories')|escape:'html':'UTF-8'}&amp;addcategory">
					<i class="icon-plus-sign"></i> {l s='Create new category'} <i class="icon-external-link-sign"></i>
				</a> *}
			</div>
		</div>
		<div class="panel-footer">
			<a href="{$link->getAdminLink('AdminNormalProducts')|escape:'html':'UTF-8'}{if isset($smarty.request.page) && $smarty.request.page > 1}&amp;submitFilterproduct={$smarty.request.page|intval}{/if}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel'}</a>
			<button type="submit" name="submitAddproduct" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save'}</button>
			<button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save and stay'}</button>
		</div>
	</div>
{* {/if} *}