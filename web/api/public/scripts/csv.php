<?php
include_once '../../common/config.php';


//////////////////////////////////////////////////////////////////////////////////////

$mysqli = new mysqli ( $host , $base_user, $base_passwd, $base );


if ( $mysqli->connect_errno ) {
	$err=$mysqli->connect_error;
	echo "error " ;
	return ;
	
}



$query="SELECT * FROM $tlicencies_encours  WHERE  inscription = '1' ORDER BY nom,prenom ";

$row=array('nom','prenom','sexe','date','adresse','code_postal','ville','email','telephone','type','officiel','entr','categorie','rang','licence','lic_ffn','date_certmedical','carte','num_carte','especes','banque','cheque1','cheque2','cheque3','num_cheque1','num_cheque2','num_cheque3','ch_sport','num_sport','coup_sport','num_coupsport','cheque_vac','cotisation' ,'tarif' ,'date_inscription','date_valide','valide');
$res = $mysqli->query($query) ;

if (!$res) {
    $err=$mysqli->error;
    echo $err  ;
    return;
} 


$out="";
if ( $res->num_rows  != 0) {
  // titre des colonnes
  $fields = $res->field_count ;
  
  $i = 0;
  while ($i < $fields) {
	$name = mysqli_fetch_field_direct($res, $i)->name;

    if(   in_array( $name,$row,true)   ) {
    	 $out.=$name.";";
    }
    
    $i++;
  }
  $out.= "\n";
}


while ($arrSelect = $res->fetch_array(MYSQLI_ASSOC) ) {
	

	
   foreach($arrSelect as $key => $elem) {
   	
   	
	if( in_array( $key,$row,true)  ) {
		   
		$elem = utf8_encode($elem);
	 // if(  $elem == "" ) {
	/*  if( $key == "categorie"  && emty($arrSelect['officiel'] != ""   ) {
	
	  	$elem="officiels";
	  }*/
		
		if( $key == "entr" ) {
		if ($elem == "0") $elem="Non" ;
		else $elem="Oui";		
	
		
		}
		if( $key == "ville") {
			
		$elem=ltrim( $elem );
		$elem=ucfirst($elem);	
		
		}
			

		
	$out.= "$elem;";
	}
   	
   }
   $out.= "\n";
  }







header("Content-type: text/x-csv");
header("Content-Disposition: attachment; filename=licencies.csv");

echo $out;
?>
