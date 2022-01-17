<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
  $mesaj                 = $_SESSION[ 'sonuclar' ][ 'mesaj' ];
  $mesaj_turu            = $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
  $_REQUEST[ 'arac_yayin_id' ] = $_SESSION[ 'sonuclar' ][ 'arac_yayin_id' ];
  unset( $_SESSION[ 'sonuclar' ] );
  echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$SQL_arac_yayinlari = <<< SQL
SELECT
	ay.*
	,ayy.adi AS yayin_yeri_adi
	,ayy.logo
	,a.arac_no
	,a.id as arac_id
  ,(select dosya_adi from tb_arac_medya where arac_id = ay.arac_id and kapak_foto=1) as dosya_adi
FROM
	tb_arac_yayinlari AS ay
LEFT JOIN tb_arac_yayin_yerleri AS ayy ON ayy.id = ay.yayin_yeri_id
LEFT JOIN tb_araclar as a on a.id = ay.arac_id
WHERE
	a.aktif=1
ORDER BY a.arac_no DESC, ayy.adi
SQL;

$SQL_yayin_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_arac_yayinlari
WHERE
	id = ?
SQL;

$SQL_arac_yayin_yerleri = <<< SQL
SELECT
	*
FROM
	tb_arac_yayin_yerleri
SQL;



$arac_yayin_id     = array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$arac_yayinlari		  = $vt->select( $SQL_arac_yayinlari, array(  ) );
$arac_yayin           = $vt->selectSingle( $SQL_yayin_bilgileri, array( $arac_yayin_id ) );
$arac_yayin_yerleri	  = $vt->select( $SQL_arac_yayin_yerleri, array() );

$arac_yayin_bilgileri = array();
$islem          = array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'guncelle' )
	$arac_yayin_bilgileri = $arac_yayin[ 2 ];

//echo "limit:".$limit;
//echo "sayfa:".$sayfa;
?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="aracYayinlari_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#aracYayinlari_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>


