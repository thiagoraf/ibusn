<?php

namespace SocialNetwork\API\Service;

/**
 * Class LdapService
 * @package SocialNetwork\API\Service
 */
class LdapService
{
    private $config = array();
    private $filters = array();
    private $contexts = array();
    private $_ress;
    private $idProvider;
    private $idRangesStart;

    /**
     * @param array $config
     * @param array $filters
     * @param array $contexts
     * @param array $idProvider
     */
    public function __construct(array $config , array $filters , array $contexts , array $idProvider )
    {
        $this->config = $config;
        $this->filters = $filters;
        $this->contexts = $contexts;
        $this->idProvider = $idProvider['service'];
        $this->idRangesStart = $idProvider['range_start'];
        $this->connect();
    }

    /**
     * Search in LDAP
     *
     * @param string $filter Ldap Filter
     * @param array $attibutes Attributes to return
     * @param bool $dn The base DN for the directory
     * @param bool $sort The attribute to use as a key in the sort.
     * @param bool $limit Limit of result
     * @param bool $offset Start offset
     * @return array|bool
     */
    public function search( $filter , array $attibutes = array() , $dn = false , $sort =  false , $limit = false , $offset = false )
    {

        $dn = ($dn) ? $dn : $this->config['base_dn'];

        $searchLimit = ($limit !== false && $offset === false) ? $limit : ($limit === false && $offset === false) ? $this->config['size_limit'] :  0;

        $search = @ldap_search($this->_ress, $dn, $filter, $attibutes , 0 , $searchLimit );
        if($sort)ldap_sort($this->_ress, $search, $sort);

        if($offset !== false )
        {
            $return = array();
            $iTotal = ldap_count_entries( $this->_ress, $search );
            if($iTotal < 1 ) return false;
            $rEntry = ldap_first_entry(  $this->_ress, $search );

            $start = $offset;
            $end = $start + $limit;

            for ( $iCurrent = 0; $iCurrent < $iTotal ;$iCurrent++)
            {
                if($iCurrent < $start)
                {
                    $rEntry = ldap_next_entry( $this->_ress, $rEntry );
                    continue;
                }
                if( $iCurrent >= $end)  break;

                $return[] =  $this->formatEntries(ldap_get_attributes( $this->_ress, $rEntry )) ;
                $rEntry = ldap_next_entry( $this->_ress, $rEntry );

            }

            return $return;
        }

        return $search ? $this->formatEntries(ldap_get_entries($this->_ress, $search)) : array();
    }


    /**
     * Count number of entries exist in ldap with $filter
     *
     * @param $filter Ldap Filter
     * @param bool $dn The base DN for the directory
     * @return bool|int
     */
    public function count( $filter , $dn = false)
    {
        $dn = ($dn) ? $dn : $this->config['base_dn'];

        $search = ldap_search($this->_ress, $dn, $filter, array() , 0 , 0);
        return $search ? ldap_count_entries( $this->_ress , $search) : false;
    }

    /**
     * Authenticate user
     *
     * @param $userDn
     * @param $password
     * @return bool
     * @throws \Exception
     */
    public function bind($userDn, $password)
    {
        if (!$userDn) 
            throw new \Exception('You must bind with an ldap userDn');
        
        if (!$password) 
            throw new \Exception('Password can not be null to bind'); 

        return (bool)ldap_bind($this->_ress, $userDn, $password);
    }

    /**
     * Authenticate with admin (Config values)
     *
     * @return bool
     * @throws \Exception
     */
    public function bindAdmin()
    {
        if (!$this->config['admin_name'])
            throw new \Exception('You must bind with an ldap userDn (admin_name)');
        
        if (!$this->config['admin_pass'])
            throw new \Exception('Password can not be null to bind (admin_pass)'); 

        return $this->bind($this->config['admin_name'] , $this->config['admin_pass'] );
    }

    /**
     * Authenticate with user (Config values)
     *
     * @return bool
     * @throws \Exception
     */
    public function bindUser()
    {
        if (!$this->config['user_name']) 
            throw new \Exception('You must bind with an ldap userDn (admin_name)');
        
        if (!$this->config['user_pass'])
            throw new \Exception('Password can not be null to bind (admin_pass)'); 

        return $this->bind($this->config['user_name'] , $this->config['user_pass'] );
    }

    /**
     * Connect to LDAP backend
     *
     * @return resource
     * @throws \Exception
     */
    private function connect()
    {
        $port = isset($this->config['port']) ? $this->config['port'] : '389' ;
        $ress = ldap_connect($this->config['host'], $port);

        if (isset($this->config['version']) && $this->config['version'] !== null) 
            ldap_set_option($ress, LDAP_OPT_PROTOCOL_VERSION, $this->config['version']);
            
        if (isset($this->config['referrals_enabled']) && $this->config['referrals_enabled'] !== null) 
            ldap_set_option($ress, LDAP_OPT_REFERRALS, $this->config['referrals_enabled']);

        if (isset($this->config['user_name']) && $this->config['version'] !== null) 
        {
            if(!isset($this->config['user_pass'])) 
                throw new \Exception('You must uncomment password key');
            
            $bindress = ldap_bind($ress, $this->config['user_name'], $this->config['user_pass']);
        
            if (!$bindress) 
                throw new \Exception('The credentials you have configured are not valid');
        } 
        else 
        {
            $bindress = ldap_bind($ress);
        
            if (!$bindress) 
                throw new \Exception('Unable to connect Ldap');
        }
    
        $this->_ress = $ress;
        return $this->_ress;
    }

