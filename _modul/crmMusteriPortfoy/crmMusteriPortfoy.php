<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj			= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu		= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'crm_musteri_id' ] = $_SESSION[ 'sonuclar' ][ 'crm_musteri_id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

if( $_REQUEST['modul'] == 'crmMusteriPortfoyArama0' ){
	$where = ' AND crm.arama_yapildi = 0';
}
if( $_REQUEST['modul'] == 'crmMusteriPortfoyArama1' ){
	$where = ' AND crm.arama_yapildi = 1';
}
if( $_REQUEST['modul'] == 'crmMusteriPortfoyMesaj0' ){
	$where = ' AND crm.mesaj_gonderildi = 0';
}
if( $_REQUEST['modul'] == 'crmMusteriPortfoyMesaj1' ){
	$where = ' AND crm.mesaj_gonderildi = 1';
}

$yetkili_subeler = $_SESSION[ 'subeler' ];

$SQL_oku = <<< SQL
SELECT
	 crm.*
	,amarka.adi AS arac_marka_adi
	,amarka.id as arac_marka_id
	,concat(k.adi,' ',k.soyadi) as personel
	,at.adi AS arac_tipi_adi
	,at.id as arac_tipi_id
	,sube.adi as sube_adi
FROM
	tb_crm_musteri_portfoy AS crm
LEFT JOIN
	tb_arac_markalari AS amarka ON amarka.id = crm.arac_marka_id
LEFT JOIN
	tb_sistem_kullanici AS k ON k.id = crm.guncelleyen_personel_id
LEFT JOIN
	tb_arac_tipleri AS at ON at.id = crm.arac_tipi_id
LEFT JOIN 
	tb_subeler AS sube ON sube.id = crm.sube_id
WHERE 
	crm.aktif = 1
AND
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE crm.sube_id in ($yetkili_subeler)
	END
$where
SQL;

$SQL_crm_musteri_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_crm_musteri_portfoy
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

$crm_musteri_id				= array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$crm_musteriler				= $vt->select( $SQL_oku, array( $_SESSION[ 'super' ]  ) );
$firmalar					= $vt->select( $SQL_firmalar, array() );
$crm_musteri				= $vt->selectSingle( $SQL_crm_musteri_bilgileri, array( $crm_musteri_id ) );
$subeler					= $vt->select( $SQL_subeler, array( $_SESSION[ 'super' ]  ) );
$arac_markalari				= $vt->select( $SQL_arac_markalari, array() );
$arac_tipleri				= $vt->select( $SQL_arac_tipleri, array() );
$crm_musteri_bilgileri		= array();
$islem						= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'guncelle' )
$crm_musteri_bilgileri = $crm_musteri[ 2 ];
?>

