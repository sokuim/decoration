<?php
class ControllerCommonHome extends Controller {
	public function index() {

		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		$this->document->addLink($this->config->get('config_url'), 'canonical');

		$site_name = $this->config->get('config_name');
		$site_url = html_entity_decode($this->config->get('config_url'), ENT_QUOTES, 'UTF-8');
		$logo = $site_url . 'image/' . $this->config->get('config_logo');
		$alternate_name = trim($this->config->get('config_alternate_name'));

		// WebSite
		$website_data = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'WebSite',
			'name'            => $site_name,
			'url'             => $site_url,
			'inLanguage'      => 'ja-JP',
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => html_entity_decode($this->url->link('product/search'), ENT_QUOTES, 'UTF-8') . '&search={search_term_string}'
				),
				'query-input' => 'required name=search_term_string'
			)
		);

		if ($alternate_name !== '') {
			$website_data['alternateName'] = $alternate_name;
		}

		$this->document->addStructuredData($website_data);

		// Organization
		$this->document->addStructuredData(array(
			'@context' => 'https://schema.org',
			'@type'    => 'Organization',
			'name'     => $site_name,
			'url'      => $site_url,
			'logo'     => $logo,
			'address'  => array(
				'@type'           => 'PostalAddress',
				'addressCountry'  => 'JP'
			)
		));

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		// 热门搜索（注目ワードから探す）
		$data['popular_search'] = '';

		$this->load->model('setting/module');

		foreach ($this->model_setting_module->getModulesByCode('popular_search') as $popular_search_module) {
			$popular_search_setting = $popular_search_module['setting'] ? json_decode($popular_search_module['setting'], true) : array();

			$popular_search = $this->load->controller('extension/module/popular_search', $popular_search_setting);

			if ($popular_search) {
				$data['popular_search'] = $popular_search;
				break;
			}
		}

		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
