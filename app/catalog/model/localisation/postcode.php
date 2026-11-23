<?php
class ModelLocalisationPostcode extends Model {
	public function getAddressByPostcode($postcode) {
		$result = array(
			'zone_id'   => 0,
			'city_id'   => 0,
			'county_id' => 0
		);

		$postcode = preg_replace('/[^0-9]/', '', (string)$postcode);

		if (strlen($postcode) != 7) {
			return $result;
		}

		// 先按町域精确匹配
		$query = $this->db->query("SELECT county_id FROM " . DB_PREFIX . "postcode_to_county WHERE postcode = '" . $this->db->escape($postcode) . "'");

		if ($query->num_rows) {
			$county_id = (int)$query->row['county_id'];

			$county_query = $this->db->query("SELECT city_id FROM " . DB_PREFIX . "county WHERE county_id = '" . $county_id . "'");

			if ($county_query->num_rows) {
				$result['county_id'] = $county_id;
				$result['city_id'] = (int)$county_query->row['city_id'];

				$city_query = $this->db->query("SELECT zone_id FROM " . DB_PREFIX . "city WHERE city_id = '" . (int)$county_query->row['city_id'] . "'");

				if ($city_query->num_rows) {
					$result['zone_id'] = (int)$city_query->row['zone_id'];
				}
			}
		} else {
			// 町域不存在时，兜底查市区町村
			$query = $this->db->query("SELECT city_id FROM " . DB_PREFIX . "postcode_to_city WHERE postcode = '" . $this->db->escape($postcode) . "'");

			if ($query->num_rows) {
				$result['city_id'] = (int)$query->row['city_id'];

				$city_query = $this->db->query("SELECT zone_id FROM " . DB_PREFIX . "city WHERE city_id = '" . (int)$query->row['city_id'] . "'");

				if ($city_query->num_rows) {
					$result['zone_id'] = (int)$city_query->row['zone_id'];
				}
			}
		}

		return $result;
	}
}
