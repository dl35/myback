<?php
$auth= array("admin","user","ent");

if ( !isset($profile) && in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}



switch ($method) {
	case 'PUT':
		$data = json_decode(file_get_contents('php://input'));
		$v= validationParams($data , $_GET  ) ;
		if( !$v ) break;
		update($_GET['id'] , $data) ;
		break;
		
	case 'POST':
		$data = json_decode(file_get_contents('php://input'));
		$v= validationParams($data , false ) ;
		if( !$v ) break;
		add( $data) ;
		break;
		
	case 'GET':
		if ( isset($id)  && $id === 'ent' ) {
			getEnt() ;
		} else {
			getCompetitions() ;
		}
		
		break;
		
	case 'DELETE':
		if (!isset($_GET ['id'] ) )
		{
		  setError( "invalid parameters" ,404 );
		  return  ;
		}
		delete( $_GET['id'] );
		break;
		
	default:
		echo "error" ;
		break;
		
}



function validationParams( $data, $get  ) {
	
	
	if ( !$data )
	{
		
		$error ="invalid data parameters " ;
		setError($error);
		return false ;
	}
	
	
	if ( $get !== false   && !isset($get['id'] ) )
	{
		
		$error ="invalid id parameters " ;
		setError($error);
		return false ;
	}
	
	
	if ( !validateObject( $data)  )
	{
		$error ="invalid object parameters " ;
		setError($error);
		return false ;
		
		
	}
	
	
	$deb = new DateTime($data->debut);
	$fin = new DateTime($data->fin);
	$d = $fin->diff($deb)->days; 

	if( $d < 0  ||  $d >=7 ) {
		$error ="date : 6 jours maxi!" ;
		setError($error);
		return false ;
	}
	
	return true ;
}

function validateObject($json) {
	
		
	if( !isset( $json->nom )     ) return false ;
	if( !isset( $json->lieu )    ) return false ;
	if( !isset( $json->categories )    ) return false ;
	if( !isset( $json->bassin )    ) return false ;
	if( !isset( $json->type )    ) return false ;
	
	if( !isset( $json->debut )    ) return false ;
	if( !isset( $json->fin )    ) return false ;
	if( !isset( $json->limite )    ) return false ;
	if( !isset( $json->heure )    ) return false ;
	if( !isset( $json->entraineur )    ) return false ;
	
	
	if( ($json->type == "stage" ) )
	{
		if ( !isset($json->max) )  return false ;
		if ( $json->max <= 0 ) return false ;
		
	}
	
	$json->nom = utf8_decode( $json->nom );
	$json->lieu = utf8_decode( $json->lieu );
	$json->commentaires = utf8_decode( $json->commentaires );
	$json->lien = utf8_decode( $json->lien );
	
	return true ;
	
}



function parseCategories($cat)
{
	//av,je,dep,reg,nat,ma
	$val=array();
	
	($cat->av == 1 ) ?  $val[]="1"  : $val[]="0";
	($cat->je == 1 ) ?  $val[]="1"  : $val[]="0";
	($cat->dep == 1 ) ? $val[]="1"  : $val[]="0";
	($cat->reg == 1 ) ? $val[]="1"  : $val[]="0";
	($cat->nat == 1 ) ? $val[]="1"  : $val[]="0";
	($cat->ma == 1 ) ?  $val[]="1"  : $val[]="0";
	
	
	return $val;
	
}

function add($data) {
	
	global $dev,$mysqli;
	global $tcompetitions;

	
	
	$heure=$data->heure.":00:00";
	
	$cat =parseCategories($data->categories);
	$datetime = new DateTime($data->debut);
	$data->debut=$datetime->format("Y-m-d");
	
	$datetime = new DateTime($data->fin);
	$data->fin=$datetime->format("Y-m-d");
	
	$datetime = new DateTime($data->limite);
	$data->limite=$datetime->format("Y-m-d");
	
	//$values.=$cat;
    
	$set = "nom" ;
	$params[] = ucfirst( strtolower( $data->nom   )  );
	$start = "s";
	$inc = "?";
	
	$set .= ",lieu" ;
	$params[]= ucfirst( strtolower( $data->lieu  ) ) ;
	$start .= "s";
	$inc .= ",?";
			
	$set .= ",debut" ;
	$params[] = $data->debut;
	$start .= "s";
	$inc .= ",?";
			
	$set .= ",fin" ;
	$params[] = $data->fin;
	$start .= "s";
	$inc .= ",?";
			
	$set .= ",limite" ;
	$params[] = $data->limite;
	$start .= "s";
	$inc .= ",?";
			
	$set .= ",heure" ;
	$params[] = $data->heure.":00:00";
	$start .= "s";
	$inc .= ",?";
	
	$set .= ",bassin" ;
	$params[] = $data->bassin;
	$start .= "s";
	$inc .= ",?";
	
	$set .= ",type" ;
	$params[] = $data->type;
	$start .= "s";
	$inc .= ",?";
	
	$set .= ",entraineur" ;
	$params[] = $data->entraineur;
	$start .= "s";
	$inc .= ",?";
	
	
	$set .= ",av,je,dep,reg,nat,ma" ;
	$pc = parseCategories( $data->categories );
	$params = array_merge( $params , $pc ) ;
	$start .= "ssssss";
	$inc .= ",?,?,?,?,?,?";
	
	
	
	$set .= ",verif" ;
	$params[] = 0;
	$start .= "s";
	$inc .= ",?";
			
	
	
	if(  isset($data->lien ) &&  !empty($data->lien))
	{
		$set.=",lien";
		$params[] =  $data->lien ;
		$inc.=",?";
		$start.="s";
	}
	
	
	if(  isset($data->commentaires ) &&  !empty($data->commentaires) )
	{
		
		
		$set.=",commentaires";
		$params[] =  $data->commentaires  ;
		$inc.=",?";
		$start.="s";
	}
	
	
	
	
	if($data->type == "compet" )
	{
		$set.=",choixnages";
		$v="0";
		
		if( isset($data->choixnages) )
		{
			if($data->choixnages === true ) {$v="1";}
		}
		$params[] = $v;
		$inc.=",?";
		$start.="s";
	}
	
	if( $data->type == "stage"  )
	{
		$set.=",max";
		$params[]= $data->max ;
		$inc.=",?";
		$start.="s";
		
		
	}
	
	
	$set ="(".$set.")";
	$inc ="(".$inc.")";
	
	
	$query=" INSERT INTO  $tcompetitions  $set  VALUES  $inc ";
	
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
	
	return get($id);

	
}





