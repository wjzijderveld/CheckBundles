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

namespace WillemJan\CheckBundles\Util;

use Composer\Composer;
use Composer\Package\AliasPackage;
use Composer\Package\PackageInterface;

class ComposerHelper 
{
    /** @var \Composer\Composer $composer */
    protected $composer;
    
    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }
    
    /**
     * @return \Composer\Repository\InstalledRepositoryInterface
     */
    protected function getInstalledRepository()
    {
        return $this->composer->getRepositoryManager()->getLocalRepository();
    }
    
    protected function findBundleFiles(PackageInterface $package)
    {
        $installPath = $this->composer->getInstallationManager()->getInstallPath($package);
        return glob($installPath . '/*Bundle.php');
    }
    
    /**
     * @return array
     */
    public function getConfiguredSymfonyBundles()
    {
        $symfonyBundles = array();
        
        $installedRepo = $this->getInstalledRepository();
        foreach ($installedRepo->getPackages() as $package) {
            /** 
             * @var $package \Composer\Package\CompletePackage
             */

            if ($package instanceOf AliasPackage) {
                continue;
            }

            if ($package->getType() == 'symfony-bundle') {
                $bundleFiles = $this->findBundleFiles($package);
                if (count($bundleFiles)) {
                    // Take the first first we find
                    // Not sure how to find the Bundle's name any other way
                    $bundleName = $this->getBundleName($package, $bundleFiles[0]);
                    $symfonyBundles[] = $bundleName;
                }
            }
        }
        
        return $symfonyBundles;
    }
    
    /**
     * Try to determine the package name with the filename and package target directory
     * Don't really think this is 100% correct, but works for now
     * 
     * @param \Composer\Package\PackageInterface $package
     * @param type $bundleFileName
     * @return string
     */
    protected function getBundleName(PackageInterface $package, $bundleFileName) 
    {
        return str_replace('/', '\\', $package->getTargetDir()) . '\\' . substr(basename($bundleFileName), 0, -4);
    }
}
