<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'personel_id' ]			= $_SESSION[ 'sonuclar' ][ 'id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$islem			= array_key_exists( 'islem'			,$_REQUEST ) 	? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id		= array_key_exists( 'personel_id'	,$_REQUEST ) 		? $_REQUEST[ 'personel_id' ]		: 0;
//Personele Ait Listelenecek Hareket Ay
@$listelenecekAy	= array_key_exists( 'tarih'	,$_REQUEST ) ? $_REQUEST[ 'tarih' ]	: date("Y-m");
 
$tarih = $listelenecekAy;

$tarihBol = explode("-", $tarih);
$ay = intval($tarihBol[1]);
$yil = $tarihBol[0];

$satir_renk				= $personel_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi			= $personel_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls			= $personel_id > 0	? 'btn btn-warning btn-sm pull-right'		: 'btn btn-success btn-sm pull-right';

$SQL_tum_personel_oku = <<< SQL
SELECT
	 p.*
FROM
	tb_personel AS p
WHERE
	firma_id = ? AND p.aktif = 1
SQL;


$SQL_tek_personel_oku = <<< SQL
SELECT
	 p.*
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
	,ok1.adi AS ozel_kod1
	,ok2.adi AS ozel_kod2
	,u.adi AS uyruk_adi
	,od.adi AS ogrenim_duzeyi_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
LEFT JOIN tb_ozel_kod AS ok1 ON p.ozel_kod1_id = ok1.id
LEFT JOIN tb_ozel_kod AS ok2 ON p.ozel_kod2_id = ok2.id
LEFT JOIN tb_ulkeler AS u ON p.uyruk_id = u.id
LEFT JOIN tb_ogrenim_duzeyleri AS od ON p.ogrenim_duzeyi_id = od.id
WHERE
	p.id = ? AND p.aktif = 1
SQL;

//belirli bir aya göre personelin giriş çıkış hareketleri
//SELECT *, COUNT(tarih) AS tarihSayisi FROM tb_giris_cikis GROUP BY tarih ORDER BY tarih ASC
$SQL_tum_giris_cikis = <<< SQL
SELECT
	id
	,tarih
	,COUNT(tarih) AS tarihSayisi
	
FROM
	tb_giris_cikis
WHERE
	personel_id = ? AND DATE_FORMAT(tarih,'%Y-%m') =?  AND aktif = 1
GROUP BY tarih
ORDER BY tarih ASC 
SQL;

//Belirli tarihe göre giriş çıkış yapılan saatler 
$SQL_belirli_tarihli_giris_cikis = <<< SQL
SELECT
     baslangic_saat
    ,bitis_saat
    ,maas_kesintisi
	,adi AS islemTipi
FROM
	tb_giris_cikis
LEFT JOIN tb_giris_cikis_tipi ON tb_giris_cikis_tipi.id =  tb_giris_cikis.islem_tipi
LEFT JOIN tb_giris_cikis_tipleri ON tb_giris_cikis_tipleri.id =  tb_giris_cikis_tipi.tip_id
WHERE
	personel_id = ? AND tarih =? AND aktif = 1
ORDER BY baslangic_saat ASC 
SQL;


//FirmanınSectiği Giriş Cıkış Tipleri
$SQL_firma_giris_cikis_tipi = <<< SQL
SELECT
	 tip.id
	,tipler.adi
	,maas_kesintisi
FROM
	tb_giris_cikis_tipi AS tip
INNER JOIN tb_giris_cikis_tipleri AS tipler ON tip.tip_id = tipler.id
WHERE 
	tip.firma_id = ?
ORDER BY tipler.adi ASC
SQL;

//Tüm Giriş Çıkış Tipleri
$SQL_tum_giris_cikis_tipleri = <<< SQL
SELECT
tb_giris_cikis_tipleri.id,
tb_giris_cikis_tipleri.adi,
(SELECT tip_id from tb_giris_cikis_tipi WHERE tb_giris_cikis_tipi.tip_id = tb_giris_cikis_tipleri.id AND firma_id = 2) AS varmi
FROM
	tb_giris_cikis_tipleri
ORDER BY adi ASC
SQL;

//BELİRTİLEN TARİHLER ARASI EN YÜKSEK CARPANLI TARİFE 
$SQL_giris_cikis_saat = <<< SQL
SELECT 
	t1.*
from
	tb_tarifeler AS t1
LEFT JOIN tb_mesai_turu AS mt ON  t1.mesai_turu = mt.id

WHERE 
	t1.baslangic_tarih <= ? AND 
	t1.bitis_tarih >= ? AND
	mt.gunler LIKE ? AND 
	t1.grup_id LIKE ? AND
	t1.aktif = 1
ORDER BY t1.id DESC
LIMIT 1
SQL;

//TARİFEYE AİT SAAT LİSTESİ
$SQL_tarife_saati = <<< SQL
SELECT 
	*
from
	tb_tarife_saati 
WHERE 
	tarife_id = ? AND 
	aktif = 1
ORDER BY baslangic ASC
SQL;

//TARİFEYE AİT SAAT LİSTESİ
$SQL_mola_saati = <<< SQL
SELECT 
	*
from
	tb_molalar
WHERE 
	tarife_id = ? AND 
	aktif = 1
ORDER BY baslangic ASC
SQL;

//TÜM ÇARPANLARIN LİSTESİ
$SQL_carpan_oku = <<< SQL
SELECT 
	tb_tarife_saati.* 
FROM 
	tb_tarife_saati
INNER JOIN tb_tarifeler ON tb_tarifeler.id = tb_tarife_saati.tarife_id
WHERE 
	firma_id = ?
GROUP BY carpan
ORDER BY carpan ASC
SQL;

$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id']) )[2];
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 0 ][ 'id' ];
$firma_giris_cikis_tipleri	= $vt->select( $SQL_firma_giris_cikis_tipi,array($_SESSION["firma_id"]))[2];
$giris_cikislar			= $vt->select( $SQL_tum_giris_cikis, array($personel_id,$listelenecekAy) )[2];
$tek_personel				= $vt->select( $SQL_tek_personel_oku, array($personel_id) )[ 2 ][ 0 ];
$carpan_listesi			= $vt->select( $SQL_carpan_oku, array($_SESSION["firma_id"]) )[ 2 ];


