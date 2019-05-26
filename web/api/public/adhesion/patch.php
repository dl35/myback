<?php

include '../../common/config.php';

//patchid();
patchparams();


////////////////////////////////////////////////////////////////////////////////////////////////
function createKeyCode ( $nom , $prenom ) {

	
	$rename='';//rand(100,999);	
	for ($i = 0; $i < strlen($nom); $i++){
		$n=$nom[$i];
		if( ord($n) >= 65  && ord($n) <= 90  )
		{
			$rename.=$n;
		}
		// deux lettres du nom
		if( strlen($rename) == 2 )
		{
			break;
		}
			
	}
	$reprename='';//rand(100,999) ;
	for ($i = 0; $i < strlen($prenom); $i++){
		$n=$prenom[$i];
		if( ord($n) >= 65  && ord($n) <= 90  )
		{
			$reprename.=$n;
		}
		// 1 lettre du prenom
		if( strlen($reprename) == 1 )
		{
			break;
		}
		
	}
	
	
	
	
	$n=$rename;
	$p=$reprename;
	$rand =$n.$p. rand(10000,99999); 

	return $rand;
	
}



function patchid() {
	global $dev;
	global $host, $base, $base_user, $base_passwd ;
	global $tlicencies;
	//global $mysqli ;

	$mysqli = new mysqli ( $host , $base_user, $base_passwd, $base );
	if ($mysqli->connect_errno) {
	($dev) ? $err=$mysqli->connect_error: $err="invalid connect";
	setError( $err );
	return ;
	}

	
	$query = "SELECT id,nom,prenom  FROM $tlicencies ";
	
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		http_response_code(404);
		($dev) ? $err=$mysqli->error: $err="invalid query";
		setError( $err );
		return;
	}
	
	
	
	$res=false;
	
	$liste=array();
	while($r = $result->fetch_assoc() ) {
		
		$id=($r['id'] );
		$nom=utf8_encode($r['nom'] );
		$prenom=utf8_encode($r['prenom'] );
		$code=createKeyCode($nom,$prenom);
		$liste[$id]=$code;
	
				}

				
		foreach ($liste as $k => $v )
		{
			$query=" UPDATE $tlicencies SET id='$v' WHERE id='$k'  ";
			echo $query;
			$result = $mysqli->query( $query ) ;
			if (!$result ) {
				http_response_code(404);
				($dev) ? $err=$mysqli->error: $err="invalid query";
				setError( $err );
				return;
			}
			
			else {
				echo "ok<br>";
			}
		}





}

function patchparams() {

	global $dev;
	global $host, $base, $base_user, $base_passwd ;
	global $tlicencies;
	//global $mysqli ;

	$mysqli = new mysqli ( $host , $base_user, $base_passwd, $base );
	if ($mysqli->connect_errno) {
	($dev) ? $err=$mysqli->connect_error: $err="invalid connect";
	setError( $err );
	return ;
	}

	
	$query = "SELECT * FROM $tlicencies ";
	
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		http_response_code(404);
		($dev) ? $err=$mysqli->error: $err="invalid query";
		setError( $err );
		return;
	}
	
	
	
	$res=false;
	
	$liste=array();
	while($r = $result->fetch_assoc() ) {
		
		$id=($r['id'] );
		$b=array();

		$b['banque']['nom']=$r['banque'];
		$b['banque']['especes']=$r['especes'];
		$b['banque']['ch1']=$r['cheque1'];
		$b['banque']['ch2']=$r['cheque2'];
		$b['banque']['ch3']=$r['cheque3'];
		$b['banque']['num1']=$r['num_cheque1'];
		$b['banque']['num2']=$r['num_cheque2'];
		$b['banque']['num3']=$r['num_cheque3'];
		$b['chvac']['nb10']=$r['nbre_chvac10'];
		$b['chvac']['nb20']=$r['nbre_chvac20'];
		$b['sport']['cheque']=$r['ch_sport'];
		$b['sport']['coupon']=$r['coup_sport'];
		$b['sport']['num_coupon']=$r['num_coupsport'];
		$b['carte']['type']= null;
		$b['carte']['num']= null;
		$b['comment']= ( stripslashes ( $r['commentaires']) );
	
		$p['infos']=$b;

		$liste[$id]= json_encode($p , JSON_UNESCAPED_SLASHES  |  JSON_PRETTY_PRINT );
	
				}

				
		foreach ($liste as $k => $v )
		{
			$query=" UPDATE $tlicencies SET params='".$mysqli->real_escape_string($v)."' WHERE id='$k'  ";
			//echo $query.'\n';
			$result = $mysqli->query( $query ) ;
			if (!$result ) {
				http_response_code(404);
				($dev) ? $err=$mysqli->error: $err="invalid query";
				setError( $err );
				return;
			}
			
			else {
				echo "ok<br>";
			}
		}


}












?>
