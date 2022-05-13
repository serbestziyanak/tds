<?php
	include "../../_cekirdek/fonksiyonlar.php";
	$vt		= new VeriTabani();
	$fn		= new Fonksiyonlar();

	$sonuc["sonuc"] ="hata";
	
	$personel_id	= array_key_exists( 'personel_id'	, $_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;
	$tutanak_id		= array_key_exists( 'tutanak_id'	, $_REQUEST ) ? $_REQUEST[ 'tutanak_id' ]	: 0;
	$tarih			= array_key_exists( 'tarih'		    , $_REQUEST ) ? $_REQUEST[ 'tarih' ]		: '';
	$tip			= array_key_exists( 'tip'		    , $_REQUEST ) ? $_REQUEST[ 'tip' ]			: '';
	$saat			= array_key_exists( 'saat'		    , $_REQUEST ) ? $_REQUEST[ 'saat' ]			: '';
	$aciklama		= array_key_exists( 'aciklama'		, $_REQUEST ) ? $_REQUEST[ 'aciklama' ]		: '';
	$durum			= array_key_exists( 'durum'		    , $_REQUEST ) ? $_REQUEST[ 'durum' ]		: 'eski';
	$islem			= array_key_exists( 'islem'		    , $_REQUEST ) ? $_REQUEST[ 'islem' ]		: '';

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
		personel_id = ? AND tarih = ? AND aktif= 1
	SQL;

	//gelen kayıt tutanaklar tablosunda var mı yok mu yoknrol ediyoruz
	$SQL_tutanak_oku = <<< SQL
	SELECT
		*
	FROM
		tb_tutanak 
	WHERE
		personel_id = ? AND 
		id 			= ? AND 
		firma_id 	= ?
	SQL;

	//gelen kayıt tutanaklar tablosunda var mı yok mu yoknrol ediyoruz
	$SQL_tutanak_varmi = <<< SQL
	SELECT
		*
	FROM
		tb_tutanak 
	WHERE
		
		firma_id 	= ? AND 
		personel_id = ? AND 
		tarih 		= ? AND 
		tip 		= ?
	SQL;
	

	$SQL_dosya_kaydet = <<< SQL
	INSERT INTO
		tb_tutanak_dosyalari
	SET
		 tutanak_id		= ?
		,dosya			= ?
		,aciklama	    = ?
	SQL;

	$SQL_tutanak_kaydet = <<< SQL
	INSERT INTO
		tb_tutanak
	SET
		 firma_id		= ?
		,personel_id	= ?
		,tarih			= ?
		,saat			= ?
		,tip			= ?
		,ekleme_tarihi	= ?
		,yazdirma 		= ?
	SQL;
	
	$SQL_yazdirma_guncelle = <<< SQL
	UPDATE 
		tb_tutanak
	SET 
		yazdirma 	= 1
	WHERE
		id 			= ?  
	SQL;

	$personel 		= $vt->select( $SQL_tek_personel_oku, array($personel_id, $_SESSION['firma_id'] ) )[2];
	$giriscikis 	= $vt->select( $SQL_personel_gun_giris_cikis, array($personel_id, $tarih) )[2];
	$tutanak_oku 	= $vt->select( $SQL_tutanak_oku, array($personel_id,$tutanak_id, $_SESSION['firma_id'] ) )[2];
	$tutanak_varmi 	= $vt->select( $SQL_tutanak_varmi, array( $_SESSION['firma_id'], $personel_id,$tarih, $tip  ) )[2];

	if( count( $personel ) < 1 ){
		echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
				<h5><i class="icon fas fa-ban"></i> Hata!</h5>
				Hatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
			</div>';
		die();
	}

	$dizin		= "../../tutanak/".$personel_id.'/';
	//personel id sine göre klasor oluşturulmu diye kontrol edip yok ise klador oluşturuyoruz
	if (!file_exists($dizin)) {
        if(!mkdir($dizin, '0777', true)){
   			$sonuc["sonuc"] = "hata";
        }
    }

	if ( $durum == 'yeni' ) {

		//Tunaka tablosuna kayıt yapılıp dosya yüklenecek
		$degerler 		= array(  $_SESSION['firma_id'], $personel_id, $tarih, $saat, $tip, date("Y-m-d H:i:s"), 1 );
		$tutanak_Ekle 	= $vt->insert( $SQL_tutanak_kaydet, $degerler );
		$tutanak_id 	= $tutanak_Ekle[ 2 ];

		if ( $tutanak_id > 0) {
			//Gelen Dosyaları Yüklemesini Yapıyoruz
			foreach ($_FILES['file']["tmp_name"] as $key => $value) {
				if( isset( $_FILES[ "file"]["tmp_name"][$key] ) and $_FILES[ "file"][ 'size' ][$key] > 0 ) {
					$dosya_adi	= rand() ."_".$tarih."_".$tip."." . pathinfo( $_FILES[ "file"][ 'name' ][$key], PATHINFO_EXTENSION );
					$hedef_yol	= $dizin.$dosya_adi;
					if( move_uploaded_file( $_FILES[ "file"][ 'tmp_name' ][$key], $hedef_yol ) ) {
						$vt->insert( $SQL_dosya_kaydet, array( $tutanak_id, $dosya_adi, $aciklama ) );
						$sonuc["sonuc"] = 'ok';
					}
				}
			}
		}
		
	}else if ( $durum == 'eski' ) {


		if ( $_REQUEST['tip'] == "gunluk" ){
			if( count( $giriscikis ) > 0 ){
				echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
						<h5><i class="icon fas fa-ban"></i> Hata!</h5>
						bHatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
					</div>';
				die();
			}
		}else{
			if( count( $giriscikis ) < 1 ){
				echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
						<h5><i class="icon fas fa-ban"></i> Hata!</h5>
						bHatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
					</div>';
				die();
			}
		}

	    switch ($islem) {
	    	case 'dosyaekle':
	    		
	    		if( count( $tutanak_oku ) > 0 ){ 

					//Gelen Dosyaları Yüklemesini Yapıyoruz
					foreach ($_FILES['file']["tmp_name"] as $key => $value) {
						if( isset( $_FILES[ "file"]["tmp_name"][$key] ) and $_FILES[ "file"][ 'size' ][$key] > 0 ) {
							$dosya_adi	= rand() ."_".$tarih."_".$tip."." . pathinfo( $_FILES[ "file"][ 'name' ][$key], PATHINFO_EXTENSION );
							$hedef_yol	= $dizin.$dosya_adi;
							if( move_uploaded_file( $_FILES[ "file"][ 'tmp_name' ][$key], $hedef_yol ) ) {
								$vt->insert( $SQL_dosya_kaydet, array( $tutanak_id, $dosya_adi, $aciklama ) );
								$sonuc["sonuc"] = 'ok';
							}
						}
					}
				}else{	
					
					//Tunaka tablosuna kayıt yapılıp dosya yüklenecek
					$degerler 		= array(  $_SESSION['firma_id'], $personel_id, $tarih, $saat, $tip, date("Y-m-d H:i:s"), 1 );
					$tutanak_Ekle 	= $vt->insert( $SQL_tutanak_kaydet, $degerler );
					$tutanak_id 	= $tutanak_Ekle[ 2 ];

					if ( $tutanak_id > 0) {
						//Gelen Dosyaları Yüklemesini Yapıyoruz
						foreach ($_FILES['file']["tmp_name"] as $key => $value) {
							if( isset( $_FILES[ "file"]["tmp_name"][$key] ) and $_FILES[ "file"][ 'size' ][$key] > 0 ) {
								$dosya_adi	= rand() ."_".$tarih."_".$tip."." . pathinfo( $_FILES[ "file"][ 'name' ][$key], PATHINFO_EXTENSION );
								$hedef_yol	= $dizin.$dosya_adi;
								if( move_uploaded_file( $_FILES[ "file"][ 'tmp_name' ][$key], $hedef_yol ) ) {
									$vt->insert( $SQL_dosya_kaydet, array( $tutanak_id, $dosya_adi, $aciklama ) );
									$sonuc["sonuc"] = 'ok';
								}
							}
						}
						$sonuc[ "sonuc" ] = 'ok';
					}
				}
	    		break;
	    	
	    	case 'yazdirma':
				if( count( $tutanak_varmi ) > 0 ){
					if ($tutanak_varmi[ 0 ][ "yazdirma" ] == 1 ) {
						$sonuc[ "sonuc" ] = 'hata';
					}else{
						$tutanak_id 		= $tutanak_varmi[ 0 ][ "id" ];
						$degerler 			= array( $tutanak_id );
						$tutanak_yazdirma 	= $vt->update( $SQL_yazdirma_guncelle, $degerler );
						$sonuc[ "sonuc" ] 	= 'ok';
					}
				}else{
					$degerler 		= array(  $_SESSION['firma_id'], $personel_id, $tarih, $saat, $tip, date("Y-m-d H:i:s"), 1 );
					$tutanak_Ekle 	= $vt->insert( $SQL_tutanak_kaydet, $degerler );
					$sonuc[ "sonuc" ]	= 'ok';
				}

	    		break;

	    	case 'sil':
	    }
	}
		

	echo json_encode($sonuc);



	//Ototmatık sunucuya dosyayı kaydetme
	// if(!empty($_FILES['data'])) {
	// 	$dosya_adi	= rand() .".pdf";
	// 	$dizin		= "../../personel_tutanak/";
	// 	$hedef_yol	= $dizin.$dosya_adi;
	// 	move_uploaded_file( $_FILES[ "data"][ 'tmp_name' ], $hedef_yol );
	// 	//echo file_get_contents($_FILES['data']['tmp_name']);
	// 	//

	//     // PDF is located at $_FILES['data']['tmp_name']
	//     // rename(...) it or send via email etc.
	//     // $content = file_get_contents($_FILES['data']['tmp_name']);
	// } else {
	//     throw new Exception("no data");
	// }

?>