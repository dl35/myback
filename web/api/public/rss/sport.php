<?php
$dir = "sport" ;
if( is_dir($dir) === false )
{
    mkdir($dir);
}
deleteall();
$api = "https://www.ecnatation.org/api/public/rss/" ;
$flux = "http://www.sports.fr//fr/cmc/natation/rss.xml";

$feed = file_get_contents( $flux );
if ( empty($feed) ) {
    echo "error";
    return ;
}



$rss = simplexml_load_string($feed);



$channel = $rss->xpath('//channel');

$info =array();

$info['title'] = (string) $channel[0]->title ;
$info['description'] = (string) $channel[0]->description ;
$info['copyright'] = (string) $channel[0]->copyright ;
$info['lastdate'] = (string) $channel[0]->lastBuildDate ;
$info['link'] = (string) $channel[0]->link ;




$items = $rss->xpath('//item');
$i = 0 ;
$datas = array();
foreach($items as $item ) {
    $d['title'] = (string) $item->title ;
    $desc = (string) $item->description ;

    $desc = preg_replace(array('/<img[^>]+>/i','/<br\/>/i','/\s{2,}/', '/[\t\n]/'), ' ', $desc);
    // $desc = utf8_encode($desc);
    $d['description'] = $desc ;
    $d['date'] = (string) $item->pubDate ;

    $img= $item->enclosure['url'];
    if ( !empty( $img ) ) {
      $filename_from_url = parse_url($img);
      $ext = pathinfo($filename_from_url['path'], PATHINFO_EXTENSION);
      if( $ext === "jpg")  {
        $ext = "jpeg" ;
      }
    $content = file_get_contents( $img );
    $rand = get_millis() ;
    $name= $dir .'/'.$dir.$rand.'.'.$ext;
    $r = file_put_contents( $name , $content);
    if( $r !== false ) {
      //  resize_img( $name , 200  ) ;
        $d['img'] = $api . $name ;
      }
 
     }
    $datas[] =$d ;
   
    $i++ ;
    if ( $i>=10 ) break ;
}


$res['channel'] =$info ;
$res['items'] =$datas ;

$fp = fopen($dir.'/sport.json', 'w');

fwrite($fp, json_encode($res));
fclose($fp);


function deleteall() {
  global $dir ;
  $files = scandir($dir);

  foreach( $files as $f ) {
    $pparts = pathinfo( $f );
    if( is_dir($f) ) continue ;
    if( $pparts === "json" ) continue ;
    $f = $dir.'/'.$f;
    unlink($f) ;

  }

}

function get_millis(){
  list($usec, $sec) = explode(' ', microtime());
  return (int) ((int) $sec * 1000 + ((float) $usec * 1000));
}

function resize_img($image_path,$max_size = 1280,$qualite = 100,$type = 'auto'){

    // Vérification que le fichier existe
    if(!file_exists($image_path)):
      return 'wrong_path';
    endif;
  
    // Extensions et mimes autorisés
    $extensions = array('jpg','jpeg','png','gif');
    $mimes = array('image/jpeg','image/gif','image/png');
  
    // Récupération de l'extension de l'image
    $tab_ext = explode('.', $image_path);
    $extension  = strtolower($tab_ext[count($tab_ext)-1]);
  
    // Récupération des informations de l'image
    $image_data = getimagesize($image_path);
  
    // Test si l'extension est autorisée
    if (in_array($extension,$extensions) && in_array($image_data['mime'],$mimes)):
      
      // On stocke les dimensions dans des variables
      $img_width = $image_data[0];
      $img_height = $image_data[1];
  
      // On vérifie quel coté est le plus grand
      if($img_width >= $img_height && $type != "height"):
  
        // Calcul des nouvelles dimensions à partir de la largeur
        if($max_size >= $img_width):
          return 'no_need_to_resize';
        endif;
  
        $new_width = $max_size;
        $reduction = ( ($new_width * 100) / $img_width );
        $new_height = round(( ($img_height * $reduction )/100 ),0);
  
      else:
  
        // Calcul des nouvelles dimensions à partir de la hauteur
        if($max_size >= $img_height):
          return 'no_need_to_resize';
        endif;
  
        $new_height = $max_size;
        $reduction = ( ($new_height * 100) / $img_height );
        $new_width = round(( ($img_width * $reduction )/100 ),0);
  
      endif;
  
      // Création de la ressource pour la nouvelle image
      $dest = imagecreatetruecolor($new_width, $new_height);
      
      // En fonction de l'extension on prépare l'iamge
      switch($extension){
        case 'jpg':
        case 'jpeg':
          $src = imagecreatefromjpeg($image_path); // Pour les jpg et jpeg
        break;
  
        case 'png':
          $src = imagecreatefrompng($image_path); // Pour les png
        break;
  
        case 'gif':
          $src = imagecreatefromgif($image_path); // Pour les gif
        break;
      }
  
      // Création de l'image redimentionnée
      if(imagecopyresampled($dest, $src, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height)):
  
        // On remplace l'image en fonction de l'extension
        switch($extension){
          case 'jpg':
          case 'jpeg':
            imagejpeg($dest , $image_path, $qualite); // Pour les jpg et jpeg
          break;
  
          case 'png':
            imagepng($dest , $image_path, $qualite); // Pour les png
          break;
  
          case 'gif':
            imagegif($dest , $image_path, $qualite); // Pour les gif
          break;
        }
  
        return 'success';
        
      else:
        return 'resize_error';
      endif;
  
    else:
      return 'no_img';
    endif;
  }


?>
