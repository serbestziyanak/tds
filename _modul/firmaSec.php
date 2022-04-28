<?php
include "../_cekirdek/fonksiyonlar.php";
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$firmaListesi = $_SESSION['firmalar'];



$SQL_firmalari_oku = <<< SQL
SELECT
	 id
	,adi
	,firma
FROM
	tb_firmalar 
WHERE
	id =? AND aktif = 1
SQL;
	
if (in_array(  $_REQUEST["firma_id"], $firmaListesi)) {
	$_SESSION[ 'firma_id' ]			= $_REQUEST["firma_id"];
	$_SESSION[ 'firma_adi' ]		= $_REQUEST["firma_adi"];

	header( "Location: ../index.php" );
}

$firmalar  = array();
foreach($firmaListesi AS $firma){
	$firmalar[] = $vt->select($SQL_firmalari_oku,array($firma[0]))[2][0];
}	
	$_SESSION['firmalarListesi'] = $firmalar;
	echo '<div class="row">';
	foreach ($firmalar as $firma) {
		echo '
		  	<div class="col-lg-3 col-sm-4">
			    <!-- small box -->
			    <div class="small-box bg-info">
				    <div class="inner">
				        <h3>&nbsp;</h3>

				        <p>'.$firma["firma"].'</p>
				    </div>
				    <div class="icon">
				        <i class="fas fa-building"></i>
				    </div>
			      <a href="_modul/firmaSec.php?firma_id='.$firma["id"].'&firma_adi='.$firma["adi"].'" class="small-box-footer">Firma İle Devam Et <i class="fas fa-arrow-circle-right"></i></a>
			    </div>
		  	</div><!-- ./col -->';
	}
	echo '</div><!-- /.row -->';


?>
