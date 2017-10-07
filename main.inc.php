<?php
/*
Plugin Name: PayPal Shopping Cart
Version: auto
Description: Append PayPal Shopping Cart on Piwigo to sell photos
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=499
Author: queguineur.fr
Author URI: http://www.queguineur.fr
*/
/*
  Plugin Panier PayPal Pour Piwigo
  Copyright (C) 2011 www.queguineur.fr � Tous droits r�serv�s.
  
  Ce programme est un logiciel libre ; vous pouvez le redistribuer ou le
  modifier suivant les termes de la �GNU General Public License� telle que
  publi�e par la Free Software Foundation : soit la version 3 de cette
  licence, soit (� votre gr�) toute version ult�rieure.
  
  Ce programme est distribu� dans l�espoir qu�il vous sera utile, mais SANS
  AUCUNE GARANTIE : sans m�me la garantie implicite de COMMERCIALISABILIT�
  ni d�AD�QUATION � UN OBJECTIF PARTICULIER. Consultez la Licence G�n�rale
  Publique GNU pour plus de d�tails.
  
  Vous devriez avoir re�u une copie de la Licence G�n�rale Publique GNU avec
  ce programme ; si ce n�est pas le cas, consultez :
  <http://www.gnu.org/licenses/>.
*/
/*
Historique
1.0.0   10/02/2011
Version initiale
		
1.0.1   10/02/2011
Ajout du Plugin URI pour permettre les mises � jours
Traduction en Anglais du Plugin Name et du nom du r�pertoire
        
1.0.2   10/02/2011
Correction du probl�me de compatibilit� avec exif view (double affichage des boutons)
	
1.0.3   15/02/2011
Add lv_LV (Latvian) thanks to Aivars Baldone

1.0.4   17/02/2011
Add de_DE and it_IT (par Sugar888)

1.0.5   27/02/2011
Correction pb compatibilit� avec certains th�mes
D�placement des boutons PayPal en d�but de table info

1.0.6   05/03/2011
Add sk_SK (by dodo)

1.0.7   26/03/2011
Add hu_HU language (Hungarian) thanks to samli

04/11/2017
Ajout onglet Paypal information utilisateur, mode
Ajout de la gestion des urls cancel, return pour Paypal

*/
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $prefixeTable;

// +-----------------------------------------------------------------------+
// | Define plugin constants                                               |
// +-----------------------------------------------------------------------+

defined('PPPPP_ID') or define('PPPPP_ID', basename(dirname(__FILE__)));
define('PPPPP_PATH' , PHPWG_PLUGINS_PATH . basename(dirname(__FILE__)) . '/');
define('PPPPP_SIZE_TABLE', $prefixeTable.'ppppp_size');
define('PPPPP_VERSION', 'auto');

