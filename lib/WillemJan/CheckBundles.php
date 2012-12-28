<?php
namespace WillemJan;

use Composer\Script\Event;

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
    
    private $kernelName = 'AppKernel';
    
    public function __construct(Event $event) {
        $this->event = $event;
        $this->composer = $this->event->getComposer();
    }
    
    static public function postPackageUpdate(Event $event) 
    {
        $self = new self($event);
        $self->run();
    }

    public function run() 
    {
        $this->event->getIO()->write(sprintf('Checking for bundles that are installed with composer, but not activated in <comment>%s</comment>', $this->kernelName));
        
        $currentActiveBundles = $this->getActiveBundles();
        list($configuredBundles, $configuredDevBundles) = $this->getConfiguredBundles();
        
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
    
    protected function getActiveBundles() 
    {
        $bundles = array();
        $extra = $this->event->getComposer()->getPackage()->getExtra();
        if (isset($extra['symfony-app-dir'])) {
            $appDir = getcwd() . DIRECTORY_SEPARATOR . $extra['symfony-app-dir'];
        } else {
            $appDir = getcwd() . DIRECTORY_SEPARATOR . 'app';
        }
        
        $kernelFile = sprintf($appDir . DIRECTORY_SEPARATOR . '%s.php', $this->kernelName);
        
        if (!file_exists($kernelFile)) {
            throw new \Exception(sprintf('No %s.php found in %s', $this->kernelName, $appDir));
        }
        require $kernelFile;
        
        $kernel = new $this->kernelName('dev', true);
        $kernel->boot();
        foreach ($kernel->getBundles() as $bundle) {
            $refClass = new \ReflectionClass($bundle);
            $bundles[] = $refClass->getName();
        }
        
        return $bundles;
        
        /**
        $refMethod = new \ReflectionMethod($this->kernelName, 'registerBundles');
        $from = $refMethod->getStartLine();
        $end = $refMethod->getEndLine();
        
        $kernelLines = file($kernelFile);
        $bundleLines = array_slice($kernelLines, $from, $end);
        
        var_dump($bundleLines);        
        */
    }
    
    protected function getConfiguredBundles()
    {
        $symfonyBundles = $symfonyDevBundles = array();
        
        $installedRepo = $this->composer->getRepositoryManager()->getLocalRepository();
        foreach ($installedRepo->getPackages() as $package) {
            /** 
             * @var $package \Composer\Package\CompletePackage
             */
            if ($package->getType() == 'symfony-bundle') {
                $installPath = $this->composer->getInstallationManager()->getInstallPath($package);
                $bundleFiles = glob($installPath . '/*Bundle.php');
                if (count($bundleFiles)) {
                    $bundleName = str_replace('/', '\\', $package->getTargetDir()) . '\\' . substr(basename($bundleFiles[0]), 0, -4);
                    $symfonyBundles[] = $bundleName;
                }
            }
        }
        
        return array($symfonyBundles, $symfonyDevBundles);
    }
            
}

?>
