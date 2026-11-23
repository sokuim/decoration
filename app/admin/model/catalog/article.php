<?php
class ModelCatalogArticle extends Model {
	public function addArticle($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "article SET article_category_id = '" . (int)$data['article_category_id'] . "', title = '" . $this->db->escape($data['title']) . "', description = '" . $this->db->escape($data['description']) . "', meta_title = '" . $this->db->escape($data['meta_title']) . "', meta_description = '" . $this->db->escape($data['meta_description']) . "', meta_keyword = '" . $this->db->escape($data['meta_keyword']) . "', image = '" . $this->db->escape(isset($data['image']) ? (string)$data['image'] : '') . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");

		$article_id = $this->db->getLastId();

		// SEO URL
		if (isset($data['article_seo_url'])) {
			foreach ($data['article_seo_url'] as $store_id => $keyword) {
				if (!empty($keyword)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', query = 'article_id=" . (int)$article_id . "', keyword = '" . $this->db->escape($keyword) . "'");
				}
			}
		}

		return $article_id;
	}

	public function editArticle($article_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "article SET article_category_id = '" . (int)$data['article_category_id'] . "', title = '" . $this->db->escape($data['title']) . "', description = '" . $this->db->escape($data['description']) . "', meta_title = '" . $this->db->escape($data['meta_title']) . "', meta_description = '" . $this->db->escape($data['meta_description']) . "', meta_keyword = '" . $this->db->escape($data['meta_keyword']) . "', image = '" . $this->db->escape(isset($data['image']) ? (string)$data['image'] : '') . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE article_id = '" . (int)$article_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'article_id=" . (int)$article_id . "'");

		if (isset($data['article_seo_url'])) {
			foreach ($data['article_seo_url'] as $store_id => $keyword) {
				if (!empty($keyword)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', query = 'article_id=" . (int)$article_id . "', keyword = '" . $this->db->escape($keyword) . "'");
				}
			}
		}
	}

	public function deleteArticle($article_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "article WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'article_id=" . (int)$article_id . "'");
	}

	public function getArticle($article_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "article WHERE article_id = '" . (int)$article_id . "'");

		return $query->row;
	}

	public function getArticles($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "article";

		$where = array();

		if (!empty($data['filter_title'])) {
			$where[] = "title LIKE '%" . $this->db->escape((string)$data['filter_title']) . "%'";
		}

		if (isset($data['filter_article_category_id']) && $data['filter_article_category_id'] != '') {
			$where[] = "article_category_id = '" . (int)$data['filter_article_category_id'] . "'";
		}

		if ($where) {
			$sql .= " WHERE " . implode(' AND ', $where);
		}

		$sort_data = array(
			'title',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalArticles($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "article";

		if (isset($data['filter_article_category_id']) && $data['filter_article_category_id'] != '') {
			$sql .= " WHERE article_category_id = '" . (int)$data['filter_article_category_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getArticleSeoUrls($article_id) {
		$article_seo_url_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'article_id=" . (int)$article_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

		foreach ($query->rows as $result) {
			$article_seo_url_data[$result['store_id']] = $result['keyword'];
		}

		return $article_seo_url_data;
	}
}
