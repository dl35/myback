<?php
include 'saison.php';
require_once('../common/fpdf.php'); 
require_once('../common/fpdi.php'); 
	 
	// initiate FPDI 
	$pdf = new FPDI(); 
// add a page 
	$pdf->AddPage(); 
	// set the sourcefile 
	$pdf->setSourceFile('modele.pdf'); 
	// import page 1 
	$tplIdx = $pdf->importPage(1); 
	// use the imported page and place it at point 10,10 with a width of 100 mm 
	$pdf->useTemplate($tplIdx,0,0,210,297); 
	$pdf->SetFont('Arial'); 
	$pdf->SetTextColor(0,0,0); 


	
$nom=strtoupper($nom);


list($y,$m,$d)=explode("-",$date);
$date=$d."/".$m."/".$y;
/////////////////////////

	$pdf->SetXY(25, 26.5);$pdf->Write(0,  $nom );
	
	$pdf->SetXY(31,35.5+5);$pdf->Write(0, $prenom );
	$pdf->SetXY(118, 35.5+5);$pdf->Write(0, $sexe);

	
    $pdf->SetXY(51, 43.5+7);$pdf->Write(0, $date);  
    $pdf->SetXY(128, 43.5+7);$pdf->Write(0, ucwords($categorie)." - ".$rang);  
	
	$pdf->SetXY(30, 50.5+7 );$pdf->Write(0, $licence); 
	
	$pdf->SetXY(25, 64.5+7);$pdf->Write(0,  $adresse );
	$pdf->SetXY(38, 71.5+7);$pdf->Write(0, $cp);
	$pdf->SetXY(71, 71.5+7);$pdf->Write(0, $ville );   

	
$ttels = preg_split("/,/", $tel);
if( isset($ttels[0]) ){ $pdf->SetXY(61, 92.5+7);$pdf->Write(0, $ttels[0]); }   
if( isset($ttels[1]) ){	$pdf->SetXY(152, 92.5+7);$pdf->Write(0, $ttels[1]); }
if( isset($ttels[2]) ){	$pdf->SetXY(61, 99.5+7);$pdf->Write(0, $ttels[2]); }
if( isset($ttels[3]) ){	$pdf->SetXY(152, 99.5+7);$pdf->Write(0, $ttels[3]);}
if( isset($ttels[4]) ){	$pdf->SetXY(61, 106.5+7);$pdf->Write(0, $ttels[4]);}


$temails = preg_split("/,/", $email);
if( isset($temails[0]) ) {$pdf->SetXY(61, 120.5+7);$pdf->Write(0, $temails[0]);}  
if( isset($temails[1]) ) {$pdf->SetXY(61, 127.5+7);$pdf->Write(0, $temails[1]);}
if( isset($temails[2]) ) {$pdf->SetXY(61, 134.5+7);$pdf->Write(0, $temails[2]);}

	$pdf->SetXY(110, 148.5+7);$pdf->Write(0, $type);
	
	
       $pdf->addPage(); 
	 	$tplIdx = $pdf->importPage(2); 
	    $pdf->useTemplate($tplIdx,0,0,210,297);

        $pdf->addPage(); 
	 	$tplIdx = $pdf->importPage(3); 
	    $pdf->useTemplate($tplIdx,0,0,210,297);
	    $pdf->SetXY(25, 73);
	    $pdf->Write(0,  $nom );
		$pdf->SetXY(125, 73);
	    $pdf->Write(0,  $prenom );
	    
	 	$pdf->SetXY(32, 87 );$pdf->Write(0,  $adresse );
	    $pdf->SetXY(32, 92 );$pdf->Write(0, $cp ." ".$ville);
	     $pdf->SetXY(142, 101);$pdf->Write(0, $date);   
	    
	    $pdf->addPage(); 
	 	$tplIdx = $pdf->importPage(4); 
	    $pdf->useTemplate($tplIdx,0,0,210,297);
	   
	    
	     $pdf->addPage(); 
	 	$tplIdx = $pdf->importPage(5); 
	    $pdf->useTemplate($tplIdx,0,0,210,297);
	   
	    
	    
	$pdf->setCompression(true); 
//$pdf->Output("inscription.pdf",'F');
//return;
	
/////////////////////

	
	

$from = "ECN natation <inscription@ecnatation.org>";
$subject = "[Club de Natation] Confirmation de pre-inscription : ". strtoupper($nom) ." ".  ucfirst(strtolower($prenom)  );





$attachment = chunk_split(base64_encode($pdf->Output("inscription.pdf",'S')));
// cl� al�atoire de limite
$boundary = md5(uniqid(microtime(), TRUE));


// Headers
/*
$headers = 'From: ECN natation <inscription@ecnatation.org>'."\r\n";
$headers .= 'Mime-Version: 1.0'."\r\n";
$headers .= 'Content-Type: multipart/mixed;boundary='.$boundary."\r\n";
$headers .= "\r\n";
*/

