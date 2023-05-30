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

$aylar = array(
	"Jan" => "Ocak",
	"Feb" => "Şubat",
	"Mar" => "Mart",
	"Apr" => "Nisan",
	"May" => "Mayıs",
	"Jun" => "Haziran",
	"Jul" => "Temmuz",
	"Aug" => "Ağustos",
	"Sep" => "Eylül",
	"Oct" => "Ekim",
	"Nov" => "Kasım",
	"Dec" => "Aralık"
);

$SQL_aktif_is = <<<SQL
	SELECT id, adi FROM sayac_isler WHERE aktif = 1
SQL;


$SQL_is_gunlukleri = <<< SQL
SELECT
	 i.adi AS is_adi
	,i.id AS is_id
	,ig.id AS gunluk_id
	,ig.tamamlanan
	,ig.tarih
	,ig.gunluk_hedef
	,ig.gecerlilik
FROM
	sayac_is_gunlukleri AS ig
JOIN
	sayac_isler AS i ON ig.is_id = i.id
WHERE
	i.id = ?
ORDER BY
	ig.tarih DESC
SQL;

$SQL_is_gunlugu = <<< SQL
	SELECT * FROM sayac_is_gunlukleri WHERE id = ?
SQL;

$islem		= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';
$aktif_is	= $vt->select( $SQL_aktif_is );




$is_id		= count( $aktif_is[ 2 ] ) > 0 ? $aktif_is[ 2 ][ 0 ][ "id" ] : 0;

$gunluk_id	= array_key_exists( 'gunluk_id', $_REQUEST ) ? $_REQUEST[ 'gunluk_id' ] : 0;

$tek_gunluk		= $vt->selectSingle( $SQL_is_gunlugu, array( $gunluk_id ) );
$is_gunlukleri	= $vt->select( $SQL_is_gunlukleri, array( $is_id ) );
$aktif_is_adi	=  $aktif_is[ 2 ][ 0 ][ "adi" ];

if( $islem == 'guncelle' ) {
	$gunlukBilgileri = array(
		 'gunluk_id'	=> $tek_gunluk[ 2 ][ 'id' ]
		,'gunluk_hedef'	=> $tek_gunluk[ 2 ][ 'gunluk_hedef' ]
		,'tarih'		=> $tek_gunluk[ 2 ][ 'tarih' ]
		/*
			Aşağıdaki "geçerlilik" parametresi girilen hedefin sadece girilen gün için mi yoksa tüm iş bitinceye kadar mı geçerli olacağına karar verir
			1 : Tüm iş bitinceye kadar hedef geçerli olacak
			2 : Sadec girilen tarihin günü için hedef geçerli 
		*/
		,'gecerlilik'	=> $tek_gunluk[ 2 ][ 'gecerlilik' ]
	);
}

