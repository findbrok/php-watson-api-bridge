<?php

namespace FindBrok\WatsonBridge;

use Carbon\Carbon;

class Token
{
    /**
     * Username for which the token belongs.
     *
     * @var string
     */
    protected $username;

    /**
     * The token payload.
     *
     * @var array
     */
    protected $payLoad;

    /**
     * Create a new instance of the Token class.
     *
     * @param string $username
     * @param array  $payLoad
     */
    public function __construct($username, $payLoad = [])
    {
        //Set Username for token
        $this->username = $username;
        //Have payload to set
        if (! empty($payLoad)) {
            $this->payLoad = $payLoad;
        } else {
            //Load from file
            $this->payLoad = $this->loadPayLoadFromFile();
        }
    }

    /**
     * Check if token is loaded in class.
     *
     * @return bool
     */
    public function hasPayLoad()
    {
        return ! empty($this->payLoad);
    }

    /**
     * Check that token file exists.
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists(__DIR__.'/Storage/'.'token-'.$this->username.'.json');
    }

    /**
     * Check that token is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->hasPayLoad() && ($this->payLoad['created'] + $this->payLoad['expires_in']) < Carbon::now()
                                                                                                         ->format('U');
    }

    /**
     * Check that the token is not expired.
     *
     * @return bool
     */
    public function isNotExpired()
    {
        return ! $this->isExpired();
    }

    /**
     * Check if token is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->exists() && $this->isNotExpired();
    }

    /**
     * Saves a token.
     *
     * @return bool
     */
    public function save()
    {
        // No payload to save.
        if (! $this->hasPayLoad()) {
            return false;
        }

        // Save the token.
        return (bool) file_put_contents($this->getFilePath(), collect($this->payLoad)->toJson(), LOCK_EX);
    }

    /**
     * Get the token file path.
     *
     * @return string
     */
    public function getFilePath()
    {
        return __DIR__.'/Storage/token-'.$this->username.'.json';
    }

    /**
     * Load payload from file.
     *
     * @return array
     */
    public function loadPayLoadFromFile()
    {
        // Not found.
        if (! $this->exists()) {
            // We return empty array.
            return [];
        }

        // Load content from file.
        return json_decode(file_get_contents($this->getFilePath()), true);
    }

    /**
     * Get the payload.
     *
     * @return array
     */
    public function getPayLoad()
    {
        return $this->payLoad;
    }

    /**
     * Get the token.
     *
     * @return string|null
     */
    public function getToken()
    {
        return collect($this->payLoad)->get('token');
    }

    /**
     * Update the token.
     *
     * @param string $token
     *
     * @return bool
     */
    public function updateToken($token)
    {
        // Update Payload.
        $this->payLoad = [
            'token'      => $token,
            'expires_in' => 3600,
            'created'    => Carbon::now()->format('U'),
        ];

        // Save token.
        return $this->save();
    }
}
