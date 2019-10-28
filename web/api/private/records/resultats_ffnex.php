<?php
//////////////////////////////////////////////////
$code_ecn="50503501000";
/////////////////////////////////////////////////

$root=$_SERVER["DOCUMENT_ROOT"];
$directory= $root."/upload/" ;
$file=$directory.$filename.".xml";

$file =  "ffnex_performances_59385.xml";

//////////////////////////////////////////////////////////////////////

$exist=file_exists ( $file ) ;

if( !$exist  ) {
$info=utf8_encode("le fichier est indisponible !");
echo $info;
return;		
}
$xml = simplexml_load_file( $file );
$isecn=readClub( $xml ) ;
if( $isecn === false ) {
$info=utf8_encode("la compétition de contient pas de nageurs de ECN Chartres de Bretagne");
echo $info;
return;	
}

$master=isMaster($xml);
$codenages= getCodesNages() ;
$compet=getCompetition( $xml );
$year_compet=$compet["year"];
$swimmers= getSwimmers( $xml ,$isecn,$year_compet ) ;




$resultats=array();
/////////////////////////////////////////////////////////////
getResultats($xml , $isecn  , $codenages ,$swimmers,$master );




//////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////
function yeartoage($year , $year_compet) {
	
$y=date("Y");
// pout les competitions Y-1
$delta=$y-$year_compet;

$age=$y-$year-$delta;	


if( $age < 18 )	return $age;
if( $age >= 18 && $age <25 ) { return 18;}
if( $age >= 25 && $age <30 ) return "C1";
if( $age >= 30 && $age <35 ) return "C2";
if( $age >= 35 && $age <40 ) return "C3";
if( $age >= 40 && $age <45 ) return "C4";
if( $age >= 45 && $age <50 ) return "C5";
if( $age >= 50 && $age <55 ) return "C6";
if( $age >= 55 && $age <60 ) return "C7";
if( $age >= 60 && $age <65 ) return "C8";
if( $age >= 65 && $age <70 ) return "C9";
if( $age >= 70 && $age <75 ) return "C10";
if( $age >= 75 && $age <80 ) return "C11";
if( $age >= 80 && $age <85 ) return "C12"; 
if( $age >= 85 && $age <90 ) return "C13";
if( $age >= 90 && $age <95 ) return "C14";
if( $age >= 95 ) return "C15";	
	
}
/////////////////////////////////////////////////////////////////////////
function isRelais($code) {
$relais=array("08","47","43","44","49","09","45","39","48","9","46","58","97","93","94","99","59","95","89","98","96","49","87","37","84")	;
return in_array($code, $relais);
}
/////////////////////////////////////////////  � impl�menter
function isRelaisMixte($code) {
$relais=array("87","37","84")	;
}
/////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////// 
function readClub( $xml ) {
global 	$code_ecn;
//050353986
// recherche le code ecn dans le document 
$xpath="//CLUB[@code='$code_ecn']";
$result = $xml->xpath( $xpath );
if ( $result ) return  (string) $result[0]['id'] ;
else  return false ;
}
///////////////////////////////////////////////////////////////////////
function isMaster( $xml ) {
//discipline id == 3  code masters
$xpath="//MEETS/MEET[@disciplineid='3']";
$result = $xml->xpath( $xpath );
if ( $result ) return true;
else return false; 
}
//////////////////////////////////////////////////////////////////////
function getCompetition( $xml ) {
$compet=array();

$xpath="//POOL";
$result = $xml->xpath( $xpath );
$compet['bassin']=(string)$result[0]['size'];

$xpath="//MEETS/MEET";
$result = $xml->xpath( $xpath );
foreach ($result  as $meet ) {
	$compet['name']=(string)$meet['name'];
	$compet['city']=(string)$meet['city'];
	$compet['startdate']=(string)$meet['startdate'];
	$d=(string)$meet['startdate'];
	$compet['year']=substr($d ,0 ,4);
}
return $compet;
}
/////////////////////////////////////////////////////////////
function getCodesRelais( ) {
$nages=array();
$codes=array( "111" => "DOS","121"=> "BRA","131"=> "PAP","48"=> "4N","47"=> "NL","43"=> "NL","44"=> "NL","46"=> "4N","9"=>"NL","87"=>"NL","37"=>"4N","84"=>"NL");	
$dist=array(  "111" => "4x50","121"=> "4x50","131"=>"4x50","48"=> "4x50","47"=> "4x50","43"=> "4x100","44"=> "4x200","46"=> "4x100","9"=>"10x50","87"=>"4x50","37"=>"4x50","84"=>"10x50");	
	foreach ( $codes  as $k =>$v ) {
		
		//cas particuliers : relais mixte
		if( $k == "87" || $k == "37" || $k== "84" ) {
		$tnage["nage"]=$v;
		$tnage["sexe"]="MI";
		$tnage["distance"]=$dist[$k];
	
		
		$nages[$k]=$tnage;	

		continue;	
		}
		
		$tnage["nage"]=$v;
		$tnage["sexe"]="F";
		$tnage["distance"]=$dist[$k];
		
		$nages[$k]=$tnage;
		
		$tnageh["nage"]=$v;
		$tnageh["sexe"]="H";
		$tnageh["distance"]=$dist[$k];
		
		$kh=$k+50;
		$nages[$kh]=$tnageh;
									}
		return $nages ;
		
	}
