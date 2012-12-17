<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

/**
 * The installation function is called by main.inc.php and maintain.inc.php
 * in order to install and/or update the plugin.
 *
 * That's why all operations must be conditionned :
 *    - use "if empty" for configuration vars
 *    - use "IF NOT EXISTS" for table creation
 *
 * Unlike the functions in maintain.inc.php, the name of this function must be unique
 * and not enter in conflict with other plugins.
 */

function ppppp_install() 
{
  global $conf, $prefixeTable, $template;

  $tables = ppppp_get_tables();

  if (!in_array($prefixeTable.'ppppp_size', $tables))
  {
    $query = "
CREATE TABLE IF NOT EXISTS ".$prefixeTable."ppppp_size (
  id tinyint(4) NOT NULL AUTO_INCREMENT,
  size varchar(40) NOT NULL,
  price float NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY size (size)
  )
;";
    pwg_query($query);

    single_insert(
      $prefixeTable."ppppp_size",
      array(
        'size' => 'Classique',
        'price' => 40,
        )
      );
  }

  if (!in_array($prefixeTable.'ppppp_config', $tables))
  {
    $query = "
CREATE TABLE IF NOT EXISTS ".$prefixeTable."ppppp_config (
  param varchar(40) NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (param)
  )
;";
    pwg_query($query);

    mass_inserts(
      $prefixeTable."ppppp_config",
      array('param', 'value'),
      array(
        array('param' => 'fixed_shipping', 'value' => '0'),
        array('param' => 'currency', 'value' => 'EUR'),
        )
      );
  }

  // add a new column to existing table
  $result = pwg_query('SHOW COLUMNS FROM `'.CATEGORIES_TABLE.'` LIKE "paypal_active";');
  if (!pwg_db_num_rows($result))
  {
    pwg_query('ALTER TABLE `'.CATEGORIES_TABLE.'` ADD `paypal_active` enum(\'true\', \'false\') default \'false\';');
  }
  
  // add config parameter
  if (empty($conf['PayPalShoppingCart']))
  {
    $default_config = serialize(array(
      'apply_to_albums' => 'all',
      ));
  
    conf_update_param('PayPalShoppingCart', $default_config);
    $conf['PayPalShoppingCart'] = $default_config;

    $template->delete_compiled_templates();
  }
}

/**
 * list all tables in an array
 *
 * @return array
 */
function ppppp_get_tables()
{
  global $prefixeTable;
  
  $tables = array();

  $query = '
SHOW TABLES
;';
  $result = pwg_query($query);

  while ($row = pwg_db_fetch_row($result))
  {
    if (preg_match('/^'.$prefixeTable.'/', $row[0]))
    {
      array_push($tables, $row[0]);
    }
  }

  return $tables;
}
?>