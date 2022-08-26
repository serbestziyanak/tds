<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem				= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$fakulte_id			= array_key_exists( 'fakulte_id', $_REQUEST )	? $_REQUEST[ 'fakulte_id' ]	: 0;
$alanlar			= array();
$degerler			= array();

$SQL_ekle			= "INSERT INTO tb_fakulteler SET ";
$SQL_guncelle 		= "UPDATE tb_fakulteler SET ";

$alanlar[]		= "universite_id";
$degerler[]		= $_SESSION['universite_id'];

foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or  $alan == 'PHPSESSID' or  $alan == 'fakulte_id') continue;
		$alanlar[]		= $alan;
		$degerler[]		= $deger;
}


$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";

if( $islem == 'guncelle' ) $degerler[] = $fakulte_id;


$SQL_tek_fakulte_oku = <<< SQL
SELECT 
	*
FROM 
	tb_fakulteler 
WHERE 
	id 			= ? AND
	aktif 		= 1 
SQL;


$SQL_sil = <<< SQL
UPDATE
	tb_fakulteler
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
		$fakulte_id = $son_eklenen_id;
	break;
	case 'guncelle':
		//Güncellenecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise Güncellenecektir.
		$tek_fakulte_oku = $vt->select( $SQL_tek_fakulte_oku, array( $fakulte_id ) ) [ 2 ];
		if (count( $tek_fakulte_oku ) > 0) {
			$sonuc = $vt->update( $SQL_guncelle, $degerler );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
		}
	break;
	case 'sil':
		//Silinecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise silinecektir.
		$tek_fakulte_oku = $vt->select( $SQL_tek_fakulte_oku, array( $fakulte_id ) ) [ 2 ];
		if (count( $tek_fakulte_oku ) > 0) {
			$sonuc = $vt->delete( $SQL_sil, array( $fakulte_id ) );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt silinrken bir hata oluştu ' . $sonuc[ 1 ] );
		}
	break;
}
$_SESSION[ 'sonuclar' ] 		= $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $fakulte_id;
header( "Location:../../index.php?modul=fakulteler&islem=guncelle&fakulte_id=".$fakulte_id );
?>