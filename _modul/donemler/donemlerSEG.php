<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem				= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$donem_id			= array_key_exists( 'donem_id', $_REQUEST )	? $_REQUEST[ 'donem_id' ]	: 0;
$alanlar			= array();
$degerler			= array();

$SQL_ekle			= "INSERT INTO tb_donemler SET ";
$SQL_guncelle 		= "UPDATE tb_donemler SET ";

$alanlar[]		= "universite_id";
$degerler[]		= $_SESSION['universite_id'];

foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or  $alan == 'PHPSESSID' or  $alan == 'donem_id') continue;

		$tarih_alani = explode( '-', $alan );

		if( $tarih_alani[ 0 ] == 'tarihalani' ) {
			$alan 	= $tarih_alani[ 1 ];
			$deger	= date( 'Y-m-d', strtotime( $deger ) );
			$alan = $tarih_alani[ 1 ];
			if( $deger == '' ) $deger = NULL;
			else $deger	= date( 'Y-m-d', strtotime( $deger ) );
		}
		$alanlar[]		= $alan;
		$degerler[]		= $deger;
}


$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";

if( $islem == 'guncelle' ) $degerler[] = $donem_id;


$SQL_tek_donem_oku = <<< SQL
SELECT 
	*
FROM 
	tb_donemler 
WHERE 
	id 			= ? AND
	aktif 		= 1 
SQL;


$SQL_sil = <<< SQL
UPDATE
	tb_donemler
SET
	aktif = 0
WHERE
	id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );

switch( $islem ) {
	case 'ekle':
		$sonuc = $vt->insert( $SQL_ekle, $degerler );
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
		else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 
		$son_eklenen_id	= $sonuc[ 2 ]; 
		$donem_id = $son_eklenen_id;
	break;
	case 'guncelle':
		//Güncellenecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise Güncellenecektir.
		$tek_donem_oku = $vt->select( $SQL_tek_donem_oku, array( $donem_id ) ) [ 2 ];
		if (count( $tek_donem_oku ) > 0) {
			$sonuc = $vt->update( $SQL_guncelle, $degerler );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
		}
	break;
	case 'sil':
		//Silinecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise silinecektir.
		$tek_donem_oku = $vt->select( $SQL_tek_donem_oku, array( $donem_id ) ) [ 2 ];
		if (count( $tek_donem_oku ) > 0) {
			$sonuc = $vt->delete( $SQL_sil, array( $donem_id ) );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt silinrken bir hata oluştu ' . $sonuc[ 1 ] );
		}
	break;
}
$_SESSION[ 'sonuclar' ] 		= $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $donem_id;
header( "Location:../../index.php?modul=donemler&islem=guncelle&donem_id=".$donem_id );
?>