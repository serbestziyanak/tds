<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();


/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'tarife_id' ]			= $_SESSION[ 'sonuclar' ][ 'id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$islem			= array_key_exists( 'islem'			,$_REQUEST ) ? $_REQUEST[ 'islem' ]			: 'ekle';
$detay			= array_key_exists( 'detay'			,$_REQUEST ) ? $_REQUEST[ 'detay' ]			: 'genel';
$tarife_id		= array_key_exists( 'tarife_id'		,$_REQUEST ) ? $_REQUEST[ 'tarife_id' ]		: 0;


$satir_renk				= $tarife_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $tarife_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $tarife_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';



$SQL_tum_tarife_oku = <<< SQL
SELECT 
	t.id,
	t.adi,
	t.baslangic_tarih,
	t.bitis_tarih,
	t.min_calisma_saati,
	t.gun_donumu,
	t.grup_id,
	mt.adi AS mesai_adi,
	(select 
		COUNT(id) 
	FROM tb_molalar 
	WHERE  
		t.id = tb_molalar.tarife_id ) AS molaSayisi,
	(select 
		COUNT(id) 
	FROM tb_tarife_saati
	WHERE  
		t.id = tb_tarife_saati.tarife_id ) AS saatSayisi
FROM 
	tb_tarifeler AS t
INNER JOIN tb_mesai_turu AS mt ON 
	mt.id = t.mesai_turu
WHERE 
	t.firma_id 	= ? AND
	t.aktif 	= 1
ORDER BY t.id ASC
SQL;


$SQL_tek_tarife_oku = <<< SQL
SELECT 
	t.*,
	mt.adi AS mesai_adi
FROM 
	tb_tarifeler AS t
INNER JOIN tb_mesai_turu AS mt ON 
	mt.id 	= t.mesai_turu
WHERE 
	t.id 		= ? AND
	t.firma_id 	= ? AND
	t.aktif 	= 1 
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
	od.tarife_id = ?
SQL;



/* Sabit tablolar */
$SQL_gruplar = <<< SQL
SELECT
	*
FROM
	tb_gruplar
WHERE
	firma_id = ? AND
	aktif = 1
SQL;

/* Sabit tablolar */
$SQL_mesai_turleri = <<< SQL
SELECT
	*
FROM
	tb_mesai_turu
WHERE
	firma_id = ? AND
	aktif = 1
SQL;

/*Tarifeye Ait Molaları Getirme*/
$SQL_mola_getir = <<< SQL
SELECT 
	*
FROM 
	tb_molalar
WHERE 
	tarife_id	= ? AND 
	aktif 	   	= 1
SQL;

/*Tarifeye Ait Molaları Getirme*/
$SQL_tarife_saat_getir = <<< SQL
SELECT 
	*
FROM 
	tb_tarife_saati
WHERE 
	tarife_id	= ? AND 
	aktif 	   	= 1
SQL;

$SQL_carpanlar = <<< SQL
SELECT 
	*
FROM 
	tb_carpanlar
WHERE 
	firma_id	= ?
SQL;


$tarifeler					= $vt->select( $SQL_tum_tarife_oku, array($_SESSION['firma_id'] ) )[ 2 ];
$tek_tarife					= $vt->select( $SQL_tek_tarife_oku, array( $tarife_id, $_SESSION['firma_id'] ) )[ 2 ][ 0 ];
$gruplar					= $vt->select( $SQL_gruplar			,array( $_SESSION[ "firma_id" ] ) )[ 2 ];
$mesai_turleri				= $vt->select( $SQL_mesai_turleri	,array( $_SESSION['firma_id'] ) )[ 2 ];
$tarifeyeAitmolaGetir 		= $vt->select( $SQL_mola_getir, array( $tarife_id ) )[ 2 ];
$tarifeyeAitsaatGetir 		= $vt->select( $SQL_tarife_saat_getir, array( $tarife_id ) )[ 2 ];
$carpanlar					= $vt->select( $SQL_carpanlar	,array( $_SESSION['firma_id'] ) )[ 2 ];


//Günlük En fazla Mola Sayısı
foreach($tarifeler AS $mola){
	$molaSayisi[] = $mola[ "molaSayisi" ]; 
	$saatSayisi[] = $mola[ "saatSayisi" ]; 
}
$molaSayisi = max($molaSayisi);
$saatSayisi = max($molaSayisi);


/*Seçili grupları arraya atıyoruz ve boş elemanları siliyoruz*/
$secili_gruplar   = explode(",", $tek_tarife["grup_id"]);
$secili_gruplar   = array_filter($secili_gruplar); 
$gruplar_duzenle  = array();
foreach ( $gruplar as $grup ) {
	/*Grupları indexlerini göre ayarlıyorum*/
	$gruplar_duzenle[$grup[ 'id']] = array();
	array_push($gruplar_duzenle[$grup[ 'id']], $grup[ 'id'], $grup[ 'adi'] );
}
?>

<div class="modal fade" id="sil_onay">
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


<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-7">
				<div class="card card-secondary" id = "card_personeller">
					<div class="card-header">
						<h3 class="card-title">Tarifeler</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_personel" data-toggle = "tooltip" title = "Yeni bir tarife ekle" href = "?modul=tarifeler&islem=ekle" class="btn btn-tool" ><i class="fas fa-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_personeller" class="table table-bordered table-hover table-sm" width = "100%" >
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Adı</th>
									<th>Mesai Türü</th>
									<th>Baş. Tar.</th>
									<th>Bit. Tar.</th>
									<th>Gün Dön.</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
									<th data-priority="1" style="width: 20px">Sil</th>
								</tr>
							</thead>
							<tbody>
								<?php 

									$sayi = 1; 
									foreach( $tarifeler AS $tarife ) { 
										$grup_adlari = array();
										/*Seçili grupları arraya atıyoruz ve boş elemanları siliyoruz*/
										$tarifeler_secili_gruplar   = explode(",", $tarife["grup_id"]);
										$tarifeler_secili_gruplar   = array_filter($tarifeler_secili_gruplar); 
										/*Seçiilmiş olan grupların başlıklarını alıp birleştiriyoruz*/
										foreach ( $tarifeler_secili_gruplar as  $grup) {
											$grup_adlari[] = array_key_exists($grup, $gruplar_duzenle) ? $gruplar_duzenle[$grup][1] : null;
											
										}
										$grup_adlari = implode(", ", $grup_adlari);
								?>
									<tr oncontextmenu="fun();" class ="personel-Tr <?php if( $tarife[ 'id' ] == $tarife_id ) echo $satir_renk; ?>" data-id="<?php echo $tarife[ 'id' ]; ?>" data-toggle="tooltip" role="button" title="<?php echo $grup_adlari; ?>">
										<td><?php echo $sayi++; ?></td>
										<td><?php echo $tarife[ 'adi' ]; ?></td>
										<td><?php echo $tarife[ 'mesai_adi' ]; ?></td>
										<td><?php echo $fn->tarihFormatiDuzelt( $tarife[ 'baslangic_tarih' ] ); ?></td>
										<td><?php echo $fn->tarihFormatiDuzelt( $tarife[ 'bitis_tarih' ] ); ?></td>
										<td><?php echo date( 'H:i', strtotime( $tarife[ 'gun_donumu' ] ) );  ?></td>
										
										<td align = "center">
											<a modul = 'tarifeler' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=tarifeler&islem=guncelle&tarife_id=<?php echo $tarife[ 'id' ]; ?>" >
												Düzenle
											</a>
										</td>
										<td align = "center">
											<button modul= 'tarifeler' yetki_islem="sil" class="btn btn-xs btn-danger" data-href="_modul/tarifeler/tarifelerSEG.php?islem=sil&tarife_id=<?php echo $tarife[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay">Sil</button>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-5">
				<div class="card <?php if( $tarife_id == 0 ) echo 'card-secondary' ?>">
					<div class="card-header p-2">
						<ul class="nav nav-pills tab-container">
							<?php if( $tarife_id > 0 ) { ?>
								<li class="nav-item" style="width: 33%;">
									<a class="nav-link <?php echo $detay == 'genel' ? 'active' : ''; ?>" href="#_genel" id="tab_genel" data-toggle="tab">Genel</a>
								</li>
								<li class="nav-item" style="width: 33%;">
									<a class="nav-link <?php echo $detay == 'saat' ? 'active' : ''; ?>" href="#_saat" id="tab_nufus" data-toggle="tab" disabled>Mesai Saatleri</a>
								</li>
								<li class="nav-item" style="width: 33%;">
									<a class="nav-link <?php echo $detay == 'mola' ? 'active' : ''; ?>" href="#_mola" id="tab_adres" data-toggle="tab">Mola Saatleri</a>
								</li>
							<?php } else {
								echo "<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Tarife Ekle</h6>";
								}
							?>
							
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content">
							<!-- GENEL BİLGİLER -->
							<div class="tab-pane <?php echo $detay == 'genel' ? 'active' : ''; ?>" id="_genel">
								<form class="form-horizontal" action = "_modul/tarifeler/tarifelerSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "tarife_id" value = "<?php echo $tarife_id; ?>">
									<h3 class="profile-username text-center"><b> </b></h3>
									<div class="form-group">
										<label class="control-label">Adı</label>
										<input required type="text" class="form-control" name ="adi" value = "<?php echo $tek_tarife[ "adi" ]; ?>"  autocomplete="off">
									</div>
									<div class="form-group">
										<label  class="control-label">Gruplar</label>
										<select  class="form-control select2"  multiple="multiple" name = "grup_id[]" required>
											<?php foreach( $gruplar as $grup ) { ?>
												<option value = ",<?php echo $grup[ 'id' ]; ?>," <?php echo in_array($grup[ 'id' ], $secili_gruplar) ? 'selected' : ''; ?>><?php echo $grup['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Mesai Türü</label>
										<select class="form-control" name = "mesai_turu" required>
											<option value="">Seçiniz</option>
											<?php foreach( $mesai_turleri as $mesai ) { ?>
												<option value="<?php echo $mesai[ 'id' ]; ?>" <?php if( $tek_tarife[ 'mesai_turu' ] == $mesai[ 'id' ] ) echo 'selected'; ?>><?php echo $mesai['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Başlangıç Tarihi</label>
										<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="tarihalani-baslangic_tarih" value="<?php echo $fn->tarihFormatiDuzelt(  $tek_tarife[ "baslangic_tarih" ] ); ?>" class="form-control datetimepicker-input" data-target="#datetimepicker1" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Bitiş Tarihi</label>
										<div class="input-group date" id="datetimepicker2" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="tarihalani-bitis_tarih" value="<?php echo $fn->tarihFormatiDuzelt( $tek_tarife[ "bitis_tarih" ] ); ?>" class="form-control datetimepicker-input" data-target="#datetimepicker2" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Gün Dönümü</label>
										<input required type="text" class="form-control" name ="gun_donumu" value = "<?php echo date( 'H:i', strtotime($tek_tarife[ "gun_donumu" ])); ?>" placeholder="06:59, 18:45 vs.">
									</div>
									<div class="form-group">
										<label class="control-label">Geç Gelme Toleransı</label>
										<input required type="text" class="form-control" name ="gec_gelme_tolerans" value = "<?php echo $tek_tarife[ "gec_gelme_tolerans" ]; ?>"  autocomplete="off">
									</div>
									<div class="form-group">
										<label class="control-label">Erken Çıkma Toleransı</label>
										<input required type="text" class="form-control" name ="erken_cikma_tolerans" value = "<?php echo $tek_tarife[ "erken_cikma_tolerans" ]; ?>"  autocomplete="off">
									</div>
									<div class="form-group">
										<label class="control-label">Normal Tolerans</label>
										<input required type="text" class="form-control" name ="normal_tolerans" value = "<?php echo $tek_tarife[ "normal_tolerans" ]; ?>"  autocomplete="off">
									</div>
									<label class="control-label">Mesai Durumu</label>
									<div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-focused bootstrap-switch-animate bootstrap-switch-off" >
										<div class="bootstrap-switch-container" >
											<input type="checkbox" name="tatil"  <?php echo $tek_tarife[ "tatil" ] == 1 ? 'checked': ''; ?>  data-bootstrap-switch="" data-off-color="danger" data-on-text="Tatil" data-off-text="Mesai" data-on-color="success">
										</div>
									</div>
									<div class="clearfix"></div>
									<label class="control-label">Tatil Günlerinde Ucret Yatsın mı?</label>
									<div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-focused bootstrap-switch-animate bootstrap-switch-off" >
										<div class="bootstrap-switch-container" >
											<input type="checkbox" name="maasa_etki_edilsin"  <?php echo $tek_tarife[ "maasa_etki_edilsin" ] == 1 ? 'checked': ''; ?>  data-bootstrap-switch="" data-off-color="danger" data-on-text="Evet" data-off-text="Hayır" data-on-color="success">
										</div>
									</div>
									<div class="card-footer">
										<button modul= 'tarifeler' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
									</div>
								</form>
							</div>

							<!--SAATLER -->
							<div class="tab-pane <?php echo $detay == 'saat' ? 'active' : ''; ?>" id="_saat">
								<form class="form-horizontal" action = "_modul/tarifeler/saatlerSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "tarife_id" value = "<?php echo $tarife_id; ?>">
									<div class="saatSatirlari">
									<?php 
										$saatSay = 1;
										if ( count( $tarifeyeAitsaatGetir ) > 0 ){
											
											foreach ($tarifeyeAitsaatGetir as $saat) {
												$carpanSelect = "<select name ='carpan[]' class='form-control'>";
												foreach ($carpanlar as $carpan) {
													$carpanSelect .= "<option value='$carpan[id]' ".( $carpan['id'] == $saat['carpan'] ? 'selected': '' )." >$carpan[adi]</option>";
												}
												$carpanSelect .="</select>"; 
												echo '<input type="hidden" name="id[]" value="'.$saat[ 'id' ].'">';
												echo '<div class="row saat">
														<div class="col-sm-1">
															<div class="form-group">
																<label class="control-label">#</label><br>
																<span href="" class="btn btn-default">'.$saatSay.'</span>
															</div>
														</div>
														<div class="col-sm-3">
															<div class="form-group">
																<label class="control-label">Başlangıç Saati</label>
																<input type="text" class="form-control" name ="baslangic[]" value = "'.date( "H:i", strtotime($saat[ "baslangic" ] ) ).'" required placeholder="Örk: 08:00 ">
															</div>
														</div>
														<div class="col-sm-3">
															<div class="form-group">
																<label class="control-label">Bitiş Saati</label>
																<input type="text" class="form-control" name ="bitis[]" value = "'.date( "H:i", strtotime( $saat[ "bitis" ] ) ).'" required placeholder="Örk: 18:30 ">
															</div>
														</div>
														<div class="col-sm-4">
															<div class="form-group">
																<label class="control-label">Çarpan</label>
																'.$carpanSelect.'
															</div>
														</div>
														<div class="col-sm-1">
															<div class="form-group">
																<label class="control-label">Sil</label><br>
																<a modul= "tarifeler" yetki_islem="saat_sil" class="btn btn-danger" data-href="_modul/tarifeler/saatlerSEG.php?islem=sil&tarife_id='.$tarife_id.'&saat_id='.$saat[ 'id' ].'" data-toggle="modal" data-target="#sil_onay"><i class="fas fa-trash"></i></a>
															</div>
														</div>
													</div>';
												$saatSay++;
											}
											$sonSaat = count($tarifeyeAitsaatGetir);

										}else{
											$sonSaat = $saatSayisi;
										}

									?>
									</div>
									<div class="card-footer">
										<button modul= 'tarifeler' yetki_islem="tarife_saat_kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
										<span  modul= 'tarifeler' yetki_islem="tarife_saat_ekle" class=" btn btn-info float-right saatSatirEkle" data-tur="saat" id="saatSatirEkle" data-sayi="<?php echo $sonSaat; ?>">Mesai Ekle</span>
									</div>
								</form>
							</div>

							<!--MOLALAR -->
							<div class="tab-pane <?php echo $detay == 'mola' ? 'active' : ''; ?>" id="_mola">
								<form class="form-horizontal" action = "_modul/molalar/molalarSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "tarife_id" value = "<?php echo $tarife_id; ?>">
									<div class="molaSatirlari">
									<?php 
										$molaSay = 1;
										if ( count( $tarifeyeAitmolaGetir ) > 0 ){
											foreach ($tarifeyeAitmolaGetir as $mola) {
												echo '<input type="hidden" name="id[]" value="'.$mola[ "id" ].'">';
												echo '<div class="row mola">
														<div class="col-sm-1">
															<div class="form-group">
																<label class="control-label">#</label><br>
																<span href="" class="btn btn-default">'.$molaSay.'</span>
															</div>
														</div>
														<div class="col-sm-5">
															<div class="form-group">
																<label class="control-label">Başlangıç Saati</label>
																<input type="text" class="form-control" name ="baslangic[]" value = "'.date( "H:i", strtotime($mola[ "baslangic" ] ) ).'" required placeholder="Örk: 08:00 ">
															</div>
														</div>
														<div class="col-sm-5">
															<div class="form-group">
																<label class="control-label">Bitiş Saati</label>
																<input type="text" class="form-control" name ="bitis[]" value = "'.date( "H:i", strtotime( $mola[ "bitis" ] ) ).'" required placeholder="Örk: 18:30 ">
															</div>
														</div>
														<div class="col-sm-1">
															<div class="form-group">
																<label class="control-label">Sil</label><br>
																<a modul= "tarifeler" yetki_islem="mola_sil" class="btn btn-danger" data-href="_modul/molalar/molalarSEG.php?islem=sil&tarife_id='.$tarife_id.'&mola_id='.$mola[ 'id' ].'" data-toggle="modal" data-target="#sil_onay"><i class="fas fa-trash"></i></a>
															</div>
														</div>
													</div>';
												$molaSay++;
											}
											$sonMola = count($tarifeyeAitmolaGetir);

										}else{
											$sonMola = $molaSayisi;
										}

									?>
									</div>
									<div class="card-footer">
										<button modul= 'tarifeler' yetki_islem="tarife_mola_kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
										<span  modul= 'tarifeler' yetki_islem="tarife_mola_ekle" class=" btn btn-info float-right SatirEkle" data-tur="mola" id="molaSatirEkle" data-sayi="<?php echo $sonMola; ?>">Mola Ekle</span>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<div id="sagtikmenu" style="display: none;">asdasdasd</div>
<style type="text/css">
	.custom-menu {
	    z-index:1000;
	    position: absolute;
	    background-color:#fff;
	    border: 1px solid #000;
	    padding: 2px;
	    border-radius: 5px;
	}
	.custom-menu a{
		display: block;
		padding: 10px 30px 10px 10px;
		border-bottom: 1px solid #ddd;
		color: #000;
	}
	.custom-menu a:hover{
		background-color: #ddd;
		transition: initial;
	}
	
</style>

<?php
	$carpanSelectSatirEkle = '<select name ="carpan[]" class="form-control">';
	foreach ($carpanlar as $carpan) {
		$carpanSelectSatirEkle .= '<option value="'.$carpan["id"].'">'.$carpan["adi"].'</option>';
	}
	$carpanSelectSatirEkle .='</select>'; 
?>
<script type="text/javascript">


$( "body" ).on('click', '.SatirEkle', function() {
	var tur = $(this).data("tur");
	var satirSay = 0;
	$("."+tur).each(function() {
      	satirSay = satirSay + 1;
    });
    satirSay = satirSay + 1;

	var ekleneceksatir = '<div class="row '+tur+'"><div class="col-sm-1"><div class="form-group"><label class="control-label">#</label><br><span href="" class="btn btn-default">'+satirSay+'</span></div></div><div class="col-sm-5"><div class="form-group"><label class="control-label">Başlangıç Saati</label><input type="text" class="form-control" name ="baslangic[]" required placeholder="Örk: 08:00 "></div></div><div class="col-sm-5"><div class="form-group"><label class="control-label">Bitiş Saati</label><input type="text" class="form-control" name ="bitis[]" required placeholder="Örk: 18:30 "></div></div><div class="col-sm-1"><div class="form-group"><label class="control-label">Sil</label><br><span class="btn btn-danger yenisil" data-tur="'+tur+'" id="yenisil'+tur+'"><i class="fas fa-trash"></i></span></div></div></div>';
	
	document.getElementById(tur+"SatirEkle").removeAttribute("data-sayi");
	document.getElementById(tur+"SatirEkle").setAttribute("data-sayi", satirSay); 
	$("."+tur+"Satirlari").append(ekleneceksatir);
})

$( "body" ).on('click', '.saatSatirEkle', function() {
	var tur = $(this).data("tur");
	var satirSay = 0;
	$("."+tur).each(function() {
      	satirSay = satirSay + 1;
    });
    satirSay = satirSay + 1;

	var ekleneceksatir = '<div class="row '+tur+'"><div class="col-sm-1"><div class="form-group"><label class="control-label">#</label><br><span href="" class="btn btn-default">'+satirSay+'</span></div></div><div class="col-sm-3"><div class="form-group"><label class="control-label">Başlangıç Saati</label><input type="text" class="form-control" name ="baslangic[]" required placeholder="Örk: 08:00 "></div></div><div class="col-sm-3"><div class="form-group"><label class="control-label">Bitiş Saati</label><input type="text" class="form-control" name ="bitis[]" required placeholder="Örk: 18:30 "></div></div><div class="col-sm-4"><div class="form-group"><label class="control-label">Çarpan</label><?php echo $carpanSelectSatirEkle; ?></div></div><div class="col-sm-1"><div class="form-group"><label class="control-label">Sil</label><br><span class="btn btn-danger yenisil" data-tur="'+tur+'" id="yenisil'+tur+'"><i class="fas fa-trash"></i></span></div></div></div>';
	
	document.getElementById(tur+"SatirEkle").removeAttribute("data-sayi");
	document.getElementById(tur+"SatirEkle").setAttribute("data-sayi", satirSay); 
	$("."+tur+"Satirlari").append(ekleneceksatir);
})



/*Tıklanan Mola Satırı Siliyoruz*/
$('.row').on("click", ".yenisil", function (e) {
	var tur = $(this).data("tur");
    e.preventDefault();
    $(this).closest("."+tur).remove();

});
var simdi = new Date(); 
//var simdi="11/25/2015 15:58";
$(function () {
	$('#datetimepicker1').datetimepicker({
		//defaultDate: simdi,
		format: 'DD.MM.yyyy',
		icons: {
		time: "far fa-clock",
		date: "fa fa-calendar",
		up: "fa fa-arrow-up",
		down: "fa fa-arrow-down"
		}
	});
});


$(function () {
	$('#datetimepicker2').datetimepicker({
		//defaultDate: simdi,
		format: 'DD.MM.yyyy',
		icons: {
		time: "far fa-clock",
		date: "fa fa-calendar",
		up: "fa fa-arrow-up",
		down: "fa fa-arrow-down"
		}
	});
});

$(function () {
	$(":input").inputmask();

	//Initialize Select2 Elements
	$('.select2').select2()

	//Initialize Select2 Elements
	$('.select2bs4').select2({
	  theme: 'bootstrap4'
	})
})

$( function () {
	$( '[ data-toggle="tooltip" ]' ).tooltip()
} );


var tbl_personeller = $( "#tbl_personeller" ).DataTable( {
	"responsive": true, "lengthChange": true, "autoWidth": true,
	"stateSave": true,
	"pageLength" : 25,
	//"buttons": ["excel", "pdf", "print","colvis"],

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
				return "Tarife Listesi";
			}
		},
		{
			extend	: 'print',
			text	: 'Yazdır',
			exportOptions : {
				columns : ':visible'
			},
			title: function(){
				return "Tarife Listesi";
			}
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