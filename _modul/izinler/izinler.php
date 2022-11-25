<?php
$fn 		= new Fonksiyonlar();
$vt		= new VeriTabani();

/*18 YAŞ ALTI VEYA 50 YAŞ USTU PERSONELİN İZİN HAKLARI OLMAYANLARIN LİSTESİ*/
$SQL_18_50_izin_kazanan = <<< SQL
SELECT
	 CONCAT( p.adi,' ',p.soyadi ) AS adi
	,p.id
	,p.dogum_tarihi
	,p.ise_giris_tarihi
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
WHERE
	p.firma_id 			 = ? AND
	(dogum_tarihi  		 >= ? OR dogum_tarihi <= ? ) AND
	ise_giris_tarihi 	 <= ?  AND 
	p.aktif 			 = 1 

SQL;

/*18 YAŞ ALTI VEYA 50 YAŞ USTU PERSONELİN İZİN HAKLARI OLANLARIN LİSTESİ*/
$SQL_18_50_izin_kazanmayan = <<< SQL
SELECT
	 CONCAT( p.adi,' ',p.soyadi ) AS adi
	,p.id
	,p.dogum_tarihi
	,p.ise_giris_tarihi
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
WHERE
	p.firma_id 			 = ? AND
	(dogum_tarihi  >= ? OR dogum_tarihi <= ? ) AND
	ise_giris_tarihi 	 > ? AND 
	p.aktif 			 = 1 
SQL;


/*DİĞER YAŞ GRUPLARI ARASINDA 1-5 VEYA 5-15  VEYA 15 YILDAN FAZLA ÇALIŞAN PERSONELİN İZİN KAZANAMAYANLARIN LİSTESİ*/
$SQL_1_5_yil_izin_kazanmayan = <<< SQL
SELECT
	 CONCAT( p.adi,' ',p.soyadi ) AS adi
	,p.id
	,p.dogum_tarihi
	,p.ise_giris_tarihi
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
WHERE
	p.firma_id 			= ? AND
	dogum_tarihi 		> ? AND 
	dogum_tarihi 		< ? AND 
	ise_giris_tarihi 	> ? AND 
	ise_giris_tarihi	< ? AND
	DATE_FORMAT(p.ise_giris_tarihi,'%m-%d') > ?  AND 
	p.aktif 			= 1 
SQL;

/*DİĞER YAŞ GRUPLARI ARASINDA 1-5 VEYA 5-15  VEYA 15 YILDAN FAZLA ÇALIŞAN PERSONELİN İZİN KAZANAMAYANLARIN LİSTESİ*/
$SQL_1_5_yil_izin_kazanan = <<< SQL
SELECT
	 CONCAT( p.adi,' ',p.soyadi ) AS adi
	,p.id
	,p.dogum_tarihi
	,p.ise_giris_tarihi
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
WHERE
	p.firma_id 			= ? AND
	dogum_tarihi 		> ? AND 
	dogum_tarihi 		< ? AND 
	ise_giris_tarihi 	> ? AND 
	ise_giris_tarihi	< ? AND
	DATE_FORMAT(p.ise_giris_tarihi,'%m-%d') <= ?  AND 
	p.aktif 			= 1 
SQL;

/*DİĞER YAŞ GRUPLARI ARASINDA 1-5 VEYA 5-15  VEYA 15 YILDAN FAZLA ÇALIŞAN PERSONELİN İZİN KAZANAMAYANLARIN LİSTESİ*/
$SQL_5_15_yil_izin_kazanmayan = <<< SQL
SELECT
	 CONCAT( p.adi,' ',p.soyadi ) AS adi
	,p.id
	,p.dogum_tarihi
	,p.ise_giris_tarihi
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
WHERE
	p.firma_id 			= ? AND
	dogum_tarihi 		> ? AND 
	dogum_tarihi 		< ? AND 
	ise_giris_tarihi 	> ? AND 
	ise_giris_tarihi 	< ? AND 
	ise_giris_tarihi	< ? AND
	DATE_FORMAT(p.ise_giris_tarihi,'%m-%d') > ?  AND 
	p.aktif 			= 1 
SQL;