$satir_renk				= $gunluk_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $gunluk_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $gunluk_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';

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
				Bu Kaydı <b>Silmek</b> istediğinize emin misiniz?
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
<?php if( $is_id != 0 ) { ?>
	<div class="col-md-8">
		<div class="card card-success">
		<div class="card-header">
			<h3 class="card-title">
				<?php echo $aktif_is_adi; ?> - İş Günlükleri
			</h3>
		</div>
		<div class="card-body">
			<table id="example2" class="table table-sm table-bordered table-hover">
				<thead>
					<tr class="">
						<th style="width: 15px">#</th>
						<th>İş Adı</th>
						<th>Günlük Hedef</th>
						<th>Tamamlanan</th>
						<th>Tarih</th>
						<th>Geçerlilik</th>
						<th data-priority="1" style="width: 20px">Düzenle</th>
						<th data-priority="1" style="width: 20px">Sil</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$sayi = 1;
						foreach( $is_gunlukleri[ 2 ] AS $gunluk ) {
						$zaman		= $fn->tarihVer( $gunluk[ 'tarih' ] );
						$zaman		= strtotime( $zaman );
						$ay_ismi 	= date("M", $zaman );
						$ayin_gunu	= date( "d", $zaman ); 
						
						$gecerlilik = $gunluk[ "gecerlilik" ] == 1 ? "İş bitinceye kadar" : "Bir günlük (" . $ayin_gunu . " " . $aylar[ $ay_ismi ] . " )";
					?>
							<tr <?php if( $gunluk[ 'gunluk_id' ] == $gunluk_id ) echo "class = '$satir_renk'"; ?>>
								<td><?php echo $sayi++; ?></td>
								<td><?php echo $gunluk[ 'is_adi' ]; ?></td>
								<td><?php echo $gunluk[ 'gunluk_hedef' ]; ?></td>
								<td><?php echo $gunluk[ 'tamamlanan' ]; ?></td>
								<td><?php echo $fn->tarihVer( $gunluk[ 'tarih' ] ); ?></td>
								<td><?php echo $gecerlilik; ?></td>
								<td align = "center">
									<a modul= 'uretim_sistemi' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs " href = "?modul=is_gunlukleri&islem=guncelle&gunluk_id=<?php echo $gunluk[ 'gunluk_id' ]; ?>" >
										Düzenle
									</a>
								</td>
								<td align = "center">
									<button modul= 'uretim_sistemi' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/uretim_sistemi/is_gunlukleriSEG.php?islem=sil&gunluk_id=<?php echo $gunluk[ 'gunluk_id' ]; ?>" data-toggle="modal" data-target="#kayit_sil_onay" >Sil</button>
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
			<h3 class="card-title">İş Günlüğü Ekle / Güncelle</h3>
		</div>

			<form class="form-horizontal" id = "kayit_formu" action = "_modul/uretim_sistemi/is_gunlukleriSEG.php" method = "POST" enctype="multipart/form-data">
				<div class="card-body">
					<input type = "hidden" name = "gunluk_id" value = "<?php echo $gunluk_id; ?>">
					<input type = "hidden" name = "is_id" value = "<?php echo $is_id; ?>">
					<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">

					<div class="form-group">
						<label class="control-label">Günlük Hedef</label>
						<input required type="number" min = "1" class="form-control" name ="gunluk_hedef" value = "<?php echo $gunlukBilgileri[ "gunluk_hedef" ]; ?>">
					</div>

					<div class="form-group">
						<label class="control-label">Tarih</label>
						<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
							<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input type="text" name="tarih" id = "tarih" value="<?php if( $gunlukBilgileri[ 'tarih' ] !='' ){echo date('d.m.Y',strtotime($gunlukBilgileri[ 'tarih' ] ));}//else{ echo date('d.m.Y'); } ?>" class="form-control datetimepicker-input" data-target="#datetimepicker1"/>
						</div>
					</div>
					<div class="form-group">
						<label  class="control-label">Geçerlilik</label>
						<select class="form-control" name = "gecerlilik" required>
							<option value="1" <?php if( $gunlukBilgileri[ 'gecerlilik' ] == 1 ) echo 'selected'; ?>>Hedef, iş bitinceye kadar geçerli</option>
							<option value="2" <?php if( $gunlukBilgileri[ 'gecerlilik' ] == 2 ) echo 'selected'; ?>>Hedef, seçilen tarihin günü için geçerli</option>
						</select>
					</div>
				</div>
				<div class="card-footer">
					<button modul= 'is_gunlukleri' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
					<a modul= 'is_gunlukleri' yetki_islem="ekle" type="reset" class="btn btn-primary btn-sm pull-right" href = "?modul=is_gunlukleri&islem=ekle" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</a>
				</div>
			</form>
		</div>
	</div>
	<?php } else { ?>
		<div class="col-md-5">
			<div class="card">
				<div class="card-body">
					<h3 class = "text-danger">Uyarı!</h3>
					<p><h6>Sistemde <b>aktif bir iş</b> bulunamadı! İş günlüğü ekleyebilmek için lütfen aktif bir iş ekleyin veya var olan bir işi aktifleştirin.</h6></p>
					<a href = "?modul=isler&islem=ekle" class="btn btn-primary">
						<i class="fas fa-cut"></i> İşler modülüne git
					</a>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<script>
/*
$('input[name="is_aktif"]').on('switchChange.bootstrapSwitch', function(event, state) {
	if (state == true ){
		$( "#bitis_tarihi" ).prop( "required", false );
	}else{
		$( "#bitis_tarihi" ).prop( "required", true );
	}
});


$( '#is_id' ).change( function(){
	var  url 		= window.location;
	var origin		= url.origin;
	var path		= url.pathname;
	var search		= (new URL(document.location)).searchParams;
	var modul   	= search.get('modul');
	var is_id = "&is_id=" + $(this).val();
	window.location.replace(origin + path+'?modul='+modul+is_id);
} );
*/

</script>

<script type="text/javascript">
	$(function () {
		$('#datetimepicker1').datetimepicker({
			//defaultDate: new Date(),
			minDate:new Date(),
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