<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )		? $_REQUEST[ 'islem' ]			: 'ekle';
$isParcasi_id	= array_key_exists( 'isParcasi_id', $_REQUEST )	? $_REQUEST[ 'isParcasi_id' ]	: 0;

$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "isParcalari", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}

$alanlar		= array();
$degerler		= array();
 
$SQL_ekle		= "INSERT INTO sayac_is_parcalari SET ";
$SQL_guncelle 	= "UPDATE sayac_is_parcalari SET ";


$alanlar[]		= 'firma_id';
$degerler[]		= $_SESSION['firma_id'];

foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or $alan == 'isParcasi_id'  or  $alan == 'PHPSESSID' ) continue;

	$alanlar[]		= $alan;
	$degerler[]		= $deger;
}

$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";


if( $islem == 'guncelle' ) $degerler[] = $isParcasi_id;


$SQL_tek_isParcasi_oku = <<< SQL
SELECT *
FROM 
	sayac_is_parcalari 
WHERE 
	id 			= ? AND
	firma_id 	= ? AND
	aktif 		= 1 
SQL;


$SQL_sil = <<< SQL
UPDATE
	sayac_is_parcalari
SET
	aktif = 0
WHERE
	id 			= ? AND
	firma_id 	= ? 
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );
$vt->islemBaslat();
switch( $islem ) {
	case 'ekle':
		$sonuc = $vt->insert( $SQL_ekle, $degerler );
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
		else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 
	break;
	case 'guncelle':
		//Güncellenecek olan isParcasi giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise Güncellenecektir.
		$tek_isParcasi_oku = $vt->select( $SQL_tek_isParcasi_oku, array( $isParcasi_id, $_SESSION['firma_id'] ) ) [ 2 ];
		if (count( $tek_isParcasi_oku ) > 0) {
			$sonuc = $vt->update( $SQL_guncelle, $degerler );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
			else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 
		}
	break;
	case 'sil':
		//Silinecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise silinecektir.
		$tek_isParcasi_oku = $vt->select( $SQL_tek_isParcasi_oku, array( $isParcasi_id, $_SESSION['firma_id'] ) ) [ 2 ];
		if (count( $tek_isParcasi_oku ) > 0) {
			$sonuc = $vt->delete( $SQL_sil, array( $isParcasi_id, $_SESSION[ 'firma_id' ] ) );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt silinrken bir hata oluştu ' . $sonuc[ 1 ] );
		}
	break;
}
$vt->islemBitir();
$_SESSION[ 'sonuclar' ] 		= $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $isParcasi_id;
header( "Location:../../index.php?modul=isParcalari" );
?>