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
namespace WillemJan\CheckBundles\Util;

/**
 * Description of ComposerHelperTest
 *
 * @author willemjan
 */
class ComposerHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Composer\Composer|PHPUnit_Framework_MockObject */
    protected $composerMock;
    
    /** @var \Composer\Repository\InstalledRepositoryInterface|PHPUnit_Framework_MockObject */
    protected $localRepository;
    
    public function setUp()
    {
        $this->composerMock = $this->getMock('\Composer\Composer', array('getInstallationManager'));
        $this->localRepository = $this->getMock('\Composer\Repository\InstalledArrayRepository', array('getPackages'));
    }
    
    /**
     * @return \WillemJan\CheckBundles\Util\ComposerHelper
     */
    protected function getHelper()
    {
        return new ComposerHelper($this->composerMock);
    }
    
    /**
     * @param string $type
     * @param string $targetDir
     * @return \Composer\Package\CompletePackage
     */
    protected function createPackageMock($type, $targetDir)
    {
        $package = $this->getMock('Composer\Package\CompletePackage', array('getTargetDir', 'getType'), array(), '', false);
        $package->expects($this->any())
                ->method('getType')
                ->will($this->returnValue($type));
        
        $package->expects($this->any())
                ->method('getTargetDir')
                ->will($this->returnValue($targetDir));
        
        return $package;
    }
    
    protected function getExamplePackages()
    {
        $demoBundle = $this->createPackageMock('symfony-bundle', 'Acme/DemoBundle');
        $packages = array(
            $this->createPackageMock('library', 'foo/testLibrary'),
            $this->createPackageMock('symfony-bundle', 'Doctrine/ORM/DoctrineBundle'),
            $demoBundle,
            new \Composer\Package\AliasPackage($demoBundle, '1.0.0', 'v1.0.0'),
        );
        
        return $packages;
    }
    
    public function testGetBundleName()
    {
        $helper = $this->getHelper();
        $package = $this->getMock('\Composer\Package\CompletePackage', array('getTargetDir'), array(), '', false);
        $package->expects($this->any())
                ->method('getTargetDir')
                ->will($this->returnValue('Foo/Bar/FooBarBundle'));
        
        $method = new \ReflectionMethod($helper, 'getBundleName');
        $method->setAccessible(true);
        $this->assertEquals('Foo\Bar\FooBarBundle\FooBarBundle', $method->invoke($helper, $package, 'FooBarBundle.php'));
        $this->assertEquals('Foo\Bar\FooBarBundle\TestBundle', $method->invoke($helper, $package, 'TestBundle.php'));
    }
    
    public function testGetConfiguredSymfonyBundles()
    {
        $this->localRepository->expects($this->any())
                ->method('getPackages')
                ->will($this->returnValue($this->getExamplePackages()));
        
        /** @var $helper \WillemJan\CheckBundles\Util\ComposerHelper|\PHPUnit_Framework_MockObject */
        $helper = $this->getMock('WillemJan\CheckBundles\Util\ComposerHelper', array('findBundleFiles', 'getInstalledRepository'), array($this->composerMock));
        $helper->expects($this->any())
                ->method('findBundleFiles')
                ->will($this->returnValue(array('FooBarBundle.php')));
        $helper->expects($this->any())
                ->method('getInstalledRepository')
                ->will($this->returnValue($this->localRepository));
                
        $bundles = $helper->getConfiguredSymfonyBundles();
        
        $this->assertEquals(array(
            'Doctrine\ORM\DoctrineBundle\FooBarBundle',
            'Acme\DemoBundle\FooBarBundle',
        ), $bundles);
    }
}