<h1>Hello Cloudreach!</h1>
<h4>Attempting MySQL connection from php...</h4>
<?php
echo "<br>".phpinfo();
echo phpversion()."</br>";

return ;

$host = 'mysql';
$user = 'ecn';
$pass = 'ecn';
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
echo "Connected to MySQL successfully!";

ini_set( 'display_errors', 1 );
 
error_reporting( E_ALL );

$from = "denis.lesech@free.fr";

$to = "denis.lesech@gmail.com";

$subject = "Vérification PHP mail";

$message = "PHP mail marche";

$headers = "From:" . $from;

$ret = mail($to,$subject,$message, $headers);

if( $ret === false  ) {
    echo " error.";
} else {
    echo "L'email a été envoyé.";
}


//echo "<br>".phpinfo();



?>

