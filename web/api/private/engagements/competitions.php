<?php




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
		getCompetitions() ;
		break;

	

	case 'DELETE':
		//deleteCompetition($_GET );
		break;



	default:
		echo "error" ;
		break;

}








function parseCategories($cat)
{
	//av,je,dep,reg,nat,ma
	$val="";
	
	($cat->av == 1 ) ?  $val.="'1'"  : $val.="'0'";
	($cat->je == 1 ) ?  $val.=",'1'"  : $val.=",'0'";
	($cat->dep == 1 ) ? $val.=",'1'"  : $val.=",'0'";
	($cat->reg == 1 ) ? $val.=",'1'"  : $val.=",'0'";
	($cat->nat == 1 ) ? $val.=",'1'"  : $val.=",'0'";
	($cat->ma == 1 ) ?  $val.=",'1'"  : $val.=",'0'";
	
	
	return $val;
	
}







function add($data) {





	global $host, $base, $base_user, $base_passwd ;

	$db = mysql_connect($host, $base_user, $base_passwd)
	or die("Connection Error: " . mysql_error());

	mysql_select_db($base) or die("Error connecting to db.");

	$response=array("success" => true, "message"  => "Modification effectuee") ;
	
	
	
	$heure=$data->heure.":00:00";

	$cat =parseCategories($data->categories);
	
	
	$set="(nom,lieu,debut,fin,limite,heure,bassin,type,entraineur,av,je,dep,reg,nat,ma" ;
	$values="('$data->nom','$data->lieu','$data->debut','$data->fin','$data->limite','$heure','$data->bassin','$data->type','$data->entraineur',$cat ";



	if(  isset($data->lien ) )
	{
		$set.=",lien";
		$values.=",'$data->lien'";
	}


	if(  isset($data->commentaires ) &&  !empty($data->commentaires) )
	{
		
		
		$set.=",commentaires";
		$values.=",'$data->commentaires'";
	}
	
	

	
	if($data->type == "compet" )
	{
		$set.=",choixnages";
		if($data->choixnages === true ) {$values.=",'1'";}
		else {$values.=",'0'";}
		
	}
	
	if( $data->type == "stage"  )
	{
		$set.=",max";
	    $values.=",'$data->max'";
	    
	    if( $data->verif === true )
	    {
	    	$set.=",verif";
	    	$values.=",'1'";
	    	
	    }
	    
	    
		
	}
	

	$set.=") ";
	$values.=") ";
	$query=" INSERT INTO  competitions  $set  VALUES  $values ";




	$result = mysql_query( $query ) ;
	if (!$result) {
		http_response_code(404);
		$response["success"]= false ;
		$response["message"]="invalid request ".$query;
	}




	header('Content-Type: application/json');

	echo json_encode( $response );





}








function update($id ,$data) {
	
	
	
	
	
	global $host, $base, $base_user, $base_passwd ;
	
	$db = mysql_connect($host, $base_user, $base_passwd)
	or die("Connection Error: " . mysql_error());
	
	mysql_select_db($base) or die("Error connecting to db.");
	
	$response=array("success" => true, "message"  => "Modification effectuee") ;
	
	$cat="";
	foreach ( (array)$data->categories  as  $k => $v )
	{
	( $v  ) ? $v="'1'" :$v="'0'"; 
		
	    if(!empty($cat) )  $cat.=",";
		$cat.=$k."=".$v;
	
	}
	
	
	$set=" SET ";
	$set.="nom= '$data->nom'";
	$set.=",lieu= '$data->lieu'";

	$set.=",bassin= '$data->bassin'";
	$set.=",type= '$data->type'";
	
	$set.=",debut= '$data->debut'";
	$set.=",fin= '$data->fin'";
	$set.=",limite= '$data->limite' ";
	
	$set.=",heure ='$data->heure:00:00'";
	$set.=",".$cat;
	
	
	if( isset($data->verif ) )
	{
		if ( $data->verif == true ) 
			{
				$set.=",verif ='1'";
			}
		else
		{ 
			$set.=",verif ='0'";
		}
	}
	
	
	if($data->type == "compet" )
	{
		if($data->choixnages === true ) {$set.=",choixnages='1'";}
		else {$set.=",choixnages='0'";}
	
	}
	
	if( $data->type == "stage"  )
	{
		$set.=",max='$data->max'";
	}
	
	if( isset($data->commentaires)  )
	{
		$set.=",commentaires='$data->commentaires'";
	}

	$set.=",entraineur='$data->entraineur' ";
	

	
	$set.=" WHERE id = $id " ;
	
	$query = "UPDATE  competitions  $set  ";
	
		
	
	$result = mysql_query( $query ) ;
	if (!$result) {
		http_response_code(404);
		$response["success"]= false ;
		$response["message"]="invalid request ".$query ;
		//return 	$error ;
	}
	
	
	
	
	header('Content-Type: application/json');
	
	echo json_encode( $response );

	
	
	
	
}







