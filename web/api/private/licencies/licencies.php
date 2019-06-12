<?php

$auth= array("admin","ecn");

if ( !isset($profile) || !in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}

switch ($method) {
	case 'PUT':
		$data = json_decode(file_get_contents('php://input'));
		$v= validateObject( $data ) ;
		if( !$v ) { 
			setError("invalid parameters");
			break;
		}
		update( $data) ;
		break;
		
	case 'POST':
		$data = json_decode(file_get_contents('php://input'));
		$v= validateObject( $data ) ;
		if( !$v ) {
			setError("invalid parameters");
			break;
		}
		
		add( $data) ;
		break;
		
	case 'GET':
		get() ;
		break;
		
		
	case 'DELETE':
		delete($_GET['id']);
		break;
		
		
	default:
		echo "error" ;
		break;
		
}



/////////////////////////////////////////////////////////////////////////////////////
function validationParams( $data, $get  ) {
	
	if ( !$data )
	{
		$error ="invalid data parameter1" ;
		setError($error);
		return false ;
	}
	
	
	if ( $get !== false   && !isset($get['id'] ) )
	{
		$error ="invalid parameter2" ;
		setError($error);
		return false ;
	}
	
	
	if ( !validateObject( $data)  )
	{
		$error ="invalid object parameter" ;
		setError($error);
		return false ;
		
		
	}
	
	
	
	
	return true ;
}


/////////////////////////////////////////////////////////////////////////////////////
function validateObject($json) {
	
	
	if ( ! array_key_exists('nom', $json) ) return false ;
	if ( ! array_key_exists('prenom', $json) ) return false ;
	if ( ! array_key_exists('date', $json) ) return false ;
	if ( ! array_key_exists('sexe', $json) ) return false ;
	
	
	if ( ! array_key_exists('adresse', $json) ) return false ;
	if ( ! array_key_exists('code_postal', $json) ) return false ;
	if ( ! array_key_exists('ville', $json) ) return false ;
	
	
	if ( ! array_key_exists('email1', $json) ) return false ;
	if ( ! array_key_exists('email2', $json) ) return false ;
	if ( ! array_key_exists('email3', $json) ) return false ;
	
	
	if ( ! array_key_exists('telephone1', $json) ) return false ;
	if ( ! array_key_exists('telephone2', $json) ) return false ;
	if ( ! array_key_exists('telephone3', $json) ) return false ;
	
	
	$d = new DateTime( $json->date );
	$df =$d->format('Y-m-d');
	$json->date =$df;
	
	return true;
}




/////////////////////////////////////////////////////////////////////////////////////
function get($id=false) {

	global $dev,$mysqli;
	global $tlicencies;
	
	
	
	( $id === false )  ?  $where=""  :  $where=" WHERE id = '$id' " ;
	
	$query = "SELECT *  FROM $tlicencies  $where  ORDER BY nom, prenom ";
	
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		http_response_code(404);
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
		setError( $err );
		return; 	
	}
	
	
	
	$rows = array();
	while($r = $result->fetch_assoc() ) {
		
		
		$r['nom']=utf8_encode( ucfirst( strtolower($r['nom']) ) );
		$r['prenom']=utf8_encode(ucfirst( strtolower($r['prenom'] ) ) );
		$r['adresse']=utf8_encode($r['adresse'] );
		$r['ville']=utf8_encode($r['ville'] );
		$r['commentaires']=utf8_encode($r['commentaires'] );
		
		$ttel=explode(",",$r['telephone'] );
		$temail=explode(",",$r['email'] );
		
		
		$r['email1']=$temail[0];
		( isset( $temail[1] ) ) ?  $r['email2']=$temail[1] : $r['email2']="";
		( isset( $temail[2] ) ) ?  $r['email3']=$temail[2] : $r['email3']="";
		
		$r['telephone1']=$ttel[0];
		( isset( $ttel[1] ) ) ?  $r['telephone2']=$ttel[1] : $r['telephone2']="";
		( isset( $ttel[2] ) ) ?  $r['telephone3']=$ttel[2] : $r['telephone3']="";
		
		
		
		($r['entr'] == '1' ) ? $r['entr'] =true  : $r['entr'] =false ;
		
		($r['auto_parentale'] == '1' ) ? $r['auto_parentale'] =true  : $r['auto_parentale'] =false ;
		($r['cert_medical'] == '1' ) ? $r['cert_medical'] =true  : $r['cert_medical'] =false ;
		($r['fiche_medicale'] == '1' ) ? $r['fiche_medicale'] =true  : $r['fiche_medicale'] =false ;
		($r['photo'] == '1' ) ? $r['photo'] =true  : $r['photo'] =false ;
		($r['reglement'] == '1' ) ? $r['reglement'] =true  : $r['reglement'] =false ;
		($r['valide'] == '1' ) ? $r['valide'] =true  : $r['valide'] =false ;
		
		($r['paye'] == '1' ) ? $r['paye'] =true  : $r['paye'] =false ;
		
		
		unset ( $r['telephone'] ) ;
		unset ( $r['email'] ) ;
		unset ( $r['inscription'] );
		unset ( $r['niveau'] );
		unset ( $r['date_inscription'] );
		unset ( $r['confirmation_email'] );
		unset ( $r['date_valide'] );
		
		
		
		
		$e = json_encode( $r ) ;
		if ( $e != false  )
		{
			$rows[] = $r  ;
		}
		else {
			
			echo "\n\n**= ".$r['id']." == ".$r['nom']. " = ***";
		}
		//unset( $r['commentaires'] ) ;
		
		
	}
	
	if( $id === false  ) {
		echo   json_encode($rows);
	} else {
		echo   json_encode($rows[0]);
	}
	
}



