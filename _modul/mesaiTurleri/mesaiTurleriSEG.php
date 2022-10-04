<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$mesai_turu_id		= array_key_exists( 'mesai_turu_id', $_REQUEST )		? $_REQUEST[ 'mesai_turu_id' ]		: 0;
$alanlar		= array();
$degerler		= array();
$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "mesaiTurleri", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}

$SQL_ekle		= "INSERT INTO tb_mesai_turu SET ";
$SQL_guncelle 	= "UPDATE tb_mesai_turu SET ";


$alanlar[]		= 'firma_id';
$degerler[]		= $_SESSION['firma_id'];

foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or $alan == 'mesai_turu_id' or  $alan == 'PHPSESSID'  or  $alan == 'gunler' ) continue;

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

$alanlar[]		= 'gunler';
$degerler[]		= implode(",", $_REQUEST[ 'gunler' ]);

$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";

if( $islem == 'guncelle' ) $degerler[] = $mesai_turu_id;


$SQL_tek_mesai_turu_oku = <<< SQL
SELECT 
	*
FROM 
	tb_mesai_turu 
WHERE 
	id 			= ? AND
	firma_id 	= ? AND
	aktif 		= 1 
SQL;


$SQL_sil = <<< SQL
UPDATE
	tb_mesai_turu
SET
	aktif = 0
WHERE
	id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );
$vt->islemBaslat();
switch( $islem ) {
	case 'ekle':
		$sonuc = $vt->insert( $SQL_ekle, $degerler );
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
		else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 
		$son_eklenen_id	= $sonuc[ 2 ]; 
		$mesai_turu_id = $son_eklenen_id;
	break;
	case 'guncelle':
		//Güncellenecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise Güncellenecektir.
		$tek_mesai_turu_oku = $vt->select( $SQL_tek_mesai_turu_oku, array( $mesai_turu_id, $_SESSION['firma_id'] ) ) [ 2 ];
		if (count( $tek_mesai_turu_oku ) > 0) {
			$sonuc = $vt->update( $SQL_guncelle, $degerler );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
		}
	break;
	case 'sil':
		//Silinecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise silinecektir.
		$tek_mesai_turu_oku = $vt->select( $SQL_tek_mesai_turu_oku, array( $mesai_turu_id, $_SESSION['firma_id'] ) ) [ 2 ];
		if (count( $tek_mesai_turu_oku ) > 0) {
			$sonuc = $vt->delete( $SQL_sil, array( $mesai_turu_id ) );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt silinrken bir hata oluştu ' . $sonuc[ 1 ] );
		}
	break;
}
$vt->islemBitir();
$_SESSION[ 'sonuclar' ] 		= $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $mesai_turu_id;
header( "Location:../../index.php?modul=mesaiTurleri&islem=guncelle&mesai_turu_id=".$mesai_turu_id );
?>