///////////////////////////////////////////////////////////////////
function getCodesNages( ) {
	$nages=array();
$codes=array( "100" => "NL","1"=> "NL","2"=> "NL","3"=> "NL","4"=> "NL","5"=> "NL","6"=> "NL","7"=> "NL","110"=> "DOS","11"=> "DOS","12"=> "DOS","13"=> "DOS","120"=>"BRA","21"=>"BRA","22"=>"BRA","23"=>"BRA","130"=>"PAP","31"=>"PAP","32"=>"PAP","33"=>"PAP","40"=>"4N","41"=>"4N","42"=>"4N"  );	
$dist=array( "100" => "25","1"=> "50","2"=> "100","3"=> "200","4"=> "400","5"=> "800","6"=> "1500","7"=> "1000","110"=> "25","11"=> "50","12"=> "100","13"=> "200","120"=>"25","21"=>"50","22"=>"100","23"=>"200","130"=>"25","31"=>"50","32"=>"100","33"=>"200","40"=>"100","41"=>"200","42"=>"400"  );	

	foreach ( $codes  as $k =>$v ) {
		$tnage["nage"]=$v;
		$tnage["sexe"]="F";
		$tnage["distance"]=$dist[$k];
		$nages[$k]=$tnage;
		$tnageh["nage"]=$v;
		$tnageh["sexe"]="H";
		$tnageh["distance"]=$dist[$k];
		
		$kh=$k+50;
		$nages[$kh]=$tnageh;

		// cas des nages mixtes	
	    $tnagem["nage"]=$v;
		$tnagem["sexe"]="M";
		$tnagem["distance"]=$dist[$k];
		
		$km=$k+200;
		$nages[$km]=$tnagem;
									}
		return $nages ;
		
	}
