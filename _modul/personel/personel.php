<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$islem			= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';


$SQL_tum_personel_oku = <<< SQL
SELECT
	 p.*
	,s.adi AS firma_adi
	,b.adi AS bolum_adi
	,b.id AS bolum_id
	,sgk.id AS sgk_kanun_no_id
	,sgk.adi AS sgk_kanun_no_adi
FROM
	tb_personel AS p
LEFT JOIN
	tb_firmalar AS s ON p.firma_id = s.id
LEFT JOIN
	tb_bolumler AS b ON p.bolum_id = b.id
LEFT JOIN
	tb_sgk_kanun_no AS sgk ON p.sgk_kanun_no_id = sgk.id
WHERE
	p.aktif = 1
SQL;

$SQL_tek_personel_oku = <<< SQL
SELECT
	 p.*
	,s.adi AS firma_adi
	,b.adi AS bolum_adi
	,b.id AS bolum_id
	,sgk.id AS sgk_kanun_no_id
	,sgk.adi AS sgk_kanun_no_adi
FROM
	tb_personel AS p
LEFT JOIN
	tb_firmalar AS s ON p.firma_id = s.id
LEFT JOIN
	tb_bolumler AS b ON p.bolum_id = b.id
LEFT JOIN
	tb_sgk_kanun_no AS sgk ON p.sgk_kanun_no_id = sgk.id
WHERE
	p.id = ? AND p.aktif = 1
SQL;

$SQL_personel_ozluk_dosyalari = <<< SQL
SELECT
	 ot.adi
	,od.dosya
FROM
	tb_personel_ozluk_dosyalari AS od
JOIN
	tb_personel_ozluk_dosya_turleri AS ot ON od.dosya_turu_id = ot.id
WHERE
	od.personel_id = ?
SQL;


$SQL_personel_detaylar = <<< SQL
SELECT
	 p.*
	,s.adi AS firma_adi
	,b.adi AS bolum_adi
	,b.id AS bolum_id
	,sgk.id AS sgk_kanun_no_id
	,sgk.adi AS sgk_kanun_no_adi
FROM
	tb_personel AS p
LEFT JOIN
	tb_firmalar AS s ON p.firma_id = s.id
LEFT JOIN
	tb_bolumler AS b ON p.bolum_id = b.id
LEFT JOIN
	tb_sgk_kanun_no AS sgk ON p.sgk_kanun_no_id = sgk.id
WHERE
	p.id = ?
SQL;

$personeller				= $vt->select( $SQL_tum_personel_oku, array() );
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];
$tek_personel				= $vt->select( $SQL_tek_personel_oku, array( $personel_id ) );
$personel_ozluk_dosyalari	= $vt->select( $SQL_personel_ozluk_dosyalari, array( $personel_id ) );
$personel_detaylar			= $vt->select( $SQL_personel_detaylar, array( $personel_id ) );



