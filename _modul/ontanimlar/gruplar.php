<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}




$SQL_oku = <<< SQL
SELECT
	*
FROM
	tb_gruplar
WHERE
	firma_id 	= ? AND
	aktif 		= 1
SQL;

$SQL_tek_grup_oku = <<< SQL
SELECT
	*
FROM
	tb_gruplar
WHERE
	id = ?
SQL;


$grup_id	= array_key_exists( 'grup_id', $_REQUEST ) ? $_REQUEST[ 'grup_id' ] : 0;
$islem		= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

$gruplar	= $vt->select( $SQL_oku, array( $_SESSION[ "firma_id" ] ) );
$tek_grup	= $vt->select( $SQL_tek_grup_oku, array( $grup_id ) );

$grup_bilgileri = array(
	 'id'							=> $grup_id > 0 ? $grup_id : 0
	,'adi'							=> $grup_id > 0 ? $tek_grup[ 2 ][ 0 ][ 'adi' ] : ''
	,'aylik_calisma_suresi'			=> $grup_id > 0 ? $tek_grup[ 2 ][ 0 ][ 'aylik_calisma_suresi' ] : ''
	,'haftalik_calisma_suresi'		=> $grup_id > 0 ? $tek_grup[ 2 ][ 0 ][ 'haftalik_calisma_suresi' ] : ''
);



$satir_renk				= $grup_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $grup_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $grup_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';

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
				<h3 class="card-title">Gruplar</h3>
			</div>
			<div class="card-body">
				<table id="example2" class="table table-sm table-bordered table-hover">
					<thead>
						<tr>
						<th style="width: 15px">#</th>
						<th>Adı</th>
						<th>A.Ç.S</th>
						<th>H.Ç.S</th>
						<th data-priority="1" style="width: 20px">Düzenle</th>
						<th data-priority="1" style="width: 20px">Sil</th>
						</tr>
					</thead>
				<tbody>
				<?php $sayi = ($sayfa-1)*$limit+1;  foreach( $gruplar[ 2 ] AS $grup ) { ?>
				<tr <?php if( $grup[ 'id' ] == $grup_id ) echo "class = '$satir_renk'"; ?>>
					<td><?php echo $sayi++; ?></td>
					<td><?php echo $grup[ 'adi' ]; ?></td>
					<td><?php echo $grup[ 'aylik_calisma_suresi' ]; ?></td>
					<td><?php echo $grup[ 'haftalik_calisma_suresi' ]; ?></td>
					<td align = "center">
						<a modul = 'gruplar' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=gruplar&islem=guncelle&grup_id=<?php echo $grup[ 'id' ]; ?>" >
							Düzenle
						</a>
					</td>
					<td align = "center">
						<button modul = 'gruplar' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/ontanimlar/gruplarSEG.php?islem=sil&grup_id=<?php echo $grup[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay" >Sil</button>
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
			<h3 class="card-title">Grup Ekle / Güncelle</h3>
		</div>
		<form id = "kayit_formu" action = "_modul/ontanimlar/gruplarSEG.php" method = "POST">
			<div class="card-body">
			<input type = "hidden" name = "grup_id" value = "<?php echo $grup_id; ?>">
			<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
			<div class="form-group">
				<label  class="control-label">Grup Adı</label>
				<input type="text" class="form-control" name ="grup_adi" value = "<?php echo $grup_bilgileri[ 'adi' ]; ?>" required placeholder="">
			</div>
			<div class="form-group">
				<label class="control-label">Aylık Çalışma Süresi</label>
				<input required type="text" class="form-control" name ="aylik_calisma_suresi" value = "<?php echo $grup_bilgileri[ "aylik_calisma_suresi" ]; ?>"  autocomplete="off">
			</div>
			<div class="form-group">
				<label class="control-label">Haftalık Çalışma Süresi</label>
				<input required type="text" class="form-control" name ="haftalik_calisma_suresi" value = "<?php echo $grup_bilgileri[ "haftalik_calisma_suresi" ]; ?>"  autocomplete="off">
			</div>
			</div>
			<div class="card-footer">
			<button modul= 'gruplar' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
				<button onclick="window.location.href = '?modul=gruplar&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
			</div>
		</form>
		</div>
	</div>
</div>


