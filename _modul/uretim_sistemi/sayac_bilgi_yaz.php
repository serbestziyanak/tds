<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$SQL_aktif_is = <<<SQL
SELECT
	id
FROM
	sayac_isler
WHERE
	aktif = 1 AND bitis_tarihi IS NULL
LIMIT 1
SQL;


$SQL_sayac_cihaz = <<<SQL
SELECT
	id
FROM
	sayac_sayac_cihazlari
WHERE
	sayac_mac = ?
SQL;


$SQL_makina = <<<SQL
SELECT
	 id
	,personel_id
	,is_parca_id
FROM
	sayac_makina
WHERE
	sayac_cihaz_id = ?
SQL;


$SQL_log_ekle = <<<SQL
INSERT INTO
	sayac_is_loglari_gunluk
SET
	 is_id				= ?
	,personel_id		= ?
	,makina_id			= ?
	,is_parca_id		= ?
	,tarih				= now()
SQL;


$sayac_mac	= array_key_exists( 'sayac_mac', $_REQUEST ) ? $_REQUEST[ 'sayac_mac' ] : 0;
$MAC_regx = '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/';


/* Geçerli bir mac adresi geldiyse işlem yap*/
if ( preg_match( $MAC_regx, $sayac_mac ) ) {

	/* Anlık bilgi gönderen cihazın idsini bul */
	$cihaz			= $vt->select( $SQL_sayac_cihaz, array( $sayac_mac ) );
	$sayac_cihaz_id	= $cihaz[ 2 ][ 0 ][ "id" ];

	/* Aktif olan işin bilgileri */
	$aktif_is	= $vt->select( $SQL_aktif_is );
	$is_id		= $aktif_is[ 2 ][ 0 ][ "id" ];

	/* Makina bilgileri */
	$makina			= $vt->select( $SQL_makina, array( $sayac_cihaz_id ) );
	$makina_id		= $makina[ 2 ][ 0 ][ "id" ]; 
	$personel_id	= $makina[ 2 ][ 0 ][ "personel_id" ]; 
	$is_parca_id	= $makina[ 2 ][ 0 ][ "is_parca_id" ];

	$sorgu_sonuc = $vt->insert( $SQL_log_ekle, array(
		 $is_id
		,$personel_id
		,$makina_id
		,$is_parca_id
	) );
}
echo time();
?>