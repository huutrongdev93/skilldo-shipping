<script>
	$(function () {
		const shippingHandle = new ShippingHandle();
		$(document)
			.on('click', '#js_shipping_fee_btn_add', function () {
				return shippingHandle.clickAddFee();
			})
			.on('click', '#js_shipping_fee_range_btn_add', function () {
				return shippingHandle.addFeeRange($(this));
			})
			.on('click', '.js_shipping_fee_range_btn_delete', function () {
				return shippingHandle.deleteFeeRange($(this));
			})
			.on('change', '.js_shipping_fee_select_type', function () {
				return shippingHandle.changeFeeType($(this));
			})
			.on('click', '.js_shipping_fee_modal_btn_confirm', function () {
				return shippingHandle.saveFee($(this));
			})
			.on('click', '.js_shipping_fee_item_btn_edit', function () {
				return shippingHandle.clickEditFee($(this));
			})
			.on('click', '#js_shipping_zone_btn_add', function () {
				return shippingHandle.clickAddZone($(this));
			})
			.on('click', '.js_shipping_zone_item_btn_edit', function () {
				return shippingHandle.clickEditZone($(this));
			})
			.on('change', '.js_shipping_zone_select_city', function () {
				return shippingHandle.changeCity($(this));
			})
			.on('change', '.js_shipping_zone_checkbox_ward_option', function () {
				return shippingHandle.changeWardOption($(this));
			})
			.on('click', '#js_shipping_zone_ward_btn_add', function () {
				return shippingHandle.addZoneWard($(this));
			})
			.on('click', '.js_shipping_zone_ward_btn_delete', function () {
				return shippingHandle.deleteZoneWard($(this));
			})
			.on('click', '#js_shipping_zone_modal_btn_confirm', function () {
				return shippingHandle.saveZone($(this));
			})
	})
</script>