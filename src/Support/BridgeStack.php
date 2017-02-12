<?php

namespace FindBrok\WatsonBridge\Support;

use FindBrok\WatsonBridge\Bridge;
use Illuminate\Support\Collection;
use FindBrok\WatsonBridge\Exceptions\WatsonBridgeException;

class BridgeStack extends Collection
{
    /**
     * The Carpenter instance.
     *
     * @var Carpenter
     */
    protected $carpenter;

    /**
     * BridgeStack constructor.
     *
     * @param Carpenter $carpenter
     * @param array     $items
     */
    public function __construct(Carpenter $carpenter, $items = [])
    {
        $this->carpenter = $carpenter;

        parent::__construct($items);
    }

    /**
     * Mounts a Bridge on the stack.
     *
     * @param string $name
     * @param string $credential
     * @param string $service
     * @param string $authMethod
     *
     * @return $this
     */
    public function mountBridge($name, $credential, $service = null, $authMethod = 'credentials')
    {
        // Creates the Bridge.
        $bridge = $this->carpenter->constructBridge($credential, $service, $authMethod);

        // Save it under a name.
        $this->put($name, $bridge);

        return $this;
    }

    /**
     * Conjures a specific Bridge to use.
     *
     * @param string $name
     *
     * @throws WatsonBridgeException
     * @return Bridge
     */
    public function conjure($name)
    {
        // We must check if the Bridge does
        // exists.
        if (! $this->has($name)) {
            throw new WatsonBridgeException('The Bridge with name "'.$name.'" does not exist.');
        }

        return $this->get($name);
    }
}
