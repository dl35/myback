<?php
include '../common/fonctions_categories.php' ;

$auth= array("admin");

if ( !isset($profile) || !in_array( $profile , $auth ) ) {
	
	setError("Not Authorized" , 401 ) ;
	return;
	
}



switch ($method) {
	

    case 'GET':
        if( !isset($id) ) {
        setError( "invalid id");
        return;
        }
        if( $id === 'prepare' ) {
        prepare() ;
        }else if( $id === 'send' ) {
        sendInscriptions(); 
        }else if( $id === 'addtest' ) {
        addTest(); 
        }else if( $id === 'deltest' ) {
        delTest();
                }
        else if( $id === 'params' ) {
         getParams();
                }     
        else if( $id === 'rappel' ) {
          getTexte('rappel');
                }     
        else if( $id === 'nouveau' ) {
          getTexte('nouveau');
                }     
        else if( $id === 'ancien' ) {
            getTexte('ancien');
                }     
                                                       
                
        break;
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'));
        $v= validateParams($data) ;
        if( !$v ) break;
        addParams( $data) ;
        
	    break;    
    case 'POST':
        if( !isset($id) ) {
			setError( "invalid id");
			return;
        }
        if( $id === 'upload' ) { 
            upload( $_FILES );
            return;
		}

        $body = json_decode(file_get_contents('php://input'));
        
        if( !isset($body) ) {
			setError( "invalid post data");
			return;
        }
     
        
          saveTexte( $body , $id );
   	    break;
	case 'DELETE':
		if( !isset($id) ) {
			setError( "invalid id");
			return;
        }
      	delete($id) ;
		break;
	default:
		setError( "invalides routes");
		break;
		
    }
////////////////////////////////////////////////////////////////////////////////////
function upload( $file ) {
	
	$taille_maxi = 6000000;
	$taille = filesize($file['file']['tmp_name']);
	$extensions = array('.pdf');
	$extension = strrchr($file['file']['name'], '.');
	
	
	if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
	{
		$message = 'Vous devez uploader un fichier de type pdf';
		setError( $message );
		return;
	}
	if($taille>$taille_maxi)
	{
		$message= 'Le fichier est trop gros...';
		setError( $message);
		return;
	}

    /* Getting file name */
	$filename = $file['file']['tmp_name'];
	if ( !file_exists($filename)) {
		$message = "Erreur upload  fichier temporaire";
		setError( $message );
		return;
	} 


// mode developpement
// chmod -R o+rw upload

	/* Location */
	//$location =  "/var/www/html/upload/" .  $file["file"]["name"] ;

	//$location =  $_SERVER["DOCUMENT_ROOT"] . "/upload/" .  $file["file"]["name"] ;
    $location =  $_SERVER["DOCUMENT_ROOT"] . "/api/common/" .  $file["file"]["name"] ;
	/* Upload file */
	$ret = move_uploaded_file($file['file']['tmp_name'],$location );
	if( $ret ) {
		$message = "upload: OK ".$location;
		setSuccess( $message );
	} else {  
		$message = "Erreur upload ".$location ;
		setError( $message );
		return;
	}

	
	
}    
////////////////////////////////////////////////////////////////////////////////////
function saveTexte( $obj , $type ){
   global $mysqli;

  $query ="REPLACE INTO messages_texte ( type , data ) VALUES (?,?) ";
  
  $params[]= $type ;
  $params[]= utf8_decode( $obj->body ) ;
  $start = "ss";

  $stmt = $mysqli->prepare( $query );
  $stmt->bind_param( $start  ,...$params );
  $result = $stmt->execute();
   if (!$result) {
        ($dev) ? $err=$mysqli->error : $err="invalid request";
        setError( $err );
        return ;
    }

  $mysqli->close();
  header("Content-type:application/json");
  echo json_encode( 'ok' );

}

