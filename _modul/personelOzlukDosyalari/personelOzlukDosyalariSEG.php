<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id	= array_key_exists( 'personel_id', $_REQUEST )		? $_REQUEST[ 'personel_id' ]	: 0;
$dosya_turu_id	= array_key_exists( 'dosya_turu_id', $_REQUEST )	? $_REQUEST[ 'dosya_turu_id' ]	: 0;
$dosya_id		= array_key_exists( 'dosya_id', $_REQUEST )			? $_REQUEST[ 'dosya_id' ]		: 0;
$dosya_durumu	= array_key_exists( 'dosya_durumu', $_REQUEST )		? $_REQUEST[ 'dosya_durumu' ]	: 0;

$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "personelOzlukDosyalari", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}

$SQL_tum_personel_oku = <<< SQL
SELECT
	tc_no
FROM
	tb_personel
WHERE
	aktif = 1 AND id = ? AND firma_id = ?
SQL;

//Dosya Yüklerken Onceden Bu Dosyanın Var olup Olmadığını Kontrol etme
$SQL_personel_ozluk_dosyasi_varmi = <<< SQL
SELECT
	*
FROM
	tb_personel_ozluk_dosyalari 
WHERE
	dosya_turu_id 		= ?
	AND personel_id 	=?
SQL;

//Silinen Dosyayı Çağırıyoruz
$SQL_personel_ozluk_dosyasi = <<< SQL
SELECT
	*
FROM
	tb_personel_ozluk_dosyalari
WHERE
	id 					= ?
SQL;


$SQL_dosya_turu_adi = <<< SQL
SELECT
	*
FROM
	tb_personel_ozluk_dosya_turleri
WHERE
	id = ?
SQL;


$SQL_dosya_kaydet = <<< SQL
INSERT INTO
	tb_personel_ozluk_dosyalari
SET
	 personel_id		= ?
	,dosya_turu_id		= ?
	,dosya				= ?
	,tarih 				= now()
SQL;

$SQL_dosya_sil = <<< SQL
DELETE FROM
	tb_personel_ozluk_dosyalari
WHERE
	id = ?
SQL;

$SQL_dosya_durum_guncelle = <<< SQL
UPDATE
	tb_personel
SET
	ozluk_dosya_durumu = ?
WHERE
	id = ?
SQL;

// Firmaya ait personel olup olmadıgını kontrol etme personel yok ise işlemi sonlandırılıyors
$personel = $vt->select( $SQL_tum_personel_oku, array( $personel_id, $_SESSION['firma_id'] ) );

if( count( $personel ) < 1 ){
	$sonuc[ "sonuc" ] = 'hata';
	die();
}
$vt->islemBaslat();
switch( $islem ) {
	case 'ekle':
		$dosya_turu_adi		= $vt->select( $SQL_dosya_turu_adi, array( $dosya_turu_id ) );

		$dosya_turu_adi		= $dosya_turu_adi[ 2 ][ 0 ][ 'adi' ];
		$tc_no				= $personel[ 2 ][ 0 ][ 'tc_no' ];

		$dosya_turu_adi     = str_replace("/", "ve", $dosya_turu_adi);
		foreach ($_FILES['OzlukDosya']["tmp_name"] as $key => $value) {
			if( isset( $_FILES[ "OzlukDosya"]["tmp_name"][$key] ) and $_FILES[ "OzlukDosya"][ 'size' ][$key] > 0 ) {
				$dosya_adi	= $tc_no . "_". rand() ."_".  $dosya_turu_adi . "." . pathinfo( $_FILES[ "OzlukDosya"][ 'name' ][$key], PATHINFO_EXTENSION );
				$dizin		= "../../personel_ozluk_dosyalari/";
				$hedef_yol	= $dizin.$dosya_adi;
				if( move_uploaded_file( $_FILES[ "OzlukDosya"][ 'tmp_name' ][$key], $hedef_yol ) ) {
					$vt->insert( $SQL_dosya_kaydet, array( $personel_id, $dosya_turu_id, $dosya_adi ) );
				}
			}
		}
	break;
	case 'sil':

		//Silinecek dosyanın bilgileri aldık
		$personel_ozluk_dosyasi = $vt->select( $SQL_personel_ozluk_dosyasi, array( $dosya_id) )[2][0];

		$vt->delete( $SQL_dosya_sil, array( $dosya_id ) );
		$dizin		= "../../personel_ozluk_dosyalari/";
		//Sunucudan Dosyayı Siliyoruz.
		unlink($dizin.$personel_ozluk_dosyasi["dosya"]);
	break;

	case 'dosyadurumu':
		$sonuc[ "sonuc" ] = 'hata';
		if ( $dosya_durumu == 0 or $dosya_durumu == 1 ) {
			$personel_dosya_durumu_guncelle = $vt->update($SQL_dosya_durum_guncelle,array($dosya_durumu,$personel_id));
			if ( $personel_dosya_durumu_guncelle ){
				$sonuc["sonuc"] = "ok";
				echo json_encode($sonuc);
				die();
			}else{
				$sonuc["sonuc"] = "hata";
				echo json_encode($sonuc);
				die();
			}
			

		}
	break;
	
}
$vt->islemBitir();
header( "Location:../../index.php?modul=personelOzlukDosyalari&personel_id=$personel_id" );
?>