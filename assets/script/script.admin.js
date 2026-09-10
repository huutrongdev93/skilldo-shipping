function render(props) {
	return function(tok, i) {
		return (i % 2) ? props[tok] : tok;
	};
}
class ShippingHandle {
	constructor() {
		this.fee = null;
		this.feeList = {
			fees : [],
			get(feeId) {
				feeId = feeId*1
				let objIndex = this.fees.findIndex((item => item.id == feeId));
				if(objIndex == -1) return null;
				return this.fees[objIndex]
			},
			add(fee) {
				let objIndex = this.fees.findIndex((item => item.id == fee.id));
				if(objIndex == -1) {
					this.fees.unshift(fee);
				}
				return this.fees;
			},
			update(fee) {
				let objIndex = this.fees.findIndex((item => item.id == fee.id));
				this.fees[objIndex] = {...this.fees[objIndex], ...fee};
				return this.fees;
			},
			delete(feeId) {
				feeId = feeId*1
				this.fees = this.fees.filter(function(item) {
					return item.id !== feeId
				})
			},
		};
		this.feeModalHandle = new bootstrap.Modal('#js_shipping_fee_modal')
		this.feeModal       = $('#js_shipping_fee_modal')
		this.feeRangeTable  = $('#js_shipping_fee_range_result')
		this.feeModalTitle  = undefined
		this.feeName        = undefined
		this.feeType        = undefined
		this.feeUnit        = undefined
		this.feeDefault     = undefined
		this.feeTable       = $('.js_shipping_fee_table')
		this.feeTableBody   = $('#js_shipping_fee_table_result')
		this.feeLoading     = $('.js_shipping_fee_loading')

		this.zone = null;
		this.zoneList = {
			zones : [],
			get(zoneId) {
				zoneId = zoneId*1
				let objIndex = this.zones.findIndex((item => item.id == zoneId));
				if(objIndex == -1) return null;
				return this.zones[objIndex]
			},
			add(zone) {
				let objIndex = this.zones.findIndex((item => item.id == zone.id));
				if(objIndex == -1) {
					this.zones.unshift(zone);
				}
				return this.zones;
			},
			update(zone) {
				let objIndex = this.zones.findIndex((item => item.id == zone.id));
				this.zones[objIndex] = {...this.zones[objIndex], ...zone};
				return this.zones;
			},
			delete(zoneId) {
				zoneId = zoneId*1
				this.zones = this.zones.filter(function(item) {
					return item.id !== zoneId
				})
			},
		};
		this.zoneModalHandle = new bootstrap.Modal('#js_shipping_zone_modal')
		this.zoneModal       = $('#js_shipping_zone_modal')
		this.cities          = localStorage.getItem('cityList');
		this.ward       	 = localStorage.getItem('wardList');
		this.city            = { value: '', label: '' };
		this.wardOptions = '';
		this.feeOptions      = '';
		this.zonewardTable = $('#js_shipping_zone_ward_result');
		this.zoneTable       = $('.js_shipping_zone_table')
		this.zoneTableBody   = $('#js_shipping_zone_table_result')
		this.zoneLoading     = $('.js_shipping_zone_loading')
 		this.loadFee();
		this.loadLocation();
		this.loadZone();
	}

