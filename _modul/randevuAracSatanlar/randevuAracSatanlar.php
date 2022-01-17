<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj			= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu		= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'randevu_id' ] = $_SESSION[ 'sonuclar' ][ 'randevu_id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$yetkili_subeler = $_SESSION[ 'subeler' ];

$SQL_oku = <<< SQL
SELECT
	 r.*
	,amarka.adi AS arac_marka_adi
	,concat(k.adi,' ',k.soyadi) as personel
	,at.adi AS arac_tipi_adi
	,sube.adi as sube_adi
FROM
	tb_randevular AS r
LEFT JOIN
	tb_arac_markalari AS amarka ON amarka.id = r.arac_marka_id
LEFT JOIN
	tb_sistem_kullanici AS k ON k.id = r.personel_id
LEFT JOIN
	tb_arac_tipleri AS at ON at.id = r.arac_tipi_id
LEFT JOIN 
	tb_subeler AS sube ON sube.id = r.sube_id
WHERE 
	r.aktif = 1 AND r.randevu_tipi = 1
AND
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE r.sube_id in ($yetkili_subeler)
	END
SQL;

$SQL_randevu_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_randevular
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

$SQL_arac_markalari = <<< SQL
SELECT
	*
FROM
	tb_arac_markalari
SQL;

$SQL_arac_tipleri = <<< SQL
SELECT
	*
FROM
	tb_arac_tipleri 
SQL;

$SQL_firmalar = <<< SQL
SELECT * FROM tb_firmalar
SQL;

$randevu_id				= array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$randevular				= $vt->select( $SQL_oku, array( $_SESSION[ 'super' ]  ) );
$firmalar				= $vt->select( $SQL_firmalar, array() );
$randevu				= $vt->selectSingle( $SQL_randevu_bilgileri, array( $randevu_id ) );
$subeler				= $vt->select( $SQL_subeler, array( $_SESSION[ 'super' ]  ) );
$arac_markalari			= $vt->select( $SQL_arac_markalari, array() );
$arac_tipleri			= $vt->select( $SQL_arac_tipleri, array() );
$randevu_bilgileri		= array();
$islem					= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'guncelle' )
$randevu_bilgileri = $randevu[ 2 ];

if( $islem == 'ekle' and $_REQUEST[ 'nereden' ] == 'crm' )
$randevu_bilgileri = array(
	 'crm_id'				=> $_REQUEST[ 'crm_id' ]
	,'adi'					=> $_REQUEST[ 'adi' ]
	,'soyadi'				=> $_REQUEST[ 'soyadi' ]
	,'cep_tel'				=> $_REQUEST[ 'cep_tel' ]
	,'email'				=> $_REQUEST[ 'email' ]
	,'arac_model_yili'		=> $_REQUEST[ 'arac_model_yili' ]
	,'sube_id'				=> $_REQUEST[ 'sube_id' ]
	,'arac_marka_id'		=> $_REQUEST[ 'arac_marka_id' ]
	,'arac_tipi_id'			=> $_REQUEST[ 'arac_tipi_id' ]
	,'arac_model'			=> $_REQUEST[ 'arac_model' ]
);

?>

