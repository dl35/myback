<?php
header('Content-Type: text/html; charset=ISO-8859-1');

// session_start();
include_once '../../common/config.php';


//////////////////////////////////////////////////////////////////////////////////////

$mysqli = new mysqli ( $host , $base_user, $base_passwd, $base );


if ( $mysqli->connect_errno ) {
	($dev) ? $err=$mysqli->connect_error: $err="invalid connect";
	echo "error " ;
	return ;
	
}




$result = $mysqli->query("SELECT COUNT(*) AS count FROM $tlicencies_encours WHERE categorie IS NOT NULL  " );
$row = $result->fetch_assoc();
$count = $row['count']; 




$query="SELECT id,nom,prenom,type,categorie,rang,commentaires,paye,cotisation,fiche_medicale,cert_medical,auto_parentale,valide,date_valide,inscription,date_inscription ".
"FROM $tlicencies_encours WHERE categorie IS NOT NULL ".
"ORDER BY $tlicencies_encours.nom, $tlicencies_encours.prenom ";



$result = $mysqli->query( $query ) ;
if (!$result) {
    ($dev) ? $err=$mysqli->error: $err="invalid query";
    echo $err  ;
    return;
} 





$cpt_ren_ok=0;
$cpt_ren_ko=0;
$cpt_nouv=0;
$cpt_inc=0;

$stats =array( 

"AV" => array(  "N"=>0 , "R_ok"=>0 , "R_ko"=> 0 , "R_inc"=>0  ,"V"=>0  ),
"JE" => array(  "N"=>0 , "R_ok"=>0 , "R_ko"=> 0 , "R_inc"=>0 , "V"=>0 ),
"JU" => array(  "N"=>0 , "R_ok"=>0 , "R_ko"=> 0 , "R_inc"=>0 , "V"=>0),
"SE" => array(  "N"=>0 , "R_ok"=>0 , "R_ko"=> 0 , "R_inc"=>0 ,"V"=>0 ),
"MA" => array(  "N"=>0 , "R_ok"=>0 , "R_ko"=> 0 , "R_inc"=>0 ,"V"=>0 )

);

$total_ren=0;

$total_cotisation=0;

while($row = $result->fetch_assoc() ) { 
	

	
$nom=$row['nom'];

// cas du test Z_
$pos = strpos($nom, "Z_");
if( $pos !== false ) continue;   	
$valide=$row['valide'];

$type=$row['type'];
$rang=$row['rang'];
$categorie=$row['categorie'];
$inscription=$row['inscription'];
$cotisation=$row['cotisation'];


$total_cotisation +=$cotisation;


if( $categorie == "")  continue;


if( $type == "R") {
$total_ren++;
}
else if ( $type == "N" ) {
$cpt_nouv++;	
}





if( $inscription == '-1' ) 
{
$cpt_ren_ko++;
if( $type == "R" ) $stats["$categorie"] ["R_ko"] ++;

}

else  if( $inscription == '0' ){
$cpt_inc++;
$stats["$categorie"]["R_inc"]++	;




} 
else {
	
if( $valide =="1" ) {
	
$stats["$categorie"] ["V"] ++;	
}	
	
	
	
if( $type == "R" ) {
$cpt_ren_ok++;	
$stats["$categorie"] ["R_ok"] ++;

}

else {
$stats["$categorie"] ["N"] ++;	
}


}


	
}


function getTotal($indice , $all=false ) {
	
global $stats;
	
$cat =array("AV","JE","JU","JU","SE","MA");	
$total=0;	
foreach (  $cat as $val ) {

$total+=$stats["$val"]["$indice"];

if ($all ) {
$total+=$stats["$val"]["N"];	
}

}
	
return $total;

}



?>

<html>
<head>
<style type="text/css">

/*table {
border_: medium solid #000000;
border-collapse_: collapse;
width_: 100%;
}*/
th {
font-family: monospace;
font-size:14px;
padding: 5px;
background-color: #327E04;
color:#FFFFFF;
}
td , p{
font-family: monospace;
border: thin solid #000000;
font-size:12px;
padding: 5px;
text-align: center;
background-color: #ffffff;
font-weight: bold;
}
caption {
font-family: sans-serif;
}

.head_mini_gray_ {
	height: 30px;
	width: 600px;
	margin-left: auto ;
    margin-right: auto ;
    left:0;
    right:0;
  background-image:url(../ressources/images/banner_mini_gray.png);
 
}

