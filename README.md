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
            "WillemJan\\CheckBundles::postPackageUpdate"
        ],
        "post-update-cmd": [
            "WillemJan\\CheckBundles::postPackageUpdate"
        ]
    }

TODO
----
* Actually add the license (+docblocks)
* Test if it actually works when installed thru composer
* Detect Kernel name, instead of hardcoded look voor AppKernel (or make it configurable)
* Write tests (sorry @grmpyprogrammer)
* Submit to packagist
* ...