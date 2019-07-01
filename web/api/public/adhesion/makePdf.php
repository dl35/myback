<?php
require_once('../fpdf/fpdf.php'); 
require_once('../fpdf/fpdi.php'); 
     
include '../common/rappel_texte.php' ;


function makepdf( $data  )
{
	// initiate FPDI 
	$pdf = new FPDI(); 
    // add a page 
	$pdf->AddPage(); 
	// set the sourcefile 
	$pdf->setSourceFile('../common/modele.pdf'); 
	// import page 1 
	$tplIdx = $pdf->importPage(1); 
	// use the imported page and place it at point 10,10 with a width of 100 mm 
	$pdf->useTemplate($tplIdx,0,0,210,297); 
	$pdf->SetFont('Arial'); 
	$pdf->SetTextColor(0,0,0); 


	
    $nom=strtoupper($data->nom);


    list($y,$m,$d)=explode("-",$data->date);
    $date=$d."/".$m."/".$y;
    /////////////////////////

	$pdf->SetXY(25, 26.5);$pdf->Write(0,  $nom );
	
	$pdf->SetXY(31,35.5+5);$pdf->Write(0, $data->prenom );
	$pdf->SetXY(118, 35.5+5);$pdf->Write(0, $data->sexe);

	
    $pdf->SetXY(51, 43.5+7);$pdf->Write(0, $date);  
    $pdf->SetXY(128, 43.5+7);$pdf->Write(0, ucwords($data->categorie)." - ".$data->rang);  
	
	//$pdf->SetXY(30, 50.5+7 );$pdf->Write(0, $licence); 
	
    $pdf->SetXY(25, 64.5+7);$pdf->Write(0,  $data->adresse );
    $pdf->SetXY(38, 71.5+7);$pdf->Write(0, $data->cp);
    $pdf->SetXY(71, 71.5+7);$pdf->Write(0, $data->ville );   

    $pdf->SetXY(61, 85.5+7);$pdf->Write(0, $data->tel1);
    $pdf->SetXY(61, 92.5+7);$pdf->Write(0, $data->tel2);
	$pdf->SetXY(61, 99.5+7);$pdf->Write(0, $data->tel3); 

    $pdf->SetXY(61, 113.5+7);$pdf->Write(0, $data->email1 );  
    $pdf->SetXY(61, 120.5+7);$pdf->Write(0, $data->email2 );  
    $pdf->SetXY(61, 127.5+7);$pdf->Write(0, $data->email3 );

    $pdf->SetXY(110, 148.5);$pdf->Write(0, $data->type);
	
	
       $pdf->addPage(); 
	 	$tplIdx = $pdf->importPage(2); 
	    $pdf->useTemplate($tplIdx,0,0,210,297);

        $pdf->addPage(); 
	 	$tplIdx = $pdf->importPage(3); 
	    $pdf->useTemplate($tplIdx,0,0,210,297);
	    $pdf->SetXY(25, 73);
	    $pdf->Write(0,  $data->nom );
		$pdf->SetXY(125, 73);
	    $pdf->Write(0,  $data->prenom );
	    
	 	$pdf->SetXY(32, 87 );$pdf->Write(0,  $data->adresse );
	    $pdf->SetXY(32, 92 );$pdf->Write(0, $data->cp ." ".$data->ville);
	    $pdf->SetXY(142, 101);$pdf->Write(0, $date);   
	    
	    $pdf->addPage(); 
	 	$tplIdx = $pdf->importPage(4); 
	    $pdf->useTemplate($tplIdx,0,0,210,297);
	   	    
	    $pdf->addPage(); 
	 	$tplIdx = $pdf->importPage(5); 
	    $pdf->useTemplate($tplIdx,0,0,210,297);
       
        $pdf->addPage(); 
	 	$tplIdx = $pdf->importPage(6); 
        $pdf->useTemplate($tplIdx,0,0,210,297);
        
        $pdf->addPage(); 
	 	$tplIdx = $pdf->importPage(7); 
	    $pdf->useTemplate($tplIdx,0,0,210,297);
	    
	    
        $pdf->setCompression(true); 
 
        return $pdf ;        

}


	
////////////////////////////////////////////////////////////////////////////////////////

function sendmailpdf( $data  )	{
 global $dev , $dev_email ;  
 global $saison_enc; 

$pdf = makepdf($data);    
$rappel = getRappel();

//$from = "ECN natation <inscription@ecnatation.org>";
$subject = "[Club de Natation] Confirmation de pre-inscription : ". strtoupper($data->nom) ." ".  ucfirst(strtolower($data->prenom)  );
$attachment = chunk_split(base64_encode($pdf->Output("inscription.pdf",'S')));
// clé aléatoire de limite
$boundary = md5(uniqid(microtime(), TRUE));

$headers  = "MIME-Version: 1.0\r\n";
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
$msg = 'Texte affiché par des clients mail ne supportant pas le type MIME.'."\r\n\r\n";

// Message HTML
$msg .= '--'.$boundary."\r\n";

$msg .= "Content-Type: text/html; charset=\"utf-8\"\r\n\r\n";

/*
$msg .= "<div>Bonjour ,<br><br>";
$msg .="Merci d'avoir validé votre pré-inscription au club Espérance Chartres de Bretagne Natation pour la $saison_enc. </br>"; 
// $msg .="<p>Vous devez maintenant <strong>imprimer le fichier inscription.pdf</strong> en pièce jointe,<strong>le signer et le déposer accompagné de votre règlement</strong>, dans la boite aux lettres du club, dans le hall de la piscine de la Conterie.</p>";
$msg .="<p>Pour mémoire, le fichier <strong> inscription.pdf </strong> contient les documents suivants :</p>";
$msg .="<ul>";
$msg .="<li>le dossier d'inscription : à signer</li>";
$msg .="<li>l'autorisation parentale : à compléter  pour les adhérents mineurs</li>";
$msg .="<li>la fiche de liaison médicale : à compléter</li>";
$msg .="<li>le réglement intèrieur : à lire et à signer</li>";
$msg .="</ul>";
*/

$msg .= $rappel ;

/*
$msg .="Pour toute question sur l'inscription au club, veuillez envoyer un email à l'adresse inscription@ecnatation.org </br>";
$msg .="Sportivement<br>--<br>Le bureau de l'association<br>Web : http://ecnatation.fr </div>\r\n";
*/

$content = chunk_split(base64_encode($pdf->Output("inscription.pdf","S") ));
$msg .= '--'.$boundary."\r\n";

$msg .= 'Content-type:application/octet-stream;name=inscription.pdf'."\r\n";
$msg .= 'Content-transfer-encoding:base64'."\r\n\r\n";
$msg .= $content."\r\n";
$msg .= '--'.$boundary."\r\n";



   $email = "" ; 
   if( !empty($data->email1) ) $email.=$data->email1 ;
   if( !empty($data->email2) ) {
      if ( !empty($email) )  $email.=",";
      $email.=$data->email2 ;
     }
   if( !empty($data->email3) ) {
     if ( !empty($email) )  $email.=",";
      $email.=$data->email3 ;
    }
 
    if( $dev ) {
        $to = $dev_email;
    } else {

        $to = $email ;
    }
 


return  mail($to, $subject, $msg, $headers); 




}





?>
