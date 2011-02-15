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

if (!defined("PPPPP_PATH")){
  define('PPPPP_PATH', PHPWG_PLUGINS_PATH.basename(dirname(__FILE__)));
}
include_once (PPPPP_PATH.'/constants.php');

function plugin_install(){
 global $prefixeTable;
 $query = "CREATE TABLE IF NOT EXISTS ".PPPPP_SIZE_TABLE." (
  id tinyint(4) NOT NULL AUTO_INCREMENT,
  size varchar(40) NOT NULL,
  price float NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY size (size)
  );";
 pwg_query($query);
 $query = "INSERT INTO ".PPPPP_SIZE_TABLE." (size,price) VALUES ('Classique', '40');";
 pwg_query($query);
 
 $query = "CREATE TABLE IF NOT EXISTS ".PPPPP_CONFIG_TABLE." (
  param varchar(40) NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (param)
  );";
 pwg_query($query);
 $query = "INSERT INTO ".PPPPP_CONFIG_TABLE." VALUES ('fixed_shipping', '0');";
 pwg_query($query);
 $query = "INSERT INTO ".PPPPP_CONFIG_TABLE." VALUES ('currency', 'EUR');";
 pwg_query($query);
 }

function plugin_uninstall(){
 global $prefixeTable;
 $query = "DROP TABLE ".PPPPP_SIZE_TABLE.";";
 pwg_query($query);
 $query = "DROP TABLE ".PPPPP_CONFIG_TABLE.";"; 
 pwg_query($query);
}
?>