/*DİĞER YAŞ GRUPLARI ARASINDA 1-5 VEYA 5-15  VEYA 15 YILDAN FAZLA ÇALIŞAN PERSONELİN İZİN KAZANAMAYANLARIN LİSTESİ*/
$SQL_5_15_yil_izin_kazanan = <<< SQL
SELECT
	 CONCAT( p.adi,' ',p.soyadi ) AS adi
	,p.id
	,p.dogum_tarihi
	,p.ise_giris_tarihi
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
WHERE
	p.firma_id 			= ? AND
	dogum_tarihi 		> ? AND 
	dogum_tarihi 		< ? AND 
	ise_giris_tarihi 	> ? AND 
	ise_giris_tarihi 	< ? AND 
	ise_giris_tarihi	< ? AND
	DATE_FORMAT(p.ise_giris_tarihi,'%m-%d') <= ?  AND 
	p.aktif 			= 1 
SQL;

/*DİĞER YAŞ GRUPLARI ARASINDA 1-5 VEYA 5-15  VEYA 15 YILDAN FAZLA ÇALIŞAN PERSONELİN İZİN KAZANAMAYANLARIN LİSTESİ*/
$SQL_15_yildan_fazla_izin_kazanmayan = <<< SQL
SELECT
	 CONCAT( p.adi,' ',p.soyadi ) AS adi
	,p.id
	,p.dogum_tarihi
	,p.ise_giris_tarihi
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
WHERE
	p.firma_id 			= ? AND
	dogum_tarihi 		> ? AND 
	dogum_tarihi 		< ? AND 
	ise_giris_tarihi 	< ? AND  
	DATE_FORMAT(p.ise_giris_tarihi,'%m-%d') > ?  AND 
	p.aktif 			= 1 
SQL;

/*DİĞER YAŞ GRUPLARI ARASINDA 1-5 VEYA 5-15  VEYA 15 YILDAN FAZLA ÇALIŞAN PERSONELİN İZİN KAZANAMAYANLARIN LİSTESİ*/
$SQL_15_yildan_fazla_izin_kazanan = <<< SQL
SELECT
	 CONCAT( p.adi,' ',p.soyadi ) AS adi
	,p.id
	,p.dogum_tarihi
	,p.ise_giris_tarihi
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
WHERE
	p.firma_id 			= ? AND
	dogum_tarihi 		> ? AND 
	dogum_tarihi 		< ? AND 
	ise_giris_tarihi 	< ? AND 
	DATE_FORMAT(p.ise_giris_tarihi,'%m-%d') <= ?  AND 
	p.aktif 			= 1 
SQL;

/*Bulunan Yıl içinde İzin Verilip verilmediğini kontrol ediliyor*/
$SQL_izin_oku = <<< SQL
SELECT
	*
FROM
	tb_izinler 
WHERE
	personel_id 	= ? AND
	yil 			= ? AND
	aktif 			= 1
SQL;

$onsekiz_yas_alti 	= date( "Y-m-d", strtotime( date( "Y-m-d").'-18 year' ) );
$elli_yas_ustu 	= date( "Y-m-d", strtotime( date( "Y-m-d").'-50 year' ) );
$bir_yil_once 		= date( "Y-m-d", strtotime( date( "Y-m-d").'-1 year' ) );
$ise_giris_tarihi   = date( "m-d" );
$bes_yil_once 		= date( "Y-m-d", strtotime( date( "Y-m-d").'-5 year' ) );
$onbes_yil_once 	= date( "Y-m-d", strtotime( date( "Y-m-d").'-15 year' ) );

$onsekiz_elli_izin_kazanan 	= $vt->select( $SQL_18_50_izin_kazanan, array( $_SESSION[ "firma_id" ], $onsekiz_yas_alti, $elli_yas_ustu,  $bir_yil_once ) )[ 2 ];