function getCompetitions($id=false) {
	global $error ;
	global $host, $base, $base_user, $base_passwd ;
	$db = mysql_connect($host, $base_user, $base_passwd)
	or die("Connection Error: " . mysql_error());

	mysql_select_db($base) or die("Error connecting to db.");

	( $id === false )  ?  $where=""  :  $where=" WHERE id = $id " ; 
	
	$query = "SELECT *  FROM competitions  $where  ORDER BY id, debut, fin ";
	
	
	$result = mysql_query( $query ) ;
	if (!$result) {
		http_response_code(404);
		
		$error["message"]="invalid request";
		return 	$error ;
	}

	

	$rows = array();
	while($r = mysql_fetch_assoc($result)) {
		
		$r['commentaires']=utf8_encode($r['commentaires'] );
		
		
		$r['categories']=setCategoriesArray( $r );
		
		$r['heure']=substr($r['heure'] , 0 ,2 );
		
		$datetime = new DateTime($r['debut'] .' 00:00:00');
		$r['debut']=$datetime->format(DateTime::ATOM);
		
		$datetime = new DateTime($r['fin'] .' 00:00:00');
		$r['fin']=$datetime->format(DateTime::ATOM);
				
		$datetime = new DateTime($r['limite'] .' 00:00:00');
		$r['limite']=$datetime->format(DateTime::ATOM);
		
		
		$e = json_encode( $r ) ;
		if ( $e != false  ) 	
		{
			$rows[] = $r  ;
		}
		else {
			
			echo "\n\n**= ".$r['id']." == ".$r['nom']. " = ***";		
		}
		
		

	}
		


	echo   json_encode($rows);

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



function validationParams( $data, $get  ) {

	$response=array("success" => false, "message"  => "invalid data parameters") ;


	if ( !$data )
	{

		header('Content-Type: application/json');
		echo json_encode( $response );
		return false ;
	}


	if ( $get !== false   && !isset($get['id'] ) )
	{

		header('Content-Type: application/json');
		echo json_encode( $response );
		return false ;
	}


	if ( !validateObject( $data)  )
	{
		header('Content-Type: application/json');
		$response['message'] ="invalid object parameters " ;
		echo json_encode( $response );
		return false ;


	}




	return true ;
}



function validateObject($json) {

     
if( !isset($json->nom )     ) return false ;
if( !isset($json->lieu )    ) return false ;
if( !isset($json->categories )    ) return false ;
if( !isset($json->bassin )    ) return false ;
if( !isset($json->type )    ) return false ;

if( !isset($json->debut )    ) return false ;
if( !isset($json->fin )    ) return false ;
if( !isset($json->limite )    ) return false ;
if( !isset($json->heure )    ) return false ;
if( !isset($json->entraineur )    ) return false ;


if( ($json->type == "compet" )  &&   !isset($json->choixnages)  ) return false ;
if( ($json->type == "stage" ) ) 
{
	if ( !isset($json->max) )  return false ;
	if ( $json->max <= 0 ) return false ;
 	
}


return true ;	
	
}




function saveCompetitions() {
	
	
	
	
	
	
}



?>