<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="randevuAracSatanlar_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#randevuAracSatanlar_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>
		<div class="row">
          <!-- left column -->
          <div class="col-md-12">
            <!-- general form elements -->
			<div id="accordion">
				<div class="card card-secondary">
					<div class="card-header" data-toggle="collapse"  href="#collapseOne">
						<h3 class="card-title">
						<a class="d-block w-100" data-toggle="collapse" href="#collapseOne">
							Randevu Ekle / Güncelle
						</a>
						</h3>
					</div>
					<form id = "kayit_formu" action = "_modul/randevuAracSatanlar/randevuAracSatanlarSEG.php" method = "POST">
						<div id="collapseOne" class="collapse <?php if( $_REQUEST[ 'islem'] == 'ekle' or $islem == 'guncelle' ) echo 'show';  ?>" data-parent="#accordion">
							<div class="card-body">
								<input type = "hidden" name = "id" value = "<?php echo $randevu_bilgileri[ 'id' ]; ?>">
								<input type = "hidden" name = "randevu_tipi" value = "1">
								<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
								<div class="row">
									<div class="col-md-4">
										<div class="form-group">
											<label  class="control-label">Şube</label>
											<select  class="form-control select2" name = "sube_id" required>
													<option value="">Seçiniz</option>
												<?php foreach( $subeler[ 2 ] AS $sube ) { ?>
													<option value = "<?php echo $sube[ 'id' ]; ?>" <?php if( $sube[ 'id' ] ==  $randevu_bilgileri[ 'sube_id' ] ) echo 'selected';elseif( $sube[ 'id' ] ==  $_SESSION[ 'sube_id' ] ) echo 'selected'; ?>><?php echo $sube[ 'adi' ]?></option>
												<?php } ?>
											</select>
										</div>
										<div class="form-group">
											<label>Araç Tipi</label>
											<select name="arac_tipi_id" class="form-control  select2" style="width: 100%;" required>
												<option value="">Seçiniz</option>
											<?php foreach( $arac_tipleri[ 2 ] AS $arac_tipi ) { ?>
												<option value = "<?php echo $arac_tipi[ 'id' ]; ?>" <?php if( $arac_tipi[ 'id' ] ==  $randevu_bilgileri[ 'arac_tipi_id' ] ) echo 'selected'?>><?php echo $arac_tipi[ 'adi' ]?></option>
											<?php } ?>
											</select>
										</div>
										<div class="form-group">
											<label>Marka</label>
											<select name="arac_marka_id" class="form-control  select2" style="width: 100%;">
												<option value="">Seçiniz</option>
											<?php foreach( $arac_markalari[ 2 ] AS $arac_marka ) { ?>
												<option value = "<?php echo $arac_marka[ 'id' ]; ?>" <?php if( $arac_marka[ 'id' ] ==  $randevu_bilgileri[ 'arac_marka_id' ] ) echo 'selected'?>><?php echo $arac_marka[ 'adi' ]?></option>
											<?php } ?>
											</select>
										</div>
										<div class="form-group">
											<label  class="control-label">Model</label>
											<input type="text" class="form-control" name ="arac_model" value = "<?php echo $randevu_bilgileri[ 'arac_model' ]; ?>" required placeholder="">
										</div>
										<div class="form-group">
											<label>Model Yılı</label>
											<select name="arac_model_yili" class="form-control  select2" style="width: 100%;">
												<option value="">Seçiniz</option>
											<?php for( $i = date('Y'); $i>1930; $i-- ) { ?>
												<option value = "<?php echo $i; ?>" <?php if( $i ==  $randevu_bilgileri[ 'arac_model_yili' ] ) echo 'selected'?>><?php echo $i?></option>
											<?php } ?>
											</select>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
										  <label class="control-label">Randevu Tarihi</label>
											<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
												<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
													<div class="input-group-text"><i class="fa fa-calendar"></i></div>
												</div>
												<input type="text" name="randevu_tarihi" value="<?php if( $arac_bilgileri['randevu_tarihi'] !=null ) echo date('d.m.Y H:i',strtotime($arac_bilgileri['randevu_tarihi'])); else echo date('d.m.Y H:i'); ?>" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#datetimepicker1"/>
											</div>
										</div>
										<div class="form-group">
											<label  class="control-label">Müşteri Adı</label>
											<input type="text" class="form-control" name ="adi" value = "<?php echo $randevu_bilgileri[ 'adi' ]; ?>" required placeholder="">
										</div>
										<div class="form-group">
											<label  class="control-label">Müşteri Soyadı</label>
											<input type="text" class="form-control" name ="soyadi" value = "<?php echo $randevu_bilgileri[ 'soyadi' ]; ?>" required placeholder="">
										</div>
										<div class="form-group">
											<label>Cep Telefonu:</label>
											<div class="input-group">
												<div class="input-group-prepend">
													<span class="input-group-text"><i class="fas fa-phone"></i></span>
												</div>
												<input type="text" name ="cep_tel" value = "<?php echo $randevu_bilgileri[ 'cep_tel' ]; ?>" class="form-control " data-inputmask='"mask": "0(999) 999-9999"' data-mask required>
											</div>
											<!-- /.input group -->
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label>Email</label>
											<div class="input-group">
												<div class="input-group-prepend">
													<span class="input-group-text"><i class="fas fa-envelope"></i></span>
												</div>
												<input type="email" class="form-control" name ="email" value = "<?php echo $randevu_bilgileri[ 'email' ]; ?>" required placeholder="">
											</div>
											<!-- /.input group -->
										</div>
										<div class="form-group">
											<label  class="control-label">Notlar</label>
											<textarea rows="4" class="form-control" name ="notlar" required ><?php echo $randevu_bilgileri[ 'notlar' ]; ?></textarea>
										</div>
									</div>
								</div>
							</div>
							<div class="card-footer">
								<button modul= 'randevuAracSatanlar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
								<a modul= 'randevuAracSatanlar'  yetki_islem="ekle" type="reset" class="btn btn-primary btn-sm pull-right" href = "?modul=randevuAracSatanlar&islem=ekle" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</a>
							</div>
						</div>
					</form>
				</div>
            </div>
            <!-- /.card -->

          </div>
          <!--/.col (left) -->


        </div>
        <!-- /.row -->
        <div class="row">
		  <div class="col-md-12">
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Araç Satmak İsteyenlerin Randevu Listesi</h3>
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
						<th>Araç Tipi</th>
						<th>Marka</th>
						<th>Model</th>
						<th>Model Yılı</th>
						<th>Adı</th>
						<th>Soyadı</th>
						<th>Cep Tel</th>
						<th>Notlar</th>
						<th>Randevu Tarihi</th>
						<th>Personel</th>
						<th data-priority="1" style="width: 20px">Düzenle</th>
						<th data-priority="1" style="width: 20px">Sil</th>
                    </tr>
                  </thead>
                  <tbody>
					<?php $sayi = 1; foreach( $randevular[ 2 ] AS $randevu ) { ?>
					<tr>
						<td><?php echo $sayi++; ?></td>
						<td><?php echo $randevu[ 'sube_adi' ]; ?></td>
						<td><?php echo $randevu[ 'arac_tipi_adi' ]; ?></td>
						<td><?php echo $randevu[ 'arac_marka_adi' ]; ?></td>
						<td><?php echo $randevu[ 'arac_model' ]; ?></td>
						<td><?php echo $randevu[ 'arac_model_yili' ]; ?></td>
						<td><?php echo $randevu[ 'adi' ]; ?></td>
						<td><?php echo $randevu[ 'soyadi' ]; ?></td>
						<td><?php echo $randevu[ 'cep_tel' ]; ?></td>
						<td><?php echo $randevu[ 'notlar' ]; ?></td>
						<td><span style="display:none;"><?php echo $randevu[ 'randevu_tarihi' ]; ?></span><?php echo date('d.m.Y H:i',strtotime($randevu['randevu_tarihi'])); ?></td>
						<td><?php echo $randevu[ 'personel' ]; ?></td>
						<td align = "center">
						  <a modul = 'randevuAracSatanlar' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=randevuAracSatanlar&islem=guncelle&id=<?php echo $randevu[ 'id' ]; ?>" >
							Düzenle
						  </a>
						</td>
						<td align = "center">
							<button modul= 'randevuAracSatanlar' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/randevuAracSatanlar/randevuAracSatanlarSEG.php?islem=sil&id=<?php echo $randevu[ 'id' ]; ?>" data-toggle="modal" data-target="#randevuAracSatanlar_sil_onay" >Sil</button>
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
        <!-- right column -->
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