////////////////////////////////////////////////////////////////////////////////////////////////
function add($data) {
	
	global $dev,$mysqli;
	global $tlicencies;
	

	$idlic = createKeyCode( $data->nom , $data->prenom) ;
	
	$nom = utf8_decode($data->nom);
	$prenom = utf8_decode($data->prenom);
	$adresse = utf8_decode($data->adresse);
	$ville = utf8_decode($data->ville);
	
	$set="(id,nom,prenom,date,sexe,adresse,code_postal,ville" ;
	$values="('$idlic','$nom','$prenom','$data->date','$data->sexe','$adresse','$data->code_postal','$ville'";
	
	$set.=",email";
	$v="";
	if ( strlen($data->email1) > 0 )
	{
		$v.="$data->email1";
	}
	
	$v.=",";
	
	if ( strlen($data->email2) > 0 )
	{
		$v.="$data->email2";
	}
	$v.=",";
	
	if ( strlen($data->email3) > 0 )
	{
		$v.="$data->email3";
	}
	
	$values.=",'$v'";
	
	
	$set.=",telephone";
	$v="";
	if ( strlen($data->telephone1) > 0 )
	{
		if( strlen($v) > 0 ) $v.=",";
		$v.="$data->telephone1";
	}
	
	if ( strlen($data->telephone2) > 0 )
	{
		if( strlen($v) > 0 ) $v.=",";
		$v.="$data->telephone2";
	}
	
	if ( strlen($data->telephone3) > 0 )
	{
		if( strlen($v) > 0 ) $v.=",";
		$v.="$data->telephone3";
	}
	
	$values.=",'$v'";
	
	
	
	if (isset($data->type) )
	{
		$set.=",type";
		$values.=",'$data->type'";
	}
	
	if (isset($data->categorie) )
	{
		$set.=",categorie";
		$values.=",'$data->categorie'";
	}
	if (isset($data->rang) )
	{
		$set.=",rang";
		$values.=",'$data->rang'";
	}
	
	if (isset($data->officiel) )
	{
		$set.=",officiel";
		$values.=",'$data->officiel'";
	}
	
	if (isset($data->licence) )
	{
		$set.=",licence";
		$values.=",'$data->licence'";
	}
	if (isset($data->cotisation) )
	{
		$set.=",cotisation";
		$values.=",'$data->cotisation'";
	}
	if (isset($data->entr) )
	{
		$set.=",entr";
		( $data->entr ) ?   $v="1"  :  $v="0" ;
		$values.=",'$v'";
	}
	
	if (isset($data->banque) )
	{
		$set.=",banque";
		$values.=",'$data->banque'";
	}
	
	if (isset($data->cheque1) )
	{
		$set.=",cheque1";
		$values.=",'$data->cheque1'";
	}
	if (isset($data->cheque2) )
	{
		$set.=",cheque2";
		$values.=",'$data->cheque2'";
	}
	if (isset($data->cheque3) )
	{
		$set.=",cheque3";
		$values.=",'$data->cheque3'";
	}
	
	
	if (isset($data->num_cheque1) )
	{
		$set.=",num_cheque1";
		$values.=",'$data->num_cheque1'";
	}
	if (isset($data->num_cheque2) )
	{
		$set.=",num_cheque2";
		$values.=",'$data->num_cheque2'";
	}
	if (isset($data->num_cheque3) )
	{
		$set.=",num_cheque3";
		$values.=",'$data->num_cheque3'";
	}
	
	
	if (isset($data->ch_sport) )
	{
		$set.=",ch_sport";
		$values.=",'$data->ch_sport'";
	}
	if (isset($data->num_sport) )
	{
		$set.=",num_sport";
		$values.=",'$data->num_sport'";
	}
	
	
	if (isset($data->coup_sport) )
	{
		$set.=",coup_sport";
		$values.=",'$data->coup_sport'";
	}
	if (isset($data->num_coupsport) )
	{
		$set.=",num_coupsport";
		$values.=",'$data->num_coupsport'";
	}
	
	
	
	if (isset($data->nbre_chvac10) )
	{
		$set.=",nbre_chvac10";
		$values.=",'$data->nbre_chvac10'";
	}
	
	
	if (isset($data->nbre_chvac20) )
	{
		$set.=",nbre_chvac20";
		$values.=",'$data->nbre_chvac20'";
	}
	
	if (isset($data->especes) )
	{
		$set.=",especes";
		$values.=",'$data->especes'";
	}
	
	
	
	if (isset($data->cert_medical) )
	{
		$set.=",cert_medical";
		( $data->cert_medical ) ?   $v="1"  :  $v="0" ;
		$values.=",'$v'";
	}
	
	if (isset($data->auto_parentale) )
	{
		$set.=",auto_parentale";
		( $data->auto_parentale ) ?   $v="1"  :  $v="0" ;
		$values.=",'$v'";
	}
	
	
	if (isset($data->fiche_medicale) )
	{
		$set.=",fiche_medicale";
		( $data->fiche_medicale ) ?   $v="1"  :  $v="0" ;
		$values.=",'$v'";
	}
	
	
	if (isset($data->photo) )
	{
		$set.=",photo";
		( $data->photo ) ?   $v="1"  :  $v="0" ;
		$values.=",'$v'";
	}
	
	
	if (isset($data->reglement) )
	{
		$set.=",reglement";
		( $data->reglement ) ?   $v="1"  :  $v="0" ;
		$values.=",'$v'";
	}
	
	if (isset($data->paye) )
	{
		$set.=",paye";
		( $data->paye ) ?   $v="1"  :  $v="0" ;
		$values.=",'$v'";
	}
	
	if (isset($data->commentaires) )
	{
		$set.=",commentaires";
		$values.=",'".utf8_decode($data->commentaires)."'";
	}
	
	
	
	$set.=") ";
	$values.=") ";
	$query =" INSERT INTO  $tlicencies  $set  VALUES  $values ";
	
echo $query ;

	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
		setError( $err );
		return;
	}
	
	
	
	return get( $idlic ) ;
	
	
}
////////////////////////////////////////////////////////////////////////////////////////////////


