<html>
<head>
<meta charset="utf-8">
</head> 
<body>
<h1>Mail für das Versenden von Zertifikaten</h1>
<form method="post" action="<?=$controller->url_for('/index/save')?>">
    <p><label>E-Mail:<br><input style="width:200px" value="<?= $mail?>" type="text" name="Mail"></label><button type="submit" name="submit" > Speichern </button></p>
    <p> Sobald ein/e Teilnehmer/in alle Lerninhalte abgeschlossen, hat wird ein Teilnahmezertifikat generiert und an die hier hinterlegte E-Mail-Adresse versendet.</p>
<!--<p><label>Name:<br><input type="text" name="Name"></label></p>
<p><label>Betreff:<br><input type="text" name="Betreff"></label></p>
<p><label>Nachricht:<br>
<textarea name="Nachricht" cols="50" rows="8"></textarea></label></p>
-->
</form>

<table class='default'>
    <thead>
		<tr>
        <th style='width:10%'><span>Name</span></th>
        <th style='width:10%'><span>Zertifikat generiert und versendet</span></th>
        <th style='width:10%'>Aktionen</th>
        <!--<th>Courseware besucht?</th>-->
    </tr>
    </thead>
<?php foreach ($members as $member){ ?>
    <tr>
            <?  $mkdate = 'Ja';
            
                if ($member['mail_sent']){
                    if ($member['mail_sent']->mkdate > 0){
                        $mkdate = date('d.m.Y - G:i:s', $member['mail_sent']->mkdate);
                    } 
                }
            ?>
            
            <td><?= $member['Vorname'] . ' ' . $member['Nachname']?></td>
            <td><?= $member['mail_sent'] ? $mkdate : 'Nein' ?></td>
            <td><?= $member['mail_sent'] ? "<a href='". $this->controller->url_for('index/sendMail/' . $member['username']) ."'>Zertifikat erneut versenden</a><br/>" :"" ?>
                    <a href='<?= URLHelper::getLink("plugins.php/courseware/progress", array('uid' => $member['user_id'])) ?>' >Fortschritt ansehen</a></td>
        </tr>
<?php } ?>
</table>

</body>





