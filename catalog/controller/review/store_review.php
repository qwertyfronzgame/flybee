<?php
class ControllerReviewStoreReview extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('review/store_review');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $this->document->setTitle($data['heading_title']);

     /*   $this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
*/
        $this->document->addStyle('catalog/view/theme/default/stylesheet/reviews.css');


        $data['text_write'] =  $this->language->get('text_write');
        $data['text_note'] = $this->language->get('text_note');
        $data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'));
        $data['text_loading'] = $this->language->get('text_loading');

        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_admin_answer'] = $this->language->get('entry_admin_answer');
        $data['store_name'] =$this->config->get('config_name');
        $data['entry_review'] = $this->language->get('entry_review');
        $data['entry_rating'] = $this->language->get('entry_rating');
        $data['entry_good'] = $this->language->get('entry_good');
        $data['entry_bad'] = $this->language->get('entry_bad');

        $data['button_continue'] = $this->language->get('button_continue');

        $this->load->model('review/store_review');

        $data['review_status'] = $this->config->get('config_review_status');

        if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
            $data['review_guest'] = true;
        } else {
            $data['review_guest'] = false;
        }

        if ($this->customer->isLogged()) {
            $data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
        } else {
            $data['customer_name'] = '';
        }

        $data['breadcrumbs'][] = array(
            'text' => $data['heading_title'],
            'href' => $this->url->link('review/store_review')
        );


            if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('sr_lite', (array)$this->config->get('config_captcha_page'))) {
                $data['captcha'] = $this->load->controller('captcha/' . $this->config->get('config_captcha'));
            } else {
                $data['captcha'] = '';
            }


        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');


            $this->response->setOutput($this->load->view('review/store_review', $data));

    }

    public function review() {
        $this->load->language('review/store_review');
        $this->load->model('review/store_review');

        $data['text_no_reviews'] = $this->language->get('text_no_reviews');

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['reviews'] = array();

        $review_total = $this->model_review_store_review->getTotalReviews();

        $results = $this->model_review_store_review->getReviews(($page - 1) * 5, 5);

        foreach ($results as $result) {
            $data['reviews'][] = array(
                'author'     => $result['author'],
                'link'     => $result['link'],
                'admin_answer'     => $result['admin_answer'],
                'text'       => nl2br($result['text']),
                'rating'     => (int)$result['rating'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            );
        }

        $pagination = new Pagination();

        $pagination->total = $review_total;
        $pagination->page = $page;
        $pagination->limit = 5;
        $pagination->url = 'index.php?route=review/store_review/review&page={page}';

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));


            $this->response->setOutput($this->load->view('review/store_review_list', $data));

    }

    public function write()
    {
        $this->load->language('review/store_review');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $data['error_name'] =  $this->language->get('error_name');
            $data['error_text'] =  $this->language->get('error_text');
            $data['error_rating'] =  $this->language->get('error_rating');
            $data['error_captcha'] =  $this->language->get('error_captcha');
            $data['text_success'] = $this->language->get('text_success');


            if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
                $json['error'] = $data['error_name'];
            }

            if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 3000)) {
                $json['error'] = $data['error_text'];
            }
            if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
                $json['error'] = $data['error_rating'];
            }

            if ($this->config->get('config_google_captcha_status')) {
                $recaptcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($this->config->get('config_google_captcha_secret')) . '&response=' . $this->request->post['g-recaptcha-response'] . '&remoteip=' . $this->request->server['REMOTE_ADDR']);

                $recaptcha = json_decode($recaptcha, true);

                if (!$recaptcha['success']) {
                    $json['error'] = $data['error_captcha'];
                }
            }

            if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('sr_lite', (array)$this->config->get('config_captcha_page'))) {
                $captcha = $this->load->controller('captcha/' . $this->config->get('config_captcha') . '/validate');

                if ($captcha) {
                    $json['error'] = $captcha;
                }
            }

            if (!isset($json['error'])) {
                $this->load->model('review/store_review');
                $json['success'] = $data['text_success'];

                $this->model_review_store_review->addReview($this->request->post);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}