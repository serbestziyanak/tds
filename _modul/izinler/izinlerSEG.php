<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "izinler", "guncelle" );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}


$personel_id	= array_key_exists( 'personel_id', $_REQUEST )		? $_REQUEST[ 'personel_id' ]	: 0;
$izin_durumu	= array_key_exists( 'izin_durumu', $_REQUEST )		? $_REQUEST[ 'izin_durumu' ]	: 0;

$alanlar		= array();
$degerler		= array();

$SQL_ekle		= "INSERT INTO tb_izinler SET personel_id = ?, yil = ?";
$SQL_guncelle 	= "UPDATE tb_izinler SET aktif = ? WHERE personel_id = ? AND yil = ?  ";

/*Firmaya Ait Personel Var mı */
$SQL_personel_oku = <<< SQL
SELECT
	id
FROM
	tb_personel 
WHERE
	id = ? AND 
	firma_id = ? AND 
	aktif = 1
SQL;

/*Bulunan Yıl içinde İzin Verilip verilmediğini kontrol ediliyor*/
$SQL_izin_oku = <<< SQL
SELECT
	*
FROM
	tb_izinler 
WHERE
	personel_id 	= ? AND
	yil 			= ?
SQL;

$tek_personel    = $vt->select( $SQL_personel_oku, array( $personel_id, $_SESSION[ 'firma_id' ] ) )[ 3 ];
$izinSorgula     = $vt->select( $SQL_izin_oku, array( $personel_id, date("Y") ) )[ 3 ];

if ( $tek_personel < 0 ) {
	$sonuc[ "sonuc" ] = 'hata';
	die();
}
$vt->islemBaslat();
/*Onceden verilen bir izin varsa ise o izi aktif veya pasif ediyoruz*/
if ( $izinSorgula > 0 ) {
	$sonuc = $vt->update( $SQL_guncelle, array( $izin_durumu, $personel_id, date( 'Y' ) ) );
	$sonuc[ "sonuc" ] = "ok";
}else{
	/*Verilen bir izin yok ise izin ekliyoruz*/
	$sonuc = $vt->insert( $SQL_ekle, array( $personel_id, date( 'Y' ) ) );
	$sonuc[ "sonuc" ] = "ok";
}

$vt->islemBitir();
echo json_encode($sonuc);
?>