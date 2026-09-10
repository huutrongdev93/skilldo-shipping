<?php
namespace Shipping\Gateway;

use Ecommerce\Cart\Scart;
use Ecommerce\Gateway\Shipping\Common\AbstractShippingGateway;
use Shipping\Gateway\Message\GetFeeRequest;
use SkillDo\Http\Request;

class ShippingZoneGateway extends AbstractShippingGateway
{
    public function getFee($data)
    {
        return $this->createRequest(GetFeeRequest::class, [
            'amount'  => $data['total'] ?? Scart::total(),
            'weight'  => $data['weight'] ?? Scart::totalWeight(),
            'contact' => [
                'city' => $data['city'] ?? '',
                'ward' => $data['ward'] ?? '',
                'address' => $data['address'] ?? '',
            ]
        ]);
    }
}