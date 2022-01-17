$(function () {
	$('.select2').select2();
	$('#datemask').inputmask('dd-mm/yyyy', { 'placeholder': 'dd/mm/yyyy' });
	$('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' });
	$('[data-mask]').inputmask();
	$('#reservation').daterangepicker();
	$('#reservationtime').daterangepicker({ timePicker: true, timePickerIncrement: 30, format: 'MM/DD/YYYY h:mm A' } );

	$('#daterange-btn').daterangepicker( {
			ranges			: {
				'Bu Gün'		: [ moment(), moment() ],
				'Dün'			: [ moment().subtract( 1, 'days' ), moment().subtract( 1, 'days' ) ],
				'Son 7 Gün' 	: [ moment().subtract( 6, 'days' ), moment() ],
				'Son 30 Gün'	: [ moment().subtract( 29, 'days' ), moment() ],
				'Bu Ay'			: [ moment().startOf( 'month' ), moment().endOf('month') ],
				'Önceki Ay'		: [ moment().subtract( 1, 'month' ).startOf( 'month' ), moment().subtract( 1, 'month' ).endOf( 'month' ) ]
			},
			locale: {
				 "applyLabel"		: "Tamam"
				,"cancelLabel"		: "İptal"
				,"clearLabel"		: "Temizle"
				,"fromLabel"		: "From"
				,"toLabel"			: "To"
				,"customRangeLabel"	: "Özel"
				,"daysOfWeek"		: [ "Pzt", "Sal", "Çrş", "Prş", "Cu", "Cmt", "Paz" ]
				,"monthNames"		: [ "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık" ]
				,"firstDay"			: 1
			}
			,startDate: moment().subtract( 29, 'days' )
			,endDate  : moment()
		}
		,function ( start, end ) {
			$( '#daterange-btn span' ).html( start.format( 'DD-MM-YYYY' ) + ' ~ ' + end.format( 'DD-MM-YYYY' ) );
			$( '#uretim_tarih_araligi' ).val( start.format( 'YYYY-MM-DD' ) + ' ~ ' + end.format( 'YYYY-MM-DD' ) );
			$( '#siparis_teslim_tarih_araligi' ).val( start.format( 'YYYY-MM-DD' ) + ' ~ ' + end.format( 'YYYY-MM-DD' ) );
			$( '#siparis_tarih_araligi' ).val( start.format( 'YYYY-MM-DD' ) + ' ~ ' + end.format( 'YYYY-MM-DD' ) );
			$( '#sevkiyat_tarih_araligi' ).val( start.format( 'YYYY-MM-DD' ) + ' ~ ' + end.format( 'YYYY-MM-DD' ) );
		}
	);
	
	$('#datepicker').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	$('#datepicker1').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	$('#datepicker2').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	$('#analiz_tarih_onanaliz').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	$('#analiz_tarih_gozetim').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	$('#siparis_teslim_tarih_kara').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	$('#siparis_teslim_fatura_tarihi_kara').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	$('#siparis_teslim_fatura_tarihi_tren').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	$('#siparis_teslim_fatura_tarihi_deniz').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	$('#siparis_teslim_cim_tarihi_tren').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	$('#siparis_teslim_tarih_tren').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	$('#siparis_teslim_tarih_deniz').datepicker({
		format: 'dd.mm.yyyy',
		todayBtn: 'linked',
		todayHighlight: 'true',
		weekStart: 1,
		autoclose:'true'
	});

	//iCheck for checkbox and radio inputs
	$('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
	  checkboxClass: 'icheckbox_minimal-blue',
	  radioClass   : 'iradio_minimal-blue'
	});
	//Red color scheme for iCheck
	$('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
	  checkboxClass: 'icheckbox_minimal-red',
	  radioClass   : 'iradio_minimal-red'
	});
	//Flat red color scheme for iCheck
	$('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
	  checkboxClass: 'icheckbox_flat-green',
	  radioClass   : 'iradio_flat-green'
	});

	//Colorpicker
	$('.my-colorpicker1').colorpicker()
	//color picker with addon
	$('.my-colorpicker2').colorpicker()

	//Timepicker
	$('.timepicker').timepicker({
		showInputs: false
	})
});
	
	/* Üretim Raporlar için tüm alanları toplu olarak seç veya seçimi bırak*/
	$( "#btn_rapor_uretim_toplu_recim" ).click( function() {
		$( "input:checkbox" ).iCheck( "check" );
		return false;

	} );

	/* Üretim Raporlar için tüm alanları toplu olarak seç veya seçimi bırak*/
	$( "#btn_rapor_uretim_toplu_recim_temizle" ).click( function() {
			$( "input:checkbox" ).iCheck( "uncheck" );
			return false;
	} );

	/* Sipariş Üretim Raporlar için tüm alanları toplu olarak seç veya seçimi bırak*/
	$( "#btn_rapor_siparis_toplu_recim" ).click( function() {
		$( "input:checkbox" ).iCheck( "check" );
		return false;

	} );

	/* Sipariş Raporlar için tüm alanları toplu olarak seç veya seçimi bırak*/
	$( "#btn_rapor_siparis_toplu_recim_temizle" ).click( function() {
			$( "input:checkbox" ).iCheck( "uncheck" );
			return false;
	} );

	/* Sevkiyat Üretim Raporlar için tüm alanları toplu olarak seç veya seçimi bırak*/
	$( "#btn_rapor_sevkiyat_toplu_recim" ).click( function() { 
		$( "input:checkbox" ).iCheck( "check" );
		return false;

	} );

	/* Sevkiyat Raporlar için tüm alanları toplu olarak seç veya seçimi bırak*/
	$( "#btn_rapor_sevkiyat_toplu_recim_temizle" ).click( function() {
			$( "input:checkbox" ).iCheck( "uncheck" );
			return false;
	} );
	
	/* Spariş Teslim Raporlar için tüm alanları toplu olarak seç veya seçimi bırak*/
	$( "#btn_rapor_siparis_teslim_toplu_recim" ).click( function() { 
		$( "input:checkbox" ).iCheck( "check" );
		return false;

	} );

	/* Spariş Teslim Raporlar için tüm alanları toplu olarak seç veya seçimi bırak*/
	$( "#btn_rapor_siparis_teslim_toplu_recim_temizle" ).click( function() {
			$( "input:checkbox" ).iCheck( "uncheck" );
			return false;
	} );