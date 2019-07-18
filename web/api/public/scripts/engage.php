<?php 
include_once '../../common/config.php';
?>


<html>
<head>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- Compiled and minified CSS -->
<link type="text/css" rel="stylesheet" href="materialize/css/materialize.min.css">

<style>
.with-gap[type="radio"]:checked+label:after{
   background-color: #ff9800;
   border-color: #ff9800;
} 

.toastred {
background-color:red;
}
.toastgreen {
background-color:green;
}

.logo{
max-width:100px;
margin-right:15px;
margin-top:15px;
border: 2px solid green;
_loat: right;
}


</style>
        
</head>

<body>
<script type="text/javascript" src="materialize/js/materialize.min.js"></script>
<script type = "text/javascript"   src = "js/jquery-2.1.1.min.js"></script>   
<?php

if ( !isset($_GET['eng'])  || !isset($_GET['id'] )  ) {
?>
   <div class="card-panel teal lighten-2">erreurs de paramètres!</div>


<?
 } else {

$id=$_GET['id'];
$eng=$_GET['eng'];
$d= getEngagement($eng , $id );

if( isset($d['error'])) {

 ?> 
  <div class="row">
  <div class="card-panel green lighten-5 red-text"><? echo $d['error'] ?>
  
  </div>
  <div class="center-align" >
  <button class="btn green darken-3 waves-effect waves-light" onClick="javascript:toecn();" >Quitter
  <i class="material-icons right">send</i>
  </button>
  </div>
  </div>
  </body>
  </html>
<?
 return ; 
}



if ( isset($d['engage']) ) {
  $datengage = $d['engage'];
} else {
  $datengage = array();
}


$choixnages = $d['choixnages'];
$message = $d['message'];
$nages = $d['commentaire'];
$datereponse = $d['datereponse'];
if( !empty($datereponse) ) {
  $dr = new DateTime( $datereponse );
  $datereponse = $dr->format('l j F Y à H:i:s ');
  $datereponse = formatFr($datereponse);

}

$stage =false;
if( isset($d['stage']) ) {
  $stage =true ;
}



?>



  <div class="row">
    <div class="col s12 m8 offset-m2">
      <div class="card green darken-3">
        <div class="card-content white-text">
          <span class="card-title">
          <img width="75px"   height="75px" style="border-radius:50%;float:right;vertical-align:middle;" src="img/logo.jpg">
          </span>
          <? echo $message ;
    if( !empty($datereponse) ) {
      echo "<br>Votre dernière validation: ".$datereponse ;
      }
    ?>
        </div>
        
      </div>
    </div>
  </div>






<?
if( count($datengage) == 0 ) {
?>
  <div class="center-align" >
  <button class="btn green darken-3 waves-effect waves-light" onClick="javascript:toecn();" >Quitter
  <i class="material-icons right">exit_to_app</i>
  </button>
  </div>
  </body>
  </html>
<?
return ;}

?>


<form style="margin-top:10px;">

<input type="hidden" name="id"  value="<? echo $id?>" />
<input type="hidden" name="eng" value="<? echo $eng?>" />

<? 
foreach( $datengage as $v ) {
$j = $v['date'] ;
$p = $v['presence'] ;
$ide = $v['id'] ;

$date = new DateTime( $j );
$jfr = $date->format('l j F Y');
$jfr = formatFr($jfr);

if( $stage ) {
$jfr = "Début du stage ".$jfr ;

}



if ( $p == "oui" ) {
  $chkoui = "checked";
  $chknon = "";
} else if ( $p == "non" ) {
  $chkoui = "";
  $chknon = "checked";
} else {
  $chkoui = "";
  $chknon = "";

}


?>

<div class="row">
    <div class="col s12 m6  offset-m3">
      <div class="card green lighten-3">
   
        <div class="card-content black-text">
          <span class="card-title" style="vertical-align:middle" ><? echo $jfr ?>
          <img width="35px"   height="35px" style="float:right;vertical-align:middle;border-radius:50%" src="img/logo.jpg">
          </span>
          <p>Valider votre engagement
          </p>
          <div class="card-action">
      <label>
        <input class="with-gap" name="<?echo $ide?>" value="oui" type="radio" <? echo $chkoui ?> />
        <span class="black-text" >Oui</span>
      
      </label>
      &nbsp;
      <label>
        <input class="with-gap" name="<?echo $ide?>" value="non" type="radio" <? echo $chknon ?> />
        <span class="black-text" >Non</span>
      </label>
      
      
      </div>
    
        </div>
     
      </div>
    </div>
  </div> 
<?
} 
if ( $choixnages == '1' ) {
?>

   
      <div class="row">
        <div class="input-field col s6 offset-s3 black-text">
          <textarea id="nages" name="nages" class="materialize-textarea black-text" ><? echo $nages ?></textarea>
          <label class="black-text" for="nages" >Vous pouvez choisir vos nages...</label>
        </div>
      </div>
<?
}
?>


    </form>
  </div>

  <div class="center-align" >
  <button class="btn green darken-3 waves-effect waves-light" onClick="javascript:save();" >Valider
  <i class="material-icons right">send</i>
  </button>
  </div>
  


<?
  }
?>




        

</body>
<script type="text/javascript"  >

function isChecked() {
  var check = true;
  $("input:radio").each(function(){
  var name = $(this).attr("name");
  if($("input:radio[name="+name+"]:checked").length == 0){
  check = false;
         }
        });

 return check;       
}

function toecn() {
window.location.href="http://www.ecnatation.fr";

}



