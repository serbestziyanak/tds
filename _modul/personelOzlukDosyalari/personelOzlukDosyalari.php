<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$islem = array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

$SQL_tum_personel_oku = <<< SQL
SELECT
	 p.*
	,s.adi AS firma_adi
FROM
	tb_personel AS p
LEFT JOIN
	tb_firmalar AS s ON p.firma_id = s.id
WHERE
	p.aktif = 1
SQL;

$SQL_tek_personel_oku = <<< SQL
SELECT
	id,adi,soyadi
FROM
	tb_personel
WHERE
	id = ?
SQL;

$SQL_personel_ozluk_dosyalari = <<< SQL
SELECT
	 od.id
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
$personel_ozluk_dosyalari		= $vt->select( $SQL_personel_ozluk_dosyalari, array( $personel_id ) );
$personel_ozluk_dosya_turleri	= $vt->select( $SQL_personel_ozluk_dosya_turleri, array() );

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
			<div class = "col-md-5">
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title">Personel Seçin</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<select  class="form-control select2" name = "personel_id" id = "personel_id" data-placeholder = "Personel ara...">
								<?php foreach( $personeller[ 2 ] AS $personel ) { ?>
									<option value = "<?php echo $personel[ 'id' ]; ?>" <?php if( $personel_id == $personel[ 'id' ] ) echo 'selected'?>><?php echo $personel[ 'adi' ] . " " . $personel[ 'soyadi' ]; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class = "col-md-7">

				<div class="card card-primary">
					<div class="card-header">
						<h3 class="card-title"><?php echo $tek_personel[ 2 ][ 0 ][ 'adi' ] . " " . $tek_personel[ 2 ][ 0 ][ 'soyadi' ]; ?> - Özlük Dosyası Ekle</h3>
					</div>
					<form action = "_modul/personelOzlukDosyalari/personelOzlukDosyalariSEG.php" method = "POST" enctype="multipart/form-data">
						<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
						<input type = "hidden" name = "islem" value = "ekle">
						<div class="card-body">
							<div class="form-group">
								<label for="exampleInputEmail1">Özlük Dosya Türü</label>
								<select  class="form-control select2" name = "dosya_turu_id" id = "dosya_turu_id" data-placeholder = "Personel ara...">
									<?php foreach( $personel_ozluk_dosya_turleri[ 2 ] AS $dosya_turu ) { ?>
										<option value = "<?php echo $dosya_turu[ 'id' ]; ?>"><?php echo $dosya_turu[ 'adi' ]; ?></option>
									<?php } ?>
								</select>
							</div>

							<div class="form-group">
								<label for="customFile">Özlük Dosyası</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" id="ozluk_dosyasi" name = "ozluk_dosyasi" accept="application/pdf, image/jpg, image/JPG, image/jpeg, image/png, image/PNG," required>
									<label class="custom-file-label" for="ozluk_dosyasi">Dosya seçiniz...</label>
								</div>
							</div>
						</div>
						<div class="card-footer">
							<button type="submit" class="btn btn-success btn-sm" style = "float:right;">Kaydet</button>
						</div>
					</form>
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
												foreach( $personel_ozluk_dosyalari[ 2 ] AS $dosya ) { ?>
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
															title="Dosyayı Sil"><i class = "fa fa-trash color:red"></i>
														</a>
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
			</div>
		</div>
	</div>
</section>

<script>
	$( '#personel_id' ).on( 'select2:select', function ( e ) {
		window.location = window.location.origin + '/index.php?modul=personelOzlukDosyalari&personel_id=' + e.params.data.id;
	} );
</script>
<script>
	$( '#ozluk_dosyasi' ).on('change',function(){
		//get the file name
		var fileName = $(this).val();
		//replace the "Choose a file" label
		$(this).next('.custom-file-label').html(fileName);
	})
</script>
