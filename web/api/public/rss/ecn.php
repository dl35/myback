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



$ch = curl_init( $flux );
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$feed = curl_exec($ch);
 
if( curl_errno($ch) ){
  echo "error";
  exit(1) ;
}


$xml ="<?xml version='1.0' encoding='UTF-8'?>";

$pos = strpos($feed , $xml ) ; 
if( $pos === false )   {
  echo "error not xml ";
  exit(1) ;
} ;




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

$updated = (string) $rss->updated ;

$i = 0 ;

$datas = array();

$t =  $rss->entry;
foreach($t as $n  ) {
    $d = array();

    $d['date'] = (string ) ( $n->published ) ;
    $d['title'] = (string ) ( $n->title ) ;
 
    $content = "" ;
    $cont=(string) ( $n->content );
    $content = str_replace("&nbsp;", "" , $cont );
    $content = strip_tags($content,"<a><p><br>");
 


    $htmlDom = new DOMDocument;
 
    //Parse the HTML of the page using DOMDocument::loadHTML
    @$htmlDom->loadHTML($content);
    $links = $htmlDom->getElementsByTagName('a');
    
    
   
    $first =true ;
    foreach($links as $link){
      
        $linkText = $link->nodeValue;
        $linkHref =  $link->getAttribute('href');
 
        if(strlen(trim($linkHref)) == 0){
            continue;
        }
  
        if($linkHref[0] == '#'){
            continue;
        }
        /*
        $extractedLinks[] = array(
            'text' => $linkText,
            'href' => $linkHref
        );*/
        $img= $linkHref;
      
        if ( !empty( $img ) ) {
        $filename_from_url = parse_url($img);
        $ext = pathinfo($filename_from_url['path'], PATHINFO_EXTENSION);


        $cimg = curl_init( $img );
        curl_setopt($cimg, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($cimg, CURLOPT_TIMEOUT, 5);
        curl_setopt($cimg, CURLOPT_RETURNTRANSFER, true);
        $contentImg = curl_exec($cimg);
        $contentType = curl_getinfo($cimg, CURLINFO_CONTENT_TYPE);
        $pos = strpos( $contentType , "image") ;
        if( $pos === false ) {
          // echo $contentType ."\n";
          continue ;
        }

    
        if( $ext === "jpg" ||  $ext === "JPG" )  {
          $ext = "jpeg" ;
        }


        $rand = get_millis() ;
        $fname= $actu.$rand.'.'.$ext;
         $name= $dir .'/'.$fname;
        $r = file_put_contents( $name , $contentImg);
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

  $d['description'] =  $content ; 
  $datas[] = $d ;
}



$info =array();
$info['title'] = "Actualité ECNatatation";
$info['description'] = "Toute l'actualité du club";
$info['copyright'] = "Copyright ecn";
$info['lastdate'] = $updated ;
$info['link'] = "http://www.ecnatation.org";

$res['channel'] = $info ;
$res['items'] = $datas ;



$fp = fopen($dir.'/'.$actu.'.json', 'w');
fwrite($fp, json_encode($res));
fclose($fp);



//////////////////////////////////////////////////////////////////////////////////
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




////////////////////////////////////////////////////////////////////////////////////
function get_millis(){
  list($usec, $sec) = explode(' ', microtime());
  return (int) ((int) $sec * 1000 + ((float) $usec * 1000));
}


?>