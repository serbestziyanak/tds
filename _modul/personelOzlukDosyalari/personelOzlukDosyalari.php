<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$islem = array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

$SQL_tum_personel_oku = <<< SQL
SELECT
	 tb_personel.id
	,CONCAT( adi, " ", soyadi ) as adi
	,(select COUNT(tb_personel_ozluk_dosyalari.id) 
		FROM tb_personel_ozluk_dosyalari 
		WHERE tb_personel_ozluk_dosyalari.personel_id = tb_personel.id 
		GROUP BY personel_id) AS dosyaSayisi
FROM
	tb_personel
WHERE
	aktif = 1
SQL;

$SQL_tek_personel_oku = <<< SQL
SELECT
	id
	,adi,
	soyadi
	,(select COUNT(tb_personel_ozluk_dosyalari.id) 
		FROM tb_personel_ozluk_dosyalari 
		WHERE tb_personel_ozluk_dosyalari.personel_id = tb_personel.id  
		GROUP BY personel_id) AS dosyaSayisi
FROM
	tb_personel
WHERE
	id = ?
SQL;

$SQL_personel_ozluk_dosyalari = <<< SQL
SELECT
	 od.id
	,od.dosya_turu_id
	,ot.adi 
	,od.dosya
FROM
	tb_personel_ozluk_dosyalari AS od
JOIN
	tb_personel_ozluk_dosya_turleri AS ot ON od.dosya_turu_id = ot.id
WHERE
	od.personel_id = ?
SQL;

$SQL_personel_ozluk_dosya_turleri = <<< SQL
SELECT
	*
FROM
	tb_personel_ozluk_dosya_turleri
SQL;

$personeller					= $vt->select( $SQL_tum_personel_oku, array() );
$personel_id					= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];
$tek_personel					= $vt->select( $SQL_tek_personel_oku, array( $personel_id ) );
$personel_ozluk_dosyalari		= $vt->select( $SQL_personel_ozluk_dosyalari, array( $personel_id ) )[2];
$personel_ozluk_dosya_turleri	= $vt->select( $SQL_personel_ozluk_dosya_turleri, array() );

//Özlük Dosyası İçin İstanilen Evrak Sayısı 
$personel_ozluk_dosya_turleri_sayisi = $personel_ozluk_dosya_turleri[3];
$satir_renk				= $personel_id > 0	? 'table-warning' : '';

$personel_ozluk_dosyalari_idleri = array();
foreach( $personel_ozluk_dosyalari as $dosya ) $personel_ozluk_dosyalari_idleri[] = $dosya[ 'dosya_turu_id' ];

