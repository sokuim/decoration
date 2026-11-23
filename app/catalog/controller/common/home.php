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

		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
