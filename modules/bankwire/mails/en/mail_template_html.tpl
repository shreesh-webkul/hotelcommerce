<tr>
	<td class="box" style="border:1px solid #D6D4D4;background-color:#f8f8f8;padding:7px 0">
		<table class="table" style="width:100%">
			<tr>
				<td width="10" style="padding:7px 0">&nbsp;</td>
				<td style="padding:7px 0">
					<font size="2" face="Open-sans, sans-serif" color="#555454">
                        <p style="border-bottom:1px solid #D6D4D4;margin:3px 0 7px;text-transform:uppercase;font-weight:500;font-size:18px;padding-bottom:10px">
                            {l s='Here are the bank details for your transfer:' mod='bankwire'}
                        </p>
                        <span style="color:#777">
                            <span style="color:#333"><strong>{l s='Amount:' mod='bankwire'}</strong></span> {literal}{total_paid}{/literal}<br />
                            <span style="color:#333"><strong>{l s='Account owner:' mod='bankwire'}</strong></span> {$bankwire_owner}<br />
                            <span style="color:#333"><strong>{l s='Account details:' mod='bankwire'}</strong></span> {$bankwire_details}<br />
                            <span style="color:#333"><strong>{l s='Bank address:' mod='bankwire'}</strong></span> {$bankwire_address}
                        </span>
                    </font>
                </td>
                <td width="10" style="padding:7px 0">&nbsp;</td>
            </tr>
        </table>
    </td>
</tr>
<tr>
	<td class="space_footer" style="padding:0!important">&nbsp;</td>
</tr>