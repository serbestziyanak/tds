<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$bos_grid_ise_kontrolleri_kilitle = '';

$SQL_hizli_veri_girisi_sablon_oku = <<< SQL
SELECT
	*
FROM
	tb_hizli_veri_girisi_sablonlar
WHERE
	id = ?
SQL;


$SQL_sablon_listesi = <<< SQL
SELECT
	*
FROM
	tb_hizli_veri_girisi_sablonlar
SQL;

// Tüm şablonların listesi
$sablon_listesi	= $vt->select( $SQL_sablon_listesi, array() )[ 2 ];


$sablon_id = array_key_exists( "sablon_id", $_GET ) ? $_GET[ 'sablon_id' ] : $sablon_listesi[ 0 ][ 'id' ];


// Boş grid için kotroller
$bos_grid_ise_kontrolleri_kilitle	= $sablon_id > 0 ? '' : 'disabled';
$card_baslik						= $sablon_id > 0 ? 'Sonuçlar' : 'Lütfen önce bir şablon oluşturunuz...';

// Şablondan tablo adı ve alan listesini çek
$sablon_bilgileri	= $vt->select( $SQL_hizli_veri_girisi_sablon_oku, array( $sablon_id ) )[ 2 ];
$tablo_adi			= $sablon_bilgileri[ 0 ][ 'tablo_adi' ];
$tablo_alanlar		= $sablon_bilgileri[ 0 ][ 'alanlar' ];


// Tablonun hangi alanları çekilcek.
$tablo_alan_secici	= explode( ",", $tablo_alanlar );
$tablo_alan_secici 	= "'" . implode ( "', '", $tablo_alan_secici ) . "'";


// Tablonun alanları veritipleri ve açıklaması okunuyor. Açıklamada gerekli ayarlar yer aldığı için okunuyor.
$SQL_tablo_bilgileri_oku = <<< SQL
SELECT
	 COLUMN_NAME as adi
	,DATA_TYPE as tipi
	,COLUMN_COMMENT as ayar
FROM
	INFORMATION_SCHEMA.COLUMNS
WHERE
	TABLE_SCHEMA = Database()
AND
	TABLE_NAME = '$tablo_adi'
AND
	COLUMN_NAME IN($tablo_alan_secici)
SQL;

$veriTipleri = array(
	 'int' 			=> 'number'
	,'varchar'		=> 'text'
	,'datetime'		=> 'calendar'
	,'decimal'		=> 'number'
	,'tinyint'		=> 'number'
	,'date'			=> 'calendar'
	,'text'			=> 'text'
	,'timestamp'	=> 'calendar'
);


$tablo_bilgileri	= $vt->select( $SQL_tablo_bilgileri_oku, array() );
$tablo_bilgileri	= $tablo_bilgileri[ 2 ];


$secilecek_alanlar	= $tablo_alanlar;
$SQL_veriler		= "";

// Veritabanından gelen alanlara göre yukarıda giridn sütün alanları belirlendi burada da verisi ayarlanıyor.
$SQL_veriler = "SELECT $secilecek_alanlar FROM $tablo_adi WHERE aktif = 1"; 


$tablo_veriler		= $vt->select( $SQL_veriler, array() );
$tablo_veriler		= $tablo_veriler[ 2 ];


