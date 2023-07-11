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

$_SESSION[ 'anasayfa_durum' ] = "guncelle";
	
if (in_array(  $_REQUEST["firma_id"], $firmaListesi)) {
	$_SESSION[ 'firma_id' ]			= $_REQUEST["firma_id"];
	$_SESSION[ 'firma_adi' ]		= $_REQUEST["firma_adi"];

	/*Son işlem Yaptığı Firmayı Cerezde Tutma
	setcookie('firma_id', 		$_REQUEST["firma_id"], $expire,"/","",true,true);
	setcookie('firma_adi', 		$_REQUEST["firma_adi"], $expire,"/","",true,true);
	*/
	header( "Location: ../index.php" );
}?> 



<section class="content">
	<div class="container-fluid">
		<div class="alert alert-warning text-center font-weight-bold">Lütfen İşlem Yapacağınız Firmayı Seçiniz</div>
		<div class="row " style = "display: flex;place-content: center;">

<?php 

$firmalar  = array();
foreach($firmaListesi AS $firma){
	$firmalar[] = $vt->select($SQL_firmalari_oku,array($firma[0]))[2][0];
}	
	$_SESSION['firmalarListesi'] = $firmalar;

	foreach ($firmalar as $firma) {
		echo '
		  	<div class="col-lg-4 col-sm-6">
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


?>
		</div>
	</div>
</section>
<script>
	$(document).ready(function(){
		var menu = document.querySelector('aside');
		menu.style.filter = "blur(10px)";
	})
	
</script>

