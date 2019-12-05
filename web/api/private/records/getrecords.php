<?php
/*********************************************************************/
/*******************************************************************/

global $trecords;
global $dev,$mysqli;

if( isset( $_GET['bassin'] ) ) {
	$bassin = $_GET['bassin'] ;
} else {
	setError( "erreur Get , bassin ...") ;
}


$query = "SELECT *  FROM ".$trecords." WHERE bassin = '$bassin'  ORDER BY sexe,nage,age,nom,prenom ";


$result = $mysqli->query( $query )  ;
if (!$result) {
	($dev) ? $err=$mysqli->error : $err="invalid request";
	setError( $err ,404 );
	return ;
}
	



$getrecords=array();
while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {

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


$key=$sexe."_".$bassin."_".$nage."_".$distance."_".$age;
$getrecords[ $key ] ['temps']=  $temps ;
$getrecords[ $key ] ['nom']=  $nom ;
$getrecords[ $key ] ['prenom']=  $prenom ;
$getrecords[ $key ] ['date']=  $date ;
$getrecords[ $key ] ['points']=  $points ;
$getrecords[ $key ] ['type']=  $type ;

}

$requete="SELECT max(date) as max FROM records ";
$result = $mysqli->query( $requete ) or die("execute query.".mysql_error());
if (!$result) echo mysql_error();
$row = mysql_fetch_array($result,MYSQL_ASSOC) ;


$lastdate=$row["max"];
$y=substr($lastdate,0,4);
$m=substr($lastdate,5,2);
$d=substr($lastdate,8,2);
$lastdate=dateFr($m,$d,$y);
$lastdate=strtolower( $lastdate );



/////////////////////////////////////////////////////////////////////////////
function  dateFr($m,$d,$y) {
$time= mktime(0, 0, 0,$m,$d,$y);
$jour = array("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
$mois = array("","Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"); 

$d_jour=date("w", $time);
$d_day=date("d", $time);
$d_month=date("n", $time);
$d_year=date("Y", $time);

$datefr = $jour[$d_jour]." ".$d_day." ".$mois[$d_month]." ".$d_year; 
return $datefr  ;

}
///////////////////////////////////////////////////////////////////////////
?>