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

use Composer\Package\RootPackage;
use Composer\Package\PackageInterface;
use Composer\Package\Package;

/**
 * Description of ComposerHelperTest
 *
 * @author willemjan
 */
class ComposerHelperTest extends \ComposerTestCase
{
    /** @var \Composer\Composer|\PHPUnit_Framework_MockObject_MockObject */
    protected $composerMock;
    
    /** @var \Composer\Repository\InstalledRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $localRepository;
    
    public function setUp()
    {
        $this->composerMock = $this->getMock('\Composer\Composer', array('getInstallationManager', 'getPackage'));
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
     * @param array $autoload
     * @return \Composer\Package\Package
     */
    protected function createPackage($name, $type, array $autoload)
    {
        $package = new Package($name, 'dev-master', '9999999-dev');
        $package->setType($type);
        $package->setAutoload($autoload);
        
        return $package;
    }
    
    protected function getExamplePackages()
    {
        $demoBundle = $this->createPackage('acme/demo-bundle', 'symfony-bundle', array('psr-0' => array('Acme\\DemoBundle' => '')));
        $packages = array(
            $this->createPackage('foo/test-library', 'library', array('psr-0' => array('' => 'foo/testLibrary'))),
            $this->createPackage('doctrine/doctrine-bundle', 'symfony-bundle', array('psr-0' => array('Doctrine\\ORM\\DoctrineBundle' => ''))),
            $demoBundle,
            new \Composer\Package\AliasPackage($demoBundle, '1.0.0', 'v1.0.0'),
        );
        
        return $packages;
    }
    
    public function testGetBundleName()
    {
        $package = $this->createPackage('foo/bar-bundle', 'symfony-bundle', array('psr-0' => array('Foo\\Bar\\FooBarBundle' => '')));
        $helper = $this->getHelper();
        
        $this->assertEquals('Foo\Bar\FooBarBundle\FooBarBundle', $helper->getFQCN($package, 'FooBarBundle.php'));
        $this->assertEquals('Foo\Bar\FooBarBundle\TestBundle', $helper->getFQCN($package, 'TestBundle.php'));
    }
    
    public function testGetConfiguredSymfonyBundles()
    {
        $this->localRepository->expects($this->any())
                ->method('getPackages')
                ->will($this->returnValue($this->getExamplePackages()));
        
        /** @var $helper \WillemJan\CheckBundles\Util\ComposerHelper|\PHPUnit_Framework_MockObject_MockObject */
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

    public function testIgnoredBundles()
    {
        $packages = array(
            $this->createPackage('foo/foo-bundle', 'symfony-bundle', array('psr-0' => array('Acme\FooBundle' => ''))),
            $this->createPackage('acme/library-bundle', 'symfony-bundle', array('psr-0' => array('Acme\LibraryBundle' => ''))),
        );

        $rootPackage = new RootPackage('DemoPackage', 'dev-master', '1.x-dev');
        $rootPackage->setExtra(array(
            'checkbundles-ignore' => array('Acme\LibraryBundle\AcmeLibraryBundle')
        ));

        $this->composerMock->expects($this->any())
            ->method('getPackage')
            ->will($this->returnValue($rootPackage));

        $this->localRepository->expects($this->any())
            ->method('getPackages')
            ->will($this->returnValue($packages));

        $kernelHelper = $this->getMockBuilder('WillemJan\Util\KernelHelper')
            ->disableOriginalConstructor()
            ->setMethods(array('getBundlesForKernels'))
            ->getMock();

        $kernelHelper->expects($this->any())
            ->method('getBundlesForKernels')
            ->will($this->returnValue(array('Acme\FooBundle\AcmeFooBundle')));

        $composerHelper = $this->getMockBuilder('WillemJan\CheckBundles\Util\ComposerHelper')
            ->setConstructorArgs(array($this->composerMock))
            ->setMethods(array('getInstalledRepository', 'findBundleFiles'))
            ->getMock();

        $composerHelper->expects($this->any())
            ->method('findBundleFiles')
            ->will($this->returnCallback(function($package) use($packages) {
                switch ($package->getName()) {
                    case 'foo/foo-bundle':
                        return array('AcmeFooBundle.php');
                        break;
                    case 'acme/library-bundle':
                        return array('AcmeLibraryBundle.php');
                        break;
                }

                return array('ShouldNotHappenBundle.php');
            }));

        $composerHelper->expects($this->any())
            ->method('getInstalledRepository')
            ->will($this->returnValue($this->localRepository));

        $this->assertEquals(array(), $composerHelper->getNonActiveSymfonyBundles($kernelHelper->getBundlesForKernels()));
    }

    /**
     * @test
     * @dataProvider bundleNameProvider
     */
    public function it_should_determine_the_bundle_name_with_different_autoloaders($expectedBundleName, $package, $bundleFileName)
    {
        $composer = $this->getComposer();

        $composerHelper = new \WillemJan\CheckBundles\Util\ComposerHelper($composer);
        $this->assertEquals($expectedBundleName, $composerHelper->getFQCN($package, $bundleFileName));
    }

    public function bundleNameProvider()
    {
        $packages = $this->getComposer()->getRepositoryManager()->getLocalRepository()->getPackages();

        return array(
            array('Acme\FooBundle\AcmeFooBundle', $packages[0], $this->getInstallPath($packages[0]) . '/AcmeFooBundle.php'),
            array('Acme\BarBundle\AcmeBarBundle', $packages[1], $this->getInstallPath($packages[1]) . '/src/AcmeBarBundle.php'),
            array('Acme\FooBarBundle\AcmeFooBarBundle', $packages[2], $this->getInstallPath($packages[2]) . '/AcmeFooBarBundle.php'),

        );
    }

    protected function getInstallPath(PackageInterface $package)
    {
        return $this->getComposer()->getInstallationManager()->getInstallPath($package);
    }
}
