<?php

namespace App\Service;

use SendinBlue;
use GuzzleHttp;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailerService
{
    private $mailer;
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function send(
        array $from,
        array $to,
        string $subject,
        string $content,
        array $context
    ):void
    {
        $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $_ENV['SENDINBLUE_KEY']);

        $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(
            new GuzzleHttp\Client(),
            $config
        );
        $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail();
        $sendSmtpEmail['subject'] = $subject;
        $sendSmtpEmail['htmlContent'] = $content;
        $sendSmtpEmail['sender'] = $from;
        $sendSmtpEmail['to'] = array($to);

        $result = $apiInstance->sendTransacEmail($sendSmtpEmail);

    }

}