$tarih_alani_options = array(
	 'format'			=> 'DD-MM-YYYY'
	,'months'			=> array('Oc', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara')
	,'weekdays'			=> array( 'Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar')
	,'weekdays_short'	=> Array( 'P', 'S', 'Ç', 'P', 'C', 'Ct', 'P' )
);

$grid_kolonlar = array();
foreach( $tablo_bilgileri as $bilgi ) {
	$satir = array(
		 'type'		=> $veriTipleri[ $bilgi[ 'tipi' ] ]
		,'title'	=> strlen( $bilgi[ 'ayar' ] ) > 0 ? explode( "-", $bilgi[ 'ayar' ] )[ 0 ] : $bilgi[ 'adi' ]
		,'name'		=> $bilgi[ 'adi' ]
	);

	if( $bilgi[ 'adi' ] == 'id' ){
		$satir[ 'readonly' ] = true;
	} 
	if( $satir[ 'type' ] == 'calendar' ) $satir[ 'options' ] = $tarih_alani_options;

	if( count( explode( "-", $bilgi[ 'ayar' ] ) ) > 1 ) {
		$kaynak_tablo			= explode( "-", $bilgi[ 'ayar' ] )[ 1 ];
		$satir[ 'type' ]		= 'dropdown';
		
		// Eğer çoklu seçim isniyorsa multiple:true yapılır. Dropdown'un verisindeki id'ler arasında 2;3;43;2 gibi noktalı virgül bırakılmalıdır.
		// $satir[ 'multiple' ]	= true;
		$dropdown_kayitlar		= array();

		$SQL_dropdown_veri_oku	= "SELECT id,adi FROM $kaynak_tablo";
		$sonuclar = $vt->select( $SQL_dropdown_veri_oku, array() )[ 2 ];

		foreach( $sonuclar AS $sonuc ) {
			$kayit = array( 'id' => $sonuc[ 'id' ], 'name' => $sonuc[ 'adi' ] );
			$dropdown_kayitlar[] = $kayit;
		}
		$satir[ 'source' ] = $dropdown_kayitlar;
	}
	$grid_kolonlar[] = $satir;
}

?>
<div class="row">
	<div class="col-md-12" id = "ust_container">
		<div class="card card-default" id = "grid_card">
			<div class="card-header">
				<h3 class="card-title pull-right"><?php echo $card_baslik?></h3>
				<div class="card-tools">
					<div class="input-group input-group-sm">
						<select class="form-control form-control float-right" id = "sablon_id" <?php echo $bos_grid_ise_kontrolleri_kilitle; ?> >
							<?php foreach( $sablon_listesi as $sablon ) { ?>
								<option value = "<?php echo $sablon[ 'id' ]; ?>" <?php if( $sablon_id == $sablon[ 'id' ] ) echo 'selected'; ?>><?php echo $sablon[ 'adi' ]; ?></option>
							<?php } ?>
						</select>&nbsp;
						<button type="button" class="btn btn-secondary btn-sm float-right" onclick = "yeniKayitEkle()" <?php echo $bos_grid_ise_kontrolleri_kilitle; ?>><i class="fas fa-user-plus"></i> &nbsp;Yeni kayıt ekle</button>&nbsp;
						<a href = "?modul=sablonlar" class="btn btn-info btn-sm float-right"><i class="fas fa-magic"></i> &nbsp;Yeni şablon oluştur</a>
					</div>
				</div>
			</div>
			<div class="card-body">
				<div id="grd_div"></div>
			</div>
			<div class="card-footer text-muted">
				<button type="button" class="btn btn-success btn-sm float-right" onclick = "kaydet()" <?php echo $bos_grid_ise_kontrolleri_kilitle; ?>><i class="fas fa-save"></i> Kaydet</button>
				<input type = "hidden" name = "txt_tablo_adi" id = "txt_tablo_adi" value = "<?php echo $tablo_adi; ?>">
			</div>
		</div>
	</div>
</div>

<script>
	let data = <?php echo json_encode( $tablo_veriler ); ?>;

	//Global değişkenler.
	let sat		= data.length;
	let sut		= Object.keys( data[ 0 ] ).length;
	let grid	= null;
	//let harfler	= [ "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "W", "Z" ];
	
	/* CRUD işlemleri */
	let eklenenKayitlar			= [];
	let guncellenenKayitlar_id	= [];
	let silinenKayitlar_id		= [];


	onbeforedeleterow = function( el, rowNumber, numOfRows ) {
		grid = el.jexcel;
		rows = grid.getSelectedRows( true );
		for( var i = 0; i < rows.length; i++ ) {
			data = grid.getRowData( rows[ i ] );
			if( data[ 0 ] * 1 > 0 ) silinenKayitlar_id.push( data[ 0 ] );
		}
		return true;
	}


	onafterchanges = function( el, records ) {
		grid =  document.getElementById( 'grd_div' ).jexcel;
		for( var i = 0; i < records.length; i++ ) {
			rowNumber = records[ i ].row;
			data = grid.getRowData( rowNumber );
			rowNumber++;
			if( data[ 0 ] > 0 && !guncellenenKayitlar_id.includes( data[ 0 ] ) ) {
				guncellenenKayitlar_id.push( data[ 0 ] );
				//for( var j = 0; j < grid.getConfig().columns.length; j++ ) grid.setStyle( harfler[ j ] + rowNumber, 'background-color', '#FCE5C5' );
				grid.setComments( "A" + rowNumber, "Değişti" );
			}
		}
	}


	// Boş bir kayıt ekle
	function yeniKayitEkle() {
		grid = document.getElementById( 'grd_div' ).jexcel;
		rowNumber = grid.getJson().length + 1;
		
		// Direk dizi verince bir satır ekler.
		// Bu satırın datası da [0] dizisidir.
		// Yani sadece id=0 olan boş bir kayıt eklemiş olur.
		// id: [0]
		// konum : 0 Satır
		// true : Öncesine ekle 
		grid.insertRow( [ 0 ], 0, true );
	}


	// Grid'i Reposnsive hale getir.
	function gridiResponsiveYap( grid, sut ) {
		alert("gridiResponsiveYap");
		let parent_container_width = $( "#grd_div" ).parents( ".card" ).width();
		toplam_genislik = parent_container_width - 95;
		ilk_sutun_genisligi = ( toplam_genislik * 5 ) / 100;
		kalan_genislik = toplam_genislik - ilk_sutun_genisligi
		diger_sutun_genisligi = kalan_genislik / ( sut - 1 );
		for( var i = 0; i <= sut; i++ ) grid.setWidth ( i, i == 0 ? ilk_sutun_genisligi : diger_sutun_genisligi );
	}


	$( document ).ready( function() {
		alert("ready");
		grid = jspreadsheet( document.getElementById( 'grd_div' ), {
			 minDimensions:[ sut, sat ]
			,json: data
			,pagination:20
			,toolbar:false
			,search: true
			// Son satırda entere basınca satır eklemesin
			,allowManualInsertRow: false
			,contextMenu	: function() {
				return false;
			}
			,paginationOptions: [ 5, 10, 20, 50, 100, 10000 ]
			,columns: <?php echo json_encode( $grid_kolonlar ); ?>
			,onbeforedeleterow	: onbeforedeleterow
			,onafterchanges		: onafterchanges
			,license: 'OTQ4ZTI1ZDdhNWE0MWY5M2VkNTA4ZmJhOTcwMTQwYTQ4MWZjNmFkYWI1NmFjMGUxNzcyNTAxZmE5ODY3MzkyNTk1NjMyM2E4Y2U0ZDUyZGU2ZDJhODA1MGY0ZmEyYzY5MjUwMzg3MzVjN2Y1NDg2YjQ1ZjkxMjM4NWI1N2VmZDcsZXlKdVlXMWxJam9pY0dGMWJDNW9iMlJsYkNJc0ltUmhkR1VpT2pFMk9ERXhOamMyTURBc0ltUnZiV0ZwYmlJNld5SnFjMmhsYkd3dWJtVjBJaXdpYW5Od2NtVmhaSE5vWldWMExtTnZiU0lzSW1OellpNWhjSEFpTENKMVpTNWpiMjB1WW5JaUxDSjFibWwwWldRdVpXUjFZMkYwYVc5dUlpd2ljMkZ2Y205amF5NWpiMjBpTENKMVpTNWpiMjB1WW5JaUxDSjFibWwwWldRdVpXUjFZMkYwYVc5dUlpd2liRzlqWVd4b2IzTjBJbDBzSW5Cc1lXNGlPaUl6SWl3aWMyTnZjR1VpT2xzaWRqY2lMQ0oyT0NJc0luQmhjbk5sY2lJc0luTm9aV1YwY3lJc0ltWnZjbTF6SWl3aWNtVnVaR1Z5SWl3aVptOXliWFZzWVNJc0ltTm9ZWEowY3lJc0ltWnZjbTF6SWl3aVptOXliWFZzWVNJc0luSmxibVJsY2lJc0luQmhjbk5sY2lJc0ltbHRjRzl5ZEdWeUlsMTk='
			,text:{
				 noRecordsFound:'Kayıt Bulunamadı'
				,showingPage:'Toplam {1} sayfadan {0}. Sayfa gösteriliyor'
				,show:'Göster '
				,entries:' Kayıt'
				,areYouSureToDeleteTheSelectedRows:'Seçili satırları silmek istediğinize emin misiniz?'
				,search:'Ara'
				,calendarUpdateButtonText:'Güncelle'
			}
		} );

		gridiResponsiveYap( grid, sut );
	} );

	$( window ).on( 'resize', function() {
		alert("resize");
		gridiResponsiveYap( grid, sut );
	} );


	//Şablon seçme select'i eleman seçince sayfayı yeni şablon_id ile yenile
	$( "#sablon_id" ).change( function() {
		sablon_id 	= $(this).val();
		url			= '?modul=hizliverigirisi&sablon_id=' + sablon_id;
		window.location.href = url;
	} );



	function kaydet() {
		
		let grid				= document.getElementById( 'grd_div' ).jexcel;
		let tum_kayitlar		= grid.getJson();
		let guncellenenKayitlar	= [];
		let grid_card			= $( '#grid_card' );
		
		
		grid_card.append('<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i> &nbsp;&nbsp;&nbsp;Kaydediliyor...</div>');

		/* 
		*	Eklenen kaytıtların listesini hazrla.
		*	Eğer bir kaydın id'si sıfır ise demekki yeni eklenen bir kayıttır.
		*/
		for( var i = 0; i < tum_kayitlar.length; i++ ) {
			if( tum_kayitlar[ i ].id ==  0 ) {
				eklenenKayitlar.push( tum_kayitlar[ i ] );
			};
		}


		/* 
		*	Silinen kayıtların listesini hazırla
		*	"silinenKayitlar" dizisindeki kayıtlar gerçekten silinmiş mi? kontrol et.
		*	Eğer grid'te bulunan herhangi bir kayıt silinenKayitlar dizisinde varsa demekki kayıt geri alınmış.
		*	O halde bu kayı silinenKayitlar dizisinden çıkar.
		*/
		for( var i = 0; i < tum_kayitlar.length; i++ ) {
			var index = silinenKayitlar_id.indexOf( tum_kayitlar[ i ].id );
			if ( index !== -1 ) {
				silinenKayitlar_id.splice( index, 1 );
			}
		}


		/* Güncellenen kayıtların listesini hazırla */
		for( var i = 0; i < tum_kayitlar.length; i++ ) {
			var index = guncellenenKayitlar_id.indexOf( tum_kayitlar[ i ].id );
			if ( index !== -1 ) {
				guncellenenKayitlar.push( tum_kayitlar[ i ] );
			}
		}

		$.ajax( {
			 url		: "_modul/hizliverigirisi/hizliverigirisiSEG.php"
			,cache		: false
			,method		: 'POST'
			,data		: {
				 eklenenKayitlar		: eklenenKayitlar
				,silinenKayitlar		: silinenKayitlar_id
				,guncellenenKayitlar	: guncellenenKayitlar
				,tabloAdi				: $( '#txt_tablo_adi' ).val()
			}
			,success	: function( sonuc ) {
				$( ".overlay" ).remove();
				//location.reload();
			}
		} );
	}

</script>
