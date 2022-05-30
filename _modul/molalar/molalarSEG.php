<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$tarife_id		= array_key_exists( 'tarife_id', $_REQUEST )		? $_REQUEST[ 'tarife_id' ]		: 0;
$mola_id		= array_key_exists( 'mola_id', $_REQUEST )			? $_REQUEST[ 'mola_id' ]		: 0;
$alanlar		= array();
$degerler		= array();

 
$SQL_ekle		= "INSERT INTO tb_molalar SET ";
$SQL_guncelle 	= "UPDATE tb_molalar SET ";

/*Tarifeye Ait Molaları Getirme*/
$SQL_mola_getir = <<< SQL
SELECT 
	*
FROM 
	tb_molalar
INNER JOIN tb_tarifeler ON tb_molalar.tarife_id = tb_tarifeler.id
WHERE 
	tb_molalar.id 	= ? AND
	tarife_id		= ? AND
	firma_id 		= ?
SQL;


$SQL_tum_mola_sil = <<< SQL
DELETE FROM
	tb_molalar
WHERE
	tarife_id = ?
SQL;

$SQL_tek_mola_sil = <<< SQL
DELETE FROM
	tb_molalar
WHERE
	id = ?
SQL;


$alanlar		= array("tarife_id","mola_baslangic","mola_bitis");

$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";

switch( $islem ) {

	case 'guncelle':
		/*Tüm  Molaları silip tekrar yüklemesini yapacağız*/

		$vt->delete( $SQL_tum_mola_sil, array( $tarife_id ) );

		foreach ($_REQUEST["mola_baslangic"] as $alan => $deger) {
			$degerler   = array();
			$degerler[] = $_REQUEST['tarife_id'];
			$degerler[] = $_REQUEST["mola_baslangic"][$alan];
			$degerler[] = $_REQUEST["mola_bitis"][$alan];
			$sonuc = $vt->insert( $SQL_ekle, $degerler );
		}
		
	break;
	case 'sil':
		/*Gelen tarife aid firmaya ait mi*/
		$mola_sorgula = $vt->select( $SQL_mola_getir, array( $mola_id, $tarife_id, $_SESSION['firma_id'] ) )[2];
		if ( count( $mola_sorgula ) > 0 ) {
			$vt->delete( $SQL_tek_mola_sil, array( $mola_id ) );	
		}

	break;
}
header( "Location:../../index.php?modul=molalar&islem=guncelle&tarife_id=".$tarife_id );
?>