?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="kayit_sil" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Lütfen Dikkat!</h4>
			</div>
			<div class="modal-body">
				Bu kaydı <b>Silmek</b> istediğinize emin misiniz?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
				<a class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>
	$( '#kayit_sil' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class = "col-md-4">
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title">Personel Seçin</h3>
					</div>
					<div class="card-body">
						<table id="tbl_personelOzlukDosyalari" class="table table-sm table-bordered table-hover">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Adı</th>
									<th style="width: 60px"> Eksik D.S.</th>
									<th data-priority=" 1" style="width: 20px">Düzenle</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1;  foreach( $personeller[ 2 ] AS $personel ) { 
									$evraklarBtnRenk = $personel_ozluk_dosya_turleri_sayisi - $personel[ "dosyaSayisi" ] == 0 ? 'success':'warning'; 
								?>
								<tr  <?php if( $personel[ 'id' ] == $personel_id ) echo "class = '$satir_renk'";?>>
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $personel[ 'adi' ]; ?></td>
									<td><?php echo $personel_ozluk_dosya_turleri_sayisi - $personel[ "dosyaSayisi" ]; ?></td>
									<td align = "center">
									<a modul = 'firmalar' yetki_islem="evraklar" class = "btn btn-sm btn-<?php echo $evraklarBtnRenk; ?> btn-xs" href = "?modul=personelOzlukDosyalari&islem=guncelle&personel_id=<?php echo $personel[ 'id' ]; ?>" >
										Evraklar
									</a>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class = "col-md-8">
				<div class="card card-primary">
					<div class="card-header">
						<h3 class="card-title"><?php echo $tek_personel[ 2 ][ 0 ][ 'adi' ] . " " . $tek_personel[ 2 ][ 0 ][ 'soyadi' ]; ?> - Özlük Dosyası Ekle</h3>
					</div>
					<div class="card-body">
						<?php foreach( $personel_ozluk_dosya_turleri[ 2 ] AS $dosya_turu ) { ?>
							<form action = "_modul/personelOzlukDosyalari/personelOzlukDosyalariSEG.php" method = "POST" enctype="multipart/form-data">
								<div class="form-group">
									<label for="exampleInputFile"><?php echo $dosya_turu[ 'adi' ]; ?></label>
									<div class="input-group">
										<?php 
											if(in_array($dosya_turu["id"], $personel_ozluk_dosyalari_idleri)){
												$buttonRenk  = 'success';
												$buttonYazi = "Güncelle";
											}else{
												$buttonRenk  = 'danger';
												$buttonYazi = "Kaydet";
											}
										?>	
										<div class="custom-file">
											<input type="hidden" value="<?php echo $dosya_turu[ 'id' ]; ?>" name="dosya_turu_id">
											<input type="hidden" value="<?php echo $personel_id?>" name="personel_id">
											<input type="file" class="custom-file-input " id="<?php echo $dosya_turu[ 'id' ]; ?>" name = "OzlukDosya" <?php echo $dosya_turu[ 'filtre' ]; ?>>
											<label class="custom-file-label" for="exampleInputFile">Dosya Seç</label>
										</div>
										<div class="input-group-append">
											<button class="btn  btn-<?php echo  $buttonRenk; ?>" type = "submit"><?php echo $buttonYazi; ?></button>
										</div>
									</div>
								</div>
							</form>
						<?php } ?>
					</div>
				</div>
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title"><?php echo $tek_personel[ 2 ][ 0 ][ 'adi' ] . " " . $tek_personel[ 2 ][ 0 ][ 'soyadi' ]; ?> - Özlük Dosyaları</h3>
					</div>
					<div class="card-body">
						<div class="card card-default">
							<div class="card-body">
								<div id="actions" class="row">
									<table class="table table-striped table-valign-middle">
										<tbody>
										<?php
												if( count( $personel_ozluk_dosyalari ) > 0 ) {
													foreach( $personel_ozluk_dosyalari AS $dosya ) { ?>
														<tr>
															<td>
																<?php echo $dosya[ 'adi' ]; ?>
															</td>
															<td align = "right" width = "5%">
																<a href = "personel_ozluk_dosyalari/<?php echo $dosya[ 'dosya' ]; ?>"
																	data-toggle="tooltip"
																	data-placement="left"
																	title="Dosyayı İndir" target="_blank">
																	<i class = "fa fa-download"></i>
																	
																</a>
															</td>
															<td align = "right" width = "5%">
																<a href = "" 
																	modul = 'personelOzlukDosyalari' yetki_islem="sil"
																	data-href="_modul/personelOzlukDosyalari/personelOzlukDosyalariSEG.php?islem=sil&personel_id=<?php echo $personel_id; ?>&dosya_id=<?php echo $dosya[ 'id' ]; ?>"
																	data-target="#kayit_sil"
																	data-toggle="modal"
																	data-toggle="tooltip" 
																	data-placement="left" 
																	title="Dosyayı Sil">
																	<i class = "fa fa-trash color:red"></i>
																</a>
															</td>
														</tr>
												<?php
													}
												} else { ?>
												<h6>Listelenecek kayıt bulunamadı!</h6>
											<?php } ?>
										</tbody>	
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
	$('#tbl_personelOzlukDosyalari').DataTable({
	  "paging": true,
	  "lengthChange": true,
	  "searching": true,
	  "ordering": true,
	  "info": true,
	  "autoWidth": false,
	  "responsive": true,
	  'pageLength'	: 25,
	  'stateSave'	: true,
	  'language'		: {
		'url': '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Turkish.json'
	}
	});

</script>