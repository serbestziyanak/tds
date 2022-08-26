<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem				= array_key_exists( 'islem', $_REQUEST )		? $_REQUEST[ 'islem' ]		: 'ekle';
$program_id			= array_key_exists( 'program_id', $_REQUEST )		? $_REQUEST[ 'program_id' ]	: 0;
$bolum_id			= array_key_exists( 'bolum_id', $_REQUEST )	? $_REQUEST[ 'bolum_id' ]	: 0;
$alanlar			= array();
$degerler			= array();

$SQL_ekle			= "INSERT INTO tb_programlar SET ";
$SQL_guncelle 		= "UPDATE tb_programlar SET ";


$alanlar[]			= "universite_id";
$degerler[]			= $_SESSION['universite_id'];

foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or  $alan == 'PHPSESSID' or  $alan == 'program_id') continue;
		$alanlar[]		= $alan;
		$degerler[]		= $deger;
}


$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";

if( $islem == 'guncelle' ) $degerler[] = $program_id;


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


$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );

switch( $islem ) {
	case 'ekle':
		$sonuc = $vt->insert( $SQL_ekle, $degerler );
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
		else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 
		$son_eklenen_id	= $sonuc[ 2 ]; 
		$program_id = $son_eklenen_id;
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
header( "Location:../../index.php?modul=programlar&islem=guncelle&program_id=".$program_id."&bolum_id=".$bolum_id );
?>