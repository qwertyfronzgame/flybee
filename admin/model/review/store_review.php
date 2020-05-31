<?php
class ModelReviewStoreReview extends Model {
	public function addReview($data) {
		$this->event->trigger('pre.admin.store_review.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['author']) . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', rating = '" . (int)$data['rating'] . "', status = '" . (int)$data['status'] . "',link = '" .$this->db->escape(strip_tags($data['link'])) . "', admin_answer = '" .$this->db->escape(strip_tags($data['admin_answer'])) . "', date_added = '" . $this->db->escape($data['date_added']) . "'");

		$review_id = $this->db->getLastId();

		$this->cache->delete('product');

		$this->event->trigger('post.admin.store_review.add', $review_id);

		return $review_id;
	}

	public function editReview($review_id, $data) {
		$this->event->trigger('pre.admin.store_review.edit', $data);

		$this->db->query("UPDATE " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['author']) . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', rating = '" . (int)$data['rating'] . "', status = '" . (int)$data['status'] . "',link = '" .$this->db->escape(strip_tags($data['link'])) . "',  admin_answer = '" .$this->db->escape(strip_tags($data['admin_answer'])) . "', date_added = '" . $this->db->escape($data['date_added']) . "', date_modified = NOW() WHERE review_id = '" . (int)$review_id  . "'");

		$this->cache->delete('product');

		$this->event->trigger('post.admin.store_review.edit', $review_id);
	}

	public function deleteReview($review_id) {
		$this->event->trigger('pre.admin.store_review.delete', $review_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE review_id = '" . (int)$review_id . "'");

		$this->cache->delete('product');

		$this->event->trigger('post.admin.store_review.delete', $review_id);
	}

	public function getReview($review_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "review WHERE review_id = '" . (int)$review_id . "'");

		return $query->row;
	}

	public function getReviews($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "review WHERE product_id = 0";


		if (isset($data['filter_rating']) && !is_null($data['filter_rating'])) {
			$sql .= " AND rating = '" . (int)$data['filter_rating'] . "'";
		}

		if (!empty($data['filter_text'])) {
			$sql .= " AND text LIKE '" . $this->db->escape($data['filter_text']) . "%'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND author LIKE '" . $this->db->escape($data['filter_author']) . "%'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$sort_data = array(
			'author',
			'rating',
			'status',
			'date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

	public function getTotalReviews($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review WHERE product_id = 0";

		if (isset($data['filter_rating']) && !is_null($data['filter_rating'])) {
			$sql .= " AND rating = '" . (int)$data['filter_rating'] . "'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND author LIKE '" . $this->db->escape($data['filter_author']) . "%'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalReviewsAwaitingApproval() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review WHERE status = '0'");

		return $query->row['total'];
	}

	public function install() {
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET status=1 WHERE `name` LIKE'%Store Reviews Lite Back End%'");
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET status=1 WHERE `name` LIKE'%Store Reviews Lite Front End%'");
		$modifications = $this->load->controller('extension/modification/refresh');
	}

	public function uninstall() {
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET status=0 WHERE `name` LIKE'%Store Reviews Lite Back End%'");
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET status=0 WHERE `name` LIKE'%Store Reviews Lite Front End%'");
		$modifications = $this->load->controller('extension/modification/refresh');
	}

	protected function curl_get_contents($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	public function getNotifications($version, $module = 'module_sr_lite') {
		$language_code = $this->config->get('config_admin_language');
		$result = $this->curl_get_contents("http://vanstudio.co.ua/index.php?route=information/message&type=$module&version=$version&language_code=$language_code");

		if (stripos($result,'<html') !== false) {
			return '';
		}
		return $result;
	}
}