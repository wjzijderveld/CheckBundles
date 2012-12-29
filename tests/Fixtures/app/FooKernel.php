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
 * Description of FooKernel
 *
 * @author willemjan
 */
class FooKernel extends Symfony\Component\HttpKernel\Kernel
{
    public function registerBundles()
    {
        return array();
    }
 
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        
    }
}