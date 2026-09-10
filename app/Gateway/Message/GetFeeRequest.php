<?php
namespace Shipping\Gateway\Message;

use Ecommerce\Gateway\Shipping\Common\Message\AbstractRequest;
use Shipping\Models\ShippingFee;
use Shipping\Models\ShippingZone;

class GetFeeRequest extends AbstractRequest
{
    public function getData(): array
    {
        return [
            'total'     => $this->getAmount(),
            'weight'    => $this->getWeight(),
            'city'      => $this->getContact()->getCity(),
            'ward'      => $this->getContact()->getWard(),
        ];
    }

    public function sendData($data): GetFeeResponse
    {
        $fee = false;

        if (!empty($data['city']))
        {
            $feeId = 0;

            $zone = ShippingZone::where('city', $data['city'])->first();

            if(hasItems($zone))
            {
                if($zone->wardOption == 1)
                {
                    $feeId = $zone->feeId;
                }
                else
                {
                    if (!empty($data['ward']))
                    {
                        foreach ($zone->wards as $item)
                        {
                            if(in_array($data['ward'], $item['wards']) !== false)
                            {
                                $feeId = $item['fee'];
                                break;
                            }
                        }
                    }

                    if(empty($feeId))
                    {
                        $feeId = $zone->feeId;
                    }
                }
            }

            if(!empty($feeId))
            {
                $shipFee = ShippingFee::find($feeId);
            }
            else
            {
                $shipFee = ShippingFee::where('default', 1)->first();
            }
        }

        if(!empty($shipFee))
        {
            $role = ($shipFee->type == 'price')?  $this->getAmount() : $this->getWeight();

            if(hasItems($shipFee->range))
            {
                foreach ($shipFee->range as $item)
                {
                    if(!isset($item['min']) || !isset($item['max']) || !isset($item['fee'])) continue;

                    if($item['min'] == 0 && $item['max'] == 0)
                    {
                        $fee = $item['fee'];
                        break;
                    }

                    if($item['min'] == 0 && $item['max'] >= $role)
                    {
                        $fee = $item['fee'];
                        break;
                    }

                    if($item['max'] == 0 && $item['min'] <= $role)
                    {
                        $fee = $item['fee'];
                        break;
                    }

                    if($item['min'] <= $role && $item['max'] >= $role)
                    {
                        $fee = $item['fee'];
                        break;
                    }
                }
            }
        }

        return new GetFeeResponse($this, [
            'fee' => $fee,
        ]);
    }
}