////////////////////////////////////////////////////////////////////
function readLicence( $file ) {
//FRA505035398692014FSIMON Florent M25061992FRA O 0510201110102011RN 482083 	
//code	       ;     ;NOM  Prenom  sexeDateNaissanceFRA .... Licence  ;     
global 	$code_ecn;

$nageurs=array();
$lines = file( $file);
foreach ($lines as $line_num => $line) {
	$pos = strpos( $line , $code_ecn    );
	
	if( $pos !== false ) {
		
		
					$key = substr($line,0,19);
					
					$year=substr($line,69,4);
					$age=yeartoage( $year );
				
				
					
					$nom= substr($line,19,25);
					$pnom= substr($line,44,20);

					
					//$line = substr($line,19,45);
					
					$d=array();
					$d['nom']=trim($nom);
					$d['prenom']=trim($pnom);
					$d['age']=$age;
					
					$nageurs[$key]=$d;
	                 }
										}

					return $nageurs;
}
//////////////////////////////////////////////////////////////////////////
function isvide( $v ) {
return (trim($v) !== "" );
}
////////////////////////////////////////////////////////////////////////
function getSwimmers($xml , $clubid , $year_compet) {
	
$swimmers=array();	
$xpath="//SWIMMERS/SWIMMER[@clubid='$clubid']";

$result = $xml->xpath( $xpath );


		foreach( $result  as $swimmer ) {
			
			$id=(string)$swimmer['id'];
			$name=(string)$swimmer['lastname'];
			$firstname=(string)$swimmer['firstname'];
			$gender=(string)$swimmer['gender'];
			$birthdate=(string)$swimmer['birthdate'];
			$b = explode("-", $birthdate);
			$age=yeartoage($b[0] , $year_compet);
			$tswim['nom']=$name;
			$tswim['prenom']=$firstname;
		
			if( $gender == 'M' ) $gender='H';
			$tswim['sexe']=$gender;
			$tswim['age']=$age;
			
			  $swimmers[$id]=$tswim;
	
				}

	return $swimmers;
	
}
/////////////////////////////////////////////////////////////////////////
function traite4Nages( $res , $nageurs ,$d) {
$r=array();
// on tratite que le 200 et 400 4 nages pour les temps intermediaires
if( $d == 200 ) {
$dnage=array("50");	
}	
else if( $d == 400 ){	
$dnage=array("50","100");
}
else {
return $r;	
}


	foreach ($res->xpath( "SPLITS/SPLIT" )  as $split ) {

		    	
		      $time=(string)$split['swimtime'];
			  $dist=(string)$split['distance'];

			 
			///  if( $dist == $nage['distance'] ) continue;
			  if( in_array( $dist , $dnage  ) )
			  {
			      $nageurs['points']="-1";
			      $nageurs['temps']=$time;
				  $nageurs['inter']=1;
				  $nageurs['distance']=$dist ;
				  $nageurs['nage']="PAP";
				   
			      $r[]=$nageurs;
			  }
			   
			   
			   
		    }

		  return $r;  
		    
}
/////////////////////////////////////////////////////////////////////
function traiteNage( $res , $swimmers , $codenages  ) {
	
global $resultats;		
$dnage=array("50","100","200","400","800");


			$temps=(string)$res['swimtime'];
			$points=(string)$res['points'];
			$race=(string)$res['raceid'];
	
  foreach ( $res->SOLO  as $solo ) {
			 $id =(string)$solo['swimmerid'];

		      $nageurs =  $swimmers[$id];
		      
		     //  if ( !isset( $codenages[$race] ) ) continue;
		      $nage    =  $codenages[$race];                   

		      $d=$nage['distance'];
		      
		      $nageurs['nage']=$nage['nage'];
		      $nageurs['distance']=$nage['distance'];
		      $nageurs['points']=$points;
		      $nageurs['temps']=$temps;
			  $nageurs['inter']=0;
			  $nageurs['ref']=$nage['nage']."(".$nage['distance'].")";
		      $resultats[]=$nageurs;
			

		  
		    if( $nage['nage'] == "4N") {


		    	// voir le traitement ....
		     $r=traite4Nages($res,$nageurs,$d);
		     
		     foreach($r as $v ) {
		     	
		     	$resultats[]=$v;
		     }
		     
		   
		   	continue; 

		    
		    }
		      
		    
		    
		    foreach ($res->xpath( "SPLITS/SPLIT" )  as $split ) {

		    	
		      $time=(string)$split['swimtime'];
			  $dist=(string)$split['distance'];

			  
			  // temps final....
			  if( $dist == $nage['distance'] ) continue;
			  
			
			 
			
				
			  if( in_array( $dist , $dnage  ) )
			  {
			      $nageurs['points']="-1";
			      $nageurs['temps']=$time;
				  $nageurs['inter']=1;
				  $nageurs['distance']=$dist ;
				  
			      $resultats[]=$nageurs;
			      
			  }
			   
		    }
		     }
		     
}

