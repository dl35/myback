<?php

$auth= array("admin","user","ent");

if ( !isset($profile) && in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}

switch ($method) {
	case 'PUT':
		$data = json_decode(file_get_contents('php://input'));
		if ( !isset($id)  ||  !isset( $data ) ) {
			$err="invalid request post id/data ";
			setError($err );
			break;
		}
		// data /idcompet ->data->notify ; /idengage data->extranat ; /idcompet data->notifyall
		update( $id ,$data );
		break;

	case 'POST':
		$data = json_decode(file_get_contents('php://input'));
		if ( !isset($id)  ||  !$data ) {
			$err="invalid request post id/data ";
			setError( $err );
			break;
		}
		(  isset($data->append) ) ? appendEngagement( $id , $data) : createEngagement( $id , $data);
		break;

	case 'GET':
		if( ! isset($id )  )
		getCompetitions();			
		else {
			
				if( ! isset($_GET['other'] )  )
				{
					getEngagements( $id );
				}
				else if( $_GET['other'] === "lic" )
				{
					getLicencies($id);
				}
				else
				{
					getCategories($id,$_GET['other']);
				}
			
			}
		break;

	case 'DELETE':
				if ( !isset($id) ) {
					$err="invalid request ";
					setError($err );
					break;
				}
				$id =  str_replace("all_", "", $id , $count);
		    ( $count > 0 ) ?	deleteAll( $id ) : delete( $id );
				break;
	
	default:
		$err="invalid request global";
		setError($err );
		break;

}


/////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////
function to_datefr($jour) {
	
	list($year,$month,$day )=explode("-",$jour)	;
	$jour = array("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
	$mois = array("","Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre");
	
	$time= mktime(0, 0, 0,$month,$day,$year);
	
	$d_jour=date("w", $time);
	$d_day=date("d", $time);
	$d_month=date("n", $time);
	$d_year=date("Y", $time);
	
	$datefr = $jour[$d_jour]." ".$d_day." ".$mois[$d_month]." ".$d_year;
	return $datefr ;
}


/////////////////////////////////////////////////////////////////////////////////////
function dateCompetitions($debut,$fin) {
	$jour=date("Y-m-d", strtotime( $debut )  );
	$date=array();
	if($debut == $fin ) { $date[0]=$jour; return $date;}
	$i=0;$date[$i]=$jour;
	$error=false;
	while( true ){
		$i++;
		$jour1= strtotime($jour . "+$i day");
		$j1= date("Y-m-d", $jour1 );
		$date[$i]=$j1;
		if( $j1 == $fin ) break;
		if( $i >= 10 ) {$error=true;break;}
	}
	if( $error ) return false;
	else return $date;
}

///////////////////////////////////////////////////////////////////////////////////////////////

function getLicencies($idcompet)
{
	global $tlicencies,$tengagements,$tcompetitions,$tengage_date;
	global $dev,$mysqli;
	
	
	
	
	$query="SELECT id,id_licencies FROM ".$tengagements." WHERE id_competitions='$idcompet' ";
	
		
	$result = $mysqli->query( $query ) ;
	$myliste=array();
	while($r = $result->fetch_assoc()) {
		
		$idlic=$r['id_licencies'];
		$id=$r['id'];
		$myliste[$idlic]=$id;
		
	}
	
		
	$query="SELECT id,nom,prenom,categorie,rang FROM ".$tlicencies." WHERE  valide='1'  ORDER BY nom,prenom ";
	$result = $mysqli->query( $query ) ;
	
	
	$myres=array();
	while($r = $result->fetch_assoc()) {
		
		$id=$r['id'] ;
		if ( isset($myliste[$id]) ) {
			continue ;
		}
		
		$r['nom']=utf8_encode( ucfirst( strtolower( $r['nom'] ) ) );
		$r['prenom']=utf8_encode($r['prenom']);
		$r['categorie']= strtolower( $r['categorie'] );
		$myres[]=$r;
		
		
	}
	
	
	header("Content-type:application/json");
	echo json_encode($myres);
	
}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function getCategories($idcompet,$cat)
{
	global $tlicencies,$tengagements,$tcompetitions,$tengage_date;
	global $dev,$mysqli;
	global $host, $base, $base_user, $base_passwd ;
	
	
	
	$cat=strtolower( $cat );
	
	$query="SELECT id_licencies FROM ".$tengagements." WHERE id_competitions=$idcompet ";
	$result=$mysqli->query($query);
	
	
	if (!$result) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		setError( $err );
		return ;
	}
	
	
	
	
	$myliste=array();
	while($r = $result->fetch_assoc() ) {
		
		$id=$r['id_licencies'];
		$myliste[$id]=$id;
		
	}
	
	
	
	$query="SELECT id,nom,prenom,rang FROM ".$tlicencies." WHERE categorie = '$cat' AND valide='1'  ORDER BY nom,prenom ";
	$result=$mysqli->query($query);
	
	
	$myres=array();
	while($r = $result->fetch_assoc()) {
		
		$id=$r['id'] ;
		if ( ! isset($myliste[$id]) )
		{
			$r['nom']=utf8_encode( ucfirst( strtolower( $r['nom'] ) ) );
			$r['prenom']=utf8_encode($r['prenom']);
			$r['selected']=false;
			$myres[]=$r;
		}
		
		
	}
	
	
	header("Content-type:application/json");
	echo json_encode($myres);
	
}



