<?php
include '../common/fonctions_categories.php' ;
include '../common/texte_inscriptions.php' ;

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
        break;
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'));
        $v= validateParams($data) ;
        if( !$v ) break;
        addParams( $data) ;
        
	    break;    
    case 'POST':
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
   // ( $r['dev'] == '0' ) ? $r['dev'] = false : $r['dev'] = true ;
    $r['dev_email'] = utf8_encode( $r['dev_email'] ) ;
    $r['saison_enc'] = utf8_encode( $r['saison_enc'] ) ;
    $r['saison_last'] = utf8_encode( $r['saison_last'] ) ;

    $r['dateforum'] = utf8_encode( $r['dateforum'] ) ;

    $r['tlicencies_encours'] = utf8_encode( $r['tlicencies_encours'] ) ;
    $r['tlicencies_last'] = utf8_encode( $r['tlicencies_last'] ) ;
    $r['tlicencies'] = utf8_encode( $r['tlicencies'] ) ;
    
    $r['tlicencies_encours']  = str_replace("tlicencies_" , "" , $r['tlicencies_encours']  );
    $r['tlicencies_last']  = str_replace("tlicencies_" , "" , $r['tlicencies_last']  );
    

    unset($r['id']);

    $mysqli->close();
    header("Content-type:application/json");
    $datas['datas']=$r;
    echo json_encode( $r );

}
////////////////////////////////////////////////////////////////////////////////////
function addParams($obj) {
	global $mysqli;
	
    $set = "dev ='$obj->dev' ";

    $de  = utf8_decode( $obj->dev_email ) ;
    $set.= ",dev_email ='$de' ";

    $se  = utf8_decode( $obj->saison_enc ) ;
    $set.= ",saison_enc ='$se' ";

    $sl  = utf8_decode( $obj->saison_last ) ;
    $set.= ",saison_last = '$sl' ";

    $df  = utf8_decode( $obj->dateforum ) ;
    $set.= ",dateforum ='$df' ";

    $le  = "tlicencies_".utf8_decode( $obj->tlicencies_encours ) ;
    $set.= ",tlicencies_encours ='$le' ";

    $ll  = "tlicencies_".utf8_decode( $obj->tlicencies_last ) ;
    $set.= ",tlicencies_last ='$ll' ";

    $lic = utf8_decode( $obj->tlicencies ) ;
    $set.= ",tlicencies ='$lic' ";

    $query = "UPDATE params SET $set  where id ='1' ";


	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		($dev) ? $err=$mysqli->error: $err="invalid query";
		setError( $err );
		return;
    }
    setSuccess("add params ");
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
	
	
	return true;
}
////////////////////////////////////////////////////////////////////////////////////
function addTest() {
	global $dev,$mysqli;
	global $tlicencies_encours;
    
    $nom = "test";
	$prenom = "test";
	$adresse = "adresse de test";
    $ville = "Noyal Chatillon sur seiche";
    $cp = "35230";
    $sexe= "H";
    $date="2000-03-15";
    $tel="0600000032,0600000033,0600000034";
    $email="inscriptions@gmail.com";

	$idlic = "TEST1234";
	
    $inscription = "1" ;
	
	$cat = CategorieFromDate( $date , $sexe ) ;
	$rang = RangFromDate( $date , $sexe );

    $cat = strtolower( $cat );

	$set="(id,nom,prenom,date,sexe,adresse,code_postal,ville,categorie,rang,type,email,telephone,inscription,date_inscription)" ;
	$values="('$idlic','$nom','$prenom','$date','$sexe','$adresse','$cp','$ville','$cat','$rang','N','$email','$tel','$inscription',NOW() )";
    $query =" INSERT INTO  $tlicencies_encours  $set  VALUES  $values ";

	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		($dev) ? $err=$mysqli->error: $err="invalid query";
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
    
    $idlic = "TEST1234";
    $query ="DELETE FROM  $tlicencies_encours  WHERE id = '$idlic' ";
	
	$result = $mysqli->query( $query ) ;
	if (!$result ) {
		($dev) ? $err=$mysqli->connect_error: $err="invalid query";
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
    

    /*    $query = "SELECT * FROM $tlicencies_encours WHERE "
                ." categorie IS NOT NULL AND  date_inscription IS NULL "
                ."  AND inscription = '0' ORDER BY id  "; 
    */
             
    if ( $dev ) {
        $query = "SELECT * FROM $tlicencies_encours WHERE "
                 ." categorie IS NOT NULL  "
                 ."  AND (  nom = 'test' )  ORDER BY id  "; 
               $query = "SELECT * FROM $tlicencies_encours WHERE "
                 ." categorie IS NOT NULL  "
                 ."  AND (  id IN ( 'SIP84172' , 'GIL23583' , 'KEL62316')  )  ORDER BY id  ";
            // SIP84172 ,  SIP84172 , KEL62316

    }



    $result = $mysqli->query($query) ;
	if (!$result) {
		($dev) ? $err=$mysqli->error : $err="invalid request";
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
        $success = envoyer_mail( $nom, $prenom, $key, $email  );
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
function envoyer_mail( $nom, $prenom, $key, $email  ) {
        global $dev,$dev_email,$saison_enc;
      
        //    $dev =false ;

        $message=getMessageInsciption($nom,$prenom,$key);

       

        $headers = "MIME-Version: 1.0\n";
        $headers .= "X-Sender: <www.ecnatation.org>\n";
        $headers .= "X-Mailer: PHP\n";
        $headers .= "X-auth-smtp-user: webmaster@ecnatation.org \n";
        $headers .= "X-abuse-contact: webmaster@ecnatation.org \n";
        $headers .= "Reply-to: ECN natation  <inscription@ecnatation.org>\n"; 
        $headers .= "From: ECN natation <inscription@ecnatation.org>\n";

        if( ! $dev ) { $headers .= "Bcc:ecninscription@gmail.com\n";}
        $headers .= "Content-Type: text/html; charset=iso-8859-1";
        
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