$secilen_personel_bilgileri = array(
	 'id'							=> $personel_id > 0 ? $personel_id												: $personeller[ 2 ][ 0 ][ 'id' ]
	,'firma_id'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'firma_id' ]						: $personeller[ 2 ][ 0 ][ 'firma_id' ]
	,'firma_adi'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'firma_adi' ]					: $personeller[ 2 ][ 0 ][ 'firma_adi' ]
	,'bolum_id'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'bolum_id' ]						: $personeller[ 2 ][ 0 ][ 'bolum_id' ]
	,'bolum_adi'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'bolum_adi' ]					: $personeller[ 2 ][ 0 ][ 'bolum_adi' ]
	,'tc_no'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'tc_no' ]						: $personeller[ 2 ][ 0 ][ 'tc_no' ]
	,'adi'							=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'adi' ]							: $personeller[ 2 ][ 0 ][ 'adi' ]
	,'soyadi'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'soyadi' ]						: $personeller[ 2 ][ 0 ][ 'soyadi' ]
	,'ise_giris_tarihi'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'ise_giris_tarihi' ]				: $personeller[ 2 ][ 0 ][ 'ise_giris_tarihi' ]
	,'isten_cikis_tarihi'			=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'isten_cikis_tarihi' ]			: $personeller[ 2 ][ 0 ][ 'isten_cikis_tarihi' ]
	,'sgk_kanun_no_id'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'sgk_kanun_no_id' ]				: $personeller[ 2 ][ 0 ][ 'sgk_kanun_no_id' ]
	,'sgk_kanun_no_adi'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'sgk_kanun_no_adi' ]				: $personeller[ 2 ][ 0 ][ 'sgk_kanun_no_adi' ]
	,'ucret'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'ucret' ]						: $personeller[ 2 ][ 0 ][ 'ucret' ]
	,'calisma_gunu'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'calisma_gunu' ]					: $personeller[ 2 ][ 0 ][ 'calisma_gunu' ]
	,'hakedis_saati'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'hakedis_saati' ]				: $personeller[ 2 ][ 0 ][ 'hakedis_saati' ]
	,'agi'							=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'agi' ]							: $personeller[ 2 ][ 0 ][ 'agi' ]
	,'normal_calisma_tutari'		=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'normal_calisma_tutari' ]		: $personeller[ 2 ][ 0 ][ 'normal_calisma_tutari' ]
	,'yuzde_50_saati'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'yuzde_50_saati' ]				: $personeller[ 2 ][ 0 ][ 'yuzde_50_saati' ]
	,'yuzde_100_saati'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'yuzde_100_saati' ]				: $personeller[ 2 ][ 0 ][ 'yuzde_100_saati' ]
	,'ikinci_fazla_mesai_odemesi'	=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'ikinci_fazla_mesai_odemesi' ]	: $personeller[ 2 ][ 0 ][ 'ikinci_fazla_mesai_odemesi' ]
	,'mesai_kazanci'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'mesai_kazanci' ]				: $personeller[ 2 ][ 0 ][ 'mesai_kazanci' ]
	,'toplam_kesinti_saati'			=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'toplam_kesinti_saati' ]			: $personeller[ 2 ][ 0 ][ 'toplam_kesinti_saati' ]
	,'toplam_gelmeme_kesintisi'		=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'toplam_gelmeme_kesintisi' ]		: $personeller[ 2 ][ 0 ][ 'toplam_gelmeme_kesintisi' ]
	,'hesaplama_hatasi'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'hesaplama_hatasi' ]				: $personeller[ 2 ][ 0 ][ 'hesaplama_hatasi' ]
	,'bankaya_odenen'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'bankaya_odenen' ]				: $personeller[ 2 ][ 0 ][ 'bankaya_odenen' ]
	,'bes'							=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'bes' ]							: $personeller[ 2 ][ 0 ][ 'bes' ]
	,'avans_toplami'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'avans_toplami' ]				: $personeller[ 2 ][ 0 ][ 'avans_toplami' ]
	,'borc_tutari'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'borc_tutari' ]					: $personeller[ 2 ][ 0 ][ 'borc_tutari' ]
	,'odeme_tutari'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'odeme_tutari' ]					: $personeller[ 2 ][ 0 ][ 'odeme_tutari' ]
	,'iskur_odemesi'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'iskur_odemesi' ]				: $personeller[ 2 ][ 0 ][ 'iskur_odemesi' ]
	,'personel_resim'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'personel_resim' ]				: $personeller[ 2 ][ 0 ][ 'personel_resim' ]
);

?>

