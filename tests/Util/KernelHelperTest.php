<?php

namespace WillemJan\CheckBundles\Util;

class KernelHelperTest extends \PHPUnit_Framework_TestCase 
{

    protected $fixtureDir;
    
    public function setUp()
    {
        $this->fixtureDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Fixtures';
    }
    
	public function testConstructor()
    {
        $helper = new KernelHelper(__DIR__);
        
        $this->assertEquals(__DIR__, $helper->getProjectRoot());
    }

    public function testCreateKernel()
    {
        $helper = new KernelHelper($this->fixtureDir);
        $kernel = $helper->createKernel('AppKernel', 'prod');
        
        $this->assertInstanceOf('AppKernel', $kernel);
        $this->assertEquals('prod', $kernel->getEnvironment());
        
        $kernel2 = $helper->createKernel('FooKernel');
        $this->assertInstanceOf('FooKernel', $kernel2);
        $this->assertEquals('dev', $kernel2->getEnvironment());
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMissingKernel()
    {
        $helper = new KernelHelper($this->fixtureDir);
        $helper->createKernel('BarKernel');
    }
    
    public function testGetBundlesForKernel()
    {
        require __DIR__ . '/../Fixtures/FooBundles.php';
        
        $helper = new KernelHelper($this->fixtureDir);
                
        $this->assertEquals(array(
            'Acme\FooBundle',
            'Acme\BarBundle',
        ), $helper->getBundlesForKernels(array('AppKernel' => 'prod')));
        
        $this->assertEquals(array(
            'Acme\FooBundle',
            'Acme\BarBundle',
            'Acme\FooBarBundle',
        ), $helper->getBundlesForKernels(array('AppKernel' => 'dev')));
    }
}

