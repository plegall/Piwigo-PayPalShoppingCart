{combine_script id='jquery.chosen' load='footer' path='themes/default/js/plugins/chosen.jquery.min.js'}
{combine_css path="themes/default/js/plugins/chosen.css"}

{footer_script}{literal}
jQuery(document).ready(function() {
  jQuery(".chzn-select").chosen();

  function checkStatusOptions() {
    if (jQuery("input[name=apply_to_albums]:checked").val() == "list") {
      jQuery("#albumList").show();
    }
    else {
      jQuery("#albumList").hide();
    }
  }

  checkStatusOptions();

  jQuery("input[name=apply_to_albums]").change(function() {
    checkStatusOptions();
  });
});
{/literal}{/footer_script}

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

{elseif $tabsheet_selected=='albums'}
<h3>{'Albums'|@translate}</h3>
<form method=post>
<fieldset>
  <legend>{'Apply to albums'|@translate}</legend>
  <p>
    <label><input type="radio" name="apply_to_albums" value="all"{if $apply_to_albums eq 'all'} checked="checked"{/if}> <strong>{'all albums'|@translate}</strong></label>
    <label><input type="radio" name="apply_to_albums" value="list"{if $apply_to_albums eq 'list'} checked="checked"{/if}> <strong>{'a list of albums'|@translate}</strong></label>
  </p>
  <p id="albumList">
    <select data-placeholder="Select albums..." class="chzn-select" multiple style="width:700px;" name="albums[]">
      {html_options options=$album_options selected=$album_options_selected}
    </select>
  </p>
  <p class="formButtons">
		<input type="submit" name="submit" value="{'Save Settings'|@translate}">
	</p>
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