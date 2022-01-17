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
	*
FROM
	tb_araclar 
WHERE 
	aktif = 1
AND
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE sube_id in ($yetkili_subeler)
	END
ORDER BY arac_no DESC
SQL;

$SQL_arac_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_araclar
WHERE
	id = ?
AND
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE sube_id in ($yetkili_subeler)
	END
SQL;

$SQL_arac_satis_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_arac_satislari
WHERE
	arac_id = ?
SQL;

$SQL_arac_tipleri = <<< SQL
SELECT
	*
FROM
	tb_arac_tipleri 
SQL;

$SQL_arac_kasa_tipleri = <<< SQL
SELECT
	*
FROM
	tb_arac_kasa_tipleri 
SQL;

$SQL_arac_vites_tipleri = <<< SQL
SELECT
	*
FROM
	tb_arac_vites_tipleri 
SQL;

$SQL_arac_vites_sayilari = <<< SQL
SELECT
	*
FROM
	tb_arac_vites_sayilari 
SQL;

$SQL_arac_yakit_tipleri = <<< SQL
SELECT
	*
FROM
	tb_arac_yakit_tipleri 
SQL;

$SQL_arac_cekis_tipleri = <<< SQL
SELECT
	*
FROM
	tb_arac_cekis_tipleri 
SQL;

$SQL_arac_renkleri = <<< SQL
SELECT
	*
FROM
	tb_arac_renkleri
SQL;

$SQL_arac_markalari = <<< SQL
SELECT
	*
FROM
	tb_arac_markalari
SQL;

$SQL_arac_yayinlari = <<< SQL
SELECT 
	ay.*
	,ayy.adi AS yayin_yeri_adi
	,ayy.logo
FROM
	tb_arac_yayinlari AS ay
LEFT JOIN tb_arac_yayin_yerleri AS ayy ON ayy.id = ay.yayin_yeri_id
WHERE
	arac_id = ?
SQL;

$SQL_yayin_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_arac_yayinlari
WHERE
	id = ?
SQL;


$SQL_firmalar = <<< SQL
SELECT * FROM tb_firmalar
SQL;

$arac_id				= array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$arac					= $vt->selectSingle( $SQL_arac_bilgileri, array( $arac_id, $_SESSION[ 'super' ]  ) );
$arac_id				= $arac[ 2 ]['id'];
$araclar				= $vt->select( $SQL_oku, array( $_SESSION[ 'super' ]  ) );
$arac_tipleri			= $vt->select( $SQL_arac_tipleri, array() );
$arac_kasa_tipleri		= $vt->select( $SQL_arac_kasa_tipleri, array() );
$arac_vites_tipleri		= $vt->select( $SQL_arac_vites_tipleri, array() );
$arac_vites_sayilari	= $vt->select( $SQL_arac_vites_sayilari, array() );
$arac_yakit_tipleri		= $vt->select( $SQL_arac_yakit_tipleri, array() );
$arac_cekis_tipleri		= $vt->select( $SQL_arac_cekis_tipleri, array() );
$arac_renkleri			= $vt->select( $SQL_arac_renkleri, array() );
$arac_markalari			= $vt->select( $SQL_arac_markalari, array() );
$arac_yayinlari			= $vt->select( $SQL_arac_yayinlari, array( $arac_id ) );
$arac_yayini			= $vt->selectSingle( $SQL_yayin_bilgileri, array( $_REQUEST[ 'yayin_id' ] ) );
$arac_satis				= $vt->selectSingle( $SQL_arac_satis_bilgileri, array( $arac_id ) );
$arac_bilgileri			= array();
$islem					= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'satis' ){
	$arac_bilgileri = $arac[ 2 ];
	$arac_satis_bilgileri = $arac_satis[ 2 ];
}
?>
<div class="modal fade" id="arac_sec_modal">
<div class="modal-dialog">
  <div class="modal-content bg-secondary">
	<div class="modal-header">
	  <h4 class="modal-title">Detaylarını görmek istediğiniz aracı seçiniz</h4>
	  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	  </button>
	</div>
	<form id = "arac_sec_form" action = "index.php" method = "GET">
		<div class="modal-body">
			<input type = "hidden" name = "modul" value = "aracSatis">
			<input type = "hidden" name = "islem" value = "satis">
			<input type = "hidden" name = "tab_no" value = "7">
			<div class="form-group">
				<label  class="control-label">Araçlar</label>
					<select  class="form-control select2" name = "id" required>
							<option value="">Seçiniz</option>
						<?php foreach( $araclar[ 2 ] AS $arac_sec ) { ?>
							<option value = "<?php echo $arac_sec[ 'id' ]; ?>" <?php if( $arac_sec[ 'id' ] ==  $arac_bilgileri[ 'id' ] ) echo 'selected'?>><?php echo $arac_sec[ 'arac_no' ]." (".$arac_sec[ 'plaka' ].")"?></option>
						<?php } ?>
					</select>
			</div>
		</div>
		<div class="modal-footer justify-content-between">
		  <button type="button" class="btn btn-outline-light" data-dismiss="modal">Kapat</button>
		  <button  modul= 'aracSatis' yetki_islem="arac_sec" type="submit" class="btn btn-outline-light">Araç Seç</button>
		</div>
	</form>
  </div>
  <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal -->