.quitte {
background-color: #2f54b5 ; 
color: white;
padding:0.15em;
width: 200px;
height:25px;
border:1px solid #ddd;
	
font:bold 11px arial, sans-serif;
-moz-border-radius:0.4em;
-khtml-border-radius:0.4em;

}


</style>
<script src="../ressources/js/jquery-1.4.2.min.js" type="text/javascript"></script>

<script type="text/javascript">

function quitte() {
	$("#wait").html(""); 
	$("#wait").hide();
	$("#div_lic").show();
	
}

function quitte_showlic() {
	$("#showlic").html("");
	$("#stats").show();
		
}

function show_lic( cat , type , ins) {
	var uniqueId=(new Date).getTime();
	var url= "showlic.php?ins="+ins+"&cat="+cat+"&type="+type+"&"+uniqueId;
	if ( $("#stats").length ) {$("#stats").hide();}
	$("#showlic").load(url);
}


</script>
</head>
<body>
<br></br>
<div id="stats"  >
<?php    if ( ! isset($_GET['include'] )  ) {?>

<div class="head_mini_gray_"><label style='display:none'>ins</label></div>

<br></br>
<?php   } else { ?>

<input type='button'  class="quitte"  value="Quitter"  onclick="javscript:quitte();" />
<?php }?>
<table  align="center">
<tr><td>
<table BORDER>
	<tr>
		<th COLSPAN=1></th>
		<th COLSPAN=7><?php echo ucfirst($saison) ?></th>
	</tr>
	<tr>
		<th>Catégories</th><th>Total</th><th>Renouvellement</th><th>%</th><th>Non-Renouvellement</th><th>%</th><th>Pas de réponse</th><th>%</th> 
	
	 
	</TR>
	<tr>
<?php $total=$stats['AV']["R_ok"]+$stats['AV']["R_ko"]+$stats['AV']["R_inc"] ;
$tot=$total;
?>
		<td>Avenirs</td><td><?php echo $total  ?></td><td style="cursor: pointer"  onclick="javascript:show_lic('AV','R','1')" ><?php echo  $stats['AV']["R_ok"] ?></td><td><?php echo round ( ($stats['AV']["R_ok"]* 100) /$total ) ?></td><td style="cursor: pointer"  onclick="javascript:show_lic('AV','R','-1')" ><?php echo  $stats['AV']["R_ko"] ?></td><td><?php echo round ( ($stats['AV']["R_ko"]* 100) /$total ) ?></td><td style="cursor: pointer" onclick="javascript:show_lic('AV','R','0')"  ><?php echo  $stats['AV']["R_inc"] ?></td><td><?php echo round ( ($stats['AV']["R_inc"]* 100) /$total ) ?></td>
	</tr>

	<tr>
<?php $total=$stats['JE']["R_ok"]+$stats['JE']["R_ko"]+$stats['JE']["R_inc"] ; 
$tot+=$total;
?>
		<td>Jeunes</td><td><?php echo $total  ?></td><td style="cursor: pointer"  onclick="javascript:show_lic('je','R','1')" ><?php echo  $stats['JE']["R_ok"] ?></td><td><?php echo round ( ($stats['JE']["R_ok"]* 100) /$total ) ?></td><td style="cursor: pointer" onclick="javascript:show_lic('JE','R','-1')" ><?php echo  $stats['JE']["R_ko"] ?></td><td><?php echo round ( ($stats['JE']["R_ko"]* 100) /$total ) ?></td><td style="cursor: pointer" onclick="javascript:show_lic('JE','R','0')" > <?php echo  $stats['JE']["R_inc"] ?></td><td><?php echo round ( ($stats['JE']["R_inc"]* 100) /$total ) ?></td>
		
	</tr>


		<tr>
<?php $total=$stats['JU']["R_ok"]+$stats['JU']["R_ko"]+$stats['JU']["R_inc"] ; 
$tot+=$total;
?>
		<td>Juniors</td><td><?php echo $total  ?></td><td style="cursor: pointer"  onclick="javascript:show_lic('JU','R','1')" ><?php echo  $stats['JU']["R_ok"] ?></td><td><?php echo round ( ($stats['JU']["R_ok"]* 100) /$total )  ?></td><td style="cursor: pointer" onclick="javascript:show_lic('JU','R','-1')" ><?php echo  $stats['JU']["R_ko"] ?></td><td><?php echo round ( ($stats['JU']["R_ko"]* 100) /$total ) ?></td><td style="cursor: pointer" onclick="javascript:show_lic('JU','R','0')" ><?php echo  $stats['JU']["R_inc"] ?></td><td><?php echo round ( ($stats['JU']["R_inc"]* 100) /$total ) ?></td>
	</tr>	
	
			<tr>