<script>
  $(document).ready(function() {
    $('#limit-belirle').change(function() {

        $(this).closest('form').submit();

    });
});
</script>
  <div class="row">
        <div class="col-md-9">
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Araç Yayınları</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
				<table id="example2" class="table table-sm table-bordered  table-hover">
				  <thead>
					<tr>
						<th style="width: 15px">#</th>
						<th>Araç No</th>
						<th>Araç Detay</th>
						<th>Logo</th>
						<th>Yayın Yeri</th>
						<th>Yayın Tarihi</th>
						<th style="width: 20px">Yayın</th>
						<th style="width: 20px">İlan</th>
						<th style="width: 20px">Düzenle</th>
						<th style="width: 120px">Yayından Kaldır</th>
					</tr>
				  </thead>
				  <tbody>
					<?php $sayi = 1; foreach( $arac_yayinlari[ 2 ] AS $arac_yayin ) { ?>
					<tr class="<?php if( $arac_yayin[ 'yayindan_alindi' ] == 1 ){ echo 'table-danger'; } ?>">
						<td><?php echo $sayi++; ?></td>
						<td style ="font-weight:bold;"><?php echo $arac_yayin[ 'arac_no' ]; ?></td>
						<td>
							<?php if( $arac_yayin['dosya_adi'] != "" ){?>
							  <a href="http://galeri.otowow.com/<?php echo $arac_yayin['arac_no'];?>" target="_blank"><img src="arac_resimler/<?php echo $arac_yayin['arac_no'];?>/<?php echo $arac_yayin['dosya_adi'];?>" height="75"/></a>
							<?php }else{ ?>
								<a href="http://galeri.otowow.com/<?php echo $arac_yayin['arac_no'];?>" target="_blank"><img src="img/arac_kapak_yok.png" height="75"/></a>
							<?php } ?>
						</td>
						<td align = "center"><span style="display:none;"><?php echo $arac_yayin[ 'yayin_yeri_adi' ]; ?></span><a href="<?php echo $arac_yayin[ 'yayin_linki' ]; ?>" target="_blank"><img src="img/<?php echo $arac_yayin[ 'logo' ]; ?>" width="200" class="img-block" ></a></td>
						<td><?php echo $arac_yayin[ 'yayin_yeri_adi' ]; ?></td>
						<td><span style="display:none;"><?php echo $arac_yayin[ 'yayinlanma_tarihi' ]; ?></span><?php echo date('d.m.Y H:i',strtotime($arac_yayin['yayinlanma_tarihi'])); ?></td>
						<td>
							<?php if( $arac_yayin[ 'yayindan_alindi' ] == 1 ){ ?>
								<span class="right badge badge-danger">Yayında Değil</span>
							<?php }else{ ?>
								<span class="right badge badge-success">Yayında</span>
							<? } ?>
						</td>
						<td align = "center">
							<a modul= 'aracYayinlari' yetki_islem="ilan" class = "btn btn-sm btn-primary btn-xs" href = "<?php echo $arac_yayin[ 'yayin_linki' ]; ?>" target="_blank">
								İlan
							</a>
						</td>
						<td align = "center">
							<a modul= 'aracYayinlari' yetki_islem="yayin_duzenle" class = "btn btn-sm btn-success btn-xs" href = "?modul=aracYayinlari&islem=guncelle&id=<?php echo $arac_yayin[ 'id' ]; ?>" >
								Düzenle
							</a>
						</td>
						<td align = "center" valign="middle">
							<?php if( $arac_yayin[ 'yayindan_alindi' ] == 1 ){ ?>
							<a modul= 'aracYayinlari' yetki_islem="yayindan_kaldir" class = "btn btn-sm btn-success btn-xs" href = "_modul/aracYayinlari/aracYayinlariSEG.php?islem=yayindan_kaldir&id=<?php echo $arac_yayin[ 'id' ]; ?>" >
								Yayına Al
							</a>
							<?php }else{ ?>
							<a modul= 'aracYayinlari' yetki_islem="yayindan_kaldir" class = "btn btn-sm btn-danger btn-xs" href = "_modul/aracYayinlari/aracYayinlariSEG.php?islem=yayindan_kaldir&id=<?php echo $arac_yayin[ 'id' ]; ?>" >
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
                </div>
            </div>
            <!-- /.card -->
        </div>
          <!-- left column -->
	  <div class="col-md-3">
		<!-- general form elements -->
		<div class="card card-primary">
		  <div class="card-header">
			<h3 class="card-title">Araç Yayın Ekle / Güncelle</h3>
		  </div>
		  <!-- /.card-header -->
		  <!-- form start -->
		  <form id = "kayit_formu" action = "_modul/aracYayinlari/aracYayinlariSEG.php" method = "POST">
			<div class="card-body">
				<input type = "hidden" name = "arac_id" value = "<?php echo $arac_yayin_bilgileri[ 'arac_id' ]; ?>">
				<input type = "hidden" name = "id" value = "<?php echo $arac_yayin_bilgileri[ 'id' ]; ?>">
				<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
				<input type = "hidden" name = "tab_no" value = "9">
				<div class="form-group">
					<label  class="control-label">Yayın Yeri</label>
					<select name="yayin_yeri_id" class="form-control" style="width: 100%;">
					<?php foreach( $arac_yayin_yerleri[ 2 ] AS $arac_yayin_yeri ) { ?>
						<option value = "<?php echo $arac_yayin_yeri[ 'id' ]; ?>" <?php if( $arac_yayin_yeri[ 'id' ] ==  $arac_yayin_bilgileri[ 'yayin_yeri_id' ] ) echo 'selected'?>><?php echo $arac_yayin_yeri[ 'adi' ]?></option>
					<?php } ?>
					</select>
				</div>
				<div class="form-group">
					<label  class="control-label">Yayın Linki</label>
					<input type="text" class="form-control" name ="yayin_linki" value = "<?php echo $arac_yayin_bilgileri[ 'yayin_linki' ]; ?>" required placeholder="">
				</div>
			</div>
			<!-- /.card-body -->
			<div class="card-footer">
				<button modul= 'aracYayinlari' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
				<a type="reset" class="btn btn-primary btn-sm pull-right" href = "?modul=aracYayinlari&islem=ekle&id=<?php echo $arac_bilgileri[ 'id' ]; ?>&tab_no=9" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</a>
			</div>
		  </form>
		</div>
		<!-- /.card -->

					  </div>
          <!-- right column -->

        </div>
        <!-- /.row -->