	loadFee(element) {

		let self = this;

		let data = {
			action: 'Shipping\\Ajax\\Admin\\ShippingFeeAjax::load'
		}

		request.post(ajax, data).then(function(response) {

			self.feeLoading.hide();

			if(response.status === 'success') {
				self.feeList.fees = response.data;
				self.renderFee();
				self.feeTable.show();
			}
			else {
				SkilldoMessage.response(response);
			}
		});

		return false;
	}
	renderFee(element) {
		let self = this;
		this.feeTableBody.html('');
		for (const [key, items_tmp] of Object.entries(this.feeList.fees)) {
			let items = [items_tmp];
			self.feeTableBody.append(items.map(function(item) {
				item.typeLabel = '';
				item.defaultLabel = '';
				if(item.type === 'price') item.typeLabel = 'Dựa trên giá trị đơn hàng';
				if(item.type === 'weight') item.typeLabel = 'Dựa trên khối lượng đơn hàng';
				if(item.default === 1) {
					item.defaultLabel = '<i class="fa-duotone fa-circle-check"></i>';
				}
				return $('#shipping_fee_template').html().split(/\$\{(.+?)\}/g).map(render(item)).join('');
			}));
		}
	}
	setFeeModal(status) {
		this.feeModal.find('#js_shipping_modal_title').html(this.feeModalTitle)
		this.feeModal.find('.js_shipping_fee_input_name').val(this.feeName)
		if(this.feeDefault === 1) {
			this.feeModal.find('.js_shipping_fee_checkbox_default').prop('checked', true);
		}
		else {
			this.feeModal.find('.js_shipping_fee_checkbox_default').prop('checked', false);
		}
		this.feeModal.find('.js_shipping_fee_select_type').val(this.feeType)
		this.feeModal.find('.js_shipping_fee_modal_btn_confirm').attr('data-status', status)
		if(this.feeType === 'weight') this.feeUnit = 'gram';
		if(this.feeType === 'price') this.feeUnit = 'đ';
	}
	clickAddFee(element) {
		this.fee = null;
		this.feeModalTitle = 'Thêm phí vận chuyển';
		this.feeName       = '';
		this.feeType       = 'price';
		this.feeUnit       = 'đ';
		this.feeDefault    = 0;
		this.feeRangeTable.html('');
		this.setFeeModal('add');
		this.feeModalHandle.show();
		return false;
	}
	addFeeRange(element) {

		let self = this;

		let items = [{ id: SkilldoUtil.uniqId(), min: 0, max: 0, fee: 0 , unit: self.unit }];

		this.feeRangeTable.append(items.map(function(item) {
			return $('#shipping_range_template').html().split(/\$\{(.+?)\}/g).map(render(item)).join('');
		}));

		return false;
	}
	deleteFeeRange(element) {
		element.closest('.range_item').remove();
		return false;
	}
	changeFeeType(element) {

		this.feeType = element.val();

		if(this.feeType === 'weight') this.feeUnit = 'gram';

		if(this.feeType === 'price') this.feeUnit = 'đ';

		this.feeRangeTable.find('.js_range_unit').html(this.feeUnit);

		return false;
	}
	addFee(element) {

		let self = this;

		element.find('span').html('Loading...').attr('disable');

		$('.loading').show();

		let data = $(':input', this.feeModal).serializeJSON();

		data.action = 'Shipping\\Ajax\\Admin\\ShippingFeeAjax::add';

		request.post(ajax, data).then(function(response) {

			$('.loading').hide();

			SkilldoMessage.response(response);

			if(response.status === 'success')
			{

				self.feeList.add(response.data);

				self.renderFee();

				self.feeModalHandle.hide();
			}

			element.find('span').html('Lưu').removeAttr('disable');
		});

		return false;
	}
	clickEditFee(element) {

		let self = this;

		let id      = element.attr('data-id');

		this.fee  = this.feeList.get(id);

		if(this.fee == null) {
			SkilldoMessage.error('Không tìm thấy phí vận chuyển để chỉnh sữa')
			return false
		}

		this.feeModalTitle = 'Cập nhật phí vận chuyển ' + this.fee.name;
		this.feeName       = this.fee.name;
		this.feeType       = this.fee.type;
		this.feeDefault    = this.fee.default;
		this.feeRangeTable.html('');
		for (const [key, items_tmp] of Object.entries(this.fee.range)) {
			let items = [items_tmp];
			self.feeRangeTable.append(items.map(function(item) {
				return $('#shipping_range_template').html().split(/\$\{(.+?)\}/g).map(render(item)).join('');
			}));
		}
		this.setFeeModal('edit');
		this.feeModalHandle.show();
		return false;
	}
	editFee(element) {

		let self = this;

		if(this.fee == null) {
			SkilldoMessage.error('Không tìm thấy phí vận chuyển để chỉnh sữa')
			return false
		}

		element.find('span').html('Loading...').attr('disable');

		$('.loading').show();

		let data = $(':input', this.feeModal).serializeJSON();

		data.action = 'Shipping\\Ajax\\Admin\\ShippingFeeAjax::save';

		data.id     = this.fee.id;

		request.post(ajax, data).then(function(response) {

			$('.loading').hide();

			SkilldoMessage.response(response);

			if(response.status === 'success') {

				self.feeList.update(response.data);

				self.renderFee();

				self.feeModalHandle.hide();
			}

			element.find('span').html('Lưu').removeAttr('disable');
		});

		return false;
	}
	saveFee(element) {
		if(element.attr('data-status') == 'add') {
			this.addFee(element)
		}
		else {
			this.editFee(element)
		}
	}
	loadZone(element) {

		let self = this;

		let data = {
			action: 'Shipping\\Ajax\\Admin\\ShippingZoneAjax::load'
		}

		request.post(ajax, data).then(function(response) {

			self.zoneLoading.hide();

			if(response.status === 'success')
			{
				self.zoneList.zones = response.data;
				self.renderZone();
				self.zoneTable.show();
			}
			else
			{
				SkilldoMessage.response(response);
			}
		});

		return false;
	}
	renderZone(element) {
		let self = this;
		this.zoneTableBody.html('');
		for (const [key, items_tmp] of Object.entries(this.zoneList.zones)) {
			let items = [items_tmp];
			self.zoneTableBody.append(items.map(function(item) {
				item.wardLabel = '';
				if(item.wardOption === 1) {
					item.wardLabel = 'Tất cả quận huyện';
				}
				else {
					item.wardLabel = 'Quận huyện tùy chọn';
				}

				let fee = self.feeList.get(item.feeId);

				if(fee != null) item.feeName = fee.name;

				return $('#shipping_zone_template').html().split(/\$\{(.+?)\}/g).map(render(item)).join('');
			}));
		}
	}
	loadLocation(element) {
		let self = this;

		if(typeof this.cities == 'undefined' || this.cities == null || this.cities === 'undefined' || this.cities === undefined) {

			let data = {
				action: 'Shipping\\Ajax\\Admin\\ShippingAjax::locations',
			};

			request.post(ajax, data).then(function (response) {
				if (response.status === 'success') {
					localStorage.setItem('cityList', JSON.stringify(response.data.cities));
					localStorage.setItem('wardList', JSON.stringify(response.data.wards));
					self.cities = response.data.cities;
					self.ward = response.data.wards;
					self.renderLocation();
				}
			});
		}
		else {
			this.cities = JSON.parse(this.cities);
			this.ward = JSON.parse(this.ward);
			this.renderLocation();
		}
	}
	renderLocation(element) {
		let self = this;
		let cityRender = '<option value="all">Tất cả tỉnh thành</option>';
		if(Object.keys(self.cities).length !== 0)
		{
			for (const [index, item] of Object.entries(self.cities))
			{
				if(item.active === true)
				{
					let selected = (self.city.value == item.id) ? ' selected="selected"' : '';
					cityRender += `<option value="${item.id}" ${selected}>${item.fullname}</option>`;
				}
			}
			self.zoneModal.find('.js_shipping_zone_select_city').html(cityRender);
		}
	}
	clickAddZone(element) {

		let self = this;

		self.feeOptions = '';

		if(Object.keys(self.feeList.fees).length !== 0) {
			for (const [index, item] of Object.entries(self.feeList.fees)) {
				self.feeOptions += `<option value="${item.id}">${item.name}</option>`;
			}
			self.zoneModal.find('.js_shipping_zone_select_fee').html(self.feeOptions);
		}
		this.setZoneModal('add');

		this.zoneModalHandle.show();

		return false;
	}
	clickEditZone(element) {

		let self = this;

		let id     = element.attr('data-id');

		this.zone  = this.zoneList.get(id);

		if(this.zone == null)
		{
			SkilldoMessage.error('Không tìm thấy khu vực vận chuyển để chỉnh sữa')
			return false
		}

		self.feeOptions = '';

		let feeOptionsEdit = '';

		if(Object.keys(self.feeList.fees).length !== 0)
		{
			for (const [index, item] of Object.entries(self.feeList.fees))
			{
				self.feeOptions += `<option value="${item.id}">${item.name}</option>`;

				if(self.zone.feeId == item.id)
				{
					feeOptionsEdit += `<option value="${item.id}" selected>${item.name}</option>`;
				}
				else
				{
					feeOptionsEdit += `<option value="${item.id}">${item.name}</option>`;
				}
			}
		}

		self.zoneModal.find('select[name="zoneFee"]').html(feeOptionsEdit);

		this.setZoneModal('edit');

		this.zoneModalHandle.show();

		return false;
	}
	setZoneModal(status) {
		let self = this;
		if(status === 'add')
		{
			this.zoneModal.find('#js_shipping_zone_modal_title').html('Thêm khu vực vận chuyển');
			this.city.value = '';
			this.city.label = '';
			this.renderLocation();
			this.zoneModal.find('.js_shipping_zone_select_city').removeAttr('readonly').removeAttr('disabled');
			this.zoneModal.find('.js_shipping_zone_checkbox_ward_option').prop('checked', true);
			this.zoneModal.find('.js_shipping_zone_ward').hide();
			this.zonewardTable.html('');
		}
		if(status === 'edit')
		{
			this.zoneModal.find('#js_shipping_zone_modal_title').html('Cập nhật khu vực vận chuyển');
			this.city.value = this.zone.city;
			this.city.label = this.zone.name;
			this.renderLocation();
			this.zoneModal.find('.js_shipping_zone_select_city').attr('readonly', true).attr('disabled', true);
			let wardOptions = [];
			self.wardOptions = '';
			if(Object.keys(self.ward).length !== 0)
			{
				for (const [index, province] of Object.entries(self.ward))
				{
					if(province.id == self.zone.city)
					{
						wardOptions = [];
						for (const [index, item] of Object.entries(province.wards))
						{
							if(item.active === true)
							{
								wardOptions.push({
									value: item.id,
									label: item.name
								})
								self.wardOptions += `<option value="${item.id}">${item.name}</option>`;
							}
						}
						break;
					}
				}
			}
			if(this.zone.wardOption === 1)
			{
				this.zoneModal.find('.js_shipping_zone_checkbox_ward_option').prop('checked', true);
				this.zoneModal.find('.js_shipping_zone_ward').hide();
			}
			else
			{
				this.zoneModal.find('.js_shipping_zone_checkbox_ward_option').prop('checked', false);

				this.zoneModal.find('.js_shipping_zone_ward').show();

				this.zonewardTable.html('');

				for (const [key, disItem] of Object.entries(this.zone.wards)) {

					disItem.feeOptions = '';

					if(Object.keys(self.feeList.fees).length !== 0)
					{
						for (const [index, item] of Object.entries(self.feeList.fees))
						{
							let selected = (disItem.fee == item.id) ? 'selected' : '';

							disItem.feeOptions += `<option value="${item.id}" ${selected}>${item.name}</option>`;
						}
					}

					disItem.wardOptions = '';

					if(Object.keys(wardOptions).length !== 0) {

						for (const [index, ward] of Object.entries(wardOptions)) {
							let selected = (disItem.wards.includes(ward.value+'')) ? 'selected' : '';
							disItem.wardOptions += `<option value="${ward.value}" ${selected}>${ward.label}</option>`;
						}
					}

					let items = [disItem];

					self.zonewardTable.append(items.map(function(item) {
						return $('#shipping_zone_ward_template').html().split(/\$\{(.+?)\}/g).map(render(item)).join('');
					}));

					$('.select2-multiple').select2();
				}
			}
		}
		this.zoneModal.find('#js_shipping_zone_modal_btn_confirm').attr('data-status', status)
	}
	changeCity(element) {

		let self = this;

		let cityValue = element.val();

		self.wardOptions  = '';

		if(cityValue === 'all')
		{
			this.city.value = 'all';
			this.city.label = 'Tất cả tỉnh thành';
		}
		else
		{
			this.city.value = cityValue;

			this.city.label = this.cities[cityValue];

			let wardOptions;

			if(Object.keys(self.ward).length !== 0) {

				for (const [index, province] of Object.entries(self.ward))
				{
					if(province.id == cityValue)
					{
						wardOptions = [];
						for (const [index, item] of Object.entries(province.wards)) {
							if(item.active === true) {
								wardOptions.push({
									value: item.id,
									label: item.name
								})
							}
						}
						break;
					}
				}

				for (const [index, ward] of Object.entries(wardOptions)) {
					self.wardOptions += `<option value="${ward.value}">${ward.label}</option>`;
				}
			}
		}

		self.zoneModal.find('.js_shipping_zone_select_ward').html(self.wardOptions);
	}
	changeWardOption(element) {
		if(!element.is(":checked")) {
			this.zoneModal.find('.js_shipping_zone_ward').show();
		}
		else {
			this.zoneModal.find('.js_shipping_zone_ward').hide();
		}
	}
	addZoneWard(element) {

		let self = this;

		let items = [{ id: uniqid(), wardOptions: self.wardOptions, feeOptions: this.feeOptions  }];

		this.zonewardTable.append(items.map(function(item) {
			return $('#shipping_zone_ward_template').html().split(/\$\{(.+?)\}/g).map(render(item)).join('');
		}));

		$('.select2-multiple').select2();

		return false;
	}
	deleteZoneWard(element) {
		element.closest('.ward_item').remove();
		return false;
	}
	addZone(element) {

		let self = this;

		element.find('span').html('Loading...').attr('disable');

		$('.loading').show();

		let data = $(':input', this.zoneModal).serializeJSON();

		data.action = 'Shipping\\Ajax\\Admin\\ShippingZoneAjax::add';

		request.post(ajax, data).then(function(response) {

			$('.loading').hide();

			SkilldoMessage.response(response);

			if(response.status === 'success')
			{
				self.zoneList.add(response.data);

				self.renderFee();

				self.zoneModalHandle.hide();
			}

			element.find('span').html('Lưu').removeAttr('disable');

		}).catch(function(err) {
			$('.loading').hide();
		});

		return false;
	}
	editZone(element) {

		let self = this;

		if(this.zone == null) {
			SkilldoMessage.error( 'Không tìm thấy khu vực vận chuyển để chỉnh sữa')
			return false
		}

		element.find('span').html('Loading...').attr('disable');

		$('.loading').show();

		let data = $(':input', this.zoneModal).serializeJSON();

		data.action = 'Shipping\\Ajax\\Admin\\ShippingZoneAjax::save';

		data.id     = this.zone.id;

		request.post(ajax, data).then(function(response) {

			$('.loading').hide();

			SkilldoMessage.response(response);

			if(response.status == 'success') {

				self.zoneList.update(response.data);

				self.renderZone();

				self.zoneModalHandle.hide();
			}

			element.find('span').html('Lưu').removeAttr('disable');
		});

		return false;
	}
	saveZone(element) {
		if(element.attr('data-status') === 'add')
		{
			this.addZone(element)
		}
		else
		{
			this.editZone(element)
		}
	}
}