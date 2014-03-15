<?php

use Composer\Package\Package;

class ComposerTestCase extends \PHPUnit_Framework_TestCase
{
    private static $composer;

    public function getComposer()
    {
        if (null === self::$composer) {
            self::$composer = new \Composer\Composer();
            $this->stubComposer();
        }

        return self::$composer;
    }

    private function stubComposer()
    {
        $localRepository = new \Composer\Repository\InstalledArrayRepository($this->getInstalledPackages());

        $composer = $this->getComposer();
        $config = $this->getComposerConfig();
        $composer->setConfig($config);
        $composer->setRepositoryManager($this->createRepositoryManager($config));
        $composer->getRepositoryManager()->setLocalRepository($localRepository);
        $composer->setInstallationManager(new \Composer\Installer\InstallationManager());
        $composer->getInstallationManager()->addInstaller(new \Composer\Installer\LibraryInstaller(new \Composer\IO\NullIO(), $composer, 'symfony-bundle'));
    }

    private function createRepositoryManager(\Composer\Config $config)
    {
        $manager = new \Composer\Repository\RepositoryManager(new \Composer\IO\NullIo(), $config);
        $manager->addRepository(new \Composer\Repository\ArrayRepository(array()));

        return $manager;
    }
    
    private function getComposerConfig()
    {
        $config = new \Composer\Config();
        $config->merge(array('config' => array(
            'vendor-dir', __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures/vendor',
        )));
        return $config;
    }

    public function getInstalledPackages()
    {
        $fooPackage = new Package('acme/foo-bundle', 'dev-master', '9999999-dev');
        $fooPackage->setAutoload(array(
            'psr-0' => array(
                'Acme\\FooBundle' => '',
            ),
        ));
        $fooPackage->setType('symfony-bundle');

        $barPackage = new Package('acme/bar-bundle', 'dev-master', '9999999-dev');
        $barPackage->setAutoload(array(
            'psr-0' => array(
                'Acme\\BarBundle' => 'src/',
            ),
        ));
        $barPackage->setType('symfony-bundle');

        $fooBarPackage = new Package('acme/foobar-bundle', 'dev-master', '9999999-dev');
        $fooBarPackage->setAutoload(array(
            'psr-4' => array(
                'Acme\\FooBarBundle\\' => '',
            ),
        ));
        $fooBarPackage->setType('symfony-bundle');

        return array(
            $fooPackage,
            $barPackage,
            $fooBarPackage,
        );
    }
}
