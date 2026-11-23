<?php
class ControllerExtensionModuleBlock extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('tool/image');

		$data['module_title'] = isset($setting['module_title']) ? $setting['module_title'] : '';

		$data['blocks'] = array();

		$is_mobile = is_mobile_device();

		if (!empty($setting['block']) && is_array($setting['block'])) {
			foreach ($setting['block'] as $block) {
				$device = isset($block['device']) ? $block['device'] : 'all';

				// 按显示端过滤：电脑端配置的不在移动端显示，移动端配置的不在电脑端显示
				if ($device == 'pc' && $is_mobile) {
					continue;
				}

				if ($device == 'mobile' && !$is_mobile) {
					continue;
				}

				$image = isset($block['image']) ? $block['image'] : '';
				$thumb = '';

				if ($image && is_file(DIR_IMAGE . $image)) {
					$thumb = $this->model_tool_image->resize($image, $setting['width'], $setting['height']);
				}

				$data['blocks'][] = array(
					'device'           => $device,
					'background_color' => isset($block['background_color']) ? $block['background_color'] : '',
					'image'            => $thumb,
					'title'            => isset($block['title']) ? $block['title'] : '',
					'link'             => isset($block['link']) ? $block['link'] : ''
				);
			}
		}

		$data['module'] = $module++;

		return $this->load->view('extension/module/block', $data);
	}
}
