<?php
require_once('../fpdf/fpdf.php'); 
require_once('../fpdf/fpdi.php'); 

	


/////////////////////
function doPdf($nom,$prenom,$date,$cotisation,$saison) {

list($y,$m,$d)=explode("-",$date);
$date=$d."/".$m."/".$y;

$nom =  "Nom: ".strtoupper($nom) ;
$prenom =  utf8_decode("Prénom: ").$prenom  ;
$date = "Date de Naissance: ".$date ;


$texte1=utf8_decode("Je soussignée, Géraldine Gilbert, Présidente de l'association Espérance" );
$texte2=utf8_decode("Chartres de Bretagne Natation (N° Jeunesse et Sports : 0135 S80)");
$texte3=utf8_decode("certifie que :");

$texte4=utf8_decode("est adhérent de l'association Espérance Chartres de Bretagne Natation");
$texte5=utf8_decode("et s'est acquitté(e) de la cotisation au club pour un montant de $cotisation Euros");
$texte6=utf8_decode("pour la $saison.");

if ( $cotisation == 0  ) {
	$texte5=utf8_decode("pour la $saison.");
	$texte6="";
}

////////////////////////////////////////////////////////////////

    $folder = realpath(dirname(__FILE__));
	// initiate FPDI 
	$pdf = new FPDI(); 
	// add a page 
	$pdf->AddPage(); 
	// set the sourcefile 
	$pdf->setSourceFile($folder.'/attestation.pdf'); 
	// import page 1 
	$tplIdx = $pdf->importPage(1); 
	// use the imported page and place it at point 10,10 with a width of 100 mm 
	$pdf->useTemplate($tplIdx,0,0,210,297); 
	$pdf->SetFont('Arial');
	$pdf->SetFontSize('13'); 
	$pdf->SetTextColor(0,0,0); 



//////////////////////////////////////////////////////////////////
	$x="30";

	$pdf->SetXY( $x ,80);$pdf->Write(0, $texte1 );
	$pdf->SetXY( $x ,85);$pdf->Write(0,  $texte2 );
	$pdf->SetXY( $x ,90);$pdf->Write(0, $texte3 );

	$pdf->SetXY( $x ,110);$pdf->Write(0, $nom );
	$pdf->SetXY( $x ,120);$pdf->Write(0, $prenom );
	$pdf->SetXY( $x ,130);$pdf->Write(0, $date);

	$pdf->SetXY( $x ,165);$pdf->Write(0, $texte4 );
	$pdf->SetXY( $x ,170);$pdf->Write(0, $texte5 );
	$pdf->SetXY( $x ,175);$pdf->Write(0, $texte6 );
	
	

	
return $pdf;	




}