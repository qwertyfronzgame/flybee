<?php

class ModelReviewStoreReview extends Model
{
    public function addReview($data)
    {
        $data['link'] = !empty($data['link']) ? $data['link'] : '';


        $this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['name']) . "', link = '" . $this->db->escape($data['link']) . "', customer_id = '" . (int)$this->customer->getId() . "', product_id = '0', text = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW()");

        $review_id = $this->db->getLastId();

        if ($this->config->get('config_review_mail')) {
            $this->load->language('review/review_mail');

            $subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

            $message = $this->language->get('text_waiting') . "\n";
            $message .= sprintf($this->language->get('text_reviewer'), $this->db->escape(strip_tags($data['name']))) . "\n";
            if ($data['rating']) {
                $message .= sprintf($this->language->get('text_rating'), $this->db->escape(strip_tags($data['rating']))) . "\n";
            }
            $message .= $this->language->get('text_review') . "\n";
            $message .= $this->db->escape(strip_tags($data['text'])) . "\n\n";


                $mail = new Mail();
                $mail->protocol = $this->config->get('config_mail_protocol');
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_host')?$this->config->get('config_mail_smtp_host'):$this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');


            $mail->setTo($this->config->get('config_email'));
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender($this->config->get('config_name'));
            $mail->setSubject($subject);
            $mail->setText($message);
            $mail->send();

            // Send to additional alert emails
            $emails = explode(',', $this->config->get('config_mail_alert'));

            foreach ($emails as $email) {
                if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }
        }
       // $this->event->trigger('post.store_review.add', $review_id);
    }

    public function getReviews($start = 0, $limit = 20)
    {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 20;
        }

        $query = $this->db->query("SELECT r.review_id, r.customer_id, r.author,r.link,r.admin_answer, r.rating, r.text, r.date_added FROM " . DB_PREFIX . "review r WHERE r.product_id = '0'  AND r.status = '1'  ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    public function getModuleReviews($start = 0, $limit = 20, $order = 0)
    {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 20;
        }
        switch ($order) {
            case 0: {
                $sql = "ORDER BY date_added  DESC LIMIT " . (int)$start . "," . (int)$limit;
                break;
            }
            case 1: {
                $sql = "ORDER BY RAND() DESC LIMIT " . (int)$start . "," . (int)$limit;
                break;
            }
            default: {
                $sql = "ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit;
            }
        }

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "review WHERE product_id = '0'  AND status = '1'  " . $sql);

        return $query->rows;
    }

    public function getTotalReviews()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r WHERE r.product_id = '0' AND r.status = '1'");

        return $query->row['total'];
    }

}