/////////////////////////////////////////////////////////////////////////////
function getAgeRelaisMasters($key){

$rage=array("100-119"=>"R1","120-159"=>"R2","160-199"=>"R3" ,"200-239"=>"R4",
"240-279"=>"R5","280-319"=>"R6","320-359"=>"R7","360-999"=>"R8");

if ( isset( $rage[ $key ] ) ) {
return 	$rage[ $key ] ;
}
else return false;
}
////////////////////////////////////////////////////////////////////////////
function getAgeGroups($res) {

	$masters=isMaster($res);
	
	$ages=array();	
		foreach($res->xpath( "//AGEGROUPS/AGEGROUP" ) as $age ) {
			
					
			$id=(string)$age['id'];
			
		
			
			$min=(string)$age['agemin'];
			$max=(string)$age['agemax'];
			if( !$masters ) {
			    if( $min == "0" )  $min="7";
		        if( $max == "999" ) $max="18";
			}	
		
			$ages[$id]['min']=$min;
			$ages[$id]['max']=$max;
			$ages[$id]['code']=$max;
			
		}

	
	
		return $ages;

}
////////////////////////////////////////////////////////////////////////
function traiteRelaisMasters($res , $swimmers , $agegroup  ) {

global $resultats;	



$coderelais=getCodesRelais();

//87 et 37 et 84 relais mixte
$relay_rec=array("47","97","87","37","39","89","84");
//("43","93","44","94","46","96","9","59","87","37");

// pour les record des relais :
// 4x100;4x200;10x100	NL
// 4x100 4N

//pour les autres relais on ne prend que la perf du premier relayeur	
	
//get categorie age
       $e=$res->xpath( "RELAY/RELAYPOSITIONS" );
		if( empty($e)  ) {  return false  ;}
		
		$temps=(string)$res['swimtime'];
		$points=(string)$res['points'];
		$race=(string)$res['raceid'];
	    $team=(string)$res['team'];    
	    $age_group= (string)$res['agegroupid'];  
	    $min=$agegroup[$age_group]['min'];
	    $max=$agegroup[$age_group]['max'];
	    $key=$min."-".$max;
	 	$R_masters=getAgeRelaisMasters($key);	
   		
	 	
 	    if( $R_masters == false ) {  return false  ;}
		 	
		if( !isset ($coderelais[$race]) ) {  return false  ;}
        	$nage    =  $coderelais[$race];        
	
    
    			$t=array("4x","10x");
			$max_dist=str_replace( $t,"" ,$nage['distance']);
			
			// revoir traitement de relais age....		
			  
			  $relais['age']= $R_masters;
			  //print_r( $nage );
			  $relais['nom']="R$team";
			  $relais['prenom']=$nage['nage']."(".$nage['distance'].")";
			  $relais['nage']=$nage['nage'];
		      $relais['distance']=$nage['distance'];
		      $relais['sexe']=$nage['sexe'];
		      $relais['points']=$points;
		      $relais['temps']=$temps;
			  $relais['inter']=0;
			  $relais['ref']=$nage['nage']."(".$nage['distance'].")";
			
			  //10*500m
			if( $race != "9"  && $race!= "59" )  {
			if( in_array($race , $relay_rec  )  )        $resultats[]=$relais;
			
			}
			  
				$relayeur_1=array();
				$i=0;
	        
		      $nage    =  $coderelais[$race];                   
		      $nageurs['nage']=$nage['nage'];
		      $nageurs['distance']=$nage['distance'];

		foreach( $res->xpath( "RELAY/RELAYPOSITIONS" ) as $relay ) {
    
		 $id = (string) $relay->RELAYPOSITION['swimmerid'];
		 $relayeur_1=$swimmers[$id];
	  	 break;
	  	} 
	
	  
	  	foreach( $res->xpath( "SPLITS/SPLIT" ) as $split  ) {
	  	
		      // on prend le 1er chrono ........
	  	      	  $time=(string)$split['swimtime'];
			  $dist=(string)$split['distance'];

					  
			if( $dist == "150" ) continue;	  
			  
			  $relayeur_1['temps']=$time;
			  $relayeur_1['distance']=$dist;
			  $relayeur_1['points']="0";

			if( $nage['nage'] == "4N" )  $relayeur_1['nage']="DOS";
			else  $relayeur_1['nage']=$nage['nage'];
			  
						  $relayeur_1['inter']=1;
			  $relayeur_1['ref']=$nage['nage']."(".$nage['distance'].")";
			  $resultats[]=$relayeur_1;
			  
		
			  
		 	if( $dist >= $max_dist )  break;
			  
	  					} 
	  
			
			  	return true;
}
/////////////////////////////////////////////////////////////////////////////
function traiteRelais($res , $swimmers , $agegroup  ) {

global $resultats;	

$coderelais=getCodesRelais();

//87 et 37 relais mixte
$relay_rec=array("43","93","44","94","46","96","9","59","87","37","84");


// pour les record des relais :
// 4x100;4x200;10x100	NL
// 4x100 4N

//pour les autres relais on ne prend que la perf du premier relayeur	
	
//get categorie age



$e=$res->xpath( "RELAY/RELAYPOSITIONS" );


if( empty($e)  ) {  return false  ;}
	
	$temps=(string)$res['swimtime'];
	$points=(string)$res['points'];
	$race=(string)$res['raceid'];
    $team=(string)$res['team'];    
    //$age_group= (string)$res['agegroupid'];  

    $age=getAgeRelais( $e , $swimmers );
    
		if( !isset ($coderelais[$race]) ) {  return false  ;}
        $nage    =  $coderelais[$race];        
	
    
    		$t=array("4x","10x");
			$max_dist=str_replace( $t,"" ,$nage['distance']);
			
	// revoir traitement de relais age....		
			  
			  $relais['age']= $age;
			  //print_r( $nage );
			  $relais['nom']="Relais";
			  $relais['prenom']=$nage['nage']."(".$nage['distance'].")";
			  $relais['nage']=$nage['nage'];
		      $relais['distance']=$nage['distance'];
		      $relais['sexe']=$nage['sexe'];
		      $relais['points']=$points;
		      $relais['temps']=$temps;
			  $relais['inter']=0;
			  $relais['ref']=$nage['nage']."(".$nage['distance'].")";
		
			  //10*500m
			if( $race != "9"  && $race!= "59" )  {
			if( in_array($race , $relay_rec  )  )        $resultats[]=$relais;
			
			
			}
			  
$relayeur_1=array();
$i=0;
	        
		      $nage    =  $coderelais[$race];                   
		      $nageurs['nage']=$nage['nage'];
		      $nageurs['distance']=$nage['distance'];

foreach( $res->xpath( "RELAY/RELAYPOSITIONS" ) as $relay ) {
    
		 $id = (string) $relay->RELAYPOSITION['swimmerid'];
		 $relayeur_1=$swimmers[$id];
	  	 break;
	  } 
	
	  
	  foreach( $res->xpath( "SPLITS/SPLIT" ) as $split  ) {
	  	
		// on prend le 1er chrono ........
	  	      $time=(string)$split['swimtime'];
			  $dist=(string)$split['distance'];

			
			  
		if( $dist == "150" ) continue;	  
			  
			  $relayeur_1['temps']=$time;
			  $relayeur_1['distance']=$dist;
			  $relayeur_1['points']="0";

			if( $nage['nage'] == "4N" )  $relayeur_1['nage']="DOS";
			else  $relayeur_1['nage']=$nage['nage'];
			  
			 //$relayeur_1['temps']=$temps;
			  $relayeur_1['inter']=1;
			  $relayeur_1['ref']=$nage['nage']."(".$nage['distance'].")";
			  $resultats[]=$relayeur_1;
			  
			
			
	  	if( $dist >= $max_dist )  break;
			  
	  	
	  	
	  } 
	  
	  
	  return true;
}

