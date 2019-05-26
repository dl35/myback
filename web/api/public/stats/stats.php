<?php

switch ($method) {
	
	case 'GET':
		getStats() ;
		break;
		
	default:
		setError( "invalides routes" ,404);
		break;
		
}



function getStats(){
	global $mysqli;
	$l =array();
	$l['enc'] = parseDatas(true);
	$l['last'] = parseDatas(false);
	
	$mysqli->close();
	
	echo json_encode($l);
	
}


function parseDatas($enc) {
	global $dev,$mysqli;
	global $tlicencies_encours , $tlicencies_last , $saison_enc , $saison_last;
	
	( $enc === true ) ? $table= $tlicencies_encours : $table= $tlicencies_last ;
	( $enc === true ) ? $saison= $saison_enc : $saison= $saison_last ;
	
	
	$query="SELECT categorie as c ,rang as r , sexe as s  FROM $table WHERE inscription ='1' and categorie IS NOT NULL ORDER BY categorie,rang,sexe  ";

	
	$result = $mysqli->query( $query )  ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
		setError( $err ,404 );
		return ;
	}
	
	
	$lic_encours=array();
	$lic_encours["tot"]=0;
	
	$lic_encours2=array();
	$lic_encours2["tot"]=0;
	
	
	
	while($r = $result->fetch_assoc() ) {
		
		$c=$r["c"];
		$rang=$r["r"];
		$sexe=strtolower($r["s"]);
		
		
		if( $c != "" )
		{
			
			$code=substr($c,0,2);
			$code=strtolower($code);
			
			
			if (  $code == "ma"    ) {
				
				if( ($rang =="C1" ) || ($rang =="C2" ) || ($rang =="C3" ) )
				{$rang="C3-"; }
				else { $rang="C4+"; }
				
				
			}
			
			
			
			if( isset( $lic_encours[$code]) )  $lic_encours[$code]+=1;
			else $lic_encours[$code]=1;
			
			
			$key=$code.$rang.$sexe;
			
			if( isset( $lic_encours2[$key]) )  $lic_encours2[$key]+=1;
			else $lic_encours2[$key]=1;
			
			
		}
		
		$lic_encours["tot"]+=1;
		$lic_encours2["tot"]+=1;
	}
	$lic_encours['saison']=$saison;
	$lic_encours2['saison']=$saison;
	
	$lic_encours['datas']=getDatas($lic_encours);
	
	
	
	return $lic_encours ;
	
}

function getDatas( $liste ){
	$val=array("av"=>"null","je"=>"null","ju"=>"null","se"=>"null","ma"=>"null");
	$datas=array();
	foreach ($liste as $k => $v ) {
		if( isset($val[$k]) ) 	$val[$k]=$liste[$k];
	}
	
	foreach ($val as $k =>$v ) {
		$datas[]=$v;
		
	}
	return $datas;
	
}


?>