function update($id ,$data) {
	
	global $dev,$mysqli;
	global $tcompetitions;
	
	$datetime = new DateTime($data->debut);
	$data->debut=$datetime->format("Y-m-d");
	
	$datetime = new DateTime($data->fin);
	$data->fin=$datetime->format("Y-m-d");
	
	$datetime = new DateTime($data->limite);
	$data->limite=$datetime->format("Y-m-d");
	
	//$values.=$cat;
	
	$set = "nom=?" ;
	$params[] = ucfirst( strtolower(  $data->nom  )  );
	$start = "s";
	
	
	$set .= ",lieu=?" ;
	$params[]= ucfirst( strtolower(  $data->lieu  ) );
	$start .= "s";
	
	
	$set .= ",debut=?" ;
	$params[] = $data->debut;
	$start .= "s";
	
	
	$set .= ",fin=?" ;
	$params[] = $data->fin;
	$start .= "s";
	
	
	$set .= ",limite=?" ;
	$params[] = $data->limite;
	$start .= "s";
	
	
	$set .= ",heure=?" ;
	$params[] = $data->heure.":00:00";
	$start .= "s";
	
	
	$set .= ",bassin=?" ;
	$params[] = $data->bassin;
	$start .= "s";
	
	
	$set .= ",type=?" ;
	$params[] = $data->type;
	$start .= "s";
	
	
	$set .= ",entraineur=?" ;
	$params[] = $data->entraineur;
	$start .= "s";
	
	
	
	$set .= ",av=?,je=?,dep=?,reg=?,nat=?,ma=?" ;
	$pc = parseCategories( $data->categories );
	$params = array_merge( $params , $pc ) ;
	$start .= "ssssss";
	
	
	
	
	$set .= ",verif=?" ;
	( $data->verif === true ) ? $params[] = 1 :  $params[] = 0 ;
	$start .= "s";
	
	
	
	
	if(  isset($data->lien ) &&  !empty($data->lien))
	{
		$set.=",lien=?";
		$params[] = $data->lien ;
		$start.="s";
		
	}
	
	
	if(  isset($data->commentaires ) &&  !empty($data->commentaires) )
	{
		
		
		$set.=",commentaires=?";
		$params[] = $data->commentaires ;
		$start.="s";
	}
	
	
	
	
	if($data->type == "compet" )
	{
		$set.=",choixnages=?";
		$v="0";
		
		if( isset($data->choixnages) )
		{
			if($data->choixnages === true ) {$v="1";}
		}
		$params[] = $v;
		$start.="s";
	}
	
	if( $data->type == "stage"  )
	{
		$set.=",max=?";
		$params[]= $data->max ;
		$start.="s";
		
		
	}
	
	
	

	
	
	$query = "UPDATE $tcompetitions SET $set  WHERE id = ?  ";
	$params[]=$id;
	$start.="s";
	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  ,...$params );
	
	
	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid connect";
		setError( $err );
		return;
	}
	
	
	$stmt->close();
	
	// setSuccess('modif valide');

	return get($id);
	
	
}




function delete($id)
{
	global $dev,$mysqli;
	global $tcompetitions;

	$query = "DELETE FROM $tcompetitions  WHERE id = ?  ";
	
	
	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param("i",$id);
	
	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request " .$query;
		setError(404 , $err );
		return ;
	}
	
	$stmt->close();
	$mysqli->close();

	$message = "Suppression valide";
	setSuccess($message);
	
}


