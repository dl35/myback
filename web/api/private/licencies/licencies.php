<?php
include  '../common/fonctions_categories.php' ;


$auth= array("admin","user");

if ( !isset($profile) || !in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}

switch ($method) {
	case 'PUT':
		$data = json_decode(file_get_contents('php://input'));
		if( isInvalidate($data ) ) {
			invalide ( $data );	
		break ;	
		}

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
function isInvalidate($json) {
	return  (  array_key_exists('invalidate', $json) ) ;
	
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
	


	$json->nom = utf8_decode($json->nom);
	$json->prenom = utf8_decode($json->prenom);
	$json->adresse = utf8_decode($json->adresse);
	$json->ville = utf8_decode($json->ville);
	$json->code_postal = utf8_decode($json->code_postal);



	
	$d = new DateTime( $json->date );
	$df =$d->format('Y-m-d');
	$json->date =$df;
	
	return true;
}




/////////////////////////////////////////////////////////////////////////////////////
function get($id=false) {

	global $dev,$mysqli;
	global $tlicencies_encours;
	
	
	
	( $id === false )  ?  $where=""  :  $where=" WHERE id = '$id' " ;
	
	$query = "SELECT *  FROM $tlicencies_encours  $where  ORDER BY nom, prenom ";
	
	
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
		
		$r['carte']=utf8_encode($r['carte'] );

		if ( !is_null( $r['date_certmedical']) &&  strlen($r['date_certmedical']) == 10 ) {
			$datecert = $r['date_certmedical'] ;	
			$y = substr( $datecert, 0, 4 );
			$m = substr( $datecert, 5, 2 );
			$d = substr( $datecert, 8, 2 );
			$r['date_certmedical'] =$d."/".$m."/".$y ;

		}
	

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
		
		if ($r['ass_ffn'] == '1' ) {
			$r['ass_ffn'] =true ;
		} else if ( $r['ass_ffn'] == '0' ) {
			$r['ass_ffn'] =false ;
		}
			
		
		unset ( $r['telephone'] ) ;
		unset ( $r['email'] ) ;
		
		unset ( $r['niveau'] );
		unset ( $r['date_inscription'] );
		unset ( $r['confirmation_email'] );
		unset ( $r['date_valide'] );
		
		unset ( $r['nbre_chvac10'] );
		unset ( $r['nbre_chvac20'] );
		
		
		$e = json_encode( $r ) ;
		if ( $e != false  )
		{
			$rows[] = $r  ;
		}
	
		
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
	global $tlicencies_encours;
	

	$idlic = createKeyCode( $data->nom , $data->prenom) ;
	

	
	$categorie = CategorieFromDate( $data->date , $data->sexe ) ;
	$rang = RangFromDate( $data->date , $data->sexe );

		
	
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
	
	$email = $v ;
	
	
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
	
	$telephone = $v ;
	
	$params=array();

	$set = "id" ;
	$params[]= $idlic;
	$start ="s";
	$inc="?";

	$set .= ",inscription" ;
	$params[]= '1';
	$start .="s";
	$inc.=",?";

	$set .= ",type" ;
	$params[]= 'N';
	$start .="s";
	$inc.=",?";

	$set .= ",nom" ;
	$params[]= ( $data->nom );
	$start .="s";
	$inc.=",?";

	$set .= ",prenom" ;
	$params[]= ( $data->prenom );
	$start .="s";
	$inc.=",?";

	$set .= ",date" ;
	$params[]= ( $data->date );
	$start.="s";
	$inc.=",?";

	$set .= ",sexe" ;
	$params[]= ( $data->sexe );
	$start.="s";
	$inc.=",?";

	$set .= ",adresse" ;
	$params[]= ( $data->adresse );
	$start.="s";
	$inc.=",?";

	$set .= ",code_postal" ;
	$params[]= ( $data->code_postal );
	$start.="s";
	$inc.=",?";

	$set .= ",ville" ;
	$params[]= ( $data->ville );
	$inc.=",?";
	$start.="s";

	$set .= ",email" ;
	$params[]= ( $email );
	$start.="s";
	$inc.=",?";

	$set .= ",telephone" ;
	$params[]= ( $telephone );
	$start.="s";
	$inc.=",?";

	$set .= ",categorie" ;
	$params[]= ( $categorie );
	$start.="s";
	$inc.=",?";

	$set .= ",rang" ;
	$params[]= ( $rang );
	$start.="s";
	$inc.=",?";

	if ( $categorie === "JE" && $rang === "1" ) {
		$set .= ",niveau" ;
		$inc .= ",'dep'";
	} 


	$set .= ",date_inscription" ;
	$inc.=",NOW()";

	$set ="(".$set.")";
	$inc ="(".$inc.")";

	$query=" INSERT INTO  $tlicencies_encours  $set  VALUES  $inc ";


	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  ,...$params );


	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid query ";
		$stmt->close();
		setError( $err );
		return;
	}
	
	
	// $stmt->close();
	
	
	
	return get( $idlic ) ;
	
	
}
////////////////////////////////////////////////////////////////////////////////////////////////
function update($data) {
	
	global $dev,$mysqli;
	global $tlicencies_encours;
	

	$email=$data->email1.','.$data->email2.','.$data->email3 ;

	$telephone=$data->telephone1.','.$data->telephone2.','.$data->telephone3 ;
	

	$params=array();
		
	$set = "nom = ? " ;
	$params[]= ( $data->nom );
	$start="s";

	$set .= ",prenom = ? " ;
	$params[]= ( $data->prenom );
	$start.="s";

	$set .= ",date = ? " ;
	$params[]= ( $data->date );
	$start.="s";

	$set .= ",sexe = ? " ;
	$params[]= ( $data->sexe );
	$start.="s";

	$set .= ",adresse = ? " ;
	$params[]= ( $data->adresse );
	$start.="s";

	$set .= ",code_postal = ? " ;
	$params[]= ( $data->code_postal );
	$start.="s";

	$set .= ",ville = ? " ;
	$params[]= ( $data->ville );
	$start.="s";

	$set .= ",email = ? " ;
	$params[]= ( $email );
	$start.="s";

	$set .= ",telephone = ? " ;
	$params[]= ( $telephone );
	$start.="s";
  
	$set .= ",categorie = ? " ;
	if ( isset( $data->categorie ) ) {
		$params[]= ( $data->categorie );
	} else {
		$params[] = NULL ;
	}
	$start.="s";


	$set .= ",rang = ? " ;
	if ( isset( $data->rang ) ) {
		$params[]= ( $data->rang );
	} else {
		$params[] = NULL ;
	}
	$start.="s";

	if ( isset( $data->type ) ) {
		$set .= ",type = ? " ;
		$params[]= ( $data->type );
		$start.="s";
	} else {
		$set .= ",type = ? " ;
		$params[]= NULL;
		$start.="s";
	}

	if ( isset( $data->officiel ) ) {
		$set .= ",officiel = ? " ;
		$params[]= ( $data->officiel );
		$start.="s";
	} else {
		$set .= ",officiel = ? " ;
		$params[]= NULL;
		$start.="s";
	}

	if ( isset( $data->licence ) ) {
		$set .= ",licence = ? " ;
		$params[]= ( $data->licence );
		$start.="s";
	} else {
		$set .= ",licence = ? " ;
		$params[]= NULL;
		$start.="s";
	}

	if ( isset( $data->commentaires ) ) {
		$set .= ",commentaires = ? " ;
		$params[]= ( utf8_decode($data->commentaires) );
		$start.="s";
	} else {
		$set .= ",commentaires = ? " ;
		$params[]= NULL;
		$start.="s";
	}
	
	if ( isset( $data->entr ) &&  $data->entr  ) {
		$set .= ",entr = ? " ;
		$params[]= '1';
		$start.="s";
	} else {
		$set .= ",entr = ? " ;
		$params[]= '0';
		$start.="s";
	}
	
	if ( isset( $data->cotisation ) ) {
		$set .= ",cotisation = ? " ;
		$params[]= $data->cotisation ;
		$start.="s";
	} else {
		$set .= ",cotisation = ? " ;
		$params[]= NULL;
		$start.="s";
	}

	if ( isset( $data->carte ) ) {
		$set .= ",carte = ? " ;
		$params[]= $data->carte ;
		$start.="s";
	} else {
		$set .= ",carte = ? " ;
		$params[]= NULL;
		$start.="s";
	}
	
	if ( isset( $data->num_carte ) ) {
		$set .= ",num_carte = ? " ;
		$params[]= $data->num_carte ;
		$start.="s";
	} else {
		$set .= ",num_carte = ? " ;
		$params[]= NULL;
		$start.="s";
	}

	if ( isset( $data->ass_ffn ) ) {
		$set .= ",ass_ffn = ? " ;
		if( $data->ass_ffn ) {
			$params[]= '1';
		} else if( !$data->ass_ffn ) {
			$params[]= '0';
		}
		$start.="s";
	} else {
		$set .= ",ass_ffn = ? " ;
		$params[]= NULL;
		$start.="s";
	}

	if ( isset( $data->date_certmedical ) &&  strlen($data->date_certmedical) === 10   ) {
		$set .= ",date_certmedical = ? " ;
		$d = substr ($data->date_certmedical, 0, 2 ) ;
		$m = substr ($data->date_certmedical, 3, 2 ) ;
		$y = substr ($data->date_certmedical, 6, 4 ) ;

		$params[]= $y."-".$m."-".$d ;
		$start.="s";
	} else {
		$set .= ",date_certmedical = ? " ;
		$params[]= NULL;
		$start.="s";
	}


	if ( isset( $data->banque ) ) {
		$set .= ",banque = ? " ;
		$params[]= $data->banque ;
		$start.="s";
	} else {
		$set .= ",banque = ? " ;
		$params[]= NULL;
		$start.="s";
	}

	if ( isset( $data->cheque1 ) ) {
		$set .= ",cheque1 = ? " ;
		$params[]= $data->cheque1 ;
		$start.="s";
	} else {
		$set .= ",cheque1 = ? " ;
		$params[]= NULL;
		$start.="s";
	}


	if ( isset( $data->cheque2 ) ) {
		$set .= ",cheque2 = ? " ;
		$params[]= $data->cheque2 ;
		$start.="s";
	} else {
		$set .= ",cheque2 = ? " ;
		$params[]= NULL;
		$start.="s";
	}


	if ( isset( $data->cheque3 ) ) {
		$set .= ",cheque3 = ? " ;
		$params[]= $data->cheque3 ;
		$start.="s";
	} else {
		$set .= ",cheque3 = ? " ;
		$params[]= NULL;
		$start.="s";
	}
	

	
	$total = 0 ;

	if ( isset($data->cheque1) ) { $total = $total + $data->cheque1 ;}
	if ( isset($data->cheque2) ) { $total = $total + $data->cheque2 ;}
	if ( isset($data->cheque3) ) { $total = $total + $data->cheque3 ;}
	

	if ( isset( $data->num_cheque1 ) ) {
		$set .= ",num_cheque1 = ? " ;
		$params[]= utf8_decode($data->num_cheque1) ;
		$start.="s";
	} else {
		$set .= ",num_cheque1 = ? " ;
		$params[]= NULL;
		$start.="s";
	}


	if ( isset( $data->num_cheque2 ) ) {
		$set .= ",num_cheque2 = ? " ;
		$params[]= utf8_decode($data->num_cheque2 );
		$start.="s";
	} else {
		$set .= ",num_cheque2 = ? " ;
		$params[]= NULL;
		$start.="s";
	}


	if ( isset( $data->num_cheque3 ) ) {
		$set .= ",num_cheque3 = ? " ;
		$params[]= utf8_decode($data->num_cheque3 );
		$start.="s";
	} else {
		$set .= ",num_cheque3 = ? " ;
		$params[]= NULL;
		$start.="s";
	}

	if ( isset( $data->ch_sport ) ) {
		$set .= ",ch_sport = ? " ;
		$params[]= $data->ch_sport ;
		$start.="s";
	} else {
		$set .= ",ch_sport = ? " ;
		$params[]= NULL;
		$start.="s";
	}


	if ( isset( $data->num_sport ) ) {
		$set .= ",num_sport = ? " ;
		$params[]= utf8_decode($data->num_sport) ;
		$start.="s";
	} else {
		$set .= ",num_sport = ? " ;
		$params[]= NULL;
		$start.="s";
	}
	
	if ( isset( $data->coup_sport ) ) {
		$set .= ",coup_sport = ? " ;
		$params[]= $data->coup_sport ;
		$start.="s";
	} else {
		$set .= ",coup_sport = ? " ;
		$params[]= NULL;
		$start.="s";
	}


	if ( isset( $data->num_coupsport ) ) {
		$set .= ",num_coupsport = ? " ;
		$params[]= utf8_decode($data->num_coupsport) ;
		$start.="s";
	} else {
		$set .= ",num_coupsport = ? " ;
		$params[]= NULL;
		$start.="s";
	}
	
/*	if ( isset( $data->nbre_chvac10 ) ) {
		$set .= ",nbre_chvac10 = ? " ;
		$params[]= $data->nbre_chvac10 ;
		$start.="s";
	} else {
		$set .= ",nbre_chvac10 = ? " ;
		$params[]= NULL;
		$start.="s";
	}

	if ( isset( $data->nbre_chvac20 ) ) {
		$set .= ",nbre_chvac20 = ? " ;
		$params[]= $data->nbre_chvac20 ;
		$start.="s";
	} else {
		$set .= ",nbre_chvac20 = ? " ;
		$params[]= NULL;
		$start.="s";
	}
*/
	if ( isset( $data->cheque_vac ) ) {
		$set .= ",cheque_vac = ? " ;
		$params[]= $data->cheque_vac ;
		$start.="s";
	} else {
		$set .= ",cheque_vac = ? " ;
		$params[]= NULL;
		$start.="s";
	}

	if ( isset( $data->especes ) ) {
		$set .= ",especes = ? " ;
		$params[]= $data->especes ;
		$start.="s";
	} else {
		$set .= ",especes = ? " ;
		$params[]= NULL;
		$start.="s";
	}
		
	if ( isset($data->ch_sport) ) { $total = $total + $data->ch_sport ; }
	if ( isset($data->coup_sport) ) { $total = $total + $data->coup_sport ; }
	// if ( isset($data->nbre_chvac10) ) { $total = $total + $data->nbre_chvac10 *10 ; }
	// if ( isset($data->nbre_chvac20) ) { $total = $total + $data->nbre_chvac20 *20 ; }
	if ( isset($data->cheque_vac) ) { $total = $total + $data->cheque_vac ; }
	if ( isset($data->especes) ) { $total = $total + $data->especes ; }

	
	$set .= ",total = ? " ;
	$params[]= $total ;
	$start.="s";
	
	if ( isset( $data->especes ) ) {
		$set .= ",especes = ? " ;
		$params[]= $data->especes ;
		$start.="s";
	} else {
		$set .= ",especes = ? " ;
		$params[]= NULL;
		$start.="s";
	}

	if ( isset( $data->cert_medical ) &&  $data->cert_medical ) {
		$set .= ",cert_medical = ? " ;
		$params[]= '1' ;
		$start.="s";
	} else {
		$set .= ",cert_medical = ? " ;
		$params[]= '0';
		$start.="s";
	}





	if ( isset( $data->auto_parentale ) &&  $data->auto_parentale  ) {
		$set .= ",auto_parentale = ? " ;
		$params[]= '1' ;
		$start.="s";
	} else {
		$set .= ",auto_parentale = ? " ;
		$params[]= '0';
		$start.="s";
	}

	if ( isset( $data->fiche_medicale ) &&  $data->fiche_medicale ) {
		$set .= ",fiche_medicale = ? " ;
		$params[]= '1' ;
		$start.="s";
	} else {
		$set .= ",fiche_medicale = ? " ;
		$params[]= '0';
		$start.="s";
	}

	if ( isset( $data->photo ) &&  $data->photo ) {
		$set .= ",photo = ? " ;
		$params[]= '1' ;
		$start.="s";
	} else {
		$set .= ",photo = ? " ;
		$params[]= '0';
		$start.="s";
	}
	if ( isset( $data->reglement ) &&  $data->reglement ) {
		$set .= ",reglement = ? " ;
		$params[]= '1' ;
		$start.="s";
	} else {
		$set .= ",reglement = ? " ;
		$params[]= '0';
		$start.="s";
	}
	
	if ( isset( $data->paye ) &&  $data->paye ) {
		$set .= ",paye = ? " ;
		$params[]= '1' ;
		$start.="s";
	} else {
		$set .= ",paye = ? " ;
		$params[]= '0';
		$start.="s";
	}
	
	
	 $attestation=false;
	 
	
	if ( $data->valide === false && isset($data->cert_medical)  &&  $data->cert_medical && isset($data->paye)  &&  $data->paye )  {
		
		$set .= ",valide = ? " ;
		$params[]= '1' ;
		$start.="s";
	
		$set .= ",date_valide = NOW() " ;
	
		$attestation=true;

	}
	

	$params[]= $data->id;
	$start.="s";

	$query = "UPDATE $tlicencies_encours SET $set WHERE id = ? ";

	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  ,...$params );


	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid query";
		$stmt->close();
		setError( $err );
		return;
	}

	
	if ( $attestation ) {
		include 'attestation/attestation_pdf.php';
		$res = send_attestation( $data ,$email );
		if ( $res === false ) {
			setError("envoi attestation erreur");
			return;
		}
		
	}
	
