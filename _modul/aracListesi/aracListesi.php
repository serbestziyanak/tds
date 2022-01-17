<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj			= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu		= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'arac_id' ] = $_SESSION[ 'sonuclar' ][ 'arac_id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

if( $_REQUEST['islem'] == 'arama' ){
	
	if( isset( $_REQUEST['arama_arac_no'] ) and $_REQUEST['arama_arac_no'] != '' )
		$arama[] = "arac_no = ".$_REQUEST['arama_arac_no'];
		
	if( isset( $_REQUEST['arama_sube_id'] ) and $_REQUEST['arama_sube_id'] != '' )
		$arama[] = "sube_id = ".$_REQUEST['arama_sube_id'];
		
	if( isset( $_REQUEST['arama_arac_marka_id'] ) and $_REQUEST['arama_arac_marka_id'] != '' )
		$arama[] = "arac_marka_id = ".$_REQUEST['arama_arac_marka_id'];
		
	if( isset( $_REQUEST['arama_arac_modeli'] ) and $_REQUEST['arama_arac_modeli'] != '' )
		$arama[] = "tipi like '%".$_REQUEST['arama_arac_modeli']."%'";
		
	if( isset( $_REQUEST['arama_model_yili'] ) and $_REQUEST['arama_model_yili'] != '' )
		$arama[] = "model_yili = ".$_REQUEST['arama_model_yili'];
				
	if( isset( $_REQUEST['arama_min_fiyat'] ) and $_REQUEST['arama_min_fiyat'] != '' )
		$arama[] = "ilan_fiyati >= ".$_REQUEST['arama_min_fiyat'];
				
	if( isset( $_REQUEST['arama_max_fiyat'] ) and $_REQUEST['arama_max_fiyat'] != '' )
		$arama[] = "ilan_fiyati <= ".$_REQUEST['arama_max_fiyat'];
				
	$arama = implode(" AND ", $arama);
	if( $arama !='' )
		$arama = "AND ".$arama;
}

$yetkili_subeler = $_SESSION[ 'subeler' ];

$SQL_oku = <<< SQL
SELECT
	 a.*
	,amarka.adi arac_marka_adi
	,sube.adi as sube_adi
	,DATEDIFF(NOW(),a.kayit_tarihi) as kayit_gun_sayisi
	,DATEDIFF(a.ruhsat_muayene_gecerlilik_tarihi,NOW()) as muayene_gun_sayisi
	,satis.satis_tarihi
FROM
	tb_araclar AS a
LEFT JOIN
	tb_arac_markalari as amarka ON amarka.id = a.arac_marka_id
LEFT JOIN
	tb_subeler as sube ON sube.id = a.sube_id
LEFT JOIN 
	tb_arac_satislari as satis ON satis.arac_id = a.id
WHERE 
	a.aktif = 1 AND (satis.dosya_kapatma = 2 OR satis.dosya_kapatma is null)
AND
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE a.sube_id in ($yetkili_subeler)
	END
ORDER BY a.arac_no DESC
SQL;

$SQL_oku_arama = <<< SQL
SELECT
	 a.*
	,amarka.adi arac_marka_adi
	,sube.adi as sube_adi
	,DATEDIFF(NOW(),a.kayit_tarihi) as kayit_gun_sayisi
	,DATEDIFF(a.ruhsat_muayene_gecerlilik_tarihi,NOW()) as muayene_gun_sayisi
FROM
	tb_araclar AS a
LEFT JOIN
	tb_arac_markalari as amarka ON amarka.id = a.arac_marka_id
LEFT JOIN
	tb_subeler as sube ON sube.id = a.sube_id
WHERE 
	a.aktif = 1 $arama
ORDER BY a.arac_no DESC
SQL;



$SQL_arac_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_araclar
WHERE
	id = ?
SQL;

$SQL_subeler = <<< SQL
SELECT
	*
FROM
	tb_subeler
WHERE 
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE id in ($yetkili_subeler)
	END
SQL;


$SQL_firmalar = <<< SQL
SELECT * FROM tb_firmalar
SQL;

$arac_id				= array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;

if( $_REQUEST['islem'] == 'arama' ){
	$araclar			= $vt->select( $SQL_oku_arama, array(  ) );
}else{
	$araclar			= $vt->select( $SQL_oku, array( $_SESSION[ 'super' ]  ) );
}

$firmalar				= $vt->select( $SQL_firmalar, array() );
$arac					= $vt->selectSingle( $SQL_arac_bilgileri, array( $arac_id ) );
$subeler				= $vt->select( $SQL_subeler, array( $_SESSION[ 'super' ]  ) );
$arac_bilgileri			= array();
$islem					= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $_REQUEST['modul'] == 'aracListesi2' )
	$ekleme_acik = 1;

