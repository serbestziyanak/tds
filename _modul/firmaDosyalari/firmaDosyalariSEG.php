<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$konu			= array_key_exists( 'konu', $_REQUEST )			    ? $_REQUEST[ 'konu' ]			: 'ekle';
$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$aciklama 		= array_key_exists( 'aciklama', $_REQUEST )	 		? $_REQUEST[ 'aciklama' ] 		: '';
$dosyaTuru_id 	= array_key_exists( 'dosyaTuru_id', $_REQUEST )	 	? $_REQUEST[ 'dosyaTuru_id' ] 	: 0;
$dosya_id 		= array_key_exists( 'dosya_id', $_REQUEST )	 		? $_REQUEST[ 'dosya_id' ] 		: 0;
$adi 			= array_key_exists( 'adi', $_REQUEST )	 			? trim($_REQUEST[ 'adi' ]) 		: '';


$SQL_tum_firma_dosyalari_oku = <<< SQL
SELECT
*
FROM
	tb_firma_dosya_turleri
SQL;

$SQL_tek_dosya_turu_oku = <<< SQL
SELECT
	*
FROM
	tb_firma_dosya_turleri
WHERE
	id 			= ? AND
	firma_id 	= ?
SQL;

$SQL_dosya_turu_kaydet = <<< SQL
INSERT INTO
	tb_firma_dosya_turleri
SET
	firma_id	= ?,
	adi			= ?
SQL;


$SQL_dosya_kaydet = <<< SQL
INSERT INTO
	tb_firma_dosyalari
SET
	dosya_turu_id		= ?
	,dosya				= ?
	,aciklama			= ?
SQL;


// Silinecek Dosya kayıtlarda var mı veya dogrı sorgu geliyor mu kontrol ediliyor
$SQL_tek_dosya_oku = <<< SQL
SELECT
	*
FROM
	tb_firma_dosyalari AS fd
INNER JOIN
	tb_firma_dosya_turleri AS dt ON fd.dosya_turu_id = dt.id
WHERE
	dt.id 		= ? AND
	dt.firma_id = ? AND 
	fd.id = ?
SQL;

$SQL_dosya_sil = <<< SQL
DELETE FROM
	tb_firma_dosyalari
WHERE
	id = ?
SQL;

$tekDosyaTuru		= $vt->select( $SQL_tek_dosya_turu_oku, array( $dosyaTuru_id, $_SESSION['firma_id']) ) [ 2 ] ;


if ( $konu == 'dosya' ) {
	//Firmaya ait bir dosya turu yok ise işlemi durduruyoruz
	if ( count($tekDosyaTuru) < 1 ){
		$sonuc[ "sonuc" ] = 'hata';
		
		echo json_encode($sonuc);
		die();
	}

	$dizin		= "../../firmaDosyalari/".$dosyaTuru_id.'/';
	//Dosya Turune göre klasörlendirmesi yapılacaktır. İd sine göre klasor oluşturulmu diye kontrol edip yok ise klador oluşturuyoruz
	if (!file_exists($dizin)) {
        if(!mkdir($dizin, '0777', true)){
   			$sonuc["sonuc"] = "hata";
   			echo json_encode($sonuc);
   			die();
        }
    }

    switch( $islem ){
    	case 'ekle':
    		//Gelen Dosyaları Yüklemesini Yapıyoruz
			foreach ($_FILES['file']["tmp_name"] as $key => $value) {
				if( isset( $_FILES[ "file"]["tmp_name"][$key] ) and $_FILES[ "file"][ 'size' ][$key] > 0 ) {
					$dosya_adi	= rand() ."_".$tekDosyaTuru[ 0 ] [ "adi" ] ."." . pathinfo( $_FILES[ "file"][ 'name' ][$key], PATHINFO_EXTENSION );
					$hedef_yol	= $dizin.$dosya_adi;
					if( move_uploaded_file( $_FILES[ "file"][ 'tmp_name' ][$key], $hedef_yol ) ) {
						$vt->insert( $SQL_dosya_kaydet, array( $dosyaTuru_id, $dosya_adi, $aciklama ) );
						$sonuc["sonuc"] = 'ok';
					}
				}
			}
			echo json_encode($sonuc);
   			die();
		
			break;

		case 'sil':

			//Silinecek dosyanın bilgileri aldık
			$tek_dosya_oku = $vt->select( $SQL_tek_dosya_oku, array( $dosyaTuru_id, $_SESSION[ 'firma_id' ], $dosya_id ) ) [ 2 ];

			if ( count( $tek_dosya_oku ) > 0 ) {
				$vt->delete( $SQL_dosya_sil, array( $dosya_id ) );
				
				//Sunucudan Dosyayı Siliyoruz.
				unlink($dizin.$tek_dosya_oku[ 0 ][ "dosya" ]);

			}
			header( "Location:../../index.php?modul=firmaDosyalari&dosyaTuru_id=$dosyaTuru_id" );
			break;

    }
    
}else if ($konu == 'tur'){

	switch ($islem) {
		case 'ekle':
			if ( $adi != '' ) {
				$vt->insert( $SQL_dosya_turu_kaydet, array( $_SESSION[ 'firma_id' ], $adi ) );
			}
				
			break;
	}

	header( "Location:../../index.php?modul=firmaDosyalari" );

}

?>