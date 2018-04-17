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
        
        $seminar = new Seminar($this->course_id);
        $this->members = $seminar->getMembers($status= 'autor');
        
        foreach ($this->members as $member){
            $this->members[$member['user_id']]['mail_sent'] = ZertifikatSentEntry::findOneBySQL('user_id = :user_id AND course_id = :course_id', array(':course_id' => $this->course_id, ':user_id' => $member['user_id']));
        }
        
        
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
    
     public function sendMail_action($user_id)
    {

        $seminar_id = Course::findCurrent()->seminar_id;
        $course = new Seminar($seminar_id);
        $institute = new Institute($course->getInstitutId());
        $zertifikatConfigEntry = ZertifikatConfigEntry::findOneBySQL('course_id = ?', array($seminar_id));
        $contact_mail = $zertifikatConfigEntry->getValue('contact_mail');
        
        $filepath = $this->pdf_action($user, $course->name, $institute->name);

        $dateien = array($filepath);
        
        $mailtext = '<html>
          

            <body>

            <h2>Teilnahmezertifikat für ' . $user . ':</h2>

            <p>Im Anhang finden Sie ein Teilnahmezertifikat für den/die Teilnehmer/in einer Onlineschulung</p>

            </body>
            </html>
            ';

            $empfaenger = $contact_mail;//$contact_mail; //Mailadresse
            //$absender   = "asudau@uos.de";
            $betreff    = "Teilnahmezertifikat für " . $user . " für erfolgreiche Teilnahme an Mitarbeiterschulung";
            $filename = 'zertifikat_'. $this->clear_string($user) . '.pdf';

            $mail = new StudipMail();
            $sent =  $mail->addRecipient($empfaenger)
                //->addRecipient('elmar.ludwig@uos.de', 'Elmar Ludwig', 'Cc')
                 ->setReplyToEmail('')
                 ->setSenderEmail('')
                 ->setSenderName('E-Learning - DSO - Datenschutz')
                 ->setSubject($betreff)
                 ->addFileAttachment($filepath, $name = $filename)
                 ->setBodyHtml($mailtext)
                 ->setBodyHtml(strip_tags($mailtext))  
                 ->send();
 
            if ($sent){
                PageLayout::postMessage(MessageBox::success(sprintf(_('e-Mail gesendet'), $sem_name)));
                $this->redirect('index');
            } else {
                 PageLayout::postMessage(MessageBox::success(sprintf(_('Senden der eMail fehlgeschlagen'), $sem_name)));
                $this->redirect('index');
            }
            
    }
    
    
    private function pdf_action($user, $seminar, $institute)
    {
        global $STUDIP_BASE_PATH, $TMP_PATH;
        require_once $STUDIP_BASE_PATH.'/vendor/tcpdf/tcpdf.php';
        require_once $STUDIP_BASE_PATH.'/public/plugins_packages/elan-ev/Zertifikats_Plugin/models/zertifikatpdf.class.php';
     
        
        // create new PDF document
        $pdf = new zertifikatpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'ISO-8859-1', false);
        $pdf->SetTopMargin(40);
        $pdf->SetLeftMargin(20);
        $pdf->SetRightMargin(20);
        $pdf->AddPage();
        $x = 35;
        $y = 50;
        $w = 60;
        $h = 60;
        
        
            //$html = $this->htmlentitiesOutsideHTMLTags($note_content[0], ENT_HTML401);
            $html = 'Hiermit wird bescheinigt, dass <br><br><br><br><b>Herr/Frau '. $user 
                    . '</b><br><br><br>am ' . date("d.m.Y",time()) 
                    . '<br><br><br>an der Mitarbeiterschulung der Fa. ' . $institute
                    . '</b><br><br><br>zum Thema<br>'
                    . '<h1 style="text-align:center">' . $seminar . '</h1>'
                    . '<br><br><br><br><br>erfolgreich teilgenommen hat.'
                    . '<br><br><br><br><br><br><br><br>Stephan Beume<br>'
                    . 'Rechtsanwalt<br>'
                    . 'Fachanwalt für Arbeitsrecht<br>'
                    . 'Datenschutzbeauftragter (TÜV)<br>';
            $pdf->writeHTMLCell('0', '0', '30', '80', studip_utf8encode($html), false, 0, false, 0);
        
        $fileid = time();   
        //$pdf->Output('/tmp/zertifikat'. $fileid, 'F');
        //return '/tmp/zertifikat'. $fileid;
        $pdf->Output( $TMP_PATH .'/zertifikat' . $fileid, 'F');
        return $TMP_PATH . '/zertifikat' . $fileid;
        //exit("delivering pdf file");
    }
    
    
     function clear_string($str){
        $search = array("ä", "ö", "ü", "ß", "Ä", "Ö",
                "Ü", "&", "é", "á", "ó", " ");
        $replace = array("ae", "oe", "ue", "ss", "Ae", "Oe",
                 "Ue", "und", "e", "a", "o", "_");
        $str = str_replace($search, $replace, $str);
        //$str = strtolower(preg_replace("/[^a-zA-Z0-9]+/", trim($how), $str));
        return $str;
}
}
