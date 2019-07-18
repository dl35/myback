<?php 
include_once '../../common/config.php';

if ( !isset($_POST) ) {

echo "error" ;

} else {

$eng=$_POST['eng'];
$id=$_POST['id'];
$nages=$_POST['nages'];

unset($_POST['eng']);
unset($_POST['id']);
unset($_POST['nages']);

$data =array();
foreach( $_POST as $ide => $v ) 
$data[$ide] = $v;

updateEngagement($eng, $id,$nages , $data  );

}


function updateEngagement ($ide, $idl,$nages , $data) {
    global $dev ,$host , $base_user, $base_passwd, $base ;
	global $tengagements,$tengage_date,$tcompetitions;
    
    $mysqli = new mysqli ( $host , $base_user, $base_passwd, $base );

    if ( $mysqli->connect_errno ) {
      ($dev) ? $err=$mysqli->connect_error: $err="invalid connect";
      setError( $err );
      return ;
      
    }

	$query = "SELECT count(te.id_licencies) as nb , tc.max ,tc.type FROM $tengagements as te "
			 ."LEFT JOIN $tcompetitions as tc ON tc.id = te.id_competitions "
			 ."WHERE te.id = '$ide' GROUP BY te.id_competitions ";
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
		setError( $err );
			return;
    	}

	
	while($r = $result->fetch_assoc() ) {
		$nb = $r['nb'] ;
		$max = $r['max'] ;
		$type = $r['type'] ;
	}
	
	if( $type == "stage" &&  $nb >= $max ) {
		$err = "Impossible de valider.<br>";
		$err .= "Le nombre maximal de $max participants est atteint !" ;
	setError( $err );
	return;


   }

	
	










	if ( !empty($nages) ) { $comment =  utf8_decode($nages ); }
    else { $comment =NULL ; }


	foreach ($data as $id => $response ) {
		$query = "UPDATE $tengage_date SET presence = ?  WHERE id = ?  ";
		
		$params=array();
		$params[]=$response;
		$start="s";
		$params[]=$id;
		$start.="s";
	
		$stmt = $mysqli->prepare( $query );
		$stmt->bind_param( $start  ,...$params );
		
		
		$result = $stmt->execute();
		if (!$result) {
			($dev) ? $err=$stmt->error : $err="invalid update engae date";
			$stmt->close();
			setError( $err );
			return;
		}
		
		$stmt->close();
		
	}
	
	$params=array();
	$query = "UPDATE $tengagements SET date_reponse = NOW() ,commentaire = ?  WHERE id = ? AND id_licencies = ? ";
	$params[]=$comment;
	$start="s";
	$params[]=$ide;
	$start.="s";
	$params[]=$idl;
	$start.="s";
	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  ,...$params );
	
	
	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid update engagements";
		$stmt->close();
		setError( $err );
		return;
	}
	
	$stmt->close();
	$mysqli->close();
	
    $message["message"] ="validation: ok ";
    header('Content-Type: application/json');
	echo json_encode( $message );
	
	
}








?>