<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="araclar_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#araclar_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>
<script>
function form_disabled( form_id ){
	var form = document.getElementById(form_id);
	var elements = form.elements;
	for (var i = 0, len = elements.length; i < len; ++i) {
		elements[i].readOnly = true;
	}
}
//form_disabled( "vekil_formu" );
</script>

        <div class="row">
          <div class="col-12">
			<h3><i class="fas fa-handshake"></i> Satış İşlemleri
			<div class="float-md-right">
				<a class = "btn btn-sm btn-secondary" style="width:200px;" href = "?modul=araclar&islem=detaylar&tab_no=1&id=<?php echo $arac_bilgileri[ 'id' ]; ?>">
					Araç Detayları Göster
				</a>
				<a class = "btn btn-sm btn-secondary" style="width:200px;" href = "?modul=prosesler&islem=prosesler&tab_no=1&id=<?php echo $arac_bilgileri[ 'id' ]; ?>">
					Prosesleri Göster
				</a>
				<button disabled class = "btn btn-sm btn-outline-secondary" style="width:200px;" href = "?modul=aracSatis&islem=satis&tab_no=7&id=<?php echo $arac_bilgileri[ 'id' ]; ?>">
					Araç Satış İşlemleri
				</button>
			</div>
			</h3>
            <div class="card card-secondary card-tabs">
              <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
				<?php
					if( $arac[ 2 ]['id'] == '' or $arac[ 2 ]['id'] == null ){
				?>
                  <li class="pt-2 px-3"><a href="#" data-toggle="modal" data-target="#arac_sec_modal"><h3 class="card-title"><?php if( $arac_bilgileri[ 'arac_no' ] !="" ){ ?><b><?php echo $arac_bilgileri[ 'arac_no' ]; ?></b> (<?php echo $arac_bilgileri[ 'plaka' ]; ?>)<?php }else{?>Araç Seç<?php } ?></h3></a></li>
                  <li class="nav-item">
                    <a class="nav-link">
						&nbsp;
					</a>					
				  </li>
				<?php
					}else{
				?>
                  <li class="pt-2 px-3"><a href="#" data-toggle="modal" data-target="#arac_sec_modal"><h3 class="card-title"><?php if( $arac_bilgileri[ 'arac_no' ] !="" ){ ?><b><?php echo $arac_bilgileri[ 'arac_no' ]; ?></b> (<?php echo $arac_bilgileri[ 'plaka' ]; ?>)<?php }else{?>Araç Seç<?php } ?></h3></a></li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 7 ) echo "active"; ?> " id="arac_bilgi7_tab" data-toggle="pill" href="#arac_bilgi7" role="tab" aria-controls="arac_bilgi7" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 7 ) echo "true"; else echo "false"; ?>">Satış / Cayma</a>
                  </li>
				  <?php if( $arac_satis_bilgileri[ 'cayma_durumu' ] == 2 ){ ?>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 1 ) echo "active"; ?> " id="arac_bilgi1_tab" data-toggle="pill" href="#arac_bilgi1" role="tab" aria-controls="arac_bilgi1" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 1 ) echo "true"; else echo "false"; ?>">Temel Bilgiler</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 2 ) echo "active"; ?> " id="arac_bilgi2_tab" data-toggle="pill" href="#arac_bilgi2" role="tab" aria-controls="arac_bilgi2" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 2 ) echo "true"; else echo "false"; ?>">Alıcı Bilgileri</a>
                  </li>
				  <?php if( $arac_satis_bilgileri[ 'alici_vekil' ] == 1 ){ ?>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 3 ) echo "active"; ?> " id="arac_bilgi3_tab" data-toggle="pill" href="#arac_bilgi3" role="tab" aria-controls="arac_bilgi3" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 3 ) echo "true"; else echo "false"; ?>">Vekalet Bilgileri</a>
                  </li>
				  <?php } ?>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 4 ) echo "active"; ?> " id="arac_bilgi4_tab" data-toggle="pill" href="#arac_bilgi4" role="tab" aria-controls="arac_bilgi4" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 4 ) echo "true"; else echo "false"; ?>">Satış Sözleşmesi</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 5 ) echo "active"; ?> " id="arac_bilgi5_tab" data-toggle="pill" href="#arac_bilgi5" role="tab" aria-controls="arac_bilgi5" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 5 ) echo "true"; else echo "false"; ?>">Evraklar</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 6 ) echo "active"; ?> " id="arac_bilgi6_tab" data-toggle="pill" href="#arac_bilgi6" role="tab" aria-controls="arac_bilgi6" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 6 ) echo "true"; else echo "false"; ?>">Yayın Kaldır</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 10 ) echo "active"; ?> " id="arac_bilgi10_tab" data-toggle="pill" href="#arac_bilgi10" role="tab" aria-controls="arac_bilgi10" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 10 ) echo "true"; else echo "false"; ?>">Dosya Kapatma</a>
                  </li>
				  <? }elseif( $arac_satis_bilgileri[ 'cayma_durumu' ] == 1 ){ ?>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 8 ) echo "active"; ?> " id="arac_bilgi8_tab" data-toggle="pill" href="#arac_bilgi8" role="tab" aria-controls="arac_bilgi8" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 8 ) echo "true"; else echo "false"; ?>">Cayma İşlemleri</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 9 ) echo "active"; ?> " id="arac_bilgi9_tab" data-toggle="pill" href="#arac_bilgi9" role="tab" aria-controls="arac_bilgi9" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 9 ) echo "true"; else echo "false"; ?>">Cayma Protokolü</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 6 ) echo "active"; ?> " id="arac_bilgi6_tab" data-toggle="pill" href="#arac_bilgi6" role="tab" aria-controls="arac_bilgi6" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 6 ) echo "true"; else echo "false"; ?>">Yayın Kaldır</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 10 ) echo "active"; ?> " id="arac_bilgi10_tab" data-toggle="pill" href="#arac_bilgi10" role="tab" aria-controls="arac_bilgi10" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 10 ) echo "true"; else echo "false"; ?>">Dosya Kapatma</a>
                  </li>
				  <? } ?>
				<?php 
					}
				?>
                </ul>
              </div>
			<?php
				if( $arac[ 2 ]['id'] == '' or $arac[ 2 ]['id'] == null ){
					echo "<h5>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class='fas fa-level-up-alt text-red'></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Araç Seçiniz</h5>";
					//exit;
				}else{
			?>
				<?php if( $arac[ 2 ]['id']*1 > 0 and $arac[ 2 ][ 'onaylandi' ] == 0 ){ ?>
				<h3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="badge badge-warning">Onay Bekleniyor</span></h3>
				<?php exit;} ?>
				
              <div class="card-body">
                <div class="tab-content" id="custom-tabs-two-tabContent">
                  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 1 ) echo "show active"; ?>" id="arac_bilgi1" role="tabpanel" aria-labelledby="arac_bilgi1_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu" action = "_modul/aracSatis/aracSatisSEG.php" method = "POST">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "1">
							<div class="form-group">
							  <label class="control-label">Satış Tarihi</label>
								<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
									<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
										<div class="input-group-text"><i class="fa fa-calendar"></i></div>
									</div>
									<input type="text" name="satis_tarihi" value="<?php if( $arac_satis_bilgileri['satis_tarihi'] !=null ) echo date('d.m.Y H:i',strtotime($arac_satis_bilgileri['satis_tarihi'])); else echo date('d.m.Y H:i'); ?>" class="form-control  datetimepicker-input" data-target="#datetimepicker1"/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Satış Fiyatı</label>
								<input type="number" step="0.01"class="form-control " name ="satis_fiyati" value = "<?php echo $arac_satis_bilgileri[ 'satis_fiyati' ]; ?>" placeholder="" required>
							</div>
							<div class="form-group">
								<label class="control-label">Komisyon Tutarı</label>
								<input type="number" step="0.01"class="form-control " name ="komisyon" value = "<?php echo $arac_satis_bilgileri[ 'komisyon' ]; ?>" placeholder="" required>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'aracSatis' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
                  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 2 ) echo "show active"; ?>" id="arac_bilgi2" role="tabpanel" aria-labelledby="arac_bilgi2_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu2" action = "_modul/aracSatis/aracSatisSEG.php" method = "POST" class="was-validated" >
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "2">
							<div class="form-group">
								<label class="control-label">T.C.Kimilk No</label>
								<input type="text" pattern="^[1-9]{1}[0-9]{9}[02468]{1}$" minlength="11" maxlength="11" class="form-control " name ="alici_tc_no" value = "<?php echo $arac_satis_bilgileri[ 'alici_tc_no' ]; ?>" placeholder="T.C.Kimilk No"  required>
								<div class="invalid-feedback">Lütfen  uygun formatta giriş yapınız.</div>
							</div>
							<div class="valid-feedback">Lütfen  uygun formatta giriş yapınız.</div>
							<div class="form-group">
								<label class="control-label">Adı</label>
								<input type="text" class="form-control " name ="alici_adi" value = "<?php echo $arac_satis_bilgileri[ 'alici_adi' ]; ?>" placeholder="Adı" required>
							</div>
							<div class="form-group">
								<label class="control-label">Soyadı</label>
								<input type="text" class="form-control " name ="alici_soyadi" value = "<?php echo $arac_satis_bilgileri[ 'alici_soyadi' ]; ?>" placeholder="Soyadı" required>
							</div>
							<div class="form-group">
								<label>Cep Telefonu:</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text"><i class="fas fa-phone"></i></span>
									</div>
									<input type="text" name ="alici_cep_tel" value = "<?php echo $arac_satis_bilgileri[ 'alici_cep_tel' ]; ?>" class="form-control " data-inputmask='"mask": "0(999) 999-9999"' data-mask required>
								</div>
								<!-- /.input group -->
							</div>
							<div class="form-group">
								<label class="control-label">E Mail</label>
								<input type="email" class="form-control " name ="alici_email" value = "<?php echo $arac_satis_bilgileri[ 'alici_email' ]; ?>" placeholder="E Mail Adresi" required>
							</div>
							<div class="form-group">
								<label class="control-label">Adres</label>
								<input type="text" class="form-control " name ="alici_adres" value = "<?php echo $arac_satis_bilgileri[ 'alici_adres' ]; ?>" placeholder="Adres" required>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body">
								  <label>Vekil</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="alici_vekil1" name="alici_vekil" value="0" <?php if( $arac_satis_bilgileri[ 'alici_vekil' ] == "0" ) echo 'checked'?> >
									<label for="alici_vekil1">
										YOK
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="alici_vekil2" name="alici_vekil" value="1" <?php if( $arac_satis_bilgileri[ 'alici_vekil' ] == "1" ) echo 'checked'?> >
									<label for="alici_vekil2">
										VAR
									</label>
								  </div>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'aracSatis' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 3 ) echo "show active"; ?>" id="arac_bilgi3" role="tabpanel" aria-labelledby="arac_bilgi3_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "vekil_formu" action = "_modul/aracSatis/aracSatisSEG.php" method = "POST" class="was-validated">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "3">
							<div class="form-group">
								<label class="control-label">T.C.Kimilk No</label>
								<input type="text" pattern="^[1-9]{1}[0-9]{9}[02468]{1}$" minlength="11" maxlength="11" class="form-control " name ="alici_vekil_tc_no" value = "<?php echo $arac_satis_bilgileri[ 'alici_vekil_tc_no' ]; ?>" placeholder="T.C.Kimilk No" required>
							</div>
							<div class="form-group">
								<label class="control-label">Adı</label>
								<input type="text" class="form-control " name ="alici_vekil_adi" value = "<?php echo $arac_satis_bilgileri[ 'alici_vekil_adi' ]; ?>" placeholder="Adı" required>
							</div>
							<div class="form-group">
								<label class="control-label">Soyadı</label>
								<input type="text" class="form-control " name ="alici_vekil_soyadi" value = "<?php echo $arac_satis_bilgileri[ 'alici_vekil_soyadi' ]; ?>" placeholder="Soyadı" required>
							</div>
							<div class="form-group">
								<label>Cep Telefonu:</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text"><i class="fas fa-phone"></i></span>
									</div>
									<input type="text" name ="alici_vekil_cep_tel" value = "<?php echo $arac_satis_bilgileri[ 'alici_vekil_cep_tel' ]; ?>" class="form-control " data-inputmask='"mask": "0(999) 999-9999"' data-mask required>
								</div>
								<!-- /.input group -->
							</div>
							<div class="form-group">
								<label class="control-label">E Mail</label>
								<input type="email" class="form-control " name ="alici_vekil_email" value = "<?php echo $arac_satis_bilgileri[ 'alici_vekil_email' ]; ?>" placeholder="E Mail Adresi" required>
							</div>
							<div class="form-group">
								<label class="control-label">Adres</label>
								<input type="text" class="form-control " name ="alici_vekil_adres" value = "<?php echo $arac_satis_bilgileri[ 'alici_vekil_adres' ]; ?>" placeholder="Adres" required>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'aracSatis' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
					<?php if( $arac_satis_bilgileri[ 'alici_vekil' ] == 0 ) echo "<script>form_disabled('vekil_formu');</script>"; ?>
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 4 ) echo "show active"; ?>" id="arac_bilgi4" role="tabpanel" aria-labelledby="arac_bilgi4_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  
					  <form id = "kayit_formu" action = "_modul/aracSatis/aracSatisSEG.php" method = "POST" class="was-validated">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "4">
							<div class="form-group">
								<a modul= 'aracSatis' yetki_islem="satis_sozlesmesi" class = "btn btn-sm btn-success" style="width:200px;" href = "_modul/aracSatis/satis_sozlesmesi.php?arac_id=<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>" target="_blank">
									Satış Sözleşmesi Görüntüle
								</a>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body ">
								  <label>Satış Sözleşmesi</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="print_satis_sozlesmesi1" name="print_satis_sozlesmesi" value="1" <?php if( $arac_satis_bilgileri[ 'print_satis_sozlesmesi' ] == "1" ) echo 'checked'?> >
									<label for="print_satis_sozlesmesi1">
										Yazdırıldı
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="print_satis_sozlesmesi2" name="print_satis_sozlesmesi" value="2" <?php if( $arac_satis_bilgileri[ 'print_satis_sozlesmesi' ] == "2" ) echo 'checked'?> >
									<label for="print_satis_sozlesmesi2">
										Yazdırılmadı
									</label>
								  </div>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'aracSatis' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 5 ) echo "show active"; ?>" id="arac_bilgi5" role="tabpanel" aria-labelledby="arac_bilgi5_tab">
					<div class="card">
					  <form id = "kayit_formu" action = "_modul/aracSatis/aracSatisSEG.php" method = "POST"  enctype="multipart/form-data">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "5">
							<input type = "hidden" name = "arac_no" value = "<?php echo $arac_bilgileri[ 'arac_no' ]; ?>">
							<div class="form-group">
								<label for="exampleInputFile">Alıcı Kimlik Fotokopi</label>
								<div class="input-group">
								  <div class="custom-file">
									<input type="file" class="custom-file-input" id="exampleInputFile" name="alici_kimlik_foto"  accept="image/x-png,image/gif,image/jpeg">
									<label class="custom-file-label" for="exampleInputFile">Dosya seçin...</label>
								  </div>
								  <div class="input-group-append">
								  <button modul= 'aracSatis' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right input-group-text"><span class="fa fa-save"></span> Kaydet</button>
								  </div>
								</div>
							</div>
							<?php if( $arac_satis_bilgileri[ 'alici_kimlik_foto' ] != null ){ ?>
							<div class="text-success text-sm"><span class="fa fa-check"></span> Bu alana fotoğraf eklenmiştir. Değiştirmek için yeni fotoğraf seçebilirsiniz.</div>
							<?php } ?>
						</div>
					  </form>
					</div>
					<!-- /.card -->
					<div class="card">
					  <form id = "kayit_formu" action = "_modul/aracSatis/aracSatisSEG.php" method = "POST"  enctype="multipart/form-data">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "5">
							<input type = "hidden" name = "arac_no" value = "<?php echo $arac_bilgileri[ 'arac_no' ]; ?>">
							<div class="form-group">
								<label for="exampleInputFile">Alıcı Vekil Kimlik Fotokopi</label>
								<div class="input-group">
								  <div class="custom-file">
									<input type="file" class="custom-file-input" id="exampleInputFile" name="alici_vekil_kimlik_foto"  accept="image/x-png,image/gif,image/jpeg">
									<label class="custom-file-label" for="exampleInputFile">Dosya seçin...</label>
								  </div>
								  <div class="input-group-append">
								  <button modul= 'aracSatis' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right input-group-text"><span class="fa fa-save"></span> Kaydet</button>
								  </div>
								</div>
							</div>
							<?php if( $arac_satis_bilgileri[ 'alici_vekil_kimlik_foto' ] != null ){ ?>
							<div class="text-success text-sm"><span class="fa fa-check"></span> Bu alana fotoğraf eklenmiştir. Değiştirmek için yeni fotoğraf seçebilirsiniz.</div>
							<?php } ?>
						</div>
					  </form>
					</div>
					<!-- /.card -->
					<div class="card">
					  <form id = "kayit_formu" action = "_modul/aracSatis/aracSatisSEG.php" method = "POST"  enctype="multipart/form-data">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "5">
							<input type = "hidden" name = "arac_no" value = "<?php echo $arac_bilgileri[ 'arac_no' ]; ?>">
							<div class="form-group">
								<label for="exampleInputFile">Vekaletname Fotoğrafı</label>
								<div class="input-group">
								  <div class="custom-file">
									<input type="file" class="custom-file-input" id="exampleInputFile" name="alici_vekaletname_foto"  accept="image/x-png,image/gif,image/jpeg">
									<label class="custom-file-label" for="exampleInputFile">Dosya seçin...</label>
								  </div>
								  <div class="input-group-append">
								  <button modul= 'aracSatis' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right input-group-text"><span class="fa fa-save"></span> Kaydet</button>
								  </div>
								</div>
							</div>
							<?php if( $arac_satis_bilgileri[ 'alici_vekaletname_foto' ] != null ){ ?>
							<div class="text-success text-sm"><span class="fa fa-check"></span> Bu alana fotoğraf eklenmiştir. Değiştirmek için yeni fotoğraf seçebilirsiniz.</div>
							<?php } ?>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
                  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 6 ) echo "show active"; ?>" id="arac_bilgi6" role="tabpanel" aria-labelledby="arac_bilgi6_tab">
					<div class="row">
					  <div class="col-md-12">
						<div class="card card-secondary">
						  <div class="card-header">
							<h3 class="card-title">Araç Yayınları</h3>
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
									<th>Araç No</th>
									<th>Logo</th>
									<th>Yayın Yeri</th>
									<th>Yayın Tarihi</th>
									<th >Yayın Durumu</th>
									<th >İlan</th>
									<th >Yayından Kaldır</th>
								</tr>
							  </thead>
							  <tbody>
								<?php $sayi = 1; foreach( $arac_yayinlari[ 2 ] AS $arac_yayin ) { ?>
								<tr class="<?php if( $arac_yayin[ 'yayindan_alindi' ] == 1 ){ echo 'table-danger'; } ?>">
									<td><?php echo $sayi++; ?></td>
									<td style ="font-weight:bold;"><?php echo $arac_bilgileri[ 'arac_no' ]; ?></td>
									<td><img src="img/<?php echo $arac_yayin[ 'logo' ]; ?>" width="80" ></td>
									<td><?php echo $arac_yayin[ 'yayin_yeri_adi' ]; ?></td>
									<td><span style="display:none;"><?php echo $arac_yayin[ 'yayinlanma_tarihi' ]; ?></span><?php echo date('d.m.Y H:i',strtotime($arac_yayin['yayinlanma_tarihi'])); ?></td>
									<td>
										<?php if( $arac_yayin[ 'yayindan_alindi' ] == 1 ){ ?>
											<span class="right badge badge-danger">Yayından Kaldırıldı</span>
										<?php }else{ ?>
											<span class="right badge badge-success">Yayında</span>
										<? } ?>
									</td>
									<td align = "center">
										<a modul= 'aracSatis' yetki_islem="ilan" class = "btn btn-sm btn-primary" href = "<?php echo $arac_yayin[ 'yayin_linki' ]; ?>" target="_blank">
											İlan
										</a>
									</td>
									<td align = "center" valign="middle">
										<?php if( $arac_yayin[ 'yayindan_alindi' ] == 1 ){ ?>
										<?php }else{ ?>
										<a modul= 'aracSatis' yetki_islem="yayindan_kaldir" class = "btn btn-sm btn-success" href = "_modul/aracSatis/aracSatisSEG.php?islem=yayindan_kaldir&yayin_id=<?php echo $arac_yayin[ 'id' ]; ?>&tab_no=6&id=<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>" >
											Yayından Kaldır
										</a>
										<? } ?>
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
					  <!-- right column -->

					</div>
					<!-- /.row -->
                  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 7 ) echo "show active"; ?>" id="arac_bilgi7" role="tabpanel" aria-labelledby="arac_bilgi7_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu" action = "_modul/aracSatis/aracSatisSEG.php" method = "POST" class="was-validated">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "7">
							<div class="form-group clearfix card">
								<div class="card-body">
								  <label>Satış / Cayma</label><br>
								  <div class="icheck-danger d-inline">
									<input required type="radio" id="cayma_durumu1" name="cayma_durumu" value="1" <?php if( $arac_satis_bilgileri[ 'cayma_durumu' ] == "1" ) echo 'checked'?> >
									<label for="cayma_durumu1">
										Cayma
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="cayma_durumu2" name="cayma_durumu" value="2" <?php if( $arac_satis_bilgileri[ 'cayma_durumu' ] == "2" ) echo 'checked'?> >
									<label for="cayma_durumu2">
										Satış
									</label>
								  </div>
								  <div class="icheck-warning d-inline">
									<input required type="radio" id="cayma_durumu3" name="cayma_durumu" value="0" <?php if( $arac_satis_bilgileri[ 'cayma_durumu' ] == "0" ) echo 'checked'?> >
									<label for="cayma_durumu3">
										Henüz bir işlem yapılmadı
									</label>
								  </div>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'aracSatis' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 8 ) echo "show active"; ?>" id="arac_bilgi8" role="tabpanel" aria-labelledby="arac_bilgi8_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu" action = "_modul/aracSatis/aracSatisSEG.php" method = "POST">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "8">
							<div class="form-group">
							  <label class="control-label">Cayma Tarihi</label>
								<div class="input-group date" id="datetimepicker2" data-target-input="nearest">
									<div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">
										<div class="input-group-text"><i class="fa fa-calendar"></i></div>
									</div>
									<input type="text" name="cayma_tarihi" value="<?php if( $arac_satis_bilgileri['cayma_tarihi'] !=null ) echo date('d.m.Y H:i',strtotime($arac_satis_bilgileri['cayma_tarihi'])); else echo date('d.m.Y H:i'); ?>" class="form-control  datetimepicker-input" data-toggle="datetimepicker" data-target="#datetimepicker2"/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Cayma Sebebi</label>
								<textarea class="form-control " name ="cayma_sebebi" placeholder="" required><?php echo $arac_satis_bilgileri[ 'cayma_sebebi' ]; ?></textarea>
							</div>
							<div class="form-group">
								<label class="control-label">Alınan Cayma Bedeli</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input type="number" class="form-control " name ="alinan_cayma_bedeli" value = "<?php echo $arac_satis_bilgileri[ 'alinan_cayma_bedeli' ]; ?>" placeholder="Örn : 500" >
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'aracSatis' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 9 ) echo "show active"; ?>" id="arac_bilgi9" role="tabpanel" aria-labelledby="arac_bilgi9_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  
					  <form id = "kayit_formu" action = "_modul/aracSatis/aracSatisSEG.php" method = "POST" class="was-validated">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "9">
							<div class="form-group">
								<a modul= 'aracSatis' yetki_islem="cayma_protokolu" class = "btn btn-sm btn-success" style="width:200px;" href = "_modul/aracSatis/cayma_protokolu.php?arac_id=<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>" target="_blank">
									Cayma Protokolü Görüntüle
								</a>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body ">
								  <label>Cayma Protokolü</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="print_cayma_protokolu1" name="print_cayma_protokolu" value="1" <?php if( $arac_satis_bilgileri[ 'print_cayma_protokolu' ] == "1" ) echo 'checked'?> >
									<label for="print_cayma_protokolu1">
										Yazdırıldı
									</label>
								  </div>
								  <div class="icheck-danger d-inline">
									<input required type="radio" id="print_cayma_protokolu2" name="print_cayma_protokolu" value="2" <?php if( $arac_satis_bilgileri[ 'print_cayma_protokolu' ] == "2" ) echo 'checked'?> >
									<label for="print_cayma_protokolu2">
										Yazdırılmadı
									</label>
								  </div>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'aracSatis' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 10 ) echo "show active"; ?>" id="arac_bilgi10" role="tabpanel" aria-labelledby="arac_bilgi10_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu" action = "_modul/aracSatis/aracSatisSEG.php" method = "POST" class="was-validated">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_satis_bilgileri[ 'arac_id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "10">
							<div class="form-group clearfix card">
								<div class="card-body">
								  <label>Dosya Kapatma</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="dosya_kapatma1" name="dosya_kapatma" value="1" <?php if( $arac_satis_bilgileri[ 'dosya_kapatma' ] == "1" ) echo 'checked'?> >
									<label for="dosya_kapatma1">
										Dosya Kapatıldı
									</label>
								  </div>
								  <div class="icheck-danger d-inline">
									<input required type="radio" id="dosya_kapatma2" name="dosya_kapatma" value="2" <?php if( $arac_satis_bilgileri[ 'dosya_kapatma' ] == "2" ) echo 'checked'?> >
									<label for="dosya_kapatma2">
										Dosya Kapatılmadı
									</label>
								  </div>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'aracSatis' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
                </div>
              </div>
              <!-- /.card -->
			<?php
				}
			?>
            </div>
          </div>		
		</div>
		<script>
			
			function form_disabled( form_adi ){
				var form = document.getElementById(form_adi);
				var elements = form.elements;
				for (var i = 0, len = elements.length; i < len; ++i) {
					elements[i].readOnly = true;
				}
			}
			//form_disabled( "kayit_formu3" );
		</script>
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
            $(function () {
                $('#datetimepicker2').datetimepicker({
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
		<script>
		$(function () {
		  bsCustomFileInput.init();
		});
		</script>

