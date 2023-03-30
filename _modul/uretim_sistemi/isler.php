<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj			= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu		= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$SQL_isler = <<< SQL
SELECT
	 i.id
	,i.adi AS is_adi
	,i.is_alma_tarihi
	,i.baslama_tarihi
	,i.bitis_tarihi
	,i.siparis_adet
	,i.aktif
	,i.aciklama
	,sg.gunluk_hedef
	,sg.tamamlanan
FROM
	sayac_isler AS i
LEFT JOIN
	sayac_is_gunlukleri AS sg ON sg.is_id = i.id
ORDER BY
	bitis_tarihi DESC
SQL;

$SQL_is = <<< SQL
SELECT
	 i.id
	,i.adi AS is_adi
	,i.is_alma_tarihi
	,i.baslama_tarihi
	,i.bitis_tarihi
	,i.siparis_adet
	,i.aktif
	,i.aciklama
	,sg.gunluk_hedef
	,sg.tamamlanan
	,sg.tarih
FROM
	sayac_isler AS i
LEFT JOIN
	sayac_is_gunlukleri AS sg ON sg.is_id = i.id
WHERE
	i.id = ?
SQL;

$is_id		= array_key_exists( 'is_id', $_REQUEST ) ? $_REQUEST[ 'is_id' ] : 0;
$tek_is		= $vt->selectSingle( $SQL_is, array( $is_id ) );
$tum_isler	= $vt->select( $SQL_isler );
$islem		= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'guncelle' )
	$isBilgileri = array(
		 'id'				=> $tek_is[ 2 ][ 'id' ]
		,'is_adi'			=> $tek_is[ 2 ][ 'is_adi' ]
		,'is_alma_tarihi'	=> $tek_is[ 2 ][ 'is_alma_tarihi' ]
		,'baslama_tarihi'	=> $tek_is[ 2 ][ 'baslama_tarihi' ]
		,'bitis_tarihi'		=> $tek_is[ 2 ][ 'bitis_tarihi' ]
		,'siparis_adet'		=> $tek_is[ 2 ][ 'siparis_adet' ]
		,'aktif'			=> $tek_is[ 2 ][ 'aktif' ]
		,'aciklama'			=> $tek_is[ 2 ][ 'aciklama' ]
	);

$satir_renk				= $is_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $is_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $is_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';
?>