$onsekiz_elli_izin_kazanmayan	= $vt->select( $SQL_18_50_izin_kazanmayan, array( $_SESSION[ "firma_id" ], $onsekiz_yas_alti, $elli_yas_ustu,  $bir_yil_once  ) )[ 2 ];
/*1 Yıl ile 5 Yıl Çalışan Personelin İzin Durumu*/
$bir_bes_yil_cal_kazanan   	= $vt->select( $SQL_1_5_yil_izin_kazanan, array( $_SESSION[ "firma_id" ], $elli_yas_ustu, $onsekiz_yas_alti,  $bes_yil_once ,  $bir_yil_once,$ise_giris_tarihi  ) )[ 2 ];
$bir_bes_yil_cal_kazanmayan   = $vt->select( $SQL_1_5_yil_izin_kazanmayan, array( $_SESSION[ "firma_id" ], $elli_yas_ustu, $onsekiz_yas_alti,  $bes_yil_once ,  $bir_yil_once,$ise_giris_tarihi  ) )[ 2 ];
/*5 Yıl ile 15 Yıl Çalışan Personelin İzin Durumu*/
$bes_onbes_yil_cal_kazanan   	= $vt->select( $SQL_5_15_yil_izin_kazanan, array( $_SESSION[ "firma_id" ], $elli_yas_ustu, $onsekiz_yas_alti, $onbes_yil_once, $bes_yil_once, $bir_yil_once, $ise_giris_tarihi ) )[ 2 ];
$bes_onbes_yil_cal_kazanmayan = $vt->select( $SQL_5_15_yil_izin_kazanmayan, array( $_SESSION[ "firma_id" ], $elli_yas_ustu, $onsekiz_yas_alti, $onbes_yil_once, $bes_yil_once, $bir_yil_once, $ise_giris_tarihi  ) )[ 2 ];
/*15 yıldan fazla çalışan personelin izin  durumu*/
$onbes_yil_cal_kazanan   	= $vt->select( $SQL_15_yildan_fazla_izin_kazanan, array( $_SESSION[ "firma_id" ], $elli_yas_ustu, $onsekiz_yas_alti, $onbes_yil_once, $ise_giris_tarihi  ) )[ 2 ];
$onbes_yil_cal_kazanmayan 	= $vt->select( $SQL_15_yildan_fazla_izin_kazanmayan, array( $_SESSION[ "firma_id" ], $elli_yas_ustu, $onsekiz_yas_alti, $onbes_yil_once, $ise_giris_tarihi ) )[ 2 ];

?>

