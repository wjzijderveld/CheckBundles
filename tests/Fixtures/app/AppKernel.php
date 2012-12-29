<?php

/*
 * This file is part of the CheckBundles
 * 
 * 
 * (c) Willem-Jan Zijderveld <wjzijderveld@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Description of AppKernel
 *
 * @author willemjan
 */
class AppKernel extends Symfony\Component\HttpKernel\Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new FooBundle(),
            new BarBundle(),
        );
        
        if ($this->getEnvironment() == 'dev') {
            $bundles[] = new FooBarBundle();
        }
        
        return $bundles;
    }
 
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        
    }
}