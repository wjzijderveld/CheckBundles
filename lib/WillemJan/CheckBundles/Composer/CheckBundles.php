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

use Composer\Script\Event;
use WillemJan\CheckBundles\Util\KernelHelper;
use WillemJan\CheckBundles\Util\ComposerHelper;

/**
 * Description of CheckBundles
 *
 * @author willemjan
 */
class CheckBundles {
    

    /**
     * @var \Composer\Script\Event
     */
    protected $event;
    
    /** @var \Composer\Composer; */
    protected $composer;
    
    /** @var array */
    protected $kernels = array('AppKernel' => 'dev');
    
    /** @var string */
    protected $projectRoot;
    
    public function __construct(Event $event) {
        $this->event = $event;
        $this->composer = $this->event->getComposer();
        
        $this->projectRoot = getcwd();
        $this->determineKernels();
    }
    
    static public function postPackageUpdate(Event $event) 
    {
        $self = new self($event);
        $self->run();
    }
    
    /**
     * @return KernelHelper
     */
    public function getKernelHelper()
    {
        $kernelHelper = new KernelHelper($this->projectRoot);
        
        $extra = $this->composer->getPackage()->getExtra();
        
        if (isset($extra['symfony-app-dir'])) {
            $kernelHelper->setAppDir($extra['symfony-app-dir']);
        }
        
        if (isset($extra['symfony-web-dir'])) {
            $kernelHelper->setWebDir($extra['symfony-web-dir']);
        }
        
        return $kernelHelper;
    }
    
    /**
     * @return \WillemJan\Util\ComposerHelper
     */
    public function getComposerHelper()
    {
        $composerHelper = new ComposerHelper($this->composer);
        
        return $composerHelper;
    }
    
    public function determineKernels()
    {
        $extra = $this->composer->getPackage()->getExtra();
        if (isset($extra['checkbundles-kernels'])) {
            $this->kernels = (array)$extra['checkbundles-kernels'];
        }
    }
    
    /**
     * @return array
     */
    public function getKernels()
    {
        return $this->kernels;
    }

    public function run()
    {
        $this->event->getIO()->write(sprintf('Checking for bundles that are installed with composer, but not activated in <comment>%s</comment>', join(', ', array_keys($this->kernels))));
        
        $kernelHelper = $this->getKernelHelper();
        $currentActiveBundles = $kernelHelper->getBundlesForKernels($this->getKernels());
        
        $composerHelper = $this->getComposerHelper();
        
        $configuredBundles = $composerHelper->getConfiguredSymfonyBundles();
        
        $nonActiveBundles = array();
        foreach ($configuredBundles as $bundle) {
            if (!in_array($bundle, $currentActiveBundles)) {
                $nonActiveBundles[] = $bundle;
            }
        }
        
        if (!count($nonActiveBundles)) {
            $this->event->getIO()->write('No inactive bundles found');
        } else {
            $this->event->getIO()->write(sprintf('Found <info>%d</info> inactive bundles:', count($nonActiveBundles)));
            foreach ($nonActiveBundles as $bundle) {
                $this->event->getIO()->write(sprintf('    <comment>%s</comment>', $bundle));
            }
        }   
    }
            
}

?>
