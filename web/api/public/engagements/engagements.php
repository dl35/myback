<?php

switch ($method) {
	case 'PUT':
		$data = json_decode(file_get_contents('php://input'));
	
		if( !isset($_GET['id'])  ||  !isset($_GET['other']) )
		{
			setError("error parametres");
			return;
		}
		
		updateEngagement( $_GET['id']  , $_GET['other'] , $data ) ;
		break;
		
		
	case 'GET':
		
		if( !isset($_GET['id'])  ||  !isset($_GET['other']) )
		{
			setError("error parametres");
			return;
		}
		getEngagement( $_GET['id']  , $_GET['other'] ) ;
		break;
		
		
		
	default:
		echo "error method invalidate" ;
		break;
		
}


function updateEngagement ($ide, $idl, $data) {
	global $dev,$mysqli;
	global $tengagements,$tengage_date;
	
	if ( isset($data->commentaire) ) { $comment =  utf8_decode($data->commentaire  ); }
    else { $comment =NULL ; }


	foreach ($data as $id => $response ) {
		$query = "UPDATE $tengage_date SET presence = ?  WHERE id = ?  ";
		
		$params=array();
		$params[]=$response;
		$start="s";
		$params[]=$id;
		$start.="s";
	
		$stmt = $mysqli->prepare( $query );
		$stmt->bind_param( $start  ,...$params );
		
		
		$result = $stmt->execute();
		if (!$result) {
			($dev) ? $err=$stmt->error : $err="invalid connect";
			$stmt->close();
			setError( $err );
			return;
		}
		
		$stmt->close();
		
	}
	
	$params=array();
	$query = "UPDATE $tengagements SET date_reponse = NOW() ,commentaire = ?  WHERE id = ? AND id_licencies = ? ";
	$params[]=$comment;
	$start="s";
	$params[]=$ide;
	$start.="s";
	$params[]=$idl;
	$start.="s";
	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  ,...$params );
	
	
	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid connect";
		$stmt->close();
		setError( $err );
		return;
	}
	
	$stmt->close();
	$mysqli->close();
	
	$message ="update ok ";
	echo json_encode( $message );
	
	
}

/**
 *
 * @param $id engagement
 * @param $code engagement
 * @return void|string
 */
function getEngagement ($id,$code ) {
	global $dev,$mysqli;
	global $tcompetitions,$tengagements,$tengage_date,$tlicencies;
	

	
	$query = "SELECT  id_competitions,id_licencies,commentaire  FROM $tengagements  WHERE id = '$id' AND id_licencies = '$code' ";
	
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		http_response_code(404);
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
		setError( $err );
		return;
	}
	
	
	
	$num = $result->num_rows ;
	if( $num === 0 ) {
		$error="engagement inconnu";
		setError($error);
		return ;
	}
	
	while($r = $result->fetch_assoc() ) {
		
		$idc= $r['id_competitions'];
		$idl= $r['id_licencies'];
		$comment = $r['commentaire'];
		
	}
	
	$query = "SELECT  id , date , presence   FROM $tengage_date  WHERE id_engage = '$id'  ";
	
	
	
	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err ,404 );
		return ;
	}
	
	$rows = array();
	while($r = $result->fetch_assoc() ) {
		$rows[] = $r;
	}
	$res['engage']=$rows;
	
	
	
	// competitions
	$query = "SELECT nom,lieu,debut,fin,limite,type,choixnages,lien   FROM $tcompetitions  WHERE id = '$idc'  ";
	

	
	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err ,404 );
		return ;
	}
	
	$rows = array();
	while($r = $result->fetch_assoc() ) {
		$rows['nom']=utf8_encode($r['nom']);
		$rows['lieu']=utf8_encode($r['lieu']);
		$rows['type']=utf8_encode($r['type']);
		$rows['debut']=new DateTime($r['debut']);
		$rows['fin']=new DateTime($r['fin']);
		$rows['limite']=new DateTime($r['limite']);
		$rows['choixnages']= $r['choixnages'];
		$rows['lien']= utf8_encode( $r['lien'] );
		$rows['max']= utf8_encode( $r['max'] );
	}
	
	
	
	$now =new DateTime();
	$limite = $rows['limite'] ;
	
	
	
	( $now  <= $limite  ) ? $res['valide']=true : $res['valide']=false ;
	
	
	
	$days=array("","lundi","mardi","mercredi","jeudi","vendredi","samedi","dimanche");
	$months=array("","Janvier","Fevrier","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre");
	
	
	
	
	
	$debut = $days[$rows['debut']->format('N') ] ." ". $rows['debut']->format('j') ." ". $months[$rows['debut']->format('n') ] ." ". $rows['debut']->format('Y')  ;
	$fin= $days[$rows['fin']->format('N') ] ." ". $rows['fin']->format('j') ." ". $months[$rows['fin']->format('n') ] ." ". $rows['fin']->format('Y')  ;
	$limite= $days[$rows['limite']->format('N') ] ." ". $rows['limite']->format('j') ." ". $months[$rows['limite']->format('n') ] ." ". $rows['limite']->format('Y')  ;
	
	
	
	
	$message = "" ;
	( $rows['type'] === "compet"  ) ? $message="La compétition " : $message="Le stage " ;
	$message.= "'".$rows['nom']."' se déroulera ";
	

	
	if( $rows['debut'] !=  $rows['fin'])
	{
		$message.= "du ".$debut ." au " .$fin ;
	}
	else {
		$message.= "le ".$debut ;
	}
	$message.= " à ".$rows['lieu'] . "."  ;
	$message.= "\nMerci de valider votre engagement." ;
	
	
	
	if( $res['valide'] === false )
	{
		$message="Date limite de réponse dépassée: ".$limite.".";
		unset($res['engage']);
	}
	
	if( $rows['choixnages'] === '1' ) {
		$res['choixnages']= true ;
	} else {
		$res['choixnages']= false ;
	}

	
	$res['lien']= $rows['lien'];
	$res['max']= $rows['max'];
	

	$res['message']=( $message );
	$res['commentaire']=( $comment );
	
	header("Content-type:application/json");
	echo  json_encode( $res );
	
	
}













?>

