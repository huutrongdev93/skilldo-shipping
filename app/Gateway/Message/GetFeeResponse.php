<?php
namespace Shipping\Gateway\Message;

use Ecommerce\Gateway\Shipping\Common\Message\AbstractResponse;

class GetFeeResponse extends AbstractResponse
{
    public function isSuccessful(): true
    {
        return true;
    }

    public function getFee()
    {
        return $this->data['fee'] ?? false;
    }

    public function getInfo(): array
    {
        return [];
    }
}