<?php
class ControllerExtensionModuleShoputilsAntispamContact extends Controller {
    private $error = array();
    const MAX_LAST_LOG_LINES = 500;
    const FILE_NAME_LOG = 'contact.log';
    const FILE_NAME_REGISTR_LOG = 'registration.log';
    const FILE_NAME_AFFILIATE_LOG = 'affiliate.log';

    public function index() {
        $this->load->language('extension/module/shoputils_antispam_contact');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('m_shoputils_antispam_contact', $this->request->post);
            $this->session->data['success'] = sprintf($this->language->get('text_success'), $this->language->get('heading_title'));
            $this->response->redirect($this->makeUrl('extension/extension', 'type=module'));
        }

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addStyle('view/stylesheet/shoputils_antispam.css');

        $data = $this->_setData(array(
            'heading_title',
            'button_save',
            'button_cancel',
            'button_clear',
            'button_download',
            'tab_general',
            'tab_log',
            'text_enabled',
            'text_disabled',
            'text_confirm',
            'text_loading',
            'text_contact',
            'text_registr',
            'text_affiliate',
            'entry_status',
            'entry_registr_status',
            'entry_affiliate_status',
            'entry_word',
            'entry_ip',
            'entry_not_found',
            'entry_log',
            'entry_log_file',
            'help_word',
            'help_ip',
            'help_not_found',
            'help_log'              => sprintf($this->language->get('help_log'), self::FILE_NAME_LOG),
            'help_registr_log'      => sprintf($this->language->get('help_registr_log'), self::FILE_NAME_REGISTR_LOG),
            'help_affiliate_log'    => sprintf($this->language->get('help_affiliate_log'), self::FILE_NAME_AFFILIATE_LOG),
            'help_log_file'         => sprintf($this->language->get('help_log_file'), self::MAX_LAST_LOG_LINES),
            'action'                => $this->makeUrl('extension/module/shoputils_antispam_contact'),
            'cancel'                => $this->makeUrl('extension/extension', 'type=module'),
            'download'              => $this->makeUrl('extension/module/shoputils_antispam_contact/downloadLog'),
            'registr_download'      => $this->makeUrl('extension/module/shoputils_antispam_contact/downloadRegistrLog'),
            'affiliate_download'    => $this->makeUrl('extension/module/shoputils_antispam_contact/downloadAffiliateLog'),
            'clear_log'             => $this->makeUrl('extension/module/shoputils_antispam_contact/clearLog'),
            'registr_clear_log'     => $this->makeUrl('extension/module/shoputils_antispam_contact/clearRegistrLog'),
            'affiliate_clear_log'   => $this->makeUrl('extension/module/shoputils_antispam_contact/clearAffiliateLog'),
            'text_copyright'        => sprintf($this->language->get('text_copyright'), $this->language->get('heading_title'), date('Y', time())),
            'error_warning'         => isset($this->error['warning']) ? $this->error['warning'] : '',
            'log_filename'          => self::FILE_NAME_LOG,
            'log_registr_filename'  => self::FILE_NAME_REGISTR_LOG,
            'log_affiliate_filename'  => self::FILE_NAME_AFFILIATE_LOG,
            'log_lines'             => $this->readLastLines(DIR_LOGS . self::FILE_NAME_LOG, self::MAX_LAST_LOG_LINES),
            'log_registr_lines'     => $this->readLastLines(DIR_LOGS . self::FILE_NAME_REGISTR_LOG, self::MAX_LAST_LOG_LINES),
            'log_affiliate_lines'   => $this->readLastLines(DIR_LOGS . self::FILE_NAME_AFFILIATE_LOG, self::MAX_LAST_LOG_LINES)
        ));

        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }

        $data['logs'] = array(
            '0' => $this->language->get('text_log_off'),
            '1' => $this->language->get('text_log_spam'),
            '2' => $this->language->get('text_log_full'),
        );

        $data['breadcrumbs'][] = array(
            'href'      => $this->makeUrl('common/dashboard'),
            'text'      => $this->language->get('text_home')
        );

        $data['breadcrumbs'][] = array(
            'href'      => $this->makeUrl('extension/extension', 'type=module'),
            'text'      => $this->language->get('text_extension')
        );

        $data['breadcrumbs'][] = array(
           'href'      => $this->makeUrl('extension/module/shoputils_antispam_contact'),
           'text'      => $this->language->get('heading_title')
        );

        $data = array_merge($data, $this->_updateData(
            array(
                 'm_shoputils_antispam_contact_status',
                 'm_shoputils_antispam_contact_registr_status',
                 'm_shoputils_antispam_contact_affiliate_status',
                 'm_shoputils_antispam_contact_word',
                 'm_shoputils_antispam_contact_ip',
                 'm_shoputils_antispam_contact_not_found',
                 'm_shoputils_antispam_contact_contact_log',
                 'm_shoputils_antispam_contact_registr_log',
                 'm_shoputils_antispam_contact_affiliate_log'
            )
        ));

        $data['header']       = $this->load->controller('common/header');
        $data['column_left']  = $this->load->controller('common/column_left');
        $data['footer']       = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/shoputils_antispam_contact', $data));
    }

    public function clearLog() {
        $this->load->language('extension/module/shoputils_antispam_contact');

        $json = array();

        if ($this->validatePermission()) {
            if (is_file(DIR_LOGS . self::FILE_NAME_LOG)) {
                @unlink(DIR_LOGS . self::FILE_NAME_LOG);
            }
            $json['success'] = $this->language->get('text_clear_log_success');
        } else {
            $json['error'] = $this->language->get('error_clear_log');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function clearRegistrLog() {
        $this->load->language('extension/module/shoputils_antispam_contact');

        $json = array();

        if ($this->validatePermission()) {
            if (is_file(DIR_LOGS . self::FILE_NAME_REGISTR_LOG)) {
                @unlink(DIR_LOGS . self::FILE_NAME_REGISTR_LOG);
            }
            $json['success'] = $this->language->get('text_clear_log_success');
        } else {
            $json['error'] = $this->language->get('error_clear_log');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function clearAffiliateLog() {
        $this->load->language('extension/module/shoputils_antispam_contact');

        $json = array();

        if ($this->validatePermission()) {
            if (is_file(DIR_LOGS . self::FILE_NAME_AFFILIATE_LOG)) {
                @unlink(DIR_LOGS . self::FILE_NAME_AFFILIATE_LOG);
            }
            $json['success'] = $this->language->get('text_clear_log_success');
        } else {
            $json['error'] = $this->language->get('error_clear_log');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function downloadLog() {
        $file = DIR_LOGS . self::FILE_NAME_LOG;

        if (is_file($file) && filesize($file) > 0) {
            $this->response->addheader('Pragma: public');
            $this->response->addheader('Expires: 0');
            $this->response->addheader('Content-Description: File Transfer');
            $this->response->addheader('Content-Type: application/octet-stream');
            $this->response->addheader('Content-Disposition: attachment; filename="' . self::FILE_NAME_LOG . '"');
            $this->response->addheader('Content-Transfer-Encoding: binary');

            $this->response->setOutput(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
        } else {
            $this->load->language('extension/module/shoputils_antispam_contact');
            $this->session->data['error'] = sprintf($this->language->get('error_warning'), basename($file));
            $this->response->redirect($this->makeUrl('extension/module/shoputils_antispam_contact'));
        }
    }

    public function downloadRegistrLog() {
        $file = DIR_LOGS . self::FILE_NAME_REGISTR_LOG;

        if (is_file($file) && filesize($file) > 0) {
            $this->response->addheader('Pragma: public');
            $this->response->addheader('Expires: 0');
            $this->response->addheader('Content-Description: File Transfer');
            $this->response->addheader('Content-Type: application/octet-stream');
            $this->response->addheader('Content-Disposition: attachment; filename="' . self::FILE_NAME_REGISTR_LOG . '"');
            $this->response->addheader('Content-Transfer-Encoding: binary');

            $this->response->setOutput(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
        } else {
            $this->load->language('module/shoputils_antispam_contact');
            $this->session->data['error'] = sprintf($this->language->get('error_warning'), basename($file));
            $this->response->redirect($this->makeUrl('extension/module/shoputils_antispam_contact'));
        }
    }

    public function downloadAffiliateLog() {
        $file = DIR_LOGS . self::FILE_NAME_AFFILIATE_LOG;

        if (is_file($file) && filesize($file) > 0) {
            $this->response->addheader('Pragma: public');
            $this->response->addheader('Expires: 0');
            $this->response->addheader('Content-Description: File Transfer');
            $this->response->addheader('Content-Type: application/octet-stream');
            $this->response->addheader('Content-Disposition: attachment; filename="' . self::FILE_NAME_AFFILIATE_LOG . '"');
            $this->response->addheader('Content-Transfer-Encoding: binary');

            $this->response->setOutput(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
        } else {
            $this->load->language('extension/module/shoputils_antispam_contact');
            $this->session->data['error'] = sprintf($this->language->get('error_warning'), basename($file));
            $this->response->redirect($this->makeUrl('extension/module/shoputils_antispam_contact'));
        }
    }

    protected function validate() {
        if (!$this->validatePermission()) {
            $this->error['warning'] = sprintf($this->language->get('error_permission'), $this->language->get('heading_title'));
        }

        return !$this->error;
    }

    protected function validatePermission() {
        return $this->user->hasPermission('modify', 'extension/module/shoputils_antispam_contact');
    }

    protected function _setData($values) {
        $data = array();
        foreach ($values as $key => $value) {
            if (is_int($key)) {
                $data[$value] = $this->language->get($value);
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    protected function _updateData($keys, $info = array()) {
        $data = array();
        foreach ($keys as $key) {
            if (isset($this->request->post[$key])) {
                $data[$key] = $this->request->post[$key];
            } elseif (isset($info[$key])) {
                $data[$key] = $info[$key];
            } else {
                $data[$key] = $this->config->get($key);
            }
        }
        return $data;
    }

    protected function makeUrl($route, $url = ''){
        return str_replace('&amp;', '&', $this->url->link($route, $url.'&token=' . $this->session->data['token'], 'SSL'));
    }

    protected function readLastLines($filename, $lines) {
        if (!is_file($filename)) {
            return array();
        }
        $handle = @fopen($filename, "r");
        if (!$handle) {
            return array();
        }
        $linecounter = $lines;
        $pos = -1;
        $beginning = false;
        $text = array();

        while ($linecounter > 0) {
            $t = " ";

            while ($t != "\n") {
                /* if fseek() returns -1 we need to break the cycle*/
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }

            $linecounter--;

            if ($beginning) {
                rewind($handle);
            }

            $text[$lines - $linecounter - 1] = fgets($handle);

            if ($beginning) {
                break;
            }
        }
        fclose($handle);

        return array_reverse($text);
    }
}
?>