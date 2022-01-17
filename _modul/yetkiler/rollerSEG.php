<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'rol_id' , $_REQUEST ) ? $_REQUEST[ 'rol_id' ] : 0;

$SQL_ekle = <<< SQL
INSERT INTO
	tb_roller
SET
	 adi = ?
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_roller
SET
	 adi = ?
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
	DELETE FROM tb_roller WHERE id = ?
SQL;

/* varsayılan rol ataması yap varsayılan rol id 1 dir */
$SQL_varsayilan_rol_atamasi_yap = <<< SQL
UPDATE
	tb_sistem_kullanici
SET
	 rol_id = 1 
WHERE
	rol_id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'ekle':
			$sorgu_sonuc = $vt->insert( $SQL_ekle, array( $fn->ilkHarfleriBuyut( $_REQUEST[ 'yetkiler_rol_adi' ] ) ) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'guncelle':
			$sorgu_sonuc = $vt->update( $SQL_guncelle, array(
				 $fn->ilkHarfleriBuyut( $_REQUEST[ 'yetkiler_rol_adi' ] )
				,$id
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
			
		break;
		case 'sil':
			/* Silinecek rolde kullanıcılar varsa onlara varsayılan rol ataması yapalım*/
			$sorgu_sonuc = $vt->update( $SQL_varsayilan_rol_atamasi_yap, array( $id ) );
			if( $sorgu_sonuc[ 0 ] ) {
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Varsayılan rol ataması yapılırken bir hata oluştu. İşlem iptal edildi ' . $sorgu_sonuc[ 1 ] );
			} else {
				/* ilgili rolü sil */
				$sorgu_sonuc = $vt->delete( $SQL_sil, array( $id ) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
			}
	break;
	}
} else {
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'İşlem türü gönderilmediğinden dolayı işleminiz iptal edildi' );
}
$vt->islemBitir();
$___islem_sonuc[ 'rol_id' ] = $id;
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
header( 'Location: ../../index.php?modul=yetkiler' );


?>