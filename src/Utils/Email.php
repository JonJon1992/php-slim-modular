<?php

namespace Core\Utils;

use Core\Traits\Create;
use Core\Utils\Render\IRendebleAdapter;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

class Email extends Message
{
    use Create;

    protected SmtpMailer $mailer;

    protected IRendebleAdapter $render;

    /**
     * @param SmtpMailer $mailer
     * @return $this
     */
    public function setMailer(SmtpMailer $mailer): Email
    {
        $this->mailer = $mailer;
        return $this;
    }

    /**
     * @param IRendebleAdapter $render
     * @return $this
     */
    public function setRender(IRendebleAdapter $render): Email
    {
        $this->render = $render;
        return $this;
    }

    public function send(): void
    {
        if (!isset($this->mailer)) {
            throw  new \Exception('Undefined mailer');
        }
        $this->mailer->send($this);
    }

    /**
     * @param $template
     * @param $params
     * @param $response
     * @return $this
     */
    public function setView($template, $params, $response = null): static
    {
        $html = view()->render($response, $template, $params);
        $this->setHtmlBody($html->getBody());
        return $this;
    }

    /**
     * @param string $fileName
     * @param array $data
     * @param string $template
     * @return $this
     */
    public function setBodyByTemplate(
        string $fileName,
        array  $data = [],
        string $template = "../../../views/Layout.phtml"
    ): self
    {

        $this->render->setLayout($template);

        $body = $this->render->renderToString($fileName, $data);

        $this->setHtmlBody($body);

        return $this;
    }

}