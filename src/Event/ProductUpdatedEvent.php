<?php

namespace App\Event;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Contracts\EventDispatcher\Event;

class ProductUpdatedEvent extends Event
{
    public const NAME = 'product.updated';
    /**
     * @var mixed
     */
    private mixed $product;

    public function __construct($product)
    {
        $this->product = $product;
    }
    public function getProduct()
    {
        return $this->product;
    }
    /**
     * @return Mailer
     */
    public function getMailer(): Mailer
    {
        return new Mailer(new SendmailTransport());
    }
}