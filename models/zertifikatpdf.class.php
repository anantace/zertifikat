<?php
require_once $STUDIP_BASE_PATH.'/vendor/tcpdf/tcpdf.php';

class zertifikatpdf extends TCPDF {

    //Page header
    public function Header() {
            
            // Logo
            $this->Image($GLOBALS['ABSOLUTE_PATH_STUDIP'] . '/' . PluginEngine::getPlugin('Zertifikats_Plugin')->getPluginPath().'/assets/images/logo.png', 45, 15, 15, '', '', '', '', false, 300);
            //$img = file_get_contents(PluginEngine::getPlugin('Zertifikats_Plugin')->getPluginPath().'/assets/images/logo.png');
            $this->Image($GLOBALS['ABSOLUTE_PATH_STUDIP'] . '/' . PluginEngine::getPlugin('Zertifikats_Plugin')->getPluginPath().'/assets/images/logo.png', 31, 80, 150, '', '', '', '', false, 300);
            //$this->Image('@'.$img, 20, 12, 15, '', '', '', '', false, 300);
            // Set font
            $this->SetFont('helvetica', 'B', 18);
            //$this->SetTextColor(255,255,255);
            // Title
            $this->SetLeftMargin(65);
       
            $this->Ln(16);
            //$this->Cell(0, 0, 'DSO Datenschut z Osnabrück UG', 0, 1, 'L', 0, '', 0, true, 'T', 'B');
            $this->SetY(20);
            $this->Cell(0, 0, studip_utf8encode('DSO Datenschutz Osnabrück UG'), 0, 1, 'L', 0, '', 0);
            $this->SetLeftMargin(70);
            $this->Line(20, 40, 190, 40, array(0, 152, 101));
            
            $this->SetFont('helvetica', 'BU', 18);
            $this->SetY(60);
            $this->Cell(0, 0, 'Teilnahmebescheinigung', false, 1, 'L', 0, '', 0);
    }

    // Page footer
    public function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-32);
            $this->Line(20, 260, 190, 260);
            // Set font
            $this->SetFont('helvetica', '', 11);
            //$this->SetTextColor(0,127,75);
            // Page number
            $this->Cell(0, 0, studip_utf8encode('DSO Datenschutz Osnabrück UG (haftungsbeschränkt) '), 0, 1, 'L', 0, '', 0, false, 'C', 'C');
            $this->Ln(2);
            $this->SetFont('helvetica', '', 8);
            //$this->Cell(0, 0, 'DSO Datenschutz Osnabrueck UG (haftungsbeschraenkt) ', 0, 1, 'L', 0, '', 0, false, 'C', 'C');
            $this->MultiCell(35, 5, studip_utf8encode('Brückenstr. 3 <br>49090 Osnabrück <br>Geschaeftsführer <br>Amtsgericht Osnabrück'), 0, 'L', 0, 0, '', '', f, 0, true);
            $this->MultiCell(40, 5, 'www.DSO-Datenschutz.de <br>USt-ID-Nr. DE296231675 <br>RA Stephan Beume <br>HRB 208700', 0, 'L', 0, 0, '', '', f, 0, true);
            $this->MultiCell(45, 5, 'Bankhaus Hallbaum AG <br>IBAN DE89250601801000550051 <br>BIC HALLDE2HXXX <br>', 0, 'L', 0, 0, '', '', f, 0, true);
            $this->MultiCell(45, 5, 'Telefon 0541/60081631 <br>Telefax 0541/60081626 <br>info@DSO-Datenschutz.de <br>', 0, 'L', 0, 0, '', '', f, 0, true);

            $this->Image($GLOBALS['ABSOLUTE_PATH_STUDIP'] . '/' . PluginEngine::getPlugin('Zertifikats_Plugin')->getPluginPath().'/assets/images/logo.png', 177, 265, 12, '', '', '', '', false, 300);
            
    }
    
    public function addShadow($x,$y,$h,$w){

        for($i=2;$i>=1;$i-=0.1){
            $this->SetAlpha(0.1-($i*0.04));
            $this->SetFillColor(0, 0, 0);
            $this->SetDrawColor(0, 0, 0);
            $this->Rect($x+$i, $y+$i, $h, $w, 'DF');
        }

        $this->SetAlpha(1);
    }
}

