<?php

include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();
error_reporting(E_ALL);

$konu			= array_key_exists( 'konu', $_REQUEST )			    ? $_REQUEST[ 'konu' ]			: 'ekle';
$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$aciklama 		= array_key_exists( 'aciklama', $_REQUEST )	 		? $_REQUEST[ 'aciklama' ] 		: '';
$dosyaTuru_id 	= array_key_exists( 'dosyaTuru_id', $_REQUEST )	 	? $_REQUEST[ 'dosyaTuru_id' ] 	: 0;
$dosya_id 		= array_key_exists( 'dosya_id', $_REQUEST )	 		? $_REQUEST[ 'dosya_id' ] 		: 0;
$adi 			= array_key_exists( 'adi', $_REQUEST )	 			? trim($_REQUEST[ 'adi' ]) 		: '';
$tarih 			= array_key_exists( 'tarih', $_REQUEST )	 		? trim($_REQUEST[ 'tarih' ]) 	: '';
$evrakTarih 	= array_key_exists( 'evrakTarih', $_REQUEST )	 	? trim($_REQUEST[ 'evrakTarih' ]) : '';
$kategori 		= array_key_exists( 'kategori', $_REQUEST )	 		? trim($_REQUEST[ 'kategori' ]) : '';


$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "firmaDosyalari", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}



$SQL_tum_firma_dosyalari_oku = <<< SQL
SELECT
	*
FROM
	tb_firma_dosya_turleri
SQL;

$SQL_tek_dosya_turu_oku = <<< SQL
SELECT
	*,
	(SELECT COUNT(tb_firma_dosyalari.id) 
		FROM tb_firma_dosyalari 
		WHERE tb_firma_dosyalari.dosya_turu_id = tb_firma_dosya_turleri.id 
		) AS dosyaSayisi
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
	kategori	= ?,
	adi			= ?,
	tarih 		= ?
SQL;

//SQL DOSYA TURU DUZENLEME
$SQL_dosya_turu_duzenle = <<< SQL
UPDATE
	tb_firma_dosya_turleri
SET
	kategori	= ?,
	adi			= ?,
	tarih 		= ?
WHERE
	id 			= ?
SQL;

$SQL_dosya_kaydet = <<< SQL
INSERT INTO
	tb_firma_dosyalari
SET
	dosya_turu_id		= ?
	,dosya				= ?
	,aciklama			= ?
	,evrakTarihi		= ?
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

$SQL_dosya_turu_sil = <<< SQL
DELETE FROM
	tb_firma_dosya_turleri
WHERE
	id = ?
SQL;

$SQL_dosya_sil = <<< SQL
DELETE FROM
	tb_firma_dosyalari
WHERE
	id = ?
SQL;


$tekDosyaTuru		= $vt->select( $SQL_tek_dosya_turu_oku, array( $dosyaTuru_id, $_SESSION['firma_id']) ) [ 2 ] ;

$vt->islemBaslat();
if ( $konu == 'dosya' ) {
	//Firmaya ait bir dosya turu yok ise işlemi durduruyoruz
	if ( count($tekDosyaTuru) < 1 ){
		$sonuc[ "sonuc" ] = 'hata - 5';
		
		echo json_encode($sonuc);
		die();
	}

	//$dizin = '../../' . $dosyaTuru_id;
	$dizin		= "../../firmaDosyalari/".$dosyaTuru_id;
	//mkdir($dizin);
	//Dosya Turune göre klasörlendirmesi yapılacaktır. İd sine göre klasor oluşturulmu diye kontrol edip yok ise klador oluşturuyoruz
	if (!is_dir($dizin)) {
        if(!mkdir($dizin, '0777', true)){
   			$sonuc["sonuc"] = "hata";
   			echo json_encode($sonuc);
   			die();
        }else{
        	chmod($dizin, 0777);
        }
    }
	
    switch( $islem ){
    	case 'ekle':
    		$tek_dosya_turu_oku = $vt->select( $SQL_tek_dosya_turu_oku, array( $dosyaTuru_id, $_SESSION[ 'firma_id' ] ) ) [ 2 ];
    		if ( count( $tek_dosya_turu_oku ) > 0 ){
    			//Gelen Dosyaları Yüklemesini Yapıyoruz
				foreach ($_FILES['file']["tmp_name"] as $key => $value) {
					$aciklama   	= $aciklama == '' ? $_FILES[ "file"][ 'name' ][$key] : $aciklama;
					$evrakTarih  	= $evrakTarih == '' ? "" : date("Y-m-d", strtotime($evrakTarih));
					if( isset( $_FILES[ "file"]["tmp_name"][$key] ) and $_FILES[ "file"][ 'size' ][$key] > 0 ) {
						$dosya_adi	= uniqid() ."_".$fn->tumuKucukHarf( $fn-> turkceKarakterSil( $tekDosyaTuru[ 0 ] [ "adi" ] ) ) ."." . pathinfo( $_FILES[ "file"][ 'name' ][$key], PATHINFO_EXTENSION );
						$hedef_yol	= $dizin . '/'.$dosya_adi;
						if( move_uploaded_file( $_FILES[ "file"][ 'tmp_name' ][$key], $hedef_yol ) ) {
							$vt->insert( $SQL_dosya_kaydet, array( $dosyaTuru_id, $dosya_adi, $aciklama,$evrakTarih ) );
							$sonuc["sonuc"] = 'ok';
						}
					}
					$aciklama  = array_key_exists( 'aciklama', $_REQUEST ) ? $_REQUEST[ 'aciklama' ] : '';
				}
				
    		}else{
    			$sonuc["sonuc"] = 'hata - 4';
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
				unlink($dizin.'/'.$tek_dosya_oku[ 0 ][ "dosya" ]);

			}
			break;

    }
    
}else if ($konu == 'tur'){

	switch ($islem) {
		case 'ekle':
			if ( $adi != '' ) {
				$tur_ekle = $vt->insert( $SQL_dosya_turu_kaydet, array( $_SESSION[ 'firma_id' ], $kategori ,$adi, $tarih ) );
				$dosyaTuru_id	= $tur_ekle[ 2 ]; 
			}
				
			break;
		
		case 'guncelle':
			if ( $adi != '' ) {
				$tur_duzenle = $vt->update( $SQL_dosya_turu_duzenle, array( $kategori, $adi, $tarih, $dosyaTuru_id ) );
			}
				
			break;

		case 'sil':
			$tek_dosya_turu_oku = $vt->select( $SQL_tek_dosya_turu_oku, array( $dosyaTuru_id, $_SESSION[ 'firma_id' ] ) ) [ 2 ];

			if ( $tek_dosya_turu_oku [0] [ "dosyaSayisi" ] < 1 ) {
				$vt->delete( $SQL_dosya_turu_sil, array( $dosyaTuru_id ) );
			}
			break;
	}
}

$vt->islemBitir();
header( "Location:../../index.php?modul=firmaDosyalari&dosyaTuru_id=$dosyaTuru_id" );

?>