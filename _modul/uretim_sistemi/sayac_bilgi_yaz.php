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

$sayac_id	= array_key_exists( 'sayac_id', $_REQUEST ) ? $_REQUEST[ 'sayac_id' ] : 0;
$kesim		= array_key_exists( 'kesim', $_REQUEST ) ? 1 : 0;


/* Eğer kesim bilgisi olarak 1 gelmiş ise ve sayac_id gelmiş ise işlemleri yap */
if( $kesim * 1 && $sayac_id ) {
	$aktif_iş	= $vt->select( $SQL_aktif_is );
	$makina		= $vt->select( $SQL_makina, array( $sayac_id ) );

	/* Aktif olan işin bilgileri */
	$is_id			= $aktif_iş[ 2 ][ 0 ][ "id" ];

	/* Makina bilgileri */
	$makina_id		= $makina[ 2 ][ 0 ][ "id" ]; 
	$personel_id	= $makina[ 2 ][ 0 ][ "personel_id" ]; 
	$is_parca_id	= $makina[ 2 ][ 0 ][ "is_parca_id" ]; 

	/* İş parçasını günlük iş loglarına ekle */
	$sorgu_sonuc = $vt->insert( $SQL_log_ekle, array(
		 $is_id
		,$personel_id
		,$makina_id
		,$is_parca_id
	) );
}
?>