///////////////////////////////////////////////////////////////////////////////////////////////
function isEngagements($id) {
	global $tengagements;
	global $dev,$mysqli;
	
	
	
	//////////////////////
	$query="SELECT id FROM ".$tengagements." WHERE id_competitions = $id ";
	$result=$mysqli->query($query);
	
	
	if (!$result) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		setError( $err );
		return ;
	}
	$nb=$result->num_rows;
	( $nb > 0 ) ? $res=true : $res=false ;
	
	
	return $res ;
}


///////////////////////////////////////////////////////////////////////////////////////////////
function createEngagement($id , $data ) {
	
	global $tlicencies,$tengagements,$tcompetitions,$tengage_date;
	global $dev,$mysqli;
	
	
	
	//////////////////////
	$query="SELECT id FROM ".$tengagements." WHERE id_competitions = $id ";
	$result=$mysqli->query($query);
	
	
	if (!$result) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		setError( $err );
		return ;
	}
	$nb=$result->num_rows;
	if( $nb > 0 )
	{
		$err="Cet engagement existe avec $nb enregistrements ";
		setError( $err );
		return ;
	}
	
	
	///////////////
	
	$cat=array("av","je","ju","se","ma");
	$niveau=array("dep","reg","nat");
	
	$wn="";
	$wc="";
	
	foreach ($data as $k => $v )
	{
		
		if( $v === false ) continue;
		
		if( in_array($k, $niveau) )
		{
			if( !empty($wn) ) $wn.=" OR ";
			$wn.=" (niveau='$k') ";
		}
		
		
		
		else if( in_array($k, $cat) )
		{
			if( !empty($wc) ) $wc.=" OR ";
			$wc.=" (categorie='$k') ";
		}
		
		
		
		
	}
	
	
	if( empty($wn) && empty($wc) )
	{
		setError( "Filtre creation engagement");
		return ;
	}
	
	
	
	
	
	if ( !empty($wn) && !empty($wc) )
	{
		$wn.=" OR ";
	}
	
	$w = " WHERE ( valide=1 AND ( ".$wn.$wc ." ) ) ";
	
	
	
	
	$query="SELECT debut,fin,type FROM ".$tcompetitions." WHERE id =$id";
	$result=$mysqli->query($query);
	
	if (!$result) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		setError( $err );
		return ;
	}
	

	
	$debut="";
	$fin="";
	$type="";
	while($r = $result->fetch_assoc() ) {
	
		$debut=$r['debut'];
		$fin=$r['fin'];
		$type=$r['type'];
	
	}
	if( $type == "stage" ) $fin=$debut;
	$dates=dateCompetitions($debut, $fin);
	
	if( !is_array($dates) )
	{
		$mysqli->close();
		$err="dates competitions > 10 jours !";
		setError( $err );
		return ;
		
	}
	
	
	$query="SELECT id FROM ".$tlicencies.$w." ORDER by nom";
		
	$result=$mysqli->query($query);
	
	
	if( !$result )
	{
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		$mysqli->close();
		setError( $err );
		return ;
		
	}
	
	
	
	$n=0;
	
	while($r = $result->fetch_assoc() ){
				
		
		$id_licencies =	$r['id'];
		
		//insertion engagement
		$query = "INSERT INTO $tengagements SET id_competitions='$id',id_licencies='$id_licencies' ";
	
		
		
		$res=$mysqli->query($query);
		if( !$res  )
		{
			($dev) ? $err=$mysqli->error." ".$query : $err="query invalid";
			setError( $err );
			return ;
			
		}
		
		
		$last_id=$mysqli->insert_id ;
		foreach ( $dates as $key => $day) {
			//insertion des dates
			$query2 = "INSERT INTO $tengage_date SET date='$day',id_engage='$last_id' ";
			$res=$mysqli->query($query2);
			
			if( !$res  )
			{
				($dev) ? $err=$mysqli->error." ".$query2 : $err="query invalid";
				setError( $err );
				return ;
				
			}
		}
		
		$n++;
		
	}// insert engagements
	
	
	$message="Creation avec $n enregistrements" ;
	setSuccess($message);
	
	
	
}



