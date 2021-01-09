<?php
class GrvHomeManagerController extends modExtraManagerController {
    public function process(array $scriptProperties = array()) {
        $html = file_get_contents(dirname(dirname(__FILE__)) . '/templates/home.html');
        $html .= '<script type="text/x-template" id="appconfigjs">'.file_get_contents(dirname(dirname(__FILE__)) . '/templates/appconfig.js').'</script>';
        return $html;
    }
    
    /**
     * The pagetitle to put in the <title> attribute.
     * @return null|string
     */
    public function getPageTitle() {
        return 'Packages and Custom Tables';
    }
}