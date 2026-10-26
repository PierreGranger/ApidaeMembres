<?php

namespace PierreGranger\Tests;

use PierreGranger\ApidaeMembres;

/**
 * Sous-classe de test permettant de mocker request().
 */
class ApidaeMembresTestable extends ApidaeMembres
{
    public ?array $mockRequestResult = null;

    public int $requestCallCount = 0;

    public ?string $lastRequestPath = null;

    public ?array $lastRequestParams = null;

    protected function request(string $path, $params = null)
    {
        if ($this->mockRequestResult !== null) {
            $this->requestCallCount++;
            $this->lastRequestPath = $path;
            $this->lastRequestParams = $params;
            if (isset($params['POSTFIELDS'])) {
                $this->lastPostfields = $params['POSTFIELDS'];
            }
            $this->lastResult = $this->mockRequestResult;

            return $this->mockRequestResult;
        }

        return parent::request($path, $params);
    }
}
