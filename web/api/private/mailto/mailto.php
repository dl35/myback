<?php
include 'config.mailto.php';


$auth= array("admin","user","ent");

if ( !isset($profile) && in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}


switch ($method) {


	case 'POST':
		$data = json_decode(file_get_contents('php://input'));
		$v= validationParams( $data ) ;
		if( !$v ){
			setError("invalid parameters");
			return;
		}
		mailto($data) ;
		break;

	case 'GET':
		getDatas() ;
		break;


	default:
		setError("invalid routes");
		break;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function mailto($data) {
	global $dev,$mysqli;
	
	    $message  ="<html><head>";
	    $message .="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head><body>";
		$message .=$data->body;
	    $message .="</body></html>";       

		$body=$message;

		$subject=$data->subject;
		$from=$data->from;

		if(  $data->mode === 'i'  ) {
			// mode inscription .....
			$email = $data->email ;

		} else 	if( $data->mode === 'l' || $data->mode === 'g' ) {
			
			// mode licencies .....
			$list=$data->dests ;

		} else if( $data->mode === 'c'   ) {
			
			// mode competitions .....
		$cond  ="  id_competitions='$data->compet' ";
		$cond .=" AND engage_date.id_engage = engagements.id ";
		$cond .=" AND ( ";
		$presence="";
		foreach ( $data->choix as $choix ) {
			
			if( strtolower( $choix)  === 'ok') {
				if( !empty($presence) ) $presence.="OR" ;
				$presence.=" engage_date.presence='oui'  " ;
			} else if ( strtolower( $choix)  === 'ko') {
				if( !empty($presence) ) $presence.="OR" ;
				$presence.=" engage_date.presence='non'  " ;
			} else {
				if( !empty($presence) ) $presence.="OR" ;
				$presence.=" engage_date.presence='at'  " ;
			}
			
			
		}
		
		
		if( substr($presence, 0, 2) == "OR" )	{
			$presence=substr($presence,2 ,strlen($presence)) ;
		}
		
		$presence.=" )";
		
		$cond.=$presence;
		$list=getLicenciesFromCompetition( $cond ) ;
		
	} 	
	
	$map = array();
	if( isset($email) ) {
		$map[]=$email;

	} else {
	
		if( !empty($list) ) {
			if( $data->mode === 'g' ) {
			   $map =  getLicenciesGroup( $list );
			} else {
				$map = getAllLicencies( $list ) ;
			}
			
		}
	}

	
	$nb=0;
	foreach ($map as $key => $value )
	{
		$to=$value;
		$success=sendMail($to,$from,$subject,$body);
		if ( $success) $nb++ ;
		
	}
	
	$message=array();
	$message['success']=true;
	$message['message']= $nb." messages envoyés"  ;
	
	echo json_encode( $message );
	
	
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function sendMail($to,$from,$subject,$body) {
	global $dev,$dev_email;
	
	

	$body .="<br>";
	$body .="[from mailto]";
	
	$subject="[ Club de Natation ] ".$subject;

	
	$headers = "MIME-Version: 1.0\n";
	$headers .= "X-Sender: <www.ecnatation.org>\n";
	$headers .= "X-Mailer: PHP\n";
	$headers .= "X-auth-smtp-user: webmaster@ecnatation.org \n";
	$headers .= "X-abuse-contact: webmaster@ecnatation.org \n";
	$headers .= "Reply-to: ECN natation  <$from>\n";
	$headers .= "From: ECN natation <$from>\n";
	if( !$dev ) {
		$headers .= "Bcc:ecninscription@gmail.com\n";
		}
	$headers .= "Content-Type: text/html; charset=\"utf-8\"";
	$headers .='Content-Transfer-Encoding: 8bit';


	if( $dev ) { 
		$body .="[$to]";
		$to=$dev_email;
	}
	

	$success = mail($to,$subject,$body,$headers);
	
	return $success;
	
}
//////////////////////////////////////////////////////////////////////////////////////////////
function getTexte() {
    global $mysqli, $saison_enc, $dateforum, $urlpublic ;
   
	$urlins = 	$urlpublic ."?adhesion" ;

    $query ="SELECT data  FROM  messages_texte where type='nouveau' ";
	

    $result = $mysqli->query($query) ;
    if (!$result) {
        ($dev) ? $err=$mysqli->error : $err="invalid request";
        setError( $err );
        return ;
    }

    $r = $result->fetch_assoc() ;
   
    $data = utf8_encode( $r['data'] ) ;
	
	$old = array("#SAISON_ENC","#URLINS","#DATEFORUM");
	$new = array($saison_enc , $urlins ,$dateforum );
	$txtbody = str_replace($old, $new, $data);
	




    return $txtbody ;
    
    
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getDatas() {
	
	global $from,$group;
	global $tlicencies;
	global $dev,$mysqli;

	global $from ;
	global $group ;


	$message_body = getTexte();

	
	$query = "SELECT id,nom,prenom,categorie ,rang,officiel  FROM ".$tlicencies." WHERE valide='1'  ORDER BY nom, prenom ";

	
	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err ,404 );
		return ;
	}

	

	$rows = array();
	while($r = $result->fetch_assoc() ) {


		
		$r['nom']=ucfirst( strtolower( utf8_encode( $r['nom'] )) ) ;
		$r['prenom'] = utf8_encode( $r['prenom'] ) ;
		$r['categorie']=ucfirst( strtolower($r['categorie']) ) ;
		
		
		$e = json_encode( $r ) ;
		if ( $e != false  )
		{
			$rows[] = $r  ;
		}
	

	}
	
	
	
	$res=getCompetionsEncours();
	$mysqli->close();
	
	
	$datas['lic']=$rows;
	$datas['comp']=$res;
	$datas['from']=$from;
	$datas['group']=$group;
	$datas['ins']=$message_body;
	
	
	echo json_encode($datas);

}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getCompetionsEncours() {

	global $tengagements,$tcompetitions;
	global $dev,$mysqli;
	
	$query = "SELECT competitions.id,nom,lieu  FROM $tengagements,$tcompetitions   WHERE competitions.id=engagements.id_competitions AND debut >= NOW()  GROUP BY engagements.id_competitions   ORDER BY id, debut, fin ";
	
	
	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err , 404 );
		return ;
	}

	$rows = array();
	while($r = $result->fetch_assoc() ) {

		$r['id']=$r['id'];
		$r['nom']=utf8_encode($r['nom'] );
		$r['lieu']=utf8_encode($r['lieu'] );
		
		$e = json_encode( $r ) ;
		if ( $e != false  )
		{
			$rows[] = $r  ;
		}
	}

	return $rows;

}
/////////////////////////////////////////////////////////////////////////////////////////////

