# CLAUDE.md - Plugin shipping

Đọc TRƯỚC khi sửa file trong plugin này.

## Plugin này là gì

**Shipping** - cổng vận chuyển "Giao hàng tận nơi" (`zone`) cho sicommerce: tính phí ship theo bảng phí (theo giá trị đơn hoặc theo cân nặng) gắn với khu vực (tỉnh/thành, tuỳ chọn riêng từng phường/xã).

- Namespace `Shipping\*`; alias `Shipping` trong `plugin.json`. Main class `shipping` (chữ thường) ở `shipping.php`. `plugin.json` version **4.0.2** (đã bỏ hằng `SHIP_VERSION` vì không ai đọc).
- Lưu ý PSR-4: `plugin.json` khai `"Shipping\\Gateway": "app\\Gateway"` (không có dấu `\` cuối, chỉ phủ lớp Gateway); các lớp còn lại (`Ajax`, `Models`, `Providers`, `Services`) do cơ chế quét thư mục `app/` của plugin nạp.
- Provider `ShippingServiceProvider::boot()` gọi `ShippingManager::addGateway('zone', ['shipping' => ShippingZone::class, 'gateway' => ShippingZoneGateway::class])`.
- Plugin có repo git riêng (`plugins/shipping/.git`), root repo bỏ qua `plugins/`.

## Phụ thuộc

- **sicommerce (cứng)**: `Ecommerce\Gateway\Shipping\Common\*` (`ShippingManager`, `AbstractShippingBase`, `AbstractShippingGateway`, `AbstractRequest/Response`), `Ecommerce\Cart\Scart`, `Ecommerce\Supports\Prd` (đơn vị tiền/cân). Provider chạy `use` thẳng nên tắt sicommerce thì lỗi.
- Core: `SkillDo\Cms\Location\Location` / `Location2` (danh sách tỉnh, phường xã).

## Database

`database/database.php` (migration, `up()` chỉ thêm phần còn thiếu nên chạy lại an toàn):
- `shipping_fee`: `name`, `type` (`price` hoặc `weight`), `range` (text, serialize mảng hạn mức `min/max/fee/unit/id`), `fee`, `default` (chỉ 1 dòng = 1).
- `shipping_zones`: `name`, `feeId`, `city` (cột integer trong DB nhưng model khai string, giá trị đặc biệt `all`), `ward`, `wardOption` (1 = một mức phí cho cả tỉnh, 0 = theo từng nhóm phường xã), `wards` (text serialize `[{id, wards:[...], fee}]`, cột thêm ở 4.0.1).
- Option: `cart_shipping['zone']` (cấu hình cổng, của sicommerce; `ActivatorService` đặt `['enabled' => false]` nếu chưa có), `shipping_db_version`.
- `DeactivatorService::uninstall` gọi `down()` drop cả hai bảng (không xoá option).

## Bản đồ file

| File | Trách nhiệm |
|---|---|
| `shipping.php` | Main class, include và gọi `ActivatorService`/`DeactivatorService` (include tay vì `active()` chạy trước khi autoload) |
| `bootstrap/config.php` | Hook `admin_assets`; hook `admin_init` chạy lại `up()` một lần khi `shipping_db_version` khác `4.0.1` (thay cho migration cập nhật) |
| `bootstrap/ajax.php` | `Ajax::admin` cho `ShippingAjax::locations`, `ShippingFeeAjax::{load,add,save,delete}`, `ShippingZoneAjax::{load,add,save,delete}` |
| `app/Gateway/ShippingZone.php` + `ShippingZoneForm.php` | Mô tả cổng (tên `zone`) và form cấu hình: nhúng 3 view admin (bảng phí, khu vực, script) vào trang cấu hình vận chuyển |
| `app/Gateway/ShippingZoneGateway.php` | `getFee()` dựng `GetFeeRequest` từ tổng tiền/cân nặng giỏ và city/ward |
| `app/Gateway/Message/GetFeeRequest.php` | Logic tính phí (xem luồng); `GetFeeResponse` luôn `isSuccessful() = true`, `getFee()` trả `false` nếu không tìm được |
| `app/Models/ShippingFee.php`, `ShippingZone.php` | Model; `retrieved` tự `unserialize` `range` / `wards` |
| `app/Ajax/Admin/*` | CRUD bảng phí và khu vực |
| `views/admin/shipping*.blade.php`, `assets/script/script.admin.js`, `assets/css/` | Giao diện quản trị (less có `.map`) |
| `views/checkout/review.blade.php` | Dòng phí ship ở trang thanh toán (`$shippingPrice` in bằng `{!! !!}`) |
| `language/{vi,en}/` | `shipping::error.*`, `checkout.shipping.fee` |

## Luồng chính

Giỏ hàng -> `ShippingZoneGateway::getFee` -> `GetFeeRequest::sendData`: tìm `ShippingZone` theo `city`, không có thì lùi về khu vực `city = 'all'`; `wardOption == 1` lấy `feeId`; `wardOption == 0` duyệt `wards` tìm phường xã khớp (không khớp thì dùng `feeId` của khu vực); không có khu vực/`feeId` thì dùng bảng phí `default = 1`. Sau đó chọn mốc trong `range` theo `type` (giá đơn hoặc cân nặng): `min=0,max=0` = áp mọi giá trị, `min=0` hoặc `max=0` là biên mở; mốc đầu tiên khớp thắng (thứ tự mảng quan trọng).  Thiếu `city` vẫn dùng khu vực `all` hoặc bảng phí mặc định; chỉ không có phí khi không có bảng phí nào khớp.

## Quy tắc và bẫy

- Sửa schema phải sửa trong `database.php` và nâng số trong hook `admin_init` ở `bootstrap/config.php` (hằng `'4.0.1'` viết cứng ở 2 chỗ trong file đó).
- Phí 0đ hợp lệ (miễn phí vận chuyển): kiểm bỏ trống bằng chuỗi rỗng, đừng đổi thành `empty()`.
- Không xoá được bảng phí đang được khu vực dùng hoặc bảng mặc định (`ShippingFeeAjax::delete`).
- Mặc định cổng tắt khi kích hoạt để không hiện ở thanh toán khi chưa có bảng phí.
- Nhiều thông báo ajax của khu vực là chuỗi tiếng Việt viết thẳng trong `trans('...')` (không có key trong `language/`), nên bản `en` hiện ra tiếng Việt.

## Đã sửa (15/11/2026)

- v4.0.2. `GetFeeRequest`: không có khu vực đúng tỉnh thì lùi về khu vực `all`; thiếu `city` vẫn rơi về `all` / bảng phí mặc định.
- `ShippingZoneAjax::add/save`: kiểm trùng phường xã cả trong cùng nhóm; `add` kiểm phường thuộc tỉnh (khu vực `all` không được chọn phường riêng, phải chọn "toàn bộ").
- Đã kiểm, KHÔNG phải lỗi: `ShippingFeeAjax::save` / `ShippingZoneAjax::save` gọi `Model::insert($data)` có `id` là đường UPDATE của khung (`ModelStatic::insert` dòng 161-172 gọi `save()` khi có khoá chính).
- Bỏ hằng `SHIP_VERSION` (không nơi nào đọc, đã grep plugins/views/packages/app).