<?php $total=$stats['SE']["R_ok"]+$stats['SE']["R_ko"]+$stats['SE']["R_inc"] ;
$tot+=$total;
?>
		<td>Seniors</td><td><?php echo $total  ?></td><td style="cursor: pointer"  onclick="javascript:show_lic('SE','R','1')" ><?php echo  $stats['SE']["R_ok"] ?></td><td><?php echo  round( ($stats['SE']["R_ok"]* 100) /$total )  ?></td><td style="cursor: pointer" onclick="javascript:show_lic('SE','R','-1')" ><?php echo  $stats['SE']["R_ko"] ?></td><td><?php echo round ( ($stats['SE']["R_ko"]* 100) /$total ) ?></td><td style="cursor: pointer" onclick="javascript:show_lic('SE','R','0')" ><?php echo  $stats['SE']["R_inc"] ?></td><td><?php echo round ( ($stats['SE']["R_inc"]* 100) /$total ) ?></td>
	</tr>

			<tr>
<?php $total=$stats['MA']["R_ok"]+$stats['MA']["R_ko"]+$stats['MA']["R_inc"] ;
$tot+=$total;
?>
		<td>Masters</td><td><?php echo $total  ?></td><td style="cursor: pointer"  onclick="javascript:show_lic('MA','R','1')" ><?php echo  $stats['MA']["R_ok"] ?></td><td><?php echo  round ( ($stats['MA']["R_ok"]* 100)  /$total ) ?></td><td style="cursor: pointer" onclick="javascript:show_lic('MA','R','-1')" ><?php echo  $stats['MA']["R_ko"] ?></td><td><?php echo round ( ($stats['MA']["R_ko"]* 100) /$total ) ?></td><td style="cursor: pointer" onclick="javascript:show_lic('MA','R','0')" ><?php echo  $stats['MA']["R_inc"] ?></td><td><?php echo round ( ($stats['MA']["R_inc"]* 100) /$total ) ?></td>
	</tr>

			<tr>
		<td>Total</td><td><?php echo $tot  ?></td><td style="cursor: pointer"  onclick="javascript:show_lic(null,'R','1')" ><?php echo getTotal("R_ok") ?></td><td><?php echo  round ( (getTotal("R_ok") * 100) /$tot  )  ?></td><td style="cursor: pointer" onclick="javascript:show_lic(null,'R','-1')" ><?php echo   getTotal("R_ko") ?></td><td><?php echo round ( (getTotal("R_ko")* 100) /$tot ) ?></td><td style="cursor: pointer" onclick="javascript:show_lic(null,'R','0')" ><?php echo  getTotal("R_inc") ?></td><td><?php echo round ( (getTotal("R_inc")* 100) /$tot  ) ?></td>
	</tr>



</table>
</td>
<td>
<table BORDER>
	<tr>
		<th>Nouveaux</th>
	</tr>
	<tr>
		<th>Licenciés</th> 
	</TR>
<?php  ?>
		<tr><td onclick="javascript:show_lic('AV','N','1')"  style="cursor: pointer"   ><?php echo $stats['AV']["N"]  ?></td></tr>
		<tr><td onclick="javascript:show_lic('JE','N','1')" style="cursor: pointer"  ><?php echo  $stats['JE']["N"] ?></td></tr>
		<tr><td onclick="javascript:show_lic('JU','N','1')" style="cursor: pointer"  ><?php echo  $stats['JU']["N"] ?></td></tr>
		<tr><td onclick="javascript:show_lic('SE','N','1')" style="cursor: pointer"  ><?php echo  $stats['SE']["N"] ?></td></tr>
		<tr><td onclick="javascript:show_lic('MA','N','1')" style="cursor: pointer"  ><?php echo  $stats['MA']["N"] ?></td></tr>
		<tr><td onclick="javascript:show_lic(null,'N','1')" style="cursor: pointer"  ><?php echo  getTotal("N") ?></td></tr>
