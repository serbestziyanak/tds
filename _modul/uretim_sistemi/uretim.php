<style>
.badge-number{
	font-size: 1.6em;
	margin-bottom:5px;
	letter-spacing: .2rem;
	font-family:'digital-clock-font';
}
.badge-label{
	font-size: 1.1em;
	margin-bottom:5px;
}
@font-face{
 font-family:'digital-clock-font';
 src: url('font/digital-7.ttf');
 letter-spacing: .2rem;
}
</style>

<script>

function istasyonGoster(id){
	$(id).show(100);
}

function istasyonGizle(id){
	$(id).hide(100);
}

</script>
<div class="content-wrapper">
	<section class="content-header">
		
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-12">
					<center><h1><b>Üretim Hattı</b></h1></center>
				</div>
			</div>
		</div>
	</section>
	<section class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4">
					<div class="timeline">
						<div class="time-label">
							<span class="bg-secondary"><a href = "#" onclick = "istasyonGoster('#istasyon_1')">  <img class="img-circle elevation-2" src="resimler/resim_yok.jpg" width = "40px" alt="User Avatar"> Fırat KAPAR</a></span>
						</div>
						<div>
							<div class="timeline-item" id = "istasyon_1" style = "display:none;">
								<span class="time">
									<i class="fas fa-clock"></i> 28.03.2023
									<button type="button" class="btn btn-tool"><i class="fas fa-times fa-lg" onclick = "istasyonGizle('#istasyon_1')"></i></button>
								</span>
								<h3 class="timeline-header"><a href="#">Cep Dikimi</a> Zara Pantolon Üretimi</h3>
								<div class="timeline-body">
									<div class="card card-widget widget-user-2">
										<!-- Add the bg color to the header using any of the bg-* classes -->
										<div class="widget-user-header bg-success">
											<div class="widget-user-image">
											<img class="img-circle elevation-2" src="resimler/resim_yok.jpg" alt="User Avatar">
											</div>
											<!-- /.widget-user-image -->
											<h3 class="widget-user-username">Fırat KAPAR</h3>
											<h5 class="widget-user-desc">#133</h5>
										</div>
										<div class="card-footer p-0">
											<ul class="nav flex-column">
	
												<li class="nav-item">
													<a href="#" class="nav-link">
													Hedef(Günlük) <span class="float-right badge bg-primary badge-number">842</span>
													</a>
												</li>
												<li class="nav-item">
													<a href="#" class="nav-link">
													Tamamlanan <span class="float-right badge bg-danger badge-number">250</span>
													</a>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
		
		
						<div class="time-label">
							<span class="bg-success"><a href = "#" onclick = "istasyonGoster('#istasyon_2')">  <img class="img-circle elevation-2" src="resimler/resim_yok.jpg" width = "40px" alt="User Avatar"> Serbest Ziyanak</a></span>
						</div>
						<div>
							<div class="timeline-item" id = "istasyon_2" style = "display:none;">
								<span class="time">
									<i class="fas fa-clock"></i> 28.03.2023
									<button type="button" class="btn btn-tool"><i class="fas fa-times fa-lg" onclick = "istasyonGizle('#istasyon_2')"></i></button>
								</span>
								<h3 class="timeline-header"><a href="#">Cep Dikimi</a> Zara Pantolon Üretimi</h3>
								<div class="timeline-body">
									<div class="card card-widget widget-user-2">
										<!-- Add the bg color to the header using any of the bg-* classes -->
										<div class="widget-user-header bg-success">
											<div class="widget-user-image">
											<img class="img-circle elevation-2" src="resimler/resim_yok.jpg" alt="User Avatar">
											</div>
											<!-- /.widget-user-image -->
											<h3 class="widget-user-username">Fırat KAPAR</h3>
											<h5 class="widget-user-desc">#133</h5>
										</div>
										<div class="card-footer p-0">
											<ul class="nav flex-column">
	
												<li class="nav-item">
													<a href="#" class="nav-link">
													Hedef(Günlük) <span class="float-right badge bg-primary badge-number">842</span>
													</a>
												</li>
												<li class="nav-item">
													<a href="#" class="nav-link">
													Tamamlanan <span class="float-right badge bg-danger badge-number">250</span>
													</a>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
		
		
		
		
		
					</div>
				</div>
			</div>
		</div>
	</section>
</div>