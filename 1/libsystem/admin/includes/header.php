<?php 
// Determine the correct path based on where this file is being called from
$base_path = (strpos($_SERVER['PHP_SELF'], '/superadmin/') !== false) ? '../../' : '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  	<meta charset="utf-8">
  	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  	<title>Library System using PHP</title>

  	<!-- Responsive design meta tag -->
  	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  	<!-- Favicon -->
  	<link rel="icon" type="image/png" href="<?php echo $base_path; ?>images/logo.png">

  	<!-- Bootstrap 3.3.7 CSS -->
  	<link rel="stylesheet" href="<?php echo $base_path; ?>bower_components/bootstrap/dist/css/bootstrap.min.css">

  	<!-- Font Awesome for icons -->
  	<link rel="stylesheet" href="<?php echo $base_path; ?>bower_components/font-awesome/css/font-awesome.min.css">

  	<!-- AdminLTE style for the admin panel -->
  	<link rel="stylesheet" href="<?php echo $base_path; ?>dist/css/AdminLTE.min.css">

  	<!-- DataTables CSS for tables -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">

    <!-- Daterange picker CSS -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>bower_components/bootstrap-daterangepicker/daterangepicker.css">

    <!-- Bootstrap time picker CSS -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>plugins/timepicker/bootstrap-timepicker.min.css">

    <!-- Bootstrap datepicker CSS -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">

    <!-- AdminLTE Skins (optional) -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>dist/css/skins/_all-skins.min.css">
    
  	<!-- HTML5 Shim and Respond.js for IE8 support -->
  	<!--[if lt IE 9]>
  	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  	<![endif]-->

  	<!-- Google Fonts (Source Sans Pro) -->
  	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

  	<!-- Custom CSS for page -->
  	<style type="text/css">
  		.mt20 {
  			margin-top: 20px;
  		}

      /* Bold text style */
      .bold {
        font-weight: bold;
      }

      /* Chart legend styling */
      #legend ul {
        list-style: none;
      }

      #legend ul li {
        display: inline;
        padding-left: 30px;
        position: relative;
        margin-bottom: 4px;
        border-radius: 5px;
        padding: 2px 8px 2px 28px;
        font-size: 14px;
        cursor: default;
        transition: background-color 200ms ease-in-out;
      }

      #legend li span {
        display: block;
        position: absolute;
        left: 0;
        top: 0;
        width: 20px;
        height: 100%;
        border-radius: 5px;
      }

      /* Fixed navbar styling */
      .main-header {
        position: fixed !important;
        top: 0;
        right: 0;
        left: 0;
        z-index: 1030;
        height: 50px;
      }

      /* Fixed sidebar styling */
      .main-sidebar {
        position: fixed !important;
        top: 50px;
        left: 0;
        height: calc(100vh - 50px) !important;
        overflow-y: auto !important;
        overflow-x: hidden;
      }

      /* Ensure sidebar content is scrollable */
      .main-sidebar .sidebar {
        padding-bottom: 50px;
      }

      /* Adjust content wrapper to account for fixed sidebar and navbar */
      .content-wrapper {
        margin-top: 50px;
        margin-left: 230px;
      }

      /* Desktop view - Sidebar always visible */
      @media (min-width: 768px) {
        .content-wrapper,
        .main-footer {
          margin-left: 230px;
        }

        .main-sidebar {
          width: 230px !important;
        }
      }

      /* Mobile view - Sidebar hidden by default */
      @media (max-width: 767px) {
        .main-sidebar {
          width: 230px !important;
          left: -230px !important;
          transition: left 0.3s ease !important;
        }

        .content-wrapper,
        .main-footer {
          margin-left: 0 !important;
        }

        /* Show sidebar when toggled */
        .sidebar-open .main-sidebar {
          left: 0 !important;
          z-index: 999 !important;
        }

        /* Dim content when sidebar is open */
        .sidebar-open .content-wrapper::before {
          content: '';
          position: fixed;
          top: 50px;
          left: 0;
          right: 0;
          bottom: 0;
          background: rgba(0, 0, 0, 0.5);
          z-index: 998;
        }

        .sidebar-open .main-header {
          z-index: 1000 !important;
        }
      }

      /* Scrollbar styling for sidebar */
      .main-sidebar::-webkit-scrollbar {
        width: 8px;
      }

      .main-sidebar::-webkit-scrollbar-track {
        background: #1a1a1a;
      }

      .main-sidebar::-webkit-scrollbar-thumb {
        background: #006400;
        border-radius: 4px;
      }

      .main-sidebar::-webkit-scrollbar-thumb:hover {
        background: #228B22;
      }
      
      .content-header {
        margin: 0 !important;
        padding-top: 20px !important;
      }

      /* Sidebar Collapse Styles */
      .sidebar-collapse .main-sidebar {
        width: 50px !important;
      }

      .sidebar-collapse .content-wrapper,
      .sidebar-collapse .main-footer {
        margin-left: 50px !important;
      }

      .sidebar-collapse .sidebar-menu > li > a {
        padding: 12px 0 !important;
        text-align: center !important;
      }

      .sidebar-collapse .sidebar-menu > li > a > span,
      .sidebar-collapse .sidebar-menu > li > .treeview-menu {
        display: none !important;
      }

      .sidebar-collapse .sidebar-menu > li > a > i {
        margin-right: 0 !important;
        font-size: 18px !important;
      }

      .sidebar-collapse .user-panel {
        padding: 10px 5px !important;
      }

      .sidebar-collapse .user-panel > .image img {
        width: 32px !important;
        height: 32px !important;
      }

      .sidebar-collapse .user-panel > .info {
        display: none !important;
      }

      .sidebar-collapse .sidebar-menu > li.header {
        padding: 0 !important;
        text-align: center !important;
        font-size: 10px !important;
        height: auto !important;
      }

      .sidebar-collapse .main-sidebar .sidebar {
        padding-bottom: 20px !important;
      }
  	</style>
</head>