<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'id' , $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "firmalar", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}

$SQL_ekle = <<< SQL
INSERT INTO
	tb_firmalar
SET
	 adi = ?
	,firma = ?
	,unvan = ?
	,vergi_no = ?
	,vergi_dairesi = ?
	,ticaret_sicil_no = ?
	,yetki_belgesi_no = ?
	,tel = ?
	,adres = ?
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_firmalar
SET
	 adi = ?
	,firma = ?
	,unvan = ?
	,vergi_no = ?
	,vergi_dairesi = ?
	,ticaret_sicil_no = ?
	,yetki_belgesi_no = ?
	,tel = ?
	,adres = ?
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
UPDATE
	tb_firmalar
SET
	 aktif = 0
WHERE
	id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $islem ) {
		case 'ekle':
			$sorgu_sonuc = $vt->insert( $SQL_ekle, array(
				 $fn->ilkHarfleriBuyut( $_REQUEST[ 'firma_adi' ] )
				,$_REQUEST[ 'firma' ]
				,$_REQUEST[ 'unvan' ]
				,$_REQUEST[ 'vergi_no' ]
				,$_REQUEST[ 'vergi_dairesi' ]
				,$_REQUEST[ 'ticaret_sicil_no' ]
				,$_REQUEST[ 'yetki_belgesi_no' ]
				,$_REQUEST[ 'tel' ]
				,$_REQUEST[ 'adres' ]
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'guncelle':
			$sorgu_sonuc = $vt->update( $SQL_guncelle, array(
				 $fn->ilkHarfleriBuyut( $_REQUEST[ 'firma_adi' ] )
				,$_REQUEST[ 'firma' ]
				,$_REQUEST[ 'unvan' ]
				,$_REQUEST[ 'vergi_no' ]
				,$_REQUEST[ 'vergi_dairesi' ]
				,$_REQUEST[ 'ticaret_sicil_no' ]
				,$_REQUEST[ 'yetki_belgesi_no' ]
				,$_REQUEST[ 'tel' ]
				,$_REQUEST[ 'adres' ]
				,$id
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'sil':
				$sorgu_sonuc = $vt->update( $SQL_sil, array( $id ) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
	}
} else {
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'İşlem türü gönderilmediğinden dolayı işleminiz iptal edildi' );
}
$vt->islemBitir();
$___islem_sonuc[ 'firma_id' ] = $id;
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
header( 'Location: ../../index.php?modul=firmalar&firma_id='.$id );


?>