function update($data) {
	
	global $dev,$mysqli;
	global $tlicencies;
	
	$nom = utf8_decode($data->nom);
	$prenom = utf8_decode($data->prenom);
	$adresse = utf8_decode($data->adresse);
	$ville = utf8_decode($data->ville);
	
	
	$set=" SET ";
	
	
	$set.="nom= '$nom' , ";
	$set.="prenom= '$prenom' , ";
	
	$set.="date= '$data->date' , ";
	$set.="sexe= '$data->sexe' , ";
	$set.="adresse= '$adresse' , ";
	$set.="code_postal= '$data->code_postal' , ";
	$set.="ville= '$ville' , ";
	
	
	$email = "";
	if ( strlen($data->email1) > 0  )  $email .= $data->email1 ;
	$email.="," ;
	if ( strlen($data->email2) > 0  )  $email .= $data->email2 ;
	$email.="," ;
	if ( strlen($data->email3) > 0  )  $email .= $data->email3 ;
	
	$tel = "" ;
	if ( strlen($data->telephone1) > 0  )  $tel .= $data->telephone1 ;
	$tel.="," ;
	if ( strlen($data->telephone2) > 0  )  $tel .= $data->telephone2 ;
	$tel.="," ;
	if ( strlen($data->telephone3) > 0  )  $tel .= $data->telephone3 ;
	
	$set.=" email= '$email' , telephone= '$tel' " ;
	
	
	(isset($data->type) ) ?   $set.=",type= '$data->type' "  :  $set.=",type=NULL" ;
	(isset($data->categorie) ) ?   $set.=",categorie= '$data->categorie' "  :  $set.=",categorie=NULL" ;
	(isset($data->rang) ) ?   $set.=",rang= '$data->rang' "  :  $set.=",rang=NULL" ;
	(isset($data->officiel) ) ?   $set.=",officiel= '$data->officiel' "  :  $set.=",officiel=NULL" ;
	
	(isset($data->licence) ) ?   $set.=",licence= '$data->licence' "  :  $set.=",licence=NULL" ;
	(isset($data->commentaires) ) ?   $set.=",commentaires= '".utf8_decode($data->commentaires)."' "  :  $set.=",commentaires=NULL" ;
	
	
	
	(isset($data->entr)  &&  $data->entr ) ?   $set.=",entr= '1' "  :  $set.=",entr= '0' " ;
	
	
	( isset($data->cotisation) ) ?  $set.=",cotisation ='$data->cotisation' "  :  $set.=",cotisation = NULL " ;


	( isset($data->banque) ) ?  $set.=",banque ='$data->banque' "  :  $set.=",banque = NULL " ;
	
	( isset($data->cheque1) ) ?  $set.=",cheque1 ='$data->cheque1' "  :  $set.=",cheque1 = NULL " ;
	( isset($data->cheque2) ) ?  $set.=",cheque2 ='$data->cheque2' "  :  $set.=",cheque2 = NULL " ;
	( isset($data->cheque3) ) ?  $set.=",cheque3 ='$data->cheque3' "  :  $set.=",cheque3 = NULL " ;
	
	$total = 0 ;

	if ( isset($data->cheque1) ) { $total = $total + $data->cheque1 ;}
	if ( isset($data->cheque2) ) { $total = $total + $data->cheque2 ;}
	if ( isset($data->cheque3) ) { $total = $total + $data->cheque3 ;}
	

	( isset($data->num_cheque1) ) ?  $set.=",num_cheque1 ='$data->num_cheque1' "  :  $set.=",num_cheque1 = NULL " ;
	( isset($data->num_cheque2) ) ?  $set.=",num_cheque2 ='$data->num_cheque2' "  :  $set.=",num_cheque2 = NULL " ;
	( isset($data->num_cheque3) ) ?  $set.=",num_cheque3 ='$data->num_cheque3' "  :  $set.=",num_cheque3 = NULL " ;
	
	
	
	( isset($data->ch_sport) ) ?  $set.=",ch_sport ='$data->ch_sport' "  :  $set.=",ch_sport = NULL " ;
	( isset($data->num_sport) ) ?  $set.=",num_sport ='$data->num_sport' "  :  $set.=",num_sport = NULL " ;
	
	
	( isset($data->coup_sport) ) ?  $set.=",coup_sport ='$data->coup_sport' "  :  $set.=",coup_sport = NULL " ;
	( isset($data->num_coupsport) ) ?  $set.=",num_coupsport ='$data->num_coupsport' "  :  $set.=",num_coupsport = NULL " ;
	
	
	( isset($data->nbre_chvac10) ) ?  $set.=",nbre_chvac10 ='$data->nbre_chvac10' "  :  $set.=",nbre_chvac10 = NULL " ;
	( isset($data->nbre_chvac20) ) ?  $set.=",nbre_chvac20 ='$data->nbre_chvac20' "  :  $set.=",nbre_chvac20 = NULL " ;
	( isset($data->especes) ) ?  $set.=",especes ='$data->especes' "  :  $set.=",especes = NULL " ;
	
	if ( isset($data->ch_sport) ) { $total = $total + $data->ch_sport ; }
	if ( isset($data->coup_sport) ) { $total = $total + $data->coup_sport ; }
	if ( isset($data->nbre_chvac10) ) { $total = $total + $data->nbre_chvac10 *10 ; }
	if ( isset($data->nbre_chvac20) ) { $total = $total + $data->nbre_chvac20 *20 ; }
	if ( isset($data->especes) ) { $total = $total + $data->especes ; }

	$set.=",total= '$total' ";
	
	
	(isset($data->cert_medical)  &&  $data->cert_medical ) ?   $set.=",cert_medical= '1' "  :  $set.=",cert_medical= '0' " ;
	(isset($data->auto_parentale)  &&  $data->auto_parentale ) ?   $set.=",auto_parentale= '1' "  :  $set.=",auto_parentale= '0' " ;
	(isset($data->fiche_medicale)  &&  $data->fiche_medicale ) ?   $set.=",fiche_medicale= '1' "  :  $set.=",fiche_medicale= '0' " ;
	
	
	(isset($data->photo)  &&  $data->photo ) ?   $set.=",photo= '1' "  :  $set.=",photo= '0' " ;
	
	
	(isset($data->reglement)  &&  $data->reglement ) ?   $set.=",reglement= '1' "  :  $set.=",reglement= '0' " ;
	(isset($data->paye)  &&  $data->paye ) ?   $set.=",paye= '1' "  :  $set.=",paye= '0' " ;
	
	
	 $attestation=false;
	// $data->valide = false ;
	
	if ( $data->valide === false && isset($data->cert_medical)  &&  $data->cert_medical && isset($data->paye)  &&  $data->paye )  {
		
		$set.=",valide= '1' ";
		$set.=",date_valide= NOW() ";
		$attestation=true;
	}
	
//	$attestation=true;
	
	$id = $data->id ;
	
	$set.=" WHERE id = '$id' " ;
	
	$query = "UPDATE  $tlicencies  $set  ";
	
		
	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
		setError( $err );
		return ;
		
	}
	
	
	if ( $attestation ) {
		include 'attestation/attestation_pdf.php';
		$res = send_attestation( $data );
		if ( $res === false ) {
			setError("envoi attestation erreur");
			return;
		}
		
	}
	
