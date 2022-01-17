<body class="hold-transition login-page" style="background-image: url('img/bg3.jpg');background-size: cover;">
<div class="login-box">
		<?php
			if( array_key_exists( 'giris_var', $_SESSION ) ) {
				if( $_SESSION[ 'giris_var' ] == 'hayir' ) {
			?>
			<div class="alert alert-danger alert-dismissible">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				<h4><i class="icon fa fa-ban"></i> Hata!</h4>
				Giriş Başarısız lütfen kullanıcı adı ve şifrenizi kontrol ederek tekrar deneyiniz!
			</div>
		<?php 
			} }
		?>
  <!-- /.login-logo -->
  <div class="card card-outline card-warning">
    <div class="card-header text-center">
		<!--img src="img/wowlogo.jfif" width="100%"-->
      <a href="../../index2.html" class="h1">Tesis Denetim Sistemi</b></a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Kullanıcı adı ve şifrenizle giriş yapınız.</p>

      <form action="_modul/girisKontrol.php" method="post"  id = "kayit_formu">
        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="Email" name="kulad">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" name="sifre">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember">
              <label for="remember">
				Beni Hatırla
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-warning btn-block">Devam</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <!--div class="social-auth-links text-center mt-2 mb-3">
        <a href="#" class="btn btn-block btn-primary">
          <i class="fab fa-facebook mr-2"></i> Facebook ile giriş yap
        </a>
        <a href="#" class="btn btn-block btn-danger">
          <i class="fab fa-google mr-2"></i> Google ile giriş yap
        </a>
      </div-->
      <!-- /.social-auth-links -->

      <p class="mb-1">
        <a href="forgot-password.html">Şifremi Unuttum</a>
      </p>
      <!--p class="mb-0">
        <a href="register.html" class="text-center">Sisteme kayıt ol</a>
      </p-->
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->

</body>
