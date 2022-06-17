<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem				= array_key_exists( 'islem', $_REQUEST )				? $_REQUEST[ 'islem' ]				: 'ekle';
$avansKesinti_id	= array_key_exists( 'avansKesinti_id', $_REQUEST )		? $_REQUEST[ 'avansKesinti_id' ]	: 0;
$alanlar		= array();
$degerler		= array();

 
$SQL_ekle		= "INSERT INTO tb_avans_kesinti SET ";
$SQL_guncelle 	= "UPDATE tb_avans_kesinti SET ";

foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or  $alan == 'PHPSESSID' or  $alan == 'avansKesinti_id' ) continue;

	if ( $alan == "verilis_tarihi" ){
		$deger = date( "Y-m-d", strtotime( $deger ) );
	}

	$alanlar[]		= $alan;
	$degerler[]		= $deger;
}

$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";

if( $islem == 'guncelle' ) $degerler[] = $avansKesinti_id;


$SQL_tek_avansKesinti_oku = <<< SQL
SELECT 
	*
FROM 
	tb_avans_kesinti
WHERE 
	id 		= ? AND
	aktif 	= 1 
SQL;


$SQL_sil = <<< SQL
UPDATE
	tb_avans_kesinti
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

		$avansKesinti_id = $son_eklenen_id;
	break;
	case 'guncelle':
		//Güncellenecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise Güncellenecektir.
		$tek_avanKesinti_oku = $vt->select( $SQL_tek_avansKesinti_oku, array( $avansKesinti_id ) ) [ 2 ];
		if (count( $tek_avanKesinti_oku ) > 0) {
			$sonuc = $vt->update( $SQL_guncelle, $degerler );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
			else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] );
		}
	break;
	case 'sil':
		//Silinecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise silinecektir.
		$tek_avanKesinti_oku = $vt->select( $SQL_tek_avansKesinti_oku, array( $avansKesinti_id ) ) [ 2 ];
		if (count( $tek_avanKesinti_oku ) > 0) {
			$sonuc = $vt->delete( $SQL_sil, array( $avansKesinti_id ) );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt silinrken bir hata oluştu ' . $sonuc[ 1 ] );
			else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] );
		}
	break;
}
$_SESSION[ 'sonuclar' ] 		= $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $avansKesinti_id;
header( "Location:../../index.php?modul=avansKesinti&personel_id=".$_REQUEST['personel_id'] );
?>