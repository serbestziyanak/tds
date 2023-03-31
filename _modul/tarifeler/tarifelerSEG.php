<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "tarifeler", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}



$tarife_id		= array_key_exists( 'tarife_id', $_REQUEST )		? $_REQUEST[ 'tarife_id' ]		: 0;
$alanlar		= array();
$degerler		= array();

 
$SQL_ekle		= "INSERT INTO tb_tarifeler SET ";
$SQL_guncelle 	= "UPDATE tb_tarifeler SET ";


$alanlar[]		= 'firma_id';
$degerler[]		= $_SESSION['firma_id'];

foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or $alan == 'tarife_id' or  $alan == 'PHPSESSID' ) continue;

	$tarih_alani = explode( '-', $alan );
	if( $tarih_alani[ 0 ] == 'tarihalani' ) {

		$alan 	= $tarih_alani[ 1 ];
		$deger	= date( 'Y-m-d', strtotime( $deger ) );

		$alan = $tarih_alani[ 1 ];
		if( $deger == '' ) $deger = NULL;
		else $deger	= date( 'Y-m-d', strtotime( $deger ) );
	}
	if ( $alan == 'tatil' ){
		$deger = $deger == "on" ? 1 : 0;
	}
	if ( $alan == 'maasa_etki_edilsin' ){
		$deger = $deger == "on" ? 1 : 0;
	}
	
	if ( $alan == 'grup_id') {
		$deger = implode(",", $deger);
	}
	$alanlar[]		= $alan;
	$degerler[]		= $deger;
}

if ( !array_key_exists("tatil", $_REQUEST) ){
	$alanlar[]		= 'tatil';
	$degerler[]		= 0;
}
if ( !array_key_exists("maasa_etki_edilsin", $_REQUEST) ){
	$alanlar[]		= 'maasa_etki_edilsin';
	$degerler[]		= 0;
}

$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";

echo $SQL_guncelle;

if( $islem == 'guncelle' ) $degerler[] = $tarife_id;


$SQL_tek_tarife_oku = <<< SQL
SELECT 
	t.*,
	mt.adi AS mesai_adi
FROM 
	tb_tarifeler AS t
INNER JOIN tb_mesai_turu AS mt ON 
	mt.id 	= t.mesai_turu
WHERE 
	t.id 		= ? AND
	t.firma_id 	= ? AND
	t.aktif 	= 1 
SQL;


$SQL_sil = <<< SQL
UPDATE
	tb_tarifeler
SET
	aktif = 0
WHERE
	id = ?
SQL;

// echo '<pre>';
// print_r($alanlar);
// echo '<br>';
// print_r($degerler);
// die();
$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );
$vt->islemBaslat();
switch( $islem ) {
	case 'ekle':
		$sonuc = $vt->insert( $SQL_ekle, $degerler );
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
		else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 
		$son_eklenen_id = $sonuc[2];
		$tarife_id = $son_eklenen_id;
	break;
	case 'guncelle':
		//Güncellenecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise Güncellenecektir.
		$tek_tarife_oku = $vt->select( $SQL_tek_tarife_oku, array( $tarife_id, $_SESSION['firma_id'] ) ) [ 2 ];
		if (count( $tek_tarife_oku ) > 0) {
			$sonuc = $vt->update( $SQL_guncelle, $degerler );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
		}
	break;
	case 'sil':
		//Silinecek olan tarife giriş yapılan firmaya mı ait oldugu kontrol ediliyor Eger firmaya ait ise silinecektir.
		$tek_tarife_oku = $vt->select( $SQL_tek_tarife_oku, array( $tarife_id, $_SESSION['firma_id'] ) ) [ 2 ];
		if (count( $tek_tarife_oku ) > 0) {
			$sonuc = $vt->delete( $SQL_sil, array( $tarife_id ) );
			if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt silinrken bir hata oluştu ' . $sonuc[ 1 ] );
		}
	break;
}
$vt->islemBitir();
$_SESSION[ 'sonuclar' ] 		= $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $tarife_id;
header( "Location:../../index.php?modul=tarifeler&islem=guncelle&tarife_id=".$tarife_id );
?>