<?php

namespace eCamp\Lib\Mail;

use Exception;
use Laminas\Mail\Message as MailMessage;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use Laminas\View\Model\ViewModel;
use Laminas\View\View;

class LaminasMail implements ProviderInterface {
    private TransportInterface $mailTransport;
    private View $view;
    private array $templateConfig;

    public function __construct(TransportInterface $mailTransport, View $view, array $templateConfig) {
        $this->mailTransport = $mailTransport;
        $this->view = $view;
        $this->templateConfig = $templateConfig;
    }

    public function sendMail(MessageData $message) {
        $body = $this->createBody($message);

        $mail = new MailMessage();
        $mail->setFrom($message->from);
        $mail->setTo($message->to);

        if (null != $message->cc) {
            $mail->setCc($message->cc);
        }
        if (null != $message->bcc) {
            $mail->setBcc($message->bcc);
        }

        $mail->setSubject($message->subject);
        $mail->setBody($body);

        $this->mailTransport->send($mail);
    }

    private function createBody(MessageData $data): MimeMessage {
        if (!array_key_exists($data->template, $this->templateConfig)) {
            throw new Exception("Config for template '".$data->template."' is missing");
        }

        $config = $this->templateConfig[$data->template];
        $part = $this->create($data, $config);

        $message = new MimeMessage();
        $message->addPart($part);

        return $message;
    }

    private function create(MessageData $data, array $config): Part {
        $type = $config['type'];

        switch ($type) {
            case Mime::MULTIPART_ALTERNATIVE:
                return $this->createMultipart($data, $config);

            case Mime::TYPE_TEXT:
            case Mime::TYPE_HTML:
                return $this->createPart($data, $config);

            default:
                throw new Exception('Type not implemented: '.$type);
        }
    }

    private function createMultipart(MessageData $data, array $config): Part {
        $partsConfig = $config['parts'];

        $partsMessage = new MimeMessage();
        foreach ($partsConfig as $partConfig) {
            $part = $this->create($data, $partConfig);
            $partsMessage->addPart($part);
        }

        $multipart = new Part($partsMessage->generateMessage());
        $multipart->type = $config['type'];
        $multipart->boundary = $partsMessage->getMime()->boundary();

        return $multipart;
    }

    private function createPart(MessageData $data, array $config): Part {
        $viewModel = new ViewModel();
        $viewModel->setOption('has_parent', true);
        $viewModel->setTemplate($config['template']);
        $viewModel->setVariables($data->data);
        $content = $this->view->render($viewModel);

        $part = new Part($content);
        $part->encoding = $config['encoding'];
        $part->type = $config['type'];
        $part->charset = $config['charset'];

        return $part;
    }
}
