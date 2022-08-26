<?php
if ( session_status() == PHP_SESSION_NONE ) {
	session_start();
}
	define("ADMIN", true);
	error_reporting( E_ALL );
	// Bu sayfa için önbellekleme yapmamaya zorla
	header( 'Pragma: no-cache' );
	header( 'Cache-Control: no-cache, must revalidate' );
	
	if( isset( $_REQUEST['sistem_yil'] ) ){
		$_SESSION['yil'] = $_REQUEST['sistem_yil'];
	}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>YYÜ - EYPS </title>
<link rel="icon" href="dist/img/AdminLTELogo.png" type="image/x-icon" />
<!-- Google Font: Source Sans Pro -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome -->
<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
<!-- DataTables -->
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<!-- Ionicons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<!-- Tempusdominus Bootstrap 4 -->
<link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
<!-- Select2 -->
<link rel="stylesheet" href="plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<!-- iCheck -->
<link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<!-- JQVMap -->
<link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="dist/css/adminlte.css">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
<!-- Daterange picker -->
<link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
<!-- summernote -->
<link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
  <!-- dropzonejs -->
<link rel="stylesheet" href="plugins/dropzone/min/dropzone.min.css">
<!-- Ekko Lightbox -->
<link rel="stylesheet" href="plugins/ekko-lightbox/ekko-lightbox.css">
<!-- bs-stepper -->
<link rel="stylesheet" href="plugins/bs-stepper/css/bs-stepper.min.css">
<!-- SweetAlert2 -->
<link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
<!-- Toastr -->
<link rel="stylesheet" href="plugins/toastr/toastr.min.css">
<!-- fullCalendar -->
<link rel="stylesheet" href="plugins/fullcalendar/main.css">
<link rel="stylesheet" href="https://cdn.datatables.net/colreorder/1.5.6/css/colReorder.dataTables.min.css">

<link rel="stylesheet" href="_css/tds.css">
<link rel="stylesheet" href="_css/agaclandirma.css">

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->

<!-- Hızlı veri girişi kütüphaneleri -->
<script src="jspreadsheet/jspreadsheet.js"></script>
<script src="jspreadsheet/jsuites.js"></script>
<link rel="stylesheet" href="jspreadsheet/jsuites.css" type="text/css" />
<link rel="stylesheet" href="jspreadsheet/jspreadsheet.css" type="text/css" />
<link rel="stylesheet" href="jspreadsheet/custom.css" type="text/css" />

<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="plugins/jszip/jszip.min.js"></script>
<script src="plugins/pdfmake/pdfmake.min.js"></script>
<script src="plugins/pdfmake/vfs_fonts.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<!-- Select2 -->
<script src="plugins/select2/js/select2.full.min.js"></script>
<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/fullcalendar/main.js"></script>
<script src="plugins/fullcalendar/locales/tr.js"></script>
<script src="plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="plugins/inputmask/jquery.inputmask.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<script src="plugins/summernote/lang/summernote-tr-TR.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>
<script	src="_cekirdek/sistem_ayar.php"></script>
<script src="_modul/ajax/ajax_islemler.js"></script>
<!-- dropzonejs -->
<script src="plugins/dropzone/min/dropzone.min.js"></script>
<!-- Ekko Lightbox -->
<script src="plugins/ekko-lightbox/ekko-lightbox.min.js"></script>
<!-- SweetAlert2 -->
<script src="plugins/sweetalert2/sweetalert2.min.js"></script>
<!-- Toastr -->
<script src="plugins/toastr/toastr.min.js"></script>
<script src="_js/mesaj.js"></script>
<script src="_js/dropzoneYukle.js"></script>