</table>
</td>

<td>
<table BORDER>
	<tr>
		<th colspan="2">Validation</th>
	</tr>
	<tr>
		<th>Licenciés</th><th>%</th>  
	</TR>
<?php $total=$stats['AV']["R_ok"]+$stats['AV']["N"] ;$tot=$total; ?>
		<tr><td onclick="javascript:show_lic('AV','V','1')"  style="cursor: pointer"   ><?php echo $stats['AV']["V"]  ?></td><td><?php  if($total == 0 ) echo "0";else   echo  round ( ($stats['AV']["V"]* 100)  /$total ) ?></td></tr>
<?php $total=$stats['JE']["R_ok"]+$stats['JE']["N"] ;$tot+=$total; ?>		
		<tr><td onclick="javascript:show_lic('JE','V','1')" style="cursor: pointer"  ><?php echo  $stats['JE']["V"] ?></td><td><?php if($total == 0 ) echo "0";else echo  round ( ($stats['JE']["V"]* 100)  /$total ) ?></td></tr>
<?php $total=$stats['JU']["R_ok"]+$stats['JU']["N"] ; $tot+=$total;?>		
		<tr><td onclick="javascript:show_lic('JU','V','1')" style="cursor: pointer"  ><?php echo  $stats['JU']["V"] ?></td><td><?php if($total == 0 ) echo "0";else echo  round ( ($stats['JU']["V"]* 100)  /$total ) ?></td></tr>
<?php $total=$stats['SE']["R_ok"]+$stats['SE']["N"] ; $tot+=$total;?>		
		<tr><td onclick="javascript:show_lic('SE','V','1')" style="cursor: pointer"  ><?php echo  $stats['SE']["V"] ?></td><td><?php  if($total == 0 ) echo "0";else    echo  round ( ($stats['SE']["V"]* 100)  /$total ) ?></td></tr>
<?php $total=$stats['MA']["R_ok"]+$stats['MA']["N"] ; $tot+=$total;?>		
		<tr><td onclick="javascript:show_lic('MA','V','1')" style="cursor: pointer"  ><?php echo  $stats['MA']["V"] ?></td><td><?php   if($total == 0 ) echo "0";else    echo  round ( ($stats['MA']["V"]* 100)  /$total ) ?></td></tr>
		
		<tr><td onclick="javascript:show_lic(null,'V','1')" style="cursor: pointer"  ><?php echo  getTotal("V") ?></td><td><?php   if($total == 0 ) echo "0";else     echo  round ( (getTotal("V")* 100)  /$tot )   ?></td></tr>
</table>
</td>

<td>
</td>

<td>
<table BORDER>
	<tr>
		<th>Total</th>
	</tr>
	<tr>
		<th><?php echo $saison ?></th> 
	</TR>

		<tr><td onclick="javascript:show_lic('AV',null,'1')"  style="cursor: pointer" ><?php echo $stats['AV']["R_ok"]+$stats['AV']["N"]  ?></td></tr>
		<tr><td onclick="javascript:show_lic('JE',null,'1')"  style="cursor: pointer" ><?php echo $stats['JE']["R_ok"]+ $stats['JE']["N"] ?></td></tr>
		<tr><td onclick="javascript:show_lic('JU',null,'1')"  style="cursor: pointer" ><?php echo  $stats['JU']["R_ok"]+$stats['JU']["N"] ?></td></tr>
		<tr><td onclick="javascript:show_lic('SE',null,'1')"  style="cursor: pointer" ><?php echo  $stats['SE']["R_ok"]+$stats['SE']["N"] ?></td></tr>
		<tr><td onclick="javascript:show_lic('MA',null,'1')"  style="cursor: pointer" ><?php echo  $stats['MA']["R_ok"]+$stats['MA']["N"] ?></td></tr>
		<tr><td onclick="javascript:show_lic(null,null,'1')"  style="cursor: pointer" ><?php echo    getTotal("R_ok" , true ) ?></td></tr>
</table>

</td>
</tr>


</table>
<table style="width:100%">
<tr><td>Total cotisations  :  <?php echo $total_cotisation ;?> Euro(s)</td></tr>
</table>
</div>
<div id="showlic" onclick="quitte_showlic()" style="text-align: center;margin-left: auto;margin-right:auto;width:1000px;" >
</div>

</body>
</html>

