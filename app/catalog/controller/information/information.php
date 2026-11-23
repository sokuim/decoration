<?php
class ControllerInformationInformation extends Controller {
	public function index() {
		$this->load->language('information/information');

		$this->load->model('catalog/information');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$site_name = $this->config->get('config_name');
			$information_title = html_entity_decode($information_info['title'], ENT_QUOTES, 'UTF-8');

			$seo_replace = array(
				'title'     => $information_title,
				'site_name' => $site_name
			);

			// 标题：后台未填写或仅重复标题时，使用后台配置的标题模板
			$meta_title = trim(html_entity_decode($information_info['meta_title'], ENT_QUOTES, 'UTF-8'));

			if ($meta_title === '' || $meta_title === $information_title) {
				$meta_title = seo_fill(seo_config('config_seo_information_title'), $seo_replace);

				if ($meta_title === '') {
					$meta_title = $information_title . '｜' . $site_name;
				}
			} elseif (mb_strpos($meta_title, $site_name) === false) {
				$meta_title .= '｜' . $site_name;
			}

			// 描述：后台未填写时，从正文自动摘要 + 后台配置的补充文案
			$meta_description = trim(html_entity_decode($information_info['meta_description'], ENT_QUOTES, 'UTF-8'));

			if ($meta_description === '') {
				$plain_description = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8'))));

				if ($plain_description !== '') {
					$meta_description = rtrim(utf8_substr($plain_description, 0, 120), '。.!！') . '。';
				}

				$meta_description .= seo_fill(seo_config('config_seo_information_description'), $seo_replace);
			}

			// 关键词：后台未填写时，由标题与后台配置的关键词组合
			$meta_keywords = trim(html_entity_decode($information_info['meta_keyword'], ENT_QUOTES, 'UTF-8'));

			if ($meta_keywords === '') {
				$keyword_terms = array(
					$information_title,
					seo_fill(seo_config('config_seo_information_keywords'), $seo_replace),
					seo_fill(seo_config('config_seo_common_keywords'), $seo_replace)
				);

				$meta_keywords = implode(',', array_unique(array_filter(array_map('trim', explode(',', implode(',', $keyword_terms))))));
			}

			$information_url = $this->url->link('information/information', 'information_id=' . $information_id);

			$this->document->setTitle($meta_title);
			$this->document->setDescription($meta_description);
			$this->document->setKeywords($meta_keywords);
			$this->document->addLink($information_url, 'canonical');

			$data['breadcrumbs'][] = array(
				'text' => $information_info['title'],
				'href' => $this->url->link('information/information', 'information_id=' .  $information_id)
			);

			// 结构化数据：WebPage
			$this->document->addStructuredData(array(
				'@context'    => 'https://schema.org',
				'@type'       => 'WebPage',
				'name'        => $information_title,
				'description' => $meta_description,
				'url'         => html_entity_decode($information_url, ENT_QUOTES, 'UTF-8'),
				'inLanguage'  => 'ja-JP',
				'isPartOf'    => array(
					'@type' => 'WebSite',
					'name'  => $site_name,
					'url'   => html_entity_decode($this->config->get('config_url'), ENT_QUOTES, 'UTF-8')
				)
			));

			// 结构化数据：BreadcrumbList
			$breadcrumb_items = array();
			$position = 1;

			foreach ($data['breadcrumbs'] as $breadcrumb) {
				$breadcrumb_items[] = array(
					'@type'    => 'ListItem',
					'position' => $position++,
					'name'     => html_entity_decode($breadcrumb['text'], ENT_QUOTES, 'UTF-8'),
					'item'     => html_entity_decode($breadcrumb['href'], ENT_QUOTES, 'UTF-8')
				);
			}

			$this->document->addStructuredData(array(
				'@context'        => 'https://schema.org',
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $breadcrumb_items
			));

			$data['heading_title'] = $information_info['title'];

			$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/information', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'information_id=' . $information_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function agree() {
		$this->load->model('catalog/information');

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$output = '';

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->setOutput($output);
	}
}
