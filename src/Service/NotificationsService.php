<?php

namespace App\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class NotificationsService
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function sendEmail(string $to, string $subject, string $content): void
    {
        $email = (new Email())
            ->from('admin@example.com')
            ->to($to)
            ->subject($subject)
            ->html($content);

        try {
            $this->mailer->send($email);
            $this->logger->info('Email sent successfully to ' . $to);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email to ' . $to . '. Error: ' . $e->getMessage());
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send email to ' . $to . '. TransportException: ' . $e->getMessage());
        }
    }
}