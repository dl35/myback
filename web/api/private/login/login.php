<?php
switch ($method) {
	
	case 'GET':
		get() ;
		break;
	case 'POST':
		$data = json_decode(file_get_contents('php://input'));
		if ( $data->email ) {
			sendMail($data->email ) ;
			return;
		}
		if ( ! validateObject($data , false ) ) {
			setError( "invalides parameters");
			return;
		}
		post($data) ;
		break;
	case 'PUT':
		$data = json_decode(file_get_contents('php://input'));
		if ( ! validateObject($data , true ) ) {
			setError( "invalides parameters");
			return;
		}
		put($data) ;
		break;
	case 'DELETE':
		if( !isset($_GET['id']) ) {
			setError( "invalid id");
			return;
		}
		delete($_GET['id']) ;
		break;
	default:
		setError( "invalides routes");
		break;
		
}

function validateObject($json , $put=false ) {
	
	if( $put && !array_key_exists('id' , $json) ) return false ;
	if ( !array_key_exists('user' , $json) ) return false ;
	if ( !array_key_exists('passwd' , $json) ) return false ;
	if ( !array_key_exists('profile' , $json) ) return false ;
	
	return true;
}

/////////////////////////////////////////////////////////////////////
function get($id=false) {
	
	
	global $tlogin ;
	global $dev,$mysqli;
	
	
	

	
	( $id === false )  ?  $wh =""  :  $wh = "WHERE id = '$id' " ;
	
	$query = "SELECT id,user,passwd,profile  FROM $tlogin  $wh ORDER BY user ";
	
	$result = $mysqli->query($query) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err );
		return ;
	}
	
	
	
	$rows = array();
	while($r = $result->fetch_assoc() ) {
		
		$r['passwd'] = utf8_encode($r['passwd'] );
		
		
		if( $r['profile'] === 'admin' ) {
			$r['color'] = 'primary';
			$r['icon'] = 'supervised_user_circle';
		} else if( $r['profile'] === 'ent' ) {
			$r['color'] = 'warn';
			$r['icon'] = 'account_box';
		} else {
			$r['color'] = 'accent';
			$r['icon'] = 'person';
		}
		
		
		
		
		$e = json_encode( $r ) ;
		if ( $e != false  )
		{
			$rows[] = $r  ;
		}
	}
	
	if( $id === false  ) {
		echo   json_encode($rows);
	} else {
		echo   json_encode($rows[0]);
	}
	
}

/////////////////////////////////////////////////////////////////////
function put( $data ) {
	

	global $tlogin ;
	global $dev,$mysqli;
	
	
	
	$set = " SET ";
	
	$set = "user=?" ;
	$params[] = utf8_decode($data->user);
	$start = "s";
	
	$set .= ",passwd=?" ;
	$params[] = utf8_decode($data->passwd);
	$start .= "s";
	
	$set .= ",profile=?" ;
	$params[] = utf8_decode($data->profile);
	$start .= "s";
	
	$id = $data->id ;
	
	$query = "UPDATE $tlogin SET $set  WHERE id = ?  ";
	$params[]= $id;
	$start.="s";
	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  ,...$params );


	
	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid execute";
		setError( $err );
		return;
	}
	$stmt->close();
	
	return get($id);
	
}



/////////////////////////////////////////////////////////////////////
function post($data) {
	
	global $tlogin ;
	global $dev,$mysqli;
	
	
	$set = "user" ;
	$params[] = $data->user;
	$start = "s";
	$inc = "?";
		
	$set .= ",passwd" ;
	$params[] = $data->passwd;
	$start .= "s";
	$inc .= ",?";
	
	$set .= ",profile" ;
	$params[] = $data->profile;
	$start .= "s";
	$inc .= ",?";
	

	
	
	$set ="(".$set.")";
	$inc ="(".$inc.")";
	
	
	$query=" INSERT INTO  $tlogin  $set  VALUES  $inc ";
	
	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  , ...$params );
	
	
	
	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid connect";
		setError(404 , $err );
		return;
	}
	
	$stmt->close();
	$id = $mysqli->insert_id ;

	return get( $id );
	
	
}
/////////////////////////////////////////////////////////////////////
function delete($id) {
	global $tlogin ;
	global $dev,$mysqli;
	
	$query=" DELETE FROM  $tlogin WHERE id = ? ";
	
	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param("i",$id);
	
	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request " .$query;
		setError($err ,404 );
		return ;
	}
	
	$stmt->close();
	$mysqli->close();
	$message = "Suppression";
	setSuccess($message);
	
}
///////////////////////////////////////////////////////////////////////
function sendMail($id) {
	global $tlogin ;
	global $dev,$mysqli;

	$query = "SELECT user,passwd,profile FROM $tlogin WHERE id = '$id' LIMIT 1 ";
	
	$result = $mysqli->query($query) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err );
		return ;
	}
	
	$email = '';
	while($r = $result->fetch_assoc() ) {
		$email = $r['user'] ;
		$passwd = $r['passwd'] ;
		$profile = $r['profile'] ;
   }
	$mysqli->close();

   	if ( $profile === 'admin') {
		$profile ='administrateur';
	} else if ( $profile === 'user') {
		$profile ='utilisateur';
	 } else {
		$profile ='entraineur'; 
	 }


	$body ="Bonjour,<br><br>";
	$body .="Voici vos identifiants :<br><br>";
	$body .="Utilisateur: $email <br>";
	$body .="Mot de passe: $passwd <br>";
	$body .="Profile: $profile ";
	
	$subject ="[ Idendifiants Club de Natation ] ";

	
	$headers = "MIME-Version: 1.0\n";
	$headers .= "X-Sender: <www.ecnatation.org>\n";
	$headers .= "X-Mailer: PHP\n";
	$headers .= "X-auth-smtp-user: webmaster@ecnatation.org \n";
	$headers .= "X-abuse-contact: webmaster@ecnatation.org \n";
	$headers .= "Reply-to: ECN natation  <webmaster@ecnatation.org>\n";
	$headers .= "From: ECN natation <webmaster@ecnatation.org>\n";
	$headers .= "Content-Type: text/html; charset=iso-8859-1";


	if( $dev ) $email="denis.lesech@gmail.com";
	$email="denis.lesech@gmail.com";

	$success = mail($email,$subject,$body,$headers);
	if( $success) {
		setSuccess('Envoi Email');
	} else {
		setError( 'Erreur Envoi Email');
	}
     	
	


}


?>
