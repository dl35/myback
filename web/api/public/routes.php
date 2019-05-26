<?php
include '../common/config.php';



$method = $_SERVER['REQUEST_METHOD'];


if ( !isset($_GET['products'] ) ) 
{
	
	$response=array("success" => false, "message"  => "invalid parameters ") ;
	return json_encode( $response );
	
}


$products =  $_GET['products'] ;

if ( isset($_GET['id'])  )
{
	$id=$_GET['id'];
}

if ( isset($_GET['other'])  )
{
	$other=$_GET['other'];
}



// connexion base de données
$mysqli = new mysqli ( $host , $base_user, $base_passwd, $base );


if ( $mysqli->connect_errno ) {
	($dev) ? $err=$mysqli->connect_error: $err="invalid connect";
	setError( $err );
	return ;
	
}



switch ($products) {
	
	case 'toadhesion':
		include 'adhesion/adhesion.php';	
		break;
	case 'tocompetitions':
		include 'competitions/competitions.php';
		break;
	case 'toengagements':
		include 'engagements/engagements.php';
		break;
	case 'topiscines':
		include 'piscines/piscines.php';
		break;
	case 'torecords':
		include 'records/records.php';
		break;
	case 'tostats':
		include 'stats/stats.php';
		break;
	default :
		header('Location: http://www.ecnatation.org/site/public');
		break;
		
}








?>
