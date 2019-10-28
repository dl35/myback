<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
$auth= array("admin","user","ent");

if ( !isset($profile) && in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}


switch ($method) {


	case 'GET':
		if( isset($id) && $id === 'names' ) {
			getNames() ;
		} 
		else if( isset($id) && $id === 'compet' ) {
			getCompet() ;
		}	
		else {
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

////////////////////////////////////////////////////////////////////////////////////////
function getCompet() {
	global $dev ;
	global $tcompetitions ;
	global $dev,$mysqli;
	
	
	
	$query = "SELECT id,nom,debut,fin  FROM $tcompetitions WHERE verif='1' AND fin <= NOW()  ORDER BY debut, fin ";
	
		
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err );
		return ;
	}


	$rows = array();
	while($r = $result->fetch_assoc() ) {
		
	
		$debut = new DateTime($r['debut']);
		$day = $debut->format("D d M");
		$day = formatFr( $day );
		
		$day2 = $debut->format("yyyyMMdd");

		$label = $r['nom']." (".$day .")";
		$label = utf8_encode( $label );
		$res['value']=$r['id']."_".$day2;
		$res['label']=$label;
		$rows[] = $res;
		
		
	}


	
	echo  json_encode($rows);


}
////////////////////////////////////////////////////////////////////////////////////////////

function formatFr($format) {
	
	$english_days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
	$french_days = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
	
	$english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	$french_months = array('Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre');
	
	$english_msmall = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	$french_msmall= array('Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jui', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec');
	
	$english_dsmall = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
	$french_dsmall = array('Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim');
	
	
	
	$format=str_replace($english_months, $french_months,$format);
	$format=str_replace($english_days, $french_days,$format);
	$format=str_replace($english_msmall, $french_msmall,$format);
	$format=str_replace($english_dsmall, $french_dsmall,$format);
	
	
	return $format;
}
/////////////////////////////////////////////////////////////////////////////////////////////
function upload( $file ) {
	
	$taille_maxi = 6000000;
	$taille = filesize($file['file']['tmp_name']);
	$extensions = array('.xml');
	//$extensions = array('.pdf');
	
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
	echo $filename ;


	echo $_SERVER['DOCUMENT_ROOT'] ;
 

	/* Location */
	$location =  $_SERVER['DOCUMENT_ROOT'] . 'upload/' . $file["file"]["name"] ;
	$location =  $_SERVER['DOCUMENT_ROOT'] .  $file["file"]["name"] ;
	
	if (file_exists($location)) {
		echo "Le fichier $filename existe.";
	} else {
		echo "Le fichier $filename n'existe pas.";
	}


	/* Upload file */
//	move_uploaded_file($file['file']['tmp_name'],$location );
	
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
		
		$pts = 	$r['points'] ;
		if ( $pts === NULL ) {
			$r['points'] = 0 ;
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