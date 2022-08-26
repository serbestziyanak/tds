<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();
$id		= array_key_exists( 'id' , $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$super	= array_key_exists( 'sistem_kullanici_super' , $_REQUEST ) ? 1 : 0; /* Kapalıysa gelmiyor zaten. açıksa da değeri birdir */

if( $_REQUEST[ 'sistem_kullanici_dogum_tarihi' ] == '' ) $dogum_tarihi = NULL;
else $dogum_tarihi = date( 'Y-m-d', strtotime( $_REQUEST[ 'sistem_kullanici_dogum_tarihi' ] ) );

$universiteler = implode(",", $_REQUEST[ 'universite_id' ]);

$SQL_ekle = <<< SQL
INSERT INTO
	tb_sistem_kullanici
SET
	 adi			= ?
	,soyadi			= ?
	,email			= ?
	,telefon		= ?
	,tc_no			= ?
	,dogum_tarihi	= ?
	,rol_id			= ?
	,super			= ?
	,sifre			= ?
	,universiteler	= ?
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_sistem_kullanici
SET
	 adi			= ?
	,soyadi			= ?
	,email			= ?
	,telefon		= ?
	,tc_no			= ?
	,dogum_tarihi	= ?
	,rol_id			= ?
	,super			= ?
	,universiteler	= ?
WHERE
	id = ?
SQL;
 
$SQL_sil = <<< SQL
	DELETE FROM tb_sistem_kullanici WHERE id = ?
SQL;

$SQL_super_kullanici_sayisi = <<< SQL
	SELECT id FROM tb_sistem_kullanici WHERE super = 1
SQL;

$SQL_kullanici_sayisi = <<< SQL
	SELECT id FROM tb_sistem_kullanici
SQL;

/* Güncellenecek kişi süper mi ona bakalım. eğer süper ise ve tek süper kullanıcı var ise ve bu kullanıcının süper yetkisi silinmeye çalışılıyorsa engelle */
$SQL_guncellenen_kisi_super_sorgula = <<<SQL
SELECT
	super
FROM
	tb_sistem_kullanici
WHERE
	id = ?
SQL;

$SQL_resim_guncelle = <<<SQL
UPDATE
	tb_sistem_kullanici
SET
	resim = ?
WHERE
	id = ?
SQL;


$SQL_kullanici_sifresi_oku = <<< SQL
	SELECT sifre FROM tb_sistem_kullanici WHERE id = ?
SQL;

$SQL_kullanici_sifresi_guncelle = <<< SQL
UPDATE
	tb_sistem_kullanici
SET
	sifre = ?
WHERE
	id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'ekle':
			$sorgu_sonuc = $vt->insert( $SQL_ekle, array(
				 $fn->ilkHarfleriBuyut( $_REQUEST[ 'sistem_kullanici_adi' ] )
				,$fn->tumuBuyukHarf( $_REQUEST[ 'sistem_kullanici_soyadi' ] )
				,$_REQUEST[ 'sistem_kullanici_email' ]
				,$_REQUEST[ 'sistem_kullanici_telefon' ]
				,$_REQUEST[ 'sistem_kullanici_tc_no' ]
				,$dogum_tarihi
				,$_REQUEST[ 'sistem_kullanici_rol_id' ]
				,$super
				,md5( $_REQUEST[ 'sistem_kullanici_sifre' ] )
				,$universiteler
			) );
			$resim_sonuc = $fn->resimYukle( 'input_sistem_kullanici_resim', $sorgu_sonuc[ 2 ] );
			if( $resim_sonuc[ 0 ] ) {
				$sorgu_sonuc = $vt->update( $SQL_resim_guncelle, array( $resim_sonuc[ 1 ], $sorgu_sonuc[ 2 ] ) );
			}
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'guncelle':
			$sorgu_sonuc_super			= $vt->rowCount( $SQL_super_kullanici_sayisi );
			$kullanici_supermi_sonuc	= $vt->selectSingle( $SQL_guncellenen_kisi_super_sorgula, array( $id ) );
			$kullanici_sifresi_oku		= $vt->selectSingle( $SQL_kullanici_sifresi_oku, array( $id ) );
			
			if( $sorgu_sonuc_super[ 2 ] == 1 && $kullanici_supermi_sonuc[ 2 ][ 'super' ] * 1 > 0 && $super == 0 ) {
				$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'Sistemde en az bir <b>SÜPER</b> kullanıcı bulunmalıdır. Bu kullanıcının <b>SÜPER</b> yetkisini kaldıramazsınız. İşlem iptal edildi' );
			} else {
				$resim_sonuc = $fn->resimYukle( 'input_sistem_kullanici_resim', $id );
				if( $resim_sonuc[ 0 ] ) {
					$vt->update( $SQL_resim_guncelle, array( $resim_sonuc[ 1 ], $id ) );
				}
				$sorgu_sonuc = $vt->update( $SQL_guncelle, array(
					 $fn->ilkHarfleriBuyut( $_REQUEST[ 'sistem_kullanici_adi' ] )
					,$fn->tumuBuyukHarf( $_REQUEST[ 'sistem_kullanici_soyadi' ] )
					,$_REQUEST[ 'sistem_kullanici_email' ]
					,$_REQUEST[ 'sistem_kullanici_telefon' ]
					,$_REQUEST[ 'sistem_kullanici_tc_no' ]
					,$dogum_tarihi
					,$_REQUEST[ 'sistem_kullanici_rol_id' ]
					,$super
					,$universiteler
					,$id
				) );
				/* Gelen şifre ile mevcut şifreyi karşılaştır. Aynı değil ise güncelle.*/
				if( $kullanici_sifresi_oku[ 2 ][ 'sifre' ] != $_REQUEST[ 'sistem_kullanici_sifre' ] ) {
					$sorgu_sonuc = $vt->update( $SQL_kullanici_sifresi_guncelle, array( md5( $_REQUEST[ 'sistem_kullanici_sifre' ] ), $id ) );
				}
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
			}
		break;
		case 'sil':
			$sorgu_sonuc_toplam		= $vt->rowCount( $SQL_kullanici_sayisi );
			$sorgu_sonuc_super		= $vt->rowCount( $SQL_super_kullanici_sayisi );
			if( $sorgu_sonuc_toplam[ 2 ] < 2 && $_REQUEST[ 'super' ] * 1 > 0 ) {
				$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'Sistemde en az bir <b>SÜPER</b> kullanıcı bulunmalıdır. Bu kullanıcıyı silemesiniz. İşlem iptal edildi' );
			} else if( $sorgu_sonuc_super[ 2 ] == 1 && $_REQUEST[ 'super' ] * 1 > 0 ) {
				$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'Sistemde en az bir <b>SÜPER</b> kullanıcı bulunmalıdır. Bu kullanıcıyı silemesiniz. İşlem iptal edildi' );
			} else if( $sorgu_sonuc_toplam[ 2 ] < 2 ) {
				$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'Sistemde en az bir kullanıcı bulunmalıdır. Bu kullanıcıyı silemesiniz. İşlem iptal edildi' );
			} else {
				$sorgu_sonuc = $vt->delete( $SQL_sil, array( $id ) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
			}
		break;
	}
} else {
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'İşlem türü gönderilmediğinden dolayı işleminiz iptal edildi' );
}
$vt->islemBitir();
$___islem_sonuc[ 'id' ] = $id;
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
header("Location:../../index.php?modul=sistemKullanicilari")
?>