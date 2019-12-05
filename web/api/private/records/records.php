<?php

$auth= array("admin","user");

if ( !isset($profile) && in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}


switch ($method) {


	case 'GET':
		if( isset($id) && $id === 'names' ) {
			getNames() ;
		} else if( isset($id) && $id === 'replace' ) {

			getReplace();
		}
		else if( isset($id) && $id === 'compet' ) {
			getCompet() ;
		} else if( isset($id) && $id === 'traite' ) {

			if( isset($_GET['other'] )  ) {
				$file= $_GET['other'] ;
				traiteFfnex($file);

			}

			
		}	
		else {
			get() ;
		}
		break;
	case 'POST':
		if( isset($id) && $id === 'upload' ) { 
			upload( $_FILES );
		} else {
			$data = json_decode(file_get_contents('php://input'));
			insert( $data );
		}
		break;
		
		
	case 'PUT':
		$data = json_decode(file_get_contents('php://input'));
		if( isset($id) && $id === 'replace' ) {
			replace( $data) ;
		}  else {
			update($data) ;
		}
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
		
		$day2 = $debut->format("Ymd");

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
	$extension = strrchr($file['file']['name'], '.');
	
	
	if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
	{
		$message = 'Vous devez uploader un fichier de type xml';
		setError( $message ,400);
		return;
	}
	if($taille>$taille_maxi)
	{
		$message= 'Le fichier est trop gros...';
		setError( $message);
		return;
	}

    /* Getting file name */
	$filename = $file['file']['tmp_name'];
	if ( !file_exists($filename)) {
		$message = "Erreur upload  fichier temporaire";
		setError( $message );
		return;
	} 


// mode developpement
// chmod -R o+rw upload

	/* Location */
	//$location =  "/var/www/html/upload/" .  $file["file"]["name"] ;

	$location =  $_SERVER["DOCUMENT_ROOT"] . "/upload/" .  $file["file"]["name"] ;

	/* Upload file */
	$ret = move_uploaded_file($file['file']['tmp_name'],$location );
	if( $ret ) {
		$message = "upload: OK";
		setSuccess( $message );
	} else {  
		$message = "Erreur upload ".$location ;
		setError( $message );
		return;
	}

	
	
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
/////////////////////////////////////////////////////////////////////////////////////////////////////
function getRecords( $bassin ) {
	
	global $trecords;
	global $dev,$mysqli;

	
	
	$query = "SELECT *  FROM ".$trecords." WHERE bassin = '$bassin' ORDER BY sexe,nage,age,nom,prenom ";


	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err ,404 );
		return ;
	}




$getrecords=array();
while( $row = $result->fetch_assoc() ) {

$nage=$row['nage'];
$distance=$row['distance'];
$bassin=$row['bassin'];
$age=$row['age'];
$sexe=$row['sexe'];
$temps=$row['temps'];

$nom=$row['nom'];
$tnom=preg_split("/ /",$nom);
if( count($tnom) == 2 ) {
	$nom=ucfirst($tnom[0])." ".ucfirst($tnom[1]);
}




$prenom=$row['prenom'];
$date=$row['date'];
$points=$row['points'];
if( $points == "-1"  ||  $points = "" ) {
$points="";	
}

$type=$row['type'];

/*
$end="";
if( $sexe=="MI"){
$end="_MI";	
}
*/


$key=$sexe."_".$nage."_".$distance."_".$age;
$getrecords[ $key ] ['temps']=  $temps ;
$getrecords[ $key ] ['nom']=  $nom ;
$getrecords[ $key ] ['prenom']=  $prenom ;
$getrecords[ $key ] ['date']=  $date ;
$getrecords[ $key ] ['points']=  $points ;
$getrecords[ $key ] ['type']=  $type ;

}



	
	$mysqli->close();
	return $getrecords ;

}

