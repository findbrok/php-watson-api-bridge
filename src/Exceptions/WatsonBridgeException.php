<?php

namespace FindBrok\WatsonBridge\Exceptions;

use Exception;
use RuntimeException;

class WatsonBridgeException extends RuntimeException
{
    /**
     * Default error message.
     *
     * @var string
     */
    protected $message = 'An error occurred while performing request to Watson';

    /**
     * Create a new instance of WatsonBridgeException.
     *
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = '', $code = 400, Exception $previous = null)
    {
        //Format message
        $message = 'Watson Bridge: '.(($message != '') ? $message : $this->message);
        //Call parent exception
        parent::__construct($message, $code, $previous);
    }
}