    /**
     * Scape especial characters
     *
     * @param $str
     * @return mixed
     */
    public function escape($str)
    {
        $metaChars = array('*', '(', ')', '\\', chr(0));
        $quotedMetaChars = array();
        foreach ($metaChars as $key => $value) 
            $quotedMetaChars[$key] = '\\'.str_pad(dechex(ord($value)), 2, '0');
        
        $str = str_replace($metaChars, $quotedMetaChars, $str);
        return $str;
    }

    /**
     * Format ldap return to array ( Key => value )
     *
     * @param array $entries
     * @return array
     */
    private function formatEntries( array  $entries )
    {
        $return = array();
        for ( $i=0; $i < $entries['count']; $i++ ) 
        {
            if(is_array($entries[$i]))
                $return[] =  $this->formatEntries($entries[$i]);
            else if(isset($entries[$entries[$i]]) && is_array($entries[$entries[$i]]))
                $return[$entries[$i]] = $this->formatEntries($entries[$entries[$i]]);
            else if($entries['count'] === 1)
                return $entries[$i];
            else  
                $return[] = $entries[$i];

            if(isset($entries['dn']))
                $return['dn'] = $entries['dn'];
        }

        if(isset($entries['dn']))
            $return['dn'] = $entries['dn'];

        return $return;
    }

    /**
     * Add attribute in DN
     *
     * @param $data Array ( attribute => value )
     * @param $dn
     * @return bool
     * @throws \Exception
     */
    public function add($data , $dn )
    {
        $this->bindAdmin();

        foreach($data as $i => $v) //Remove campos em branco ou false para evitar erros no ldap.
            if($v === '' || $v === false)
                unset($data[$i]);

        if (ldap_add($this->_ress, $dn , $data))
        {
            $this->unbindAdmin();
            return true;
        }
        else
            throw new \Exception(ldap_error($this->_ress));

        return false;
    }

    /**
     * Delete DN
     *
     * @param $dn
     * @return bool
     * @throws \Exception
     */
    public function delete( $dn )
    {
        $this->bindAdmin();

        if (  ldap_delete($this->_ress, $dn))
        {
            $this->unbindAdmin();
            return true;
        }
        else
            throw new \Exception(ldap_error($this->_ress));

        return false;
    }

    /**
     * Replace Attributes in DN
     *
     * @param array $attributes
     * @param $dn
     * @return bool
     */
    public function replaceAttributes( array $attributes , $dn )
    {
        $this->bindAdmin();
        return ldap_mod_replace ( $this->_ress , $dn , $attributes ) ? $this->unbindAdmin() : false ;
    }

    /**
     * Remove DN attributes
     *
     * @param array $attributes
     * @param $dn
     * @return bool
     */
    public function removeAttributes( array $attributes , $dn )
    {
        $this->bindAdmin();
        return ldap_mod_del ( $this->_ress , $dn , $attributes ) ? $this->unbindAdmin() : false ;
    }

    /**
     * Add attribute to DN
     *
     * @param array $attributes
     * @param $dn
     * @return bool
     */
    public function addAttributes( array $attributes , $dn )
    {
        $this->bindAdmin();

        return ldap_mod_add ( $this->_ress , $dn , $attributes ) ? $this->unbindAdmin() : false ;
    }


    /**
     * Rename DN
     *
     * @param $dn
     * @param $newrdn
     * @param $newparent
     * @param bool $deleteoldrdn
     * @return bool
     */
    public function rename( $dn, $newrdn, $newparent, $deleteoldrdn = true )
    {
        $this->bindAdmin();

        return ldap_rename ( $this->_ress, $dn, $newrdn, $newparent, $deleteoldrdn) ? $this->unbindAdmin() : false;
    }

    /**
     * Close active connection
     *
     */
    function close()
    {
        return ldap_close($this->_ress);
    }

    /**
     * Close admin connection and reconnect with user
     *
     * @return bool
     */
    function unbindAdmin()
    {
        $this->close();
        $this->connect();
        return true;
    }

    /**
     * Convert filter format
     *
     * @param $search
     * @param $params
     * @param $conditional
     * @return string
     */
    function stemFilter($search, $params , $conditional)
    {
        $search = str_replace(' ', '*', $search);

        if (!is_array($params))
            $params = array($params);

        foreach ($params as $i => $param)
            $params[$i] = "($param=*$search*)";

        return '(' . $conditional . implode('', $params) . ')';
    }

    /**
     * Return $config position from the config service
     *
     * @param $config
     * @return bool
     */
    public function getConfig( $config )
    {
        return isset($this->config[$config]) ? $this->config[$config] : false;
    }

    /**
     * Return $filter position from the filter configuration
     *
     * @param $filter
     * @return bool
     */
    public function getFilter( $filter )
    {
        return isset($this->filters[$filter]) ? $this->filters[$filter] : false;
    }

    /**
     * Set context ( Base DN )
     *
     * @param $context
     * @param $value
     */
    public function setContext($context , $value)
    {
        $this->contexts[$context] = $value;
    }

    /**
     * Get $context position from the context configuration
     *
     * @param $context
     * @return mixed
     */
    public function getContext($context )
    {
        return $this->contexts[$context];
    }

    /**
     *  Generate next valid id
     *
     * @param $type
     * @return mixed
     */
    public function getNextId( $type )
    {
        return  $this->idProvider->generate( 'uid_number', 10000000000 );
    }

}