# include("./resultats_ffnex.php") ;
/////////////////////////////////////////////////////////////////////////////////////////////////////
function traiteFfnex( $filename) {

	

  $code_ecn="50503501000";
 //////////////////////////////////////////////////////////////////////
 
  
  $root=$_SERVER["DOCUMENT_ROOT"];
  $directory= $root."/upload/" ;
  $file=$directory.$filename.".xml";
  
  //////////////////////////////////////////////////////////////////////
  
  $exist=file_exists ( $file ) ;
  
  if( !$exist  ) {
  $message= "Le fichier est indisponible !" ;
  setError( $message ) ;
  return;		
  }
  $xml = simplexml_load_file( $file );
  
 

  
  $isecn=readClub( $xml , $code_ecn ) ;
  if( $isecn === false ) {
  $message="Il n'y a pas de nageurs de ECN Chartres de Bretagne" ;
  setError( $message ) ;
  return;	
 
  }



  $master=isMaster($xml);
  $codenages= getCodesNages() ;
  $compet=getCompetition( $xml );
  $year_compet=$compet["year"];
  $swimmers= getSwimmers( $xml ,$isecn,$year_compet ) ;




  $resultats = getResultats($xml, $isecn, $codenages, $swimmers, $master );

usort($resultats, function ($item2, $item1) {
	if ( $item2['nom'] !== $item1['nom'] ) {
		return $item2['nom'] <=> $item1['nom'];
	} else if ( $item2['prenom'] !== $item1['prenom'] ) {
		return $item2['prenom'] <=> $item1['prenom'];
	} else if ( $item2['sexe'] !== $item1['sexe'] ) {
			return $item2['sexe'] <=> $item1['sexe'];}
	else if ( $item2['nage'] !== $item1['nage'] ) {
		return $item2['nage'] <=> $item1['nage'];
	}  if ( $item2['distance'] !== $item1['distance'] ) {
		return $item2['distance'] <=> $item1['distance'];
	} else return $item2['distance'] <=> $item1['distance'];
   
});






  if( !isset( $compet['bassin']) ) {
	setError( "bassin not exist ...") ;
	return ;
	
	}

if( isset($resultats['error']) ) {

		echo json_encode( $resultats );
		return;
	}



  // on recupère $compet et $resultats
  // on récupère $compet , $resultats ...
  $getrecords =	getRecords( $compet['bassin']  ) ;
 
  $allperfs = array();
  

  foreach ( $resultats  as  $value  ) {

 	
	$sexe=$value["sexe"];
	$distance=$value["distance"];
	$nage=$value["nage"];
	$age=$value['age'];
	$time=$value["temps"];
	$nom=$value["nom"];
	$prenom=$value["prenom"];
	$points=$value["points"];
	
	$value['bassin'] = $compet['bassin'] ;
	$value['lieu'] = $compet['city'] ;
	$value['date'] = $compet['startdate'] ;
   
	
	$inter=$value["inter"];
	$refnage="-";
	if( $inter == "1"){ 
	    $refnage=$value["ref"]; 
		} else {
		$value['ref']="";	
		}

   // debut de la clé						
   $debk=$sexe."_".$nage."_".$distance;

   if ( empty($age) ) continue ;		
   if ( $age == '0'  ) continue ;		


//age masters
if( strpos($age,"C") !== false ) {
	$age=str_replace("C","",$age);
	$max=$age;
	$master=true;
}
// age relais masters
else if ( strpos($age,"R") !== false ) {
	$age=str_replace("R","",$age);
	$max=$age;
	$master=true;
   
}


else {
  $max=18;
  $master=false;
}

for  ($a=$age ; $a <=$max ; $a++   )

				  {
					  if( $master )   {   
						          $key=$debk."_C".$a;
								  $key2=$debk."_18";
								  $ta="C".$a; 
								  $tabkey=array();
								  $tabkey[$key2]=18;
								  $tabkey[$key]=$ta;
								  //les relais
					  if ( strpos($debk,"4x") !== false  ||  strpos($debk,"10x") !== false )
					     {
								   $key=$debk."_R".$a;$ta="R".$a; 
								   $tabkey=array();
								   $tabkey[$key]=$ta;
						 }			  
							 } else {  $key=$debk."_".$a; $ta=$a;
									   $tabkey=array();
									   $tabkey[$key]=$ta;
										}				 	
		  
		  foreach ($tabkey as $key => $ta  ) {
		   
		  $perfs =array(); $perfs['age'] = $ta ;$perfs['time'] = $time ;
		
		 
		
		  if( isset( $getrecords[$key] ) ) {
		  $rectime=$getrecords[$key]['temps'] ;
		
		  $perfs['rectime'] = $rectime ;
		  if ( $time < $rectime ) { 
			$perfs['type'] = 'perf' ; 
		  } else if( $time == $rectime ) {
			$perfs['type'] = 'eqperf' ;
		   } else {
			$perfs['type'] = 'noperf' ;
		   }
		    
		   } else {
			$perfs['rectime'] = "-1" ;
			$perfs['type'] = 'perf' ;
		
			  }	
	 
			}
			$value['perf'][] = $perfs ;   
			
		    }

				  $allperfs[]=$value;
  
}
$datas =  array();
$datas['compet'] = $compet ;
$datas['datas'] = $allperfs ;

echo json_encode($datas) ;

}
/////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////
function insert($data) {
	
	global $dev,$mysqli;
	global $trecords;
	

	$set = "nom" ;
	$params[] = ucfirst( strtolower( utf8_decode( $data->nom ) )  );
	$start = "s";
	$values = "?";

	$set .= ",prenom" ;
	$params[] = ucfirst( strtolower(  utf8_decode(  $data->prenom ) )  );
	$start .= "s";
	$values .= ",?";

	$set .= ",lieu" ;
	$params[] = ucfirst( strtolower( utf8_decode( $data->lieu ) )  );
	$start .= "s";
	$values .= ",?";

	$set .= ",points" ;
	$params[] = $data->points;
	$start .= "s";
	$values .= ",?";

	$set .= ",date" ;
	$params[] = $data->date;
	$start .= "s";
	$values .= ",?";

	$set .= ",temps" ;
	$params[] =   $data->temps ;
	$start .= "s";
	$values .= ",?";

	if ( ! isset($data->type)) {
		$data->type = "CLUB" ;
	}

	$set .= ",type" ;
	$params[] =   $data->type ;
	$start .= "s";
	$values .= ",?";

	$set .= ",nage" ;
	$params[] =   $data->nage ;
	$start .= "s";
	$values .= ",?";

	$set .= ",distance" ;
	$params[] =   $data->distance ;
	$start .= "s";
	$values .= ",?";

	$set .= ",bassin" ;
	$params[] =   $data->bassin ;
	$start .= "s";
	$values .= ",?";

	$set .= ",sexe" ;
	$params[] =   $data->sexe ;
	$start .= "s";
	$values .= ",?";

	$set .= ",age" ;
	$params[] =   $data->ageupdate ;
	$start .= "s";
	$values .= ",?";

	$query = "INSERT INTO $trecords ($set)  VALUES ($values) ";
	
	
	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  ,...$params );
	
	
	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid connect";
		setError( $err );
		return;
	}
	
	
	$stmt->close();
	
	setSuccess("insert ok");
	
	
	
}
/////////////////////////////////////////////////////////////////////////////////////////////////////
function update($data) {
	
	global $dev,$mysqli;
	global $trecords;
	

	$set = "nom=?" ;
	$params[] = ucfirst( strtolower( utf8_decode( $data->nom ) )  );
	$start = "s";

	$set .= ",prenom=?" ;
	$params[] = ucfirst( strtolower(  utf8_decode(  $data->prenom ) )  );
	$start .= "s";

	$set .= ",lieu=?" ;
	$params[] = ucfirst( strtolower( utf8_decode( $data->lieu ) )  );
	$start .= "s";

	$set .= ",points=?" ;
	$params[] = $data->points;
	$start .= "s";

	$set .= ",date=?" ;
	$params[] = $data->date;
	$start .= "s";

	$set .= ",temps=?" ;
	$params[] =   $data->temps ;
	$start .= "s";
	if ( ! isset($data->type)) {
		$data->type = "CLUB" ;
	}

	$set .= ",type=?" ;
	$params[] =   $data->type ;
	$start .= "s";



	$query = "UPDATE $trecords SET $set  WHERE nage = ? AND distance = ? AND bassin = ? AND sexe = ? AND age = ? ";
	$params[] = $data->nage ;
	$params[] = $data->distance ;
	$params[] = $data->bassin ;
	$params[] = $data->sexe ;
	$params[] = $data->ageupdate ;
	$start .= "sssss";
	
	
	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  ,...$params );
	
	
	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid connect";
		setError( $err );
		return;
	}
	
	
	$stmt->close();
	
	setSuccess("update ok");
	
	
	
}
///////////////////////////////////////////////////////////////////////////////
function getReplace() {

	global $trecords;
	global $dev,$mysqli;

	$rows = array();

	$query  = "SELECT DISTINCT nom  FROM ".$trecords  ." ORDER BY nom" ;
	
	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err ,404 );
		return ;
	}
	

	while($r = $result->fetch_assoc() ) {

		$v = utf8_encode( $r['nom'] );
	
		if ( strlen( $v ) === 0 ) {
			continue ;
		}
		
		if ( $v != false  )
		{
			if ( strlen( $v ) === 0 ) {
				continue ;
			}
			$rows['nom'][] = $v  ;
		}
	}