//////////////////////////////////////////////////////////////////////////
function getAgeRelais( $e , $swimmers ){
$age=0;	
foreach( $e as $relay ) {
		foreach ( $relay as $elm ){
		 $id = (string) $elm['swimmerid'];
		 $swimmer=$swimmers[$id];
	 	 if ( $age < $swimmer['age']  ) {
		 	$age=$swimmer['age'];
		 }
	
	  }	
	  
}
	  
	return $age;
}

///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////


function getResultats( $xml , $clubid ,$codenages , $swimmers,$masters ) {



$agegroup=getAgeGroups( $xml );

$xpath="//RESULTS/RESULT[@clubid='$clubid'][@disqualificationid='']";
$result = $xml->xpath( $xpath );	

  
		foreach( $result  as $res ) {
		
				if( $masters ){
				$code=traiteRelaisMasters($res , $swimmers , $agegroup );

				}
				else {
				$code= traiteRelais($res , $swimmers , $agegroup  );	
				}
		
		if( $code === false ) {
			traiteNage($res , $swimmers , $codenages  );
								}

		
		
		}

	

return $resultats;
}

	
//////////////////////////////////////////////////////////
print_r( $resultats );


?>
<label><?php echo $compet["city"] ?> : <?php echo $compet["name"] ?>&nbsp;&nbsp;(<?php echo $compet["bassin"] ?> m)</label>
<br></br>

