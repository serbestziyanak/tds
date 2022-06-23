<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= "guncelle";
$personel_id	= array_key_exists( 'personel_id', $_REQUEST )		? $_REQUEST[ 'personel_id' ]	: 0;
$alanlar		= array();
$degerler		= array();

 
$SQL_ekle		= "INSERT INTO tb_genel_ayarlar SET ";
$SQL_guncelle 	= "UPDATE tb_genel_ayarlar SET ";


foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or  $alan == 'PHPSESSID' ) continue;

	if ( $alan == 'giris_cikis_denetimi_grubu' or $alan == 'puantaj_hesaplama_grubu' or $alan == 'devamli_gelen' ) {
		$deger = implode("", $deger);
	}

	if ( $alan == 'tutanak_olustur' ){
		$deger = $deger == on ? 1 : 0;
	}

	$alanlar[]		= $alan;
	$degerler[]		= $deger;
}
if ( !array_key_exists("tutanak_olustur", $_REQUEST) ){
	$alanlar[]		= 'tutanak_olustur';
	$degerler[]		= 0;
}

$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE firma_id = ?";

if( $islem == 'guncelle' ) $degerler[] = $_SESSION['firma_id'];

$SQL_sil = <<< SQL
UPDATE
	tb_genel_ayarlar
SET
	aktif = 0
WHERE
	id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );

switch( $islem ) {
	
	case 'guncelle':
		$sonuc = $vt->update( $SQL_guncelle, $degerler );
		
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
	break;
}
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $personel_id;
header( "Location:../../index.php?modul=genelAyarlar&islem=guncelle" );
?>