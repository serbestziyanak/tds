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
$aktif_tab		= array_key_exists( 'aktif_tab'		,$_REQUEST ) ? $_REQUEST[ 'aktif_tab' ]		: 'tab_genel';


$satir_renk				= $personel_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= "Güncelle";
$kaydet_buton_cls		= $personel_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';



$SQL_ayar_oku = <<< SQL
SELECT
	*
FROM
	tb_genel_ayarlar 
WHERE
	firma_id = ?
SQL;

/* Gruplar */
$SQL_gruplar = <<< SQL
SELECT
	*
FROM
	tb_gruplar
WHERE
	firma_id = ? AND
	aktif = 1
SQL;
/* Carpanlar */
$SQL_carpanlar = <<< SQL
SELECT
	*
FROM
	tb_carpanlar
WHERE
	firma_id = ?
SQL;



$ayar						= $vt->select( $SQL_ayar_oku, array( $_SESSION[ 'firma_id' ] ) )[ 2 ][ 0 ];
$gruplar					= $vt->select( $SQL_gruplar, array( $_SESSION[ 'firma_id' ] ) )[ 2 ];
$carpanlar					= $vt->select( $SQL_carpanlar, array( $_SESSION[ 'firma_id' ] ) )[ 2 ];

$giris_cikis_denetimi_grubu = array_filter( explode( ",", $ayar[ "giris_cikis_denetimi_grubu" ] ) );
$puantaj_hesaplama_grubu 	= array_filter( explode( ",", $ayar[ "puantaj_hesaplama_grubu" ] ) );
$beyaz_yakali_personel 		= $ayar[ "beyaz_yakali_personel" ];

?>

