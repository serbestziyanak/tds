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


$islem			= array_key_exists( 'islem'			,$_REQUEST ) ? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id	= array_key_exists( 'personel_id'	,$_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;
//Personele Ait Listelenecek Hareket Ay
$listelenecekAy	= array_key_exists( 'tarih'	,$_REQUEST ) ? $_REQUEST[ 'tarih' ]	: date("Y-m");

$satir_renk				= $personel_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $personel_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $personel_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';

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
FROM
	tb_personel AS p
WHERE
	p.id = ? AND p.aktif = 1
SQL;

//SELECT *, COUNT(tarih) AS tarihSayisi FROM tb_giris_cikis GROUP BY tarih ORDER BY tarih ASC
$SQL_tum_giris_cikis = <<< SQL
SELECT
	id
	,tarih
	,COUNT(tarih) AS tarihSayisi
	
FROM
	tb_giris_cikis
WHERE
	personel_id = ? AND DATE_FORMAT(tarih,'%Y-%m') =? 
GROUP BY tarih
ORDER BY tarih ASC 
SQL;

//Belirli tarihe göre giriş çıkış yapılan saatler 
$SQL_belirli_tarihli_giris_cikis = <<< SQL
SELECT
    baslangic_saat
    ,bitis_saat
    ,(SELECT adi 
		FROM tb_giris_cikis_tipleri 
		WHERE tb_giris_cikis.islem_tipi = tb_giris_cikis_tipleri.id ) AS islemTipi
FROM
	tb_giris_cikis
WHERE
	personel_id = ? AND tarih =? 
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


$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id']) );
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];
$firma_giris_cikis_tipleri	= $vt->select( $SQL_firma_giris_cikis_tipi,array($_SESSION["firma_id"]))[2];
$giris_cikislar				= $vt->select( $SQL_tum_giris_cikis, array($personel_id,$listelenecekAy) )[2];
$tum_giris_cikis_tipleri	= $vt->select( $SQL_tum_giris_cikis_tipleri)[2];

$satir_renk					= $personel_id > 0	? 'table-warning' : '';

//Bir günde en fazla kaç giriş çıkış yapıldığını bulma
foreach($giris_cikislar AS $giriscikis){
	$tarihSayisi[] = $giriscikis["tarihSayisi"]; 
}
$tarihSayisi = max($tarihSayisi); 
?>

