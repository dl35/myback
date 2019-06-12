<?php

/*
$auth= array("adm","ecn");

if ( !isset($profile) || !in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}*/

switch ($method) {
	case 'PUT':
	
		$data = json_decode(file_get_contents('php://input'));
		$v= validateObject( $data ) ;
		if( !$v ) { 
			setError("invalid parameters");
			break;
		}
		put( $data) ;
		break;
		
	case 'GET':
		get() ;
		break;
		
	default:
		echo "error" ;
		break;
		
}






/////////////////////////////////////////////////////////////////////////////////////
function validateObject($json) {
	
	
	if ( ! array_key_exists('nom', $json) ) return false ;
	if ( ! array_key_exists('prenom', $json) ) return false ;
	if ( ! array_key_exists('categorie', $json) ) return false ;
	if ( ! array_key_exists('rang', $json) ) return false ;
//	if ( ! array_key_exists('niveau', $json) ) return false ;
	

	return true;
}




/////////////////////////////////////////////////////////////////////////////////////
function get() {

	global $dev,$mysqli;
	global $tlicencies;
	
	
	
	$query = "SELECT id ,nom , prenom , categorie , rang , niveau  FROM $tlicencies
		WHERE categorie is NOT NULL   ORDER BY nom, prenom ";
	
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		http_response_code(404);
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
		setError( $err );
		return; 	
	}
	
	
	
	$rows = array();
	while($r = $result->fetch_assoc() ) {
		
		
		$r['nom']=utf8_encode(  ucfirst( strtolower( $r['nom']  ) ) );
		$r['prenom']=utf8_encode( ucfirst( strtolower( $r['prenom'] ) ) );
		
		
		if ( strlen( $r['niveau'] ) === 0 ) {
			$r['niveau'] = NULL ;
		}
		
		if ( strlen( $r['categorie'] ) === 0 ) {
			continue ;
		}
		
		$r['categorie'] = strtolower( $r['categorie'] );

		$e = json_encode( $r ) ;
		if ( $e !== false  )
		{
			$rows[] = $r  ;
		}
		
		
	}
	
		echo   json_encode($rows);
	
}




////////////////////////////////////////////////////////////////////////////////////////////////


function put($data) {
	
	global $dev,$mysqli;
	global $tlicencies;
	
	
	$nom = utf8_decode($data->nom) ;
	$prenom = utf8_decode($data->prenom) ;
	
	$set=" SET ";
	
	
	$set.="nom= '$nom' , ";
	$set.="prenom= '$prenom' , ";
	
	$set.="categorie= '$data->categorie' , ";
	$set.="rang= '$data->rang' , ";

	if ( isset($data->niveau)  ) {
		$set.="niveau= '$data->niveau'  ";
	} else {
		$set.="niveau= NULL ";
	}
     
	

	$id = $data->id ;
	
	$set.=" WHERE id = '$id' " ;
	$query = "UPDATE  $tlicencies  $set  ";
	
	
		
	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
		setError( $err );
		return ;
	}
	
	
	return get($id);
	
}


?>