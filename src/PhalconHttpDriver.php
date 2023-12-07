<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar;

use DebugBar\HttpDriverInterface;
use Phalcon\DI\Injectable;
use Phalcon\Http\ResponseInterface;

class PhalconHttpDriver extends Injectable implements HttpDriverInterface
{
    /**
     * {@inheritDoc}
     */
    public function setHeaders(array $headers): void
    {
        if ($this->response instanceof ResponseInterface === false) {
            return;
        }

        foreach ($headers as $key => $value) {
            $this->response->setHeader($key, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isSessionStarted(): bool
    {
        if (!$this->session->exists()) {
            $this->session->start();
        }
        return $this->session->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function setSessionValue($name, $value): void
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
    public function hasSessionValue($name): bool
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
    public function getSessionValue($name): mixed
    {
        return $this->session->get($name);
    }

    /**
     * Deletes a value from the session
     *
     * @param string $name
     */
    public function deleteSessionValue($name): void
    {
        $this->session->remove($name);
    }
}
