<?php
include 'attestation_pdf.php';


$auth= array("admin","user","ent");

if ( !isset($profile) && in_array( $profile , $auth ) ) {
	setError("Not Authorized" , 401 ) ;
	return;
}

switch ($method) {
	case 'GET':
		if ( !isset($id) ) {
			$err="invalid request post id/data ";
			setError($err );
			break;
		}
		sendAttestation( $id );
        break;
        
    default:
		$err="invalid request global";
		setError($err );
        break;
    }

///////////////////////////////////////////////////////////////
function sendAttestation($id) {
	global $mysqli;
	global $tlicencies_encours,$saison_enc,$president;
	
	$query = "SELECT *  FROM $tlicencies_encours WHERE id = '$id' ";
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		http_response_code(404);
		$err=$mysqli->connect_error ;
		setError( $err );
		return; 	
	}

    $data = array();
	while($r = $result->fetch_assoc() ) {
		$data['nom']=( ucfirst( strtolower($r['nom']) ) );
		$data['prenom']=(ucfirst( strtolower($r['prenom'] ) ) );
		$data['date']=($r['date'] );
		$data['cotisation']=($r['cotisation'] );
        $data['email']=$r['email'] ;
   
    }

$res = send_attestation( $data ) ;
if ( $res === false ) {
    setError("envoi attestation erreur");
    return;
}

$message=array();
$message['success']=true;
$message['message']= "email envoyé"  ;
echo json_encode( $message );


}


function send_attestation ( $data ) {
	global $dev,$dev_email,$saison_enc,$president;
	

	$nom = $data['nom'];
	$prenom = $data['prenom'];
	$cotisation = $data['cotisation'];
	$date = $data['date'];
    $email = $data['email'];
    

		$temails = explode(",", $email);
		$v = "" ;
		foreach($temails as $t  ) {
			 if  ( empty($t) ) continue ;	
			 if  ( empty($v) ) { $v=$t; 
			 } else { $v.=",".$t; }
        }    
    
        $to=$v;
	
	
  	$pdf=doPdf($nom,$prenom,$date,$cotisation,$saison_enc,$president);
	$pdf->setCompression(true);
	// clé aléatoire de limite
	$boundary = md5(uniqid(microtime(), TRUE));
	
	$subject ="[Club de Natation] Confirmation Inscription $saison_enc : ".$prenom." ".$nom;
	
	
	
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
	$msg = 'Texte affiché par des clients mail ne supportant pas le type MIME.'."\r\n\r\n";
	
	$msg .= '--'.$boundary."\r\n";
	$msg .= "Content-type: text/html; charset=UTF-8\r\n\r\n";
	$msg .= "<div>Bonjour ,<br><br>";
	$msg.="Pour information, le dossier d'inscription de <strong>$prenom $nom</strong> au club Espérance Chartres de Bretagne Natation pour la $saison_enc a été traité.";
    $msg.="<p>L'attestation d'inscription est en pièce jointe.</p>";
    if( $dev ) {
        $msg.="<p>mode dev => $to</p>";
    }
   
	$msg.="Sportivement<br>--<br>Le bureau de l'association<br>Web : http://ecnatation.fr </div>\r\n";
	
	
	$content = chunk_split(base64_encode($pdf->Output("attestation.pdf","S") ));
	$msg .= '--'.$boundary."\r\n";
	
	$msg .= 'Content-type:application/octet-stream;name=attestation.pdf'."\r\n";
	$msg .= 'Content-transfer-encoding:base64'."\r\n\r\n";
	$msg .= $content."\r\n";
	$msg .= '--'.$boundary."\r\n";
	
   if( $dev ) {
       $to=$dev_email ;
   }
    
	$success = mail($to,$subject,$msg,$headers);
	
	

	return $success ;
	
}







    ?>