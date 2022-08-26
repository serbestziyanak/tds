<?php
$fn = new Fonksiyonlar();

$islem          					= array_key_exists( 'islem', $_REQUEST )  ? $_REQUEST[ 'islem' ] 	: 'ekle';

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj                 			= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu            			= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$program_id		= array_key_exists( 'program_id'	,$_REQUEST ) ? $_REQUEST[ 'program_id' ]	: 0;
$bolum_id		= array_key_exists( 'bolum_id'		,$_REQUEST ) ? $_REQUEST[ 'bolum_id' ]		: 0;

//bolume Ait bölüleri getirme
$SQL_bolum_programlari = <<< SQL
SELECT
	*
FROM
	tb_programlar
WHERE 
	bolum_id = ? AND
	aktif 	 = 1
SQL;

/*Tek Bir Bölümü Getirme*/
$SQL_program_oku = <<< SQL
SELECT
	*
FROM
	tb_programlar
WHERE
	id 		= ? AND
	aktif 	= 1
SQL;

/*Tüm bolumyi Getirme*/
$SQL_tum_bolumler = <<< SQL
SELECT
	*
FROM
	tb_bolumler
WHERE
	universite_id = ? AND
	aktif 		  = 1
SQL;

$bolumler			= $vt->select( $SQL_tum_bolumler, array( $_SESSION[ 'universite_id' ] ) )[ 2 ];
@$program			= $vt->select( $SQL_program_oku, array( $program_id ) )[ 2 ][ 0 ];

?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="sil_onay">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Lütfen Dikkat!</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p><b>Bu kategoriyi sildiğinizde kategori altındaki alt kategoriler de silinecektir.</b></p>
				<p>Bu kaydı <b>Silmek</b> istediğinize emin misiniz?</p>
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn btn-success" data-dismiss="modal">İptal</button>
				<a type="button" class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<script>
	$( '#sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>

<script>  
	$(document).ready(function() {
		$('#limit-belirle').change(function() {

			$(this).closest('form').submit();

		});
	});
</script>
<div class="row">
	<!-- left column -->
	<div class="col-md-4">
		<!-- general form elements -->
		<div class="card card-secondary">
			<div class="card-header">
				<h3 class="card-title">Program Ekle / Güncelle</h3>
			</div>
			<!-- /.card-header -->
			<!-- form start -->
			<form id = "kayit_formu" action = "_modul/programlar/programlarSEG.php" method = "POST">
				<div class="card-body">
					<input type = "hidden" name = "program_id" value = "<?php echo $program[ 'id' ]; ?>">
					<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
					<div class="form-group">
						<label  class="control-label">Bölüm</label>
						<select class="form-control select2" name = "bolum_id" required>
							<option>Seçiniz...</option>
							<?php 
								foreach( $bolumler AS $bolum ){
									echo '<option value="'.$bolum[ "id" ].'" '.($program[ "bolum_id" ] == $bolum[ "id" ] ? "selected" : null) .'>'.$bolum[ "adi" ].'</option>';
								}

							?>
						</select>
					</div>
					<div class="form-group">
						<label  class="control-label">Adı</label>
						<input type="text" class="form-control" name ="adi" value = "<?php echo $program[ 'adi' ]; ?>" required placeholder="Kategori adı giriniz">
					</div>
				</div>
				<!-- /.card-body -->
				<div class="card-footer">
					<button modul= 'programlar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
					<button onclick="window.location.href = '?modul=programlar&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
				</div>
			</form>
		</div>
		<!-- /.card -->
	</div>
	<!--/.col (left) -->
	<div class="col-md-8">
		<div class="card card-secondary">
			<div class="card-header">
				<h3 class="card-title">Programlar</h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body p-0">

				<table class="table programTablo table-hover ">
					<tbody>
						<?php 
							foreach( $bolumler AS $bolum ){
								$bolumprogramlari = $vt->select( $SQL_bolum_programlari, array( $bolum[ "id" ] ) )[ 2 ];

								if ( count( $bolumprogramlari ) > 0 ) {
									echo '
										<tr class="table-secondary" data-widget="expandable-table" aria-expanded="'.($bolum_id == $bolum[ "id" ] ? "true": "false").'">
											<td>
												<i class="expandable-table-caret fas fa-caret-right fa-fw"></i>
												'.$bolum[ "adi" ].'
											</td>
										</tr>';
									echo '
										<tr class="expandable-body">
											<td>
												<div class="p-0">
													<table class="table programTablo table-hover">
														<tbody>';
									/*Fakülteye Ait Bölümleri listeledi*/
									foreach ( $bolumprogramlari AS $program ){
										echo '
											<tr class="table-warning"
												data-widget="" 
												aria-expanded="false"
												onmouseover="document.getElementById(\'tr-'.$program[ "id" ].'\').style.display = \'block\';" 
												onmouseout="document.getElementById(\'tr-'.$program[ "id" ].'\').style.display = \'none\';" >
												<td>
													&nbsp;&nbsp;&nbsp;'.$program[ "adi" ].'
													<div class="float-right" id="tr-'.$program[ "id" ].'" style="display: none;">
														<a href = "?modul=programlar&islem=guncelle&program_id='.$program[ "id" ].'&bolum_id='.$bolum[ "id" ].'"  modul = "programlar" yetki_islem="duzenle" class="btn btn-xs btn-warning">Düzenle</a>
														<a modul= "programlar" yetki_islem="sil" class="btn btn-xs btn-danger" data-href="_modul/programlar/programlarSEG.php?islem=sil&program_id='.$program[ "id" ].'&bolum_id='.$bolum_id.'" data-toggle="modal" data-target="#sil_onay">Sil</a>
													</div>
												</td>
											</tr>';
									}
									/*Bölümler Tablosunu Kapatıyoruz*/						 
									echo '						
														</tbody>
													</table>
												</div>
											</td>
										</tr>';
								}else{
									echo '
										<tr class="table-secondary">
											<td class="border-0">'.$bolum[ "adi" ].'</td>
										</tr>';
								}
							}
						?>
					</tbody>
				</table>
			</div>
			<!-- /.card -->
		</div>
		<!-- right column -->
	</div>

