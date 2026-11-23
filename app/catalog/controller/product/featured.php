<?php
class ControllerProductFeatured extends Controller {
	public function index() {
		$this->load->language('product/featured');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$site_name = $this->config->get('config_name');
		$seo_replace = array('site_name' => $site_name);

		$heading = seo_fill($this->config->get('config_seo_featured_list_heading'), $seo_replace);
		if ($heading === '') {
			$heading = $this->language->get('heading_title');
		}

		$meta_title = seo_fill($this->config->get('config_seo_featured_list_title'), $seo_replace);
		if ($meta_title === '') {
			$meta_title = $heading . '｜' . $site_name;
		}

		$meta_description = seo_fill($this->config->get('config_seo_featured_list_description'), $seo_replace);
		if ($meta_description === '') {
			$meta_description = $heading . '。' . $site_name . '（くらしマート）公式オンラインショップ。';
		}

		$meta_keywords = seo_fill($this->config->get('config_seo_featured_list_keywords'), $seo_replace);
		if ($meta_keywords === '') {
			$meta_keywords = seo_fill($this->config->get('config_seo_common_keywords'), $seo_replace);
		}

		$this->document->setTitle($meta_title);
		$this->document->setDescription($meta_description);
		$this->document->setKeywords($meta_keywords);

		$data['heading_title'] = $heading;

		$this->document->setUrl(($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI']);
		$this->document->setImage($this->model_tool_image->resize($this->config->get('config_image'), 600, 315));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $heading,
			'href' => $this->url->link('product/featured')
		);

		$data['products'] = array();

		$product_ids = array();

		$query = $this->db->query("SELECT setting FROM " . DB_PREFIX . "module WHERE code = 'featured'");

		foreach ($query->rows as $row) {
			$setting = json_decode($row['setting'], true);

			if (!empty($setting['product']) && (!isset($setting['status']) || $setting['status'])) {
				foreach ($setting['product'] as $product_id) {
					$product_ids[(int)$product_id] = (int)$product_id;
				}
			}
		}

		$width  = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
		$height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');

		foreach ($product_ids as $product_id) {
			$result = $this->model_catalog_product->getProduct($product_id);

			if ($result) {
				$data['products'][] = $this->model_catalog_product->handleSingleProduct($result, $width, $height);
			}
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('product/featured', $data));
	}
}
