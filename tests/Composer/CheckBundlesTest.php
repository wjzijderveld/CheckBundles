<?php
/*
 * This file is part of the CheckBundles library
 * 
 * 
 * (c) Willem-Jan Zijderveld <wjzijderveld@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WillemJan\CheckBundles\Composer;

class CheckBundlesTest extends \PHPUnit_Framework_TestCase 
{
    /** @var Composer\Composer|PHPUnit_Framework_MockObject */
    protected $composerMock;
    
    /** @var Composer\Package\PackageInterface|PHPUnit_Framework_MockObject */
    protected $packageMock;
    
    /** @var Composer\Script\Event|PHPUnit_Framework_MockObject */
    protected $composerEventMock;
    
    public function setUp()
    {
        $this->composerMock = $this->getMock('Composer\Composer', array('getPackage'));
        $this->packageMock = $this->getMockForAbstractClass('Composer\Package\PackageInterface');
        
        $this->composerMock->expects($this->any())
                ->method('getPackage')
                ->will($this->returnValue($this->packageMock));
        
        $this->composerEventMock = $this->getMock('Composer\Script\Event', array('getComposer'), array(
            'postPackageUpdate',
            $this->composerMock,
            $this->getMockForAbstractClass('Composer\IO\IOInterface'),
            true,
        ));
        
        $this->composerEventMock->expects($this->any())
            ->method('getComposer')
            ->will($this->returnValue($this->composerMock));
    }
    
    protected function mockComposerObjects($packageExtraValue = null)
    {
        $this->composerMock = $this->getMock('Composer\Composer', array('getPackage'));
        $this->packageMock = $this->getMockForAbstractClass('Composer\Package\PackageInterface');
        $this->packageMock->expects($this->any())
                ->method('getExtra')
                ->will($this->returnValue($packageExtraValue));
        
        $this->composerMock->expects($this->any())
                ->method('getPackage')
                ->will($this->returnValue($this->packageMock));
        
        $this->composerEventMock = $this->getMock('Composer\Script\Event', array('getComposer'), array(
            'postPackageUpdate',
            $this->composerMock,
            $this->getMockForAbstractClass('Composer\IO\IOInterface'),
            true,
        ));
        
        $this->composerEventMock->expects($this->any())
                ->method('getComposer')
                ->will($this->returnValue($this->composerMock));
    }
    
    public function testGetDefaultKernelHelper()
    {
        $this->mockComposerObjects();
        
        $checkBundles = new CheckBundles($this->composerEventMock);
        
        $kernelHelper = $checkBundles->getKernelHelper();
        
        $this->assertInstanceOf('WillemJan\CheckBundles\Util\KernelHelper', $kernelHelper);
        $this->assertEquals('app', $kernelHelper->getAppDir());
        $this->assertEquals('web', $kernelHelper->getWebDir());
    }
    
    public function testConfiguredKernelHelper()
    {
        $this->mockComposerObjects(array(
            'symfony-app-dir'   => 'foo',
            'symfony-web-dir'   => 'bar',
        ));
        
        $checkBundles = new CheckBundles($this->composerEventMock);        
        $kernelHelper = $checkBundles->getKernelHelper();

        $this->assertInstanceOf('WillemJan\CheckBundles\Util\KernelHelper', $kernelHelper);
        $this->assertEquals('foo', $kernelHelper->getAppDir());
        $this->assertEquals('bar', $kernelHelper->getWebDir());
    }
    
    public function testDetermineKernels()
    {
        $checkBundles = new CheckBundles($this->composerEventMock);
        $this->assertEquals(array('AppKernel' => 'dev'), $checkBundles->getKernels());
        
        $this->mockComposerObjects(array(
            'checkbundles-kernels' => array(
                'FooKernel' => 'dev',
                'BarKernel' => array('dev', 'prod'),
            ),
        ));
        
        $checkBundles2 = new CheckBundles($this->composerEventMock);
        
        $this->assertEquals(array(
            'FooKernel' => 'dev', 
            'BarKernel' => array('dev', 'prod'),
        ), $checkBundles2->getKernels());
    }

}