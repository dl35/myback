<?php

$actu = "ecn" ;
$dir = __DIR__ ."/".$actu  ;

if( is_dir($dir) === false )
{
  $r =   mkdir($dir);
  if( $r == false ) {
    echo "error make directory " ;
    exit(1) ;
  }
}




$api = "https://www.ecnatation.org/api/public/rss/" ;
$flux = "https://ecnatation.blogspot.com/feeds/posts/default/";



$feed = file_get_contents( $flux );
if ( empty($feed) ) {
    echo "error";
    exit(1) ;
}


$rss = simplexml_load_string($feed);
if ($rss === false) {
  echo "Erreur lors du chargement du XML\n";
  foreach(libxml_get_errors() as $error) {
      echo "\t", $error->message;
  }
  exit(1) ;
}


$r= deleteall();
if( $r == false ) {
  echo "error delete all " ;
  exit(1) ;
}

$updated = $rss->updated ;

$i = 0 ;

$datas = array();

$t =  $rss->entry;
foreach($t as $n  ) {
    $d = array();

    $d['date'] = (string ) ( $n->published ) ;
    $d['title'] = (string ) ( $n->title ) ;
 
    echo "********\n\n";
    $content = "" ;
    $cont=(string) ( $n->content );
    $content = str_replace("&nbsp;", "" , $cont );
    $content = strip_tags($content,"<a>");
 

 

    $htmlDom = new DOMDocument;
 
    //Parse the HTML of the page using DOMDocument::loadHTML
    @$htmlDom->loadHTML($content);
    $links = $htmlDom->getElementsByTagName('a');
    
    
   
    $first =true ;
    foreach($links as $link){
    
        //Get the link text.
        $linkText = $link->nodeValue;
        //Get the link in the href attribute.
        $linkHref =  $link->getAttribute('href');
 
        //If the link is empty, skip it and don't
        //add it to our $extractedLinks array
        if(strlen(trim($linkHref)) == 0){
            continue;
        }
  
        //Skip if it is a hashtag / anchor link.
        if($linkHref[0] == '#'){
            continue;
        }
     
        //Add the link to our $extractedLinks array.
        $extractedLinks[] = array(
            'text' => $linkText,
            'href' => $linkHref
        );
     
        $img= $linkHref;
     


      
        if ( !empty( $img ) ) {
        $filename_from_url = parse_url($img);
        $ext = pathinfo($filename_from_url['path'], PATHINFO_EXTENSION);


      
        if(getimagesize($img) == false)
        {
          echo "\n=== NOT IMG ===". $img ; 
       
          
          continue;
        } 
    
        if( $ext === "jpg" ||  $ext === "JPG" )  {
          $ext = "jpeg" ;
        }


        $content2 = file_get_contents( $img );
        $rand = get_millis() ;
        $fname= $actu.$rand.'.'.$ext;
         $name= $dir .'/'.$fname;
        $r = file_put_contents( $name , $content2);
        if( $r !== false ) {
          //  resize_img( $name , 200  ) ;
          if( $first ) {
            $d['img'] = $api .$actu."/".$fname ;
            $first = false;
          } else {
            $d['images'][] = $api .$actu."/".$fname ;
            }
           
          } 

          
         
         
    }
    $content = str_replace( $img ,"TOTO" , $content);
    $content = preg_replace('/<a(.*?)href="TOTO"(.*?)>(.*?)<\/a>/' ,"",$content);


}

 $content = preg_replace('/<a(.*?)href="TOTO"(.*?)>(.*?)<\/a>/' ,"",$content);

  $d['description'] =  $content ; // = strip_tags($content);
$datas[] = $d ;
}



// print_r( $datas );

// $res['channel'] =$info ;
$res['items'] =$datas ;

$fp = fopen($dir.'/'.$actu.'.json', 'w');


fwrite($fp, json_encode($res));
fclose($fp);




function deleteall() {
  global $dir ;
  $files = scandir($dir);
  $r = true ;
  foreach( $files as $f ) {
    $pparts = pathinfo( $f );
    if( is_dir($f) ) continue ;
    if( $pparts === "json" ) continue ;
    $f = $dir.'/'.$f;
    $r = unlink($f) ;
    if( $r === false ) {
      break ;
    }

  }
return $r ;
}






function get_millis(){
  list($usec, $sec) = explode(' ', microtime());
  return (int) ((int) $sec * 1000 + ((float) $usec * 1000));
}


?>