<div class="row">
	<div class="col-12 col-sm-12">
		<div class="card ">
			<div class="card-header p-2">
				<ul class="nav nav-pills tab-container"  id="yetkiSekmeler" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="genel-tab" data-toggle="tab" href="#onsekiz_elli" role="tab" aria-controls="roller_tab" aria-selected="true"><b>18 Yaş Altı ve 50 Yaş Üstü Personel</b></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="genel-tab" data-toggle="tab" href="#bir_bes" role="tab" aria-controls="genel" aria-selected="false"><b>1 İle 5 Yıl Arası Çalışan Personel</b></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="genel-tab" data-toggle="tab" href="#bes_onbes" role="tab" aria-controls="genel" aria-selected="false"><b>5 İle 15 Yıl Arası Çalışan Personel</b></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="genel-tab" data-toggle="tab" href="#onbes" role="tab" aria-controls="genel" aria-selected="false"><b>15 Yıldan Fazla Çalışan Personel</b></a>
					</li>

				</ul>
			</div>
			<div class="card-body">
				<div class="tab-content" id="custom-tabs-one-tabContent">
					<!-- 18 Yaşından küçük ile 50 Yaş Üstü Personele ait İzin Durumu -->
					<div class="tab-pane fade show active" id="onsekiz_elli" role="tabpanel" aria-labelledby="roller_tab">
						<table id="tbl_onsekiz_elli" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Ad Soyad</th>
									<th>Yaş</th>
									<th>Bölümü</th>
									<th>Grubu</th>
									<th>İşe Giriş Tarihi</th>
									<th>Toplam İzin Günü</th>
									<th>Çalışması Gereken G.S.</th>
									<th>Kullanım</th>
									<th>Açıklama</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1 ;foreach ($onsekiz_elli_izin_kazanan as $personel) { 
									$izinVerildiMi = $vt->select( $SQL_izin_oku, array( $personel[ 'id' ], date( 'Y' ) ) )[ 3 ];
								?>
									<tr>
										<td><?php echo $sayi; ?></td>
										<td><?php echo $personel[ 'adi' ] ?></td>
										<td>
											<?php
												$diff = date_diff(date_create( $personel[ 'dogum_tarihi' ] ), date_create($bugun));
												echo $diff->format('%y');
											?>
										</td>
										<td><?php echo $personel[ 'bolum_adi' ] ?></td>
										<td><?php echo $personel[ 'grup_adi' ] ?></td>
										<td><?php echo $fn->tarihVer( $personel[ 'ise_giris_tarihi' ] ); ?></td>
										<td>20</td>
										<td><span class="text-success" >Kazanıldı</span></td>
										<td>
											<div class="icheck-success">
												<input 
													type 	="checkbox" <?php echo $izinVerildiMi > 0 ? 'checked' : ''; ?>  
													onclick = "izinKullanim(this,<?php echo $personel[ 'id' ]; ?>);" 
													id 		= "dosyaDurumu<?php echo $personel[ 'id' ] ?>" >
												<label for="dosyaDurumu<?php echo $personel[ 'id' ] ?>"  ></label>
											</div>
										</td>
										<td>Açıklama</td>
									</tr>
								<?php $sayi++; } ?>

								<?php ;foreach ($onsekiz_elli_izin_kazanmayan as $personel) { 
									$izinVerildiMi = $vt->select( $SQL_izin_oku, array( $personel[ 'id' ], date( 'Y' ) ) )[ 3 ];
								?>
									<tr>
										<td><?php echo $sayi; ?></td>
										<td><?php echo $personel[ 'adi' ] ?></td>
										<td>
											<?php
												$diff = date_diff(date_create( $personel[ 'dogum_tarihi' ] ), date_create($bugun));
												echo $diff->format('%y');
											?>
												
										</td>
										<td><?php echo $personel[ 'bolum_adi' ] ?></td>
										<td><?php echo $personel[ 'grup_adi' ] ?></td>
										<td><?php echo $fn->tarihVer( $personel[ 'ise_giris_tarihi' ] ); ?></td>
										<td>20</td>
										<td>
											<span class="text-danger">
											<?php
												$ise_giris_ay_gun =  date( "m-d", strtotime($personel["ise_giris_tarihi"])); 
												if ( $ise_giris_ay_gun > date("m-d") ) {
													$baslangicTarihi = date_create( date( "Y-m-d" ) ); 
													$bitisTarihi = date_create( date( "Y" ) ."-". $ise_giris_ay_gun );
												}else{
													$baslangicTarihi = date_create( date( "Y-m-d" ) ); 
													$bitisTarihi = date_create( date( "Y", strtotime( date( "Y" ).'+1 year' ) )."-".$ise_giris_ay_gun );
												}

												$kalan_gun = date_diff($baslangicTarihi,$bitisTarihi);
												echo $kalan_gun->format("%a Gün Kaldı");
											?>
											</span>
										</td>
										<td>-</td>
										<td>Açıklama</td>
									</tr>
								<?php $sayi++; } ?>
							</tbody>
						</table>					
					</div>
					<!-- 1 İle 5 Yıl Çalışan Personele Ait İzin Durumu-->
					<div class="tab-pane fade show" id="bir_bes" role="tabpanel" aria-labelledby="roller_tab">
						<table id="tbl_bir_bes" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Ad Soyad</th>
									<th>Yaş</th>
									<th>Bölümü</th>
									<th>Grubu</th>
									<th>İşe Giriş Tarihi</th>
									<th>Toplam İzin Günü</th>
									<th>Çalışması Gereken G.S.</th>
									<th>Kullanım</th>
									<th>Açıklama</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1 ;foreach ($bir_bes_yil_cal_kazanan as $personel) { 
									$izinVerildiMi = $vt->select( $SQL_izin_oku, array( $personel[ 'id' ], date( 'Y' ) ) )[ 3 ];
								?>
									<tr>
										<td><?php echo $sayi; ?></td>
										<td><?php echo $personel[ 'adi' ] ?></td>
										<td>
											<?php
												$diff = date_diff(date_create( $personel[ 'dogum_tarihi' ] ), date_create($bugun));
												echo $diff->format('%y');
											?>
												
										</td>
										<td><?php echo $personel[ 'bolum_adi' ] ?></td>
										<td><?php echo $personel[ 'grup_adi' ] ?></td>
										<td><?php echo $fn->tarihVer( $personel[ 'ise_giris_tarihi' ] ); ?></td>
										<td>14</td>
										<td><span class="text-success" >Kazanıldı</span></td>
										<td>
											<div class="icheck-success">
												<input 
													type 	="checkbox" <?php echo $izinVerildiMi > 0 ? 'checked' : ''; ?>  
													onclick 	= "izinKullanim(this,<?php echo $personel[ 'id' ]; ?>);" 
													id 		= "dosyaDurumu<?php echo $personel[ 'id' ] ?>" >
												<label for="dosyaDurumu<?php echo $personel[ 'id' ] ?>"  ></label>
											</div>
										</td>
										<td>Açıklama</td>
									</tr>
								<?php $sayi++; } ?>

								<?php ;foreach ($bir_bes_yil_cal_kazanmayan as $personel) { 
									$izinVerildiMi = $vt->select( $SQL_izin_oku, array( $personel[ 'id' ], date( 'Y' ) ) )[ 3 ];
								?>
									<tr>
										<td><?php echo $sayi; ?></td>
										<td><?php echo $personel[ 'adi' ] ?></td>
										<td>
											<?php
												$diff = date_diff(date_create( $personel[ 'dogum_tarihi' ] ), date_create($bugun));
												echo $diff->format('%y');
											?>	
										</td>
										<td><?php echo $personel[ 'bolum_adi' ] ?></td>
										<td><?php echo $personel[ 'grup_adi' ] ?></td>
										<td><?php echo $fn->tarihVer( $personel[ 'ise_giris_tarihi' ] ); ?></td>
										<td>14</td>
										<td>
											<span class="text-danger">
											<?php
												$ise_giris_ay_gun =  date( "m-d", strtotime($personel["ise_giris_tarihi"])); 
												if ( $ise_giris_ay_gun > date("m-d") ) {
													$baslangicTarihi = date_create( date( "Y-m-d" ) ); 
													$bitisTarihi = date_create( date( "Y" ) ."-". $ise_giris_ay_gun );
												}else{
													$baslangicTarihi = date_create( date( "Y-m-d" ) ); 
													$bitisTarihi = date_create( date( "Y", strtotime( date( "Y" ).'+1 year' ) )."-".$ise_giris_ay_gun );
												}

												$kalan_gun = date_diff($baslangicTarihi,$bitisTarihi);
												echo $kalan_gun->format("%a Gün Kaldı");
											?>
											</span>
										</td>
										<td>-</td>
										<td>Açıklama</td>

									</tr>
								<?php $sayi++; } ?>
							</tbody>

						</table>					
					</div>
					<!-- 5 İle 15 Yıl Çalışan Personele Ait İzin Durumu-->
					<div class="tab-pane fade show" id="bes_onbes" role="tabpanel" aria-labelledby="roller_tab">
						<table id="tbl_bes_onbes" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Ad Soyad</th>
									<th>Yaş</th>
									<th>Bölümü</th>
									<th>Grubu</th>
									<th>İşe Giriş Tarihi</th>
									<th>Toplam İzin Günü</th>
									<th>Çalışması Gereken G.S.</th>
									<th>Kullanım</th>
									<th>Açıklama</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1 ;foreach ($bes_onbes_yil_cal_kazanan as $personel) { 
									$izinVerildiMi = $vt->select( $SQL_izin_oku, array( $personel[ 'id' ], date( 'Y' ) ) )[ 3 ];
								?>
									<tr>
										<td><?php echo $sayi; ?></td>
										<td><?php echo $personel[ 'adi' ] ?></td>
										<td>
											<?php
												$diff = date_diff(date_create( $personel[ 'dogum_tarihi' ] ), date_create($bugun));
												echo $diff->format('%y');
											?>	
										</td>
										<td><?php echo $personel[ 'bolum_adi' ] ?></td>
										<td><?php echo $personel[ 'grup_adi' ] ?></td>
										<td><?php echo $fn->tarihVer( $personel[ 'ise_giris_tarihi' ] ); ?></td>
										<td>20</td>
										<td><span class="text-success" >Kazanıldı</span></td>
										<td>
											<div class="icheck-success">
												<input 
													type 	="checkbox" <?php echo $izinVerildiMi > 0 ? 'checked' : ''; ?>  
													onclick 	= "izinKullanim(this,<?php echo $personel[ 'id' ]; ?>);" 
													id 		= "dosyaDurumu<?php echo $personel[ 'id' ] ?>" >
												<label for="dosyaDurumu<?php echo $personel[ 'id' ] ?>"  ></label>
											</div>
										</td>
										<td>Açıklama</td>
									</tr>
								<?php $sayi++; } ?>

								<?php ;foreach ($bes_onbes_yil_cal_kazanmayan as $personel) { 
								?>
									<tr>
										<td><?php echo $sayi; ?></td>
										<td><?php echo $personel[ 'adi' ] ?></td>
										<td>
											<?php
												$diff = date_diff(date_create( $personel[ 'dogum_tarihi' ] ), date_create($bugun));
												echo $diff->format('%y');
											?>
												
										</td>
										<td><?php echo $personel[ 'bolum_adi' ] ?></td>
										<td><?php echo $personel[ 'grup_adi' ] ?></td>
										<td><?php echo $fn->tarihVer( $personel[ 'ise_giris_tarihi' ] ); ?></td>
										<td>20</td>
										<td>
											<span class="text-danger">
											<?php
												$ise_giris_ay_gun =  date( "m-d", strtotime($personel["ise_giris_tarihi"])); 
												if ( $ise_giris_ay_gun > date("m-d") ) {
													$baslangicTarihi = date_create( date( "Y-m-d" ) ); 
													$bitisTarihi = date_create( date( "Y" ) ."-". $ise_giris_ay_gun );
												}else{
													$baslangicTarihi = date_create( date( "Y-m-d" ) ); 
													$bitisTarihi = date_create( date( "Y", strtotime( date( "Y" ).'+1 year' ) )."-".$ise_giris_ay_gun );
												}

												$kalan_gun = date_diff($baslangicTarihi,$bitisTarihi);
												echo $kalan_gun->format("%a Gün Kaldı");
											?>
											</span>
										</td>
										<td>-</td>
										<td>Açıklama</td>
									</tr>
								<?php $sayi++; } ?>
							</tbody>

						</table>					
					</div>
					<!-- 15 Yıldan Fazla Çalışan Personele Ait İzin Durumu-->
					<div class="tab-pane fade show" id="onbes" role="tabpanel" aria-labelledby="roller_tab">
						<table id="tbl_onbes_yil" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Ad Soyad</th>
									<th>Yaş</th>
									<th>Bölümü</th>
									<th>Grubu</th>
									<th>İşe Giriş Tarihi</th>
									<th>Toplam İzin Günü</th>
									<th>Çalışması Gereken G.S.</th>
									<th>Kullanım</th>
									<th>Açıklama</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1 ;foreach ($onbes_yil_cal_kazanan as $personel) { 
									$izinVerildiMi = $vt->select( $SQL_izin_oku, array( $personel[ 'id' ], date( 'Y' ) ) )[ 3 ];
								?>
									<tr>
										<td><?php echo $sayi; ?></td>
										<td><?php echo $personel[ 'adi' ] ?></td>
										<td>
											<?php
												$diff = date_diff(date_create( $personel[ 'dogum_tarihi' ] ), date_create($bugun));
												echo $diff->format('%y');
											?>	
										</td>
										<td><?php echo $personel[ 'bolum_adi' ] ?></td>
										<td><?php echo $personel[ 'grup_adi' ] ?></td>
										<td><?php echo $fn->tarihVer( $personel[ 'ise_giris_tarihi' ] ); ?></td>
										<td>26</td>
										<td><span class="text-success" >Kazanıldı</span></td>
										<td>
											<div class="icheck-success">
												<input 
													type 	="checkbox" <?php echo $izinVerildiMi > 0 ? 'checked' : ''; ?>  
													onclick 	= "izinKullanim(this,<?php echo $personel[ 'id' ]; ?>);" 
													id 		= "dosyaDurumu<?php echo $personel[ 'id' ] ?>" >
												<label for="dosyaDurumu<?php echo $personel[ 'id' ] ?>"  ></label>
											</div>
										</td>
										<td>Açıklama</td>
									</tr>
								<?php $sayi++; } ?>

								<?php ;foreach ($onbes_yil_cal_kazanmayan as $personel) { 
									$izinVerildiMi = $vt->select( $SQL_izin_oku, array( $personel[ 'id' ], date( 'Y' ) ) )[ 3 ];
								?>
									<tr>
										<td><?php echo $sayi; ?></td>
										<td><?php echo $personel[ 'adi' ] ?></td>
										<td>
											<?php
												$diff = date_diff(date_create( $personel[ 'dogum_tarihi' ] ), date_create($bugun));
												echo $diff->format('%y');
											?>
										</td>
										<td><?php echo $personel[ 'bolum_adi' ] ?></td>
										<td><?php echo $personel[ 'grup_adi' ] ?></td>
										<td><?php echo $fn->tarihVer( $personel[ 'ise_giris_tarihi' ] ); ?></td>
										<td>26</td>
										<td>
											<span class="text-danger">
											<?php
												$ise_giris_ay_gun =  date( "m-d", strtotime($personel["ise_giris_tarihi"])); 
												if ( $ise_giris_ay_gun > date("m-d") ) {
													$baslangicTarihi = date_create( date( "Y-m-d" ) ); 
													$bitisTarihi = date_create( date( "Y" ) ."-". $ise_giris_ay_gun );
												}else{
													$baslangicTarihi = date_create( date( "Y-m-d" ) ); 
													$bitisTarihi = date_create( date( "Y", strtotime( date( "Y" ).'+1 year' ) )."-".$ise_giris_ay_gun );
												}

												$kalan_gun = date_diff($baslangicTarihi,$bitisTarihi);
												echo $kalan_gun->format("%a Gün Kaldı");
											?>
											</span>
										</td>
										<td>-</td>
										<td>Açıklama</td>
									</tr>
								<?php $sayi++; } ?>
							</tbody>

						</table>					
					</div>
				</div>
			</div>
			<!-- /.card -->
		</div>
	</div>
