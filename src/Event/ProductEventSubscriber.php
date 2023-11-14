<?php

namespace App\Event;

use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpKernel\Log\Logger;

class ProductEventSubscriber implements EventSubscriberInterface
{
    /**
     * @param ProductCreatedEvent $event
     * @return void
     */
    public function onProductCreation(ProductCreatedEvent $event): void
    {
        $product = $event->getProduct();
        $mailer = $event->getMailer();
        $message = "Product {$product->getTitle()} is created";
        $emailData = [
            'subject' => "Product {$product->getTitle()} is created",
            'text' => $message,
            'html' => $message
        ];
        $this->sendEmail($mailer, $emailData);
    }

    /**
     * @param ProductUpdatedEvent $event
     * @return void
     */
    public function onProductUpdation(ProductUpdatedEvent $event): void
    {
        $product = $event->getProduct();
        $mailer = $event->getMailer();
        $message = "Product {$product->getTitle()} is updated";
        $emailData = [
            'subject' => "Product {$product->getTitle()} is updated",
            'text' => $message,
            'html' => $message
        ];
        $this->sendEmail($mailer, $emailData);
    }

    /**
     * @param $mailer
     * @param $emailData
     * @return void
     */
    private function sendEmail($mailer, $emailData): void
    {
        $message = '';
        $email = (new Email())
            ->from($_ENV["FROM_EMAIL"])
            ->to($_ENV["ADMIN_EMAIL"])
            ->subject($emailData['subject'])
            ->text($emailData['text'])
            ->html($emailData['text']);
        try {
            $mailer->send($email);
        } catch (Exception $exception) {
            $logger = new Logger();
            $message = $exception->getMessage();
            $logger->error($message);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductCreatedEvent::NAME => 'onProductCreation',
            ProductUpdatedEvent::NAME => 'onProductUpdation'
        ];
    }
}