function save() {
 // var form = $(this);
if( !isChecked() ) {
  M.toast({html: 'Vous devez confimer votre présence pour chaque date!' , classes: 'toastred' });
  return ;
}

 
  var datas= $("form").serialize()  ;
  var url = "engage_ajax.php";

    $.ajax({
           type: "POST",
           url: url,
           data: datas,
           success: function(data)
           {
            var v = data.message ;
            M.toast({html: v ,displayLength: 1500 , completeCallback: function(){ toecn() }  ,  classes: 'toastgreen' });    
           },
           error: function(data)
           {
               var err = data.responseJSON.message;
               M.toast({html: err , classes: 'toastred' });    
           },


         });


}


</script>

</html>

<?

function formatFr($format) {
  $english_days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
  $french_days = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
  $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'Décember');
  $french_months = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
  return str_replace($english_months, $french_months, str_replace($english_days, $french_days, $format));
}


function getEngagement ($id,$code ) {
	global $dev ,$host , $base_user, $base_passwd, $base ;
	global $tcompetitions,$tengagements,$tengage_date,$tlicencies;
	$mysqli = new mysqli ( $host , $base_user, $base_passwd, $base );


  if ( $mysqli->connect_errno ) {
    ($dev) ? $err=$mysqli->connect_error: $err="invalid connect";
    $error["error"] = $err;
    return  $error ;
        
  }












	
	$query = "SELECT  id_competitions,id_licencies,commentaire,date_reponse  FROM $tengagements  WHERE id = '$id' AND id_licencies = '$code' ";
	
	
	$result = $mysqli->query( $query ) ;
	if (!$result) {
		http_response_code(404);
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
	  $error["error"] = $err;
    return $error ;
    	}
	
	
	
	$num = $result->num_rows ;
	if( $num === 0 ) {
		$err="cet engagement est inconnu !";
	  $error["error"] = $err;
    return $error ;
    	}
	
	while($r = $result->fetch_assoc() ) {
		
		$idc= $r['id_competitions'];
		$idl= $r['id_licencies'];
    $comment = $r['commentaire'];
    $datereponse = $r['date_reponse'];
    
		
	}
	
	$query = "SELECT  id , date , presence   FROM $tengage_date  WHERE id_engage = '$id'  ";
	
	
	
	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
    $error["error"] = $err;
    return $error ;
    
	}
	
	$rows = array();
	while($r = $result->fetch_assoc() ) {
		$rows[] = $r;
	}
	$res['engage']=$rows;
	
	
	
	// competitions
	$query = "SELECT nom,lieu,debut,fin,limite,type,choixnages,lien,max   FROM $tcompetitions  WHERE id = '$idc'  ";
	

	
	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
    $error["error"] = $err;
    return $error ;
    
	}
	
	$rows = array();
	while($r = $result->fetch_assoc() ) {
		$rows['nom']=utf8_encode($r['nom']);
		$rows['lieu']=utf8_encode($r['lieu']);
		$rows['type']=utf8_encode($r['type']);
		$rows['debut']=new DateTime($r['debut']);
		$rows['fin']=new DateTime($r['fin']);
		$rows['limite']=new DateTime($r['limite']);
		$rows['choixnages']= $r['choixnages'];
		$rows['lien']= utf8_encode( $r['lien'] );
		$rows['max']= utf8_encode( $r['max'] );
	}
	
	
	
	$now =new DateTime();
	$limite = $rows['limite'] ;
	
	
	
	( $now  <= $limite  ) ? $valide=true : $valide=false ;
  
  
	$days=array("","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");
	$months=array("","Janvier","Fevrier","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre");
	
	
	
	$debut = $days[$rows['debut']->format('N') ] ." ". $rows['debut']->format('j') ." ". $months[$rows['debut']->format('n') ] ." ". $rows['debut']->format('Y')  ;
	$fin= $days[$rows['fin']->format('N') ] ." ". $rows['fin']->format('j') ." ". $months[$rows['fin']->format('n') ] ." ". $rows['fin']->format('Y')  ;
	$limite= $days[$rows['limite']->format('N') ] ." ". $rows['limite']->format('j') ." ". $months[$rows['limite']->format('n') ] ." ". $rows['limite']->format('Y')  ;
	
	
	
	
	$message = "" ;
	( $rows['type'] === "compet"  ) ? $message="La compétition " : $message="Le stage " ;
	$message.= "'".$rows['nom']."' se déroulera ";
	

	
	if( $rows['debut'] !=  $rows['fin'])
	{
		$message.= "du ".$debut ." au " .$fin ;
	}
	else {
		$message.= "le ".$debut ;
	}
  $message.= " à ".$rows['lieu'] . "."  ;
  $message.= "<br>La date limite de réponse est le ".$limite."." ;
  if ( $rows['type'] === "stage"  ) {
    $message .="<br>Le nombre de participants est limité à ".$rows['max']."." ;
  }
	
	if( $valide === false )
	{
		$message="La date limite pour les réponses est dépassée: ".$limite.".";
		unset($res['engage']);
	}
	
	if( $rows['choixnages'] === '1' ) {
		$res['choixnages']= true ;
	} else {
		$res['choixnages']= false ;
	}

	
  $res['lien']= $rows['lien'];
  
  if( $rows['type'] === "stage" )  {
    $res['stage']= true;
  }
  
	
	

	$res['message']=( $message );
  $res['commentaire']=( $comment );
  $res['datereponse']=( $datereponse );
	
	//header("Content-type:application/json");
	return $res ;
	
	
}

?>