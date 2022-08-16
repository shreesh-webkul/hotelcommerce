{if isset($list)}
	{foreach from=$list key=key item=product}
        <tr>
            <td style="border:1px solid #D6D4D4;">
                <table class="table">
                    <tr>
                        <td width="10">&nbsp;</td>
                        <td class="text-center">
                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                <img src="{$product['cover_img']}" class="img-responsive" />
                            </font>
                        </td>
                        <td width="10">&nbsp;</td>
                    </tr>
                </table>
            </td>
            <td style="border:1px solid #D6D4D4;">
                <table class="table">
                    <tr>
                        <td width="10">&nbsp;</td>
                        <td  class="text-center">
                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                {$product['name']}
                            </font>
                        </td>
                        <td width="10">&nbsp;</td>
                    </tr>
                </table>
            </td>
            <td style="border:1px solid #D6D4D4;">
                <table class="table">
                    <tr>
                        <td width="10">&nbsp;</td>
                        <td align="right"  class="text-center">
                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                {convertPrice price=$product['unit_price']}
                            </font>
                        </td>
                        <td width="10">&nbsp;</td>
                    </tr>
                </table>
            </td>
            <td style="border:1px solid #D6D4D4;">
                <table class="table">
                    <tr>
                        <td width="10">&nbsp;</td>
                        <td align="right"  class="text-center">
                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                {$product['quantity']}
                            </font>
                        </td>
                        <td width="10">&nbsp;</td>
                    </tr>
                </table>
            </td>
            <td style="border:1px solid #D6D4D4;">
                <table class="table">
                    <tr>
                        <td width="10">&nbsp;</td>
                        <td align="right"  class="text-center">
                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                {convertPrice price=$product['price']}
                            </font>
                        </td>
                        <td width="10">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    {/foreach}
{/if}
<style>
	.pull-right {
		float: right;
	}
</style>
