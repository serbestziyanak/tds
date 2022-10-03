<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$tarife_id		= array_key_exists( 'tarife_id', $_REQUEST )		? $_REQUEST[ 'tarife_id' ]		: 0;
$saat_id		= array_key_exists( 'saat_id', $_REQUEST )			? $_REQUEST[ 'saat_id' ]		: 0;
$alanlar		= array();
$degerler		= array();

$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "sgkKanunNo", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}

 
$SQL_ekle		= "INSERT INTO tb_tarife_saati SET ";
$SQL_guncelle 	= "UPDATE tb_tarife_saati SET ";

/*Tarifeye Ait Molaları Getirme*/
$SQL_saat_getir = <<< SQL
SELECT 
	*
FROM 
	tb_tarife_saati
INNER JOIN tb_tarifeler ON tb_tarife_saati.tarife_id = tb_tarifeler.id
WHERE 
	tb_tarife_saati.id 	= ? AND
	tarife_id		= ? AND
	firma_id 		= ?
SQL;


$SQL_tek_saat_sil = <<< SQL
UPDATE 
	tb_tarife_saati
SET
	aktif 	= 0
WHERE
	id 		= ?
SQL;


$alanlar		= array("tarife_id","baslangic","bitis","carpan");

$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";
$vt->islemBaslat();
switch( $islem ) {

	case 'guncelle':
		/*Tüm  Molaları silip tekrar yüklemesini yapacağız*/

		foreach ($_REQUEST["baslangic"] as $alan => $deger) {
			$degerler   = array();
			$degerler[] = $_REQUEST['tarife_id'];
			$degerler[] = $_REQUEST["baslangic"][$alan];
			$degerler[] = $_REQUEST["bitis"][$alan];
			$degerler[] = $_REQUEST["carpan"][$alan];

			if ( $_REQUEST[ "id" ][$alan] > 0) {
				echo 'Güncellendi<br>';
				$degerler[] = $_REQUEST[ "id" ][$alan];
				$sonuc = $vt->update( $SQL_guncelle, $degerler );
			}else{
				/*Yeni Eklenecek Veri*/
				echo 'eklendi<br>';
				$sonuc = $vt->insert( $SQL_ekle, $degerler );
			}
		}
		
	break;
	case 'sil':
		/*Gelen tarife aid firmaya ait mi*/
		$saat_sorgula = $vt->select( $SQL_saat_getir, array( $saat_id, $tarife_id, $_SESSION[ 'firma_id' ] ) )[2];
		if ( count( $saat_sorgula ) > 0 ) {
			$vt->update( $SQL_tek_saat_sil, array( $saat_id ) );	
		}

	break;
}
$vt->islemBitir();
header( "Location:../../index.php?modul=tarifeler&islem=guncelle&tarife_id=".$tarife_id."&detay=saat" );
?>