<?php

$auth= array("admin","user","ent");

if ( !isset($profile) && in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}

switch ($method) {

	case 'GET' :
	if( !isset( $id ) ) {
		setError( "invalides routes" ,404);
		break;
	} 
	  getPiscine($id) ;
	break ;
	
	case 'POST':
		$data = json_decode(file_get_contents('php://input'));
		if( !isset( $data ) ) {
			setError( "invalides routes" ,404);
			break;
		} 
		
		if ( isset($data->bbox )  ) {
			getdatas( $data->bbox ) ;
			return ;
		} else if ( !validateObject( $data)  )	{
			$error ="invalid object parameters " ;
			setError($error);
			return false ;
		}
		
	
		add($data) ;
		
		break;

		case 'PUT':
		$data = json_decode(file_get_contents('php://input'));
		if( !isset( $data ) && !isset( $id )  ) {
			setError( "invalides routes" ,404);
			break;
		} 
		if ( !validateObject( $data)  )
		{
			$error ="invalid object parameters " ;
			setError($error);
			return false ;
		}
	
		update($data , $id) ;
		break;

		case 'DELETE'	:
		if( !isset( $id ) ) {
			setError( "invalides routes" ,404);
			break;
		} 
		delete( $id );
		break;

	default:
		setError( "invalides routes" ,404);
		break;
		
}


///////////////////////////////////////////////////////////

function getPiscine( $id ) {
	global $dev, $mysqli;
	
	$q1 = "SELECT id,libelle,adresse,cp,ville,latitude,longitude FROM piscines WHERE id = '$id' ";
	$q2 = "SELECT longueur,couloirs FROM bassins  WHERE id_piscines = '$id' ";
	
	$r1 = $mysqli->query( $q1 ) ;
	if (!$r1) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		setError( $err );
		return ;
	}
		$res= array();
	while($row = $r1->fetch_assoc()  ) {
		$d =array();
		foreach($row as $key => $value){ // each key in each row
			$d[$key] =utf8_encode($value);
		  }

		$res = $d;
	}

	$r2 = $mysqli->query( $q2 ) ;
	if (!$r2) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		setError( $err );
		return ;
	}
	
	while($row = $r2->fetch_assoc()  ) {
		$d =array();
		foreach($row as $key => $value){ // each key in each row
			$d[$key] =utf8_encode($value);
		  }

		$res['bassins'][] = $d;
	}

		$r1->close();
		$r2->close();
		$mysqli->close();

		echo json_encode( $res ) ;

}

/////////////////////////////////////////////////////////////
function delete( $id ) {
	global $dev, $mysqli;
	$query = "DELETE FROM piscines,bassins USING piscines , bassins WHERE piscines.id = '$id' and  piscines.id = bassins.id_piscines " ;

	$result = $mysqli->query( $query ) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		setError( $err );
		return ;
	}
	
	setSuccess("supp ok");

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
/////////////////////////////////////////////////////////////////////////

function validateObject($json) {
  
  $json->libelle = utf8_decode( $json->libelle );
  $json->ville = utf8_decode( $json->ville );
  $json->adresse = utf8_decode( $json->adresse );
  

  return true ;

}
/////////////////////////////////////////////////////////////////////////
function add($data) {
	global $dev, $mysqli;
	
	$set = "libelle" ;
	$params[] = ucfirst( strtolower( $data->libelle   )  );
	$start = "s";
	$inc = "?";

	$set .= ",adresse" ;
	$params[] = ucfirst( strtolower( $data->adresse   )  );
	$start .= "s";
	$inc .= ",?";

	$set .= ",cp" ;
	$params[] = ucfirst( strtolower( $data->cp  )  );
	$start .= "s";
	$inc .= ",?";

	$set .= ",ville" ;
	$params[] = ucfirst( strtolower( $data->ville   )  );
	$start .= "s";
	$inc .= ",?";

	$set .= ",latitude" ;
	$params[] = ucfirst( strtolower( $data->latitude   )  );
	$start .= "s";
	$inc .= ",?";

	$set .= ",longitude" ;
	$params[] = ucfirst( strtolower( $data->longitude   )  );
	$start .= "s";
	$inc .= ",?";


	$set ="(".$set.")";
	$inc ="(".$inc.")";
	
	$query=" INSERT INTO  piscines  $set  VALUES  $inc ";
	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  , ...$params );


	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid execute";
		setError(404 , $err );
		return;
	}
	
	
	$stmt->close();
	$id = $mysqli->insert_id ;
	
	$res = insertBassins($id, $data->bassins  ,$mysqli  ) ;
	if ( $res ) {
		$message=array();
		$message['success']=true;
		$message['message']= "ajout ok";
		echo json_encode( $message );

	} else {
		setError( $res  );
	}

}
////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////
function update($data, $id ) {
	global $dev, $mysqli;
	global $tpiscines;


	$set = "libelle=?" ;
	$params[] = ucfirst( strtolower( $data->libelle   )  );
	$start = "s";
	

	$set .= ",adresse=?" ;
	$params[] = ucfirst( strtolower( $data->adresse   )  );
	$start .= "s";


	$set .= ",cp=?" ;
	$params[] = ucfirst( strtolower( $data->cp  )  );
	$start .= "s";


	$set .= ",ville=?" ;
	$params[] = ucfirst( strtolower( $data->ville   )  );
	$start .= "s";


	$set .= ",latitude=?" ;
	$params[] = ucfirst( strtolower( $data->latitude   )  );
	$start .= "s";
	

	$set .= ",longitude=?" ;
	$params[] = ucfirst( strtolower( $data->longitude   )  );
	$start .= "s";
	
	$query = "UPDATE $tpiscines SET $set  WHERE id = ?  ";


	$params[]= $id;

	$start.="s";
	$stmt = $mysqli->prepare( $query );
	if ( !$stmt ) {
		( $dev ) ? $err=$mysqli->error : $err="invalid execute";
		setError($err , 404);
		return;	
	}
	$stmt->bind_param( $start  ,...$params );


	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid execute";
		setError($err , 404);
		return;
	}

	$query = "DELETE FROM bassins  WHERE bassins.id_piscines = '$id' " ;
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		setError( $err );
		return ;
	}

	$res = insertBassins($id, $data->bassins  ,$mysqli  ) ;
	if ( $res ) {
		$message=array();
		$message['success']=true;
		$message['message']= "update ok";
		echo json_encode( $message );

	} else {
		setError( $res  );
	}

}
/////////////////////////////////////////////////////////////////////////////
function insertBassins($id,$bassins,$mysqli){
	
 $stmt = $mysqli->prepare("INSERT INTO bassins (id_piscines , longueur, couloirs ) VALUES (? , ? , ?) ");
 	foreach($bassins as $item)
	{
		$stmt->bind_param("sss", $id , $item->longueur , $item->couloirs );
		$res= $stmt->execute();
		if (!$res) {
			$err=$stmt->error ;
			break ;
		} 


	}
$stmt->close();

if (isset($err) ) {
	return $err ;
} else return true ;


}



?>