<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="container col-sm-12 card" style="display: block; padding: 15px 10px;">
				<button class="btn btn-outline-primary btn-lg col-xs-6 col-sm-2" data-toggle="modal" data-target="#PersonelHareketEkle">Personele Hareket Ekle</button>
				<button class="btn btn-outline-success btn-lg col-xs-6 col-sm-2" data-toggle="modal" data-target="#TopluHareketEkle">Toplu Hareket Ekle</button>
				<button class="btn btn-outline-warning   btn-lg col-xs-6 col-sm-2" data-toggle="modal" data-target="#IslemTipi">Giriş Çıkış Tipi</button>
				
				<div class="col-sm-2" style="float: right;display: flex;">
					<div class="">
						<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
							<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="tarihSec" class="form-control datetimepicker-input" data-target="#datetimepicker1" data-toggle="datetimepicker" id="tarihSec" value="<?php if($listelenecekAy) echo $listelenecekAy; ?>"/>
						</div>
					</div>
					<div style="float: right;display: flex;">
						<button class="btn btn-success" id="listeleBtn">listele</button>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card card-secondary" id = "card_personeller">
					<div class="card-header">
						<h3 class="card-title">Personeller</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_personel" data-toggle = "tooltip" title = "Yeni bir personel ekle" href = "?modul=personel&islem=ekle" class="btn btn-tool" ><i class="fas fa-user-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_personeller" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>TC No</th>
									<th>Adı</th>
									<th>Soyadı</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $personeller[ 2 ] AS $personel ) { ?>
								<tr <?php if( $personel[ 'id' ] == $personel_id ) echo "class = '$satir_renk'"; ?>>
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $personel[ 'tc_no' ]; ?></td>
									<td><?php echo $personel[ 'adi' ]; ?></td>
									<td><?php echo $personel[ 'soyadi' ]; ?></td>

									<td align = "center">
										<a modul = 'personel' yetki_islem="duzenle" class = "btn btn-sm btn-success btn-xs" href = "?modul=giriscikis&personel_id=<?php echo $personel[ 'id' ]; ?>&tarih=<?php echo $listelenecekAy; ?>" >
											Hareketler
										</a>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-8">
				<div class="card card-secondary">
					<div class="card-header p-2">
						<ul class="nav nav-pills">
							<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Personel Hareketleri</h6>
						</ul>
					</div>
					<div class="card-body">
						<table id="tbl_giriscikislar" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Tarih</th>
									<th>Gün</th>
									<?php
										$i = 1;
										while ($i <= $tarihSayisi) {
											
											$thBaslikilk = $i == 1 ? 'İlk Giriş' : 'Giriş';

											$thBaslikSon = $i == $tarihSayisi ? 'Son Çıkış' : 'Çıkış';

											echo '<th>'.$thBaslikilk.'</th><th>'.$thBaslikSon.'</th>';
											$i++;
										}
									?>
									<th>İşlem</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
									<th data-priority="1" style="width: 20px">Sil</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $giris_cikislar AS $giriscikis ) { ?>
								<tr>
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $fn->tarihVer($giriscikis[ 'tarih' ]); ?></td>
									<td><?php echo $fn->gunVer($giriscikis[ 'tarih' ]); ?></td>
									<?php 

										$islemtipi = array();
										//Gelen tarihe ait giriş çıkışları aldık 
										$giris_cikis_saatleri = $vt->select($SQL_belirli_tarihli_giris_cikis,array($personel_id,$giriscikis[ 'tarih' ]))[2];
										$giriscikisFarki = $tarihSayisi - count($giris_cikis_saatleri);
										
										//uygulanan işlem tipleri
										foreach($giris_cikis_saatleri AS $giriscikis){
											$islemtipi[] = $giriscikis["islemTipi"];
										}

										//Bir Personel Bir günde en cok giris çıkıs sayısı en yüksek olan tarih ise
										if (count($giris_cikis_saatleri) ==$tarihSayisi ) {
											foreach($giris_cikis_saatleri AS $giriscikis){
												$baslangicSaat = $giriscikis[ 'baslangic_saat' ] == '' ? ' - ' : $giriscikis[ 'baslangic_saat' ];
												$bitisSaat = $giriscikis[ 'bitis_saat' ] == '' ? ' - ' : $giriscikis[ 'bitis_saat' ];
												echo '
													<td class="text-center">'.$baslangicSaat.'</td>
													<td class="text-center">'.$bitisSaat.'</td>	
												';
											}
										}else if(count($giris_cikis_saatleri) == 1 ){ // 1 Günde sadece bir kes giriş çıkış yapmıs ise 
											echo '<td class="text-center">'.$giris_cikis_saatleri[0][ 'baslangic_saat' ].'</td>';
											$i = 1;
											while ($i <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
												echo '
													<td class="text-center"> - </td>
													<td class="text-center"> - </td>	
												';
												$i++;
											}
											echo '<td class="text-center">'.$giris_cikis_saatleri[0][ 'bitis_saat' ].'</td>';
										}else{ //Gündee birden fazla giriş çıkış var ise 
											$i = 1;
											foreach($giris_cikis_saatleri AS $giriscikis){
												
												if($i < count($giris_cikis_saatleri)){

													$baslangicSaat = $giriscikis[ 'baslangic_saat' ] == '' ? ' - ' : $giriscikis[ 'baslangic_saat' ];
													$bitisSaat = $giriscikis[ 'bitis_saat' ] == '' ? ' - ' : $giriscikis[ 'bitis_saat' ];
													echo '
														<td class="text-center">'.$baslangicSaat.'</td>
														<td class="text-center">'.$bitisSaat.'</td>';
												}else{
													echo '<td  class="text-center">'.$giriscikis[ 'baslangic_saat' ].'</td>';
													$j = 1;
													while ($j <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
														echo '
															<td class="text-center"> - </td>
															<td class="text-center"> - </td>	
														';
														$j++;
													}
													echo '<td class="text-center">'.$giriscikis[ 'bitis_saat' ].'</td>';
												}
												$i++;
											}
										}
									?>
									
									<td> 
										<?php 
											echo implode(", ", $islemtipi);
										?>
									</td>
									<td align = "center">
										<a modul = 'giriscikis' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=giriscikis&islem=guncelle&giriscikis_id=<?php echo $giriscikis[ 'id' ]; ?>" >
											Düzenle
										</a>
									</td>
									<td align = "center">
										<button modul= 'giriscikis' yetki_islem="sil" class="btn btn-xs btn-danger" data-href="_modul/giriscikis/giriscikisSEG.php?islem=sil&personel_id=<?php echo $personel_id; ?>&giriscikis_tarih=<?php echo $giriscikis[ 'tarih' ]; ?>" data-toggle="modal" data-target="#sil_onay">Sil</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!--Toplu Hareket Ekleme Modalı-->
<div class="modal fade" id="TopluHareketEkle"  aria-modal="true" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="_modul/giriscikis/giriscikisSEG.php" method="post" >
				<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
				<input type = "hidden" name = "toplu" value = "toplu">
				<div class="modal-header">
					<h4 class="modal-title">Toplu Hareket Ekle</h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-info alert-dismissible">
						<h5><i class="icon fas fa-info"></i> Bilgi!</h5>
						Saatlı olan işlem tiplerinde saat seçilebilir. Örneğin yıllık veya günlük izin verilirken saat seçimine gerek yoktur.
					</div>
					<div class="form-group">
						<label class="control-label">Başlangıc Tarihi ve Saati</label>
						<div class="input-group date" id="toplubaslangicDateTime" data-target-input="nearest">
							<div class="input-group-append" data-target="#toplubaslangicDateTime" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="baslangicTarihSaat" class="form-control datetimepicker-input" data-target="#toplubaslangicDateTime" data-toggle="datetimepicker"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label">Bitiş Tarihi ve Saati</label>
						<div class="input-group date" id="toplubitisDateTime" data-target-input="nearest">
							<div class="input-group-append" data-target="#toplubitisDateTime" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="bitisTarihSaat" class="form-control datetimepicker-input" data-target="#toplubitisDateTime" data-toggle="datetimepicker"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label">İşlem Tipi</label>
						<select class="form-control select2" name = "islem_tipi">
							<option value="">Seçiniz</option>
							<?php foreach( $firma_giris_cikis_tipleri as $tip ) { ?>
								<option value="<?php echo $tip[ 'id' ]; ?>"><?php echo $tip['adi']; ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Açıklama</label>
						<textarea class="form-control" rows="2" name="aciklama" placeholder="Açıklama Yazabilirisniz"></textarea>
					</div>
					
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
					<button type="submit" class="btn btn-success">Kaydet</button>
					
				</div>
			</form>
		</div>
	</div>
</div>


<!--Personel Hareket Ekleme Modalı-->
<div class="modal fade" id="PersonelHareketEkle"  aria-modal="true" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="_modul/giriscikis/giriscikisSEG.php" method="post" >
				<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
				<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
				<div class="modal-header">
					<h4 class="modal-title">Personel Hareket Ekle</h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-info alert-dismissible">
						<h5><i class="icon fas fa-info"></i> Bilgi!</h5>
						Saatlı olan işlem tiplerinde saat seçilebilir. Örneğin yıllık veya günlük izin verilirken saat seçimine gerek yoktur.
					</div>
					<div class="form-group">
						<label class="control-label">Başlangıc Tarihi ve Saati</label>
						<div class="input-group date" id="baslangicDateTime" data-target-input="nearest">
							<div class="input-group-append" data-target="#baslangicDateTime" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="baslangicTarihSaat" class="form-control datetimepicker-input" data-target="#baslangicDateTime" data-toggle="datetimepicker"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label">Bitiş Tarihi ve Saati</label>
						<div class="input-group date" id="bitisDateTime" data-target-input="nearest">
							<div class="input-group-append" data-target="#bitisDateTime" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="bitisTarihSaat" class="form-control datetimepicker-input" data-target="#bitisDateTime" data-toggle="datetimepicker"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label">İşlem Tipi</label>
						<select class="form-control select2" name = "islem_tipi">
							<option value="">Seçiniz</option>
							<?php foreach( $firma_giris_cikis_tipleri as $tip ) { ?>
								<option value="<?php echo $tip[ 'id' ]; ?>"><?php echo $tip['adi']; ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Açıklama</label>
						<textarea class="form-control" rows="2" name="aciklama" placeholder="Açıklama Yazabilirisniz"></textarea>
					</div>
					
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
					<button type="submit" class="btn btn-success">Kaydet</button>
					
				</div>
			</form>
		</div>
	</div>
</div>

<!--Firma Tip İşlemleri -->
<div class="modal fade" id="IslemTipi"  aria-modal="true" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Giriş Çıkış - İzin Tipi Ayarlama</h4>
				</div>
				<div class="modal-body">
					<table id="tbl_firmaGirisCikis" class="table table-bordered table-hover table-sm" width = "100%">
						<thead>
							<tr>
								<th style="width: 15px">#</th>
								<th>Baslık</th>
								<th>Maaş Kesintisi</th>
								<th data-priority="1" style="width: 20px">Sil</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								$sayi = 1;
								foreach ($firma_giris_cikis_tipleri as $giris_cikis_tipi) {
									$maas_kesintisi = $giris_cikis_tipi["maas_kesintisi"] ==1 ? 'Kesintili':' Kesintisiz';
									echo '<tr>';
										echo '<td>'.$sayi.'</td>';
										echo '<td>'.$giris_cikis_tipi["adi"].'</td>';
										echo '<td>'.$maas_kesintisi.'</td>';
										echo '<td><button modul= "giriscikis" yetki_islem="sil" class="btn btn-xs btn-danger" data-href="_modul/giriscikis/giriscikisSEG.php?islem=tipsil&tip_id='.$giris_cikis_tipi["id"].'" data-toggle="modal" data-target="#sil_onay">Sil</button></td>';
									echo '</tr>';
									$sayi++;
								} 
							?>
						</tbody>
					</table>
					<button class="btn btn-outline-info col-sm-6 offset-sm-3" data-toggle="modal" data-target="#IslemTipleri"> Farklı İzin Kaydet</button>
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">Kapat</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!--Tüm İşlem Tip Listesi -->
<div class="modal fade" id="IslemTipleri"  aria-modal="true" role="dialog" >
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form action="_modul/giriscikis/tipSEG.php" method="post">
				<input type="hidden" name="islem" value="ekle">
				<div class="modal-header">
					<h4 class="modal-title">Giriş Çıkış - İzin Tipi Ayarlama</h4>
				</div>
				<div class="modal-body">
					<div style="display: none;" id="FirmaTipSayisi" data-tipSayi="<?php echo count($firma_giris_cikis_tipleri); ?>"> </div>
					<table id="tbl_giriscikislar" class="table table-bordered table-hover table-sm" width = "100%">
						<thead>
							<tr>
								<th style="width: 15px">Seçiniz</th>
								<th>Baslık</th>
								<th>Maaş Kesintisi</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								$sayi = 1;
								foreach ($tum_giris_cikis_tipleri as $giris_cikis_tipi) {
									if ($giris_cikis_tipi["varmi"] == null) {
										echo '<tr class="TipTr TipTr-'.$giris_cikis_tipi["id"].'">';
											echo '<td class="text-center">
														<div class="form-group">
															<div class="icheck-success d-inline">
																<input type="checkbox" name = "tip_id[]" value="'.$giris_cikis_tipi["id"].'" id="TipId-'.$giris_cikis_tipi["id"].'">
																<label for="TipId-'.$giris_cikis_tipi["id"].'">
																</label>
															</div>
														</div>
													</td>';
											echo '<td>'.$giris_cikis_tipi["adi"].'</td>';
											echo '<td>
													<div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-focused bootstrap-switch-animate bootstrap-switch-off" >
														<div class="bootstrap-switch-container" >
															<input type="checkbox" name="maas_kesintisi[]" checked="" value="'.$giris_cikis_tipi["id"].'" data-bootstrap-switch="" data-off-color="danger" data-on-text="Kesintili" data-off-text="Kesintisiz" data-on-color="success">
														</div>
													</div>
												</td>';
										echo '</tr>';
										$sayi++;
									}
								} 
							?>
						</tbody>
					</table>
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
					<button type="submit" class="btn btn-success">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade"  id="sil_onay">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Lütfen Dikkat</h4>
			</div>
			<div class="modal-body">
				<p>Bu kaydı silmek istediğinize emin misiniz?</p>
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
				<a class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>
	/* Kayıt silme onay modal açar. */
	$( '#sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>


<script type="text/javascript">


	$(function () {
		$('#datetimepicker1').datetimepicker({
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

	$(function () {
		$('#baslangicDateTime').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM-DD HH:mm',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});

	$(function () {
		$('#bitisDateTime').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM-DD HH:mm',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});

	$(function () {
		$('#toplubaslangicDateTime').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM-DD HH:mm',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});

	$(function () {
		$('#toplubitisDateTime').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM-DD HH:mm',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});
	

	$("body").on('click', '#listeleBtn', function() {
		const tarih 		= $("#tarihSec").val();
		const  url 			= window.location;
		const origin		= url.origin;
		const path			= url.pathname;
		const search		= (new URL(document.location)).searchParams;
		const modul   		= search.get('modul');
		const personel_id   = search.get('personel_id');
		window.location.replace(origin + path+'?modul='+modul+'&personel_id='+personel_id+'&tarih='+tarih);
	})

	var tbl_personeller = $( "#tbl_personeller" ).DataTable( {
		"responsive": true, "lengthChange": true, "autoWidth": true,
		"stateSave": true,
		"pageLength" : 15,
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
	} ).buttons().container().appendTo('#tbl_personeller_wrapper .col-md-6:eq(0)');

	$('#card_personeller').on('maximized.lte.cardwidget', function() {
		var tbl_personeller = $( "#tbl_personeller" ).DataTable();
		var column = tbl_personeller.column(  tbl_personeller.column.length - 1 );
		column.visible( ! column.visible() );
		var column = tbl_personeller.column(  tbl_personeller.column.length - 2 );
		column.visible( ! column.visible() );
	});

	$('#card_personeller').on('minimized.lte.cardwidget', function() {
		var tbl_personeller = $( "#tbl_personeller" ).DataTable();
		var column = tbl_personeller.column(  tbl_personeller.column.length - 1 );
		column.visible( ! column.visible() );
		var column = tbl_personeller.column(  tbl_personeller.column.length - 2 );
		column.visible( ! column.visible() );
	} );
</script>
