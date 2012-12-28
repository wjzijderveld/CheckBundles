CheckBundles
============

A script to check which Symfony Bundles there are installerd thru composer, but are not active in AppKernel.

Installation
============
The package is not yet on packagist, so you need to add a manual repository.

    "repositories": [
        {
            "type": "vcs",
            "url": "git://github.com/wjzijderveld/CheckBundles.git"
        }
    ]
	
Also, because scripts are not executed from nested repositories. You need to add the script:

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

ToDo
====

* Test if it actually works when installed thru composer
* Write *more* tests (sorry @grmpyprogrammer)
* Submit to packagist
* ...