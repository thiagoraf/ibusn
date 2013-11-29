<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new SocialNetwork\API\SocialNetworkAPI(),
            new SocialNetwork\Bundle\IndexBundle\SocialNetworkIndexBundle(),
            new SocialNetwork\Bundle\HomeBundle\SocialNetworkHomeBundle(),
            new SocialNetwork\Bundle\ProfileBundle\SocialNetworkProfileBundle(),
            new SocialNetwork\Bundle\LoginBundle\SocialNetworkLoginBundle(),
            new Sensio\Bundle\DistributionBundle\SensioDistributionBundle(),
            new SocialNetwork\Bundle\FriendBundle\SocialNetworkFriendBundle(),
            new SocialNetwork\Bundle\FollowBundle\SocialNetworkFollowBundle(),
            new SocialNetwork\Bundle\GroupBundle\SocialNetworkGroupBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            //$bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getCacheDir()
    {
        return '/tmp/my_cache/' . $this->environment;
    }

    public function getLogDir()
    {
        return '/tmp/my_logs';
    }
}