////////////////////////////////////////////////////////////////////////////////////////////
function getEngagements($id) {
	
	global $tlicencies,$tengagements,$tcompetitions,$tengage_date;
	global $dev,$mysqli;
	
	
	$query="SELECT $tengagements.commentaire,engage_date.date,engage_date.id as edid ,$tengagements.id,$tengagements.id_licencies,$tengagements.id_competitions,$tengagements.extranat,$tlicencies.nom,$tlicencies.prenom,$tlicencies.categorie,$tlicencies.sexe,$tlicencies.rang,$tengagements.notification,$tengage_date.presence ".
			" FROM $tlicencies,$tengagements,$tengage_date ".
			" WHERE  $tengagements.id_competitions = $id  ".
			" AND  $tlicencies.id=$tengagements.id_licencies ".
			" AND $tengage_date.id_engage = $tengagements.id ".
	        " ORDER BY $tlicencies.id,$tlicencies.nom, $tlicencies.prenom,$tengage_date.date asc ";

	

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
		$r['commentaire']=utf8_encode($r['commentaire'] );
		
		$r['nom']=utf8_encode($r['nom'] );
		$r['prenom']=utf8_encode($r['prenom'] );
		
		$r['categorie']=ucfirst( $r['categorie'] );
		$tengage=array();
		/*$debut = new DateTime($r['debut']);
		if( $r['debut'] == $r['fin'] ) {
			$day = $debut->format("D d M"); */


		$tengage['day'] = substr($r['date'],8 );
		$tengage['presence'] = $r['presence'];
		$tengage['edid'] = $r['edid'];
		
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
	

	echo  json_encode(array_values($rows) ) ;
	//,  JSON_NUMERIC_CHECK );

}


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


function getCompetitions() {
	global $dev ;
	global $tcompetitions ;
	global $dev,$mysqli;
	
	
	
	$query = "SELECT id,nom,lieu,debut,fin  FROM $tcompetitions WHERE verif='1' AND debut >= NOW()  ORDER BY debut, fin ";
	
		
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err );
		return ;
	}


	$rows = array();
	while($r = $result->fetch_assoc() ) {
		
		$day= '' ;
		$debut = new DateTime($r['debut']);
		if( $r['debut'] == $r['fin'] ) {
			$day = $debut->format("D d M");
		} else {
			$fin = new DateTime($r['fin']);
			$dfin = $fin->format("D d M");
			$ddebut = $debut->format("D d");
			$day = $ddebut ." au ". $dfin;
			
		}
		
		$day = formatFr( $day );
		
		$label = $r['nom'].' ('.$r['lieu'] .") ".$day;
		$label = utf8_encode( $label );
		$res['id']=$r['id'];
		$res['label']=$label;
		$rows[] = $res;
		
		
	}


	
	echo  json_encode(array_values($rows));

}


function dateToFr( $ch ) {
	$k=array("Mon" => "Lun" , "Tue" => "Mar" , "Wed" => "Mer","Thu" => "Jeu" ,"Fri" => "Ven" , "") ;
	
	
}



function update( $id  , $data )
{
	global $dev , $mysqli;
	global $tengagements ;
	
	
	
	if( isset($data->notifyall)  )
	{
		$idcompetitions=$id;
		sendMails( $idcompetitions  , true  ) ;
		return;
	}
	else if( isset($data->notify)  )
	{
		$idcompetitions=$id;
		$ide= $data->notify;
		sendMails( $idcompetitions  , $ide  ) ;
		return;
	}
	else if( isset($data->extranat)  )
	{
		$ide = $data->extranat;
		$query="UPDATE $tengagements SET extranat = 1 - extranat  WHERE $tengagements.id =$ide ";
	
		$result = $mysqli->query( $query ) ;
		if (!$result) {
			$error="invalid request";
			setError($error);
			return ;
		}
		
		setSuccess( "Extranat" );
		return;
	}
	
	else {
		setError("Put not authorized");
		return;
	}
	
	
	
	
}



