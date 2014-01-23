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

class KernelHelper
{

    /** @var string */
    protected $projectRoot;

    /** @var string */
    protected $appDir = 'app';

    /** @var string */
    protected $webDir = 'web';

    public function __construct($projectRoot)
    {
        $this->projectRoot = $projectRoot;
    }

    /**
     * @return string
     */
    public function getProjectRoot()
    {
        return $this->projectRoot;
    }

    /**
     * @param string $projectRoot
     */
    public function setProjectRoot($projectRoot)
    {
        $this->projectRoot = $projectRoot;
    }

    /**
     * Creates a given kernel
     *
     * @param  string                               $kernelName
     * @param  string                               $environment
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return \Symfony\Component\HttpKernel\Kernel
     */
    public function createKernel($kernelName, $environment = 'dev')
    {
        if (!class_exists($kernelName)) {
            $appDir = $this->projectRoot . DIRECTORY_SEPARATOR . $this->appDir;
            $kernelFile = $appDir . DIRECTORY_SEPARATOR . $kernelName . '.php';
            if (!file_exists($kernelFile)) {
                throw new \InvalidArgumentException(sprintf('Kernel %s not found in %s', $kernelName, $appDir));
            }

            require $kernelFile;

            if (!class_exists($kernelName)) {
                throw new \RuntimeException(sprintf('Kernel %s not found in file %s', $kernelName, $kernelFile));
            }
        }

        $kernel = new $kernelName($environment, false);

        return $kernel;
    }

    /**
     * Collects all bundles
     *
     * @param  array $kernels
     * @return array
     */
    public function getBundlesForKernels(array $kernels)
    {
        $bundles = array();
        foreach ($kernels as $name => $environments) {
            $environments = (array) $environments;
            foreach ($environments as $environment) {
                $kernel = $this->createKernel($name, $environment);
                $registeredBundles = array();
                foreach ($kernel->registerBundles() as $bundle) {
                    $registeredBundles[] = get_class($bundle);
                }
                $bundles = array_merge($bundles, $registeredBundles);
            }
        }

        return $bundles;
    }

    /**
     * @return string
     */
    public function getAppDir()
    {
        return $this->appDir;
    }

    /**
     * @param string $appDir
     */
    public function setAppDir($appDir)
    {
        $this->appDir = $appDir;
    }

    /**
     * @return string
     */
    public function getWebDir()
    {
        return $this->webDir;
    }

    /**
     * @param string $appDir
     */
    public function setWebDir($webDir)
    {
        $this->webDir = $webDir;
    }

}