#################################################################
$query  = "SELECT DISTINCT prenom FROM ".$trecords ." ORDER BY prenom" ;
	
	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err ,404 );
		return ;
	}
	

	while($r = $result->fetch_assoc() ) {

		$v =  utf8_encode( $r['prenom'] ) ;
	
		if ( $v != false  )
		{
			if ( strlen( $v ) === 0 ) {
				continue ;
			}
			$rows['prenom'][] = $v  ;
		}
	}

#################################################################
$query  = "SELECT DISTINCT lieu FROM ".$trecords ." ORDER BY lieu";
	
	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err ,404 );
		return ;
	}
	

	while($r = $result->fetch_assoc() ) {

		$v =  utf8_encode( $r['lieu'] )  ;
	
		if ( strlen( $v ) === 0 ) {
			continue ;
		}
		
		if ( $v != false  )
		{
			if ( strlen( $v ) === 0 ) {
				continue ;
			}
			$rows['lieu'][] = $v  ;
		}
	}




	$mysqli->close();
	echo json_encode($rows);

}
///////////////////////////////////////////////////////////////////////////
function replace($data) {
	
	global $dev,$mysqli;
	global $trecords;
	
	$type = $data->replace ;
	$value = $data->value ;
	$old = $data->oldvalue ;


	if( $type === "nom"  ) {
		$set = " nom = ? " ;
		$params[] = ucfirst( strtolower( utf8_decode( $value ) )  );
		$params[] = ucfirst( strtolower( utf8_decode( $old ) )  );
		$start = "ss";
		$query = "UPDATE $trecords SET $set WHERE nom = ? ";
	} else if ( $type === "prenom" ) {
		$set = " prenom = ? " ;
		$params[] = ucfirst( strtolower( utf8_decode( $value ) )  );
		$params[] = ucfirst( strtolower( utf8_decode( $old ) )  );
		$start = "ss";
		$query = "UPDATE $trecords SET $set WHERE prenom = ? ";

	} else if ( $type === "lieu" ) {
		$set = " lieu = ? " ;
		$params[] = ucfirst( strtolower( utf8_decode( $value ) )  );
		$params[] = ucfirst( strtolower( utf8_decode( $old ) )  );
		$start = "ss";
		$query = "UPDATE $trecords SET $set WHERE lieu = ? ";
	} else {
		$err="invalid data replace";
		setError( $err );
		return;
	}

	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  ,...$params );
	
	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid connect";
		setError( $err );
		return;
	}
	
	
	$stmt->close();
	
	setSuccess("replace ok");
		
	
}