if( $islem == 'guncelle' )
$arac_bilgileri = array(
	 'id'			=> $arac[ 2 ][ 'id' ]
	,'adi'			=> $arac[ 2 ][ 'adi' ]
	,'soyadi'		=> $arac[ 2 ][ 'soyadi' ]
	,'cep_telefonu'	=> $arac[ 2 ][ 'cep_telefonu' ]
	,'iban'			=> $arac[ 2 ][ 'iban' ]
	,'firma_id'		=> $arac[ 2 ][ 'firma_id' ]
);
?>

<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="aracListesi_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#aracListesi_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>

<div class="modal fade" id="aracListesi_onayla_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Lütfen Dikkat!</h4>
			</div>
			<div class="modal-body">
				Bu kaydı <b>Onaylamak</b> istediğinize emin misiniz?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
				<a class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>
	$( '#aracListesi_onayla_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>
		<?php if( $_REQUEST['islem'] != 'arama' ){ ?>
		<div class="row">
          <!-- right column -->
          <!-- left column -->
			<div class="col-md-12">
				<div id="accordion">
					<!-- general form elements -->
					<div class="card card-secondary">
						<div class="card-header" data-toggle="collapse"  href="#collapseOne">
							<h3 class="card-title">
							<a class="d-block w-100" data-toggle="collapse" href="#collapseOne">
								<i class="fas fa-plus"></i>
								Yeni Araç Ekle
							</a>
							</h3>
						</div>
					  <!-- /.card-header -->
					  <!-- form start -->
						<form id = "kayit_formu" action = "_modul/aracListesi/aracListesiSEG.php" method = "POST">
							<div id="collapseOne" class="collapse <?php if( $ekleme_acik == 1 or $islem == 'guncelle' ) echo 'show';  ?>" data-parent="#accordion">
								<div class="card-body">
									<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
									<div class="form-group">
										<label  class="control-label">Şube</label>
											<select  class="form-control select2" name = "sube_id" required>
													<option value="">Seçiniz</option>
												<?php foreach( $subeler[ 2 ] AS $sube ) { ?>
													<option value = "<?php echo $sube[ 'id' ]; ?>" <?php if( $sube[ 'id' ] ==  $arac_bilgileri[ 'sube_id' ] ) echo 'selected'?>><?php echo $sube[ 'adi' ]?></option>
												<?php } ?>
											</select>
									</div>
									<div class="form-group">
										<label  class="control-label">Plaka</label>
											<input type="text" class="form-control" name ="plaka" value = "<?php echo $arac_bilgileri[ 'plaka' ]; ?>" required placeholder="Örn : 34ABC123">
									</div>
									<div class="form-group">
									  <label class="control-label">Kayıt Tarihi:</label>
										<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input type="text" name="kayit_tarihi" value="<?php if( $arac_bilgileri['kayit_tarihi'] !=null ) echo date('d.m.Y H:i',strtotime($arac_bilgileri['kayit_tarihi'])); else echo date('d.m.Y H:i'); ?>" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#datetimepicker1"/>
										</div>
									</div>
								</div>
							
								<!-- /.card-body -->
								<div class="card-footer">
									<button modul= 'aracListesi' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
									<a modul= 'aracListesi'  yetki_islem="ekle" type="reset" class="btn btn-primary btn-sm pull-right" href = "?modul=aracListesi&islem=ekle" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</a>
								</div>
							</div>
						</form>
					</div>
					<!-- /.card -->
				</div>
			</div>
			<!--/.col (left) -->


        </div>
		<?php } ?>
        <!-- /.row -->

        <div class="row">
			<div class="col-md-12">
				<div class="card card-success">
				  <div class="card-header">
					<h3 class="card-title"><i class="fas fa-car-side"></i> &nbsp;Araçlar</h3>
					<!--div class="card-tools">
					  <div class="input-group input-group-sm" style="width: 150px;">
						<input type="text" name="table_search" class="form-control float-right" placeholder="Ara">
						<div class="input-group-append">
						  <button type="submit" class="btn btn-default">
							<i class="fas fa-search"></i>
						  </button>
						</div>
					  </div>
					</div-->
				  </div>
				  <!-- /.card-header -->
				  <div class="card-body">
					<table id="example2" class="table table-sm table-bordered table-hover">
					  <thead>
						<tr>
							<th style="width: 15px">#</th>
							<th>Şube</th>
							<th>Araç No</th>
							<th>Marka</th>
							<th>Ticari Adı</th>
							<th>Tipi / Seri</th>
							<th>Model Yılı</th>
							<th>İlan Fiyatı</th>
							<th>Muayene Tarihi</th>
							<th>Kayıt Tarihi</th>
							<?php if( $_REQUEST['islem'] != 'arama' ){ ?>
							<th data-priority="1">Onay</th>
							<?php } ?>
							<th data-priority="1">Detaylar</th>
							<th data-priority="1">Prosesler</th>
							<th data-priority="1">Satış</th>
							<?php if( $_REQUEST['islem'] != 'arama' ){ ?>
							<th data-priority="1">Sil</th>
							<?php } ?>
						</tr>
					  </thead>
					  <tbody>
						<?php $sayi = 1; foreach( $araclar[ 2 ] AS $arac ) { 
						$tr_class = "";
						if( $arac['kayit_gun_sayisi'] >25 )
							$tr_class = "table-warning";
						if( $arac['kayit_gun_sayisi'] > 60 )
							$tr_class = "table-danger";
							
						$tr_class2 = "";
						if( $arac['muayene_gun_sayisi'] <= 7 )
							$tr_class2 = "table-danger";
							
						?>
						<tr>
							<td><?php echo $sayi++; ?></td>
							<td><?php echo $arac[ 'sube_adi' ]; ?></td>
							<td style ="font-weight:bold;"><?php echo $arac[ 'arac_no' ]; ?></td>
							<td><?php echo $arac[ 'arac_marka_adi' ]; ?></td>
							<td><?php echo $arac[ 'ticari_adi' ]; ?></td>
							<td><?php echo $arac[ 'model_tipi' ]; ?></td>
							<td><?php echo $arac[ 'model_yili' ]; ?></td>
							<td><?php echo $fn->sayiFormatiVer($arac[ 'ilan_fiyati' ]); ?> &#8378;</td>
							<?php if( $arac[ 'ruhsat_muayene_gecerlilik_tarihi' ] == '' or $arac[ 'ruhsat_muayene_gecerlilik_tarihi' ] == null ){ ?>
							<td></td>
							<?php }else{ ?>
							<td class="<?php echo $tr_class2; ?>"><span style="display:none;"><?php echo $arac[ 'ruhsat_muayene_gecerlilik_tarihi' ]; ?></span><?php echo date('d.m.Y',strtotime($arac['ruhsat_muayene_gecerlilik_tarihi'])); ?></td>
							<?php } ?>
							<td class="<?php echo $tr_class; ?>"><span style="display:none;"><?php echo $arac[ 'kayit_tarihi' ]; ?></span><?php echo date('d.m.Y H:i',strtotime($arac['kayit_tarihi'])); ?></td>
							<?php if( $_REQUEST['islem'] != 'arama' ){ ?>
							<td align = "center">
								<?php if( $arac[ 'onaya_gonderildi' ] == 1 and $arac[ 'onaylandi' ] == 0 ){ ?>
									<button modul= 'aracListesi' yetki_islem="onayla" class="btn btn-sm  bg-indigo color-palette btn-xs" data-href="_modul/aracListesi/aracListesiSEG.php?islem=onayla&id=<?php echo $arac[ 'id' ]; ?>" data-toggle="modal" data-target="#aracListesi_onayla_onay" >Onayla</button>
								<?php } ?>
								<?php if( $arac[ 'onaya_gonderildi' ] == 0 and $arac[ 'onaylandi' ] == 0 ){ ?>
									<span class="badge badge-warning" >Sürüyor</span>
								<?php } ?>
								<?php if( $arac[ 'onaya_gonderildi' ] == 1 and $arac[ 'onaylandi' ] == 1 ){ ?>
									<button modul= 'aracListesi' yetki_islem="onay_kaldir" class="btn btn-sm  btn-success btn-xs" data-href="_modul/aracListesi/aracListesiSEG.php?islem=onayla&id=<?php echo $arac[ 'id' ]; ?>" data-toggle="modal" data-target="#aracListesi_onayla_onay" >Onayı Kaldır</button>
								<?php } ?>
							</td>
							<?php } ?>
							<td align = "center">
								<a modul= 'aracListesi' yetki_islem="detaylar" class = "btn btn-sm btn-primary btn-xs" href = "?modul=araclar&islem=detaylar&id=<?php echo $arac[ 'id' ]; ?>&tab_no=1" >
									Detaylar
									<?php if( $arac[ 'arac_detaylari_eksik_alan_sayisi' ] > 0 ){ ?>
										<i class="fas fa-exclamation-triangle text-yellow"></i>
									<?php }else{ ?>
										<i class="fas fa-check-circle text-green"></i>
									<?php } ?>							
								</a>
							</td>
							<td align = "center">
								<a modul= 'aracListesi' yetki_islem="prosesler" class = "btn btn-sm btn-secondary btn-xs" href = "?modul=prosesler&id=<?php echo $arac[ 'id' ]; ?>&tab_no=1" >
									Prosesler
									<?php if( $arac[ 'prosesler_eksik_alan_sayisi' ] > 0 ){ ?>
										<i class="fas fa-exclamation-triangle text-yellow"></i>
									<?php }else{ ?>
										<i class="fas fa-check-circle text-green"></i>
									<?php } ?>																
								</a>
							</td>
							<td align = "center">
								<a modul= 'aracListesi' yetki_islem="arac_satis" class = "btn btn-sm btn-info btn-xs" href = "?modul=aracSatis&islem=satis&id=<?php echo $arac[ 'id' ]; ?>&tab_no=7" >
									Satış
								</a>
							</td>
							<?php if( $_REQUEST['islem'] != 'arama' ){ ?>
							<td align = "center">
								<button modul= 'aracListesi' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/aracListesi/aracListesiSEG.php?islem=sil&id=<?php echo $arac[ 'id' ]; ?>" data-toggle="modal" data-target="#aracListesi_sil_onay" >Sil</button>
							</td>
							<?php } ?>
						</tr>
						<?php } ?>
					  </tbody>
					</table>
				  </div>
				  <!-- /.card-body -->
				  <div class="card-footer clearfix">
					<!--ul class="pagination pagination-sm m-0 float-right">
					  <li class="page-item"><a class="page-link" href="#">«</a></li>
					  <li class="page-item"><a class="page-link" href="#">1</a></li>
					  <li class="page-item"><a class="page-link" href="#">2</a></li>
					  <li class="page-item"><a class="page-link" href="#">3</a></li>
					  <li class="page-item"><a class="page-link" href="#">»</a></li>
					</ul-->
				  </div>
				</div>
				<!-- /.card -->
			</div>
		</div>
<script type="text/javascript">
	var simdi = new Date(); 
	//var simdi="11/25/2015 15:58";
	$(function () {
		$('#datetimepicker1').datetimepicker({
			//defaultDate: simdi,
			format: 'DD.MM.yyyy HH:mm',
			icons: {
			time: "far fa-clock",
			date: "fa fa-calendar",
			up: "fa fa-arrow-up",
			down: "fa fa-arrow-down"
			}
		});
	});
	
</script>

