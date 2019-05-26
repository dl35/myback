<?php


switch ($method) {


	case 'GET':
		getDatas() ;
		break;


	default:
		setError("invalid routes");
		break;

}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getDatas() {
	
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
		
		
		$e = json_encode( $r ) ;
		if ( $e != false  )
		{
			$datas[] = $r  ;
		}
	

	}
	
	$mysqli->close();
	
	
	
	echo json_encode($datas);

}

?>