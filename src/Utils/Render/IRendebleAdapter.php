<?php

declare(strict_types=1);

namespace Core\Utils\Render;

use Psr\Http\Message\ResponseInterface;

interface IRendebleAdapter
{
    /**
     * @param string $layout
     */
    public function setLayout(string $layout);

    /**
     * @param ResponseInterface $response
     * @param string $templateName
     * @param array $data
     * @return ResponseInterface
     */
    public function render(ResponseInterface $response, string $templateName, array $data = []): ResponseInterface;

    /**
     * @param string $templateName
     * @param array $data
     */
    public function renderToString(string $templateName, array $data = []);
}
