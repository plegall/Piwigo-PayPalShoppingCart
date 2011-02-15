<div class="titrePage">
<h2>{'PayPal Shopping Cart'|@translate}</h2>
</div>

{if $tabsheet_selected=='currency'}
<h3>{'Currency'|@translate}</h3>
<form method=post>
<fieldset>
<legend>{'Currency'|@translate}</legend>
<br>
<select name=currency onchange=submit()>
{foreach from=$ppppp_array_currency item=currency_label key=currency_code}
<option value="{$currency_code}"{if $ppppp_currency==$currency_code} selected{/if}>{$currency_label} ({$currency_code})</option>
{/foreach}
</select>
<br>
<br>
<!--input type=submit value="{'Update data'|@translate}"-->
</fieldset>
</form>

{elseif $tabsheet_selected=='size'}
<h3>{'Size'|@translate}</h3>
<form method=post>
<fieldset>
<legend>{'Append photo size'|@translate}</legend>
<br>
{'Size'|@translate} <input type=text name=size>
{'Price'|@translate} <input type=text name=price>
<br>
<br>
<input type=submit value="{'Append data'|@translate}">
</fieldset>
</form>
<fieldset>
<table class=table2>
<tr class=throw>
<th>{'Size'|@translate}</th>
<th>{'Price'|@translate}</th>
<th>{'Action'|@translate}</th>
</tr>
{foreach from=$ppppp_array_size item=ppppp_row_size name=ppppp_row_size_loop}
<tr class="{if $smarty.foreach.ppppp_row_size_loop.index is odd}row1{else}row2{/if}">
<td>{$ppppp_row_size.size}</td>
<td>{$ppppp_row_size.price}</td>
<td>
<form method=post>
<input type=hidden name=delete value='{$ppppp_row_size.id}'}>
<input type=submit value="{'Delete data'|@translate}">
</form>
</td>
</tr>
{/foreach}
</table>
</fieldset>

{else}
<h3>{'Shipping cost'|@translate}</h3>
<form method=post>
<fieldset>
<legend>{'Fixed shipping cost'|@translate}</legend>
<br>
<input type=text name=fixed_shipping value={$ppppp_fixed_shipping}>
<br>
<br>
<input type=submit value="{'Update data'|@translate}">
</fieldset>
</form>
{/if}