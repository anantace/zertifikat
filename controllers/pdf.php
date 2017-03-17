<?php

class PrintController extends CoursewareStudipController {

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        //PageLayout::addStylesheet($GLOBALS['ABSOLUTE_URI_STUDIP'] . $this->plugin->getPluginPath() . '/assets/courseware.min.css');

    }

    public function index_action()
    {
    }
    
    function pdf_action()
    {
        global $STUDIP_BASE_PATH;
        require_once $STUDIP_BASE_PATH.'/vendor/tcpdf/tcpdf.php';
        require_once $this->plugin->getPluginPath().'/models/zertifikatpdf.php';
        /**
        $note_content = Request::get("note-data");
        $note_type = Request::get("note-type");
        $note_color = Request::get("note-color");
        $note_content = json_decode($note_content);
         * 
         */
        
        // create new PDF document
        $pdf = new zertifikatpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'ISO-8859', false);
        $pdf->SetTopMargin(40);
        $pdf->SetLeftMargin(20);
        $pdf->SetRightMargin(20);
        $pdf->AddPage();
        $x = 35;
        $y = 50;
        $w = 60;
        $h = 60;
        
        
            //$html = $this->htmlentitiesOutsideHTMLTags($note_content[0], ENT_HTML401);
            $html = 'Teila?ahmebescheinigung';
            $pdf->writeHTML($html, true, 0, true, 0);
        
        $pdf->Output('zertifikat.pdf');
        exit("delivering pdf file");
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
 *    l�sst aber die HTML-Tags bestehen.
 *    @param string $htmlText Zeichenkette die HTML-Tags und Sonderzeichen enth�lt
 *    @param obj $ent flag f�r htmlentities
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
