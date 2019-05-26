<?php

$auth= array("admin","user");

if ( !isset($profile) && in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}

switch ($method) {
	case 'PUT':
		echo "PUT" ;
		$data = json_decode(file_get_contents('php://input'));
		var_dump($data);
			
		print_r( $_GET );
		break;

	case 'POST':
		echo "\n**********POST*************\n" ;
		$data = json_decode(file_get_contents('php://input'));
		//echo $data ;
		if( $data === false ) echo "errrr" ;
			
		//var_dump($data);
		print_r ( $data );
		echo "**********\n" ;
		print_r( $_GET );
		break;

	case 'GET':
		echo "GET" ;
		print_r( $_GET );
		break;

	case 'HEAD':
		echo "HEAD" ;
		print_r( $_GET );
		break;

	case 'DELETE':
		echo "DELETE" ;
		print_r( $_GET );
		break;

	case 'OPTIONS':
		echo "OPTIONS" ;
		print_r( $_GET );
		break;

	default:
		echo "error" ;
		break;

}

?>