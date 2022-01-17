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



$yetkili_subeler = $_SESSION[ 'subeler' ];

$SQL_oku = <<< SQL
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
LEFT JOIN tb_arac_satislari as satis ON satis.arac_id = a.id
WHERE 
	a.aktif = 1 AND satis.dosya_kapatma = 1 AND satis.cayma_durumu = 1
AND
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE a.sube_id in ($yetkili_subeler)
	END
ORDER BY a.arac_no DESC
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
$araclar			= $vt->select( $SQL_oku, array( $_SESSION[ 'super' ]  ) );
$firmalar				= $vt->select( $SQL_firmalar, array() );
$subeler				= $vt->select( $SQL_subeler, array( $_SESSION[ 'super' ]  ) );
$arac_bilgileri			= array();
$islem					= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $_REQUEST['modul'] == 'aracDosyaKapanan2' )
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
<div class="modal fade" id="aracDosyaKapanan_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#aracDosyaKapanan_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>

<div class="modal fade" id="aracDosyaKapanan_onayla_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#aracDosyaKapanan_onayla_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>

        <div class="row">
			<div class="col-md-12">
				<div class="card card-danger">
				  <div class="card-header">
					<h3 class="card-title"><i class="fas fa-check"></i> Dosyası Kapanan Araçlar (Cayma)</h3>
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
							<th data-priority="1">Detaylar</th>
							<th data-priority="1">Prosesler</th>
							<th data-priority="1">Satış</th>
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
							<td align = "center">
								<a modul= 'aracDosyaKapanan' yetki_islem="detaylar" class = "btn btn-sm btn-primary btn-xs" href = "?modul=araclar&islem=detaylar&id=<?php echo $arac[ 'id' ]; ?>&tab_no=1" >
									Detaylar
									<?php if( $arac[ 'arac_detaylari_eksik_alan_sayisi' ] > 0 ){ ?>
										<i class="fas fa-exclamation-triangle text-yellow"></i>
									<?php }else{ ?>
										<i class="fas fa-check-circle text-green"></i>
									<?php } ?>							
								</a>
							</td>
							<td align = "center">
								<a modul= 'aracDosyaKapanan' yetki_islem="prosesler" class = "btn btn-sm btn-secondary btn-xs" href = "?modul=prosesler&id=<?php echo $arac[ 'id' ]; ?>&tab_no=1" >
									Prosesler
									<?php if( $arac[ 'prosesler_eksik_alan_sayisi' ] > 0 ){ ?>
										<i class="fas fa-exclamation-triangle text-yellow"></i>
									<?php }else{ ?>
										<i class="fas fa-check-circle text-green"></i>
									<?php } ?>																
								</a>
							</td>
							<td align = "center">
								<a modul= 'aracDosyaKapanan' yetki_islem="arac_satis" class = "btn btn-sm btn-info btn-xs" href = "?modul=aracSatis&islem=satis&id=<?php echo $arac[ 'id' ]; ?>&tab_no=7" >
									Satış
								</a>
							</td>
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

