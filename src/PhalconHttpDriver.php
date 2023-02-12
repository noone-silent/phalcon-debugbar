<?php

namespace Nin\Debugbar;

use Phalcon\DI\Injectable;
use DebugBar\HttpDriverInterface;

class PhalconHttpDriver extends Injectable implements HttpDriverInterface
{
    /**
     * {@inheritDoc}
     */
    public function setHeaders(array $headers)
    {
        if ($this->response !== null) {
            foreach ($headers as $key => $value) {
                $this->response->setHeader($key, $value);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isSessionStarted()
    {
        if (!$this->session->exists()) {
            $this->session->start();
        }
        return $this->session->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function setSessionValue($name, $value)
    {
        $this->session->set($name, $value);
    }

    /**
     * Checks if a value is in the session
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasSessionValue($name)
    {
        return $this->session->has($name);
    }

    /**
     * Returns a value from the session
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getSessionValue($name)
    {
        return $this->session->get($name);
    }

    /**
     * Deletes a value from the session
     *
     * @param string $name
     */
    public function deleteSessionValue($name)
    {
        $this->session->remove($name);
    }
}