/**
 * ajoute des licencies à l'engagement
 */
function sendMails($idcompetitions,$idengage ) {
	
	global $urlvalidation;
	global $tlicencies,$tengagements,$tcompetitions,$tengage_date;
	global $dev,$mysqli ;

	
	$query="SELECT id,nom,prenom,email  FROM $tlicencies ";
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error: $err="invalid request";
		setError( $err );
		return ;
	}
	
	
	
	$map = array();
	while($r = $result->fetch_assoc() ) {
		$x = array();
		$x['nom']=$r['nom'];
		$x['prenom']=$r['prenom'];
		$v=$r['email'];
		
		while ( strpos($v, ',,') !== false) {
			$v=str_replace(",,", ",", $v);
		}
		if( substr($v,-1,1) == ","  )
		{
			$v=substr($v,0,strlen($v)-1);
		}
		
		$x['email']=$v;
		$map[$r['id']] = $x;
	}
	
	
	
	// competitions parameters
	
	$query="SELECT * FROM ".$tcompetitions." WHERE verif='1' AND  id = '$idcompetitions'  ";
	$result=$mysqli->query($query);
	
	if (!$result) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err = "invalid query" ;
		setError( $err );
		return ;
	}
	
		
	$compet=array();
	while($r = $result->fetch_assoc() ) {
		
		$compet["debut"]=$r['debut'];
		$compet["fin"]=$r['fin'];
		$compet["limite"]=$r['limite'];
		$compet["nom"]=$r['nom'];
		$compet["lieu"]=$r['lieu'];
		$compet["type"]=$r['type'];
		$compet["choixnages"]=$r['choixnages'];
		$compet["lien"]=$r['lien'];
		
	}

	
	
	if( strtotime($compet['limite'] ) <= time()  )
	{
		$err="la date limite est dépassée ! ".$compet['limite'];
		setError( utf8_decode( $err ) );
		return ;
	}
	
	( $idengage === true ) ? $wh=" id_competitions = '$idcompetitions' " :$wh=" id = '$idengage' " ;
	
		
	$query="SELECT id,id_licencies,notification,date_reponse  FROM $tengagements WHERE $wh ";
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error: $err="invalid request";
		setError( $err );
		return ;
	}
	
	
	$meng = array();
	
	while($r = $result->fetch_assoc() ) {
		
		
		
		$id=$r['id'];
		$idlic=$r['id_licencies'];
		$notification=$r['notification'];
		$date_reponse=$r['date_reponse'];
		if ( $idengage === true && $date_reponse !== NULL  ) {
			continue;
		}
		
		
		/*if( $notification == 0 ) $first=true;
		else $first=false;*/
		
		if( !isset($map[$idlic]) ) { continue; }
		$success=false;
		
		if( $compet['type']=="stage" ) {
			$success= envoyer_mail_stage($compet, $map[$idlic], $id , $idlic );
			$success = true ;
		} else {
		  $success= envoyer_mail( $compet, $map[$idlic], $id , $idlic );
		    }
	    if( $success ) { $meng[]=$id; }    
		
	}
	
	
	$nb=0;
	foreach ( $meng as $id  )
	{
		
		$date_notify=date( 'Y-m-d H:i:s');
		
		$query="UPDATE $tengagements SET notification=notification+1, date_notify = '$date_notify' ".
		       " WHERE $tengagements.id = '$id' ";
		
		$result = $mysqli->query( $query ) ;
		if (!$result) {
		  break;
		}
		
		$nb++;
	}
	

	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request update";
		setError( $err );
		return ;
	}
	
	($nb > 1 ) ? $message="$nb notifications" : $message="$nb notification" ;
	setSuccess($message);
	

	
}






