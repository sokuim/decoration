<?php
class ControllerCatalogArticle extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/article');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/article');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_article->addArticle($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('catalog/article', 'user_token=' . $this->session->data['user_token']));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/article');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_article->editArticle($this->request->get['article_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('catalog/article', 'user_token=' . $this->session->data['user_token']));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/article');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $article_id) {
				$this->model_catalog_article->deleteArticle($article_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('catalog/article', 'user_token=' . $this->session->data['user_token']));
		}

		$this->getList();
	}

	protected function getList() {
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/article', 'user_token=' . $this->session->data['user_token'])
		);

		$data['add'] = $this->url->link('catalog/article/add', 'user_token=' . $this->session->data['user_token']);
		$data['delete'] = $this->url->link('catalog/article/delete', 'user_token=' . $this->session->data['user_token']);

		$data['articles'] = array();

		$filter_data = array(
			'start' => 0,
			'limit' => 100
		);

		$results = $this->model_catalog_article->getArticles($filter_data);

		foreach ($results as $result) {
			$data['articles'][] = array(
				'article_id' => $result['article_id'],
				'title'      => $result['title'],
				'sort_order' => $result['sort_order'],
				'status'     => $result['status'],
				'edit'       => $this->url->link('catalog/article/edit', 'user_token=' . $this->session->data['user_token'] . '&article_id=' . $result['article_id'])
			);
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['success'] = isset($this->session->data['success']) ? $this->session->data['success'] : '';

		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		$data['selected'] = isset($this->request->post['selected']) ? (array)$this->request->post['selected'] : array();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/article_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['article_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_title'] = isset($this->error['title']) ? $this->error['title'] : '';
		$data['error_description'] = isset($this->error['description']) ? $this->error['description'] : '';
		$data['error_keyword'] = isset($this->error['keyword']) ? $this->error['keyword'] : array();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/article', 'user_token=' . $this->session->data['user_token'])
		);

		if (!isset($this->request->get['article_id'])) {
			$data['action'] = $this->url->link('catalog/article/add', 'user_token=' . $this->session->data['user_token']);
		} else {
			$data['action'] = $this->url->link('catalog/article/edit', 'user_token=' . $this->session->data['user_token'] . '&article_id=' . $this->request->get['article_id']);
		}

		$data['cancel'] = $this->url->link('catalog/article', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		$article_info = array();

		if (isset($this->request->get['article_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$article_info = $this->model_catalog_article->getArticle($this->request->get['article_id']);
		}

		foreach (array('title', 'description', 'meta_title', 'meta_description', 'meta_keyword') as $field) {
			if (isset($this->request->post[$field])) {
				$data[$field] = $this->request->post[$field];
			} elseif (!empty($article_info)) {
				$data[$field] = $article_info[$field];
			} else {
				$data[$field] = '';
			}
		}

		if (isset($this->request->post['article_category_id'])) {
			$data['article_category_id'] = $this->request->post['article_category_id'];
		} elseif (!empty($article_info)) {
			$data['article_category_id'] = $article_info['article_category_id'];
		} else {
			$data['article_category_id'] = 0;
		}

		$this->load->model('catalog/article_category');

		$data['article_categories'] = $this->model_catalog_article_category->getCategories();

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($article_info)) {
			$data['status'] = $article_info['status'];
		} else {
			$data['status'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($article_info)) {
			$data['sort_order'] = $article_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (isset($this->request->post['article_seo_url'])) {
			$data['article_seo_url'] = $this->request->post['article_seo_url'];
		} elseif (isset($this->request->get['article_id'])) {
			$data['article_seo_url'] = $this->model_catalog_article->getArticleSeoUrls($this->request->get['article_id']);
		} else {
			$data['article_seo_url'] = array();
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/article_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/article')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['title']) < 1) || (utf8_strlen($this->request->post['title']) > 255)) {
			$this->error['title'] = $this->language->get('error_title');
		}

		if ((utf8_strlen($this->request->post['description']) < 3)) {
			$this->error['description'] = $this->language->get('error_description');
		}

		if (isset($this->request->post['article_seo_url'])) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['article_seo_url'] as $store_id => $keyword) {
				if (!empty($keyword)) {
					$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

					foreach ($seo_urls as $seo_url) {
						if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['article_id']) || ($seo_url['query'] != 'article_id=' . $this->request->get['article_id']))) {
							$this->error['keyword'][$store_id] = $this->language->get('error_keyword');
						}
					}
				}
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/article')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