//	header("X-Message: modification ok",true);
//	header('HeaderName: HeaderValue');
	return get( $data->id);
	
}

/////////////////////////////////////////////////////////////////////////////////////
function invalide($data) {
	global $dev,$mysqli;
	global $tlicencies_encours;

		$id = $data->invalidate ;

		$set = "";
		$params = array();
		$start = "" ;

    	$set .= "banque = ? " ;
		$params[]= NULL;
		$start.="s";

		$set .= ",cheque1 = ? " ;
		$params[]= NULL;
		$start.="s";
		$set .= ",cheque2 = ? " ;
		$params[]= NULL;
		$start.="s";
		$set .= ",cheque3 = ? " ;
		$params[]= NULL ;
		$start.="s";
		
	   	$set .= ",num_cheque1 = ? " ;
		$params[]= NULL;
		$start.="s";
		$set .= ",num_cheque2 = ? " ;
		$params[]= NULL;
		$start.="s";
		$set .= ",num_cheque3 = ? " ;
		$params[]= NULL;
		$start.="s";
		$set .= ",ch_sport = ? " ;
		$params[]= NULL;
		$start.="s";
	
		$set .= ",num_sport = ? " ;
		$params[]= NULL;
		$start.="s";
		$set .= ",coup_sport = ? " ;
		$params[]= NULL;
		$start.="s";
		$set .= ",num_coupsport = ? " ;
		$params[]= NULL;
		$start.="s";
		
		/*$set .= ",nbre_chvac10 = ? " ;
		$params[]= NULL;
		$start.="s";
		$set .= ",nbre_chvac20 = ? " ;
		$params[]= NULL;
		$start.="s";*/


		$set .= ",cheque_vac = ? " ;
		$params[]= NULL;
		$start.="s";
		


		$set .= ",especes = ? " ;
		$params[]= NULL;
		$start.="s";
	
		$total = 0 ;
		$set .= ",total = ? " ;
		$params[]= $total ;
		$start.="s";
	
		$set .= ",especes = ? " ;
		$params[]= NULL;
		$start.="s";
	
		$set .= ",cert_medical = ? " ;
		$params[]= '0';
		$start.="s";
		$set .= ",auto_parentale = ? " ;
		$params[]= '0';
		$start.="s";
		$set .= ",fiche_medicale = ? " ;
		$params[]= '0';
		$start.="s";
		$set .= ",photo = ? " ;
		$params[]= '0';
		$start.="s";
		$set .= ",reglement = ? " ;
		$params[]= '0';
		$start.="s";
		$set .= ",paye = ? " ;
		$params[]= '0';
		$start.="s";
		$set .= ",cotisation = ? " ;
		$params[]= '0';
		$start.="s";
		$set .= ",valide = ? " ;
		$params[]= '0';
		$start.="s";
		$set .= ",inscription = ? " ;
		$params[]= '-1';
		$start.="s";
		$set .= ",ass_ffn = ? " ;
		$params[]= NULL ;
		$start.="s";
		$set .= ",date_certmedical = ? " ;
		$params[]= NULL ;
		$start.="s";

		$set .= ",date_valide = ? " ;
		$params[]= NULL ;
		$start.="s";

		$d = new DateTime();
		$df =$d->format('Y-m-d H:i:s');
	


		$set .= ",date_inscription = ? " ;
		$params[]= "$df" ;
		$start.="s";



		$set .= ",commentaires = ? " ;
		$params[]= 'annulation logicielle le '.$df ;
		$start.="s";

		
		$params[]= $id;
		$start.="s";
	
		$query = "UPDATE $tlicencies_encours SET $set WHERE id = ? ";
	
		$stmt = $mysqli->prepare( $query );
		$stmt->bind_param( $start  ,...$params );
	
	
		$result = $stmt->execute();
		if (!$result) {
			($dev) ? $err=$stmt->error : $err="invalid query";
			$stmt->close();
			setError( $err );
			return;
		}



//	header("X-Message: modification ok",true);
	setSuccess("inscription invalide ok");
}