////////////////////////////////////////////////////////////////////////////////////
function getTexte( $type , $json=true ) {
    global $mysqli;
   

    $query ="SELECT data  FROM  messages_texte where type='$type'   ";
	

    $result = $mysqli->query($query) ;
    if (!$result) {
        ($dev) ? $err=$mysqli->error : $err="invalid request";
        setError( $err );
        return ;
    }

    $r = $result->fetch_assoc() ;
   
    $data = utf8_encode( $r['data'] ) ;
       
    if( $json ) {
        $mysqli->close();
        header("Content-type:application/json");
        echo json_encode( $data );
    } else {
        return $data ;
    }
    
}
////////////////////////////////////////////////////////////////////////////////////
function getParams() {
    global $mysqli;
   
    $query ="SELECT *  FROM  params where id ='1' ";
	

    $result = $mysqli->query($query) ;
    if (!$result) {
        ($dev) ? $err=$mysqli->error : $err="invalid request";
        setError( $err );
        return ;
    }

    $r = $result->fetch_assoc() ;
   
    $r['dev_email'] = utf8_encode( $r['dev_email'] ) ;
    $r['dateforum'] = utf8_encode( $r['dateforum'] ) ;
    $r['saison_enc'] = utf8_encode( $r['saison_enc'] ) ;
    $r['saison_last'] = utf8_encode( $r['saison_last'] ) ;

   
    $r['tlicencies_encours'] = utf8_encode( $r['tlicencies_encours'] ) ;
    $r['tlicencies_last'] = utf8_encode( $r['tlicencies_last'] ) ;
    $r['tlicencies'] = utf8_encode( $r['tlicencies'] ) ;
    
    $r['president'] = utf8_encode( $r['president'] ) ;

    unset($r['id']);

    $mysqli->close();
    header("Content-type:application/json");
    echo json_encode( $r );

}
////////////////////////////////////////////////////////////////////////////////////
function addParams($obj) {
	global $mysqli;
    

    $pamas =array();
    
	$set = "dev=?" ;
	$params[] = $obj->dev ;
	$start = "s";
		
	$set .= ",dev_email=?" ;
	$params[]= utf8_decode( $obj->dev_email ) ;
    $start .= "s";
    
    $set .= ",president=?" ;
	$params[]= utf8_decode( $obj->president ) ;
	$start .= "s";

	$set .= ",saison_enc=?" ;
	$params[]= utf8_decode( $obj->saison_enc ) ;
    $start .= "s";
    
    $set .= ",saison_last=?" ;
	$params[]= utf8_decode( $obj->saison_last ) ;
	$start .= "s";

    $set .= ",dateforum=?" ;
	$params[]= utf8_decode( $obj->dateforum ) ;
    $start .= "s";
    
    $set .= ",tlicencies_encours=?" ;
	$params[]= utf8_decode( $obj->tlicencies_encours ) ;
	$start .= "s";
   
    $set .= ",tlicencies_last=?" ;
	$params[]= utf8_decode( $obj->tlicencies_last ) ;
    $start .= "s";
    
    $set .= ",tlicencies=?" ;
	$params[]= utf8_decode( $obj->tlicencies ) ;
    $start .= "s";

  
    $id='1';    
    $query = "UPDATE params SET $set  WHERE id = ?  ";
	$params[]=$id;
	$start.="s";
	$stmt = $mysqli->prepare( $query );
	$stmt->bind_param( $start  ,...$params );
	
	
	$result = $stmt->execute();
	if (!$result) {
		($dev) ? $err=$stmt->error : $err="invalid connect";
		setError( $err );
		return;
	}
		
    $stmt->close();
    setSuccess("params modif: ok");
    return;
    
    
}
/////////////////////////////////////////////////////////////////////////////////////
function validateParams($json) {
	
	
	if ( ! array_key_exists('dev', $json) ) return false ;
	if ( ! array_key_exists('dev_email', $json) ) return false ;
	if ( ! array_key_exists('saison_enc', $json) ) return false ;
	if ( ! array_key_exists('saison_last', $json) ) return false ;
	if ( ! array_key_exists('dateforum', $json) ) return false ;
	if ( ! array_key_exists('tlicencies_encours', $json) ) return false ;
	if ( ! array_key_exists('tlicencies_last', $json) ) return false ;
    if ( ! array_key_exists('tlicencies', $json) ) return false ;
    if ( ! array_key_exists('president', $json) ) return false ;
	
	
	return true;
}
////////////////////////////////////////////////////////////////////////////////////
function addTest() {
	global $dev, $dev_email, $mysqli;
	global $tlicencies_encours;
    
    $nom = "testecn35";
	$prenom = utf8_decode("éssai");
	$adresse = "adresse de testecn35";
    $ville = "Noyal Chatillon sur seiche";
    $cp = "35230";
    $sexe= "H";
    
    $time = strtotime("-12 year", time());
    $mydate = date("Y-m-d", $time);

    
    $date=$mydate;
    $tel="0600000032,0600000033,0600000034";
    $email= $dev_email;

	$idlic = "TEST0000";
	
    $inscription = "1" ;
	
	$cat = CategorieFromDate( $date , $sexe ) ;
	$rang = RangFromDate( $date , $sexe );

    $cat = strtolower( $cat );

	$set="(id,nom,prenom,date,sexe,adresse,code_postal,ville,categorie,rang,type,email,telephone,inscription,date_inscription)" ;
	$values="('$idlic','$nom','$prenom','$date','$sexe','$adresse','$cp','$ville','$cat','$rang','N','$email','$tel','$inscription',NOW() )";
    $query =" INSERT INTO  $tlicencies_encours  $set  VALUES  $values ";

	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		$err=$mysqli->error ;
		setError( $err );
		return;
    }
    setSuccess("add test");
    return;
    
    
}
////////////////////////////////////////////////////////////////////////////////////
function delTest() {
    global $dev,$mysqli;
    global $tlicencies_encours;
    
    
    $query ="DELETE FROM  $tlicencies_encours  WHERE nom = 'testecn35' OR nom = 'testnew' ";
	
	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		$err=$mysqli->error ;
		setError( $err );
		return;
    }
    $mysqli->close();
    setSuccess("del test");

}
////////////////////////////////////////////////////////////////////////////////////
function sendInscriptions() {
    global $tlicencies_encours ;
    global $dev,$mysqli ;
    
    // texte_inscription 
    $texte_inscription =  getTexte( 'ancien'  , false ) ;



    $query = "SELECT * FROM $tlicencies_encours WHERE "
                ." categorie IS NOT NULL AND  date_inscription IS NULL "
                ."  AND inscription = '0' ORDER BY id  "; 
    
             
    if ( $dev ) {
        $query = "SELECT * FROM $tlicencies_encours WHERE "
                 ." categorie IS NOT NULL  "
                 ."  AND (  nom = 'testecn35' )  ORDER BY id  "; 
       
       /*          $query = "SELECT * FROM $tlicencies_encours WHERE "
                 ." categorie IS NOT NULL  "
                 ."  AND (  id IN (  'GIL23583' )  )  ORDER BY id  ";
            // SIP84172 ,  SIP84172 , KEL62316
        */    

    }



    $result = $mysqli->query($query) ;
	if (!$result) {
		$err=$mysqli->error ;
		setError( $err );
		return ;
	}
	
    $nb = 0;
    $error = 0;
	while($r = $result->fetch_assoc() ) {
        $key=$r['id'];
        $email=splitemail( $r['email'] );
        $nom=$r['nom'];
        $prenom=$r['prenom'];
        if( empty($email)  ) continue;
        $success = envoyer_mail( $nom, $prenom, $key, $email , $texte_inscription  );
        if ( $success ) $nb++ ;
        else $error++;    

    }

    $mysqli->close();
    setSuccess( "send inscriptions: ".$nb);

}
/////////////////////////////////////////////////////////////////////////////////
function splitemail($email){

    $temail = explode("," , $email )  ;
    $v="";
    foreach( $temail as $k ) {
            if( empty($k) ) continue;

            if( !empty($v) ) {
                $v.=",";    
            }
            $v.=$k;
      
    }

        return $v;
    

}
/////////////////////////////////////////////////////////////////////////////////
function envoyer_mail( $nom, $prenom, $key, $email , $texte_inscription  ) {
        global $dev,$dev_email,$saison_enc,$dateforum,$urlpublic ;
             			
        $urldesin = "http://www.ecnatation.org/api/public/scripts/delete.php?key=".$key;
        $urlins = 	$urlpublic ."?adhesion/".$key;
      
        $message  ="<html><head>";
        $message .="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head><body>";
        
        $txtbody = $texte_inscription ;
        $old = array("#NOM","#PRENOM","#SAISON_ENC","#URLINS","#URLDESIN","#DATEFORUM");
        $new = array( $nom , $prenom , $saison_enc , $urlins ,$urldesin , $dateforum );
        $txtbody = str_replace($old, $new, $txtbody);
        
        $message .= $txtbody;

        $message .="</body></html>";       

        $headers  = "MIME-Version: 1.0\n";
        $headers .= "X-Sender: <www.ecnatation.org>\n";
        $headers .= "X-Mailer: PHP\n";
        $headers .= "X-auth-smtp-user: webmaster@ecnatation.org \n";
        $headers .= "X-abuse-contact: webmaster@ecnatation.org \n";
        $headers .= "Reply-to: ECN natation  <inscription@ecnatation.org>\n"; 
        $headers .= "From: ECN natation <inscription@ecnatation.org>\n";

        if( ! $dev ) { $headers .= "Bcc:ecninscription@gmail.com\n";}
              
        $headers .= "Content-Type: text/html; charset=\"utf-8\"";
        $headers .='Content-Transfer-Encoding: 8bit';

 

        $subject ="[Club de Natation] Inscription  $saison_enc : ".$prenom." ".$nom;
       
        if( $dev ) {
            $subject ="[TEST Club de Natation] Inscription  $saison_enc : ".$prenom." ".$nom;
            $email = $dev_email;
        }

       

        $success = mail($email,$subject,$message,$headers);
        return $success;




}
/////////////////////////////////////////////////////////////////////////////////
function prepare() {
    global $tlicencies_encours ;
    global $dev,$mysqli ;
    $query=array();
    
   
    $year = date('Y') + 1 ;
    $table_new = "licencies_".$year;

    $query[]="CREATE TABLE $table_new LIKE $tlicencies_encours  ";
    $query[]="INSERT $table_new SELECT * FROM $tlicencies_encours  ";  
    $query[]="UPDATE $table_new  SET paye=0,photo=0,fiche_medicale=0,cert_medical=0,auto_parentale=0,carte=NULL,num_carte=NULL,confirmation_email=0,date_valide=NULL,date_inscription=NULL,inscription='0',valide=0,commentaires=NULL,cotisation='0.00',type='R',num_cheque1=NULL,num_cheque2=NULL,num_cheque3=NULL,cheque1=NULL "; 
    $query[]="UPDATE $table_new  SET total=0,reglement=0,num_cheque1=NULL,num_cheque2=NULL,num_cheque3=NULL,cheque1=NULL,cheque2=NULL,cheque3=NULL,ch_sport=NULL,num_sport=NULL,coup_sport=NULL,num_coupsport=NULL,banque=NULL,especes=NULL,nbre_chvac10=0,nbre_chvac20=0 ";
    $query[]="UPDATE $table_new  SET categorie = NULL  WHERE categorie = '' ";
    $query[]="UPDATE $table_new  SET officiel = NULL  WHERE officiel = '' ";
    $query[]="UPDATE $table_new  SET type = 'R'  WHERE type = 'N' ";
    $query[]="UPDATE $table_new  SET inscription='1' ,date_inscription = NOW()  WHERE categorie IS NULL ";
    $query[]="UPDATE $table_new  SET inscription='1' ,date_inscription = NOW()  WHERE entr ='1' ";

    
    foreach ($query as $q ) {

        $result = $mysqli->query( $q );
        if (!$result) {
            $err=$mysqli->error ;
            $mysqli->close();
            setError($err ,404 );
            return; ;
        }

    }

   

    $query = "SELECT * FROM  $table_new  WHERE categorie IS NOT NULL  ORDER BY id ";
    $result = $mysqli->query( $query );
    if (!$result) {
        $err=$mysqli->error ;
        $mysqli->close();
        setError($err ,404 );
        return;
    }

  


    while( $r = $result->fetch_assoc() ) {
        $date=$r['date'];
        $sexe=$r['sexe'];
        $cat=CategorieFromDate( $date , $sexe);
      	$rang=RangFromDate ( $date , $sexe);
        
        $niveau=$r['niveau'];
		if ( empty($niveau) && ( $cat == "JE" || $cat == "JU" || $cat == "SE") ){
			$niveau=",niveau='dep' ";
		} else {
            $niveau = "" ;
        }
		
		$id=$r['id'];
		$nom=$r['nom'];
		$prenom=$r['prenom'];
		$query="UPDATE $table_new SET categorie = '$cat' , rang = '$rang' $niveau  WHERE  id='$id'   ";
        
        if ( $mysqli->query( $query ) === false ) {
        $err = $mysqli->error . "$nom : $prenom  ($cat $rang)" ;
        $mysqli->close();
        setError($err ,404 );
        return;
                     }
     
    
   }
   $mysqli->close();
   setSuccess('creation table '.$table_new .':  ok ') ;

}
/////////////////////////////////////////////////////////////////////////////////
function delete( $id ) {
	global $tcompetitions , $tengagements, $tengage_date ;
	global $dev,$mysqli;
    
    $queries = array();
    if( $id === 'competitions' ) {
        $queries[] = "DELETE FROM  $tcompetitions ";
        $queries[] = "ALTER TABLE  $tcompetitions AUTO_INCREMENT = 1 ";
    } else if ( $id === 'engagements' ) {
        $queries[] = "DELETE FROM  $tengagements ";
        $queries[] = "DELETE FROM  $tengage_date ";
        $queries[] = "ALTER TABLE  $tengagements AUTO_INCREMENT = 1 ";
        $queries[] = "ALTER TABLE  $tengage_date AUTO_INCREMENT = 1 ";
    }  
	
    foreach( $queries as $query ) {
        $stmt = $mysqli->prepare( $query );
        $result = $stmt->execute();
        if (!$result) {
            ($dev) ? $err=$mysqli->error : $err="invalid request delete ".$query;
            $stmt->close();
	        $mysqli->close();
            setError($err ,404 );
            return ;
        }
    }
 	
	$stmt->close();
	$mysqli->close();
	$message = "table $id : Suppression";
	setSuccess($message);

}