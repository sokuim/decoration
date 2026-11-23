<?php
class ControllerExtensionModuleTopAd extends Controller {
	public function index($setting) {
		if (empty($setting['status']) || empty($setting['image'])) {
			return '';
		}

		// 显示端过滤：pc 仅在电脑端显示，mobile 仅在移动端显示
		$device = isset($setting['device']) ? $setting['device'] : 'all';
		$is_mobile = is_mobile_device();

		if ($device == 'pc' && $is_mobile) {
			return '';
		}

		if ($device == 'mobile' && !$is_mobile) {
			return '';
		}

		if (!is_file(DIR_IMAGE . $setting['image'])) {
			return '';
		}

		$data['image'] = $this->config->get('config_url') . 'image/' . $setting['image'];
		$data['text'] = isset($setting['text']) ? $setting['text'] : '';
		$data['link'] = isset($setting['link']) ? $setting['link'] : '';
		$data['height'] = !empty($setting['height']) ? (int)$setting['height'] : 60;

		return $this->load->view('extension/module/top_ad', $data);
	}
}
