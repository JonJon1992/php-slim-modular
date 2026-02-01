<?php

namespace Core\Utils\Render;

use Slim\Views\PhpRenderer;

class SlimPhpRenderAdapter extends PhpRenderer implements IRendebleAdapter
{

    /**
     * @param string $templateName
     * @param array $data
     * @return string
     */
    public function renderToString(string $templateName, array $data = []): string
    {
        return $this->fetch($templateName, $data, true);
    }
}
