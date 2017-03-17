<?php
class IndexController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
        Navigation::activateItem('course/zert-settings');
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->course_id       = Request::get('cid');
        $this->course          = Course::find($this->course_id);

        PageLayout::setTitle($this->course->getFullname()." - " ._("Kontakt"));

        // $this->set_layout('layouts/base');
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
    }

    public function index_action()
    {
        global $perm;
        $zertifikatConfigEntry = ZertifikatConfigEntry::findOneBySQL('course_id = ?', array($this->course_id));
            if ($zertifikatConfigEntry){
            $this->mail = $zertifikatConfigEntry->getValue('contact_mail');
        }
        
        $this->formSettings = ZertifikatConfigEntry::findOneBySQL('course_id = ?', array($this->course_id));
        if($this->formSettings){
            $this->coursename = $this->course->getFullname();
            $this->mailto = $this->formSettings['contact_mail'];
        }
        $this->dozent = $perm->have_studip_perm('tutor', $this->course_id);
                //ContactForm::findBySQL
    }

     public function save_action(){
        
        $zertifikatConfigEntry = ZertifikatConfigEntry::findOneBySQL('course_id = ?', array($this->course_id));
        
        if (!$zertifikatConfigEntry){
            $zertifikatConfigEntry = new ZertifikatConfigEntry();
        }
        $zertifikatConfigEntry->contact_mail = Request::get('Mail');
        $zertifikatConfigEntry->course_id = $this->course_id;
           

            if ($zertifikatConfigEntry->store() !== false) {
                $message = MessageBox::success(_('Die Änderungen wurden übernommen.'));
                PageLayout::postMessage($message);
            }

            $this->redirect($this::url_for('/index'));
          
    }
    
    
    // customized #url_for for plugins
    public function url_for($to)
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    }
}