//Bir günde en fazla kaç giriş çıkış yapıldığını bulma
foreach($giris_cikislar AS $giriscikis){
	$tarihSayisi[] = $giriscikis["tarihSayisi"]; 
}

@$tarihSayisi = max($tarihSayisi); 

?>

<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="container col-sm-12 card" style="display: block; padding: 15px 10px;">
				<div class="col-sm-2 float-left">
					<div class="form-group">
						<select class="form-control select2" id="personelAra" name = "personel_id" onchange="personelpuantaj(this.value);">
							<?php foreach( $personeller as $personel ) { ?>
								<option value="<?php echo $personel[ 'id' ]; ?>" <?php if( $tek_personel[ 'id' ] == $personel[ 'id' ] ) echo 'selected'; ?>><?php echo $personel['adi'].' '.$personel['soyadi']; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="col-sm-2" style="float: right;display: flex;">
					<div class="">
						<div class="input-group date" id="datetimepickerAy" data-target-input="nearest">
							<div class="input-group-append" data-target="#datetimepickerAy" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="tarihSec" class="form-control datetimepicker-input" data-target="#datetimepickerAy" data-toggle="datetimepicker" id="tarihSec" value="<?php if($listelenecekAy) echo $listelenecekAy; ?>"/>
						</div>
					</div>
					<div style="float: right;display: flex;">
						<button class="btn btn-success" id="listeleBtn">listele</button>
					</div>
				</div>
			</div>
			
			<div class="col-12">
				<div class="card card-secondary" id = "card_giriscikislar">
					<div class="card-header">
						<h3 class="card-title"><?php echo $tek_personel["adi"].' '.$tek_personel["soyadi"] ?> Puantaj İşlemleri</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_personel" data-toggle = "tooltip" title = "Yeni bir personel ekle" href = "?modul=personel&islem=ekle" class="btn btn-tool" ><i class="fas fa-user-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_giriscikislar" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Tarih</th>
									<?php
										$i = 1;

										echo $tarihSayisi == 0 ? '<th>İlk Giriş</th><th>Son Çıkış</th>':'';
										while ($i <= $tarihSayisi) {
											
											$thBaslikilk = $i == 1 ? 'İlk Giriş' : 'Giriş';

											$thBaslikSon = $i == $tarihSayisi ? 'Son Çıkış' : 'Çıkış';

											echo '<th>'.$thBaslikilk.'</th><th>'.$thBaslikSon.'</th>';
											$i++;
										}
									?>
									<th>İzin</th>
									<?php 

										foreach ( $carpan_listesi as $carpan ) {
											echo '<th>'.$carpan[ "carpan" ].'</th>';
										}
									?>
									<th>Hafta Tatili</th>
									<th>Ücretli İzin S.</th>
									<th>Ücretsiz İzin S.</th>
									<th>Toplam Kesinti S.</th>
									<th>Açıklama</th>
								</tr>
							</thead>
							<tbody>
								<?php 

									$gunSayisi = $fn->ikiHaneliVer($ay) == date("m") ? date("d") : date("t",mktime(0,0,0,$ay,01,$yil));	

									/*Günlerin Saymak için*/
									$sayi = 1; 
									$genelCalismaSuresiToplami = array();
									while( $sayi <= $gunSayisi ) { 
										
										$KullanilanSaatler 			= array(); // Hangi tarilerin uygulanacağını kontrol ediyoruz
										$kullanilacakMolalar 		= array(); //tarifelerer ait molalar
										$saatSay 					= 0;
										$asilkullanilanMolalar		= array(); //Personelin Kullandığı molalar
										$calismasiGerekenToplamDakika = array(); //Calışması gereken toplam dakika
										$calisilanToplamDakika 		= array(); //Personelin çalıştığı toplam dakika
										$kullanilanToplamMola		= array(); //Asil Molaların Toplamı
										$kullanilmayanMolaToplami	= array(); 
										$islenenSaatler			= array(); 
										$izin[ "ucretli" ]			= 0; 
										$izin[ "ucretsiz" ]			= 0; 
										$kullanilmasiGerekenToplamMola= 0; 

										$personel_giris_cikis_saatleri = $vt->select($SQL_belirli_tarihli_giris_cikis,array($personel_id,$tarih."-".$sayi))[2];
										$personel_giris_cikis_sayisi   = count($personel_giris_cikis_saatleri);
										$rows = $personel_giris_cikis_sayisi == 0 ?  1 : $personel_giris_cikis_sayisi;

										/*Perosnel Giriş Yapmış ise tatilden Satılmayacak Ek mesai oalrak hesaplanacaktır. */
										if($personel_giris_cikis_sayisi > 0) {
											$tatil = 'hayir';
										}

										//Personelin En erken giriş saati ve en geç çıkış saatini alıyoruz ona göre tutanak olusturulacak
										$son_cikis_index 	= $personel_giris_cikis_sayisi - 1;
										$ilk_islemtipi 	= $personel_giris_cikis_saatleri[0]['islem_tipi'];
										$son_islemtipi 	= $personel_giris_cikis_saatleri[$son_cikis_index]['islem_tipi'];

										$ilkGirisSaat 		= $fn->saatKarsilastir($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ], $personel_giris_cikis_saatleri[0]["baslangic_saat_guncellenen"]);

										$SonCikisSaat 		= $fn->saatKarsilastir($personel_giris_cikis_saatleri[$son_cikis_index][ 'bitis_saat' ], $personel_giris_cikis_saatleri[$son_cikis_index]["bitis_saat_guncellenen"]);

										/*Tairhin hangi güne denk oldugunu getirdik*/
										$gun = $fn->gunVer($tarih."-".$sayi);
										$giris_cikis_saat_getir = $vt->select( $SQL_giris_cikis_saat, array( $tarih."-".$sayi, $tarih."-".$sayi, '%,'.$gun.',%', '%,'.$tek_personel["grup_id"].',%' ) ) [ 2 ];
										//Mesaiye 10 DK gec Gelme olasıılıgını ekledik 10 dk ya kadaar gec gelebilir 

										/*tarifeye ait mesai saatleri */
										$saatler = $vt->select( $SQL_tarife_saati, array( $giris_cikis_saat_getir[ 0 ][ 'id' ] ) )[ 2 ];

										/*tarifeye ait mola saatleri */
										$molalar = $vt->select( $SQL_mola_saati, array( $giris_cikis_saat_getir[ 0 ][ 'id' ] ) )[ 2 ];
										

										$mesai_baslangic 	= date("H:i",  strtotime( $saatler[ 0 ]["baslangic"] )  );

										//Personel 5 DK  erken çıkabilir
										$mesai_bitis 		= date("H:i", strtotime( $$saatler[ 0 ]["bitis"] )  );
										//Eger Tatil Olarak İsaretlenmisse Giriş Zorunluluğu bulunmayıp mesaiye gelmisse mesai yazdıracaktır.
										$tatil 			= $giris_cikis_saat_getir[ 0 ]["tatil"] == 1  ?  'evet' : 'hayir';
										$maasa_etki_edilsin = $giris_cikis_saat_getir[ 0 ]["maasa_etki_edilsin"] == 1  ?  'evet' : 'hayir';
										
										/*Personelin Hangi saat dilimler,nde maasın hesaplanacağını kontrol ediyoruz*/			
										foreach ( $saatler as $alan => $saat ) {
											if ( $SonCikisSaat[ 0 ] <= $saat[ "bitis" ] AND  $saat[ "baslangic" ] <= $SonCikisSaat[ 0 ]   ){
												$saySaat = $alan;
											}
										}

										/*Personelin HaNGİ saat dilimine kadar çalışmiş ise o zaman dilimlerini siziye aktarıyoruz*/
										while ($saatSay <= $saySaat ) {
											$KullanilanSaatler[] = $saatler[ $saatSay ];
											$saatSay++;
										}
										/*Personelin il mesai basşalngı ve son çıkış saatini alıyoruz*/
										if ( $personel_giris_cikis_sayisi > 0){
											if ($ilkGirisSaat[0] < $mesai_baslangic AND ( $ilk_islemtipi == "" or $ilk_islemtipi == "0" )  ) {
												
											}else{
												$gunluk_baslangic = $ilkGirisSaat[0];
											}
											if ($SonCikisSaat[0] > $mesai_bitis AND ( $son_islemtipi == "" or $son_islemtipi == "0" ) ) {
												
											}else{
												$gunluk_bitis	   = $SonCikisSaat[0];
											}
										}else{
											$gunluk_baslangic = $mesai_baslangic;
											$gunluk_bitis	   = $mesai_bitis;
										}

										/*Personelin Çalıştığı saat dilimleri arasında kullandığı mola saatlerinizi alıyoruz*/
										foreach ( $molalar as $mola ) {
											foreach ( $KullanilanSaatler as $key => $saat ) {
												if ( $saat[ "baslangic" ] <= $mola[ "baslangic" ] AND $mola[ "bitis" ] <= $saat[ "bitis" ] ){
													$kullanilacakMolalar[ $saat[ "carpan" ] ][] = $mola;
												}
											}
										}

										/*Personelin tarifeye ait saat dilimleri arasında kaç saat çalışması gerektigini kotrol ediyoruz*/
									 	foreach ( $KullanilanSaatler as $saatkey => $saat ) {
									 		$calismasiGerekenToplamDakika[ $saat[ "carpan" ] ] += $fn->saatfarkiver( $saat[ "baslangic" ], $saat[ "bitis" ] );
									 	}

									 	/*Kullanılacak Molaların hangilerinin kullandığını kontrol ediyoruz*/
										foreach ( $kullanilacakMolalar as $molakey => $molalar ) {
											foreach ($molalar as $key => $mola) {
												foreach ( $personel_giris_cikis_saatleri as $giris ) {
													/*Personel İzinli Değilse */
													if( $giris[ "islemTipi" ]  == '' ){
														if ( $giris[ "baslangic_saat" ] <= $mola[ "baslangic" ]  AND $mola[ "bitis" ] <= $giris[ "bitis_saat" ]){
																$asilkullanilanMolalar[ $molakey ][] = $mola;
														}else if( $mola[ "bitis" ] <= $giris[ "bitis_saat" ] ){
															if ( $mola[ "baslangic" ] <= $giris[ "baslangic_saat" ] AND $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ] > $giris[ "baslangic_saat" ] ) {
																$asilkullanilanMolalar[ $molakey ][ $key ][ "baslangic" ] 	= $giris[ "baslangic_saat" ];
																$asilkullanilanMolalar[ $molakey ][ $key ][ "bitis" ] 		= $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ];
															}
														}else if ( $mola[ "bitis" ] >= $giris[ "bitis_saat" ] ){
															if ( $mola[ "baslangic" ] >= $giris[ "baslangic_saat" ] AND $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ] > $giris[ "bitis_saat" ] AND $mola[ "baslangic" ] < $giris[ "bitis_saat" ]) {
																$asilkullanilanMolalar[ $molakey ][ $key ][ "baslangic" ] 	= $mola[ "baslangic" ];
																$asilkullanilanMolalar[ $molakey ][ $key ][ "bitis" ] 		= $giris[ "bitis_saat" ];
															}
														}
													}else{
														/*Personel İzinli İse */
														if ( $giris[ "baslangic_saat" ] <= $mola[ "baslangic" ]  AND $mola[ "bitis" ] <= $giris[ "bitis_saat" ]){
																$kullanilmayanMolalar[ $molakey ][] = $mola;
														}else if( $mola[ "bitis" ] <= $giris[ "bitis_saat" ] ){
															if ( $mola[ "baslangic" ] <= $giris[ "baslangic_saat" ] AND $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ] > $giris[ "baslangic_saat" ] ) {
																$kullanilmayanMolalar[ $molakey ][ $key ][ "baslangic" ] 	= $giris[ "baslangic_saat" ];
																$kullanilmayanMolalar[ $molakey ][ $key ][ "bitis" ] 		= $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ];
															}
														}else if ( $mola[ "bitis" ] >= $giris[ "bitis_saat" ] ){
															if ( $mola[ "baslangic" ] >= $giris[ "baslangic_saat" ] AND $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ] > $giris[ "bitis_saat" ] AND $mola[ "baslangic" ] < $giris[ "bitis_saat" ]) {
																$kullanilmayanMolalar[ $molakey ][ $key ][ "baslangic" ] 	= $mola[ "baslangic" ];
																$kullanilmayanMolalar[ $molakey ][ $key ][ "bitis" ] 		= $giris[ "bitis_saat" ];
															}
														}
													}
												}
											}
										}
										/*Kullanılan Molaların Toıoplam Süresi Dakika HEsaplaması*/
									 	foreach ( $asilkullanilanMolalar as $molakey => $molalar ) {
									 		foreach ($molalar as  $mola) {
									 			$kullanilanToplamMola[ $molakey ] += $fn->saatfarkiver( $mola[ "baslangic" ], $mola[ "bitis" ] ); 
									 		}
									 	}

									 	/*Personel giriş çıkış yapmış ise çıkış giriş arasında kullanmadığı molaları hesaplıyoruz*/
									 	foreach ( $kullanilmayanMolalar as $molakey => $molalar ) {
									 		foreach ($molalar as  $mola) {
									 			$kullanilmayanMolaToplami[ $molakey ] += $fn->saatfarkiver( $mola[ "baslangic" ], $mola[ "bitis" ] ); 
									 		}
									 	}
									 	/*İlk Giriş Saatini aliyoruz */
										if ( $ilkGirisSaat[ 0 ] < $mesai_baslangic ) {
											$ilkGirisSaat[ 0 ] = $mesai_baslangic;
										} 

										/*son Çıkış Saatini aliyoruz */
									 	if ( $SonCikisSaat[0] >= $KullanilanSaatler[ count( $KullanilanSaatler ) - 1 ][ "bitis" ] ) {
											$SonCikisSaat[ 0 ] = $KullanilanSaatler[ count( $KullanilanSaatler ) - 1 ][ "bitis" ];
										}

										ksort($KullanilanSaatler);
										$i 				= 0; //Saatlere ait index
										$kullanildi 		= 0; // ilk giriş şim hesaplanması yapıldımı kontrol için 
										/*Tarifenin başlangıc saati yani normal mesai saat aralığı*/
										$ilkUygulanacakSaat = $KullanilanSaatler[ 0 ][ "carpan" ];
										/*Personelin Toplam Çalışma Sürelerini Hesaplama*/
									 	foreach ( $personel_giris_cikis_saatleri as $girisKey => $giris ) {
									 		$i = 0;	
									 		if ( $giris[ "islemTipi" ]  != '' AND  $girisKey == 0  ){
									 			$kullanildi = 1;
									 		}else{
									 			foreach ($KullanilanSaatler as $saatkey => $saat) {

										 			if ( $kullanildi == 0 ) {

										 				if ( $giris["bitis_saat"] > $saat["bitis"] ){
										 					$fark = $fn->saatfarkiver( date("H:i",strtotime($saat[ "bitis" ])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) );;
										 					$calisilanToplamDakika[ $saat["carpan"] ] += $fn->saatfarkiver( date("H:i",strtotime($ilkGirisSaat[0])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) ) - $fark;
										 				}else{
										 					$calisilanToplamDakika[ $saat["carpan"] ] += $fn->saatfarkiver( date("H:i",strtotime($ilkGirisSaat[0])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) );
										 				}
										 				
										 				$kullanildi = 1;

										 			}else if( $giris["bitis_saat" ] < $saat["bitis"] and $kullanildi == 1  and $giris["bitis_saat" ] >= $saat["baslangic"]  ){

										 				$fark = $fn->saatfarkiver( date("H:i",strtotime($giris[ "baslangic_saat" ])), date("H:i",strtotime($saat[ "baslangic" ] ) ) );;
										 				if ( $giris[ "islemTipi" ]  == '' ){
										 					if( $fark > 0 ){
											 					$calisilanToplamDakika[ $saat["carpan"] ] += $fn->saatfarkiver( date("H:i",strtotime($giris[ "baslangic_saat" ])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) ) - $fark;
											 					if ( $girisKey != 0 ) {
											 						$calisilanToplamDakika[ $KullanilanSaatler[$i - 1]["carpan"] ] += $fark;
											 					}
											 					
											 					
											 				}else{
											 					$calisilanToplamDakika[ $saat["carpan"] ] += $fn->saatfarkiver( date("H:i",strtotime($giris[ "baslangic_saat" ])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) );
											 				}
										 				}
											 				
										 			}
										 			$i++;

										 			/*
										 				personelin maas kesintisi degeri 0 veya boş işe ücretli izin veya normal giriş çıkış yapmıştır personelin masından kesinti yapılmayacaktır
														personelin maas Kesintisi degeri 1 olması halinde ücretsiz izin aldığını belirtir 
										 			*/
										 		}
									 		}
										 		
									 		if( $giris[ "maas_kesintisi" ]  == 0 AND $giris[ "islemTipi" ]  != ''  ){	
								 				$izin[ "ucretli" ] += $fn->saatfarkiver( date("H:i",strtotime($giris[ "baslangic_saat" ])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) );
								 			}else if( $giris[ "maas_kesintisi" ]  == 1 ) {
								 				$izin[ "ucretsiz" ] += $fn->saatfarkiver( date("H:i",strtotime($giris[ "baslangic_saat" ])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) );
								 			}
									 	}


									 	/*tarifeye ait molaların hangilerinin kullandığını kontrol edip toplam kaç dakika mola kullanılmış kontrol sağlıyoruz*/
										foreach ($KullanilanSaatler as $saatkey => $saat) {
											if ( $calisilanToplamDakika[ $saat[ "carpan" ] ] >= $kullanilanToplamMola[ $saat[ "carpan" ] ] ) {
												$calisilanToplamDakika[ $saat[ "carpan" ] ] -= $kullanilanToplamMola[ $saat[ "carpan" ] ];
											}else{
												$calisilanToplamDakika[ $saat[ "carpan" ] ] = '0';
											}
										}

										/*Tüm Günlerin calışma sürelerini carpani ile birlikte dizide topluyoruz*/
										foreach ($calisilanToplamDakika as $carpan => $dakika) {
											if ( $dakika > 0 )
												$genelCalismaSuresiToplami[ $carpan ] += $dakika;
										}
										/*Tatil Günlerinin Toplam Saatini Topla*/
										if ($tatil == 'evet' AND $personel_giris_cikis_sayisi == 0 ) {
											if ( $maasa_etki_edilsin == 'evet' ) {
												$tatilGunleriToplamDakika += 450;
											}
											 
										}

										foreach ($kullanilacakMolalar[ $ilkUygulanacakSaat ] as $molakey => $mola) {
											$kullanilmasiGerekenToplamMola += $fn->saatfarkiver($mola[ "baslangic" ], $mola[ "bitis" ]);
										}

										
									?>
									<tr>
										<td><?php echo $sayi; ?></td>
										<td><?php echo $sayi.'.'.$fn->ayAdiVer($ay,1).''.$fn->gunVer($tarih."-".$sayi); ?></td>
										<?php 
											$i = 1;
											$islemtipi = array();
											if ($personel_giris_cikis_sayisi == 0) {
												$col = ($tarihSayisi*2);
												$col = $col == 0 ? 2 : $col;
												$i = 1;
												while ($i <= $col) { 
													echo '<td class="text-center" >-</td>';
													$i++;
												}
												$islemtipi["gelmedi"] = "Gelmedi"; 
											}
											$giriscikisFarki = $tarihSayisi - $personel_giris_cikis_sayisi;
										
											//uygulanan işlem tipleri
											foreach($personel_giris_cikis_saatleri AS $giriscikis){
												$giriscikis["islemTipi"] != "" ? $islemtipi[] = $giriscikis["islemTipi"] : '';
											}
											$fark["UcretliIzin"] 	= 0;
											$fark["UcretsizIzin"] 	= 0;
											$fark["mesai"] 		= 0;
											//Bir Personel Bir günde en cok giris çıkıs sayısı en yüksek olan tarih ise
											if ($personel_giris_cikis_sayisi ==$tarihSayisi ) {
												foreach($personel_giris_cikis_saatleri AS $giriscikis){
													$baslangicSaat = $giriscikis[ 'baslangic_saat' ] == '' ? ' - ' : $giriscikis[ 'baslangic_saat' ];
													$bitisSaat = $giriscikis[ 'bitis_saat' ] == '' ? ' - ' : $giriscikis[ 'bitis_saat' ];
													echo '
														<td class="text-center">'.$baslangicSaat.'</td>
														<td class="text-center">'.$bitisSaat.'</td>';

													//Giriş Çıkış Arasındakik Dakika Farkı
													$baslangicSaati = strtotime($baslangicSaat );
													$bitisSaati 	= strtotime($bitisSaat );
													$ToplamDakika 	= ($bitisSaati - $baslangicSaati) / 60;

													if ($giriscikis["islemTipi"] == "") {
														$fark["mesai"] 	+= $ToplamDakika;
													}else{
														//Maaş Kesintisi Yapılıp Yapılmayacağını kontrol ediyoruz
														$fark["UcretsizIzin"]  += $giriscikis["maas_kesintisi"] == 1 ?  $ToplamDakika : $ToplamDakika;
													}
													
													
												}
											}else if($personel_giris_cikis_sayisi == 1 ){ // 1 Günde sadece bir kes giriş çıkış yapmıs ise 
												echo '<td class="text-center">'.$personel_giris_cikis_saatleri[0][ 'baslangic_saat' ].'</td>';
												$i = 1;
												while ($i <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
													echo '
														<td class="text-center"> - </td>
														<td class="text-center"> - </td>	
													';
													$i++;
												}
												echo '<td class="text-center">'.$personel_giris_cikis_saatleri[0][ 'bitis_saat' ].'</td>';

												$baslangicSaati = strtotime($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ]);
												$bitisSaati 	 = strtotime($personel_giris_cikis_saatleri[0][ 'bitis_saat' ]);
												$ToplamDakika 	 = ($bitisSaati - $baslangicSaati) / 60;

												if ($personel_giris_cikis_saatleri[0][ 'islemTipi' ] == "") {
													$fark["mesai"] += $ToplamDakika;
												}else{
													//Maaş Kesintisi Yapılıp Yapılmayacağını kontrol ediyoruz
													$fark["UcretsizIzin"]  += $giriscikis["maas_kesintisi"] == 1 ?  $ToplamDakika : $ToplamDakika;
												}

											}else{ //Gündee birden fazla giriş çıkış var ise 
												$i = 1;
												foreach($personel_giris_cikis_saatleri AS $giriscikis){
													
													if($i < $personel_giris_cikis_sayisi){

														$baslangicSaat = $giriscikis[ 'baslangic_saat' ] == '' ? ' - ' : $giriscikis[ 'baslangic_saat' ];
														$bitisSaat = $giriscikis[ 'bitis_saat' ] == '' ? ' - ' : $giriscikis[ 'bitis_saat' ];
														echo '
															<td class="text-center">'.$baslangicSaat.'</td>
															<td class="text-center">'.$bitisSaat.'</td>';
													}else{
														$baslangicSaat = $giriscikis[ 'baslangic_saat' ] == '' ? ' - ' : $giriscikis[ 'baslangic_saat' ];
														$bitisSaat = $giriscikis[ 'bitis_saat' ] == '' ? ' - ' : $giriscikis[ 'bitis_saat' ];
														echo '<td  class="text-center">'.$baslangicSaat.'</td>';
														$j = 1;
														while ($j <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
															echo '
																<td class="text-center"> - </td>
																<td class="text-center"> - </td>	
															';
															$j++;
														}
														echo '<td class="text-center">'.$bitisSaat.'</td>';
														
													}
													$i++;
													$baslangicSaati = strtotime($baslangicSaat );
													$bitisSaati 	= strtotime($bitisSaat );
													$ToplamDakika 	= ($bitisSaati - $baslangicSaati) / 60;

													if ($giriscikis["islemTipi"] == "") {
														$fark["mesai"] 	+= $ToplamDakika;
													}else{
														//Maaş Kesintisi Yapılıp Yapılmayacağını kontrol ediyoruz
														$fark["UcretsizIzin"]  += $giriscikis["maas_kesintisi"] == 1 ?  $ToplamDakika : $ToplamDakika;
													}
												}
											}

										?>
										<td>	
											<?php
												/*Tatil olup olmadığını Kontrol Ediyoruz*/ 
												if ( $tatil == 'evet' ){
													echo '<b class="text-center text-info">Tatil</b>';
												}else{
													echo array_key_exists("gelmedi", $islemtipi) ? '<b class="text-center text-danger">Gelmedi</b>' : '<b class="text-center text-warning">'.implode(", ", $islemtipi).'</b>';
													echo count($islemtipi) == 0  ? '<b class="text-center text-success">Mesaide</b>' : '';
												}
													
											?>
										</td>
										<?php 
											
											foreach ( $carpan_listesi as $carpan ) {
												if ( $calisilanToplamDakika[ $carpan[ "carpan" ] ] > 0 )
													echo '<td>'.gmdate("H:i", ( $calisilanToplamDakika[ $carpan[ "carpan" ] ] * 60 ) ).'</td>';
												else
													echo	'<td> - </td>';
											}	
										?>
										<td>
											<?php 
												if ( $tatil == 'evet' and $personel_giris_cikis_sayisi == 0 ){

													if ( $maasa_etki_edilsin == 'evet' ) {
														$toplamTatilSayisi += 450; 
														echo '07:30';
													}else{
														echo '-';
													}
													
												}else{
													echo array_key_exists("gelmedi", $islemtipi) ? '<b class="text-center text-danger">Gelmedi</b>' : '<b class="text-center text-warning">'.implode(", ", $islemtipi).'</b>';
													echo count($islemtipi) == 0  ? '<b class="text-center text-success">Mesaide</b>' : '';
												}
											?>
												
										</td>
										<td>
											<?php 
												if ($izin[ "ucretli" ]>0) {
													$ucretliIzinGenelToplam  += $izin[ "ucretli" ] - $kullanilmayanMolaToplami[ $ilkUygulanacakSaat ];
													echo gmdate("H:i", ( ( $izin[ "ucretli" ] - $kullanilmayanMolaToplami[ $ilkUygulanacakSaat ] ) * 60 ) );
												}else{
													echo '-';
												}
											?>
										</td>
										<td>
											<?php 
												if ($izin["ucretsiz"]>0) {
													$ucretsizIzinGenelToplam  += $izin[ "ucretsiz" ] - $kullanilmayanMolaToplami[ $ilkUygulanacakSaat ];
													echo gmdate("H:i", ( ( $izin[ "ucretsiz" ]  - $kullanilmayanMolaToplami[ $ilkUygulanacakSaat ] ) * 60 ) );
												}else{
													echo '-';
												}
											?>
											
										</td>
										<td>
											<?php 
													
												$toplamIzın = $izin[ "ucretli" ] + $izin[ "ucretsiz" ];
												$cikarilacakMola = $kullanilmasiGerekenToplamMola;
												
												if ( $toplamIzın > 0 ) {
													$cikarilacakMola -= $kullanilmasiGerekenToplamMola - $kullanilanToplamMola[ $ilkUygulanacakSaat ];
												}

												//Toplam Calısılması gereken - calıstığı süre - izin süresi - Mola
												$ToplamKesintiSaati = $calismasiGerekenToplamDakika[$ilkUygulanacakSaat] - $calisilanToplamDakika[$ilkUygulanacakSaat] - $toplamIzın  - $cikarilacakMola;
												if($tatil == 'evet' AND $personel_giris_cikis_sayisi == 0){
													echo '-';
												}else{
													$molaSuresi = 0;
													
													if($ToplamKesintiSaati > 0){
														$genelToplamKesintiSuresi += $ToplamKesintiSaati ;
														echo gmdate("H:i", ( ( $ToplamKesintiSaati) * 60 ) );
													}else{
														echo '-';
													}
												}

												

											?>
										</td>
										<td>-</td>
										
									</tr>
								<?php $sayi++;} ?>
							</tbody>
							<tfoot>
								<?php 
									/*Giriş Çıkış Sayısının 2 katı kadar sütun oluşşturuyoruz ve sabit 3 tane sutun ile birleştiriyoruz */
									$birlestirilecekSutunSayisi = 3 + ( $tarihSayisi * 2 );
								?>

								<th colspan=" <?php echo $birlestirilecekSutunSayisi; ?> "> Toplam:</th>

								<?php
									/*Hangi Çarpanda Ne kadar Çalıştığını Hesaplıyoruz*/
									foreach ( $carpan_listesi as $carpan ) {
										$saat 	= floor($genelCalismaSuresiToplami[ $carpan[ "carpan" ] ] / 60);
										$dakika 	= floor($genelCalismaSuresiToplami[ $carpan[ "carpan" ] ] % 60);
										echo '<th>'.$saat.'.'.$dakika.'</th>';
									}

									/*Hafta Tatili Toplam Saat Hesaplama*/
									$tatilsaat 	= floor($tatilGunleriToplamDakika / 60 );
									$tatildakika 	= floor($tatilGunleriToplamDakika % 60 );
									echo '<th>'.$tatilsaat.'.'.$tatildakika.'</th>';

									/* Toplamda Kullandığı Ücretsiz izni Hesaplıyoruz*/
									$saat 		= floor( $ucretliIzinGenelToplam / 60 );
									$dakika 		= floor( $ucretliIzinGenelToplam % 60 );
									echo '<th>'.$saat.'.'.$dakika.'</th>';

									/* Toplamda Kullandığı Ücretsiz izni Hesaplıyoruz*/
									$saat 		= floor( $ucretsizIzinGenelToplam / 60 );
									$dakika 		= floor( $ucretsizIzinGenelToplam % 60 );
									echo '<th>'.$saat.'.'.$dakika.'</th>';

									/*Toplam Kesinti Yapılan Toplam Suues*/
									$kesintisaat 		= floor( $genelToplamKesintiSuresi / 60 );
									$kesintidakika 		= floor( $genelToplamKesintiSuresi % 60 );
									echo '<th>'.$kesintisaat.'.'.$kesintidakika.'</th>';
								?>
								<th></th>

							</tfoot>
						</table>
					</div>
				</div>
			</div>	
		</div>
	</div>
