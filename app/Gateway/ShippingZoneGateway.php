<?php
namespace Shipping\Gateway;

use Ecommerce\Cart\Scart;
use Ecommerce\Gateway\Shipping\Common\AbstractShippingGateway;
use Shipping\Gateway\Message\GetFeeRequest;
use SkillDo\Http\Request;

class ShippingZoneGateway extends AbstractShippingGateway
{
    /**
     * Ngưỡng miễn phí vận chuyển (lưu trong cart_shipping['zone']['freeShipAmount']).
     * 0 = không áp dụng.
     */
    public function getFreeShipAmount(): float
    {
        return (float)($this->getParameter('freeShipAmount') ?? 0);
    }

    public function setFreeShipAmount($value): static
    {
        return $this->setParameter('freeShipAmount', is_numeric($value) ? (float)$value : 0);
    }

    public function getFee($data)
    {
        return $this->createRequest(GetFeeRequest::class, [
            'amount'  => $this->orderAmount($data),
            'weight'  => $this->orderWeight($data),
            'contact' => [
                'city' => $data['city'] ?? '',
                'ward' => $data['ward'] ?? '',
                'address' => $data['address'] ?? '',
            ]
        ]);
    }

    /**
     * Giá trị đơn để so ngưỡng miễn phí / bảng phí theo giá: tiền hàng sau giảm giá.
     * Cả loadCheckoutReview lẫn saveCheckout đều truyền subtotal + discountAmount;
     * 'total' giữa hai luồng không cùng nghĩa (review chưa trừ giảm giá, save đã trừ).
     */
    protected function orderAmount($data): float
    {
        if (isset($data['subtotal']) && is_numeric($data['subtotal']))
        {
            return max(0, (float)$data['subtotal'] - (float)($data['discountAmount'] ?? 0));
        }

        return (float)($data['total'] ?? Scart::total());
    }

    /**
     * Cân nặng: sicommerce đã tính sẵn shippingWeight.weight (có phương án dự phòng khi
     * sản phẩm chưa nhập cân nặng) — dùng nó trước, Scart chỉ là đường lùi.
     */
    protected function orderWeight($data): float
    {
        if (isset($data['shippingWeight']['weight']) && is_numeric($data['shippingWeight']['weight']))
        {
            return (float)$data['shippingWeight']['weight'];
        }

        return (float)($data['weight'] ?? Scart::totalWeight());
    }
}
