<?php

switch ($method) {
	case 'GET':
		if ( isset($id) ) {
			getEngagements($id) ;
		} else {
			getCompetitions() ;
		}
		break;
	
	default:
		echo "error" ;
		break;

}

/////////////////////////////////////////////////////////////////////////////////
function formatFr($format) {
	
	$english_days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
	$french_days = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
	
	$english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	$french_months = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
	
	$english_msmall = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	$french_msmall= array('Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jui', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc');
	
	$english_dsmall = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
	$french_dsmall = array('Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim');
	
	
	
	$format=str_replace($english_months, $french_months,$format);
	$format=str_replace($english_days, $french_days,$format);
	$format=str_replace($english_msmall, $french_msmall,$format);
	$format=str_replace($english_dsmall, $french_dsmall,$format);
	
	
	return $format;
}

//////////////////////////////////////////////////////////////////////////////////////////
function getCompetitions() {
	global $tcompetitions ;
	global $dev,$mysqli;
	global $tengagements ;


	
	
	$query = "SELECT count(*) as nb , (id_competitions) as idc FROM $tengagements GROUP BY id_competitions ";
	
	
	$result = $mysqli->query($query) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err );
		return ;
	}
	
	
	$map = array();
	while($r = $result->fetch_assoc() ) {
				
			$idc = $r['idc'] ;
			$nb = $r['nb'] ;
			$map[ $idc ]= $nb;
		}


    


	
	$query = "SELECT id,nom,lieu,debut,fin,type  FROM $tcompetitions  ORDER BY debut, fin ";
	
		
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		http_response_code(404);
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
		setError( $err );
		return;
	}
	
	
	$now = new DateTime() ;
	$rows = array();
	while($r = $result->fetch_assoc() ) {
		
		 $id = $r['id'] ;
		( isset( $map[$id] ) ) ?  $res['nb']=$map[$id]:$res['nb']=0;
	
		$datetime = new DateTime($r['fin'] .' 00:00:00');
		( $datetime >= $now ) ?  $res['next'] = true  : $res['next'] = false ;

		
		$res['type'] = $r['type'] ;
		$res['nom'] = utf8_encode( $r['nom'] );
		$res['lieu'] = utf8_encode( $r['lieu'] );
		$res['debut'] = $r['debut'];
		$res['fin'] = $r['fin'];

		$res['id']=$r['id'];
	
		$rows[] = $res;
		
		
	}
	
	
	
	echo  json_encode(array_values($rows));
	
}
///////////////////////////////////////////////////////////////
function getEngagements($id) {
	
	global $tlicencies,$tengagements,$tcompetitions,$tengage_date;
	global $dev,$mysqli;
	
	$query = "SELECT id,nom,lieu,debut,fin,type  FROM $tcompetitions  WHERE id ='$id' ";
	
		
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		http_response_code(404);
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
		setError( $err );
		return;
	}
	$dcompet = array();
	while($r = $result->fetch_assoc() ) {

		$dcompet['nom'] = utf8_encode( $r['nom'] ) ;
		$dcompet['lieu'] = utf8_encode( $r['lieu'] ) ;
		$dcompet['debut'] =  $r['debut']  ;
		$dcompet['fin'] =  $r['fin']  ;

		

	}		


		
	$query="SELECT engage_date.date,engage_date.id as edid ,$tengagements.id_licencies,$tengagements.id_competitions,$tlicencies.nom,$tlicencies.prenom,$tlicencies.categorie,$tlicencies.sexe,$tlicencies.rang,$tengage_date.presence ".
			" FROM $tlicencies,$tengagements,$tengage_date ".
			" WHERE  $tengagements.id_competitions = $id  ".
			" AND  $tlicencies.id=$tengagements.id_licencies ".
			" AND $tengage_date.id_engage = $tengagements.id ".
			" ORDER BY $tlicencies.nom, $tlicencies.prenom,$tengage_date.date asc ";
	
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error: $err="invalid query";
		setError( $err );
		return ;
	}
	
	
	
	$rows = array();
	$last="";
	while($r = $result->fetch_assoc() ) {
		//unset( $r['commentaires'] ) ;
		//$r['comment']=utf8_encode($r['comment'] );
		$r['nom']=utf8_encode($r['nom'] );
		$r['prenom']=utf8_encode($r['prenom'] );
		$r['categorie']=ucfirst( $r['categorie'] );
		
		$tengage=array();
		$tengage['day'] = substr($r['date'],8 );
		$tengage['presence'] = $r['presence'];
		
		
		unset($r['date']);
		unset($r['presence']);
		unset($r['edid']);
		
		if(empty($last) ) {
			$last = $r ;
			$last['eng'][]=$tengage;
		} else if ( $last['id_licencies'] == $r['id_licencies'] ) {
			$last['eng'][]=$tengage;
		} else {
			
			$rows[] = $last;
			$last = $r ;
			$last['eng'][]=$tengage;
		}
		
		
		
	}
	
	if ( !empty($last)  )
		$rows[] = $last;
	
	$resultat['compet'] = $dcompet ;	
	$resultat['engage'] = array_values($rows) ;

	echo  json_encode($resultat ,JSON_NUMERIC_CHECK );
	//	echo  json_encode(array_values($rows),JSON_NUMERIC_CHECK );
		
}

?>