function get( $id ) {
	
		
		global $dev,$mysqli;
		global $tcompetitions ;
	
	
		$query = "SELECT *  FROM $tcompetitions  WHERE id ='$id'  ORDER BY debut, fin ";
	
		$result = $mysqli->query($query) ;
		if (!$result) {
			($dev) ? $err=$mysqli->error : $err="invalid request";
			setError( $err );
			return ;
		}
	
		$r = $result->fetch_assoc() ;
		
		$r['nb'] = (int)  0 ;
		
		( $r['verif'] == '0' ) ? $r['verif'] = false : $r['verif'] = true ;
		( $r['choixnages'] == '0' ) ? $r['choixnages'] = false : $r['choixnages'] = true ;
		
		$r['categories']=setCategoriesArray( $r );
		
		$r['heure']=substr($r['heure'] , 0 ,2 );
		
		$datetime = new DateTime($r['debut'] .' 00:00:00');
		$r['debut']=$datetime->format(DateTime::ATOM);
		
		$now = new DateTime() ;
		$datetime = new DateTime($r['fin'] .' 00:00:00');
		( $datetime >= $now ) ?  $r['next'] = true  : $r['next'] = false ;
		
		$r['fin']=$datetime->format(DateTime::ATOM);
	
		$datetime = new DateTime($r['limite'] .' 00:00:00');
		$r['limite']=$datetime->format(DateTime::ATOM);
		
		$r['nom'] = utf8_encode( $r['nom'] ) ;
		$r['lieu'] = utf8_encode( $r['lieu'] ) ;

		$r['lien'] = utf8_encode( $r['lien'] ) ;
		$r['commentaires'] = utf8_encode( $r['commentaires'] ) ;



	    $mysqli->close();
		header("Content-type:application/json");
		$datas['datas']=$r;
		echo json_encode( $r );
	
}



function getEnt() {
	
	
	global $dev,$mysqli;
	global $tlicencies ;
	
	
	
	$query = "SELECT nom, prenom , email FROM $tlicencies WHERE entr ='1' ORDER BY nom , prenom  ";
	
	
	$result = $mysqli->query($query) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err );
		return ;
	}
	
	
	$rows = array();

	$t = array("value" => "-" , "nom" => "-" );
	$rows[] = $t ;		
	while($r = $result->fetch_assoc() ) {
		
		$t = array();
		$temail = explode(',' , $r['email']  ) ;
		foreach ($temail as $k ) {
			
			if( strlen($k) !== 0 ) {
				$t['value'] = $k ;
				break ;
			}
			
		}
		
		
		$t['nom'] = utf8_encode($r['nom']) ." ".utf8_encode($r['prenom']) ;
				
		$rows[] = $t ;		
		
	}
	echo json_encode( $rows );
}

function getCompetitions() {
	
	
	global $dev,$mysqli;
	global $tengagements ;
	global $tcompetitions ;
	
	
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
	
	
	
	
	$query = "SELECT *  FROM $tcompetitions ORDER BY debut, fin ";
	
	

	
	$result = $mysqli->query($query) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err );
		return ;
	}

	

	$rows = array();
	while($r = $result->fetch_assoc() ) {
	
		$id = $r['id'];
		( isset( $map[$id] ) ) ?  $r['nb']=$map[$id]:$r['nb']=0;
		
		( $r['verif'] == '0' ) ? $r['verif'] = false : $r['verif'] = true ;
		( $r['choixnages'] == '0' ) ? $r['choixnages'] = false : $r['choixnages'] = true ;
		
		$r['categories']=setCategoriesArray( $r );
		
		$r['heure']=substr($r['heure'] , 0 ,2 );
		
		$datetime = new DateTime($r['debut'] .' 00:00:00');
		$r['debut']=$datetime->format(DateTime::ATOM);
		$datetime = new DateTime($r['fin'] .' 00:00:00');
		$now =new DateTime("now");
		
		
		$r['next']=false;
		if(  $datetime  >= $now ) {
			$r['next']=true;
		}
		
		
		$r['fin']=$datetime->format(DateTime::ATOM);
		$datetime = new DateTime($r['limite'] .' 00:00:00');
		$r['limite']=$datetime->format(DateTime::ATOM);
		
		$r['nom'] = utf8_encode( $r['nom'] ) ;
		$r['lieu'] = utf8_encode( $r['lieu'] ) ;
		$r['lien'] = utf8_encode( $r['lien'] ) ;
		$r['commentaires'] = utf8_encode( $r['commentaires'] ) ;


		$e = json_encode( $r ) ;
		if ( $e != false  ) 	
		{
			$rows[] = $r  ;
		}
				

	}
		
	$mysqli->close();
	
	
	header("Content-type:application/json");
	
//	$datas['datas']=$rows;
	
	echo json_encode( $rows );

}




function setCategoriesArray( &$r ) {
	$res=array("av" => false ,"je" => false ,"dep" => false,"reg" => false,"nat" => false,"ma" => false );
	
	foreach ( $res as $k => $v )
	{
		if( $r[$k] == 1  ) $res[$k]=true;
		unset( $r[$k] );
		
		
	}
	
	
	
	return $res ;
}





?>