</head>
<?php 
	$SQL_modul_klasor = <<< SQL
	SELECT
		klasor
	FROM
		tb_modul
	WHERE
		modul = ?
	SQL;

	if( array_key_exists( 'giris_var', $_SESSION ) && $_SESSION[ 'giris_var' ] == 'evet' ) { ?>
		<body class="hold-transition sidebar-mini layout-fixed text-sm">
			<div class="wrapper">
				<?php include "_modul/ustBar.php"; ?>
				<?php include "_modul/solMenu.php"; ?>

				<?php
						if( array_key_exists( 'modul', $_REQUEST ) && isset( $_REQUEST[ 'modul'  ]  ) ) {
							if( !$fn->yetkiKontrol( $_SESSION[ 'kullanici_id' ], $_REQUEST[ 'modul' ], 'goruntule' ) ) {
								$modul = 'yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
							} else {
								/* Modüllerin bulunduğu klasörler modul ismi ile aynı olmayabileceği için klasor/modul_ismi şeklinde dosyalar include ediliyor.*/
								$modul_klasor = $vt->select( $SQL_modul_klasor, array( $_REQUEST[ 'modul' ] ) );
								$modul_klasor = $modul_klasor[ 2 ][ 0 ][ 'klasor' ];
								$modul  = "_modul/" . $modul_klasor  . "/" . $_REQUEST[ 'modul' ] . ".php";
							}
						} else {
							$modul	= "_modul/anasayfa/anasayfa.php";
						}	
				?>

			<div class="content-wrapper">
				<!-- Content Header (Page header) -->
				<div class="content-header">
				<div class="container-fluid">
					<!--div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Modül Adı</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="#">Home</a></li>
						<li class="breadcrumb-item active">modul_adi</li>
						</ol>
					</div>
					</div-->
				</div>
				</div>
				<!-- /.content-header -->

				<!-- Main content -->
				<section class="content">
				<div class="container-fluid">
					<?php include $modul; ?>
				</div><!-- /.container-fluid -->
				</section>
				<!-- /.content -->
			</div>
			<!-- /.content-wrapper -->
				<?php include "_modul/footer.php" ?>
			</div>
		</body>
	<?PHP } else { include "_modul/giris.php"; } ?>
<script>
$(function () {
	$(":input").inputmask();

	//Initialize Select2 Elements
	$('.select2').select2()

	//Initialize Select2 Elements
	$('.select2bs4').select2({
	  theme: 'bootstrap4'
	})


	$("input[data-bootstrap-switch]").each(function(){
		$(this).bootstrapSwitch('state', $(this).prop('checked'));
	});
})
</script>
<script>
  $(function () {
	  
    $("#example1").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

	$('#example2').DataTable({
	  "paging": true,
	  "lengthChange": true,
	  "searching": true,
	  "ordering": true,
	  "info": true,
	  "autoWidth": false,
	  "responsive": true,
	  'pageLength'	: 25,
	  'language'		: {
		'url': '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Turkish.json'
	}

	});
	$('#crm').DataTable({
	  "paging": true,
	  "lengthChange": true,
	  "searching": true,
	  "ordering": true,
	  "info": true,
	  "autoWidth": false,
	  "responsive": true,
	  'pageLength'	: 25,
	  'language'		: {
		'url': '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Turkish.json'
	}

	});
  });

	/* Slect2 nesnesinin sayfanın genişliğine göre otomatik uzayıp kısalmasını sağlar*/
	$( window ).on( 'resize', function() {
		$('.form-group').each(function() {
			var formGroup = $( this ),
				formgroupWidth = formGroup.outerWidth();
			formGroup.find( '.select2-container' ).css( 'width', formgroupWidth );
		});
	} );
	
	/* Slect2 nesnesinin sayfanın genişliğine göre otomatik uzayıp kısalmasını sağlar*/
	$( window ).on( 'resize', function() {
		$('.description-block').each(function() {
			var formGroup = $( this ),
				formgroupWidth = formGroup.outerWidth();
			formGroup.find( '.select2-container' ).css( 'width', formgroupWidth );
		});
	} );
	
	
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip()
	});

</script>

</html>
