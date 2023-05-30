<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj                 = $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu            = $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'makinaTur_id' ] = $_SESSION[ 'sonuclar' ][ 'makinaTur_id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$SQL_oku = <<< SQL
SELECT
	*
FROM 
	sayac_makina_turleri
WHERE
	firma_id = ? AND
	aktif 	 = 1
SQL;

$SQL_tek_isParca_oku = <<< SQL
SELECT
	*
FROM 
	sayac_makina_turleri
WHERE
	id = ?
SQL;


$makinaTur_id	= array_key_exists( 'makinaTur_id', $_REQUEST ) ? $_REQUEST[ 'makinaTur_id' ] 	: 0;
$islem			= array_key_exists( 'islem', $_REQUEST ) 		? $_REQUEST[ 'islem' ] 			: 'ekle';

$makinaTurleri	= $vt->select( $SQL_oku, array( $_SESSION[ "firma_id" ] ) );
$tek_isParca	= $vt->select( $SQL_tek_isParca_oku, array( $makinaTur_id ) );

$isParcasiBilgi = array(
	 'id'		=> $makinaTur_id > 0 ? $makinaTur_id : 0
	,'adi'		=> $makinaTur_id > 0 ? $tek_isParca[ 2 ][ 0 ][ 'adi' ] : ''

);

$satir_renk				= $makinaTur_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $makinaTur_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $makinaTur_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';

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

<div class="row">
	<div class="col-md-6">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">Makina Türleri</h3>
			</div>
			<div class="card-body">
				<table id="example2" class="table table-sm table-bordered table-hover">
					<thead>
						<tr>
							<th style="width: 15px">#</th>
							<th>Adı</th>
							<th data-priority="1" style="width: 20px">Düzenle</th>
							<th data-priority="1" style="width: 20px">Sil</th>
						</tr>
					</thead>
					<tbody>
						<?php $sayi = ($sayfa-1)*$limit+1;  foreach( $makinaTurleri[ 2 ] AS $is ) { ?>
						<tr <?php if( $is[ 'id' ] == $makinaTur_id ) echo "class = '$satir_renk'"; ?>>
							<td><?php echo $sayi++; ?></td>
							<td><?php echo $is[ 'adi' ]; ?></td>
							<td align = "center">
								<a modul = 'makinaTurleri' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=makinaTurleri&islem=guncelle&makinaTur_id=<?php echo $is[ 'id' ]; ?>" >
									Düzenle
								</a>
							</td>
							<td align = "center">
								<button modul = 'makinaTurleri' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/uretim_sistemi/makinaTurleriSEG.php?islem=sil&makinaTur_id=<?php echo $is[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay" >Sil</button>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<div class="card-footer clearfix">
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="card card-secondary">
		<div class="card-header">
			<h3 class="card-title">Makina Türü Ekle / Güncelle</h3>
		</div>
		<form id = "kayit_formu" action = "_modul/uretim_sistemi/makinaTurleriSEG.php" method = "POST">
			<div class="card-body">
			<input type = "hidden" name = "makinaTur_id" value = "<?php echo $isParcasiBilgi[ 'id' ]; ?>">
			<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
			<div class="form-group">
				<label  class="control-label">Adı</label>
				<input type="text" class="form-control" name ="adi" value = "<?php echo $isParcasiBilgi[ 'adi' ]; ?>" required placeholder="">
			</div>

			</div>
			<div class="card-footer">
			<button modul= 'makinaTurleri' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi?></button>
				<button onclick="window.location.href = '?modul=makinaTurleri&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
			</div>
		</form>
		</div>
	</div>
</div>



