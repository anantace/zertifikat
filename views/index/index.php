<html>
<head>
<meta charset="utf-8">
</head> 
<body>
<h1>Mail für das Versenden von Zertifikaten</h1>
<form method="post" action="<?=$controller->url_for('/index/save')?>">
    <p><label>E-Mail:<br><input value="<?= $mail?>" type="text" name="Mail"></label></p>
    <p> Sobald ein/e Teilnehmer/in alle Lerninhalte abgeschlossen, hat wird ein Teilnehmezertifikat generiert und an die hier hinterlegte E-Mail-Adresse versendet.</p>
<!--<p><label>Name:<br><input type="text" name="Name"></label></p>
<p><label>Betreff:<br><input type="text" name="Betreff"></label></p>
<p><label>Nachricht:<br>
<textarea name="Nachricht" cols="50" rows="8"></textarea></label></p>
-->
<button type="submit" name="submit" > Speichern
</form>
</body>





