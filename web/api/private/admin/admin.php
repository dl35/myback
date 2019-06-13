<?php
include '../common/fonctions_categories.php' ;
include '../common/texte_inscriptions.php' ;

switch ($method) {
	

    case 'GET':
        if( !isset($id) ) {
        setError( "invalid id");
        return;
        }
        if( $id === 'prepare') {
        prepare() ;
        }else if($id === 'send') {
        sendInscriptions(); 
        }
        break;
    case 'POST':
	    break;    
    case 'PUT':
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
                 ."  AND (  nom = 'le sech' OR nom ='simon' )  ORDER BY id  "; 

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

        if ( !empty($email) ) {
            if( !empty($v) ) {
                $v.=",";    
            }
            $v.=$k;
        }
    }

        return $v;
    

}
/////////////////////////////////////////////////////////////////////////////////
function envoyer_mail( $nom, $prenom, $key, $email  ) {
        global $dev,$dev_email,$saison_enc;
      

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
    
   
    $year = date('Y');
    $table_new = "licencies_".$year;

    $query[]="CREATE TABLE $table_new LIKE $tlicencies_encours  ";
    $query[]="INSERT $table_new SELECT * FROM $tlicencies_encours  ";  
    $query[]="UPDATE $table_new  SET paye=0,photo=0,fiche_medicale=0,cert_medical=0,auto_parentale=0,carte=NULL,num_carte=NULL,confirmation_email=0,date_valide=NULL,date_inscription=NULL,inscription='0',valide=0,commentaires=NULL,cotisation='0.00',type='R',num_cheque1=NULL,num_cheque2=NULL,num_cheque3=NULL,cheque1=NULL "; 
    $query[]="UPDATE $table_new  SET total=0,reglement=0,num_cheque1=NULL,num_cheque2=NULL,num_cheque3=NULL,cheque1=NULL,cheque2=NULL,cheque3=NULL,ch_sport=NULL,num_sport=NULL,coup_sport=NULL,num_coupsport=NULL,banque=NULL,especes=NULL,nbre_chvac10=0,nbre_chvac20=0 ";
    $query[]="UPDATE $table_new  SET categorie = NULL  WHERE categorie = '' ";
    $query[]="UPDATE $table_new  SET officiel = NULL  WHERE officiel = '' ";

    
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