$headers = "MIME-Version: 1.0\r\n";
$headers .= "X-Sender: <www.ecnatation.org>\r\n";
$headers .= "X-Mailer: PHP\r\n";
$headers .= "X-auth-smtp-user: webmaster@ecnatation.org\r\n";
$headers .= "X-abuse-contact: webmaster@ecnatation.org\r\n";
$headers .= "Reply-to: ECN natation  <inscription@ecnatation.org>\r\n"; 
$headers .= "From: ECN natation <inscription@ecnatation.org>\r\n";
$headers .= "Bcc:ecninscription@gmail.com\r\n";
$headers .= 'Content-Type: multipart/mixed;boundary='.$boundary."\r\n";
$headers .= "\r\n";





// Message
$msg = 'Texte affich� par des clients mail ne supportant pas le type MIME.'."\r\n\r\n";

// Message HTML
$msg .= '--'.$boundary."\r\n";
$msg .= 'Content-type: text/html; charset=iso-8859-1'."\r\n\r\n";
$msg .= "<div>Bonjour ,<br><br>";
$msg.="Merci d'avoir valid� votre pr�-inscription au club Esp�rance Chartres de Bretagne Natation pour la $saison. </br>"; 
$msg.="<p>Vous devez maintenant <strong>imprimer le fichier inscription.pdf</strong> en pi�ce jointe,<strong>le signer et le d�poser accompagn� de votre r�glement</strong>, dans la boite aux lettres du club, dans le hall de la piscine de la Conterie.</p>";
$msg.="<p>Pour m�moire, le fichier <strong> inscription.pdf </strong> contient les documents suivants :</p>";

$msg.="<ul>";
$msg.="<li>le dossier d'inscription : � signer</li>";
$msg.="<li>l'autorisation parentale : � compl�ter  pour les adh�rents mineurs</li>";
$msg.="<li>la fiche de liaison m�dicale : � compl�ter</li>";
$msg.="<li>le r�glement int�rieur : � lire et � signer</li>";
$msg.="</ul>";

$msg.="<p><strong>Rappel des tarifs</strong></p>";
$msg.="<ul>";
$msg.="<li>1er enfant : 245 Euros(int�grant la carte Esp�rance)</li>";
$msg.="<li>2�me enfant : 213 Euros</li>";
$msg.="<li>3�me enfant : 193 Euros</li>";
$msg.="<li>Master Elite (3 s�ances) : 296 Euros (int�grant la carte Esp�rance)</li>";
$msg.="<li>Master Perf (2 s�ances) : 272 Euros (int�grant la carte Esp�rance)</li>";
$msg.="<li>Master Pr�-comp�tition (1 s�ance) : 250 Euros (int�grant la carte Esp�rance)</li>";
$msg.="</ul>";


//$msg.="<p>Merci de compl�ter et signer ces documents et les d�poser avec le r�glement et le certificat m�dical, dans une enveloppe � votre ";
//$msg.="nom, dans la boite aux lettres du club dans le hall de la piscine de la Conterie.</p>";
$msg.="<p>Le certificat m�dical doit pr�ciser qu'il n'y a pas de ";
$msg.="<strong><u>contre-indications � la pratique de la natation sportive en comp�tition.</u></strong></p></br>";

$msg.="<p><strong>Attention : � partir du lundi 17 septembre, l'acc�s aux entrainements ne sera possible ";
$msg.="qu'avec la carte d'acc�s au bassin d�livr�e suite � la r�ception du dossier complet.</strong></p>";

$msg.="<p>Merci de d�poser votre dossier complet dans une <strong> enveloppe en indiquant le nom,le pr�nom et l'ann�e de naissance du licenci� </strong> dans la boite aux lettres du club ";
$msg.="situ�e � l'int�rieur du Hall de la piscine de la Conterie au plus t�t.<strong>Ne pas oublier d'indiquer le nom et le pr�nom du licenci� au dos du ch�que.</strong></p>"; 

$msg.="<p>Suite au traitement de votre dossier complet, vous recevrez une notification par email. </p>";



$msg.="Pour toute question sur l'inscription au club, veuillez envoyer un email � l'adresse inscription@ecnatation.org </br>";
$msg.="Sportivement<br>--<br>Le bureau de l'association<br>Web : http://ecnatation.fr </div>\r\n";




$content = chunk_split(base64_encode($pdf->Output("inscription.pdf","S") ));
$msg .= '--'.$boundary."\r\n";

$msg .= 'Content-type:application/octet-stream;name=inscription.pdf'."\r\n";
$msg .= 'Content-transfer-encoding:base64'."\r\n\r\n";
$msg .= $content."\r\n";
$msg .= '--'.$boundary."\r\n";


$response=utf8_encode ("$prenom $nom est pr�-inscrit .<br>Veuillez ".
"consulter votre boite e-mail.<br> Merci." );







?>
