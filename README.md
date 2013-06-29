CheckBundles
============

A script to check which Symfony Bundles there are installerd thru composer, but are not active in AppKernel.

[![Build Status](https://travis-ci.org/wjzijderveld/CheckBundles.png?branch=master)](https://travis-ci.org/wjzijderveld/CheckBundles)

Installation
============
The package is on packagist, so the easiest way is to add it to your composer.json:

	"require": {
		"wjzijderveld/check-bundles": "dev-master"
	}
	
If you can't or don't want to use packagist, you need to add a manual repository.

    "repositories": [
        {
            "type": "vcs",
            "url": "git://github.com/wjzijderveld/CheckBundles.git"
        }
    ]
	
Because scripts are not executed from nested repositories, you need to add the script itself to your composer.json:

    "scripts": {
        "post-install-cmd": [
            "WillemJan\\CheckBundles\\Composer\\CheckBundles::postPackageUpdate"
        ],
        "post-update-cmd": [
            "WillemJan\\CheckBundles\\Composer\\CheckBundles::postPackageUpdate"
        ]
    }
	
By default, this library checks AppKernel in dev environment.
If you have a different kernel then the default AppKernel, or if you want to check only prod-env.
Or if you have multiple kernels you'd like to check, you can use the _checkbundles-kernels_ extra attribute:

    "extra": {
		"checkbundles-kernels": {
			"FooKernel": "dev",
			"BarKernel": "prod",
			"TmpKernel": "test"
		}
	}

You can also choose to ignore some bundles, you might use a bundle without the need to define it in any of your Kernels.

    "extra": {
        "checkbundles-ignore": ["Acme\IgnoredFooBundle\AcmeIgnoredFooBundle"]
    }

ToDo
====

* Write a functional test that actually uses a composer.json file
* ...

Feedback
========
I would really like some feedback, so feel free to create a issue/PR or email me.