</section>

<script type="text/javascript">


	$(function () {
		$('#datetimepickerAy').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});

	function personelpuantaj(personel_id){
		var tarih 		= $("#tarihSec").val();
		var  url 			= window.location;
		var origin		= url.origin;
		var path			= url.pathname;
		var search		= (new URL(document.location)).searchParams;
		var modul   		= search.get('modul');
		var personel_id  	= "&personel_id="+personel_id;
		
		window.location.replace(origin + path+'?modul='+modul+''+personel_id+'&tarih='+tarih);
	}	
	$(function () {
		$(":input").inputmask();

		//Initialize Select2 Elements
		$('.select2').select2()

		//Initialize Select2 Elements
		$('.select2bs4').select2({
		  theme: 'bootstrap4'
		})


		$("input[data-bootstrap-switch]").each(function(){
			$(this).bootstrapSwitch('state', $(this).prop('checked'));
		});
	})

	$("body").on('click', '#listeleBtn', function() {
		var tarih 		= $("#tarihSec").val();
		var  url 			= window.location;
		var origin		= url.origin;
		var path			= url.pathname;
		var search		= (new URL(document.location)).searchParams;
		var modul   		= search.get('modul');
		var detay   		= search.get('detay');
		var personel_id   = search.get('personel_id');
		if(detay == null) {
			detay 	= ''; 
		}else{
			detay  	= "&detay="+detay;
		}
		if(personel_id == null) {
			personel_id 	= ''; 
		}else{
			personel_id  	= "&personel_id="+personel_id;
		}
		
		window.location.replace(origin + path+'?modul='+modul+''+personel_id+''+detay+'&tarih='+tarih);
	})


	var tbl_giriscikislar = $( "#tbl_giriscikislar" ).DataTable( {
		"responsive": true, "lengthChange": true, "autoWidth": true,
		"stateSave": true,
		"pageLength" : 31,
		//"buttons": ["excel", "print"],

		buttons : [
			{
				extend	: 'colvis',
				text	: "Alan Seçiniz"
				
			},
			{
				extend	: 'excel',
				text 	: 'Excel',
				exportOptions: {
					columns: ':visible'
				},
				title: function(){
					return "Giriş Çıkış Bilgileri";
				}
			},
			{
				extend	: 'print',
				text	: 'Yazdır',
				exportOptions : {
					columns : ':visible'
				},
				title: function(){
					return "Giriş Çıkış Bilgileri";
				}
			}
		],
		"columnDefs": [
			{
				"targets" : [],
				"visible" : false
			}
		],
		"language": {
			"decimal"			: "",
			"emptyTable"		: "Gösterilecek kayıt yok!",
			"info"				: "Toplam _TOTAL_ kayıttan _START_ ve _END_ arası gösteriliyor",
			"infoEmpty"			: "Toplam 0 kayıttan 0 ve 0 arası gösteriliyor",
			"infoFiltered"		: "",
			"infoPostFix"		: "",
			"thousands"			: ",",
			"lengthMenu"		: "Show _MENU_ entries",
			"loadingRecords"	: "Yükleniyor...",
			"processing"		: "İşleniyor...",
			"search"			: "Ara:",
			"zeroRecords"		: "Eşleşen kayıt bulunamadı!",
			"paginate"			: {
				"first"		: "İlk",
				"last"		: "Son",
				"next"		: "Sonraki",
				"previous"	: "Önceki"
			}
		}
	} ).buttons().container().appendTo('#tbl_giriscikislar_wrapper .col-md-6:eq(0)');

</script>