/////////////////////////////////////////////////////////////////////////////////////
function delete($id) {
	global $dev,$mysqli;
	global $tlicencies_encours;
	$query = "DELETE FROM  $tlicencies_encours WHERE id = '$id' ";
	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
		setError( $err );
		return ;
		
	}
	header("X-Message: modification ok",true);
	setSuccess("Suppression ok");
}


/////////////////////////////////////////////////////////////////////////////////

function send_attestation ( $data , $email) {
	
	
	global $dev,$dev_email,$saison_enc,$president;
	
	
	$nom = $data->nom;
	$prenom = $data->prenom;
	$cotisation = $data->cotisation;
	$date = $data->date;
	
	if( $dev ) { 
		$to=$dev_email;
	} else {
		$temails = explode(",", $email);
		$v = "" ;
		foreach($temails as $t  ) {
			 if  ( empty($t) ) continue ;	
			 if  ( empty($v) ) { $v=$t; 
			 } else { $v.=",".$t; }

		 }

		$to=$v;
	}
	
    // viens de attestation/attestation_pdf		 
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
	$msg.="Sportivement<br>--<br>Le bureau de l'association<br>Web : http://ecnatation.fr </div>\r\n";
	
	
	$content = chunk_split(base64_encode($pdf->Output("attestation.pdf","S") ));
	$msg .= '--'.$boundary."\r\n";
	
	$msg .= 'Content-type:application/octet-stream;name=attestation.pdf'."\r\n";
	$msg .= 'Content-transfer-encoding:base64'."\r\n\r\n";
	$msg .= $content."\r\n";
	$msg .= '--'.$boundary."\r\n";
	
	
	$success = mail($to,$subject,$msg,$headers);
	
	

	return $success ;
	
}

?>