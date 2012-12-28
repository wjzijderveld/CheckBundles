<?php
namespace WillemJan;

use Composer\Scripts\Event;

/**
 * Description of CheckBundles
 *
 * @author willemjan
 */
class CheckBundles {
    
    static public function postUpdate(Event $event) 
    {
        $self = new self;
        $self->run();
    }

    public function run() 
    {
        if (!class_exists('AppKernel')) {
            throw new \Exception('CheckBundles currently only works with AppKernel');
        }
        
        echo $bundleContent = \ReflectionMethod::export('AppKernel', 'registerBundles');
    }
}

?>
