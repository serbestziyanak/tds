<?php
	include "../../_cekirdek/fonksiyonlar.php";
	$vt		= new VeriTabani();
	$fn		= new Fonksiyonlar();

	$sonuc["sonuc"] ="hata - 1";
	
	$personel_id	= array_key_exists( 'personel_id'	, $_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;
	$tutanak_id		= array_key_exists( 'tutanak_id'	, $_REQUEST ) ? $_REQUEST[ 'tutanak_id' ]	: 0;
	$tarih			= array_key_exists( 'tarih'		    , $_REQUEST ) ? $_REQUEST[ 'tarih' ]		: '';
	$tip			= array_key_exists( 'tip'		    , $_REQUEST ) ? $_REQUEST[ 'tip' ]			: '';
	$saat			= array_key_exists( 'saat'		    , $_REQUEST ) ? $_REQUEST[ 'saat' ]			: '';
	$aciklama		= array_key_exists( 'aciklama'		, $_REQUEST ) ? $_REQUEST[ 'aciklama' ]		: '';
	$durum			= array_key_exists( 'durum'		    , $_REQUEST ) ? $_REQUEST[ 'durum' ]		: 'eski';
	$dosya_id		= array_key_exists( 'dosya_id'		, $_REQUEST ) ? $_REQUEST[ 'dosya_id' ]		: '';
	$islem			= array_key_exists( 'islem'		    , $_REQUEST ) ? $_REQUEST[ 'islem' ]		: '';

	$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "tutanakolustur", $islem );

	if ( $yetiKontrol == 0 ) {
		include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
		die();
	}

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
	$SQL_dosya_oku = <<< SQL
	SELECT
		t.id as tutanak_id,
		t.personel_id,
		t.firma_id,
		td.dosya,
		td.id as dosya_id
	FROM
		tb_tutanak AS t
	INNER JOIN 
		tb_tutanak_dosyalari AS td ON td.tutanak_id = t.id
	WHERE
		t.personel_id 	= ? AND 
		t.id 			= ? AND 
		t.firma_id 		= ? AND
		td.id  			= ?
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
	
	//Tutanak Dosya Kaydetme
	$SQL_dosya_kaydet = <<< SQL
	INSERT INTO
		tb_tutanak_dosyalari
	SET
		 tutanak_id		= ?
		,dosya			= ?
		,aciklama	    = ?
	SQL;

	//Tutanak Kaydetme
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
	
	//Tutanak dosyası tekrar yazdırmamak için bilgi güncelleme
	$SQL_yazdirma_guncelle = <<< SQL
	UPDATE 
		tb_tutanak
	SET 
		yazdirma 	= ?
	WHERE
		id 			= ?  
	SQL;

	//Tutanak dcosya silme
	$SQL_dosya_sil = <<< SQL
	DELETE FROM
		tb_tutanak_dosyalari
	WHERE
		id = ?
	SQL;

	$personel 		= $vt->select( $SQL_tek_personel_oku, array($personel_id, $_SESSION['firma_id'] ) )[2];
	$giriscikis 	= $vt->select( $SQL_personel_gun_giris_cikis, array($personel_id, $tarih) )[2];
	$tutanak_oku 	= $vt->select( $SQL_tutanak_oku, array($personel_id,$tutanak_id, $_SESSION['firma_id'] ) )[2];
	$tutanak_varmi 	= $vt->select( $SQL_tutanak_varmi, array( $_SESSION['firma_id'], $personel_id,$tarih, $tip ) )[2];

	$vt->islemBaslat();
	if( count( $personel ) < 1 ){
		echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
				<h5><i class="icon fas fa-ban"></i> Hata!</h5>
				Hatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
			</div>';
		die();
	}

	$dizin		= "../../tutanak/".$personel_id;
	//personel id sine göre klasor oluşturulmu diye kontrol edip yok ise klador oluşturuyoruz
	if (!is_dir($dizin)) {
        if(!mkdir($dizin, '0777', true)){
   			$sonuc["sonuc"] = "hata - 2";
        }else{	
        	chmod($dizin, 0777);
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
					$hedef_yol	= $dizin.'/'.$dosya_adi;
					if( move_uploaded_file( $_FILES[ "file"][ 'tmp_name' ][$key], $hedef_yol ) ) {
						$vt->insert( $SQL_dosya_kaydet, array( $tutanak_id, $dosya_adi, $aciklama ) );
						$sonuc["sonuc"] = 'ok';
					}
				}
			}
		}
		
	}else if ( $durum == 'eski' AND $islem != 'sil' ) {


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
							$hedef_yol	= $dizin.'/'.$dosya_adi;
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
								$hedef_yol	= $dizin.'/'.$dosya_adi;
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
						$yazdirma = 0;
					}else{
						$yazdirma = 1;
					}

					$tutanak_id 		= $tutanak_varmi[ 0 ][ "id" ];
					$degerler 			= array( $yazdirma, $tutanak_id );
					$tutanak_yazdirma 	= $vt->update( $SQL_yazdirma_guncelle, $degerler );
					$sonuc[ "sonuc" ] 	= 'ok';
					$_SESSION['anasayfa_durum'] = 'guncelle';
				}else{
					$degerler 		= array(  $_SESSION['firma_id'], $personel_id, $tarih, $saat, $tip, date("Y-m-d H:i:s"), 1 );
					$tutanak_Ekle 	= $vt->insert( $SQL_tutanak_kaydet, $degerler );
					$sonuc[ "sonuc" ]	= 'ok';
					$_SESSION['anasayfa_durum'] = 'guncelle';
				}

	    		break;
	    }
	}

	if ( $islem == 'sil' ) {

		//Silinecek dosyanın bilgileri aldık
		$tutanak_dosyasi = $vt->select( $SQL_dosya_oku, array( $personel_id, $tutanak_id, $_SESSION['firma_id'], $dosya_id) ) [2];
		if ( count( $tutanak_dosyasi ) > 0 ) {
			$vt->delete( $SQL_dosya_sil, array( $dosya_id ) );
			//Sunucudan Dosyayı Siliyoruz.
			unlink( $dizin.'/'.$tutanak_dosyasi [0] ["dosya"] );
			$sonuc[ "sonuc" ]	= 'ok';

			$vt->islemBitir();
			header( "Location:../../index.php?modul=personelOzlukDosyalari&personel_id=$personel_id" );
		}	
	}

	$vt->islemBitir();
	
	echo json_encode($sonuc);

?>