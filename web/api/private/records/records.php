<?php

$auth= array("admin","user","ent");

if ( !isset($profile) && in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}


switch ($method) {


	case 'GET':
		if( isset($id) && $id === 'names' ) {
			getNames() ;
		} else {
			get() ;
		}
		break;
	case 'POST':
		upload( $_FILES );
		break;
		
		
	case 'PUT':
		$data = json_decode(file_get_contents('php://input'));
		put($data) ;
		break;
	default:
		setError( "invalides routes" ,405);
		break;

}
/////////////////////////////////////////////////////////////////////////////////////////////
function upload( $file ) {
	
	$taille_maxi = 6000000;
	$taille = filesize($file['file']['tmp_name']);
	//$extensions = array('.xml');
	$extensions = array('.pdf');
	
	$extension = strrchr($file['file']['name'], '.');
	
	
	if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
	{
		$message = 'Vous devez uploader un fichier de type ???xml';
		setError( $message ,400);
		return;
	}
	if($taille>$taille_maxi)
	{
		$message= 'Le fichier est trop gros...';
		setError( $message ,400);
		return;
	}

/* Getting file name */
	$filename = $file['file']['name'];
	
	
	/* Location */
	$location =  $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $file["file"]["name"] ;
	
	/* Upload file */
	move_uploaded_file($file['file']['tmp_name'],$location );
	
	//$arr = array("name"=>$filename);
	$message = array("message"=>"upload");
	echo json_encode( $message );
	
	
}


//////////////////////////////////////////////////////////////////////////////////////////////
function getNames() {
	
	global $trecords,$tlicencies;
	global $dev,$mysqli;
	
	
	$query  = "SELECT nom , prenom , sexe FROM ".$trecords." GROUP BY nom,prenom,sexe ";
	$query .= " UNION " ;
	$query .= " SELECT nom ,  prenom , sexe FROM  ".$tlicencies." where valide ='1' GROUP BY  nom, prenom, sexe  ORDER BY sexe , nom , prenom  ";
	
	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err ,404 );
		return ;
	}
	
	
	
	$rows = array();
	while($r = $result->fetch_assoc() ) {

		$name = ucfirst( strtolower( utf8_encode($r['nom'] ) ) );
		$prename =ucfirst( strtolower( utf8_encode($r['prenom'] ) ) );
		if ( strlen( $name) === 0 ) {
			continue ;
		}
		
		if ( strlen( $prename) === 0 ) {
			continue ;
		}
		
		$r['nom'] = $name ;
		$r['prenom'] = $prename;
		$e = json_encode( $r ) ;
		if ( $e != false  )
		{
			$rows[] = $r  ;
		}
	}
	
	$mysqli->close();
	echo json_encode($rows);
	
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function get() {
	
	global $trecords;
	global $dev,$mysqli;

	
	$query = "SELECT *  FROM ".$trecords." ORDER BY bassin,sexe,nage,age,nom,prenom ";


	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err ,404 );
		return ;
	}

	

	$datas = array();
	while($r = $result->fetch_assoc() ) {


		if( empty($r['nage'])) continue ;
		
		$r['nom']=utf8_encode($r['nom'] );
		$r['prenom']=utf8_encode($r['prenom'] );
		$r['lieu']=utf8_encode($r['lieu'] );
		
		if( $r['bassin'] == '25' ) {
			
			if ( $r['sexe'] == 'F' ) {
				
			} else {
				
				
			}
			
			
		} else {
			
			
		}
		
		
		
		$e = json_encode( $r ) ;
		if ( $e != false  )
		{
			$datas[] = $r  ;
		}
	}
	
	$mysqli->close();
	echo json_encode($datas);

}


function update($data) {
	
	global $db, $base ;
	global $trecords;
	
	$r=mysql_select_db($base) ;
	if (!$r ) {
		setError(mysql_error($db));
		return;
	}
	
	$query = "UPDATE $trecords  $set  ";
	
	$result = mysql_query( $query ) ;
	if (!$result) {
		setError( "invalid request ".$query );
		return;
	}
	
	setSuccess("update ok");
	
	
	
}


?>