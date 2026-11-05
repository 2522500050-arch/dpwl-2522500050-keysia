<?php
// Pastikan path menunjuk ke folder AdminLTE sesuai letak di folder public/assets/
$adminlte_url = base_url('/template/AdminLTE-2.4.18/');
require APPPATH . '/views/templates/header.php';
require APPPATH . '/views/templates/sidebar.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Page Header
      <small>Optional description</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
      <li class="active">Here</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content container-fluid">

    <!-------------------------- 
        | Your Page Content Here | 
        -------------------------->

    <p>
      Login sebagai:
      <strong><?= htmlspecialchars(
                $username,
                ENT_QUOTES,
                'UTF-8'
              ) ?></strong>
    </p>

    <form method="post"
      action="<?= htmlspecialchars(
                site_url('auth/logout'),
                ENT_QUOTES,
                'UTF-8'
              ) ?>">
      <button type="submit"
        class="btn btn-danger">
        Logout
      </button>
    </form>

  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
require APPPATH . '/views/templates/footer.php';
?>


<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AdminLTE 2 | Starter</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <link rel="stylesheet" href="<?= htmlspecialchars($adminlte_url . 'bower_components/bootstrap/dist/css/bootstrap.min.css', ENT_QUOTES, 'UTF-8') ?>">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= htmlspecialchars($adminlte_url . 'bower_components/font-awesome/css/font-awesome.min.css', ENT_QUOTES, 'UTF-8') ?>">

  <!-- Ionicons -->
  <link rel="stylesheet" href="<?= htmlspecialchars($adminlte_url . 'bower_components/Ionicons/css/ionicons.min.css', ENT_QUOTES, 'UTF-8') ?>">

  <!-- Theme style -->
  <link rel="stylesheet" href="<?= htmlspecialchars($adminlte_url . 'dist/css/AdminLTE.min.css', ENT_QUOTES, 'UTF-8') ?>">

  <!-- AdminLTE Skins -->
  <link rel="stylesheet" href="<?= htmlspecialchars($adminlte_url . 'dist/css/skins/skin-blue.min.css', ENT_QUOTES, 'UTF-8') ?>">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]> 
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script> 
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script> 
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">

    <!-- Main Header -->
    <header class="main-header">

      <!-- Logo -->
      <a href="<?= htmlspecialchars(site_url('admin'), ENT_QUOTES, 'UTF-8') ?>" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>A</b>LT</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>Admin</b>LTE</span>
      </a>

      <!-- Header Navbar -->
      <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
          <span class="sr-only">Toggle navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <!-- Messages: style can be found in dropdown.less-->
            <li class="dropdown messages-menu">
              <!-- Menu toggle button -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-envelope-o"></i>
                <span class="label label-success">4</span>
              </a>
              <ul class="dropdown-menu">
                <li class="header">You have 4 messages</li>
                <li>
                  <!-- inner menu: contains the messages -->
                  <ul class="menu">
                    <li><!-- start message -->
                      <a href="#">
                        <div class="pull-left">
                          <!-- User Image -->
                          <img src="<?= htmlspecialchars($adminlte_url . 'dist/img/avatar2.png', ENT_QUOTES, 'UTF-8') ?>" class="img-circle" alt="User Image">
                        </div>
                        <!-- Message title and timestamp -->
                        <h4>
                          Support Team
                          <small><i class="fa fa-clock-o"></i> 5 mins</small>
                        </h4>
                        <!-- The message -->
                        <p>Why not buy a new awesome theme?</p>
                      </a>
                    </li>
                    <!-- end message -->
                  </ul>
                  <!-- /.menu -->
                </li>
                <li class="footer"><a href="#">See All Messages</a></li>
              </ul>
            </li>
            <!-- /.messages-menu -->

            <!-- Notifications Menu -->
            <li class="dropdown notifications-menu">
              <!-- Menu toggle button -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-bell-o"></i>
                <span class="label label-warning">10</span>
              </a>
              <ul class="dropdown-menu">
                <li class="header">You have 10 notifications</li>
                <li>
                  <!-- Inner Menu: contains the notifications -->
                  <ul class="menu">
                    <li><!-- start notification -->
                      <a href="#">
                        <i class="fa fa-users text-aqua"></i> 5 new members joined today
                      </a>
                    </li>
                    <!-- end notification -->
                  </ul>
                </li>
                <li class="footer"><a href="#">View all</a></li>
              </ul>
            </li>

            <!-- Tasks Menu -->
            <li class="dropdown tasks-menu">
              <!-- Menu Toggle Button -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-flag-o"></i>
                <span class="label label-danger">9</span>
              </a>
              <ul class="dropdown-menu">
                <li class="header">You have 9 tasks</li>
                <li>
                  <!-- Inner menu: contains the tasks -->
                  <ul class="menu">
                    <li><!-- Task item -->
                      <a href="#">
                        <h3>
                          Design some buttons
                          <small class="pull-right">20%</small>
                        </h3>
                        <div class="progress xs">
                          <div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">20% Complete</span>
                          </div>
                        </div>
                      </a>
                    </li>
                    <!-- end task item -->
                  </ul>
                </li>
                <li class="footer">
                  <a href="#">View all tasks</a>
                </li>
              </ul>
            </li>

            <!-- User Account Menu -->
            <li class="dropdown user user-menu">
              <!-- Menu Toggle Button -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="<?= htmlspecialchars($adminlte_url . 'dist/img/avatar2.png', ENT_QUOTES, 'UTF-8') ?>" class="user-image" alt="User Image">
                <span class="hidden-xs"><?= htmlspecialchars($username ?? 'Guest', ENT_QUOTES, 'UTF-8') ?></span>
              </a>
              <ul class="dropdown-menu">
                <!-- The user image in the menu -->
                <li class="user-header">
                  <img src="<?= htmlspecialchars($adminlte_url . 'dist/img/avatar2.png', ENT_QUOTES, 'UTF-8') ?>" class="img-circle" alt="User Image">
                  <p>
                    <?= htmlspecialchars($username ?? 'Guest', ENT_QUOTES, 'UTF-8') ?> - Web Developer
                    <small>Member since Nov. 2012</small>
                  </p>
                </li>
                <!-- Menu Body -->
                <li class="user-body">
                  <div class="row">
                    <div class="col-xs-4 text-center">
                      <a href="#">Followers</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Sales</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Friends</a>
                    </div>
                  </div>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                  <div class="pull-left">
                    <a href="#" class="btn btn-default btn-flat">Profile</a>
                  </div>
                  <div class="pull-right">
                    <a href="#" class="btn btn-default btn-flat">Sign out</a>
                  </div>
                </li>
              </ul>
            </li>
            <!-- Control Sidebar Toggle Button -->
            <li>
              <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
            </li>
          </ul>
        </div>
      </nav>
    </header>

    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
      <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
          <div class="pull-left image">
            <img src="<?= htmlspecialchars($adminlte_url . 'dist/img/avatar2.png', ENT_QUOTES, 'UTF-8') ?>" class="img-circle" alt="User Image">
          </div>
          <div class="pull-left info">
            <p>
              Login sebagai:
              <strong><?= htmlspecialchars($username ?? 'Guest', ENT_QUOTES, 'UTF-8') ?></strong>
            </p>
            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
          </div>
        </div>

        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
          <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Search...">
            <span class="input-group-btn">
              <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
            </span>
          </div>
        </form>

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu" data-widget="tree">
          <li class="header">HEADER</li>
          <li class="active"><a href="#"><i class="fa fa-link"></i> <span>Link</span></a></li>
          <li><a href="#"><i class="fa fa-link"></i> <span>Another Link</span></a></li>
          <li class="treeview">
            <a href="#"><i class="fa fa-link"></i> <span>Multilevel</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li><a href="#">Link in level 2</a></li>
              <li><a href="#">Link in level 2</a></li>
            </ul>
          </li>
        </ul>
      </section>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
      <section class="content-header">
        <h1>
          Page Header
          <small>Optional description</small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
          <li class="active">Here</li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content container-fluid">
        <p>
          Login sebagai:
          <strong><?= htmlspecialchars($username ?? 'Guest', ENT_QUOTES, 'UTF-8') ?></strong>
        </p>

        <form method="post" action="<?= htmlspecialchars(site_url('auth/logout'), ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="btn btn-danger">
            Logout
          </button>
        </form>
      </section>
    </div>

    <!-- Main Footer -->
    <footer class="main-footer">
      <div class="pull-right hidden-xs">
        Anything you want
      </div>
      <strong>Copyright &copy; 2016 <a href="#">Company</a>.</strong> All rights reserved.
    </footer>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
        <li class="active"><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
        <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="control-sidebar-home-tab">
          <h3 class="control-sidebar-heading">Recent Activity</h3>
          <ul class="control-sidebar-menu">
            <li>
              <a href="javascript:;">
                <i class="menu-icon fa fa-birthday-cake bg-red"></i>
                <div class="menu-info">
                  <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>
                  <p>Will be 23 on April 24th</p>
                </div>
              </a>
            </li>
          </ul>

          <h3 class="control-sidebar-heading">Tasks Progress</h3>
          <ul class="control-sidebar-menu">
            <li>
              <a href="javascript:;">
                <h4 class="control-sidebar-subheading">
                  Custom Template Design
                  <span class="pull-right-container">
                    <span class="label label-danger pull-right">70%</span>
                  </span>
                </h4>
                <div class="progress progress-xxs">
                  <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
                </div>
              </a>
            </li>
          </ul>
        </div>

        <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>

        <div class="tab-pane" id="control-sidebar-settings-tab">
          <form method="post">
            <h3 class="control-sidebar-heading">General Settings</h3>
            <div class="form-group">
              <label class="control-sidebar-subheading">
                Report panel usage
                <input type="checkbox" class="pull-right" checked>
              </label>
              <p>Some information about this general settings option</p>
            </div>
          </form>
        </div>
      </div>
    </aside>

    <div class="control-sidebar-bg"></div>
  </div>

  <!-- REQUIRED JS SCRIPTS -->
  <script src="<?= htmlspecialchars($adminlte_url . 'bower_components/jquery/dist/jquery.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars($adminlte_url . 'bower_components/bootstrap/dist/js/bootstrap.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars($adminlte_url . 'dist/js/adminlte.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
</body>

</html>