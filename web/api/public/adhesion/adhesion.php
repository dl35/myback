<?php
include  '../common/fonctions_categories.php' ;

switch ($method) {
	case 'PUT':
		$data = json_decode(file_get_contents('php://input'));
		if(  !isset($id) )
		{
			setError("PUT id parameters");
			return;
		}
		$v= validationParams($data) ;
		if( $v === false ) {
			return;
		}
		update($id,$data) ;
		break;

	case 'POST':
		$data = json_decode(file_get_contents('php://input'));
		$v= validationParams( $data ) ;
		if( $v === false ) {
			setError("Post parameters");
			return;
		}
		add(  $data) ;
		break;

	case 'GET':
		if(  !isset($id) )
		{
		  setError("id parameters");
		  return;
		}
		get($id);
		break;
	default:
		setError("Method not exist");
		break;

}


/////////////////////////////////////////////////////////////////////////////////////
function validationParams( $data  ) {
	
	if ( !$data )
	{
		$error ="invalid data parameter" ;
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
/////////////////////////////////////////////////////////////////
function validateObject( $data ) {

	
	
	
	if ( ! array_key_exists('nom', $data) ) return false ;
	if ( ! array_key_exists('prenom', $data) ) return false ;
	if ( ! array_key_exists('date', $data) ) return false ;
	if ( ! array_key_exists('sexe', $data) ) return false ;
	
	if ( ! array_key_exists('adresse', $data) ) return false ;
	if ( ! array_key_exists('cp', $data) ) return false ;
	if ( ! array_key_exists('ville', $data) ) return false ;
	
	if ( ! array_key_exists('email1', $data) ) return false ;
	if ( ! array_key_exists('email2', $data) ) return false ;
	if ( ! array_key_exists('email3', $data) ) return false ;

	
	if ( ! array_key_exists('tel1', $data) ) return false ;
	if ( ! array_key_exists('tel2', $data) ) return false ;
	if ( ! array_key_exists('tel3', $data) ) return false ;

	

	$data->nom=utf8_decode($data->nom);
	$data->prenom=utf8_decode($data->prenom);
	$data->adresse=utf8_decode($data->adresse);
	$data->ville=utf8_decode($data->ville);
	
	
	
	return true;
	
}
////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////
function get($id) {
	global $dev ;
	global $tlicencies_encours;
	global $mysqli;
	
	
	$where =" WHERE id ='$id' " ;
	$query = "SELECT id,nom,prenom,date,sexe,adresse,code_postal,ville,email,telephone  FROM $tlicencies_encours  $where LIMIT 1 ";
	
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error: $err="invalid query";
		setError( $err );
		return;
	}
	
	
	
	$res=false;
	while($r = $result->fetch_assoc() ) {
		
		
		$res['nom']=utf8_encode( $r['nom'] );
		$res['prenom']=utf8_encode( $r['prenom'] );
		$res['adresse']=utf8_encode( $r['adresse'] );
		$res['cp']=utf8_encode( $r['code_postal'] );
		$res['ville']=utf8_encode( $r['ville'] );
		$res['date']=utf8_encode( $r['date'] );
		$res['sexe']=utf8_encode( $r['sexe'] );
		
		
		$emails = explode(",", $r['email'] );
		isset( $emails[0] ) ? $res['email1']=$emails[0] :$res['email1']="" ;
		isset( $emails[1] ) ? $res['email2']=$emails[1] :$res['email2']="" ;
		isset( $emails[2] ) ? $res['email3']=$emails[2] :$res['email3']="" ;
		
		$tels = explode(",", $r['telephone'] );
		isset( $tels[0] ) ? $res['tel1']=$tels[0] :$res['tel1']="" ;
		isset( $tels[1] ) ? $res['tel2']=$tels[1] :$res['tel2']="" ;
		isset( $tels[2] ) ? $res['tel3']=$tels[2] :$res['tel3']="" ;
		
		
		
	}
	
	if (count($res) === 0  ) {
		setError("cet id n'existe pas");
		return;
	}
	
	header("Content-type:application/json");
	echo json_encode($res);
	
}



////////////////////////////////////////////////////////////////////////////////////////////////
function update($id ,$data) {
	
	global $dev;
	global $tlicencies_encours;
	global $mysqli ;
	
	
	$email=$data->email1.','.$data->email2.','.$data->email3 ;
	$tel=$data->tel1.','.$data->tel2.','.$data->tel3 ;
	
	$cat = CategorieFromDate( $data->date , $data->sexe ) ;
	$rang = RangFromDate( $data->date , $data->sexe );

	$data->categorie = $cat ;
	$data->rang = $rang ;


	$set=" SET ";
	
	
	$set.="nom= '$data->nom' , ";
	$set.="prenom= '$data->prenom' , ";
	
	$set.="date= '$data->date' , ";
	$set.="sexe= '$data->sexe' , ";
	$set.="adresse= '$data->adresse' , ";
	$set.="code_postal= '$data->cp' , ";
	$set.="ville= '$data->ville' , ";
	$set.="email= '$email' , ";
	$set.="telephone= '$tel' , ";
	
	$set.="inscription= '1', ";
	$set.="date_inscription= NOW() , ";

	$set.="categorie= '$cat', ";
	$set.="rang= '$rang' ";

	
	$set.=" WHERE id = '$id'  " ;
	
	$query = "UPDATE  $tlicencies_encours  $set  ";
	


	
	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		http_response_code(404);
		($dev) ? $err=$mysqli->error: $err="invalid query";
		setError( $err );
		return ;
		
	}
	
	include 'makePdf.php' ;
	$ret =  sendmailpdf( $data );
	
	if( $ret ) {
		setSuccess("Modification, Email ok");
	} else {
		setSuccess("error");
	}

	
	
}

