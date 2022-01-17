<?php
$fn = new Fonksiyonlar();

$SQL_aktif_arac_sayisi = <<< SQL
SELECT
	count(*) as sayi
FROM
	tb_araclar as a
LEFT JOIN
	tb_arac_satislari AS asatis ON asatis.arac_id = a.id
WHERE
	a.aktif = 1 AND (asatis.dosya_kapatma != 1 or asatis.dosya_kapatma is null)
SQL;

$SQL_sube_aktif_arac_sayisi = <<< SQL
SELECT
	count(*) as sayi
FROM
	tb_araclar as a
LEFT JOIN
	tb_arac_satislari AS asatis ON asatis.arac_id = a.id
WHERE
	a.aktif = 1 AND (asatis.dosya_kapatma != 1 or asatis.dosya_kapatma is null) AND a.sube_id = ?
SQL;

$SQL_satilan_arac_sayisi = <<< SQL
SELECT
	count(*) as sayi
FROM
	tb_araclar as a
LEFT JOIN
	tb_arac_satislari AS asatis ON asatis.arac_id = a.id
WHERE
	a.aktif = 1 AND asatis.dosya_kapatma = 1 AND asatis.cayma_durumu = 2
SQL;

$SQL_dosya_kapatilan_arac_sayisi = <<< SQL
SELECT
	count(*) as sayi
FROM
	tb_araclar as a
LEFT JOIN
	tb_arac_satislari AS asatis ON asatis.arac_id = a.id
WHERE
	a.aktif = 1 AND asatis.dosya_kapatma = 1 AND asatis.cayma_durumu = 1
SQL;

$SQL_arac_markalari = <<< SQL
SELECT
	*
FROM
	tb_arac_markalari
SQL;

$SQL_subeler = <<< SQL
SELECT
	*
FROM
	tb_subeler
SQL;


$arac_markalari						= $vt->select( $SQL_arac_markalari, array() );
$subeler							= $vt->select( $SQL_subeler, array( ) );
$aktif_arac_sayisi					= $vt->selectSingle( $SQL_aktif_arac_sayisi, array( ) );
$sube_aktif_arac_sayisi				= $vt->selectSingle( $SQL_aktif_arac_sayisi, array( $_SESSION[ 'sube_id' ] ) );
$sube_satilan_arac_sayisi			= $vt->selectSingle( $SQL_satilan_arac_sayisi, array( $_SESSION[ 'sube_id' ] ) );
$sube_dosya_kapatilan_arac_sayisi	= $vt->selectSingle( $SQL_dosya_kapatilan_arac_sayisi, array( $_SESSION[ 'sube_id' ] ) );

?>
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?php echo $aktif_arac_sayisi[ '2' ]['sayi']; ?></h3>

                <p>Toplam Portföy</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div>
              <a href="?modul=aracListesi" class="small-box-footer">Daha fazla bilgi <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?php echo $sube_aktif_arac_sayisi[ '2' ]['sayi']; ?></h3>

                <p>Franchise Portföyü</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="?modul=aracListesi" class="small-box-footer">Daha fazla bilgi <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?php echo $sube_satilan_arac_sayisi[ '2' ]['sayi']; ?></h3>
                <p>Satılan Araçlar</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="?modul=aracSatilan" class="small-box-footer">Daha fazla bilgi <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3><?php echo $sube_dosya_kapatilan_arac_sayisi[ '2' ]['sayi']; ?></h3>

                <p>Kapanan Dosyalar</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="?modul=aracDosyaKapanan" class="small-box-footer">Daha fazla bilgi <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->
        <!-- Main row -->
        <div class="row" style="background: url('img/anasayfa_bg6.jpg');background-size: auto;">
			<div class="col-md-4">
			<br>
				<div class="card">
				  <div class="card-header">
					<h3 class="card-title"><b>Araç Ara</b></h3>
				  </div>
				  <!-- /.card-header -->
				  <!-- form start -->
				  <form id = "kayit_formu" action = "?modul=aracListesi" method = "POST">
					<div class="card-body">
						<input type = "hidden" name = "islem" value = "arama">
						<div class="form-group">
							<input type="text" class="form-control" name ="arama_arac_no" value = "" placeholder="Araç No" >
						</div>
						<div class="form-group">
							<select  class="form-control select2" name = "arama_sube_id" >
									<option value="">Şube</option>
								<?php foreach( $subeler[ 2 ] AS $sube ) { ?>
									<option value = "<?php echo $sube[ 'id' ]; ?>"><?php echo $sube[ 'adi' ]?></option>
								<?php } ?>
							</select>
						</div>
						<div class="form-group">
							<select name="arama_arac_marka_id" class="form-control  select2" style="width: 100%;" >
								<option value="">Marka</option>
							<?php foreach( $arac_markalari[ 2 ] AS $arac_marka ) { ?>
								<option value = "<?php echo $arac_marka[ 'id' ]; ?>" ><?php echo $arac_marka[ 'adi' ]?></option>
							<?php } ?>
							</select>
						</div>
						<div class="form-group">
							<input type="text" class="form-control " name ="arama_arac_modeli" value = "" placeholder="Model / Tip" >
						</div>
						<div class="form-group">
							<select name="arama_model_yili" class="form-control  select2" style="width: 100%;" >
								<option value="">Model Yılı</option>
							<?php for( $i = date( 'Y' );$i>1900;$i-- ) { ?>
								<option value = "<?php echo $i; ?>" <?php if( $i ==  $arac_bilgileri[ 'model_yili' ] ) echo 'selected'?>><?php echo $i;?></option>
							<?php } ?>
							</select>
						</div>
						<div class="row">
							<div class="form-group col-md-6">
								<input type="text" class="form-control " name ="arama_min_fiyat" value = "" placeholder="Min Fiyat" >
							</div>
							<div class="form-group col-md-6">
								<input type="text" class="form-control " name ="arama_max_fiyat" value = "" placeholder="Max Fiyat" >
							</div>
						</div>
					</div>
					<!-- /.card-body -->
					<div class="card-footer">
						<button modul= 'anasayfa' yetki_islem="ara" type="submit" style="width:100%;" class="btn btn-warning btn-sm pull-right"><span class="fa fa-search"></span> Ara</button>
					</div>
				  </form>
				</div>
				<br>
			</div>	
        </div>
        <!-- /.row (main row) -->