<?  return ;?>
<table>
<thead><tr>
<td>Nom -Prenom</td><td>Sexe</td><td>Age</td><td>Origine</td><td>Nage</td><td>Distance</td><td>Temps</td><td>Perfs!</td></tr></thead>
 <?php 
 
include 'getrecords.php'; 
 


 $i=0;
 $max=18;
 
$bassin=$compet["bassin"];
$lieu=$compet["city"];  
$date=$compet["startdate"]; 



//////////////////////////////////////////////////////

 foreach ( $resultats  as  $value  ) {

 	

 	
 	 $sexe=$value["sexe"];
 	 $distance=$value["distance"];
 	 $nage=$value["nage"];
 	 $age=$value['age'];
 	 $time=$value["temps"];
 	 $nom=$value["nom"];
 	 $prenom=$value["prenom"];
 	 $points=$value["points"];
 	 
 	 
 	
 	 
 	 $inter=$value["inter"];
 	  $oricol="";$refnage="-";
 	 if( $inter == "1") { 
 	 			$refnage=$value["ref"]; 
	          	$oricol="style='background-color:yellow'";
 	     				}
	
     $debk=$sexe."_".$bassin."_".$nage."_".$distance;




$ok="";

//age masters
  if( strpos($age,"C") !== false ) {
  	$age=str_replace("C","",$age);
  	$max=$age;
  	$master=true;
  }
// age relais masters
  else if ( strpos($age,"R") !== false ) {
  	$age=str_replace("R","",$age);
  	$max=$age;
  	$master=true;
 	
  }
  
  
else {
	$max=18;
	$master=false;
}
  
for  ($a=$age ; $a <=$max ; $a++   )

					{
						if( $master )   {   $key=$debk."_C".$a;
							  	    $key2=$debk."_18";
							   	    $ta="C".$a; 
								    $tabkey=array();
								    $tabkey[$key2]=18;
								    $tabkey[$key]=$ta;
											//les relais
							       if( strpos($debk,"4x") !== false  || 
								strpos($debk,"10x") !== false
								)
							    	{
								  	$key=$debk."_R".$a;$ta="R".$a; 
								  	$tabkey=array();
									$tabkey[$key]=$ta;
								
							 	}	
			
			 				          }
								     else {    $key=$debk."_".$a; $ta=$a;
					 						   $tabkey=array();
											   $tabkey[$key]=$ta;
										  }				 	
			
			
			
			foreach ($tabkey as $key => $ta  ) {
				
			$pce=$lieu."_".$nom."_".$prenom."_".$date."_".$time."_".$points;
			$pce=$key."_".$pce;
			
			$uid=uniqid();
			 
			if( isset( $getrecords[$key] ) ) {
			$rec_time=$getrecords[$key]['temps'] ;
			$ok.="";
	       
	    
			
			if( $time < $rec_time ) {$ok.="<label style='font-weight:bold;background-color:red;color:white'   id='$uid'  onClick=\"javascript:setPceRecords( '$pce' ,'$uid'  )\" >$ta [$rec_time]</label>&nbsp;";	 }
         	
         	else if( $time == $rec_time ) {
         	$ok.="<label style='font-weight:bold;background-color:green;color:white'   id='$uid'  onClick=\"javascript:setPceRecords( '$pce' ,'$uid'  )\" >$ta [$rec_time]</label>&nbsp;";	
         	//$ok.= $time." ".$rectime." ";
         	}
         	
			}
         	
			else {
			$rec_time=-1;

			$ok.="<label style='font-weight:bold;background-color:red;color:white' id='$uid' onClick=\"javascript:setPceRecords( '$pce' ,'$uid'  )\" > $ta [-1]</label>&nbsp;";	
				
			}	
			}
				 
					
				 
					}

			


 if ($i%2 === 1) 
echo "<tr class=\"odd\" ><td>".$nom." ".$prenom."</td><td>".$sexe."</td><td>".$age."</td><td $oricol >".$refnage."</td><td>".$nage."</td><td>".$distance."</td><td>".$time."</td><td>".$ok."</td></tr>"	;
else 				 	
echo "<tr><td>".$nom." ".$prenom."</td><td>".$sexe."</td><td>".$age."</td><td $oricol >".$refnage."</td><td>".$nage."</td><td>".$distance."</td><td>".$time."</td><td>".$ok."</td></tr>"	;			 	
 	
 $i++;				 	
				
	
 }
 	




?>
</table>