<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-3">
				<div class="card card-primary card-outline">
					<div class="card-body box-profile">
						<div class="text-center">
						<img class="profile-user-img img-fluid img-circle" src="<?php echo 'personel_resimler/' . $secilen_personel_bilgileri[ 'personel_resim' ]; ?>" alt="User profile picture">
						</div>

						<h3 class="profile-username text-center"><?php echo $secilen_personel_bilgileri[ 'adi' ] . " " . $secilen_personel_bilgileri[ 'soyadi' ]; ?></h3>

						<p class="text-muted text-center"><?php echo $secilen_personel_bilgileri[ 'firma_adi' ]; ?></p>

						<ul class="list-group list-group-unbordered mb-3">
							<li class="list-group-item">
								<b>TC No</b> <a class="float-right"><?php echo $secilen_personel_bilgileri[ 'tc_no' ]; ?></a>
							</li>
							<li class="list-group-item">
								<b>SGK Kanun No</b> <a class="float-right"><?php echo $secilen_personel_bilgileri[ 'sgk_kanun_no_adi' ]; ?></a>
							</li>
							<li class="list-group-item">
								<b>Çalışma Günü</b> <a class="float-right"><?php echo $secilen_personel_bilgileri[ 'calisma_gunu' ]; ?></a>
							</li>
							<li class="list-group-item">
								<b>Ücret</b> <a class="float-right"><?php echo number_format( $secilen_personel_bilgileri[ 'ucret' ], 2, ',', '.'); ?></a>
							</li>
							<li class="list-group-item">
								<b>Avans Toplamı</b> <a class="float-right"><?php echo number_format( $secilen_personel_bilgileri[ 'avans_toplami' ], 2, ',', '.'); ?></a>
							</li>
							<li class="list-group-item">
								<b>Bankaya Ödenen</b> <a class="float-right"><?php echo number_format( $secilen_personel_bilgileri[ 'bankaya_odenen' ], 2, ',', '.'); ?></a>
							</li>
						</ul>

						<a href="?modul=personelEkle&islem=guncelle&personel_id=<?php echo $personel_id; ?>" class="btn btn-primary btn-block"><b>Düzenle</b></a>
					</div>
					<!-- /.card-body -->
				</div>
				<div class="card card-primary card-outline">
					<div class="card-header">
						<h3 class="card-title">Personel Seçin</h3>
						<div class = "card-tools">
							<a href="?modul=personelEkle&islem=ekle" class="btn btn-sm btn-default"><span class="fa fa-user-plus"></span></a>
						</div>
					</div>
					<div class="card-body">
						<div class="form-group">
							<select  class="form-control select2" name = "personel_id" id = "personel_id" data-placeholder = "Personel ara...">
									<option value="">Seçiniz</option>
								<?php foreach( $personeller[ 2 ] AS $personel ) { ?>
									<option value = "<?php echo $personel[ 'id' ]; ?>" <?php if( $personel_id == $personel[ 'id' ] ) echo 'selected'?>><?php echo $personel[ 'adi' ] . " " . $personel[ 'soyadi' ]; ?></option>
								<?php } ?>
							</select>
						</div>
						<!-- table class="table table-bordered">
							<thead>
								<tr>
									<th  width = "25%"></th>
									<th>Adı Soyadı</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach( $personeller[ 2 ] as $personel ) { ?>
								<tr>
									<td align = "center">
										<img class="profile-user-img-personel-listesi img-fluid img-circle" src="<?php echo 'personel_resimler/' . $personel[ 'personel_resim' ]; ?>">
									</td>
									<td><a href = "?modul=personel&personel_id=<?php echo $personel[ 'id' ]?>"><?php echo $personel[ 'adi' ] . " " . $personel[ 'soyadi' ]; ?></a></td>
								</tr>
							<?php } ?>
							</tbody>
						</table -->
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title">Detaylar</h3>
						<div class="card-tools">
							<a href="?modul=personelEkle&islem=guncelle&personel_id=<?php echo $personel_id; ?>" data-toggle="tooltip" data-placement="left" title="Düzenle"><i class="fa fa-edit"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table class="table table-striped table-valign-middle" >
							<tbody>
							<?php foreach( $personel_detaylar[ 2 ] AS $detay ) { ?>
								<tr>
									<td>TC No</td>
									<td colspan = "3" align = "right"><?php echo $detay[ 'tc_no' ]; ?></td>
								</tr>
								<tr>
									<td>Adı Soyadı</td>
									<td colspan = "3" align = "right"><?php echo $detay[ 'adi' ] . " " . $detay[ 'soyadi' ]; ?></td>
								</tr>
								<tr>
									<td>Firma</td>
									<td colspan = "3" align = "right"><?php echo $detay[ 'firma_adi' ]; ?></td>
								</tr>
								<tr>
									<td>Bölüm</td>
									<td colspan = "3" align = "right"><?php echo $detay[ 'bolum_adi' ]; ?></td>
								</tr>
								<tr>
									<td>SGK Kanun No</td>
									<td colspan = "3" align = "right"><?php echo $detay[ 'sgk_kanun_no_adi' ]; ?></td>
								</tr>
								<tr>
									<td>İşe Giriş Tarihi</td>
									<td colspan = "3" align = "right"><?php echo $fn->tarihFormatiDuzelt( $detay[ 'ise_giris_tarihi' ] ); ?></td>
								</tr>
								<tr>
									<td>İşeten Çıkış Tarihi</td>
									<td colspan = "3" align = "right"><?php echo $fn->tarihFormatiDuzelt( $detay[ 'isten_cikis_tarihi' ] ); ?></td>
								</tr>
								<tr>
									<td>Çalışma Günü</td>
									<td colspan = "3" align = "right"><?php echo $detay[ 'calisma_gunu' ]; ?></td>
								</tr>
								<tr>
									<td>Ücret</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'ucret' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Hakediş Saati</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'hakedis_saati' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Agi</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'agi' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Normal Çalışma Tutarı</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'normal_calisma_tutari' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Yüzde 50 Saati</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'yuzde_50_saati' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Yüzde 100 Saati</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'yuzde_100_saati' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>İkinci Fazla Mesai Ödemesi</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'ikinci_fazla_mesai_odemesi' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Mesai Kazancı</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'mesai_kazanci' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Toplam Kesinti Saati</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'toplam_kesinti_saati' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Toplama Gelmeme Saati</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'toplam_gelmeme_kesintisi' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Hesaplama Hatası</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'hesaplama_hatasi' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Bankaya Ödenen</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'bankaya_odenen' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Bes</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'bes' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Avans Toplamı</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'avans_toplami' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Borç Tutarı</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'borc_tutari' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>Ödeme Tutarı</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'odeme_tutari' ], 2, ',', '.') ?></td>
								</tr>
								<tr>
									<td>İşkur Ödemesi</td>
									<td colspan = "3" align = "right"><?php echo number_format( $detay[ 'iskur_odemesi' ], 2, ',', '.') ?></td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-5">
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title">Özlük Dosyaları</h3>
						<div class="card-tools">
							<a href="?modul=personelOzlukDosyalari&personel_id=<?php echo $personel_id; ?>" data-toggle="tooltip" data-placement="left" title="Düzenle"><i class="fa fa-edit"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table class="table table-striped table-valign-middle">
							<tbody>
							<?php 
								if( count( $personel_ozluk_dosyalari[ 2 ] ) > 0 ) {
								foreach( $personel_ozluk_dosyalari[ 2 ] AS $dosya ) { ?>
								<tr>
									<td>
										<?php echo $dosya[ 'adi' ]; ?>
									</td>
									<td colspan = "3" align = "right">
										<a href = "personel_ozluk_dosyalari/<?php echo $dosya[ 'dosya' ]; ?>" data-toggle="tooltip" data-placement="left" title="Dosyayı İndir"><i class = "fa fa-download"></i></a>
									</td>
								</tr>
							<?php 
									}
								} else {
									echo "<a href='?modul=personelOzlukDosyalari&personel_id=$personel_id'>Özlük Dosyası Ekle</a>";
								}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
	$( '#personel_id' ).on( 'select2:select', function ( e ) {
		window.location = window.location.origin + '/index.php?modul=personel&personel_id=' + e.params.data.id;
	} );
</script>
