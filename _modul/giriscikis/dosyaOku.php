<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();


$SQL_giris_cikis_kaydet = <<< SQL
INSERT INTO
	tb_giris_cikis
SET
	personel_id	= ?,
	tarih		= ?,
	baslangic_saat	= ?
SQL;

//Personel Olup Olmadıgını kontrol etme 
$SQL_personel_oku = <<< SQL
SELECT
	p.id
FROM
	tb_personel AS p
WHERE
	p.firma_id = ? AND 
	p.kayit_no = ?
SQL;

//Çıkış Yapılıp Yapılmadığı Kontrolü
$SQL_personel_gun_cikis = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis 
WHERE
	personel_id 		   = ? AND 
	tarih 				   = ? AND 
	baslangic_saat IS NOT NULL AND 
	bitis_saat IS NULL AND
	aktif = 1
SQL;

//Giriş Çıkış id sine göre listeleme 
$SQL_personel_giris_cikis = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis 
WHERE
	id 		   = ? AND
	aktif 	   = 1
SQL;

$SQL_bitis_saat_guncelle = <<< SQL
UPDATE tb_giris_cikis
SET 
	bitis_saat 	= ?
WHERE
	id 			= ?  
SQL;



$Dosya = fopen( "../../yedek6.txt", "r" ) or exit( "Dosya Açılamadı !" );
 
while( !feof( $Dosya ) )
{	
	
  	$satir 		= fgets( $Dosya );
  	$satir_bol 	= explode( ",", $satir );
  	$tarih_bol  = explode( " ", $satir_bol[3] );
  	$tarih 		= $tarih_bol[0];
  	$saat 		= $tarih_bol[1];
  	$personel_kayit_numarasi = intval( $satir_bol[1] ); 

  	//Gelen kayıt numarasına göre personelli çağırıyoruz
  	$personel_varmi = $vt->select( $SQL_personel_oku, array($_SESSION['firma_id'], $personel_kayit_numarasi ) ) [2];
  	// print_r($personel_varmi);
  	// echo $personel_varmi[ 0 ][ 'id' ];
  	// die();
  	//Personel Varsa işlmelere devam ediliyor
  	if ( count( $personel_varmi ) > 0 ){
  		
  		//Personel giriş yapıp cıkış yapmadığını kontrol ediyoruz
  		$girisvarmi  = $vt->select($SQL_personel_gun_cikis, array( $personel_varmi[ 0 ][ 'id' ], $tarih ))[ 2 ];
  		
  		if ( count( $girisvarmi ) > 0 ){
  			$vt->update($SQL_bitis_saat_guncelle, array( $saat, $girisvarmi[0][ 'id' ] ));
  		}else{
  			$vt->insert( $SQL_giris_cikis_kaydet, array( $personel_varmi[ 0 ][ 'id' ], $tarih, $saat ) );
  		}
  	}

  	
}
 
fclose($Dosya);


?>