function ppppp_append_form($tpl_source, &$smarty)
{
  global $theme;

  $pattern = '#<.*\"infoTable\".*>#';
  $replacement = '
  <tr>
   <td class="label">{\'Buy this picture\'|@translate}</td>
   <td>
    <form name="ppppp_form" target="paypal" action="https://{$ppppp_url}/cgi-bin/webscr" method="post" onSubmit="javascript:pppppValid()">
     <input type="hidden" name="cancel_return" value="{$ppppp_cancel}">
     <input type="hidden" name="return" value="{$ppppp_return}">
     <input type="hidden" name="add" value="1">
     <input type="hidden" name="cmd" value="_cart">
     <input type="hidden" name="business" value="{$ppppp_business}">
     <input type="hidden" name="item_name">
     <input type="hidden" name="no_shipping" value="2"><!-- shipping address mandatory -->
	 <input type="hidden" name="handling_cart" value="{$ppppp_fixed_shipping}"> 
     <input type="hidden" name="currency_code" value="{$ppppp_currency}">
     <select name="amount">
	  {foreach from=$ppppp_array_size item=ppppp_row_size}
      <option value="{$ppppp_row_size.price}">{$ppppp_row_size.size} : {$ppppp_row_size.price} {$ppppp_currency}</option>
	  {/foreach}
	 </select>
     <input type="submit" value="{\'Add to cart\'|@translate}">
    </form>
   </td>
   <td>
    <form target="paypal" action="https://{$ppppp_url}/cgi-bin/webscr" method="post">
	 <input type="hidden" name="cancel_return" value="{$ppppp_cancel}">
     <input type="hidden" name="return" value="{$ppppp_return}">
     <input type="hidden" name="cmd" value="_cart">
     <input type="hidden" name="business" value="{$ppppp_business}">
     <input type="hidden" name="display" value="1">
     <input type="hidden" name="no_shipping" value="2">
     <input type=submit value="{\'View Shopping Cart\'|@translate}">
    </form>
   </td>
  </tr> 
 
 {literal}
 <script type="text/javascript">
 function pppppValid(){
  var amount=document.ppppp_form.amount;
  var selectedAmount=amount[amount.selectedIndex];
  document.ppppp_form.item_name.value="Photo \"{/literal}{$current.TITLE}\", Ref {$INFO_FILE}, {\'Size\'|@translate} : {literal} "+selectedAmount.text;
  }
 </script>
 {/literal}
 ';

  if (strpos($theme, 'stripped') === 0)
  {
    $pattern = '#</div>\s*<!--\s*theImage\s*-->#';
    $replacement = '
{combine_css path="plugins/PayPalShoppingCart/stripped.css"}
<table id="paypalCart">'.$replacement.'</table>';
    $replacement = $replacement.'$0';
  }
  else
  {
    if(!preg_match($pattern,$tpl_source))
    {
      $pattern='#{if isset\(\$COMMENT_IMG\)}#';
      $replacement='<table>'.$replacement.'</table>';
      $replacement=$replacement.'$0';
    }
    else
    {
      $replacement='$0'.$replacement;
    }
  }
  
  return preg_replace($pattern, $replacement, $tpl_source,1);
}

function ppppp_picture_handler()
{
  global $template, $conf, $page;

  if (!ppppp_is_paypal_active())
  {
    return;
  }
  
	if(!empty($_SERVER['HTTPS'])) $scheme = 'https'; else $scheme = 'http';
	if(!empty($_SERVER['HTTP_HOST'])) $host = $_SERVER['HTTP_HOST'];
	if(!empty($scheme) and !empty($host)) { $base = $scheme.'://'.$host; } 
	unset($scheme,$host);
 
  $template->set_prefilter('picture', 'ppppp_append_form');
  load_language('plugin.lang', PPPPP_PATH);
  
  $query='SELECT * FROM '.PPPPP_SIZE_TABLE.' '.@$conf['PayPalShoppingCart_sizes_order_by'].';';
  $result = pwg_query($query);
  while($row = pwg_db_fetch_assoc($result))
  {
    $template->append('ppppp_array_size',$row);
  }

  $template->assign(
    array(
      'ppppp_fixed_shipping' => $conf['PayPalShoppingCart']['fixed_shipping'],
      'ppppp_currency' => $conf['PayPalShoppingCart']['currency'],
      'ppppp_business' => $conf['PayPalShoppingCart']['business'],
      'ppppp_url' => $conf['PayPalShoppingCart']['url'],
      'ppppp_cancel' => $base.$_SERVER['REQUEST_URI'],
      'ppppp_return' => $base,
     )
    );
    
    unset($base);
    
}

add_event_handler('loc_begin_picture', 'ppppp_picture_handler');

function ppppp_append_js($tpl_source, &$smarty){
 load_language('plugin.lang', PPPPP_PATH);
 if(strstr($tpl_source,"{'Menu'|@translate}")==false)
  return $tpl_source;
 $pattern = '#{/foreach}#';  
 $replacement = '{/foreach}
 <li><a href="" title="'.l10n('View my PayPal Shopping Cart').'" onclick="document.forms[\'ppppp_form_view_cart\'].submit()">'.l10n('View Shopping Cart').'</a></li>
 <form name="ppppp_form_view_cart" target="paypal" action="https://{$ppppp_url}/cgi-bin/webscr" method="post">
     <input type="hidden" name="cmd" value="_cart">
     <input type="hidden" name="business" value="{$ppppp_business}">
     <input type="hidden" name="display" value="1">
     <input type="hidden" name="no_shipping" value="2">
  </form>
  ';
 return preg_replace($pattern, $replacement, $tpl_source); 
 }

function ppppp_index_handler(){
 global $conf, $template;
 $template->set_prefilter('menubar', 'ppppp_append_js');
 //$template->assign('ppppp_e_mail',get_webmaster_mail_address()); 
 $template->assign(
	array(
		'ppppp_business' => $conf['PayPalShoppingCart']['business'],
		'ppppp_url' => $conf['PayPalShoppingCart']['url'],
	)
 );
 }

add_event_handler('loc_begin_index', 'ppppp_index_handler');

function ppppp_admin_menu($menu){
 load_language('plugin.lang', PPPPP_PATH);
 array_push($menu, array(
  'NAME' => l10n('PayPal Shopping Cart'),
  'URL' => get_admin_plugin_menu_link(PPPPP_PATH . 'admin.php')));
 return $menu;
 }

add_event_handler('get_admin_plugin_menu_links', 'ppppp_admin_menu');

add_event_handler('init', 'ppppp_init');
/**
 * plugin initialization
 *   - unserialize configuration
 *   - load language
 */
function ppppp_init()
{
  global $conf;
  
  // load plugin language file
  load_language('plugin.lang', PPPPP_PATH);
  
  // prepare plugin configuration
  $conf['PayPalShoppingCart'] = safe_unserialize($conf['PayPalShoppingCart']);
}

// add_event_handler('loc_end_index_thumbnails', 'ppppp_loc_end_index_thumbnails');
add_event_handler('loc_begin_index', 'ppppp_lightbox_exception');
function ppppp_lightbox_exception()
{
  if (!ppppp_is_paypal_active())
  {
    return;
  }
  
  remove_event_handler('loc_end_index_thumbnails', 'lightbox_plugin', 40);
}

function ppppp_is_paypal_active()
{
  global $conf, $page;

  if ($conf['PayPalShoppingCart']['apply_to_albums'] == 'list')
  {
    if (!isset($page['category']))
    {
      return false;
    }

    $query = '
SELECT
    paypal_active
  FROM '.CATEGORIES_TABLE.'
  WHERE id = '.$page['category']['id'].'
;';
    list($paypal_active) = pwg_db_fetch_row(pwg_query($query));

    if ('false' == $paypal_active)
    {
      return false ;
    }
  }

  return true;
}
?>