<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card <?php if( $personel_id == 0 ) echo 'card-secondary' ?>">
					<div class="card-header p-2">
						<ul class="nav nav-pills tab-container">
							<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Genel Ayarlar</h6>
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content">
							<!-- GENEL BİLGİLER -->
							<div class="tab-pane active" id="_genel">
								<form class="form-horizontal" action = "_modul/genelAyarlar/genelAyarlarSEG.php" method = "POST" enctype="multipart/form-data">
									<h3 class="profile-username text-center"><b> </b></h3>
									<div class="form-group col-sm-3 float-left">
										<label class="control-label">Aylık Çalışma Saati</label>
										<input required type="text" class="form-control" name ="aylik_calisma_saati" value = "<?php echo $ayar[ "aylik_calisma_saati" ]; ?>" id = "txt_adi">
									</div>
									<div class="form-group col-sm-3 float-left">
										<label class="control-label">Haftalık Çalışma Saati</label>
										<input required type="text" class="form-control" name ="haftalik_calisma_saati" value = "<?php echo $ayar[ "haftalik_calisma_saati" ]; ?>" id = "txt_soyadi">
									</div>
									
									<div class="form-group col-sm-3 float-left">
										<label class="control-label">Günlük Çalışma Süresi(Dakika)</label>
										<input required type="text" class="form-control" name ="gunluk_calisma_suresi" value = "<?php echo $ayar[ "gunluk_calisma_suresi" ]; ?>" id = "txt_soyadi">
									</div>
									
									<div class="form-group col-sm-3 float-left">
										<label class="control-label">Yarım Gün Tatil Süresi(Dakika)</label>
										<input required type="text" class="form-control" name ="yarim_gun_tatil_suresi" value = "<?php echo $ayar[ "yarim_gun_tatil_suresi" ]; ?>" id = "txt_soyadi">
									</div>
									
									<div class="form-group">
										<label  class="control-label">Hangi Gruplara Giriş Çıkış Denetimi Yapılsın </label>
										<select  class="form-control select2"  multiple="multiple" name = "giris_cikis_denetimi_grubu[]" required>
											<?php foreach( $gruplar as $grup ) { ?>
												<option value = ",<?php echo $grup[ 'id' ]; ?>," <?php echo in_array($grup[ 'id' ], $giris_cikis_denetimi_grubu) ? 'selected' : ''; ?>><?php echo $grup['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group ">
										<label class="control-label">Kaç Gün Gelmediğinde Pazar Verilmesin</label>
										<input type="number" class="form-control" name ="pazar_kesinti_sayisi" value = "<?php echo $ayar[ "pazar_kesinti_sayisi" ]; ?>" required maxlength = "11" minlength = "11">
									</div>
									<div class="form-group">
										<label  class="control-label">Puantaj Hesaplama Hangi Gruplara Uygulansın</label>
										<select  class="form-control select2"  multiple="multiple" name = "puantaj_hesaplama_grubu[]" required>
											<?php foreach( $gruplar as $grup ) { ?>
												<option value = ",<?php echo $grup[ 'id' ]; ?>," <?php echo in_array($grup[ 'id' ], $puantaj_hesaplama_grubu ) ? 'selected' : ''; ?>><?php echo $grup['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label  class="control-label">Beyaz Yakalı Personel Grubu</label>
										<select  class="form-control select2"   name = "beyaz_yakali_personel" required>
											<?php foreach( $gruplar as $grup ) { ?>
												<option value = "<?php echo $grup[ 'id' ]; ?>" <?php echo $grup[ 'id' ] == $beyaz_yakali_personel  ? 'selected' : ''; ?>><?php echo $grup['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group col-sm-6 float-left">
										<label  class="control-label">Normal Çalışma Çarpanı</label>
										<select  class="form-control select2"   name = "normal_carpan_id" required>
											<?php foreach( $carpanlar as $carpan ) { ?>
												<option value = "<?php echo $carpan[ 'id' ]; ?>" <?php echo $carpan[ 'id' ] == $ayar[ "normal_carpan_id" ]  ? 'selected' : ''; ?>><?php echo $carpan['adi'] ." - ".$carpan['carpan']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group col-sm-6 float-left">
										<label  class="control-label">Tatil Mesaisi Çarpanı</label>
										<select  class="form-control select2"   name = "tatil_mesai_carpan_id" required>
											<?php foreach( $carpanlar as $carpan ) { ?>
												<option value = "<?php echo $carpan[ 'id' ]; ?>" <?php echo $carpan[ 'id' ] == $ayar[ "tatil_mesai_carpan_id" ]  ? 'selected' : ''; ?>><?php echo $carpan['adi'] ." - ".$carpan['carpan']; ?></option>
											<?php } ?>
										</select>
									</div>


									<label class="control-label col-sm-6">Giriş Çıkış Listeleri Gösterilsin Mi? ( Anasayfada Bulunan Gelmeyenler, Geç Gelenler ve Erken  Çıkanlar Listesi )</label>
									<div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-focused bootstrap-switch-animate bootstrap-switch-off" >
										<div class="bootstrap-switch-container" >
											<input type="checkbox" name="giris_cikis_liste_goster"  <?php echo $ayar[ "giris_cikis_liste_goster" ] == 1 ? 'checked': ''; ?>  data-bootstrap-switch="" data-off-color="danger" data-on-text="Evet" data-off-text="Hayır" data-on-color="success">
										</div>
									</div>
									<div class="clearfix"></div>
									<label class="control-label col-sm-6">Giriş Çıkış Listeleri Tutanak  Kaydedilsin Mi?</label>
									<div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-focused bootstrap-switch-animate bootstrap-switch-off" >
										<div class="bootstrap-switch-container" >
											<input type="checkbox" name="giris_cikis_tutanak_kaydet"  <?php echo $ayar[ "giris_cikis_tutanak_kaydet" ] == 1 ? 'checked': ''; ?>  data-bootstrap-switch="" data-off-color="danger" data-on-text="Evet" data-off-text="Hayır" data-on-color="success">
										</div>
									</div>
									<div class="clearfix"></div>
									<label class="control-label col-sm-6">Tutanak Oluştur Butonu Oluşturulsun mu? </label>
									<div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-focused bootstrap-switch-animate bootstrap-switch-off" >
										<div class="bootstrap-switch-container" >
											<input type="checkbox" name="tutanak_olustur"  <?php echo $ayar[ "tutanak_olustur" ] == 1 ? 'checked': ''; ?>  data-bootstrap-switch="" data-off-color="danger" data-on-text="Evet" data-off-text="Hayır" data-on-color="success">
										</div>
									</div>
									
									<div class="clearfix"></div>
									<div class="card-footer">
										<button modul= 'genelAyarlar' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
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
<script type="text/javascript">
/* Slect2 nesnesinin sayfanın genişliğine göre otomatik uzayıp kısalmasını sağlar*/
$( window ).on( 'resize', function() {
	$('.form-group').each(function() {
		var formGroup = $( this ),
			formgroupWidth = formGroup.outerWidth();
		formGroup.find( '.select2-container' ).css( 'width', formgroupWidth );
	});
} );


/* Slect2 nesnesinin sayfanın genişliğine göre otomatik uzayıp kısalmasını sağlar*/
$( window ).on( 'resize', function() {
	$('.description-block').each(function() {
		var formGroup = $( this ),
			formgroupWidth = formGroup.outerWidth();
		formGroup.find( '.select2-container' ).css( 'width', formgroupWidth );
	});
} );


$( function () {
	$( '[ data-toggle="tooltip" ]' ).tooltip()
} );



</script>