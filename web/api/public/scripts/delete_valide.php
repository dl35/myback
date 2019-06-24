<?php

include_once '../../common/config.php';

if(  !isset($_POST['key'] )  )
{
header("Location: http://ecnatation.fr/");
exit();
}

else {

 // connexion base de données
$mysqli = new mysqli ( $host , $base_user, $base_passwd, $base );

$key = $_POST['key'] ;

if ( $mysqli->connect_errno ) {
	($dev) ? $err=$mysqli->connect_error: $err="invalid connect";
	echo $err  ;
	return ;
	
}   


$query="UPDATE $tlicencies_encours set inscription='-1' , date_inscription=NOW()  WHERE id = '$key' LIMIT 1 " ;


$result = $mysqli->query( $query ) ;
if (!$result) {
    ($dev) ? $err=$mysqli->error: $err="invalid query";
    echo $err  ;
    return;
} 
   
}

?>
<html>
<head>
<style>
.head_mini_gray {
	height: 30px;
	width: 600px;
    background-image:url(banner_mini_gray.png);
 
}
</style>

<script type="text/javascript">

function toecn() {
ecn ="http://www.ecnatation.fr";
window.location=ecn;
}

</script>
</head>
<body>
<center>
<br>
<div class="head_mini_gray"></div>
<br>
<hr>
<hr>

<strong>
<?php
if ($mysqli->affected_rows > 0) {
    $v = "Suppression effectuée.";
   
}
else {
    $v = "La suppression a déjà été effectuée.";
}
?>


    <span  style='color: green' ><?php echo $v  ?></span>
    <br><br>
    <input  style='background-color: green ;color:white'  type="button" value="Quitter"  onclick="toecn()"/>

</strong>
</center>



</body>
</html>
