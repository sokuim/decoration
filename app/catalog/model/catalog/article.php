<?php
class ModelCatalogArticle extends Model {
	public function getArticle($article_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "article WHERE article_id = '" . (int)$article_id . "' AND status = '1'");

		return $query->row;
	}

	public function getArticles($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "article WHERE status = '1'";

		if (!empty($data['filter_article_category_id'])) {
			$sql .= " AND article_category_id = '" . (int)$data['filter_article_category_id'] . "'";
		}

		$sort_data = array(
			'title',
			'sort_order',
			'date_added'
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
				$data['limit'] = 10;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getArticleCategory($article_category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_category WHERE article_category_id = '" . (int)$article_category_id . "' AND status = '1'");

		return $query->row;
	}

	public function getArticleCategories() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_category WHERE status = '1' ORDER BY sort_order, name ASC");

		return $query->rows;
	}

	public function getTotalArticles($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "article WHERE status = '1'";

		if (!empty($data['filter_article_category_id'])) {
			$sql .= " AND article_category_id = '" . (int)$data['filter_article_category_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
}
