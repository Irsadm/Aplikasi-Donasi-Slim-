<?php

namespace App\Extensions;

class Mailer
{
    public function send(array $data)
    {
        // create the transport
        $transport = (new \Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
                    ->setUsername('donasiapp@gmail.com')
                    ->setPassword('generasi3');
        // create the mailer
        $mailer = new \Swift_Mailer($transport);

        $message = (new \Swift_Message($data['subject']))
                    ->setFrom([$data['from'] => $data['sender']])
                    ->setTo([$data['to'] => $data['receiver']])
                    ->setBody($data['content'], 'text/html')
                    ->addPart(strip_tags($data['content']), 'text/plain');
        $result = $mailer->send($message);
    }
}
