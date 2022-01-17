<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'id' , $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;






$SQL_yayin_guncelle = <<< SQL
UPDATE
	tb_arac_yayinlari
SET
	 arac_id	 = ?
	,yayin_yeri_id	 = ?
	,yayin_linki	 = ?
	,yayinlandi	 = 1
WHERE
	id = ?
SQL;

$SQL_yayindan_kaldir = <<< SQL
UPDATE 
	tb_arac_yayinlari 
SET
	 yayindan_alindi = not yayindan_alindi
	,yayindan_alinma_tarihi = now()
WHERE 
	id = ?
SQL;


$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'guncelle':
			$sorgu_sonuc = $vt->update( $SQL_yayin_guncelle, array(
				 $_REQUEST[ 'arac_id' ]
				,$_REQUEST[ 'yayin_yeri_id' ]
				,$_REQUEST[ 'yayin_linki' ]
				,$id
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'yayindan_kaldir':
				$sorgu_sonuc = $vt->update( $SQL_yayindan_kaldir, array( $id ) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
	}
} else {
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'İşlem türü gönderilmediğinden dolayı işleminiz iptal edildi' );
}
$vt->islemBitir();
$___islem_sonuc[ 'sube_id' ] = $id;
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
header( 'Location: ../../index.php?modul=aracYayinlari' );


?>