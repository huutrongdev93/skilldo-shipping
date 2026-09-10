<hr />
<div class="row">
    <div class="col-md-3">
        <div class="ui-title-bar__group pt-0">
            <h3 class="ui-title-bar__title" style="font-size: 20px;">Phí vận chuyển</h3>
            <p style="margin-top: 10px; margin-left: 1px; color: #8c8c8c">Thêm phí vận chuyển mới cho các khu vực vận chuyển khác nhau.</p>
            <div class="">
	            <button class="btn btn-blue" id="js_shipping_fee_btn_add">
		            {!! Admin::icon('add') !!}
		            <span>Thêm phí vận chuyển</span>
	            </button>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="box p-3">
            <div class="shipping-loading-box js_shipping_fee_loading">
				<div class="shipping-loading"></div>
			</div>
            <div class="shipping-table js_shipping_fee_table" style="display:none;">
                <table class="display table table-striped media-table ">
	                <thead>
	                <tr>
		                <th class="manage-column">Tên</th>
		                <th class="manage-column">Loại</th>
		                <th class="manage-column">Mặc định</th>
		                <th class="manage-column">#</th>
	                </tr>
	                </thead>
	                <tbody id="js_shipping_fee_table_result"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade shipping_modal" id="js_shipping_fee_modal" aria-hidden="true" aria-labelledby="js_shipping_modal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title fs-5" id="js_shipping_modal_title">Thêm phí vận chuyển</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="">
					<div class="form-group mb-3">
						<label for="">Tên phí vận chuyển</label>
						<input name="feeName" class="form-control js_shipping_fee_input_name"/>
					</div>
					<div class="form-group">
						<label class="d-block form-check">
							<input type="checkbox" name="feeDefault" class="js_shipping_fee_checkbox_default form-check-input" value="1"> Phí vận chuyển mặc định
						</label>
						<p><i style="font-style: italic">Nếu chọn, khi đơn hàng không xác định được phí vận chuyển sẽ sử dụng phí vận chuyển này</i></p>
					</div>
					<div class="form-group mb-3">
						<label for="">Tiêu chuẩn tính phí</label>
						<select name="feeType" class="form-control js_shipping_fee_select_type">
							<option value="price">Dựa trên giá trị đơn hàng</option>
							<option value="weight">Dựa trên khối lượng đơn hàng</option>
						</select>
					</div>
					<div class="shipping_range_table">
						<table class="display table table-striped media-table ">
							<thead>
							<tr>
								<th class="manage-column column-image">Hạn mức</th>
								<th class="manage-column column-title">Đơn vị</th>
								<th class="manage-column column-prices">Giá vận chuyển</th>
								<th class="manage-column column-prices">#</th>
							</tr>
							</thead>
							<tbody id="js_shipping_fee_range_result"></tbody>
						</table>
						<button class="btn btn-blue mt-3" id="js_shipping_fee_range_btn_add" type="button"><?php echo Admin::icon('add');?> Thêm hạn mức</button>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-blue js_shipping_fee_modal_btn_confirm" data-status="add" type="button"><?php echo Admin::icon('save');?> Lưu</button>
			</div>
		</div>
	</div>
</div>

<script id="shipping_range_template" type="text/x-custom-template">
	<tr class="range_item" data-id="${id}">
		<td class="column-value">
			<div class="range-value d-flex gap-1 align-items-center">
				<input name="range[${id}][min]" type="number" min="0" class="form-control" value="${min}">
				<span>-</span>
				<input name="range[${id}][max]" type="number" min="0" class="form-control" value="${max}">
			</div>
		</td>
		<td class="unit column-unit js_range_unit">${unit}</td>
		<td class="prices column-prices">
			<input name="range[${id}][fee]" type="number" min="0" class="form-control" value="${fee}">
		</td>
		<td class="action column-action">
			<button class="btn btn-red js_shipping_fee_range_btn_delete" data-id="${id}">{!! Admin::icon('delete') !!}</button>
		</td>
	</tr>
</script>
<script id="shipping_fee_template" type="text/x-custom-template">
	<tr class="js_column js_shipping_fee_item tr_${id}" data-id="${id}">
		<td class="column-name">${name}</td>
		<td class="column-type">${typeLabel}</td>
		<td class="column-type">${defaultLabel}</td>
		<td class="column-action">
			<button class="btn btn-blue js_shipping_fee_item_btn_edit" data-id="${id}">{!! Admin::icon('edit') !!}</button>
			<button class="btn btn-red js_btn_confirm"
			        data-bs-toggle="tooltip"
			        data-bs-placement="top"
			        data-bs-title="Xóa"
			        data-action="delete"
			        data-ajax="ShippingFeeAdminAjax::delete"
			        data-id="${id}"
			        data-module="shipping"
			        data-trash="disable"
			        data-heading="Xóa Dữ liệu"
			        data-description="Bạn chắc chắn muốn xóa phí vận chuyển <b>${name}</b> ?">{!! Admin::icon('delete') !!}</button>

		</td>
	</tr>
</script>