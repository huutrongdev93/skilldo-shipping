<hr />
<div class="row">
    <div class="col-md-3">
        <div class="ui-title-bar__group pt-0">
            <h3 class="ui-title-bar__title" style="font-size: 20px;">Khu vực vận chuyển</h3>
            <p style="margin-top: 10px; margin-left: 1px; color: #8c8c8c">Thêm phí vận chuyển mới cho các khu vực vận chuyển khác nhau.</p>
            <div class="">
	            <button class="btn btn-blue" id="js_shipping_zone_btn_add">
		            {!! Admin::icon('add') !!}
		            Thêm khu vực vận chuyển
	            </button>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="box p-3">
	        <div class="shipping-loading-box js_shipping_zone_loading"><div class="shipping-loading"></div></div>
	        <div class="shipping-table js_shipping_zone_table" style="display:none;">
		        <table class="display table table-striped media-table ">
			        <thead>
			        <tr>
				        <th class="manage-column">Khu vực</th>
				        <th class="manage-column">Quận huyện</th>
				        <th class="manage-column">Vận chuyển</th>
				        <th class="manage-column">#</th>
			        </tr>
			        </thead>
			        <tbody id="js_shipping_zone_table_result"></tbody>
		        </table>
	        </div>
        </div>
    </div>
</div>

<div class="modal fade shipping_modal" id="js_shipping_zone_modal" aria-hidden="true" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title fs-5" id="js_shipping_zone_modal_title">Thêm khu vực vận chuyển</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="">
					<div class="form-group mb-3">
						<label for="">Chọn tỉnh thành phố</label>
						<select name="zoneCity" class="form-control js_shipping_zone_select_city"></select>
					</div>

					<div class="form-group mb-3">
						<label for="">Phí vận chuyển mặc định</label>
						<select name="zoneFee" class="form-control js_shipping_zone_select_fee"></select>
						<p><i style="font-style: italic">Khi quận huyện thuộc khu vực này không được chọn phí vận chuyển sẽ áp dụng phí vận chuyển này</i></p>
					</div>

					<div class="form-group mb-3">
						<label class="d-block">
							<input type="checkbox" name="zoneDistrictOption" class="js_shipping_zone_checkbox_district_option" value="1" checked> Áp dụng với tất cả quận huyện
						</label>
					</div>

					<div class="shipping_range_table js_shipping_zone_district" style="display: none">
						<table class="display table table-striped media-table ">
							<thead>
							<tr>
								<th class="manage-column">Quận huyện</th>
								<th class="manage-column">Phí vận chuyển</th>
								<th class="manage-column">#</th>
							</tr>
							</thead>
							<tbody id="js_shipping_zone_district_result"></tbody>
						</table>
						<button class="btn btn-blue mt-3" id="js_shipping_zone_district_btn_add" type="button"><?php echo Admin::icon('add');?> Thêm quận huyện</button>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-blue" id="js_shipping_zone_modal_btn_confirm" data-status="add" type="button"><?php echo Admin::icon('save');?> Lưu</button>
			</div>
		</div>
	</div>
</div>

<script id="shipping_zone_district_template" type="text/x-custom-template">
	<tr class="district_item" data-id="${id}">
		<td class="column-district">
			<select name="zoneDistricts[${id}][districts][]" class="form-control js_shipping_zone_select_district select2-multiple" multiple>${districtOptions}</select>
		</td>
		<td class="column-district">
			<select name="zoneDistricts[${id}][fee]" class="form-control js_shipping_zone_select_fee">${feeOptions}</select>
		</td>
		<td class="action column-action">
			<button class="btn btn-red js_shipping_zone_district_btn_delete" data-id="${id}">{!! Admin::icon('delete') !!}</button>
		</td>
	</tr>
</script>

<script id="shipping_zone_template" type="text/x-custom-template">
	<tr class="js_column js_shipping_fee_item" data-id="${id}">
		<td class="column-name">${name}</td>
		<td class="column-type">${districtLabel}</td>
		<td class="column-type">${feeName}</td>
		<td class="column-action">
			<button class="btn btn-blue js_shipping_zone_item_btn_edit" data-id="${id}">{!! Admin::icon('edit') !!}</button>
			<button class="btn btn-red js_btn_confirm"
			        data-bs-toggle="tooltip"
			        data-bs-placement="top"
			        data-bs-title="Xóa"
			        data-action="delete"
			        data-ajax="ShippingZoneAdminAjax::delete"
			        data-id="${id}"
			        data-module="shipping"
			        data-trash="disable"
			        data-heading="Xóa Dữ liệu"
			        data-description="Bạn chắc chắn muốn xóa khu vực vận chuyển <b>${name}</b> ?">{!! Admin::icon('delete') !!}</button>
		</td>
	</tr>
</script>
