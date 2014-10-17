<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

class PayPalShoppingCart_maintain extends PluginMaintain
{
  private $installed = false;

  function __construct($plugin_id)
  {
    parent::__construct($plugin_id);
  }

  function install($plugin_version, &$errors=array())
  {
    global $conf, $prefixeTable, $template;

    $query = "
CREATE TABLE IF NOT EXISTS ".$prefixeTable."ppppp_size (
  id tinyint(4) NOT NULL AUTO_INCREMENT,
  size varchar(40) NOT NULL,
  price float NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY size (size)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8
;";
    pwg_query($query);

    $query = '
SELECT COUNT(*)
  FROM '.$prefixeTable.'ppppp_size
;';
    list($counter) = pwg_db_fetch_row(pwg_query($query));

    if (0 == $counter)
    {
      single_insert(
        $prefixeTable."ppppp_size",
        array(
          'size' => 'Classic',
          'price' => 40,
          )
        );
    }

    // add a new column to existing table
    $result = pwg_query('SHOW COLUMNS FROM `'.CATEGORIES_TABLE.'` LIKE "paypal_active";');
    if (!pwg_db_num_rows($result))
    {
      pwg_query('ALTER TABLE `'.CATEGORIES_TABLE.'` ADD `paypal_active` enum(\'true\', \'false\') default \'false\';');
    }

    $ppppp_config = array(
      'fixed_shipping' => 0,
      'currency' => 'EUR',
      'apply_to_albums' => 'all',
      );
    
    // move the content of table ppppp_config into $conf['PayPalShoppingCart'], serialized
    $result = pwg_query('SHOW TABLES LIKE "'.$prefixeTable.'ppppp_config";');
    if (pwg_db_num_rows($result))
    {
      $query = '
SELECT
    *
  FROM '.$prefixeTable.'ppppp_config
;';
      $result = pwg_query($query);
      while ($row = pwg_db_fetch_assoc($result))
      {
        if (isset($ppppp_config[ $row['param'] ]))
        {
          $ppppp_config[ $row['param'] ] = $row['value'];
        }
      }
      
      pwg_query('DROP TABLE '.$prefixeTable.'ppppp_config;');
    }
  
    // load existing config parameters
    if (!empty($conf['PayPalShoppingCart']))
    {
      $conf['PayPalShoppingCart'] = safe_unserialize($conf['PayPalShoppingCart']);
      
      foreach ($conf['PayPalShoppingCart'] as $key => $value)
      {
        $ppppp_config[$key] = $value;
      }
    }
    
    conf_update_param('PayPalShoppingCart', $ppppp_config, true);
    
    $this->installed = true;
  }

  function activate($plugin_version, &$errors=array())
  {
    global $prefixeTable;
    
    if (!$this->installed)
    {
      $this->install($plugin_version, $errors);
    }
  }

  function update($old_version, $new_version, &$errors=array())
  {
    $this->install($new_version, $errors);
  }
  
  function deactivate()
  {
  }

  function uninstall()
  {
    global $prefixeTable;
 
    $query = "DROP TABLE ".$prefixeTable."ppppp_size;";
    pwg_query($query);
    
    $result = pwg_query('SHOW TABLES LIKE "'.$prefixeTable.'ppppp_config";');
    if (pwg_db_num_rows($result))
    {
      $query = "DROP TABLE ".$prefixeTable."ppppp_config;"; 
      pwg_query($query);
    }

    // delete configuration
    pwg_query('DELETE FROM `'. CONFIG_TABLE .'` WHERE param = "PayPalShoppingCart";');
  
    // delete field
    pwg_query('ALTER TABLE `'. CATEGORIES_TABLE .'` DROP COLUMN paypal_active;');
  }
}
?>