/////////////////////////////////////////////////////////////////////////////////////
function yeartoage($year , $year_compet) {
	
	$y=date("Y");
	// pout les competitions Y-1
	$delta=$y-$year_compet;
	
	$age=$y-$year-$delta;	
	
	
	if( $age < 18 )	return $age;
	if( $age >= 18 && $age <25 ) { return 18;}
	if( $age >= 25 && $age <30 ) return "C1";
	if( $age >= 30 && $age <35 ) return "C2";
	if( $age >= 35 && $age <40 ) return "C3";
	if( $age >= 40 && $age <45 ) return "C4";
	if( $age >= 45 && $age <50 ) return "C5";
	if( $age >= 50 && $age <55 ) return "C6";
	if( $age >= 55 && $age <60 ) return "C7";
	if( $age >= 60 && $age <65 ) return "C8";
	if( $age >= 65 && $age <70 ) return "C9";
	if( $age >= 70 && $age <75 ) return "C10";
	if( $age >= 75 && $age <80 ) return "C11";
	if( $age >= 80 && $age <85 ) return "C12"; 
	if( $age >= 85 && $age <90 ) return "C13";
	if( $age >= 90 && $age <95 ) return "C14";
	if( $age >= 95 ) return "C15";	
		
	}
	/////////////////////////////////////////////////////////////////////////
	function isRelais($code) {
	$relais=array("08","47","43","44","49","09","45","39","48","9","46","58","97","93","94","99","59","95","89","98","96","49","87","37","84")	;
	return in_array($code, $relais);
	}
	/////////////////////////////////////////////  � impl�menter
	function isRelaisMixte($code) {
	$relais=array("87","37","84")	;
	}
	/////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////// 
	function readClub( $xml , $code_ecn ) {
	// recherche le code ecn dans le document 
	$xpath="//CLUB[@code='$code_ecn']";
	
	$result = $xml->xpath( $xpath );
	if ( $result ) return  (string) $result[0]['id'] ;
	else  return false ;
	}
	///////////////////////////////////////////////////////////////////////
	function isMaster( $xml ) {
	//discipline id == 3  code masters
	$xpath="//MEETS/MEET[@disciplineid='3']";
	$result = $xml->xpath( $xpath );
	if ( $result ) return true;
	else return false; 
	}
	//////////////////////////////////////////////////////////////////////
	function getCompetition( $xml ) {
	$compet=array();
	
	$xpath="//POOL";
	$result = $xml->xpath( $xpath );
	$compet['bassin']=(string)$result[0]['size'];
	
	$xpath="//MEETS/MEET";
	$result = $xml->xpath( $xpath );
	foreach ($result  as $meet ) {
		$compet['name']=(string)$meet['name'];
		$compet['city']=(string)$meet['city'];
		$compet['startdate']=(string)$meet['startdate'];
		$d=(string)$meet['startdate'];
		$compet['year']=substr($d ,0 ,4);
	}
	return $compet;
	}
	/////////////////////////////////////////////////////////////
	function getCodesRelais( ) {
	$nages=array();
	$codes=array( "111" => "DOS","121"=> "BRA","131"=> "PAP","48"=> "4N","47"=> "NL","43"=> "NL","44"=> "NL","46"=> "4N","9"=>"NL","87"=>"NL","37"=>"4N","84"=>"NL");	
	$dist=array(  "111" => "4x50","121"=> "4x50","131"=>"4x50","48"=> "4x50","47"=> "4x50","43"=> "4x100","44"=> "4x200","46"=> "4x100","9"=>"10x50","87"=>"4x50","37"=>"4x50","84"=>"10x50");	
		foreach ( $codes  as $k =>$v ) {
			
			//cas particuliers : relais mixte
			if( $k == "87" || $k == "37" || $k== "84" ) {
			$tnage["nage"]=$v;
			$tnage["sexe"]="MI";
			$tnage["distance"]=$dist[$k];
		
			
			$nages[$k]=$tnage;	
	
			continue;	
			}
			
			$tnage["nage"]=$v;
			$tnage["sexe"]="F";
			$tnage["distance"]=$dist[$k];
			
			$nages[$k]=$tnage;
			
			$tnageh["nage"]=$v;
			$tnageh["sexe"]="H";
			$tnageh["distance"]=$dist[$k];
			
			$kh=$k+50;
			$nages[$kh]=$tnageh;
										}
			return $nages ;
			
		}
	///////////////////////////////////////////////////////////////////
	function getCodesNages( ) {
		$nages=array();
	$codes=array( "100" => "NL","1"=> "NL","2"=> "NL","3"=> "NL","4"=> "NL","5"=> "NL","6"=> "NL","7"=> "NL","110"=> "DOS","11"=> "DOS","12"=> "DOS","13"=> "DOS","120"=>"BRA","21"=>"BRA","22"=>"BRA","23"=>"BRA","130"=>"PAP","31"=>"PAP","32"=>"PAP","33"=>"PAP","40"=>"4N","41"=>"4N","42"=>"4N"  );	
	$dist=array( "100" => "25","1"=> "50","2"=> "100","3"=> "200","4"=> "400","5"=> "800","6"=> "1500","7"=> "1000","110"=> "25","11"=> "50","12"=> "100","13"=> "200","120"=>"25","21"=>"50","22"=>"100","23"=>"200","130"=>"25","31"=>"50","32"=>"100","33"=>"200","40"=>"100","41"=>"200","42"=>"400"  );	
	
		foreach ( $codes  as $k =>$v ) {
			$tnage["nage"]=$v;
			$tnage["sexe"]="F";
			$tnage["distance"]=$dist[$k];
			$nages[$k]=$tnage;
			$tnageh["nage"]=$v;
			$tnageh["sexe"]="H";
			$tnageh["distance"]=$dist[$k];
			
			$kh=$k+50;
			$nages[$kh]=$tnageh;
	
			// cas des nages mixtes	
			$tnagem["nage"]=$v;
			$tnagem["sexe"]="M";
			$tnagem["distance"]=$dist[$k];
			
			$km=$k+200;
			$nages[$km]=$tnagem;
										}
			return $nages ;
			
		}
	
	//////////////////////////////////////////////////////////////////////////
	function isvide( $v ) {
	return (trim($v) !== "" );
	}
	////////////////////////////////////////////////////////////////////////
	function getSwimmers($xml , $clubid , $year_compet) {
		
	$swimmers=array();	
	$xpath="//SWIMMERS/SWIMMER[@clubid='$clubid']";
	
	$result = $xml->xpath( $xpath );
	
	
			foreach( $result  as $swimmer ) {
				
				$id=(string)$swimmer['id'];
				$name=(string)$swimmer['lastname'];
				$firstname=(string)$swimmer['firstname'];
				$gender=(string)$swimmer['gender'];
				$birthdate=(string)$swimmer['birthdate'];
				$b = explode("-", $birthdate);
				$age=yeartoage($b[0] , $year_compet);
				$tswim['nom']=$name;
				$tswim['prenom']=$firstname;
			
				if( $gender == 'M' ) $gender='H';
				$tswim['sexe']=$gender;
				$tswim['age']=$age;
				
				  $swimmers[$id]=$tswim;
		
					}
	
		return $swimmers;
		
	}
	/////////////////////////////////////////////////////////////////////////
	function traite4Nages( $res , $nageurs ,$d) {
	$r=array();
	// on tratite que le 200 et 400 4 nages pour les temps intermediaires
	if( $d == 200 ) {
	$dnage=array("50");	
	}	
	else if( $d == 400 ){	
	$dnage=array("50","100");
	}
	else {
	return $r;	
	}
	
	
		foreach ($res->xpath( "SPLITS/SPLIT" )  as $split ) {
	
					
				  $time=(string)$split['swimtime'];
				  $dist=(string)$split['distance'];
	
				 
				///  if( $dist == $nage['distance'] ) continue;
				  if( in_array( $dist , $dnage  ) )
				  {
					  $nageurs['points']="-1";
					  $nageurs['temps']=$time;
					  $nageurs['inter']=1;
					  $nageurs['distance']=$dist ;
					  $nageurs['nage']="PAP";
					   
					  $r[]=$nageurs;
				  }
				   
				   
				   
				}
	
			  return $r;  
				
	}
	/////////////////////////////////////////////////////////////////////
	function traiteNage( $res , $swimmers , $codenages , &$resultats ) {
		
		
	$dnage=array("50","100","200","400","800");
	
	
				$temps=(string)$res['swimtime'];
				$points=(string)$res['points'];
				$race=(string)$res['raceid'];
		
	  foreach ( $res->SOLO  as $solo ) {
				 $id =(string)$solo['swimmerid'];
	
				  $nageurs =  $swimmers[$id];
				  
				 //  if ( !isset( $codenages[$race] ) ) continue;
				  $nage    =  $codenages[$race];                   
	
				  $d=$nage['distance'];
				  
				  $nageurs['nage']=$nage['nage'];
				  $nageurs['distance']=$nage['distance'];
				  $nageurs['points']=$points;
				  $nageurs['temps']=$temps;
				  $nageurs['inter']=0;
				  $nageurs['ref']=$nage['nage']."(".$nage['distance'].")";
				  $resultats[]=$nageurs;
				
			  
				if( $nage['nage'] == "4N") {
	
	
					// voir le traitement ....
				 $r=traite4Nages($res,$nageurs,$d);
				 
				 foreach($r as $v ) {
					 
					 $resultats[]=$v;
				 }
				 
			   
				   continue; 
	
				
				}
				  
				
				
				foreach ($res->xpath( "SPLITS/SPLIT" )  as $split ) {
	
					
				  $time=(string)$split['swimtime'];
				  $dist=(string)$split['distance'];
	
				  
				  // temps final....
				  if( $dist == $nage['distance'] ) continue;
				  
				
				 
				
					
				  if( in_array( $dist , $dnage  ) )
				  {
					  $nageurs['points']="-1";
					  $nageurs['temps']=$time;
					  $nageurs['inter']=1;
					  $nageurs['distance']=$dist ;
					  
					  $resultats[]=$nageurs;
					  
				  }
				   
				}
				 }
				 
	}
	
	/////////////////////////////////////////////////////////////////////////////
	function getAgeRelaisMasters($key){
	
	$rage=array("100-119"=>"R1","120-159"=>"R2","160-199"=>"R3" ,"200-239"=>"R4",
	"240-279"=>"R5","280-319"=>"R6","320-359"=>"R7","360-999"=>"R8");
	
	if ( isset( $rage[ $key ] ) ) {
	return 	$rage[ $key ] ;
	}
	else return false;
	}
	////////////////////////////////////////////////////////////////////////////
	function getAgeGroups($res) {
	
		$masters=isMaster($res);
		
		$ages=array();	
			foreach($res->xpath( "//AGEGROUPS/AGEGROUP" ) as $age ) {
				
						
				$id=(string)$age['id'];
				
			
				
				$min=(string)$age['agemin'];
				$max=(string)$age['agemax'];
				if( !$masters ) {
					if( $min == "0" )  $min="7";
					if( $max == "999" ) $max="18";
				}	
			
				$ages[$id]['min']=$min;
				$ages[$id]['max']=$max;
				$ages[$id]['code']=$max;
				
			}
	
		
		
			return $ages;
	
	}
	////////////////////////////////////////////////////////////////////////
	function traiteRelaisMasters($res , $swimmers , $agegroup , &$resultats  ) {
	
	
	$coderelais=getCodesRelais();
	
	//87 et 37 et 84 relais mixte
	$relay_rec=array("47","97","87","37","39","89","84");
	//("43","93","44","94","46","96","9","59","87","37");
	
	// pour les record des relais :
	// 4x100;4x200;10x100	NL
	// 4x100 4N
	
	//pour les autres relais on ne prend que la perf du premier relayeur	
		
	//get categorie age
		   $e=$res->xpath( "RELAY/RELAYPOSITIONS" );
			if( empty($e)  ) {  return false  ;}
			
			$temps=(string)$res['swimtime'];
			$points=(string)$res['points'];
			$race=(string)$res['raceid'];
			$team=(string)$res['team'];    
			$age_group= (string)$res['agegroupid'];  
			$min=$agegroup[$age_group]['min'];
			$max=$agegroup[$age_group]['max'];
			$key=$min."-".$max;
			 $R_masters=getAgeRelaisMasters($key);	
			   
			 
			 if( $R_masters == false ) {  return false  ;}
				 
			if( !isset ($coderelais[$race]) ) {  return false  ;}
				$nage    =  $coderelais[$race];        
		
		
				$t=array("4x","10x");
				$max_dist=str_replace( $t,"" ,$nage['distance']);
				
				// revoir traitement de relais age....		
				  
				  $relais['age']= $R_masters;
				  //print_r( $nage );
				  $relais['nom']="R$team";
				  $relais['prenom']=$nage['nage']."(".$nage['distance'].")";
				  $relais['nage']=$nage['nage'];
				  $relais['distance']=$nage['distance'];
				  $relais['sexe']=$nage['sexe'];
				  $relais['points']=$points;
				  $relais['temps']=$temps;
				  $relais['inter']=0;
				  $relais['ref']=$nage['nage']."(".$nage['distance'].")";
				
				  //10*500m
				if( $race != "9"  && $race!= "59" )  {
				if( in_array($race , $relay_rec  )  )        $resultats[]=$relais;
				
				}
				  
					$relayeur_1=array();
					$i=0;
				
				  $nage    =  $coderelais[$race];                   
				  $nageurs['nage']=$nage['nage'];
				  $nageurs['distance']=$nage['distance'];
	
			foreach( $res->xpath( "RELAY/RELAYPOSITIONS" ) as $relay ) {
		
			 $id = (string) $relay->RELAYPOSITION['swimmerid'];
			 $relayeur_1=$swimmers[$id];
			   break;
			  } 
		
		  
			  foreach( $res->xpath( "SPLITS/SPLIT" ) as $split  ) {
			  
				  // on prend le 1er chrono ........
						  $time=(string)$split['swimtime'];
				  $dist=(string)$split['distance'];
	
						  
				if( $dist == "150" ) continue;	  
				  
				  $relayeur_1['temps']=$time;
				  $relayeur_1['distance']=$dist;
				  $relayeur_1['points']="0";
	
				if( $nage['nage'] == "4N" )  $relayeur_1['nage']="DOS";
				else  $relayeur_1['nage']=$nage['nage'];
				  
							  $relayeur_1['inter']=1;
				  $relayeur_1['ref']=$nage['nage']."(".$nage['distance'].")";
				  $resultats[]=$relayeur_1;
				  
			
				  
				 if( $dist >= $max_dist )  break;
				  
							  } 
		  
				
					  return true;
	}
	/////////////////////////////////////////////////////////////////////////////
	function traiteRelais($res , $swimmers , $agegroup , &$resultats  ) {
	
	
	
	$coderelais=getCodesRelais();
	
	//87 et 37 relais mixte
	$relay_rec=array("43","93","44","94","46","96","9","59","87","37","84");
	
	
	// pour les record des relais :
	// 4x100;4x200;10x100	NL
	// 4x100 4N
	
	//pour les autres relais on ne prend que la perf du premier relayeur	
		
	//get categorie age
	
	
	
	$e=$res->xpath( "RELAY/RELAYPOSITIONS" );
	
	
	if( empty($e)  ) {  return false  ;}
		
		$temps=(string)$res['swimtime'];
		$points=(string)$res['points'];
		$race=(string)$res['raceid'];
		$team=(string)$res['team'];    
		//$age_group= (string)$res['agegroupid'];  
	
		$age=getAgeRelais( $e , $swimmers );
		
			if( !isset ($coderelais[$race]) ) {  return false  ;}
			$nage    =  $coderelais[$race];        
		
		
				$t=array("4x","10x");
				$max_dist=str_replace( $t,"" ,$nage['distance']);
				
		// revoir traitement de relais age....		
				  
				  $relais['age']= $age;
				  //print_r( $nage );
				  $relais['nom']="Relais";
				  $relais['prenom']=$nage['nage']."(".$nage['distance'].")";
				  $relais['nage']=$nage['nage'];
				  $relais['distance']=$nage['distance'];
				  $relais['sexe']=$nage['sexe'];
				  $relais['points']=$points;
				  $relais['temps']=$temps;
				  $relais['inter']=0;
				  $relais['ref']=$nage['nage']."(".$nage['distance'].")";
			
				  //10*500m
				if( $race != "9"  && $race!= "59" )  {
				if( in_array($race , $relay_rec  )  )        $resultats[]=$relais;
				
				
				}
				  
	$relayeur_1=array();
	$i=0;
				
				  $nage    =  $coderelais[$race];                   
				  $nageurs['nage']=$nage['nage'];
				  $nageurs['distance']=$nage['distance'];
	
	foreach( $res->xpath( "RELAY/RELAYPOSITIONS" ) as $relay ) {
		
			 $id = (string) $relay->RELAYPOSITION['swimmerid'];
			 $relayeur_1=$swimmers[$id];
			   break;
		  } 
		
		  
		  foreach( $res->xpath( "SPLITS/SPLIT" ) as $split  ) {
			  
			// on prend le 1er chrono ........
					$time=(string)$split['swimtime'];
				  $dist=(string)$split['distance'];
	
				
				  
			if( $dist == "150" ) continue;	  
				  
				  $relayeur_1['temps']=$time;
				  $relayeur_1['distance']=$dist;
				  $relayeur_1['points']="0";
	
				if( $nage['nage'] == "4N" )  $relayeur_1['nage']="DOS";
				else  $relayeur_1['nage']=$nage['nage'];
				  
				 //$relayeur_1['temps']=$temps;
				  $relayeur_1['inter']=1;
				  $relayeur_1['ref']=$nage['nage']."(".$nage['distance'].")";
				  $resultats[]=$relayeur_1;
				  
				
				
			  if( $dist >= $max_dist )  break;
				  
			  
			  
		  } 
		  
		  
		  return true;
	}
	
	//////////////////////////////////////////////////////////////////////////
	function getAgeRelais( $e , $swimmers ){
	$age=0;	
	foreach( $e as $relay ) {
			foreach ( $relay as $elm ){
			 $id = (string) $elm['swimmerid'];
			 $swimmer=$swimmers[$id];
			  if ( $age < $swimmer['age']  ) {
				 $age=$swimmer['age'];
			 }
		
		  }	
		  
	}
		  
		return $age;
	}
	
	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////
	
	
	function getResultats( $xml, $clubid, $codenages, $swimmers, $masters ) {
	
	$resultats = array();	
	
	$agegroup=getAgeGroups( $xml );
	
	$xpath="//RESULTS/RESULT[@clubid='$clubid'][@disqualificationid='']";
	$result = $xml->xpath( $xpath );	
	
			  foreach( $result  as $res ) {
			
					if( $masters ) {
						$code=traiteRelaisMasters($res , $swimmers , $agegroup , $resultats );
					} else {
						$code= traiteRelais($res , $swimmers , $agegroup ,$resultats  );	
					}
			
					if( $code === false ) {
						traiteNage($res , $swimmers , $codenages , $resultats  );
					}
	
			}
	
	
	
	return $resultats ;
	
	
	}
	
?>