//	header("X-Message: modification ok",true);
	header('HeaderName: HeaderValue');
	return get($id);
	
}



/////////////////////////////////////////////////////////////////////////////////////
function delete($id) {
	global $dev,$mysqli;
	global $tlicencies;
	

/*	$query = "DELETE FROM  $tlicencies WHERE id = '$id' ";
	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
		setError( $err );
		return ;
		
	}*/
	header("X-Message: modification ok",true);
	setSuccess("Suppression ok");
}


/////////////////////////////////////////////////////////////////////////////////

function send_attestation ( $data) {
	
	
	global $dev,$dev_email,$saison_enc;
	
	
	$nom = $data->nom;
	$prenom = $data->prenom;
	$cotisation = $data->cotisation;
	$date = $data->date;
	
	if( $dev ) $to=$dev_email;
	
	$to="denis.lesech@gmail.com";
	
	$pdf=doPdf($nom,$prenom,$date,$cotisation,$saison_enc);
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
	$msg .= 'Content-type: text/html; charset=iso-8859-1'."\r\n\r\n";
	$msg .= "<div>Bonjour ,<br><br>";
	$msg.="Pour information, le dossier d'inscription de <strong>$prenom $nom</strong> au club Espérance Chartres de Bretagne Natation pour la $saison_enc a été traité.";
	$msg.="<p>L'attestation d'inscription est en pièce jointe.</p>";
	$msg.="Sportivement<br>--<br>Le bureau de l'association<br>Web : http://ecnatation.fr </div>\r\n";
	
	
	$content = chunk_split(base64_encode($pdf->Output("attestation.pdf","S") ));
	$msg .= '--'.$boundary."\r\n";
	
	$msg .= 'Content-type:application/octet-stream;name=attestation.pdf'."\r\n";
	$msg .= 'Content-transfer-encoding:base64'."\r\n\r\n";
	$msg .= $content."\r\n";
	$msg .= '--'.$boundary."\r\n";
	
	
	$success = mail($to,$subject,$msg,$headers);
	
	
/*	$to      = 'denis.lesech@gmail.com';
	$subject = 'le sujet';
	$message = 'Bonjour !';
	$headers = 'From: webmaster@example.com' . "\r\n" .
			'Reply-To: webmaster@example.com' . "\r\n" .
			'X-Mailer: PHP/' . phpversion(). "\r\n" ;
	$headers .= 'Content-Type: multipart/mixed;boundary='.$boundary."\r\n";
	$headers .= "\r\n";
	
	
	$success = mail($to, $subject, $msg , $headers);*/
	

	return $success ;
	
}

?>