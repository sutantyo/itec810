<?php
/**
 *  Randomised Programming Quiz System - A quiz system that develops random programming questions from defined templates
 *  Copyright (C) 2010-2012 Ben Evans <ben@nebev.net>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/	
class Model_Auth_ActiveDirectory extends Model_Auth_General{

	protected static function load_module() {
		//return; //do nothing Ivan
		
		// Load up the AD-LDAP module
		include_once("adldap/adLDAP.php");
		
		// Get Our LDAP Configuration
		$config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
		$ldap_config = $config->ldap->toArray();
		
		// Make sure LDAP Config is valid
		if(
			!is_array($ldap_config) || 
			!array_key_exists("username", $ldap_config) || 
			!array_key_exists("password", $ldap_config) || 
			!array_key_exists("account_suffix", $ldap_config) || 
			!array_key_exists("base_dn", $ldap_config) || 
			!array_key_exists("usessl", $ldap_config) || 
			!array_key_exists("usetls", $ldap_config) 
		){
			throw new Exception("No Appropriate LDAP Configuration found in Application Configuration", 3000);
		}
		
		// Make sure we have at least ONE domain controller to talk to
		$domain_controllers = array();
		$dc_counter = 1;
		for( $dc_counter = 1; $dc_counter <= 3; $dc_counter++ ) {
			if( array_key_exists("domain_controller_" . $dc_counter, $ldap_config) ) {
				$domain_controllers[] = $ldap_config[ "domain_controller_" . $dc_counter ];
			}
		}
		if( sizeof($domain_controllers) < 1) {
			throw new Exception("No Active Directory Domain Controllers specified", 3000);
		}
		
		// Manufacture the Options adLDAP Expects:
		$options = array(
			"account_suffix"		=> $ldap_config['account_suffix'],
			"base_dn"				=> $ldap_config['base_dn'],
			'domain_controllers'	=> $domain_controllers,
			'admin_username'			=> $ldap_config['username'],
			'admin_password'			=> $ldap_config['password'],
			'use_ssl'				=> intval( $ldap_config['usessl'] ),
			"use_tls"				=> intval( $ldap_config['usetls'] )
		);
		
		if( $options['use_ssl'] == 0 ) { $options['use_ssl'] = false; }else{ $options['use_ssl'] = true; }
		if( $options['use_tls'] == 0 ) { $options['use_tls'] = false; }else{ $options['use_tls'] = true; }
		
		
		return new adLDAP($options);
		//return new Model_Mock_adLDAP($options);
		//return new $ldap_config['provider']($options);
	}


	public function authenticate($username, $password) {
	    
	    //return true; //HACK Ivan
	    
		$adldap = Model_Auth_ActiveDirectory::load_module();
		return $adldap->authenticate( $username, $password );
	}

	/**
	 * Returns true if a user is part of the Active Directory Group passed
	 *
	 * @param string $username 
	 * @param string $group 
	 * @return boolean
	 * @author Ben Evans
	 */
	public function userInGroup( $username, $group ) {
	    //return true; //lecturer? ivan
	    //return false; //student? ivan
		$adldap = Model_Auth_ActiveDirectory::load_module();
		$ad_groups = $adldap->user()->groups($username, true);
		foreach($ad_groups as $ad_group) {
			if(strtolower($ad_group) == strtolower($group)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns the users that are part of the given group
	 *
	 * @param string $group 
	 * @return multitype:string
	 * @author Ben Evans
	 */
	public static function getUsersFromGroup( $group ) {
		$adldap = Model_Auth_ActiveDirectory::load_module();
		return $adldap->group()->members($group);
	}
	
	/**
	 * Gets the groups that a given user is a member of
	 *
	 * @param string $username 
	 * @return void
	 * @author Ben Evans
	 */
	public static function getUserGroups( $username ) {
		$adldap = Model_Auth_ActiveDirectory::load_module();
		return $adldap->user()->groups($username, true);
	}
	
	
	/**
	 * Returns Basic User Details
	 *
	 * @param string $username 
	 * @return array
	 * @author Ben Evans
	 */
	public static function getUserDetails( $username ) {
		
		// Start by consulting the local database first
		$db = Zend_Registry::get("db");
		$query = "SELECT * FROM ad_user_cache WHERE samaccountname=" . $db->quote($username);
		$stmt = $db->query( $query );
		$rows = $stmt->fetchAll();
		
		if( sizeof( $rows ) == 1 ) {
			$row = current($rows);
			return $row;
		}
		
		// OK. We need to do an LDAP Query instead (and then update the database)
		$vUser = Model_Auth_ActiveDirectory::updateUser( $username );
		if( !array_key_exists("sn", $vUser[0]) ) {
			if( !array_key_exists("givenname", $vUser[0]) ) {
				$vUser[0]['sn'] = array($username);
				$vUser[0]['givenname'] = array("");
			}
		}elseif( !array_key_exists("givenname", $vUser[0]) ) {
			$vUser[0]['givenname'] = array("");
		}
		
		$ad_groups = self::getUserGroups($username);
		
		return array(   "last_name" => $vUser[0]['sn'][0]
		              , "first_name" => $vUser[0]['givenname'][0]
		              , "groups" => $ad_groups );
	}
	
	
	/**
	 * Updates the Active Directory Database Cache for a given user
	 *
	 * @param string $username 
	 * @param string $last_name 
	 * @param string $first_name 
	 * @return void
	 * @author Ben Evans
	 */
	protected static function updateCache( $username, $last_name, $first_name ) {
		$db = Zend_Registry::get("db");
		$db->query("DELETE FROM ad_user_cache WHERE samaccountname = " . $db->quote($username) . " LIMIT 1");
		$db->query("UPDATE ad_user_cache SET first_name = " . $db->quote($first_name) . ", last_name = " . $db->quote($last_name) . " WHERE samaccountname = " . $db->quote($username) . " LIMIT 1");
	}
	
	/**
	 * Updates the Active Directory Database Cache for a User
	 *
	 * @param string $username 
	 * @return void
	 * @author Ben Evans
	 */
	public static function updateUser( $username ) {
		$adldap = Model_Auth_ActiveDirectory::load_module();
		$vUser = $adldap->user()->info( $username, array("givenName", "sn") );
		$sn = "";
		$fn = "";
		
		if( array_key_exists("sn", $vUser[0]) ) {
			$sn = $vUser[0]['sn'][0];
		}
		if( array_key_exists("givenname", $vUser[0]) ) {
			$fn = $vUser[0]['givenname'][0];
		}
		
		Model_Auth_ActiveDirectory::updateCache($username, $sn, $fn);
		return $vUser;
	}

}
	
?>
