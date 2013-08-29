<?php

namespace SocialNetwork\API\OM;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, \Serializable
{
    protected  $username , $password , $roles , $groups , $attributes;

    public function getRoles()
    {
        return $this->roles;
    }

    public function getUsername()
    {

        return $this->username;
    }

    public function getPassword()
    {
        return  $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function setAttribute( $name, $value )
    {
        $this->attributes[$name] =  $value;
    }

    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null ; 
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function eraseCredentials()
    {
        return null; //With ldap No credentials with stored ; Maybe forgotten the roles
    }

    public function serialize()
    {
        return serialize(array($this->roles, $this->attributes));
    }

    public function unserialize($serialized)
    {
        list($this->roles, $this->attributes) = unserialize($serialized);
    }

    public function setUsername($name)
    {
        $this->username = $name;
    }

    public function setPassword($password)
    {
        if(substr( $password , 0 , 5 ) == '{md5}')
            $password =  substr( $password , 5 );

        $this->password = $password;
    }

    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    public function getGroups()
    {
        return $this->groups;
    }

}
