<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj			= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu		= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'rol_id' ]			= $_SESSION[ 'sonuclar' ][ 'rol_id' ];
	$_REQUEST[ 'aktif_tab_id' ]		= $_SESSION[ 'aktif_tab_id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	unset( $_SESSION[ 'aktif_tab_id' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

/* Rol modul comboları ve ilgili tab'ın seçili olması için kullanılıyor. Tab1*/
$rol_id			= isset( $_SESSION[ 'rol_id' ] )		? $_SESSION[ 'rol_id' ]			: 0;
$modul_id		= isset( $_SESSION[ 'modul_id' ] )		? $_SESSION[ 'modul_id' ]		: 0;
if( isset( $_SESSION[ 'aktif_tab_id' ] ) ) {
	$aktif_tab_id = $_SESSION[ 'aktif_tab_id' ];
	unset( $_SESSION[ 'aktif_tab_id' ] );
} else if( isset( $_REQUEST[ 'aktif_tab_id' ] ) ) {
	$aktif_tab_id = $_REQUEST[ 'aktif_tab_id' ];
} else {
	$aktif_tab_id = 'rol_ekle_guncelle';
}

$SQL_roller = <<< SQL
SELECT
	*
FROM
	tb_roller
ORDER BY id
SQL;

$SQL_modul_islem_turleri = <<< SQL
SELECT
	 m.id
	, m.adi
	,( SELECT GROUP_CONCAT( islem_turu_id SEPARATOR ',' ) FROM tb_modul_yetki_islemler AS myi WHERE m.id = myi.modul_id ) AS islem_turleri
FROM
	tb_modul AS m
SQL;

$SQL_moduller = <<< SQL
SELECT
	*
FROM
	tb_modul
WHERE
	kategori = 0 AND menude_goster = 1
SQL;

$SQL_tum_depolar = <<<SQL
SELECT
	 id
	,adi
FROM
	tb_depolar
SQL;

$SQL_tum_firmalar = <<<SQL
SELECT
	 id
	,adi
FROM
	tb_firmalar
SQL;

$id			= array_key_exists( 'rol_id' , $_REQUEST ) ? $_REQUEST[ 'rol_id' ] : 0;
$islem		= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

$roller		= $vt->select( $SQL_roller );
$moduller	= $vt->select( $SQL_moduller );

$tum_depolar		= $vt->select( $SQL_tum_depolar );
$tum_firmalar		= $vt->select( $SQL_tum_firmalar );

/*
Aşağıda Fonkiyonlar sınıfında tanımlanmış iki fonksşyon kullanılmaktadır.
#params
@$id rol id dir.
@true sadece id dizini verir. false olursa tüm yetki dahilindeki tüm kayıtların tüm alanlarını verir.
*/
$yetkili_depo_idler 	= $fn->yetkiliDepoVer( $id, true );
$yetkili_firma_idler 	= $fn->yetkiliFirmaVer( $id, true );

?>

<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="rol_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Lütfen Dikkat!</h4>
			</div>
			<div class="modal-body">
				Bu rolü <b>Silmek</b> istediğinize emin misiniz?<br>Bu role sahip kullanıcılar için <b>Varsayılan</b> Rol ataması yapılacak. Yine de silmek istiyor musunuz?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
				<a class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>
	$( '#rol_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>
        <div class="row">
          <div class="col-12 col-sm-12">
            <div class="card card-primary card-tabs">
              <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs"  id="yetkiSekmeler" role="tablist">
					<li class="nav-item">
						<a class="nav-link <?php if( $aktif_tab_id == 'rol_ekle_guncelle' ) echo 'active'; ?>" id="genel-tab" data-toggle="tab" href="#rol_ekle_guncelle" role="tab" aria-controls="roller_tab" aria-selected="true"><b>Roller</b></a>
					</li>
					<li class="nav-item">
						<a class="nav-link <?php if( $aktif_tab_id == 'rol_yetkileri' ) echo 'active'; ?>" id="genel-tab" data-toggle="tab" href="#rol_yetkileri" role="tab" aria-controls="genel" aria-selected="false"><b>Yetkiler</b></a>
					</li>
					<!--li class="nav-item">
						<a class="nav-link <?php if( $aktif_tab_id == 'rol_depo_yetkileri' ) echo 'active'; ?>" id="genel-tab" data-toggle="tab" href="#rol_depo_yetkileri" role="tab" aria-controls="genel" aria-selected="false"><b>Depo Yetkileri</b></a>
					</li>
					<li class="nav-item">
						<a class="nav-link <?php if( $aktif_tab_id == 'rol_firma_yetkileri' ) echo 'active'; ?>" id="genel-tab" data-toggle="tab" href="#rol_firma_yetkileri" role="tab" aria-controls="genel" aria-selected="false"><b>Firma Yetkileri</b></a>
					</li-->
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content" id="custom-tabs-one-tabContent">
					<div class="tab-pane fade show <?php if( $aktif_tab_id == 'rol_ekle_guncelle' )	echo 'active'; else echo 'pane'; ?>" id="rol_ekle_guncelle" role="tabpanel" aria-labelledby="roller_tab">
						<div class="row">
							<div class="col-md-4">
								<div class="card card-secondary">
									<div class="card-header with-border">
										<h3 class="card-title">Rol Listesi</h3>
									</div>
									<div class="card-body">
										<table class="table table-sm table-striped table-bordered table-hover">
											<tr>
												<th  style="width: 15px">#</th>
												<th>Adı</th>
												<th style="width: 40px">Düzenle</th>
												<th style="width: 40px">Sil</th>
											</tr>
											<?php $sayi = 1; foreach( $roller[ 2 ] AS $rol ) { ?>
											<tr>
												<td><?php echo $sayi++; ?></td>
												<td><?php echo $rol[ 'adi' ]; ?></td>
												<td align = "center">
												<?php 
														/* Varsayılan rol düzenlenemesin */
													if( $rol[ 'varsayilan' ] * 1 == 0 ) { ?>
													<a modul= 'yetkiler' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=yetkiler&rol_adi=<?php echo $rol[ 'adi' ]; ?>&islem=guncelle&rol_id=<?php echo $rol[ 'id' ]; ?>" >
														Düzenle
													</a>
												<?php } ?>
												</td>
												<td align = "center">
													<?php 
														/* Varsayılan rol silinemesin */
													if( $rol[ 'varsayilan' ] * 1 == 0 ) { ?>
													<button modul= 'yetkiler' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/yetkiler/rollerSEG.php?islem=sil&rol_id=<?php echo $rol[ 'id' ]; ?>" data-toggle="modal" data-target="#rol_sil_onay" >Sil</button>
												<?php } ?>
												</td>
											</tr>
											<?php } ?>
										</table>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="card card-secondary">
									<div class="card-header with-border">
										<h3 class="card-title">Rol Ekle - Düzenle</h3>
									</div>
									<form class="form-horizontal" id = "kayit_formu" action = "_modul/yetkiler/rollerSEG.php" method = "POST">
										<div class="card-body">
											<input type = "hidden" name = "rol_id" value = "<?php echo $id ?>">
											<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
											<input type = "hidden" name = "aktif_tab_id" value = "rol_ekle_guncelle">
											<div class="form-group">
												<label  class="col-sm-3 control-label">Rolün Adı</label>
												<div class="col-sm-9">
													<input type="text" class="form-control input-sm" name ="yetkiler_rol_adi" value = "<?php echo $_REQUEST[ 'rol_adi' ]; ?>">
												</div>
											</div>
										</div>
										<div class="card-footer">
											<div class = "btn-toolbar">
												<button modul= 'yetkiler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
												<a type="reset" class="btn btn-primary btn-sm pull-right" href = "?modul=yetkiler&islem=ekle" ><span class="fa fa-plus"></span> Temizle / Yeni Rol</a>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>						
					</div>
					<div class="tab-pane <?php if( $aktif_tab_id == 'rol_yetkileri' )		echo 'active'; else echo 'pane'; ?>" id="rol_yetkileri" role="tabpanel" aria-labelledby="ekle-tab">
						<div class="col-md-5">
							<div class="card card-secondary">
								<div class="card-header with-border">
									<h3 class="card-title">Rol ve Modül Yetki İşlemleri</h3>
								</div>
								<form class="form-horizontal" id = "frm_yetki_islemler" method = "POST">
									<div class="card-body">
										<input type = "hidden" name = "aktif_tab_id" value = "rol_yetkileri">
										<div class="form-group">
											<label  class="col-sm-3 control-label">Roller</label>
											<div class="col-sm-9">
												<select class="form-control input-sm" id = "cmb_roller">
													<?php foreach( $roller[ 2 ] AS $satir ) { ?>
													<option value = "<?php echo $satir[ 'id' ];?>" <?php if( $satir[ 'id' ] == $rol_id ) echo 'selected'?>><?php echo $satir[ 'adi' ]?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label  class="col-sm-3 control-label">Modüller</label>
											<div class="col-sm-9">
												<select class="form-control input-sm" id = "cmb_moduller">
													<?php foreach( $moduller[ 2 ] AS $satir ) { ?>
													<option value = "<?php echo $satir[ 'id' ];?>" <?php if( $satir[ 'id' ] == $modul_id ) echo 'selected'?>><?php echo $satir[ 'adi' ]?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label  class="col-sm-3 control-label">İşlemler</label>
											<div class="col-sm-9">
												<div class="panel panel-default" style="overflow-y:auto;">
												
													<!-- Default panel contents -->
													<div class="panel-heading">Listeden işlem seçin</div>
													<!-- List group -->
													
													<ul class="list-group" id="list_rol_modul_yetki_islemler">
														<!-- Buraya ajax_islemler.js den data yetki işlemler geliyor -->
													</ul>
												</div>
											</div>
										</div>
									</div>
									<div class="card-footer">
										<div class = "btn-toolbar">
											<button 
												type="button"
												class="btn btn-success btn-sm pull-right"  
												id = "btn_rol_yetki_kaydet"
												modul = "yetkiler"
												yetki_islem = "kaydet"
											><span class="fa fa-save"></span> Kaydet</button>
											<a 
												type="button"
												class="btn btn-primary btn-sm pull-right"  
												href = "?modul=yetkiler&aktif_tab_id=rol_yetkileri"
											><span class="fa fa-refresh"></span> Vazgeç</a>
										</div>
									</div>								
								</form>
							</div>
						</div>
					</div>

					<div class="tab-pane <?php if( $aktif_tab_id == 'rol_depo_yetkileri' )	echo 'active'; else echo 'pane'; ?>" id="rol_depo_yetkileri" role="tabpanel" aria-labelledby="ekle-tab">
					<div class="row">
						<div class="col-md-4">
							<div class="card card-warning">
								<div class="card-header with-border">
									<h3 class="card-title">Rol Listesi</h3>
								</div>
								<div class="card-body">
									<table class="table table-bordered table-hover">
										<tr>
											<th  style="width: 15px">#</th>
											<th>Adı</th>
											<th style="width: 40px">Depolar</th>
										</tr>
										<?php $sayi = 1; foreach( $roller[ 2 ] AS $rol ) { ?>
										<tr>
											<td><?php echo $sayi++; ?></td>
											<td><?php echo $rol[ 'adi' ]; ?></td>
											<td align = "center">
											<?php 
													/* Varsayılan rol düzenlenemesin */
												if( $rol[ 'varsayilan' ] * 1 == 0 ) { ?>
												<a modul= 'yetkiler' yetki_islem="duzenle" class = "btn btn-sm btn-primary btn-xs" href = "?modul=yetkiler&aktif_tab_id=rol_depo_yetkileri&rol_adi=<?php echo $rol[ 'adi' ]; ?>&rol_id=<?php echo $rol[ 'id' ]; ?>" >
													Yetkili Depolar
												</a>
											<?php } ?>
											</td>
										</tr>
										<?php } ?>
									</table>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card card-primary">
								<div class="card-header with-border">
									<h3 class="card-title">Rol'ün yetkili olduğu depolar</h3>
								</div>
								<div class="card-body">
									<form class="form-horizontal" id = "kayit_formu" action = "_modul/rolDepoYetkiAtama.php" method = "POST">
										<input type = "hidden" name = "rol_id" value = "<?php echo $id ?>">
										<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
										<input type = "hidden" name = "aktif_tab_id" value = "rol_depo_yetkileri">
										<?php foreach( $tum_depolar[ 2 ] as $td ) { ?>
											<div class="form-group">
												<label  class="col-sm-2 control-label"></label>
												<div class="col-sm-10">
													<label>
														<input type="checkcard" class="minimal" name = "chk_depo_idler[]"
															value = "<?php echo $td[ 'id' ]; ?>" 
															<?php if( in_array( $td[ 'id' ], $yetkili_depo_idler ) ) echo 'checked'; ?>
															> <?php echo $td[ 'adi' ]; ?>
													</label>
												</div>
											</div>
										<?php } ?>
										<div class="card-footer">
											<div class = "btn-toolbar">
												<button modul= 'yetkiler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					
					</div>
					</div>

					<div class="tab-pane <?php if( $aktif_tab_id == 'rol_firma_yetkileri' )	echo 'active'; else echo 'pane'; ?>" id="rol_firma_yetkileri" role="tabpanel" aria-labelledby="ekle-tab">
					<div class="row">
						<div class="col-md-4">
							<div class="card card-warning">
								<div class="card-header with-border">
									<h3 class="card-title">Rol Listesi</h3>
								</div>
								<div class="card-body">
									<table class="table table-bordered table-hover">
										<tr>
											<th  style="width: 15px">#</th>
											<th>Adı</th>
											<th style="width: 40px">Depolar</th>
										</tr>
										<?php $sayi = 1; foreach( $roller[ 2 ] AS $rol ) { ?>
										<tr>
											<td><?php echo $sayi++; ?></td>
											<td><?php echo $rol[ 'adi' ]; ?></td>
											<td align = "center">
											<?php 
													/* Varsayılan rol düzenlenemesin */
												if( $rol[ 'varsayilan' ] * 1 == 0 ) { ?>
												<a modul= 'yetkiler' yetki_islem="duzenle" class = "btn btn-sm btn-primary btn-xs" href = "?modul=yetkiler&aktif_tab_id=rol_firma_yetkileri&rol_adi=<?php echo $rol[ 'adi' ]; ?>&rol_id=<?php echo $rol[ 'id' ]; ?>" >
													Yetkili Firmalar
												</a>
											<?php } ?>
											</td>
										</tr>
										<?php } ?>
									</table>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card card-primary">
								<div class="card-header with-border">
									<h3 class="card-title">Rol'ün yetkili olduğu firmalar</h3>
								</div>
								<div class="card-body">
									<form class="form-horizontal" id = "kayit_formu" action = "_modul/rolFirmaYetkiAtama.php" method = "POST">
										<input type = "hidden" name = "rol_id" value = "<?php echo $id ?>">
										<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
										<input type = "hidden" name = "aktif_tab_id" value = "rol_firma_yetkileri">
										<?php foreach( $tum_firmalar[ 2 ] as $tf ) { ?>
											<div class="form-group">
												<label  class="col-sm-2 control-label"></label>
												<div class="col-sm-10">
													<label>
														<input type="checkcard" class="minimal" name = "chk_firma_idler[]"
															value = "<?php echo $tf[ 'id' ]; ?>" 
															<?php if( in_array( $tf[ 'id' ], $yetkili_firma_idler ) ) echo 'checked'; ?>
															> <?php echo $tf[ 'adi' ]; ?>
													</label>
												</div>
											</div>
										<?php } ?>
										<div class="card-footer">
											<div class = "btn-toolbar">
												<button modul= 'yetkiler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					</div>
				


                </div>
              </div>
              <!-- /.card -->
            </div>
          </div>
        </div>
        

