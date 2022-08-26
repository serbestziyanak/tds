<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem				= array_key_exists( 'islem', $_REQUEST )		? $_REQUEST[ 'islem' ]		: 'ekle';

/**/
$SQL_ders_yili_donem_oku = <<< SQL
SELECT 
	*
FROM 
	tb_ders_yili_donemleri
WHERE 
	program_id 		= ? AND
	ders_yili_id 	= ? AND
	donem_id		= ?
SQL;

/*Ders Yılı Domei Ekleme*/
$SQL_ders_yili_donem_ekle = <<< SQL
INSERT INTO 
	tb_ders_yili_donemleri 
SET
	program_id 		= ?,
	ders_yili_id 	= ?,
	donem_id		= ?
SQL;


/*DERSSLERİ EKLEME İŞLEMİ*/
$SQL_donem_dersleri = <<< SQL
INSERT INTO 
	tb_donem_dersleri
SET
	ders_yili_donem_id 	= ?,
	ders_id 			= ?,
	teorik_ders_saati 	= ?,
	uygulama_ders_saati = ?
SQL;

/**/
$SQL_tek_program_oku = <<< SQL
SELECT 
	*
FROM 
	tb_programlar 
WHERE 
	id 			= ? AND
	aktif 		= 1 
SQL;

$SQL_sil = <<< SQL
UPDATE
	tb_programlar
SET
	aktif = 0
WHERE
	id = ?
SQL;



$donem_degerler = array( $_REQUEST['program_id'], $_REQUEST['ders_yili_id'], $_REQUEST['donem_id'] );

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );

switch( $islem ) {
	case 'ekle':
		/*Programın Önceden eklenip eklenmediğini kontrol ediyoruz*/
		$ders_yili_donem_oku = $vt->select( $SQL_ders_yili_donem_oku, $donem_degerler )[2];

		if ( count($ders_yili_donem_oku) > 0) {
			$ders_yili_donem_id = $ders_yili_donem_oku[0][ "id" ];
		}else{
			$sonuc = $vt->insert( $SQL_ders_yili_donem_ekle, $donem_degerler );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
			else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 
			$ders_yili_donem_id	= $sonuc[ 2 ]; 
		}

		foreach ($_REQUEST['ders_id'] as $ders_id) {
			$ders_degerler[] = $ders_yili_donem_id;
			$ders_degerler[] = $ders_id;
			$ders_degerler[] = $_REQUEST['teorik_ders_saati-'.$ders_id];
			$ders_degerler[] = $_REQUEST['uygulama_ders_saati-'.$ders_id];

			$sonuc = $vt->insert( $SQL_donem_dersleri, $ders_degerler );

			$ders_degerler = array();
		}

	break;
	case 'guncelle':
		//Güncellenecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise Güncellenecektir.
		$tek_program_oku = $vt->select( $SQL_tek_program_oku, array( $program_id ) ) [ 2 ];
		if (count( $tek_program_oku ) > 0) {
			$sonuc = $vt->update( $SQL_guncelle, $degerler );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
		}
	break;
	case 'sil':
		//Silinecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise silinecektir.
		$tek_program_oku = $vt->select( $SQL_tek_program_oku, array( $program_id ) ) [ 2 ];
		if (count( $tek_program_oku ) > 0) {
			$sonuc = $vt->delete( $SQL_sil, array( $program_id ) );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt silinrken bir hata oluştu ' . $sonuc[ 1 ] );
		}
	break;
}

$_SESSION[ 'sonuclar' ] 		= $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $program_id;
header( "Location:../../index.php?modul=donemDersleri");
?>