function getLicenciesGroup ( $list ) {
	global $tlicencies , $tlicencies_encours ;
	global $dev,$mysqli;
	$membres = false;
	$attente = false;
	$preinscrit = false;
	$officiel = false;
	$entraineur = false;
	$in = "";


	foreach( $list as $key ) {
		if ($key === "me") {
			$membres = true;
			break;
		}
		if ($key === "at") {
			// attente non validés
			$attente = true;
			break;
		}
		if ($key === "pre") {
			// attente non validés
			$preinscrit = true;
			break;
		}


		if ($key === "of") {
			$officiel = true;
			continue;
		}

		if ($key === "ent") {
			$entraineur = true;
			continue;
		}


		$key =strtoupper( $key );

		if( empty($in) ) {
			$in .= "'".$key."'";
		} else {
			$in .= ",'".$key."'";
		}

	}

	if ( $membres ) {
		$query = "SELECT DISTINCT  email  FROM $tlicencies  WHERE valide='1'  ORDER BY id ";
	} else if ( $preinscrit ) {
		$query = "SELECT DISTINCT  email  FROM $tlicencies_encours  WHERE  inscription ='1' AND valide='0'  ORDER BY id ";
	} else if ( $attente ) {
		$query = "SELECT DISTINCT  email  FROM $tlicencies_encours  WHERE  inscription ='0' AND valide='0'  ORDER BY id ";
	}
	
	else {
		$wh = "" ;
		if( $officiel ) {
			$wh = " officiel IS NOT NULL " ;
		} 
		if( $entraineur ) {
			if( !empty($wh) ) $wh.= " OR " ;
			$wh = " entr = '1' " ;
		}

        if( ! empty($in) ) {
			if( !empty($wh) ) $wh.= " OR " ;
			$wh .= " categorie IN ( $in ) ";
		}  


	$query = "SELECT DISTINCT  email  FROM $tlicencies  WHERE $wh AND valide='1'  ORDER BY id ";

	}

	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err , 404 );
		return ;
	}

	$rows = array();
	while($r = $result->fetch_assoc() ) {
		$e = explode(",", $r['email']);
		$email = "" ;
		
		foreach($e as $k ) {
			if( empty( trim($k) ) ) continue ;
			$email .= ( empty($email) ) ?  $k : ",".$k ;
			}
			if( !empty($email) ) {
				$rows[]  = $email ;
			}
		
		}

		
		return $rows ;

	}
/////////////////////////////////////////////////////////////////////////////////////////////
function getAllLicencies( $list ) {
	global $tlicencies;
	global $dev,$mysqli;
	
	$in = "";
	foreach( $list as $key ) {
		
		if( empty($in) ) {
			$in .= "'".$key."'";
		} else {
			$in .= ",'".$key."'";
		}

	}

	$query = "SELECT email  FROM $tlicencies  WHERE id IN ( $in )  ORDER BY id ";

	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err , 404 );
		return ;
	}



	$rows = array();
	while($r = $result->fetch_assoc() ) {
		$e = explode(",", $r['email']);
		$email = "" ;
		foreach($e as $k ) {
			if( empty( trim($k) )  ) continue ;
			$email .= ( empty($email) ) ?  $k : ",".$k ;
			}
			if( !empty($email) ) {
				$rows[]  = $email ;
			}
		
		}

		
		return $rows ;

	}



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function validationParams($data) {
	
	if( ! isset($data->body)     )  return false ;
	if( ! isset($data->subject)  )  return false ;
	if( ! isset($data->from)     )  return false ;
	if( ! isset($data->mode)     )  return false ;
	
	if( isset($data->compet ))
	{
		if ( !isset( $data->choix) && !is_array($data->choix) ) return false; 
		$tv=array('at','ko','ok') ;
		$ret = true;		
		foreach ( $data->choix as $choice ) {
			
			if ( ! in_array( strtolower($choice) ,$tv ) ) {
				$ret =false;
				break;
			}
			
		}
			return $ret;
	}
	else if( isset($data->dests)  )
	{
		if( !is_array($data->dests ) && empty($data->dests ) ) return false ;
		else return true;
	}
	else if( isset($data->email)  )
	{
		if( empty($data->email ) ) return false ;
		else return true;
	}
		
		return false ;
		
		
}
///////////////////////////////////////////////////////////////////////////////
function getLicenciesFromCompetition( $cond ) {

	global $tengagements,$tengage_date;
	global $dev,$mysqli;

	$query = "SELECT id_licencies  FROM $tengagements , $tengage_date WHERE $cond ORDER BY id_licencies ";
	


	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err , 404 );
		return ;
	}

	$rows = array();
	while($r = $result->fetch_assoc() ) {
		$rows[] =  $r['id_licencies'] ;
		}
	return $rows ;


}
////////////////////////////////////////////////////////////////////////



?>
