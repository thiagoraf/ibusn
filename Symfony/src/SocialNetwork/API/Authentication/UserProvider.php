<?php
namespace SocialNetwork\API\Authentication;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Security\Core\User\UserProviderInterface,
    Symfony\Component\Security\Core\User\UserInterface,
    Symfony\Component\Security\Core\Exception\UsernameNotFoundException,
    Symfony\Component\Security\Core\Exception\UnsupportedUserException,
    SocialNetwork\API\OM\User;
use SocialNetwork\API\Provider\Criteria;

class UserProvider implements UserProviderInterface{

    protected $userProvider, $roleProvider, $groupProvider ,$parameters;

    public function __construct( $container )
    {
        $this->userProvider = $container->get('API.UserProvider');
        $this->groupProvider = $container->get('API.GroupProvider');
        $this->roleProvider = $container->get('API.RoleProvider');
        $this->parameters = $container->get('sensio.distribution.webconfigurator')->getParameters();
    }

    public function loadUserByUsername( $uid )
    {
        $u = $this->userProvider->getAuthentication( $uid );

        if(!$u)
            throw new UsernameNotFoundException(sprintf('User "%s" not found', $uid));



        $user = new User();
        $user->setAttributes($u);
        $user->setUsername($u['uid']);
        $user->setPassword( $u['password'] );



        $r = array();
//        if($u['uid'] == $this->parameters['admin-uid'])
//        {
//            $c = new Criteria();
//            $roles = $this->roleProvider->find($c , array('uid') );
//        }
//        else
        $roles = $this->roleProvider->getUserRoles( $u['uid'] , array('uid') );

        foreach($roles as $v)
            $r[] = $v['uid'];

        $user->setRoles( $r );

        $r = array();
        if(isset( $u['gidnumber'] ))
        {
            $group = $this->groupProvider->get( $u['gidnumber'] , array('uid') );
            $r[] = $group['uid'];

            $groups =  $this->groupProvider->getUserGroups( $u['uid'] , array('uid') );

            foreach($groups as $v)
                $r[] = $v['uid'];
        }

        $user->setGroups( $r );



        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if(!$user instanceof User)
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));

        return $this->loadUserByUsername( $user->getAttribute('uid') ); /* TODO: Resolver esse problema */
    }

    public function supportsClass($class)
    {
        return (bool) ($class === 'SocialNetwork\API\OM\User');
    }

}