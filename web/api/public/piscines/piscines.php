<?php



switch ($method) {
	
	case 'POST':
		$data = json_decode(file_get_contents('php://input'));
		if( !isset( $data ) || ! isset($data->bbox) ) {
			setError( "invalides routes" ,404);
			break;
		}
		
		getdatas( $data->bbox ) ;
		break;
		
	default:
		setError( "invalides routes" ,404);
		break;
		
}

function getDatas( $bbox ) {
	global $dev, $mysqli;
	
	list($left,$bottom,$right,$top)=explode(",",$bbox);
	
	$query = "SELECT * FROM piscine WHERE longitude>=".$left." AND longitude<=".$right." AND latitude>=".$bottom." AND latitude<=".$top ;
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		setError( $err );
		return ;
	}
	
//	https://waze.com/ul?q=66%20Acacia%20Avenue&ll=45.6906304,-120.810983&navigate=yes
	
	
	while($geo = $result->fetch_assoc()  ) {
		
		
		( $geo['bassin'] == '25') ? $c="#ff7800" :  $c="#ff0000";
		
		$formation[] = array(
				'type' 		=> 'Feature',
				'geometry'	=> array(
						'type' => 'Point',
						'coordinates' => array(
								$geo['longitude'],
								$geo['latitude'])),
				'properties' => array(
						'name' => utf8_encode( $geo['libelle'] ) ,
						'ville' => $geo['cp']." ".utf8_encode( $geo['ville'] ) ,
						'couloir' =>$geo['couloirs'] ,
						'bassin' =>$geo['bassin'] ,
						'description' => utf8_encode( $geo['adresse'] ) ,
						'color' => "$c" ,
						'source' => 'ecn' ,
						'waze' => 'ul?ll=' .$geo['latitude'] . ',' . $geo['longitude'] .'&navigate=yes',
						'precision' => $geo['region'] ));
								
								
								
	}
	if( !isset($formation ) ) {
		$formation= [];
	}
	echo  json_encode($formation);
	
}



?>
