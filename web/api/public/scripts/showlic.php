<?php 
// header('Content-Type: text/html; charset=ISO-8859-1');
include_once '../../common/config.php';


?>
<center>
<div class="head_mini_gray_"><label style='display:none'></label></div>
<br>
<input type='button'  class="quitte"  value="Quitte"  onclick="javscript:quitte_showlic();" />
</center>
<br>
<div style="width:400px;height:400px;overflow: auto; margin-left: auto; margin-right: auto;text-align: center"  >
<?php

if(  !isset($_GET['ins']) ) {
	
echo "<label>Get error</label>";	
return;	
}




$tabcat=array("AV"=>"Avenirs","JE"=>"Jeunes","JU"=>"Juniors","SE"=>"Seniors","MA"=>"Masters");
$tabins=array("1"=>"inscrit(s)","-1"=>"non-inscrit(s)","0"=>"sans-réponse");


$tabdos=array("AV"=>"Dossier(s) Avenirs Validé(s)","JE"=>"Dossier(s) Jeunes Validé(s)","JU"=>"Dossier(s) Juniors Validé(s)","SE"=>"Dossier(s) Seniors Validé(s)","MA"=>"Dossier(s) Masters Validé(s)");


	
$ttot=true;	
$type=$_GET['type'];
$cat=$_GET['cat'];
$ins=$_GET['ins'];

$valide=false;

if( $type=="V"  ) {
$valide=true;	
	
$wh=" valide='1' ";
if ( $cat != 'null' ){
$ttot=false;	
$wh.=" AND categorie LIKE '$cat%' ";	
}	
	

}

else {


$wh=" inscription='$ins' ";
if( $ins == "0" )  { $wh .="  AND categorie IS NOT NULL "; }
if( $type != "null" ) {
$wh.=" AND type = '$type' ";
}
if ( $cat != 'null' ){
$ttot=false;	
$wh.=" AND categorie LIKE '$cat%' ";	
}


}




/////////////////////////////////////////////////



$mysqli = new mysqli ( $host , $base_user, $base_passwd, $base );


if ( $mysqli->connect_errno ) {
	($dev) ? $err=$mysqli->connect_error: $err="invalid connect";
	echo "error " ;
	return ;
	
}

 


$query="SELECT nom,prenom,type,categorie,rang ".
"FROM $tlicencies_encours WHERE  ".$wh.
"ORDER BY $tlicencies_encours.nom, $tlicencies_encours.prenom ";



$result = $mysqli->query( $query ) ;
if (!$result) {
    ($dev) ? $err=$mysqli->error: $err="invalid query";
    echo $err  ;
    return;
}  



echo "<br/>";
echo "<table style='width:100%'  class='tshowlic'  >";
$tab="";
$i=0;
while($row = $result->fetch_assoc() ) { 
	
$nom=utf8_encode($row['nom']);
$prenom=utf8_encode($row['prenom']);
$cate=strtoupper( $row['categorie'] );
$rang=$row['rang'];
    
if( empty($cate) ) continue;


$i++;







$tab.="<tr><td>$i</td><td>".$nom."</td><td>".$prenom."</td><td>".ucfirst($cate)."</td><td>".$rang."</td></tr>";

}

if( $ttot ) {
if( $valide )
echo "<label>".$i."&nbsp;&nbsp;Dossier(s) Validé(s)</label><br>";
else 
echo "<label>".$i."&nbsp;&nbsp;".$tabins[$ins]."</label><br>";

}
else {
if ( $valide )	
echo "<label>".$i."&nbsp;&nbsp;".$tabdos[$cat]."</label><br>";
else	
echo "<label>".$i."&nbsp;&nbsp;".$tabcat[$cat]."&nbsp;&nbsp;".$tabins[$ins]."</label><br>";


}

echo $tab;


?>
</table>
</div>