<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="crmMusteriPortfoy_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#crmMusteriPortfoy_sil_onay' ).on( 'show.bs.modal', function( e ) {
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
							Müşteri Ekle / Güncelle
						</a>
						</h3>
					</div>
					<form id = "kayit_formu" action = "_modul/crmMusteriPortfoy/crmMusteriPortfoySEG.php" method = "POST">
						<div id="collapseOne" class="collapse <?php if( $_REQUEST[ 'islem'] == 'ekle' or $islem == 'guncelle' ) echo 'show';  ?>" data-parent="#accordion">
							<div class="card-body">
								<input type = "hidden" name = "id" value = "<?php echo $crm_musteri_bilgileri[ 'id' ]; ?>">
								<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
								<input type = "hidden" name = "modul_adi" value = "<?php echo $_REQUEST['modul']; ?>">
								<div class="row">
									<div class="col-md-4">
										<div class="form-group clearfix card">
											<div class="card-body">
											  <label>Araç Alış Satış</label><br>
											  <div class="icheck-success d-inline">
												<input required type="radio" id="arac_alis_satis1" name="arac_alis_satis" value="1" <?php if( $crm_musteri_bilgileri[ 'arac_alis_satis' ] == "1" ) echo 'checked'?> >
												<label for="arac_alis_satis1">
													Alıcı
												</label>
											  </div>
											  <div class="icheck-success d-inline">
												<input required type="radio" id="arac_alis_satis2" name="arac_alis_satis" value="2" <?php if( $crm_musteri_bilgileri[ 'arac_alis_satis' ] == "2" ) echo 'checked'?> >
												<label for="arac_alis_satis2">
													Satıcı
												</label>
											  </div>
											</div>
										</div>
										<div class="form-group">
											<label  class="control-label">Şube</label>
											<select  class="form-control select2" name = "sube_id" required>
													<option value="">Seçiniz</option>
												<?php foreach( $subeler[ 2 ] AS $sube ) { ?>
													<option value = "<?php echo $sube[ 'id' ]; ?>" <?php if( $sube[ 'id' ] ==  $crm_musteri_bilgileri[ 'sube_id' ] ) echo 'selected'?>><?php echo $sube[ 'adi' ]?></option>
												<?php } ?>
											</select>
										</div>
										<div class="form-group">
											<label>Araç Tipi</label>
											<select name="arac_tipi_id" class="form-control  select2" style="width: 100%;" required>
												<option value="">Seçiniz</option>
											<?php foreach( $arac_tipleri[ 2 ] AS $arac_tipi ) { ?>
												<option value = "<?php echo $arac_tipi[ 'id' ]; ?>" <?php if( $arac_tipi[ 'id' ] ==  $crm_musteri_bilgileri[ 'arac_tipi_id' ] ) echo 'selected'?>><?php echo $arac_tipi[ 'adi' ]?></option>
											<?php } ?>
											</select>
										</div>
										<div class="form-group">
											<label>Marka</label>
											<select name="arac_marka_id" class="form-control  select2" style="width: 100%;" >
												<option value="">Seçiniz</option>
											<?php foreach( $arac_markalari[ 2 ] AS $arac_marka ) { ?>
												<option value = "<?php echo $arac_marka[ 'id' ]; ?>" <?php if( $arac_marka[ 'id' ] ==  $crm_musteri_bilgileri[ 'arac_marka_id' ] ) echo 'selected'?>><?php echo $arac_marka[ 'adi' ]?></option>
											<?php } ?>
											</select>
										</div>
										<div class="form-group">
											<label  class="control-label">Model</label>
											<input type="text" class="form-control" name ="arac_model" value = "<?php echo $crm_musteri_bilgileri[ 'arac_model' ]; ?>"  placeholder="">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label>Model Yılı</label>
											<select name="arac_model_yili" class="form-control  select2" style="width: 100%;" >
												<option value="">Seçiniz</option>
											<?php for( $i = date('Y'); $i>1930; $i-- ) { ?>
												<option value = "<?php echo $i; ?>" <?php if( $i ==  $crm_musteri_bilgileri[ 'arac_model_yili' ] ) echo 'selected'?>><?php echo $i?></option>
											<?php } ?>
											</select>
										</div>
										<div class="form-group">
											<label  class="control-label">Müşteri Adı</label>
											<input type="text" class="form-control" name ="adi" value = "<?php echo $crm_musteri_bilgileri[ 'adi' ]; ?>" required placeholder="">
										</div>
										<div class="form-group">
											<label  class="control-label">Müşteri Soyadı</label>
											<input type="text" class="form-control" name ="soyadi" value = "<?php echo $crm_musteri_bilgileri[ 'soyadi' ]; ?>" required placeholder="">
										</div>
										<div class="form-group">
											<label>Cep Telefonu:</label>
											<div class="input-group">
												<div class="input-group-prepend">
													<span class="input-group-text"><i class="fas fa-phone"></i></span>
												</div>
												<input type="text" name ="cep_tel" value = "<?php echo $crm_musteri_bilgileri[ 'cep_tel' ]; ?>" class="form-control " data-inputmask='"mask": "0(999) 999-9999"' data-mask required>
											</div>
											<!-- /.input group -->
										</div>
										<div class="form-group">
											<label>Email</label>
											<div class="input-group">
												<div class="input-group-prepend">
													<span class="input-group-text"><i class="fas fa-envelope"></i></span>
												</div>
												<input type="email" class="form-control" name ="email" value = "<?php echo $crm_musteri_bilgileri[ 'email' ]; ?>"  placeholder="">
											</div>
											<!-- /.input group -->
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label  class="control-label">Notlar</label>
											<textarea rows="4" class="form-control" name ="notlar"  ><?php echo $crm_musteri_bilgileri[ 'notlar' ]; ?></textarea>
										</div>
										<div class="form-group clearfix card">
											<div class="card-body">
											  <label><i class="fas fa-phone-volume" align="center"></i> Arama Durumu</label><br>
											  <div class="icheck-success d-inline">
												<input required type="radio" id="arama_yapildi2" name="arama_yapildi" value="1" <?php if( $crm_musteri_bilgileri[ 'arama_yapildi' ] == "1" ) echo 'checked'?> >
												<label for="arama_yapildi2">
													Arama Yapıldı
												</label>
											  </div>
											  <div class="icheck-danger d-inline">
												<input required type="radio" id="arama_yapildi1" name="arama_yapildi" value="0" <?php if( $crm_musteri_bilgileri[ 'arama_yapildi' ] == "0" ) echo 'checked'?> >
												<label for="arama_yapildi1">
													Arama Yapılmadı
												</label>
											  </div>
											</div>
										</div>
										<div class="form-group clearfix card">
											<div class="card-body">
											  <label><i class="far fa-comment-dots"></i> Mesaj</label><br>
											  <div class="icheck-success d-inline">
												<input required type="radio" id="mesaj_gonderildi1" name="mesaj_gonderildi" value="1" <?php if( $crm_musteri_bilgileri[ 'mesaj_gonderildi' ] == "1" ) echo 'checked'?> >
												<label for="mesaj_gonderildi1">
													Mesaj Gönderildi
												</label>
											  </div>
											  <div class="icheck-danger d-inline">
												<input required type="radio" id="mesaj_gonderildi2" name="mesaj_gonderildi" value="0" <?php if( $crm_musteri_bilgileri[ 'mesaj_gonderildi' ] == "0" ) echo 'checked'?> >
												<label for="mesaj_gonderildi2">
													Mesaj Gönderilmedi
												</label>
											  </div>
											</div>
										</div>
										<div class="form-group clearfix card">
											<div class="card-body">
											  <label><i class="fas fa-user-check"></i> Sonuç</label><br>
											  <div class="icheck-success d-inline">
												<input required type="radio" id="sonuc1" name="sonuc" value="1" <?php if( $crm_musteri_bilgileri[ 'sonuc' ] == "1" ) echo 'checked'?> >
												<label for="sonuc1">
													Olumlu
												</label>
											  </div>
											  <div class="icheck-danger d-inline">
												<input required type="radio" id="sonuc2" name="sonuc" value="2" <?php if( $crm_musteri_bilgileri[ 'sonuc' ] == "2" ) echo 'checked'?> >
												<label for="sonuc2">
													Olumsuz
												</label>
											  </div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="card-footer">
								<button modul= 'crmMusteriPortfoy' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
								<a modul= 'crmMusteriPortfoy'  yetki_islem="ekle" type="reset" class="btn btn-primary btn-sm pull-right" href = "?modul=crmMusteriPortfoy&islem=ekle" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</a>
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
                <h3 class="card-title">Müşteriler</h3>
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
                <table id="crm" class="table table-sm table-bordered table-hover">
                  <thead>
                    <tr>
						<th style="width: 15px">#</th>
						<th>Şube</th>
						<th>Durum</th>
						<th>Araç Tipi</th>
						<th>Marka</th>
						<th>Model</th>
						<th>Yıl</th>
						<th>Adı</th>
						<th>Soyadı</th>
						<th>Cep Tel</th>
						<th style="width: 20px">Notlar</th>
						<th>Kayıt Tarihi</th>
						<th>Personel</th>
						<th data-priority="1" align="center"><i class="fas fa-phone-volume" align="center"></i></th>
						<th data-priority="1" align="center"><i class="far fa-comment-dots"></i></th>
						<th data-priority="1" align="center"><i class="fas fa-user-check"></i></th>
						<th data-priority="1"><i class="fab fa-whatsapp"></i></th>
						<th data-priority="1"><i class="fas fa-clock"></i></th>
						<th data-priority="1"><i class="fas fa-edit"></i></th>
						<th data-priority="1"><i class="fas fa-trash-alt"></i></th>
                    </tr>
                  </thead>
                  <tbody>
					<?php $sayi = 1; foreach( $crm_musteriler[ 2 ] AS $crm_musteri ) { 
					$whatsapp_mesaj = "Satılık aracınızın, daha fazla sitede yayınlanarak 
										daha hızlı satılmasını istiyorsanız işe bu mesajı cevaplayarak başlayabilirsiniz. 
										Ücretsiz ekspertiz, ücretsiz fotoğraf, ücretsiz yıkama ve ücretsiz ilanlar gibi 
										bir çok hizmetten hiçbir ücret ödemeden yararlanabilirsiniz. 
										Üstelik Sıfır komisyon. 
										Ayrıntılar için https://otowow.com/ adresini ziyaret edebilirsiniz.";
					?>
					<tr>
						<td><?php echo $sayi++; ?></td>
						<td><?php echo $crm_musteri[ 'sube_adi' ]; ?></td>
						<td><?php if( $crm_musteri[ 'arac_alis_satis' ] == 1 ) echo "Alıcı"; if( $crm_musteri[ 'arac_alis_satis' ] == 2 ) echo "Satıcı"; ?></td>
						<td><?php echo $crm_musteri[ 'arac_tipi_adi' ]; ?></td>
						<td><?php echo $crm_musteri[ 'arac_marka_adi' ]; ?></td>
						<td><?php echo $crm_musteri[ 'arac_model' ]; ?></td>
						<td><?php echo $crm_musteri[ 'arac_model_yili' ]; ?></td>
						<td><?php echo $crm_musteri[ 'adi' ]; ?></td>
						<td><?php echo $crm_musteri[ 'soyadi' ]; ?></td>
						<td><?php echo $crm_musteri[ 'cep_tel' ]; ?></td>
						<td><?php echo $crm_musteri[ 'notlar' ]; ?></td>
						<td><span style="display:none;"><?php echo $crm_musteri[ 'kayit_tarihi' ]; ?></span><?php echo date('d.m.Y H:i',strtotime($crm_musteri['kayit_tarihi'])); ?></td>
						<td><?php echo $crm_musteri[ 'personel' ]; ?></td>
						<td class="<?php if( $crm_musteri[ 'arama_yapildi' ] == 1 ) echo "table-success"; else echo "table-danger"; ?>"><?php if( $crm_musteri[ 'arama_yapildi' ] == 1 ) echo "<i class='fas fa-check'></i>"; else echo "<i class='fas fa-ban'></i>"; ?></td>
						<td class="<?php if( $crm_musteri[ 'mesaj_gonderildi' ] == 1 ) echo "table-success"; else echo "table-danger"; ?>"><?php if( $crm_musteri[ 'mesaj_gonderildi' ] == 1 ) echo "<i class='fas fa-check'></i>"; else echo "<i class='fas fa-ban'></i>"; ?></td>
						<td class="<?php if( $crm_musteri[ 'sonuc' ] == 1 ) echo "table-success"; elseif( $crm_musteri[ 'sonuc' ] == 2 ) echo "table-danger"; ?>"><?php if( $crm_musteri[ 'sonuc' ] == 1 ) echo "<i class='fas fa-check'></i>"; elseif( $crm_musteri[ 'sonuc' ] == 2 ) echo "<i class='fas fa-ban'></i>"; ?></td>
						<td align = "center">
						  <a modul = 'crmMusteriPortfoy' yetki_islem="whatsapp" target="_blank" class = "btn btn-sm btn-info" href = "https://wa.me/9<?php echo str_replace(array('(',')',' ','-'),'',$crm_musteri[ 'cep_tel' ]); ?>?text=<?php echo $whatsapp_mesaj; ?>" >
							<i class="fab fa-whatsapp"></i>
						  </a>
						</td>
						<td align = "center">
						<?php if( $crm_musteri[ 'arac_alis_satis' ] == 1 ){ ?>
						  <a modul = 'crmMusteriPortfoy' yetki_islem="randevu_olustur" class = "btn btn-sm btn-primary " href = "?modul=randevuAracAlanlar&nereden=crm&islem=ekle&crm_id=<?php echo $crm_musteri[ 'id' ]; ?>&adi=<?php echo $crm_musteri[ 'adi' ]; ?>&soyadi=<?php echo $crm_musteri[ 'soyadi' ]; ?>&cep_tel=<?php echo $crm_musteri[ 'cep_tel' ]; ?>&email=<?php echo $crm_musteri[ 'email' ]; ?>&arac_model_yili=<?php echo $crm_musteri[ 'arac_model_yili' ]; ?>&sube_id=<?php echo $crm_musteri[ 'sube_id' ]; ?>&arac_marka_id=<?php echo $crm_musteri[ 'arac_marka_id' ]; ?>&arac_tipi_id=<?php echo $crm_musteri[ 'arac_tipi_id' ]; ?>&arac_model=<?php echo $crm_musteri[ 'arac_model' ]; ?>" >
							<i class="fas fa-clock"></i>
						  </a>
						<?php } ?>
						<?php if( $crm_musteri[ 'arac_alis_satis' ] == 2 ){ ?>
						  <a modul = 'crmMusteriPortfoy' yetki_islem="randevu_olustur" class = "btn btn-sm btn-primary " href = "?modul=randevuAracSatanlar&nereden=crm&islem=ekle&crm_id=<?php echo $crm_musteri[ 'id' ]; ?>&adi=<?php echo $crm_musteri[ 'adi' ]; ?>&soyadi=<?php echo $crm_musteri[ 'soyadi' ]; ?>&cep_tel=<?php echo $crm_musteri[ 'cep_tel' ]; ?>&email=<?php echo $crm_musteri[ 'email' ]; ?>&arac_model_yili=<?php echo $crm_musteri[ 'arac_model_yili' ]; ?>&sube_id=<?php echo $crm_musteri[ 'sube_id' ]; ?>&arac_marka_id=<?php echo $crm_musteri[ 'arac_marka_id' ]; ?>&arac_tipi_id=<?php echo $crm_musteri[ 'arac_tipi_id' ]; ?>&arac_model=<?php echo $crm_musteri[ 'arac_model' ]; ?>" >
							<i class="fas fa-clock"></i>
						  </a>
						<?php } ?>
						</td>
						<td align = "center">
						  <a modul = 'crmMusteriPortfoy' yetki_islem="duzenle" class = "btn btn-sm btn-warning" href = "?modul=<?php echo $_REQUEST['modul']; ?>&islem=guncelle&id=<?php echo $crm_musteri[ 'id' ]; ?>" >
							<i class="fas fa-edit"></i>
						  </a>
						</td>
						<td align = "center">
							<button modul= 'crmMusteriPortfoy' yetki_islem="sil" class="btn btn-sm btn-danger" data-href="_modul/crmMusteriPortfoy/crmMusteriPortfoySEG.php?islem=sil&id=<?php echo $crm_musteri[ 'id' ]; ?>&modul_adi=<?php echo $_REQUEST['modul']; ?>" data-toggle="modal" data-target="#crmMusteriPortfoy_sil_onay" >
								<i class="fas fa-trash-alt"></i>
							</button>
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

