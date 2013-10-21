<?php
/**
 * This File is used from the command line to create a YAML config file. We use Yaml so that
 * we can easily tripwire the other files...
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package RYD
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */	
	$credentials = array(
		'base_url' => 'https://record.synseer.net',
		'logo_url' => 'images/logo.jpg',
		'app_name' => 'Record Your Doctor',
		'EULA_VERSION' => 1,
		'SPLASH_VERSION' => 1,
		'MOTD_VERSION' => 1,
		'debug' => false,
		'tmp_dir' => "/var/www/tmp",
		'timezone' => "America/New_York",
		'mysql_user' => 'root',
		'mysql_password' => 'password',
		'mysql_host' => 'localhost',
		'mysql_database' => 'record',
		'rackspace_user' => 'my@rackspace_user',
		'rackspace_key' => 'my_rackspace_key'
);

	require_once('util/spyc/spyc.php');
	$yaml_str = Spyc::YAMLDump($credentials);

	echo $yaml_str;

?>
