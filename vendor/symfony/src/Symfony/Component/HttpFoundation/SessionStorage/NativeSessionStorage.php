<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\SessionStorage;

/**
 * NativeSessionStorage.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class NativeSessionStorage implements SessionStorageInterface
{
    static protected $sessionIdRegenerated = false;
    static protected $sessionStarted       = false;

    protected $options;

    /**
     * Available options:
     *
     *  * name:     The cookie name (_SESS by default)
     *  * id:       The session id (null by default)
     *  * lifetime: Cookie lifetime
     *  * path:     Cookie path
     *  * domain:   Cookie domain
     *  * secure:   Cookie secure
     *  * httponly: Cookie http only
     *
     * The default values for most options are those returned by the session_get_cookie_params() function
     *
     * @param array $options  An associative array of options
     */
    public function __construct(array $options = array())
    {
        $cookieDefaults = session_get_cookie_params();

        $this->options = array_merge(array(
            'name'          => '_SESS',
            'lifetime'      => $cookieDefaults['lifetime'],
            'path'          => $cookieDefaults['path'],
            'domain'        => $cookieDefaults['domain'],
            'secure'        => $cookieDefaults['secure'],
            'httponly'      => isset($cookieDefaults['httponly']) ? $cookieDefaults['httponly'] : false,
        ), $options);

        session_name($this->options['name']);
    }

    /**
     * Starts the session.
     */
    public function start()
    {
        if (self::$sessionStarted) {
            return;
        }

        session_set_cookie_params(
            $this->options['lifetime'],
            $this->options['path'],
            $this->options['domain'],
            $this->options['secure'],
            $this->options['httponly']
        );

        // disable native cache limiter as this is managed by HeaderBag directly
        session_cache_limiter(false);

        if (!ini_get('session.use_cookies') && $this->options['id'] && $this->options['id'] != session_id()) {
            session_id($this->options['id']);
        }

        session_start();

        self::$sessionStarted = true;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        if (!self::$sessionStarted) {
            throw new \RuntimeException('The session must be started before reading its ID');
        }

        return session_id();
    }

    /**
     * Reads data from this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string $key A unique key identifying your data
     *
     * @return mixed Data associated with the key
     */
    public function read($key, $default = null)
    {
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }

    /**
     * Removes data from this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param  string $key  A unique key identifying your data
     *
     * @return mixed Data associated with the key
     */
    public function remove($key)
    {
        $retval = null;

        if (isset($_SESSION[$key])) {
            $retval = $_SESSION[$key];
            unset($_SESSION[$key]);
        }

        return $retval;
    }

    /**
     * Writes data to this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string $key   A unique key identifying your data
     * @param mixed  $data  Data associated with your key
     *
     */
    public function write($key, $data)
    {
        $_SESSION[$key] = $data;
    }

    /**
     * Regenerates id that represents this storage.
     *
     * @param  Boolean $destroy Destroy session when regenerating?
     *
     * @return Boolean True if session regenerated, false if error
     *
     */
    public function regenerate($destroy = false)
    {
        if (self::$sessionIdRegenerated) {
            return;
        }

        session_regenerate_id($destroy);

        self::$sessionIdRegenerated = true;
    }
}