function envoyer_mail($compet, $lic  , $ide , $idlic )
{

	global $dev , $dev_email;
	global $urlscript;

	$url=$urlscript."/engage.php?eng=".$ide."&id=".$idlic ;


	
	$lieu=$compet['lieu'];
	$label=$compet['nom'];
	
	
	$debut=$compet['debut'];
	$fin=$compet['fin'];
	$limite=$compet['limite'];
	
		
	if( $debut != $fin )
	{
		$date=" du ".to_datefr($debut)." au ".to_datefr($fin);
	}
	
	else
	{
		$date=" le ".to_datefr($debut);
	}
	
	$limite = to_datefr($limite);
	$nageur=$lic['prenom']." ".$lic['nom'];
	
	$to=$lic['email'];

	 
	// $nageur = utf8_encode( $label ) ;

	$subject =utf8_decode("[ Club Natation ] Engagement à la compétition ") . $label ." : ".$nageur;
	
	$label = utf8_encode( $label ) ;
	$lieu = utf8_encode( $lieu ) ;
	$nageur = utf8_encode( $nageur ) ;
		
	$message="<html><head><title>Engagement competition</title></head><body>".
			"Bonjour,<br> $nageur a été selectionné(e) pour participer à la compétition suivante : <br>".
			"$label qui se deroulera $date à $lieu .<br>".
			"La date limite de validation est le : <u>$limite</u> .<br><br>".
			"Merci de VALIDER ou NON la participation de $nageur à cette compétition .<br>".
			"Pour cela cliquer sur le lien ci-dessous :<br>".
			"<a href=\"$url\" >gestion de l'engagement</a><br><br>".
			
			"<strong>ATTENTION, nous vous rappelons que le règlement intérieur du club précise que  :<br>".
			"-le transport des nageurs aux compétitions est à la charge des parents.<br>".
			"Merci de vous assurer que le déplacement de votre enfant à la compétition est assuré.<br>".
			"Nous mettons à la disposition des parents sur le site Web, les Wikis par catégorie pour faciliter le covoiturage.<br><br>".
			
			"-pour tout forfait après la date limite d'engagement à la compétition SANS fourniture d'un certificat médical,<br>".
			"la Fédération Française de Natation (FFN) nous facture une amende pour chaque épreuve non nagée.<br>".
			"Sans fourniture du certificat médical, cette amende sera à payer par les parents du nageur.<br><br></strong>".
						
			"Sportivement<br>".
			"--<br>".
			"Les entraineurs du club <br>".
			"Web : <a href=\"http://ecnatation.fr\" >http://ecnatation.fr</a><br>".
			"</body></html>"  ;
	
			
			$message =utf8_decode( $message );
			
			$headers = "MIME-Version: 1.0\n";
			$headers .= "X-Sender: <www.ecnatation.org>\n";
			$headers .= "X-Mailer: PHP\n";
			$headers .= "X-auth-smtp-user: webmaster@ecnatation.org \n";
			$headers .= "X-abuse-contact: webmaster@ecnatation.org \n";
			$headers .= "Reply-to: ECN natation  <competitions@ecnatation.org>\n";
			$headers .= "From: ECN natation <webmaster@ecnatation.org>\n";
			if( !$dev )  $headers .= "Bcc:ecninscription@gmail.com\n";
			$headers .= "Content-Type: text/html; charset=iso-8859-1";
			
			
		    $dev = true ;		
			
			if( $dev ) $to = $dev_email ;
			
			
	
	/* $subject = "essai de transmission" ;
    $message = "Ceci est un essai." ;		
	$to = "denis.lesech@gmail.com"; */
			return  mail($to,$subject,$message,$headers);
			
			
}



	function envoyer_mail_stage($compet, $lic  , $ide , $idlic )
	{
	
		global $dev ,$dev_email;
		global $urlscript;
		
	
		$url=$urlscript."/engage.php?eng=".$ide."&id=".$idlic ;
		
		$lieu=$compet['lieu'];
		$label=$compet['nom'];
		
		
		$debut=$compet['debut'];
		$fin=$compet['fin'];
		$limite=$compet['limite'];	
	
		if( $debut != $fin )
		{
			$date=" du ".to_datefr($debut)." au ".to_datefr($fin);
		}
		
		else
		{
			$date=" le ".to_datefr($debut);
		}
		
		$limite = to_datefr($limite);
		$nageur=$lic['prenom']." ".$lic['nom'];
		
		$to=$lic['email'];
		
		$subject ="[ Club Natation Stage] $label : ".$nageur;

		$nageur = utf8_encode( $nageur );
		$lieu = utf8_encode( $lieu );

		$message="<html><head><title>Stage</title></head><body>".
				"Bonjour,<br> un stage est organisé à  $lieu  $date . <br><br>".
				"Merci de VALIDER ou NON la participation de $nageur à ce stage .<br>
				Pour cela cliquer sur le lien ci-dessous :<br>
				<a href=\"$url\" >gestion du stage </a><br><br>
				Sportivement<br>
				--<br>
				Les entraineurs du club <br>
				Web : <a href=\"http://ecnatation.fr\" >http://ecnatation.fr</a><br>
				</body></html>"  ;
		
		$message = utf8_decode( $message );		

		$headers = "MIME-Version: 1.0\n";
		$headers .= "X-Sender: <www.ecnatation.org>\n";
		$headers .= "X-Mailer: PHP\n";
		$headers .= "X-auth-smtp-user: webmaster@ecnatation.org \n";
		$headers .= "X-abuse-contact: webmaster@ecnatation.org \n";
		$headers .= "Reply-to: ECN natation  <competitions@ecnatation.org>\n";
		$headers .= "From: ECN natation <webmaster@ecnatation.org>\n";
		$headers .= "Bcc:ecninscription@gmail.com\n";
		$headers .= "Content-Type: text/html; charset=iso-8859-1";

		if( $dev ) $to = $dev_email ;
		
	
		$success = mail($to,$subject,$message,$headers);

		return $success;
}







