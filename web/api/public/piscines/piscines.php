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
/////////////////////////////////////////////////////////////
function getDatas( $bbox ) {
	global $dev, $mysqli;
	
	list($left,$bottom,$right,$top)=explode(",",$bbox);
	
	$query = "SELECT * FROM piscines WHERE longitude>=".$left." AND longitude<=".$right." AND latitude>=".$bottom." AND latitude<=".$top ;
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		setError( $err );
		return ;
	}
	
	
	
	
	while($geo = $result->fetch_assoc()  ) {
		
		$id = $geo['id'] ;
		$query = "SELECT longueur,couloirs FROM bassins WHERE id_piscines = '$id' " ;
		$rbassins = $mysqli->query( $query ) ;
		if (!$rbassins) {
			//setError( $mysqli->error );
			break;
		}

		
		( $geo['bassin'] == '25') ? $c="#ff7800" :  $c="#ff0000";
		
		$f = array(
				'type' 		=> 'Feature',
				'geometry'	=> array(
						'type' => 'Point',
						'coordinates' => array(
								$geo['longitude'],
								$geo['latitude'])),
				'properties' => array(
					    'id' =>  $geo['id']  ,
						'name' => utf8_encode( $geo['libelle'] ) ,
						'ville' => $geo['cp']." ".utf8_encode( $geo['ville'] ) ,
						//'bassins' =>$rbassins ,
						'description' => utf8_encode( $geo['adresse'] ) ,
						'color' => "$c" ,
						'source' => 'ecn' ,
						'waze' => $geo['latitude']. "," . $geo['longitude'] ,
						'precision' => $geo['region'] ));
			$ch = [] ;					
			foreach ($rbassins as $b )	{
					$len = $b['longueur'] ;
					$coul = $b['couloirs'] ;
					$ch [] = $len . " m ".$coul ." coul";
			}				 
			$f['properties']['bassins'] = $ch ;
			$formation[] = $f ;

	}
	if( !isset($formation ) ) {
		$formation= [];
	}


	echo  json_encode($formation);
	
}

?>
