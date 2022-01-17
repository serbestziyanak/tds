<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj			= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu		= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'modul_id' ]			= $_SESSION[ 'sonuclar' ][ 'modul_id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	unset( $_SESSION[ 'aktif_tab_id' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

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

$SQL_yetki_islem_turleri = <<<SQL
SELECT
	*
FROM
	tb_yetki_islem_turleri
SQL;

$modul_id						= array_key_exists( 'modul_id' , $_REQUEST ) ? $_REQUEST[ 'modul_id' ] : 0;
$moduller						= $vt->select( $SQL_moduller );
$tum_yetki_islem_turleri		= $vt->select( $SQL_yetki_islem_turleri );
$modul_yetkili_islem_turleri	= $fn->yetkiliIslemTurleriVer( $modul_id );

?>

<div class = "row">
	<div class="col-md-6">
		<div class="card card-secondary">
			<div class="card-header with-border">
				<h3 class="card-title">Modül Listesi</h3>
			</div>
			<div class="card-body">
				<table class="table table-sm table-bordered table-hover">
					<tr>
						<th  style="width: 15px">#</th>
						<th>Adı</th>
						<th style="width: 40px">İşlemler</th>
					</tr>
					<?php $sayi = 1; foreach( $moduller[ 2 ] AS $modul ) { ?>
					<tr class="<?php if( $modul[ 'id' ] == $modul_id ) echo 'info'; ?>" >
						<td><?php echo $sayi++; ?></td>
						<td><?php echo $modul[ 'adi' ]; ?></td>
						<td align = "center">
							<a class = "btn btn-sm btn-primary btn-xs" href = "?modul=modulYetkileri&modul_id=<?php echo $modul[ 'id' ]; ?>" >
								İşlemler
							</a>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="card card-secondary">
			<div class="card-header with-border">
				<h3 class="card-title">Modülde Atanan Yetki İşlem Türleri</h3>
			</div>
			<form id = "kayit_formu" action = "_modul/modulYetkileri/modulYetkiIslemAtama.php" method = "POST">
				<div class="card-body">
					<input type = "hidden" name = "modul_id" value = "<?php echo $modul_id ?>">
					<input type = "hidden" name = "aktif_tab_id" value = "modul_yetki_islemler">
					<?php foreach( $tum_yetki_islem_turleri[ 2 ] as $islem_turu ) { ?>
						<div class="form-group">
							<div class="custom-control custom-checkbox">
									<input class="custom-control-input custom-control-input-success" type="checkbox" id="<?php echo $islem_turu[ 'id' ]; ?>" name = "chk_modul_yetki_islemler_idler[]"
										value = "<?php echo $islem_turu[ 'id' ]; ?>" 
										<?php if( in_array( $islem_turu[ 'id' ], $modul_yetkili_islem_turleri ) ) echo 'checked'; ?>
										> 
								<label for="<?php echo $islem_turu[ 'id' ]; ?>" class="custom-control-label"><?php echo $islem_turu[ 'gorunen_adi' ]; ?></label>
							</div>
						</div>
					<?php } ?>
				</div>	
				<div class="card-footer">
					<div class = "btn-toolbar">
						<button modul= 'yetkiler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
					</div>
				</div>				
			</form>
		</div>
	</div>
</div>
