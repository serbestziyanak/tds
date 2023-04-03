<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj                 = $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu            = $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'sayacCihaz_id' ] = $_SESSION[ 'sonuclar' ][ 'sayacCihaz_id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$SQL_oku = <<< SQL
SELECT
	*
FROM 
	sayac_sayac_cihazlari
WHERE
	firma_id = ? AND
	aktif 	 = 1
SQL;

$SQL_tekCihaz_oku = <<< SQL
SELECT
	*
FROM 
	sayac_sayac_cihazlari
WHERE
	id 			= ? AND 
	firma_id 	= ?
SQL;


$sayacCihaz_id	= array_key_exists( 'sayacCihaz_id', $_REQUEST ) ? $_REQUEST[ 'sayacCihaz_id' ] : 0;
$islem			= array_key_exists( 'islem', $_REQUEST ) 		 ? $_REQUEST[ 'islem' ] 		: 'ekle';

$sayacCihazlari	= $vt->select( $SQL_oku, array( $_SESSION[ "firma_id" ] ) );
$tekCihaz		= $vt->select( $SQL_tekCihaz_oku, array( $sayacCihaz_id, $_SESSION[ "firma_id" ] ) );

$cihazBilgisi = array(
	 'id'			=> $sayacCihaz_id > 0 ? $sayacCihaz_id : 0
	,'sayac_mac'	=> $sayacCihaz_id > 0 ? $tekCihaz[ 2 ][ 0 ][ 'sayac_mac' ] : ''
	,'ip_adresi'	=> $sayacCihaz_id > 0 ? $tekCihaz[ 2 ][ 0 ][ 'ip_adresi' ] : ''
	,'sayac_no'		=> $sayacCihaz_id > 0 ? $tekCihaz[ 2 ][ 0 ][ 'sayac_no' ] : ''

);

$satir_renk				= $sayacCihaz_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $sayacCihaz_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $sayacCihaz_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';

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
				<h3 class="card-title">Sayaç Cihazları</h3>
			</div>
			<div class="card-body">
				<table id="example2" class="table table-sm table-bordered table-hover">
					<thead>
						<tr>
							<th style="width: 15px">#</th>
							<th>Sayac Mac Adresi</th>
							<th>Sayaç İp Adresi</th>
							<th>Sayaç Numarasi</th>
							<th data-priority="1" style="width: 20px">Düzenle</th>
							<th data-priority="1" style="width: 20px">Sil</th>
						</tr>
					</thead>
					<tbody>
						<?php $sayi = ($sayfa-1)*$limit+1;  foreach( $sayacCihazlari[ 2 ] AS $is ) { ?>
						<tr <?php if( $is[ 'id' ] == $sayacCihaz_id ) echo "class = '$satir_renk'"; ?>>
							<td><?php echo $sayi++; ?></td>
							<td><?php echo $is[ 'sayac_mac' ]; ?></td>
							<td><?php echo $is[ 'ip_adresi' ]; ?></td>
							<td><?php echo $is[ 'sayac_no' ]; ?></td>
							<td align = "center">
								<a modul = 'sayacCihazlari' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=sayacCihazlari&islem=guncelle&sayacCihaz_id=<?php echo $is[ 'id' ]; ?>" >
									Düzenle
								</a>
							</td>
							<td align = "center">
								<button modul = 'sayacCihazlari' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/uretim_sistemi/sayacCihazlariSEG.php?islem=sil&sayacCihaz_id=<?php echo $is[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay" >Sil</button>
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
			<h3 class="card-title">Sayaç Cıhazı Ekle / Güncelle</h3>
		</div>
		<form id = "kayit_formu" action = "_modul/uretim_sistemi/sayacCihazlariSEG.php" method = "POST">
			<div class="card-body">
			<input type = "hidden" name = "sayacCihaz_id" value = "<?php echo $cihazBilgisi[ 'id' ]; ?>">
			<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
			<div class="form-group">
				<label  class="control-label">Sayaç Mac Adresi</label>
				<input autocomplete="off" type="text" class="form-control" name ="sayac_mac" value = "<?php echo $cihazBilgisi[ 'sayac_mac' ]; ?>" required placeholder="AS:A2:E3:B5:33:44">
			</div>
			
			<div class="form-group">
				<label  class="control-label">Sayaç İp Adresi</label>
				<input autocomplete="off" type="text" class="form-control" name ="ip_adresi" value = "<?php echo $cihazBilgisi[ 'ip_adresi' ]; ?>" required placeholder="192.168.1.1">
			</div>
			<div class="form-group">
				<label  class="control-label">Sayaç Numarası</label>
				<input autocomplete="off" type="text" class="form-control" name ="sayac_no" value = "<?php echo $cihazBilgisi[ 'sayac_no' ]; ?>" required placeholder="">
			</div>
			</div>
			<div class="card-footer">
			<button modul= 'sayacCihazlari' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi?></button>
				<button onclick="window.location.href = '?modul=sayacCihazlari&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
			</div>
		</form>
		</div>
	</div>
</div>



