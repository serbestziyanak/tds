<?php
	include "../../_cekirdek/fonksiyonlar.php";
	$vt		= new VeriTabani();
	$fn		= new Fonksiyonlar();

	
	$personel_id	= array_key_exists( 'personel_id'	, $_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;
	$tarih			= array_key_exists( 'tarih'		    , $_REQUEST ) ? $_REQUEST[ 'tarih' ]		: '';

	//Gelen Personele Ait Bilgiler
	$SQL_tek_personel_oku = <<< SQL
	SELECT
		*,
		CONCAT(adi, ' ', soyadi) AS adsoyad
	FROM
		tb_personel
	WHERE
		id = ? AND firma_id =? AND aktif = 1
	SQL;

	//Çıkış Yapılıp Yapılmadığı Kontrolü
	$SQL_personel_gun_giris_cikis = <<< SQL
	SELECT
		*
	FROM
		tb_giris_cikis 
	WHERE
		personel_id = ? AND tarih = ? 
	SQL;

	$personel 		= $vt->select( $SQL_tek_personel_oku, array($personel_id,$_SESSION['firma_id']) )[2];
	$giriscikis 	= $vt->select( $SQL_personel_gun_giris_cikis, array($personel_id,$tarih) )[2];

	if(count($personel)<1){
		echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
				<h5><i class="icon fas fa-ban"></i> Hata!</h5>
				aHatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
			</div>';
		die();
	}
	if ( $_REQUEST['tip'] == "gunluk" ) {
		if(count($giriscikis)>0){
			echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
					<h5><i class="icon fas fa-ban"></i> Hata!</h5>
					bHatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
				</div>';
			die();
		}
	}else{
		if(count($giriscikis)<1){
			echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
					<h5><i class="icon fas fa-ban"></i> Hata!</h5>
					bHatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
				</div>';
			die();
		}
	}



	if(!empty($_FILES['data'])) {
		$dosya_adi	= rand() .".pdf";
		$dizin		= "../../personel_tutanak/";
		$hedef_yol	= $dizin.$dosya_adi;
		move_uploaded_file( $_FILES[ "data"][ 'tmp_name' ], $hedef_yol );
		//echo file_get_contents($_FILES['data']['tmp_name']);
		//

	    // PDF is located at $_FILES['data']['tmp_name']
	    // rename(...) it or send via email etc.
	    // $content = file_get_contents($_FILES['data']['tmp_name']);
	} else {
	    throw new Exception("no data");
	}

?>