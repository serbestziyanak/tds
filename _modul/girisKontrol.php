<?php
include "../_cekirdek/fonksiyonlar.php";
session_start();
$_SESSION[ 'firma_turu' ] = $_POST[ 'firma' ];

$vt = new VeriTabani();

$k	= trim( $_POST[ 'kulad' ] );
$s	= trim( $_POST[ 'sifre' ] );

$SQL_kontrol = <<< SQL
SELECT
	 k.*
	,CASE k.super WHEN 1 THEN "Süper" ELSE r.adi END AS rol_adi
FROM
	tb_sistem_kullanici AS k
JOIN
	tb_roller AS r ON k.rol_id = r.id
WHERE
	k.email = ? AND
	k.sifre = ?
LIMIT 1
SQL;

$sorguSonuc = $vt->selectSingle( $SQL_kontrol, array( $k, md5( $s ) ) );
if( !$sorguSonuc[ 0 ] ) {
	$kullaniciBilgileri	= $sorguSonuc[ 2 ];
	if( $kullaniciBilgileri[ 'id' ] * 1 > 0 ) {
		$_SESSION[ 'kullanici_id' ]		= $kullaniciBilgileri[ 'id' ];
		$_SESSION[ 'adi' ]				= $kullaniciBilgileri[ 'adi' ];
		$_SESSION[ 'soyadi' ]			= $kullaniciBilgileri[ 'soyadi' ];
		$_SESSION[ 'ad_soyad' ]			= $kullaniciBilgileri[ 'adi' ] . ' ' . $kullaniciBilgileri[ 'soyadi' ];
		$_SESSION[ 'kullanici_resim' ]	= $kullaniciBilgileri[ 'resim' ];
		$_SESSION[ 'rol_id' ]			= $kullaniciBilgileri[ 'rol_id' ];
		$_SESSION[ 'rol_adi' ]			= $kullaniciBilgileri[ 'rol_adi' ];
		$_SESSION[ 'sube_id' ]			= $kullaniciBilgileri[ 'sube_id' ];
		$_SESSION[ 'subeler' ]			= $kullaniciBilgileri[ 'subeler' ];
		$_SESSION[ 'giris' ]			= true;
		$_SESSION[ 'giris_var' ]		= 'evet';
		$_SESSION[ 'yil' ]				= date('Y');
		$_SESSION[ 'super' ]			= $kullaniciBilgileri[ 'super' ];
		$_SESSION[ 'universite_id' ]	= $kullaniciBilgileri[ 'universiteler' ];
	} else {
		$_SESSION[ 'giris_var' ] = 'hayir';
	}
} else {
	$_SESSION[ 'giris_var' ] = 'hayir';
}
header( "Location: ../index.php" );
?>