/**
 * ajoute des licencies à l'engagement
 */
function appendEngagement($idcompetitions,$datas) {
	
	global $tlicencies,$tengagements,$tcompetitions,$tengage_date;
	global $dev,$mysqli ;
	
	$listelicencies=$datas->ids;
	
	$query="SELECT debut,fin,limite,type FROM ".$tcompetitions." WHERE id = $idcompetitions";
	$result=$mysqli->query($query);
	
	if (!$result) {
		($dev) ? $err=$mysqli->error ." ".$query  : $err="invalid query";
		setError( $err );
		return ;
	}
	
	
	$debut="";
	$fin="";
	$type="";
	$limite="";
	while($r = $result->fetch_assoc() ) {
		
		$debut=$r['debut'];
		$fin=$r['fin'];
		$type=$r['type'];
		$limite=$r['limite'];
	}
	if( $type == "stage" ) $fin=$debut;
	$dates=dateCompetitions($debut, $fin);
	
	if( !is_array($dates) )
	{
		$mysqli->close();
		$err = "dates competitions > 10 jours !";
		setError( $err );
		return ;
		
	}
	


	$n=0;
	foreach ($listelicencies as $id_licencies){
		
	
		//insertion engagement
		$query = "INSERT INTO $tengagements SET id_competitions='$idcompetitions',id_licencies='$id_licencies'  ";
		
		$res=$mysqli->query($query);
		if( !$res  )
		{
			($dev) ? $err=$mysqli->error." ".$query : $err="query invalid";
			setError( $err );
			return ;
			
		}
		
		
		$last_id=$mysqli->insert_id ;
		foreach ( $dates as $key => $day) {
			//insertion des dates
			$query2 = "INSERT INTO $tengage_date SET date='$day',id_engage='$last_id' ";
			$res=$mysqli->query($query2);
			
			if( !$res  )
			{
				($dev) ? $err=$mysqli->error." ".$query2 : $err="query invalid";
				setError( $err );
				return ;
				
			}
		}
		
		$n++;
		
	}// insert engagements
	
	($n > 1 ) ? $message="Ajout avec $n enregistrements" : $message="Ajout avec $n enregistrement" ;
	setSuccess( $message);
	
	
}

/**
 * supression d'un ou de tous les engagements
 */
function delete( $ide ) {
	global $dev,$mysqli;
	global $tengagements,$tengage_date ;
	
	
	$query= "DELETE $tengagements, $tengage_date FROM $tengagements,$tengage_date WHERE $tengagements.notification = 0 AND $tengagements.id=$ide AND  $tengage_date.id_engage=$tengagements.id" ;
	
	
	$result = $mysqli->query( $query ) ;
		if (!$result) {
			($dev) ? $err=$mysqli->error." ".$query : $err="query invalid";
			setError( $err );
			return ;
		}
		
	
	$mysqli->close();

	$message="Supression";
	setSuccess($message);
	return;
	
	
	
}


/**
 * supression d'un ou de tous les engagements
 */
function deleteAll($idc) {
	global $dev,$mysqli;
	global $tengagements,$tengage_date ;
	
	
	$query= "DELETE $tengagements, $tengage_date FROM $tengagements,$tengage_date WHERE $tengagements.notification = 0 AND $tengagements.id_competitions=$idc AND  $tengage_date.id_engage=$tengagements.id" ;
	
	
	$result = $mysqli->query( $query ) ;
		if (!$result) {
			($dev) ? $err=$mysqli->error." ".$query : $err="query invalid";
			setError( $err );
			return ;
		}
		
	
	$nb = $mysqli->affected_rows;
	$mysqli->close();
	
	$message="Supression: ".$nb ;
	setSuccess($message);
	return;
	
	
	
}






?>
