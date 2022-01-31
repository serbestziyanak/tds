<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt					= new VeriTabani();
$fn					= new Fonksiyonlar();
$grup_id		= array_key_exists( 'grup_id' , $_REQUEST ) ? $_REQUEST[ 'grup_id' ] : 0;
$islem				= array_key_exists( 'islem' , $_REQUEST ) ? $_REQUEST[ 'islem' ] : '';
$___islem_sonuc		= array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );



$SQL_ekle = <<< SQL
INSERT INTO
	tb_gruplar
SET
	 adi = ?
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_gruplar
SET
	 adi = ?
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
UPDATE
	tb_gruplar
SET
	 aktif = 0
WHERE
	id = ?
SQL;


switch( $_REQUEST[ 'islem' ] ) {
	case 'ekle':
		$sonuc = $vt->insert( $SQL_ekle, array(
			 $fn->ilkHarfleriBuyut( $_REQUEST[ 'grup_adi' ] )
		) );
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
	break;
	case 'guncelle':
		$sonuc = $vt->update( $SQL_guncelle, array(
			 $fn->ilkHarfleriBuyut( $_REQUEST[ 'grup_adi' ] )
			,$grup_id
		) );
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
	break;
	case 'sil':
		$sonuc = $vt->update( $SQL_sil, array( $grup_id ) );
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sonuc[ 1 ] );
	break;
}

$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
header( 'Location: ../../index.php?modul=gruplar' );


?>