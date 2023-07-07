  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <!--<li class="nav-item d-sm-inline-block">
        <div class="btn-group">
          <button type="button" class="btn btn-default"><?php echo $_SESSION['firma_adi']; ?></button>
          <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false">
          <span class="sr-only">Toggle Dropdown</span>
          </button>
          <div class="dropdown-menu" role="menu" style="">
            <?php 
              $firmalar = $_SESSION['firmalarListesi'];
              foreach ($firmalar as $firma) {
                echo '<a class="dropdown-item" href="_modul/firmaSec.php?firma_id='.$firma["id"].'&firma_adi='.$firma["adi"].'">'.$firma["adi"].'</a>';
              }
            ?>
          </div>
        </div>
      </li>
      
      li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li-->
    </ul>
    <!--<span class="nav-link text-red">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ||</span>
    Dönem KAPAT
    <form class="form-inline" action = "_modul/puantaj/donemKapat.php" method = "POST">
      <div class="input-group input-group-sm">
        <span class="nav-link">Dönem Kapat</span>
        <select name="yil" class="form-control">
          <option value="2021" <?php echo date("Y") == "2021" ? "selected" : null; ?>>2021</option>
          <option value="2022" <?php echo date("Y") == "2022" ? "selected" : null; ?>>2022</option>
        </select>&nbsp;
        
        <?php $ay = date("m"); settype( $ay,"integer");?>
        <select name="ay" class="form-control">
          <option value="1" <?php echo $ay   == "1"  ? "selected" : null; ?>>Ocak</option>
          <option value="2" <?php echo $ay   == "2"  ? "selected" : null; ?>>Şubat</option>
          <option value="3" <?php echo $ay   == "3"  ? "selected" : null; ?>>Mart</option>
          <option value="4" <?php echo $ay   == "4"  ? "selected" : null; ?>>Nisan</option>
          <option value="5" <?php echo $ay   == "5"  ? "selected" : null; ?>>Mayıs</option>
          <option value="6" <?php echo $ay   == "6"  ? "selected" : null; ?>>Haziran</option>
          <option value="7" <?php echo $ay   == "7"  ? "selected" : null; ?>>Temmuz</option>
          <option value="8" <?php echo $ay   == "8"  ? "selected" : null; ?>>Ağustos</option>
          <option value="9" <?php echo $ay   == "9"  ? "selected" : null; ?>>Eylül</option>
          <option value="10" <?php echo $ay  == "10" ? "selected" : null; ?>>Ekim</option>
          <option value="11" <?php echo $ay  == "11" ? "selected" : null; ?>>Kasım</option>
          <option value="12" <?php echo $ay  == "12" ? "selected" : null; ?>>Aralık</option>
        </select>&nbsp;
        <button type="submit" class="form-control btn btn-info">Kapat</button>
      </div>
    </form>-->
    

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Messages Dropdown Menu -->
      <!--li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <div class="media">
              <img src="dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <div class="media">
              <img src="dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  John Pierce
                  <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">I got your message bro</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <div class="media">
              <img src="dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Nora Silvester
                  <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li-->
      <!-- Notifications Dropdown Menu -->
      <!--li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">15 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 4 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li-->
      
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="true">
          <i class="fas fa-calendar-check"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-2" >
          <span class="dropdown-item dropdown-header">Dönem Kapat<br><code>(Kapatılan döneme ait, Giriş Çıkış, <br>Tarife ve Avans düzenlenemez.)</code></span>
          <form class="" action = "_modul/puantaj/donemKapat.php" method = "POST">
            <div class="form-group">
              <label class="control-label">Yıl</label>
              <select name="yil" class="form-control">
                <option value="2021" <?php echo date("Y") == "2021" ? "selected" : null; ?>>2021</option>
                <option value="2022" <?php echo date("Y") == "2022" ? "selected" : null; ?>>2022</option>
                <option value="2023" <?php echo date("Y") == "2023" ? "selected" : null; ?>>2023</option>
              </select>
            </div>
              
              <?php $ay = date("m"); settype( $ay,"integer");?>
            <div class="form-group">
              <label class="control-label">Ay</label>
              <select name="ay" class="form-control">
                <option value="1" <?php echo $ay   == "1"  ? "selected" : null; ?>>Ocak</option>
                <option value="2" <?php echo $ay   == "2"  ? "selected" : null; ?>>Şubat</option>
                <option value="3" <?php echo $ay   == "3"  ? "selected" : null; ?>>Mart</option>
                <option value="4" <?php echo $ay   == "4"  ? "selected" : null; ?>>Nisan</option>
                <option value="5" <?php echo $ay   == "5"  ? "selected" : null; ?>>Mayıs</option>
                <option value="6" <?php echo $ay   == "6"  ? "selected" : null; ?>>Haziran</option>
                <option value="7" <?php echo $ay   == "7"  ? "selected" : null; ?>>Temmuz</option>
                <option value="8" <?php echo $ay   == "8"  ? "selected" : null; ?>>Ağustos</option>
                <option value="9" <?php echo $ay   == "9"  ? "selected" : null; ?>>Eylül</option>
                <option value="10" <?php echo $ay  == "10" ? "selected" : null; ?>>Ekim</option>
                <option value="11" <?php echo $ay  == "11" ? "selected" : null; ?>>Kasım</option>
                <option value="12" <?php echo $ay  == "12" ? "selected" : null; ?>>Aralık</option>
              </select>
            </div>
              <button type="submit" class="form-control btn btn-info">Kapat</button>
          </form>
        </div>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="true">
          <i class="fas fa-hotel"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-1" >
          <span class="dropdown-item dropdown-header">Firma Değiştir</span>
          <?php 
            $firmalar = $_SESSION['firmalarListesi'];
            foreach ($firmalar as $firma) {
              echo '<a class="dropdown-item kisalt" href="_modul/firmaSec.php?firma_id='.$firma["id"].'&firma_adi='.$firma["adi"].'">'.$firma["adi"].'</a>';
            }
          ?>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="sagSidebar" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
        
  </nav>
  <!-- /.navbar -->
