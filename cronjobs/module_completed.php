<?php
/**
* preliminary_participants.php
*
* @author Till Glöggler <tgloeggl@uos.de>
* @access public
*/
require_once 'lib/classes/CronJob.class.php';


class ModuleCompleted extends CronJob
{

    public static function getName()
    {
        return dgettext('Zertifikat', 'Zertifikat - Zertifikat für Nutzer verschicken');
    }

    public static function getDescription()
    {
        return dgettext('Zertifikat', 'Sendet Teilnahmezertifikat für Nutzer welche die Lernmodule vollständig bearbeitet haben.');
    }
    
    private static function sendZertifikatsMail($user, $seminar, $institute, $contact_mail){
        
        $filepath = self::pdf_action($user, $seminar, $institute);

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
            $filename = 'zertifikat_'. clear_string($user) . '.pdf';

            $mail = new StudipMail();
            return $mail->addRecipient($empfaenger)
                //->addRecipient('elmar.ludwig@uos.de', 'Elmar Ludwig', 'Cc')
                 ->setReplyToEmail('')
                 ->setSenderEmail('')
                 ->setSenderName('E-Learning - DSO - Datenschutz')
                 ->setSubject($betreff)
                 ->addFileAttachment($filepath, $name = $filename)
                 ->setBodyHtml($mailtext)
                 ->setBodyHtml(strip_tags($mailtext))  
                 ->send();

    }

    public function execute($last_result, $parameters = array())
    {
        $db = DBManager::get();
        PluginEngine::getPlugin('Courseware');
        
        // get all courses with configured Zertifikats-Plugin
        $res = $db->query("SELECT course_id, contact_mail FROM zertifikat_config "); //WHERE complete != 1");
        $entries = $res->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($entries as $entry) {
            $contact_mail = $entry['contact_mail'];
            $seminar_id = $entry['course_id'];
            $course = new Seminar($seminar_id);
            $institut = new Institute($course->getInstitutId());
             
            //get number of TestBlocks in Course
            //$blocks = \Mooc\DB\Block::findBySQL('seminar_id = ? ORDER BY position', array($seminar_id));
            if ($seminar_id == '240f7e7eaa7b167363090a9d36b19e6c'){
                echo 'das betroffene Seminar ';
            }
                    
            
             $stmt = $db->prepare("SELECT id as id FROM mooc_blocks mb
                        WHERE mb.seminar_id = :sem_id
                        AND mb.type = 'Chapter'");
                $stmt->execute(array('sem_id' => $seminar_id));
                $blocks_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //get TN
            $members = $course->getMembers('autor');
            
            //foreach TN()
            $course_completed_by_all_members = true;
            foreach ($members as $member){
  
                //if not already sent
                 $stmt = $db->prepare("SELECT * FROM zertifikat_sent
                    WHERE user_id = :user_id
                    AND course_id = :sem_id");
                 $stmt->execute(array('user_id' => $member['user_id'], 'sem_id' => $seminar_id));
                 $result = $stmt->fetch(PDO::FETCH_ASSOC);

                 if (!$result){
                
                    //prüfen ob alle Blöcke absolviert wurden
                    $complete = false;

                    foreach ($blocks_ids as $block_id){
                        $block = new \Mooc\DB\Block($block_id['id']);
                        if (!$block->hasUserCompleted($member['user_id'])){
                            $complete = false;
                            if ($seminar_id == '240f7e7eaa7b167363090a9d36b19e6c'){
                                echo $member['fullname'] .' block ' . $block_id['id'];
                            }
                            break;
                        } else {
                            $complete = true;
                        }
                    }
              
                    if ($complete){
                    
                        if(self::sendZertifikatsMail($member['fullname'], $course->name, $institut->name, $contact_mail)){
                         
                            $stmt = $db->prepare("INSERT IGNORE INTO zertifikat_sent
                                (user_id, course_id, mail_sent, mkdate)
                                VALUES (:user_id, :sem_id, '1', :mkdate)");
                            $stmt->execute(array('user_id' => $member['user_id'], 'sem_id' => $seminar_id, 'mkdate' => time()));
                            
                            echo 'Bescheinigung über Abschluss der Inhalte des Kurses '. $course->name . " durch User " . $member['fullname'] . " wurde versendet \n";

                         } else {
                             echo 'Mail konnte nicht versendet werden für: '. $member['fullname'] .' mail: '. $contact_mail . ' \n';
                         }           
                     } else {
                         //hier ist noch ein Nutzer der nicht fertig ist
                         //$course_completed_by_all_members = false;
                     }  
                } 
            }
            
//            if ($course_completed_by_all_members){
//                $stmt = $db->prepare("UPDATE zertifikat_config SET complete = 1 WHERE course_id like :sem_id");
//                $stmt->execute(array('sem_id' => $seminar_id));
//            }
        }

        return true;
    }
    
     function pdf_action($user, $seminar, $institute)
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
    
    
    
    private function getColor($colorname) 
    {
        $colors = array(
            "white"    => array(255,255,255),
            "yellow"   => array(254,250,188),
            "blue"     => array(188,254,250),
            "green"    => array(188,250,188),
            "red"      => array(254,188,188),
            "orange"   => array(254,188,108)
        );
        
        return $colors[$colorname];
    }
    
    /*
 *    Wandelt Sonderzeichen in HTML-Entities um, 
 *    lässt aber die HTML-Tags bestehen.
 *    @param string $htmlText Zeichenkette die HTML-Tags und Sonderzeichen enthält
 *    @param obj $ent flag für htmlentities
 * 
 *    @return string gibt Zeichenkette mit darstellbarem HTML wieder 
 */

    private function htmlentitiesOutsideHTMLTags($htmlText, $ent)
    {
        $matches = Array();
        $sep = '###HTMLTAG###';

        preg_match_all(":</{0,1}[a-z]+[^>]*>:i", $htmlText, $matches);

        $tmp = preg_replace(":</{0,1}[a-z]+[^>]*>:i", $sep, $htmlText);
        $tmp = preg_replace('/<!-- [^>]+\-->/i', "", $tmp); 
        
        $tmp = explode($sep, $tmp);

        for ($i=0; $i<count($tmp); $i++)
            $tmp[$i] = htmlentities($tmp[$i], $ent,  false);

        $tmp = join($sep, $tmp);

        for ($i=0; $i<count($matches[0]); $i++)
            $tmp = preg_replace(":$sep:", $matches[0][$i], $tmp, 1);

        return $tmp;
    }
     
    
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