</div>
<script type="text/javascript">

	var tbl_onsekiz_elli = $( "#tbl_onsekiz_elli" ).DataTable( {
		"responsive": true, "lengthChange": true, "autoWidth": true,
		"stateSave": true,
		"pageLength" : 25,
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
				return "İzin Bilgileri";
			}
		},
		{
			extend	: 'print',
			text	: 'Yazdır',
			exportOptions : {
				columns : ':visible'
			},
			title: function(){
				return "İzin Bilgileri";
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
	} ).buttons().container().appendTo('#tbl_onsekiz_elli_wrapper .col-md-6:eq(0)');

	var tbl_bir_bes = $( "#tbl_bir_bes" ).DataTable( {
		"responsive": true, "lengthChange": true, "autoWidth": true,
		"stateSave": true,
		"pageLength" : 25,
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
				return "İzin Bilgileri";
			}
		},
		{
			extend	: 'print',
			text	: 'Yazdır',
			exportOptions : {
				columns : ':visible'
			},
			title: function(){
				return "İzin Bilgileri";
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
	} ).buttons().container().appendTo('#tbl_bir_bes_wrapper .col-md-6:eq(0)');

	var tbl_bes_onbes = $( "#tbl_bes_onbes" ).DataTable( {
		"responsive": true, "lengthChange": true, "autoWidth": true,
		"stateSave": true,
		"pageLength" : 25,
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
				return "İzin Bilgileri";
			}
		},
		{
			extend	: 'print',
			text	: 'Yazdır',
			exportOptions : {
				columns : ':visible'
			},
			title: function(){
				return "İzin Bilgileri";
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
	} ).buttons().container().appendTo('#tbl_bes_onbes_wrapper .col-md-6:eq(0)');

	var tbl_onbes_yil = $( "#tbl_onbes_yil" ).DataTable( {
		"responsive": true, "lengthChange": true, "autoWidth": true,
		"stateSave": true,
		"pageLength" : 25,
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
				return "İzin Bilgileri";
			}
		},
		{
			extend	: 'print',
			text	: 'Yazdır',
			exportOptions : {
				columns : ':visible'
			},
			title: function(){
				return "İzin Bilgileri";
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
	} ).buttons().container().appendTo('#tbl_onbes_yil_wrapper .col-md-6:eq(0)');

	function izinKullanim(cb,personel_id) {
     	var izin_durumu;
	  	var personel_id 	= personel_id; 
	  	cb.checked == true  ?  izin_durumu = 1 : izin_durumu = 0;

	  	var url         	= '_modul/izinler/izinlerSEG.php';
	     $.ajax({
	          type: "POST",
	          url: url,
	          data: 'personel_id='+personel_id+'&izin_durumu='+izin_durumu, 
	          cache: false,
	          success: function(response) {
	               var response = JSON.parse(response);
	               if ( response.sonuc == 'ok' ){
	               	mesajVer('Personele Ait İzin Kullanımı Güncelllendi','yesil');
	               }
	          }

	     })
	}

</script>

