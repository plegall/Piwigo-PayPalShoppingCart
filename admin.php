<?php
/*
  Plugin Panier PayPal Pour Piwigo
  Copyright (C) 2011 www.queguineur.fr — Tous droits réservés.
  
  Ce programme est un logiciel libre ; vous pouvez le redistribuer ou le
  modifier suivant les termes de la “GNU General Public License” telle que
  publiée par la Free Software Foundation : soit la version 3 de cette
  licence, soit (à votre gré) toute version ultérieure.
  
  Ce programme est distribué dans l’espoir qu’il vous sera utile, mais SANS
  AUCUNE GARANTIE : sans même la garantie implicite de COMMERCIALISABILITÉ
  ni d’ADÉQUATION À UN OBJECTIF PARTICULIER. Consultez la Licence Générale
  Publique GNU pour plus de détails.
  
  Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec
  ce programme ; si ce n’est pas le cas, consultez :
  <http://www.gnu.org/licenses/>.
*/
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');
global $template;
include_once(PHPWG_ROOT_PATH .'admin/include/tabsheet.class.php');
load_language('plugin.lang', PPPPP_PATH);
$my_base_url = get_admin_plugin_menu_link(__FILE__);

// onglets
if (!isset($_GET['tab']))
    $page['tab'] = 'currency';
else
    $page['tab'] = $_GET['tab'];

$tabsheet = new tabsheet();
$tabsheet->add('currency',
               l10n('Currency'),
               $my_base_url.'&amp;tab=currency');
$tabsheet->add('albums', l10n('Albums'), $my_base_url.'&amp;tab=albums');
$tabsheet->add('size',
               l10n('Size'),
               $my_base_url.'&amp;tab=size');
$tabsheet->add('shipping',
               l10n('Shipping cost'),
               $my_base_url.'&amp;tab=shipping');			   
$tabsheet->select($page['tab']);
$tabsheet->assign();

switch($page['tab'])
{
  case 'currency':
    
    $array_currency = array(
      'AUD'=>'Australian Dollar',
      'BRL'=>'Brazilian Real',
      'CAD'=>'Canadian Dollar',
      'CZK'=>'Czech Koruna',
      'DKK'=>'Danish Krone',
      'EUR'=>'Euro',
      'HKD'=>'Hong Kong Dollar',
      'HUF'=>'Hungarian Forint',
      'ILS'=>'Israeli New Sheqel',
      'JPY'=>'Japanese Yen',
      'MYR'=>'Malaysian Ringgit',
      'MXN'=>'Mexican Peso',
      'NOK'=>'Norwegian Krone',
      'NZD'=>'New Zealand Dollar',
      'PHP'=>'Philippine Peso',
      'PLN'=>'Polish Zloty',
      'GBP'=>'Pound Sterling',
      'SGD'=>'Singapore Dollar',
      'SEK'=>'Swedish Krona',
      'CHF'=>'Swiss Franc',
      'TWD'=>'Taiwan New Dollar',
      'THB'=>'Thai Baht',
      'USD'=>'U.S. Dollar'
      );
  
    if(isset($_POST['currency']) and isset($array_currency[ $_POST['currency'] ]))
    {
      $conf['PayPalShoppingCart']['currency'] = $_POST['currency'];
      conf_update_param('PayPalShoppingCart', $conf['PayPalShoppingCart']);
      
      $page['infos'][] = l10n('Your configuration settings are saved');
    }
 
    $template->assign(
      array(
        'ppppp_currency' => $conf['PayPalShoppingCart']['currency'],
        'ppppp_array_currency' => $array_currency,
        )
      );
    
    break;

   case 'albums' :

     if (isset($_POST['apply_to_albums']) and in_array($_POST['apply_to_albums'], array('all', 'list')))
     {
       $conf['PayPalShoppingCart']['apply_to_albums'] = $_POST['apply_to_albums'];
       conf_update_param('PayPalShoppingCart', $conf['PayPalShoppingCart']);

       if ($_POST['apply_to_albums'] == 'list')
       {
         check_input_parameter('albums', $_POST, true, PATTERN_ID);

         if (empty($_POST['albums']))
         {
           $_POST['albums'][] = -1;
         }
       
         $query = '
UPDATE '.CATEGORIES_TABLE.'
  SET paypal_active = \'false\'
  WHERE id NOT IN ('.implode(',', $_POST['albums']).')
;';
         pwg_query($query);

         $query = '
UPDATE '.CATEGORIES_TABLE.'
  SET paypal_active = \'true\'
  WHERE id IN ('.implode(',', $_POST['albums']).')
;';
         pwg_query($query);
       }

       $page['infos'][] = l10n('Your configuration settings are saved');
     }
   
     // associate to albums
     $query = '
SELECT id
  FROM '.CATEGORIES_TABLE.'
  WHERE paypal_active = \'true\'
;';
     $paypal_albums = array_from_query($query, 'id');

     $query = '
SELECT id,name,uppercats,global_rank
  FROM '.CATEGORIES_TABLE.'
;';
     display_select_cat_wrapper($query, $paypal_albums, 'album_options');

     $template->assign('apply_to_albums', $conf['PayPalShoppingCart']['apply_to_albums']);

     break;
  
 
  case 'size':
    
    if (isset($_POST['delete']))
    {
      check_input_parameter('delete', $_POST, false, PATTERN_ID);
      
      pwg_query('DELETE FROM '.PPPPP_SIZE_TABLE.' WHERE id = '.$_POST['delete'].';');

      $page['infos'][] = l10n('Your configuration settings are saved');
    }
    else if (isset($_POST['size']) and isset($_POST['price']))
    {
      single_insert(
        PPPPP_SIZE_TABLE,
        array(
          'size' => pwg_db_real_escape_string($_POST['size']),
          'price' => pwg_db_real_escape_string($_POST['price']),
          )
        );

      $page['infos'][] = l10n('Your configuration settings are saved');
    }
    
    $query='SELECT * FROM '.PPPPP_SIZE_TABLE.';';
    $result = pwg_query($query);
    while($row = pwg_db_fetch_assoc($result))
    {
      $template->append('ppppp_array_size',$row);
    }
    
    break;

  case 'shipping':
    
    if (isset($_POST['fixed_shipping'])and is_numeric($_POST['fixed_shipping']))
    {
      $conf['PayPalShoppingCart']['fixed_shipping'] = $_POST['fixed_shipping'];
      conf_update_param('PayPalShoppingCart', $conf['PayPalShoppingCart']);
      
      $page['infos'][] = l10n('Your configuration settings are saved');
    }
    
    $template->assign('ppppp_fixed_shipping', $conf['PayPalShoppingCart']['fixed_shipping']);
    break;
}

$template->set_filenames(array('plugin_admin_content' => dirname(__FILE__) . '/admin.tpl')); 
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');
?>