<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="kayit_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">Lütfen Dikkat!</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				Bu Kaydı <b>Silmek</b> istediğinize emin misiniz?<br>Bu işe ait tüm iş günlükleri de silinecektir.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
				<a class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>
	$( '#kayit_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>

		<div class="row">
		<div class="col-md-8">
			<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">Sistem Kullanıcıları</h3>
			</div>
			<div class="card-body">
				<table id="example2" class="table table-sm table-bordered table-hover">
					<thead>
						<tr class="">
							<th style="width: 15px">#</th>
							<th>Adı</th>
							<th>İş Alma Tarihi</th>
							<th>Başlama Tarihi</th>
							<th>Bitiş Tarihi</th>
							<th align = "center">Aktif</th>
							<th data-priority="1" style="width: 20px">Düzenle</th>
							<th data-priority="1" style="width: 20px">Sil</th>
						</tr>
					</thead>
					<tbody>
						<?php $sayi = 1; foreach( $tum_isler[ 2 ] AS $is ) { ?>
						<tr <?php if( $is[ 'id' ] == $is_id ) echo "class = '$satir_renk'"; ?>>
							<td><?php echo $sayi++; ?></td>
							<td><?php echo $is[ 'is_adi' ]; ?></td>
							<td><?php echo $fn->tarihVer( $is[ 'is_alma_tarihi' ] ); ?></td>
							<td><?php echo $fn->tarihVer( $is[ 'baslama_tarihi' ] ); ?></td>
							<td><?php echo $is[ 'bitis_tarihi' ] == NULL ? 'Devam ediyor...' : $fn->tarihVer( $is[ 'bitis_tarihi' ] ); ?></td>
							<td align = "center"><?php
								$label = $is[ 'aktif' ] * 1 > 0
									? '<h5><span class="float-right badge bg-success">Evet</span></h5>'
									: '<h5><span class="float-right badge bg-secondary">Hayır</span></h5>';
								
								echo $label;
							?></td>
							<td align = "center">
								<a modul= 'uretim_sistemi' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs " href = "?modul=isler&islem=guncelle&is_id=<?php echo $is[ 'id' ]; ?>" >
									Düzenle
								</a>
							</td>
							<td align = "center">
								<button modul= 'uretim_sistemi' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/uretim_sistemi/islerSEG.php?islem=sil&is_id=<?php echo $is[ 'id' ]; ?>" data-toggle="modal" data-target="#kayit_sil_onay" >Sil</button>
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
		<div class="col-md-4">
			<div class="card card-secondary">
			<div class="card-header">
				<h3 class="card-title">İş Ekle / Güncelle</h3>
			</div>

				<form class="form-horizontal" id = "kayit_formu" action = "_modul/uretim_sistemi/islerSEG.php" method = "POST" enctype="multipart/form-data">
					<div class="card-body">
						<input type = "hidden" name = "is_id" value = "<?php echo $is_id; ?>">
						<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
						<div class="form-group">
							<label  class="control-label">Adı</label>
							<input required type="text" class="form-control" name ="is_adi" value = "<?php echo $isBilgileri[ 'is_adi' ]; ?>">
						</div>

						<div class="form-group">
						<label class="control-label">İşi Alma Tarihi</label>
							<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
								<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
									<div class="input-group-text"><i class="fa fa-calendar"></i></div>
								</div>
								<input required type="text" name="is_alma_tarihi" value="<?php if( $isBilgileri[ 'is_alma_tarihi' ] !='' ){echo date('d.m.Y',strtotime($isBilgileri[ 'is_alma_tarihi' ] ));}//else{ echo date('d.m.Y'); } ?>" class="form-control datetimepicker-input" data-target="#datetimepicker1"/>
							</div>
						</div>

						<div class="form-group">
						<label class="control-label">Başlama Tarihi</label>
							<div class="input-group date" id="datetimepicker2" data-target-input="nearest">
								<div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">
									<div class="input-group-text"><i class="fa fa-calendar"></i></div>
								</div>
								<input required type="text" name="baslama_tarihi" value="<?php if( $isBilgileri[ 'baslama_tarihi' ] !='' ){echo date('d.m.Y',strtotime($isBilgileri[ 'baslama_tarihi' ] ));}//else{ echo date('d.m.Y'); } ?>" class="form-control datetimepicker-input" data-target="#datetimepicker2"/>
							</div>
						</div>

						<div class="form-group">
						<label class="control-label">Bitiş Tarihi</label>
							<div class="input-group date" id="datetimepicker3" data-target-input="nearest">
								<div class="input-group-append" data-target="#datetimepicker3" data-toggle="datetimepicker">
									<div class="input-group-text"><i class="fa fa-calendar"></i></div>
								</div>
								<input type="text" name="bitis_tarihi" id = "bitis_tarihi" value="<?php if( $isBilgileri[ 'bitis_tarihi' ] !='' ){echo date('d.m.Y',strtotime($isBilgileri[ 'bitis_tarihi' ] ));}//else{ echo date('d.m.Y'); } ?>" class="form-control datetimepicker-input" data-target="#datetimepicker3"/>
							</div>
						</div>

						<div class="form-group">
							<label  class="control-label">Sipariş Adedi</label>
							<input required type="number" min="1" class="form-control" name ="siparis_adet" value = "<?php echo $isBilgileri[ 'siparis_adet' ]; ?>">
						</div>
						<div class="form-group">
							<label  class="control-label">Açıklama</label>
							<textarea class="form-control" name ="aciklama" rows = "3"><?php echo $isBilgileri[ 'aciklama' ]; ?></textarea>
						</div>
						<div class="form-group">
							<div class='material-switch pull-right' style ="padding-top:10px">
								<label class="control-label"></label>
								<input id='is_aktif' name='is_aktif' data-on-text = "Aktif" data-off-text = "Pasif" type="checkbox" <?php if ( $isBilgileri[ 'aktif' ] * 1 > 0 ) echo 'checked'; ?> data-bootstrap-switch data-off-color="danger" data-on-color="success"> (Pasif olduğunda iş bitmiş anlamına gelir.)
							</div>
						</div>
					</div>
					<div class="card-footer">
							<button modul= 'isler' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
							<a modul= 'isler' yetki_islem="ekle" type="reset" class="btn btn-primary btn-sm pull-right" href = "?modul=isler&islem=ekle" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</a>
					</div>
				</form>
			</div>
		</div>
		</div>

<script>
$('input[name="is_aktif"]').on('switchChange.bootstrapSwitch', function(event, state) {
	if (state == true ){
		$( "#bitis_tarihi" ).prop( "required", false );
	}else{
		$( "#bitis_tarihi" ).prop( "required", true );
	}
});
</script>

<script type="text/javascript">
	var simdi = new Date(); 
	//var simdi="11/25/2015 15:58";
	$(function () {
		$('#datetimepicker1,#datetimepicker2,#datetimepicker3').datetimepicker({
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
</script>