////////////////////////////////////////////////////////////////////////////////////////////////
function add($data) {
	
	global $dev;
	global $tlicencies_encours;
	global $mysqli;
	
	
	$query=" SELECT id FROM $tlicencies_encours ";

	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		http_response_code(404);
		($dev) ? $err=$mysqli->error: $err="invalid query";
		setError( $err );
		return;
	}
	
	$tcode=array();	
	while($r = $result->fetch_assoc() ) {
		
		$tid[]=$r['id'];
		
	}
	

	$data->date=substr($data->date ,0,10);
				
	$id=createKeyCode($data->nom , $data->prenom );
	while (true)
	{
			
		if( ! in_array($id, $tid))
		{
			break;
		}
		$id=createKeyCode($data->nom , $data->prenom );
	}
	


	$email=$data->email1.','.$data->email2.','.$data->email3 ;
	$tel=$data->tel1.','.$data->tel2.','.$data->tel3 ;

	$cat = CategorieFromDate($data->date, $data->sexe);
	$rang = RangFromDate($data->date, $data->sexe );	

	$data->categorie = $cat ;
	$data->rang = $rang ;

	$set="(id,nom,prenom,date,sexe,adresse,code_postal,ville,email,telephone,inscription,date_inscription,categorie,rang" ;
	$values="('$id','$data->nom','$data->prenom','$data->date','$data->sexe','$data->adresse','$data->cp','$data->ville','$email','$tel','1',NOW(),'$cat','$rang' ";
	$set.=") ";
	$values.=") ";

	$query=" INSERT INTO  $tlicencies_encours  $set  VALUES  $values ";

	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		($dev) ? $err=$mysqli->error: $err="invalid query";
		setError( $err );
		return;
	}
	

	include 'makePdf.php' ;
	$ret =  sendmailpdf( $data );
	
	if( $ret ) {
		setSuccess("Ajout, email ok");
	} else {
		setSuccess("Mail erreur");
	}

	
	
}


?>
